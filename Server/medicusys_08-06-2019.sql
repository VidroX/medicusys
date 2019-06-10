-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2019 at 10:54 PM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medicusys`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `simple_loop` ()  BEGIN
  DECLARE counter BIGINT DEFAULT 0;

  my_loop: LOOP
    SET counter=counter+1;

    IF counter=100000 THEN
      LEAVE my_loop;
    END IF;

    SELECT counter; #uncomment if you'd like to print the counter
	INSERT INTO `visits` (`doctor_id`, `patient_id`, `visit_date`, `visited`) VALUES ('1', '1', '2019-06-02 00:00:00', '0');

  END LOOP my_loop;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `diagnoses`
--

CREATE TABLE `diagnoses` (
  `id` int(10) NOT NULL,
  `doctor_id` int(10) NOT NULL,
  `patient_id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `detection_date` date NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `diagnoses`
--

INSERT INTO `diagnoses` (`id`, `doctor_id`, `patient_id`, `name`, `detection_date`, `active`) VALUES
(2, 1, 1, 'Allergy', '2019-06-07', 0),
(3, 1, 1, 'Allergy', '2019-06-07', 0),
(4, 1, 1, 'Allergy', '2019-06-07', 0),
(5, 1, 1, 'Allergy', '2019-06-07', 0),
(6, 1, 1, 'Foodborne illness', '2019-06-07', 0),
(7, 1, 1, 'Allergy2', '2019-06-07', 0),
(8, 1, 1, 'Asd', '2019-06-07', 0),
(9, 1, 1, 'Pulmonary embolism', '2019-06-07', 0),
(10, 1, 1, 'Adverse drug reaction', '2019-06-07', 0),
(11, 1, 1, 'Adverse drug reaction', '2019-06-07', 0),
(12, 1, 1, 'dasdas', '2019-06-08', 0),
(13, 1, 1, 'Asd', '2019-06-08', 1),
(14, 1, 1, 'Migraine', '2019-06-08', 1),
(15, 1, 2, 'Obstipation', '2019-06-08', 1),
(16, 1, 1, 'Allergy', '2019-06-08', 1),
(17, 1, 1, 'Adverse drug reaction', '2019-06-08', 1),
(18, 1, 1, 'Anxiety disorder', '2019-06-08', 1),
(19, 1, 1, 'Adverse drug reaction', '2019-06-08', 1);

-- --------------------------------------------------------

--
-- Table structure for table `diagnoses_data`
--

CREATE TABLE `diagnoses_data` (
  `id` int(10) NOT NULL,
  `diagnosis_id` int(10) NOT NULL,
  `symptom_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `diagnoses_data`
--

INSERT INTO `diagnoses_data` (`id`, `diagnosis_id`, `symptom_id`) VALUES
(1, 3, 1),
(2, 3, 4),
(3, 4, 1),
(4, 4, 127),
(5, 4, 4),
(6, 5, 1),
(7, 5, 127),
(8, 5, 128),
(9, 5, 129),
(10, 5, 130),
(11, 5, 4),
(12, 6, 23),
(13, 6, 24),
(14, 8, 130),
(15, 9, 22),
(16, 10, 1),
(17, 10, 2),
(18, 10, 4),
(19, 10, 12),
(20, 11, 4),
(21, 13, 4),
(22, 14, 23),
(23, 15, 1),
(24, 16, 1),
(25, 16, 4),
(26, 16, 21),
(27, 17, 4),
(28, 18, 7),
(29, 19, 4);

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `specialty` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `specialty`) VALUES
(1, 1, NULL),
(2, 6, NULL),
(3, 8, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

CREATE TABLE `medication` (
  `id` int(10) NOT NULL,
  `diagnosis_id` int(10) NOT NULL,
  `rp` varchar(255) NOT NULL,
  `dtdn` varchar(255) NOT NULL,
  `signa` varchar(255) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `medication`
--

INSERT INTO `medication` (`id`, `diagnosis_id`, `rp`, `dtdn`, `signa`, `active`) VALUES
(1, 13, 'Panthenol3', '2123asd', '31', 0),
(2, 13, 'Panthenol2', '1', '4', 0),
(3, 13, '1234', 'Sdasd', 'sdasd', 0),
(4, 13, 'asd', 'asd', 'asd', 0),
(5, 14, '!w1', 'sa', 'd', 0),
(6, 13, '123', '321', '123', 0),
(7, 13, '123', '321', '22', 0),
(8, 13, '22', '22', '33', 0),
(9, 16, '123', '321', '12213312321', 0),
(10, 16, '21213', '3222', '231', 1),
(11, 14, 'sadas', 'dsad', 'sda', 1),
(12, 16, 'dd', 'asd', 'sa', 0),
(13, 17, '111', '12', '22', 1),
(14, 15, 'dd', 'as', 's', 1),
(15, 16, 'hbh', 'ftf', 'ftf', 1),
(16, 18, '2', '32', '3', 1),
(17, 16, '2', 's', 'd', 1),
(18, 19, '2', 'dsa', 'dd', 0),
(19, 19, 's', 'd', '123', 1),
(20, 19, 'dca sdzxc ', 'd as d', 'asas da', 1);

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `version` varchar(120) COLLATE utf8mb4_bin DEFAULT NULL,
  `headline` text COLLATE utf8mb4_bin NOT NULL,
  `maintext` text COLLATE utf8mb4_bin NOT NULL,
  `image` text COLLATE utf8mb4_bin COMMENT 'pathToImage'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `date`, `version`, `headline`, `maintext`, `image`) VALUES
(1, '2019-06-01', NULL, 'Updated telegram bot', 'sfdfajdfhlkjasdhflkjsdhflkasjfdhlkajdsfljsdflask', 'no_image.jpg'),
(2, '2019-06-01', NULL, 'Updated telegram bot', 'sfdfajdfhlkjasdhflkjsdhflkasjfdhlkajdsfljsdflask', 'no_image.jpg'),
(3, '2019-06-01', NULL, 'Updated telegram bot', 'sfdfajdfhlkjasdhflkjsdhflkasjfdhlkajdsfljsdflask', 'no_image.jpg'),
(4, '2019-06-01', NULL, 'Updated telegram bot', 'sfdfajdfhlkjasdhflkjsdhflkasjfdhlkajdsfljsdflask', 'no_image.jpg'),
(5, '2019-06-01', NULL, 'Updated telegram bot', 'sfdfajdfhlkjasdhflkjsdhflkasjfdhlkajdsfljsdflask', 'no_image.jpg'),
(6, '2019-06-01', NULL, 'Updated telegram bot', 'sfdfajdfhlkjasdhflkjsdhflkasjfdhlkajdsfljsdflask', 'no_image.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `doctor_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `doctor_id`) VALUES
(1, 3, 1),
(2, 4, 1),
(3, 9, 3),
(4, 10, 1),
(5, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `recorders`
--

CREATE TABLE `recorders` (
  `id` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `recorders`
--

INSERT INTO `recorders` (`id`, `user_id`) VALUES
(1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `symptoms`
--

CREATE TABLE `symptoms` (
  `id` int(10) NOT NULL,
  `api_id` int(10) DEFAULT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `symptoms`
--

INSERT INTO `symptoms` (`id`, `api_id`, `name`) VALUES
(1, 10, 'Abdominal pain'),
(2, 238, 'Anxiety'),
(3, 104, 'Back pain'),
(4, 75, 'Burning eyes'),
(5, 46, 'Burning in the throat'),
(6, 170, 'Cheek swelling'),
(7, 17, 'Chest pain'),
(8, 31, 'Chest tightness'),
(9, 175, 'Chills'),
(10, 139, 'Cold sweats'),
(11, 15, 'Cough'),
(12, 207, 'Dizziness'),
(13, 244, 'Drooping eyelid'),
(14, 273, 'Dry eyes'),
(15, 87, 'Earache'),
(16, 92, 'Early satiety'),
(17, 287, 'Eye pain'),
(18, 33, 'Eye redness'),
(19, 153, 'Fast, deepened breathing'),
(20, 76, 'Feeling of foreign body in the eye'),
(21, 11, 'Fever'),
(22, 57, 'Going black before the eyes'),
(23, 9, 'Headache'),
(24, 45, 'Heartburn'),
(25, 122, 'Hiccups'),
(26, 149, 'Hot flushes'),
(27, 40, 'Increased thirst'),
(28, 73, 'Itching eyes'),
(29, 96, 'Itching in the nose'),
(30, 35, 'Lip swelling'),
(31, 235, 'Memory gap'),
(32, 112, 'Menstruation disorder'),
(33, 123, 'Missed period'),
(34, 44, 'Nausea'),
(35, 136, 'Neck pain'),
(36, 114, 'Nervousness'),
(37, 133, 'Night cough'),
(38, 12, 'Pain in the limbs'),
(39, 203, 'Pain on swallowing'),
(40, 37, 'Palpitations'),
(41, 140, 'Paralysis'),
(42, 54, 'Reduced appetite'),
(43, 14, 'Runny nose'),
(44, 29, 'Shortness of breath'),
(45, 124, 'Skin rash'),
(46, 52, 'Sleeplessness'),
(47, 95, 'Sneezing'),
(48, 13, 'Sore throat'),
(49, 64, 'Sputum'),
(50, 179, 'Stomach burning'),
(51, 28, 'Stuffy nose'),
(52, 138, 'Sweating'),
(53, 248, 'Swollen glands in the armpits'),
(54, 169, 'Swollen glands on the neck'),
(55, 211, 'Tears'),
(56, 16, 'Tiredness'),
(57, 115, 'Tremor at rest'),
(58, 144, 'Unconsciousness, short'),
(59, 101, 'Vomiting'),
(60, 181, 'Vomiting blood'),
(61, 56, 'weakness'),
(62, 23, 'Weight gain'),
(63, 30, 'Wheezing'),
(64, 33, ''),
(66, 10, 'Абдоминальная боль'),
(67, 235, 'Амнезия'),
(68, 35, 'Ангионевротический отек'),
(69, 52, 'Бессоница'),
(70, 13, 'Боль в горле'),
(71, 17, 'Боль в груди'),
(72, 12, 'Боль в конечностях'),
(73, 104, 'Боль в спине'),
(74, 87, 'Боль в ухе'),
(75, 136, 'Боль в шее'),
(76, 203, 'Боль при глотании'),
(77, 287, 'Глазная боль'),
(78, 9, 'Головная боль'),
(79, 207, 'Головокружение'),
(80, 75, 'Жжение в глазах'),
(81, 46, 'Жжение в горле'),
(82, 123, 'Задержка менструации'),
(83, 28, 'Заложенность носа'),
(84, 96, 'Зуд в носу'),
(85, 73, 'Зуд глаз'),
(86, 45, 'Изжога'),
(87, 122, 'Икота'),
(88, 153, 'Интенсивное дыхание'),
(89, 15, 'Кашель'),
(90, 124, 'Кожная сыпь'),
(91, 144, 'Кратковременная потеря сознания'),
(92, 11, 'Лихорадка'),
(93, 64, 'Мокрота'),
(94, 112, 'Нарушение менструации'),
(95, 14, 'Насморк'),
(96, 114, 'Нервозность'),
(97, 133, 'Ночной кашель'),
(98, 57, 'Обморок'),
(99, 29, 'Одышка'),
(100, 175, 'Озноб'),
(101, 76, 'Ощущение инородного тела в глазу'),
(102, 140, 'Паралич'),
(103, 40, 'Полидипсия'),
(104, 138, 'Потливость'),
(105, 23, 'Прибавление в весе'),
(106, 149, 'Приливы'),
(107, 170, 'Припухшая щека'),
(108, 244, 'Птоз века'),
(109, 92, 'Раннее насыщение'),
(110, 101, 'Рвота'),
(111, 181, 'Рвота кровью'),
(112, 211, 'Слезотечение'),
(113, 54, 'Снижение аппетита'),
(114, 273, 'Сухость глаз'),
(115, 37, 'Тахикардия'),
(116, 31, 'Теснота в груди'),
(117, 44, 'Тошнота'),
(118, 238, 'Тревожность'),
(119, 115, 'Тремор в покое'),
(120, 169, 'Увеличение лимфоузлов в области шеи'),
(121, 248, 'Увеличение лимфоузлов в подмышечной впадине'),
(122, 16, 'Усталость'),
(123, 139, 'Холодный пот'),
(124, 30, 'Хрип'),
(125, 95, 'Чихание'),
(126, 179, 'Чувство жжения в желудке'),
(127, -1, 'asdasd'),
(128, -1, 'asdasdd'),
(129, -1, 'asdasdd123'),
(130, -1, 'asdasdd55r');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `patronymic` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` int(1) NOT NULL DEFAULT '1',
  `mobilephone` varchar(12) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pass` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `fcm_reg_token` varchar(255) DEFAULT NULL,
  `home_address` varchar(255) NOT NULL,
  `activated` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `patronymic`, `birthdate`, `gender`, `mobilephone`, `email`, `pass`, `token`, `fcm_reg_token`, `home_address`, `activated`) VALUES
(1, 'Андрей', 'Филипенко', 'Сергеевич', '1985-04-01', 1, '380001234567', 'test@example.com', '$2y$10$KCKaO/y2pd6w2S.6BaIWW.U6MTYWs6gTCoIxj5YaeTWsVIuYLEQ.6', '123', NULL, 'ул. Сумская, 1', 0),
(2, 'Антон', 'Филипенко2', 'Сергеевич2', '1985-01-21', 1, '380001234564', 'test2@example.com', '$2y$10$KCKaO/y2pd6w2S.6BaIWW.U6MTYWs6gTCoIxj5YaeTWsVIuYLEQ.6', '32s1', NULL, 'ул. Сумская, 2', 1),
(3, 'Сергей', 'Степаненко', 'Артемович', '1986-08-16', 1, '3801111111', 'test3@example.com', '$2y$10$KCKaO/y2pd6w2S.6BaIWW.U6MTYWs6gTCoIxj5YaeTWsVIuYLEQ.6', '3fd434f4ca1036051ca5acbdd34ef050bb271b4b3b7411ae7feb9c455005057f', '1232', 'ул. Сумская, 3', 0),
(4, 'Сергей2', 'Степаненко2', 'Артемович2', '1926-08-11', 1, '3802222222', 'test4@example.com', '$2y$10$KCKaO/y2pd6w2S.6BaIWW.U6MTYWs6gTCoIxj5YaeTWsVIuYLEQ.6', '1234', NULL, 'ул. Сумская, 4', 0),
(5, 'Antony', 'Bzzz', 'Zzzb', '1890-05-05', 1, '30901212211', 'test5@example.com', '123', '55341', NULL, 'Ksds St', 0),
(6, 'Test', 'Test', 'Test', '1000-11-12', 1, '380991232123', 'test6@example.com', '$2y$10$O.Rnb0jdJLcC517wn90CsOvX4bV9HwlegnttzpbQSaz/NiJgMu8Mu', 'g422', NULL, 'test str', 0),
(8, 'Test', 'Test', 'Test', '1000-11-12', 1, '380991232124', 'test7@example.com', '$2y$10$c07LWQiY88LR4EGgsqMmUON7OX4nzdfTk45ULlJwrPDtJhe3yG0Ny', 'bvedqq', NULL, 'test str', 0),
(9, 'Test', 'Test', 'Test', '1000-11-12', 1, '380991232125', 'test8@example.com', '$2y$10$1bkNVabUb0eKafivBDawZel5jquniLjbbyAeelxNtGZrLszQrspty', 'htqeqwd', NULL, 'test str', 0),
(10, 'SAdasd', 'asdasd', 'asdasd', '1923-12-12', 1, '380123456789', 'asdasd@asdasd.com', '$2y$10$F9Kf1rSrZmsVm1twYNdw..pQ8FdHev/uT9fhpjw8MF51sodZOfAse', '99608643f163a1eb91aa51023f6857ee7bfd1da7e5377d41af8402c8090e2403', NULL, 'asdasd', 0);

-- --------------------------------------------------------

--
-- Table structure for table `visits`
--

CREATE TABLE `visits` (
  `id` int(10) NOT NULL,
  `doctor_id` int(10) DEFAULT NULL,
  `patient_id` int(10) DEFAULT NULL,
  `visit_date` datetime DEFAULT NULL,
  `visited` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `visits`
--

INSERT INTO `visits` (`id`, `doctor_id`, `patient_id`, `visit_date`, `visited`) VALUES
(1, 2, 3, '2019-06-13 00:00:00', 0),
(2, 1, 1, '2019-05-01 00:00:00', 1),
(3, 1, 1, '2019-06-19 00:00:00', 0),
(4, 1, 1, '2019-06-02 00:00:00', 0),
(5, 1, 1, '2019-05-06 00:00:00', 0),
(6, 1, 1, '2019-06-01 23:00:00', 0),
(7, 1, 4, '2019-06-02 00:00:00', 0),
(8, 1, 2, '2019-06-01 22:00:00', 1),
(9, 1, 2, '2019-06-01 22:00:00', 0),
(10, 1, 2, '2019-06-01 22:00:00', 0),
(11, 1, 2, '2019-06-01 22:00:00', 0),
(12, 1, 2, '2019-06-01 22:00:00', 0),
(13, 1, 2, '2019-06-01 22:00:00', 0),
(14, 1, 2, '2019-06-01 22:00:00', 0),
(15, 1, 2, '2019-06-01 22:00:00', 0),
(16, 1, 2, '2019-06-01 22:00:00', 0),
(17, 1, 2, '2019-06-01 22:00:00', 0),
(18, 1, 2, '2019-06-01 22:00:00', 0),
(19, 1, 2, '2019-06-01 22:00:00', 0),
(20, 1, 2, '2019-06-01 22:00:00', 0),
(21, 1, 2, '2019-06-01 22:00:00', 0),
(22, 1, 2, '2019-06-01 22:00:00', 0),
(23, 1, 2, '2019-06-01 22:00:00', 0),
(24, 1, 2, '2019-06-01 22:00:00', 0),
(25, 1, 2, '2019-06-01 22:00:00', 0),
(26, 1, 2, '2019-06-01 22:00:00', 0),
(27, 1, 2, '2019-06-01 22:00:00', 0),
(28, 1, 2, '2019-06-01 22:00:00', 0),
(29, 1, 2, '2019-06-01 22:00:00', 0),
(30, 1, 2, '2019-06-01 22:00:00', 0),
(31, 1, 5, '2019-06-02 23:00:00', 0),
(32, 1, 5, '2019-06-02 23:00:00', 0),
(33, 1, 5, '2019-06-02 23:00:00', 0),
(34, 1, 5, '2019-06-02 23:00:00', 0),
(35, 1, 5, '2019-06-02 23:00:00', 0),
(36, 1, 5, '2019-06-02 23:00:00', 0),
(37, 1, 5, '2018-06-02 23:00:00', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `diagnoses_data`
--
ALTER TABLE `diagnoses_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diagnosis_id` (`diagnosis_id`),
  ADD KEY `symptom_id` (`symptom_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medication`
--
ALTER TABLE `medication`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diagnosis_id` (`diagnosis_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `recorders`
--
ALTER TABLE `recorders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `symptoms`
--
ALTER TABLE `symptoms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `name_2` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobilephone` (`mobilephone`);

--
-- Indexes for table `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `visit_date` (`visit_date`),
  ADD KEY `sort_index2` (`visit_date`,`patient_id`,`visited`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `diagnoses`
--
ALTER TABLE `diagnoses`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `diagnoses_data`
--
ALTER TABLE `diagnoses_data`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `medication`
--
ALTER TABLE `medication`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `recorders`
--
ALTER TABLE `recorders`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `symptoms`
--
ALTER TABLE `symptoms`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD CONSTRAINT `diagnoses_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`),
  ADD CONSTRAINT `diagnoses_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `diagnoses_data`
--
ALTER TABLE `diagnoses_data`
  ADD CONSTRAINT `diagnoses_data_ibfk_1` FOREIGN KEY (`diagnosis_id`) REFERENCES `diagnoses` (`id`),
  ADD CONSTRAINT `diagnoses_data_ibfk_2` FOREIGN KEY (`symptom_id`) REFERENCES `symptoms` (`id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `medication`
--
ALTER TABLE `medication`
  ADD CONSTRAINT `medication_ibfk_1` FOREIGN KEY (`diagnosis_id`) REFERENCES `diagnoses` (`id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

--
-- Constraints for table `recorders`
--
ALTER TABLE `recorders`
  ADD CONSTRAINT `recorders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `visits`
--
ALTER TABLE `visits`
  ADD CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `visits_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
