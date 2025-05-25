<?php
// Start session
session_start();
// Database connection
require_once '../config/database.php';

// Initialize response array
$response = [
  'success' => false,
  'message' => '',
  'cart_count' => 0
];

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  
  // Add to cart
  if ($action === 'add' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Validate product exists
    $query = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0 && $quantity > 0) {
      // Add to cart or update quantity
      if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
      } else {
        $_SESSION['cart'][$product_id] = $quantity;
      }
      
      $response['success'] = true;
      $response['message'] = 'Product added to cart';
    } else {
      $response['message'] = 'Invalid product or quantity';
    }
  }
  
  // Update quantity
  else if ($action === 'update' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    if (isset($_SESSION['cart'][$product_id])) {
      if ($quantity > 0) {
        $_SESSION['cart'][$product_id] = $quantity;
        $response['success'] = true;
        $response['message'] = 'Cart updated';
      } else {
        unset($_SESSION['cart'][$product_id]);
        $response['success'] = true;
        $response['message'] = 'Product removed from cart';
      }
    } else {
      $response['message'] = 'Product not in cart';
    }
  }
  
  // Remove from cart
  else if ($action === 'remove' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    
    if (isset($_SESSION['cart'][$product_id])) {
      unset($_SESSION['cart'][$product_id]);
      $response['success'] = true;
      $response['message'] = 'Product removed from cart';
    } else {
      $response['message'] = 'Product not in cart';
    }
  }
  
  // Calculate cart count
  $cart_count = 0;
  foreach ($_SESSION['cart'] as $quantity) {
    $cart_count += $quantity;
  }
  $response['cart_count'] = $cart_count;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
