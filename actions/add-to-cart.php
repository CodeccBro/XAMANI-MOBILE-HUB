<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../shop.php');
    exit;
}

$productId = filter_input(
    INPUT_POST,
    'product_id',
    FILTER_VALIDATE_INT
);

$quantity = filter_input(
    INPUT_POST,
    'quantity',
    FILTER_VALIDATE_INT
);

if (!$productId || !$quantity || $quantity < 1) {
    header('Location: ../shop.php');
    exit;
}

// Verify product and stock using the database
$stmt = $pdo->prepare("
    SELECT id, stock
    FROM products
    WHERE id = :id
      AND status = 'active'
    LIMIT 1
");

$stmt->execute(['id' => $productId]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: ../shop.php');
    exit;
}

$availableStock = (int) $product['stock'];

if ($availableStock <= 0) {
    header('Location: ../product.php?id=' . $productId);
    exit;
}

// Store cart items as product_id => quantity
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$currentQuantity = (int) ($_SESSION['cart'][$productId] ?? 0);

$newQuantity = $currentQuantity + $quantity;

// Never allow cart quantity to exceed available stock
$_SESSION['cart'][$productId] = min(
    $newQuantity,
    $availableStock
);

header('Location: ../cart.php');
exit;