<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT s.*, u.name AS cashier, c.name AS client_name FROM sales s LEFT JOIN users u ON s.user_id=u.id LEFT JOIN clients c ON s.client_id=c.id WHERE s.id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { echo 'Facture introuvable.'; exit; }
$items = $pdo->prepare("SELECT si.*, p.name FROM sale_items si JOIN products p ON si.product_id=p.id WHERE si.sale_id=?");
$items->execute([$id]);
$items = $items->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Facture <?=htmlspecialchars($s['invoice_number'])?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>@media print{ .no-print{display:none} }</style>
</head><body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Facture <?=htmlspecialchars($s['invoice_number'])?></h2>
    <div>
      <button onclick="window.print()" class="btn btn-sm btn-primary no-print">Imprimer</button>
      <a href="index.php" class="btn btn-sm btn-secondary no-print">Fermer</a>
    </div>
  </div>

  <p><strong>Caissier:</strong> <?=htmlspecialchars($s['cashier'])?> <br>
  <strong>Client:</strong> <?=htmlspecialchars($s['client_name']?:'--')?> <br>
  <strong>Date:</strong> <?=htmlspecialchars($s['created_at'])?></p>

  <table class="table">
    <thead><tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Sous-total</th></tr></thead>
    <tbody>
    <?php foreach($items as $it): ?>
      <tr>
        <td><?=htmlspecialchars($it['name'])?></td>
        <td><?= $it['qty'] ?></td>
        <td><?= number_format($it['price'],2) ?></td>
        <td><?= number_format($it['subtotal'],2) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <h4>Total: <?= number_format($s['total'],2) ?></h4>
</div>
</body></html>
