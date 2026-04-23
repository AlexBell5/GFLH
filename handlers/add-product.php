<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ./pages/login.php");
    exit;
}


$conn = new mysqli('localhost', 'root', '', 'GFLH');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$address = '';
$stmt = $conn->prepare("SELECT address FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $address = $row['address'];
}
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="../styles/navbar.css" />
   <script src="../scripts/settings.js"></script>
  <?php include('../includes/navbar.php'); ?>
  <script src="../scripts/settings.js"></script>
  <meta charset="UTF-8">
  <title>Add Product</title>
  <link rel="stylesheet" href="../styles/add-products.css" />
</head>
<body>

<h1>Add Product</h1>

<?php if (isset($_GET['error'])): ?>
  <p style="color:red;">Error: <?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>

<form method="POST" action="/GFLH/handlers/add_product.php" enctype="multipart/form-data" onsubmit="return validateForm()">

  <input type="text" name="product_name" placeholder="Product Name" required><br><br>

  <textarea name="description" placeholder="Description"></textarea><br><br>

  <input type="number" name="price" step="0.01" placeholder="Price" required><br><br>

  <input type="number" name="stock_quantity" placeholder="Stock" required><br><br>

  <input type="file" name="product_image"><br><br>

  <label>
    <input type="checkbox" id="delivery_option" name="delivery_option"> Delivery
  </label>

  <label>
    <input type="checkbox" id="pickup_option" name="pickup_option"> Pickup
  </label>

  <br><br>

  <!-- ✅ ALWAYS REQUIRED -->
   <h>Delivery Address</h>
  <textarea id="address" name="address" required><?php echo htmlspecialchars($address); ?></textarea>

  <br><br>

  <button type="submit">Add Product</button>

</form>

<script>
function validateForm() {
  const delivery = document.getElementById('delivery_option');
  const pickup = document.getElementById('pickup_option');

  if (!delivery.checked && !pickup.checked) {
    delivery.setCustomValidity("Select at least delivery or pickup");
    delivery.reportValidity();
    return false;
  } else {
    delivery.setCustomValidity("");
  }

  return true;
}
</script>

</body>
</html>
