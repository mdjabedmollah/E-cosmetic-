<?php
// Start session
session_start();
// Database connection
require_once 'config/database.php';
// Include header
include_once 'includes/header.php';

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
  header("Location: cart.php");
  exit();
}

// Process checkout form
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate form data
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $zip = trim($_POST['zip'] ?? '');
  $payment_method = $_POST['payment_method'] ?? '';
  
  if (empty($name)) $errors[] = "Name is required";
  if (empty($email)) $errors[] = "Email is required";
  if (empty($address)) $errors[] = "Address is required";
  if (empty($city)) $errors[] = "City is required";
  if (empty($zip)) $errors[] = "ZIP code is required";
  if (empty($payment_method)) $errors[] = "Payment method is required";
  
  // If no errors, process the order
  if (empty($errors)) {
    // Calculate total
    $total = 0;
    foreach ($_SESSION['cart'] as $id => $quantity) {
      $query = "SELECT price FROM products WHERE id = $id";
      $result = mysqli_query($conn, $query);
      $product = mysqli_fetch_assoc($result);
      $total += $product['price'] * $quantity;
    }
    
    // Add shipping
    $total += 10;
    
    // Insert order into database
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $query = "INSERT INTO orders (customer_name, customer_email, address, city, zip, payment_method, total, user_id) 
          VALUES ('$name', '$email', '$address', '$city', '$zip', '$payment_method', $total, " . ($user_id ? $user_id : "NULL") . ")";
    
    if (mysqli_query($conn, $query)) {
      $order_id = mysqli_insert_id($conn);
      
      // Insert order items
      foreach ($_SESSION['cart'] as $id => $quantity) {
        $query = "SELECT price FROM products WHERE id = $id";
        $result = mysqli_query($conn, $query);
        $product = mysqli_fetch_assoc($result);
        $price = $product['price'];
        
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                  VALUES ($order_id, $id, $quantity, $price)";
        mysqli_query($conn, $query);
      }
      
      // Clear cart
      $_SESSION['cart'] = [];
      $success = true;
    } else {
      $errors[] = "Error processing your order. Please try again.";
    }
  }
}
?>

<main class="container">
  <?php if ($success): ?>
    <section class="checkout-success">
      <h1>Order Confirmed!</h1>
      <p>Thank you for your purchase. Your order has been successfully placed.</p>
      <a href="products.php" class="btn btn-primary">Continue Shopping</a>
    </section>
  <?php else: ?>
    <section class="checkout-section">
      <h1>Checkout</h1>
      
      <?php if (!empty($errors)): ?>
        <div class="error-messages">
          <ul>
            <?php foreach ($errors as $error): ?>
              <li><?php echo $error; ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      
      <div class="checkout-container">
        <div class="checkout-form">
          <h2>Shipping Information</h2>
          <form method="post" action="checkout.php">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input type="text" id="name" name="name" value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" required>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
              <label for="address">Address</label>
              <input type="text" id="address" name="address" required>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" required>
              </div>
              
              <div class="form-group">
                <label for="zip">ZIP Code</label>
                <input type="text" id="zip" name="zip" required>
              </div>
            </div>
            
            <h2>Payment Method</h2>
            <div class="payment-methods">
              <div class="payment-option">
                <input type="radio" id="credit-card" name="payment_method" value="credit_card" required>
                <label for="credit-card">Credit Card</label>
              </div>
              
              <div class="payment-option">
                <input type="radio" id="paypal" name="payment_method" value="paypal">
                <label for="paypal">PayPal</label>
              </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Place Order</button>
          </form>
        </div>
        
        <div class="order-summary">
          <h2>Order Summary</h2>
          <div class="summary-items">
            <?php
            $total = 0;
            foreach ($_SESSION['cart'] as $id => $quantity) {
              $query = "SELECT * FROM products WHERE id = $id";
              $result = mysqli_query($conn, $query);
              $product = mysqli_fetch_assoc($result);
              
              $item_total = $product['price'] * $quantity;
              $total += $item_total;
            ?>
              <div class="summary-item">
                <div class="item-info">
                  <span class="item-quantity"><?php echo $quantity; ?>x</span>
                  <span class="item-name"><?php echo $product['name']; ?></span>
                </div>
                <span class="item-price">$<?php echo number_format($item_total, 2); ?></span>
              </div>
            <?php } ?>
          </div>
          
          <div class="summary-totals">
            <div class="summary-row">
              <span>Subtotal:</span>
              <span>$<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
              <span>Shipping:</span>
              <span>$<?php echo number_format(10, 2); ?></span>
            </div>
            <div class="summary-row total">
              <span>Total:</span>
              <span>$<?php echo number_format($total + 10, 2); ?></span>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>
</main>

<?php
// Include footer
include_once 'includes/footer.php';
?>
