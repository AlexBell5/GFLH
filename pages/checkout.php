<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout - GFLH</title>
  <link rel="stylesheet" href="../styles/navbar.css" />
  <style>
    main {
      max-width: 800px;
      margin: 40px auto;
      padding: 20px;
    }

    .checkout-container {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      padding: 30px;
    }

    .checkout-header {
      text-align: center;
      margin-bottom: 30px;
      border-bottom: 2px solid #16a34a;
      padding-bottom: 20px;
    }

    .checkout-header h1 {
      color: #333;
      margin-bottom: 10px;
    }

    .order-items {
      margin: 30px 0;
    }

    .item {
      display: flex;
      justify-content: space-between;
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }

    .item-info {
      flex: 1;
    }

    .item-name {
      font-weight: 600;
      color: #333;
      margin-bottom: 5px;
    }

    .item-quantity {
      font-size: 14px;
      color: #666;
    }

    .item-price {
      text-align: right;
      font-weight: 600;
      color: #16a34a;
      font-size: 18px;
    }

    .order-summary {
      background: #f9f9f9;
      padding: 20px;
      border-radius: 8px;
      margin: 20px 0;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 16px;
    }

    .summary-row.total {
      font-size: 20px;
      font-weight: 700;
      color: #16a34a;
      border-top: 2px solid #ddd;
      padding-top: 10px;
      margin-top: 10px;
    }

    .checkout-actions {
      display: flex;
      gap: 15px;
      margin-top: 30px;
    }

    .btn-complete {
      flex: 1;
      padding: 14px;
      background: #16a34a;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-complete:hover {
      background: #15803d;
    }

    .btn-cancel {
      flex: 1;
      padding: 14px;
      background: #f0f0f0;
      color: #333;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-cancel:hover {
      background: #e0e0e0;
    }

    .empty-cart {
      text-align: center;
      padding: 40px;
      color: #666;
    }

    .empty-cart h2 {
      color: #333;
      margin-bottom: 15px;
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
  </style>
</head>
<body>
  <script src="../scripts/settings.js"></script>
<?php include('../includes/navbar.php'); ?>

<main>
  <div class="checkout-container">
    <div class="checkout-header">
      <h1>Order Summary</h1>
      <p>Review your items and complete your order</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
      <div class="error-message">
        <?php
          if ($_GET['error'] === 'empty_cart') {
            echo "Your cart is empty. <a href='../pages/products.php'>Browse products</a>";
          } else {
            echo "Something went wrong. Please try again.";
          }
        ?>
      </div>
    <?php endif; ?>

    <?php 
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../pages/login.php");
        exit;
    }

    $customer_id = $_SESSION['user_id'];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get all basket items
        $stmt = $pdo->prepare("
            SELECT 
              o.shipment_id,
              o.product_id,
              o.quantity,
              o.total_price,
              p.product_name
            FROM orders o
            JOIN products p ON o.product_id = p.product_id
            WHERE o.customer_id = ? AND o.order_status = 'basket'
            ORDER BY o.order_date DESC
        ");
        $stmt->execute([$customer_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)): 
        ?>
          <div class="empty-cart">
            <h2>Your Cart is Empty</h2>
            <p>Add some products before checking out.</p>
            <a href="../pages/products.php" class="btn-cancel" style="inline-size: fit-content; display: inline-block; padding: 10px 20px; margin-top: 15px;">Browse Products</a>
          </div>
        <?php else: 
          $total = 0;
          foreach ($items as $item) {
              $total += $item['total_price'];
          }
        ?>
          <div class="order-items">
            <?php foreach ($items as $item): ?>
              <div class="item">
                <div class="item-info">
                  <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                  <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                </div>
                <div class="item-price">£<?php echo number_format($item['total_price'], 2); ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="order-summary">
            <div class="summary-row">
              <span>Subtotal:</span>
              <span>£<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
              <span>Delivery:</span>
              <span>FREE</span>
            </div>
            <div class="summary-row total">
              <span>Total:</span>
              <span>£<?php echo number_format($total, 2); ?></span>
            </div>
          </div>

          <form method="POST" action="../handlers/checkout.php" style="display: flex; gap: 15px;">
            <button type="submit" class="btn-complete">Complete Order</button>
            <a href="../pages/products.php" class="btn-cancel">Continue Shopping</a>
          </form>
        <?php endif; ?>

    <?php } catch (Exception $e) { ?>
      <div class="error-message">
        Unable to load cart. Please try again.
      </div>
    <?php } ?>
  </div>
</main>

</body>
</html>
