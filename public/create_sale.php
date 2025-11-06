<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$products = $pdo->query("SELECT id,name,price,stock FROM products ORDER BY name")->fetchAll();
$clients = $pdo->query("SELECT id,name FROM clients ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = !empty($_POST['client_id']) ? (int)$_POST['client_id'] : null;
    $user_id = current_user_id();
    $product_ids = $_POST['product_id'] ?? [];
    $qtys = $_POST['qty'] ?? [];
    $prices = $_POST['price'] ?? [];

    $items = [];
    $total = 0;
    for ($i=0;$i<count($product_ids);$i++){
        $pid = (int)$product_ids[$i];
        $q = (int)$qtys[$i];
        $pr = (float)$prices[$i];
        if($q<=0) continue;
        $subtotal = $q*$pr;
        $items[] = ['product_id'=>$pid,'qty'=>$q,'price'=>$pr,'subtotal'=>$subtotal];
        $total += $subtotal;
    }

    if (count($items) === 0) { $error = 'Aucun article ajouté.'; }
    else {
        $pdo->beginTransaction();
        try {
            $invoice_number = 'INV-'.date('Ymd').'-'.substr(md5(uniqid()),0,6);
            $stmt = $pdo->prepare("INSERT INTO sales (invoice_number,user_id,client_id,total,paid) VALUES (?,?,?,?,?)");
            $stmt->execute([$invoice_number,$user_id,$client_id,$total,$total]);
            $sale_id = $pdo->lastInsertId();
            $stmtItem = $pdo->prepare("INSERT INTO sale_items (sale_id,product_id,qty,price,subtotal) VALUES (?,?,?,?,?)");
            $stmtUpdate = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            foreach($items as $it){
                $stmtItem->execute([$sale_id,$it['product_id'],$it['qty'],$it['price'],$it['subtotal']]);
                $stmtUpdate->execute([$it['qty'],$it['product_id']]);
            }
            $pdo->commit();
            header('Location: invoice.php?id='.$sale_id);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Erreur: '.$e->getMessage();
        }
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Caisse</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-4">
  <h3>Caisse</h3>
  <?php if(!empty($error)): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="post" id="saleForm">
    <div class="mb-2">
      <label class="form-label">Client (optionnel)</label>
      <select name="client_id" class="form-select"><option value="">-- Aucun --</option>
        <?php foreach($clients as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <table class="table" id="itemsTable">
      <thead><tr><th>Produit</th><th>Prix</th><th>Qté</th><th>Sous-total</th><th></th></tr></thead>
      <tbody></tbody>
    </table>

    <div class="mb-2">
      <button type="button" class="btn btn-sm btn-outline-primary" id="addRow">Ajouter ligne</button>
    </div>

    <div class="mb-3">
      <h4>Total: <span id="total">0.00</span></h4>
    </div>

    <button class="btn btn-success">Enregistrer vente</button>
  </form>
</div>

<script>
// Data from server
const products = <?= json_encode($products) ?>;

function format(n){ return Number(n).toFixed(2); }

function addRow(productId='', qty=1, price=0){
  const tbody = document.querySelector('#itemsTable tbody');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select name="product_id[]" class="form-select product-select">
        <option value="">-- choisir --</option>
        ${products.map(p => `<option value="${p.id}" data-price="${p.price}" ${p.id==productId?'selected':''}>${p.name}</option>`).join('')}
      </select>
    </td>
    <td><input name="price[]" class="form-control price" value="${price}" readonly></td>
    <td><input name="qty[]" type="number" min="1" class="form-control qty" value="${qty}"></td>
    <td class="subtotal">0.00</td>
    <td><button type="button" class="btn btn-sm btn-danger remove">X</button></td>
  `;
  tbody.appendChild(tr);
  attachRowEvents(tr);
  recalc();
}

function attachRowEvents(tr){
  const select = tr.querySelector('.product-select');
  const priceInput = tr.querySelector('.price');
  const qtyInput = tr.querySelector('.qty');
  const remove = tr.querySelector('.remove');

  select.addEventListener('change', ()=>{
    const opt = select.options[select.selectedIndex];
    const p = opt ? opt.dataset.price || 0 : 0;
    priceInput.value = format(p);
    recalc();
  });
  qtyInput.addEventListener('input', recalc);
  remove.addEventListener('click', ()=>{ tr.remove(); recalc(); });
}

function recalc(){
  let total = 0;
  document.querySelectorAll('#itemsTable tbody tr').forEach(tr=>{
    const price = parseFloat(tr.querySelector('.price').value||0);
    const qty = parseInt(tr.querySelector('.qty').value||0);
    const subtotal = price * qty;
    tr.querySelector('.subtotal').innerText = format(subtotal);
    total += subtotal;
  });
  document.getElementById('total').innerText = format(total);
}

document.getElementById('addRow').addEventListener('click', ()=> addRow());
addRow();
</script>
</body></html>
