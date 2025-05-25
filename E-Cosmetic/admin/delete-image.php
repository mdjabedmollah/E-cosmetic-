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

// Get image ID
$image_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($image_id <= 0) {
  header("Location: upload-image.php");
  exit();
}

// Get image details to delete the file
$query = "SELECT image_url FROM images WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $image_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
  $image = mysqli_fetch_assoc($result);
  $image_path = '../' . $image['image_url'];
  
  // Delete image from database
  $query = "DELETE FROM images WHERE id = ?";
  $stmt = mysqli_prepare($conn, $query);
  mysqli_stmt_bind_param($stmt, "i", $image_id);
  
  if (mysqli_stmt_execute($stmt)) {
    // Delete file from server
    if (file_exists($image_path)) {
      unlink($image_path);
    }
    
    // Set success message
    $_SESSION['success_message'] = "Image deleted successfully.";
  } else {
    // Set error message
    $_SESSION['error_message'] = "Error deleting image.";
  }
}

// Redirect back to image gallery
header("Location: upload-image.php");
exit();
