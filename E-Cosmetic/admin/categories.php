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

// Handle category deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
  $category_id = intval($_GET['delete']);
  
  // Check if category has products
  $check_query = "SELECT COUNT(*) as product_count FROM products WHERE category_id = ?";
  $check_stmt = mysqli_prepare($conn, $check_query);
  mysqli_stmt_bind_param($check_stmt, "i", $category_id);
  mysqli_stmt_execute($check_stmt);
  $check_result = mysqli_stmt_get_result($check_stmt);
  $product_count = mysqli_fetch_assoc($check_result)['product_count'];
  
  if ($product_count > 0) {
    $error = "Cannot delete category. It has $product_count products associated with it.";
  } else {
    // Get category image to delete file
    $query = "SELECT image_url FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
      $image_url = $row['image_url'];
      
      // Delete category from database
      $delete_query = "DELETE FROM categories WHERE id = ?";
      $delete_stmt = mysqli_prepare($conn, $delete_query);
      mysqli_stmt_bind_param($delete_stmt, "i", $category_id);
      
      if (mysqli_stmt_execute($delete_stmt)) {
        // Delete image file if it exists and is not a default image
        if (!empty($image_url) && strpos($image_url, 'category-placeholder') === false) {
          $file_path = '../' . $image_url;
          if (file_exists($file_path)) {
            unlink($file_path);
          }
        }
        
        $success = "Category deleted successfully.";
      } else {
        $error = "Error deleting category.";
      }
    }
  }
}

// Get all categories
$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id 
          ORDER BY c.name ASC";
$result = mysqli_query($conn, $query);
$categories = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categories - Beauty Essentials Admin</title>
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
    .admin-actions {
      margin-bottom: 20px;
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
      text-decoration: none;
    }
    .btn:hover {
      background-color: #ff5252;
    }
    .btn-sm {
      padding: 5px 10px;
      font-size: 14px;
    }
    .btn-secondary {
      background-color: #4ecdc4;
    }
    .btn-secondary:hover {
      background-color: #3dbdb4;
    }
    .btn-danger {
      background-color: #ff6b6b;
    }
    .btn-danger:hover {
      background-color: #ff5252;
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
    .categories-table {
      width: 100%;
      border-collapse: collapse;
    }
    .categories-table th,
    .categories-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    .categories-table th {
      background-color: #f5f5f5;
      font-weight: 600;
    }
    .categories-table tr:hover {
      background-color: #f9f9f9;
    }
    .category-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 4px;
    }
    .category-actions {
      display: flex;
      gap: 5px;
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
      <h2>Categories</h2>
      
      <?php if (isset($error)): ?>
        <div class="error-message">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($success)): ?>
        <div class="success-message">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>
      
      <div class="admin-actions">
        <a href="add-category.php" class="btn">Add New Category</a>
      </div>
      
      <?php if (empty($categories)): ?>
        <p>No categories found.</p>
      <?php else: ?>
        <table class="categories-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Image</th>
              <th>Name</th>
              <th>Products</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $category): ?>
              <tr>
                <td><?php echo $category['id']; ?></td>
                <td>
                  <img 
                    src="../<?php echo $category['image_url']; ?>" 
                    alt="<?php echo htmlspecialchars($category['name']); ?>"
                    class="category-image"
                    onerror="this.src='https://via.placeholder.com/60x60.png?text=No+Image'"
                  >
                </td>
                <td><?php echo htmlspecialchars($category['name']); ?></td>
                <td><?php echo $category['product_count']; ?></td>
                <td class="category-actions">
                  <a href="edit-category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                  <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
