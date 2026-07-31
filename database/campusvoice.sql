-- ============================================================
-- CampusVoice — Campus Improvement & Student Voice Platform
-- Complete MySQL Database Schema + Seed Data
-- ============================================================
-- Run: mysql -u root -p < campusvoice.sql
-- Or import via phpMyAdmin on XAMPP/Laragon
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Drop database if exists and recreate
DROP DATABASE IF EXISTS `campusvoice`;
CREATE DATABASE `campusvoice` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `campusvoice`;

-- ============================================================
-- TABLE 1: students
-- ============================================================
CREATE TABLE `students` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_number` VARCHAR(50) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `must_change_password` TINYINT(1) NOT NULL DEFAULT 1,
    `contact_info` TEXT DEFAULT NULL,
    `department` VARCHAR(100) DEFAULT NULL,
    `year_level` VARCHAR(20) DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_student_number` (`student_number`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 2: administrators
-- ============================================================
CREATE TABLE `administrators` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_username` (`username`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 3: surveys
-- ============================================================
CREATE TABLE `surveys` (
    `survey_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT 'General',
    `open_date` DATETIME DEFAULT NULL,
    `close_date` DATETIME DEFAULT NULL,
    `status` ENUM('draft', 'active', 'closed', 'archived') NOT NULL DEFAULT 'draft',
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_dates` (`open_date`, `close_date`),
    CONSTRAINT `fk_surveys_admin` FOREIGN KEY (`created_by`) REFERENCES `administrators`(`admin_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 4: survey_questions
-- ============================================================
CREATE TABLE `survey_questions` (
    `question_id` INT AUTO_INCREMENT PRIMARY KEY,
    `survey_id` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_type` ENUM('multiple_choice', 'yes_no', 'rating', 'short_answer') NOT NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 1,
    `order_index` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_survey_id` (`survey_id`),
    INDEX `idx_order` (`survey_id`, `order_index`),
    CONSTRAINT `fk_questions_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`survey_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 5: survey_choices
-- ============================================================
CREATE TABLE `survey_choices` (
    `choice_id` INT AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT NOT NULL,
    `choice_text` VARCHAR(500) NOT NULL,
    `order_index` INT NOT NULL DEFAULT 0,
    INDEX `idx_question_id` (`question_id`),
    CONSTRAINT `fk_choices_question` FOREIGN KEY (`question_id`) REFERENCES `survey_questions`(`question_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 6: responses
-- Note: Responses are NOT cascaded on survey/question delete — preserved for historical data
-- ============================================================
CREATE TABLE `responses` (
    `response_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `survey_id` INT NOT NULL,
    `question_id` INT NOT NULL,
    `choice_id` INT DEFAULT NULL,
    `rating_value` INT DEFAULT NULL,
    `text_answer` TEXT DEFAULT NULL,
    `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_student_question` (`student_id`, `question_id`),
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_survey_id` (`survey_id`),
    INDEX `idx_question_id` (`question_id`),
    INDEX `idx_submitted_at` (`submitted_at`),
    CONSTRAINT `fk_responses_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_responses_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`survey_id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_responses_question` FOREIGN KEY (`question_id`) REFERENCES `survey_questions`(`question_id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_responses_choice` FOREIGN KEY (`choice_id`) REFERENCES `survey_choices`(`choice_id`) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `chk_rating_range` CHECK (`rating_value` IS NULL OR (`rating_value` >= 1 AND `rating_value` <= 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 7: survey_results (cached computed statistics)
-- ============================================================
CREATE TABLE `survey_results` (
    `result_id` INT AUTO_INCREMENT PRIMARY KEY,
    `survey_id` INT NOT NULL,
    `question_id` INT NOT NULL,
    `computed_metric` VARCHAR(50) NOT NULL COMMENT 'e.g. percentage, average, count, distribution',
    `computed_value` DECIMAL(10,2) DEFAULT NULL,
    `computed_details` TEXT DEFAULT NULL COMMENT 'JSON for complex results like distributions',
    `last_refreshed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_survey_id` (`survey_id`),
    INDEX `idx_question_id` (`question_id`),
    UNIQUE KEY `uk_survey_question_metric` (`survey_id`, `question_id`, `computed_metric`),
    CONSTRAINT `fk_results_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys`(`survey_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_results_question` FOREIGN KEY (`question_id`) REFERENCES `survey_questions`(`question_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 8: login_history (optional audit trail)
-- ============================================================
CREATE TABLE `login_history` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `role` ENUM('student', 'admin') NOT NULL,
    `action` VARCHAR(50) NOT NULL DEFAULT 'login',
    `login_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    INDEX `idx_user_role` (`user_id`, `role`),
    INDEX `idx_login_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 9: announcements (for dashboard notifications)
-- ============================================================
CREATE TABLE `announcements` (
    `announcement_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `target` ENUM('all', 'students', 'admins') NOT NULL DEFAULT 'all',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_target` (`target`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 10: activity_logs (admin activity tracking)
-- ============================================================
CREATE TABLE `activity_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `role` ENUM('student', 'admin') NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `target_type` VARCHAR(50) DEFAULT NULL COMMENT 'e.g. survey, student, question',
    `target_id` INT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`, `role`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA
-- ============================================================

-- --------------------------------------------------------
-- Administrators (passwords: admin123)
-- password_hash for 'admin123' using bcrypt
-- --------------------------------------------------------
INSERT INTO `administrators` (`username`, `full_name`, `email`, `password_hash`, `role`, `status`) VALUES
('admin', 'Dr. Maria Santos', 'admin@campusvoice.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('superadmin', 'Prof. Juan Dela Cruz', 'superadmin@campusvoice.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active');

-- --------------------------------------------------------
-- Students (default password = student number, must change on first login)
-- All passwords below hash to their respective student_number values
-- For demo, we use a common hash for 'password' - in production, each would match their student number
-- --------------------------------------------------------
INSERT INTO `students` (`student_number`, `full_name`, `email`, `password_hash`, `must_change_password`, `department`, `year_level`, `status`) VALUES
('STU-2024-001', 'Ana Marie Reyes', 'ana.reyes@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Computer Science', '3rd Year', 'active'),
('STU-2024-002', 'Carlos Miguel Torres', 'carlos.torres@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Information Technology', '2nd Year', 'active'),
('STU-2024-003', 'Maria Isabella Garcia', 'maria.garcia@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Business Administration', '4th Year', 'active'),
('STU-2024-004', 'James Patrick Cruz', 'james.cruz@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 'Engineering', '1st Year', 'active'),
('STU-2024-005', 'Sofia Angelica Santos', 'sofia.santos@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 'Education', '3rd Year', 'active');

-- --------------------------------------------------------
-- Surveys
-- --------------------------------------------------------
INSERT INTO `surveys` (`title`, `description`, `category`, `open_date`, `close_date`, `status`, `created_by`) VALUES
('Campus Wi-Fi Performance Survey', 'Help us improve internet connectivity across campus. Share your experience with Wi-Fi speed, reliability, and coverage in different buildings.', 'Wi-Fi', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active', 1),
('Cafeteria Quality & Service Feedback', 'Rate the quality of food, cleanliness, service speed, and menu variety in the campus cafeteria.', 'Cafeteria', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active', 1),
('Library Services Evaluation', 'Evaluate library resources, study spaces, operating hours, and digital access for academic needs.', 'Library', '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active', 1),
('Campus Safety & Security Assessment', 'Share your feedback on campus safety measures, lighting, security personnel, and emergency protocols.', 'Safety', '2026-06-01 00:00:00', '2026-08-31 23:59:59', 'active', 2),
('Student Wellness Program Survey', 'Help us understand your wellness needs — mental health support, health services, recreational facilities, and counseling availability.', 'Wellness', '2027-01-01 00:00:00', '2027-06-30 23:59:59', 'draft', 1);

-- --------------------------------------------------------
-- Survey Questions — Survey 1: Wi-Fi Performance
-- --------------------------------------------------------
INSERT INTO `survey_questions` (`survey_id`, `question_text`, `question_type`, `is_required`, `order_index`) VALUES
(1, 'How would you rate the overall Wi-Fi speed on campus?', 'rating', 1, 1),
(1, 'Which building do you most frequently use Wi-Fi in?', 'multiple_choice', 1, 2),
(1, 'Do you experience frequent Wi-Fi disconnections?', 'yes_no', 1, 3),
(1, 'What improvements would you suggest for campus Wi-Fi?', 'short_answer', 0, 4);

-- Survey 1 Choices — Q2: Building selection
INSERT INTO `survey_choices` (`question_id`, `choice_text`, `order_index`) VALUES
(2, 'Main Academic Building', 1),
(2, 'Science & Technology Building', 2),
(2, 'Library', 3),
(2, 'Student Center', 4),
(2, 'Engineering Building', 5);

-- Survey 1 Choices — Q3: Yes/No
INSERT INTO `survey_choices` (`question_id`, `choice_text`, `order_index`) VALUES
(3, 'Yes', 1),
(3, 'No', 2);

-- --------------------------------------------------------
-- Survey Questions — Survey 2: Cafeteria
-- --------------------------------------------------------
INSERT INTO `survey_questions` (`survey_id`, `question_text`, `question_type`, `is_required`, `order_index`) VALUES
(2, 'How would you rate the overall food quality in the cafeteria?', 'rating', 1, 1),
(2, 'How would you rate the cleanliness of the dining area?', 'rating', 1, 2),
(2, 'Which meal period do you usually visit the cafeteria?', 'multiple_choice', 1, 3),
(2, 'Are you satisfied with the menu variety?', 'yes_no', 1, 4),
(2, 'What food items or improvements would you like to see?', 'short_answer', 0, 5);

-- Survey 2 Choices — Q7: Meal period
INSERT INTO `survey_choices` (`question_id`, `choice_text`, `order_index`) VALUES
(7, 'Breakfast (7:00 - 9:00 AM)', 1),
(7, 'Lunch (11:00 AM - 1:00 PM)', 2),
(7, 'Afternoon Snack (3:00 - 5:00 PM)', 3),
(7, 'Dinner (5:00 - 7:00 PM)', 4);

-- Survey 2 Choices — Q8: Yes/No
INSERT INTO `survey_choices` (`question_id`, `choice_text`, `order_index`) VALUES
(8, 'Yes', 1),
(8, 'No', 2);

-- --------------------------------------------------------
-- Survey Questions — Survey 3: Library
-- --------------------------------------------------------
INSERT INTO `survey_questions` (`survey_id`, `question_text`, `question_type`, `is_required`, `order_index`) VALUES
(3, 'How would you rate the availability of study spaces in the library?', 'rating', 1, 1),
(3, 'How often do you use the library per week?', 'multiple_choice', 1, 2),
(3, 'Are the library operating hours sufficient for your needs?', 'yes_no', 1, 3),
(3, 'How would you rate the digital resources (e-books, online databases)?', 'rating', 1, 4),
(3, 'What additional resources or services would you like the library to offer?', 'short_answer', 0, 5);

-- Survey 3 Choices — Q11: Frequency
INSERT INTO `survey_choices` (`question_id`, `choice_text`, `order_index`) VALUES
(11, '1-2 times per week', 1),
(11, '3-4 times per week', 2),
(11, 'Daily', 3),
(11, 'Rarely / Never', 4);

-- Survey 3 Choices — Q12: Yes/No
INSERT INTO `survey_choices` (`question_id`, `choice_text`, `order_index`) VALUES
(12, 'Yes', 1),
(12, 'No', 2);

-- --------------------------------------------------------
-- Survey Questions — Survey 4: Safety
-- --------------------------------------------------------
INSERT INTO `survey_questions` (`survey_id`, `question_text`, `question_type`, `is_required`, `order_index`) VALUES
(4, 'How safe do you feel on campus during the day?', 'rating', 1, 1),
(4, 'How safe do you feel on campus at night?', 'rating', 1, 2),
(4, 'Are you aware of the campus emergency protocols?', 'yes_no', 1, 3),
(4, 'Which area of campus do you feel least safe in?', 'multiple_choice', 1, 4),
(4, 'What safety improvements would you recommend?', 'short_answer', 0, 5);

-- Survey 4 Choices — Q17: Yes/No
INSERT INTO `survey_choices` (`question_id`, `choice_text`, `order_index`) VALUES
(17, 'Yes', 1),
(17, 'No', 2);

-- Survey 4 Choices — Q18: Areas
INSERT INTO `survey_choices` (`question_id`, `choice_text`, `order_index`) VALUES
(18, 'Parking Lot', 1),
(18, 'Back Gate Area', 2),
(18, 'Sports Complex', 3),
(18, 'Building Corridors', 4),
(18, 'I feel safe everywhere', 5);

-- --------------------------------------------------------
-- Sample Responses — Student 4 (James) answered Survey 1 (Wi-Fi)
-- --------------------------------------------------------
INSERT INTO `responses` (`student_id`, `survey_id`, `question_id`, `choice_id`, `rating_value`, `text_answer`, `submitted_at`) VALUES
(4, 1, 1, NULL, 2, NULL, '2026-07-15 10:30:00'),
(4, 1, 2, 3, NULL, NULL, '2026-07-15 10:30:00'),
(4, 1, 3, 6, NULL, NULL, '2026-07-15 10:30:00'),
(4, 1, 4, NULL, NULL, 'Wi-Fi in the library is extremely slow during peak hours. Need more access points.', '2026-07-15 10:30:00');

-- Student 5 (Sofia) answered Survey 1 (Wi-Fi)
INSERT INTO `responses` (`student_id`, `survey_id`, `question_id`, `choice_id`, `rating_value`, `text_answer`, `submitted_at`) VALUES
(5, 1, 1, NULL, 3, NULL, '2026-07-16 14:20:00'),
(5, 1, 2, 1, NULL, NULL, '2026-07-16 14:20:00'),
(5, 1, 3, 6, NULL, NULL, '2026-07-16 14:20:00'),
(5, 1, 4, NULL, NULL, 'Better coverage in outdoor areas would be great.', '2026-07-16 14:20:00');

-- Student 4 (James) answered Survey 2 (Cafeteria)
INSERT INTO `responses` (`student_id`, `survey_id`, `question_id`, `choice_id`, `rating_value`, `text_answer`, `submitted_at`) VALUES
(4, 2, 5, NULL, 4, NULL, '2026-07-17 09:15:00'),
(4, 2, 6, NULL, 3, NULL, '2026-07-17 09:15:00'),
(4, 2, 7, 10, NULL, NULL, '2026-07-17 09:15:00'),
(4, 2, 8, 13, NULL, NULL, '2026-07-17 09:15:00'),
(4, 2, 9, NULL, NULL, 'More vegetarian and healthy options please!', '2026-07-17 09:15:00');

-- Student 5 (Sofia) answered Survey 2 (Cafeteria)
INSERT INTO `responses` (`student_id`, `survey_id`, `question_id`, `choice_id`, `rating_value`, `text_answer`, `submitted_at`) VALUES
(5, 2, 5, NULL, 3, NULL, '2026-07-18 11:45:00'),
(5, 2, 6, NULL, 4, NULL, '2026-07-18 11:45:00'),
(5, 2, 7, 10, NULL, NULL, '2026-07-18 11:45:00'),
(5, 2, 8, 14, NULL, NULL, '2026-07-18 11:45:00'),
(5, 2, 9, NULL, NULL, 'The food prices could be more student-friendly.', '2026-07-18 11:45:00');

-- --------------------------------------------------------
-- Sample Survey Results (pre-computed for Wi-Fi survey)
-- --------------------------------------------------------
INSERT INTO `survey_results` (`survey_id`, `question_id`, `computed_metric`, `computed_value`, `computed_details`) VALUES
(1, 1, 'average', 2.50, '{"distribution": {"1": 0, "2": 1, "3": 1, "4": 0, "5": 0}, "total_responses": 2}'),
(1, 2, 'percentage', NULL, '{"choices": {"Main Academic Building": 50, "Science & Technology Building": 0, "Library": 50, "Student Center": 0, "Engineering Building": 0}, "total_responses": 2}'),
(1, 3, 'percentage', NULL, '{"choices": {"Yes": 100, "No": 0}, "total_responses": 2}');

-- --------------------------------------------------------
-- Announcements
-- --------------------------------------------------------
INSERT INTO `announcements` (`title`, `content`, `target`, `is_active`, `created_by`) VALUES
('Welcome to CampusVoice!', 'We are excited to launch our new campus feedback platform. Your voice matters — please take a few minutes to complete any available surveys.', 'all', 1, 1),
('Wi-Fi Survey Extended', 'The Wi-Fi Performance Survey deadline has been extended. Please share your feedback to help us improve campus connectivity.', 'students', 1, 1),
('New Safety Survey Available', 'A new Campus Safety & Security Assessment is now available. Your responses will help us create a safer campus environment.', 'students', 1, 2);

-- --------------------------------------------------------
-- Sample Activity Logs
-- --------------------------------------------------------
INSERT INTO `activity_logs` (`user_id`, `role`, `action`, `description`, `target_type`, `target_id`) VALUES
(1, 'admin', 'create_survey', 'Created survey: Campus Wi-Fi Performance Survey', 'survey', 1),
(1, 'admin', 'create_survey', 'Created survey: Cafeteria Quality & Service Feedback', 'survey', 2),
(1, 'admin', 'create_survey', 'Created survey: Library Services Evaluation', 'survey', 3),
(2, 'admin', 'create_survey', 'Created survey: Campus Safety & Security Assessment', 'survey', 4),
(4, 'student', 'submit_survey', 'Submitted response for: Campus Wi-Fi Performance Survey', 'survey', 1),
(5, 'student', 'submit_survey', 'Submitted response for: Campus Wi-Fi Performance Survey', 'survey', 1),
(4, 'student', 'submit_survey', 'Submitted response for: Cafeteria Quality & Service Feedback', 'survey', 2),
(5, 'student', 'submit_survey', 'Submitted response for: Cafeteria Quality & Service Feedback', 'survey', 2);

COMMIT;
