<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$code = $_GET['code'] ?? '';
if (!$code) {
  echo json_encode(['exists' => false]);
  exit;
}

$stmt = $pdo->prepare("SELECT name FROM products WHERE code = ?");
$stmt->execute([$code]);
$product = $stmt->fetch();

if ($product) {
  echo json_encode(['exists' => true, 'name' => $product['name']]);
} else {
  echo json_encode(['exists' => false]);
}
