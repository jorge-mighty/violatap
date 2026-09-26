-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2026 at 05:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `violatap`
--

-- --------------------------------------------------------

--
-- Table structure for table `code_of_discipline`
--

CREATE TABLE `code_of_discipline` (
  `offense_id` tinyint(3) UNSIGNED NOT NULL,
  `offense` varchar(50) NOT NULL,
  `category` enum('Minor','Major') NOT NULL,
  `description` text DEFAULT NULL,
  `sanction_1st` text NOT NULL,
  `sanction_2nd` text NOT NULL,
  `sanction_3rd` text NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `code_of_discipline`
--

INSERT INTO `code_of_discipline` (`offense_id`, `offense`, `category`, `description`, `sanction_1st`, `sanction_2nd`, `sanction_3rd`, `is_archived`, `created_at`) VALUES
(1, 'Loitering and Noise', 'Minor', 'Loitering during class hours, making unnecessary noise such as singing or boisterous conversation causing annoyance and disturbance', 'Warning and refer for counseling', 'Warning; Summon parent or guardian', '2 hours community service with counseling', 0, '2026-08-25 12:00:13'),
(2, 'Prohibited Attire', 'Minor', 'Wearing prohibited attire inside the University', 'Warning followed by counseling', 'Prohibition from entering University premises', 'Prohibition from entering University premises; Summon parent or guardian; 2 hours community service', 0, '2026-08-25 12:00:13'),
(3, 'Classroom Abandonment', 'Minor', 'Abandoning the classroom without permission from the instructor while the class is going on', 'Warning followed by counseling', 'Prohibition from attending the class; Submit letter of apology signed by the parent/guardian and noted by the OSAS Director', 'Prohibition from attending the class; Summon parent/guardian; 2 hours community service', 0, '2026-08-25 12:00:13'),
(4, 'Prohibited Grooming/Piercings', 'Minor', 'Having a prohibited haircut, hairstyle and prohibited piercings', 'Warning followed by counseling', 'Prohibition from entering University premises; Summon parent/guardian.', 'Prohibition from entering University premises; 2 hours community service', 0, '2026-08-25 12:00:13'),
(5, 'Smoking and Vaping', 'Minor', 'Smoking and Vaping within the University Premises', 'Confiscation of prohibited ENDS/ENNDS; Warning followed by counseling', 'Summon parent/guardians; 2 hours community service', 'Summon parent/guardians; 4 hours community service', 0, '2026-08-25 12:00:13'),
(6, 'Littering', 'Minor', 'Littering in the University Campus', 'Warning followed by counseling', 'Summon parent/guardian; 4 hours community service', 'Summon parent/guardians; 8 hours community service', 0, '2026-08-25 12:00:13'),
(7, 'Unauthorized Material Distribution', 'Minor', 'Posting/Distribution of printed materials within the University without approval.', 'Taking down/confiscation of posted and printed materials; Warning followed by counseling', 'Summon parents/guardians; 4 hours community service', 'Summon parent/guardians; 8 hours community service', 0, '2026-08-25 12:00:13'),
(8, 'Posting Derogatory/Seditious Materials', 'Minor', 'Posting of derogatory /seditious materials within the University premises', 'Warning followed by counselling; 2 hours community service', 'Summon parent/guardian; 4 hours community service', 'Summon parent/guardian; 8 hours community service', 0, '2026-08-25 12:00:13'),
(9, 'ID Violations', 'Minor', 'Non-wearing of valid ID/wearing of non-validated ID inside the campus', 'Warning followed by counseling', 'Summon parent/guardian; 2 hours community service', 'Summon parent/guardian; 4 hours community service', 0, '2026-08-25 12:00:13'),
(10, 'Unauthorized Assembly', 'Minor', 'Unauthorized assembly of students within the University during class hours', 'Warning followed by counselling; 2 hours community service', 'Summon parents/guardians; 4 hours community service', 'Summon parent/guardian; 8 hours community service', 0, '2026-08-25 12:00:13'),
(11, 'Alcohol Intoxication', 'Minor', 'Intoxicated with liquor while within the premises of the University', 'Warning followed by counselling; 4 hours community service', 'Summon parent/guardian; 8 hours community service', 'Summon parent/guardian; 16 hours community service', 0, '2026-08-25 12:00:13'),
(12, 'Unlawful Mass Action', 'Minor', 'Involvement in Mass action and activities for purposes contrary to law', 'Warning followed by counseling; Summon parent/guardian; 8 hours community service', 'Summon parent/guardian; 5 days suspension', 'Summon parent/guardian; 1 semester suspension', 0, '2026-08-25 12:00:13'),
(13, 'Bringing Unauthorized Outsiders', 'Minor', 'Bringing outsider/s inside the University premises to commit acts punishable under this Code', 'Summon parent/guardian; Warning followed by counseling; 8 hours community service', 'Summon parent/guardian; Warning followed by counselling; 10 days community service', 'Summon parent/guardian; Expulsion', 0, '2026-08-25 12:00:13'),
(14, 'Unaccredited Organizations/Fraternities', 'Minor', 'Organizing/joining any unaccredited/ unrecognized fraternity/sorority or frasority and other student organizations and whose purpose is to create disorder and disability to the University', 'Warning followed by counseling; Summon parent/guardian; 5 days suspension', 'Summon parent/guardian; 1 semester suspension', 'Summon parent/guardian; Expulsion', 0, '2026-08-25 12:00:13'),
(15, 'Extortion', 'Minor', 'Extorting money from fellow students', 'Warning followed by counseling; Summon parent/guardian; 5 days suspension and restitution of the amount /property taken.', 'Summon parent/guardian; 1 semester suspension', 'Summon parent/guardian; Expulsion', 0, '2026-08-25 12:00:13'),
(16, 'Gambling', 'Minor', 'Any form of gambling within the University premises', 'Warning followed by counseling; 4 hours community service', 'Summon parent/guardian; 8 hours community service', 'Summon parent/guardian; 16 hours community service', 0, '2026-08-25 12:00:13'),
(17, 'Unpaid Just Debt', 'Minor', 'Failure to pay a just debt', 'Warning followed by counselling and payment of the debt', 'Summon parents/guardians; counselling and payment of the debt', 'Summon parent/guardian; Payment of the debt. In case of non-payment, issuance of good moral certificate deferred', 0, '2026-08-25 12:00:13'),
(18, 'Data Privacy Violation (Minor)', 'Minor', 'Violation of any provision of the Data Privacy Act', 'Warning followed by counseling; Summon parent/guardian; 10 days suspension', 'Warning followed by counseling; Summon parent/guardian; 1 semester suspension', 'Warning followed by counseling; Summon parent/guardian; Expulsion', 0, '2026-08-25 12:00:13'),
(19, 'Fund Mismanagement', 'Minor', 'Unlawful use and neglectful conduct in the custody of funds', 'Warning followed by counseling and payment/restitution of the funds', 'Summon parent/guardian; Counseling and payment/restitution; 1 semester suspension', 'Summon of parent/guardian; Payment/restitution. In case of non-payment, good moral deferred; Expulsion', 0, '2026-08-25 12:00:13'),
(20, 'Alarm and Scandal', 'Major', 'Violent conduct tantamount to alarm and scandal', 'Summon parent/guardian; Refer for counseling; 4 hours community service', 'Summon parent/guardian; Refer for counseling; 8 hours community service', 'Summon parent/guardian; Refer for counseling; 5 days Suspension.', 0, '2026-08-25 12:00:13'),
(21, 'Grave Threat and Bullying', 'Major', 'Grave threat and bullying of fellow student', 'Summon parent/guardian; Refer for counseling; 4 hours community service', 'Summon parent/guardian; Refer for counseling; 8 hours community service', 'Summon parent/guardian; Refer for counseling; 5 days suspension', 0, '2026-08-25 12:00:13'),
(22, 'Slight Physical Assault (Student)', 'Major', 'Assault of fellow student resulting to slight physical injury', 'Summon parent/guardian; Refer for counseling, reimbursement of medical expenses; 8 hours community service', 'Summon parent/guardian; Refer for counseling, payment of damages/reimbursement; 16 hours community service', 'Summon parent/guardian; Refer for counseling; Payment of damages/reimbursement; 5 days suspension', 0, '2026-08-25 12:00:13'),
(23, 'Serious Physical Assault (Student)', 'Major', 'Assault to fellow student resulting to serious physical injury', 'Summon parent/guardian; Refer for counseling, reimbursement of medical expenses/damages; 16 hours community service', 'Summon parent/guardian; Refer for counseling, payment of damages/reimbursement; 24 hours community service', 'Payment of damages/reimbursement of medical expenses; Expulsion from the University', 0, '2026-08-25 12:00:13'),
(24, 'Threat Against Authority', 'Major', 'Grave threat against persons in authority of the University and their agents', 'Summon parent/guardian; Refer for counseling and 4 hours community service.', 'Summon parent/guardian; Refer for counseling and 8 hours community service.', 'Summon parent/guardian; Refer for counselling, and 5 days suspension.', 0, '2026-08-25 12:00:13'),
(25, 'Slight Assault Against Authority', 'Major', 'Assault against persons in authority of the University and their agents resulting to slight physical injury', 'Summon parent/guardian; Refer for counseling, reimbursement of medical expenses; 8 hours community service', 'Summon parent/guardian; Refer for counseling; Payment of damages/reimbursement; 16 hours community service', 'Summon parent/guardian; Refer for counseling; Payment of damages/reimbursement; 5 days suspension', 0, '2026-08-25 12:00:13'),
(26, 'Serious Assault Against Authority', 'Major', 'Assault against persons in authority of the University and their agents resulting to serious physical injury', 'Summon parent/guardian; Refer for counseling; Reimbursement of medical expenses/damages; 16 hours community service', 'Summon parent/guardian; Refer for counseling; Payment of damages/reimbursement; 24 hours community service', 'Summon parent/guardian; Payment of damages/reimbursement; Expulsion from the University', 0, '2026-08-25 12:00:13'),
(27, 'Vandalism', 'Major', 'Vandalism and destruction of the University property.', 'Summon parent/guardian; Refer for counseling; Clean/restore and replace or pay for damages', 'Summon parent/guardian; Refer for counseling; Clean/restore and replace or pay; 8 hours community service', 'Summon parent/guardian; Refer for counseling; Clean/restore and replace or pay; 5 days suspension', 0, '2026-08-25 12:00:13'),
(28, 'Academic Cheating', 'Major', 'Cheating during examinations', 'Summon parent/guardian; Refer for counseling; Grade of 5.0 or failure in exam', 'Summon parent/guardian; Refer for counseling; Grade of 5.0 or failure in exam; 16 hours community service.', 'Summon parent/guardian; Refer for counseling; Grade of 5.0 or failure in subject; 24 hours community service', 0, '2026-08-25 12:00:13'),
(29, 'Proxy Examination', 'Major', 'Taking examinations by proxy', 'Summon parent/guardian; Refer for counseling; 1 semester suspension (If outsider: perpetual disqualification)', 'Summon parent/guardian; Refer for counseling; 1 year suspension (If outsider: perpetual disqualification)', 'Summon parent/guardian; Refer for counseling; Expulsion from University (If outsider: perpetually barred)', 0, '2026-08-25 12:00:13'),
(30, 'Pornographic Materials', 'Major', 'Viewing, reading, distribution of pornographic objects, pictures and literature', 'Warning followed by counseling.', 'Summon parent/guardian; 2 hours community service', 'Summon parent/guardian; 4 hours community service', 0, '2026-08-25 12:00:13'),
(31, 'Unauthorized University Representation', 'Major', 'Unauthorized representation of the University', 'Warning followed by Counseling; 2 hours community service', 'Summon parents/guardians; 4 hours community service', 'Summon parent/guardian; 8 hours community service', 0, '2026-08-25 12:00:13'),
(32, 'Liquor/Chemical Possession', 'Major', 'Possessing/selling of intoxicating liquor or chemicals in any form within Campus', 'Confiscation; Warning followed by counseling; Summon parent/guardian; 8 hours community service', 'Summon parent/guardian; 16 hours community service', 'Summon parent/guardian; 5 days suspension', 0, '2026-08-25 12:00:13'),
(33, 'Theft', 'Major', 'Theft committed against any person inside the University premises.', 'Restitution; Warning; Summon parent/guardian; Counseling; 5 days suspension', 'Summon parent/guardian; Counseling; 1 semester suspension; Restitution', 'Expulsion from the University', 0, '2026-08-25 12:00:13'),
(34, 'Qualified Theft', 'Major', 'Qualified theft committed by student with designations imbued with trust and confidence', 'Restitution; Warning; Summon parent/guardian; Counseling; 10 days suspension & restitution', 'Summon parent/guardian; Counselling; 1 semester suspension; Restitution', 'Expulsion from the University', 0, '2026-08-25 12:00:13'),
(35, 'Robbery', 'Major', 'Robbery committed within the University premises', 'Restitution; Warning; Summon parent/Guardian; Counseling; 10 days Suspension & restitution', 'Summon parent/guardian; Counselling; 1 semester suspension; Restitution', 'Expulsion from the University', 0, '2026-08-25 12:00:13'),
(36, 'Defamation/Slander', 'Major', 'Uttering defamatory, slanderous and libellous statements against University personnel/students', 'Warning followed by counselling; 4 hours community service', 'Summon parent/guardian; 8 hours community service', 'Summon parent/guardian; 16 hours community service', 0, '2026-08-25 12:00:13'),
(37, 'Disrespect to Faculty/Staff', 'Major', 'Disrespect of faculty members, employees and other University officials', 'Warning followed by counseling; 4 hours community service', 'Summon parent/guardian; 8 hours community service', 'Summon parent/guardian; 16 hours community service', 0, '2026-08-25 12:00:13'),
(38, 'Document Falsification', 'Major', 'Forging signatures, falsifying or tampering University records, credentials, or documents', 'Warning followed by counseling; Summon parent/guardian; 5 days suspension', 'Summon parent/guardian; 1 semester suspension', 'Summon parent/guardian; Expulsion', 0, '2026-08-25 12:00:13'),
(39, 'Intellectual Dishonesty/Plagiarism', 'Major', 'Committing any form of intellectual dishonesty such as, but not limited to plagiarism', 'Warning followed by counseling; Summon parent/guardian; 10 days suspension', 'Warning followed by counseling; Summon parent/guardian; 1 semester suspension', 'Warning followed by counseling; Summon parent/guardian; Expulsion', 0, '2026-08-25 12:00:13'),
(40, 'Data Privacy Violation (Major)', 'Major', 'Violation of any provision of the Data Privacy Act', 'Warning followed by counseling; Summon parent/guardian; 10 days suspension', 'Warning followed by counselling; Summon parent/guardian; 1 semester suspension', 'Warning followed by counselling; Summon parent/guardian; Expulsion', 0, '2026-08-25 12:00:13'),
(41, 'Lewd or Lascivious Conduct', 'Major', 'Lewd or Lascivious Conduct between opposite/same sex', 'Warning followed by counseling; Summon parent/guardian; 15 days suspension', 'Summon parent/guardian; 1 semester suspension.', 'Summon parent/guardian; Expulsion', 0, '2026-08-25 12:00:13'),
(42, 'Sexual Intercourse on Campus', 'Major', 'Indulging in sexual intercourse inside the university campus', 'Summon parent/guardian; Counseling; 1 semester suspension', 'Summon of parent/guardian; Counseling; 1 school year suspension', 'Summon of parent/guardian; Expulsion from the University', 0, '2026-08-25 12:00:13'),
(43, 'Video/Photo Voyeurism', 'Major', 'Involvement in video and photo -- voyeurism acts', 'Summon parent/guardian; Counseling; 1 semester suspension', 'Summon parent/guardian; Counseling; 1 school year suspension', 'Summon parent/guardian; Expulsion from the University', 0, '2026-08-25 12:00:13'),
(44, 'Cyberbullying', 'Major', 'Cyber bullying and libellous, malicious, derogatory and irresponsible use of social media', 'Warning followed by counselling; Summon parent/guardian; 15 days suspension', 'Summon parent/guardian; 1 semester suspension', 'Summon parent/guardian; 1 year suspension', 0, '2026-08-25 12:00:13'),
(45, 'Admission/Scholarship Deception', 'Major', 'Committing fraudulent acts/deception in connection with Admission, registration and scholarship', 'Summon parent/guardian; Counseling; Cancellation/revocation of enrolment/scholarship', 'N/A', 'N/A', 0, '2026-08-25 12:00:13'),
(46, 'Graduation/Honors Deception', 'Major', 'Committing fraudulent acts/deception in connection with graduation/Latin Honors', 'Summon parent/guardian; Counseling; Cancellation of graduation rights & revocation of Latin Honors', 'N/A', 'N/A', 0, '2026-08-25 12:00:13'),
(47, 'Cybercrime Violations', 'Major', 'Any violation of Cybercrime Act 2012 (Hacking, Illegal access, misuse of data)', 'Warning followed by counseling; Summon parent/guardian; 10 days suspension', 'Warning followed by counselling; Summon parent/guardian; 1 semester suspension', 'Warning followed by counselling; Summon parent/guardian; Expulsion', 0, '2026-08-25 12:00:13'),
(48, 'Deadly Weapons Possession', 'Major', 'An act violating P.D. 1972 or Unlawful possession of deadly weapons', 'Confiscation; Summon of Parents/Guardian; Referral/turn over to proper agency', 'N/A', 'N/A', 0, '2026-08-25 12:00:13'),
(49, 'Dangerous Drugs Violation', 'Major', 'An act violating R.A. 9165 or Comprehensive Dangerous Drugs Act of 2002', 'Custody of student; Refer/turn over to Proper agency.', 'N/A', 'N/A', 0, '2026-08-25 12:00:13'),
(53, 'asdasd', 'Minor', 'asdasdasdadsdasda', 'sadfas', 'safasfs', 'asfdasf', 1, '2026-09-07 19:51:48');

-- --------------------------------------------------------

--
-- Table structure for table `nfc_cards`
--

CREATE TABLE `nfc_cards` (
  `card_id` smallint(5) UNSIGNED NOT NULL,
  `nfc_uid` varchar(15) NOT NULL,
  `student_uid` smallint(5) UNSIGNED NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nfc_cards`
--

INSERT INTO `nfc_cards` (`card_id`, `nfc_uid`, `student_uid`, `is_active`) VALUES
(2, 'db:be:fe:4e', 19, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rbac_permissions`
--

CREATE TABLE `rbac_permissions` (
  `role` varchar(30) NOT NULL,
  `module_key` varchar(30) NOT NULL,
  `is_allowed` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rbac_permissions`
--

INSERT INTO `rbac_permissions` (`role`, `module_key`, `is_allowed`) VALUES
('COD', 'audit_logs', 1),
('COD', 'code_of_discipline', 1),
('COD', 'dashboard', 1),
('COD', 'mobile_app', 1),
('COD', 'settings', 1),
('COD', 'student_nfc_management', 1),
('COD', 'user_management', 1),
('COD', 'violation_records', 1),
('CSO', 'audit_logs', 1),
('CSO', 'code_of_discipline', 1),
('CSO', 'dashboard', 1),
('CSO', 'mobile_app', 1),
('CSO', 'settings', 1),
('CSO', 'user_management', 1),
('CSO', 'violation_records', 1),
('Guard', 'mobile_app', 1),
('Super Admin', 'audit_logs', 1),
('Super Admin', 'code_of_discipline', 1),
('Super Admin', 'dashboard', 1),
('Super Admin', 'mobile_app', 1),
('Super Admin', 'settings', 1),
('Super Admin', 'student_nfc_management', 1),
('Super Admin', 'user_management', 1),
('Super Admin', 'violation_records', 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_uid` smallint(5) UNSIGNED NOT NULL,
  `student_id_no` char(11) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `middlename` varchar(20) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `gender` enum('Male','Female') NOT NULL DEFAULT 'Male',
  `course` enum('BSIT','BSIS','BEED','BSED','BTVTED','BTLED','BSHM','BSTM','BS Entrep','BIndTech') DEFAULT 'BSIT',
  `major` varchar(60) DEFAULT NULL,
  `department` enum('Computer Studies Department','Teacher Education Department','Industrial Technology Department','Hospitality and Business Management Department') DEFAULT 'Computer Studies Department',
  `year_level` tinyint(3) UNSIGNED DEFAULT NULL,
  `section` varchar(5) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_uid`, `student_id_no`, `first_name`, `middlename`, `last_name`, `gender`, `course`, `major`, `department`, `year_level`, `section`, `created_at`, `is_archived`) VALUES
(19, '2026-6442-M', 'Mark Joseph', 'Reyes', 'Santos', 'Male', 'BSIS', '', 'Computer Studies Department', 1, 'A', '2026-09-16 08:23:49', 0),
(20, '2021-5619-M', 'asda', 'awqw', 'safda', 'Male', 'BSIT', '', 'Computer Studies Department', 2, 'A', '2026-09-18 21:21:50', 1),
(80, '2024-1857-M', 'Maria Elena', 'Santos', 'Dela Cruz', 'Female', 'BSIS', NULL, 'Computer Studies Department', 3, 'A', '2026-09-25 19:09:27', 0),
(81, '2015-0366-M', 'John Eric', 'Tadaya', 'Cano', 'Male', 'BSIT', NULL, 'Computer Studies Department', 4, 'A', '2026-09-25 19:09:27', 0),
(82, '2023-7584-M', 'Bea', 'Cervaña', 'Basan', 'Female', 'BSIT', NULL, 'Computer Studies Department', 4, 'A', '2026-09-25 19:09:27', 0),
(83, '2023-5899-M', 'Irish Shane', 'Elle', 'Elegua', 'Female', 'BSIT', NULL, 'Computer Studies Department', 4, 'A', '2026-09-25 19:09:27', 0),
(84, '2025-2764-M', 'Christian', 'Pascual', 'Flores', 'Male', 'BSHM', NULL, 'Hospitality and Business Management Department', 2, 'B', '2026-09-25 19:09:27', 0),
(85, '2025-6524-M', 'Rachelle Ann', 'Ramos', 'Aquino', 'Female', 'BSHM', NULL, 'Hospitality and Business Management Department', 2, 'C', '2026-09-25 19:09:27', 0),
(86, '2024-1625-M', 'Bea Nicole', 'Castillo', 'Gonzales', 'Female', 'BSTM', NULL, 'Hospitality and Business Management Department', 3, 'B', '2026-09-25 19:09:27', 0),
(87, '2024-6468-M', 'Kevin Dave', 'Aquino', 'Cruz', 'Male', 'BSTM', NULL, 'Hospitality and Business Management Department', 3, 'D', '2026-09-25 19:09:27', 0),
(88, '2023-7211-M', 'John Paul', 'Lim', 'Tan', 'Male', 'BS Entrep', NULL, 'Hospitality and Business Management Department', 4, 'A', '2026-09-25 19:09:27', 0),
(89, '2023-5016-M', 'Mary Grace', 'Cruz', 'Flores', 'Female', 'BS Entrep', NULL, 'Hospitality and Business Management Department', 4, 'A', '2026-09-25 19:09:27', 0),
(90, '2025-3683-M', 'Joshua Emmanuel', 'Flores', 'Tan', 'Male', 'BIndTech', 'Electronics Technology', 'Industrial Technology Department', 2, 'E', '2026-09-25 19:09:27', 0),
(91, '2023-7672-M', 'Mark Anthony', 'Villanueva', 'Torres', 'Male', 'BIndTech', 'Automotive Technology', 'Industrial Technology Department', 4, 'C', '2026-09-25 19:09:27', 0),
(92, '2023-3615-M', 'Princess Joy', 'Mendoza', 'Torres', 'Female', 'BIndTech', 'Heating Ventilating Air Conditioning-Refrigeration Technolog', 'Industrial Technology Department', 4, 'D', '2026-09-25 19:09:27', 0),
(93, '2026-5367-M', 'Jessa Mae', 'Aquino', 'Ramos', 'Female', 'BTLED', 'Home Economics', 'Teacher Education Department', 1, 'A', '2026-09-25 19:09:27', 0),
(94, '2026-5385-M', 'Juan Carlos', 'Reyes', 'Bautista', 'Male', 'BEED', NULL, 'Teacher Education Department', 1, 'B', '2026-09-25 19:09:27', 0),
(95, '2025-2428-M', 'Analyn', 'Garcia', 'Mendoza', 'Female', 'BSED', 'Mathematics', 'Teacher Education Department', 2, 'A', '2026-09-25 19:09:27', 0),
(96, '2025-6583-M', 'Krizia Mae', 'Santos', 'Bautista', 'Female', 'BEED', NULL, 'Teacher Education Department', 2, 'B', '2026-09-25 19:09:27', 0),
(97, '2024-1850-M', 'John Rey', 'Bautista', 'Mendoza', 'Male', 'BSED', 'Social Studies', 'Teacher Education Department', 3, 'C', '2026-09-25 19:09:27', 0),
(98, '1235', 'qwe', 'asd', 'zxcv', 'Male', 'BSED', 'Mathematics', 'Teacher Education Department', 4, 'A', '2026-09-25 20:18:26', 0);

-- --------------------------------------------------------

--
-- Table structure for table `system_audit_logs`
--

CREATE TABLE `system_audit_logs` (
  `log_id` int(10) UNSIGNED NOT NULL,
  `user_id` smallint(5) UNSIGNED NOT NULL,
  `action` enum('Successful Login','Failed Login Attempt','Successful Logout','Password Changed','Password Reset','User Registered','User Archived','User Restored','Violation Filed','Violation Verified','Case Settled','Student Registered','Student Updated','NFC Card Assigned','Offense Added','Offense Updated','Offense Archived','RBAC Policies Updated','RBAC Defaults Restored','Student Added','CSV File Imported','Card Disabled','Card Restored','Student Archived','Details Viewed','Student Restored','Offense Restored','Database Backup Created','Database Restored') NOT NULL,
  `details` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_audit_logs`
--

INSERT INTO `system_audit_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `timestamp`) VALUES
(1, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-09 15:52:10'),
(2, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-09 16:24:00'),
(3, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-09 16:44:07'),
(4, 14, 'Database Backup Created', 'Comprehensive system backup (App package, user data, system preferences) compiled and downloaded.', '::1', '2026-09-09 16:55:30'),
(5, 14, 'Database Backup Created', 'Comprehensive system backup (App package, user data, system preferences) compiled and downloaded.', '::1', '2026-09-09 17:31:29'),
(6, 14, 'Database Backup Created', 'Comprehensive system backup (App package, user data, system preferences) compiled and downloaded.', '::1', '2026-09-09 17:32:53'),
(7, 14, 'Database Restored', 'Failed system restoration attempt (invalid archive): violatap_full_system_backup_2026-09-09_19-32-53.zip', '::1', '2026-09-09 17:35:02'),
(8, 14, 'Database Backup Created', 'Comprehensive system backup (App package, user data, system preferences) compiled and downloaded.', '::1', '2026-09-09 17:35:21'),
(9, 14, 'Database Restored', 'Failed system restoration attempt (invalid archive): violatap_full_system_backup_2026-09-09_19-35-21.zip', '::1', '2026-09-09 17:35:35'),
(10, 14, 'Database Backup Created', 'Comprehensive system backup (App package, user data, system preferences) compiled and downloaded.', '::1', '2026-09-09 17:39:38'),
(11, 14, 'Database Restored', 'Failed system restoration attempt (invalid archive): violatap_full_system_backup_2026-09-09_19-39-38.zip', '::1', '2026-09-09 17:39:55'),
(12, 14, '', 'Viewed detailed profile for student ID: 2.', '::1', '2026-09-09 18:19:10'),
(13, 14, 'Student Updated', 'Successfully updated details for student ID: 2 (Juan Carlos Bautista).', '::1', '2026-09-09 18:19:17'),
(14, 14, '', 'Viewed detailed profile for student ID: 1.', '::1', '2026-09-09 18:19:21'),
(15, 14, 'Student Updated', 'Successfully updated details for student ID: 1 (Maria Elena Dela Cruz).', '::1', '2026-09-09 18:19:26'),
(16, 14, '', 'Viewed detailed profile for student ID: 6.', '::1', '2026-09-09 18:19:31'),
(17, 14, 'Student Updated', 'Successfully updated details for student ID: 6 (Christian Flores).', '::1', '2026-09-09 18:19:39'),
(18, 14, '', 'Viewed detailed profile for student ID: 7.', '::1', '2026-09-09 18:19:42'),
(19, 14, 'Student Updated', 'Successfully updated details for student ID: 7 (Bea Nicole Gonzales).', '::1', '2026-09-09 18:19:47'),
(20, 14, '', 'Viewed detailed profile for student ID: 3.', '::1', '2026-09-09 18:19:50'),
(21, 14, 'Student Updated', 'Successfully updated details for student ID: 3 (Analyn Mendoza).', '::1', '2026-09-09 18:19:54'),
(22, 14, '', 'Viewed detailed profile for student ID: 3.', '::1', '2026-09-09 18:19:58'),
(23, 14, 'Student Updated', 'Successfully updated details for student ID: 3 (Analyn Mendoza).', '::1', '2026-09-09 18:20:02'),
(24, 14, '', 'Viewed detailed profile for student ID: 5.', '::1', '2026-09-09 18:20:06'),
(25, 14, 'Student Updated', 'Successfully updated details for student ID: 5 (Jessa Mae Ramos).', '::1', '2026-09-09 18:20:10'),
(26, 14, '', 'Viewed detailed profile for student ID: 9.', '::1', '2026-09-09 18:20:14'),
(27, 14, 'Student Updated', 'Successfully updated details for student ID: 9 (Roselyn Soriano).', '::1', '2026-09-09 18:20:19'),
(28, 14, '', 'Viewed detailed profile for student ID: 8.', '::1', '2026-09-09 18:20:30'),
(29, 14, 'Student Updated', 'Successfully updated details for student ID: 8 (John Paul Tan).', '::1', '2026-09-09 18:20:39'),
(30, 14, '', 'Viewed detailed profile for student ID: 4.', '::1', '2026-09-09 18:20:42'),
(31, 14, 'Student Updated', 'Successfully updated details for student ID: 4 (Mark Anthony Torres).', '::1', '2026-09-09 18:21:09'),
(32, 14, '', 'Viewed detailed profile for student ID: 9.', '::1', '2026-09-09 18:23:54'),
(33, 14, 'Student Updated', 'Successfully updated details for student ID: 9 (Roselyn Soriano).', '::1', '2026-09-09 18:24:06'),
(34, 14, '', 'Viewed detailed profile for student ID: 2.', '::1', '2026-09-09 18:24:10'),
(35, 14, '', 'Viewed detailed profile for student ID: 14.', '::1', '2026-09-09 18:27:46'),
(36, 14, 'Student Updated', 'Successfully updated details for student ID: 14 (Christian Paul Ramos).', '::1', '2026-09-09 18:27:52'),
(37, 14, '', 'Viewed detailed profile for student ID: 15.', '::1', '2026-09-09 18:27:56'),
(38, 14, 'Student Updated', 'Successfully updated details for student ID: 15 (Rachelle Ann Aquino).', '::1', '2026-09-09 18:28:01'),
(39, 14, '', 'Viewed detailed profile for student ID: 11.', '::1', '2026-09-09 18:28:05'),
(40, 14, 'Student Updated', 'Successfully updated details for student ID: 11 (Krizia Mae Bautista).', '::1', '2026-09-09 18:28:11'),
(41, 14, '', 'Viewed detailed profile for student ID: 9.', '::1', '2026-09-09 18:28:26'),
(42, 14, 'Student Updated', 'Successfully updated details for student ID: 9 (Roselyn Soriano).', '::1', '2026-09-09 18:28:33'),
(43, 14, '', 'Viewed detailed profile for student ID: 13.', '::1', '2026-09-09 18:28:37'),
(44, 14, '', 'Viewed detailed profile for student ID: 13.', '::1', '2026-09-09 18:28:40'),
(45, 14, 'Student Updated', 'Successfully updated details for student ID: 13 (Princess Joy Torres).', '::1', '2026-09-09 18:28:50'),
(46, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-10 10:05:01'),
(47, 14, 'Database Backup Created', 'Comprehensive system backup (App package, user data, system preferences) compiled and downloaded.', '::1', '2026-09-10 10:12:51'),
(48, 14, 'Database Backup Created', 'Comprehensive system backup (App package, user data, system preferences) compiled and downloaded.', '::1', '2026-09-10 10:14:14'),
(49, 14, 'Database Restored', 'Database state successfully restored from file: violatap_database_backup_2026-09-10_12-17-55.sql', '::1', '2026-09-10 10:21:22'),
(50, 14, 'Database Restored', 'Database state successfully restored from file: violatap (12).sql', '::1', '2026-09-10 10:21:56'),
(51, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-10 16:42:53'),
(52, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-11 16:45:55'),
(53, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-12 11:27:17'),
(54, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '192.168.100.139', '2026-09-12 12:59:57'),
(55, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '127.0.0.1', '2026-09-12 15:34:47'),
(56, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '127.0.0.1', '2026-09-13 11:15:22'),
(57, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-14 04:33:53'),
(58, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '192.168.254.109', '2026-09-14 04:37:07'),
(59, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 08:42:13'),
(60, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 08:42:14'),
(61, 14, 'NFC Card Assigned', 'Successfully linked NFC card UID: 0b:cc:fc:4e to student ID: 1.', '::1', '2026-09-15 08:42:55'),
(62, 14, 'NFC Card Assigned', 'Successfully linked NFC card UID: 0b:cc:fc:4e to student ID: 2.', '::1', '2026-09-15 08:43:16'),
(63, 14, 'NFC Card Assigned', 'Successfully linked NFC card UID: 0b:cc:fc:4e to student ID: 1.', '::1', '2026-09-15 08:43:32'),
(64, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 12:18:29'),
(65, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 15:44:04'),
(66, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '192.168.254.100', '2026-09-15 16:59:14'),
(67, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '192.168.254.100', '2026-09-15 17:00:12'),
(68, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 17:05:06'),
(69, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved successfully.', '::1', '2026-09-15 17:06:49'),
(70, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 17:09:55'),
(71, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 17:23:29'),
(72, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved successfully.', '::1', '2026-09-15 17:50:51'),
(73, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 17:55:43'),
(74, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 18:14:46'),
(75, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 18:42:50'),
(76, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 19:06:26'),
(77, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 22:42:58'),
(78, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-15 23:18:14'),
(79, 16, 'Successful Login', 'User \'sgbautistaguard\' successfully logged in.', '::1', '2026-09-15 23:41:48'),
(80, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-16 00:19:31'),
(81, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-16 00:30:02'),
(82, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-16 00:35:59'),
(83, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-16 01:14:11'),
(84, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-16 01:27:10'),
(85, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-16 03:57:00'),
(86, 18, 'Violation Filed', 'Filed violation type ID: 9 (Offense #3) for student ID: 1. Record ID: 76.', '192.168.1.3', '2026-09-16 03:58:27'),
(87, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.4', '2026-09-16 04:00:16'),
(88, 18, 'Violation Filed', 'Filed violation type ID: 9 (Offense #4) for student ID: 1. Record ID: 77.', '192.168.1.4', '2026-09-16 04:01:15'),
(89, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-16 07:29:49'),
(90, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-16 07:38:15'),
(91, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-16 07:39:15'),
(92, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-16 07:41:39'),
(93, 14, 'Successful Logout', 'User \'superadmin\' logged out safely from the application.', '192.168.1.3', '2026-09-16 07:47:40'),
(94, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-16 07:48:48'),
(95, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-16 07:50:49'),
(96, 14, 'Successful Logout', 'User \'superadmin\' logged out safely from the application.', '192.168.1.3', '2026-09-16 07:57:32'),
(97, 19, 'Successful Login', 'User \'roblescsoadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-16 07:58:06'),
(98, 14, 'Student Added', 'Successfully added student: Mark Joseph Santos (ID: 2026-6442-M).', '::1', '2026-09-16 08:23:49'),
(99, 14, 'NFC Card Assigned', 'Successfully linked NFC card UID: db:be:fe:4e to student ID: 19.', '::1', '2026-09-16 08:27:06'),
(100, 18, 'Violation Filed', 'Filed violation type ID: 2 (Offense #1) for student ID: 19. Record ID: 78.', '192.168.1.3', '2026-09-16 08:36:03'),
(101, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '127.0.0.1', '2026-09-18 12:43:07'),
(102, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '127.0.0.1', '2026-09-18 12:55:50'),
(103, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '192.168.100.139', '2026-09-18 15:46:10'),
(104, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '127.0.0.1', '2026-09-18 18:18:00'),
(105, 14, 'Offense Archived', 'Offense ID 53 status changed to archived=1', '127.0.0.1', '2026-09-18 20:26:12'),
(106, 14, 'Card Disabled', 'Successfully disabled NFC card ID: 2.', '127.0.0.1', '2026-09-18 21:07:50'),
(107, 14, '', 'Viewed detailed profile for student ID: 19.', '127.0.0.1', '2026-09-18 21:09:37'),
(108, 14, '', 'Viewed detailed profile for student ID: 19.', '127.0.0.1', '2026-09-18 21:10:03'),
(109, 14, '', 'Viewed detailed profile for student ID: 19.', '127.0.0.1', '2026-09-18 21:10:07'),
(110, 14, '', 'Viewed detailed profile for student ID: 19.', '127.0.0.1', '2026-09-18 21:19:39'),
(111, 14, 'Student Added', 'Successfully added student: asda safda (ID: 2021-5619-M).', '127.0.0.1', '2026-09-18 21:21:50'),
(112, 14, 'Student Archived', 'Successfully archived student record ID: 20.', '127.0.0.1', '2026-09-18 21:22:24'),
(113, 14, '', 'Viewed detailed profile for student ID: 19.', '127.0.0.1', '2026-09-18 21:22:50'),
(114, 14, '', 'Viewed detailed profile for student ID: 19.', '127.0.0.1', '2026-09-18 21:22:51'),
(115, 14, '', 'Viewed detailed profile for student ID: 19.', '127.0.0.1', '2026-09-18 21:22:55'),
(116, 14, '', 'Viewed detailed profile for student ID: 20.', '127.0.0.1', '2026-09-18 21:23:27'),
(117, 14, '', 'Viewed detailed profile for student ID: 20.', '127.0.0.1', '2026-09-18 21:23:31'),
(118, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '127.0.0.1', '2026-09-19 05:48:21'),
(119, 14, 'Password Changed', 'User password successfully modified.', '127.0.0.1', '2026-09-19 06:06:58'),
(120, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '127.0.0.1', '2026-09-19 06:07:55'),
(121, 14, 'Password Changed', 'User password successfully modified.', '127.0.0.1', '2026-09-19 06:08:19'),
(122, 14, '', 'Updated account avatar image.', '127.0.0.1', '2026-09-19 06:15:46'),
(123, 14, 'Password Changed', 'User password successfully modified.', '127.0.0.1', '2026-09-19 06:56:44'),
(124, 14, 'Password Changed', 'User password successfully modified.', '127.0.0.1', '2026-09-19 06:57:04'),
(125, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '127.0.0.1', '2026-09-19 07:29:55'),
(126, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '127.0.0.1', '2026-09-19 07:47:56'),
(127, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '127.0.0.1', '2026-09-19 07:49:28'),
(128, 14, '', 'Updated account avatar image.', '127.0.0.1', '2026-09-19 08:12:20'),
(129, 14, '', 'Updated account avatar image.', '127.0.0.1', '2026-09-19 08:12:52'),
(130, 14, '', 'Deleted profile picture and restored to letter default.', '127.0.0.1', '2026-09-19 08:15:42'),
(131, 14, '', 'Updated account avatar image.', '127.0.0.1', '2026-09-19 08:17:01'),
(132, 14, '', 'Deleted profile picture and restored to letter default.', '127.0.0.1', '2026-09-19 08:31:03'),
(133, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-19 08:35:52'),
(134, 14, '', 'Updated account avatar image.', '::1', '2026-09-19 08:43:39'),
(135, 14, '', 'Deleted profile picture and restored to letter default.', '::1', '2026-09-19 08:43:54'),
(136, 14, '', 'Updated account avatar image.', '::1', '2026-09-19 08:44:14'),
(137, 14, 'Database Backup Created', 'Database data backup (.sql) compiled and downloaded.', '::1', '2026-09-19 09:15:02'),
(138, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-19 10:35:36'),
(139, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-19 11:51:10'),
(140, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:30:32'),
(141, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:31:35'),
(142, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:32:42'),
(143, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:32:44'),
(144, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-19 12:32:47'),
(145, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:32:53'),
(146, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:37:05'),
(147, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:37:20'),
(148, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:37:32'),
(149, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:39:48'),
(150, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:39:55'),
(151, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:39:57'),
(152, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-19 12:39:59'),
(153, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:40:01'),
(154, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:40:59'),
(155, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:41:29'),
(156, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:42:30'),
(157, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:42:58'),
(158, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:43:12'),
(159, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:43:40'),
(160, 14, 'Card Restored', 'Restored active status for NFC card ID: 2.', '::1', '2026-09-19 12:43:44'),
(161, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:43:47'),
(162, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:46:20'),
(163, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:47:33'),
(164, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:48:08'),
(165, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:48:34'),
(166, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:48:59'),
(167, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:49:03'),
(168, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-19 12:50:07'),
(169, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:50:54'),
(170, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-19 12:53:17'),
(171, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-19 12:54:00'),
(172, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:54:12'),
(173, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:54:25'),
(174, 14, 'Card Disabled', 'Disabled NFC card record ID: 2.', '::1', '2026-09-19 12:54:32'),
(175, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:54:43'),
(176, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:54:55'),
(177, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:55:00'),
(178, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:55:03'),
(179, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 12:56:41'),
(180, 14, 'Card Restored', 'Restored active status for NFC card ID: 2.', '::1', '2026-09-19 12:56:43'),
(181, 14, '', 'Updated account avatar image.', '::1', '2026-09-19 13:04:15'),
(182, 14, '', 'Updated account avatar image.', '::1', '2026-09-19 13:04:30'),
(183, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:13:13'),
(184, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:16:12'),
(185, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:18:10'),
(186, 14, 'Student Updated', 'Updated student details for ID: 19 (Mark Joseph Delos Santos).', '::1', '2026-09-19 13:18:48'),
(187, 14, 'NFC Card Assigned', 'Linked NFC Card UID: db:be:fe:4e to student UID: 19.', '::1', '2026-09-19 13:18:48'),
(188, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:19:04'),
(189, 14, 'Card Disabled', 'Disabled NFC card record ID: 2.', '::1', '2026-09-19 13:19:10'),
(190, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:19:15'),
(191, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-19 13:19:19'),
(192, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:19:32'),
(193, 14, 'Student Archived', 'Archived student record ID: 19.', '::1', '2026-09-19 13:19:39'),
(194, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:19:45'),
(195, 14, 'Student Restored', 'Restored student record ID: 19 from archive.', '::1', '2026-09-19 13:19:54'),
(196, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:19:57'),
(197, 14, 'Card Restored', 'Restored active status for NFC card ID: 2.', '::1', '2026-09-19 13:19:59'),
(198, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:25:11'),
(199, 14, 'Card Disabled', 'Disabled NFC card record ID: 2.', '::1', '2026-09-19 13:25:18'),
(200, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-19 13:25:29'),
(201, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:25:36'),
(202, 14, 'Student Archived', 'Archived student record ID: 19.', '::1', '2026-09-19 13:25:43'),
(203, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:25:59'),
(204, 14, 'Student Restored', 'Restored student record ID: 19 from archive.', '::1', '2026-09-19 13:26:23'),
(205, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:26:43'),
(206, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:27:20'),
(207, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:32:36'),
(208, 14, 'Student Archived', 'Archived student record ID: 19.', '::1', '2026-09-19 13:32:45'),
(209, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:32:53'),
(210, 14, 'Student Restored', 'Restored student record ID: 19 from archive.', '::1', '2026-09-19 13:33:10'),
(211, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:33:14'),
(212, 14, 'Card Restored', 'Restored active status for NFC card ID: 2.', '::1', '2026-09-19 13:33:20'),
(213, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:35:41'),
(214, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:35:59'),
(215, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:38:00'),
(216, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:40:17'),
(217, 14, 'Student Updated', 'Updated student details for ID: 19 (Mark Joseph Santos).', '::1', '2026-09-19 13:40:33'),
(218, 14, 'NFC Card Assigned', 'Linked NFC Card UID: db:be:fe:4e to student UID: 19.', '::1', '2026-09-19 13:40:33'),
(219, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:40:40'),
(220, 14, 'Card Disabled', 'Disabled NFC card record ID: 2.', '::1', '2026-09-19 13:40:46'),
(221, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:40:49'),
(222, 14, 'Card Restored', 'Restored active status for NFC card ID: 2.', '::1', '2026-09-19 13:40:52'),
(223, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:40:54'),
(224, 14, 'Student Archived', 'Archived student record ID: 19.', '::1', '2026-09-19 13:41:01'),
(225, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:41:04'),
(226, 14, 'Student Restored', 'Restored student record ID: 19 from archive.', '::1', '2026-09-19 13:41:07'),
(227, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:45:06'),
(228, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:45:26'),
(229, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-19 13:45:30'),
(230, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 13:53:00'),
(231, 14, 'Password Reset', 'Reset password credentials for user ID #18 (garciacodadmin).', '::1', '2026-09-19 15:14:13'),
(232, 14, 'User Archived', 'Archived account for user ID #15 (sgdelacruzguard).', '::1', '2026-09-19 15:21:05'),
(233, 14, 'User Restored', 'Restored archived account for user ID #15 (sgdelacruzguard).', '::1', '2026-09-19 15:21:17'),
(234, 14, 'User Archived', 'Archived account for user ID #15 (sgdelacruzguard).', '::1', '2026-09-19 15:25:34'),
(235, 14, 'User Restored', 'Restored archived account for user ID #15 (sgdelacruzguard).', '::1', '2026-09-19 15:25:50'),
(236, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-19 15:26:16'),
(237, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 15:26:19'),
(238, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-19 16:26:19'),
(239, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-19 18:06:39'),
(240, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-19 18:28:51'),
(241, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-19 18:30:45'),
(242, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 18:33:02'),
(243, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-19 18:45:10'),
(244, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-19 19:05:08'),
(245, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-19 19:05:24'),
(246, 14, 'Violation Filed', 'Filed violation type ID: 9 (Offense #1) for student UID: 19. Record ID: 79.', '192.168.1.3', '2026-09-19 19:18:46'),
(247, 14, 'Password Changed', 'User successfully updated their account password.', '192.168.1.3', '2026-09-19 19:25:40'),
(248, 14, 'Successful Logout', 'User \'superadmin\' logged out safely from the application.', '192.168.1.3', '2026-09-19 19:25:44'),
(249, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-19 19:25:56'),
(250, 14, 'Violation Filed', 'Filed violation type ID: 2 (Offense #2) for student UID: 19. Record ID: 80.', '192.168.1.3', '2026-09-19 19:26:32'),
(251, 18, 'Failed Login Attempt', 'Attempted mobile login username: \'superadmin\'', '192.168.1.3', '2026-09-19 21:05:08'),
(252, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-19 21:05:36'),
(253, 14, 'Password Changed', 'User successfully updated their account password.', '192.168.1.3', '2026-09-19 21:05:57'),
(254, 14, 'Successful Logout', 'User \'superadmin\' logged out safely from the application.', '192.168.1.3', '2026-09-19 21:08:45'),
(255, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-20 13:10:34'),
(256, 14, 'Violation Verified', 'Verified violation record ID #79. Status updated to Unsettled.', '::1', '2026-09-20 13:11:27'),
(257, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-20 14:00:57'),
(258, 14, 'Violation Filed', 'Filed violation type ID: 1 (Offense #1) for student UID: 19. Record ID: 81.', '192.168.1.3', '2026-09-20 14:02:29'),
(259, 14, 'Successful Logout', 'User \'superadmin\' logged out safely from the application.', '192.168.1.3', '2026-09-20 14:02:40'),
(260, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-20 14:04:31'),
(261, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-20 14:33:27'),
(262, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-20 14:37:55'),
(263, 14, 'Violation Filed', 'Filed violation type ID: 4 (Offense #1) for student UID: 19. Record ID: 82.', '192.168.1.3', '2026-09-20 14:51:31'),
(264, 14, 'Successful Logout', 'User \'superadmin\' logged out safely from the application.', '192.168.1.3', '2026-09-20 14:53:09'),
(265, 14, 'Successful Login', 'User \'superadmin\' successfully logged in via mobile app.', '192.168.1.3', '2026-09-20 14:53:23'),
(266, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-22 10:29:36'),
(267, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 11:24:23'),
(268, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 11:24:32'),
(269, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 11:24:33'),
(270, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-22 11:33:22'),
(271, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 12:10:37'),
(272, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 12:17:04'),
(273, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 12:19:50'),
(274, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 12:20:20'),
(275, 14, 'Card Disabled', 'Disabled NFC card record ID: 2.', '::1', '2026-09-22 12:22:54'),
(276, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 12:23:00'),
(277, 14, 'Card Restored', 'Restored active status for NFC card ID: 2.', '::1', '2026-09-22 12:23:04'),
(278, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 12:23:06'),
(279, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-22 12:25:44'),
(280, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-23 14:28:23'),
(281, 18, 'Successful Login', 'User \'garciacodadmin\' successfully logged in.', '::1', '2026-09-23 14:31:13'),
(282, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-23 14:51:43'),
(283, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-23 14:52:39'),
(284, 16, 'Successful Login', 'User \'sgbautistaguard\' successfully logged in.', '::1', '2026-09-23 14:52:50'),
(285, 16, 'Successful Login', 'User \'sgbautistaguard\' successfully logged in.', '::1', '2026-09-23 14:56:39'),
(286, 16, 'Successful Login', 'User \'sgbautistaguard\' successfully logged in.', '::1', '2026-09-23 14:59:15'),
(287, 16, 'Successful Login', 'User \'sgbautistaguard\' successfully logged in.', '::1', '2026-09-23 15:03:47'),
(288, 19, 'Successful Login', 'User \'roblescsoadmin\' successfully logged in.', '::1', '2026-09-23 15:04:42'),
(289, 18, 'Successful Login', 'User \'garciacodadmin\' successfully logged in.', '::1', '2026-09-23 15:08:20'),
(290, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-23 16:59:31'),
(291, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-23 17:07:45'),
(292, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-23 17:17:25'),
(293, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-23 17:17:39'),
(294, 14, 'Card Disabled', 'Disabled NFC card record ID: 2.', '::1', '2026-09-23 17:17:46'),
(295, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-23 17:17:47'),
(296, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-23 18:18:53'),
(297, 14, '', 'Deleted profile picture and restored to letter default.', '::1', '2026-09-23 19:01:21'),
(298, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-23 19:04:20'),
(299, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-25 06:07:05'),
(300, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-25 06:08:19'),
(301, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-25 06:08:24'),
(302, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-25 06:08:39'),
(303, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-25 06:08:42'),
(304, 14, '', 'Viewed detailed operations profile for student ID: 20.', '::1', '2026-09-25 06:09:06'),
(305, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-25 06:47:14'),
(306, 19, 'Successful Login', 'User \'roblescsoadmin\' successfully logged in.', '::1', '2026-09-25 08:08:06'),
(307, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-25 14:12:05'),
(308, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-25 14:12:31'),
(309, 19, 'Successful Login', 'User \'roblescsoadmin\' successfully logged in.', '::1', '2026-09-25 14:15:40'),
(310, 14, 'Successful Login', 'User \'superadmin\' successfully logged in.', '::1', '2026-09-25 14:39:06'),
(311, 19, 'Successful Login', 'User \'roblescsoadmin\' successfully logged in.', '::1', '2026-09-25 14:39:50'),
(312, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-25 16:34:28'),
(313, 14, 'Successful Login', 'User &#039;superadmin&#039; successfully logged in.', '::1', '2026-09-25 16:51:14'),
(314, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-25 16:51:34'),
(315, 19, '', 'User &#039;roblescsoadmin&#039; successfully logged out.', '::1', '2026-09-25 17:32:02'),
(316, 14, 'Successful Login', 'User &#039;superadmin&#039; successfully logged in.', '::1', '2026-09-25 17:32:13'),
(317, 14, '', 'User &#039;superadmin&#039; successfully logged out.', '::1', '2026-09-25 17:57:51'),
(318, 14, 'Successful Login', 'User &#039;superadmin&#039; successfully logged in.', '::1', '2026-09-25 17:57:59'),
(319, 14, 'Successful Login', 'User &#039;superadmin&#039; successfully logged in.', '::1', '2026-09-25 17:59:24'),
(320, 14, '', 'Successfully imported 21 student records via CSV upload.', '::1', '2026-09-25 18:09:55'),
(321, 14, '', 'Successfully imported 20 student records via CSV upload.', '::1', '2026-09-25 18:23:42'),
(322, 14, '', 'Successfully imported 20 student records via CSV upload.', '::1', '2026-09-25 18:30:23'),
(323, 14, 'Student Added', 'Added new student: sas asfa (Student ID: 23456).', '::1', '2026-09-25 18:38:41'),
(324, 14, '', 'Viewed detailed operations profile for student ID: 21.', '::1', '2026-09-25 18:39:04'),
(325, 14, '', 'Successfully imported 18 student records via CSV upload.', '::1', '2026-09-25 18:40:35'),
(326, 14, '', 'Successfully imported 19 student records via CSV upload.', '::1', '2026-09-25 18:43:55'),
(327, 14, '', 'Successfully imported 18 student records via CSV upload.', '::1', '2026-09-25 18:59:46'),
(328, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-25 19:00:20'),
(329, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-25 19:00:23'),
(330, 14, '', 'Viewed detailed operations profile for student ID: 74.', '::1', '2026-09-25 19:00:26'),
(331, 14, '', 'Successfully imported 18 student records via CSV upload.', '::1', '2026-09-25 19:09:27'),
(332, 14, '', 'Viewed detailed operations profile for student ID: 85.', '::1', '2026-09-25 19:09:36'),
(333, 14, '', 'Viewed detailed operations profile for student ID: 87.', '::1', '2026-09-25 19:09:50'),
(334, 14, '', 'Viewed detailed operations profile for student ID: 95.', '::1', '2026-09-25 19:09:59'),
(335, 14, '', 'User &#039;superadmin&#039; successfully logged out.', '::1', '2026-09-25 19:31:16'),
(336, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-25 19:31:35'),
(337, 19, '', 'Viewed detailed operations profile for student ID: 85.', '::1', '2026-09-25 19:32:12'),
(338, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-25 19:59:28'),
(339, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-25 19:59:54'),
(340, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-25 20:00:02'),
(341, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-25 20:03:23'),
(342, 19, '', 'Viewed detailed operations profile for student ID: 87.', '::1', '2026-09-25 20:03:32'),
(343, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-25 20:03:41'),
(344, 14, 'Student Added', 'Added new student: qwe zxcv (Student ID: 1235).', '::1', '2026-09-25 20:18:26'),
(345, 14, '', 'Viewed detailed operations profile for student ID: 98.', '::1', '2026-09-25 20:28:32'),
(346, 14, '', 'Viewed detailed operations profile for student ID: 92.', '::1', '2026-09-25 20:28:34'),
(347, 14, '', 'Viewed detailed operations profile for student ID: 98.', '::1', '2026-09-25 20:28:37'),
(348, 14, '', 'Viewed detailed operations profile for student ID: 85.', '::1', '2026-09-25 20:29:03'),
(349, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-25 20:29:32'),
(350, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-25 20:29:37'),
(351, 14, 'Successful Login', 'User &#039;superadmin&#039; successfully logged in.', '::1', '2026-09-25 20:32:45'),
(352, 14, '', 'Viewed detailed operations profile for student ID: 85.', '::1', '2026-09-25 20:34:45'),
(353, 14, '', 'User &#039;superadmin&#039; successfully logged out.', '::1', '2026-09-25 21:36:06'),
(354, 14, 'Successful Login', 'User &#039;superadmin&#039; successfully logged in.', '::1', '2026-09-25 21:36:49'),
(355, 19, '', 'User &#039;roblescsoadmin&#039; successfully logged out.', '::1', '2026-09-25 22:16:21'),
(356, 14, 'Password Reset', 'Reset password credentials for user ID #18 (garciacodadmin).', '::1', '2026-09-25 22:16:31'),
(357, 18, 'Successful Login', 'User &#039;garciacodadmin&#039; successfully logged in.', '::1', '2026-09-25 22:16:45'),
(358, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-25 22:17:49'),
(359, 18, '', 'User &#039;garciacodadmin&#039; successfully logged out.', '::1', '2026-09-25 22:18:39'),
(360, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-25 22:18:58'),
(361, 19, '', 'User &#039;roblescsoadmin&#039; successfully logged out.', '::1', '2026-09-25 22:27:24'),
(362, 14, 'Password Reset', 'Reset password credentials for user ID #18 (garciacodadmin).', '::1', '2026-09-25 22:27:52'),
(363, 18, 'Successful Login', 'User &#039;garciacodadmin&#039; successfully logged in.', '::1', '2026-09-25 22:27:59'),
(364, 18, '', 'User &#039;garciacodadmin&#039; successfully logged out.', '::1', '2026-09-25 22:28:27'),
(365, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-25 22:28:38'),
(366, 14, 'RBAC Defaults Restored', 'RBAC settings successfully restored to default policies.', '::1', '2026-09-25 22:33:54'),
(367, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-25 22:34:30'),
(368, 14, 'Violation Verified', 'Verified violation record ID #82. Status updated to Unsettled.', '::1', '2026-09-25 22:37:50'),
(369, 19, 'Violation Verified', 'Verified violation record ID #81. Status updated to Unsettled.', '::1', '2026-09-25 22:38:09'),
(370, 14, 'Violation Verified', 'Verified violation record ID #82. Status updated to Unsettled.', '::1', '2026-09-25 22:40:23'),
(371, 14, '', 'Settled case for violation record ID #82. Cleared at timestamp updated.', '::1', '2026-09-25 23:25:29'),
(372, 14, 'Violation Verified', 'Verified violation record ID #82. Status updated to Unsettled.', '::1', '2026-09-25 23:26:56'),
(373, 14, '', 'Rolled back violation record ID #82 state to For Verification.', '::1', '2026-09-25 23:26:59'),
(374, 14, 'Violation Verified', 'Verified violation record ID #82. Status updated to Unsettled.', '::1', '2026-09-25 23:30:02'),
(375, 14, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-25 23:45:56'),
(376, 14, 'Card Restored', 'Restored active status for NFC card ID: 2.', '::1', '2026-09-25 23:45:59'),
(377, 14, '', 'Viewed detailed operations profile for student ID: 86.', '::1', '2026-09-26 00:09:30'),
(378, 14, '', 'Settled case for violation record ID #82. Cleared at timestamp updated.', '::1', '2026-09-26 00:09:43'),
(379, 14, '', 'Viewed detailed operations profile for student ID: 85.', '::1', '2026-09-26 00:11:01'),
(380, 19, '', 'User &#039;roblescsoadmin&#039; successfully logged out.', '::1', '2026-09-26 00:23:16'),
(381, 18, 'Successful Login', 'User &#039;garciacodadmin&#039; successfully logged in.', '::1', '2026-09-26 00:23:34'),
(382, 18, '', 'User &#039;garciacodadmin&#039; successfully logged out.', '::1', '2026-09-26 00:41:09'),
(383, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-26 00:41:42'),
(384, 18, 'Successful Login', 'User &#039;garciacodadmin&#039; successfully logged in.', '::1', '2026-09-26 00:56:03'),
(385, 14, '', 'Viewed detailed operations profile for student ID: 83.', '::1', '2026-09-26 01:01:18'),
(386, 14, 'Violation Verified', 'Verified violation record ID #80. Status updated to Unsettled.', '::1', '2026-09-26 01:01:29'),
(387, 14, 'Successful Login', 'User &#039;superadmin&#039; successfully logged in.', '::1', '2026-09-26 01:20:18'),
(388, 14, 'Violation Verified', 'Verified violation record ID #82. Status updated to Unsettled.', '::1', '2026-09-26 01:20:51'),
(389, 14, '', 'User &#039;superadmin&#039; successfully logged out.', '::1', '2026-09-26 01:23:08'),
(390, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-26 01:24:47'),
(391, 19, 'Violation Verified', 'Verified violation record ID #81. Status updated to Unsettled.', '::1', '2026-09-26 01:25:26'),
(392, 19, '', 'User &#039;roblescsoadmin&#039; successfully logged out.', '::1', '2026-09-26 01:27:18'),
(393, 18, 'Successful Login', 'User &#039;garciacodadmin&#039; successfully logged in.', '::1', '2026-09-26 01:27:41'),
(394, 18, '', 'Settled case for violation record ID #79. Cleared at timestamp updated.', '::1', '2026-09-26 01:27:59'),
(395, 18, 'Offense Updated', 'Updated offense ID #1: Loitering and Noise1', '::1', '2026-09-26 01:28:18'),
(396, 18, 'Offense Updated', 'Updated offense ID #1: Loitering and Noise', '::1', '2026-09-26 01:28:27'),
(397, 18, 'Offense Restored', 'Restored offense record ID #53 from archive', '::1', '2026-09-26 01:28:47'),
(398, 18, 'Offense Archived', 'Archived offense record ID #53', '::1', '2026-09-26 01:28:59'),
(399, 18, '', 'Viewed detailed operations profile for student ID: 19.', '::1', '2026-09-26 01:29:11'),
(400, 18, 'Card Disabled', 'Disabled NFC card record ID: 2.', '::1', '2026-09-26 01:29:15'),
(401, 14, 'Successful Login', 'User &#039;superadmin&#039; successfully logged in.', '::1', '2026-09-26 01:39:57'),
(402, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-26 01:40:55'),
(403, 14, '', 'User &#039;superadmin&#039; successfully logged out.', '::1', '2026-09-26 02:10:22'),
(404, 14, 'Successful Login', 'User &#039;superadmin&#039; successfully logged in.', '::1', '2026-09-26 02:10:36'),
(405, 18, '', 'User &#039;garciacodadmin&#039; successfully logged out.', '::1', '2026-09-26 02:16:11'),
(406, 18, 'Successful Login', 'User &#039;garciacodadmin&#039; successfully logged in.', '::1', '2026-09-26 02:16:33'),
(407, 18, '', 'User &#039;garciacodadmin&#039; successfully logged out.', '::1', '2026-09-26 02:17:39'),
(408, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-26 02:18:21'),
(409, 18, 'Successful Login', 'User &#039;garciacodadmin&#039; successfully logged in.', '::1', '2026-09-26 02:22:36'),
(410, 19, '', 'User &#039;roblescsoadmin&#039; successfully logged out.', '::1', '2026-09-26 02:25:28'),
(411, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-26 02:25:45'),
(412, 19, 'Successful Login', 'User &#039;roblescsoadmin&#039; successfully logged in.', '::1', '2026-09-26 02:28:57'),
(413, 19, '', 'User &#039;roblescsoadmin&#039; successfully logged out.', '::1', '2026-09-26 02:29:08'),
(414, 18, 'Successful Login', 'User &#039;garciacodadmin&#039; successfully logged in.', '::1', '2026-09-26 02:37:01'),
(415, 14, 'RBAC Defaults Restored', 'RBAC settings successfully restored to default policies.', '::1', '2026-09-26 02:38:09'),
(416, 14, 'RBAC Policies Updated', 'Role-based access control policies updated and saved.', '::1', '2026-09-26 02:38:58'),
(417, 14, 'RBAC Defaults Restored', 'RBAC settings successfully restored to default policies.', '::1', '2026-09-26 02:40:19'),
(418, 18, 'Offense Updated', 'Updated offense ID #1: Loitering and Noise1', '::1', '2026-09-26 02:48:21'),
(419, 14, 'Offense Updated', 'Updated offense ID #1: Loitering and Noise', '::1', '2026-09-26 02:49:12'),
(420, 19, 'Password Reset', 'Reset password credentials for user ID #16 (sgbautistaguard).', '::1', '2026-09-26 03:17:56'),
(421, 19, 'Password Reset', 'Reset password credentials for user ID #16 (sgbautistaguard).', '::1', '2026-09-26 03:29:44'),
(422, 18, 'Password Reset', 'Reset password credentials for user ID #19 (roblescsoadmin).', '::1', '2026-09-26 03:29:50'),
(423, 18, 'Failed Login Attempt', 'Attempted mobile login username: &#039;sgbautistaguard&#039;', '192.168.1.3', '2026-09-26 03:35:04'),
(424, 19, 'Password Reset', 'Reset password credentials for user ID #16 (sgbautistaguard).', '::1', '2026-09-26 03:35:21'),
(425, 16, 'Successful Login', 'User &#039;sgbautistaguard&#039; successfully logged in via mobile app.', '192.168.1.3', '2026-09-26 03:35:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` smallint(5) UNSIGNED NOT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `last_name` varchar(20) DEFAULT NULL,
  `school_id_no` char(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(60) NOT NULL,
  `role` enum('Guard','COD','CSO','Superadmin') NOT NULL DEFAULT 'Guard',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `school_id_no`, `username`, `password`, `role`, `is_archived`, `created_at`) VALUES
(14, '', 'SysAdmin', '', 'superadmin', '$2y$10$6Le3XpZ/aWqJyz0T6.LdmetKZxA1CxmS/kJLdA.j9XbJg8qVvW61u', 'Superadmin', 0, '2026-03-20 23:27:52'),
(15, 'Juanito', 'SG Dela Cruz', '2021-8469-M', 'sgdelacruzguard', '$2y$10$ikZKphHYuS1UaTgyBCUvMuj/ahfwtkX1MM0UWBI27FhhbVI9TAsUK', 'Guard', 0, '2026-09-09 17:56:29'),
(16, 'Mayumi', 'SG Bautista', '2020-0646-M', 'sgbautistaguard', '$2y$10$Jvwe18.8zlVdzN20Kyo1ieJ0CQt7hSEquvzCsVfOKiU2gKHr1RcGm', 'Guard', 0, '2026-09-09 17:59:56'),
(18, 'Francisco Javier', 'Garcia', '2020-7895-M', 'garciacodadmin', '$2y$10$mdAT1FLHKJxTrD28L/hM7u6CmEf2MEIHBtCMrGVS19jlOGm6KqAum', 'COD', 0, '2026-09-09 18:08:50'),
(19, 'Dyck Andre', 'Robles', '170601-124-', 'roblescsoadmin', '$2y$10$FqflAFmsX2DU8CAp9vBcouO3VvfP/HAAec5e2AdJ.QKxZw6woC9m2', 'CSO', 0, '2026-09-16 07:53:57');

-- --------------------------------------------------------

--
-- Table structure for table `violation_records`
--

CREATE TABLE `violation_records` (
  `record_id` smallint(5) UNSIGNED NOT NULL,
  `student_uid` smallint(5) UNSIGNED NOT NULL,
  `offense_id` tinyint(3) UNSIGNED NOT NULL,
  `offense_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `applied_sanction` text DEFAULT NULL,
  `user_id` smallint(5) UNSIGNED NOT NULL,
  `incident_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `status` enum('Unsettled','Settled','For Verification') NOT NULL DEFAULT 'For Verification',
  `cleared_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `violation_records`
--

INSERT INTO `violation_records` (`record_id`, `student_uid`, `offense_id`, `offense_count`, `applied_sanction`, `user_id`, `incident_date`, `remarks`, `status`, `cleared_at`) VALUES
(78, 19, 2, 1, 'Warning followed by counseling', 18, '2026-09-16 08:36:03', 'mugo palda', 'Settled', '2026-09-16 08:53:09'),
(79, 19, 9, 1, 'Warning followed by counseling', 14, '2026-09-19 19:18:46', '', 'Settled', '2026-09-26 01:27:59'),
(80, 19, 2, 2, 'Prohibition from entering University premises', 14, '2026-09-19 19:26:32', '', 'Unsettled', NULL),
(81, 19, 1, 1, 'Warning and refer for counseling', 14, '2026-09-20 14:02:29', 'jsjdbsb', 'Unsettled', NULL),
(82, 19, 4, 1, 'Warning followed by counseling', 14, '2026-09-20 14:51:31', '', 'Unsettled', '2026-09-26 00:09:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `code_of_discipline`
--
ALTER TABLE `code_of_discipline`
  ADD PRIMARY KEY (`offense_id`);

--
-- Indexes for table `nfc_cards`
--
ALTER TABLE `nfc_cards`
  ADD PRIMARY KEY (`card_id`),
  ADD UNIQUE KEY `nfc_uid` (`nfc_uid`),
  ADD KEY `student_uid` (`student_uid`),
  ADD KEY `idx_nfc_handshake` (`nfc_uid`,`is_active`);

--
-- Indexes for table `rbac_permissions`
--
ALTER TABLE `rbac_permissions`
  ADD PRIMARY KEY (`role`,`module_key`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_uid`),
  ADD UNIQUE KEY `school_id_no` (`student_id_no`);

--
-- Indexes for table `system_audit_logs`
--
ALTER TABLE `system_audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_audit_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `school_id_no` (`school_id_no`),
  ADD KEY `last_name` (`last_name`);

--
-- Indexes for table `violation_records`
--
ALTER TABLE `violation_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `type_id` (`offense_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_unsettled_check` (`student_uid`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `code_of_discipline`
--
ALTER TABLE `code_of_discipline`
  MODIFY `offense_id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `nfc_cards`
--
ALTER TABLE `nfc_cards`
  MODIFY `card_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_uid` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `system_audit_logs`
--
ALTER TABLE `system_audit_logs`
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=426;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `violation_records`
--
ALTER TABLE `violation_records`
  MODIFY `record_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
