-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 03:38 PM
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
-- Database: `php_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `admin_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `admin_name`, `admin_email`, `admin_password`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
(1, 'Uncategorized', '2025-04-08 21:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `is_from_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `user_id`, `message_text`, `is_from_admin`, `is_read`, `created_at`) VALUES
(1, 9, 'aa', 0, 1, '2025-04-23 20:52:42'),
(2, 9, 'hi', 0, 1, '2025-05-02 13:49:06'),
(3, 9, 'mana', 0, 1, '2025-05-02 13:49:17'),
(4, 9, 'jj', 0, 1, '2025-05-09 23:21:21'),
(5, 9, 'jj', 1, 1, '2025-05-09 23:21:33'),
(6, 12, 'hhh', 0, 1, '2025-05-12 08:15:30'),
(7, 12, 'nananan', 1, 1, '2025-05-12 08:15:39'),
(8, 12, 'hello', 0, 1, '2025-05-12 09:21:54'),
(9, 12, 'hi how can i services u', 1, 1, '2025-05-12 09:22:16');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL COMMENT '\r\n',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `rental_length` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','canceled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` enum('credit_card','cash_on_delivery') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Customer rental orders';

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `quantity`, `rental_length`, `total_price`, `status`, `created_at`, `updated_at`, `payment_method`) VALUES
(1, 2, 16, 1, 1, 9000.00, 'pending', '2025-04-04 19:01:22', '2025-04-04 20:01:58', 'credit_card'),
(2, 2, 16, 1, 1, 9000.00, 'pending', '2025-04-04 19:54:36', '2025-04-04 20:01:58', 'credit_card'),
(3, 2, 5, 1, 1, 12000.00, 'pending', '2025-04-04 19:55:50', '2025-04-04 20:01:58', 'credit_card'),
(4, 2, 32, 1, 1, 5000.00, 'pending', '2025-04-04 20:12:12', '2025-04-04 20:12:12', 'credit_card'),
(5, 2, 20, 1, 1, 20000.00, 'pending', '2025-04-04 20:44:04', '2025-04-04 20:44:04', 'cash_on_delivery'),
(6, 2, 16, 1, 1, 9000.00, 'pending', '2025-04-05 08:33:43', '2025-04-05 08:33:43', 'cash_on_delivery'),
(7, 2, 3, 1, 1, 18000.00, 'shipped', '2025-04-07 17:47:43', '2025-04-11 09:05:34', 'cash_on_delivery'),
(8, 2, 7, 1, 1, 18000.00, 'delivered', '2025-04-07 17:47:43', '2025-04-11 09:05:52', 'cash_on_delivery'),
(9, 2, 14, 1, 1, 14000.00, 'pending', '2025-04-13 09:39:13', '2025-04-13 09:39:13', 'cash_on_delivery'),
(10, 9, 15, 1, 1, 13000.00, 'pending', '2025-04-15 16:46:15', '2025-04-15 16:46:15', 'cash_on_delivery'),
(11, 9, 1, 1, 1, 12000.00, 'pending', '2025-04-15 16:46:15', '2025-04-15 16:46:15', 'cash_on_delivery'),
(12, 9, 2, 1, 1, 15000.00, 'pending', '2025-04-15 16:46:15', '2025-04-15 16:46:15', 'cash_on_delivery'),
(13, 9, 1, 1, 1, 12000.00, 'pending', '2025-04-16 22:07:55', '2025-04-16 22:07:55', 'cash_on_delivery'),
(14, 9, 16, 50, 1, 450000.00, 'pending', '2025-04-17 10:07:17', '2025-04-17 10:07:17', 'cash_on_delivery'),
(15, 10, 19, 1, 1, 7000.00, 'shipped', '2025-04-17 10:35:58', '2025-04-17 10:38:53', 'cash_on_delivery'),
(16, 9, 17, 1, 1, 10000.00, 'pending', '2025-05-02 14:07:06', '2025-05-02 14:07:06', 'cash_on_delivery'),
(17, 9, 15, 1, 1, 13000.00, 'pending', '2025-05-02 14:11:14', '2025-05-02 14:11:14', 'cash_on_delivery'),
(18, 9, 17, 1, 1, 9000.00, 'pending', '2025-05-03 19:21:00', '2025-05-03 19:21:00', 'cash_on_delivery'),
(19, 9, 16, 1, 1, 7650.00, 'pending', '2025-05-09 22:27:02', '2025-05-09 22:27:02', 'cash_on_delivery'),
(20, 9, 9, 1, 1, 12750.00, 'pending', '2025-05-09 22:34:25', '2025-05-09 22:34:25', 'cash_on_delivery'),
(21, 9, 16, 1, 1, 8100.00, 'pending', '2025-05-09 22:59:32', '2025-05-09 22:59:32', 'cash_on_delivery'),
(22, 9, 23, 1, 1, 18000.00, 'pending', '2025-05-09 23:12:26', '2025-05-09 23:12:26', 'cash_on_delivery'),
(23, 9, 14, 1, 1, 12600.00, 'pending', '2025-05-09 23:18:23', '2025-05-09 23:18:23', 'cash_on_delivery'),
(24, 6, 29, 1, 1, 11400.00, 'pending', '2025-05-09 23:27:39', '2025-05-09 23:27:39', 'cash_on_delivery'),
(25, 12, 23, 1, 1, 19000.00, 'pending', '2025-05-10 11:27:27', '2025-05-10 11:27:27', 'cash_on_delivery'),
(26, 12, 39, 1, 1, 9500.00, 'pending', '2025-05-10 16:51:08', '2025-05-10 16:51:08', 'cash_on_delivery'),
(27, 12, 39, 1, 1, 9500.00, 'pending', '2025-05-11 09:36:50', '2025-05-11 09:36:50', 'cash_on_delivery'),
(28, 12, 19, 1, 1, 6650.00, 'pending', '2025-05-12 08:14:29', '2025-05-12 08:14:29', 'cash_on_delivery'),
(29, 12, 17, 1, 1, 9000.00, 'pending', '2025-05-12 08:15:05', '2025-05-12 08:15:05', 'cash_on_delivery'),
(30, 12, 16, 1, 1, 8100.00, 'processing', '2025-05-12 09:18:12', '2025-05-12 09:18:32', 'cash_on_delivery'),
(31, 9, 39, 2, 1, 18000.00, 'pending', '2025-05-15 18:40:17', '2025-05-15 18:40:17', 'cash_on_delivery'),
(32, 9, 35, 1, 1, 22500.00, 'pending', '2025-05-15 18:40:17', '2025-05-15 18:40:17', 'cash_on_delivery'),
(33, 9, 42, 1, 1, 117000.00, 'pending', '2025-05-15 18:41:39', '2025-05-15 18:41:39', 'cash_on_delivery'),
(34, 9, 29, 1, 1, 10200.00, 'pending', '2025-05-15 18:46:24', '2025-05-15 18:46:24', 'cash_on_delivery'),
(35, 9, 45, 1, 1, 10200.00, 'pending', '2025-05-15 18:51:03', '2025-05-15 18:51:03', 'cash_on_delivery'),
(36, 9, 2, 1, 1, 12750.00, 'pending', '2025-05-15 19:06:41', '2025-05-15 19:06:41', 'cash_on_delivery'),
(37, 9, 45, 1, 1, 10200.00, 'pending', '2025-05-15 19:10:16', '2025-05-15 19:10:16', 'cash_on_delivery'),
(38, 9, 39, 1, 1, 8500.00, 'pending', '2025-05-15 19:33:07', '2025-05-15 19:33:07', 'cash_on_delivery');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','cash_on_delivery') NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Order payment information';

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `user_id`, `payment_amount`, `payment_method`, `payment_status`, `payment_date`) VALUES
(1, 4, 2, 5000.00, 'credit_card', 'completed', '2025-04-04 20:12:12'),
(2, 5, 2, 20000.00, 'cash_on_delivery', 'completed', '2025-04-04 20:44:04'),
(3, 6, 2, 9000.00, 'cash_on_delivery', 'completed', '2025-04-05 08:33:43'),
(4, 7, 2, 18000.00, 'cash_on_delivery', 'completed', '2025-04-07 17:47:43'),
(5, 8, 2, 18000.00, 'cash_on_delivery', 'completed', '2025-04-07 17:47:43'),
(6, 9, 2, 14000.00, 'cash_on_delivery', 'completed', '2025-04-13 09:39:13'),
(7, 10, 9, 13000.00, 'cash_on_delivery', 'completed', '2025-04-15 16:46:15'),
(8, 11, 9, 12000.00, 'cash_on_delivery', 'completed', '2025-04-15 16:46:15'),
(9, 12, 9, 15000.00, 'cash_on_delivery', 'completed', '2025-04-15 16:46:15'),
(10, 13, 9, 12000.00, 'cash_on_delivery', 'completed', '2025-04-16 22:07:55'),
(11, 14, 9, 450000.00, 'cash_on_delivery', 'completed', '2025-04-17 10:07:17'),
(12, 15, 10, 7000.00, 'cash_on_delivery', 'completed', '2025-04-17 10:35:58'),
(13, 16, 9, 10000.00, 'cash_on_delivery', 'completed', '2025-05-02 14:07:06'),
(14, 17, 9, 13000.00, 'cash_on_delivery', 'completed', '2025-05-02 14:11:14'),
(15, 18, 9, 9000.00, 'cash_on_delivery', 'completed', '2025-05-03 19:21:00'),
(16, 19, 9, 7650.00, 'cash_on_delivery', 'completed', '2025-05-09 22:27:02'),
(17, 20, 9, 12750.00, 'cash_on_delivery', 'completed', '2025-05-09 22:34:25'),
(18, 21, 9, 8100.00, 'cash_on_delivery', 'completed', '2025-05-09 22:59:32'),
(19, 22, 9, 18000.00, 'cash_on_delivery', 'completed', '2025-05-09 23:12:26'),
(20, 23, 9, 12600.00, 'cash_on_delivery', 'completed', '2025-05-09 23:18:23'),
(21, 24, 6, 11400.00, 'cash_on_delivery', 'completed', '2025-05-09 23:27:39'),
(22, 25, 12, 19000.00, 'cash_on_delivery', 'completed', '2025-05-10 11:27:27'),
(23, 26, 12, 9500.00, 'cash_on_delivery', 'completed', '2025-05-10 16:51:08'),
(24, 27, 12, 9500.00, 'cash_on_delivery', 'completed', '2025-05-11 09:36:50'),
(25, 28, 12, 6650.00, 'cash_on_delivery', 'completed', '2025-05-12 08:14:29'),
(26, 29, 12, 9000.00, 'cash_on_delivery', 'completed', '2025-05-12 08:15:05'),
(27, 30, 12, 8100.00, 'cash_on_delivery', 'completed', '2025-05-12 09:18:12');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_image` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_image2` varchar(255) DEFAULT NULL,
  `product_image3` varchar(255) DEFAULT NULL,
  `product_image4` varchar(255) DEFAULT NULL,
  `product_quantity` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `product_price`, `product_description`, `product_image`, `category`, `created_at`, `product_image2`, `product_image3`, `product_image4`, `product_quantity`) VALUES
(1, 'Sofa', 12000.00, 'This is a modern sectional sofa set designed for a spacious living room. The L-shaped design provides ample seating space, making it perfect for large families or gatherings. The deep gray upholstery gives a contemporary and sophisticated look, while the matching ottoman adds comfort and functionality.\n\nThe plush cushions and cozy fabric ensure a relaxing experience. The living room features neutral tones, complementing the sleek gray area rug and minimalistic decor. The gallery below showcases different sectional sofa styles, offering variations in color, fabric, and arrangement to suit different aesthetics.\n\nThis setup is ideal for anyone looking for luxury, comfort, and modern elegance in their home. 🏡✨\n\n\n\n\n\n\n\n', 'sofaa.jpeg', 'sofas', '2025-02-19 16:27:10', 'sofa23.jpeg', 'sofa24.jpeg', 'sofa25.jpeg', 0),
(2, 'Coffee Table', 15000.00, 'Elegant dining table for a modern look.', 'table.jpeg', 'coffeetables', '2025-02-19 19:11:43', 'cof1.jpeg', 'cof2.jpeg', 'cof3.jpeg', 5),
(3, 'Accent Chair', 18000.00, 'Modern Accent Chair', 'acc16.jpeg', 'accentchair', '2025-02-19 19:11:43', 'acc15.jpeg', 'acc17.jpeg', 'acc18.jpeg', 6),
(5, 'Round Dining Table', 12000.00, 'Comfortable luxury sofa for your living room.', 'round.jpeg', 'round', '2025-02-19 19:22:48', 'round5.jpeg', 'round6.jpeg', 'round7.jpeg', 5),
(6, 'Rectangle Dining Table', 15000.00, 'Elegant dining table for a modern look.', 'rec.jpeg', 'rectangle', '2025-02-19 19:22:48', 'ree1.jpeg', 'ree2.jpeg', 'ree3.jpeg', 10),
(7, 'Square Dining Table\n', 18000.00, 'King-size bed with a soft mattress.', 'sq.jpeg', 'square', '2025-02-19 19:22:48', 'sg2.jpeg', 'square1.jpeg', 'square2.jpeg', 9),
(8, 'Bedframe', 12000.00, 'Comfortable luxury sofa for your living room.', 'frames.jpeg', 'bedframe', '2025-02-19 19:37:24', 'frame4.jpeg', 'frame5.jpeg', 'frame6.jpeg', 0),
(9, 'Dresser', 15000.00, 'Elegant dining table for a modern look.', 'dr.jpeg', 'dresser', '2025-02-19 19:37:24', 'dr1.jpeg', 'dr11.jpeg', 'dr111.jpeg', 8),
(10, 'Side Table', 18000.00, 'King-size bed with a soft mattress.', 'side.jpeg', 'sidetable', '2025-02-19 19:37:24', 'side1.jpeg', 'side11.jpeg', 'side111.jpeg', 10),
(11, 'Office Chair', 15000.00, 'Elegant dining table for a modern look.', 'chairs.jpeg', 'officechair', '2025-02-19 19:37:24', 'chair1.jpeg', 'chair2.jpeg', 'chair3.jpeg', 10),
(12, 'Desk', 18000.00, 'King-size bed with a soft mattress.', 'des1.jpeg', 'desk', '2025-02-19 19:37:24', 'des2.jpeg', 'des3.jpeg', 'des2.jpeg', 1),
(13, 'Corner Desk', 18000.00, 'King-size bed with a soft mattress.', 'cr7.jpeg', 'cornerdesk', '2025-02-19 19:37:24', 'cr7.jpeg', 'cr8.jpeg', 'cr9.jpeg', 10),
(14, 'Sofa', 14000.00, 'Comfortable luxury sofas for your living room.', 'sofa1.jpeg', 'sofas', '2025-02-21 20:39:21', 'sofat.jpeg', 'sofatt.jpeg', 'sofattt.jpeg', 5),
(15, 'Sofa', 13000.00, 'Comfortable luxury sofas for your living room.', 'sofa2.jpeg', 'sofas', '2025-02-21 20:39:53', 'sofad.jpeg', 'sofadd.jpeg', 'sofaddd.jpeg', 8),
(16, 'Coffee Table', 9000.00, 'Elegant dining table for a modern look.', 'coffee2.jpeg', 'coffeetables', '2025-02-22 10:35:16', 'cof4.jpeg', 'cof5.jpeg', 'cof6.jpeg', 7),
(17, 'Coffee Table', 10000.00, 'Elegant dining table for a modern look.', 'coffee3.jpeg', 'coffeetables', '2025-02-22 10:35:16', 'cof7.jpeg', 'cof8.jpeg', 'cof9.jpeg', 1),
(18, 'Accent Chair', 5000.00, 'Modern Accent Chair', 'acc4.jpeg', 'accentchair', '2025-02-22 10:38:48', 'acc12.jpeg', 'acc13.jpeg', 'acc14.jpeg', 10),
(19, 'Accent Chair', 7000.00, 'Modern Accent Chair', 'acc2.jpeg', 'accentchair', '2025-02-22 10:38:48', 'acc9.jpeg', 'acc10.jpeg', 'acc11.jpeg', 5),
(20, 'Round Dining Table', 20000.00, 'Round Dining Table', 'round3.jpeg', 'round', '2025-02-22 10:52:46', 'round8.jpeg', 'round9.jpeg', 'round11.jpeg', 7),
(21, 'Round Dining Table', 20000.00, 'Round Dining Table', 'round2.jpg', 'round', '2025-02-22 10:52:46', 'r1.jpeg', 'r2.jpeg', 'r3.jpeg', 10),
(22, 'Rectangle Dining Table', 20000.00, 'Rectangle Dining Table', 'rec2.jpeg', 'rectangle', '2025-02-22 10:55:22', 'ree4.jpeg', 'ree5.jpeg', 'ree6.jpeg', 10),
(23, 'Rectangle Dining Table', 20000.00, 'Rectangle Dining Table', 'rec3.jpeg', 'rectangle', '2025-02-22 10:55:22', 'ree7.jpeg', 'ree8.jpeg', 'ree9.jpeg', 7),
(24, 'Square Dining Table\r\n', 20000.00, 'Square Dining Table\r\n', 'square3.jpeg', 'square', '2025-02-22 10:58:39', 'sq5.jpeg', 'sq6.jpeg', 'sq7.jpeg', 10),
(25, 'Square Dining Table\r\n', 20000.00, 'Square Dining Table\r\n', 'sg3.jpeg', 'square', '2025-02-22 10:58:39', 'sg4.jpeg', 'sg5.jpeg', 'sg6.jpeg', 10),
(26, 'Bedframe', 12000.00, 'Bedframe', 'bed3.jpeg', 'bedframe', '2025-02-22 11:14:54', 'frame1.jpeg', 'frame2.jpeg', 'frame3.jpeg', 8),
(27, 'Bedframe', 12000.00, 'Bedframe', 'bed2.jpeg', 'bedframe', '2025-02-22 11:14:54', 'frame7.jpeg', 'frame8.jpeg', 'frame9.jpeg', 10),
(28, 'Dresser', 12000.00, 'Dresser', 'dr2.jpeg', 'dresser', '2025-02-22 11:18:34', 'dr22.jpeg', 'dr222.jpeg', 'dr2222.jpeg', 10),
(29, 'Dresser', 12000.00, 'Dresser', 'dr3.jpeg', 'dresser', '2025-02-22 11:18:34', 'dr2.jpeg', 'dr22.jpeg', 'dr222.jpeg', 8),
(30, 'Side Table', 10000.00, 'Side Table', 'side2.jpeg', 'sidetable', '2025-02-22 11:21:06', 'side5.jpeg', 'side6.jpeg', 'side7.jpeg', 10),
(31, 'Side Table', 10000.00, 'Side Table', 'sid3.jpeg', 'sidetable', '2025-02-22 11:21:06', 'side8.jpeg', 'side9.jpeg', 'side10.jpeg', 10),
(32, 'Office Chair', 5000.00, 'Office Chair', 'office2.jpeg', 'officechair', '2025-02-22 11:24:21', 'chair4.jpeg', 'chair5.jpeg', 'chair6.jpeg', 9),
(33, 'Office Chair', 5000.00, 'Office Chair', 'office3.jpeg', 'officechair', '2025-02-22 11:24:21', 'o1.jpeg', 'o2.jpeg', 'o3.jpeg', 10),
(34, 'Desk', 30000.00, 'Desk', 'desk3.jpeg', 'desk', '2025-02-22 11:26:41', 'desk1.jpeg', 'desk11.jpeg', 'desk111.jpeg', 10),
(35, 'Desk', 25000.00, 'Desk', 'des5.jpeg', 'desk', '2025-02-22 11:26:41', 'des4.jpeg', 'des6.jpeg', 'des7.jpeg', 9),
(36, 'Corner Desk', 20000.00, 'Corner Desk', 'cr2.jpeg', 'cornerdesk', '2025-02-22 11:28:37', 'corner1.jpeg', 'corner3.jpeg', 'corner2.jpeg', 10),
(37, 'Corner Desk', 20000.00, 'Corner Desk', 'cr3.jpeg\r\n', 'cornerdesk', '2025-02-22 11:28:37', 'cr6.jpeg', 'cr5.jpeg', 'cr6.jpeg', 10),
(38, 'Sofa', 12000.00, 'Good sofa', 'sofa11.jpeg', 'sofas', '2025-03-04 22:20:55', 'sofaj.jpeg', 'sofajj.jpeg', 'sofajjj.jpeg', 9),
(39, 'Coffee table', 10000.00, 'GOOD QUALITY ', 'cof10.jpeg', 'coffeetables', '2025-03-05 17:47:00', 'cof11.jpeg', 'cof12.jpeg', 'cof13.jpeg', 8),
(40, 'Accent Chair', 8000.00, 'GOOD QUALITY', 'acc5.jpeg', 'accentchair', '2025-03-05 23:42:10', 'acc6.jpeg', 'acc7.jpeg', 'acc8.jpeg', 10),
(41, 'Round Dining Table ', 18000.00, 'good quality ', 'r4.jpeg', 'round', '2025-03-06 23:09:32', 'r5.jpeg', 'r6.jpeg', 'r7.jpeg', 10),
(42, 'Rectangle Dining Table ', 130000.00, 'good quality ', 'ree11.jpeg', 'rectangle', '2025-03-06 23:23:24', 'ree12.jpeg', 'ree13.jpeg', 'ree14.jpeg', 9),
(43, 'Square Dining Table ', 20000.00, 'good quality', 'sq8.jpeg', 'square', '2025-03-06 23:42:43', 'sq9.jpeg', 'sq10.jpeg', 'sq11.jpeg', 9),
(44, 'Bedframe', 7000.00, 'good', 'frame10.jpeg', 'bedframe', '2025-03-06 23:58:42', 'frame11.jpeg', 'frame12.jpeg', 'frame13.jpeg', 10),
(45, 'Dresser ', 12000.00, 'good one', 'dr5.jpeg', 'dresser', '2025-03-08 11:06:22', 'dr33.jpeg', 'dr333.jpeg', 'dr4.jpeg', 8),
(46, 'Side Table ', 10000.00, 'good quality ', 'sidee12.jpeg', 'sidetable', '2025-03-08 11:29:46', 'side13.jpeg', 'side14.jpeg', 'side15.jpeg', 10),
(47, 'Office Chair ', 6000.00, 'GOOD ', 'chair7.jpeg', 'officechair', '2025-03-08 12:04:18', 'chair8.jpeg', 'chair9.jpeg', 'chair10.jpeg', 10),
(48, 'Office Desk ', 18000.00, 'good ', 'des8.jpeg', 'desk', '2025-03-08 12:33:47', 'des9.jpeg', 'des10.jpeg', 'des11.jpeg', 10),
(49, 'Corner Desk', 17000.00, 'good  corner desk ❤️', 'cr10.jpeg', 'cornerdesk', '2025-03-08 12:50:10', 'cr11.jpeg', 'cr12.jpeg', 'cr13.jpeg', 9);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `loyalty_points` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_email`, `user_password`, `loyalty_points`) VALUES
(1, 'radia', 'bendjima', 'radia2004', 10),
(2, 'rodina', 'marie@gmail.com', 'marie2012', 10),
(3, 'radia', 'bendima@gmail.com', 'bendjima2004', 10),
(4, 'Administrator', 'admin@gmail.com', '$2y$10$cHj5kSb4dCZmvyuIyWvAaOKJ8.3Uaji7LQO6Yvh7Ec.Z98AyMjWky', 10),
(5, 'radia', 'radiab2@gmail.com', 'radiab2004', 10),
(6, 'mars', 'mars@gmail.com', 'mars2004', 40),
(7, 'hey', 'hey@gmail.com', 'hey2005', 10),
(8, 'rodina', 'rodina@gmail.com', 'rodina2009', 10),
(9, 'aa', 'aa@gmail.com', '123456', 338),
(10, 'hh', 'hh@gmail.com', '456789', 10),
(11, 'ss', 'ss@gmail.com', '123456', 50),
(12, 'yy', 'yy@gmail.com', '123456', 76);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admins_email` (`admin_email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `fk_message_user` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_user` (`user_id`),
  ADD KEY `fk_order_product` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payment_order` (`order_id`),
  ADD KEY `fk_payment_user` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_message_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
