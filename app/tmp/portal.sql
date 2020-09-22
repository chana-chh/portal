-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.36-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for portal
DROP DATABASE IF EXISTS `portal`;
CREATE DATABASE IF NOT EXISTS `portal` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `portal`;

-- Dumping structure for table portal.clanci
DROP TABLE IF EXISTS `clanci`;
CREATE TABLE IF NOT EXISTS `clanci` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naslov` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clanak` text COLLATE utf8mb4_unicode_ci,
  `rezime` tinytext COLLATE utf8mb4_unicode_ci,
  `objavljen` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `korisnik_id` int(10) unsigned NOT NULL,
  `kategorija_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `publish_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_clanci_kategorije` (`kategorija_id`),
  CONSTRAINT `FK_clanci_kategorije` FOREIGN KEY (`kategorija_id`) REFERENCES `kategorije` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal.clanci: ~0 rows (approximately)
/*!40000 ALTER TABLE `clanci` DISABLE KEYS */;
/*!40000 ALTER TABLE `clanci` ENABLE KEYS */;

-- Dumping structure for table portal.dokumenti
DROP TABLE IF EXISTS `dokumenti`;
CREATE TABLE IF NOT EXISTS `dokumenti` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clanak_id` int(10) unsigned DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `vrsta` enum('закони','уредбе','правилници','обрасци','линкови') COLLATE utf8mb4_unicode_ci NOT NULL,
  `korisnik_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_dokumenti_korisnici` (`korisnik_id`),
  KEY `FK_dokumenti_clanci` (`clanak_id`),
  CONSTRAINT `FK_dokumenti_clanci` FOREIGN KEY (`clanak_id`) REFERENCES `clanci` (`id`),
  CONSTRAINT `FK_dokumenti_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal.dokumenti: ~0 rows (approximately)
/*!40000 ALTER TABLE `dokumenti` DISABLE KEYS */;
/*!40000 ALTER TABLE `dokumenti` ENABLE KEYS */;

-- Dumping structure for table portal.kategorije
DROP TABLE IF EXISTS `kategorije`;
CREATE TABLE IF NOT EXISTS `kategorije` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naziv` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vrsta` enum('опште','управе','синдикати') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal.kategorije: ~15 rows (approximately)
/*!40000 ALTER TABLE `kategorije` DISABLE KEYS */;
INSERT IGNORE INTO `kategorije` (`id`, `naziv`, `vrsta`) VALUES
	(1, 'Вести', 'опште'),
	(2, 'Саопштења', 'опште'),
	(3, 'Адресар ЈП и установа', 'опште'),
	(4, 'Градска управа за послове органа Града', 'управе'),
	(5, 'Градска управа за јавне приходе и инспекцијске послове', 'управе'),
	(6, 'Градска управа за развој', 'управе'),
	(7, 'Градска управа за друштвене делатности и послове са грађанима', 'управе'),
	(8, 'Градска управа за заједничке послове', 'управе'),
	(9, 'Градско правобранилаштво', 'управе'),
	(10, 'Служба за интерну ревизију', 'управе'),
	(11, 'Служба за буџетску инспекцију', 'управе'),
	(12, 'Служба заштитника грађана', 'управе'),
	(13, 'Самостални синдикат', 'синдикати'),
	(14, 'Синдикат "Независност"', 'синдикати'),
	(15, 'Синдикат "Пера"', 'синдикати');
/*!40000 ALTER TABLE `kategorije` ENABLE KEYS */;

-- Dumping structure for table portal.komentari
DROP TABLE IF EXISTS `komentari`;
CREATE TABLE IF NOT EXISTS `komentari` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_clanka` int(10) unsigned NOT NULL,
  `naslov` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `korisnik` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `korisnik_ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `komentar` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_komentari_clanci` (`id_clanka`),
  CONSTRAINT `FK_komentari_clanci` FOREIGN KEY (`id_clanka`) REFERENCES `clanci` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal.komentari: ~0 rows (approximately)
/*!40000 ALTER TABLE `komentari` DISABLE KEYS */;
/*!40000 ALTER TABLE `komentari` ENABLE KEYS */;

-- Dumping structure for table portal.korisnici
DROP TABLE IF EXISTS `korisnici`;
CREATE TABLE IF NOT EXISTS `korisnici` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prezime` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `korisnicko_ime` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lozinka` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dozvoljene_kategorije` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivo` int(10) unsigned NOT NULL,
  `korisnik_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`korisnicko_ime`),
  KEY `FK_korisnici_korisnici` (`korisnik_id`),
  CONSTRAINT `FK_korisnici_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal.korisnici: ~1 rows (approximately)
/*!40000 ALTER TABLE `korisnici` DISABLE KEYS */;
INSERT IGNORE INTO `korisnici` (`id`, `ime`, `prezime`, `email`, `korisnicko_ime`, `lozinka`, `dozvoljene_kategorije`, `nivo`, `korisnik_id`, `created_at`) VALUES
	(1, 'Admin', '', '', 'admin', '$2y$10$RWD9bVOhe1GlWER7DVKMAukc2/OAwpoAvC/8A.wYOpGtqMFTezQHm', '', 0, 1, '2020-01-08 19:47:03');
/*!40000 ALTER TABLE `korisnici` ENABLE KEYS */;

-- Dumping structure for table portal.logovi
DROP TABLE IF EXISTS `logovi`;
CREATE TABLE IF NOT EXISTS `logovi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `izmene` text COLLATE utf8mb4_unicode_ci,
  `tip` enum('brisanje','dodavanje','izmena','upload') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dodavanje',
  `korisnik_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_logovi_korisnici` (`korisnik_id`),
  CONSTRAINT `FK_logovi_korisnici` FOREIGN KEY (`korisnik_id`) REFERENCES `korisnici` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal.logovi: ~0 rows (approximately)
/*!40000 ALTER TABLE `logovi` DISABLE KEYS */;
/*!40000 ALTER TABLE `logovi` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
