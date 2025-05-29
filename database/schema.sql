-- Create the eCommerce database
CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category_id INT,
    stock_quantity INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Company information table
CREATE TABLE company_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(100),
    website VARCHAR(100),
    opening_hours TEXT,
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(50),
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description, image) VALUES
('Electronics', 'Latest electronic gadgets and devices', 'electronics.jpg'),
('Clothing', 'Fashion and apparel for all ages', 'clothing.jpg'),
('Books', 'Wide selection of books and literature', 'books.jpg'),
('Home & Garden', 'Everything for your home and garden', 'home-garden.jpg'),
('Sports', 'Sports equipment and accessories', 'sports.jpg');

-- Insert sample products
INSERT INTO products (name, description, price, image, category_id, stock_quantity, featured) VALUES
-- Electronics
('Smartphone Pro Max', 'Latest flagship smartphone with advanced features', 999.99, 'smartphone.jpg', 1, 50, TRUE),
('Wireless Headphones', 'Premium noise-canceling wireless headphones', 299.99, 'headphones.jpg', 1, 75, TRUE),
('Laptop Gaming Edition', 'High-performance gaming laptop', 1299.99, 'laptop.jpg', 1, 25, FALSE),
('Smart Watch', 'Fitness tracking smartwatch with GPS', 249.99, 'smartwatch.jpg', 1, 100, TRUE),

-- Clothing
('Premium T-Shirt', 'Comfortable cotton t-shirt in various colors', 29.99, 'tshirt.jpg', 2, 200, FALSE),
('Denim Jeans', 'Classic fit denim jeans', 79.99, 'jeans.jpg', 2, 150, FALSE),
('Winter Jacket', 'Warm and stylish winter jacket', 149.99, 'jacket.jpg', 2, 80, TRUE),
('Running Shoes', 'Comfortable running shoes for daily exercise', 89.99, 'shoes.jpg', 2, 120, FALSE),

-- Books
('Web Development Guide', 'Complete guide to modern web development', 39.99, 'webdev-book.jpg', 3, 60, FALSE),
('Science Fiction Novel', 'Bestselling science fiction adventure', 14.99, 'scifi-book.jpg', 3, 90, FALSE),
('Cookbook Deluxe', 'Professional cookbook with 500+ recipes', 49.99, 'cookbook.jpg', 3, 40, FALSE),

-- Home & Garden
('Coffee Maker Pro', 'Professional-grade coffee maker', 199.99, 'coffee-maker.jpg', 4, 35, TRUE),
('Plant Pot Set', 'Decorative plant pots for indoor gardening', 24.99, 'plant-pots.jpg', 4, 80, FALSE),
('LED Desk Lamp', 'Adjustable LED desk lamp with USB charging', 59.99, 'desk-lamp.jpg', 4, 60, FALSE),

-- Sports
('Yoga Mat Premium', 'Non-slip premium yoga mat', 34.99, 'yoga-mat.jpg', 5, 100, FALSE),
('Basketball Official', 'Official size basketball', 19.99, 'basketball.jpg', 5, 150, FALSE),
('Dumbbells Set', 'Adjustable dumbbells set', 129.99, 'dumbbells.jpg', 5, 45, FALSE);

-- Insert company information
INSERT INTO company_info (name, address, phone, email, website, opening_hours, logo) VALUES
('TechStore Pro', '123 Commerce Street, Business District, Tech City, TC 12345', '+1 (555) 123-4567', 'contact@techstorepro.com', 'www.techstorepro.com', 'Monday - Friday: 9:00 AM - 8:00 PM\nSaturday: 10:00 AM - 6:00 PM\nSunday: 12:00 PM - 5:00 PM', 'logo.png');
