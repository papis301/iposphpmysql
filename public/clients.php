<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: clients.php?deleted=1');
    exit;
}
$clients = $pdo->query("SELECT * FROM clients ORDER BY created_at DESC")->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Clients</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Clients</h2>
    <div><a href="client_add.php" class="btn btn-sm btn-primary">Ajouter</a></div>
  </div>
  <table class="table table-striped">
  <thead><tr><th>ID</th><th>Nom</th><th>Téléphone</th><th>Email</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach($clients as $c): ?>
    <tr>
      <td><?= $c['id'] ?></td>
      <td><?= htmlspecialchars($c['name']) ?></td>
      <td><?= htmlspecialchars($c['phone']) ?></td>
      <td><?= htmlspecialchars($c['email']) ?></td>
      <td>
        <a href="clients.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  </table>
</div>
</body></html>
