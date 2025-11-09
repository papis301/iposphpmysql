<?php
require_once '../includes/db.php';

// Récupération initiale de tous les produits
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des produits - iPOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📦 Liste des produits</h2>
        <div>
            <a href="index.php" class="btn btn-secondary me-2">🏠 Accueil</a>
            <a href="product_add.php" class="btn btn-success">➕ Ajouter un produit</a>
        </div>
    </div>

    <!-- Champ de recherche -->
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 Rechercher un produit par nom ou code-barres...">
    </div>

    <!-- Tableau des produits -->
    <div id="productTable">
        <table class="table table-bordered table-hover bg-white shadow-sm">
            <thead class="table-success">
                <tr>
                    <th>ID</th>
                    <th>Code-barres</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id']) ?></td>
                        <td><?= htmlspecialchars($p['code']) ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['price']) ?> FCFA</td>
                        <td><?= htmlspecialchars($p['stock']) ?></td>
                        <td>
                            <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">✏️ Modifier</a>
                            <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce produit ?')">🗑️ Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Recherche dynamique AJAX
$('#searchInput').on('keyup', function() {
    let query = $(this).val().trim();
    $.ajax({
        url: 'search_products.php',
        method: 'GET',
        data: { q: query },
        success: function(data) {
            $('#productTable').html(data);
        }
    });
});
</script>

</body>
</html>
