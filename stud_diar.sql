-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Hostiteľ: 127.0.0.1
-- Čas generovania: Št 21.Jan 2021, 01:55
-- Verzia serveru: 10.4.14-MariaDB
-- Verzia PHP: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáza: `stud_diar`
--

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `lecture` int(11) NOT NULL,
  `subject` int(11) NOT NULL,
  `room` varchar(22) COLLATE utf8_slovak_ci NOT NULL DEFAULT ''
) ;

--
-- Sťahujem dáta pre tabuľku `lessons`
--

INSERT INTO `lessons` (`id`, `lecture`, `subject`, `room`) VALUES
(23, 0, 2, 'RB052'),
(20, 1, 1, 'RB052'),
(24, 1, 2, 'PA0A1');

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `title` varchar(128) COLLATE utf8_slovak_ci NOT NULL,
  `svgicon` text COLLATE utf8_slovak_ci NOT NULL,
  `css` varchar(48) COLLATE utf8_slovak_ci DEFAULT NULL,
  `inmenu` tinyint(1) NOT NULL DEFAULT 0,
  `content` varchar(48) COLLATE utf8_slovak_ci NOT NULL,
  `loggedin` tinyint(4) NOT NULL,
  `loggedout` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;

--
-- Sťahujem dáta pre tabuľku `menu`
--

INSERT INTO `menu` (`id`, `title`, `svgicon`, `css`, `inmenu`, `content`, `loggedin`, `loggedout`) VALUES
(1, 'Predmety', '<svg width=\"40\" height=\"40\" viewBox=\"0 0 24 24\">\r\n  <g stroke=\"none\">\r\n    <rect width=\"3.9569383\" height=\"1.5827754\" x=\"1.0039992\" y=\"19.676897\"></rect>\r\n    <path d=\"m 5.7797985,19.428783 c 1.6225681,-0.547961 3.2789861,-0.83147 5.0781215,0 v 1.829285 H 5.7797985 Z\"></path>\r\n    <rect width=\"3.9100726\" height=\"1.5373355\" x=\"12.094041\" y=\"19.720732\"></rect>\r\n    <rect width=\"2.7346578\" height=\"1.5815912\" x=\"15.83273\" y=\"23.272585\" transform=\"rotate(-11.04974)\"></rect>\r\n    <path d=\"M 74.960938 20.035156 L 64.753906 22.076172 L 74.722656 71.900391 L 84.929688 69.859375 L 74.960938 20.035156 z M 77.537109 57.21875 A 4.6651782 3.6718749 79.198697 0 1 81.654297 61.158203 A 4.6651782 3.6718749 79.198697 0 1 78.921875 66.429688 A 4.6651782 3.6718749 79.198697 0 1 74.439453 62.535156 A 4.6651782 3.6718749 79.198697 0 1 77.171875 57.263672 A 4.6651782 3.6718749 79.198697 0 1 77.537109 57.21875 z \" transform=\"scale(0.26458333)\"></path>\r\n    <rect id=\"rect866\" width=\"2.7639508\" height=\"1.8662573\" x=\"15.526752\" y=\"6.633296\" transform=\"rotate(-11.958774)\"></rect>\r\n    <rect width=\"3.9464016\" height=\"1.1901846\" x=\"12.077241\" y=\"17.72752\"></rect>\r\n    <rect width=\"3.9534175\" height=\"9.4695072\" x=\"12.063323\" y=\"7.4552517\"></rect>\r\n    <rect width=\"3.9746559\" height=\"1.1870815\" x=\"12.053897\" y=\"5.4673553\"></rect>\r\n    <rect width=\"3.9628439\" height=\"1.9725631\" x=\"12.059802\" y=\"2.7093105\"></rect>\r\n    <rect width=\"3.9510324\" height=\"1.9784691\" x=\"0.9921875\" y=\"2.6974986\"></rect>\r\n    <path d=\"M 3.75 20.675781 L 3.75 71.488281 L 18.679688 71.488281 L 18.679688 20.675781 L 3.75 20.675781 z M 11.228516 57.484375 A 4.6246044 4.6719554 0 0 1 15.853516 62.15625 A 4.6246044 4.6719554 0 0 1 11.228516 66.828125 A 4.6246044 4.6719554 0 0 1 6.6054688 62.15625 A 4.6246044 4.6719554 0 0 1 11.228516 57.484375 z \" transform=\"scale(0.26458333)\"></path>\r\n    <path d=\"m 5.7379422,8.9201527 c 1.7474172,0.5718875 3.4657018,0.6782463 5.1344968,0 v 9.5903813 c -1.7732678,-0.62244 -3.4625403,-0.398466 -5.1344968,0 z\"></path>\r\n    <path d=\"M 5.7295904,6.6525378 H 10.882881 V 8.064055 c -1.6134641,0.7474795 -3.3512885,0.6037095 -5.1532906,0 z\"></path>\r\n  </g>\r\n</svg>', 'subjects', 1, 'subjects', 1, 1),
(2, 'Rozvrh', '<svg viewBox=\"0 0 100 100\" width=\"40\" height=\"40\">\r\n							<g stroke-width=\"0\">\r\n								<rect x=\"20.013454\" y=\"38.822006\" rx=\"1.3847109\" ry=\"1.345933\" width=\"11.235391\" height=\"11.039455\"/>\r\n								<rect x=\"36.417637\" y=\"38.822006\" rx=\"1.3847109\" ry=\"1.345933\" width=\"11.235391\" height=\"11.039455\"/>\r\n								<rect x=\"53.880154\" y=\"38.822006\" rx=\"1.3847109\" ry=\"1.345933\" width=\"11.235391\" height=\"11.039455\"/>\r\n								<rect x=\"20.013454\" y=\"56.284523\" rx=\"1.3847109\" ry=\"1.345933\" width=\"11.235391\" height=\"11.039455\"/>\r\n								<rect x=\"36.417637\" y=\"56.284523\" rx=\"1.3847109\" ry=\"1.345933\" width=\"11.235391\" height=\"11.039455\"/>\r\n								<path d=\"m 47.426975,73.152777 v 5.799749 H 13.229166 c -3.2820504,0 -5.3985067,-2.46731 -5.3985067,-5.398508 V 21.048583 c 0,-2.648778 2.1773327,-5.398506 5.3985067,-5.398506 h 3.316487 v 5.60331 c 0,2.287057 3.27809,3.482708 6.032228,3.482708 2.822118,0 6.087984,-1.357778 6.087984,-3.514899 V 15.73021 h 33.330639 v 5.490104 c 0,2.738462 3.341472,3.494899 6.053344,3.494899 2.617461,0 6.250178,-0.611786 6.250178,-3.608542 v -5.508752 h 3.884345 c 2.557977,0 4.299479,1.338796 4.299479,4.299479 V 43.779855 H 76.940213 V 32.460991 H 14.039453 V 73.15306 Z\"/>\r\n							</g>\r\n							<g stroke-linecap=\"round\" stroke-linejoin=\"round\">\r\n								<path stroke-width=\"6\" d=\"M 22.45064,17.938233 V 7.2507924\"/>\r\n								<path d=\"M 67.958987,17.938233 V 7.2507924\" stroke-width=\"6\"/>\r\n								<circle fill=\"none\" stroke-width=\"5.5\" cx=\"76.795311\" cy=\"73.078644\" r=\"20.018072\"/>\r\n								<path fill=\"none\" stroke-width=\"5.5\" d=\"M 84.985025,75.701857 H 73.853251 V 64.64024\"/>\r\n							</g>\r\n						</svg>', 'schedule', 1, 'schedule', 1, 1),
(3, 'Úlohy', '<svg viewBox=\"0 0 100 100\" width=\"40\" height=\"40\" stroke-width=\"0\">\r\n							<path d=\"M 10.787013,85.050326 V 22.384693 c 0,-4.854801 2.192667,-7.198285 7.184318,-7.198285 h 10.821356 l 3.701957,-3.726799 2.785174,-0.10797 2.047723,-7.3277973 c 0.445128,-1.6612422 2.270104,-2.6800131 4.408147,-2.6800131 h 5.732746 c 2.911664,0 3.726918,1.4185551 4.068399,2.6929809 l 1.805525,7.0342355 h 3.426378 l 4.464694,4.489486 h 9.708095 c 4.781891,0 7.363837,2.109257 7.363837,7.392368 v 16.087495 l -7.274077,7.288219 V 24.433191 c 0,-1.325078 -0.774723,-1.95137 -1.970135,-1.95137 h -9.966811 v 5.719452 c 0,1.045895 -0.703661,1.763025 -1.784703,1.763025 H 31.758248 c -1.65034,0 -2.133242,-0.890308 -2.133242,-2.16206 v -4.946292 h -9.806049 c -1.283498,0 -1.757869,0.601225 -1.757869,1.789938 v 58.177457 c 0,1.353844 0.558285,1.763026 1.784705,1.763026 h 11.920546 l -7.266902,7.295412 H 17.59102 c -5.024385,0 -6.804007,-2.275205 -6.804007,-6.831453 z M 47.235419,6.3009692 C 45.897668,4.9665675 43.48279,5.2427455 42.121494,6.2654322 40.760198,7.2881189 40.73355,8.9640953 40.815894,11.231511 l 7.863257,0.02659 c 0,-1.9782816 -0.105981,-3.6227301 -1.443732,-4.9571318 z\"/>\r\n							<path d=\"m 62.110937,45.231251 c 2.281662,0 2.64314,-0.19459 2.64314,-1.526019 v -4.883227 c 0,-0.672374 -0.317424,-1.490385 -1.490385,-1.490385 H 25.399999 c -1.149972,0 -1.490386,0.440556 -1.490386,1.490385 v 4.954037 c 0,0.945091 0.236196,1.490385 1.490386,1.490385 l 36.710938,-0.03518 z\"/>\r\n							<path d=\"M 58.84522,57.507486 H 25.399999 c -0.9198,0 -1.069609,-0.910202 -1.069609,-1.852618 v -4.371274 c 0,-0.890018 0.31793,-1.422135 1.422134,-1.422135 h 37.717307 c 1.319224,0.0794 1.461099,1.560317 1.461099,1.560317 z\"/>\r\n							<path d=\"M 54.144617,62.208089 H 25.929166 c -1.302753,0 -1.610639,0.59664 -1.610639,1.610638 v 4.563287 c 0,1.461176 1.164918,1.541587 2.670108,1.541587 h 19.44047 z\"/>\r\n							<path d=\"m 87.479701,51.666912 -6.532958,-6.552197 3.704246,-3.718359 c 0.999053,-0.999053 2.679204,-1.122986 3.80219,0 l 9.492401,9.526695 c 0.781096,0.781096 1.040412,2.191728 0.681018,2.551122 l -2.29508,2.372467 c -1.262291,1.304856 -2.300427,2.372466 -2.306967,2.372466 -0.0066,0 -2.951723,-2.948485 -6.54485,-6.552194 z m 7.969954,2.539944 c 0.377366,-0.505625 -0.02633,-1.100188 -0.471661,-1.889654 l -4.315697,-4.356551 -4.315697,-4.356554 -2.203601,2.26517 c 0,0 1.916544,1.947681 4.258988,4.253614 l 4.258985,4.192604 c 0.893412,0.327549 1.988142,0.52358 2.788683,-0.108629 z\"/>\r\n							<path d=\"m 78.579248,47.268898 -2.396965,2.380722 13.199724,13.110276 c 0,0 2.821836,-2.536432 2.610391,-2.592705 -0.211444,-0.05627 -13.41315,-12.898293 -13.41315,-12.898293 z\"/>\r\n							<path d=\"m 47.690605,80.543574 -0.0011,-2.17165 L 73.621366,52.440063 86.651586,65.480756 59.664099,92.51031 Z M 70.615698,66.271355 c 0.386365,-0.740974 0.524853,-1.448146 -0.0535,-2.057568 -1.207246,-0.574545 -2.132469,-0.343314 -3.036507,-0.05155 l -15.576645,15.207552 1.958917,1.972081 c 0.418514,0.418514 1.394826,0.29275 1.687576,0 z m 5.699474,-5.713115 c 0.544229,-0.844806 0.728524,-1.742285 0,-2.773348 -1.133848,-0.309338 -1.699616,-0.145275 -2.287929,0 l -1.500616,1.497136 c -0.328768,0.90527 -0.392287,1.789319 0.05664,2.632373 0.960571,0.293978 1.75354,0.298462 2.371119,0 z\"/>\r\n							<path d=\"m 40.446428,99.262849 5.19881,-19.146555 0.04595,2.378911 11.833309,11.962947 -16.879193,4.986476 c -0.26486,0.07097 -0.265853,0.06818 -0.198876,-0.181779 z m 12.720523,-6.064488 -6.86179,-6.942798 -1.988015,5.839818 2.761936,2.835662 z\"/>\r\n						</svg>', 'notes', 1, 'notes', 1, 1),
(4, 'Prihlásenie', '', 'log-in', 0, 'log-in', 0, 1),
(5, 'Účet', '<svg viewBox=\"0 0 40 40\" width=\"40\" height=\"40\"><path stroke-width=\"0\" d=\"M 39.999999,39.999999 H 4.9993032e-7 v -4.34749 L 13.645542,26.912239 v -3.63104 c -1.872406,0 -3.446551,-2.17911 -3.446551,-3.51579 V 8.2394695 c 0,-2.64654 4.448832,-8.23947 9.97991,-8.23947 5.531075,0 9.712545,5.61375 9.712545,8.18141 V 19.765409 c 0,1.33668 -1.52757,3.51579 -3.461839,3.51579 v 3.62129 l 13.532626,7.72389 z\"/></svg>', 'account', 1, 'account', 1, 0),
(6, 'Galéria', '<svg viewBox=\"0 0 100 100\" width=\"40\" height=\"40\">\r\n								<path fill=\"none\" stroke-width=\"8.9\" stroke-linecap=\"butt\" stroke-linejoin=\"bevel\" d=\"M 94.5967 55.8325 V 93.6858 H 55.8271 m -11.3109 0 H 5.25859 V 55.7402 m 0 -11.3027 V 6.66614 H 44.3225 m 11.4884 0 H 94.5967 V 44.5165\"/>\r\n								<path d=\"M 20.7036 78.5687 H 79.3419 L 65.0214 60.4448 L 54.7026 73.0125 L 40.1505 55.1531 c 0 0 -19.976 22.8865 -19.4469 23.4156 Z\"/>\r\n								<circle cx=\"67.4688\" cy=\"33.3911\" r=\"8.33437\"/>\r\n						</svg>', 'gallery', 1, 'gallery', 0, 0),
(7, 'Chyba načítania!', '', NULL, 0, 'error', 1, 1),
(8, 'Chyba javascriptu!', '', NULL, 0, 'noscript', 1, 1);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `deadline` datetime DEFAULT NULL,
  `subject` int(11) NOT NULL,
  `info` varchar(255) COLLATE utf8_slovak_ci NOT NULL
) ;

--
-- Sťahujem dáta pre tabuľku `notes`
--

INSERT INTO `notes` (`id`, `deadline`, `subject`, `info`) VALUES
(1, '2021-01-21 15:50:00', 1, 'pokusná úloha!'),
(3, NULL, 2, 'asdqweert'),
(6, '2021-02-02 00:00:00', 1, 'ad'),
(9, '2021-01-21 10:20:00', 9, 'vaii');

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `schedule`
--

CREATE TABLE `schedule` (
  `user_id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `les_no` int(11) NOT NULL,
  `les_id` int(11) NOT NULL
) ;

--
-- Sťahujem dáta pre tabuľku `schedule`
--

INSERT INTO `schedule` (`user_id`, `day`, `les_no`, `les_id`) VALUES
(1, 1, 2, 20),
(1, 1, 3, 20),
(1, 2, 7, 23),
(1, 2, 8, 23),
(1, 1, 6, 24),
(1, 1, 7, 24),
(1, 1, 8, 24),
(1, 3, 4, 24),
(1, 3, 5, 24);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `abb` varchar(5) COLLATE utf8_slovak_ci NOT NULL,
  `name` varchar(48) COLLATE utf8_slovak_ci NOT NULL,
  `user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;

--
-- Sťahujem dáta pre tabuľku `subjects`
--

INSERT INTO `subjects` (`id`, `abb`, `name`, `user`) VALUES
(1, 'POS', 'Princípy operačných systémov', 1),
(2, 'MS', 'Modelovanie a simulácia', 1),
(9, 'VAII', 'Vývoj aplikácii pre internet a intranet', 1),
(10, 'SI', 'Softvérové inžinierstvo', 1),
(11, 'VAMZ', 'Vývoj aplikácii pre mobilné zariadenia', 2);

-- --------------------------------------------------------

--
-- Štruktúra tabuľky pre tabuľku `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nick` varchar(15) COLLATE utf8_slovak_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_slovak_ci NOT NULL,
  `pass` varchar(255) COLLATE utf8_slovak_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;

--
-- Sťahujem dáta pre tabuľku `user`
--

INSERT INTO `user` (`id`, `nick`, `email`, `pass`) VALUES
(1, 'janovic3', 'janovic3@stud.uniza.sk', '36aa5ac3a796f7d3ba5d3095a6daad97'),
(2, 'moriak14', 'smnjanovic@gmail.com', '10b68d4e6fcde70e5b0622b9412cad1d');

--
-- Kľúče pre exportované tabuľky
--

--
-- Indexy pre tabuľku `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `les_uniq` (`lecture`,`subject`,`room`),
  ADD KEY `sched_sub` (`subject`);

--
-- Indexy pre tabuľku `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pre tabuľku `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_note_cat` (`subject`) USING BTREE;

--
-- Indexy pre tabuľku `schedule`
--
ALTER TABLE `schedule`
  ADD UNIQUE KEY `sched_uniq` (`user_id`,`day`,`les_no`),
  ADD KEY `sched_len` (`les_id`);

--
-- Indexy pre tabuľku `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_sub` (`user`);

--
-- Indexy pre tabuľku `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pre exportované tabuľky
--

--
-- AUTO_INCREMENT pre tabuľku `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pre tabuľku `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pre tabuľku `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pre tabuľku `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pre tabuľku `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Obmedzenie pre exportované tabuľky
--

--
-- Obmedzenie pre tabuľku `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `sched_sub` FOREIGN KEY (`subject`) REFERENCES `subjects` (`id`);

--
-- Obmedzenie pre tabuľku `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `note_cat` FOREIGN KEY (`subject`) REFERENCES `subjects` (`id`);

--
-- Obmedzenie pre tabuľku `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `for_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `sched_len` FOREIGN KEY (`les_id`) REFERENCES `lessons` (`id`);

--
-- Obmedzenie pre tabuľku `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_user_sub` FOREIGN KEY (`user`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
