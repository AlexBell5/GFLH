<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up - GFLH</title>
  <link rel="stylesheet" href="../styles/style.css" />
  <link rel="stylesheet" href="../styles/navbar.css" />
</head>
<body>
  <script src="../scripts/settings.js"></script>
<?php include('../includes/navbar.php'); ?>
<!-- SIGN-UP SECTION -->
<main class="auth-page">
  <div class="auth-card">

    <header class="auth-header">
      <h1>Create Account</h1>
      <p>Sign up to get started with GFLH</p>
    </header>

    <!-- ✅ ERROR MESSAGES -->
    <?php if (isset($_GET['error'])): ?>
      <div class="auth-error">
        <?php
          if ($_GET['error'] === 'email_exists') {
            echo "An account with this email already exists.";
          } elseif ($_GET['error'] === 'password_mismatch') {
            echo "Passwords do not match.";
          } elseif ($_GET['error'] === 'invalid_role') {
            echo "Invalid role selected.";
          } else {
            echo "Something went wrong. Please try again.";
          }
        ?>
      </div>
    <?php endif; ?>

    <!-- SIGNUP FORM -->
    <form class="auth-form" action="../handlers/signup.php" method="post">

      <div class="form-group">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          placeholder="john_doe"
          required
        />
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input
          type="email"
          id="email"
          name="email"
          placeholder="you@example.com"
          required
        />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="••••••••"
          required
        />
      </div>

      <div class="form-group">
        <label for="confirm-password">Confirm Password</label>
        <input
          type="password"
          id="confirm-password"
          name="confirm-password"
          placeholder="••••••••"
          required
        />
      </div>

      <div class="form-group">
        <label>Account Type</label>
        <div class="role-options">
          <label class="role-label">
            <input type="radio" name="role" value="customer" checked />
            Customer
          </label>
          <label class="role-label">
            <input type="radio" name="role" value="farmer" />
            Farmer
          </label>
        </div>
      </div>

      <div class="form-options">
        <label class="remember-me">
          <input type="checkbox" required />
          I agree to the <a href="#">Terms & Conditions</a>
        </label>
      </div>

      <button type="submit" class="btn-primary auth-button">
        Sign Up
      </button>
    </form>

    <footer class="auth-footer">
      <p>
        Already have an account?
        <a href="login.php">Log in</a>
      </p>
    </footer>

  </div>
</main>

</body>
</html>
