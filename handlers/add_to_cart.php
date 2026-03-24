<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Login required");
}

$pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$product_id = $_POST['product_id'];
$quantity = (int)$_POST['quantity'];
$delivery_method = $_POST['delivery_method'] ?? 'delivery';

// Make delivery address optional
$delivery_address = $_POST['delivery_address'] ?? null; // allow NULL

$customer_id = $_SESSION['user_id'];

// Get product info including farmer_id
$stmt = $pdo->prepare("SELECT price, stock_quantity, farmer_id FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: /GFLH/pages/products.php?error=product_not_found");
    exit;
}

// Prevent farmers from buying their own products
if (isset($_SESSION['role']) && $_SESSION['role'] === 'farmer' && $product['farmer_id'] == $customer_id) {
    header("Location: /GFLH/pages/products.php?error=own_product");
    exit;
}

// ✅ VALIDATION
if ($quantity < 1) $quantity = 1;
if ($quantity > $product['stock_quantity']) {
    $quantity = $product['stock_quantity'];
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
        SET quantity = ?, total_price = ?, delivery_method = ?, delivery_address = ? 
        WHERE shipment_id = ?
    ");
    $stmt->execute([
        $newQty,
        $product['price'] * $newQty,
        $delivery_method,
        $delivery_address, // can be NULL now
        $existing['shipment_id']
    ]);

} else {
    // Generate unique order ID
    $order_id = abs(crc32(uniqid() . mt_rand()));

    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (order_id, customer_id, product_id, quantity, total_price, delivery_method, delivery_address, order_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'basket')
    ");

    $stmt->execute([
        $order_id,
        $customer_id,
        $product_id,
        $quantity,
        $total_price,
        $delivery_method,
        $delivery_address // can be NULL
    ]);
}

header("Location: /GFLH/pages/products.php");
exit;