<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = $_POST['index']; // index pertanyaan yang dijawab
    $jawaban = $_POST['jawaban'];

    if (isset($_SESSION['pertanyaan_umum'][$index])) {
        $_SESSION['pertanyaan_umum'][$index]['jawaban_admin'] = $jawaban;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Pertanyaan tidak ditemukan.']);
    }
    exit;
}
?>
