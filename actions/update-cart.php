<?php
session_start();

require_once __DIR__ . '/../config/database.php';

function redirectCart($message, $type = 'success') {
    $_SESSION['cart_message'] = $message;
    $_SESSION['cart_message_type'] = $type;

    header('Location: ../cart.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cart.php');
    exit;
}

$token = (string)($_POST['csrf_token'] ?? '');

if (
    empty($_SESSION['cart_csrf']) ||
    !hash_equals($_SESSION['cart_csrf'], $token)
) {
    http_response_code(403);
    exit('Invalid request. Please return to your cart and try again.');
}

$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);

if (!$productId || $productId < 1 || !$quantity || $quantity < 1) {
    redirectCart('Please select a valid product quantity.', 'error');
}

if (!isset($_SESSION['cart'][$productId])) {
    redirectCart('That product is no longer in your cart.', 'error');
}

try {
    $stmt = $pdo->prepare("
        SELECT name, stock, status
        FROM products
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product || $product['status'] !== 'active') {
        unset($_SESSION['cart'][$productId]);
        redirectCart('This product is no longer available and was removed from your cart.', 'error');
    }

    $stock = (int)$product['stock'];

    if ($stock < 1) {
        unset($_SESSION['cart'][$productId]);
        redirectCart('This product is out of stock and was removed from your cart.', 'error');
    }

    if ($quantity > $stock) {
        redirectCart(
            'Only ' . $stock . ' unit(s) of ' . $product['name'] . ' are currently available.',
            'error'
        );
    }

    $_SESSION['cart'][$productId] = $quantity;

    redirectCart('Your cart has been updated.');

} catch (PDOException $e) {
    error_log('Cart update error: ' . $e->getMessage());
    redirectCart('Unable to update your cart right now. Please try again.', 'error');
}