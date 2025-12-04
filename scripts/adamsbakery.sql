-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for adamsbakery
CREATE DATABASE IF NOT EXISTS adamsbakery;
USE adamsbakery;

-- Dumping structure for table adamsbakery.admin_users
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.admin_users: ~0 rows (approximately)
INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
	(1, 'adamsbakery', '#G7r!p9vQ2z@Lc4', '2025-09-17 16:22:57');

-- Dumping structure for table adamsbakery.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.categories: ~4 rows (approximately)
INSERT INTO `categories` (`id`, `nama`, `created_at`) VALUES
	(1, 'Roti Manis', '2025-09-17 16:22:57'),
	(2, 'Roti Gurih', '2025-09-17 16:22:57'),
	(3, 'Kue Kering', '2025-09-17 16:22:57'),
	(4, 'Kue Ulang Tahun', '2025-09-17 16:22:57');

-- Dumping structure for table adamsbakery.customer_users
CREATE TABLE IF NOT EXISTS `customer_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_verified` tinyint(1) DEFAULT '0' COMMENT 'Status verifikasi (0=belum, 1=sudah)',
  `otp_code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Kode OTP 6 digit',
  `otp_expires_at` datetime DEFAULT NULL COMMENT 'Waktu kedaluwarsa OTP',
  `otp_attempts` int unsigned DEFAULT '0' COMMENT 'Jumlah percobaan OTP',
  `reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Token unik untuk reset password',
  `reset_expires` datetime DEFAULT NULL COMMENT 'Waktu kadaluarsa token reset',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_customer_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.customer_users: ~4 rows (approximately)
INSERT INTO `customer_users` (`id`, `nama_lengkap`, `email`, `password`, `phone`, `alamat`, `created_at`, `updated_at`, `is_verified`, `otp_code`, `otp_expires_at`, `otp_attempts`, `reset_token`, `reset_expires`) VALUES
	(2, 'Adam F', 'yanto@gmail.com', '$2y$10$o.m9k2sNGxJBAmXMfvVhPeLoR2jan6tIpaIa/i3CCZ3nwJfels6hW', '12345', 'pacul', '2025-09-23 06:53:03', '2025-09-23 06:53:03', 0, NULL, NULL, 0, NULL, NULL),
	(25, 'adamdam', 'nurcahyaputraa@gmail.com', '$2y$10$0cAjyvX6YguL7r9Fwg0HVOVAY1VccQub/ggj0hWiKBMBzZ89pHm1a', '082225348452', 'jc dfg', '2025-11-21 10:05:24', '2025-11-25 08:21:24', 1, NULL, NULL, 0, NULL, NULL),
	(32, 'kicik', 'myname101213@gmail.com', '$2y$10$xpw44YuC1Wmu.QEVPNFb7uj8mW2vnaJqJ1n.5tSQ0egzjCvFm6Hqa', '082225348452', 'jl.pwt', '2025-11-27 13:04:51', '2025-11-27 13:05:21', 1, NULL, NULL, 0, NULL, NULL),
	(33, 'kicik', 'juominomiojo@gmail.com', '$2y$10$XjUiqOQee.72hun9GQ.CnuBsnkKHuR136lCQvdsVK.wHDxU9brskO', '081911465619', 'jl.pwt', '2025-11-27 13:07:01', '2025-11-27 13:07:28', 1, NULL, NULL, 0, NULL, NULL);

-- Dumping structure for table adamsbakery.custom_order_quotes
CREATE TABLE IF NOT EXISTS `custom_order_quotes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kontak_id` int NOT NULL,
  `quoted_price` decimal(10,2) NOT NULL,
  `quote_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `valid_until` date DEFAULT NULL,
  `status` enum('pending','accepted','rejected','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `kontak_id` (`kontak_id`),
  CONSTRAINT `custom_order_quotes_ibfk_1` FOREIGN KEY (`kontak_id`) REFERENCES `kontak` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.custom_order_quotes: ~0 rows (approximately)

-- Dumping structure for table adamsbakery.kontak
CREATE TABLE IF NOT EXISTS `kontak` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pesan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kontak` enum('ulasan','custom_order','pertanyaan') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'ulasan',
  `custom_order_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `budget_range` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `jumlah_porsi` int DEFAULT NULL,
  `status` enum('pending','reviewed','quoted','confirmed','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_reply` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Jawaban admin',
  PRIMARY KEY (`id`),
  KEY `idx_kontak_jenis` (`jenis_kontak`),
  KEY `idx_kontak_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.kontak: ~3 rows (approximately)
INSERT INTO `kontak` (`id`, `nama`, `email`, `pesan`, `jenis_kontak`, `custom_order_details`, `budget_range`, `event_date`, `jumlah_porsi`, `status`, `created_at`, `admin_reply`) VALUES
	(1, 'Adam', 'yanto@gmail.com', 'G wuenak', 'ulasan', NULL, NULL, NULL, NULL, 'pending', '2025-09-18 11:26:56', NULL),
	(16, 'Donat Coklat', 'gusticaesar17@gmail.com', 'szhj', 'custom_order', 'szdh', '500rb - 1jt', '2025-11-24', 55, 'completed', '2025-11-20 02:21:50', NULL),
	(17, 'roti abon', 'gusticaesar17@gmail.com', 'rotinya ditipisin', 'custom_order', 'abonnya dibanyakin', '500rb - 1jt', '2025-11-28', 50, 'confirmed', '2025-11-21 10:16:00', NULL),
	(18, 'Donat Coklat', 'nurcahyaputraa@gmail.com', 'rxtiryi', 'custom_order', '5rurr', '2jt - 5jt', '2025-11-30', 88, 'confirmed', '2025-11-27 13:28:03', NULL);

-- Dumping structure for table adamsbakery.packages
CREATE TABLE IF NOT EXISTS `packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `harga` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'placeholder.jpg',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.packages: ~7 rows (approximately)
INSERT INTO `packages` (`id`, `nama`, `deskripsi`, `harga`, `created_at`, `image`) VALUES
	(1, 'Paket Pagi Sehat', 'Roti tawar gandum + croissant + susu segar untuk memulai hari', 35000.00, '2025-09-17 16:22:57', '1763472280_paket roti cokelat X keju.jpg'),
	(2, 'Paket Ulang Tahun Kecil', 'Kue tart vanilla + 6 donat mix + lilin ulang tahun', 200000.00, '2025-09-17 16:22:57', '1763472326_pket roti besar.jpg'),
	(3, 'Paket Ulang Tahun Besar', 'Kue tart coklat + 12 donat mix + black forest mini + dekorasi', 350000.00, '2025-09-17 16:22:57', '1763472308_paket super besar.jpg'),
	(4, 'Paket Kue Kering Lebaran', 'Nastar + kastengel + putri salju dalam kemasan cantik', 120000.00, '2025-09-17 16:22:57', '1763472245_paket xtra big.jpg'),
	(5, 'Paket Sarapan Keluarga', '2 roti tawar + 4 croissant + selai strawberry', 65000.00, '2025-09-17 16:22:57', '1763472288_paket xtra big.jpg'),
	(6, 'Paket Kecil', 'Paket Roti Unyil dengan 4 varian rasa yang bisa dipilih sesuai keinginan! ', 10000.00, '2025-09-22 06:41:24', '1763472229_paket roti cokelat X keju.jpg'),
	(7, 'Paket Sedang', 'Paket Sedang dengan 4 varian rasa sesuai keinginanmu!', 15000.00, '2025-09-22 06:44:01', '1763472299_paket roti cokelat X keju.jpg');

-- Dumping structure for table adamsbakery.pertanyaan_umum
CREATE TABLE IF NOT EXISTS `pertanyaan_umum` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `pertanyaan` text COLLATE utf8mb4_general_ci NOT NULL,
  `admin_reply` text COLLATE utf8mb4_general_ci COMMENT 'Jawaban admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.pertanyaan_umum: ~2 rows (approximately)
INSERT INTO `pertanyaan_umum` (`id`, `nama`, `email`, `pertanyaan`, `admin_reply`, `created_at`) VALUES
	(7, 'Donat Coklat', 'nurcahyaputraa@gmail.com', 'halo', 'hai', '2025-11-08 20:37:10'),
	(9, 'roti hot dawg', 'gusticaesar17@gmail.com', 'ntaps ndan', 'siap ndan', '2025-11-21 17:16:58'),
	(10, 'roti hot dawg', 'nurcahyaputraa@gmail.com', 'joizshh', 'gfhndh', '2025-11-27 20:26:52');

-- Dumping structure for table adamsbakery.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `kategori` enum('Roti Manis','Roti Gurih','Kue Kering','Kue Ulang Tahun') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'placeholder.jpg',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.products: ~11 rows (approximately)
INSERT INTO `products` (`id`, `category_id`, `nama`, `harga`, `kategori`, `deskripsi`, `image`, `created_at`, `updated_at`) VALUES
	(3, 1, 'Roti Cokelat', 18000.00, 'Roti Manis', 'roti didalemnya cokelat', '1763472054_roti bulat coklat.jpg', '2025-09-17 16:22:57', '2025-11-18 13:20:54'),
	(4, 1, 'Donat Coklat', 12000.00, 'Roti Manis', 'Donat lembut dengan glazur coklat manis yang menggoda', '1763472010_donat coklat.jpg', '2025-09-17 16:22:57', '2025-11-18 13:20:10'),
	(5, 1, 'Donat Strawberry', 12000.00, 'Roti Manis', 'Donat dengan topping strawberry segar dan manis', '1763472018_donat stroberi.jpg', '2025-09-17 16:22:57', '2025-11-18 13:20:18'),
	(6, 3, 'Kue Nastar', 45000.00, 'Kue Kering', 'Kue kering tradisional dengan isian nanas manis (per toples)', '1763471956_paket nastar.jpg', '2025-09-17 16:22:57', '2025-11-18 13:19:16'),
	(7, 1, 'roti keju', 50000.00, 'Roti Manis', 'Kue isi keju', '1763472066_roti keju.jpg', '2025-09-17 16:22:57', '2025-11-18 13:21:06'),
	(8, 1, 'Kue Donat Coklat', 150000.00, 'Roti Manis', 'Kue donat cokelat', '1763472032_donat coklat.jpg', '2025-09-17 16:22:57', '2025-11-18 13:20:32'),
	(9, 2, 'Kue Sosis Gurih', 14000.00, 'Roti Gurih', 'Kue Sosis Gurih dengan Rasa Gurih', '1763471976_hot dawg.jpg', '2025-09-17 16:22:57', '2025-11-18 13:19:36'),
	(15, 2, 'pizza', 8000.00, 'Roti Gurih', 'jafjk', '1763471984_pizza unik.jpg', '2025-11-02 04:53:28', '2025-11-18 13:19:44'),
	(16, 1, 'roti pukis', 50000.00, 'Roti Manis', 'joz', '1763472080_pukis coklat keju.jpg', '2025-11-02 04:56:06', '2025-11-18 13:21:20'),
	(17, 2, 'roti abon', 11111.00, 'Roti Gurih', 'dg', '1763471999_roti abon.jpg', '2025-11-02 05:01:52', '2025-11-18 13:19:59'),
	(18, 4, 'roti iwir iwir', 6000.00, 'Kue Ulang Tahun', 'iwir iwir', '1763649345_roti bulet coklat.jpg', '2025-11-20 14:35:45', '2025-11-20 14:35:45');

-- Dumping structure for table adamsbakery.promos
CREATE TABLE IF NOT EXISTS `promos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.promos: ~0 rows (approximately)
INSERT INTO `promos` (`id`, `title`, `description`, `created_at`) VALUES
	(1, 'Gratis Ongkir', 'Gratis coii', '2025-09-23 16:24:29');

-- Dumping structure for table adamsbakery.reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transaction_id` int NOT NULL,
  `product_id` int DEFAULT NULL COMMENT 'ID produk jika review untuk produk',
  `package_id` int DEFAULT NULL COMMENT 'ID paket jika review untuk paket',
  `item_type` enum('product','package') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'product' COMMENT 'Tipe item: product atau package',
  `nama_reviewer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rating` int NOT NULL,
  `review_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_review_transaction` (`transaction_id`),
  KEY `idx_review_product` (`product_id`),
  KEY `idx_review_package` (`package_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.reviews: ~2 rows (approximately)
INSERT INTO `reviews` (`id`, `transaction_id`, `product_id`, `package_id`, `item_type`, `nama_reviewer`, `rating`, `review_text`, `created_at`) VALUES
	(10, 36, NULL, NULL, 'product', 'adam', 3, 'regegas', '2025-11-02 03:22:55'),
	(21, 50, 16, NULL, 'product', 'adamdam', 5, 'ntaps', '2025-11-21 10:12:04'),
	(22, 52, NULL, 1, 'package', 'adamdam', 1, 'juzjuz', '2025-11-24 14:16:12'),
	(23, 54, 18, NULL, 'product', 'kicik', 3, '', '2025-11-27 13:26:15'),
	(24, 54, NULL, 2, 'package', 'kicik', 1, '', '2025-11-27 13:26:24');

-- Dumping structure for table adamsbakery.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_pembeli` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('transfer_bank') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'transfer_bank',
  `bank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transfer_amount` decimal(10,2) DEFAULT NULL,
  `transfer_proof` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `customer_id` int DEFAULT NULL,
  `bukti_pembayaran` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'bukti pembayaran',
  PRIMARY KEY (`id`),
  KEY `idx_transaction_customer` (`customer_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.transactions: ~5 rows (approximately)
INSERT INTO `transactions` (`id`, `nama_pembeli`, `email`, `phone`, `alamat`, `total_amount`, `payment_method`, `bank_name`, `account_name`, `account_number`, `transfer_amount`, `transfer_proof`, `status`, `created_at`, `updated_at`, `customer_id`, `bukti_pembayaran`) VALUES
	(36, 'adam', 'nurcahyaputraa@gmail.com', '082225348452', 'eF', 56000.00, 'transfer_bank', 'Mandiri', 'SDF', 'sdg', 56000.00, NULL, 'confirmed', '2025-11-02 03:22:15', '2025-11-02 03:22:25', NULL, 'bukti_1762053735.jpg'),
	(50, 'adamdam', 'nurcahyaputraa@gmail.com', '082225348452', 'jc dfg', 50000.00, 'transfer_bank', 'Mandiri', 'Adam Bakery', 'erhserh', 50000.00, NULL, 'confirmed', '2025-11-21 10:08:59', '2025-11-21 10:10:26', 25, 'bukti_1763719739_05c97d2e1f08d395.jpg'),
	(51, 'adamdam', 'nurcahyaputraa@gmail.com', '082225348452', 'jc dfg', 350000.00, 'transfer_bank', 'Mandiri', '0', 'erhserh', 350000.00, NULL, 'confirmed', '2025-11-24 12:38:39', '2025-11-24 12:41:22', 25, 'file_1763987919_5c2e1e1fe57a542b.jpg'),
	(52, 'adamdam', 'nurcahyaputraa@gmail.com', '082225348452', 'jc dfg', 35000.00, 'transfer_bank', 'Lainnya', '0', '-', 35000.00, NULL, 'confirmed', '2025-11-24 13:59:01', '2025-11-24 14:01:56', 25, 'file_1763992741_0af403540d7b244c.jpg'),
	(53, 'adamdam', 'nurcahyaputraa@gmail.com', '082225348452', 'jc dfg', 67111.00, 'transfer_bank', 'Mandiri', '0', '1390088899913', 67111.00, NULL, 'confirmed', '2025-11-25 09:02:16', '2025-11-25 09:02:39', 25, 'file_1764061336_29628cfbf75b5611.jpg'),
	(54, 'kicik', 'juominomiojo@gmail.com', '081911465619', 'jl.pwt', 206000.00, 'transfer_bank', 'Mandiri', '0', 'wgwagwag', 206000.00, NULL, 'confirmed', '2025-11-27 13:25:16', '2025-11-27 13:25:38', 33, 'file_1764249916_44950f42a1808c02.png');

-- Dumping structure for table adamsbakery.transaction_items
CREATE TABLE IF NOT EXISTS `transaction_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transaction_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `package_id` int DEFAULT NULL,
  `item_type` enum('product','package') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_package_id` (`package_id`),
  CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaction_items_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table adamsbakery.transaction_items: ~6 rows (approximately)
INSERT INTO `transaction_items` (`id`, `transaction_id`, `product_id`, `package_id`, `item_type`, `quantity`, `price`) VALUES
	(37, 36, NULL, NULL, 'product', 1, 56000.00),
	(54, 50, 16, NULL, 'product', 1, 50000.00),
	(55, 51, NULL, 3, 'package', 1, 350000.00),
	(56, 52, NULL, 1, 'package', 1, 35000.00),
	(57, 53, 18, NULL, 'product', 1, 6000.00),
	(58, 53, 17, NULL, 'product', 1, 11111.00),
	(59, 53, 16, NULL, 'product', 1, 50000.00),
	(60, 54, 18, NULL, 'product', 1, 6000.00),
	(61, 54, NULL, 2, 'package', 1, 200000.00);

-- ========================================
-- Migration: Add admin notification tracking
-- ========================================
-- Add columns to transactions table for admin notification tracking
ALTER TABLE `transactions` 
ADD COLUMN `admin_notified_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu notifikasi admin dikirim',
ADD COLUMN `admin_notified_status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending' COMMENT 'Status pengiriman notifikasi ke admin (pending/sent/failed)';

-- Add columns to kontak table for admin notification tracking
ALTER TABLE `kontak` 
ADD COLUMN `admin_notified_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu notifikasi admin dikirim',
ADD COLUMN `admin_notified_status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending' COMMENT 'Status pengiriman notifikasi ke admin (pending/sent/failed)';

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
