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

-- Dumping structure for table sistem_berkas.area_kerja_petugas
CREATE TABLE IF NOT EXISTS `area_kerja_petugas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.area_kerja_petugas_ukur
CREATE TABLE IF NOT EXISTS `area_kerja_petugas_ukur` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `petugas_ukur_id` bigint unsigned NOT NULL,
  `kecamatan_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `area_kerja_petugas_ukur_petugas_ukur_id_foreign` (`petugas_ukur_id`),
  KEY `area_kerja_petugas_ukur_kecamatan_id_foreign` (`kecamatan_id`),
  CONSTRAINT `area_kerja_petugas_ukur_kecamatan_id_foreign` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `area_kerja_petugas_ukur_petugas_ukur_id_foreign` FOREIGN KEY (`petugas_ukur_id`) REFERENCES `petugas_ukur` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.berkas
CREATE TABLE IF NOT EXISTS `berkas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomer_berkas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pemohon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_alas_hak` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomer_hak` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_permohonan_id` bigint unsigned NOT NULL,
  `kecamatan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomer_wa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `posisi_sekarang_user_id` bigint unsigned NOT NULL,
  `status` enum('Diproses','Selesai','Ditutup','Pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Diproses',
  `waktu_mulai_proses` timestamp NULL DEFAULT NULL,
  `waktu_selesai_proses` timestamp NULL DEFAULT NULL,
  `status_pengiriman` enum('Diterima','Dikirim') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Diterima',
  `pengirim_id` bigint unsigned DEFAULT NULL,
  `penerima_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `petugas_ukur_id` bigint unsigned DEFAULT NULL,
  `tanggal_ukur` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `berkas_nomer_berkas_unique` (`nomer_berkas`),
  KEY `berkas_posisi_sekarang_user_id_foreign` (`posisi_sekarang_user_id`),
  KEY `berkas_jenis_permohonan_id_foreign` (`jenis_permohonan_id`),
  KEY `berkas_pengirim_id_foreign` (`pengirim_id`),
  KEY `berkas_penerima_id_foreign` (`penerima_id`),
  KEY `berkas_petugas_ukur_id_foreign` (`petugas_ukur_id`),
  CONSTRAINT `berkas_jenis_permohonan_id_foreign` FOREIGN KEY (`jenis_permohonan_id`) REFERENCES `jenis_permohonans` (`id`),
  CONSTRAINT `berkas_penerima_id_foreign` FOREIGN KEY (`penerima_id`) REFERENCES `users` (`id`),
  CONSTRAINT `berkas_pengirim_id_foreign` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id`),
  CONSTRAINT `berkas_petugas_ukur_id_foreign` FOREIGN KEY (`petugas_ukur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `berkas_posisi_sekarang_user_id_foreign` FOREIGN KEY (`posisi_sekarang_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7006 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.desas
CREATE TABLE IF NOT EXISTS `desas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kecamatan_id` bigint unsigned NOT NULL,
  `nama_desa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `desas_kecamatan_id_foreign` (`kecamatan_id`),
  CONSTRAINT `desas_kecamatan_id_foreign` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=366 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.jabatans
CREATE TABLE IF NOT EXISTS `jabatans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jabatans_nama_jabatan_unique` (`nama_jabatan`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.jadwal_ukur
CREATE TABLE IF NOT EXISTS `jadwal_ukur` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `berkas_id` bigint unsigned NOT NULL,
  `petugas_ukur_id` bigint unsigned NOT NULL,
  `no_surat_tugas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_rencana_ukur` date DEFAULT NULL,
  `status_proses` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Terjadwal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jadwal_ukur_berkas_id_foreign` (`berkas_id`),
  KEY `jadwal_ukur_petugas_ukur_id_foreign` (`petugas_ukur_id`),
  CONSTRAINT `jadwal_ukur_berkas_id_foreign` FOREIGN KEY (`berkas_id`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_ukur_petugas_ukur_id_foreign` FOREIGN KEY (`petugas_ukur_id`) REFERENCES `petugas_ukur` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.jenis_permohonans
CREATE TABLE IF NOT EXISTS `jenis_permohonans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_permohonan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_timeline_hari` int NOT NULL,
  `memerlukan_ukur` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_permohonans_nama_permohonan_unique` (`nama_permohonan`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.kecamatans
CREATE TABLE IF NOT EXISTS `kecamatans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_kecamatan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.petugas_ukur
CREATE TABLE IF NOT EXISTS `petugas_ukur` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `keahlian` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contoh: Pengukuran, Pemetaan, dll',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `petugas_ukur_user_id_unique` (`user_id`),
  CONSTRAINT `petugas_ukur_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.riwayat_berkas
CREATE TABLE IF NOT EXISTS `riwayat_berkas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `berkas_id` bigint unsigned NOT NULL,
  `dari_user_id` bigint unsigned NOT NULL,
  `ke_user_id` bigint unsigned NOT NULL,
  `waktu_kirim` timestamp NOT NULL,
  `catatan_pengiriman` text COLLATE utf8mb4_unicode_ci,
  `status_penerimaan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `riwayat_berkas_berkas_id_foreign` (`berkas_id`),
  KEY `riwayat_berkas_dari_user_id_foreign` (`dari_user_id`),
  KEY `riwayat_berkas_ke_user_id_foreign` (`ke_user_id`),
  CONSTRAINT `riwayat_berkas_berkas_id_foreign` FOREIGN KEY (`berkas_id`) REFERENCES `berkas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_berkas_dari_user_id_foreign` FOREIGN KEY (`dari_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `riwayat_berkas_ke_user_id_foreign` FOREIGN KEY (`ke_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84930 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.tims
CREATE TABLE IF NOT EXISTS `tims` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_tim` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_sk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_sk` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tims_nama_tim_unique` (`nama_tim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.tim_user
CREATE TABLE IF NOT EXISTS `tim_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tim_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tim_user_tim_id_foreign` (`tim_id`),
  KEY `tim_user_user_id_foreign` (`user_id`),
  CONSTRAINT `tim_user_tim_id_foreign` FOREIGN KEY (`tim_id`) REFERENCES `tims` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tim_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sistem_berkas.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan_id` bigint unsigned DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_jabatan_id_foreign` (`jabatan_id`),
  CONSTRAINT `users_jabatan_id_foreign` FOREIGN KEY (`jabatan_id`) REFERENCES `jabatans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
