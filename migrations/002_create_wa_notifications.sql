-- Migration: Create wa_notifications table and add admin_notifications_disabled flag

-- Create wa_notifications table
CREATE TABLE IF NOT EXISTS `wa_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ref_table` varchar(50) NOT NULL,
  `ref_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `message_id` varchar(255) DEFAULT NULL,
  `raw_response` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ref` (`ref_table`, `ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add disable flag to transactions and kontak
ALTER TABLE `transactions` 
ADD COLUMN `admin_notifications_disabled` TINYINT(1) DEFAULT 0 COMMENT 'Jika 1, tidak mengirim notifikasi WA ke admin untuk order ini';

ALTER TABLE `kontak` 
ADD COLUMN `admin_notifications_disabled` TINYINT(1) DEFAULT 0 COMMENT 'Jika 1, tidak mengirim notifikasi WA ke admin untuk kontak ini';
