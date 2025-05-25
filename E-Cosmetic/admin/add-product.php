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

// Get all categories for the dropdown
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);
$categories = [];
if ($categories_result) {
  while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row;
  }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $name = isset($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : '';
  $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
  $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
  $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
  $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
  $featured = isset($_POST['featured']) ? 1 : 0;
  $image_url = isset($_POST['image_url']) ? mysqli_real_escape_string($conn, $_POST['image_url']) : 'assets/images/product-placeholder.jpg';
  
  // Validate form data
  if (empty($name)) {
    $error = "Product name is required.";
  } elseif (empty($description)) {
    $error = "Product description is required.";
  } elseif ($price <= 0) {
    $error = "Price must be greater than zero.";
  } elseif ($category_id <= 0) {
    $error = "Please select a category.";
  } elseif ($stock < 0) {
    $error = "Stock cannot be negative.";
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
        $new_file_name = uniqid('product_') . '.' . $file_ext;
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
    
    // If no errors, insert product into database
    if (empty($error)) {
      $query = "INSERT INTO products (name, description, price, category_id, stock, featured, image_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
      $stmt = mysqli_prepare($conn, $query);
      mysqli_stmt_bind_param($stmt, "ssdiiss", $name, $description, $price, $category_id, $stock, $featured, $image_url);
      
      if (mysqli_stmt_execute($stmt)) {
        $success = "Product added successfully!";
        // Clear form data after successful submission
        $name = $description = $image_url = '';
        $price = $stock = 0;
        $category_id = 0;
        $featured = 0;
      } else {
        $error = "Error adding product: " . mysqli_error($conn);
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
  <title>Add New Product - Beauty Essentials Admin</title>
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
    .form-group input[type="number"],
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
    }
    .form-group textarea {
      height: 150px;
      resize: vertical;
    }
    .form-group .checkbox-label {
      display: flex;
      align-items: center;
      font-weight: normal;
    }
    .form-group .checkbox-label input {
      margin-right: 10px;
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
      <h2>Add New Product</h2>
      
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
          <label for="name">Product Name</label>
          <input type="text" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
        </div>
        
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
          <label for="price">Price ($)</label>
          <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?php echo isset($price) ? $price : ''; ?>" required>
        </div>
        
        <div class="form-group">
          <label for="category_id">Category</label>
          <select id="category_id" name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo $category['id']; ?>" <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($category['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label for="stock">Stock Quantity</label>
          <input type="number" id="stock" name="stock" min="0" value="<?php echo isset($stock) ? $stock : '10'; ?>">
        </div>
        
        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" name="featured" <?php echo (isset($featured) && $featured) ? 'checked' : ''; ?>>
            Featured Product
          </label>
        </div>
        
        <div class="form-group">
          <label for="image">Product Image</label>
          <input type="file" id="image" name="image" accept="image/*">
          <small>Allowed file types: JPG, PNG, GIF, WEBP. Maximum size: 5MB.</small>
        </div>
        
        <div class="form-group">
          <label for="image_url">Or Image URL</label>
          <input type="text" id="image_url" name="image_url" value="<?php echo isset($image_url) ? htmlspecialchars($image_url) : ''; ?>" placeholder="e.g., assets/images/product-1.jpg">
          <small>Leave empty if uploading an image file.</small>
        </div>
        
        <div class="form-actions">
          <button type="submit" class="btn">Add Product</button>
          <a href="products.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
