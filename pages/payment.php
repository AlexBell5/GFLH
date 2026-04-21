<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <link rel="stylesheet" href="../styles/payment.css">
    <link rel="stylesheet" href="../styles/navbar.css">
</head>
<body>
    <script src="../scripts/settings.js"></script>
<?php include('../includes/navbar.php'); ?>

<h2 style="text-align:center;">Enter Payment Details</h2>

<div class="payment-container">
    <h2>Enter Payment Details</h2>

    <form method="POST" action="../handlers/checkout.php">
        
        <label>Card Number</label>
        <input type="text" name="card_number" required>

        <label>Expiry (MM/YY)</label>
        <input type="text" name="expiry" required>

        <label>CVV</label>
        <input type="text" name="cvv" required>

        <button type="submit">Complete Order</button>

    </form>
</div>

</body>
</html>