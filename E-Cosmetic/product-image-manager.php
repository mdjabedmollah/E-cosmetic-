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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
  // Define allowed file types
  $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  // Max file size (5MB)
  $max_size = 5 * 1024 * 1024;
  
  // Get file info
  $file_name = $_FILES['image']['name'];
  $file_size = $_FILES['image']['size'];
  $file_tmp = $_FILES['image']['tmp_name'];
  $file_type = $_FILES['image']['type'];
  
  // Get file extension
  $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
  
  // Validate file
  if (!in_array($file_type, $allowed_types)) {
    $error = "Invalid file type. Only JPG, PNG, GIF, and WEBP files are allowed.";
  } elseif ($file_size > $max_size) {
    $error = "File size is too large. Maximum size is 5MB.";
  } else {
    // Create unique filename
    $new_file_name = uniqid('product_') . '.' . $file_ext;
    $upload_dir = 'assets/images/';
    $upload_path = $upload_dir . $new_file_name;
    
    // Check if directory exists, if not create it
    if (!file_exists($upload_dir)) {
      // Try to create directory with full permissions
      if (!mkdir($upload_dir, 0777, true)) {
        $error = "Failed to create directory. Please check server permissions.";
      } else {
        // Set directory permissions
        chmod($upload_dir, 0777);
      }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
      $error = "Upload directory is not writable. Please check permissions.";
    } else {
      // Move uploaded file
      if (move_uploaded_file($file_tmp, $upload_path)) {
        $success = "Image uploaded successfully!";
        
        // If product ID is provided, update the product image
        if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
          $product_id = intval($_POST['product_id']);
          $image_url = $upload_path;
          
          $query = "UPDATE products SET image_url = ? WHERE id = ?";
          $stmt = mysqli_prepare($conn, $query);
          mysqli_stmt_bind_param($stmt, "si", $image_url, $product_id);
          
          if (mysqli_stmt_execute($stmt)) {
            $success .= " Product image updated.";
          } else {
            $error = "Error updating product image in database.";
          }
        }
      } else {
        $error = "Error uploading file. Please try again.";
      }
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
  <section class="product-image-manager">
    <h1>Product Image Manager</h1>
    
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
    
    <div class="upload-section">
      <h2>Upload Product Image</h2>
      <form method="post" enctype="multipart/form-data" class="upload-form">
        <div class="form-group">
          <label for="product_id">Select Product</label>
          <select name="product_id" id="product_id">
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
    
    <div class="product-images">
      <h2>Current Product Images</h2>
      
      <?php if (empty($products)): ?>
        <p class="no-products">No products found.</p>
      <?php else: ?>
        <div class="product-image-grid">
          <?php foreach ($products as $product): ?>
            <div class="product-image-item">
              <div class="product-image-preview">
                <?php 
                $image_path = $product['image_url'];
                // Check if image exists
                $image_exists = false;
                if (!empty($image_path)) {
                  if (preg_match("~^(?:f|ht)tps?://~i", $image_path)) {
                    $image_exists = true;
                  } else {
                    // Make sure the path starts correctly
                    if (strpos($image_path, '/') !== 0 && strpos($image_path, 'assets/') !== 0) {
                      $image_path = '/' . $image_path;
                    }
                    
                    $file_path = $_SERVER['DOCUMENT_ROOT'] . $image_path;
                    $image_exists = file_exists($file_path);
                  }
                }
                ?>
                <img 
                  src="<?php echo $image_exists ? $image_path : 'assets/images/product-placeholder.jpg'; ?>" 
                  alt="<?php echo htmlspecialchars($product['name']); ?>"
                  onerror="this.src='assets/images/product-placeholder.jpg'"
                >
              </div>
              <div class="product-image-info">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="image-path"><?php echo $product['image_url'] ? $product['image_url'] : 'No image set'; ?></p>
                <form method="post" enctype="multipart/form-data" class="quick-upload-form">
                  <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                  <div class="form-group">
                    <input type="file" name="image" accept="image/*" required>
                    <button type="submit" class="btn btn-sm btn-secondary">Update Image</button>
                  </div>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<style>
  .product-image-manager {
    margin: 40px 0;
  }
  
  .product-image-manager h1 {
    margin-bottom: 30px;
    text-align: center;
  }
  
  .upload-section, .product-images {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  
  .upload-section h2, .product-images h2 {
    margin-bottom: 20px;
    font-size: 24px;
  }
  
  .product-image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
  }
  
  .product-image-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
  }
  
  .product-image-preview {
    height: 200px;
    overflow: hidden;
  }
  
  .product-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  
  .product-image-info {
    padding: 15px;
  }
  
  .product-image-info h3 {
    margin-bottom: 10px;
    font-size: 18px;
  }
  
  .image-path {
    font-size: 12px;
    color: #666;
    margin-bottom: 10px;
    word-break: break-all;
  }
  
  .quick-upload-form {
    display: flex;
    align-items: center;
  }
  
  .quick-upload-form .form-group {
    display: flex;
    flex: 1;
    gap: 10px;
  }
  
  .btn-sm {
    padding: 5px 10px;
    font-size: 14px;
  }
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?>
