<?php
session_start();

// Redirect if not logged in or not a farmer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'farmer') {
    header("Location: ./pages/login.php");
    exit;
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Product - GFLH</title>
  <link rel="stylesheet" href="./styles/add-products.css" />
  <link rel="stylesheet" href="./styles/navbar.css" />

</head>
<body>
<?php include('./includes/navbar.php'); ?>

<main>
  <div class="add-product-container">
    <h1>Add New Product</h1>

    <!-- ❌ ERROR MESSAGES -->
    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error">
        <?php
          $errors = [
              'validation_failed' => 'Please fill in all required fields correctly.',
              'invalid_image_type' => 'Invalid image type. Please upload JPEG, PNG, GIF, or WebP.',
              'image_too_large' => 'Image is too large. Maximum size is 5MB.',
              'upload_failed' => 'Failed to upload image. Please try again.',
              'database_error' => 'Failed to add product. Please try again.'
          ];
          $error_code = $_GET['error'];
          echo htmlspecialchars($errors[$error_code] ?? 'An error occurred.');
        ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="add_product.php" enctype="multipart/form-data">
      <div class="form-group">
        <label for="product_name">Product Name *</label>
        <input 
          type="text" 
          id="product_name" 
          name="product_name" 
          placeholder="e.g., Organic Tomatoes" 
          required 
        />
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea 
          id="description" 
          name="description" 
          placeholder="Describe your product (quality, origin, preparation, etc.)"
        ></textarea>
      </div>

      <div class="form-group">
        <label for="price">Price (£) *</label>
        <input 
          type="number" 
          id="price" 
          name="price" 
          step="0.01" 
          min="0" 
          placeholder="0.00" 
          required 
        />
      </div>

      <div class="form-group">
        <label for="stock_quantity">Stock Quantity *</label>
        <input 
          type="number" 
          id="stock_quantity" 
          name="stock_quantity" 
          min="0" 
          placeholder="0" 
          required 
        />
      </div>

      <div class="form-group">
        <label for="product_image">Product Image (Max 5MB)</label>
        <input 
          type="file" 
          id="product_image" 
          name="product_image" 
          accept="image/*" 
          onchange="previewImage(event)"
        />
        <img id="image_preview" class="image-preview" alt="Preview" />
      </div>

      <div class="form-group">
        <label>Delivery & Pickup Options</label>
        <div class="checkbox-group">
          <label>
            <input type="checkbox" name="delivery_option" value="1" />
            Delivery Available
          </label>
          <label>
            <input type="checkbox" name="pickup_option" value="1" />
            Pickup Available
          </label>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-submit">Add Product</button>
        <a href="./pages/profile.php" style="text-decoration: none;">
          <button type="button" class="btn btn-cancel">Cancel</button>
        </a>
      </div>
    </form>
  </div>
</main>

<script>
  function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('image_preview');
    
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else {
      preview.style.display = 'none';
    }
  }
</script>
</body>
</html>
