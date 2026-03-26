<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
?>

<nav class="navbar">
    <script src="../scripts/settings.js"></script>
    <div class="navbar-container">
        <!-- Logo Section -->
        <div class="navbar-logo">
            <img src="/GFLH/images/image.png" class="logo-icon">
            <div class="logo-text">
                <span class="logo-main">GLH</span>
                <span class="logo-sub">Greenfield Local Hub</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="navbar-links">
            <a href="/GFLH/index.php" class="nav-link">Home</a>
            <a href="/GFLH/pages/products.php" class="nav-link">Products</a>
            <a href="/GFLH/pages/producers.php" class="nav-link">Producers</a>
            <a href="/GFLH/pages/settings.php" class="nav-link">Settings</a>
        </div>

        <!-- Right Section -->
        <div class="navbar-right">
            <!-- Shopping Cart Icon -->
            <?php if ($is_logged_in): ?>
                <button id="cartToggle" class="navbar-icon">🛒</button>
            <?php endif; ?>

            <!-- User Profile Icon -->
            <?php if ($is_logged_in): ?>
                <a href="/GFLH/pages/profile.php" class="navbar-icon">👤</a>
            <?php endif; ?>

            <!-- Login Button or Logout Link -->
            <?php if ($is_logged_in): ?>
                <a href="/GFLH/handlers/logout.php" class="btn-login">Logout</a>
            <?php else: ?>
                <a href="/GFLH/pages/login.php" class="btn-login">Login</a>
            <?php endif; ?>
                <a>Dark Mode</a>
            <div class="toggle-switch" id="toggle">
    <div class="toggle-slider"></div>
</div>
        </div>
    </div>
</nav>

<?php if ($is_logged_in): ?>
<div id="cartSidebar" class="cart-sidebar">
    <div class="cart-header">
        <h2>Your Basket</h2>
        <button id="closeCart">✖</button>
    </div>

    <div class="cart-items">
        <?php
        // Try to load cart items, but do nothing if empty
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("
                SELECT 
                    o.order_id,
                    o.quantity,
                    o.total_price,
                    p.product_name
                FROM orders o
                JOIN products p ON o.product_id = p.product_id
                WHERE o.customer_id = ? AND o.order_status = 'basket'
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $items = $stmt->fetchAll();

            if (!empty($items)):
                $total = 0;
                foreach ($items as $item):
                    $total += $item['total_price'];
        ?>
                    <div class="cart-item">
                        <div>
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                            <p>Qty: <?php echo $item['quantity'] ?? 1; ?></p>
                        </div>
                        <div>
                            £<?php echo number_format($item['total_price'], 2); ?>
                        </div>
                    </div>
        <?php
                endforeach;
        ?>
                <div class="cart-total">
                    <strong>Total: £<?php echo number_format($total, 2); ?></strong>
                </div>
        <?php
            endif; // empty cart does nothing
        } catch (PDOException $e) {
            // fail silently if there’s a DB error
        }
        ?>
    </div>

    <a href="/GFLH/pages/checkout.php" class="checkout-btn" style="display: block; text-align: center; text-decoration: none; color: white; cursor: pointer;">Checkout</a>
</div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const cart = document.getElementById("cartSidebar");
    const openBtn = document.getElementById("cartToggle");
    const closeBtn = document.getElementById("closeCart");

    if (openBtn) {
        openBtn.onclick = () => {
            cart.style.right = "0";
        };
    }

    if (closeBtn) {
        closeBtn.onclick = () => {
            cart.style.right = "-400px";
        };
    }
});
</script>

<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>