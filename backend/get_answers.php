<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['pertanyaan_umum'])) {
    $_SESSION['pertanyaan_umum'] = [];
}

echo json_encode($_SESSION['pertanyaan_umum']);
