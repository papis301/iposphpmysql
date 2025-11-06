<?php
require_once __DIR__ . '/../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $role = 'cashier';

    if (empty($phone) || empty($name) || empty($password)) {
        $error = "Tous les champs sont requis.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $error = "Ce numéro est déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (phone, name, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$phone, $name, $hash, $role]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Inscription</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-5">
  <div class="card mx-auto" style="max-width:520px;padding:20px">
    <h3>Créer un compte</h3>
    <?php if(!empty($error)): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post">
      <div class="mb-2"><label class="form-label">Nom</label><input class="form-control" name="name" required></div>
      <div class="mb-2"><label class="form-label">Téléphone</label><input class="form-control" name="phone" required></div>
      <div class="mb-2"><label class="form-label">Mot de passe</label><input type="password" class="form-control" name="password" required></div>
      <button class="btn btn-success">S'inscrire</button>
      <a href="login.php" class="btn btn-link">Se connecter</a>
    </form>
  </div>
</div>
</body></html>
