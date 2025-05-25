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
$images = [];

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
  
  // Debug information
  echo "<div style='background: #f8f9fa; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd;'>";
  echo "<h3>Debug Information:</h3>";
  echo "<p>File Name: " . htmlspecialchars($file_name) . "</p>";
  echo "<p>File Size: " . $file_size . " bytes</p>";
  echo "<p>File Type: " . htmlspecialchars($file_type) . "</p>";
  echo "<p>File Extension: " . htmlspecialchars($file_ext) . "</p>";
  echo "<p>Temporary Path: " . htmlspecialchars($file_tmp) . "</p>";
  echo "</div>";
  
  // Validate file
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
      // Try to create directory with full permissions
      if (!mkdir($upload_dir, 0777, true)) {
        $error = "Failed to create directory. Please check server permissions.";
        echo "<div style='background: #f8d7da; padding: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb;'>";
        echo "<p>Error creating directory: " . $upload_dir . "</p>";
        echo "<p>Current script path: " . __DIR__ . "</p>";
        echo "</div>";
      } else {
        // Set directory permissions
        chmod($upload_dir, 0777);
      }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
      $error = "Upload directory is not writable. Please check permissions.";
      echo "<div style='background: #f8d7da; padding: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb;'>";
      echo "<p>Directory not writable: " . $upload_dir . "</p>";
      echo "</div>";
    } else {
      // Move uploaded file
      if (move_uploaded_file($file_tmp, $upload_path)) {
        // Save image info to database
        $image_url = 'assets/images/' . $new_file_name;
        $title = isset($_POST['title']) ? mysqli_real_escape_string($conn, $_POST['title']) : '';
        $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
        
        // Check if images table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'images'");
        if (mysqli_num_rows($table_check) == 0) {
          // Create images table if it doesn't exist
          $create_table = "CREATE TABLE IF NOT EXISTS images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            image_url VARCHAR(255) NOT NULL,
            uploaded_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
          )";
          mysqli_query($conn, $create_table);
        }
        
        $query = "INSERT INTO images (title, description, image_url, uploaded_by) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $image_url, $_SESSION['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
          $success = "Image uploaded successfully.";
        } else {
          $error = "Error saving image information to database: " . mysqli_error($conn);
        }
      } else {
        $error = "Error uploading file. Please try again.";
        echo "<div style='background: #f8d7da; padding: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb;'>";
        echo "<p>Failed to move uploaded file from " . htmlspecialchars($file_tmp) . " to " . htmlspecialchars($upload_path) . "</p>";
        echo "<p>PHP Error: " . error_get_last()['message'] . "</p>";
        echo "</div>";
      }
    }
  }
}

// Get all images
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'images'");
if (mysqli_num_rows($table_check) > 0) {
  $query = "SELECT * FROM images ORDER BY created_at DESC";
  $result = mysqli_query($conn, $query);
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $images[] = $row;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Image Upload - Beauty Essentials</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <style>
    /* Simple admin styles */
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    .admin-header {
      background-color: #333;
      color: white;
      padding: 15px 0;
      margin-bottom: 30px;
    }
    .admin-header h1 {
      margin: 0;
      font-size: 24px;
    }
    .admin-card {
      background-color: white;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .admin-card h2 {
      margin-top: 0;
      margin-bottom: 20px;
      font-size: 20px;
    }
    .form-group {
      margin-bottom: 15px;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    .form-group input, .form-group textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    .btn {
      display: inline-block;
      padding: 8px 16px;
      background-color: #ff6b6b;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn:hover {
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
    .image-gallery {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
    }
    .gallery-item {
      border: 1px solid #ddd;
      border-radius: 4px;
      overflow: hidden;
    }
    .gallery-image {
      height: 200px;
      overflow: hidden;
    }
    .gallery-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .gallery-info {
      padding: 10px;
    }
    .gallery-path {
      font-size: 12px;
      color: #666;
      word-break: break-all;
      margin-bottom: 10px;
    }
    .gallery-actions {
      display: flex;
      gap: 5px;
    }
    .btn-sm {
      padding: 4px 8px;
      font-size: 12px;
    }
    .server-info {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="admin-header">
    <div class="container">
      <h1>Beauty Essentials Admin - Image Upload</h1>
    </div>
  </div>

  <div class="container">
    <!-- Server Information for Debugging -->
    <div class="server-info">
      <h3>Server Information</h3>
      <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
      <p><strong>Upload Max Filesize:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
      <p><strong>Post Max Size:</strong> <?php echo ini_get('post_max_size'); ?></p>
      <p><strong>Current Directory:</strong> <?php echo __DIR__; ?></p>
      <p><strong>Upload Directory:</strong> <?php echo realpath('../assets/images'); ?></p>
      <p><strong>Upload Directory Exists:</strong> <?php echo file_exists('../assets/images') ? 'Yes' : 'No'; ?></p>
      <p><strong>Upload Directory Writable:</strong> <?php echo is_writable('../assets/images') ? 'Yes' : 'No'; ?></p>
    </div>
    
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
    
    <div class="admin-card">
      <h2>Upload New Image</h2>
      <form method="post" action="upload-image.php" enctype="multipart/form-data">
        <div class="form-group">
          <label for="title">Image Title</label>
          <input type="text" id="title" name="title" placeholder="Enter image title">
        </div>
        
        <div class="form-group">
          <label for="description">Image Description</label>
          <textarea id="description" name="description" rows="3" placeholder="Enter image description"></textarea>
        </div>
        
        <div class="form-group">
          <label for="image">Select Image</label>
          <input type="file" id="image" name="image" accept="image/*" required>
          <small>Allowed file types: JPG, PNG, GIF, WEBP. Maximum size: 5MB.</small>
        </div>
        
        <div class="form-group">
          <button type="submit" class="btn">Upload Image</button>
          <a href="../index.php" class="btn" style="background-color: #6c757d;">Back to Store</a>
        </div>
      </form>
    </div>
    
    <div class="admin-card">
      <h2>Image Gallery</h2>
      
      <?php if (empty($images)): ?>
        <p>No images uploaded yet.</p>
      <?php else: ?>
        <div class="image-gallery">
          <?php foreach ($images as $image): ?>
            <div class="gallery-item">
              <div class="gallery-image">
                <img src="../<?php echo $image['image_url']; ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" onerror="this.src='../assets/images/image-not-found.jpg'">
              </div>
              <div class="gallery-info">
                <h3><?php echo htmlspecialchars($image['title'] ?: 'Untitled'); ?></h3>
                <p class="gallery-path"><?php echo $image['image_url']; ?></p>
                <div class="gallery-actions">
                  <button class="btn btn-sm copy-path" data-path="<?php echo $image['image_url']; ?>">Copy Path</button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    // Copy image path to clipboard
    document.addEventListener('DOMContentLoaded', function() {
      const copyButtons = document.querySelectorAll('.copy-path');
      
      copyButtons.forEach(button => {
        button.addEventListener('click', function() {
          const path = this.getAttribute('data-path');
          navigator.clipboard.writeText(path).then(() => {
            // Change button text temporarily
            const originalText = this.textContent;
            this.textContent = 'Copied!';
            setTimeout(() => {
              this.textContent = originalText;
            }, 2000);
          });
        });
      });
    });
  </script>
</body>
</html>
