<?php
// This script creates a very simple placeholder image without using GD library

// Create a simple HTML placeholder
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Product Placeholder</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 400px;
            height: 400px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            font-family: Arial, sans-serif;
        }
        .placeholder-text {
            color: #999;
            font-size: 24px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="placeholder-text">No Image Available</div>
</body>
</html>
';

// Save the HTML to a file
file_put_contents('assets/images/placeholder.html', $html);

echo "HTML placeholder created successfully at assets/images/placeholder.html<br>";
echo "Please download a placeholder image from below and save it as product-placeholder.jpg:<br>";
echo "<a href='https://via.placeholder.com/400x400.png?text=No+Image' download='product-placeholder.jpg'>Download Placeholder Image</a>";
?>
