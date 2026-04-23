<?php
/*
 * User profile page with role-specific content
 * Shows account info, farmer products/dashboard or customer order information
 */
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}


$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];


$host = 'localhost';
$db = 'GFLH';
$dbuser = 'root';
$pass = '';

$conn = new mysqli($host, $dbuser, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


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
  <script src="../scripts/settings.js"></script>
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
        
        <?php
        
        $sales_stmt = $conn->prepare("
            SELECT 
                SUM(o.total_price) as total_sales,
                COUNT(DISTINCT o.order_id) as total_orders,
                COUNT(DISTINCT o.customer_id) as total_customers
            FROM orders o
            JOIN products p ON o.product_id = p.product_id
            WHERE p.farmer_id = ? AND o.order_status = 'completed'
        ");
        $sales_stmt->bind_param("i", $user_id);
        $sales_stmt->execute();
        $sales_stats = $sales_stmt->get_result()->fetch_assoc();
        $sales_stmt->close();
        
        
        $customer_sales_stmt = $conn->prepare("
            SELECT 
                u.username as customer_name,
                u.email as customer_email,
                SUM(o.total_price) as total_spent,
                COUNT(DISTINCT o.order_id) as orders_count,
                MAX(o.order_date) as last_order_date
            FROM orders o
            JOIN products p ON o.product_id = p.product_id
            JOIN users u ON o.customer_id = u.user_id
            WHERE p.farmer_id = ? AND o.order_status = 'completed'
            GROUP BY o.customer_id, u.username, u.email
            ORDER BY total_spent DESC
        ");
        $customer_sales_stmt->bind_param("i", $user_id);
        $customer_sales_stmt->execute();
        $customer_sales_result = $customer_sales_stmt->get_result();
        $customer_sales = [];
        while ($row = $customer_sales_result->fetch_assoc()) {
            $customer_sales[] = $row;
        }
        $customer_sales_stmt->close();
        
        
        $recent_orders_stmt = $conn->prepare("
            SELECT 
                o.order_id,
                o.order_date,
                o.total_price,
                o.quantity,
                p.product_name,
                u.username as customer_name
            FROM orders o
            JOIN products p ON o.product_id = p.product_id
            JOIN users u ON o.customer_id = u.user_id
            WHERE p.farmer_id = ? AND o.order_status = 'completed'
            ORDER BY o.order_date DESC
            LIMIT 10
        ");
        $recent_orders_stmt->bind_param("i", $user_id);
        $recent_orders_stmt->execute();
        $recent_orders_result = $recent_orders_stmt->get_result();
        $recent_orders = [];
        while ($row = $recent_orders_result->fetch_assoc()) {
            $recent_orders[] = $row;
        }
        $recent_orders_stmt->close();
        ?>
        
        <!-- Sales Summary -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
          <div>
            <h3 style="margin: 0 0 10px 0; color: #333;">Total Sales</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: #16a34a;">
              £<?php echo number_format($sales_stats['total_sales'] ?? 0, 2); ?>
            </p>
          </div>
          <div>
            <h3 style="margin: 0 0 10px 0; color: #333;">Total Orders</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: #16a34a;">
              <?php echo $sales_stats['total_orders'] ?? 0; ?>
            </p>
          </div>
          <div>
            <h3 style="margin: 0 0 10px 0; color: #333;">Total Customers</h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: #16a34a;">
              <?php echo $sales_stats['total_customers'] ?? 0; ?>
            </p>
          </div>
        </div>
        
        <!-- Sales by Customer -->
        <?php if (!empty($customer_sales)): ?>
        <div style="margin-bottom: 30px;">
          <h3>Top Customers</h3>
          <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
              <thead style="background: #f9fafb;">
                <tr>
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Customer</th>
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Email</th>
                  <th style="padding: 12px; text-align: right; border-bottom: 1px solid #e5e7eb;">Orders</th>
                  <th style="padding: 12px; text-align: right; border-bottom: 1px solid #e5e7eb;">Total Spent</th>
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb;">Last Order</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($customer_sales as $customer): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                  <td style="padding: 12px;"><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                  <td style="padding: 12px;"><?php echo htmlspecialchars($customer['customer_email']); ?></td>
                  <td style="padding: 12px; text-align: right;"><?php echo $customer['orders_count']; ?></td>
                  <td style="padding: 12px; text-align: right; font-weight: bold; color: #16a34a;">
                    £<?php echo number_format($customer['total_spent'], 2); ?>
                  </td>
                  <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($customer['last_order_date'])); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>
        
        <!-- Recent Orders -->
        <?php if (!empty($recent_orders)): ?>
        <div>
          <h3>Recent Sales</h3>
          <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
              <thead style="background: 
                <tr>
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid 
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid 
                  <th style="padding: 12px; text-align: left; border-bottom: 1px solid 
                  <th style="padding: 12px; text-align: right; border-bottom: 1px solid 
                  <th style="padding: 12px; text-align: right; border-bottom: 1px solid 
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_orders as $order): ?>
                <tr style="border-bottom: 1px solid 
                  <td style="padding: 12px;"><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                  <td style="padding: 12px;"><?php echo htmlspecialchars($order['product_name']); ?></td>
                  <td style="padding: 12px;"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                  <td style="padding: 12px; text-align: right;"><?php echo $order['quantity']; ?></td>
                  <td style="padding: 12px; text-align: right; font-weight: bold; color: 
                    £<?php echo number_format($order['total_price'], 2); ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px;">
          <h3 style="color: #333; margin-bottom: 10px;">No Sales Yet</h3>
          <p style="color: #666; margin-bottom: 20px;">Start by adding your first product to begin selling.</p>
          <a href="/GFLH/handlers/add-product.php" style="display: inline-block; background: #16a34a; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Add Your First Product</a>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($role === 'customer'): ?>
      <section>
        <h2>Your Orders</h2>
        <p>
          <a href="/GFLH/pages/order_history.php" style="margin-right: 15px;">📦 View Order History</a> | 
          <a href="products.php">Browse Products</a>
        </p>
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
        <button onclick="toggleEditForm()">✏️ Edit Profile</button>
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

