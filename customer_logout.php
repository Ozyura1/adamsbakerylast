<?php
session_start();

// Clear customer session
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);
unset($_SESSION['has_order']);
unset($_SESSION['last_transaction_id']);

// Redirect to home page
header("Location: index.php?logged_out=1");
exit();
?>
