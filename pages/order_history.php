<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

$customer_id = $_SESSION['user_id'];

$pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get customer's completed orders
$stmt = $pdo->prepare("
    SELECT 
      o.shipment_id,
      o.order_id,
      o.order_date,
      o.total_price,
      o.order_status,
      p.product_name,
      o.quantity,
      u.username as farmer_name
    FROM orders o
    JOIN products p ON o.product_id = p.product_id
    JOIN users u ON p.farmer_id = u.user_id
    WHERE o.customer_id = ? AND o.order_status IN ('completed', 'pending', 'confirmed', 'shipped')
    ORDER BY o.order_date DESC
");
$stmt->execute([$customer_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <script src="../scripts/settings.js"></script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order History - GFLH</title>
  <link rel="stylesheet" href="../styles/navbar.css" />
  <link rel="stylesheet" href="../styles/order_history.css" />

</head>
<body>
<?php include('../includes/navbar.php'); ?>

<main>
  <div class="history-header">
    <h1>Your Order History</h1>
    <p>View all your past orders and their status</p>
  </div>

  <?php if (count($orders) > 0): ?>
    <div class="orders-container">
      <?php 
      // Group orders by shipment_id for display
      $grouped_orders = [];
      foreach ($orders as $order) {
        if (!isset($grouped_orders[$order['shipment_id']])) {
          $grouped_orders[$order['shipment_id']] = [];
        }
        $grouped_orders[$order['shipment_id']][] = $order;
      }
      ?>

      <?php foreach ($grouped_orders as $shipment_id => $order_items): 
        $first_item = $order_items[0];
        $shipment_total = 0;
        foreach ($order_items as $item) {
          $shipment_total += $item['total_price'];
        }
      ?>
        <div class="order-card">
          <div class="order-header">
            <div class="order-info">
              <div class="order-id">Order #<?php echo $first_item['order_id']; ?></div>
              <div class="order-date"><?php echo date('F d, Y \a\t H:i', strtotime($first_item['order_date'])); ?></div>
            </div>
            <div class="order-status status-<?php echo strtolower($first_item['order_status']); ?>">
              <?php echo ucfirst($first_item['order_status']); ?>
            </div>
          </div>

          <div class="order-body">
            <?php foreach ($order_items as $item): ?>
              <div class="order-item">
                <div class="item-details">
                  <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                  <div class="item-farmer">from <?php echo htmlspecialchars($item['farmer_name']); ?></div>
                  <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                </div>
                <div class="item-total">
                  <div class="item-price">£<?php echo number_format($item['total_price'], 2); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="order-footer">
            <div class="order-total">
              <span class="total-label">Order Total:</span>
              <span class="total-amount">£<?php echo number_format($shipment_total, 2); ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <h2>No Orders Yet</h2>
      <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
      <a href="../pages/products.php">Browse Products</a>
    </div>
  <?php endif; ?>
</main>

</body>
</html>
