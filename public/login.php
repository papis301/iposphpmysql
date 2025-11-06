<?php
require_once __DIR__ . '/../includes/db.php';
session_start();
if (isset($_SESSION['user_id'])) header('Location: index.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password, name, role FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Téléphone ou mot de passe incorrect.";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Connexion</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card mx-auto" style="max-width:420px;padding:20px">
    <h3 class="mb-3">Connexion</h3>
    <?php if(!empty($error)): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post">
      <div class="mb-2"><label class="form-label">Téléphone</label><input class="form-control" name="phone" required></div>
      <div class="mb-2"><label class="form-label">Mot de passe</label><input type="password" class="form-control" name="password" required></div>
      <button class="btn btn-primary">Se connecter</button>
      <a href="register.php" class="btn btn-link">Créer un compte</a>
    </form>
  </div>
</div>
</body>
</html>
