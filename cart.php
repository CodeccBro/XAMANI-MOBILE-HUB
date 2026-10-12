<?php
session_start();

require_once __DIR__ . '/config/database.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($amount) {
    return '₦' . number_format((float)$amount, 2);
}

// Ensure the cart exists
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Create CSRF token for cart actions
if (empty($_SESSION['cart_csrf'])) {
    $_SESSION['cart_csrf'] = bin2hex(random_bytes(32));
}

$cart = $_SESSION['cart'];
$cartProducts = [];
$subtotal = 0;
$totalQuantity = 0;
$cartWarnings = [];

// Fetch current product details from the database
$validCart = [];

foreach ($cart as $productId => $quantity) {
    $id = filter_var($productId, FILTER_VALIDATE_INT);
    $qty = filter_var($quantity, FILTER_VALIDATE_INT);

    if (!$id || $id < 1 || !$qty || $qty < 1) {
        continue;
    }

    $validCart[(int)$id] = min((int)$qty, 999);
}

$_SESSION['cart'] = $validCart;

if ($validCart) {
    $ids = array_keys($validCart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT id, name, brand, price, stock, image_path, status
        FROM products
        WHERE id IN ($placeholders)
    ");

    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $foundIds = [];

    foreach ($products as $product) {
        $id = (int)$product['id'];
        $foundIds[] = $id;
        $quantity = $validCart[$id];
        $stock = (int)$product['stock'];

        $product['quantity'] = $quantity;
        $product['line_total'] = (float)$product['price'] * $quantity;
        $product['available'] =
            $product['status'] === 'active' &&
            $stock >= $quantity &&
            $stock > 0;

        if ($product['status'] !== 'active' || $stock < 1) {
            $cartWarnings[] = $product['name'] . ' is currently unavailable.';
        } elseif ($quantity > $stock) {
            $cartWarnings[] = $product['name'] .
                ' has only ' . $stock . ' unit(s) available.';
        }

        $subtotal += $product['line_total'];
        $totalQuantity += $quantity;
        $cartProducts[] = $product;
    }

    // Remove products that no longer exist from the session cart
    foreach ($validCart as $id => $quantity) {
        if (!in_array($id, $foundIds, true)) {
            unset($_SESSION['cart'][$id]);
        }
    }
}

// Calculate whether the customer can proceed to checkout
$canCheckout = !empty($cartProducts);

foreach ($cartProducts as $product) {
    if (!$product['available']) {
        $canCheckout = false;
        break;
    }
}

// Process status message
$message = $_SESSION['cart_message'] ?? '';
$messageType = $_SESSION['cart_message_type'] ?? 'success';

unset($_SESSION['cart_message'], $_SESSION['cart_message_type']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shopping Cart | Xamani Mobile Hub</title>

    <link rel="stylesheet" href="assets/css/cart.css">

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

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="shop.php?category=Smartphones">Smartphones</a>
            <a href="shop.php?category=Accessories">Accessories</a>
        </nav>

        <a href="shop.php" class="nav-shop-btn">Continue Shopping</a>

    </header>


    <main class="cart-container">

        <!-- BREADCRUMB -->

        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <strong>Shopping Cart</strong>
        </div>


        <!-- PAGE HEADING -->

        <div class="page-heading">
            <div>
                <span class="eyebrow">YOUR SELECTION</span>
                <h1>Shopping Cart</h1>
                <p>Review your products before proceeding to checkout.</p>
            </div>

            <?php if ($totalQuantity > 0): ?>
            <span class="cart-items-count">
                <?= $totalQuantity ?> item(s)
            </span>
            <?php endif; ?>
        </div>


        <!-- ALERT MESSAGES -->

        <?php if ($message !== ''): ?>
        <div class="alert <?= $messageType === 'error' ? 'alert-error' : 'alert-success' ?>">
            <?= e($message) ?>
        </div>
        <?php endif; ?>

        <?php if ($cartWarnings): ?>
        <div class="alert alert-warning">
            <strong>Please review your cart:</strong>
            <ul>
                <?php foreach ($cartWarnings as $warning): ?>
                <li><?= e($warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>


        <?php if (!$cartProducts): ?>

        <!-- EMPTY CART -->

        <section class="empty-cart">

            <div class="empty-cart-icon">
                <svg viewBox="0 0 24 24" width="65" height="65" fill="none" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>
            </div>

            <h2>Your cart is empty</h2>

            <p>
                Looks like you haven't added any products yet.
                Explore our collection and find something you love.
            </p>

            <a href="shop.php" class="primary-btn">
                Explore Products →
            </a>

        </section>

        <?php else: ?>

        <!-- CART LAYOUT -->

        <div class="cart-layout">

            <section class="cart-main">

                <div class="cart-card">

                    <div class="cart-card-heading">
                        <h2>Cart Items</h2>
                        <span><?= count($cartProducts) ?> product(s)</span>
                    </div>


                    <!-- DESKTOP TABLE -->

                    <div class="cart-table-wrapper">

                        <table class="cart-table">

                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($cartProducts as $product): ?>

                                <tr>

                                    <td>
                                        <div class="cart-product">

                                            <a href="product.php?id=<?= (int)$product['id'] ?>" class="product-image">

                                                <?php if (!empty($product['image_path'])): ?>
                                                <img src="<?= e($product['image_path']) ?>"
                                                    alt="<?= e($product['name']) ?>">
                                                <?php else: ?>
                                                <span>📱</span>
                                                <?php endif; ?>

                                            </a>

                                            <div class="product-info">

                                                <span class="product-brand">
                                                    <?= e($product['brand'] ?: 'Xamani Mobile Hub') ?>
                                                </span>

                                                <a href="product.php?id=<?= (int)$product['id'] ?>"
                                                    class="product-name">
                                                    <?= e($product['name']) ?>
                                                </a>

                                                <?php if ($product['available']): ?>
                                                <span class="stock-label">Available</span>
                                                <?php else: ?>
                                                <span class="stock-label unavailable">
                                                    Unavailable
                                                </span>
                                                <?php endif; ?>

                                            </div>

                                        </div>
                                    </td>

                                    <td class="price-cell">
                                        <?= money($product['price']) ?>
                                    </td>

                                    <td>

                                        <form action="actions/update-cart.php" method="POST" class="quantity-form">

                                            <input type="hidden" name="csrf_token"
                                                value="<?= e($_SESSION['cart_csrf']) ?>">

                                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                                            <div class="quantity-control">

                                                <button type="button" class="qty-btn" onclick="changeQuantity(this, -1)"
                                                    aria-label="Decrease quantity">−</button>

                                                <input type="number" name="quantity"
                                                    value="<?= (int)$product['quantity'] ?>" min="1"
                                                    max="<?= max(1, (int)$product['stock']) ?>"
                                                    aria-label="Product quantity" required>

                                                <button type="button" class="qty-btn" onclick="changeQuantity(this, 1)"
                                                    aria-label="Increase quantity">+</button>

                                            </div>

                                            <button type="submit" class="update-link">
                                                Update
                                            </button>

                                        </form>

                                    </td>

                                    <td class="line-total">
                                        <?= money($product['line_total']) ?>
                                    </td>

                                    <td>

                                        <form action="actions/remove-from-cart.php" method="POST">

                                            <input type="hidden" name="csrf_token"
                                                value="<?= e($_SESSION['cart_csrf']) ?>">

                                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                                            <button type="submit" class="remove-btn"
                                                aria-label="Remove <?= e($product['name']) ?> from cart"
                                                title="Remove product">
                                                ×
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>


                    <!-- MOBILE PRODUCT CARDS -->

                    <div class="mobile-cart-items">

                        <?php foreach ($cartProducts as $product): ?>

                        <article class="mobile-cart-item">

                            <div class="mobile-item-top">

                                <a href="product.php?id=<?= (int)$product['id'] ?>" class="product-image">

                                    <?php if (!empty($product['image_path'])): ?>
                                    <img src="<?= e($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                                    <?php else: ?>
                                    <span>📱</span>
                                    <?php endif; ?>

                                </a>

                                <div class="mobile-product-info">

                                    <span class="product-brand">
                                        <?= e($product['brand'] ?: 'Xamani Mobile Hub') ?>
                                    </span>

                                    <a href="product.php?id=<?= (int)$product['id'] ?>" class="product-name">
                                        <?= e($product['name']) ?>
                                    </a>

                                    <strong class="mobile-price">
                                        <?= money($product['price']) ?>
                                    </strong>

                                    <span class="stock-label <?= $product['available'] ? '' : 'unavailable' ?>">
                                        <?= $product['available'] ? 'Available' : 'Unavailable' ?>
                                    </span>

                                </div>

                                <form action="actions/remove-from-cart.php" method="POST">

                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['cart_csrf']) ?>">

                                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                                    <button type="submit" class="remove-btn" aria-label="Remove product">×</button>

                                </form>

                            </div>

                            <div class="mobile-item-bottom">

                                <form action="actions/update-cart.php" method="POST" class="quantity-form">

                                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['cart_csrf']) ?>">

                                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                                    <div class="quantity-control">

                                        <button type="button" class="qty-btn"
                                            onclick="changeQuantity(this, -1)">−</button>

                                        <input type="number" name="quantity" value="<?= (int)$product['quantity'] ?>"
                                            min="1" max="<?= max(1, (int)$product['stock']) ?>"
                                            aria-label="Product quantity" required>

                                        <button type="button" class="qty-btn"
                                            onclick="changeQuantity(this, 1)">+</button>

                                    </div>

                                    <button type="submit" class="update-link">
                                        Update
                                    </button>

                                </form>

                                <strong class="line-total">
                                    <?= money($product['line_total']) ?>
                                </strong>

                            </div>

                        </article>

                        <?php endforeach; ?>

                    </div>


                    <div class="cart-bottom-actions">
                        <a href="shop.php" class="continue-link">
                            ← Continue Shopping
                        </a>
                    </div>

                </div>

            </section>


            <!-- ORDER SUMMARY -->

            <aside class="cart-sidebar">

                <div class="summary-card">

                    <h2>Order Summary</h2>

                    <p class="summary-subtitle">
                        Your current cart total.
                    </p>

                    <div class="summary-row">
                        <span>Products</span>
                        <strong><?= count($cartProducts) ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Total Quantity</span>
                        <strong><?= $totalQuantity ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong><?= money($subtotal) ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Delivery</span>
                        <span class="delivery-note">To be confirmed</span>
                    </div>

                    <div class="summary-total">
                        <span>Estimated Total</span>
                        <strong><?= money($subtotal) ?></strong>
                    </div>

                    <p class="summary-disclaimer">
                        Delivery charges, if applicable, will be confirmed
                        separately before dispatch.
                    </p>

                    <?php if ($canCheckout): ?>

                    <a href="checkout.php" class="checkout-btn">
                        Proceed to Checkout →
                    </a>

                    <?php else: ?>

                    <button type="button" class="checkout-btn disabled" disabled>
                        Review Cart Availability
                    </button>

                    <?php endif; ?>

                    <div class="secure-note">
                        <span>🔒</span>
                        <span>Order securely through our checkout process.</span>
                    </div>

                </div>


                <div class="support-card">
                    <span class="support-icon">💬</span>
                    <div>
                        <h3>Need help?</h3>
                        <p>
                            Contact Xamani Mobile Hub if you need assistance
                            with your shopping cart.
                        </p>
                    </div>
                </div>

            </aside>

        </div>

        <?php endif; ?>

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
                    Smartphones, accessories, and smarter everyday technology.
                </p>

            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <a href="index.php">Home</a>
                <a href="shop.php">Shop</a>
                <a href="checkout.php">Checkout</a>
            </div>

            <div class="footer-links">
                <h4>Categories</h4>
                <a href="shop.php?category=Smartphones">Smartphones</a>
                <a href="shop.php?category=Accessories">Accessories</a>
                <a href="shop.php?category=Refurbished%20Phones">Refurbished Phones</a>
            </div>

        </div>

        <div class="footer-bottom">
            © <?= date('Y') ?> Xamani Mobile Hub. All rights reserved.
        </div>

    </footer>


    <script>
    function changeQuantity(button, change) {
        const form = button.closest('form');
        const input = form.querySelector('input[name="quantity"]');

        const min = Number(input.min) || 1;
        const max = Number(input.max) || 999;
        const current = Number(input.value) || min;

        input.value = Math.max(min, Math.min(max, current + change));
    }
    </script>

</body>

</html>