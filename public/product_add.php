<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_admin()) die('Accès refusé.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $code = trim($_POST['code']);
    $desc = trim($_POST['description']);

    $stmt = $pdo->prepare("INSERT INTO products (code,name,description,price,stock) VALUES (?,?,?,?,?)");
    $stmt->execute([$code,$name,$desc,$price,$stock]);
    header('Location: products.php?added=1');
    exit;
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Ajouter produit</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-4">
  <h3>Ajouter produit</h3>
  <form method="post">
    <div class="mb-2"><label class="form-label">Code</label><input name="code" class="form-control"></div>
    <div class="mb-2"><label class="form-label">Nom</label><input name="name" class="form-control" required></div>
    <div class="mb-2"><label class="form-label">Prix</label><input name="price" type="number" step="0.01" class="form-control" required></div>
    <div class="mb-2"><label class="form-label">Stock</label><input name="stock" type="number" class="form-control" required value="0"></div>
    <div class="mb-2"><label class="form-label">Description</label><textarea name="description" class="form-control"></textarea></div>
    <button class="btn btn-success">Ajouter</button>
  </form>
</div>
</body></html>
