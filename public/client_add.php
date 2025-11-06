<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $stmt = $pdo->prepare("INSERT INTO clients (name,phone,email,address) VALUES (?,?,?,?)");
    $stmt->execute([$name,$phone,$email,$address]);
    header('Location: clients.php?added=1');
    exit;
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Ajouter client</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-4">
  <h3>Ajouter client</h3>
  <form method="post">
    <div class="mb-2"><label class="form-label">Nom</label><input name="name" class="form-control" required></div>
    <div class="mb-2"><label class="form-label">Téléphone</label><input name="phone" class="form-control"></div>
    <div class="mb-2"><label class="form-label">Email</label><input name="email" class="form-control"></div>
    <div class="mb-2"><label class="form-label">Adresse</label><textarea name="address" class="form-control"></textarea></div>
    <button class="btn btn-success">Ajouter</button>
  </form>
</div>
</body></html>
