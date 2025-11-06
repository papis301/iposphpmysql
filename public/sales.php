<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$sales = $pdo->query("SELECT s.*, u.name as cashier FROM sales s JOIN users u ON s.user_id=u.id ORDER BY s.created_at DESC")->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Ventes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-4">
  <h3>Historique des ventes</h3>
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Invoice</th><th>Caissier</th><th>Total</th><th>Date</th><th></th></tr></thead>
    <tbody>
    <?php foreach($sales as $s): ?>
      <tr>
        <td><?= $s['id'] ?></td>
        <td><?= htmlspecialchars($s['invoice_number']) ?></td>
        <td><?= htmlspecialchars($s['cashier']) ?></td>
        <td><?= number_format($s['total'],2) ?></td>
        <td><?= $s['created_at'] ?></td>
        <td><a href="invoice.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">Voir</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body></html>
