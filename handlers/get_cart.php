<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo '<p>Please log in to view your cart.</p>';
    exit;
}

$customer_id = (int)$_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=GFLH", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT o.order_id, o.quantity, o.total_price, p.product_name
        FROM orders o
        JOIN products p ON o.product_id = p.product_id
        WHERE o.customer_id = ? AND o.order_status = 'basket'
    ");
    $stmt->execute([$customer_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        echo '<p>Your basket is empty.</p>';
        exit;
    }

    $total = 0;
    foreach ($items as $item) {
        $total += $item['total_price'];
        echo '<div class="cart-item">
                <div>
                    <strong>' . htmlspecialchars($item['product_name']) . '</strong>
                    <p>Qty: ' . $item['quantity'] . '</p>
                </div>
                <div>£' . number_format($item['total_price'], 2) . '</div>
              </div>';
    }

    echo '<div class="cart-total"><strong>Total: £' . number_format($total, 2) . '</strong></div>';

} catch (Exception $e) {
    echo '<p>Error loading cart.</p>';
}
?>