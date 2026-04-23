<?php
/*
 * Handler for adding new products as a farmer
 * Validates input, processes image uploads, and inserts product with delivery/pickup options
 */
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ../pages/login.php");
    exit;
}


$conn = new mysqli('localhost', 'root', '', 'GFLH');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $farmer_id = $_SESSION['user_id'];

    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $delivery_option = isset($_POST['delivery_option']) ? 1 : 0;
    $pickup_option = isset($_POST['pickup_option']) ? 1 : 0;
    $address = trim($_POST['address']);

    
    if (
        empty($product_name) ||
        $price <= 0 ||
        $stock_quantity < 0 ||
        empty($address)
    ) {
        header("Location: ../add-product.php?error=validation_failed");
        exit;
    }

    
    if (!$delivery_option && !$pickup_option) {
        header("Location: ../add-product.php?error=validation_failed");
        exit;
    }

    
    $image_path = null;

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($_FILES['product_image']['type'], $allowed)) {
            header("Location: ../add-product.php?error=invalid_image_type");
            exit;
        }

        if ($_FILES['product_image']['size'] > 5 * 1024 * 1024) {
            header("Location: ../add-product.php?error=image_too_large");
            exit;
        }

        $dir = "../images/products/";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = time() . "_" . basename($_FILES['product_image']['name']);
        $path = $dir . $filename;

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $path)) {
            $image_path = "images/products/" . $filename;
        }
    }

    
    $stmt = $conn->prepare("
        INSERT INTO products 
        (farmer_id, product_name, description, price, stock_quantity, image_path, delivery_option, pickup_option, delivery_address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issdissis",
        $farmer_id,
        $product_name,
        $description,
        $price,
        $stock_quantity,
        $image_path,
        $delivery_option,
        $pickup_option,
        $address
    );

    if ($stmt->execute()) {
        header("Location: ../pages/profile.php?success=1");
    } else {
        header("Location: ../add-product.php?error=database_error");
    }

    $stmt->close();
}

$conn->close();
?>
