<?php
include 'db.php';
include 'csrf.php';
require_once __DIR__ . '/AdminNotifier.php';

$notifier = new AdminNotifier($conn);
$notifier->notifyNewCustomOrder($kontak_id);

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('<div style="max-width: 600px; margin: 2rem auto; padding: 2rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px;"><h3 style="color: #721c24;">Keamanan: Token tidak valid</h3></div>');
    }
    
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $pesan = $_POST['pesan'];
    
    $jenis_kontak = isset($_POST['jenis_kontak']) ? $_POST['jenis_kontak'] : 'ulasan';
    
    $allowed_jenis = ['custom_order', 'pertanyaan_umum'];
    if (!in_array($jenis_kontak, $allowed_jenis)) {
        $jenis_kontak = 'ulasan';
    }
    
    $custom_order_details = null;
    $budget_range = null;
    $event_date = null;
    $jumlah_porsi = null;

    // Jika custom order, ambil field tambahan
    if ($jenis_kontak == 'custom_order') {
        $custom_order_details = isset($_POST['custom_order_details']) ? $_POST['custom_order_details'] : null;
        $budget_range = isset($_POST['budget_range']) ? $_POST['budget_range'] : null;
        $event_date = isset($_POST['event_date']) && !empty($_POST['event_date']) ? $_POST['event_date'] : null;
        $jumlah_porsi = isset($_POST['jumlah_porsi']) && !empty($_POST['jumlah_porsi']) ? intval($_POST['jumlah_porsi']) : null;
    }

    if ($jenis_kontak == 'pertanyaan_umum') {
        $stmt = $conn->prepare("INSERT INTO pertanyaan_umum (nama, email, pertanyaan) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $email, $pesan);
    } else {
        $stmt = $conn->prepare("INSERT INTO kontak (nama, email, pesan, jenis_kontak, custom_order_details, budget_range, event_date, jumlah_porsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssi", $nama, $email, $pesan, $jenis_kontak, $custom_order_details, $budget_range, $event_date, $jumlah_porsi);
    }

    // ====== CEK BERHASIL ATAU TIDAK ======
    if ($stmt->execute()) {
        $last_insert_id = $stmt->insert_id;
        
        // Send admin notification for custom orders
        if ($jenis_kontak === 'custom_order') {
            $notifier = new AdminNotifier($conn);
            $admin_notif_result = $notifier->notifyNewCustomOrder($last_insert_id, true);
            
            if (!$admin_notif_result['status']) {
                error_log("Process contact: Admin notification failed for custom order #$last_insert_id - " . ($admin_notif_result['reason'] ?? $admin_notif_result['error'] ?? 'Unknown'));
            }
        }

        switch ($jenis_kontak) {
            case 'custom_order':
                echo "<div style='max-width: 600px; margin: 2rem auto; padding: 2rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px;'>";
                echo "<h3 style='color: #155724; margin-top: 0;'>Permintaan Pesanan Kustom Diterima!</h3>";
                echo "<p style='color: #155724;'>Terima kasih <strong>" . htmlspecialchars($nama) . "</strong>, permintaan pesanan kustom Anda sudah kami terima!</p>";
                echo "<p style='color: #155724;'>Tim kami akan menghubungi Anda dalam 1-2 jam kerja untuk membahas detail dan memberikan penawaran harga.</p>";
                if ($event_date) {
                    echo "<p style='color: #155724;'><strong>Tanggal acara:</strong> " . date('d M Y', strtotime($event_date)) . "</p>";
                }
                echo "<a href='../contact.php' style='display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background: #8B4513; color: white; text-decoration: none; border-radius: 4px;'>Kembali</a>";
                echo "</div>";
                break;

            case 'pertanyaan_umum':
                echo "<div style='max-width: 600px; margin: 2rem auto; padding: 2rem; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px;'>";
                echo "<h3 style='color: #0c5460; margin-top: 0;'>Pertanyaan Diterima!</h3>";
                echo "<p style='color: #0c5460;'>Terima kasih <strong>" . htmlspecialchars($nama) . "</strong>, pertanyaan Anda sudah kami terima dan akan dijawab segera!</p>";
                echo "<a href='../contact.php' style='display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background: #8B4513; color: white; text-decoration: none; border-radius: 4px;'>Kembali</a>";
                echo "</div>";
                break;

            default:
                echo "<div style='max-width: 600px; margin: 2rem auto; padding: 2rem; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;'>";
                echo "<h3 style='color: #856404; margin-top: 0;'>Ulasan Diterima!</h3>";
                echo "<p style='color: #856404;'>Terima kasih <strong>" . htmlspecialchars($nama) . "</strong>, ulasan Anda sangat berharga bagi kami!</p>";
                echo "<a href='../contact.php' style='display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background: #8B4513; color: white; text-decoration: none; border-radius: 4px;'>Kembali</a>";
                echo "</div>";
                break;
        }
    } else {
        echo "<div style='max-width: 600px; margin: 2rem auto; padding: 2rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px;'>";
        echo "<h3 style='color: #721c24; margin-top: 0;'>Terjadi Kesalahan</h3>";
        echo "<p style='color: #721c24;'>Maaf, terjadi kesalahan. Silakan coba lagi.</p>";
        echo "<a href='../contact.php' style='display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background: #8B4513; color: white; text-decoration: none; border-radius: 4px;'>Coba Lagi</a>";
        echo "</div>";
    }
    
    $stmt->close();
}
?>
