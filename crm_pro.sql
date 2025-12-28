-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2025 at 10:37 AM
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
-- Database: `crm_pro`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entity_type` enum('deal','customer','task') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `field_name`, `old_value`, `new_value`, `created_at`) VALUES
(1, 1, 'deal', 3, 'Value', '99999999.99', '99999991', '2025-12-27 09:58:43'),
(2, 1, 'deal', 5, 'Value', '12344.00', '1234412', '2025-12-27 10:09:55'),
(3, 1, 'deal', 5, 'Stage', 'Lead', 'Closed', '2025-12-27 10:10:26'),
(4, 1, 'deal', 5, 'Stage', 'Closed', 'Lead', '2025-12-27 10:24:55'),
(5, 1, 'deal', 5, 'Stage', 'Lead', 'Closed', '2025-12-27 10:25:13'),
(6, 2, 'deal', 5, 'Stage', 'Closed', 'Proposal', '2025-12-27 10:26:20'),
(7, 1, 'deal', 2, 'Stage', 'Negotiation', 'Closed', '2025-12-27 13:50:47'),
(8, 1, 'deal', 5, 'Stage', 'Proposal', 'Closed', '2025-12-27 14:12:44'),
(9, 1, 'deal', 1, 'Stage', 'Proposal', 'Closed', '2025-12-27 14:12:45'),
(10, 1, 'deal', 3, 'Stage', 'Negotiation', 'Closed', '2025-12-27 14:12:46'),
(11, 1, 'deal', 4, 'Stage', 'Lead', 'Negotiation', '2025-12-27 14:12:48'),
(12, 1, 'deal', 4, 'Stage', 'Negotiation', 'Closed', '2025-12-27 14:12:50'),
(13, 1, 'deal', 6, 'Stage', 'Lead', 'Closed', '2025-12-27 14:12:51'),
(14, 1, 'deal', 2, 'Stage', 'Closed', 'Negotiation', '2025-12-27 14:17:12'),
(15, 1, 'deal', 4, 'Stage', 'Closed', 'Proposal', '2025-12-27 14:17:12'),
(16, 1, 'deal', 5, 'Stage', 'Closed', 'Proposal', '2025-12-27 14:17:13'),
(17, 1, 'deal', 6, 'Stage', 'Closed', 'Negotiation', '2025-12-27 14:17:14'),
(18, 1, 'deal', 7, 'Stage', 'Lead', 'Closed', '2025-12-27 14:19:38'),
(19, 1, 'deal', 8, 'Stage', 'Lead', 'Proposal', '2025-12-27 15:05:01'),
(20, 1, 'deal', 5, 'Stage', 'Proposal', 'Lead', '2025-12-27 15:05:03'),
(21, 1, 'deal', 3, 'Value', '99999991.00', '1.00', '2025-12-27 15:05:18'),
(22, 1, 'deal', 5, 'Stage', 'Lead', 'Closed', '2025-12-28 04:42:55'),
(23, 1, 'deal', 2, 'Stage', 'Negotiation', 'Closed', '2025-12-28 04:42:56'),
(24, 1, 'deal', 8, 'Stage', 'Proposal', 'Closed', '2025-12-28 04:42:59'),
(25, 1, 'deal', 6, 'Stage', 'Negotiation', 'Closed', '2025-12-28 04:43:00'),
(26, 1, 'deal', 4, 'Stage', 'Proposal', 'Closed', '2025-12-28 04:43:01'),
(27, 1, 'deal', 1, 'Stage', 'Closed', 'Negotiation', '2025-12-28 04:43:10'),
(28, 1, 'deal', 3, 'Stage', 'Closed', 'Proposal', '2025-12-28 04:43:11'),
(29, 1, 'deal', 4, 'Stage', 'Closed', 'Lead', '2025-12-28 04:43:13'),
(30, 1, 'deal', 5, 'Stage', 'Closed', 'Negotiation', '2025-12-28 04:43:14'),
(31, 1, 'deal', 6, 'Stage', 'Closed', 'Proposal', '2025-12-28 04:43:15'),
(32, 1, 'deal', 7, 'Stage', 'Closed', 'Lead', '2025-12-28 04:43:18'),
(33, 1, 'deal', 8, 'Stage', 'Closed', 'Negotiation', '2025-12-28 04:43:27'),
(34, 1, 'deal', 2, 'Stage', 'Closed', 'Proposal', '2025-12-28 04:43:29'),
(35, 1, 'deal', 2, 'Stage', 'Proposal', 'Closed', '2025-12-28 04:43:34'),
(36, 1, 'deal', 9, 'Stage', 'Lead', 'Closed', '2025-12-28 05:20:37'),
(37, 1, 'deal', 8, 'Stage', 'Negotiation', 'Closed', '2025-12-28 08:20:42'),
(38, 1, 'deal', 8, 'Stage', 'Closed', 'Negotiation', '2025-12-28 08:20:43'),
(39, 1, 'deal', 8, 'Stage', 'Negotiation', 'Closed', '2025-12-28 08:20:45'),
(40, 1, 'deal', 9, 'Stage', 'Closed', 'Negotiation', '2025-12-28 08:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `status` enum('Active','Inactive','Lead') DEFAULT 'Lead',
  `source` varchar(50) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `score` int(11) DEFAULT 0,
  `potential_value` decimal(10,2) DEFAULT 0.00,
  `deleted_at` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `state`, `phone`, `company`, `job_title`, `status`, `source`, `assigned_to`, `created_at`, `score`, `potential_value`, `deleted_at`, `avatar`) VALUES
(1, 'nihar', 'borad', 'admin1@crm.com', NULL, NULL, 'qwerty', NULL, 'Active', 'web', 1, '2025-12-25 11:52:07', 50, 0.00, NULL, NULL),
(28, 'new ', 'Lead', 'lead@mail.com', NULL, NULL, 'frequent', NULL, 'Active', 'Website Landing Page', 1, '2025-12-26 17:20:43', 95, 47.00, NULL, NULL),
(29, 'new ', 'Lead', 'packdteam@gmail.com', 'NY', NULL, 'frequent', NULL, 'Active', 'Web Form', 2, '2025-12-27 15:09:34', 40, 1.00, NULL, 'uploads/cust_29_1766910841.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

CREATE TABLE `deals` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(10,2) DEFAULT 0.00,
  `stage` enum('Lead','Qualification','Proposal','Negotiation','Closed') DEFAULT 'Lead',
  `due_date` date DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deals`
--

INSERT INTO `deals` (`id`, `customer_id`, `title`, `value`, `cost`, `profit`, `stage`, `due_date`, `assigned_to`, `created_at`, `deleted_at`) VALUES
(1, 0, 'ssss', 1123.00, 0.00, 0.00, 'Negotiation', '2025-12-08', 1, '2025-12-26 15:42:12', NULL),
(2, 0, 'new deal', 123456.00, 0.00, 0.00, 'Closed', '2025-12-19', 2, '2025-12-26 15:45:39', NULL),
(3, 0, 'qwert', 1.00, 0.00, 1.00, 'Proposal', '2025-12-18', 2, '2025-12-26 15:53:24', NULL),
(4, 0, 'ssss', 123.00, 0.00, 0.00, 'Lead', '2025-12-31', 2, '2025-12-26 15:58:01', NULL),
(5, 28, 'qasxxxxxx', 1234412.00, 0.00, 0.00, 'Negotiation', '2026-01-02', 2, '2025-12-27 10:09:27', NULL),
(6, 1, 'ssss', 123.00, 0.00, 0.00, 'Proposal', '2025-12-17', 1, '2025-12-27 13:36:57', NULL),
(7, 28, 'sales - new Lead', 1000.00, 200.00, 800.00, 'Lead', '2025-12-24', 2, '2025-12-27 14:18:33', NULL),
(8, 28, 'sales - new Lead', 1000.00, 200.00, 800.00, 'Closed', '2025-12-24', 2, '2025-12-27 14:19:20', NULL),
(9, 29, 'sales - new Lead', 1000.00, 200.00, 800.00, 'Negotiation', '2025-12-25', 2, '2025-12-28 05:20:23', NULL),
(10, 1, 'sales - nihar borad', 1000.00, 200.00, 800.00, 'Lead', '2025-12-25', 1, '2025-12-28 07:09:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `name`, `subject`, `body`) VALUES
(1, 'Introductory Email', 'Partnership with {Company}', 'Hi {First_Name},\n\nI hope you are having a great week.\n\nI was researching {Company} and see great potential for collaboration.\n\nBest,\n{Owner_Name}'),
(2, 'Follow Up', 'Quick question regarding our chat', 'Hi {First_Name},\n\nJust wanted to circle back on our last conversation.\n\nAre you still interested in moving forward?\n\nThanks,\n{Owner_Name}'),
(3, 'Contract Sent', 'Contract for {Company}', 'Hello {First_Name},\n\nPlease find the attached contract.\n\nLet me know if you have questions.\n\nBest,\n{Owner_Name}');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `related_to` enum('customer','deal') NOT NULL,
  `related_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `related_to`, `related_id`, `filename`, `filepath`, `uploaded_by`, `created_at`) VALUES
(1, 'customer', 28, 'no in these chapters only dont create new chatper.pdf', 'uploads/1766849726_nointhesechaptersonlydontcreatenewchatper.pdf', 1, '2025-12-27 15:35:26'),
(2, 'customer', 29, 'DNA-Technology-and-Genetic-Engineering-in-Microbiology (1).pdf', 'uploads/1766897864_DNA-Technology-and-Genetic-Engineering-in-Microbiology1.pdf', 1, '2025-12-28 04:57:44');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `related_to` enum('customer','deal') NOT NULL,
  `related_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('Note','Email','Call','Meeting') DEFAULT 'Note'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `related_to`, `related_id`, `note`, `created_by`, `created_at`, `type`) VALUES
(1, 'customer', 28, 'bhai bhai', 1, '2025-12-26 17:38:34', 'Note'),
(2, 'customer', 28, '📧 <strong>Sent Email:</strong> Partnership with frequent<br><br><span class=\'text-muted small\'>Hi new ,<br />\r\n<br />\r\nI hope you are having a great week.<br />\r\n<br />\r\nI was researching frequent and see great potentia...</span>', 1, '2025-12-27 14:35:29', 'Email'),
(3, 'customer', 28, '📧 <strong>Sent Email:</strong> Partnership with frequent<br><br><span class=\'text-muted small\'>Hi new ,<br />\r\n<br />\r\nI hope you are having a great week.<br />\r\n<br />\r\nI was researching frequent and see great potentia...</span>', 1, '2025-12-27 15:35:54', 'Email'),
(4, 'customer', 29, '📧 <strong>Sent Email:</strong> Partnership with frequent<br><br><span class=\'text-muted small\'>Hi new ,<br />\r\n<br />\r\nI hope you are having a great week.<br />\r\n<br />\r\nI was researching frequent and see great potentia...</span>', 1, '2025-12-28 05:01:26', 'Email'),
(5, 'customer', 29, 'this is the best lead', 1, '2025-12-28 05:19:18', 'Note'),
(6, 'customer', 29, '📞 <strong>Call Logged: Voicemail</strong><br>ok ok', 1, '2025-12-28 05:23:25', 'Note'),
(7, 'customer', 29, 'wesd', 1, '2025-12-28 08:34:10', 'Note');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('Product','Service') NOT NULL DEFAULT 'Service',
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `type`, `price`, `cost`, `description`, `created_at`, `deleted_at`) VALUES
(1, 'sales', 'Product', 1000.00, 200.00, 'qwert', '2025-12-27 14:17:45', NULL),
(2, 'new service', 'Service', 2222.00, 1111.00, 'web', '2025-12-28 07:09:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `related_to` enum('customer','deal') DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `status`, `due_date`, `created_at`, `deleted_at`, `related_to`, `related_id`) VALUES
(1, 'Follow up: ssss', 'New deal assigned. Please contact the client.', 2, 'Completed', '2025-12-31', '2025-12-26 15:58:01', NULL, NULL, NULL),
(2, 'qqasxdc v', 'mnbvcx', 1, 'Pending', '2025-12-30', '2025-12-26 16:15:28', NULL, NULL, NULL),
(3, 'Close: qasxxxxxx', 'System Task', 1, 'Completed', '2026-01-02', '2025-12-27 10:09:27', NULL, 'deal', 5),
(4, 'Close: ssss', 'System Generated', 1, 'Completed', '2025-12-17', '2025-12-27 13:36:57', NULL, 'deal', 6),
(5, 'Close: sales - new Lead', 'System Generated', 2, 'Pending', '2025-12-24', '2025-12-27 14:18:33', NULL, 'deal', 7),
(6, 'Close: sales - new Lead', 'System Generated', 2, 'Completed', '2025-12-24', '2025-12-27 14:19:20', NULL, 'deal', 8),
(7, 'New Web Lead: new ', 'Please contact immediately.', 2, 'Pending', '2025-12-27', '2025-12-27 15:09:34', NULL, 'customer', 29),
(8, 'follow up', NULL, 1, 'Pending', '2025-12-26', '2025-12-27 15:28:23', NULL, NULL, NULL),
(9, 'Follow up', NULL, 1, 'Pending', '2025-12-30', '2025-12-27 15:31:28', NULL, NULL, NULL),
(10, 'Follow up', '', 1, 'Pending', '2025-12-30', '2025-12-27 15:35:14', NULL, 'customer', 28),
(11, 'Close: sales - new Lead', 'System Generated', 2, 'Pending', '2025-12-25', '2025-12-28 05:20:23', NULL, 'deal', 9),
(12, 'Close: sales - nihar borad', 'System Generated', 1, 'Pending', '2025-12-25', '2025-12-28 07:09:34', NULL, 'deal', 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'sales_rep',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `status`, `created_at`, `reset_token_hash`, `reset_token_expires_at`, `deleted_at`, `avatar`) VALUES
(1, 'Super Admin', 'admin@crm.com', '$2y$10$5F/W22kwrGGghojrl/G9d.ohCS0E1nm84s.jDgGx7HFIdujovDWlW', 'admin', 'active', '2025-12-25 11:41:15', NULL, NULL, NULL, 'uploads/avatar_1_1766905236.jpg'),
(2, 'sales', 'sales@crm.com', '$2y$10$s3rHcCR57041ybwwBifVUO1jO0U0mnFUG71vOpeFvPcejJMhVoCi2', 'sales_rep', 'active', '2025-12-26 14:23:26', NULL, NULL, NULL, NULL),
(3, 'manager', 'manager@crm.com', '$2y$10$16VC3on4RDQZe4CiK/BIm.LjKW5B7k3Lqs3Qy1m5HivHEirsrH7Um', 'manager', 'active', '2025-12-26 14:24:53', '5a405cae93ea4a746a9c76c2057a3ba94604b851055b80383b5fe68e4b071c67', '2025-12-26 22:18:48', NULL, NULL),
(4, 'abhi', 'abhi@crm.com', '$2y$10$1ys7kc9Hjq0iBlJp5Zt9FOazLQ1tEp4jge8i2I60vhWNdtY2YPoke', 'manager', 'active', '2025-12-28 05:32:40', NULL, NULL, NULL, NULL),
(5, 'tejas', 'tejas@crm.com', '$2y$10$OTt/C3C2KZ72q1nbqXA8YOQxU0Wxln0dgyq2jvqSMAmm67uv7drJO', 'admin', 'active', '2025-12-28 06:13:52', NULL, NULL, NULL, NULL),
(6, 'prince', 'prince@crm.com', '$2y$10$HTDiYFs2iuR2U3dIpTuLgOUP9ykpdVLeTL/7Nd/ICN0TXa0eK9Nea', 'admin', 'active', '2025-12-28 06:46:14', NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`);

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
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deals`
--
ALTER TABLE `deals`
  ADD CONSTRAINT `deals_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
