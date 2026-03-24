<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Login required");
}

$pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$product_id = $_POST['product_id'];
$quantity = (int)$_POST['quantity'];
$customer_id = $_SESSION['user_id'];

// Get product info
$stmt = $pdo->prepare("SELECT price, stock_quantity FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found");
}

// ✅ VALIDATION
if ($quantity < 1) $quantity = 1;

if ($quantity > $product['stock_quantity']) {
    $quantity = $product['stock_quantity']; // prevent overflow
}

$total_price = $product['price'] * $quantity;

// Check if already in basket → update instead
$stmt = $pdo->prepare("
    SELECT shipment_id, quantity 
    FROM orders 
    WHERE customer_id = ? 
    AND product_id = ? 
    AND order_status = 'basket'
");
$stmt->execute([$customer_id, $product_id]);
$existing = $stmt->fetch();

if ($existing) {
    $newQty = $existing['quantity'] + $quantity;

    if ($newQty > $product['stock_quantity']) {
        $newQty = $product['stock_quantity'];
    }

    $stmt = $pdo->prepare("
        UPDATE orders 
        SET quantity = ?, total_price = ? 
        WHERE shipment_id = ?
    ");
    $stmt->execute([
        $newQty,
        $product['price'] * $newQty,
        $existing['shipment_id']
    ]);

} else {
    // Generate unique order ID using crc32 to avoid collisions
    $order_id = abs(crc32(uniqid() . mt_rand()));

    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (order_id, customer_id, product_id, quantity, total_price, delivery_method, order_status) 
        VALUES (?, ?, ?, ?, ?, 'delivery', 'basket')
    ");

    $stmt->execute([
        $order_id,
        $customer_id,
        $product_id,
        $quantity,
        $total_price
    ]);
}

header("Location: /GFLH/pages/products.php");
exit;