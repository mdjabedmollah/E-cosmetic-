-- Add user_id column to orders table
ALTER TABLE orders ADD COLUMN user_id INT DEFAULT NULL;
ALTER TABLE orders ADD FOREIGN KEY (user_id) REFERENCES users(id);
