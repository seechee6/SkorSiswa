-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2025 at 05:05 PM
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
-- Database: `skorsiswa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `advisors`
--

CREATE TABLE `advisors` (
  `id` int(11) NOT NULL,
  `advisor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `advisors`
--

INSERT INTO `advisors` (`id`, `advisor_id`, `student_id`) VALUES
(1, 7, 4),
(2, 7, 5),
(3, 7, 6),
(4, 7, 7),
(5, 7, 8),
(6, 7, 9),
(7, 7, 10),
(8, 7, 11),
(9, 7, 12),
(10, 7, 13);

-- --------------------------------------------------------

--
-- Table structure for table `advisor_notes`
--

CREATE TABLE `advisor_notes` (
  `id` int(11) NOT NULL,
  `advisor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `advisor_notes`
--

INSERT INTO `advisor_notes` (`id`, `advisor_id`, `student_id`, `course_id`, `note`, `created_at`) VALUES
(1, 7, 4, 1, 'Alice shows excellent academic performance. Discussed career paths in computer science.', '2025-06-19 21:32:23'),
(2, 7, 4, 1, 'Alice expressed interest in AI/ML. Recommended advanced courses for next semester.', '2025-06-19 21:32:23'),
(3, 7, 5, 1, 'Bob needs to improve time management. Suggested study techniques and resources.', '2025-06-19 21:32:23'),
(4, 7, 5, 1, 'Follow-up meeting with Bob - showing improvement in recent assignments.', '2025-06-19 21:32:23'),
(5, 7, 6, 1, 'Charlie is doing well overall. Discussed internship opportunities.', '2025-06-19 21:32:23'),
(6, 7, 6, 1, 'Charlie completed summer internship application. Provided recommendation letter.', '2025-06-19 21:32:23');

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `max_mark` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `course_id`, `name`, `weight`, `max_mark`) VALUES
(1, 1, 'Assignment 1', 15.00, 100.00),
(2, 1, 'Assignment 2', 15.00, 100.00),
(3, 1, 'Midterm Exam', 25.00, 100.00),
(4, 1, 'Project', 15.00, 100.00),
(5, 1, 'Final Exam', 30.00, 100.00),
(6, 2, 'Lab Assignment 1', 10.00, 100.00),
(7, 2, 'Lab Assignment 2', 10.00, 100.00),
(8, 2, 'Quiz 1', 15.00, 100.00),
(9, 2, 'Quiz 2', 15.00, 100.00),
(10, 2, 'Midterm Exam', 20.00, 100.00),
(11, 2, 'Final Exam', 30.00, 100.00),
(12, 3, 'Homework 1', 10.00, 100.00),
(13, 3, 'Homework 2', 10.00, 100.00),
(14, 3, 'Test 1', 25.00, 100.00),
(15, 3, 'Test 2', 25.00, 100.00),
(16, 3, 'Final Exam', 30.00, 100.00),
(17, 4, 'Assignment 1', 10.00, 100.00),
(18, 4, 'Assignment 2', 10.00, 100.00),
(19, 4, 'Database Project', 25.00, 100.00),
(20, 4, 'Midterm Exam', 25.00, 100.00),
(21, 4, 'Final Exam', 30.00, 100.00),
(22, 5, 'Team Project Phase 1', 15.00, 100.00),
(23, 5, 'Team Project Phase 2', 15.00, 100.00),
(24, 5, 'Team Project Final', 30.00, 100.00),
(25, 5, 'Midterm Exam', 20.00, 100.00),
(26, 5, 'Final Exam', 20.00, 100.00),
(27, 6, 'Problem Set 1', 10.00, 100.00),
(28, 6, 'Problem Set 2', 10.00, 100.00),
(29, 6, 'Problem Set 3', 10.00, 100.00),
(30, 6, 'Midterm Exam', 30.00, 100.00),
(31, 6, 'Final Exam', 40.00, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `assessment_marks`
--

CREATE TABLE `assessment_marks` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `mark` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_marks`
--

INSERT INTO `assessment_marks` (`id`, `enrollment_id`, `assessment_id`, `mark`) VALUES
(1, 1, 1, 90.00),
(2, 1, 2, 90.00),
(3, 1, 3, 78.00),
(4, 1, 4, 88.00),
(5, 1, 5, 91.00),
(6, 2, 1, 75.00),
(7, 2, 2, 80.00),
(8, 2, 3, 72.00),
(9, 2, 4, 82.00),
(10, 2, 5, 78.00),
(11, 3, 1, 92.00),
(12, 3, 2, 88.00),
(13, 3, 3, 85.00),
(14, 3, 4, 90.00),
(15, 3, 5, 88.00),
(16, 4, 6, 88.00),
(17, 4, 7, 85.00),
(18, 4, 8, 90.00),
(19, 4, 9, 87.00),
(20, 4, 10, 83.00),
(21, 4, 11, 85.00),
(22, 5, 6, 78.00),
(23, 5, 7, 75.00),
(24, 5, 8, 82.00),
(25, 5, 9, 80.00),
(26, 5, 10, 76.00),
(27, 5, 11, 78.00),
(28, 18, 6, 90.00),
(29, 18, 7, 88.00),
(30, 18, 8, 85.00),
(31, 18, 9, 92.00),
(32, 18, 10, 88.00),
(33, 18, 11, 90.00),
(34, 8, 1, 72.00),
(35, 8, 2, 78.00),
(36, 8, 3, 68.00),
(37, 8, 4, 75.00),
(38, 8, 5, 70.00),
(39, 9, 1, 95.00),
(40, 9, 2, 92.00),
(41, 9, 3, 90.00),
(42, 9, 4, 94.00),
(43, 9, 5, 92.00),
(44, 10, 1, 65.00),
(45, 10, 2, 70.00),
(46, 10, 3, 62.00),
(47, 10, 4, 68.00),
(48, 10, 5, 65.00),
(49, 11, 1, 88.00),
(50, 11, 2, 85.00),
(51, 11, 3, 80.00),
(52, 11, 4, 82.00),
(53, 11, 5, 80.00),
(54, 12, 1, 78.00),
(55, 12, 2, 75.00),
(56, 12, 3, 72.00),
(57, 12, 4, 70.00),
(58, 12, 5, 75.00),
(59, 13, 1, 55.00),
(60, 13, 2, 58.00),
(61, 13, 3, 52.00),
(62, 13, 4, 60.00),
(63, 13, 5, 55.00),
(64, 14, 1, 82.00),
(65, 14, 2, 85.00),
(66, 14, 3, 80.00),
(67, 14, 4, 78.00),
(68, 14, 5, 82.00),
(69, 6, 12, 75.00),
(70, 6, 13, 80.00),
(71, 6, 14, 72.00),
(72, 6, 15, 78.00),
(73, 6, 16, 75.00),
(74, 7, 12, 90.00),
(75, 7, 13, 88.00),
(76, 7, 14, 85.00),
(77, 7, 15, 90.00),
(78, 7, 16, 92.00),
(79, 22, 17, 85.00),
(80, 22, 18, 88.00),
(81, 22, 19, 90.00),
(82, 22, 20, 82.00),
(83, 22, 21, 85.00),
(84, 23, 17, 80.00),
(85, 23, 18, 78.00),
(86, 23, 19, 80.00),
(87, 23, 20, 75.00),
(88, 23, 21, 78.00),
(89, 24, 17, 90.00),
(90, 24, 18, 92.00),
(91, 24, 19, 95.00),
(92, 24, 20, 88.00),
(93, 24, 21, 90.00),
(94, 33, 27, 85.00),
(95, 33, 28, 82.00),
(96, 33, 29, 80.00),
(97, 33, 30, 78.00),
(98, 33, 31, 80.00),
(99, 34, 27, 92.00),
(100, 34, 28, 90.00),
(101, 34, 29, 88.00),
(102, 34, 30, 85.00),
(103, 34, 31, 88.00),
(104, 35, 27, 72.00),
(105, 35, 28, 70.00),
(106, 35, 29, 68.00),
(107, 35, 30, 65.00),
(108, 35, 31, 70.00),
(109, 23, 1, 80.00),
(110, 25, 19, 80.00),
(112, 25, 21, 80.00);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `semester` varchar(10) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `lecturer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `semester`, `year`, `lecturer_id`) VALUES
(1, 'CS101', 'Introduction to Computer Science', 'SEM 1', '2024/2025', 2),
(2, 'CS102', 'Data Structures and Algorithms', 'SEM 2', '2024/2025', 2),
(3, 'MATH201', 'Calculus II', 'SEM 2', '2024/2025', 3),
(4, 'CS203', 'Database Systems', 'SEM 1', '2024/2025', 2),
(5, 'CS301', 'Software Engineering', 'SEM 2', '2024/2025', 3),
(6, 'MATH101', 'Linear Algebra', 'SEM 1', '2024/2025', 3);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `created_at`) VALUES
(1, 4, 1, '2025-06-19 21:32:22'),
(2, 5, 1, '2025-06-19 21:32:22'),
(3, 6, 1, '2025-06-19 21:32:22'),
(4, 4, 2, '2025-06-19 21:32:22'),
(5, 5, 2, '2025-06-19 21:32:22'),
(6, 4, 3, '2025-06-19 21:32:22'),
(7, 6, 3, '2025-06-19 21:32:22'),
(8, 7, 1, '2025-06-19 21:32:22'),
(9, 8, 1, '2025-06-19 21:32:22'),
(10, 9, 1, '2025-06-19 21:32:22'),
(11, 10, 1, '2025-06-19 21:32:22'),
(12, 11, 1, '2025-06-19 21:32:22'),
(13, 12, 1, '2025-06-19 21:32:22'),
(14, 13, 1, '2025-06-19 21:32:22'),
(15, 7, 2, '2025-06-19 21:32:22'),
(16, 8, 2, '2025-06-19 21:32:22'),
(17, 9, 2, '2025-06-19 21:32:22'),
(18, 6, 2, '2025-06-19 21:32:22'),
(19, 10, 3, '2025-06-19 21:32:22'),
(20, 11, 3, '2025-06-19 21:32:22'),
(21, 12, 3, '2025-06-19 21:32:22'),
(22, 13, 3, '2025-06-19 21:32:22'),
(23, 4, 4, '2025-06-19 21:32:22'),
(24, 5, 4, '2025-06-19 21:32:22'),
(25, 6, 4, '2025-06-19 21:32:22'),
(26, 7, 4, '2025-06-19 21:32:22'),
(27, 8, 4, '2025-06-19 21:32:22'),
(28, 9, 4, '2025-06-19 21:32:22'),
(29, 10, 5, '2025-06-19 21:32:22'),
(30, 11, 5, '2025-06-19 21:32:22'),
(31, 12, 5, '2025-06-19 21:32:22'),
(32, 13, 5, '2025-06-19 21:32:22'),
(33, 7, 6, '2025-06-19 21:32:22'),
(34, 8, 6, '2025-06-19 21:32:22'),
(35, 9, 6, '2025-06-19 21:32:22');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_remarks`
--

CREATE TABLE `feedback_remarks` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `remark` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback_remarks`
--

INSERT INTO `feedback_remarks` (`id`, `enrollment_id`, `remark`, `created_at`) VALUES
(1, 1, 'Excellent work on assignments. Keep up the good performance!', '2025-06-19 21:32:23'),
(2, 2, 'Good effort, but please focus more on exam preparation.', '2025-06-19 21:32:23'),
(3, 3, 'Outstanding student with consistent high performance.', '2025-06-19 21:32:23'),
(4, 4, 'Your lab work is excellent, continue to apply these skills to real-world problems.', '2025-06-19 21:32:23'),
(5, 5, 'Needs to improve understanding of core data structure concepts.', '2025-06-19 21:32:23'),
(6, 6, 'Your math skills are improving, keep practicing!', '2025-06-19 21:32:23'),
(7, 7, 'Excellent grasp of mathematical concepts.', '2025-06-19 21:32:23'),
(8, 8, 'You need to attend more office hours for extra help.', '2025-06-19 21:32:23'),
(9, 9, 'Exceptional work throughout the semester.', '2025-06-19 21:32:23'),
(10, 10, 'Please seek tutoring support to improve your understanding.', '2025-06-19 21:32:23'),
(11, 11, 'Good consistent performance throughout the semester.', '2025-06-19 21:32:23'),
(12, 18, 'Excellent progress in programming concepts!', '2025-06-19 21:32:23'),
(13, 22, 'Your database design skills are excellent.', '2025-06-19 21:32:23'),
(14, 24, 'Outstanding database project. Consider taking advanced DB courses.', '2025-06-19 21:32:23'),
(15, 23, 'STUPID', '2025-06-20 10:50:20');

-- --------------------------------------------------------

--
-- Table structure for table `final_exam_marks`
--

CREATE TABLE `final_exam_marks` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `mark` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `final_exam_marks`
--

INSERT INTO `final_exam_marks` (`id`, `enrollment_id`, `mark`) VALUES
(1, 1, 82.00),
(2, 2, 78.00),
(3, 3, 88.00),
(4, 4, 85.00),
(5, 5, 78.00),
(6, 18, 90.00),
(7, 6, 75.00),
(8, 7, 92.00),
(9, 8, 70.00),
(10, 9, 92.00),
(11, 10, 65.00),
(12, 11, 80.00),
(13, 12, 75.00),
(14, 13, 55.00),
(15, 14, 82.00),
(16, 22, 85.00),
(17, 23, 82.00),
(18, 24, 90.00),
(19, 33, 80.00),
(20, 34, 88.00),
(21, 35, 70.00);

-- --------------------------------------------------------

--
-- Table structure for table `mark_update_notifications`
--

CREATE TABLE `mark_update_notifications` (
  `id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `assessment_id` int(11) DEFAULT NULL,
  `is_final_exam` tinyint(1) DEFAULT 0,
  `old_mark` decimal(5,2) DEFAULT NULL,
  `new_mark` decimal(5,2) NOT NULL,
  `change_reason` text DEFAULT NULL,
  `acknowledged` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mark_update_notifications`
--

INSERT INTO `mark_update_notifications` (`id`, `lecturer_id`, `student_id`, `enrollment_id`, `assessment_id`, `is_final_exam`, `old_mark`, `new_mark`, `change_reason`, `acknowledged`, `created_at`) VALUES
(1, 2, 4, 23, NULL, 1, 78.00, 82.00, 'Remark request approved - Request ID: 5', 1, '2025-06-21 16:42:01'),
(2, 2, 4, 23, 17, 0, 75.00, 80.00, NULL, 0, '2025-06-21 21:24:41'),
(3, 2, 4, 1, 1, 0, 85.00, 90.00, NULL, 0, '2025-06-21 21:46:00'),
(4, 2, 6, 25, 21, 0, NULL, 80.00, NULL, 0, '2025-06-21 21:47:17');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 4, 'Your CS101 Assignment 1 mark has been uploaded.', 1, '2025-06-19 21:32:23'),
(2, 4, 'New feedback available for CS101.', 1, '2025-06-19 21:32:23'),
(3, 4, 'Your CS102 Final Exam result is now available.', 1, '2025-06-19 21:32:23'),
(4, 4, 'Reminder: CS203 Database Project due next week.', 1, '2025-06-19 21:32:23'),
(5, 5, 'Your CS101 midterm result is now available.', 0, '2025-06-19 21:32:23'),
(6, 5, 'New feedback available for CS102.', 0, '2025-06-19 21:32:23'),
(7, 5, 'CS101 final grades have been released.', 0, '2025-06-19 21:32:23'),
(8, 6, 'Welcome to the SkorSiswa system!', 1, '2025-06-19 21:32:23'),
(9, 6, 'Your MATH201 Test 2 grade has been updated.', 1, '2025-06-19 21:32:23'),
(10, 6, 'Congratulations on your excellent performance in CS101.', 1, '2025-06-19 21:32:23'),
(11, 7, 'Your CS101 marks have been uploaded.', 0, '2025-06-19 21:32:23'),
(12, 7, 'Please submit your missing assignment for CS102.', 0, '2025-06-19 21:32:23'),
(13, 8, 'Excellent work on your CS101 project!', 0, '2025-06-19 21:32:23'),
(14, 9, 'Your MATH101 Problem Set 3 requires revision.', 0, '2025-06-19 21:32:23'),
(15, 10, 'Your CS101 final grade has been posted.', 0, '2025-06-19 21:32:23'),
(16, 11, 'New feedback available from your advisor.', 0, '2025-06-19 21:32:23'),
(17, 12, 'CS301 Team Project Phase 2 grade now available.', 0, '2025-06-19 21:32:23'),
(18, 13, 'You have been enrolled in a new course: CS301.', 0, '2025-06-19 21:32:23'),
(19, 4, 'New feedback has been added to your course', 1, '2025-06-20 10:50:20'),
(20, 4, 'Your remark request has been approved. Your mark has been updated to 82.', 1, '2025-06-21 16:42:01'),
(21, 4, 'Your mark for Assignment 1 in CS203 - Database Systems has been updated.', 1, '2025-06-21 21:24:41'),
(22, 4, 'Your mark for Assignment 1 in CS101 - Introduction to Computer Science has been updated.', 1, '2025-06-21 21:46:00'),
(23, 6, 'Your mark for Final Exam in CS203 - Database Systems has been posted.', 1, '2025-06-21 21:47:17'),
(24, 2, 'New remark request from Charlie Davis for Final Exam in CS101', 0, '2025-06-21 22:09:57'),
(25, 6, 'Your remark request for Final Exam has been submitted and is under review', 1, '2025-06-21 22:09:57'),
(26, 6, 'Your remark request has been rejected. Please check the lecturer\'s response for details.', 1, '2025-06-21 22:10:40'),
(27, 2, 'New remark request from Alice Brown for Final Exam in CS101', 0, '2025-06-21 22:17:41'),
(28, 4, 'Your remark request for Final Exam has been submitted and is under review', 1, '2025-06-21 22:17:41'),
(29, 4, 'Your remark request for Final Exam in CS101 has been approved. Your mark has been updated to 91.00.', 1, '2025-06-21 22:28:52'),
(30, 2, 'New remark request from Alice Brown for Final Exam in CS102', 0, '2025-06-21 22:56:29'),
(31, 4, 'Your remark request for Final Exam has been submitted and is under review', 1, '2025-06-21 22:56:29'),
(32, 4, 'Your remark request has been rejected. Please check the lecturer\'s response for details.', 1, '2025-06-21 22:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `remark_requests`
--

CREATE TABLE `remark_requests` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `current_mark` decimal(5,2) NOT NULL,
  `requested_mark` decimal(5,2) NOT NULL,
  `justification` text NOT NULL,
  `status` enum('pending','approved','rejected','under_review') DEFAULT 'pending',
  `lecturer_response` text DEFAULT NULL,
  `lecturer_id` int(11) NOT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remark_requests`
--

INSERT INTO `remark_requests` (`id`, `student_id`, `enrollment_id`, `assessment_id`, `current_mark`, `requested_mark`, `justification`, `status`, `lecturer_response`, `lecturer_id`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 6, 3, 5, 88.00, 100.00, 'haha', 'rejected', 'No', 2, '2025-06-21 22:10:40', '2025-06-21 22:09:57', '2025-06-21 22:10:40'),
(2, 4, 1, 5, 82.00, 91.00, 'Hello', 'approved', 'OK', 2, '2025-06-21 22:28:52', '2025-06-21 22:17:41', '2025-06-21 22:28:52'),
(3, 4, 4, 11, 85.00, 90.00, 'I think my code is runable too with the correct output', 'rejected', 'Follow what you have learn from the course', 2, '2025-06-21 22:57:37', '2025-06-21 22:56:29', '2025-06-21 22:57:37');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'admin'),
(4, 'advisor'),
(2, 'lecturer'),
(3, 'student');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `action`, `created_at`) VALUES
(1, 1, 'Admin logged into the system', '2025-06-19 21:32:23'),
(2, 2, 'Lecturer logged in', '2025-06-19 21:32:23'),
(3, 4, 'Student logged in', '2025-06-19 21:32:23'),
(4, 7, 'Advisor logged in', '2025-06-19 21:32:23'),
(5, 6, 'User logged in', '2025-06-19 21:40:12'),
(6, 4, 'User logged in', '2025-06-19 22:01:39'),
(7, 4, 'User logged in', '2025-06-19 22:31:24'),
(8, 4, 'User logged in', '2025-06-19 22:37:56'),
(9, 4, 'User logged in', '2025-06-19 22:41:58'),
(10, 4, 'User logged in', '2025-06-19 22:57:47'),
(11, 4, 'User logged in', '2025-06-19 23:21:30'),
(12, 4, 'User logged in', '2025-06-20 00:07:19'),
(13, 4, 'User logged in', '2025-06-20 10:12:28'),
(14, 4, 'User logged in', '2025-06-20 10:28:40'),
(15, 4, 'User logged in', '2025-06-20 10:47:50'),
(16, 2, 'User logged in', '2025-06-20 10:49:17'),
(17, 4, 'User logged in', '2025-06-20 10:51:01'),
(18, 4, 'User logged in', '2025-06-20 11:01:56'),
(19, 2, 'Responded to remark request #5 with status: Approved', '2025-06-21 16:42:01'),
(20, 4, 'User logged in', '2025-06-21 17:17:05'),
(21, 2, 'User logged in', '2025-06-21 17:18:40'),
(22, 4, 'User logged in', '2025-06-21 17:31:23'),
(23, 4, 'User logged in', '2025-06-21 17:34:46'),
(24, 4, 'User logged in', '2025-06-21 17:39:32'),
(25, 5, 'User logged in', '2025-06-21 17:40:05'),
(26, 5, 'User logged in', '2025-06-21 17:40:46'),
(27, 4, 'User logged in', '2025-06-21 18:08:57'),
(28, 4, 'User logged in', '2025-06-21 18:09:15'),
(29, 4, 'User logged in', '2025-06-21 18:25:17'),
(30, 6, 'User logged in', '2025-06-21 18:40:50'),
(31, 4, 'User logged in', '2025-06-21 20:54:00'),
(32, 4, 'User logged in', '2025-06-21 21:02:36'),
(33, 4, 'User logged in', '2025-06-21 21:10:12'),
(34, 2, 'User logged in', '2025-06-21 21:24:10'),
(35, 4, 'User logged in', '2025-06-21 21:25:28'),
(36, 4, 'User logged in', '2025-06-21 21:44:56'),
(37, 2, 'User logged in', '2025-06-21 21:45:41'),
(38, 4, 'User logged in', '2025-06-21 21:46:14'),
(39, 6, 'User logged in', '2025-06-21 21:47:29'),
(40, 2, 'User logged in', '2025-06-21 22:10:13'),
(41, 4, 'User logged in', '2025-06-21 22:11:25'),
(42, 6, 'User logged in', '2025-06-21 22:11:45'),
(43, 4, 'User logged in', '2025-06-21 22:17:01'),
(44, 4, 'User logged in', '2025-06-21 22:22:11'),
(45, 2, 'User logged in', '2025-06-21 22:28:27'),
(46, 4, 'User logged in', '2025-06-21 22:29:06'),
(47, 2, 'User logged in', '2025-06-21 22:57:01'),
(48, 4, 'User logged in', '2025-06-21 22:58:13'),
(49, 6, 'User logged in', '2025-06-21 23:05:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `matric_no` varchar(20) DEFAULT NULL,
  `staff_id` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `matric_no`, `staff_id`, `email`, `password_hash`, `role_id`, `created_at`) VALUES
(1, 'Admin User', NULL, 'ADMIN001', 'admin@university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 1, '2025-06-19 13:32:22'),
(2, 'Dr. John Smith', NULL, 'LEC001', 'john.smith@university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 2, '2025-06-19 13:32:22'),
(3, 'Dr. Sarah Johnson', NULL, 'LEC002', 'sarah.johnson@university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 2, '2025-06-19 13:32:22'),
(4, 'Alice Brown', 'STU001', NULL, 'alice.brown@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(5, 'Bob Wilson', 'STU002', NULL, 'bob.wilson@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(6, 'Charlie Davis', 'STU003', NULL, 'charlie.davis@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(7, 'Diana Evans', 'STU004', NULL, 'diana.evans@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(8, 'Edward Foster', 'STU005', NULL, 'edward.foster@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(9, 'Fiona Grant', 'STU006', NULL, 'fiona.grant@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(10, 'George Harris', 'STU007', NULL, 'george.harris@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(11, 'Hannah Irving', 'STU008', NULL, 'hannah.irving@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(12, 'Ian Jackson', 'STU009', NULL, 'ian.jackson@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(13, 'Julia King', 'STU010', NULL, 'julia.king@student.university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 3, '2025-06-19 13:32:22'),
(14, 'Prof. Mary Taylor', NULL, 'ADV001', 'mary.taylor@university.edu', '$2y$10$D5EwxbvZ9JtNeQ3w3N5or.gTzhumoDFoXGfiBuU63JHTg4PdTKhC.', 4, '2025-06-19 13:32:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advisors`
--
ALTER TABLE `advisors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `advisor_id` (`advisor_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `advisor_notes`
--
ALTER TABLE `advisor_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advisor_id` (`advisor_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `assessment_marks`
--
ALTER TABLE `assessment_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollment_id` (`enrollment_id`,`assessment_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `feedback_remarks`
--
ALTER TABLE `feedback_remarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enrollment_id` (`enrollment_id`);

--
-- Indexes for table `final_exam_marks`
--
ALTER TABLE `final_exam_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollment_id` (`enrollment_id`);

--
-- Indexes for table `mark_update_notifications`
--
ALTER TABLE `mark_update_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `remark_requests`
--
ALTER TABLE `remark_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matric_no` (`matric_no`),
  ADD UNIQUE KEY `staff_id` (`staff_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advisors`
--
ALTER TABLE `advisors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `advisor_notes`
--
ALTER TABLE `advisor_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `assessment_marks`
--
ALTER TABLE `assessment_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `feedback_remarks`
--
ALTER TABLE `feedback_remarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `final_exam_marks`
--
ALTER TABLE `final_exam_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `mark_update_notifications`
--
ALTER TABLE `mark_update_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `remark_requests`
--
ALTER TABLE `remark_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advisors`
--
ALTER TABLE `advisors`
  ADD CONSTRAINT `advisors_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `advisors_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `advisor_notes`
--
ALTER TABLE `advisor_notes`
  ADD CONSTRAINT `advisor_notes_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `advisor_notes_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `assessment_marks`
--
ALTER TABLE `assessment_marks`
  ADD CONSTRAINT `assessment_marks_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`),
  ADD CONSTRAINT `assessment_marks_ibfk_2` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`);

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `feedback_remarks`
--
ALTER TABLE `feedback_remarks`
  ADD CONSTRAINT `feedback_remarks_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`);

--
-- Constraints for table `final_exam_marks`
--
ALTER TABLE `final_exam_marks`
  ADD CONSTRAINT `final_exam_marks_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`);

--
-- Constraints for table `mark_update_notifications`
--
ALTER TABLE `mark_update_notifications`
  ADD CONSTRAINT `mark_update_notifications_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `mark_update_notifications_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `mark_update_notifications_ibfk_3` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`),
  ADD CONSTRAINT `mark_update_notifications_ibfk_4` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `remark_requests`
--
ALTER TABLE `remark_requests`
  ADD CONSTRAINT `remark_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `remark_requests_ibfk_2` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`),
  ADD CONSTRAINT `remark_requests_ibfk_3` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`),
  ADD CONSTRAINT `remark_requests_ibfk_4` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
