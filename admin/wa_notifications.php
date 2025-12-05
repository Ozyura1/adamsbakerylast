<?php
session_start();
include '../backend/db.php';
require_once __DIR__ . '/../backend/admin_notifier.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle resend action
$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resend_notification_id'])) {
    $nid = intval($_POST['resend_notification_id']);

    // fetch the notification record
    $stmt = $conn->prepare("SELECT * FROM wa_notifications WHERE id = ?");
    $stmt->bind_param("i", $nid);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows) {
        $record = $res->fetch_assoc();
        $ref_table = $record['ref_table'];
        $ref_id = intval($record['ref_id']);

        $notifier = new AdminNotifier($conn);
        if ($ref_table === 'transactions') {
            $result = $notifier->notifyNewOrder($ref_id, true);
        } else if ($ref_table === 'kontak') {
            $result = $notifier->notifyNewCustomOrder($ref_id, true);
        } else {
            $result = ['status' => false, 'error' => 'Unknown reference table'];
        }

        if (!empty($result['status'])) {
            $feedback = "Resend berhasil.";
        } else {
            $feedback = "Resend gagal: " . ($result['error'] ?? ($result['reason'] ?? 'Unknown'));
        }
    } else {
        $feedback = 'Notifikasi tidak ditemukan.';
    }
}

// Fetch last 200 notifications
$sql = "SELECT * FROM wa_notifications ORDER BY sent_at DESC LIMIT 200";
$notifications = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Notifikasi WA - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main>
    <h2>Riwayat Notifikasi WhatsApp</h2>

    <?php if ($feedback): ?>
        <div style="padding:10px; background:#f0f0f0; border-radius:6px; margin-bottom:12px;">
            <?php echo htmlspecialchars($feedback); ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ref Table</th>
                <th>Ref ID</th>
                <th>Type</th>
                <th>Status</th>
                <th>Message ID</th>
                <th>Sent At</th>
                <th>Raw Response</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $notifications->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['ref_table']); ?></td>
                <td><?php echo $row['ref_id']; ?></td>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['message_id']); ?></td>
                <td><?php echo htmlspecialchars($row['sent_at']); ?></td>
                <td style="max-width:300px; overflow:auto;"><pre style="white-space:pre-wrap; word-break:break-word; font-size:12px;"><?php echo htmlspecialchars($row['raw_response']); ?></pre></td>
                <td>
                    <form method="post" onsubmit="return confirm('Kirim ulang notifikasi ini ke admin?');">
                        <input type="hidden" name="resend_notification_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" style="padding:6px 8px; background:#28a745; color:#fff; border:none; border-radius:4px;">Resend</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
