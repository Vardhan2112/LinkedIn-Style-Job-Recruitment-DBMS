-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2024 at 05:19 PM
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
-- Database: `jrs`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `notify_company_of_application` (IN `p_application_id` INT)   BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_company_id INT;
    DECLARE cur CURSOR FOR 
        SELECT c.company_id
        FROM application a
        JOIN job_posting jp ON a.job_id = jp.job_id
        JOIN company c ON jp.company_id = c.company_id
        WHERE a.application_id = p_application_id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    loop_notify: LOOP
        FETCH cur INTO v_company_id;
        IF done THEN
            LEAVE loop_notify;
        END IF;

        -- Insert notification logic
        INSERT INTO notification_log (application_id, company_id, notification_time)
        VALUES (p_application_id, v_company_id, NOW());
    END LOOP;

    CLOSE cur;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `user_with_most_experience` () RETURNS LONGTEXT CHARSET utf8mb4 COLLATE utf8mb4_bin DETERMINISTIC BEGIN
    DECLARE result JSON;

    SELECT JSON_OBJECT(
        'username', u.name,
        'total_experience_days', exp_summary.total_experience,
        'experience_title', exp_summary.experience_title,
        'company_name', exp_summary.company_name
    ) INTO result
    FROM (
        SELECT e.profile_id, 
               SUM(DATEDIFF(e.end_date, e.start_date)) AS total_experience,
               GROUP_CONCAT(DISTINCT e.title SEPARATOR ', ') AS experience_title,
               GROUP_CONCAT(DISTINCT e.company_name SEPARATOR ', ') AS company_name
        FROM experience e
        GROUP BY e.profile_id
        ORDER BY total_experience DESC
        LIMIT 1
    ) AS exp_summary
    JOIN users u ON u.profile_id = exp_summary.profile_id;

    RETURN result;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `action_log`
--

CREATE TABLE `action_log` (
  `log_id` int(11) NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `action_type` enum('INSERT','UPDATE','DELETE') DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `action_log`
--

INSERT INTO `action_log` (`log_id`, `action_time`, `action_type`, `user_id`) VALUES
(12, '2024-11-19 10:38:02', 'INSERT', 9),
(13, '2024-11-19 10:41:08', 'INSERT', 10),
(15, '2024-11-19 11:09:04', 'UPDATE', 9);

--
-- Triggers `action_log`
--
DELIMITER $$
CREATE TRIGGER `log_user_delete` AFTER DELETE ON `action_log` FOR EACH ROW BEGIN
    INSERT INTO action_log 
    (action_time, action_type, user_id)
    VALUES 
    (NOW(), 'DELETE', OLD.user_id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `application`
--

CREATE TABLE `application` (
  `application_id` int(11) NOT NULL,
  `resume_url` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `apply_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application`
--

INSERT INTO `application` (`application_id`, `resume_url`, `user_id`, `job_id`, `apply_date`, `status`) VALUES
(1, 'resume_john.pdf', 1, 1, '2024-11-14', 'Pending'),
(2, 'resume_jane.pdf', 2, 2, '2024-11-15', 'Shortlisted'),
(5, 'resume_mike.pdf', 4, 6, '2024-11-18', 'Under Review'),
(6, 'resume_sarah.pdf', 5, 7, '2024-11-17', 'Shortlisted'),
(7, 'resume_james.pdf', 6, 8, '2024-11-16', 'Pending'),
(8, 'resume_lisa.pdf', 7, 9, '2024-11-15', 'Interview Scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `industry` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`company_id`, `company_name`, `location`, `industry`) VALUES
(1, 'TechCorp', 'New York', 'Technology'),
(2, 'Data Inc.', 'San Francisco', 'Data Analytics'),
(4, 'Innovate Solutions', 'Boston', 'Software Development'),
(5, 'HealthTech Inc', 'Chicago', 'Healthcare Technology'),
(6, 'FinServ Corp', 'Miami', 'Financial Services'),
(7, 'Green Energy Ltd', 'Seattle', 'Renewable Energy'),
(8, 'AI Systems', 'Austin', 'Artificial Intelligence');

-- --------------------------------------------------------

--
-- Table structure for table `comp_job`
--

CREATE TABLE `comp_job` (
  `job_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comp_job`
--

INSERT INTO `comp_job` (`job_id`, `company_id`) VALUES
(1, 2),
(2, 2),
(6, 4),
(7, 5),
(8, 6),
(9, 7),
(10, 8);

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `profile_id` int(11) NOT NULL,
  `institution` varchar(100) DEFAULT NULL,
  `degree` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`profile_id`, `institution`, `degree`, `start_date`, `end_date`) VALUES
(101, 'MIT', 'BSc Computer Science', '2008-09-01', '2012-06-15'),
(102, 'Stanford', 'MSc Data Science', '2015-09-01', '2017-06-15'),
(103, 'University of Washington', 'B.Sc. in Software Engineering', '2006-09-01', '2010-05-30'),
(103, 'Georgia Tech', 'M.Sc. in Computer Science', '2010-09-01', '2012-05-30'),
(104, 'UCLA', 'B.Sc. in Data Science', '2010-09-01', '2014-05-30'),
(104, 'Northwestern University', 'M.Sc. in Health Informatics', '2014-09-01', '2016-05-30'),
(105, 'Boston University', 'B.Sc. in Computer Science', '2008-09-01', '2012-05-30'),
(105, 'NYU', 'MBA in Financial Technology', '2012-09-01', '2014-05-30'),
(106, 'UC Berkeley', 'B.Sc. in Environmental Science', '2011-09-01', '2015-05-30'),
(106, 'University of Michigan', 'M.Sc. in Environmental Engineering', '2015-09-01', '2017-05-30'),
(107, 'MIT', 'B.Sc. in Computer Science', '2009-09-01', '2013-05-30'),
(107, 'Stanford University', 'Ph.D. in Artificial Intelligence', '2013-09-01', '2017-05-30');

-- --------------------------------------------------------

--
-- Table structure for table `experience`
--

CREATE TABLE `experience` (
  `profile_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `company_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `experience`
--

INSERT INTO `experience` (`profile_id`, `title`, `company_name`, `description`, `start_date`, `end_date`) VALUES
(101, 'Software Engineer', 'TechCorp', 'Developed backend APIs.', '2018-01-01', '2023-01-01'),
(102, 'Data Scientist', 'DataAnalytics Inc.', 'Built predictive models.', '2019-03-01', '2023-11-01'),
(103, 'Senior Cloud Engineer', 'Amazon AWS', 'Led cloud infrastructure projects', '2017-06-01', '2024-11-19'),
(103, 'Software Engineer', 'Microsoft', 'Developed Azure cloud solutions', '2012-06-01', '2017-05-30'),
(104, 'Data Analyst', 'Blue Cross', 'Healthcare insurance data analysis', '2016-06-01', '2018-12-31'),
(104, 'Healthcare Data Scientist', 'Mayo Clinic', 'Analyzed patient outcomes data', '2019-01-01', '2024-11-19'),
(105, 'FinTech Lead Developer', 'Goldman Sachs', 'Led trading platform development', '2018-01-01', '2024-11-19'),
(105, 'Financial Software Engineer', 'JP Morgan', 'Developed trading algorithms', '2014-06-01', '2017-12-31'),
(106, 'Environmental Engineer', 'Solar City', 'Designed solar energy solutions', '2017-06-01', '2019-12-31'),
(106, 'Senior Energy Consultant', 'Tesla', 'Led renewable energy projects', '2020-01-01', '2024-11-19'),
(107, 'AI Research Lead', 'Google DeepMind', 'Led AI research projects', '2020-06-01', '2024-11-19'),
(107, 'Machine Learning Engineer', 'OpenAI', 'Developed ML models', '2017-06-01', '2020-05-30');

-- --------------------------------------------------------

--
-- Table structure for table `job_posting`
--

CREATE TABLE `job_posting` (
  `job_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `min_salary` int(11) DEFAULT NULL,
  `max_salary` int(11) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_posting`
--

INSERT INTO `job_posting` (`job_id`, `company_id`, `title`, `description`, `min_salary`, `max_salary`, `deadline`, `status`) VALUES
(1, 1, 'Frontend Developer', 'Develop user interfaces.', 60000, 80000, '2024-12-31', 'Active'),
(2, 2, 'Data Engineer', 'Build data pipelines.', 70000, 90000, '2024-12-15', 'Active'),
(6, 4, 'Senior Software Engineer', 'Lead development of cloud applications', 90000, 120000, '2024-12-30', 'Active'),
(7, 5, 'Healthcare Data Analyst', 'Analyze patient data and create insights', 75000, 95000, '2024-12-25', 'Active'),
(8, 6, 'Financial Software Developer', 'Develop trading platforms', 85000, 115000, '2024-12-28', 'Active'),
(9, 7, 'Renewable Energy Consultant', 'Consult on green energy projects', 70000, 90000, '2024-12-20', 'Active'),
(10, 8, 'AI Research Engineer', 'Research and develop AI solutions', 95000, 125000, '2024-12-22', 'Active'),
(11, 8, 'AI Research Engineer', 'Research and develop AI solutions', 95000, 125000, '2024-06-09', 'Expired');

--
-- Triggers `job_posting`
--
DELIMITER $$
CREATE TRIGGER `update_job_status` BEFORE UPDATE ON `job_posting` FOR EACH ROW BEGIN
    IF NEW.deadline < CURDATE() THEN
        SET NEW.status = 'Expired';
    ELSE
        SET NEW.status = 'Active';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `job_skill`
--

CREATE TABLE `job_skill` (
  `job_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_skill`
--

INSERT INTO `job_skill` (`job_id`, `skill_id`) VALUES
(1, 1),
(1, 4),
(2, 3),
(2, 4),
(6, 7),
(7, 8),
(8, 9),
(9, 10),
(10, 11);

-- --------------------------------------------------------

--
-- Table structure for table `notification_log`
--

CREATE TABLE `notification_log` (
  `log_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `notification_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_log`
--

INSERT INTO `notification_log` (`log_id`, `application_id`, `company_id`, `notification_time`) VALUES
(18, 1, 1, '2024-11-19 14:24:43'),
(20, 1, 1, '2024-11-19 07:43:19'),
(21, 2, 2, '2024-11-19 10:26:42'),
(23, 1, 1, '2024-11-20 06:58:00'),
(24, 1, 1, '2024-11-20 07:09:03');

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `profile_id` int(11) NOT NULL,
  `profile_url` varchar(100) DEFAULT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`profile_id`, `profile_url`, `headline`, `summary`) VALUES
(1, 'https://example.com/profiles/1', 'Software Developer', 'Experienced in full-stack development.'),
(2, 'https://example.com/profiles/2', 'Data Scientist', 'Specialized in machine learning and data analysis.'),
(101, 'john_doe_profile.com', 'Software Engineer', 'Experienced in full-stack development.'),
(102, 'jane_smith_profile.com', 'Data Scientist', 'Specialized in machine learning and AI.'),
(103, 'mike_wilson_profile.com', 'Senior Developer', 'Cloud computing expert'),
(104, 'sarah_chen_profile.com', 'Data Analyst', 'Healthcare data specialist'),
(105, 'james_kumar_profile.com', 'Financial Tech Expert', '10 years in FinTech'),
(106, 'lisa_green_profile.com', 'Energy Consultant', 'Sustainable energy specialist'),
(107, 'alex_wong_profile.com', 'AI Engineer', 'Machine learning expert');

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE `skill` (
  `skill_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `domain` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skill`
--

INSERT INTO `skill` (`skill_id`, `name`, `domain`) VALUES
(1, 'JavaScript', 'Frontend Development'),
(2, 'Python', 'Data Engineering'),
(3, 'Machine Learning', 'AI'),
(4, 'SQL', 'Database'),
(5, 'Python', 'Programming'),
(6, 'Java', 'Programming'),
(7, 'Cloud Computing', 'Infrastructure'),
(8, 'Healthcare Analytics', 'Healthcare'),
(9, 'Financial Analysis', 'Finance'),
(10, 'Renewable Energy', 'Energy'),
(11, 'Deep Learning', 'AI');

-- --------------------------------------------------------

--
-- Table structure for table `skill_prof`
--

CREATE TABLE `skill_prof` (
  `profile_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skill_prof`
--

INSERT INTO `skill_prof` (`profile_id`, `skill_id`) VALUES
(1, 1),
(1, 4),
(101, 1),
(101, 4),
(102, 3),
(102, 4),
(103, 1),
(103, 4),
(103, 7),
(104, 2),
(104, 4),
(104, 8),
(105, 2),
(105, 4),
(105, 9),
(106, 2),
(106, 4),
(106, 10),
(107, 2),
(107, 3),
(107, 11);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `dob`, `age`, `profile_id`, `location`) VALUES
(1, 'John Doe', 'john.doe@example.com', '1990-01-01', 34, 1, 'New York'),
(2, 'Jane Smith', 'jane.smith@example.com', '1995-05-15', 29, 2, 'San Francisco'),
(4, 'Mike Wilson', 'mike.wilson@email.com', '1988-03-15', 35, 103, 'Boston'),
(5, 'Sarah Chen', 'sarah.chen@email.com', '1992-07-22', 31, 104, 'Chicago'),
(6, 'James Kumar', 'james.kumar@email.com', '1990-11-30', 33, 105, 'Miami'),
(7, 'Lisa Green', 'lisa.green@email.com', '1993-04-18', 30, 106, 'Seattle');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `log_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO action_log 
    (action_time, action_type, user_id)
    VALUES 
    (NOW(), 'INSERT', NEW.user_id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_user_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    INSERT INTO action_log 
    (action_time, action_type, user_id)
    VALUES 
    (NOW(), 'UPDATE', NEW.user_id);
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `action_log`
--
ALTER TABLE `action_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `fk_application_user` (`user_id`),
  ADD KEY `fk_application_job` (`job_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `comp_job`
--
ALTER TABLE `comp_job`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`profile_id`,`start_date`,`end_date`,`degree`);

--
-- Indexes for table `experience`
--
ALTER TABLE `experience`
  ADD PRIMARY KEY (`profile_id`,`company_name`,`start_date`,`end_date`);

--
-- Indexes for table `job_posting`
--
ALTER TABLE `job_posting`
  ADD PRIMARY KEY (`job_id`);

--
-- Indexes for table `job_skill`
--
ALTER TABLE `job_skill`
  ADD PRIMARY KEY (`job_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `notification_log`
--
ALTER TABLE `notification_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`profile_id`);

--
-- Indexes for table `skill`
--
ALTER TABLE `skill`
  ADD PRIMARY KEY (`skill_id`);

--
-- Indexes for table `skill_prof`
--
ALTER TABLE `skill_prof`
  ADD PRIMARY KEY (`profile_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `profile_id` (`profile_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `action_log`
--
ALTER TABLE `action_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notification_log`
--
ALTER TABLE `notification_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `action_log`
--
ALTER TABLE `action_log`
  ADD CONSTRAINT `action_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `application`
--
ALTER TABLE `application`
  ADD CONSTRAINT `fk_application_job` FOREIGN KEY (`job_id`) REFERENCES `job_posting` (`job_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_application_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comp_job`
--
ALTER TABLE `comp_job`
  ADD CONSTRAINT `comp_job_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_posting` (`job_id`),
  ADD CONSTRAINT `comp_job_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`);

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `education_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`profile_id`);

--
-- Constraints for table `experience`
--
ALTER TABLE `experience`
  ADD CONSTRAINT `experience_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`profile_id`);

--
-- Constraints for table `job_skill`
--
ALTER TABLE `job_skill`
  ADD CONSTRAINT `job_skill_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_posting` (`job_id`),
  ADD CONSTRAINT `job_skill_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`skill_id`);

--
-- Constraints for table `notification_log`
--
ALTER TABLE `notification_log`
  ADD CONSTRAINT `notification_log_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `application` (`application_id`),
  ADD CONSTRAINT `notification_log_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`);

--
-- Constraints for table `skill_prof`
--
ALTER TABLE `skill_prof`
  ADD CONSTRAINT `skill_prof_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`profile_id`),
  ADD CONSTRAINT `skill_prof_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skill` (`skill_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`profile_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
