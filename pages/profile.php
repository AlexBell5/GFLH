<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Get user info from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Database connection
$host = 'localhost';
$db = 'GFLH';
$dbuser = 'root';
$pass = '';

$conn = new mysqli($host, $dbuser, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch additional user data
$stmt = $conn->prepare("SELECT email, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo ucfirst($role); ?> Profile - GFLH</title>
  <link rel="stylesheet" href="../styles/style.css" />
  <link rel="stylesheet" href="../styles/navbar.css" />
  <link rel="stylesheet" href="../styles/profile.css" />
</head>
<body>
<?php include('../includes/navbar.php'); ?>
  <main>

    <!-- PROFILE HEADER -->
    <div>
      <h1><?php echo htmlspecialchars($username); ?> (<?php echo ucfirst($role); ?>)</h1>
    </div>

    <!-- COMMON SECTION: ACCOUNT INFORMATION -->
    <section>
      <h2>Account Information</h2>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
      <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
      <p><strong>Account Type:</strong> <?php echo ucfirst($role); ?></p>
    </section>

    <?php if ($role === 'farmer'): ?>
      <!-- FARMER-SPECIFIC CONTENT -->

      <!-- FARMER SECTION: YOUR PRODUCTS -->
      <section>
        <h2>Your Products</h2>
        <?php
          // Fetch farmer's products
          $products_stmt = $conn->prepare(
              "SELECT product_id, product_name, description, price, stock_quantity FROM products WHERE farmer_id = ? ORDER BY created_at DESC"
          );
          $products_stmt->bind_param("i", $user_id);
          $products_stmt->execute();
          $products_result = $products_stmt->get_result();
          $products_count = $products_result->num_rows;

          if ($products_count > 0):
        ?>
          <p>You have <strong><?php echo $products_count; ?></strong> product(s) listed.</p>
        <?php else: ?>
          <p>You haven't listed any products yet.</p>
        <?php endif; ?>
        
        <p>
          <a href="/GFLH/pages/manage_products.php" style="margin-right: 15px;">📋 Manage Products</a> | 
          <a href="/GFLH/handlers/add-product.php">+ Add New Product</a>
        </p>
      </section>

      <!-- FARMER SECTION: SALES DASHBOARD -->
      <section>
        <h2>Sales Dashboard</h2>
        <p>Sales dashboard coming soon.</p>
      </section>

    <?php elseif ($role === 'customer'): ?>
      <!-- CUSTOMER-SPECIFIC CONTENT -->

      <!-- CUSTOMER SECTION: YOUR ORDERS -->
      <section>
        <h2>Your Orders</h2>
        <p>
          <a href="/GFLH/pages/order_history.php" style="margin-right: 15px;">📦 View Order History</a> | 
          <a href="products.php">Browse Products</a>
        </p>
      </section>

      <!-- CUSTOMER SECTION: SAVED ITEMS -->
      <section>
        <h2>Favorite Farmers & Products</h2>
        <p>No saved items yet.</p>
      </section>

    <?php endif; ?>

    <!-- COMMON SECTION: EDIT PROFILE -->
    <section id="editProfileSection" style="display:none;">
      <h2>Edit Profile</h2>
      <form id="editProfileForm">
        <div>
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        <div>
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div>
          <label for="newPassword">New Password (leave blank to keep current):</label>
          <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password">
        </div>
        <div>
          <button type="submit">Save Changes</button>
          <button type="button" onclick="cancelEdit()">Cancel</button>
        </div>
        <div id="updateMessage" style="margin-top: 10px; display:none;"></div>
      </form>
    </section>

    <!-- COMMON SECTION: ACTIONS -->
    <section>
      <h2>Account Settings</h2>
      <p>
        <button onclick="toggleEditForm()" style="cursor:pointer; padding:8px 12px; background:#007bff; color:white; border:none; border-radius:4px;">Edit Profile</button> | 
        <a href="../handlers/logout.php">Logout</a>
      </p>
    </section>

  </main>

  <script>
    function toggleEditForm() {
      const section = document.getElementById('editProfileSection');
      section.style.display = section.style.display === 'none' ? 'block' : 'none';
    }

    function cancelEdit() {
      document.getElementById('editProfileSection').style.display = 'none';
      document.getElementById('editProfileForm').reset();
    }

    document.getElementById('editProfileForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const username = document.getElementById('username').value.trim();
      const email = document.getElementById('email').value.trim();
      const newPassword = document.getElementById('newPassword').value;

      if (username === '' || email === '') {
        showMessage('Username and email are required', 'error');
        return;
      }

      const data = {
        username: username,
        email: email,
        password: newPassword || null
      };

      fetch('../handlers/update_profile.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          showMessage('Profile updated successfully!', 'success');
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          showMessage(result.error || 'Failed to update profile', 'error');
        }
      })
      .catch(error => {
        showMessage('Error: ' + error.message, 'error');
      });
    });

    function showMessage(msg, type) {
      const messageDiv = document.getElementById('updateMessage');
      messageDiv.textContent = msg;
      messageDiv.style.display = 'block';
      messageDiv.style.color = type === 'success' ? 'green' : 'red';
    }
  </script>
</body>
</html>

<?php
$conn->close();
?>
