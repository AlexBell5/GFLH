<?php
// handlers/signup.php
session_start();

$host = 'localhost';
$db   = 'GFLH';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm-password'];
    $role     = $_POST['role'] ?? 'customer';

    // Validate role
    if (!in_array($role, ['customer', 'farmer'])) {
        header("Location: ../pages/signup.php?error=invalid_role");
        exit;
    }

    // Password mismatch
    if ($password !== $confirm) {
        header("Location: ../pages/signup.php?error=password_mismatch");
        exit;
    }

    // ✅ Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Email already registered
        header("Location: ../pages/signup.php?error=email_exists");
        exit;
    }
    $check->close();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $username, $email, $password_hash, $role);

    if ($stmt->execute()) {
        header("Location: ../pages/login.php?success=account_created");
        exit;
    } else {
        header("Location: ../pages/signup.php?error=unknown");
        exit;
    }
}
