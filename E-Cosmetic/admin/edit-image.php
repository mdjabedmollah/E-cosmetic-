<?php
// Start session
session_start();
// Database connection
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit();
}

$error = '';
$success = '';
$image = null;

// Get image ID
$image_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($image_id <= 0) {
  header("Location: upload-image.php");
  exit();
}

// Get image details
$query = "SELECT * FROM images WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $image_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
  header("Location: upload-image.php");
  exit();
}

$image = mysqli_fetch_assoc($result);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = isset($_POST['title']) ? mysqli_real_escape_string($conn, $_POST['title']) : '';
  $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
  
  // Update image details
  $query = "UPDATE images SET title = ?, description = ? WHERE id = ?";
  $stmt = mysqli_prepare($conn, $query);
  mysqli_stmt_bind_param($stmt, "ssi", $title, $description, $image_id);
  
  if (mysqli_stmt_execute($stmt)) {
    $success = "Image details updated successfully.";
    
    // Refresh image data
    $query = "SELECT * FROM images WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $image_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $image = mysqli_fetch_assoc($result);
  } else {
    $error = "Error updating image details.";
  }
}

// Include header
include_once 'admin-header.php';
?>

<main class="container">
  <section class="admin-section">
    <h1>Edit Image</h1>
    
    <div class="admin-content">
      <div class="admin-sidebar">
        <div class="admin-menu">
          <a href="dashboard.php" class="admin-menu-item">Dashboard</a>
          <a href="products.php" class="admin-menu-item">Products</a>
          <a href="categories.php" class="admin-menu-item">Categories</a>
          <a href="orders.php" class="admin-menu-item">Orders</a>
          <a href="upload-image.php" class="admin-menu-item active">Images</a>
          <a href="users.php" class="admin-menu-item">Users</a>
        </div>
      </div>
      
      <div class="admin-main">
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
          <h2>Edit Image Details</h2>
          
          <div class="edit-image-preview">
            <img src="../<?php echo $image['image_url']; ?>" alt="<?php echo htmlspecialchars($image['title']); ?>">
          </div>
          
          <form method="post" action="edit-image.php?id=<?php echo $image_id; ?>" class="admin-form">
            <div class="form-group">
              <label for="title">Image Title</label>
              <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($image['title']); ?>">
            </div>
            
            <div class="form-group">
              <label for="description">Image Description</label>
              <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($image['description']); ?></textarea>
            </div>
            
            <div class="form-group">
              <label>Image Path</label>
              <div class="input-with-copy">
                <input type="text" value="<?php echo $image['image_url']; ?>" readonly>
                <button type="button" class="btn btn-sm btn-outline copy-path" data-path="<?php echo $image['image_url']; ?>">Copy</button>
              </div>
            </div>
            
            <div class="form-group">
              <label>Upload Date</label>
              <input type="text" value="<?php echo date('F d, Y H:i', strtotime($image['created_at'])); ?>" readonly>
            </div>
            
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Update Details</button>
              <a href="upload-image.php" class="btn btn-outline">Back to Gallery</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
  // Copy image path to clipboard
  document.addEventListener('DOMContentLoaded', function() {
    const copyButton = document.querySelector('.copy-path');
    
    copyButton.addEventListener('click', function() {
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
</script>

<?php
// Include footer
include_once 'admin-footer.php';
?>
