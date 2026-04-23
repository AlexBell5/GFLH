<?php
/*
 * User login form page
 * Displays login interface with email/password fields and links to signup
 */
session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>log In - GFLH</title>
  <link rel="stylesheet" href="../styles/login.css" />
  <link rel="stylesheet" href="../styles/navbar.css" />
</head>
<body>
  <script src="../scripts/settings.js"></script>
<?php include('../includes/navbar.php'); ?>
<!-- LOGIN SECTION -->
  <main class="auth-page">
    <div class="auth-card">

      <header class="auth-header">
        <h1>Welcome Back</h1>
        <p>Please log in to your account</p>
      </header>

      <!-- ✅ SUCCESS MESSAGES -->
      <?php if (isset($_GET['success'])): ?>
        <div class="auth-success">
          <?php
            if ($_GET['success'] === 'account_created') {
              echo "Account created successfully! Please log in.";
            } else {
              echo "Success! Please log in.";
            }
          ?>
        </div>
      <?php endif; ?>

      <!-- ❌ ERROR MESSAGES -->
      <?php if (isset($_GET['error'])): ?>
        <div class="auth-error">
          <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
      <?php endif; ?>

<form class="auth-form" action="../handlers/login.php" method="post">
    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required />
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required />
    </div>
    <button type="submit" id = "loginBtn" class="btn-primary auth-button">Log In</button>
</form>

      <footer class="auth-footer">
        <p>
          Don't have an account?
          <a href="signup.php">Sign up</a>
        </p>
      </footer>

    </div>
  </main>

</body>
</html>
