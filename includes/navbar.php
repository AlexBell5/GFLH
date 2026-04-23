<?php
if (session_status() == PHP_SESSION_NONE) session_start();
$is_logged_in = isset($_SESSION['user_id']);
?>

<nav class="navbar">
    <div class="navbar-container">

        <!-- Logo -->
        <div class="navbar-logo">
            <img src="/GFLH/images/image.png" class="logo-icon">
            <div class="logo-text">
                <span class="logo-main">GLH</span>
                <span class="logo-sub">Greenfield Local Hub</span>
            </div>
        </div>

        <!-- Links -->
        <div class="navbar-links">
            <a href="/GFLH/index.php" class="nav-link">Home</a>
            <a href="/GFLH/pages/products.php" class="nav-link">Products</a>
            <a href="/GFLH/pages/producers.php" class="nav-link">Producers</a>
            <a href="/GFLH/pages/settings.php" class="nav-link">Settings</a>
        </div>

        <!-- Right Section -->
        <div class="navbar-right">
            <?php if ($is_logged_in): ?>
                <button id="cartToggle" class="navbar-icon">
                    🛒 
                </button>
                <a href="/GFLH/pages/profile.php" class="navbar-icon">👤</a>
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
<!-- Cart Sidebar -->
<div id="cartSidebar" class="cart-sidebar">
    <div class="cart-header">
        <h2>Your Basket</h2>
        <button id="closeCart">✖</button>
    </div>
    <div class="cart-items">
        <!-- Cart items loaded dynamically via AJAX -->
    </div>
    <a href="/GFLH/pages/checkout.php" class="checkout-btn">Checkout</a>
</div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const cart = document.getElementById("cartSidebar");
    const openBtn = document.getElementById("cartToggle");
    const closeBtn = document.getElementById("closeCart");

    if (openBtn) {
        openBtn.addEventListener('click', async () => {
            cart.style.right = "0";
            await refreshCartSidebar(); 
        });
    }
    if (closeBtn) closeBtn.onclick = () => cart.style.right = "-400px";

    
    async function refreshCartSidebar() {
        const itemsDiv = cart.querySelector('.cart-items');
        try {
            const response = await fetch('/GFLH/handlers/get_cart.php', { credentials: 'same-origin' });
            const html = await response.text();
            itemsDiv.innerHTML = html;
        } catch (err) {
            itemsDiv.innerHTML = '<p>Error loading cart</p>';
        }
    }

    
    window.refreshCartSidebar = refreshCartSidebar;
});
</script>

<script type="text/javascript" src="
