<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama' => $_POST['nama'],
        'email' => $_POST['email'],
        'pesan' => $_POST['pesan'],
        'jawaban_admin' => '',
        'timestamp' => time()
    ];

    // Simpan di session sementara
    $_SESSION['pertanyaan_umum'][] = $data;

    echo json_encode(['success' => true]);
    exit;
}
?>
