<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Login required");
}

$customer_id = (int)$_SESSION['user_id'];

// Connect to database using PDO
$pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get and sanitize POST inputs
$product_id = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$delivery_method = $_POST['delivery_method'] ?? 'delivery';
$allowed_methods = ['delivery', 'pickup'];
if (!in_array($delivery_method, $allowed_methods)) {
    $delivery_method = 'delivery';
}
$delivery_address = trim($_POST['delivery_address'] ?? '');
if ($delivery_address === '') {
    $delivery_address = null;
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Fetch product info with row lock to prevent overselling
    $stmt = $pdo->prepare("SELECT price, stock_quantity, farmer_id FROM products WHERE product_id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $pdo->rollBack();
        header("Location: /GFLH/pages/products.php?error=product_not_found");
        exit;
    }

    // Prevent farmers from buying their own products
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'farmer' && $product['farmer_id'] == $customer_id) {
        $pdo->rollBack();
        header("Location: /GFLH/pages/products.php?error=own_product");
        exit;
    }

    // Adjust quantity if it exceeds stock
    if ($quantity > $product['stock_quantity']) {
        $quantity = $product['stock_quantity'];
    }

    $total_price = $product['price'] * $quantity;

    // Check if product is already in basket
    $stmt = $pdo->prepare("
        SELECT order_id, quantity 
        FROM orders 
        WHERE customer_id = ? 
          AND product_id = ? 
          AND order_status = 'basket'
        FOR UPDATE
    ");
    $stmt->execute([$customer_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update existing basket item
        $newQty = $existing['quantity'] + $quantity;
        if ($newQty > $product['stock_quantity']) {
            $newQty = $product['stock_quantity'];
        }

        $stmt = $pdo->prepare("
            UPDATE orders 
            SET quantity = ?, total_price = ?, delivery_method = ?, delivery_address = ?
            WHERE order_id = ?
        ");
        $stmt->execute([
            $newQty,
            $product['price'] * $newQty,
            $delivery_method,
            $delivery_address,
            $existing['order_id']
        ]);

    } else {
        // Insert new basket item
        $stmt = $pdo->prepare("
            INSERT INTO orders 
            (customer_id, product_id, quantity, total_price, delivery_method, delivery_address, order_status)
            VALUES (?, ?, ?, ?, ?, ?, 'basket')
        ");
        $stmt->execute([
            $customer_id,
            $product_id,
            $quantity,
            $total_price,
            $delivery_method,
            $delivery_address
        ]);
    }

    // Commit transaction
    $pdo->commit();

    // Redirect back to products page
    header("Location: /GFLH/pages/products.php");
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log the error in real application
    header("Location: /GFLH/pages/products.php?error=unknown");
    exit;
}