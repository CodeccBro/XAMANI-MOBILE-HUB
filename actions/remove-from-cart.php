<?php
session_start();

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

if (!$productId || $productId < 1) {
    $_SESSION['cart_message'] = 'Invalid product selection.';
    $_SESSION['cart_message_type'] = 'error';

    header('Location: ../cart.php');
    exit;
}

if (isset($_SESSION['cart'][$productId])) {
    unset($_SESSION['cart'][$productId]);

    $_SESSION['cart_message'] = 'Product removed from your cart.';
    $_SESSION['cart_message_type'] = 'success';
} else {
    $_SESSION['cart_message'] = 'That product was not found in your cart.';
    $_SESSION['cart_message_type'] = 'error';
}

header('Location: ../cart.php', true, 303);
exit;