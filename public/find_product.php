<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$code = $_GET['code'] ?? '';
if (!$code) { echo 'null'; exit; }

$stmt = $pdo->prepare("SELECT * FROM products WHERE code = ?");
$stmt->execute([$code]);
$product = $stmt->fetch();

echo json_encode($product ?: null);
