-- LocalLoop Database Schema & Seed Data
-- Hyperlocal Community Platform
CREATE DATABASE IF NOT EXISTS `localloop` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `localloop`;

-- 1. Areas (Localities)
CREATE TABLE IF NOT EXISTS `areas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `district` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) NOT NULL DEFAULT 'Kerala',
    `pincode` VARCHAR(20) NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `icon` VARCHAR(50) DEFAULT 'bi-tag',
    `color` VARCHAR(20) DEFAULT '#2563eb',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(20) DEFAULT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `area_id` INT DEFAULT NULL,
    `profile_photo` VARCHAR(255) DEFAULT 'default-avatar.png',
    `bio` TEXT DEFAULT NULL,
    `role` ENUM('user', 'business', 'admin') NOT NULL DEFAULT 'user',
    `status` ENUM('active', 'warned', 'suspended') NOT NULL DEFAULT 'active',
    `reputation_points` INT NOT NULL DEFAULT 50,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. User Badges System
CREATE TABLE IF NOT EXISTS `badges` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `icon` VARCHAR(50) NOT NULL,
    `points_required` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_badges` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `badge_id` INT NOT NULL,
    `awarded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `user_badge_unique` (`user_id`, `badge_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`badge_id`) REFERENCES `badges`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Posts (Community Feed)
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `area_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `location` VARCHAR(150) DEFAULT NULL,
    `post_type` ENUM('general', 'announcement', 'recommendation', 'alert') DEFAULT 'general',
    `status` ENUM('pending', 'approved', 'rejected', 'hidden') DEFAULT 'approved',
    `views` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Post Images
CREATE TABLE IF NOT EXISTS `post_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Comments
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `comment` TEXT NOT NULL,
    `status` ENUM('active', 'hidden') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Likes
CREATE TABLE IF NOT EXISTS `likes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `post_user_like` (`post_id`, `user_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Saved Posts
CREATE TABLE IF NOT EXISTS `saved_posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `post_user_saved` (`post_id`, `user_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Reports
CREATE TABLE IF NOT EXISTS `reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reporter_id` INT NOT NULL,
    `post_id` INT DEFAULT NULL,
    `comment_id` INT DEFAULT NULL,
    `reason` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `status` ENUM('pending', 'reviewed', 'dismissed', 'actioned') DEFAULT 'pending',
    `reviewed_by` INT DEFAULT NULL,
    `reviewed_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`comment_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `sender_id` INT DEFAULT NULL,
    `type` VARCHAR(50) NOT NULL,
    `message` VARCHAR(255) NOT NULL,
    `link` VARCHAR(255) DEFAULT '#',
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Events
CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `area_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `event_date` DATE NOT NULL,
    `event_time` TIME NOT NULL,
    `venue` VARCHAR(255) NOT NULL,
    `organizer` VARCHAR(150) NOT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('upcoming', 'completed', 'cancelled') DEFAULT 'upcoming',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Event Participants / RSVPs
CREATE TABLE IF NOT EXISTS `event_participants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `status` ENUM('interested', 'going') DEFAULT 'interested',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `event_user_unique` (`event_id`, `user_id`),
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Jobs
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `area_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `company` VARCHAR(150) NOT NULL,
    `job_type` ENUM('full-time', 'part-time', 'freelance', 'internship') DEFAULT 'full-time',
    `salary` VARCHAR(100) DEFAULT 'Negotiable',
    `location` VARCHAR(150) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `requirements` TEXT DEFAULT NULL,
    `contact_email` VARCHAR(150) NOT NULL,
    `contact_phone` VARCHAR(30) DEFAULT NULL,
    `deadline` DATE DEFAULT NULL,
    `status` ENUM('open', 'closed') DEFAULT 'open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. Job Applications
CREATE TABLE IF NOT EXISTS `job_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `resume_path` VARCHAR(255) DEFAULT NULL,
    `cover_note` TEXT DEFAULT NULL,
    `status` ENUM('submitted', 'reviewed', 'shortlisted', 'rejected') DEFAULT 'submitted',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. Marketplace (Buy & Sell)
CREATE TABLE IF NOT EXISTS `marketplace_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `area_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `item_condition` ENUM('New', 'Like New', 'Good', 'Fair') DEFAULT 'Good',
    `description` LONGTEXT NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `status` ENUM('available', 'reserved', 'sold') DEFAULT 'available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. Help Requests
CREATE TABLE IF NOT EXISTS `help_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `area_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `urgency` ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    `description` LONGTEXT NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `status` ENUM('open', 'in_progress', 'resolved') DEFAULT 'open',
    `resolved_by_user_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 18. Help Responses / Offers
CREATE TABLE IF NOT EXISTS `help_offers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `is_accepted` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `help_requests`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 19. Lost & Found
CREATE TABLE IF NOT EXISTS `lost_found` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `area_id` INT NOT NULL,
    `type` ENUM('lost', 'found') NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `item_date` DATE NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `contact_info` VARCHAR(150) NOT NULL,
    `status` ENUM('open', 'resolved') DEFAULT 'open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 20. Admin Audit Log
CREATE TABLE IF NOT EXISTS `admin_actions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `target_type` VARCHAR(50) NOT NULL,
    `target_id` INT NOT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 21. Messages (Chat)
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id` INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `attachment` VARCHAR(255) DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- SEED DATA (Realistic Demo Content)
-- ========================================================

-- Areas
INSERT INTO `areas` (`id`, `name`, `district`, `state`, `pincode`, `status`) VALUES
(1, 'Kodungallur Town', 'Thrissur', 'Kerala', '680664', 'active'),
(2, 'Irinjalakuda', 'Thrissur', 'Kerala', '680121', 'active'),
(3, 'Thrissur City (Round)', 'Thrissur', 'Kerala', '680001', 'active'),
(4, 'Kakkanad / Infopark', 'Ernakulam', 'Kerala', '682030', 'active'),
(5, 'Aluva Junction', 'Ernakulam', 'Kerala', '683101', 'active'),
(6, 'Chalakudy', 'Thrissur', 'Kerala', '680307', 'active'),
(7, 'Fort Kochi', 'Ernakulam', 'Kerala', '682001', 'active');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `color`, `status`) VALUES
(1, 'Announcements', 'announcements', 'Official locality news, civic updates & alerts', 'bi-megaphone-fill', '#0284c7', 'active'),
(2, 'Events', 'events', 'Cultural festivals, sports tournaments & community meetups', 'bi-calendar-event-fill', '#8b5cf6', 'active'),
(3, 'Lost & Found', 'lost-found', 'Report misplaced items or find what you lost nearby', 'bi-search-heart-fill', '#ea580c', 'active'),
(4, 'Jobs', 'jobs', 'Local employment, part-time jobs & internships', 'bi-briefcase-fill', '#059669', 'active'),
(5, 'Buy & Sell', 'buy-sell', 'Hyperlocal marketplace for electronics, bikes, books & more', 'bi-cart-fill', '#d97706', 'active'),
(6, 'Community Help', 'help', 'Emergency requests, volunteer assistance & neighborly care', 'bi-heart-pulse-fill', '#dc2626', 'active'),
(7, 'Recommendations', 'recommendations', 'Trusted local food spots, mechanics, electricians & clinics', 'bi-star-fill', '#ca8a04', 'active'),
(8, 'Property & Rent', 'property', 'Rooms, apartments, shops and land for lease or rent', 'bi-building-fill', '#0d9488', 'active'),
(9, 'Education & Tuition', 'education', 'Coaching classes, student study groups and workshops', 'bi-mortarboard-fill', '#6366f1', 'active'),
(10, 'Student Corner', 'students', 'College projects, textbooks exchange and peer clubs', 'bi-backpack-fill', '#ec4899', 'active');

-- Badges
INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `points_required`) VALUES
(1, 'Helpful Neighbour', 'Given to citizens who actively resolve community help requests', 'bi-shield-check', 100),
(2, 'Trusted Seller', 'Recognized for transparent and positive marketplace trades', 'bi-patch-check-fill', 80),
(3, 'Community Contributor', 'Actively shares verified civic announcements and events', 'bi-award-fill', 60),
(4, 'Community Helper', 'Always willing to step up in times of neighborhood need', 'bi-heart-fill', 120),
(5, 'Top Contributor', 'Elite member with over 200 reputation points', 'bi-trophy-fill', 200);

-- Default passwords: 'password123' hashed with BCRYPT ($2y$10$wO3oW1oKk/n88z4zX23MfevN7nIe9ZqjA4E7jUjE5BqfS9hQ8oJWW)
-- We will use password_hash('admin123', PASSWORD_BCRYPT) and password_hash('user123', PASSWORD_BCRYPT)
-- For guaranteed authentication in PHP, let's use standard hashes:
-- 'password123': $2y$10$QjY8M1UjL6pA8C1pE1f3XeN1eZ7qP2O6Z4qW8U7E5BqfS9hQ8oJWW (we can update on setup)
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password_hash`, `area_id`, `profile_photo`, `bio`, `role`, `status`, `reputation_points`) VALUES
(1, 'System Administrator', 'admin', 'admin@localloop.in', '9876543210', '$2y$10$w2G4860D4e2pU3bY3/5VzeW8yB4kZ6y8Qf7O.wIu88UoK5183/m3G', 1, 'admin-avatar.jpg', 'Chief Moderator and System Administrator for LocalLoop Kerala.', 'admin', 'active', 999),
(2, 'Rahul Krishnan', 'rahulk', 'rahul@example.com', '9847123456', '$2y$10$w2G4860D4e2pU3bY3/5VzeW8yB4kZ6y8Qf7O.wIu88UoK5183/m3G', 1, 'user1.jpg', 'Kodungallur resident. Tech enthusiast, volunteer & football lover.', 'user', 'active', 185),
(3, 'Dr. Ananya Nair', 'ananya_doc', 'ananya@example.com', '9447112233', '$2y$10$w2G4860D4e2pU3bY3/5VzeW8yB4kZ6y8Qf7O.wIu88UoK5183/m3G', 3, 'user2.jpg', 'Pediatrician at Thrissur. Passionate about community health and cleanliness.', 'user', 'active', 240),
(4, 'Malabar Bakes & Cafe', 'malabarbakes', 'business@malabarbakes.com', '9895001122', '$2y$10$w2G4860D4e2pU3bY3/5VzeW8yB4kZ6y8Qf7O.wIu88UoK5183/m3G', 1, 'biz1.jpg', 'Fresh artisanal pastries, snacks and traditional tea snacks in Kodungallur.', 'business', 'active', 140),
(5, 'Sreejith Menon', 'sreejith_m', 'sree@example.com', '9744556677', '$2y$10$w2G4860D4e2pU3bY3/5VzeW8yB4kZ6y8Qf7O.wIu88UoK5183/m3G', 4, 'user3.jpg', 'Software developer at Infopark Kakkanad. Love weekend bike rides.', 'user', 'active', 95);

-- Award Badges
INSERT INTO `user_badges` (`user_id`, `badge_id`) VALUES
(2, 1),
(2, 3),
(3, 1),
(3, 4),
(3, 5),
(4, 2);

-- Sample Community Posts
INSERT INTO `posts` (`id`, `user_id`, `category_id`, `area_id`, `title`, `description`, `location`, `post_type`, `status`, `views`) VALUES
(1, 2, 1, 1, 'Water Supply Maintenance on Bypass Road this Thursday', 'The Kerala Water Authority has announced scheduled maintenance pipeline repairs on Bypass Road from 9:00 AM to 4:00 PM on Thursday. Residents are requested to store adequate drinking water beforehand.', 'Bypass Road, Kodungallur', 'announcement', 'approved', 342),
(2, 4, 4, 1, 'Hiring: Experienced Barista & Front Desk Staff', 'Malabar Bakes & Cafe is expanding! Looking for an energetic barista with experience in espresso brewing and customer service. Competitive pay, flexible shifts and lunch provided. Please drop your CV or apply through LocalLoop.', 'Near North Temple Gate, Kodungallur', 'general', 'approved', 215),
(3, 3, 6, 3, 'Urgent: Blood Donor Needed (O+ve) at West Fort Hospital', 'Patient admitted for orthopedic surgery needs 2 units of O-Positive blood by tomorrow 11 AM. If eligible and willing to donate, please reach out or comment below. Sincere thanks for your community solidarity!', 'West Fort Hospital, Thrissur', 'alert', 'approved', 510),
(4, 5, 7, 4, 'Best Electrician & Inverter Repair in Kakkanad?', 'Can anyone recommend an honest, certified electrician for inverter battery wiring repair in Kakkanad near Rajagiri? Urgent since our backup went down yesterday.', 'Near Rajagiri Valley, Kakkanad', 'recommendation', 'approved', 143),
(5, 2, 2, 1, 'Kodungallur Monsoon Football League 2026 - Registrations Open!', 'The annual 7-a-side floodlight football championship kicks off next weekend at Municipal Stadium. 16 neighborhood teams competing for the Cup. Registered refereeing, live scoreboards, and trophies for winners!', 'Municipal Ground, Kodungallur', 'announcement', 'approved', 420);

-- Sample Comments
INSERT INTO `comments` (`id`, `post_id`, `user_id`, `comment`, `status`) VALUES
(1, 1, 3, 'Thank you for this crucial update Rahul! Notified our apartment residents group.', 'active'),
(2, 1, 4, 'Noted, we will store water for the cafe kitchen.', 'active'),
(3, 3, 2, 'I am O+ve and live 15 mins away. DMing my contact details right away!', 'active'),
(4, 3, 5, 'Shared with Thrissur Blood Donors WhatsApp group as well.', 'active'),
(5, 4, 3, 'Call Ramesh Electricals (9847223344) near Infopark signal. Very prompt and reasonable.', 'active'),
(6, 5, 5, 'Team Kakkanad Strikers is registering! Looking forward to the tournament.', 'active');

-- Sample Likes
INSERT INTO `likes` (`post_id`, `user_id`) VALUES
(1, 3), (1, 4), (1, 5),
(2, 2), (2, 5),
(3, 1), (3, 2), (3, 4), (3, 5),
(4, 2),
(5, 1), (5, 2), (5, 3), (5, 4), (5, 5);

-- Sample Events
INSERT INTO `events` (`id`, `user_id`, `area_id`, `title`, `description`, `event_date`, `event_time`, `venue`, `organizer`, `status`) VALUES
(1, 2, 1, 'Kodungallur Monsoon 7s Football Tournament', 'Spectacular floodlit football championship with 16 local teams. Trophies, cash prize and refreshing food stalls by local vendors. Come support your local stars!', '2026-10-05', '18:00:00', 'Municipal Stadium, Kodungallur', 'Kodungallur Sports Club', 'upcoming'),
(2, 3, 3, 'Thrissur Clean City & Green Plastic Drive', 'Join doctors, students, and citizens in clearing non-biodegradable waste around Swaraj Round and planting 200 indigenous saplings. Gloves and refreshments provided.', '2026-10-12', '07:30:00', 'Thekkinkadu Maidan, Thrissur', 'Thrissur Green Earth Foundation', 'upcoming'),
(3, 5, 4, 'Infopark Techies Meetup & Open Source Hack', 'An informal evening meetup discussing Web Development, AI tooling, and open-source contribution for local developers and college students.', '2026-10-18', '16:00:00', 'SmartCity Hub Hall B, Kakkanad', 'Kerala FOSS Community', 'upcoming');

-- Sample Event Participants
INSERT INTO `event_participants` (`event_id`, `user_id`, `status`) VALUES
(1, 2, 'going'),
(1, 4, 'interested'),
(1, 5, 'going'),
(2, 2, 'going'),
(2, 3, 'going'),
(3, 5, 'going');

-- Sample Jobs
INSERT INTO `jobs` (`id`, `user_id`, `area_id`, `title`, `company`, `job_type`, `salary`, `location`, `description`, `requirements`, `contact_email`, `contact_phone`, `deadline`, `status`) VALUES
(1, 4, 1, 'Barista & Bakery Assistant', 'Malabar Bakes & Cafe', 'full-time', '₹16,000 - ₹20,000 / month', 'Kodungallur North Gate', 'Prepare coffee beverages, manage bakery counter, maintain sanitation and handle POS billing during rush hours.', 'Good communication in Malayalam & English, prior cafe/kitchen experience preferred.', 'jobs@malabarbakes.com', '9895001122', '2026-10-15', 'open'),
(2, 5, 4, 'Junior PHP / Web Developer', 'Kakkanad Digital Labs', 'full-time', '₹25,000 - ₹35,000 / month', 'Infopark Phase 1, Kakkanad', 'Build dynamic web applications using PHP, MySQL, JavaScript and REST APIs. Great collaborative environment with mentoring.', 'Knowledge of PHP 8+, PDO, relational DBs, Git and responsive CSS.', 'careers@kakkanaddigital.in', '9744556677', '2026-10-25', 'open'),
(3, 2, 1, 'Part-Time Graphic Designer & Social Media', 'LocalLoop Media Partners', 'part-time', '₹10,000 / month', 'Remote / Kodungallur', 'Design event flyers, social media reels/banners, and promotional banners for local businesses.', 'Proficiency in Figma, Canva or Photoshop. Eye for modern typography.', 'design@localloop.in', '9847123456', '2026-10-20', 'open');

-- Sample Marketplace Items
INSERT INTO `marketplace_items` (`id`, `user_id`, `area_id`, `title`, `category`, `price`, `item_condition`, `description`, `location`, `phone`, `status`) VALUES
(1, 2, 1, 'Hercules Roadeo 21-Speed Mountain Bike', 'Bikes & Cycles', 6500.00, 'Good', 'Dual disc brakes, front shock absorbers, recently serviced with new tires. Great for fitness commutes and college travel.', 'South Nada, Kodungallur', '9847123456', 'available'),
(2, 5, 4, 'Dell 24-inch Full HD IPS Monitor (1080p 75Hz)', 'Electronics', 7200.00, 'Like New', 'Used for 6 months as dual display for work from home. Pristine condition with original box, HDMI cable and power adapter.', 'Near InfoPark Gate 2, Kakkanad', '9744556677', 'available'),
(3, 3, 3, 'Ergonomic Wooden Study Desk with Bookshelf', 'Furniture', 3800.00, 'Good', 'Solid teak finish engineered wood table with side drawers and upper bookshelf tier. Moving out sale.', 'Punkunnam, Thrissur', '9447112233', 'available');

-- Sample Help Requests
INSERT INTO `help_requests` (`id`, `user_id`, `area_id`, `title`, `urgency`, `description`, `location`, `status`, `resolved_by_user_id`) VALUES
(1, 2, 1, 'Need a wheelchair for 5 days for elderly grandparent recovering from knee surgery', 'High', 'Our 82-year-old grandfather was discharged today from hospital and needs a standard manual wheelchair for moving around house until Sunday. Will handle with utmost care and return promptly.', 'Near Kodungallur Bypass Junction', 'open', NULL),
(2, 3, 3, 'Emergency: Volunteer driver needed for medicine delivery to hospice care', 'Medium', 'Require a kind volunteer with two-wheeler or car to pick up specialized pediatric inhalers from Jubilee Mission Pharmacy and deliver to Snehabhavan Hospice.', 'Thrissur Town', 'resolved', 2);

-- Sample Help Offers
INSERT INTO `help_offers` (`request_id`, `user_id`, `message`, `phone`, `is_accepted`) VALUES
(1, 3, 'We have a spare fold-up wheelchair from our clinic that you can borrow free of charge! Please contact me.', '9447112233', 0),
(2, 2, 'Happy to help! Picking up the package right now.', '9847123456', 1);

-- Sample Lost & Found
INSERT INTO `lost_found` (`id`, `user_id`, `area_id`, `type`, `title`, `description`, `item_date`, `location`, `contact_info`, `status`) VALUES
(1, 2, 1, 'lost', 'Black Leather Wallet with Driving License & College ID', 'Misplaced black WildHorn wallet near Kodungallur Private Bus Stand near bakery shop. Contains Kerala DL in the name of Rahul K and college alumni card. Cash can be kept, please return the cards!', '2026-09-22', 'Private Bus Stand, Kodungallur', '9847123456', 'open'),
(2, 5, 4, 'found', 'Set of Bike Keys with Blue Helmets Keychain', 'Found near Infopark Phase 1 bus waiting bay on a bench yesterday evening. Honda ignition key with a blue helmet miniature keychain.', '2026-09-23', 'Infopark Phase 1 Bus Stop', 'Please verify bike model via message on 9744556677', 'open');

-- Sample Notifications
INSERT INTO `notifications` (`user_id`, `sender_id`, `type`, `message`, `link`, `is_read`) VALUES
(2, 3, 'like', 'Dr. Ananya Nair liked your post on Water Supply Maintenance.', 'post.php?id=1', 0),
(2, 4, 'comment', 'Malabar Bakes & Cafe commented on your announcement.', 'post.php?id=1', 0),
(4, 2, 'job_view', 'Rahul Krishnan viewed your Barista job listing.', 'jobs.php?id=1', 1),
(2, 3, 'help_offer', 'Dr. Ananya Nair offered assistance on your Wheelchair help request!', 'help.php?id=1', 0);
