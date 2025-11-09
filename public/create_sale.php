<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

// Récupérer les produits et clients
$products = $pdo->query("SELECT * FROM products ORDER BY name ASC")->fetchAll();
$clients = $pdo->query("SELECT * FROM clients ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Nouvelle vente - iPOS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/html5-qrcode"></script>
<style>
  /* Grille produits */
  .sale-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
  }
  .product-card {
    cursor: pointer;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    text-align: center;
    background: #fff;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .product-card:hover {
    transform: scale(1.03);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
  .product-img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    display: block;
  }
  .card-body {
    padding: 5px;
  }
  .card-title {
    font-size: 0.85rem;
    margin: 0;
  }
  .card-price {
    font-size: 0.8rem;
    color: #2a7a2e;
  }

  /* Panier */
  .cart-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 5px;
    vertical-align: middle;
  }

  @media (max-width: 576px) {
    .product-img { height: 80px; }
    .card-title { font-size: 0.75rem; }
    .card-price { font-size: 0.7rem; }
  }
</style>
</head>
<body class="container py-4">

<h3>Nouvelle vente</h3>
<a href="index.php" class="btn btn-secondary btn-sm mb-3">⬅ Retour</a>

<form id="saleForm" method="post" action="save_sale.php">
  <div class="mb-3">
    <label>Client</label>
    <select name="client_id" class="form-select">
      <option value="">-- Aucun --</option>
      <?php foreach($clients as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Scanner de code-barres -->
  <div class="mb-3">
    <label>Scanner un produit :</label><br>
    <button type="button" class="btn btn-success" onclick="startScanner()">📷 Ouvrir la caméra</button>
    <input type="text" id="barcodeInput" class="form-control mt-2" placeholder="Ou entrer le code-barres manuellement">
    <div id="reader" style="width:320px; display:none; margin-top:10px;"></div>
  </div>

  <!-- Grille produits -->
  <h5>Produits disponibles :</h5>
  <div class="sale-grid">
    <?php foreach($products as $p): ?>
      <div class="product-card" onclick='addProductToTable(<?= htmlspecialchars(json_encode($p)) ?>)'>
        <?php 
          $img = $p['image'] ? htmlspecialchars($p['image']) : 'https://via.placeholder.com/120x100?text=Produit';
        ?>
        <img src="<?= $img ?>" class="product-img">
        <div class="card-body">
          <h6 class="card-title"><?= htmlspecialchars($p['name']) ?></h6>
          <div class="card-price"><?= number_format($p['price'],2) ?> FCFA</div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Panier -->
  <h5>Panier :</h5>
  <table class="table table-bordered" id="saleTable">
    <thead class="table-light">
      <tr>
        <th>Produit</th>
        <th>Prix</th>
        <th>Quantité</th>
        <th>Sous-total</th>
        <th></th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <h4>Total : <span id="totalAmount">0.00</span> FCFA</h4>
  <button type="submit" class="btn btn-primary">Valider la vente</button>
</form>

<script>
// === SCANNER CODE-BARRES ===
let html5QrCode;

function startScanner() {
  const readerDiv = document.getElementById('reader');
  readerDiv.style.display = 'block';
  if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");
  html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, onScanSuccess)
    .catch(err => alert("Erreur caméra: " + err));
}

function onScanSuccess(decodedText) {
  document.getElementById('barcodeInput').value = decodedText;
  document.getElementById('barcodeInput').dispatchEvent(new Event('change'));
  stopScanner();
}

function stopScanner() {
  if (html5QrCode) {
    html5QrCode.stop().then(() => { document.getElementById('reader').style.display='none'; })
      .catch(err => console.error(err));
  }
}

// === CHAMP BARCODE ===
document.getElementById('barcodeInput').addEventListener('change', function() {
  const code = this.value.trim();
  if (!code) return;
  fetch('find_product.php?code=' + encodeURIComponent(code))
    .then(r => r.json())
    .then(p => {
      if (p) addProductToTable(p);
      else alert('Produit non trouvé pour le code : ' + code);
      this.value=''; this.focus();
    });
});

// === AJOUT PRODUIT AU PANIER ===
function addProductToTable(p) {
  const tbody = document.querySelector('#saleTable tbody');
  // Vérifier doublon
  let existing = Array.from(tbody.querySelectorAll('tr'))
      .find(tr => tr.querySelector('input[name="product_id[]"]').value == p.id);
  if (existing) {
    let qtyInput = existing.querySelector('input[name="qty[]"]');
    qtyInput.value = parseInt(qtyInput.value)+1;
    updateTotal(); return;
  }
  const row = document.createElement('tr');
  const imgHtml = `<img src="${p.image ? p.image : 'https://via.placeholder.com/50'}" class="cart-img">`;
  row.innerHTML = `
    <td>${imgHtml}${p.name}<input type="hidden" name="product_id[]" value="${p.id}"></td>
    <td>${parseFloat(p.price).toFixed(2)}<input type="hidden" name="price[]" value="${p.price}"></td>
    <td><input type="number" name="qty[]" class="form-control" value="1" min="1" onchange="updateTotal()"></td>
    <td class="subtotal">${parseFloat(p.price).toFixed(2)}</td>
    <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove();updateTotal()">X</button></td>
  `;
  tbody.appendChild(row);
  updateTotal();
}

// === CALCUL TOTAL ===
function updateTotal() {
  let total = 0;
  document.querySelectorAll('#saleTable tbody tr').forEach(tr => {
    const qty = parseInt(tr.querySelector('input[name="qty[]"]').value) || 0;
    const price = parseFloat(tr.querySelector('input[name="price[]"]').value);
    const subtotal = qty*price;
    tr.querySelector('.subtotal').textContent = subtotal.toFixed(2);
    total += subtotal;
  });
  document.getElementById('totalAmount').textContent = total.toFixed(2);
}
</script>

</body>
</html>
