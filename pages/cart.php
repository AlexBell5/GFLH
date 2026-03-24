<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shopping Cart - GFLH</title>
  <link rel="stylesheet" href="../styles/style.css" />
  <link rel="stylesheet" href="../styles/navbar.css" />
</head>
<body>
<?php include('../includes/navbar.php'); ?>
<main>
  <h1>Shopping Cart</h1>
  
  <?php if (isset($_GET['error'])): ?>
    <div style="background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fecaca;">
      <?php if ($_GET['error'] === 'empty_cart'): ?>
        <strong>Error:</strong> Your cart is empty. Add some products before checking out.
      <?php elseif ($_GET['error'] === 'own_product'): ?>
        <strong>Error:</strong> You cannot purchase your own products. Farmers are not allowed to buy products from their own farm.
      <?php else: ?>
        <strong>Error:</strong> An unknown error occurred.
      <?php endif; ?>
    </div>
  <?php endif; ?>
  
  <p>View and manage your shopping cart.</p>
</main>
</body>
</html>
