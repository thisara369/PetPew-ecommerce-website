-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 28, 2026 at 10:07 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `petpew`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logins`
--

DROP TABLE IF EXISTS `logins`;
CREATE TABLE IF NOT EXISTS `logins` (
  `username` varchar(255) NOT NULL,
  `login_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `tracking_number` varchar(100) DEFAULT NULL,
  `tracking_note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `payment_method`, `total_amount`, `status`, `tracking_number`, `tracking_note`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cash on Delivery', 6800.00, 'Pending', NULL, NULL, '2026-05-28 04:54:17', '2026-05-28 04:54:17');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `method` varchar(100) NOT NULL,
  `card_holder` varchar(255) DEFAULT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `exp_month` varchar(2) DEFAULT NULL,
  `exp_year` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `method`, `card_holder`, `card_last4`, `exp_month`, `exp_year`) VALUES
(1, 1, 'Cash on Delivery', '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `sub_category` varchar(100) DEFAULT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `badge_type` varchar(50) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT NULL,
  `reviews` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `original_price`, `image`, `category`, `sub_category`, `badge`, `badge_type`, `rating`, `reviews`, `created_at`) VALUES
(18, 'Royal Canin Golden Retriever Adult', 'Premium breed-specific dry food formulated to meet the nutritional needs of purebred Golden Retrievers.', 4200.00, 4500.00, 'photo/Training Treats.jpg', 'Dog', 'food', 'Sale', 'sale', 4.80, 95, '2026-05-28 09:59:54'),
(19, 'KONG Classic Dog Toy', 'Super-bouncy, red natural rubber toy that is perfect for dogs that like to chew.', 1250.00, 1500.00, 'photo/Dog Toys.jpg.webp', 'Dog', 'toys', 'Hot', 'new', 4.90, 142, '2026-05-28 09:59:54'),
(20, 'Orthopedic Memory Foam Bed', 'Ultra-plush memory foam bed designed to support joints and relieve pressure points.', 6800.00, 7500.00, 'photo/Dog Bed.jpg.webp', 'Dog', 'beds', NULL, NULL, 4.70, 38, '2026-05-28 09:59:54'),
(21, 'Furminator Undercoat Deshedding Tool', 'Reduces loose hair from shedding up to 90% with regular grooming sessions.', 2950.00, 3200.00, 'photo/Dog Shampoo.jpg.webp', 'Dog', 'grooming', NULL, NULL, 4.80, 73, '2026-05-28 09:59:54'),
(22, 'Premium Padded Dog Collar & Leash Set', 'Heavy-duty reflective nylon set with comfortable neoprene padding.', 1850.00, NULL, 'photo/Dog Collar.jpg.webp', 'Dog', 'accessories', NULL, NULL, 4.60, 54, '2026-05-28 09:59:54'),
(23, 'Whiskas Dry Cat Food (Chicken & Milk)', 'Nutritionally complete dry food formulated for adult cats to promote shiny coats and strong teeth.', 2150.00, 2400.00, 'photo/cat food.png', 'Cat', 'food', 'Sale', 'sale', 4.70, 110, '2026-05-28 09:59:54'),
(24, 'Interactive Cat Tree & Scratching Post', 'Multi-level activity tower with plush perches, scratching posts, and hanging toys.', 8500.00, 9500.00, 'photo/Scratching Post.png', 'Cat', 'toys', 'Popular', 'new', 4.90, 64, '2026-05-28 09:59:54'),
(25, 'Stainless Steel Pet Drinking Fountain', 'Whisper-quiet automatic drinking fountain with a dual-filtration system.', 4200.00, NULL, 'photo/Cat Harness.png', 'Cat', 'accessories', NULL, NULL, 4.50, 42, '2026-05-28 09:59:54'),
(26, 'Premium Handfeeding Formula for Parrots', 'High-nutrient formula designed to support rapid growth and optimal development in baby birds.', 1950.00, 2100.00, 'photo/WhatsApp Image 2025-05-18 at 17.06.24_8306589b.jpg', 'Birds', 'food', NULL, NULL, 4.80, 28, '2026-05-28 09:59:54'),
(27, 'Double Stack Bird Cage with Stand', 'Spacious iron parrot cage equipped with rolling casters, perches, and feeding bowls.', 16500.00, 18000.00, 'photo/Congo Cage.png', 'Birds', 'cages', NULL, NULL, 4.70, 15, '2026-05-28 09:59:54'),
(28, 'Sera Vipan Family Flake Food', 'Balanced flake food suitable for all ornamental fish in community aquariums.', 850.00, NULL, 'photo/Arowana Sticks Food.png', 'Aquarium', 'food', NULL, NULL, 4.90, 88, '2026-05-28 09:59:54'),
(29, '30-Gallon Frameless Aquarium Starter Kit', 'Sleek, low-iron glass aquarium set complete with a quiet hang-on filter and LED lighting.', 24500.00, 27000.00, 'photo/Fish Tank Aquarium Combo Tank.png', 'Aquarium', 'tanks', 'Best Seller', 'new', 4.80, 32, '2026-05-28 09:59:54'),
(30, 'Premium Crested Gecko Diet (Banana & Papaya)', 'Complete, balanced powdered meal for Crested Geckos, Gargoyle Geckos, and other frugivores.', 2800.00, NULL, 'photo/Rep-Cal Herptivite Multivitamin.png', 'Exotic Pets', 'food', NULL, NULL, 4.80, 19, '2026-05-28 09:59:54'),
(31, 'Glass Terrarium Habitat (Medium-Tall)', 'Premium glass reptile enclosure with front opening doors and dual ventilation.', 18500.00, 21000.00, 'photo/Hedgehog Starter Pack.png', 'Exotic Pets', 'habitats', NULL, NULL, 4.60, 11, '2026-05-28 09:59:54'),
(32, 'Oxbow Western Timothy Hay (90oz)', 'High-fiber sweet grass hay essential for the digestive health of rabbits, guinea pigs, and chinchillas.', 1950.00, 2200.00, 'photo/Premium Rabbit Food.png', 'Small Pets', 'food', 'Essential', 'sale', 4.90, 145, '2026-05-28 09:59:54'),
(33, 'Silent Spinner Exercise Wheel (Medium)', 'Whisper-quiet enclosed wheel designed to keep hamsters, gerbils, and mice active and healthy.', 1650.00, NULL, 'photo/Practice Wheel.png', 'Small Pets', 'toys', NULL, NULL, 4.70, 58, '2026-05-28 09:59:54'),
(34, 'Royal Canin Golden Retriever Adult', 'Premium breed-specific dry food formulated to meet the nutritional needs of purebred Golden Retrievers.', 4200.00, 4500.00, 'photo/Training Treats.jpg', 'Dog', 'food', 'Sale', 'sale', 4.80, 95, '2026-05-28 09:59:54'),
(35, 'KONG Classic Dog Toy', 'Super-bouncy, red natural rubber toy that is perfect for dogs that like to chew.', 1250.00, 1500.00, 'photo/Dog Toys.jpg.webp', 'Dog', 'toys', 'Hot', 'new', 4.90, 142, '2026-05-28 09:59:54'),
(36, 'Orthopedic Memory Foam Bed', 'Ultra-plush memory foam bed designed to support joints and relieve pressure points.', 6800.00, 7500.00, 'photo/Dog Bed.jpg.webp', 'Dog', 'beds', NULL, NULL, 4.70, 38, '2026-05-28 09:59:54'),
(37, 'Furminator Undercoat Deshedding Tool', 'Reduces loose hair from shedding up to 90% with regular grooming sessions.', 2950.00, 3200.00, 'photo/Dog Shampoo.jpg.webp', 'Dog', 'grooming', NULL, NULL, 4.80, 73, '2026-05-28 09:59:54'),
(38, 'Premium Padded Dog Collar & Leash Set', 'Heavy-duty reflective nylon set with comfortable neoprene padding.', 1850.00, NULL, 'photo/Dog Collar.jpg.webp', 'Dog', 'accessories', NULL, NULL, 4.60, 54, '2026-05-28 09:59:54'),
(39, 'Whiskas Dry Cat Food (Chicken & Milk)', 'Nutritionally complete dry food formulated for adult cats to promote shiny coats and strong teeth.', 2150.00, 2400.00, 'photo/cat food.png', 'Cat', 'food', 'Sale', 'sale', 4.70, 110, '2026-05-28 09:59:54'),
(40, 'Interactive Cat Tree & Scratching Post', 'Multi-level activity tower with plush perches, scratching posts, and hanging toys.', 8500.00, 9500.00, 'photo/Scratching Post.png', 'Cat', 'toys', 'Popular', 'new', 4.90, 64, '2026-05-28 09:59:54'),
(41, 'Stainless Steel Pet Drinking Fountain', 'Whisper-quiet automatic drinking fountain with a dual-filtration system.', 4200.00, NULL, 'photo/Cat Harness.png', 'Cat', 'accessories', NULL, NULL, 4.50, 42, '2026-05-28 09:59:54'),
(42, 'Premium Handfeeding Formula for Parrots', 'High-nutrient formula designed to support rapid growth and optimal development in baby birds.', 1950.00, 2100.00, 'photo/WhatsApp Image 2025-05-18 at 17.06.24_8306589b.jpg', 'Birds', 'food', NULL, NULL, 4.80, 28, '2026-05-28 09:59:54'),
(43, 'Double Stack Bird Cage with Stand', 'Spacious iron parrot cage equipped with rolling casters, perches, and feeding bowls.', 16500.00, 18000.00, 'photo/Congo Cage.png', 'Birds', 'cages', NULL, NULL, 4.70, 15, '2026-05-28 09:59:54'),
(44, 'Sera Vipan Family Flake Food', 'Balanced flake food suitable for all ornamental fish in community aquariums.', 850.00, NULL, 'photo/Arowana Sticks Food.png', 'Aquarium', 'food', NULL, NULL, 4.90, 88, '2026-05-28 09:59:54'),
(45, '30-Gallon Frameless Aquarium Starter Kit', 'Sleek, low-iron glass aquarium set complete with a quiet hang-on filter and LED lighting.', 24500.00, 27000.00, 'photo/Fish Tank Aquarium Combo Tank.png', 'Aquarium', 'tanks', 'Best Seller', 'new', 4.80, 32, '2026-05-28 09:59:54'),
(46, 'Premium Crested Gecko Diet (Banana & Papaya)', 'Complete, balanced powdered meal for Crested Geckos, Gargoyle Geckos, and other frugivores.', 2800.00, NULL, 'photo/Rep-Cal Herptivite Multivitamin.png', 'Exotic Pets', 'food', NULL, NULL, 4.80, 19, '2026-05-28 09:59:54'),
(47, 'Glass Terrarium Habitat (Medium-Tall)', 'Premium glass reptile enclosure with front opening doors and dual ventilation.', 18500.00, 21000.00, 'photo/Hedgehog Starter Pack.png', 'Exotic Pets', 'habitats', NULL, NULL, 4.60, 11, '2026-05-28 09:59:54'),
(48, 'Oxbow Western Timothy Hay (90oz)', 'High-fiber sweet grass hay essential for the digestive health of rabbits, guinea pigs, and chinchillas.', 1950.00, 2200.00, 'photo/Premium Rabbit Food.png', 'Small Pets', 'food', 'Essential', 'sale', 4.90, 145, '2026-05-28 09:59:54'),
(49, 'Silent Spinner Exercise Wheel (Medium)', 'Whisper-quiet enclosed wheel designed to keep hamsters, gerbils, and mice active and healthy.', 1650.00, NULL, 'photo/Practice Wheel.png', 'Small Pets', 'toys', NULL, NULL, 4.70, 58, '2026-05-28 09:59:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `admin_role` varchar(50) DEFAULT NULL,
  `shift_start` time DEFAULT NULL,
  `shift_end` time DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'uploads/avatars/default.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `admin_role`, `shift_start`, `shift_end`, `full_name`, `age`, `avatar`, `created_at`) VALUES
(1, 'Thisara', 'thisaratharuk@gmail.com', '$2y$10$SNji4fwi7tXXU88CGHJc2etRyUIqiafeWMQOT5S87FN5o.VQsM.zS', 'user', NULL, NULL, NULL, 'Thisara Tharuk Thirimanne', 19, 'uploads/avatars/av_6a17ca2b6ae507.75953543.jpg', '2026-05-28 04:52:10'),
(2, 'admin', 'admin@petpew.com', '$2y$10$uwTI3wqeLJNXq3e2G/jemOUirdXDLBL0ekZm7ePbrRjZD4N84J62K', 'admin', 'super_admin', NULL, NULL, 'Administrator', 30, 'uploads/avatars/default.png', '2026-06-16 09:59:44');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
