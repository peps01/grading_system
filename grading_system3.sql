-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2025 at 02:57 AM
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
-- Database: `grading_system3`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `activity_name` varchar(100) NOT NULL,
  `total_score` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `subject_id`, `activity_name`, `total_score`) VALUES
(6, 15, 'Output', 50),
(7, 16, 'Midterm Activity', 100),
(8, 16, 'Output', 100),
(9, 18, 'Midterm Activity', 100),
(10, 18, 'Crud System', 100),
(11, 19, 'Midterm Activity', 100);

-- --------------------------------------------------------

--
-- Table structure for table `activity_scores`
--

CREATE TABLE `activity_scores` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `score` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_scores`
--

INSERT INTO `activity_scores` (`id`, `activity_id`, `student_id`, `score`) VALUES
(8, 6, 14, 40),
(9, 6, 16, 50),
(10, 10, 14, 100),
(11, 10, 16, 90),
(12, 10, 19, 50),
(13, 9, 14, 90),
(14, 9, 16, 88),
(15, 11, 14, 99),
(16, 11, 16, 88),
(17, 11, 19, 75),
(18, 6, 29, 50),
(19, 7, 29, 50),
(20, 8, 29, 50),
(21, 9, 29, 100);

-- --------------------------------------------------------

--
-- Table structure for table `assigned_subjects`
--

CREATE TABLE `assigned_subjects` (
  `id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assigned_subjects`
--

INSERT INTO `assigned_subjects` (`id`, `instructor_id`, `subject_id`) VALUES
(18, 2, 19),
(26, 1, 18),
(30, 1, 15),
(32, 1, 16),
(36, 2, 20);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `status` enum('Present','Absent','Late') DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `class_schedule_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `subject_id`, `date`, `status`, `student_id`, `class_schedule_id`) VALUES
(45, 15, '2024-12-20', 'Absent', 14, 4),
(46, 15, '2024-12-20', 'Present', 16, 4),
(47, 15, '2024-12-20', 'Present', 19, 4),
(48, 18, '2024-12-20', 'Present', 14, 5),
(49, 18, '2024-12-20', 'Present', 16, 5),
(50, 18, '2024-12-20', '', 19, 5),
(51, 15, '2024-12-15', 'Present', 14, 2),
(52, 15, '2024-12-15', 'Present', 16, 2),
(53, 15, '2024-12-15', '', 19, 2);

-- --------------------------------------------------------

--
-- Table structure for table `class_schedule`
--

CREATE TABLE `class_schedule` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_schedule`
--

INSERT INTO `class_schedule` (`id`, `subject_id`, `schedule_date`, `time_start`, `time_end`) VALUES
(1, 15, '2024-12-12', '10:30:00', '13:30:00'),
(2, 15, '2024-12-15', '10:30:00', '13:30:00'),
(3, 15, '2024-02-20', '12:20:00', '14:00:00'),
(4, 15, '2024-12-20', '10:30:00', '13:30:00'),
(5, 18, '2024-12-20', '10:30:00', '13:30:00'),
(6, 19, '2024-12-20', '10:30:00', '12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`, `instructor_id`) VALUES
(13, 'BSIT', 1),
(17, 'Web Application Development', 1),
(19, 'BSED', 4);

-- --------------------------------------------------------

--
-- Table structure for table `course_subject`
--

CREATE TABLE `course_subject` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_subject`
--

INSERT INTO `course_subject` (`id`, `course_id`, `subject_id`) VALUES
(10, 13, 15),
(11, 17, 16),
(12, 17, 15),
(13, 17, 17),
(14, 17, 18);

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_name` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `total_score` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `subject_id`, `exam_name`, `date`, `total_score`) VALUES
(6, 15, 'Prelim Exam', '2024-12-13', 100),
(7, 18, 'Prelim Exam ', '2024-12-25', 100),
(8, 19, 'Prelim Exam', '2024-12-13', 100);

-- --------------------------------------------------------

--
-- Table structure for table `exam_scores`
--

CREATE TABLE `exam_scores` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `score` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_scores`
--

INSERT INTO `exam_scores` (`id`, `exam_id`, `student_id`, `score`) VALUES
(12, 6, 14, 90),
(13, 6, 16, 99),
(14, 7, 14, 92),
(15, 7, 16, 88),
(16, 7, 19, 66),
(17, 6, 29, 50),
(18, 7, 29, 100);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `grade` float DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grade_weights`
--

CREATE TABLE `grade_weights` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `quiz_weight` float DEFAULT 0,
  `activity_weight` float DEFAULT 0,
  `attendance_weight` float DEFAULT 0,
  `exam_weight` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade_weights`
--

INSERT INTO `grade_weights` (`id`, `subject_id`, `quiz_weight`, `activity_weight`, `attendance_weight`, `exam_weight`) VALUES
(1, 15, 20, 30, 10, 40),
(4, 18, 25, 30, 15, 30),
(5, 16, 20, 30, 10, 40),
(6, 19, 20, 30, 10, 40);

-- --------------------------------------------------------

--
-- Table structure for table `parent_codes`
--

CREATE TABLE `parent_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `student_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parent_codes`
--

INSERT INTO `parent_codes` (`id`, `code`, `student_id`) VALUES
(5, 'e78cf8d8f1b9ac05', 14),
(6, '93102386dfc88b23', 15),
(7, '881c693e6b5e2253', 29),
(8, '41a39fda72aedafd', 16);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `quiz_name` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `total_score` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `subject_id`, `quiz_name`, `date`, `total_score`) VALUES
(7, 15, 'Pop Quiz', '2024-12-13', 20),
(8, 16, '', '0000-00-00', 0),
(9, 18, 'Surprise Quiz', '2024-12-20', 20),
(10, 16, 'Surprise Quiz', '2024-12-20', 30),
(11, 19, 'Pop Quiz', '2024-12-20', 30),
(12, 18, 'Sample Quiz', '2024-12-20', 20);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_scores`
--

CREATE TABLE `quiz_scores` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `score` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_scores`
--

INSERT INTO `quiz_scores` (`id`, `quiz_id`, `student_id`, `score`) VALUES
(25, 7, 14, 10),
(26, 7, 16, 20),
(27, 9, 14, 15),
(28, 9, 16, 20),
(29, 9, 19, 10),
(30, 11, 14, 20),
(31, 11, 16, 15),
(32, 11, 19, 20),
(33, 7, 29, 20),
(34, 10, 29, 20),
(35, 9, 29, 20),
(36, 9, 15, 20);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(4, 'Admin'),
(1, 'Instructor'),
(3, 'Parent'),
(2, 'Student'),
(5, 'Super Admin');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `section_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `section_name`) VALUES
(2, 'WAD2AB'),
(3, 'WAD2CD'),
(4, 'WAD2EF');

-- --------------------------------------------------------

--
-- Table structure for table `section_subject`
--

CREATE TABLE `section_subject` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_subject`
--

INSERT INTO `section_subject` (`id`, `section_id`, `subject_id`) VALUES
(1, 2, 15),
(11, 2, 16),
(7, 2, 18),
(12, 2, 19),
(2, 3, 15),
(9, 3, 16),
(8, 3, 18),
(10, 4, 15);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `section_id`) VALUES
(14, 30, 2),
(15, 31, 3),
(16, 32, 2),
(17, 34, 4),
(18, 36, 4),
(19, 37, 2),
(28, 50, 2),
(29, 58, 2);

-- --------------------------------------------------------

--
-- Table structure for table `student_data`
--

CREATE TABLE `student_data` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `usn` varchar(15) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `enrolled_subjects` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_data`
--

INSERT INTO `student_data` (`id`, `student_id`, `usn`, `full_name`, `enrolled_subjects`, `date_of_birth`, `gender`, `contact_number`, `address`) VALUES
(8, 14, '23002138200', 'Pepep Jaballa', NULL, '2003-02-09', 'Male', '09123456789', 'Capoocan, Leyte'),
(9, 15, '23002138201', 'Pep\'s Egano', NULL, NULL, NULL, NULL, NULL),
(10, 16, '23002138205', 'Peps Egano', NULL, NULL, NULL, NULL, NULL),
(11, 17, '23002138210', 'Pep\'s Egano', NULL, NULL, NULL, NULL, NULL),
(12, 18, '23002138299', 'Pep\'s Egano', NULL, NULL, NULL, NULL, NULL),
(13, 19, '230021382055', 'Mark John Egano', NULL, NULL, NULL, NULL, NULL),
(15, 28, '23456789875', 'Jeromee Jaballa', NULL, NULL, NULL, NULL, NULL),
(16, 29, '21000219400', 'Lance Dinaga', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`) VALUES
(15, 'System Integration and Architecture 1'),
(16, 'Introduction to Human Computer Interaction'),
(17, 'IT Major Elective 1 Web Application Development'),
(18, 'Integrative Programming and Technology'),
(19, 'Euthenics'),
(20, 'Calculus'),
(24, 'Math');

-- --------------------------------------------------------

--
-- Table structure for table `superadmin_codes`
--

CREATE TABLE `superadmin_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(100) NOT NULL,
  `status` enum('unused','used') DEFAULT 'unused',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `superadmin_codes`
--

INSERT INTO `superadmin_codes` (`id`, `code`, `status`, `created_at`) VALUES
(1, 'PEPSGWAPS', 'used', '2024-12-17 23:42:12'),
(2, 'Peps', 'used', '2024-12-18 00:59:17'),
(3, 'NEW', 'used', '2024-12-18 02:09:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('pending','active','rejected') DEFAULT 'pending',
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role_id`, `created_at`, `status`, `profile_image`) VALUES
(1, 'admin', '$2y$10$Y3Tt7IFk4tpSvSZhm/XfMuIIm8JBoY8Egcw1TbK4WbikhjOUonThq', 'Mark John Egano', 'pepepmjegano@gmail.com', 1, '2024-12-07 10:20:04', 'active', NULL),
(2, 'pepep', '$2y$10$E4Q8MvHHB4K4OpLRBoxEeek4ykDwRHA8rEYHfBHcIk9Np/mmIk3zS', 'Pepep Egano', 'pepepjaballa36140@gmail.com', 1, '2024-12-07 14:31:31', 'active', NULL),
(4, 'admin3', '$2y$10$uxZv3vlkAG8aSc7xABmNKOPQFHYGFoaKIMaWzqOdUgM1uTUe2VcOO', 'Peps Egano', 'pepepmjegano@gmail.com', 1, '2024-12-08 10:31:45', 'active', NULL),
(30, 'mark ', '$2y$10$Pbe9183zqb417eH8OFcbj.LJ6zeRzoFp2Amz4CVTMY4g/S3uY3tJ.', 'Pepep Jaballa', 'markjohnegano@gmail.com', 2, '2024-12-10 19:18:55', 'active', NULL),
(31, 'mark1', '$2y$10$.LeWoOo9.sjb/d6EbvSBqeW9My8hiJjsswDUZgwEun.kg5cEGYG5K', 'Pep\'s Egano', 'pepepmjegan@gmail.com', 2, '2024-12-10 20:13:47', 'active', NULL),
(32, 'mark3', '$2y$10$0N.e6HCdgm6dCXGfPTmkce3nmWf35xC4to21HfIEZoIPmqC5BwEwW', 'Peps Egano', 'pepepmjeno@gmail.com', 2, '2024-12-10 20:28:26', 'active', NULL),
(34, 'mark5', '$2y$10$0LEBRNFtmkisLbkuOQUwceAPxC/jsF/RB6lvOLyps5Qii/xytUQ/a', 'Pep\'s Egano', 'emmanueljaballa@gmail.com', 2, '2024-12-11 23:14:51', 'active', NULL),
(36, 'mark9', '$2y$10$Qskj1Ed8a3jcR2DVE2.XHOJEoXgLp8WKRIDnTOnHONVgYOJXFlkZG', 'Pep\'s Egano', 'pepep@gmail.com', 2, '2024-12-15 11:38:33', 'active', NULL),
(37, 'pepep1', '$2y$10$PmKokxCuRFVJ/JMrgvc5ze7cZxhG593MNd3ZBmaBYeEzStATG0QXa', 'Mark John Egano', 'fgh@gmail.com', 2, '2024-12-15 11:46:24', 'active', NULL),
(50, 'ark', '$2y$10$d5AdNwr5AXBH1QAKDorh1uJBQ5QVbKW8S9GV7OMvVX/KXlH5Eky4K', 'Jeromee Jaballa', 'jeromee@gmail.com', 2, '2024-12-17 21:18:49', 'active', NULL),
(51, 'teacher', '$2y$10$M1UPLgTKRTTxOABAC6Bij.G.P4LLfohlkxHdsP3UAxf0MJX0FsYWW', 'Abubwe Bubwe', 'bubwe@gmail.com', 1, '2024-12-17 21:22:03', 'rejected', NULL),
(52, 'superadmin', '$2y$10$ZvstYjH/kBmCByypX1HmqOgXdNNik18bShTnakcDzUFDmBvzzZLSW', 'Monkey D. Luffy', 'zoro@gmail.com', 5, '2024-12-18 00:03:02', 'active', NULL),
(54, 'zoro', '$2y$10$LwmQ6hziKteNJZoM5W3N3ew5a6SQ51kab78iXPqFQwzyb4ak8JCCq', 'Roronoa Zoro', 'roronoazoro@gmail.com', 4, '2024-12-18 02:11:24', 'active', NULL),
(56, 'adminsss', '$2y$10$jsl3L5TJg2zLhstglAIkVu2SAWtkOZMuMuJLV/7/frm8tSqj0k/oG', 'markjohn', 'markjohnegano@gmail.com', 1, '2024-12-18 18:52:55', 'rejected', NULL),
(57, 'teacher1', '$2y$10$eIFz9OuGUz2E37iUre7gkO8UDiDMMCQgdt1XR7wAtm0Wmt4GGLQGW', 'Babwe Kombre', 'babwe@gmail.com', 1, '2024-12-18 19:00:32', 'rejected', NULL),
(58, 'hakdog', '$2y$10$sdfqfIlc/N6EmcRUc6LNW.4GIW9FH9SHEKVaH/TOUlDrNYJt3fZ/2', 'Lance Dinaga', 'lancedinaga47@gmail.com', 2, '2024-12-19 15:47:22', 'active', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activities_subject` (`subject_id`);

--
-- Indexes for table `activity_scores`
--
ALTER TABLE `activity_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `assigned_subjects`
--
ALTER TABLE `assigned_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_subjects_ibfk_1` (`instructor_id`),
  ADD KEY `assigned_subjects_ibfk_2` (`subject_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attendance_subject` (`subject_id`),
  ADD KEY `fk_attendance_student` (`student_id`),
  ADD KEY `fk_attendance_class_schedule` (`class_schedule_id`);

--
-- Indexes for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_ibfk_1` (`instructor_id`);

--
-- Indexes for table `course_subject`
--
ALTER TABLE `course_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_subject_ibfk_1` (`course_id`),
  ADD KEY `course_subject_ibfk_2` (`subject_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exams_subject` (`subject_id`);

--
-- Indexes for table `exam_scores`
--
ALTER TABLE `exam_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_scores_ibfk_1` (`exam_id`),
  ADD KEY `exam_scores_ibfk_2` (`student_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `grades_ibfk_2` (`course_id`);

--
-- Indexes for table `grade_weights`
--
ALTER TABLE `grade_weights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `parent_codes`
--
ALTER TABLE `parent_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `parent_codes_ibfk_1` (`student_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quizzes_subject` (`subject_id`);

--
-- Indexes for table `quiz_scores`
--
ALTER TABLE `quiz_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_scores_ibfk_1` (`quiz_id`),
  ADD KEY `quiz_scores_ibfk_2` (`student_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `section_subject`
--
ALTER TABLE `section_subject`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section_subject` (`section_id`,`subject_id`),
  ADD UNIQUE KEY `section_id` (`section_id`,`subject_id`),
  ADD KEY `fk_section_subject` (`subject_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `students_ibfk_2` (`section_id`),
  ADD KEY `students_ibfk_1` (`user_id`);

--
-- Indexes for table `student_data`
--
ALTER TABLE `student_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_data_ibfk_1` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `superadmin_codes`
--
ALTER TABLE `superadmin_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `users_ibfk_1` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `activity_scores`
--
ALTER TABLE `activity_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `assigned_subjects`
--
ALTER TABLE `assigned_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `class_schedule`
--
ALTER TABLE `class_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `course_subject`
--
ALTER TABLE `course_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `exam_scores`
--
ALTER TABLE `exam_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grade_weights`
--
ALTER TABLE `grade_weights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `parent_codes`
--
ALTER TABLE `parent_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `quiz_scores`
--
ALTER TABLE `quiz_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `section_subject`
--
ALTER TABLE `section_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `student_data`
--
ALTER TABLE `student_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `superadmin_codes`
--
ALTER TABLE `superadmin_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activities_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_scores`
--
ALTER TABLE `activity_scores`
  ADD CONSTRAINT `activity_scores_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`),
  ADD CONSTRAINT `activity_scores_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `assigned_subjects`
--
ALTER TABLE `assigned_subjects`
  ADD CONSTRAINT `assigned_subjects_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assigned_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_class_schedule` FOREIGN KEY (`class_schedule_id`) REFERENCES `class_schedule` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_schedule`
--
ALTER TABLE `class_schedule`
  ADD CONSTRAINT `class_schedule_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_subject`
--
ALTER TABLE `course_subject`
  ADD CONSTRAINT `course_subject_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `fk_exams_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_scores`
--
ALTER TABLE `exam_scores`
  ADD CONSTRAINT `exam_scores_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `exam_scores_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grade_weights`
--
ALTER TABLE `grade_weights`
  ADD CONSTRAINT `grade_weights_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `parent_codes`
--
ALTER TABLE `parent_codes`
  ADD CONSTRAINT `parent_codes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `fk_quizzes_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_scores`
--
ALTER TABLE `quiz_scores`
  ADD CONSTRAINT `quiz_scores_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quiz_scores_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `section_subject`
--
ALTER TABLE `section_subject`
  ADD CONSTRAINT `fk_section_subject` FOREIGN KEY (`subject_id`) REFERENCES `assigned_subjects` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `section_subject_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `section_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_data`
--
ALTER TABLE `student_data`
  ADD CONSTRAINT `student_data_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
