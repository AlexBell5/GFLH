<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout - GFLH</title>
  <link rel="stylesheet" href="../styles/navbar.css" />
  <link rel="stylesheet" href="../styles/checkout.css" />

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

        
        $stmt = $pdo->prepare("
            SELECT 
              o.order_id,
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

<form method="GET" action="../pages/payment.php" style="display: flex; gap: 15px;">
    <button type="submit" class="btn-complete">Proceed to Payment</button>
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

