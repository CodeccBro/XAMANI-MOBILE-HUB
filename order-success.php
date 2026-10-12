<?php
session_start();

require_once __DIR__ . '/config/database.php';

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatPrice($amount)
{
    return '₦' . number_format((float) $amount, 2);
}

/*
|--------------------------------------------------------------------------
| Get Order ID
|--------------------------------------------------------------------------
*/

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId || $orderId < 1) {
    header('Location: shop.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Verify Order Access
|--------------------------------------------------------------------------
|
| The checkout process should store the successful order ID in the session.
| Only that order can be viewed here.
|
*/

if (
    !isset($_SESSION['last_order_id']) ||
    (int) $_SESSION['last_order_id'] !== $orderId
) {
    http_response_code(403);
    exit('You are not authorized to view this order.');
}

/*
|--------------------------------------------------------------------------
| Fetch Order
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE id = :id
    LIMIT 1
");

$stmt->execute(['id' => $orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    exit('Order not found.');
}

/*
|--------------------------------------------------------------------------
| Fetch Order Items
|--------------------------------------------------------------------------
*/

$itemStmt = $pdo->prepare("
    SELECT *
    FROM order_items
    WHERE order_id = :order_id
    ORDER BY id ASC
");

$itemStmt->execute(['order_id' => $orderId]);

$orderItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Payment Method
|--------------------------------------------------------------------------
*/

$paymentMethods = [
    'cash_on_delivery' => 'Cash on Delivery',
    'bank_transfer' => 'Bank Transfer'
];

$paymentMethod = $paymentMethods[$order['payment_method']]
    ?? ucfirst(str_replace('_', ' ', $order['payment_method']));

/*
|--------------------------------------------------------------------------
| Order Status
|--------------------------------------------------------------------------
*/

$orderStatus = ucfirst($order['status']);

$orderDate = date(
    'F j, Y \a\t g:i A',
    strtotime($order['created_at'])
);

$cartCount = array_sum(array_map(
    fn($item) => is_array($item)
        ? (int) ($item['quantity'] ?? 0)
        : (int) $item,
    $_SESSION['cart'] ?? []
));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Successful | Xamani Mobile Hub</title>

    <link rel="stylesheet" href="assets/css/order-success.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

    <!-- NAVBAR -->

    <header class="navbar">

        <a href="index.php" class="logo">
            <span class="logo-icon">X</span>
            <span>XAMANI <span class="logo-light">MOBILE HUB</span></span>
        </a>

        <nav class="nav-links" id="navLinks">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="shop.php?category=Smartphones">Smartphones</a>
            <a href="shop.php?category=Accessories">Accessories</a>
        </nav>

        <div class="nav-actions">
            <a href="cart.php" class="cart-link">
                🛒 Cart
                <?php if ($cartCount > 0): ?>
                <span class="cart-count"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <a href="shop.php" class="nav-shop-btn">Shop Now</a>
        </div>

        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false">
            ☰
        </button>

    </header>


    <!-- SUCCESS PAGE -->

    <main class="success-page">

        <!-- SUCCESS HEADER -->

        <section class="success-header">

            <div class="success-icon-wrapper">
                <div class="success-icon">
                    <svg viewBox="0 0 24 24" width="43" height="43" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12l4 4L19 6" />
                    </svg>
                </div>
            </div>

            <span class="success-label">ORDER CONFIRMED</span>

            <h1>Thank you for your order!</h1>

            <p>
                Your order has been successfully placed.
                We've received your details and your order
                is now awaiting processing.
            </p>

            <div class="order-reference">
                <span>Order Number</span>
                <strong>
                    #XMH-<?= str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT) ?>
                </strong>
            </div>

        </section>


        <!-- ORDER CONTENT -->

        <div class="success-layout">

            <!-- LEFT CONTENT -->

            <div class="success-main">

                <!-- ORDER ITEMS -->

                <section class="success-card">

                    <div class="card-heading">
                        <div>
                            <h2>Order Summary</h2>
                            <p>Here's what you ordered.</p>
                        </div>

                        <span class="item-count">
                            <?= count($orderItems) ?> Item(s)
                        </span>
                    </div>

                    <div class="order-items">

                        <?php foreach ($orderItems as $item): ?>

                        <div class="order-item">

                            <div class="item-icon">
                                📱
                            </div>

                            <div class="item-details">

                                <h3>
                                    <?= e($item['product_name']) ?>
                                </h3>

                                <p>
                                    Quantity: <?= (int) $item['quantity'] ?>
                                </p>

                                <span>
                                    <?= formatPrice($item['unit_price']) ?>
                                    each
                                </span>

                            </div>

                            <div class="item-price">
                                <?= formatPrice($item['subtotal']) ?>
                            </div>

                        </div>

                        <?php endforeach; ?>

                    </div>

                    <div class="order-totals">

                        <div class="total-row">
                            <span>Order Subtotal</span>
                            <strong><?= formatPrice($order['total_amount']) ?></strong>
                        </div>

                        <div class="total-row grand-total">
                            <span>Total Amount</span>
                            <strong><?= formatPrice($order['total_amount']) ?></strong>
                        </div>

                    </div>

                </section>


                <!-- DELIVERY INFORMATION -->

                <section class="success-card">

                    <div class="card-heading">
                        <div>
                            <h2>Customer Information</h2>
                            <p>Details provided during checkout.</p>
                        </div>
                    </div>

                    <div class="info-grid">

                        <div class="info-block">

                            <div class="info-icon">👤</div>

                            <div>
                                <span>Customer Name</span>
                                <strong>
                                    <?= e($order['customer_name']) ?>
                                </strong>
                            </div>

                        </div>

                        <div class="info-block">

                            <div class="info-icon">📞</div>

                            <div>
                                <span>Phone Number</span>
                                <strong>
                                    <?= e($order['phone']) ?>
                                </strong>
                            </div>

                        </div>

                        <div class="info-block full-width">

                            <div class="info-icon">📍</div>

                            <div>
                                <span>Delivery Address</span>
                                <strong>
                                    <?= nl2br(e($order['address'])) ?>
                                </strong>
                            </div>

                        </div>

                    </div>

                </section>

            </div>


            <!-- RIGHT SIDEBAR -->

            <aside class="success-sidebar">

                <!-- ORDER STATUS -->

                <div class="sidebar-card">

                    <h3>Order Details</h3>

                    <div class="detail-row">
                        <span>Order Number</span>
                        <strong>
                            #XMH-<?= str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT) ?>
                        </strong>
                    </div>

                    <div class="detail-row">
                        <span>Order Date</span>
                        <strong><?= e($orderDate) ?></strong>
                    </div>

                    <div class="detail-row">
                        <span>Payment Method</span>
                        <strong><?= e($paymentMethod) ?></strong>
                    </div>

                    <div class="detail-row">
                        <span>Order Status</span>
                        <span class="status-badge">
                            <?= e($orderStatus) ?>
                        </span>
                    </div>

                </div>


                <!-- NEXT STEPS -->

                <div class="sidebar-card next-steps">

                    <div class="next-icon">📦</div>

                    <h3>What happens next?</h3>

                    <div class="steps-list">

                        <div class="step">

                            <span class="step-number">1</span>

                            <div>
                                <strong>Order Received</strong>
                                <p>
                                    Your order has been submitted
                                    successfully.
                                </p>
                            </div>

                        </div>

                        <div class="step">

                            <span class="step-number">2</span>

                            <div>
                                <strong>Order Processing</strong>
                                <p>
                                    Your order will be reviewed
                                    and prepared.
                                </p>
                            </div>

                        </div>

                        <div class="step">

                            <span class="step-number">3</span>

                            <div>
                                <strong>Delivery Coordination</strong>
                                <p>
                                    The next steps for delivery
                                    will be communicated to you.
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </aside>

        </div>


        <!-- ACTION BUTTONS -->

        <div class="success-actions">

            <a href="shop.php" class="primary-btn">
                Continue Shopping →
            </a>

            <a href="index.php" class="secondary-btn">
                ← Back to Home
            </a>

            <button type="button" class="print-btn" onclick="window.print()">
                🖨 Print Order
            </button>

        </div>


        <!-- SUPPORT NOTE -->

        <div class="support-note">

            <span>💬</span>

            <p>
                Need assistance with your order?
                Keep your order number handy when contacting
                Xamani Mobile Hub.
            </p>

        </div>

    </main>


    <!-- FOOTER -->

    <footer class="footer">

        <div class="footer-content">

            <div class="footer-brand">

                <a href="index.php" class="logo">
                    <span class="logo-icon">X</span>
                    <span>XAMANI <span class="logo-light">MOBILE HUB</span></span>
                </a>

                <p>
                    Your destination for smartphones,
                    accessories, and smarter everyday technology.
                </p>

            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <a href="index.php">Home</a>
                <a href="shop.php">Shop</a>
                <a href="cart.php">Shopping Cart</a>
            </div>

            <div class="footer-links">
                <h4>Categories</h4>
                <a href="shop.php?category=Smartphones">Smartphones</a>
                <a href="shop.php?category=Accessories">Accessories</a>
                <a href="shop.php?category=Refurbished%20Phones">Refurbished</a>
            </div>

            <div class="footer-links">
                <h4>Customer</h4>
                <a href="account/login.php">Login</a>
                <a href="cart.php">My Cart</a>
            </div>

        </div>

        <div class="footer-bottom">

            <p>
                © <?= date('Y') ?> Xamani Mobile Hub.
                All rights reserved.
            </p>

            <p>Made for smarter shopping.</p>

        </div>

    </footer>


    <script>
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');

    menuToggle.addEventListener('click', function() {
        const isOpen = navLinks.classList.toggle('show');

        menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        menuToggle.textContent = isOpen ? '✕' : '☰';
    });
    </script>

</body>

</html>