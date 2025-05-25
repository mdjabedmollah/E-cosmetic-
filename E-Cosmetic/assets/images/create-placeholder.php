<?php
// This script creates a placeholder image for products
// Run this once to create the placeholder image

// Set the image dimensions
$width = 400;
$height = 400;

// Create a blank image
$image = imagecreatetruecolor($width, $height);

// Define colors
$bg_color = imagecolorallocate($image, 240, 240, 240);
$text_color = imagecolorallocate($image, 150, 150, 150);
$border_color = imagecolorallocate($image, 200, 200, 200);

// Fill the background
imagefill($image, 0, 0, $bg_color);

// Draw border
imagerectangle($image, 0, 0, $width - 1, $height - 1, $border_color);

// Add text
$text = "No Image";
$font = 5; // Built-in font
$text_width = imagefontwidth($font) * strlen($text);
$text_height = imagefontheight($font);

// Center the text
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

// Draw the text
imagestring($image, $font, $x, $y, $text, $text_color);

// Output the image
header('Content-Type: image/jpeg');
imagejpeg($image, 'product-placeholder.jpg', 90);

// Free memory
imagedestroy($image);

echo "Placeholder image created successfully!";
?>
