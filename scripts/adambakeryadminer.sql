-- Adminer 4.8.1 MySQL 10.11.6-MariaDB-0+deb12u1 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

USE `adamsbakery`;

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1,	'adamsbakery',	'#G7r!p9vQ2z@Lc4',	'2025-09-17 16:22:57');

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`id`, `nama`, `created_at`) VALUES
(1,	'Roti Manis',	'2025-09-17 16:22:57'),
(2,	'Roti Gurih',	'2025-09-17 16:22:57'),
(3,	'Kue Kering',	'2025-09-17 16:22:57'),
(4,	'Kue Ulang Tahun',	'2025-09-17 16:22:57');

DROP TABLE IF EXISTS `customer_users`;
CREATE TABLE `customer_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0 COMMENT 'Status verifikasi (0=belum, 1=sudah)',
  `otp_code` varchar(6) DEFAULT NULL COMMENT 'Kode OTP 6 digit',
  `otp_expires_at` datetime DEFAULT NULL COMMENT 'Waktu kedaluwarsa OTP',
  `otp_attempts` int(10) unsigned DEFAULT 0 COMMENT 'Jumlah percobaan OTP',
  `reset_token` varchar(255) DEFAULT NULL COMMENT 'Token unik untuk reset password',
  `reset_expires` datetime DEFAULT NULL COMMENT 'Waktu kadaluarsa token reset',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_customer_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `customer_users` (`id`, `nama_lengkap`, `email`, `password`, `phone`, `alamat`, `created_at`, `updated_at`, `is_verified`, `otp_code`, `otp_expires_at`, `otp_attempts`, `reset_token`, `reset_expires`) VALUES
(2,	'Adam F',	'yanto@gmail.com',	'$2y$10$o.m9k2sNGxJBAmXMfvVhPeLoR2jan6tIpaIa/i3CCZ3nwJfels6hW',	'12345',	'pacul',	'2025-09-23 06:53:03',	'2025-09-23 06:53:03',	0,	NULL,	NULL,	0,	NULL,	NULL),
(25,	'adamdam',	'nurcahyaputraa@gmail.com',	'$2y$12$dhE/lfxHZeu3BKnhJH.4peOJx.6nAwMOM7xbB.P8or75.hy5X3qsa',	'082225348452',	'jc dfg',	'2025-11-21 10:05:24',	'2025-12-07 00:28:56',	1,	NULL,	NULL,	0,	NULL,	NULL),
(32,	'kicik',	'myname101213@gmail.com',	'$2y$10$xpw44YuC1Wmu.QEVPNFb7uj8mW2vnaJqJ1n.5tSQ0egzjCvFm6Hqa',	'082225348452',	'jl.pwt',	'2025-11-27 13:04:51',	'2025-11-27 13:05:21',	1,	NULL,	NULL,	0,	NULL,	NULL),
(33,	'kicik',	'juominomiojo@gmail.com',	'$2y$10$XjUiqOQee.72hun9GQ.CnuBsnkKHuR136lCQvdsVK.wHDxU9brskO',	'081911465619',	'jl.pwt',	'2025-11-27 13:07:01',	'2025-11-27 13:07:28',	1,	NULL,	NULL,	0,	NULL,	NULL),
(35,	'Tegar',	'trex50990@gmail.com',	'$2y$12$0bZ9v7buFDCIFSjaS05Y5e7ewBWTvCUixDFpJsSM6jnzkTdi8um0C',	'082225348452',	'BAHENOL BAHLIL ETANOL',	'2025-12-05 10:46:44',	'2025-12-05 10:47:12',	1,	NULL,	NULL,	0,	NULL,	NULL),
(36,	'Farrel',	'farrelking2@gmail.com',	'$2y$12$fGPKT0v/yy/A2qzQ0o09g.jdxAKxxWZg8as4NBWV10cA0TsjbAIwS',	'085175393742',	'yeyeye',	'2025-12-05 17:19:59',	'2025-12-05 17:20:41',	1,	NULL,	NULL,	0,	NULL,	NULL),
(37,	'jujuomino',	'smsgjcore838@gmail.com',	'$2y$12$BSUKQo7bjkV4rHk8P4Jpsu7NdRfmewEG5JLMvE7Y8Gt.PmZcPYYKe',	'081911465619',	'jl.pwt',	'2025-12-07 00:42:53',	'2025-12-07 00:42:53',	0,	'286972',	'2025-12-07 00:47:53',	0,	NULL,	NULL),
(38,	'index',	'indexphp@gmail.com',	'$2y$12$j9BBe/gbyXmT9FyUo23zqeezAuFqVKlHXO7slWm9cFVtzJRhjYzQq',	'082211445566',	'bumi',	'2025-12-07 06:51:03',	'2025-12-07 06:51:21',	0,	'343466',	'2025-12-07 06:56:03',	1,	NULL,	NULL),
(39,	'kepo banget dengan no wa',	'indexphp1@gmail.com',	'$2y$12$0eYOHM6bTAG4k7EJaRvn6.Q7mcVXwhWbO294f0G7ig2TqqDGycoai',	'082211445566',	'KEPOO BANGETTTT',	'2025-12-07 06:56:22',	'2025-12-07 06:56:22',	0,	'935414',	'2025-12-07 07:01:22',	0,	NULL,	NULL),
(40,	'KENAPAA KEPO BANGETT??',	'indexphp2@gmail.com',	'$2y$12$3JON/K7H4LbtavMRLUit0emH81QB96qSlAFLk2/BElz7vQZjExNVi',	'082211445566',	'GA BOLEH KEPO',	'2025-12-07 06:57:42',	'2025-12-07 06:57:42',	0,	'894966',	'2025-12-07 07:02:42',	0,	NULL,	NULL);

DROP TABLE IF EXISTS `kontak`;
CREATE TABLE `kontak` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `jenis_kontak` enum('ulasan','custom_order','pertanyaan') DEFAULT 'ulasan',
  `custom_order_details` text DEFAULT NULL,
  `budget_range` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `jumlah_porsi` int(11) DEFAULT NULL,
  `status` enum('pending','reviewed','quoted','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_reply` text DEFAULT NULL COMMENT 'Jawaban admin',
  `admin_notified_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu notifikasi admin dikirim',
  `admin_notified_status` enum('pending','sent','failed') DEFAULT 'pending' COMMENT 'Status pengiriman notifikasi ke admin (pending/sent/failed)',
  PRIMARY KEY (`id`),
  KEY `idx_kontak_jenis` (`jenis_kontak`),
  KEY `idx_kontak_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kontak` (`id`, `nama`, `email`, `pesan`, `jenis_kontak`, `custom_order_details`, `budget_range`, `event_date`, `jumlah_porsi`, `status`, `created_at`, `admin_reply`, `admin_notified_at`, `admin_notified_status`) VALUES
(1,	'Adam',	'yanto@gmail.com',	'G wuenak',	'ulasan',	NULL,	NULL,	NULL,	NULL,	'pending',	'2025-09-18 11:26:56',	NULL,	NULL,	'pending'),
(16,	'Donat Coklat',	'gusticaesar17@gmail.com',	'szhj',	'custom_order',	'szdh',	'500rb - 1jt',	'2025-11-24',	55,	'completed',	'2025-11-20 02:21:50',	NULL,	NULL,	'pending'),
(17,	'roti abon',	'gusticaesar17@gmail.com',	'rotinya ditipisin',	'custom_order',	'abonnya dibanyakin',	'500rb - 1jt',	'2025-11-28',	50,	'confirmed',	'2025-11-21 10:16:00',	NULL,	NULL,	'pending'),
(18,	'Donat Coklat',	'nurcahyaputraa@gmail.com',	'rxtiryi',	'custom_order',	'5rurr',	'2jt - 5jt',	'2025-11-30',	88,	'confirmed',	'2025-11-27 13:28:03',	NULL,	NULL,	'pending'),
(19,	'Farrel',	'farrelking2@gmail.com',	'mimimimi',	'custom_order',	'woilah cik test',	'> 5jt',	'2025-12-10',	100,	'confirmed',	'2025-12-04 08:18:23',	NULL,	'2025-12-04 08:18:23',	'sent'),
(20,	'gustiwir',	'nurcahyaputraa@gmail.com',	'ngiwir ngiwir',	'custom_order',	'rasanya harus ngiwir buangett',	'> 5jt',	'2025-12-11',	800,	'confirmed',	'2025-12-07 00:26:26',	NULL,	'2025-12-07 00:26:27',	'sent');


DROP TABLE IF EXISTS `custom_order_quotes`;
CREATE TABLE `custom_order_quotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kontak_id` int(11) NOT NULL,
  `quoted_price` decimal(10,2) NOT NULL,
  `quote_details` text DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `status` enum('pending','accepted','rejected','expired') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `kontak_id` (`kontak_id`),
  CONSTRAINT `custom_order_quotes_ibfk_1` FOREIGN KEY (`kontak_id`) REFERENCES `kontak` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `packages`;
CREATE TABLE `packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT 'placeholder.jpg',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `packages` (`id`, `nama`, `deskripsi`, `harga`, `created_at`, `image`) VALUES
(1,	'Paket Pagi Sehat',	'Roti tawar gandum + croissant + susu segar untuk memulai hari',	35000.00,	'2025-09-17 16:22:57',	'1763472280_paket roti cokelat X keju.jpg'),
(2,	'Paket Ulang Tahun Kecil',	'Kue tart vanilla + 6 donat mix + lilin ulang tahun',	200000.00,	'2025-09-17 16:22:57',	'1763472326_pket roti besar.jpg'),
(3,	'Paket Ulang Tahun Besar',	'Kue tart coklat + 12 donat mix + black forest mini + dekorasi',	350000.00,	'2025-09-17 16:22:57',	'1763472308_paket super besar.jpg'),
(4,	'Paket Kue Kering Lebaran',	'Nastar + kastengel + putri salju dalam kemasan cantik',	120000.00,	'2025-09-17 16:22:57',	'1763472245_paket xtra big.jpg'),
(5,	'Paket Sarapan Keluarga',	'2 roti tawar + 4 croissant + selai strawberry',	65000.00,	'2025-09-17 16:22:57',	'1763472288_paket xtra big.jpg'),
(6,	'Paket Kecil',	'Paket Roti Unyil dengan 4 varian rasa yang bisa dipilih sesuai keinginan! ',	10000.00,	'2025-09-22 06:41:24',	'1763472229_paket roti cokelat X keju.jpg'),
(7,	'Paket Sedang',	'Paket Sedang dengan 4 varian rasa sesuai keinginanmu!',	15000.00,	'2025-09-22 06:44:01',	'1763472299_paket roti cokelat X keju.jpg');

DROP TABLE IF EXISTS `pertanyaan_umum`;
CREATE TABLE `pertanyaan_umum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pertanyaan` text NOT NULL,
  `admin_reply` text DEFAULT NULL COMMENT 'Jawaban admin',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pertanyaan_umum` (`id`, `nama`, `email`, `pertanyaan`, `admin_reply`, `created_at`) VALUES
(7,	'Donat Coklat',	'nurcahyaputraa@gmail.com',	'halo',	'hai',	'2025-11-08 20:37:10'),
(9,	'roti hot dawg',	'gusticaesar17@gmail.com',	'ntaps ndan',	'siap ndan',	'2025-11-21 17:16:58'),
(10,	'roti hot dawg',	'nurcahyaputraa@gmail.com',	'joizshh',	'gfhndh',	'2025-11-27 20:26:52'),
(11,	'gustiwir',	'nurcahyaputraa@gmail.com',	'rasanya enak cui',	'jelazz wir',	'2025-12-07 00:27:30');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `kategori` enum('Roti Manis','Roti Gurih','Kue Kering','Kue Ulang Tahun') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'placeholder.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` (`id`, `category_id`, `nama`, `harga`, `kategori`, `deskripsi`, `image`, `created_at`, `updated_at`) VALUES
(3,	1,	'Roti Cokelat',	18000.00,	'Roti Manis',	'roti didalemnya cokelat',	'1763472054_roti bulat coklat.jpg',	'2025-09-17 16:22:57',	'2025-11-18 13:20:54'),
(4,	1,	'Donat Coklat',	12000.00,	'Roti Manis',	'Donat lembut dengan glazur coklat manis yang menggoda',	'1763472010_donat coklat.jpg',	'2025-09-17 16:22:57',	'2025-11-18 13:20:10'),
(5,	1,	'Donat Strawberry',	12000.00,	'Roti Manis',	'Donat dengan topping strawberry segar dan manis',	'1763472018_donat stroberi.jpg',	'2025-09-17 16:22:57',	'2025-11-18 13:20:18'),
(6,	3,	'Kue Nastarr',	45000.00,	'Kue Kering',	'Kue kering tradisional dengan isian nanas manis (per toples)',	'1763471956_paket nastar.jpg',	'2025-09-17 16:22:57',	'2025-12-05 19:06:04'),
(8,	1,	'Kue Donat Coklat',	150000.00,	'Roti Manis',	'Kue donat cokelat',	'1763472032_donat coklat.jpg',	'2025-09-17 16:22:57',	'2025-11-18 13:20:32'),
(9,	2,	'Kue Sosis Gurih',	14000.00,	'Roti Gurih',	'Kue Sosis Gurih dengan Rasa Gurih',	'1763471976_hot dawg.jpg',	'2025-09-17 16:22:57',	'2025-11-18 13:19:36'),
(15,	2,	'pizza',	8000.00,	'Roti Gurih',	'jafjk',	'1763471984_pizza unik.jpg',	'2025-11-02 04:53:28',	'2025-11-18 13:19:44'),
(16,	1,	'roti pukis',	50000.00,	'Roti Manis',	'joz',	'1763472080_pukis coklat keju.jpg',	'2025-11-02 04:56:06',	'2025-11-18 13:21:20'),
(17,	2,	'roti abon',	11111.00,	'Roti Gurih',	'dg',	'1763471999_roti abon.jpg',	'2025-11-02 05:01:52',	'2025-11-18 13:19:59'),
(18,	4,	'roti iwir iwir',	6000.00,	'Kue Ulang Tahun',	'iwir iwir',	'1763649345_roti bulet coklat.jpg',	'2025-11-20 14:35:45',	'2025-11-20 14:35:45'),
(20,	4,	'uwur uwur',	15000.00,	'Kue Ulang Tahun',	'roti rasanya nguwur banget',	'1765067940_6934cca4d7a01.png',	'2025-12-07 00:39:00',	'2025-12-07 00:40:06');

DROP TABLE IF EXISTS `promos`;
CREATE TABLE `promos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `promos` (`id`, `title`, `description`, `created_at`) VALUES
(1,	'Gratis Ongkir',	'Gratis coii',	'2025-09-23 16:24:29');

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pembeli` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('transfer_bank') DEFAULT 'transfer_bank',
  `bank_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `transfer_amount` decimal(10,2) DEFAULT NULL,
  `transfer_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `customer_id` int(11) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL COMMENT 'bukti pembayaran',
  `admin_notified_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu notifikasi admin dikirim',
  `admin_notified_status` enum('pending','sent','failed') DEFAULT 'pending' COMMENT 'Status pengiriman notifikasi ke admin (pending/sent/failed)',
  `admin_notifications_disabled` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_transaction_customer` (`customer_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `transactions` (`id`, `nama_pembeli`, `email`, `phone`, `alamat`, `total_amount`, `payment_method`, `bank_name`, `account_name`, `account_number`, `transfer_amount`, `transfer_proof`, `status`, `created_at`, `updated_at`, `customer_id`, `bukti_pembayaran`, `admin_notified_at`, `admin_notified_status`, `admin_notifications_disabled`) VALUES
(36,	'adam',	'nurcahyaputraa@gmail.com',	'082225348452',	'eF',	56000.00,	'transfer_bank',	'Mandiri',	'SDF',	'sdg',	56000.00,	NULL,	'confirmed',	'2025-11-02 03:22:15',	'2025-11-02 03:22:25',	NULL,	'bukti_1762053735.jpg',	NULL,	'pending',	0),
(50,	'adamdam',	'nurcahyaputraa@gmail.com',	'082225348452',	'jc dfg',	50000.00,	'transfer_bank',	'Mandiri',	'Adam Bakery',	'erhserh',	50000.00,	NULL,	'confirmed',	'2025-11-21 10:08:59',	'2025-11-21 10:10:26',	25,	'bukti_1763719739_05c97d2e1f08d395.jpg',	NULL,	'pending',	0),
(51,	'adamdam',	'nurcahyaputraa@gmail.com',	'082225348452',	'jc dfg',	350000.00,	'transfer_bank',	'Mandiri',	'0',	'erhserh',	350000.00,	NULL,	'confirmed',	'2025-11-24 12:38:39',	'2025-11-24 12:41:22',	25,	'file_1763987919_5c2e1e1fe57a542b.jpg',	NULL,	'pending',	0),
(52,	'adamdam',	'nurcahyaputraa@gmail.com',	'082225348452',	'jc dfg',	35000.00,	'transfer_bank',	'Lainnya',	'0',	'-',	35000.00,	NULL,	'confirmed',	'2025-11-24 13:59:01',	'2025-11-24 14:01:56',	25,	'file_1763992741_0af403540d7b244c.jpg',	NULL,	'pending',	0),
(53,	'adamdam',	'nurcahyaputraa@gmail.com',	'082225348452',	'jc dfg',	67111.00,	'transfer_bank',	'Mandiri',	'0',	'1390088899913',	67111.00,	NULL,	'confirmed',	'2025-11-25 09:02:16',	'2025-11-25 09:02:39',	25,	'file_1764061336_29628cfbf75b5611.jpg',	NULL,	'pending',	0),
(54,	'kicik',	'juominomiojo@gmail.com',	'081911465619',	'jl.pwt',	206000.00,	'transfer_bank',	'Mandiri',	'0',	'wgwagwag',	206000.00,	NULL,	'confirmed',	'2025-11-27 13:25:16',	'2025-11-27 13:25:38',	33,	'file_1764249916_44950f42a1808c02.png',	NULL,	'pending',	0),
(55,	'Farrel',	'farrelking2@gmail.com',	'085175393742',	'BAHENOL BAHLIL ETANOL',	35000.00,	'transfer_bank',	'Mandiri',	'0',	'12345',	35000.00,	NULL,	'confirmed',	'2025-12-04 08:26:18',	'2025-12-07 00:19:12',	NULL,	'file_1764836778_4edd668625c11cb3.png',	'2025-12-07 00:19:04',	'sent',	0),
(56,	'Tegar',	'trex50990@gmail.com',	'082225348452',	'Rumah Gusti',	200000.00,	'transfer_bank',	'Lainnya',	'0',	'1793833442',	200000.00,	NULL,	'confirmed',	'2025-12-05 10:51:23',	'2025-12-05 18:51:21',	35,	'file_1764931883_f58c79902e7be7c5.png',	'2025-12-05 10:51:24',	'sent',	0),
(57,	'Farrel',	'farrelking2@gmail.com',	'085175393742',	'yeyeye',	65000.00,	'transfer_bank',	'Lainnya',	'0',	'12345',	65000.00,	NULL,	'confirmed',	'2025-12-05 18:03:27',	'2025-12-05 18:41:02',	36,	'file_1764957807_ac6629bd71961350.jpg',	'2025-12-05 18:07:11',	'sent',	0),
(58,	'adamdam',	'nurcahyaputraa@gmail.com',	'082225348452',	'jc dfg',	80000.00,	'transfer_bank',	'Lainnya',	'0',	'-',	80000.00,	NULL,	'confirmed',	'2025-12-07 00:21:47',	'2025-12-07 00:23:03',	25,	'file_1765066907_51d22f24d66ac6e8.png',	'2025-12-07 00:23:03',	'sent',	0);

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL COMMENT 'ID produk jika review untuk produk',
  `package_id` int(11) DEFAULT NULL COMMENT 'ID paket jika review untuk paket',
  `item_type` enum('product','package') NOT NULL DEFAULT 'product' COMMENT 'Tipe item: product atau package',
  `nama_reviewer` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_review_transaction` (`transaction_id`),
  KEY `idx_review_product` (`product_id`),
  KEY `idx_review_package` (`package_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `reviews` (`id`, `transaction_id`, `product_id`, `package_id`, `item_type`, `nama_reviewer`, `rating`, `review_text`, `created_at`) VALUES
(10,	36,	NULL,	NULL,	'product',	'adam',	3,	'regegas',	'2025-11-02 03:22:55'),
(21,	50,	16,	NULL,	'product',	'adamdam',	5,	'ntaps',	'2025-11-21 10:12:04'),
(22,	52,	NULL,	1,	'package',	'adamdam',	1,	'juzjuz',	'2025-11-24 14:16:12'),
(23,	54,	18,	NULL,	'product',	'kicik',	3,	'',	'2025-11-27 13:26:15'),
(24,	54,	NULL,	2,	'package',	'kicik',	1,	'',	'2025-11-27 13:26:24'),
(25,	58,	NULL,	1,	'package',	'adamdam',	2,	'mantep coi',	'2025-12-07 00:24:02'),
(26,	58,	6,	NULL,	'product',	'adamdam',	4,	'rasanya mantap seperti iwir iwir',	'2025-12-07 00:24:37');

DROP TABLE IF EXISTS `transaction_items`;
CREATE TABLE `transaction_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `item_type` enum('product','package') NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transaction_id` (`transaction_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_package_id` (`package_id`),
  CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaction_items_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `transaction_items` (`id`, `transaction_id`, `product_id`, `package_id`, `item_type`, `quantity`, `price`) VALUES
(37,	36,	NULL,	NULL,	'product',	1,	56000.00),
(54,	50,	16,	NULL,	'product',	1,	50000.00),
(55,	51,	NULL,	3,	'package',	1,	350000.00),
(56,	52,	NULL,	1,	'package',	1,	35000.00),
(57,	53,	18,	NULL,	'product',	1,	6000.00),
(58,	53,	17,	NULL,	'product',	1,	11111.00),
(59,	53,	16,	NULL,	'product',	1,	50000.00),
(60,	54,	18,	NULL,	'product',	1,	6000.00),
(61,	54,	NULL,	2,	'package',	1,	200000.00),
(62,	55,	NULL,	1,	'package',	1,	35000.00),
(63,	56,	NULL,	2,	'package',	1,	200000.00),
(64,	57,	NULL,	5,	'package',	1,	65000.00),
(65,	58,	NULL,	1,	'package',	1,	35000.00),
(66,	58,	6,	NULL,	'product',	1,	45000.00);

CREATE TABLE wa_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ref_table VARCHAR(50) NOT NULL, 
    ref_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    message_id VARCHAR(255) DEFAULT NULL,
    raw_response TEXT DEFAULT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO wa_notifications (ref_table, ref_id, type, status, message_id, raw_response, sent_at) VALUES
('transactions', 58, 'payment_confirmation', 'sent', 'MSG123456789', '{"status":"sent","message_id":"MSG123456789"}', '2025-12-07 07:34:37');

-- 2025-12-07 07:34:37