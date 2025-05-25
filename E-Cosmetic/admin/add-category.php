<?php
// Start session
session_start();
// Database connection
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit();
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $name = isset($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : '';
  $image_url = isset($_POST['image_url']) ? mysqli_real_escape_string($conn, $_POST['image_url']) : 'assets/images/category-placeholder.jpg';
  
  // Validate form data
  if (empty($name)) {
    $error = "Category name is required.";
  } else {
    // Check if category already exists
    $check_query = "SELECT id FROM categories WHERE name = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $name);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
      $error = "A category with this name already exists.";
    } else {
      // Handle image upload if provided
      if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_type = $_FILES['image']['type'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_type, $allowed_types)) {
          $error = "Invalid file type. Only JPG, PNG, GIF, and WEBP files are allowed.";
        } elseif ($file_size > $max_size) {
          $error = "File size is too large. Maximum size is 5MB.";
        } else {
          // Create unique filename
          $new_file_name = uniqid('category_') . '.' . $file_ext;
          $upload_dir = '../assets/images/';
          $upload_path = $upload_dir . $new_file_name;
          
          // Check if directory exists, if not create it
          if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
              $error = "Failed to create directory. Please check server permissions.";
            }
          }
          
          // Move uploaded file
          if (move_uploaded_file($file_tmp, $upload_path)) {
            $image_url = 'assets/images/' . $new_file_name;
          } else {
            $error = "Error uploading file. Please try again.";
          }
        }
      }
      
      // If no errors, insert category into database
      if (empty($error)) {
        $query = "INSERT INTO categories (name, image_url) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $name, $image_url);
        
        if (mysqli_stmt_execute($stmt)) {
          $success = "Category added successfully!";
          // Clear form data after successful submission
          $name = $image_url = '';
        } else {
          $error = "Error adding category: " . mysqli_error($conn);
        }
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Category - Beauty Essentials Admin</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <style>
    /* Admin styles */
    body {
      background-color: #f8f9fa;
    }
    .admin-header {
      background-color: #ff6b6b;
      color: white;
      padding: 15px 0;
      margin-bottom: 30px;
    }
    .admin-header h1 {
      margin: 0;
      font-size: 24px;
    }
    .admin-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    .admin-nav {
      display: flex;
      background-color: white;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .admin-nav a {
      padding: 15px 20px;
      color: #333;
      text-decoration: none;
      font-weight: 500;
    }
    .admin-nav a:hover {
      background-color: #f5f5f5;
    }
    .admin-card {
      background-color: white;
      border-radius: 8px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .admin-card h2 {
      margin-top: 0;
      margin-bottom: 20px;
      color: #333;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }
    .form-group input[type="text"],
    .form-group textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
    }
    .btn {
      display: inline-block;
      padding: 10px 20px;
      background-color: #ff6b6b;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 500;
    }
    .btn:hover {
      background-color: #ff5252;
    }
    .btn-secondary {
      background-color: #6c757d;
    }
    .btn-secondary:hover {
      background-color: #5a6268;
    }
    .error-message {
      background-color: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .success-message {
      background-color: #d4edda;
      color: #155724;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <header class="admin-header">
    <div class="admin-container">
      <h1>Beauty Essentials Admin</h1>
    </div>
  </header>

  <div class="admin-container">
    <nav class="admin-nav">
      <a href="dashboard.php">Dashboard</a>
      <a href="products.php">Products</a>
      <a href="categories.php">Categories</a>
      <a href="orders.php">Orders</a>
      <a href="users.php">Users</a>
      <a href="../index.php">View Store</a>
    </nav>

    <div class="admin-card">
      <h2>Add New Category</h2>
      
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
      
      <form method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="name">Category Name</label>
          <input type="text" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
        </div>
        
        <div class="form-group">
          <label for="image">Category Image</label>
          <input type="file" id="image" name="image" accept="image/*">
          <small>Allowed file types: JPG, PNG, GIF, WEBP. Maximum size: 5MB.</small>
        </div>
        
        <div class="form-group">
          <label for="image_url">Or Image URL</label>
          <input type="text" id="image_url" name="image_url" value="<?php echo isset($image_url) ? htmlspecialchars($image_url) : ''; ?>" placeholder="e.g., assets/images/category-1.jpg">
          <small>Leave empty if uploading an image file.</small>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn">Add Category</button>
          <a href="categories.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
