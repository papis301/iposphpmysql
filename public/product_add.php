<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $code = trim($_POST['code']);
    $imagePath = null;

    // Vérifie si le code-barres existe déjà
    $check = $pdo->prepare("SELECT id FROM products WHERE code = ?");
    $check->execute([$code]);
    if ($check->fetch()) {
        $error = "⚠️ Ce code-barres existe déjà dans la base de données.";
    } else {
        // Gestion de l'image uploadée
        if (!empty($_FILES['image']['name'])) {
            $targetDir = __DIR__ . '/../uploads/products/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $fileName;

            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $imagePath = 'uploads/products/' . $fileName;
                } else {
                    $error = "Erreur lors du téléchargement de l’image.";
                }
            } else {
                $error = "Format d’image non valide. (jpeg, jpg, png, webp seulement)";
            }
        }

        // Insertion du produit
        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, code, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $stock, $code, $imagePath]);
            header('Location: products.php?added=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter un produit - iPOS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="container py-4">

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>➕ Ajouter un produit</h3>
  <a href="products.php" class="btn btn-secondary btn-sm">⬅ Retour</a>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-3 shadow-sm">
  <!-- Champ code-barres -->
  <div class="mb-3">
    <label class="form-label">Code-barres du produit</label>
    <div class="input-group">
      <input type="text" name="code" id="codeInput" class="form-control" placeholder="Scannez ou entrez le code" required>
      <button type="button" class="btn btn-success" id="scanButton" onclick="startScanner()">📷 Scanner</button>
      <button type="button" id="stopButton" class="btn btn-danger" onclick="stopScanner()" style="display:none;">✖ Arrêter</button>
    </div>
    <div id="reader" style="width:320px; display:none; margin-top:10px;"></div>
    <div id="codeStatus" class="mt-2"></div>
  </div>

  <!-- Champ nom -->
  <div class="mb-3">
    <label class="form-label">Nom du produit</label>
    <input type="text" name="name" class="form-control" required>
  </div>

  <!-- Champ prix -->
  <div class="mb-3">
    <label class="form-label">Prix (FCFA)</label>
    <input type="number" step="0.01" name="price" class="form-control" required min="0">
  </div>

  <!-- Champ stock -->
  <div class="mb-3">
    <label class="form-label">Stock initial</label>
    <input type="number" name="stock" class="form-control" required min="0">
  </div>

  <!-- Champ image -->
  <div class="mb-3">
    <label class="form-label">Image du produit</label>
    <input type="file" name="image" id="imageInput" accept="image/*" class="form-control">
    <div class="mt-2">
      <img id="preview" src="#" alt="Prévisualisation" class="img-thumbnail" style="max-width:150px; display:none;">
    </div>
  </div>

  <button type="submit" id="submitBtn" class="btn btn-primary">✅ Ajouter le produit</button>
</form>

<script>
let html5QrCode = null;

// Prévisualisation image
document.getElementById('imageInput').addEventListener('change', function(event) {
  const [file] = event.target.files;
  const preview = document.getElementById('preview');
  if (file) {
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
});

// Scanner code-barres
function startScanner() {
  const readerDiv = document.getElementById('reader');
  const stopButton = document.getElementById('stopButton');
  const scanButton = document.getElementById('scanButton');

  readerDiv.style.display = 'block';
  stopButton.style.display = 'inline-block';
  scanButton.disabled = true;

  if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");

  html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    (decodedText) => {
      document.getElementById('codeInput').value = decodedText;
      stopScanner();
      checkBarcode(decodedText);
    }
  ).catch(err => {
    alert("Erreur d’accès à la caméra : " + err);
    stopScanner();
  });
}

// Arrêter le scanner
function stopScanner() {
  const readerDiv = document.getElementById('reader');
  const stopButton = document.getElementById('stopButton');
  const scanButton = document.getElementById('scanButton');

  if (html5QrCode) {
    html5QrCode.stop().then(() => {
      readerDiv.style.display = 'none';
      stopButton.style.display = 'none';
      scanButton.disabled = false;
    }).catch(err => console.error("Erreur d'arrêt du scanner :", err));
  } else {
    readerDiv.style.display = 'none';
    stopButton.style.display = 'none';
    scanButton.disabled = false;
  }
}

// Vérification AJAX du code-barres
function checkBarcode(code) {
  const submitBtn = document.getElementById('submitBtn');
  const statusDiv = document.getElementById('codeStatus');

  if (!code) return;

  statusDiv.innerHTML = '<div class="text-muted">🔍 Vérification en cours...</div>';
  submitBtn.disabled = true;

  fetch('check_barcode.php?code=' + encodeURIComponent(code))
    .then(response => response.json())
    .then(data => {
      if (data.exists) {
        statusDiv.innerHTML = `<div class="alert alert-danger p-2">⚠️ Ce code existe déjà pour le produit <b>${data.name}</b>.</div>`;
        submitBtn.disabled = true;
      } else {
        statusDiv.innerHTML = `<div class="alert alert-success p-2">✅ Code valide, vous pouvez l'utiliser.</div>`;
        submitBtn.disabled = false;
      }
    })
    .catch(error => {
      console.error(error);
      statusDiv.innerHTML = '<div class="alert alert-warning p-2">⚠️ Erreur lors de la vérification.</div>';
      submitBtn.disabled = false;
    });
}

document.getElementById('codeInput').addEventListener('input', (e) => {
  const code = e.target.value.trim();
  if (code !== '') checkBarcode(code);
});
</script>
</body>
</html>
