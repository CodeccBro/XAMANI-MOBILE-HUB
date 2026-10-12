<?php
session_start();
require_once __DIR__ . '/config/database.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($value) {
    return '₦' . number_format((float)$value, 2);
}

$cart = $_SESSION['cart'] ?? [];
$cartIds = [];

foreach ($cart as $id => $quantity) {
    if (filter_var($id, FILTER_VALIDATE_INT) && (int)$quantity > 0) {
        $cartIds[(int)$id] = (int)$quantity;
    }
}

if (!$cartIds) {
    header('Location: cart.php');
    exit;
}

$placeholders = implode(',', array_fill(0, count($cartIds), '?'));

$stmt = $pdo->prepare("
    SELECT id, name, brand, price, stock, image_path, status
    FROM products
    WHERE id IN ($placeholders)
");

$stmt->execute(array_keys($cartIds));
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$items = [];
$subtotal = 0;
$cartError = false;

foreach ($products as $product) {
    $id = (int)$product['id'];
    $quantity = $cartIds[$id];

    if (
        $product['status'] !== 'active' ||
        (int)$product['stock'] < $quantity
    ) {
        $cartError = true;
        continue;
    }

    $product['quantity'] = $quantity;
    $product['line_total'] = $quantity * (float)$product['price'];

    $subtotal += $product['line_total'];
    $items[] = $product;
}

if (count($items) !== count($cartIds)) {
    $cartError = true;
}

$deliveryFee = 0;
$total = $subtotal + $deliveryFee;

if (empty($_SESSION['checkout_csrf'])) {
    $_SESSION['checkout_csrf'] = bin2hex(random_bytes(32));
}

$errors = $_SESSION['checkout_errors'] ?? [];
$old = $_SESSION['checkout_old'] ?? [];

unset($_SESSION['checkout_errors'], $_SESSION['checkout_old']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Xamani Mobile Hub</title>

    <link rel="stylesheet" href="assets/css/checkout.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

    <header class="navbar">
        <a href="index.php" class="logo">
            <span class="logo-icon">X</span>
            <span>XAMANI <span class="logo-light">MOBILE HUB</span></span>
        </a>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="cart.php">Cart</a>
        </nav>

        <a href="shop.php" class="nav-shop-btn">Continue Shopping</a>
    </header>

    <main class="checkout-container">

        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <a href="cart.php">Cart</a>
            <span>›</span>
            <strong>Checkout</strong>
        </div>

        <div class="page-heading">
            <span class="eyebrow">SECURE ORDER DETAILS</span>
            <h1>Checkout</h1>
            <p>You're one step away from your next upgrade.</p>
        </div>

        <div class="checkout-steps">
            <div class="step completed">
                <span>✓</span> Shopping Cart
            </div>
            <div class="step active">
                <span>2</span> Checkout
            </div>
            <div class="step">
                <span>3</span> Confirmation
            </div>
        </div>

        <?php if ($cartError): ?>
        <div class="alert error">
            Some items in your cart are unavailable or exceed current stock.
            Please <a href="cart.php">review your cart</a> before placing an order.
        </div>
        <?php endif; ?>

        <?php if ($errors): ?>
        <div class="alert error">
            <strong>Please correct the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="actions/place-order.php" method="POST" class="checkout-layout" id="checkoutForm">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['checkout_csrf']) ?>">

            <div class="checkout-main">

                <section class="checkout-card">
                    <div class="card-title">
                        <span class="title-icon">👤</span>
                        <div>
                            <h2>Customer Information</h2>
                            <p>Tell us who is placing this order.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="customer_name">Full Name <span>*</span></label>
                        <input type="text" id="customer_name" name="customer_name" placeholder="Enter your full name"
                            value="<?= e($old['customer_name'] ?? '') ?>" maxlength="150" autocomplete="name" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number <span>*</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="e.g. 08012345678"
                            value="<?= e($old['phone'] ?? '') ?>" maxlength="25" autocomplete="tel" required>
                        <small>We'll use this number to contact you about your order.</small>
                    </div>
                </section>

                <section class="checkout-card">
                    <div class="card-title">
                        <span class="title-icon">📍</span>
                        <div>
                            <h2>Delivery Information</h2>
                            <p>Where should your order be delivered?</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Delivery Address <span>*</span></label>
                        <textarea id="address" name="address" rows="4"
                            placeholder="Enter your street, area, city and state" maxlength="1000"
                            required><?= e($old['address'] ?? '') ?></textarea>
                    </div>

                    <div class="delivery-notice">
                        <span>🚚</span>
                        <div>
                            <strong>Delivery Arrangement</strong>
                            <p>
                                Delivery availability and any applicable delivery charges
                                will be confirmed with you before dispatch.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="checkout-card">
                    <div class="card-title">
                        <span class="title-icon">💳</span>
                        <div>
                            <h2>Payment Method</h2>
                            <p>Choose how you would like to pay.</p>
                        </div>
                    </div>

                    <div class="payment-options">

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cash_on_delivery"
                                <?= ($old['payment_method'] ?? 'cash_on_delivery') === 'cash_on_delivery' ? 'checked' : '' ?>>
                            <span class="payment-symbol">💵</span>
                            <span class="payment-text">
                                <strong>Cash on Delivery</strong>
                                <small>Pay when your order is delivered, subject to confirmation.</small>
                            </span>
                            <span class="radio-indicator"></span>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="bank_transfer"
                                <?= ($old['payment_method'] ?? '') === 'bank_transfer' ? 'checked' : '' ?>>
                            <span class="payment-symbol">🏦</span>
                            <span class="payment-text">
                                <strong>Bank Transfer</strong>
                                <small>Receive payment instructions after placing your order.</small>
                            </span>
                            <span class="radio-indicator"></span>
                        </label>

                    </div>

                    <div class="payment-notice">
                        <span>🔒</span>
                        <p>No card or bank account details are collected on this page.</p>
                    </div>
                </section>

            </div>

            <aside class="checkout-sidebar">
                <div class="summary-card">

                    <h2>Order Summary</h2>
                    <p class="summary-subtitle">Review your items before ordering.</p>

                    <div class="summary-items">
                        <?php foreach ($items as $item): ?>
                        <div class="summary-item">
                            <div class="item-image">
                                <?php if (!empty($item['image_path'])): ?>
                                <img src="<?= e($item['image_path']) ?>" alt="<?= e($item['name']) ?>">
                                <?php else: ?>
                                <span>📱</span>
                                <?php endif; ?>
                            </div>

                            <div class="item-details">
                                <span class="item-brand"><?= e($item['brand']) ?></span>
                                <strong><?= e($item['name']) ?></strong>
                                <small>Qty: <?= (int)$item['quantity'] ?></small>
                            </div>

                            <span class="item-total">
                                <?= money($item['line_total']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-prices">
                        <div>
                            <span>Subtotal</span>
                            <strong><?= money($subtotal) ?></strong>
                        </div>

                        <div>
                            <span>Delivery</span>
                            <span class="delivery-pending">To be confirmed</span>
                        </div>
                    </div>

                    <div class="summary-total">
                        <span>Order Total</span>
                        <strong><?= money($total) ?></strong>
                    </div>

                    <p class="total-note">
                        Excludes any delivery charges agreed separately.
                    </p>

                    <button type="submit" class="place-order-btn" <?= $cartError || !$items ? 'disabled' : '' ?>>
                        Place Order →
                    </button>

                    <div class="order-disclaimer">
                        By placing your order, you agree to be contacted
                        regarding payment and delivery arrangements.
                    </div>

                    <a href="cart.php" class="back-to-cart">← Return to Cart</a>

                </div>

                <div class="help-card">
                    <span>💬</span>
                    <div>
                        <strong>Need assistance?</strong>
                        <p>Contact Xamani Mobile Hub for help with your order.</p>
                    </div>
                </div>
            </aside>

        </form>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <div>
                <a href="index.php" class="logo">
                    <span class="logo-icon">X</span>
                    <span>XAMANI <span class="logo-light">MOBILE HUB</span></span>
                </a>
                <p>Smartphones, accessories, and smarter everyday technology.</p>
            </div>
            <div class="footer-links">
                <a href="index.php">Home</a>
                <a href="shop.php">Shop</a>
                <a href="cart.php">Cart</a>
            </div>
        </div>
        <div class="footer-bottom">
            © <?= date('Y') ?> Xamani Mobile Hub. All rights reserved.
        </div>
    </footer>

</body>

</html>