<?php
session_start();

// Redirect if not logged in or not a farmer
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
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $delivery_option = isset($_POST['delivery_option']) ? 1 : 0;
    $pickup_option = isset($_POST['pickup_option']) ? 1 : 0;

    // Validation
    if (empty($product_name) || $price <= 0 || $stock_quantity < 0) {
        header("Location: ../add-product.php?error=validation_failed");
        exit;
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            header("Location: ../add-product.php?error=invalid_image_type");
            exit;
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            header("Location: ../add-product.php?error=image_too_large");
            exit;
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = realpath(__DIR__ . '/../images/products/');
        if (!$upload_dir) {
            mkdir(__DIR__ . '/../images/products/', 0755, true);
            $upload_dir = realpath(__DIR__ . '/../images/products/');
        }

        // Generate unique filename
        $filename = 'product_' . $farmer_id . '_' . time() . '_' . basename($file['name']);
        $upload_path = $upload_dir . DIRECTORY_SEPARATOR . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $image_path = 'images/products/' . $filename;
        } else {
            header("Location: ../add-product.php?error=upload_failed");
            exit;
        }
    }

    // Insert product into database
    $stmt = $conn->prepare("
        INSERT INTO products 
        (farmer_id, product_name, description, price, stock_quantity, image_path, delivery_option, pickup_option) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issdisii",
        $farmer_id,
        $product_name,
        $description,
        $price,
        $stock_quantity,
        $image_path,
        $delivery_option,
        $pickup_option
    );

    if ($stmt->execute()) {
        header("Location: ../pages/profile.php?success=product_added");
        exit;
    } else {
        header("Location: ../add-product.php?error=database_error");
        exit;
    }

    $stmt->close();
} else {
    header("Location: ../add-product.php");
    exit;
}

$conn->close();
?>
