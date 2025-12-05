<?php
session_start();
include '../backend/db.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// --- UPDATE STATUS PESANAN KUSTOM ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $kontak_id = intval($_POST['kontak_id']);
    $new_status = $conn->real_escape_string($_POST['status']);
    $update_sql = "UPDATE kontak SET status = '$new_status' WHERE id = $kontak_id";
    $conn->query($update_sql);
}

// --- UPDATE JAWABAN ADMIN UNTUK PERTANYAAN UMUM ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_reply'])) {
    $question_id = intval($_POST['question_id']);
    $admin_reply = $conn->real_escape_string($_POST['admin_reply']);
    $update_reply = "UPDATE pertanyaan_umum SET admin_reply = '$admin_reply' WHERE id = $question_id";
    $conn->query($update_reply);
}

// --- GET DATA PESANAN KUSTOM ---
$sql_custom = "SELECT * FROM kontak WHERE jenis_kontak = 'custom_order' ORDER BY created_at DESC";
$result_custom = $conn->query($sql_custom);

// --- GET DATA PERTANYAAN UMUM ---
$sql_question = "SELECT * FROM pertanyaan_umum ORDER BY created_at DESC";
$result_question = $conn->query($sql_question);


// Fungsi warna status
function getStatusStyle($status) {
    switch ($status) {
        case 'pending': return ['#fff3cd', '#856404'];
        case 'reviewed': return ['#d0ebff', '#004085'];
        case 'quoted': return ['#fef9e7', '#7d6608'];
        case 'confirmed': return ['#e2f0d9', '#155724'];
        case 'completed': return ['#e2e3e5', '#383d41'];
        case 'cancelled': return ['#f8d7da', '#721c24'];
        default: return ['#f8f9fa', '#6c757d'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Adam Bakery</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/logoadambakery.png">
    <style>
        main { padding: 20px 40px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn {
            background: #eee; border: none; padding: 10px 18px; border-radius: 8px;
            cursor: pointer; font-weight: bold; color: #555;
        }
        .tab-btn.active { background: #8B4513; color: #fff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .order-card {
            background: #fff; border-radius: 12px; padding: 16px 20px;
            margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .order-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 8px;
        }
        .status-badge {
            padding: 6px 12px; border-radius: 20px; font-size: 13px;
            font-weight: bold; text-transform: capitalize;
        }
        .detail-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr));
            gap: 12px 20px; margin-bottom: 12px;
        }
        .detail-item { font-size: 14px; line-height: 1.5; }
        .detail-label { font-weight: 600; color: #222; margin-right: 6px; }
        .detail-value { color: #555; }
        .detail-box {
            background: #f9f9f9; border: 1px solid #eee; padding: 10px 14px;
            border-radius: 8px; margin-top: 4px; font-size: 14px; color: #444;
        }
        .status-form { margin-top: 14px; display: flex; gap: 8px; align-items: center; }
        .status-form select, textarea {
            padding: 6px 10px; border-radius: 6px; border: 1px solid #ccc;
            width: 100%;
        }
        .status-form button {
            padding: 6px 12px; border: none; background: #8B4513;
            color: #fff; border-radius: 6px; cursor: pointer;
        }
        .status-form button:hover { background: #A0522D; }
        .no-orders {
            padding: 16px; background: #fff3cd; border: 1px solid #ffeeba;
            border-radius: 8px; color: #856404;
        }
    </style>
</head>
<body>
<header class="admin-header">
    <h1>Kelola Pesanan - Adam Bakery</h1>
    <nav class="admin-nav">
        <a href="dashboard.php">Dashboard</a> |
        <a href="manage_products.php">Kelola Produk</a> |
        <a href="manage_packages.php">Kelola Paket</a> |
        <a href="view_transactions.php">Transaksi</a> |
        <a href="admin_promos.php">Promo</a> |
        <a href="view_reviews.php">Ulasan</a> |
        <a href="view_custom_orders.php">Pesanan & Pertanyaan</a> |
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <div class="tabs">
        <button class="tab-btn active" data-tab="custom">Pesanan Kustom</button>
        <button class="tab-btn" data-tab="question">Pertanyaan Umum</button>
    </div>

    <!-- Tab Pesanan Kustom -->
    <div id="tab-custom" class="tab-content active">
        <h2>Daftar Pesanan Kustom</h2>
        <?php if ($result_custom->num_rows > 0): ?>
            <?php while ($order = $result_custom->fetch_assoc()):
                [$bgColor, $textColor] = getStatusStyle($order['status']); ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>Pesanan #<?= $order['id'] ?></h3>
                            <p style="color:#666;margin:0;">
                                <?= date('d M Y H:i', strtotime($order['created_at'])) ?>
                            </p>
                        </div>
                        <span class="status-badge" style="background:<?= $bgColor ?>;color:<?= $textColor ?>;">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>

                    <div class="detail-grid">
                        <div><b>Nama:</b> <?= htmlspecialchars($order['nama']) ?></div>
                        <div><b>Email:</b> <?= htmlspecialchars($order['email']) ?></div>
                    </div>

                    <?php if ($order['custom_order_details']): ?>
                        <div class="detail-box"><?= nl2br(htmlspecialchars($order['custom_order_details'])) ?></div>
                    <?php endif; ?>

                    <form method="post" class="status-form">
                        <input type="hidden" name="kontak_id" value="<?= $order['id'] ?>">
                        <label>Status:</label>
                        <select name="status">
                            <?php foreach(['pending','reviewed','quoted','confirmed','completed','cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= $order['status']==$st?'selected':''; ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_status">Update</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-orders">Belum ada pesanan kustom.</div>
        <?php endif; ?>
    </div>

    <!-- Tab Pernyataan Umum -->
    <div id="tab-question" class="tab-content">
        <h2>Pertanyaan Umum</h2>
        <?php if ($result_question->num_rows > 0): ?>
            <?php while ($q = $result_question->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>Pertanyaan #<?= $q['id'] ?></h3>
                            <p style="color:#666;margin:0;"><?= date('d M Y H:i', strtotime($q['created_at'])) ?></p>
                        </div>
                    </div>
                    <p><b>Nama:</b> <?= htmlspecialchars($q['nama']) ?></p>
                    <p><b>Email:</b> <?= htmlspecialchars($q['email']) ?></p>
                    <div class="detail-box">
                        <?= htmlspecialchars($q['pertanyaan'] ?? '') ?>
                    </div>

                    <form method="post" class="status-form" style="flex-direction:column;align-items:flex-start;">
                        <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                        <label for="reply">Jawaban Admin:</label>
                        <textarea name="admin_reply" rows="3" placeholder="Tulis jawaban di sini..."><?= htmlspecialchars($q['admin_reply'] ?? '') ?></textarea>
                        <button type="submit" name="send_reply" style="margin-top:10px;">Kirim Jawaban</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-orders">Belum ada pertanyaan umum.</div>
        <?php endif; ?>
    </div>
</main>

<script>
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(b => b.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(`tab-${btn.dataset.tab}`).classList.add('active');
        });
    });
</script>
</body>
</html>
