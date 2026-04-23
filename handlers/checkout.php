<?php
/*
 * Process checkout and complete orders
 * Updates order status from basket to completed and decrements product stock quantities
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

$customer_id = $_SESSION['user_id'];

$pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    
    $pdo->beginTransaction();

    
    $stmt = $pdo->prepare("
        SELECT o.order_id, o.product_id, o.quantity, p.farmer_id
        FROM orders o
        JOIN products p ON o.product_id = p.product_id
        WHERE o.customer_id = ? AND o.order_status = 'basket'
    ");
    $stmt->execute([$customer_id]);
    $basket_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($basket_items)) {
        $pdo->rollBack();
        header("Location: ../pages/cart.php?error=empty_cart");
        exit;
    }

    
    $user_role = $_SESSION['role'] ?? '';
    if ($user_role === 'farmer') {
        foreach ($basket_items as $item) {
            if ($item['farmer_id'] == $customer_id) {
                $pdo->rollBack();
                header("Location: ../pages/cart.php?error=own_product");
                exit;
            }
        }
    }

    
    foreach ($basket_items as $item) {
        
        $update_stock = $pdo->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity - ? 
            WHERE product_id = ?
        ");
        $update_stock->execute([$item['quantity'], $item['product_id']]);

        
        $update_order = $pdo->prepare("
            UPDATE orders 
            SET order_status = 'completed' 
            WHERE order_id = ?
        ");
        $update_order->execute([$item['order_id']]);
    }

    
    $pdo->commit();

    
    header("Location: ../pages/products.php?checkout=success");
    exit;

} catch (Exception $e) {
    echo "<p style='color:red'>DB error: " . $e->getMessage() . "</p>";
    $pdo->rollBack();
    header("Location: ../pages/cart.php?error=checkout_failed");
    exit;
}
?>

