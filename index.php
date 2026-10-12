<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Xamani Mobile Hub | Find Your Next Phone</title>

    <meta name="description" content="Shop smartphones, accessories and refurbished phones at Xamani Mobile Hub.">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

    <!-- Navigation -->
    <header class="navbar">
        <a href="index.php" class="logo">
            <span class="logo-icon">X</span>
            <span>XAMANI<span class="logo-light"> MOBILE HUB</span></span>
        </a>

        <nav class="nav-links">
            <a href="index.php" class="active">Home</a>
            <a href="shop.php">Shop</a>
            <a href="shop.php?category=Smartphones">Smartphones</a>
            <a href="shop.php?category=Accessories">Accessories</a>
        </nav>

        <div class="nav-actions">
            <a href="cart.php" class="cart-link">
                <span>🛒</span> Cart
            </a>
            <a href="shop.php" class="nav-button">Shop Now</a>
        </div>

        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false">
            ☰
        </button>
    </header>

    <main>
        <!-- Hero -->
        <section class="hero">
            <div class="hero-content">
                <span class="eyebrow">
                    <span class="status-dot"></span>
                    YOUR NEXT UPGRADE STARTS HERE
                </span>

                <h1>
                    The right phone.<br>
                    <span>The right price.</span>
                </h1>

                <p>
                    Discover smartphones that match your lifestyle.
                    Explore trusted brands, essential accessories,
                    and great deals — all in one place.
                </p>

                <div class="hero-buttons">
                    <a href="shop.php" class="btn-primary">
                        Explore Phones <span>→</span>
                    </a>
                    <a href="#categories" class="btn-secondary">
                        Browse Categories
                    </a>
                </div>

                <div class="hero-benefits">
                    <div>
                        <span class="benefit-icon">✓</span>
                        Quality Products
                    </div>
                    <div>
                        <span class="benefit-icon">✓</span>
                        Easy Ordering
                    </div>
                    <div>
                        <span class="benefit-icon">✓</span>
                        Customer Support
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="visual-glow"></div>
                <div class="floating-tag tag-top">
                    <span class="tag-icon">✦</span>
                    <div>
                        <strong>Upgrade your tech</strong>
                        <small>Find your perfect match</small>
                    </div>
                </div>

                <div class="phone-stage">
                    <div class="phone phone-back">
                        <div class="camera camera-one"></div>
                        <div class="camera camera-two"></div>
                        <div class="camera camera-three"></div>
                        <div class="camera-flash"></div>
                        <div class="phone-logo">X</div>
                    </div>

                    <div class="phone phone-front">
                        <div class="phone-island"></div>
                        <div class="screen-wallpaper">
                            <div class="wallpaper-orb orb-one"></div>
                            <div class="wallpaper-orb orb-two"></div>
                            <div class="wallpaper-orb orb-three"></div>
                            <div class="screen-time">9:41</div>
                            <div class="screen-caption">Explore what's next.</div>
                        </div>
                    </div>
                </div>

                <div class="floating-tag tag-bottom">
                    <span class="tag-star">★</span>
                    <div>
                        <strong>Smart choices</strong>
                        <small>Shop with confidence</small>
                    </div>
                </div>

                <span class="decor decor-one">✦</span>
                <span class="decor decor-two">+</span>
            </div>
        </section>

        <!-- Benefits strip -->
        <section class="trust-strip">
            <div class="trust-item">
                <span class="trust-icon">📱</span>
                <div>
                    <strong>Latest Devices</strong>
                    <small>Explore popular models</small>
                </div>
            </div>

            <div class="trust-item">
                <span class="trust-icon">💳</span>
                <div>
                    <strong>Easy Ordering</strong>
                    <small>A simple shopping experience</small>
                </div>
            </div>

            <div class="trust-item">
                <span class="trust-icon">🛡️</span>
                <div>
                    <strong>Quality Focused</strong>
                    <small>Shop with confidence</small>
                </div>
            </div>

            <div class="trust-item">
                <span class="trust-icon">💬</span>
                <div>
                    <strong>Need Help?</strong>
                    <small>We're here to assist</small>
                </div>
            </div>
        </section>

        <!-- Categories -->
        <section class="section categories-section" id="categories">
            <div class="section-heading">
                <div>
                    <span class="section-label">FIND YOUR FIT</span>
                    <h2>Shop by category</h2>
                    <p>Everything you need to stay connected.</p>
                </div>

                <a href="shop.php" class="text-link">
                    View all products →
                </a>
            </div>

            <div class="category-grid">
                <a href="shop.php?category=Smartphones" class="category-card category-phone">
                    <div class="category-copy">
                        <span class="category-number">01 / DEVICES</span>
                        <h3>Smartphones</h3>
                        <p>Discover your next upgrade.</p>
                        <span class="category-arrow">↗</span>
                    </div>
                    <div class="category-art smartphone-art">
                        <div class="mini-phone mini-phone-back"></div>
                        <div class="mini-phone mini-phone-front">
                            <div class="mini-screen"></div>
                        </div>
                    </div>
                </a>

                <a href="shop.php?category=Accessories" class="category-card category-accessories">
                    <div class="category-copy">
                        <span class="category-number">02 / ESSENTIALS</span>
                        <h3>Accessories</h3>
                        <p>Power up your everyday.</p>
                        <span class="category-arrow">↗</span>
                    </div>
                    <div class="category-art accessory-art">
                        <div class="earbud earbud-one"></div>
                        <div class="earbud earbud-two"></div>
                        <div class="earbud-case"></div>
                    </div>
                </a>

                <a href="shop.php?category=Refurbished%20Phones" class="category-card category-refurbished">
                    <div class="category-copy">
                        <span class="category-number">03 / SMART VALUE</span>
                        <h3>Refurbished</h3>
                        <p>Explore more for your money.</p>
                        <span class="category-arrow">↗</span>
                    </div>
                    <div class="category-art refurbished-art">
                        <div class="refurb-phone">
                            <div class="refurb-screen"></div>
                        </div>
                        <span class="refurb-spark">✦</span>
                    </div>
                </a>
            </div>
        </section>

        <!-- Featured products -->
        <section class="section products-section" id="featured">
            <div class="section-heading">
                <div>
                    <span class="section-label">HANDPICKED FOR YOU</span>
                    <h2>Featured phones</h2>
                    <p>Take a look at devices worth discovering.</p>
                </div>

                <a href="shop.php" class="text-link">
                    Shop all phones →
                </a>
            </div>

            <div class="product-grid">

                <!-- Demo product 1 -->
                <article class="product-card">
                    <a href="shop.php" class="product-image product-image-one">
                        <span class="product-badge">POPULAR</span>
                        <div class="demo-phone demo-phone-dark">
                            <div class="demo-camera camera-a"></div>
                            <div class="demo-camera camera-b"></div>
                            <div class="demo-camera camera-c"></div>
                            <div class="demo-flash"></div>
                        </div>
                    </a>
                    <div class="product-info">
                        <span class="product-brand">APPLE</span>
                        <h3>iPhone Series</h3>
                        <p class="product-description">Explore available iPhone models</p>
                        <div class="product-bottom">
                            <span class="price">Explore prices</span>
                            <a href="shop.php" class="add-button" aria-label="Explore iPhones">→</a>
                        </div>
                    </div>
                </article>

                <!-- Demo product 2 -->
                <article class="product-card">
                    <a href="shop.php" class="product-image product-image-two">
                        <span class="product-badge badge-new">EXPLORE</span>
                        <div class="demo-phone demo-phone-blue">
                            <div class="demo-camera camera-a"></div>
                            <div class="demo-camera camera-b"></div>
                            <div class="demo-camera camera-c"></div>
                            <div class="demo-flash"></div>
                        </div>
                    </a>
                    <div class="product-info">
                        <span class="product-brand">SAMSUNG</span>
                        <h3>Samsung Galaxy</h3>
                        <p class="product-description">Discover Galaxy smartphones</p>
                        <div class="product-bottom">
                            <span class="price">Explore prices</span>
                            <a href="shop.php" class="add-button" aria-label="Explore Samsung phones">→</a>
                        </div>
                    </div>
                </article>

                <!-- Demo product 3 -->
                <article class="product-card">
                    <a href="shop.php" class="product-image product-image-three">
                        <span class="product-badge badge-value">GREAT VALUE</span>
                        <div class="demo-phone demo-phone-silver">
                            <div class="demo-camera camera-a"></div>
                            <div class="demo-camera camera-b"></div>
                            <div class="demo-camera camera-c"></div>
                            <div class="demo-flash"></div>
                        </div>
                    </a>
                    <div class="product-info">
                        <span class="product-brand">ANDROID</span>
                        <h3>Android Phones</h3>
                        <p class="product-description">Options for different budgets</p>
                        <div class="product-bottom">
                            <span class="price">Explore prices</span>
                            <a href="shop.php" class="add-button" aria-label="Explore Android phones">→</a>
                        </div>
                    </div>
                </article>

                <!-- Demo product 4 -->
                <article class="product-card">
                    <a href="shop.php?category=Accessories" class="product-image product-image-four">
                        <span class="product-badge badge-accessory">ESSENTIALS</span>
                        <div class="demo-earbuds">
                            <div class="demo-bud bud-left"></div>
                            <div class="demo-bud bud-right"></div>
                            <div class="demo-case"></div>
                        </div>
                    </a>
                    <div class="product-info">
                        <span class="product-brand">ACCESSORIES</span>
                        <h3>Everyday Essentials</h3>
                        <p class="product-description">Explore useful phone accessories</p>
                        <div class="product-bottom">
                            <span class="price">Explore prices</span>
                            <a href="shop.php?category=Accessories" class="add-button"
                                aria-label="Explore accessories">→</a>
                        </div>
                    </div>
                </article>

            </div>

            <div class="products-cta">
                <a href="shop.php" class="btn-primary">
                    Browse All Products <span>→</span>
                </a>
            </div>
        </section>

        <!-- Promotional banner -->
        <section class="promo-section">
            <div class="promo-content">
                <span class="section-label">YOUR NEXT CHAPTER</span>
                <h2>Better tech.<br>Better possibilities.</h2>
                <p>
                    Find a device that works for your lifestyle,
                    your needs, and your budget.
                </p>
                <a href="shop.php" class="btn-light">
                    Find Your Phone <span>→</span>
                </a>
            </div>

            <div class="promo-decoration">
                <div class="promo-ring ring-one"></div>
                <div class="promo-ring ring-two"></div>
                <div class="promo-phone"></div>
            </div>
        </section>

        <!-- Newsletter / contact -->
        <section class="newsletter-section">
            <div>
                <span class="section-label">STAY CONNECTED</span>
                <h2>Have a question about a phone?</h2>
                <p>Get in touch with Xamani Mobile Hub for help finding a device.</p>
            </div>

            <a href="shop.php" class="btn-primary">
                Explore the Shop <span>→</span>
            </a>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-main">
            <div class="footer-brand">
                <a href="index.php" class="logo footer-logo">
                    <span class="logo-icon">X</span>
                    <span>XAMANI<span class="logo-light"> MOBILE HUB</span></span>
                </a>

                <p>
                    Your destination for smartphones, accessories,
                    and smarter everyday technology.
                </p>
            </div>

            <div class="footer-column">
                <h4>Explore</h4>
                <a href="index.php">Home</a>
                <a href="shop.php">All Products</a>
                <a href="shop.php?category=Smartphones">Smartphones</a>
                <a href="shop.php?category=Accessories">Accessories</a>
            </div>

            <div class="footer-column">
                <h4>Customer</h4>
                <a href="cart.php">Shopping Cart</a>
                <a href="account/login.php">Customer Login</a>
                <a href="shop.php">Browse Products</a>
            </div>

            <div class="footer-column">
                <h4>Contact</h4>
                <p>Need help choosing a device?</p>
                <a href="shop.php">Contact us through the shop</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <span id="currentYear"></span> Xamani Mobile Hub. All rights reserved.</p>
            <span>Made for smarter shopping.</span>
        </div>
    </footer>

    <script>
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.querySelector('.nav-links');

    menuToggle.addEventListener('click', function() {
        const isOpen = navLinks.classList.toggle('show');
        menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        menuToggle.textContent = isOpen ? '✕' : '☰';
    });

    document.getElementById('currentYear').textContent =
        new Date().getFullYear();
    </script>

</body>

</html>