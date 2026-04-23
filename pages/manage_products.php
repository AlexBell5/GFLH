<?php
/*
 * Farmer product management interface
 * Displays table of farmer's products with editing and deletion capabilities
 */
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../pages/login.php");
    exit;
}

$farmer_id = $_SESSION['user_id'];

$host = 'localhost';
$db = 'GFLH';
$dbuser = 'root';
$pass = '';

$conn = new mysqli($host, $dbuser, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$stmt = $conn->prepare("
    SELECT product_id, product_name, description, price, stock_quantity, created_at
    FROM products 
    WHERE farmer_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <script src="../scripts/settings.js"></script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Products - GFLH</title>
  <link rel="stylesheet" href="../styles/navbar.css" />
  <link rel="stylesheet" href="../styles/manage-products.css" />
</head>
<body>
<?php include('../includes/navbar.php'); ?>

<main>
  <div class="manage-header">
    <h1>Manage Your Products</h1>
    <a href="/GFLH/handlers/add-product.php" class="add-btn">+ Add New Product</a>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="success-message">
      Product updated successfully!
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="error-message">
      <?php
        if ($_GET['error'] === 'invalid_values') {
          echo "Invalid price or stock quantity.";
        } elseif ($_GET['error'] === 'unauthorized') {
          echo "You don't have permission to edit this product.";
        } else {
          echo "An error occurred. Please try again.";
        }
      ?>
    </div>
  <?php endif; ?>

  <?php if (count($products) > 0): ?>
    <table class="products-table">
      <thead>
        <tr>
          <th>Product Name</th>
          <th>Price (£)</th>
          <th>Stock</th>
          <th>Added</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $product): ?>
          <tr>
            <td class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></td>
            <td class="price-cell">£<?php echo number_format($product['price'], 2); ?></td>
            <td class="stock-cell <?php echo $product['stock_quantity'] <= 5 ? 'low' : ''; ?>">
              <?php echo $product['stock_quantity']; ?> units
            </td>
            <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
            <td>
              <div class="actions">
                <button class="btn-edit" onclick="openEditModal(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['stock_quantity']; ?>)">Edit</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="empty-state">
      <h2>No Products Yet</h2>
      <p>Start by adding your first product to your store.</p>
      <a href="/GFLH/handlers/add-product.php" class="add-btn" style="display: inline-block; margin-top: 15px;">+ Add First Product</a>
    </div>
  <?php endif; ?>
</main>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">Edit Product</div>
    <form method="POST" action="../handlers/edit_product.php">
      <input type="hidden" id="productId" name="product_id">

      <div class="form-group">
        <label for="editPrice">Price (£) *</label>
        <input 
          type="number" 
          id="editPrice" 
          name="price" 
          step="0.01" 
          min="0" 
          required 
        />
      </div>

      <div class="form-group">
        <label for="editStock">Stock Quantity *</label>
        <input 
          type="number" 
          id="editStock" 
          name="stock_quantity" 
          min="0" 
          required 
        />
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn-save">Save Changes</button>
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(productId, productName, price, stock) {
  document.getElementById('productId').value = productId;
  document.getElementById('editPrice').value = price;
  document.getElementById('editStock').value = stock;
  document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
  document.getElementById('editModal').classList.remove('active');
}


window.onclick = function(event) {
  const modal = document.getElementById('editModal');
  if (event.target === modal) {
    modal.classList.remove('active');
  }
}
</script>

</body>
</html>

