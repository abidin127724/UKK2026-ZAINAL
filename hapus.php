<?php
session_start();

/* ===== CEK LOGIN ADMIN ===== */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

/* ===== KONEKSI DATABASE ===== */
$conn = new mysqli("localhost","root","","ukk");
if ($conn->connect_error) die("Koneksi gagal: ".$conn->connect_error);

/* ===== CEK ID ===== */
$id = $_GET['id'] ?? 0;
$id = intval($id);

if($id > 0){
    $stmt = $conn->prepare("DELETE FROM pengaduan WHERE id_pengaduan=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$conn->close();

/* ===== REDIRECT KEMBALI ===== */
header("Location: daftar_pengaduan.php");
exit;
?>
