-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql306.infinityfree.com
-- Generation Time: Feb 14, 2026 at 11:19 AM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40904287_carp_travel`
--

-- --------------------------------------------------------

--
-- Table structure for table `comenzi`
--

CREATE TABLE `comenzi` (
  `id` int(11) NOT NULL,
  `id_utilizator` int(11) NOT NULL,
  `data_comanda` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `status` enum('in_asteptare','confirmata','anulata') DEFAULT 'in_asteptare'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comenzi`
--

INSERT INTO `comenzi` (`id`, `id_utilizator`, `data_comanda`, `total`, `status`) VALUES
(1, 1, '2026-01-15 22:27:52', '2600.00', '');

-- --------------------------------------------------------

--
-- Table structure for table `comenzi_produse`
--

CREATE TABLE `comenzi_produse` (
  `id` int(11) NOT NULL,
  `id_comanda` int(11) NOT NULL,
  `id_produs` int(11) NOT NULL,
  `cantitate` int(11) NOT NULL DEFAULT 1,
  `pret_unitar` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comenzi_produse`
--

INSERT INTO `comenzi_produse` (`id`, `id_comanda`, `id_produs`, `cantitate`, `pret_unitar`, `created_at`) VALUES
(1, 1, 11, 2, '1300.00', '2026-01-15 20:27:52');

-- --------------------------------------------------------

--
-- Table structure for table `cos_cumparaturi`
--

CREATE TABLE `cos_cumparaturi` (
  `id` int(11) NOT NULL,
  `id_utilizator` int(11) NOT NULL,
  `id_produs` int(11) NOT NULL,
  `cantitate` int(11) NOT NULL COMMENT 'Numar persoane',
  `data_adaugare` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cos_cumparaturi`
--

INSERT INTO `cos_cumparaturi` (`id`, `id_utilizator`, `id_produs`, `cantitate`, `data_adaugare`) VALUES
(16, 1, 15, 1, '2026-01-15 23:52:54');

-- --------------------------------------------------------

--
-- Table structure for table `imagini_produse`
--

CREATE TABLE `imagini_produse` (
  `id` int(11) NOT NULL,
  `id_produs` int(11) NOT NULL,
  `imagine` varchar(255) NOT NULL,
  `ordine` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `imagini_produse`
--

INSERT INTO `imagini_produse` (`id`, `id_produs`, `imagine`, `ordine`) VALUES
(1, 1, 'produse_poze/istanbul_1.jpg', 1),
(2, 1, 'produse_poze/istanbul_2.jpg', 2),
(3, 1, 'produse_poze/istanbul_3.jpg', 3),
(4, 2, 'produse_poze/bali_1.jpg', 1),
(5, 2, 'produse_poze/bali_2.jpg', 2),
(6, 2, 'produse_poze/bali_3.jpg', 3),
(7, 3, 'produse_poze/santorini_1.jpg', 1),
(8, 3, 'produse_poze/santorini_2.jpg', 2),
(9, 3, 'produse_poze/santorini_3.jpg', 3),
(10, 4, 'produse_poze/dubai_1.jpg', 1),
(11, 4, 'produse_poze/dubai_2.jpg', 2),
(12, 4, 'produse_poze/dubai_3.jpg', 3),
(13, 5, 'produse_poze/abu_1.jpg', 1),
(14, 5, 'produse_poze/abu_2.jpg', 2),
(15, 5, 'produse_poze/abu_3.jpg', 3),
(16, 6, 'produse_poze/cairo_1.jpg', 1),
(17, 6, 'produse_poze/cairo_2.jpg', 2),
(18, 6, 'produse_poze/cairo_3.jpg', 3),
(19, 7, 'produse_poze/hur_1.jpg', 1),
(20, 7, 'produse_poze/hur_2.jpg', 2),
(21, 7, 'produse_poze/hur_3.jpg', 3),
(22, 8, 'produse_poze/paris_1.jpg', 1),
(23, 8, 'produse_poze/paris_2.jpg', 2),
(24, 8, 'produse_poze/paris_3.jpg', 3),
(25, 9, 'produse_poze/florence_1.jpg', 1),
(26, 9, 'produse_poze/florence_2.jpg', 2),
(27, 9, 'produse_poze/florence_3.jpg', 3),
(28, 10, 'produse_poze/cha_1.jpg', 1),
(29, 10, 'produse_poze/cha_2.jpg', 2),
(30, 10, 'produse_poze/cha_3.jpg', 3),
(31, 11, 'produse_poze/zerm_1.jpg', 1),
(32, 11, 'produse_poze/zerm_2.jpg', 2),
(33, 11, 'produse_poze/zerm_3.jpg', 3),
(34, 12, 'produse_poze/barcelona_1.jpg', 1),
(35, 12, 'produse_poze/barcelona_2.jpg', 2),
(36, 12, 'produse_poze/barcelona_3.jpg', 3),
(37, 13, 'produse_poze/lisabona_1.jpg', 1),
(38, 13, 'produse_poze/lisabona_2.jpg', 2),
(39, 13, 'produse_poze/lisabona_3.jpg', 3),
(40, 14, 'produse_poze/bangkok_1.jpg', 1),
(41, 14, 'produse_poze/bangkok_2.jpg', 2),
(42, 14, 'produse_poze/bangkok_3.jpg', 3),
(43, 15, 'produse_poze/newyork_1.jpg', 1),
(44, 15, 'produse_poze/newyork_2.jpg', 2),
(45, 15, 'produse_poze/newyork_3.jpg', 3),
(46, 16, 'produse_poze/marrakech_1.jpg', 1),
(47, 16, 'produse_poze/marrakech_2.jpg', 2),
(48, 16, 'produse_poze/marrakech_3.jpg', 3),
(49, 17, 'produse_poze/reykjavik_1.jpg', 1),
(50, 17, 'produse_poze/reykjavik_2.jpg', 2),
(51, 17, 'produse_poze/reykjavik_3.jpg', 3),
(52, 18, 'produse_poze/kyoto_1.jpg', 1),
(53, 18, 'produse_poze/kyoto_2.jpg', 2),
(54, 18, 'produse_poze/kyoto_3.jpg', 3);

-- --------------------------------------------------------

--
-- Table structure for table `produse`
--

CREATE TABLE `produse` (
  `id` int(11) NOT NULL,
  `tip_pachet` varchar(255) NOT NULL,
  `plecare` varchar(100) NOT NULL,
  `destinatie` varchar(100) NOT NULL,
  `descriere` text NOT NULL,
  `pret` decimal(10,2) NOT NULL,
  `durata` int(11) NOT NULL COMMENT 'Durata in zile',
  `locuri_disponibile` int(11) NOT NULL DEFAULT 20,
  `data_plecare` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produse`
--

INSERT INTO `produse` (`id`, `tip_pachet`, `plecare`, `destinatie`, `descriere`, `pret`, `durata`, `locuri_disponibile`, `data_plecare`) VALUES
(1, 'City Break', 'București (OTP)', 'Istanbul, Turcia, Istanbul Airport (IST)', 'Istanbul combină istoria milenară cu viața modernă vibrantă. Moschei magnifice, bazaruri tradiționale și palate otomane creează o experiență culturală unică.', '300.00', 4, 20, '2026-03-18'),
(2, 'Litoral', 'București (OTP)', 'Bali, Indonezia\r\nNgurah Rai Intl Airport (DPS)', 'Bali este o destinație exotică, cu plaje spectaculoase, temple impresionante și peisaje naturale uimitoare. Perfectă pentru relaxare și aventuri culturale.', '1200.00', 10, 10, '2026-05-12'),
(3, 'Circuit', 'București (OTP)', 'Santorini, Grecia (7 zile)\r\nSantorini National Airport (JTR)\r\n----->\r\nCreta, Grecia (8 zile)\r\nHerakl', 'Santorini impresionează prin apusurile legendare, casele albe cu acoperiș albastru și peisajele vulcanice dramatice. Ideal pentru vacanțe romantice. Creta combină plaje spectaculoase cu istorie antică și sate pitorești. Perfectă pentru familii și turiști dornici de aventură și relaxare.', '1350.00', 15, 5, '2026-06-02'),
(4, 'Litoral', 'București (OTP)', 'Dubai, UAE\r\nDubai Intl Airport (DXB)', 'Dubai este orașul luxului și al experiențelor moderne: arhitectură futuristă, centre comerciale impresionante și activități unice în deșert.', '1050.00', 5, 3, '2026-04-10'),
(5, 'City Break', 'București (OTP)', 'Abu Dhabi, UAE\r\nAbu Dhabi Intl Airport (AUH)', 'Abu Dhabi combină luxul modern cu tradiția arabă. Moschei elegante, plaje superbe și atracții culturale oferă o experiență completă.', '1000.00', 5, 7, '2026-05-28'),
(6, 'City Break', 'București (OTP)', 'Cairo, Egipt\r\nCairo Intl Airport (CAI)', 'Cairo, capitala Egiptului, este poarta către civilizația antică. Piramidele din Giza și Muzeul Egiptean sunt doar câteva dintre atracțiile impresionante.', '650.00', 6, 8, '2026-03-30'),
(7, 'Litoral', 'București (OTP)', 'Hurghada, Egipt\r\nHurghada Intl Airport (HRG)', 'Hurghada este o destinație de plajă de top, cu ape cristaline și recife de corali. Perfectă pentru relaxare și sporturi acvatice.', '700.00', 7, 1, '2026-04-10'),
(8, 'City Break', 'București (OTP)', 'Paris, Franța\r\nCharles de Gaulle (CDG)', 'Parisul, orașul luminilor, impresionează prin arhitectură iconică, muzee celebre și gastronomie rafinată. Ideal pentru city break romantic sau cultural.', '900.00', 5, 13, '2026-06-05'),
(9, 'City Break', 'București (OTP)', 'Florence, Italia\r\nFlorence Airport, Peretola (FLR)', 'Florence, inima Renașterii, oferă artă, cultură și arhitectură de excepție. Muzee și străzi medievale creează o experiență autentică italiană.', '850.00', 5, 9, '2026-05-18'),
(10, 'Munte', 'București (OTP)', 'Chamonix, Franța\r\nGeneva Airport (GVA)', 'Chamonix, la poalele Mont Blanc, este paradisul iubitorilor de munte și aventură. Pârtii de schi, drumeții și priveliști alpine uimitoare.', '1100.00', 7, 5, '2026-04-15'),
(11, 'Munte', 'București (OTP)', 'Zermatt, Elveția\r\nZurich Airport (ZRH)', 'Zermatt oferă experiențe alpine autentice, cu Matterhorn în fundal. Schi, drumeții și natură pură într-un cadru idilic elvețian.', '1300.00', 6, 20, '2026-05-10'),
(12, 'city break', 'București (OTP)', 'Barcelona, Spania', 'Descoperă energia Barcelonei: arhitectură spectaculoasă, plaje urbane și gastronomie mediteraneană.', '620.00', 4, 25, '2026-04-10'),
(13, 'city break', 'București (OTP)', 'Lisabona, Portugalia', 'Un city break relaxant într-un oraș boem, plin de istorie, priveliști superbe și muzică fado.', '590.00', 4, 22, '2026-05-08'),
(14, 'circuit', 'București (OTP)', 'Bangkok, Thailanda', 'Circuit exotic în inima Asiei, cu temple impresionante, piețe tradiționale și experiențe autentice.', '1890.00', 10, 18, '2026-02-15'),
(15, 'city break', 'București (OTP)', 'New York, SUA', 'Experimentează orașul care nu doarme niciodată: zgârie-nori, shopping și atracții celebre.', '2150.00', 6, 16, '2026-03-20'),
(16, 'circuit', 'București (OTP)', 'Marrakech, Maroc', 'Călătorie culturală într-un oraș exotic, plin de culoare, tradiții și arome orientale.', '980.00', 7, 20, '2026-04-02'),
(17, 'circuit', 'București (OTP)', 'Reykjavik, Islanda', 'Explorare spectaculoasă a naturii islandeze: ghețari, vulcani, cascade și aurora boreală.', '2490.00', 8, 14, '2026-01-25'),
(18, 'circuit', 'București (OTP)', 'Kyoto, Japonia', 'Circuit cultural în Japonia tradițională, cu temple istorice, grădini zen și experiențe autentice.', '2690.00', 9, 15, '2026-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `utilizatori`
--

CREATE TABLE `utilizatori` (
  `id` int(11) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `parola` varchar(255) NOT NULL,
  `data_inregistrare` datetime DEFAULT current_timestamp(),
  `rol` enum('client','agent','admin') NOT NULL DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilizatori`
--

INSERT INTO `utilizatori` (`id`, `nume`, `email`, `parola`, `data_inregistrare`, `rol`) VALUES
(1, 'Simion Louis', 'louissimion58@gmail.com', '$2y$10$SPu1DmSGR2o2QrY9ImUfMeDND33rEQagBseeK1BzvHfSqSMN8oDaS', '2026-01-14 17:14:03', 'client'),
(2, 'admin', 'admincarptravel@gmail.com', '$2y$10$OSxpcMO3o1VZaVI95OOsjOiahOgWKcnjj78AiqFH9ldBFmB04TVjW', '2026-02-13 12:09:32', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comenzi`
--
ALTER TABLE `comenzi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comenzi_utilizator` (`id_utilizator`);

--
-- Indexes for table `comenzi_produse`
--
ALTER TABLE `comenzi_produse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cp_comanda` (`id_comanda`),
  ADD KEY `idx_cp_produs` (`id_produs`);

--
-- Indexes for table `cos_cumparaturi`
--
ALTER TABLE `cos_cumparaturi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cos_utilizator` (`id_utilizator`),
  ADD KEY `fk_cos_produs` (`id_produs`);

--
-- Indexes for table `imagini_produse`
--
ALTER TABLE `imagini_produse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_imagini_produs` (`id_produs`);

--
-- Indexes for table `produse`
--
ALTER TABLE `produse`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `utilizatori`
--
ALTER TABLE `utilizatori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comenzi`
--
ALTER TABLE `comenzi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comenzi_produse`
--
ALTER TABLE `comenzi_produse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cos_cumparaturi`
--
ALTER TABLE `cos_cumparaturi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `imagini_produse`
--
ALTER TABLE `imagini_produse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `produse`
--
ALTER TABLE `produse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `utilizatori`
--
ALTER TABLE `utilizatori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comenzi`
--
ALTER TABLE `comenzi`
  ADD CONSTRAINT `fk_comenzi_utilizator` FOREIGN KEY (`id_utilizator`) REFERENCES `utilizatori` (`id`);

--
-- Constraints for table `comenzi_produse`
--
ALTER TABLE `comenzi_produse`
  ADD CONSTRAINT `fk_cp_comanda` FOREIGN KEY (`id_comanda`) REFERENCES `comenzi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cp_produs` FOREIGN KEY (`id_produs`) REFERENCES `produse` (`id`);

--
-- Constraints for table `cos_cumparaturi`
--
ALTER TABLE `cos_cumparaturi`
  ADD CONSTRAINT `fk_cos_produs` FOREIGN KEY (`id_produs`) REFERENCES `produse` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cos_utilizator` FOREIGN KEY (`id_utilizator`) REFERENCES `utilizatori` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imagini_produse`
--
ALTER TABLE `imagini_produse`
  ADD CONSTRAINT `fk_imagini_produs` FOREIGN KEY (`id_produs`) REFERENCES `produse` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
