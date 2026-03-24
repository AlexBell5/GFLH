<?php
session_start();

// Redirect if not logged in or not a farmer
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

// Fetch farmer's products
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Products - GFLH</title>
  <link rel="stylesheet" href="../styles/navbar.css" />
  <style>
    main {
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px;
    }

    .manage-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #16a34a;
    }

    .manage-header h1 {
      color: #333;
    }

    .add-btn {
      background: #16a34a;
      color: white;
      padding: 12px 24px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
    }

    .add-btn:hover {
      background: #15803d;
    }

    .products-table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      border-radius: 8px;
      overflow: hidden;
    }

    .products-table thead {
      background: #f3f4f6;
      border-bottom: 2px solid #e5e7eb;
    }

    .products-table th {
      padding: 15px;
      text-align: left;
      font-weight: 600;
      color: #333;
    }

    .products-table td {
      padding: 15px;
      border-bottom: 1px solid #e5e7eb;
    }

    .products-table tbody tr:hover {
      background: #f9fafb;
    }

    .product-name {
      font-weight: 600;
      color: #333;
    }

    .price-cell {
      color: #16a34a;
      font-weight: 600;
    }

    .stock-cell {
      color: #666;
    }

    .stock-cell.low {
      color: #dc2626;
      font-weight: 600;
    }

    .actions {
      display: flex;
      gap: 10px;
    }

    .btn-edit {
      background: #3b82f6;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      transition: 0.3s;
    }

    .btn-edit:hover {
      background: #2563eb;
    }

    .btn-delete {
      background: #ef4444;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      transition: 0.3s;
    }

    .btn-delete:hover {
      background: #dc2626;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
    }

    .modal.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 8px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .modal-header {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 20px;
      color: #333;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #333;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      box-sizing: border-box;
    }

    .form-group input:focus {
      outline: none;
      border-color: #16a34a;
      box-shadow: 0 0 5px rgba(22, 163, 74, 0.3);
    }

    .modal-actions {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .modal-actions button {
      flex: 1;
      padding: 10px;
      border: none;
      border-radius: 4px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-save {
      background: #16a34a;
      color: white;
    }

    .btn-save:hover {
      background: #15803d;
    }

    .btn-cancel {
      background: #e5e7eb;
      color: #333;
    }

    .btn-cancel:hover {
      background: #d1d5db;
    }

    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 15px;
      border-radius: 6px;
      margin-bottom: 20px;
      border: 1px solid #c3e6cb;
    }

    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 15px;
      border-radius: 6px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #666;
    }

    .empty-state h2 {
      color: #333;
      margin-bottom: 15px;
    }
  </style>
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

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('editModal');
  if (event.target === modal) {
    modal.classList.remove('active');
  }
}
</script>

</body>
</html>
