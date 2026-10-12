<?php
require_once __DIR__ . '/config/database.php';

/*
|--------------------------------------------------------------------------
| Get Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$brand = trim($_GET['brand'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$allowedSorts = [
    'newest' => 'p.created_at DESC, p.id DESC',
    'oldest' => 'p.created_at ASC, p.id ASC',
    'low_price' => 'p.price ASC, p.id DESC',
    'high_price' => 'p.price DESC, p.id DESC',
    'name' => 'p.name ASC, p.id ASC'
];

$orderBy = $allowedSorts[$sort] ?? $allowedSorts['newest'];

/*
|--------------------------------------------------------------------------
| Load Categories & Brands
|--------------------------------------------------------------------------
*/

$categories = $pdo->query("
    SELECT category_name
    FROM categories
    ORDER BY category_name ASC
")->fetchAll(PDO::FETCH_COLUMN);

$brands = $pdo->query("
    SELECT DISTINCT brand
    FROM products
    WHERE status = 'active'
      AND brand IS NOT NULL
      AND brand != ''
    ORDER BY brand ASC
")->fetchAll(PDO::FETCH_COLUMN);

/*
|--------------------------------------------------------------------------
| Build Product Query
|--------------------------------------------------------------------------
*/

$where = ["p.status = 'active'"];
$params = [];

if ($search !== '') {
    $where[] = "(p.name LIKE :search_name
                 OR p.brand LIKE :search_brand
                 OR p.description LIKE :search_description)";

    $keyword = "%{$search}%";

    $params['search_name'] = $keyword;
    $params['search_brand'] = $keyword;
    $params['search_description'] = $keyword;
}

if ($category !== '') {
    $where[] = "c.category_name = :category";
    $params['category'] = $category;
}

if ($brand !== '') {
    $where[] = "p.brand = :brand";
    $params['brand'] = $brand;
}

$whereSQL = implode(' AND ', $where);

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$perPage = 12;
$page = max(1, (int) ($_GET['page'] ?? 1));

$countSQL = "
    SELECT COUNT(*)
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE $whereSQL
";

$countStmt = $pdo->prepare($countSQL);
$countStmt->execute($params);

$totalProducts = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / $perPage));

$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

/*
|--------------------------------------------------------------------------
| Fetch Products
|--------------------------------------------------------------------------
*/

$productSQL = "
    SELECT
        p.id,
        p.name,
        p.brand,
        p.price,
        p.description,
        p.storage,
        p.ram,
        p.stock,
        p.image_path,
        c.category_name

    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id

    WHERE $whereSQL

    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($productSQL);

foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function shopUrl($overrides = []) {
    $query = array_merge($_GET, $overrides);

    foreach ($query as $key => $value) {
        if ($value === '' || $value === null) {
            unset($query[$key]);
        }
    }

    return 'shop.php' . ($query ? '?' . http_build_query($query) : '');
}

$startResult = $totalProducts > 0 ? $offset + 1 : 0;
$endResult = min($offset + $perPage, $totalProducts);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shop Phones & Accessories | Xamani Mobile Hub</title>

    <link rel="stylesheet" href="assets/css/shop.css">

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
                🛒 <span>Cart</span>
            </a>

            <a href="shop.php" class="nav-shop-btn">Shop Now</a>
        </div>

        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false">
            ☰
        </button>

    </header>


    <!-- SHOP BANNER -->

    <section class="shop-hero">

        <div class="shop-hero-content">

            <div class="breadcrumbs">
                <a href="index.php">Home</a>
                <span>›</span>
                <span>Shop</span>
            </div>

            <span class="hero-label">EXPLORE OUR COLLECTION</span>

            <h1>Find your next <span>upgrade.</span></h1>

            <p>
                Browse smartphones and accessories that match
                your style, needs, and budget.
            </p>

            <div class="hero-pills">
                <span>✓ Quality Devices</span>
                <span>✓ Great Selection</span>
                <span>✓ Easy Ordering</span>
            </div>

        </div>

        <div class="shop-hero-decoration">
            <div class="circle circle-one"></div>
            <div class="circle circle-two"></div>

            <div class="hero-phone">
                <div class="phone-camera camera-one"></div>
                <div class="phone-camera camera-two"></div>
                <div class="phone-camera camera-three"></div>
            </div>
        </div>

    </section>


    <!-- MAIN SHOP -->

    <main class="shop-container">

        <div class="shop-heading">

            <div>
                <span class="section-label">OUR COLLECTION</span>
                <h2>Shop All Products</h2>
                <p>Discover something worth upgrading to.</p>
            </div>

            <div class="product-total">
                <?= number_format($totalProducts) ?> Products
            </div>

        </div>


        <!-- SEARCH -->

        <form action="shop.php" method="GET" class="search-form">

            <div class="search-field">
                <span class="search-icon">⌕</span>

                <input type="search" name="search" placeholder="Search phones, brands, accessories..."
                    value="<?= e($search) ?>">
            </div>

            <?php if ($category !== ''): ?>
            <input type="hidden" name="category" value="<?= e($category) ?>">
            <?php endif; ?>

            <?php if ($brand !== ''): ?>
            <input type="hidden" name="brand" value="<?= e($brand) ?>">
            <?php endif; ?>

            <?php if ($sort !== 'newest'): ?>
            <input type="hidden" name="sort" value="<?= e($sort) ?>">
            <?php endif; ?>

            <button type="submit" class="search-btn">
                Search Products →
            </button>

        </form>


        <!-- CATEGORY TABS -->

        <div class="category-tabs">

            <a href="<?= e(shopUrl(['category' => '', 'page' => 1])) ?>"
                class="category-tab <?= $category === '' ? 'selected' : '' ?>">
                All Products
            </a>

            <?php foreach ($categories as $item): ?>

            <a href="<?= e(shopUrl(['category' => $item, 'page' => 1])) ?>"
                class="category-tab <?= $category === $item ? 'selected' : '' ?>">

                <?= e($item) ?>

            </a>

            <?php endforeach; ?>

        </div>


        <!-- SHOP LAYOUT -->

        <div class="shop-layout">

            <!-- FILTER SIDEBAR -->

            <aside class="filter-sidebar">

                <div class="sidebar-heading">
                    <h3>Filters</h3>
                    <a href="shop.php">Clear All</a>
                </div>

                <div class="filter-group">

                    <h4>Categories</h4>

                    <a href="<?= e(shopUrl(['category' => '', 'page' => 1])) ?>"
                        class="filter-link <?= $category === '' ? 'filter-active' : '' ?>">
                        All Products
                    </a>

                    <?php foreach ($categories as $item): ?>

                    <a href="<?= e(shopUrl(['category' => $item, 'page' => 1])) ?>"
                        class="filter-link <?= $category === $item ? 'filter-active' : '' ?>">

                        <?= e($item) ?>

                    </a>

                    <?php endforeach; ?>

                </div>


                <div class="filter-group">

                    <h4>Brands</h4>

                    <a href="<?= e(shopUrl(['brand' => '', 'page' => 1])) ?>"
                        class="filter-link <?= $brand === '' ? 'filter-active' : '' ?>">
                        All Brands
                    </a>

                    <?php foreach ($brands as $item): ?>

                    <a href="<?= e(shopUrl(['brand' => $item, 'page' => 1])) ?>"
                        class="filter-link <?= $brand === $item ? 'filter-active' : '' ?>">

                        <?= e($item) ?>

                    </a>

                    <?php endforeach; ?>

                </div>


                <div class="sidebar-promo">

                    <div class="promo-icon">📱</div>

                    <h4>Looking for a new phone?</h4>

                    <p>
                        Explore our available devices
                        and find your perfect match.
                    </p>

                    <a href="shop.php?category=Smartphones">
                        Explore Phones →
                    </a>

                </div>

            </aside>


            <!-- PRODUCT CONTENT -->

            <section class="products-content">

                <div class="products-toolbar">

                    <p>
                        Showing
                        <strong><?= $startResult ?>–<?= $endResult ?></strong>
                        of
                        <strong><?= $totalProducts ?></strong>
                        products
                    </p>

                    <form method="GET" action="shop.php" class="sort-form">

                        <input type="hidden" name="search" value="<?= e($search) ?>">
                        <input type="hidden" name="category" value="<?= e($category) ?>">
                        <input type="hidden" name="brand" value="<?= e($brand) ?>">

                        <label for="sort">Sort by:</label>

                        <select name="sort" id="sort" onchange="this.form.submit()">

                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>
                                Newest First
                            </option>

                            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>
                                Oldest First
                            </option>

                            <option value="low_price" <?= $sort === 'low_price' ? 'selected' : '' ?>>
                                Price: Low to High
                            </option>

                            <option value="high_price" <?= $sort === 'high_price' ? 'selected' : '' ?>>
                                Price: High to Low
                            </option>

                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>
                                Name: A to Z
                            </option>

                        </select>

                        <noscript>
                            <button type="submit">Apply</button>
                        </noscript>

                    </form>

                </div>


                <!-- PRODUCT GRID -->

                <?php if (count($products) > 0): ?>

                <div class="product-grid">

                    <?php foreach ($products as $product): ?>

                    <?php
                        $inStock = (int) $product['stock'] > 0;
                        $image = $product['image_path'] ?: '';
                        ?>

                    <article class="product-card">

                        <a href="product.php?id=<?= (int) $product['id'] ?>" class="product-image">

                            <?php if (!$inStock): ?>
                            <span class="product-badge out-of-stock">
                                OUT OF STOCK
                            </span>
                            <?php else: ?>
                            <span class="product-badge in-stock">
                                IN STOCK
                            </span>
                            <?php endif; ?>

                            <?php if ($image !== ''): ?>

                            <img src="<?= e($image) ?>" alt="<?= e($product['name']) ?>" loading="lazy">

                            <?php else: ?>

                            <div class="no-image">
                                <span>📱</span>
                                <p>No Image Available</p>
                            </div>

                            <?php endif; ?>

                        </a>


                        <div class="product-info">

                            <span class="product-brand">
                                <?= e($product['brand'] ?: 'XAMANI MOBILE HUB') ?>
                            </span>

                            <a href="product.php?id=<?= (int) $product['id'] ?>">
                                <h3><?= e($product['name']) ?></h3>
                            </a>

                            <div class="product-specs">

                                <?php if (!empty($product['storage'])): ?>
                                <span><?= e($product['storage']) ?></span>
                                <?php endif; ?>

                                <?php if (!empty($product['ram'])): ?>
                                <span><?= e($product['ram']) ?> RAM</span>
                                <?php endif; ?>

                            </div>

                            <div class="product-rating">
                                <span class="stock-dot <?= $inStock ? '' : 'unavailable' ?>"></span>

                                <?= $inStock ? 'Available' : 'Currently unavailable' ?>
                            </div>

                            <div class="product-bottom">

                                <div class="price-area">
                                    <small>Price</small>

                                    <strong>
                                        ₦<?= number_format((float) $product['price'], 2) ?>
                                    </strong>
                                </div>

                                <a href="product.php?id=<?= (int) $product['id'] ?>" class="view-product-btn"
                                    aria-label="View <?= e($product['name']) ?>">

                                    →

                                </a>

                            </div>

                        </div>

                    </article>

                    <?php endforeach; ?>

                </div>


                <!-- PAGINATION -->

                <?php if ($totalPages > 1): ?>

                <div class="pagination">

                    <?php if ($page > 1): ?>

                    <a href="<?= e(shopUrl(['page' => $page - 1])) ?>" class="page-btn">
                        ← Previous
                    </a>

                    <?php endif; ?>


                    <?php
                        $firstPage = max(1, $page - 2);
                        $lastPage = min($totalPages, $page + 2);
                        ?>

                    <?php for ($i = $firstPage; $i <= $lastPage; $i++): ?>

                    <a href="<?= e(shopUrl(['page' => $i])) ?>"
                        class="page-number <?= $page === $i ? 'current' : '' ?>">

                        <?= $i ?>

                    </a>

                    <?php endfor; ?>


                    <?php if ($page < $totalPages): ?>

                    <a href="<?= e(shopUrl(['page' => $page + 1])) ?>" class="page-btn">
                        Next →
                    </a>

                    <?php endif; ?>

                </div>

                <?php endif; ?>


                <?php else: ?>

                <!-- EMPTY STATE -->

                <div class="empty-products">

                    <div class="empty-icon">🔎</div>

                    <h3>No products found</h3>

                    <p>
                        We couldn't find any products matching
                        your search or selected filters.
                    </p>

                    <a href="shop.php" class="reset-btn">
                        View All Products →
                    </a>

                </div>

                <?php endif; ?>

            </section>

        </div>

    </main>


    <!-- SHOP CTA -->

    <section class="shop-cta">

        <div>
            <span>YOUR NEXT UPGRADE</span>

            <h2>Find a phone that fits your lifestyle.</h2>

            <p>
                Explore our collection of smartphones
                and accessories today.
            </p>
        </div>

        <a href="shop.php" class="cta-btn">
            Explore Products →
        </a>

    </section>


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
                <a href="shop.php?category=Refurbished%20Phones">Refurbished Phones</a>

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