-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3307
-- Thời gian đã tạo: Th5 17, 2025 lúc 10:37 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `dbstudent`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `courses`
--

CREATE TABLE `courses` (
  `course_id` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `credits` int(11) NOT NULL,
  `lecturer` varchar(100) DEFAULT NULL,
  `schedule` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `credits`, `lecturer`, `schedule`, `created_at`) VALUES
('C00001', 'Web', 3, 'Dương Quá', 'T2-4, 7:30-9:30', '2025-05-16 18:34:39'),
('C00002', 'Finance', 4, 'Murray ', 'T4-7, 15:00-18:00', '2025-05-16 19:02:25'),
('C00003', 'FB', 3, NULL, NULL, '2025-05-17 04:59:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `d`
--

CREATE TABLE `d` (
  `dep_id` varchar(11) NOT NULL,
  `dep_name` varchar(100) NOT NULL,
  `dep_email` varchar(50) NOT NULL,
  `dep_password` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `d`
--

INSERT INTO `d` (`dep_id`, `dep_name`, `dep_email`, `dep_password`, `location`, `status`) VALUES
('IS01', 'BDA', 'bda@vnu.edu.vn', '$2y$10$CJ7mp1xPU8yqmr7KpB7d4O.8VCy.WOCswY3fRfOsws9', '1st VNUIS', 'active'),
('IS02', 'Management Information System', 'mis@vnu.edu.vn', '$2y$10$sA8oyqUrFTNMpeMybbY4WenrpsAcfv9H7/0GT74IYu7', '1st VNUIS', 'active'),
('IS03', 'International Business', 'ib@vnu.edu.vn', '$2y$10$0qYRbGfNScVsYCictJItb.DMrGRnLkYa0KXQuQuhGno', '4th floor', 'active'),
('IS04', 'AC', 'ac@gmail.com', '$2y$10$ls57majeBRwhsx/Y8DDGq.1kpbjNDaU59vNysDyP0nn', '4th floor', 'active'),
('IS05', 'English', 'eng@gmail.com', '$2y$10$M.9of5sK6sKEX89ESrigfuKN71Y1u.3Ookqc1kTpRVp', '1st VNUIS', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `dep_id` varchar(10) NOT NULL,
  `dob` date NOT NULL,
  `address` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `img` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `student`
--

INSERT INTO `student` (`student_id`, `student_name`, `dep_id`, `dob`, `address`, `email`, `password`, `phone_number`, `img`) VALUES
(250001, 'Nguyen Van An', 'IS04', '2000-04-05', 'Bình Thuận', '21098@gmail.com', '', '0358923452', ''),
(250002, 'Vu Thi Binh ', 'IS04', '2003-06-07', 'Điện Biên', 'bvt@gmail.com', '', '0328588238', ''),
(250003, 'Tran Thai Thinh', 'IS02', '2001-03-04', 'Vĩnh Phúc', 'thinhtt@gmail.com', '', '0326357723', ''),
(250004, 'Chi Quế Trần', 'IS02', '2001-05-03', 'Vĩnh Long', 'chitq@gmail.com', '', '0325782375', ''),
(250005, 'Chi Phuong  Trần', 'IS01', '2000-02-07', 'Tuyên Quang', 'trquchi@gmail.com', '', '0941630428', ''),
(250006, 'Holland David', 'IS03', '2000-02-09', 'Hà Nội', 'hd@gmail.com', '', '037256372752', ''),
(250007, 'Tran Van Binh', 'IS05', '2001-05-31', 'Bắc Ninh', 'binhtv@gmail.com', '', '0273658235', ''),
(250008, 'Nguyen Van Lam', 'IS05', '2003-02-27', 'Bac Giang', 'lamnv@gmail.com', '', '03265687043', ''),
(250009, 'Dao Thu Phuong', 'IS02', '2000-09-09', 'Hung Yen', 'phuongdt@gmail.com', '', '0375829835', ''),
(250010, 'Chi Quế Trần', 'IS01', '2003-12-06', 'TP Hồ Chí Minh', 'tech.admin@company.com', '', '0912345677', ''),
(250011, 'Phạm Thanh Tâm', 'IS02', '2003-12-03', 'Bình Định', 'hihihi@gmail.com', '', '0929483924', ''),
(250012, 'Anna Trương', 'IS02', '2001-04-23', 'Bà Rịa - Vũng Tàu', 'anna@gmail.com', '', '02743256923', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `student_courses`
--

CREATE TABLE `student_courses` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `course_id` varchar(20) NOT NULL,
  `enrollment_date` date NOT NULL,
  `grade` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `student_courses`
--

INSERT INTO `student_courses` (`id`, `student_id`, `course_id`, `enrollment_date`, `grade`) VALUES
(2, '250006', 'C00001', '2025-05-17', 9.40),
(3, '250010', 'C00001', '2025-05-17', 9.80),
(4, '250001', 'C00001', '2025-05-17', NULL),
(5, '250012', 'C00001', '2025-05-17', NULL),
(6, '250008', 'C00001', '2025-05-17', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `student_id`, `username`, `password`, `role`) VALUES
(11, 250002, '250002', '$2y$10$PaPADc5NAt.Ldd8AjudX3e8fQ8AZcgKouEKFMixiRy8K/vanoURyi', 'student'),
(12, 250003, '250003', '$2y$10$Y8cNBY.osj7D/UscHyZedOttMx6RdKobNGYIWBYFzUSsHMr1/rlkS', 'student'),
(13, 250004, '250004', '$2y$10$naxXYDZ2nCaCTajyt1QqNuFPjPb1B63GGrpsWTeYIXFMSh0FN2dSO', 'student'),
(14, 250005, '250005', '$2y$10$pHgKqLtLd0bHJIG1ptPzwOkBVpW5xUbF0mAlyImIvupG0kcpITUoi', 'student'),
(15, 250006, '250006', '$2y$10$WdlP7ihRWC0Ysv411GbFB.fZEvfmaj9vbY//Ocv76m7Err8IKP99W', 'student'),
(16, NULL, 'IS04', '$2y$10$ls57majeBRwhsx/Y8DDGq.1kpbjNDaU59vNysDyP0nn4C8AxLUA7W', 'department'),
(18, NULL, 'IS05', '$2y$10$M.9of5sK6sKEX89ESrigfuKN71Y1u.3Ookqc1kTpRVpf1jHmx/GaS', 'department'),
(19, 250007, '250007', '$2y$10$DFZykz9X7knup2gPCKIXDe1NLi1XEF92ilyIOiCuPrSUBxMDqPcny', 'student'),
(20, 250008, '250008', '$2y$10$sl49YQg6XF2VsgzUs/b5Pe1STGP6SuXXhxgiS7w7qPS0LDsRiHeQ6', 'student'),
(21, NULL, 'IS01', '$2y$10$riCGE/HgU9xmWMHKerYEw.WFSwOrAopc6sVpc2uLuMN4EHNaqoUDK', 'department'),
(22, NULL, 'IS02', '$2y$10$VjCxGqCRx0vSh.4M9mR86OOYLrvcel2UwKmkTzOmAHOxiGswHIxfq', 'department'),
(23, NULL, 'IS03', '$2y$10$wrVLo7YoThFDqj9DWj6veOKgB4q0gfYs9z9qb8wlgQB5uEoTO9IOu', 'department'),
(24, 250009, '250009', '$2y$10$U5zaobEX4NCCDbkd398pL.hO7lzNdg9ziq/TmQofAsfSTFbLwBUaK', 'student'),
(25, 250001, '250001', '$2y$10$x/Q8AUba/C8EvPBnctDdi.5oBOXWhK8g3GfB3REl57D9b3S9m9JUK', 'student'),
(27, 250010, '250010', '$2y$10$g3l7gAorejoKfYAwq2lwaObAB90ZPxHq5BCsvtLcDQxfCqohYojpi', 'student'),
(28, 250011, '250011', '$2y$10$WC/eOutB6sttSmUr785jvOaXjOJN6HFCW4ZuL2xGr7.RoQDPmqYTy', 'student'),
(29, NULL, 'superadmin', '$2y$10$IL/0u2eh8eEEautA9JAQYux/Uc7ttQVlv1gkQUnF6Le6tOPwvd.ZO', 'admin'),
(30, NULL, 'adminChi', '$2y$10$gemWXfF5rLsNzR7yplyjlOta6VTUbHaAndBzJk1LtKx4gJDCQUyKS', 'admin'),
(31, 250012, '250012', '$2y$10$Dka66aNwxBH.WLBp.Ss6iOzQHnvPhrnHVNwXoDgwbKALepFhbj.ki', 'student'),
(32, NULL, 'admin1', '$2y$10$v2WuyFX5y0BVjalAI/dS8.mO5gpaYHooCrIQUCNpZOpl5WNVxZlca', 'superadmin');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Chỉ mục cho bảng `d`
--
ALTER TABLE `d`
  ADD PRIMARY KEY (`dep_id`);

--
-- Chỉ mục cho bảng `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `unique` (`phone_number`),
  ADD KEY `fk_student_department` (`dep_id`);

--
-- Chỉ mục cho bảng `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `fk_student_courses_student` (`student_id`),
  ADD KEY `fk_student_courses_courses` (`course_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_student` (`student_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_student_department` FOREIGN KEY (`dep_id`) REFERENCES `d` (`dep_id`);

--
-- Các ràng buộc cho bảng `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_student` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
