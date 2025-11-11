-- ============================================
-- Abeth Hardware POS Database Setup
-- ============================================
-- This file creates all necessary tables and sample data
-- Import this file through phpMyAdmin after installing XAMPP

-- Create database
CREATE DATABASE IF NOT EXISTS `hardware_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `hardware_db`;

-- ============================================
-- Table: users
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','staff','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: products
-- ============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: cart
-- ============================================
CREATE TABLE IF NOT EXISTS `cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  KEY `customer_id` (`customer_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: transactions
-- ============================================
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `cash_received` decimal(10,2) DEFAULT NULL,
  `change_amount` decimal(10,2) DEFAULT NULL,
  `transaction_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `order_type` enum('pickup','delivery') DEFAULT 'pickup',
  `delivery_address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'completed',
  PRIMARY KEY (`transaction_id`),
  KEY `user_id` (`user_id`),
  KEY `transaction_date` (`transaction_date`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: transaction_items
-- ============================================
CREATE TABLE IF NOT EXISTS `transaction_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`transaction_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Sample Data: Admin User
-- ============================================
-- Password: admin123 (use bcrypt in production!)
INSERT INTO `users` (`fname`, `lname`, `email`, `password`, `role`) VALUES
('Admin', 'User', 'admin@abeth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Staff', 'Member', 'staff@abeth.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff'),
('John', 'Doe', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- ============================================
-- Sample Data: Products
-- ============================================
INSERT INTO `products` (`name`, `category`, `price`, `stock`, `description`) VALUES
('Hammer', 'Tools', 250.00, 50, 'Heavy-duty claw hammer'),
('Screwdriver Set', 'Tools', 450.00, 30, 'Professional 6-piece screwdriver set'),
('Paint Brush', 'Painting', 120.00, 100, '2-inch synthetic bristle brush'),
('Nails (1kg)', 'Fasteners', 85.00, 200, 'Assorted construction nails'),
('Wood Glue', 'Adhesives', 180.00, 75, 'Strong wood bonding adhesive'),
('Measuring Tape', 'Measuring', 220.00, 40, '5-meter retractable measuring tape'),
('Safety Goggles', 'Safety', 150.00, 60, 'Impact-resistant safety eyewear'),
('Drill Bit Set', 'Tools', 680.00, 25, 'HSS drill bits 1-10mm'),
('Sandpaper Pack', 'Abrasives', 95.00, 150, 'Assorted grit sandpaper'),
('Utility Knife', 'Cutting', 175.00, 45, 'Retractable blade utility knife');

-- ============================================
-- Default Credentials
-- ============================================
-- Email: admin@abeth.com | Password: admin123
-- Email: staff@abeth.com | Password: admin123
-- Email: customer@example.com | Password: admin123
--
-- IMPORTANT: Change these passwords after first login!
-- ============================================
