-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 11, 2026 at 07:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `food_delivery`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `prep_time` int(11) DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `item_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `restaurant_id`, `item_name`, `category`, `price`, `description`, `prep_time`, `status`, `item_image`) VALUES
(1, 2, 'pilau', 'traditional', 7000.00, 'nyama ya kuku, mayai,juice', 25, 'unavailable', 'menu_69845340c0a5c4.16838025.jpg'),
(3, 2, 'pilau', 'traditional', 7000.00, 'nyama ya kuku, mayai,juice', 25, 'available', 'menu_69845421eea2c4.29765357.jpg'),
(4, 2, 'pilau', 'traditional', 7000.00, 'nyama ya kuku, mayai,juice', 25, 'available', 'menu_69845472e286c9.68027843.jpg'),
(5, 4, 'pilau', 'main-course', 7000.00, 'nyama', 15, 'available', 'menu_698454ac840732.33998319.jpg'),
(6, 4, 'pilau', 'main-course', 7000.00, 'nyama', 15, 'available', 'menu_6984716797a6a0.02317666.jpg'),
(7, 5, 'chipsi', 'beverage', 5000.00, 'nyama', 15, 'available', 'menu_6988c668c58842.13230676.png');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` enum('read','unread') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `fullname`, `email`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 'BARAKA ORWANDA', 'barakaorwanda@gmail.com', 'registration matter', 'i need a help on registrating my restaurant', 'unread', '2026-02-10 13:35:52'),
(2, 'BARAKA ORWANDA', 'barakaorwanda@gmail.com', 'registration matter', 'i need a help on registrating my restaurant', 'unread', '2026-02-10 13:42:29'),
(3, 'BARAKA ORWANDA', 'barakaorwanda@gmail.com', 'registration matter', 'i need a help on registrating my restaurant', 'unread', '2026-02-10 13:43:42');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'confirmed',
  `inserted_at` datetime NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `qr_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `delivery_address`, `payment_method`, `phone`, `status`, `inserted_at`, `expires_at`, `qr_token`) VALUES
(1, 1, 10260.00, 'temeke', 'airtelmoney', '770860853', 'pending', '2026-02-08 17:12:31', '2026-02-08 19:12:31', '40ef908c2bc4074138d6728992f0496d'),
(2, 2, 7900.00, 'keko', 'mpesa', '750137089', 'confirmed', '2026-02-10 10:01:13', '2026-02-10 12:01:13', 'fa5d4f314fdd84ba218e04f5256f1f7e'),
(3, 2, 16160.00, 'keko', 'mpesa', '750137089', 'pending', '2026-02-10 10:05:14', '2026-02-10 12:05:14', '9f75c4de854a7c508c593b3e6e98adf5');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `quantity`, `price`) VALUES
(1, 1, 6, 1, 7000.00),
(2, 2, 7, 1, 5000.00),
(3, 3, 7, 1, 5000.00),
(4, 3, 6, 1, 7000.00);

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `restaurant_name` varchar(90) NOT NULL,
  `restaurant_owner` varchar(90) NOT NULL,
  `cuisine_type` varchar(90) NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  `address` varchar(100) NOT NULL,
  `commission_rate` int(11) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `restaurant_image` varchar(90) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`id`, `restaurant_name`, `restaurant_owner`, `cuisine_type`, `phone_number`, `address`, `commission_rate`, `status`, `restaurant_image`) VALUES
(1, 'mama gee food', 'Mashaka kishinde', 'tanzanian', '750137082', 'Dodoma', 15, 'active', 'img_69833d9b1bd0f7.41771065.jpeg'),
(2, 'mama gee food', 'Mashaka kishinde', 'tanzanian', '750137082', 'Dodoma', 15, 'active', 'img_69833dc3c32a67.03625298.jpeg'),
(3, 'Mama Bonge', 'mama bonge Og', 'tanzanian', '675324788', 'Moshi', 15, 'active', 'img_69833e68403dc6.37809512.jpeg'),
(4, 'Mama Bonge', 'mama bonge Og', 'tanzanian', '770860853', 'Moshi', 15, 'active', 'img_69833ebb0683c7.97978576.jpeg'),
(5, 'Dodoma cafe', 'golden boy', 'fast-food', '770860853', 'dodoma', 15, 'active', 'img_6988c44e06e379.95859308.png');

-- --------------------------------------------------------

--
-- Table structure for table `scan_logs`
--

CREATE TABLE `scan_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `scanned_at` datetime DEFAULT NULL,
  `device` varchar(255) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scan_logs`
--

INSERT INTO `scan_logs` (`id`, `order_id`, `scanned_at`, `device`, `ip_address`) VALUES
(1, 2, '2026-02-07 08:17:15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '127.0.0.1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `role` enum('user','restaurant_owner','admin') DEFAULT 'user',
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone_number`, `role`, `password`, `created_at`) VALUES
(1, 'bloodgeo', 'jackson.hassan@example.com', '779860853', 'user', '$2y$10$ycnQzEvvrAqaGv1fnd76eOuMMtPmpmt1.Z13kCpU3VFyb38pfRCxe', '2026-02-04 11:03:14'),
(2, 'aloo', 'aloo@gmail.com', '750137089', 'user', '$2y$10$AkESFMOd8lHHWYQTq9u6a.GrsgLFkCSh6JSjWoJqJTbunzYR3X1qW', '2026-02-04 11:17:22'),
(3, 'Mr.GEO', 'godyezekiel35@gmail.com', '754749485', 'admin', '$2y$10$vN4yGADYNJwGl9eVr.3WEOAwQ0N4UYq73hjf1n4ZDWceuWl5ynx0O', '2026-02-09 16:21:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_menu_restaurant` (`restaurant_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scan_logs`
--
ALTER TABLE `scan_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `scan_logs`
--
ALTER TABLE `scan_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `fk_menu_restaurant` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
