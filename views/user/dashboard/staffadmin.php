<?php
if (session_status() === PHP_SESSION_NONE) {
    if (!isset($_SESSION)) { session_start(); }
}

require_once __DIR__ . '/../../init.php';
$db = Database::getConnection();

$brand_name = "SIREMO";
$admin_name = $_SESSION['user_name'] ?? 'Admin Staff';
$admin_email = $_SESSION['user_email'] ?? '@staffadmin.swm';

// Menentukan halaman aktif berdasarkan parameter 'action' di URL (Default: home)
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

if ($action === 'home') {
    // A. Menghitung Total Armada (Mobil)
    $stmtMobil = $db->query("SELECT COUNT(*) AS total FROM mobil");
    $total_mobil = $stmtMobil->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // B. Menghitung Total Transaksi (Booking) Aktif/Selesai
    $stmtBooking = $db->query("SELECT COUNT(*) AS total FROM booking");
    $total_booking = $stmtBooking->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmtPelanggan = $db->query("SELECT COUNT(*) AS total FROM pelanggan");
    $total_pelanggan = $stmtPelanggan->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmtFasilitas = $db->query("SELECT COUNT(*) AS total FROM fasilitas");
    $total_fasilitas = $stmtFasilitas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

} elseif ($action === 'data_mobil') {
    $stmtMobil = $db->query("SELECT * FROM mobil ORDER BY id_mobil DESC");
    $mobil_list = $stmtMobil->fetchAll(PDO::FETCH_ASSOC);

    $stmtFasilitas = $db->query("SELECT * FROM fasilitas ORDER BY id_fasilitas DESC");
    $fasilitas_list = $stmtFasilitas->fetchAll(PDO::FETCH_ASSOC);

} elseif ($action === 'booking') {
    $queryBooking = "SELECT b.kode_booking, b.id_pelanggan, b.id_mobil, b.durasi_hari, b.total_harga, b.status_booking, p.nama_lengkap, m.merk_mobil 
                     FROM booking b
                     LEFT JOIN pelanggan p ON b.id_pelanggan = p.id_pelanggan
                     LEFT JOIN mobil m ON b.id_mobil = m.id_mobil
                     ORDER BY b.id_booking DESC";
    $stmtBooking = $db->query($queryBooking);
    $booking_list = $stmtBooking->fetchAll(PDO::FETCH_ASSOC);

} elseif ($action === 'data_pelanggan') {
    $pelangganModel = new PelangganModel();
    $daftar_pelanggan = $pelangganModel->getAllPelanggan();

} elseif ($action === 'konfirmasi_booking') {
    $sqlPending = "SELECT b.*, m.merk_mobil, p.nama_lengkap as nama_pelanggan
                   FROM booking b
                   JOIN mobil m ON b.id_mobil = m.id_mobil
                   JOIN pelanggan p ON b.id_pelanggan = p.id
                   WHERE b.status_booking = 'Pending'
                   ORDER BY b.id_booking ASC";
    $stmtPending = $db->query($sqlPending);
    $pendingBookings = $stmtPending->fetchAll(PDO::FETCH_ASSOC);
    
    $sqlSopir = "SELECT id_karyawan, nama_karyawan, no_telp FROM karyawan WHERE role = 'Staff Lapangan'";
    $stmtSopir = $db->query($sqlSopir);
    $daftar_sopir = $stmtSopir->fetchAll(PDO::FETCH_ASSOC);
} elseif ($action === 'fasilitas') {
    $stmtFasilitas = $db->query("SELECT * FROM fasilitas ORDER BY id_fasilitas DESC");
    $fasilitas_list = $stmtFasilitas->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= $brand_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans flex h-screen overflow-hidden">
<!-- Sidebar -->
<?php include __DIR__ . '/../sidebar/staffadmin.php'; ?>

<!-- Main Content -->