<?php
// This script checks if the images directory exists and is writable
// Place this file in your project root directory and run it

// Define the directory path
$directory = 'assets/images';

echo "<h1>Directory Check</h1>";

// Check if directory exists
if (!file_exists($directory)) {
    echo "<p>Directory does not exist. Attempting to create it...</p>";
    
    // Try to create the directory
    if (mkdir($directory, 0777, true)) {
        echo "<p style='color: green;'>Directory created successfully!</p>";
        
        // Set permissions
        chmod($directory, 0777);
        echo "<p>Permissions set to 0777</p>";
    } else {
        echo "<p style='color: red;'>Failed to create directory. Please create it manually.</p>";
        echo "<p>Error: " . error_get_last()['message'] . "</p>";
    }
} else {
    echo "<p>Directory exists.</p>";
}

// Check if directory is writable
if (is_writable($directory)) {
    echo "<p style='color: green;'>Directory is writable.</p>";
} else {
    echo "<p style='color: red;'>Directory is not writable. Please set proper permissions (chmod 777).</p>";
}

// Display server information
echo "<h2>Server Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current Script Path: " . __FILE__ . "</p>";
echo "<p>Directory Path: " . realpath($directory) . "</p>";
echo "<p>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>Post Max Size: " . ini_get('post_max_size') . "</p>";

// Try to create a test file
$testFile = $directory . '/test.txt';
$fileHandle = @fopen($testFile, 'w');
if ($fileHandle) {
    fwrite($fileHandle, 'This is a test file to check write permissions.');
    fclose($fileHandle);
    echo "<p style='color: green;'>Test file created successfully!</p>";
    
    // Clean up
    if (unlink($testFile)) {
        echo "<p>Test file removed.</p>";
    } else {
        echo "<p style='color: orange;'>Could not remove test file. Please delete it manually.</p>";
    }
} else {
    echo "<p style='color: red;'>Could not create test file. Please check directory permissions.</p>";
}
?>
