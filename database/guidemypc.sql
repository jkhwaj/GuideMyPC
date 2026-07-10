-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2026 at 01:09 AM
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
-- Database: `guidemypc`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Windows', 'windows', 'Fix updates, drivers, blue screens, slow performance, and errors.', '🪟'),
(2, 'macOS', 'macos', 'Solve app issues, storage problems, updates, and performance bugs.', '🍎'),
(3, 'Linux', 'linux', 'Troubleshoot terminal issues, packages, permissions, and networking.', '🐧'),
(4, 'Android', 'android', 'Fix apps, storage, battery, updates, and connection problems.', '📱'),
(5, 'iPhone / iPad', 'iphone', 'Get help with iOS, backups, storage, apps, and Wi-Fi issues.', '📲'),
(6, 'Wi-Fi & Routers', 'wifi', 'Diagnose internet problems, weak signal, router setup, and disconnects.', '📶');

-- --------------------------------------------------------

--
-- Table structure for table `community_comments`
--

CREATE TABLE `community_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_likes`
--

CREATE TABLE `community_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_posts`
--

CREATE TABLE `community_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `community_posts`
--

INSERT INTO `community_posts` (`id`, `user_id`, `title`, `content`, `created_at`) VALUES
(2, 1, 'slow pc', 'thanks', '2026-07-07 18:42:24');

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `official_url` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `downloads`
--

INSERT INTO `downloads` (`id`, `name`, `description`, `official_url`, `category`, `created_at`) VALUES
(2, 'Google Chrome', 'Fast and secure web browser from Google.', 'https://www.google.com/chrome/', 'Browser', '2026-07-07 14:45:06');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `guide_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `guide_id`, `created_at`) VALUES
(1, 1, 1, '2026-07-07 17:43:50');

-- --------------------------------------------------------

--
-- Table structure for table `guides`
--

CREATE TABLE `guides` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `difficulty` varchar(50) DEFAULT NULL,
  `estimated_time` varchar(50) DEFAULT NULL,
  `risk_level` varchar(50) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guides`
--

INSERT INTO `guides` (`id`, `category_id`, `title`, `slug`, `description`, `difficulty`, `estimated_time`, `risk_level`, `content`, `created_at`, `views`) VALUES
(1, 1, 'Slow Computer', 'slow-computer', 'Speed up startup, remove junk files, and improve performance.', 'Beginner', '15-30 Minutes', 'Low', '1. Restart your computer.\r\n\r\n2. Open Task Manager and check CPU, Memory and Disk usage.\r\n\r\n3. Disable unnecessary startup programs.\r\n\r\n4. Run Windows Disk Cleanup.\r\n\r\n5. Remove unused software.\r\n\r\n6. Scan for malware using Windows Defender.\r\n\r\n7. Install Windows Updates.\r\n\r\n8. Restart your PC and test performance.', '2026-07-06 19:44:43', 0),
(2, 1, 'Blue Screen', 'blue-screen', 'Fix Blue Screen crashes and common Windows stop errors.', 'Intermediate', '20 Minutes', 'Medium', 'Step-by-step guide', '2026-07-06 19:44:43', 1),
(3, 1, 'Windows Update Problems', 'windows-update', 'Fix failed or stuck Windows updates.', 'Beginner', '15 Minutes', 'Low', 'Step-by-step guide', '2026-07-06 19:44:43', 1),
(4, 1, 'Driver Issues', 'driver-issues', 'Troubleshoot missing or outdated drivers.', 'Intermediate', '20 Minutes', 'Medium', 'Step-by-step guide', '2026-07-06 19:44:43', 1),
(5, 1, 'Virus Removal', 'virus-removal', 'Remove malware using trusted tools.', 'Beginner', '30 Minutes', 'Low', 'Step-by-step guide', '2026-07-06 19:44:43', 0);

-- --------------------------------------------------------

--
-- Table structure for table `guide_ratings`
--

CREATE TABLE `guide_ratings` (
  `id` int(11) NOT NULL,
  `guide_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guide_ratings`
--

INSERT INTO `guide_ratings` (`id`, `guide_id`, `user_id`, `rating`, `created_at`) VALUES
(1, 1, 1, 4, '2026-07-07 15:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `guide_steps`
--

CREATE TABLE `guide_steps` (
  `id` int(11) NOT NULL,
  `guide_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `step_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guide_steps`
--

INSERT INTO `guide_steps` (`id`, `guide_id`, `step_number`, `step_text`) VALUES
(25, 1, 1, 'Restart your computer and check if performance improves.'),
(26, 1, 2, 'Open Task Manager and check CPU, Memory, and Disk usage.'),
(27, 1, 3, 'Disable unnecessary startup programs.'),
(28, 1, 4, 'Run Windows Disk Cleanup.'),
(29, 1, 5, 'Remove unused software.'),
(30, 1, 6, 'Scan for malware using Windows Defender.'),
(31, 1, 7, 'Install Windows Updates.'),
(32, 1, 8, 'Restart your PC and test performance.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'jhon', 'test@gmail.com', '$2y$10$wpbBCjWFO9nbQtwtLNd5H.gE3/4J.Lr9WOWnUY.ojpqtrCoaruuw2', 'admin', '2026-07-07 11:09:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `guide_step_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 1,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `community_comments`
--
ALTER TABLE `community_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `community_likes`
--
ALTER TABLE `community_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`guide_id`),
  ADD KEY `guide_id` (`guide_id`);

--
-- Indexes for table `guides`
--
ALTER TABLE `guides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `guide_ratings`
--
ALTER TABLE `guide_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`guide_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `guide_steps`
--
ALTER TABLE `guide_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guide_id` (`guide_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_progress` (`user_id`,`guide_step_id`),
  ADD KEY `guide_step_id` (`guide_step_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `community_comments`
--
ALTER TABLE `community_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `community_likes`
--
ALTER TABLE `community_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `community_posts`
--
ALTER TABLE `community_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `guides`
--
ALTER TABLE `guides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `guide_ratings`
--
ALTER TABLE `guide_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `guide_steps`
--
ALTER TABLE `guide_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `community_comments`
--
ALTER TABLE `community_comments`
  ADD CONSTRAINT `community_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_likes`
--
ALTER TABLE `community_likes`
  ADD CONSTRAINT `community_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD CONSTRAINT `community_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`guide_id`) REFERENCES `guides` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guides`
--
ALTER TABLE `guides`
  ADD CONSTRAINT `guides_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `guide_ratings`
--
ALTER TABLE `guide_ratings`
  ADD CONSTRAINT `guide_ratings_ibfk_1` FOREIGN KEY (`guide_id`) REFERENCES `guides` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guide_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guide_steps`
--
ALTER TABLE `guide_steps`
  ADD CONSTRAINT `guide_steps_ibfk_1` FOREIGN KEY (`guide_id`) REFERENCES `guides` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`guide_step_id`) REFERENCES `guide_steps` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
