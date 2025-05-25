<?php
// Database connection
require_once 'config/database.php';
// Include header
include_once 'includes/header.php';
?>

<main class="container">
  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1>Beauty Essentials</h1>
      <p>Discover our premium collection of cosmetic products</p>
      <a href="/assets/images/backgroundimage.jpg" class="btn btn-primary">Shop Now</a>
    </div>
  </section>

  <!-- Featured Products -->
  <section class="featured-products">
    <h2>Featured Products</h2>
    <div class="products-grid">
      <?php
      // Get featured products
      $query = "SELECT * FROM products WHERE featured = 1 LIMIT 4";
      $result = mysqli_query($conn, $query);

      while ($product = mysqli_fetch_assoc($result)) {
      ?>
        <div class="product-card">
          <div class="product-image">
            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
          </div>
          <div class="product-info">
            <h3><?php echo $product['name']; ?></h3>
            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">View Details</a>
          </div>
        </div>
      <?php } ?>
    </div>
    <div class="view-all">
      <a href="products.php" class="btn btn-outline">View All Products</a>
    </div>
  </section>

  <!-- Categories Section -->
  <section class="categories">
    <h2>Shop by Category</h2>
    <div class="category-grid">
      <?php
      // Get categories
      $query = "SELECT * FROM categories LIMIT 3";
      $result = mysqli_query($conn, $query);

      while ($category = mysqli_fetch_assoc($result)) {
      ?>
        <div class="category-card">
          <img src="<?php echo $category['image_url']; ?>" alt="<?php echo $category['name']; ?>">
          <h3><?php echo $category['name']; ?></h3>
          <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-tertiary">Shop Now</a>
        </div>
      <?php } ?>
    </div>
  </section>
</main>

<?php
// Include footer
include_once 'includes/footer.php';
?>
