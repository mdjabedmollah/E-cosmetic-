<?php
// Database connection
require_once 'config/database.php';
// Include header
include_once 'includes/header.php';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
  header("Location: products.php");
  exit();
}

// Get product details
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.id = $product_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
  header("Location: products.php");
  exit();
}

$product = mysqli_fetch_assoc($result);
?>

<main class="container">
  <div class="product-detail">
    <div class="product-image-large">
      <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
    </div>
    <div class="product-info-detail">
      <h1><?php echo $product['name']; ?></h1>
      <p class="product-category">Category: <?php echo $product['category_name']; ?></p>
      <p class="product-price-large">$<?php echo number_format($product['price'], 2); ?></p>
      <div class="product-description">
        <h3>Description</h3>
        <p><?php echo $product['description']; ?></p>
      </div>
      <div class="product-quantity">
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" min="1" value="1">
      </div>
      <button class="btn btn-primary add-to-cart-detail" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
      <a href="products.php" class="btn btn-outline">Back to Products</a>
    </div>
  </div>

  <!-- Related Products -->
  <section class="related-products">
    <h2>You May Also Like</h2>
    <div class="products-grid">
      <?php
      // Get related products (same category, excluding current product)
      $related_query = "SELECT * FROM products 
                        WHERE category_id = {$product['category_id']} 
                        AND id != $product_id 
                        LIMIT 4";
      $related_result = mysqli_query($conn, $related_query);

      while ($related = mysqli_fetch_assoc($related_result)) {
      ?>
        <div class="product-card">
          <div class="product-image">
            <img src="<?php echo $related['image_url']; ?>" alt="<?php echo $related['name']; ?>">
          </div>
          <div class="product-info">
            <h3><?php echo $related['name']; ?></h3>
            <p class="product-price">$<?php echo number_format($related['price'], 2); ?></p>
            <a href="product.php?id=<?php echo $related['id']; ?>" class="btn btn-secondary">View Details</a>
          </div>
        </div>
      <?php } ?>
    </div>
  </section>
</main>

<?php
// Include footer
include_once 'includes/footer.php';
?>
