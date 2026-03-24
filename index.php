<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS GFLH
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci
    ");

    // Select database
    $pdo->exec("USE GFLH");

    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('customer', 'farmer') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    // Create products table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            product_id INT AUTO_INCREMENT PRIMARY KEY,
            farmer_id INT NOT NULL,
            product_name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            stock_quantity INT NOT NULL,
            image_path VARCHAR(255),
            delivery_option BOOLEAN DEFAULT FALSE,
            pickup_option BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (farmer_id) REFERENCES users(user_id) ON DELETE CASCADE
        )
    ");

    // Create orders table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            shipment_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL UNIQUE,
            customer_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            total_price DECIMAL(10, 2) NOT NULL,
            delivery_method ENUM('delivery', 'collection') NOT NULL,
            delivery_address VARCHAR(255),
            order_status ENUM('basket','pending', 'confirmed', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
            FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
        )
    ");

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}

// Fetch featured products (latest 4 in stock)
$featuredProducts = $pdo->query("
    SELECT p.product_id, p.product_name, p.price, p.stock_quantity, p.image_path, u.username as farmer_name
    FROM products p
    INNER JOIN users u ON p.farmer_id = u.user_id
    WHERE p.stock_quantity > 0
    ORDER BY p.created_at DESC
    LIMIT 4
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch stats
$farmerCount = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'farmer'")->fetch()['count'];
$productCount = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GFLH - Greenfield Local Hub</title>
    <link rel="stylesheet" href="styles/navbar.css" />
    <link rel="stylesheet" href="styles/home.css" />
    <link rel="stylesheet" href="styles/dark-mode.css">

</head>
<body>
    <?php include('includes/navbar.php'); ?>
    <script src="scripts/settings.js"></script>
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-container">
                <h1>Fresh, Local, Sustainable</h1>
                <p>Discover the best local produce from farmers and artisans in your community. Supporting local has never been easier or more delicious.</p>
                <div class="hero-buttons">
                    <a href="/GFLH/pages/products.php" class="btn btn-primary">🛒 Shop Now</a>
                    <a href="/GFLH/pages/producers.php" class="btn btn-secondary">Meet Our Producers →</a>
                </div>
            </div>
        </section>

        <!-- Why Buy Local Section -->
        <section class="why-buy-section">
            <div class="why-buy-container">
                <h2 class="section-title">Why Buy Local?</h2>
                <div class="benefits-grid">
                    <div class="benefit-card">
                        <div class="benefit-icon">🌿</div>
                        <h3>Sustainable & Fresh</h3>
                        <p>Lower carbon footprint with products traveling just a few miles. Everything is harvested at peak freshness.</p>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon">❤️</div>
                        <h3>Support Community</h3>
                        <p>Your money goes directly to local farmers and producers, strengthening our local economy.</p>
                    </div>
                    <div class="benefit-card">
                        <div class="benefit-icon">🏆</div>
                        <h3>Quality Guaranteed</h3>
                        <p>Know exactly where your food comes from. Full traceability and transparency for every product.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section class="featured-section">
            <div class="featured-header">
                <h2>Featured Products</h2>
                <a href="/GFLH/pages/products.php" class="view-all">View All →</a>
            </div>
            <div class="products-grid">
                <?php if (empty($featuredProducts)): ?>
                    <p>No products available yet. Check back soon!</p>
                <?php else: ?>
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($product['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-farm">📍 <?php echo htmlspecialchars($product['farmer_name']); ?></div>
                                <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                <div class="product-footer">
                                    <span class="product-price">£<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="product-quantity">Qty: <?php echo $product['stock_quantity']; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-icon">👥</div>
                    <h3><?php echo $farmerCount; ?></h3>
                    <p>Local Producers</p>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">✅</div>
                    <h3><?php echo $productCount; ?>+</h3>
                    <p>Quality Products</p>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">📍</div>
                    <h3>100%</h3>
                    <p>Locally Sourced</p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-container">
                <h2>Ready to Start Shopping?</h2>
                <p>Join our community and enjoy fresh, local products delivered to your door or ready for collection. Earn loyalty points with every purchase!</p>
                <div class="cta-buttons">
                    <a href="/GFLH/pages/products.php" class="cta-btn cta-btn-primary">Browse Products</a>
                    <a href="/GFLH/pages/about.php" class="cta-btn cta-btn-secondary">Learn More</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div>
                <div class="footer-logo">
                    <div class="footer-logo-text">
                        <span class="footer-logo-main">GLH</span>
                        <span class="footer-logo-sub">Good Local Hub</span>
                    </div>
                </div>
                <p class="footer-text">Supporting local farmers and food producers. Fresh, sustainable, and transparent.</p>
            </div>
            <div>
                <h3>Quick Links</h3>
                <a href="/GFLH/pages/products.php" class="footer-link">Products</a>
                <a href="/GFLH/pages/producers.php" class="footer-link">Our Producers</a>
                <a href="/GFLH/pages/about.php" class="footer-link">About Us</a>
            </div>
            <div>
                <h3>Contact</h3>
                <p class="contact-info">Email: hello@glh.local</p>
                <p class="contact-info">Phone: 01234 567890</p>
            </div>
        </div>
        <div class="footer-divider">
            <p>&copy; 2026 Good Local Hub. All rights reserved.</p>
        </div>
    </footer>
    
</body>
</html>