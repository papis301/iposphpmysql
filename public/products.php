<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!is_admin()) die('Accès refusé.');

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: products.php?deleted=1');
    exit;
}

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Produits</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Produits</h2>
    <div><a href="product_add.php" class="btn btn-sm btn-primary">Ajouter</a> <a href="index.php" class="btn btn-sm btn-secondary">Dashboard</a></div>
  </div>
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Nom</th><th>Prix</th><th>Stock</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($products as $p): ?>
      <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= number_format($p['price'],2) ?></td>
        <td><?= $p['stock'] ?></td>
        <td>
          <a href="product_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Éditer</a>
          <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body></html>
