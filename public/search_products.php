<?php
require_once '../includes/db.php';

$q = $_GET['q'] ?? '';
$q = trim($q);

if ($q) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR code LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$q%", "%$q%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}

$products = $stmt->fetchAll();

?>

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
        <?php if (count($products) > 0): ?>
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
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center text-muted">Aucun produit trouvé</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
