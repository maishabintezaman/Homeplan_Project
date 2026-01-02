-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 02, 2026 at 10:38 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `homeplan`
--

-- --------------------------------------------------------

--
-- Table structure for table `architects`
--

CREATE TABLE `architects` (
  `user_id` int(11) NOT NULL,
  `license_no` varchar(80) NOT NULL,
  `years` int(11) NOT NULL DEFAULT 0,
  `portfolio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `architect_expertise`
--

CREATE TABLE `architect_expertise` (
  `id` int(11) NOT NULL,
  `architect_user_id` int(11) NOT NULL,
  `expertise` varchar(80) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `architect_expertise`
--

INSERT INTO `architect_expertise` (`id`, `architect_user_id`, `expertise`, `created_at`) VALUES
(1, 8, 'Residential Design', '2025-12-22 20:23:46'),
(2, 8, 'Commercial', '2025-12-22 20:23:46'),
(3, 5, 'Landscape', '2025-12-22 20:23:46');

-- --------------------------------------------------------

--
-- Table structure for table `architect_profiles`
--

CREATE TABLE `architect_profiles` (
  `architect_id` int(11) NOT NULL,
  `certificate_number` varchar(60) NOT NULL,
  `years_experience` int(11) NOT NULL DEFAULT 0,
  `expertise` varchar(255) NOT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `architect_profiles`
--

INSERT INTO `architect_profiles` (`architect_id`, `certificate_number`, `years_experience`, `expertise`, `portfolio_url`, `created_at`) VALUES
(5, 'BD-ARC-45821', 6, 'Commercial Buildings', '', '2025-12-22 16:33:31'),
(8, 'AR23456', 7, 'Residential', NULL, '2025-12-20 22:28:52'),
(9, 'AR123456', 3, 'Commercial,Residential', NULL, '2025-12-23 15:16:08');

-- --------------------------------------------------------

--
-- Table structure for table `architect_projects`
--

CREATE TABLE `architect_projects` (
  `project_id` int(11) NOT NULL,
  `architect_id` int(11) NOT NULL,
  `title` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `architect_projects`
--

INSERT INTO `architect_projects` (`project_id`, `architect_id`, `title`, `description`, `image_url`, `created_at`) VALUES
(1, 8, 'Lakeview Apartments', NULL, '/homeplan/uploads/projects/afnan_1.jpg', '2025-12-22 17:46:19'),
(2, 8, 'South Breeze', NULL, '/homeplan/uploads/projects/afnan_2.jpg', '2025-12-22 17:46:19'),
(10, 8, 'Modern Duplex', '', '/homeplan/uploads/projects/arch_8_1767267366_13ae3a4c.jpg', '2026-01-01 11:36:06'),
(11, 5, 'Lakeview Building', 'A modern commercial+residential building with all new features', '/homeplan/uploads/projects/arch_5_1767332721_eebb2212.webp', '2026-01-02 05:45:21');

-- --------------------------------------------------------

--
-- Table structure for table `architect_project_images`
--

CREATE TABLE `architect_project_images` (
  `image_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(200) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `architect_requests`
--

CREATE TABLE `architect_requests` (
  `request_id` int(11) NOT NULL,
  `client_user_id` int(11) NOT NULL,
  `architect_user_id` int(11) NOT NULL,
  `project_type` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `area_sqft` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `budget` decimal(12,2) DEFAULT NULL,
  `preferred_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `architect_requests`
--

INSERT INTO `architect_requests` (`request_id`, `client_user_id`, `architect_user_id`, `project_type`, `location`, `area_sqft`, `message`, `status`, `created_at`, `budget`, `preferred_date`) VALUES
(1, 1, 8, 'Residential', 'Mirpur,Dhaka', 2600, NULL, 'accepted', '2025-12-22 20:53:44', 300000.00, '2026-01-31'),
(2, 1, 5, 'Renovation', 'Gulshan,Dhaka', 7600, NULL, 'accepted', '2025-12-22 21:32:44', 100000000.00, '2026-01-01'),
(3, 1, 8, 'Renovation', 'Gulshan,Dhaka', 2790, NULL, 'accepted', '2025-12-22 22:00:33', 2300000.00, '2026-01-01'),
(4, 1, 8, 'Commercial', 'Gulshan,Dhaka', 3500, NULL, 'rejected', '2025-12-22 22:14:55', 30000000.00, '2026-01-01'),
(5, 3, 8, 'Renovation', 'Gulshan,Dhaka', 3500, '', 'accepted', '2026-01-01 11:18:46', 450000.00, '2026-01-02'),
(6, 1, 8, 'Commercial', 'Gulshan,Dhaka', 2600, '', 'accepted', '2026-01-02 04:28:57', 4000000.00, '2026-03-01'),
(7, 3, 8, 'Residential', 'Gulshan,Dhaka', 2400, '', 'rejected', '2026-01-02 05:16:55', 9000000.00, '2026-03-02');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `property_id`, `client_id`, `provider_id`, `status`, `created_at`) VALUES
(1, 8, 3, 2, 'pending', '2025-12-31 19:28:46'),
(2, 6, 3, 2, 'pending', '2025-12-31 19:28:54'),
(4, 5, 3, 2, 'pending', '2025-12-31 19:33:48'),
(6, 2, 3, 2, 'pending', '2025-12-31 19:53:57');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`) VALUES
(1),
(3);

-- --------------------------------------------------------

--
-- Table structure for table `developers`
--

CREATE TABLE `developers` (
  `user_id` int(11) NOT NULL,
  `reg_no` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `developer_land_requests`
--

CREATE TABLE `developer_land_requests` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `developer_id` int(11) NOT NULL,
  `area_unit` varchar(20) NOT NULL,
  `area_value` decimal(10,2) NOT NULL,
  `location_text` varchar(255) NOT NULL,
  `asking_price` decimal(12,2) NOT NULL,
  `road_width` decimal(10,2) DEFAULT NULL,
  `ownership_type` varchar(60) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `developer_land_requests`
--

INSERT INTO `developer_land_requests` (`id`, `request_id`, `developer_id`, `area_unit`, `area_value`, `location_text`, `asking_price`, `road_width`, `ownership_type`, `notes`, `created_at`) VALUES
(1, 19, 10, 'sqft', 3500.00, 'Banani,Dhaka', 3000000.00, 70.00, 'single', '', '2026-01-01 11:18:01'),
(2, 21, 10, 'katha', 6.00, 'Gulshan,Dhaka', 1100000000.00, 70.00, 'single', '', '2026-01-02 05:16:15'),
(3, 22, 10, 'katha', 6.00, 'Gulshan,Dhaka', 70000000.00, 60.00, 'single', '', '2026-01-02 05:36:12'),
(4, 23, 2, 'sqft', 5000.00, 'Mohammadpur,Dhaka', 6000000.00, 70.00, 'single', '', '2026-01-02 05:37:38');

-- --------------------------------------------------------

--
-- Table structure for table `developer_profiles`
--

CREATE TABLE `developer_profiles` (
  `developer_id` int(11) NOT NULL,
  `license_no` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `developer_projects`
--

CREATE TABLE `developer_projects` (
  `project_id` int(11) NOT NULL,
  `developer_id` int(11) NOT NULL,
  `title` varchar(120) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `developer_projects`
--

INSERT INTO `developer_projects` (`project_id`, `developer_id`, `title`, `location`, `description`, `image_url`, `created_at`) VALUES
(1, 2, 'Navana Baridhara', 'Baridhara,Dhaka', '', '/homeplan/uploads/developer_projects/dev_2_1767330682_24c01d5c.webp', '2026-01-02 05:11:22');

-- --------------------------------------------------------

--
-- Table structure for table `expertise`
--

CREATE TABLE `expertise` (
  `expertise_id` int(11) NOT NULL,
  `type` varchar(120) NOT NULL,
  `years` int(11) DEFAULT 0,
  `number_of_clients` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expertise`
--

INSERT INTO `expertise` (`expertise_id`, `type`, `years`, `number_of_clients`) VALUES
(1, 'Residential', 0, 0),
(2, 'Commercial', 0, 0),
(3, 'Renovation', 0, 0),
(4, 'Mosque', 0, 0),
(5, 'Interior Design', 0, 0),
(6, 'Landscape', 0, 0),
(7, 'Structural', 0, 0),
(8, '3D Visualization', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `flat_search_requests`
--

CREATE TABLE `flat_search_requests` (
  `request_id` int(11) NOT NULL,
  `budget` decimal(12,2) DEFAULT NULL,
  `area_sqft` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interior_designers`
--

CREATE TABLE `interior_designers` (
  `user_id` int(11) NOT NULL,
  `license_no` varchar(80) NOT NULL,
  `style` varchar(80) DEFAULT NULL,
  `portfolio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interior_profiles`
--

CREATE TABLE `interior_profiles` (
  `interior_id` int(11) NOT NULL,
  `reg_no` varchar(100) NOT NULL,
  `style` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interior_requests`
--

CREATE TABLE `interior_requests` (
  `request_id` int(11) NOT NULL,
  `style_pref` varchar(120) DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `land_offers`
--

CREATE TABLE `land_offers` (
  `request_id` int(11) NOT NULL,
  `project_type` varchar(120) DEFAULT NULL,
  `land_area_sqft` int(11) DEFAULT NULL,
  `road_width` int(11) DEFAULT NULL,
  `ownership_type` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `land_requests`
--

CREATE TABLE `land_requests` (
  `request_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `developer_id` int(11) NOT NULL,
  `area_unit` enum('katha','sqft','sqm','decimal') NOT NULL,
  `area_value` decimal(12,2) NOT NULL,
  `location_text` varchar(220) NOT NULL,
  `address_line` varchar(240) DEFAULT NULL,
  `map_link` text DEFAULT NULL,
  `asking_price` decimal(16,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'BDT',
  `land_type` enum('residential','commercial','industrial','agri','mixed') NOT NULL DEFAULT 'residential',
  `frontage_ft` decimal(10,2) DEFAULT NULL,
  `road_width_ft` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('submitted','reviewing','accepted','rejected') NOT NULL DEFAULT 'submitted',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `house` varchar(60) DEFAULT NULL,
  `street` varchar(80) DEFAULT NULL,
  `city` varchar(80) NOT NULL,
  `area_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `location_name` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `house`, `street`, `city`, `area_code`, `created_at`, `location_name`) VALUES
(1, NULL, 'Dhanmondi', 'Dhaka', NULL, '2025-12-18 16:43:26', NULL),
(2, NULL, 'Uttara', 'Dhaka', NULL, '2025-12-18 16:43:26', NULL),
(3, NULL, 'Mirpur', 'Dhaka', NULL, '2025-12-18 16:43:26', NULL),
(4, NULL, 'Gulshan', 'Dhaka', NULL, '2025-12-18 16:43:26', NULL),
(5, NULL, 'Dhanmondi 27', 'Dhaka', '1209', '2025-12-22 16:34:10', NULL),
(6, NULL, 'Sector 10', 'Uttara', '1230', '2025-12-22 16:34:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `quality` varchar(80) DEFAULT NULL,
  `unit` varchar(40) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_requests`
--

CREATE TABLE `material_requests` (
  `request_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
  `budget` decimal(12,2) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_vendors`
--

CREATE TABLE `material_vendors` (
  `user_id` int(11) NOT NULL,
  `trade_license` varchar(80) DEFAULT NULL,
  `delivery_radius` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_vendor_profiles`
--

CREATE TABLE `material_vendor_profiles` (
  `vendor_id` int(11) NOT NULL,
  `trade_license` varchar(120) NOT NULL,
  `delivery_radius` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `sent_at` timestamp NULL DEFAULT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `status`, `sent_at`, `creation_date`) VALUES
(1, 1, 'Your request for \'Uttara Sector 10 Flat\' has been accepted by the provider.', 'read', NULL, '2025-12-22 21:45:27'),
(2, 1, 'Your request for \'Uttara Sector 10 Flat\' has been accepted by the provider.', 'read', NULL, '2025-12-22 21:47:09'),
(3, 1, 'Your request for \'Lakeview Apartments\' has been accepted by the provider.', 'read', NULL, '2025-12-22 21:47:15'),
(4, 1, 'Your request for \'Mirpur Sunshine Apartment\' has been rejected by the provider.', 'read', NULL, '2025-12-22 22:01:34'),
(5, 1, 'Your request for \'Modhumoti\' has been accepted by the provider.', 'read', NULL, '2025-12-23 17:11:51'),
(6, 1, 'Your request for \'Sagorika\' has been accepted by the provider.', 'read', NULL, '2025-12-24 05:25:49'),
(7, 2, 'New land request received from client ID #3', 'read', NULL, '2026-01-01 11:08:53'),
(8, 10, 'New land request received (Request ID #19)', 'read', NULL, '2026-01-01 11:18:01'),
(9, 8, 'New architect request: Renovation (Gulshan,Dhaka)', 'read', NULL, '2026-01-01 11:18:46'),
(10, 3, 'Your request (Renovation - Gulshan,Dhaka) was accepted by the architect.', 'unread', NULL, '2026-01-01 11:19:10'),
(11, 1, 'Your request (Commercial - Gulshan,Dhaka) was rejected by the architect.', 'unread', NULL, '2026-01-01 11:19:13'),
(12, 8, 'New architect request: Commercial (Gulshan,Dhaka)', 'read', NULL, '2026-01-02 04:28:57'),
(13, 1, 'Your request (Commercial - Gulshan,Dhaka) was accepted by the architect.', 'unread', NULL, '2026-01-02 04:32:40'),
(14, 10, 'New land request received (Request ID #21)', 'read', NULL, '2026-01-02 05:16:15'),
(15, 8, 'New architect request: Residential (Gulshan,Dhaka)', 'read', NULL, '2026-01-02 05:16:55'),
(16, 3, 'Your request (Residential - Gulshan,Dhaka) was rejected by the architect.', 'unread', NULL, '2026-01-02 05:17:14'),
(17, 10, 'New land request received (Request ID #22)', 'read', NULL, '2026-01-02 05:36:12'),
(18, 2, 'New land request received (Request ID #23)', 'read', NULL, '2026-01-02 05:37:38'),
(19, 1, 'Your request (Renovation - Gulshan,Dhaka) was accepted by the architect.', 'unread', NULL, '2026-01-02 05:43:19'),
(20, 2, 'New property request for \'South Breeze\' (Request ID #26)', 'read', NULL, '2026-01-02 08:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `developer_id` int(11) NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `category` varchar(60) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `budget_range` varchar(80) DEFAULT NULL,
  `status` enum('planned','ongoing','completed') NOT NULL DEFAULT 'completed',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `developer_id`, `title`, `description`, `location`, `category`, `summary`, `budget_range`, `status`, `created_at`) VALUES
(1, 10, 'Modern 6-Storey Residential Building', NULL, 'Dhanmondi, Dhaka', 'Residential', 'Earthquake-resistant building with basement parking.', 'BDT 4.5 – 6 Crore', 'completed', '2025-12-31 16:46:43');

-- --------------------------------------------------------

--
-- Table structure for table `project_images`
--

CREATE TABLE `project_images` (
  `image_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `image_url` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `property_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `project_name` varchar(160) DEFAULT NULL,
  `size_sqft` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `no_of_bedrooms` int(11) DEFAULT 0,
  `no_of_bathrooms` int(11) DEFAULT 0,
  `availability_status` enum('available','booked','sold') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`property_id`, `provider_id`, `location_id`, `project_name`, `size_sqft`, `price`, `no_of_bedrooms`, `no_of_bathrooms`, `availability_status`, `created_at`) VALUES
(1, 12, 1, 'Dhanmondi Lake View Apartment', 1450, 12000000.00, 3, 2, 'available', '2025-12-18 16:50:20'),
(2, 12, 2, 'Uttara Sector 10 Flat', 1100, 8500000.00, 2, 2, 'available', '2025-12-18 16:50:20'),
(3, 12, 3, 'Mirpur Sunshine Apartment', 1250, 8500000.00, 3, 2, 'available', '2025-12-18 16:56:05'),
(4, 12, 3, 'Mirpur Family Flat', 980, 6500000.00, 2, 2, 'available', '2025-12-18 16:56:05'),
(5, 12, 4, 'Lakeview Apartments', 3200, 10000000.00, 4, 3, 'available', '2025-12-20 21:06:22'),
(6, 12, 4, 'South Breeze', 2700, 120000000.00, 4, 4, 'available', '2025-12-22 22:13:11'),
(7, 12, 5, 'Sagorika', 3500, 3400000.00, 5, 4, 'available', '2025-12-22 22:13:39'),
(8, 12, 6, 'Modhumoti', 2500, 1000000.00, 3, 3, 'available', '2025-12-22 22:14:02'),
(9, 12, 5, 'Bashati Provati', 2400, 2000000.00, 3, 2, 'available', '2025-12-31 22:18:31'),
(10, 13, 5, 'Manjulika', 5000, 5000000.00, 4, 4, 'available', '2026-01-02 08:22:44');

-- --------------------------------------------------------

--
-- Table structure for table `providers`
--

CREATE TABLE `providers` (
  `provider_id` int(11) NOT NULL,
  `provider_type` enum('architect','developer','interior','material_vendor') NOT NULL,
  `firm_name` varchar(160) DEFAULT NULL,
  `license_no` varchar(80) DEFAULT NULL,
  `reg_no` varchar(80) DEFAULT NULL,
  `trade_license` varchar(80) DEFAULT NULL,
  `trade_certificate` varchar(120) DEFAULT NULL,
  `delivery_radius_km` int(11) DEFAULT NULL,
  `availability` enum('available','busy','offline') DEFAULT 'available',
  `workers` int(11) DEFAULT NULL,
  `studio_name` varchar(160) DEFAULT NULL,
  `style` varchar(120) DEFAULT NULL,
  `based_in_location_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `providers`
--

INSERT INTO `providers` (`provider_id`, `provider_type`, `firm_name`, `license_no`, `reg_no`, `trade_license`, `trade_certificate`, `delivery_radius_km`, `availability`, `workers`, `studio_name`, `style`, `based_in_location_id`) VALUES
(2, 'developer', NULL, NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, NULL),
(12, 'architect', NULL, NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `provider_expertise`
--

CREATE TABLE `provider_expertise` (
  `provider_id` int(11) NOT NULL,
  `expertise_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_materials`
--

CREATE TABLE `provider_materials` (
  `provider_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `availability` enum('in_stock','out_of_stock') DEFAULT 'in_stock'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `request_type` varchar(30) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `area_unit` varchar(20) DEFAULT NULL,
  `area_value` decimal(12,2) DEFAULT NULL,
  `location_text` varchar(255) DEFAULT NULL,
  `asking_price` decimal(14,2) DEFAULT NULL,
  `road_width` decimal(10,2) DEFAULT NULL,
  `ownership_type` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `client_id`, `provider_id`, `property_id`, `request_type`, `status`, `creation_date`, `area_unit`, `area_value`, `location_text`, `asking_price`, `road_width`, `ownership_type`, `notes`, `created_at`) VALUES
(9, 3, 2, 5, 'property', 'accepted', '2025-12-21 06:04:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(10, 3, 2, 1, 'property', 'rejected', '2025-12-22 17:49:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(11, 3, 2, 2, 'property', 'accepted', '2025-12-22 21:17:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(12, 3, 2, 3, 'property', 'rejected', '2025-12-22 22:00:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(13, 3, 2, 8, 'property', 'accepted', '2025-12-23 17:01:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(14, 3, 2, 7, 'property', 'accepted', '2025-12-24 05:22:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(15, 1, 2, 8, 'property', 'rejected', '2025-12-31 20:46:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(16, 1, 2, 4, 'property', 'accepted', '2025-12-31 20:47:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(17, 3, 4, NULL, 'developer_land', 'pending', '2026-01-01 10:58:35', 'katha', 7.00, 'Dhanmondi,Dhaka', 5000000.00, 70.00, 'Single', '', '2026-01-02 07:44:37'),
(18, 3, 10, NULL, 'developer_land', 'rejected', '2026-01-01 11:08:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(19, 3, 10, NULL, 'developer_land', 'pending', '2026-01-01 11:18:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(20, 1, 12, 9, 'property', 'accepted', '2026-01-02 04:28:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(21, 3, 10, NULL, 'developer_land', 'accepted', '2026-01-02 05:16:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(22, 1, 10, NULL, 'developer_land', 'pending', '2026-01-02 05:36:12', 'katha', 6.00, 'Gulshan,Dhaka', 70000000.00, 60.00, 'single', '', '2026-01-02 07:44:37'),
(23, 3, 2, NULL, 'developer_land', 'accepted', '2026-01-02 05:37:38', 'sqft', 5000.00, 'Mohammadpur,Dhaka', 6000000.00, 70.00, 'single', '', '2026-01-02 07:44:37'),
(24, 3, 2, 6, 'property', 'accepted', '2026-01-02 06:01:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(25, 1, 2, 7, 'property', 'rejected', '2026-01-02 07:01:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:44:37'),
(26, 1, 2, 6, 'property', 'accepted', '2026-01-02 08:39:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 08:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `request_assignments`
--

CREATE TABLE `request_assignments` (
  `request_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `architect_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_assignments`
--

INSERT INTO `request_assignments` (`request_id`, `provider_id`, `architect_id`, `property_id`, `assigned_at`) VALUES
(9, 2, NULL, 5, '2025-12-21 06:04:12'),
(10, 2, NULL, 1, '2025-12-22 17:49:41'),
(11, 2, NULL, 2, '2025-12-22 21:17:13'),
(12, 2, NULL, 3, '2025-12-22 22:00:05'),
(13, 2, NULL, 8, '2025-12-23 17:01:16'),
(14, 2, NULL, 7, '2025-12-24 05:22:21'),
(15, 2, NULL, 8, '2025-12-31 20:46:54'),
(16, 2, NULL, 4, '2025-12-31 20:47:08'),
(20, 12, NULL, 9, '2026-01-02 04:28:09'),
(24, 2, NULL, 6, '2026-01-02 06:01:47'),
(25, 2, NULL, 7, '2026-01-02 07:01:26'),
(26, 2, NULL, 6, '2026-01-02 08:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review_txt` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trade_requests`
--

CREATE TABLE `trade_requests` (
  `request_id` int(11) NOT NULL,
  `trade_type` varchar(120) DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role` enum('client','property_owner','developer','architect','material_provider','worker_provider','interior_designer','admin') NOT NULL,
  `provider_type` enum('developer','builder','contractor','architect') DEFAULT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `house` varchar(60) DEFAULT NULL,
  `street` varchar(80) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `area_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role`, `provider_type`, `full_name`, `email`, `phone`, `password`, `profile_pic`, `house`, `street`, `city`, `area_code`, `created_at`) VALUES
(1, 'client', NULL, 'Ahmed', 'ahmed@gmail.com', '01323444732', '$2y$10$C9mXBmsbSF9V9WKzs3S97ulJ7GdDdM67gaXACFkYbHKFzJ67t9Asa', NULL, NULL, NULL, NULL, NULL, '2025-12-18 15:54:29'),
(2, 'developer', NULL, 'Navana Real Estate', 'navanarealestate@gmail.com', '01948323444', '$2y$10$C9mXBmsbSF9V9WKzs3S97ulJ7GdDdM67gaXACFkYbHKFzJ67t9Asa', NULL, NULL, NULL, NULL, NULL, '2025-12-18 15:54:29'),
(3, 'client', NULL, 'Rahim Uddin', 'rahim@gmail.com', '01711111111', '$2y$10$C9mXBmsbSF9V9WKzs3S97ulJ7GdDdM67gaXACFkYbHKFzJ67t9Asa', NULL, NULL, NULL, 'Dhaka', NULL, '2025-12-22 16:32:52'),
(4, 'developer', NULL, 'Sheltech Properties', 'sheltech@gmail.com', '01722222222', '$2y$10$C9mXBmsbSF9V9WKzs3S97ulJ7GdDdM67gaXACFkYbHKFzJ67t9Asa', NULL, NULL, NULL, 'Dhaka', NULL, '2025-12-22 16:32:52'),
(5, 'architect', NULL, 'Farhana Islam', 'farhana.architect@gmail.com', '01733333333', '$2y$10$C9mXBmsbSF9V9WKzs3S97ulJ7GdDdM67gaXACFkYbHKFzJ67t9Asa', NULL, NULL, NULL, 'Dhaka', NULL, '2025-12-22 16:32:52'),
(8, 'architect', NULL, 'Afnan Kabir', 'afnan@gmail.com', '01715984312', '$2y$10$C9mXBmsbSF9V9WKzs3S97ulJ7GdDdM67gaXACFkYbHKFzJ67t9Asa', NULL, NULL, NULL, NULL, NULL, '2025-12-20 22:28:52'),
(9, 'architect', NULL, 'Dipro Sen', 'dipro@gmail.com', '01222222222', '$2y$10$C9mXBmsbSF9V9WKzs3S97ulJ7GdDdM67gaXACFkYbHKFzJ67t9Asa', NULL, NULL, NULL, NULL, NULL, '2025-12-23 15:16:08'),
(10, 'developer', 'developer', 'Abul Developers Ltd', 'abuldevelopers@gmail.com', '01711111111', '$2y$10$C9mXBmsbSF9V9WKzs3S97ulJ7GdDdM67gaXACFkYbHKFzJ67t9Asa', NULL, NULL, NULL, 'Dhaka', NULL, '2025-12-31 10:44:14'),
(12, 'property_owner', NULL, 'Kabir Zaman', 'kabirzaman@gmail.com', '01323444731', '$2y$10$C9mXBmsbSF9V9WKzs3S97ulJ7GdDdM67gaXACFkYbHKFzJ67t9Asa', NULL, NULL, NULL, NULL, NULL, '2025-12-31 20:51:17'),
(13, 'property_owner', NULL, 'Monir Mustafa', 'monirmustafa@gmail.com', '01948327129', '$2y$10$vM8z9glJLYvhPA9/9E4HK./KNmpO9SRjGHnlLJras7JvG7zFW5VEe', NULL, NULL, NULL, NULL, NULL, '2026-01-02 07:56:02');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_users_after_insert_clients` AFTER INSERT ON `users` FOR EACH ROW BEGIN
  IF NEW.role = 'client' THEN
    INSERT IGNORE INTO clients (client_id) VALUES (NEW.user_id);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_expertise`
--

CREATE TABLE `user_expertise` (
  `user_id` int(11) NOT NULL,
  `expertise_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_materials`
--

CREATE TABLE `vendor_materials` (
  `vendor_user_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quality` varchar(60) NOT NULL,
  `price` decimal(16,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `worker_providers`
--

CREATE TABLE `worker_providers` (
  `user_id` int(11) NOT NULL,
  `trade_license` varchar(80) DEFAULT NULL,
  `trade_certificate` varchar(80) DEFAULT NULL,
  `availability` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `worker_provider_profiles`
--

CREATE TABLE `worker_provider_profiles` (
  `provider_id` int(11) NOT NULL,
  `trade_certificate` varchar(120) NOT NULL,
  `availability` varchar(80) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `architects`
--
ALTER TABLE `architects`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `architect_expertise`
--
ALTER TABLE `architect_expertise`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_architect_user` (`architect_user_id`),
  ADD KEY `idx_expertise` (`expertise`);

--
-- Indexes for table `architect_profiles`
--
ALTER TABLE `architect_profiles`
  ADD PRIMARY KEY (`architect_id`),
  ADD UNIQUE KEY `certificate_no` (`certificate_number`);

--
-- Indexes for table `architect_projects`
--
ALTER TABLE `architect_projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `architect_id` (`architect_id`);

--
-- Indexes for table `architect_project_images`
--
ALTER TABLE `architect_project_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_project` (`project_id`);

--
-- Indexes for table `architect_requests`
--
ALTER TABLE `architect_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_architect` (`architect_user_id`),
  ADD KEY `idx_client` (`client_user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `uniq_request` (`property_id`,`client_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `developers`
--
ALTER TABLE `developers`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `developer_land_requests`
--
ALTER TABLE `developer_land_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `developer_id` (`developer_id`);

--
-- Indexes for table `developer_profiles`
--
ALTER TABLE `developer_profiles`
  ADD PRIMARY KEY (`developer_id`);

--
-- Indexes for table `developer_projects`
--
ALTER TABLE `developer_projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `idx_developer_id` (`developer_id`);

--
-- Indexes for table `expertise`
--
ALTER TABLE `expertise`
  ADD PRIMARY KEY (`expertise_id`),
  ADD UNIQUE KEY `uniq_expertise_type` (`type`);

--
-- Indexes for table `flat_search_requests`
--
ALTER TABLE `flat_search_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `interior_designers`
--
ALTER TABLE `interior_designers`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `interior_profiles`
--
ALTER TABLE `interior_profiles`
  ADD PRIMARY KEY (`interior_id`);

--
-- Indexes for table `interior_requests`
--
ALTER TABLE `interior_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `land_offers`
--
ALTER TABLE `land_offers`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `land_requests`
--
ALTER TABLE `land_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_land_requests_dev` (`developer_id`,`created_at`),
  ADD KEY `idx_land_requests_client` (`client_id`,`created_at`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `idx_locations_city` (`city`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`);

--
-- Indexes for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `material_vendors`
--
ALTER TABLE `material_vendors`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `material_vendor_profiles`
--
ALTER TABLE `material_vendor_profiles`
  ADD PRIMARY KEY (`vendor_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `idx_projects_developer` (`developer_id`);

--
-- Indexes for table `project_images`
--
ALTER TABLE `project_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_project_images_project` (`project_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`property_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `idx_properties_price` (`price`),
  ADD KEY `idx_properties_bedrooms` (`no_of_bedrooms`),
  ADD KEY `idx_properties_status` (`availability_status`),
  ADD KEY `properties_provider_user_fk` (`provider_id`);

--
-- Indexes for table `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`provider_id`),
  ADD KEY `based_in_location_id` (`based_in_location_id`);

--
-- Indexes for table `provider_expertise`
--
ALTER TABLE `provider_expertise`
  ADD PRIMARY KEY (`provider_id`,`expertise_id`),
  ADD KEY `expertise_id` (`expertise_id`);

--
-- Indexes for table `provider_materials`
--
ALTER TABLE `provider_materials`
  ADD PRIMARY KEY (`provider_id`,`material_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `uq_client_property_type` (`client_id`,`property_id`,`request_type`),
  ADD KEY `idx_requests_provider_id` (`provider_id`),
  ADD KEY `idx_requests_type_status` (`request_type`,`status`);

--
-- Indexes for table `request_assignments`
--
ALTER TABLE `request_assignments`
  ADD PRIMARY KEY (`request_id`,`provider_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `architect_id` (`architect_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `trade_requests`
--
ALTER TABLE `trade_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_expertise`
--
ALTER TABLE `user_expertise`
  ADD PRIMARY KEY (`user_id`,`expertise_id`),
  ADD KEY `fk_ue_exp` (`expertise_id`);

--
-- Indexes for table `vendor_materials`
--
ALTER TABLE `vendor_materials`
  ADD PRIMARY KEY (`vendor_user_id`,`material_id`,`quality`),
  ADD KEY `fk_vm_mat` (`material_id`);

--
-- Indexes for table `worker_providers`
--
ALTER TABLE `worker_providers`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `worker_provider_profiles`
--
ALTER TABLE `worker_provider_profiles`
  ADD PRIMARY KEY (`provider_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `architect_expertise`
--
ALTER TABLE `architect_expertise`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `architect_projects`
--
ALTER TABLE `architect_projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `architect_project_images`
--
ALTER TABLE `architect_project_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `architect_requests`
--
ALTER TABLE `architect_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `developer_land_requests`
--
ALTER TABLE `developer_land_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `developer_projects`
--
ALTER TABLE `developer_projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expertise`
--
ALTER TABLE `expertise`
  MODIFY `expertise_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `land_requests`
--
ALTER TABLE `land_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_images`
--
ALTER TABLE `project_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `architects`
--
ALTER TABLE `architects`
  ADD CONSTRAINT `fk_arch_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `architect_expertise`
--
ALTER TABLE `architect_expertise`
  ADD CONSTRAINT `fk_ae_user` FOREIGN KEY (`architect_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `architect_profiles`
--
ALTER TABLE `architect_profiles`
  ADD CONSTRAINT `fk_architect_profiles_user` FOREIGN KEY (`architect_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `architect_projects`
--
ALTER TABLE `architect_projects`
  ADD CONSTRAINT `fk_arch_projects_arch` FOREIGN KEY (`architect_id`) REFERENCES `architect_profiles` (`architect_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_architect_projects_architect` FOREIGN KEY (`architect_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `architect_project_images`
--
ALTER TABLE `architect_project_images`
  ADD CONSTRAINT `fk_api_project` FOREIGN KEY (`project_id`) REFERENCES `architect_projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `architect_requests`
--
ALTER TABLE `architect_requests`
  ADD CONSTRAINT `fk_ar_architect` FOREIGN KEY (`architect_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ar_client` FOREIGN KEY (`client_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `developers`
--
ALTER TABLE `developers`
  ADD CONSTRAINT `fk_dev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `developer_land_requests`
--
ALTER TABLE `developer_land_requests`
  ADD CONSTRAINT `fk_dlr_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `developer_profiles`
--
ALTER TABLE `developer_profiles`
  ADD CONSTRAINT `fk_developer_profiles_user` FOREIGN KEY (`developer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `developer_projects`
--
ALTER TABLE `developer_projects`
  ADD CONSTRAINT `fk_devproj_dev` FOREIGN KEY (`developer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `flat_search_requests`
--
ALTER TABLE `flat_search_requests`
  ADD CONSTRAINT `flat_search_requests_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `interior_designers`
--
ALTER TABLE `interior_designers`
  ADD CONSTRAINT `fk_int_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `interior_profiles`
--
ALTER TABLE `interior_profiles`
  ADD CONSTRAINT `fk_interior_profiles_user` FOREIGN KEY (`interior_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `interior_requests`
--
ALTER TABLE `interior_requests`
  ADD CONSTRAINT `interior_requests_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `land_offers`
--
ALTER TABLE `land_offers`
  ADD CONSTRAINT `land_offers_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `land_requests`
--
ALTER TABLE `land_requests`
  ADD CONSTRAINT `fk_land_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_land_developer` FOREIGN KEY (`developer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD CONSTRAINT `material_requests_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_requests_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`);

--
-- Constraints for table `material_vendors`
--
ALTER TABLE `material_vendors`
  ADD CONSTRAINT `fk_mv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `material_vendor_profiles`
--
ALTER TABLE `material_vendor_profiles`
  ADD CONSTRAINT `fk_material_vendor_profiles_user` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_developer` FOREIGN KEY (`developer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `project_images`
--
ALTER TABLE `project_images`
  ADD CONSTRAINT `fk_project_images_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`),
  ADD CONSTRAINT `properties_provider_user_fk` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `providers`
--
ALTER TABLE `providers`
  ADD CONSTRAINT `providers_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `providers_ibfk_2` FOREIGN KEY (`based_in_location_id`) REFERENCES `locations` (`location_id`) ON DELETE SET NULL;

--
-- Constraints for table `provider_expertise`
--
ALTER TABLE `provider_expertise`
  ADD CONSTRAINT `provider_expertise_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_expertise_ibfk_2` FOREIGN KEY (`expertise_id`) REFERENCES `expertise` (`expertise_id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_materials`
--
ALTER TABLE `provider_materials`
  ADD CONSTRAINT `provider_materials_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_materials_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `fk_requests_provider` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE;

--
-- Constraints for table `request_assignments`
--
ALTER TABLE `request_assignments`
  ADD CONSTRAINT `fk_ra_architect` FOREIGN KEY (`architect_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `request_assignments_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_assignments_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`provider_id`) ON DELETE CASCADE;

--
-- Constraints for table `trade_requests`
--
ALTER TABLE `trade_requests`
  ADD CONSTRAINT `trade_requests_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_expertise`
--
ALTER TABLE `user_expertise`
  ADD CONSTRAINT `fk_ue_exp` FOREIGN KEY (`expertise_id`) REFERENCES `expertise` (`expertise_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ue_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_materials`
--
ALTER TABLE `vendor_materials`
  ADD CONSTRAINT `fk_vm_mat` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vm_vendor` FOREIGN KEY (`vendor_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `worker_providers`
--
ALTER TABLE `worker_providers`
  ADD CONSTRAINT `fk_wp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `worker_provider_profiles`
--
ALTER TABLE `worker_provider_profiles`
  ADD CONSTRAINT `fk_worker_provider_profiles_user` FOREIGN KEY (`provider_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
