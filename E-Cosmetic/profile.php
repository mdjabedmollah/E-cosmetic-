<?php
// Start session
session_start();
// Database connection
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user information
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');
  
  if (empty($name) || empty($email)) {
    $error = "Name and email are required.";
  } else {
    // Check if email already exists for another user
    $query = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
      $error = "Email already exists. Please use a different email.";
    } else {
      // Update user information
      $query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
      $stmt = mysqli_prepare($conn, $query);
      mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $phone, $address, $user_id);
      
      if (mysqli_stmt_execute($stmt)) {
        // Update session variables
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        
        $success = "Profile updated successfully.";
        
        // Refresh user data
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
      } else {
        $error = "Failed to update profile. Please try again.";
      }
    }
  }
}

// Process password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  
  if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $error = "All password fields are required.";
  } elseif ($new_password !== $confirm_password) {
    $error = "New passwords do not match.";
  } elseif (strlen($new_password) < 6) {
    $error = "New password must be at least 6 characters long.";
  } else {
    // Verify current password
    if (password_verify($current_password, $user['password'])) {
      // Hash new password
      $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
      
      // Update password
      $query = "UPDATE users SET password = ? WHERE id = ?";
      $stmt = mysqli_prepare($conn, $query);
      mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
      
      if (mysqli_stmt_execute($stmt)) {
        $success = "Password changed successfully.";
      } else {
        $error = "Failed to change password. Please try again.";
      }
    } else {
      $error = "Current password is incorrect.";
    }
  }
}

// Get user orders
$query = "SELECT * FROM orders WHERE customer_email = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $user['email']);
mysqli_stmt_execute($stmt);
$orders_result = mysqli_stmt_get_result($stmt);

// Include header
include_once 'includes/header.php';
?>

<main class="container">
  <section class="profile-section">
    <h1>My Account</h1>
    
    <?php if (!empty($error)): ?>
      <div class="error-message">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
      <div class="success-message">
        <?php echo $success; ?>
      </div>
    <?php endif; ?>
    
    <div class="profile-container">
      <div class="profile-sidebar">
        <div class="profile-menu">
          <a href="#profile" class="profile-menu-item active" data-target="profile-info">Profile Information</a>
          <a href="#password" class="profile-menu-item" data-target="change-password">Change Password</a>
          <a href="#orders" class="profile-menu-item" data-target="order-history">Order History</a>
        </div>
      </div>
      
      <div class="profile-content">
        <div id="profile-info" class="profile-tab active">
          <h2>Profile Information</h2>
          <form method="post" action="profile.php" class="profile-form">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
              <label for="address">Address</label>
              <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
          </form>
        </div>
        
        <div id="change-password" class="profile-tab">
          <h2>Change Password</h2>
          <form method="post" action="profile.php" class="profile-form">
            <div class="form-group">
              <label for="current_password">Current Password</label>
              <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
              <label for="new_password">New Password</label>
              <input type="password" id="new_password" name="new_password" required>
              <small>Password must be at least 6 characters long.</small>
            </div>
            
            <div class="form-group">
              <label for="confirm_password">Confirm New Password</label>
              <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
          </form>
        </div>
        
        <div id="order-history" class="profile-tab">
          <h2>Order History</h2>
          
          <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <div class="order-list">
              <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                <div class="order-item">
                  <div class="order-header">
                    <div class="order-info">
                      <span class="order-id">Order #<?php echo $order['id']; ?></span>
                      <span class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="order-status <?php echo strtolower($order['status']); ?>">
                      <?php echo ucfirst($order['status']); ?>
                    </div>
                  </div>
                  
                  <div class="order-details">
                    <div class="order-address">
                      <strong>Shipping Address:</strong>
                      <p><?php echo htmlspecialchars($order['address']); ?>, <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['zip']); ?></p>
                    </div>
                    
                    <div class="order-total">
                      <strong>Total:</strong>
                      <span>$<?php echo number_format($order['total'], 2); ?></span>
                    </div>
                  </div>
                  
                  <?php
                  // Get order items
                  $query = "SELECT oi.*, p.name, p.image_url FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = ?";
                  $stmt = mysqli_prepare($conn, $query);
                  mysqli_stmt_bind_param($stmt, "i", $order['id']);
                  mysqli_stmt_execute($stmt);
                  $items_result = mysqli_stmt_get_result($stmt);
                  ?>
                  
                  <div class="order-items">
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                      <div class="order-product">
                        <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>">
                        <div class="order-product-info">
                          <h4><?php echo $item['name']; ?></h4>
                          <p>Quantity: <?php echo $item['quantity']; ?></p>
                          <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                      </div>
                    <?php endwhile; ?>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <p class="no-orders">You haven't placed any orders yet.</p>
            <a href="products.php" class="btn btn-primary">Start Shopping</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
  // Profile tab navigation
  document.addEventListener('DOMContentLoaded', function() {
    const menuItems = document.querySelectorAll('.profile-menu-item');
    const tabs = document.querySelectorAll('.profile-tab');
    
    menuItems.forEach(item => {
      item.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all menu items and tabs
        menuItems.forEach(i => i.classList.remove('active'));
        tabs.forEach(t => t.classList.remove('active'));
        
        // Add active class to clicked menu item
        this.classList.add('active');
        
        // Show corresponding tab
        const targetId = this.getAttribute('data-target');
        document.getElementById(targetId).classList.add('active');
      });
    });
    
    // Check if URL has hash and activate corresponding tab
    if (window.location.hash) {
      const hash = window.location.hash.substring(1);
      const menuItem = document.querySelector(`.profile-menu-item[href="#${hash}"]`);
      if (menuItem) {
        menuItem.click();
      }
    }
  });
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?>
