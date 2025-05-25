<?php
// Start session
session_start();
// Database connection
require_once 'config/database.php';
// Include header
include_once 'includes/header.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
?>

<main class="container">
  <section class="cart-section">
    <h1>Your Shopping Cart</h1>
    
    <?php if (empty($_SESSION['cart'])) { ?>
      <div class="empty-cart">
        <p>Your cart is empty.</p>
        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
      </div>
    <?php } else { ?>
      <div class="cart-items">
        <table class="cart-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Total</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $total = 0;
            foreach ($_SESSION['cart'] as $id => $quantity) {
              // Get product details
              $query = "SELECT * FROM products WHERE id = $id";
              $result = mysqli_query($conn, $query);
              $product = mysqli_fetch_assoc($result);
              
              $item_total = $product['price'] * $quantity;
              $total += $item_total;
            ?>
              <tr>
                <td class="product-info">
                  <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                  <div>
                    <h3><?php echo $product['name']; ?></h3>
                  </div>
                </td>
                <td>$<?php echo number_format($product['price'], 2); ?></td>
                <td>
                  <div class="quantity-control">
                    <button class="quantity-btn decrease" data-id="<?php echo $id; ?>">-</button>
                    <span class="quantity"><?php echo $quantity; ?></span>
                    <button class="quantity-btn increase" data-id="<?php echo $id; ?>">+</button>
                  </div>
                </td>
                <td>$<?php echo number_format($item_total, 2); ?></td>
                <td>
                  <button class="btn btn-danger remove-item" data-id="<?php echo $id; ?>">Remove</button>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      
      <div class="cart-summary">
        <div class="summary-details">
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
        <div class="cart-actions">
          <a href="products.php" class="btn btn-outline">Continue Shopping</a>
          <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        </div>
      </div>
    <?php } ?>
  </section>
</main>

<?php
// Include footer
include_once 'includes/footer.php';
?>
