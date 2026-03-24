<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

$customer_id = $_SESSION['user_id'];

$pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get all basket items for this customer
    $stmt = $pdo->prepare("
        SELECT shipment_id, product_id, quantity 
        FROM orders 
        WHERE customer_id = ? AND order_status = 'basket'
    ");
    $stmt->execute([$customer_id]);
    $basket_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($basket_items)) {
        $pdo->rollBack();
        header("Location: ../pages/cart.php?error=empty_cart");
        exit;
    }

    // Process each item
    foreach ($basket_items as $item) {
        // Decrease product stock
        $update_stock = $pdo->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity - ? 
            WHERE product_id = ?
        ");
        $update_stock->execute([$item['quantity'], $item['product_id']]);

        // Update order status to completed
        $update_order = $pdo->prepare("
            UPDATE orders 
            SET order_status = 'completed' 
            WHERE shipment_id = ?
        ");
        $update_order->execute([$item['shipment_id']]);
    }

    // Commit transaction
    $pdo->commit();

    // Redirect to success page
    header("Location: ../pages/products.php?checkout=success");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: ../pages/cart.php?error=checkout_failed");
    exit;
}
?>
