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

// Get statistics
// Total products
$query = "SELECT COUNT(*) as total FROM products";
$result = mysqli_query($conn, $query);
$total_products = mysqli_fetch_assoc($result)['total'];

// Total categories
$query = "SELECT COUNT(*) as total FROM categories";
$result = mysqli_query($conn, $query);
$total_categories = mysqli_fetch_assoc($result)['total'];

// Total orders
$query = "SELECT COUNT(*) as total FROM orders";
$result = mysqli_query($conn, $query);
$total_orders = mysqli_fetch_assoc($result)['total'];

// Total users
$query = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($conn, $query);
$total_users = mysqli_fetch_assoc($result)['total'];

// Total images
$query = "SHOW TABLES LIKE 'images'";
$result = mysqli_query($conn, $query);
$total_images = 0;
if (mysqli_num_rows($result) > 0) {
  $query = "SELECT COUNT(*) as total FROM images";
  $result = mysqli_query($conn, $query);
  $total_images = mysqli_fetch_assoc($result)['total'];
}

// Recent orders
$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          GROUP BY o.id 
          ORDER BY o.created_at DESC 
          LIMIT 5";
$result = mysqli_query($conn, $query);
$recent_orders = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $recent_orders[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Beauty Essentials</title>
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
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-card {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .stat-card h3 {
      margin-top: 0;
      margin-bottom: 10px;
      font-size: 16px;
      color: #6c757d;
    }
    .stat-card .stat-value {
      font-size: 32px;
      font-weight: 700;
      color: #ff6b6b;
    }
    .orders-table {
      width: 100%;
      border-collapse: collapse;
    }
    .orders-table th,
    .orders-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    .orders-table th {
      background-color: #f5f5f5;
      font-weight: 600;
    }
    .orders-table tr:hover {
      background-color: #f9f9f9;
    }
    .status-badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 500;
    }
    .status-pending {
      background-color: #fff3cd;
      color: #856404;
    }
    .status-processing {
      background-color: #cce5ff;
      color: #004085;
    }
    .status-shipped {
      background-color: #d4edda;
      color: #155724;
    }
    .status-delivered {
      background-color: #d1e7dd;
      color: #0f5132;
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
    .btn-outline {
      background-color: transparent;
      color: #6c757d;
      border: 1px solid #6c757d;
    }
    .btn-outline:hover {
      background-color: #f8f9fa;
    }
    .quick-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .view-all-link {
      text-align: center;
      margin-top: 20px;
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
      <h2>Store Statistics</h2>
      
      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total Products</h3>
          <div class="stat-value"><?php echo $total_products; ?></div>
        </div>
        
        <div class="stat-card">
          <h3>Total Categories</h3>
          <div class="stat-value"><?php echo $total_categories; ?></div>
        </div>
        
        <div class="stat-card">
          <h3>Total Orders</h3>
          <div class="stat-value"><?php echo $total_orders; ?></div>
        </div>
        
        <div class="stat-card">
          <h3>Total Users</h3>
          <div class="stat-value"><?php echo $total_users; ?></div>
        </div>
        
        <div class="stat-card">
          <h3>Total Images</h3>
          <div class="stat-value"><?php echo $total_images; ?></div>
        </div>
      </div>
    </div>
    
    <div class="admin-card">
      <h2>Recent Orders</h2>
      
      <?php if (empty($recent_orders)): ?>
        <p>No orders yet.</p>
      <?php else: ?>
        <table class="orders-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Items</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_orders as $order): ?>
              <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo $order['item_count']; ?></td>
                <td>$<?php echo number_format($order['total'], 2); ?></td>
                <td>
                  <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                    <?php echo ucfirst($order['status']); ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                <td>
                  <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        
        <div class="view-all-link">
          <a href="orders.php" class="btn btn-outline">View All Orders</a>
        </div>
      <?php endif; ?>
    </div>
    
    <div class="admin-card">
      <h2>Quick Actions</h2>
      
      <div class="quick-actions">
        <a href="add-product.php" class="btn">Add New Product</a>
        <a href="add-category.php" class="btn btn-secondary">Add New Category</a>
        <a href="manual-image-upload.php" class="btn" style="background-color: #6c757d;">Upload Image</a>
      </div>
    </div>
  </div>
</body>
</html>
