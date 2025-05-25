<?php
// Start session
session_start();
// Database connection
require_once 'config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  
  if (empty($email) || empty($password)) {
    $error = "Please enter both email and password.";
  } else {
    // Get user from database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 1) {
      $user = mysqli_fetch_assoc($result);
      
      // Verify password
      if (password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        
        // Redirect to home page
        header("Location: index.php");
        exit();
      } else {
        $error = "Invalid email or password.";
      }
    } else {
      $error = "Invalid email or password.";
    }
  }
}

// Include header
include_once 'includes/header.php';
?>

<main class="container">
  <section class="auth-section">
    <div class="auth-container">
      <h1>Login</h1>
      
      <?php if (!empty($error)): ?>
        <div class="error-message">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <form method="post" action="login.php" class="auth-form">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Login</button>
      </form>
      
      <div class="auth-links">
        <p>Don't have an account? <a href="register.php">Register</a></p>
      </div>
    </div>
  </section>
</main>

<?php
// Include footer
include_once 'includes/footer.php';
?>
