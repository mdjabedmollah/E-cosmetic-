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

$error = '';
$success = '';

// Process image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && isset($_POST['product_id'])) {
  $product_id = intval($_POST['product_id']);
  
  // Define allowed file types
  $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  // Max file size (5MB)
  $max_size = 5 * 1024 * 1024;
  
  // Get file info
  $file_name = $_FILES['image']['name'];
  $file_size = $_FILES['image']['size'];
  $file_tmp = $_FILES['image']['tmp_name'];
  $file_type = $_FILES['image']['type'];
  
  // Validate file
  if (!in_array($file_type, $allowed_types)) {
    $error = "Invalid file type. Only JPG, PNG, GIF, and WEBP files are allowed.";
  } elseif ($file_size > $max_size) {
    $error = "File size is too large. Maximum size is 5MB.";
  } else {
    // Create unique filename
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_file_name = 'product_' . $product_id . '_' . uniqid() . '.' . $file_ext;
    $upload_dir = 'assets/images/';
    $upload_path = $upload_dir . $new_file_name;
    
    // Check if directory exists, if not create it
    if (!file_exists($upload_dir)) {
      if (!mkdir($upload_dir, 0777, true)) {
        $error = "Failed to create directory. Please check server permissions.";
      }
    }
    
    // Move uploaded file
    if (move_uploaded_file($file_tmp, $upload_path)) {
      // Update product image in database
      $image_url = $upload_path;
      
      $query = "UPDATE products SET image_url = ? WHERE id = ?";
      $stmt = mysqli_prepare($conn, $query);
      mysqli_stmt_bind_param($stmt, "si", $image_url, $product_id);
      
      if (mysqli_stmt_execute($stmt)) {
        $success = "Image uploaded and product updated successfully!";
      } else {
        $error = "Error updating product image in database.";
      }
    } else {
      $error = "Error uploading file. Please try again.";
    }
  }
}

// Get all products
$query = "SELECT id, name, image_url FROM products ORDER BY name ASC";
$result = mysqli_query($conn, $query);
$products = [];
if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
  }
}

// Include header
include_once 'includes/header.php';
?>

<main class="container">
  <section class="image-upload-section">
    <h1>Product Image Upload</h1>
    
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
    
    <div class="upload-card">
      <h2>Upload Product Image</h2>
      <p>Select a product and upload an image for it. The image will be used on the product listing and detail pages.</p>
      
      <form method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="product_id">Select Product</label>
          <select name="product_id" id="product_id" required>
            <option value="">-- Select a product --</option>
            <?php foreach ($products as $product): ?>
              <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label for="image">Select Image</label>
          <input type="file" id="image" name="image" accept="image/*" required>
          <small>Allowed file types: JPG, PNG, GIF, WEBP. Maximum size: 5MB.</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Upload Image</button>
      </form>
    </div>
    
    <div class="products-list">
      <h2>Current Product Images</h2>
      
      <table class="products-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Current Image</th>
            <th>Image Path</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <tr>
              <td><?php echo htmlspecialchars($product['name']); ?></td>
              <td class="image-preview">
                <img 
                  src="<?php echo $product['image_url']; ?>" 
                  alt="<?php echo htmlspecialchars($product['name']); ?>"
                  onerror="this.src='https://via.placeholder.com/100x100.png?text=No+Image'"
                  width="100"
                >
              </td>
              <td class="image-path"><?php echo $product['image_url'] ? $product['image_url'] : 'No image set'; ?></td>
              <td>
                <form method="post" enctype="multipart/form-data" class="inline-form">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <input type="file" name="image" accept="image/*" required style="width: 150px;">
                  <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<style>
  .image-upload-section {
    margin: 40px 0;
  }
  
  .image-upload-section h1 {
    margin-bottom: 30px;
    text-align: center;
  }
  
  .upload-card {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  
  .products-list {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  
  .products-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }
  
  .products-table th, .products-table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
  }
  
  .image-preview img {
    border: 1px solid #ddd;
    border-radius: 4px;
    object-fit: cover;
  }
  
  .image-path {
    font-size: 12px;
    color: #666;
    word-break: break-all;
  }
  
  .inline-form {
    display: flex;
    align-items: center;
    gap: 5px;
  }
  
  .btn-sm {
    padding: 5px 10px;
    font-size: 14px;
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

<?php
// Include footer
include_once 'includes/footer.php';
?>
