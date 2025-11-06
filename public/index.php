<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
if(!is_logged_in()) header('Location: login.php');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>IPOS - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Dashboard</h1>
    <div>
      <a href="products.php" class="btn btn-sm btn-primary">Produits</a>
      <a href="clients.php" class="btn btn-sm btn-secondary">Clients</a>
      <a href="create_sale.php" class="btn btn-sm btn-success">Caisse</a>
      <a href="sales.php" class="btn btn-sm btn-info">Ventes</a>
      <a href="logout.php" class="btn btn-sm btn-outline-dark">Déconnexion</a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Ventes aujourd'hui</h5>
        <?php
        $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS sum FROM sales WHERE DATE(created_at)=CURDATE()");
        $stmt->execute();
        $row = $stmt->fetch();
        ?>
        <p><strong><?= $row['cnt'] ?></strong> ventes — Total: <strong><?= number_format($row['sum'],2) ?> </strong></p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
