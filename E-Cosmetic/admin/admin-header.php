<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Beauty Essentials</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
  <header class="admin-header">
    <div class="container">
      <div class="header-content">
        <div class="logo">
          <a href="dashboard.php">Beauty Essentials Admin</a>
        </div>
        <div class="header-actions">
          <div class="user-menu">
            <a href="#" class="account-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </a>
            <div class="user-dropdown">
              <a href="../profile.php">My Account</a>
              <a href="../index.php">View Store</a>
              <a href="../logout.php">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
