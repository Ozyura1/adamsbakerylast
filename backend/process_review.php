<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = intval($_POST['transaction_id']);
    $item_type = $_POST['item_type'];
    $item_id = intval($_POST['item_id']);
    $nama_reviewer = $_POST['nama_reviewer'];
    $rating = intval($_POST['rating']);
    $review_text = $_POST['review_text'];
    
    // Check if review already exists
    if ($item_type == 'product') {
        $check_sql = "SELECT id FROM reviews WHERE transaction_id = ? AND product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('ii', $transaction_id, $item_id);
    } else {
        $check_sql = "SELECT id FROM reviews WHERE transaction_id = ? AND package_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('ii', $transaction_id, $item_id);
    }
    
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo "<script>alert('Anda sudah memberikan ulasan untuk item ini.'); window.location.href='../review.php?transaction_id=$transaction_id';</script>";
        exit();
    }
    $check_stmt->close();
    
    // Insert review
    if ($item_type == 'product') {
        $sql = "INSERT INTO reviews (transaction_id, product_id, item_type, nama_reviewer, rating, review_text) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iissss', $transaction_id, $item_id, $item_type, $nama_reviewer, $rating, $review_text);
    } else {
        $sql = "INSERT INTO reviews (transaction_id, package_id, item_type, nama_reviewer, rating, review_text) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iissss', $transaction_id, $item_id, $item_type, $nama_reviewer, $rating, $review_text);
    }
    
    if ($stmt->execute()) {
        echo "<script>alert('Terima kasih atas ulasan Anda!'); window.location.href='../review.php?transaction_id=$transaction_id';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.location.href='../review.php?transaction_id=$transaction_id';</script>";
    }
    $stmt->close();
} else {
    header("Location: ../index.php");
    exit();
}
?>
