-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for sistem_berkas
CREATE DATABASE IF NOT EXISTS `sistem_berkas` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `sistem_berkas`;

-- Dumping structure for table sistem_berkas.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table sistem_berkas.migrations: ~30 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
	(3, '2019_08_19_000000_create_failed_jobs_table', 1),
	(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(5, '2025_09_06_071903_create_berkas_table', 1),
	(6, '2025_09_06_071912_create_riwayat_berkas_table', 1),
	(7, '2025_09_06_085638_add_approval_columns_to_users_table', 1),
	(8, '2025_09_06_095316_create_kecamatans_table', 1),
	(9, '2025_09_06_095327_create_desas_table', 1),
	(10, '2025_09_07_065027_create_jenis_permohonans_table', 1),
	(11, '2025_09_07_065217_modify_jenis_permohonan_in_berkas_table', 1),
	(12, '2025_09_07_122720_create_notifications_table', 1),
	(13, '2025_09_07_130502_create_jabatans_table', 1),
	(14, '2025_09_07_130604_modify_role_to_jabatan_in_users_table', 1),
	(15, '2025_09_07_172915_add_transfer_status_to_berkas_table', 1),
	(16, '2025_09_12_121311_add_status_penerimaan_to_riwayat_berkas_table', 2),
	(17, '2025_09_13_145847_add_waktu_proses_to_berkas_table', 3),
	(20, '2025_09_15_200034_create_petugas_ukur_table', 4),
	(21, '2025_09_15_200053_create_jadwal_ukur_table', 4),
	(22, '2025_09_17_003835_create_area_kerja_petugas_ukur_table', 4),
	(23, '2025_09_18_211500_add_petugas_ukur_to_berkas_table', 5),
	(24, '2025_09_18_211638_create_area_kerja_petugas_table', 5),
	(25, '2025_09_26_000018_add_memerlukan_ukur_to_jenis_permohonans_table', 6),
	(27, '2025_12_27_093745_add_soft_deletes_to_users_table', 7),
	(33, '2025_10_13_000001_create_tims_table', 8),
	(34, '2026_01_04_224807_create_penerima_kuasas_table', 9),
	(37, '2026_01_04_224859_add_penerima_kuasa_id_to_berkas_table', 10),
	(38, '2026_01_05_100701_create_wa_templates_table', 11),
	(40, '2025_11_14_014617_create_wa_logs_table', 12),
	(41, '2026_01_06_000001_create_wa_placeholders_table', 13);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
