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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order History - GFLH</title>
  <link rel="stylesheet" href="../styles/navbar.css" />
  <style>
    main {
      max-width: 1000px;
      margin: 40px auto;
      padding: 20px;
    }

    .history-header {
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #16a34a;
    }

    .history-header h1 {
      color: #333;
      margin-bottom: 10px;
    }

    .history-header p {
      color: #666;
    }

    .orders-container {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .order-card {
      background: white;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      transition: 0.3s;
    }

    .order-card:hover {
      box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    }

    .order-header {
      background: #f9fafb;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #e5e7eb;
    }

    .order-info {
      flex: 1;
    }

    .order-id {
      font-size: 14px;
      color: #666;
      margin-bottom: 5px;
    }

    .order-date {
      color: #999;
      font-size: 13px;
    }

    .order-status {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      text-transform: capitalize;
    }

    .status-completed {
      background: #d1fae5;
      color: #065f46;
    }

    .status-pending {
      background: #fef3c7;
      color: #92400e;
    }

    .status-confirmed {
      background: #dbeafe;
      color: #0c4a6e;
    }

    .status-shipped {
      background: #cffafe;
      color: #164e63;
    }

    .order-body {
      padding: 20px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      align-items: start;
      padding: 15px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .item-details {
      flex: 1;
    }

    .item-name {
      font-weight: 600;
      color: #333;
      margin-bottom: 5px;
    }

    .item-farmer {
      font-size: 13px;
      color: #666;
      margin-bottom: 5px;
    }

    .item-quantity {
      font-size: 13px;
      color: #999;
    }

    .item-total {
      text-align: right;
    }

    .item-price {
      font-size: 18px;
      font-weight: 700;
      color: #16a34a;
    }

    .order-footer {
      padding: 15px 20px;
      background: #f9fafb;
      display: flex;
      justify-content: flex-end;
      border-top: 1px solid #e5e7eb;
    }

    .order-total {
      display: flex;
      gap: 40px;
      align-items: center;
    }

    .total-label {
      color: #666;
      font-weight: 500;
    }

    .total-amount {
      font-size: 20px;
      font-weight: 700;
      color: #16a34a;
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

    .empty-state a {
      display: inline-block;
      background: #16a34a;
      color: white;
      padding: 12px 24px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      margin-top: 15px;
      transition: 0.3s;
    }

    .empty-state a:hover {
      background: #15803d;
    }
  </style>
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
