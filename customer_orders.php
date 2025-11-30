<?php
session_start();
include 'backend/db.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_auth.php");
    exit();
}

// Get customer orders
$customer_id = $_SESSION['customer_id'];
$sql = "SELECT t.*, 
        GROUP_CONCAT(
            CASE 
                WHEN ti.item_type = 'product' THEN CONCAT(p.nama, ' (', ti.quantity, 'x)')
                WHEN ti.item_type = 'package' THEN CONCAT(pkg.nama, ' (', ti.quantity, 'x)')
            END SEPARATOR ', '
        ) as items
        FROM transactions t
        LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
        LEFT JOIN products p ON ti.product_id = p.id
        LEFT JOIN packages pkg ON ti.package_id = pkg.id
        WHERE t.customer_id = $customer_id
        GROUP BY t.id
        ORDER BY t.created_at DESC";

$result = $conn->query($sql);

include 'includes/header.php';
?>

<main>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Riwayat Pesanan</h2>
        <div>
            <span>Selamat datang, <?php echo $_SESSION['customer_name']; ?>!</span>
            <a href="customer_logout.php" class="btn-secondary" style="margin-left: 1rem;">Logout</a>
        </div>
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <div style="display: grid; gap: 1rem;">
            <?php while ($order = $result->fetch_assoc()): ?>
                <div style="border: 1px solid #ddd; padding: 1rem; border-radius: 8px; background: #f9f9f9;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <h3>Pesanan #<?php echo $order['id']; ?></h3>
                            <p style="color: #666; margin: 0;"><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <span class="badge <?php echo $order['status'] == 'confirmed' ? 'badge-success' : ($order['status'] == 'cancelled' ? 'badge-error' : 'badge-warning'); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <strong>Items:</strong><br>
                        <?php echo $order['items']; ?>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between;">
                        <div>
                            <strong>Total: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong>
                        </div>
                        <div>
                            <strong>Bank: <?php echo $order['bank_name']; ?></strong>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>Anda belum memiliki pesanan.</p>
        <a href="products.php" class="btn">Mulai Belanja</a>
    <?php endif; ?>
</main>

<style>
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
}
.badge-success { background: #d4edda; color: #155724; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-error { background: #f8d7da; color: #721c24; }
</style>

<?php include 'includes/footer.php'; ?>
