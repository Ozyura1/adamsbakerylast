<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = $conn->real_escape_string($_POST['transaction_id']);
    $item_type = $conn->real_escape_string($_POST['item_type']);
    $item_id = $conn->real_escape_string($_POST['item_id']);
    $nama_reviewer = $conn->real_escape_string($_POST['nama_reviewer']);
    $rating = $conn->real_escape_string($_POST['rating']);
    $review_text = $conn->real_escape_string($_POST['review_text']);
    
    // Check if review already exists
    $check_sql = "SELECT id FROM reviews WHERE transaction_id = '$transaction_id' AND ";
    if ($item_type == 'product') {
        $check_sql .= "product_id = '$item_id'";
    } else {
        $check_sql .= "package_id = '$item_id'";
    }
    
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        echo "<script>alert('Anda sudah memberikan ulasan untuk item ini.'); window.location.href='../review.php?transaction_id=$transaction_id';</script>";
        exit();
    }
    
    // Insert review
    if ($item_type == 'product') {
        $sql = "INSERT INTO reviews (transaction_id, product_id, item_type, nama_reviewer, rating, review_text) 
                VALUES ('$transaction_id', '$item_id', 'product', '$nama_reviewer', '$rating', '$review_text')";
    } else {
        $sql = "INSERT INTO reviews (transaction_id, package_id, item_type, nama_reviewer, rating, review_text) 
                VALUES ('$transaction_id', '$item_id', 'package', '$nama_reviewer', '$rating', '$review_text')";
    }
    
    if ($conn->query($sql)) {
        echo "<script>alert('Terima kasih atas ulasan Anda!'); window.location.href='../review.php?transaction_id=$transaction_id';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location.href='../review.php?transaction_id=$transaction_id';</script>";
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
