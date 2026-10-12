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

function formatPrice($price)
{
    return '₦' . number_format((float) $price, 2);
}

/*
|--------------------------------------------------------------------------
| Get Product ID
|--------------------------------------------------------------------------
*/

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$productId || $productId < 1) {
    http_response_code(404);
    exit('Invalid product ID. <a href="shop.php">Back to Shop</a>');
}

/*
|--------------------------------------------------------------------------
| Fetch Product
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        p.*,
        c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = :id
      AND p.status = 'active'
    LIMIT 1
");

$stmt->execute(['id' => $productId]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    exit('Product not found. <a href="shop.php">Back to Shop</a>');
}

/*
|--------------------------------------------------------------------------
| Product Information
|--------------------------------------------------------------------------
*/

$inStock = (int) $product['stock'] > 0;
$stock = (int) $product['stock'];
$image = $product['image_path'] ?? '';
$categoryName = $product['category_name'] ?? 'Products';

/*
|--------------------------------------------------------------------------
| Fetch Related Products
|--------------------------------------------------------------------------
*/

$relatedStmt = $pdo->prepare("
    SELECT
        id,
        name,
        brand,
        price,
        image_path,
        stock
    FROM products
    WHERE status = 'active'
      AND id != :product_id
      AND category_id <=> :category_id
    ORDER BY created_at DESC
    LIMIT 4
");

$relatedStmt->execute([
    'product_id' => $productId,
    'category_id' => $product['category_id']
]);

$relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Cart Count
|--------------------------------------------------------------------------
*/

$cartCount = 0;

foreach (($_SESSION['cart'] ?? []) as $quantity) {
    if (is_array($quantity)) {
        $cartCount += (int) ($quantity['quantity'] ?? 0);
    } else {
        $cartCount += (int) $quantity;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($product['name']) ?> | Xamani Mobile Hub</title>

    <link rel="stylesheet" href="assets/css/product.css">

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
            <a href="shop.php" class="active">Shop</a>
            <a href="shop.php?category=Smartphones">Smartphones</a>
            <a href="shop.php?category=Accessories">Accessories</a>
        </nav>

        <div class="nav-actions">
            <a href="cart.php" class="cart-link">
                🛒 Cart
                <span class="cart-count"><?= $cartCount ?></span>
            </a>
            <a href="shop.php" class="nav-shop-btn">Shop Now</a>
        </div>

        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false">☰</button>

    </header>


    <!-- BREADCRUMBS -->

    <div class="breadcrumbs">

        <a href="index.php">Home</a>
        <span>›</span>

        <a href="shop.php">Shop</a>
        <span>›</span>

        <a href="shop.php?category=<?= urlencode($categoryName) ?>">
            <?= e($categoryName) ?>
        </a>

        <span>›</span>

        <span class="current"><?= e($product['name']) ?></span>

    </div>


    <!-- PRODUCT DETAILS -->

    <main class="product-container">

        <div class="product-layout">

            <!-- PRODUCT IMAGE -->

            <div class="product-gallery">

                <div class="main-image">

                    <?php if (!$inStock): ?>
                    <span class="image-badge unavailable">
                        OUT OF STOCK
                    </span>
                    <?php else: ?>
                    <span class="image-badge available">
                        IN STOCK
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($image)): ?>

                    <img src="<?= e($image) ?>" alt="<?= e($product['name']) ?>" id="mainProductImage">

                    <?php else: ?>

                    <div class="no-image">
                        <span>📱</span>
                        <p>No Product Image Available</p>
                    </div>

                    <?php endif; ?>

                </div>

                <div class="image-caption">
                    <span>✓</span>
                    Product images are for reference
                </div>

            </div>


            <!-- PRODUCT INFORMATION -->

            <div class="product-details">

                <div class="product-category">
                    <?= e($categoryName) ?>
                </div>

                <div class="brand-label">
                    <?= e($product['brand'] ?: 'XAMANI MOBILE HUB') ?>
                </div>

                <h1><?= e($product['name']) ?></h1>

                <div class="product-availability">

                    <span class="availability-dot <?= !$inStock ? 'red' : '' ?>"></span>

                    <?php if ($inStock): ?>
                    <span>In Stock</span>
                    <span class="separator">•</span>
                    <span><?= $stock ?> unit(s) available</span>
                    <?php else: ?>
                    <span class="out-text">Currently Out of Stock</span>
                    <?php endif; ?>

                </div>


                <!-- PRICE -->

                <div class="price-box">

                    <span class="price-label">Product Price</span>

                    <div class="price">
                        <?= formatPrice($product['price']) ?>
                    </div>

                    <p>Price shown in Nigerian Naira (NGN)</p>

                </div>


                <!-- PRODUCT SPECS -->

                <div class="specifications">

                    <h3>Product Specifications</h3>

                    <div class="spec-grid">

                        <?php if (!empty($product['brand'])): ?>
                        <div class="spec-item">
                            <span class="spec-icon">🏷️</span>
                            <div>
                                <small>Brand</small>
                                <strong><?= e($product['brand']) ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($product['storage'])): ?>
                        <div class="spec-item">
                            <span class="spec-icon">💾</span>
                            <div>
                                <small>Storage</small>
                                <strong><?= e($product['storage']) ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($product['ram'])): ?>
                        <div class="spec-item">
                            <span class="spec-icon">⚡</span>
                            <div>
                                <small>RAM</small>
                                <strong><?= e($product['ram']) ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="spec-item">
                            <span class="spec-icon">📦</span>
                            <div>
                                <small>Availability</small>
                                <strong><?= $inStock ? 'Available' : 'Unavailable' ?></strong>
                            </div>
                        </div>

                    </div>

                </div>


                <!-- ADD TO CART -->

                <form action="actions/add-to-cart.php" method="POST" class="purchase-form">

                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

                    <?php if ($inStock): ?>

                    <div class="quantity-section">

                        <label for="quantity">Quantity</label>

                        <div class="quantity-control">

                            <button type="button" id="decreaseQty" aria-label="Decrease quantity">
                                −
                            </button>

                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $stock ?>"
                                required>

                            <button type="button" id="increaseQty" aria-label="Increase quantity">
                                +
                            </button>

                        </div>

                    </div>


                    <div class="purchase-buttons">

                        <button type="submit" class="add-cart-btn">
                            🛒 Add to Cart
                        </button>

                        <a href="shop.php" class="continue-btn">
                            Continue Shopping →
                        </a>

                    </div>

                    <?php else: ?>

                    <button type="button" class="disabled-btn" disabled>
                        Currently Unavailable
                    </button>

                    <a href="shop.php" class="continue-link">
                        ← Browse Other Products
                    </a>

                    <?php endif; ?>

                </form>


                <!-- SHOPPING BENEFITS -->

                <div class="shopping-benefits">

                    <div>
                        <span>✓</span>
                        <p>Easy Ordering</p>
                    </div>

                    <div>
                        <span>✓</span>
                        <p>Quality Focused</p>
                    </div>

                    <div>
                        <span>✓</span>
                        <p>Customer Support</p>
                    </div>

                </div>

            </div>

        </div>


        <!-- DESCRIPTION -->

        <section class="description-section">

            <div class="section-heading">
                <span>PRODUCT INFORMATION</span>
                <h2>Product Description</h2>
            </div>

            <div class="description-card">

                <?php if (!empty($product['description'])): ?>

                <p><?= nl2br(e($product['description'])) ?></p>

                <?php else: ?>

                <p>
                    Explore the <?= e($product['name']) ?> at Xamani Mobile Hub.
                    Contact us for additional details about this product.
                </p>

                <?php endif; ?>

            </div>

        </section>


        <!-- RELATED PRODUCTS -->

        <?php if (!empty($relatedProducts)): ?>

        <section class="related-section">

            <div class="related-heading">

                <div>
                    <span>YOU MAY ALSO LIKE</span>
                    <h2>Related Products</h2>
                    <p>Discover more products from our collection.</p>
                </div>

                <a href="shop.php">View All Products →</a>

            </div>


            <div class="related-grid">

                <?php foreach ($relatedProducts as $related): ?>

                <article class="related-card">

                    <a href="product.php?id=<?= (int) $related['id'] ?>" class="related-image">

                        <?php if (!empty($related['image_path'])): ?>

                        <img src="<?= e($related['image_path']) ?>" alt="<?= e($related['name']) ?>" loading="lazy">

                        <?php else: ?>

                        <div class="related-placeholder">📱</div>

                        <?php endif; ?>

                    </a>

                    <div class="related-info">

                        <span>
                            <?= e($related['brand'] ?: 'XAMANI MOBILE HUB') ?>
                        </span>

                        <h3>
                            <a href="product.php?id=<?= (int) $related['id'] ?>">
                                <?= e($related['name']) ?>
                            </a>
                        </h3>

                        <div class="related-bottom">

                            <strong>
                                <?= formatPrice($related['price']) ?>
                            </strong>

                            <a href="product.php?id=<?= (int) $related['id'] ?>">
                                →
                            </a>

                        </div>

                    </div>

                </article>

                <?php endforeach; ?>

            </div>

        </section>

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


    <!-- JAVASCRIPT -->

    <script>
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');

    menuToggle.addEventListener('click', function() {
        const isOpen = navLinks.classList.toggle('show');

        menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        menuToggle.textContent = isOpen ? '✕' : '☰';
    });

    const quantityInput = document.getElementById('quantity');
    const increaseBtn = document.getElementById('increaseQty');
    const decreaseBtn = document.getElementById('decreaseQty');

    if (quantityInput && increaseBtn && decreaseBtn) {

        const maxStock = Number(quantityInput.max);

        increaseBtn.addEventListener('click', function() {
            const current = Number(quantityInput.value) || 1;

            if (current < maxStock) {
                quantityInput.value = current + 1;
            }
        });

        decreaseBtn.addEventListener('click', function() {
            const current = Number(quantityInput.value) || 1;

            if (current > 1) {
                quantityInput.value = current - 1;
            }
        });

        quantityInput.addEventListener('change', function() {
            let value = Number(quantityInput.value);

            if (!Number.isInteger(value) || value < 1) {
                value = 1;
            }

            if (value > maxStock) {
                value = maxStock;
            }

            quantityInput.value = value;
        });
    }
    </script>

</body>

</html>