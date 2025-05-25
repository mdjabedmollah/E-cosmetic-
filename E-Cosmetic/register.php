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
$success = false;

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  
  // Validate input
  if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    $error = "Please fill in all fields.";
  } elseif ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } elseif (strlen($password) < 6) {
    $error = "Password must be at least 6 characters long.";
  } else {
    // Check if email already exists
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
      $error = "Email already exists. Please use a different email or login.";
    } else {
      // Hash password
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      
      // Insert new user
      $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
      $stmt = mysqli_prepare($conn, $query);
      mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
      
      if (mysqli_stmt_execute($stmt)) {
        $success = true;
      } else {
        $error = "Registration failed. Please try again.";
      }
    }
  }
}

// Include header
include_once 'includes/header.php';
?>

<main class="container">
  <section class="auth-section">
    <div class="auth-container">
      <h1>Create an Account</h1>
      
      <?php if (!empty($error)): ?>
        <div class="error-message">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="success-message">
          Registration successful! You can now <a href="login.php">login</a>.
        </div>
      <?php else: ?>
        <form method="post" action="register.php" class="auth-form">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
          </div>
          
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
          </div>
          
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <small>Password must be at least 6 characters long.</small>
          </div>
          
          <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
          </div>
          
          <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
      <?php endif; ?>
      
      <div class="auth-links">
        <p>Already have an account? <a href="login.php">Login</a></p>
      </div>
    </div>
  </section>
</main>

<?php
// Include footer
include_once 'includes/footer.php';
?>
