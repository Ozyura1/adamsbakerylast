<?php 
include 'includes/header.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Generate CSRF token
generateCSRFToken();
?>
<main>
    <h2 style="text-align:center; color:#4e342e; position:relative;">
        Hubungi Kami
        <div style="width:60px; height:2px; background:#8B4513; margin:8px auto 0;"></div>
    </h2>

    <!-- NAVIGASI TAB -->
    <div class="contact-tabs">
        <button class="tab-btn active" onclick="showTab('custom_order', event)">Pesanan Kustom</button>
        <button class="tab-btn" onclick="showTab('pertanyaan', event)">Pertanyaan Umum</button>
    </div>

    <!-- TAB PESANAN KUSTOM -->
    <div id="custom_order-tab" class="tab-content active">
        <h3 class="tab-title">Pesanan Kustom</h3>
        <div class="title-underline"></div>

        <form method="post" action="backend/process_contact.php">
            <!-- Tambahkan CSRF token ke form -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
            <input type="hidden" name="jenis_kontak" value="custom_order">
            
            <label>Nama:</label>
            <input type="text" name="nama" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>No. Telepon:</label>
            <input type="tel" name="phone">

            <label>Jenis Acara:</label>
            <select name="jenis_acara">
                <option value="">Pilih jenis acara</option>
                <option value="ulang_tahun">Ulang Tahun</option>
                <option value="pernikahan">Pernikahan</option>
                <option value="graduation">Wisuda</option>
                <option value="corporate">Corporate Event</option>
                <option value="lainnya">Lainnya</option>
            </select>

            <label>Tanggal Acara:</label>
            <input type="date" name="event_date" min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>">

            <label>Jumlah Porsi (perkiraan):</label>
            <input type="number" name="jumlah_porsi" min="1" placeholder="contoh: 50">

            <label>Budget Range:</label>
            <select name="budget_range">
                <option value="">Pilih budget range</option>
                <option value="< 500rb">< Rp 500.000</option>
                <option value="500rb - 1jt">Rp 500.000 - 1.000.000</option>
                <option value="1jt - 2jt">Rp 1.000.000 - 2.000.000</option>
                <option value="2jt - 5jt">Rp 2.000.000 - 5.000.000</option>
                <option value="> 5jt">> Rp 5.000.000</option>
            </select>
            
            <label>Detail Pesanan Kustom:</label>
            <textarea name="custom_order_details" placeholder="Jelaskan detail pesanan Anda..." required></textarea>

            <label>Pesan Tambahan:</label>
            <textarea name="pesan" placeholder="Ada permintaan khusus atau pertanyaan lainnya?"></textarea>
            
            <button type="submit">Kirim Permintaan Pesanan</button>
        </form>
    </div>

    <!-- TAB PERTANYAAN UMUM -->
    <div id="pertanyaan-tab" class="tab-content">
        <h3 class="tab-title">Pertanyaan Umum</h3>
        <div class="title-underline"></div>

        <!-- FORM KIRIM PERTANYAAN -->
        <form method="post" action="backend/process_contact.php">
            <!-- Tambahkan CSRF token ke form -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(getCSRFToken()); ?>">
            <input type="hidden" name="jenis_kontak" value="pertanyaan_umum">
            
            <label>Nama:</label>
            <input type="text" name="nama" required>

            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Pertanyaan:</label>
            <textarea name="pesan" placeholder="Apa yang ingin Anda tanyakan?" required></textarea>
            
            <button type="submit">Kirim Pertanyaan</button>
        </form>

        <hr style="margin:40px 0;">

        <!-- TAMPILAN Q&A -->
        <h4 style="text-align:center; color:#8B4513;">Pertanyaan yang Sudah Dijawab</h4>
        <div class="qa-section">
        <?php
        $result = $conn->query("
            SELECT nama, pertanyaan AS pesan, admin_reply, created_at 
            FROM pertanyaan_umum 
            WHERE admin_reply IS NOT NULL AND admin_reply != '' 
            ORDER BY created_at DESC
        ");

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="qa-item">';
                echo '<div class="qa-header">';
                echo '<strong>👤 ' . htmlspecialchars($row['nama']) . '</strong>';
                echo '<span class="qa-date">' . date('d M Y', strtotime($row['created_at'])) . '</span>';
                echo '</div>';
                echo '<div class="qa-question">';
                echo '<strong>Pertanyaan:</strong><br>';
                echo nl2br(htmlspecialchars($row['pesan']));
                echo '</div>';
                echo '<div class="qa-answer">';
                echo '<strong>Jawaban Admin:</strong><br>';
                echo nl2br(htmlspecialchars($row['admin_reply']));
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p style="text-align:center; color:#777;">Belum ada pertanyaan yang dijawab.</p>';
        }
        ?>
        </div>
    </div>
</main>

<!-- ========== STYLE ========== -->
<style>
main {
    max-width: 900px;
    margin: 50px auto;
    padding: 0 20px;
    font-family: "Poppins", sans-serif;
    color: #4e342e;
    text-align: center;
}

/* === TAB PILIHAN === */
.contact-tabs {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    margin: 2rem auto;
    gap: 10px;
}

.tab-btn {
    background: #d7b899;
    color: #4e342e;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.4s ease;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}
.tab-btn:hover {
    background-color: #c49a6c;
    color: white;
    transform: scale(1.05);
}
.tab-btn.active {
    background: #8b5a3c;
    color: #fff;
    transform: scale(1.05);
}

/* === TAB CONTENT === */
.tab-content {
    display: none; /* ⬅ disembunyikan default */
}
.tab-content.active {
    display: block; /* ⬅ cuma yang aktif tampil */
}

.tab-title {
    text-align: center;
    color: #8B4513;
    margin-bottom: 0.5rem;
    font-weight: 700;
}
.title-underline {
    width: 80px;
    height: 2px;
    background: #c49a6c;
    margin: 0 auto 1.5rem;
}

/* === FORM === */
form {
    background: #fffaf5;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    margin: 0 auto;
    max-width: 700px;
    text-align: left;
    animation: fadeSlide 0.5s ease;
}
@keyframes fadeSlide {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

label {
    font-weight: bold;
    color: #4e342e;
    display: block;
    margin-bottom: 6px;
}

input, select, textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #e0c7a7;
    border-radius: 8px;
    font-size: 15px;
    background: #fff;
    margin-bottom: 15px;
    transition: border-color 0.3s, box-shadow 0.3s;
}
input:focus, select:focus, textarea:focus {
    border-color: #c49a6c;
    box-shadow: 0 0 5px rgba(196,154,108,0.4);
    outline: none;
}
textarea {
    min-height: 120px;
    resize: vertical;
}

button[type="submit"] {
    background-color: #8B4513;
    color: white;
    padding: 12px 0;
    border: none;
    border-radius: 25px;
    width: 100%;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}
button[type="submit"]:hover {
    background-color: #a05e2e;
    transform: translateY(-2px);
}

/* === Q&A STYLE === */
.qa-section {
    max-width: 700px;
    margin: 30px auto;
    background: #fffaf5;
    padding: 25px 35px;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    text-align: left;
    animation: fadeSlide 0.5s ease;
}

.qa-item {
    background: #fff;
    border: 1px solid #e0c7a7;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    transition: transform 0.2s ease, box-shadow 0.3s ease;
}
.qa-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

.qa-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-weight: 600;
    color: #4e342e;
}

.qa-date {
    font-size: 0.85rem;
    color: #a2835d;
}

.qa-question, .qa-answer {
    background: #fffaf0;
    padding: 12px 15px;
    border-radius: 8px;
    border-left: 4px solid #c49a6c;
    margin-bottom: 10px;
}

.qa-question strong, .qa-answer strong {
    color: #8B4513;
}


.customer { justify-content: flex-start; }
.customer-bubble {
    background-color: #f8e4c7;
    color: #4e342e;
    border-top-left-radius: 0;
}

.admin { justify-content: flex-end; }
.admin-bubble {
    background-color: #c49a6c;
    color: white;
    border-top-right-radius: 0;
}

#pertanyaan-tab h4 {
    text-align: center;
    color: #8B4513;
    margin-bottom: 20px;
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
    form { padding: 20px; }
    .bubble {
        max-width: 90%;
        font-size: 0.9rem;
        padding: 10px 12px;
    }
    .tab-btn {
        flex: 1 1 45%;
        text-align: center;
    }
}
</style>

<!-- ========== SCRIPT ========== -->
<script>
function showTab(tabName, event) {
    const tabContents = document.querySelectorAll('.tab-content');
    const tabBtns = document.querySelectorAll('.tab-btn');
    
    tabContents.forEach(content => content.classList.remove('active'));
    tabBtns.forEach(btn => btn.classList.remove('active'));

    document.getElementById(tabName + '-tab').classList.add('active');
    if (event && event.target) event.target.classList.add('active');

    window.scrollTo({
        top: document.querySelector('.contact-tabs').offsetTop - 20,
        behavior: 'smooth'
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Tampilkan tab default saat halaman dibuka
    showTab('custom_order', { target: document.querySelector('.tab-btn:first-child') });
});
</script>

<?php include 'includes/footer.php'; ?>
