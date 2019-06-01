-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Июн 01 2019 г., 22:46
-- Версия сервера: 10.1.40-MariaDB
-- Версия PHP: 7.3.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `medicusis`
--

-- --------------------------------------------------------

--
-- Структура таблицы `doctors`
--

CREATE TABLE `doctors` (
  `id` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `news`
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
-- Дамп данных таблицы `news`
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
-- Структура таблицы `patients`
--

CREATE TABLE `patients` (
  `id` int(10) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `doctor_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `doctor_id`) VALUES
(1, 3, 1),
(2, 4, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `patronymic` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `mobilephone` varchar(12) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pass` varchar(255) NOT NULL,
  `home_address` varchar(255) NOT NULL,
  `activated` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `patronymic`, `birthdate`, `mobilephone`, `email`, `pass`, `home_address`, `activated`) VALUES
(1, 'Андрей', 'Филипенко', 'Сергеевич', '1985-04-01', '380001234567', 'test@example.com', '$2y$10$KCKaO/y2pd6w2S.6BaIWW.U6MTYWs6gTCoIxj5YaeTWsVIuYLEQ.6', 'ул. Сумская, 1', 0),
(2, 'Андрей2', 'Филипенко2', 'Сергеевич2', '1985-01-21', '380001234564', 'test2@example.com', '$2y$10$KCKaO/y2pd6w2S.6BaIWW.U6MTYWs6gTCoIxj5YaeTWsVIuYLEQ.6', 'ул. Сумская, 2', 1),
(3, 'Сергей', 'Степаненко', 'Артемович', '1986-08-16', '3801111111', 'test3@example.com', '$2y$10$KCKaO/y2pd6w2S.6BaIWW.U6MTYWs6gTCoIxj5YaeTWsVIuYLEQ.6', 'ул. Сумская, 3', 0),
(4, 'Сергей2', 'Степаненко2', 'Артемович2', '1926-08-11', '3802222222', 'test4@example.com', '$2y$10$KCKaO/y2pd6w2S.6BaIWW.U6MTYWs6gTCoIxj5YaeTWsVIuYLEQ.6', 'ул. Сумская, 4', 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobilephone` (`mobilephone`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
