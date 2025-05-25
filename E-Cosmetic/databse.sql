-- Create database
CREATE DATABASE IF NOT EXISTS cosmetic_store;
USE cosmetic_store;

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  image_url VARCHAR(255) NOT NULL
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  category_id INT NOT NULL,
  featured BOOLEAN DEFAULT 0,
  stock INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(100) NOT NULL,
  customer_email VARCHAR(100) NOT NULL,
  address VARCHAR(255) NOT NULL,
  city VARCHAR(100) NOT NULL,
  zip VARCHAR(20) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  total DECIMAL(10, 2) NOT NULL,
  status VARCHAR(20) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert sample categories
INSERT INTO categories (name, image_url) VALUES
('Skincare', 'assets/images/category-skincare.jpg'),
('Makeup', 'assets/images/category-makeup.jpg'),
('Hair Care', 'assets/images/category-haircare.jpg');

-- Insert sample products
INSERT INTO products (name, description, price, image_url, category_id, featured, stock) VALUES
('Hydrating Face Cream', 'A rich, moisturizing face cream that hydrates and nourishes your skin.', 29.99, 'assets/images/product-1.jpg', 1, 1, 15),
('Vitamin C Serum', 'Brightening serum with vitamin C to reduce dark spots and improve skin tone.', 34.50, 'assets/images/product-2.jpg', 1, 1, 20),
('Matte Lipstick', 'Long-lasting matte lipstick in a beautiful red shade.', 19.99, 'assets/images/product-3.jpg', 2, 1, 25),
('Volumizing Mascara', 'Adds volume and length to your lashes for a dramatic look.', 24.99, 'assets/images/product-4.jpg', 2, 1, 18),
('Argan Oil Hair Treatment', 'Nourishing hair oil that repairs damaged hair and adds shine.', 27.50, 'assets/images/product-5.jpg', 3, 0, 12),
('Exfoliating Face Scrub', 'Gentle exfoliating scrub to remove dead skin cells and reveal brighter skin.', 22.99, 'assets/images/product-6.jpg', 1, 0, 15),
('Foundation', 'Medium to full coverage foundation with a natural finish.', 39.99, 'assets/images/product-7.jpg', 2, 0, 20),
('Moisturizing Shampoo', 'Hydrating shampoo for dry and damaged hair.', 18.50, 'assets/images/product-8.jpg', 3, 0, 22),
('Eye Shadow Palette', 'Versatile eyeshadow palette with 12 beautiful shades.', 45.99, 'assets/images/product-9.jpg', 2, 0, 10),
('Anti-Aging Night Cream', 'Rejuvenating night cream that works while you sleep.', 49.99, 'assets/images/product-10.jpg', 1, 0, 15);
