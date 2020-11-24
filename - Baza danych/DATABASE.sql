-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 24 Lis 2020, 22:05
-- Wersja serwera: 10.3.25-MariaDB-2.cba
-- Wersja PHP: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `nonom123`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `class`
--

CREATE TABLE `class` (
  `id` int(11) NOT NULL,
  `value` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `class`
--

INSERT INTO `class` (`id`, `value`) VALUES
(32, '1A'),
(34, '1C'),
(35, '1B'),
(36, '1D'),
(37, '1E'),
(38, '2A'),
(39, '2B'),
(40, '2C'),
(42, '2D'),
(43, '2E'),
(44, '3A'),
(45, '3B'),
(46, '3C'),
(47, '3D'),
(48, '3E');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `day`
--

CREATE TABLE `day` (
  `id` int(11) NOT NULL,
  `value` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `day`
--

INSERT INTO `day` (`id`, `value`) VALUES
(1, 'Poniedziałek'),
(2, 'Wtorek'),
(3, 'Środa'),
(4, 'Czwartek'),
(5, 'Piątek'),
(6, 'Sobota'),
(7, 'Niedziela');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `hour`
--

CREATE TABLE `hour` (
  `id` int(11) NOT NULL,
  `value` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `hour`
--

INSERT INTO `hour` (`id`, `value`) VALUES
(0, '07:10 - 07:55'),
(1, '08:00 - 08:45'),
(2, '08:55 - 09:40'),
(3, '09:50 - 10:35'),
(4, '10:55 - 11:40'),
(5, '11:50 - 12:35'),
(6, '12:45 - 13:30'),
(7, '13:40 - 14:25'),
(8, '14:30 - 15:15'),
(9, '15:25 - 16:10');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `info`
--

CREATE TABLE `info` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf8_polish_ci NOT NULL,
  `content` text COLLATE utf8_polish_ci NOT NULL,
  `info_type_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `info`
--

INSERT INTO `info` (`id`, `name`, `content`, `info_type_id`) VALUES
(1, 'Kontakt i Dojazd', '<h1>Lokalizacja i kontakt:</h1>\n\n<p>\n</p><h5>Nazwa szkoły</h5>\nIV Liceum Ogólnokształcące<br>\nim. Bolesława Chrobrego\n<h5>Adres szkoły:</h5>\npl. gen. W. Sikorskiego 1<br\\>\n41-902 Bytom\n<p></p>\n\n<p>\n</p><h5>Telefon (sekretariat):</h5>\n032 281 41 93\n<h5>Fax:</h5>\n032 281 41 93\n<p></p>	\n\n<p>\n</p><h5>Strona Internetowa:</h5>\nhttp://www.4lo.bytom.pl/\n<h5>Poczta elektroniczna:</h5>\nsekretariat.4lo.bytom@wp.pl\n<p></p></br\\>', 3),
(2, 'Zastępstwa', '<h1>Zastępstwa na dzień 07.06.2017:</h1>\n\n<p>\n<h2>Za p. A. Solecką</h2>\n1. 1d - klasa zaczyna od 2 lekcji</br>\n3. 1b - p. B. Balcerak</br>\n4. 2e - p. B. Balcerak</br>\n5. 1d - p. L. Piwko\n</p>\n\n<p>\n<h2>Za p. M. Pośpiecha</h2>\n1. A1 kl.II - grupa zaczyna od 3 lekcji </br>\n2. A1 kl.II - grupa zaczyna od 3 lekcji </br>\n3. 2b - wycieczka</br>\n4. 2b - wycieczka\n</p>\n\n<p>\n<h2>Za p. K. Materne</h2>\n4. 1b - p. E. Klimek <br>\n5. 1b - p. K. Bociańska <br>\n6. 2c - p. K. Bociańska <br>\n</p>', 1),
(3, 'Plan Lekcji', '<div class=\"float_text\">\n<h1>Czas trwania lekcji:</h1>\n0. 07.10 - 07.55<br>\n1. 08.00 - 08.45<br>\n2. 08.55 - 09.40<br>\n3. 09.50 - 10.35<br>\n4. 10.55 - 11.40<br>\n5. 11.50 - 12.35<br>\n6. 12.45 - 13.30<br>\n7. 13.40 - 14.25<br>\n8. 14.30 - 15.15<br>\n9. 15.25 - 16.10\n</div>\n', 1),
(5, 'Samorząd', '<h1>Samorząd szkolny</h1>\n<p>\n<b>Przewodniczący:</b><br/>\nBujoczek Mateusz<br/><br/>\n\n<b>Zastępca:</b><br/>\nPołap Agnieszka<br/><br/>\n\n<b>Skarbnik:</b><br/>\nCieniawska Zuzanna\n</p>\n\n<a href=#\">Regulamin Samorządu Ucznowskiego</a>', 1),
(7, 'Rozszerzenia', '<h1>Wybór przedmiotów rozszerzonych</h1>\n\n<p>\n<a href=\"#\" target=\"_blank\">Regulamin wyboru przedmiotów nauczanych w zakresie rozszerzonym</a><br>\n<a href=\"#\" target=\"_blank\">Instrukcja wyboru oraz wypełniania deklaracji</a>\n</p>\n\n<p class=\"float_text\">\n<a href=\"#\" target=\"_blank\">Formularz_III-A-1</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_III-A-2</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_III-B-1</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_III-B-2</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_III-C-1</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_III-C-2</a>\n</p>\n\n<p class=\"float_text\">\n<a href=\"#\" target=\"_blank\">Formularz_IV-D-4</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_IV-D-4</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_IV-E-3</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_IV-E-4</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_IV-F-3</a><br>\n<a href=\"#\" target=\"_blank\">Formularz_IV-F-4</a>\n</p>', 1),
(8, 'Terminy Zebrań', '<h2>Zebranie informacyjne dla rodziców odbędzie się</h2>\n<h1>11 października (poniedziałek) o godzinie 22:00.', 2),
(10, 'Rada Rodziców', '<p>\n</p><h2>Wpłaty na rzecz Rady Rodziców prosimy przesyłać na poniższe konto bankowe:</h2>\n<h4>Getin Bank O/Bytom<br>\nBytom 41-902, ul. Katowicka 16<br>\n24 1560 1049 2103 1804 7925 0001</h4>\n<p></p>\n\n<p>\nW tytule przelewu (wpłaty) prosimy wpisać imię i nazwisko dziecka oraz klasę.\n</p>\n\n<p>\n</p><h2>Rachunek prowadzony na rzecz:</h2>\nRada Rodziców przy IV Liceum Ogólnokształcącym<br>\nim. Bolesława Chrobrego<br>\nul. Gen. W.Sikorskiego 1<br>\n41-902 Bytom\n<p></p>\n\n<p></p><h4>\n<a href=\"#\" target=\"_blank\"> Regulamin RR (Rady Rodziców)</a></h4>\n<p></p>\n\n<p>\n</p><h2>Skład prezydium RR wraz z danymi kontaktowymi nr tel. oraz adresy e-mail:</h2>\n<b>Przewodniczący:</b><br>\nMichał Flasz - 513152173 - michal@flasz.pl<br><br>\n<b>Wice przewodniczący:</b><br>\nTomasz Zakszewski - foto@focal.pl\n<p></p>', 2),
(11, 'Szczęśliwe Numerki', '<h1>Numerki niepytane na Listopad 2016:</h1>\r\n\r\n<p>\r\n08.11.2016 - 6 i 16<br/>\r\n09.11.2016 - 3 i 25<br/>\r\n10.11.2016 - 11 i 29<br/>\r\n14.11.2016 - 33 i 21<br/>\r\n15.11.2016 - 12 i 18<br/>\r\n16.11.2016 - 2 i 13<br/>\r\n17.11.2016 - 22 i 31 i 19<br/>\r\n18.11.2016 - 15 i 28<br/>\r\n21.11.2016 - 1 i 17<br/>\r\n22.11.2016 - 23 i 34<br/>\r\n23.11.2016 - 4 i 5 i 30<br/>\r\n24.11.2016 - 14 i 32<br/>\r\n25.11.2016 - 20 i 24<br/>\r\n28.11.2016 - 9 i 26<br/>\r\n29.11.2016 - 7 i 13 i 8<br/>\r\n30.11.2016 - 10 i 27\r\n</p>', 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `info_type`
--

CREATE TABLE `info_type` (
  `id` int(11) NOT NULL,
  `value` text CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Zrzut danych tabeli `info_type`
--

INSERT INTO `info_type` (`id`, `value`) VALUES
(1, 'Dla Ucznia'),
(2, 'Dla Rodzica'),
(3, 'O Szkole');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `lesson`
--

CREATE TABLE `lesson` (
  `id` int(11) NOT NULL,
  `value` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `lesson`
--

INSERT INTO `lesson` (`id`, `value`) VALUES
(1, 'Polski'),
(2, 'Angielski'),
(3, 'Niemiecki'),
(4, 'WF'),
(5, 'Przyroda'),
(6, 'Fizyka'),
(7, 'Matematyka'),
(8, 'WDŻ'),
(9, 'Godz. Wychowawcza'),
(10, 'Chemia');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `name` text COLLATE utf8_polish_ci NOT NULL,
  `content` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `news`
--

INSERT INTO `news` (`id`, `date`, `name`, `content`) VALUES
(193, '2017-01-18', 'Example #1', '<img src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Calico_tabby_cat_-_Savannah.jpg/1200px-Calico_tabby_cat_-_Savannah.jpg\"/>'),
(194, '2010-01-18', 'Example #2', '<img src=\"https://cdn.vox-cdn.com/thumbor/EaUuzIdnUGXAs_LokdLgtdrJZCY=/0x0:420x314/1400x1050/filters:focal(136x115:202x181):format(gif)/cdn.vox-cdn.com/uploads/chorus_image/image/55279403/tenor.0.gif\"/>'),
(195, '2017-02-12', 'Example #3', '<p>\n<h2><b> Lorem ipsum dolor sit amet, consectetur adipiscing elit. </b></h2>\nInteger commodo arcu at diam semper maximus. Pellentesque lorem magna, tincidunt vel leo ut, tempus semper enim. Aliquam nunc orci, sodales vel faucibus non, maximus at enim. Suspendisse velit felis, convallis et fringilla in, egestas sed sem. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Praesent porttitor sapien vel ipsum imperdiet bibendum. Phasellus lorem turpis, interdum nec convallis vel, pretium non leo. In nunc ipsum, maximus et aliquet in, rutrum id velit. Suspendisse blandit lectus lorem, sit amet blandit orci accumsan ac. Quisque bibendum et ante vitae fermentum. Etiam aliquam fringilla odio, in ullamcorper massa elementum sed.</p>\n<img src=\"https://gll.urk.edu.pl/zasoby/74/b06.png\"/>\n<p>Phasellus eget diam nec orci bibendum blandit at sit amet felis. Ut mattis enim at urna eleifend, a sagittis risus ultricies. Nullam eget condimentum lacus. Sed sagittis dui vel rutrum bibendum. Sed bibendum nulla ut erat convallis, sit amet sodales nisi egestas. Nulla tincidunt metus eu leo rhoncus tincidunt. Donec iaculis ligula accumsan, gravida nibh ut, convallis leo. Fusce id est velit. Vivamus pharetra rutrum sem non mattis. Phasellus vel egestas dui. Nullam et risus nisi. Suspendisse potenti. Proin ut elit lorem. Praesent semper purus vitae erat tincidunt lobortis. Donec elementum, massa sed pharetra aliquet, est ipsum blandit elit, scelerisque mattis ligula nunc a felis. Cras et felis porta, mollis eros non, dapibus ligula. </p>'),
(196, '2017-05-18', 'Example #4', '<img src=\"https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/5eeea355389655.59822ff824b72.gif\"/>'),
(197, '2017-05-12', 'Example #5', '<img src=\"https://images.theconversation.com/files/279363/original/file-20190613-32335-g34q75.jpg?ixlib=rb-1.1.0&q=45&auto=format&w=1200&h=1200.0&fit=crop\"/>'),
(198, '2018-01-18', 'Example #6', '<img src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/3/37/TaroTokyo20110213-TokyoTower-01.jpg/1200px-TaroTokyo20110213-TokyoTower-01.jpg\"/>'),
(199, '2018-01-18', 'Example #7', 'Maecenas faucibus vitae arcu pretium laoreet. Sed eu scelerisque ex, quis consequat lorem. Donec vel nisl mattis, malesuada lacus ac, sodales lacus. Pellentesque varius ligula ut laoreet vestibulum. Vivamus pretium sem non nisl egestas vehicula. Suspendisse vitae ligula vel sapien vulputate scelerisque. Sed pharetra eget eros vel auctor. Sed ipsum ipsum, facilisis a quam non, consectetur congue neque. Nulla vitae sapien sit amet orci lobortis pharetra vestibulum ac massa. Praesent mattis varius nibh at tincidunt. Donec ullamcorper quam arcu. Cras ornare eleifend urna a elementum.eeeee'),
(200, '2018-01-18', 'Example #8', '<img src=\"https://thumbs.gfycat.com/CooperativeDelightfulGrayling-size_restricted.gif\"/>'),
(201, '2018-01-18', 'Example #9', '<img src=\"https://images.theconversation.com/files/332407/original/file-20200504-83721-qp9zyt.jpg?ixlib=rb-1.1.0&q=45&auto=format&w=496&fit=clip\"/>'),
(202, '2018-01-18', 'Example #10', '<h1>Lorem ipsum dolor sit amet, </h1>\n<h3>consectetur adipiscing elit. In gravida</h3>\n<h5>sit amet dolor id consectetur. </h6>\n<span style=\"letter-spacing: 8px;\">Nulla sit amet nisl diam.</span>'),
(203, '2018-01-18', 'Example #11', '<img src=\"https://cf.bstatic.com/images/hotel/max1024x768/170/170698541.jpg\"/>'),
(204, '2018-01-18', 'Example #12', 'Maecenas faucibus vitae arcu pretium laoreet. Sed eu scelerisque ex, quis consequat lorem. Donec vel nisl mattis, malesuada lacus ac, sodales lacus.'),
(205, '2018-01-18', 'Example #13', '<img src=\"https://post.greatist.com/wp-content/uploads/sites/3/2020/02/325466_1100-1100x628.jpg\"/>'),
(206, '2018-01-18', 'Example #14', '<img src=\"https://images.theconversation.com/files/319375/original/file-20200309-118956-1cqvm6j.jpg?ixlib=rb-1.1.0&q=45&auto=format&w=1200&h=900.0&fit=crop\"/>'),
(207, '2018-01-18', 'Example #15', '<img src=\"https://www.tabletowo.pl/wp-content/uploads/2018/03/gify-wszedzie.gif\"/>'),
(208, '2018-01-18', 'Example #16', '<img src=\"https://static.toiimg.com/thumb/msid-60132235,imgsize-169468,width-800,height-600,resizemode-75/60132235.jpg\"/>'),
(209, '2018-01-18', 'Example #17', '<img src=\"https://cdn.vox-cdn.com/thumbor/VCuWHw2qUHB6UbN97mA1_abeyNU=/1400x0/filters:no_upscale()/cdn.vox-cdn.com/uploads/chorus_asset/file/13250843/breakdancing_together.jpg\"/>'),
(210, '2018-01-19', 'Example #17', '<img src=\"https://www.sciencenews.org/wp-content/uploads/2019/12/120719_scientistsrights_feat_opt2-1027x579.png\"/>');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `register_code`
--

CREATE TABLE `register_code` (
  `id` int(11) NOT NULL,
  `value` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `register_code`
--

INSERT INTO `register_code` (`id`, `value`) VALUES
(19, '5CC3B585'),
(20, 'EF65244E'),
(21, '30DBC881'),
(22, '060937AB'),
(23, '3C474B12');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `room`
--

CREATE TABLE `room` (
  `id` int(11) NOT NULL,
  `value` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `room`
--

INSERT INTO `room` (`id`, `value`) VALUES
(12, '01'),
(13, '02'),
(14, '03A'),
(15, '03B'),
(16, '05'),
(17, '04'),
(18, '06'),
(19, '07'),
(20, '08'),
(21, '09'),
(22, '10'),
(23, '11'),
(24, '12'),
(25, '13'),
(26, '14'),
(27, '15A'),
(28, '15B');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `teacher`
--

CREATE TABLE `teacher` (
  `id` int(11) NOT NULL,
  `value` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `teacher`
--

INSERT INTO `teacher` (`id`, `value`) VALUES
(6, 'Henryk Kalla'),
(9, 'Joanna Jaksik'),
(18, 'Anna Ulfik'),
(25, 'Ewa Szpara'),
(32, 'Leszek Piwko'),
(39, 'Joanna Furtak');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `hour_id` int(11) NOT NULL,
  `day_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `timetable`
--

INSERT INTO `timetable` (`id`, `lesson_id`, `teacher_id`, `class_id`, `room_id`, `hour_id`, `day_id`) VALUES
(41, 1, 6, 35, 12, 1, 5),
(42, 5, 6, 35, 14, 0, 1),
(43, 8, 6, 34, 14, 3, 1),
(44, 7, 6, 36, 14, 1, 1),
(45, 9, 6, 34, 14, 2, 1),
(51, 8, 39, 34, 15, 4, 4),
(55, 1, 25, 34, 17, 3, 2),
(56, 7, 39, 34, 18, 4, 1),
(57, 3, 6, 34, 21, 6, 1),
(60, 3, 6, 34, 23, 3, 4),
(61, 5, 9, 34, 17, 5, 1),
(64, 7, 32, 34, 14, 4, 3),
(65, 10, 6, 34, 20, 5, 3),
(66, 4, 39, 34, 17, 4, 2),
(67, 4, 39, 34, 17, 5, 2),
(68, 4, 6, 34, 15, 6, 2),
(69, 6, 39, 34, 20, 7, 2),
(70, 4, 25, 34, 18, 6, 3),
(71, 9, 39, 34, 18, 5, 4),
(73, 10, 6, 34, 14, 7, 4),
(74, 3, 18, 34, 18, 5, 5),
(75, 6, 6, 34, 20, 6, 5),
(76, 10, 6, 34, 16, 7, 5),
(77, 3, 9, 34, 15, 8, 5);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `timetable_type`
--

CREATE TABLE `timetable_type` (
  `id` int(11) NOT NULL,
  `name` text COLLATE utf8_polish_ci NOT NULL,
  `type_name` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `timetable_type`
--

INSERT INTO `timetable_type` (`id`, `name`, `type_name`) VALUES
(1, 'Klasa', 'class'),
(2, 'Nauczyciel', 'teacher'),
(3, 'Sala', 'room');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `login` text COLLATE utf8_polish_ci NOT NULL,
  `pass` text COLLATE utf8_polish_ci NOT NULL,
  `email` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

--
-- Zrzut danych tabeli `user`
--

INSERT INTO `user` (`id`, `login`, `pass`, `email`) VALUES
(4, 'admin', '$2y$10$Mnnv2Amcj.87It4fXS93WezWre/SkQjuA9/NgDeLyID2AutVClDpu', 'email@gmail.com');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `day`
--
ALTER TABLE `day`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hour`
--
ALTER TABLE `hour`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `info`
--
ALTER TABLE `info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `info_type_id` (`info_type_id`);

--
-- Indexes for table `info_type`
--
ALTER TABLE `info_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lesson`
--
ALTER TABLE `lesson`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `register_code`
--
ALTER TABLE `register_code`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `hour_id` (`hour_id`),
  ADD KEY `day_id` (`day_id`);

--
-- Indexes for table `timetable_type`
--
ALTER TABLE `timetable_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `class`
--
ALTER TABLE `class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
--
-- AUTO_INCREMENT dla tabeli `info`
--
ALTER TABLE `info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT dla tabeli `info_type`
--
ALTER TABLE `info_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT dla tabeli `lesson`
--
ALTER TABLE `lesson`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT dla tabeli `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;
--
-- AUTO_INCREMENT dla tabeli `register_code`
--
ALTER TABLE `register_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT dla tabeli `room`
--
ALTER TABLE `room`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
--
-- AUTO_INCREMENT dla tabeli `teacher`
--
ALTER TABLE `teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
--
-- AUTO_INCREMENT dla tabeli `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;
--
-- AUTO_INCREMENT dla tabeli `timetable_type`
--
ALTER TABLE `timetable_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT dla tabeli `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
