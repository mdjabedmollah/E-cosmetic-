<?php
// Start session
session_start();
// Database connection
require_once 'config/database.php';
// Include header
include_once 'includes/header.php';

// Get category filter if exists
$category_filter = isset($_GET['category']) ? $_GET['category'] : null;

// Build query
$query = "SELECT * FROM products";
if ($category_filter) {
  $query .= " WHERE category_id = " . intval($category_filter);
}
$query .= " ORDER BY name ASC";

// Get products
$result = mysqli_query($conn, $query);
?>

<main class="container">
  <section class="products-header">
    <h1>Our Products</h1>
    <div class="filters">
      <form action="products.php" method="get">
        <select name="category" id="category-filter" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <?php
          $cat_query = "SELECT * FROM categories ORDER BY name ASC";
          $cat_result = mysqli_query($conn, $cat_query);
          while ($category = mysqli_fetch_assoc($cat_result)) {
            $selected = ($category_filter == $category['id']) ? 'selected' : '';
            echo "<option value='{$category['id']}' {$selected}>{$category['name']}</option>";
          }
          ?>
        </select>
      </form>
    </div>
  </section>

  <section class="products-grid">
    <?php
    if (mysqli_num_rows($result) > 0) {
      while ($product = mysqli_fetch_assoc($result)) {
    ?>
      <div class="product-card">
        <div class="product-image">
          <!-- Use a default placeholder image URL if the image doesn't exist -->
          <img 
            src="<?php echo $product['image_url']; ?>" 
            alt="<?php echo $product['name']; ?>"
            onerror="this.src='https://via.placeholder.com/400x400.png?text=No+Image'"
          >
        </div>
        <div class="product-info">
          <h3><?php echo $product['name']; ?></h3>
          <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
          <div class="product-actions">
            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">View Details</a>
            <button class="btn btn-primary add-to-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
          </div>
        </div>
      </div>
    <?php
      }
    } else {
      echo "<p class='no-products'>No products found.</p>";
    }
    ?>
  </section>
</main>

<?php
// Include footer
include_once 'includes/footer.php';
?>
