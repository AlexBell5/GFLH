<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php:

$username = trim($data['username'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? null;
$userId   = $_SESSION['user_id'];

if ($username === '' || $email === '') {
    echo json_encode(['success' => false, 'error' => 'Username and email are required']);
    exit();
}


$host = 'localhost';
$db = 'GFLH';
$dbuser = 'root';
$dbpass = '';

$conn = new mysqli($host, $dbuser, $dbpass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}


$checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
$checkEmail->bind_param("si", $email, $userId);
$checkEmail->execute();
if ($checkEmail->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Email is already in use']);
    $checkEmail->close();
    $conn->close();
    exit();
}
$checkEmail->close();


if ($password && !empty($password)) {
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password_hash=? WHERE user_id=?");
    $stmt->bind_param("sssi", $username, $email, $passwordHash, $userId);
} else {
    
    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE user_id=?");
    $stmt->bind_param("ssi", $username, $email, $userId);
}

if ($stmt->execute()) {
    
    $_SESSION['username'] = $username;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update profile: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

