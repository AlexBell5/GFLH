<?php 
session_start();

// Database connection
$host = 'localhost';
$db = 'GFLH';
$dbuser = 'root';
$pass = '';

$conn = new mysqli($host, $dbuser, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sorting logic
$order = "p.created_at DESC";

if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'name_asc': $order = "p.product_name ASC"; break;
        case 'name_desc': $order = "p.product_name DESC"; break;
        case 'price_low': $order = "p.price ASC"; break;
        case 'price_high': $order = "p.price DESC"; break;
    }
}

// Fetch products
$query = "
    SELECT 
        p.product_id,
        p.product_name,
        p.description,
        p.price,
        p.stock_quantity,
        p.image_path,
        p.delivery_option,
        p.pickup_option,
        u.username as farmer_name
    FROM products p
    INNER JOIN users u ON p.farmer_id = u.user_id
    ORDER BY $order
";

$result = $conn->query($query);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products - GFLH</title>
  <link rel="stylesheet" href="../styles/products.css">
  <link rel="stylesheet" href="../styles/navbar.css">
</head>

<body>

<?php include('../includes/navbar.php'); ?>
<script src="../scripts/settings.js"></script>
<main>
  <?php if (isset($_GET['error'])): ?>
    <div style="background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 6px; margin: 20px auto; max-width: 1000px; border: 1px solid #fecaca;">
      <?php if ($_GET['error'] === 'own_product'): ?>
        <strong>Error:</strong> This is your own product.
      <?php elseif ($_GET['error'] === 'product_not_found'): ?>
        <strong>Error:</strong> The product you tried to add could not be found. It may have been removed.
      <?php else: ?>
        <strong>Error:</strong> An unknown error occurred.
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="products-section">

    <!-- HEADER -->
    <div class="products-header">
      <h1>Our Products</h1>
      <p>Browse our full range of fresh, locally-sourced products</p>
    </div>

    <!-- CONTROLS -->
    <div class="products-controls">
      <input type="text" id="searchInput" placeholder="Search products or producers...">

      <select id="sortSelect" onchange="handleSortChange(this.value)">
        <option value="">Sort</option>
        <option value="name_asc">Name (A–Z)</option>
        <option value="name_desc">Name (Z–A)</option>
        <option value="price_low">Price (Low → High)</option>
        <option value="price_high">Price (High → Low)</option>
      </select>

    </div>

    <p>Showing <?php echo count($products); ?> products</p>

    <!-- PRODUCTS -->
    <?php if (count($products) > 0): ?>
      <div class="products-grid" id="productsGrid">

        <?php foreach ($products as $product): ?>
          <div class="product-card">

            <!-- IMAGE -->
            <div class="product-image">
              <?php if (!empty($product['image_path']) && file_exists('../' . $product['image_path'])): ?>
                <img src="../<?php echo htmlspecialchars($product['image_path']); ?>">
              <?php endif; ?>
            </div>

            <!-- INFO -->
            <div class="product-info">

              <div class="product-name">
                <?php echo htmlspecialchars($product['product_name']); ?>
              </div>

              <div class="product-farmer">
                by <?php echo htmlspecialchars($product['farmer_name']); ?>
              </div>

              <?php if (!empty($product['description'])): ?>
                <div class="product-description">
                  <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>
                  <?php if (strlen($product['description']) > 100) echo "..."; ?>
                </div>
              <?php endif; ?>

              <div class="product-footer">

                <div class="price-section">
                  <span class="product-price">£<?php echo number_format($product['price'], 2); ?></span>

                  <?php if ($product['stock_quantity'] > 0): ?>
                    <span class="product-stock">
                      <?php echo $product['stock_quantity']; ?> available
                    </span>
                  <?php else: ?>
                    <span class="product-stock">Out of stock</span>
                  <?php endif; ?>
                </div>

                <!-- DELIVERY OPTIONS -->
                <?php if ($product['delivery_option'] || $product['pickup_option']): ?>
                  <div class="product-options">
                    <?php if ($product['delivery_option']): ?>
                      <span class="option-badge">🚚 Delivery</span>
                    <?php endif; ?>
                    <?php if ($product['pickup_option']): ?>
                      <span class="option-badge">🏪 Pickup</span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <!-- ACTIONS -->
                <div class="product-actions">
<form method="POST" action="/GFLH/handlers/add_to_cart.php" class="cart-form">
  
  <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

  <input 
    type="number" 
    name="quantity" 
    value="1" 
    min="1" 
    max="<?php echo $product['stock_quantity']; ?>"
    class="quantity-input"
  >




  <button class="btn-add-cart"
    <?php if ($product['stock_quantity'] <= 0) echo 'disabled'; ?>>
    Add to Cart
  </button>

</form>
                </div>

              </div>
            </div>

          </div>
        <?php endforeach; ?>

      </div>

    <?php else: ?>
      <div class="no-products">
        <h2>No Products Available</h2>
        <p>Check back soon for fresh farm products!</p>
      </div>
    <?php endif; ?>

  </div>
</main>

<!-- SEARCH SCRIPT -->
<script>
document.getElementById("searchInput").addEventListener("keyup", function () {
  const value = this.value.toLowerCase();
  const cards = document.querySelectorAll(".product-card");

  cards.forEach(card => {
    const name = card.querySelector(".product-name").innerText.toLowerCase();
    const farmer = card.querySelector(".product-farmer").innerText.toLowerCase();

    if (name.includes(value) || farmer.includes(value)) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
});
</script>

<!-- SORT SCRIPT -->
<script>
function handleSortChange(value) {
  if (value === "") return;
  window.location.href = "?sort=" + value;
}
</script>


</body>
</html>