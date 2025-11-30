<?php
// backend/fonnte_config.php
// Dapatkan token dari https://dashboard.fonnte.com/
// GANTI nilai di bawah ini dengan token Anda yang sesungguhnya

// ⚠️ PENTING: Gunakan environment variable untuk production
// define('FONNTE_TOKEN', getenv('FONNTE_TOKEN') ?: 'your_token_here');

define('FONNTE_BASE_URL', 'https://api.fonnte.com');
define('FONNTE_TOKEN', 'mJXKDhRoHJKTG6i2WG5H'); // GANTI dengan token Fonnte Anda

// Nomor fallback jika nomor pelanggan tidak tersedia
define('FONNTE_FALLBACK_RECIPIENT', '+628123456789'); // GANTI dengan nomor fallback Anda

// Flag untuk enable/disable WhatsApp notifications
define('FONNTE_ENABLE_NOTIFICATIONS', true);

// Timeout untuk API call (dalam detik)
define('FONNTE_TIMEOUT', 15);
