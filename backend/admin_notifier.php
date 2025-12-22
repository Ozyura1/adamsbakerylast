<?php
/**
 * Admin Notification Helper
 * Handles sending WhatsApp notifications to admin for new orders
 */

require_once __DIR__ . '/fonnte_config.php';
require_once __DIR__ . '/FonnteGateway.php';
require_once __DIR__ . '/process_contact.php';

class AdminNotifier
{
    private $gateway;
    private $conn;
    private $adminWaNumber;

    public function __construct($database_connection)
    {
        $this->conn = $database_connection;
        $this->adminWaNumber = defined('ADMIN_WA_NUMBER') ? ADMIN_WA_NUMBER : null;
        
        if (defined('FONNTE_ENABLE_NOTIFICATIONS') && FONNTE_ENABLE_NOTIFICATIONS && $this->adminWaNumber) {
            try {
                $this->gateway = new FonnteGateway();
            } catch (Exception $e) {
                error_log("AdminNotifier: Failed to initialize FonnteGateway: " . $e->getMessage());
                $this->gateway = null;
            }
        }
    }

    /**
     * Notify admin of new regular order (from transactions table)
     */
    public function notifyNewOrder($transaction_id, $notify_enabled = true)
    {
        if (!$notify_enabled || !$this->gateway) {
            return ['status' => false, 'reason' => 'Notifier disabled or gateway unavailable'];
        }

        try {
            // Fetch transaction details
            $stmt = $this->conn->prepare("SELECT * FROM transactions WHERE id = ?");
            $stmt->bind_param("i", $transaction_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                error_log("AdminNotifier: Transaction #$transaction_id not found");
                return ['status' => false, 'reason' => 'Transaction not found'];
            }

            $transaction = $result->fetch_assoc();
            $stmt->close();

            // Fetch transaction items
            $items_stmt = $this->conn->prepare("
                SELECT ti.*, 
                       CASE 
                           WHEN ti.item_type = 'product' THEN p.nama
                           WHEN ti.item_type = 'package' THEN pkg.nama
                       END as item_name
                FROM transaction_items ti
                LEFT JOIN products p ON ti.product_id = p.id
                LEFT JOIN packages pkg ON ti.package_id = pkg.id
                WHERE ti.transaction_id = ?
            ");
            $items_stmt->bind_param("i", $transaction_id);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();

            // Build item list
            $items_list = "";
            while ($item = $items_result->fetch_assoc()) {
                $items_list .= "• " . ($item['item_name'] ?? 'Item') . " x" . $item['quantity'] . "\n";
            }
            $items_stmt->close();

            // Build message
            $order_id = $transaction['id'];
            $nama = $transaction['nama_pembeli'] ?? 'Unknown';
            $phone = $transaction['phone'] ?? 'N/A';
            $alamat = trim($transaction['alamat'] ?? 'N/A');
            $total = 'Rp ' . number_format($transaction['total_amount'], 0, ',', '.');
            $status = ucfirst($transaction['status'] ?? 'pending');
            $created_at = date('d/m/Y H:i', strtotime($transaction['created_at']));

            $message = "🔔 *Pesanan Baru - Adam's Bakery*\n\n";
            $message .= "📦 *Order ID:* #$order_id\n";
            $message .= "👤 *Nama Pembeli:* $nama\n";
            $message .= "📱 *No. WA:* $phone\n";
            $message .= "🏠 *Alamat:* $alamat\n";
            $message .= "💰 *Total:* $total\n";
            $message .= "📊 *Status:* $status\n";
            $message .= "📅 *Waktu:* $created_at\n";
            
            if (!empty($items_list)) {
                $message .= "\n*Item yang dipesan:*\n$items_list";
            }

            $message .= "\n🔗 Buka Dashboard: https://adambakery.thebamfams.web.id/adamsbakery/admin/login.php";

            // Respect per-order disable flag
            if (!empty($transaction['admin_notifications_disabled'])) {
                error_log("AdminNotifier: Notifications disabled for transaction #$transaction_id");
                return ['status' => false, 'reason' => 'Notifications disabled for this order'];
            }

            // Send WhatsApp
            $wa_result = $this->gateway->sendMessage($this->adminWaNumber, $message);

            // Log result to database (including raw response)
            $raw = $wa_result['raw_response'] ?? (isset($wa_result['error']) ? json_encode(['error' => $wa_result['error']]) : null);
            $this->logNotification($transaction_id, 'order_new', $wa_result['status'], $wa_result['message_id'] ?? null, 'transactions', $raw);

            if ($wa_result['status']) {
                error_log("AdminNotifier: Notification sent successfully for order #$order_id");
                return ['status' => true, 'message_id' => $wa_result['message_id'] ?? null];
            } else {
                error_log("AdminNotifier: Failed to send notification for order #$order_id: " . ($wa_result['error'] ?? 'Unknown error'));
                return ['status' => false, 'error' => $wa_result['error'] ?? 'Failed to send'];
            }

        } catch (Exception $e) {
            error_log("AdminNotifier Exception (notifyNewOrder): " . $e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Notify admin of new custom order (from kontak table)
     */
    public function notifyNewCustomOrder($kontak_id, $notify_enabled = true)
    {
        if (!$notify_enabled || !$this->gateway) {
            return ['status' => false, 'reason' => 'Notifier disabled or gateway unavailable'];
        }

        try {
            // Fetch custom order details
            $stmt = $this->conn->prepare("SELECT * FROM kontak WHERE id = ?");
            $stmt->bind_param("i", $kontak_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                error_log("AdminNotifier: Custom order #$kontak_id not found");
                return ['status' => false, 'reason' => 'Custom order not found'];
            }

            $order = $result->fetch_assoc();
            $stmt->close();

            // Build message with custom order details
            $order_id = $order['id'];
            $nama = $order['nama'] ?? 'Unknown';
            $email = $order['email'] ?? 'N/A';
            $item = $order['pesan'] ?? 'N/A';
            $details = $order['custom_order_details'] ?? '';
            $budget = $order['budget_range'] ?? 'Not specified';
            $event_date = $order['event_date'] ? date('d/m/Y', strtotime($order['event_date'])) : 'Not specified';
            $porsi = $order['jumlah_porsi'] ?? '0';
            $status = ucfirst($order['status'] ?? 'pending');
            $created_at = date('d/m/Y H:i', strtotime($order['created_at']));

            $message = "⭐ *Pesanan Kustom Baru - Adam's Bakery*\n\n";
            $message .= "📦 *Order ID:* #$order_id\n";
            $message .= "👤 *Nama Pemesan:* $nama\n";
            $message .= "📧 *Email:* $email\n";
            $message .= "🍰 *Item Pesanan:* $item\n";
            
            if (!empty($details)) {
                $message .= "🎨 *Detail Khusus:* $details\n";
            }
            
            $message .= "📅 *Tanggal Event:* $event_date\n";
            $message .= "🍽️ *Jumlah Porsi:* $porsi\n";
            $message .= "💵 *Budget Range:* $budget\n";
            $message .= "📊 *Status:* $status\n";
            $message .= "🕐 *Waktu Diterima:* $created_at\n";

            $message .= "\n🔗 Balas/buat quote di: https://adambakery.thebamfams.web.id/adamsbakery/admin/login.php";

            // Respect per-order disable flag
            if (!empty($order['admin_notifications_disabled'])) {
                error_log("AdminNotifier: Notifications disabled for custom order #$kontak_id");
                return ['status' => false, 'reason' => 'Notifications disabled for this custom order'];
            }

            // Send WhatsApp
            $wa_result = $this->gateway->sendMessage($this->adminWaNumber, $message);

            // Log result to database (including raw response)
            $raw = $wa_result['raw_response'] ?? (isset($wa_result['error']) ? json_encode(['error' => $wa_result['error']]) : null);
            $this->logNotification($order_id, 'custom_order_new', $wa_result['status'], $wa_result['message_id'] ?? null, 'kontak', $raw);

            if ($wa_result['status']) {
                error_log("AdminNotifier: Custom order notification sent successfully for #$order_id");
                return ['status' => true, 'message_id' => $wa_result['message_id'] ?? null];
            } else {
                error_log("AdminNotifier: Failed to send custom order notification for #$order_id: " . ($wa_result['error'] ?? 'Unknown error'));
                return ['status' => false, 'error' => $wa_result['error'] ?? 'Failed to send'];
            }

        } catch (Exception $e) {
            error_log("AdminNotifier Exception (notifyNewCustomOrder): " . $e->getMessage());
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Log notification result to database
     */
    private function logNotification($ref_id, $type, $success, $message_id = null, $table = 'transactions', $raw_response = null)
    {
        try {
            $notified_status = $success ? 'sent' : 'failed';

            // Update transactions/kontak summary columns
            if ($table === 'transactions') {
                $stmt = $this->conn->prepare("UPDATE transactions SET admin_notified_at = NOW(), admin_notified_status = ? WHERE id = ?");
                $stmt->bind_param("si", $notified_status, $ref_id);
                $stmt->execute();
                $stmt->close();
            } else if ($table === 'kontak') {
                $stmt = $this->conn->prepare("UPDATE kontak SET admin_notified_at = NOW(), admin_notified_status = ? WHERE id = ?");
                $stmt->bind_param("si", $notified_status, $ref_id);
                $stmt->execute();
                $stmt->close();
            }

            // Insert detailed record into wa_notifications
            $ins = $this->conn->prepare("INSERT INTO wa_notifications (ref_table, ref_id, type, status, message_id, raw_response, sent_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $ins->bind_param("sissss", $table, $ref_id, $type, $notified_status, $message_id, $raw_response);
            $ins->execute();
            $ins->close();
        } catch (Exception $e) {
            error_log("AdminNotifier: Failed to log notification: " . $e->getMessage());
        }
    }
}

?>
