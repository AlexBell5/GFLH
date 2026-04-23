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
    <input type="text" name="card_number" id="card_number" required 
           pattern="\d{16}" maxlength="16" placeholder="1234123412341234">

    <label>Expiry (MM/YY)</label>
    <input type="text" name="expiry" id="expiry" required 
           pattern="^(0[1-9]|1[0-2])\/\d{2}$" placeholder="MM/YY">

    <label>CVV</label>
    <input type="text" name="cvv" id="cvv" required 
           pattern="\d{3,4}" maxlength="4" placeholder="123">

    <button type="submit">Complete Order</button>
    </form>
</div>

</body>
</html>
