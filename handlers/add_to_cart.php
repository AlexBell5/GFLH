<?php
/*
 * AJAX handler for adding products to shopping cart
 * Validates stock availability, prevents farmers from buying own products, updates cart quantity
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Login required']);
    exit;
}

$customer_id = (int)$_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1,(int)($_POST['quantity'] ?? 1));

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT price, stock_quantity, farmer_id FROM products WHERE product_id=? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) { $pdo->rollBack(); echo json_encode(['status'=>'error','message'=>'Product not found']); exit; }

    if (isset($_SESSION['role']) && $_SESSION['role']==='farmer' && $product['farmer_id']==$customer_id) {
        $pdo->rollBack();
        echo json_encode(['status'=>'error','message'=>'Cannot buy your own product']);
        exit;
    }

    if ($quantity > $product['stock_quantity']) $quantity = $product['stock_quantity'];
    $total_price = $product['price'] * $quantity;

    $stmt = $pdo->prepare("SELECT order_id, quantity FROM orders WHERE customer_id=? AND product_id=? AND order_status='basket' FOR UPDATE");
    $stmt->execute([$customer_id,$product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
        $stmt = $pdo->prepare("UPDATE orders SET quantity=?, total_price=? WHERE order_id=?");
        $stmt->execute([$newQty,$product['price']*$newQty,$existing['order_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, product_id, quantity, total_price, order_status) VALUES (?,?,?,?, 'basket')");
        $stmt->execute([$customer_id,$product_id,$quantity,$total_price]);
    }

    
    $stmt = $pdo->prepare("SELECT SUM(quantity) as cart_count FROM orders WHERE customer_id=? AND order_status='basket'");
    $stmt->execute([$customer_id]);
    $cart_count = (int)$stmt->fetchColumn();

    $pdo->commit();

    echo json_encode(['status'=>'success','message'=>'Added to cart','cart_count'=>$cart_count]);
    exit;

} catch(Exception $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status'=>'error','message'=>'Failed to add to cart: '.$e->getMessage()]);
    exit;
}
?>
