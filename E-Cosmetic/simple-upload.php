<?php
// Start session
session_start();

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
    
    // Debug information
    echo "<div style='background: #f8f9fa; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd;'>";
    echo "<h3>Upload Information:</h3>";
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
        $upload_dir = 'assets/images/';
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
                $success = "Image uploaded successfully to: " . $upload_path;
                
                // Display the uploaded image
                echo "<div style='background: #d4edda; padding: 10px; margin-bottom: 20px; border: 1px solid #c3e6cb;'>";
                echo "<h3>Uploaded Image:</h3>";
                echo "<img src='" . $upload_path . "' style='max-width: 300px; max-height: 300px;'>";
                echo "<p>Image URL: " . $upload_path . "</p>";
                echo "</div>";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Image Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="file"] {
            display: block;
            margin-bottom: 10px;
        }
        button {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ff5252;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .server-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 30px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Simple Image Upload</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="image">Select Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small>Allowed file types: JPG, PNG, GIF, WEBP. Maximum size: 5MB.</small>
            </div>
            
            <button type="submit">Upload Image</button>
        </form>
        
        <div class="server-info">
            <h2>Server Information</h2>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Upload Max Filesize:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
            <p><strong>Post Max Size:</strong> <?php echo ini_get('post_max_size'); ?></p>
            <p><strong>Current Directory:</strong> <?php echo __DIR__; ?></p>
            <p><strong>Upload Directory:</strong> <?php echo realpath('assets/images'); ?></p>
            <p><strong>Upload Directory Exists:</strong> <?php echo file_exists('assets/images') ? 'Yes' : 'No'; ?></p>
            <p><strong>Upload Directory Writable:</strong> <?php echo is_writable('assets/images') ? 'Yes' : 'No'; ?></p>
        </div>
    </div>
</body>
</html>
