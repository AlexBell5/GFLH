<?php
/*
 * Handler for updating product price and stock
 * Validates farmer authorization and updates product attributes
 */
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../pages/login.php");
    exit;
}

$farmer_id = $_SESSION['user_id'];

$host = 'localhost';
$db = 'GFLH';
$dbuser = 'root';
$pass = '';

$conn = new mysqli($host, $dbuser, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);

    
    if ($price <= 0 || $stock_quantity < 0) {
        header("Location: ../pages/manage_products.php?error=invalid_values");
        exit;
    }

    
    $verify = $conn->prepare("SELECT product_id FROM products WHERE product_id = ? AND farmer_id = ?");
    $verify->bind_param("ii", $product_id, $farmer_id);
    $verify->execute();
    $verify->store_result();

    if ($verify->num_rows === 0) {
        header("Location: ../pages/manage_products.php?error=unauthorized");
        exit;
    }
    $verify->close();

    
    $stmt = $conn->prepare("
        UPDATE products 
        SET price = ?, stock_quantity = ? 
        WHERE product_id = ? AND farmer_id = ?
    ");
    $stmt->bind_param("diii", $price, $stock_quantity, $product_id, $farmer_id);

    if ($stmt->execute()) {
        header("Location: ../pages/manage_products.php?success=product_updated");
        exit;
    } else {
        header("Location: ../pages/manage_products.php?error=update_failed");
        exit;
    }

    $stmt->close();
}

$conn->close();
?>

