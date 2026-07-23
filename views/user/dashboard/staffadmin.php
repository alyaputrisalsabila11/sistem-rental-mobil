<?php
// =========================================================================
// FILE: views/user/dashboard/staffadmin.php (Dashboard Admin Terintegrasi)
// =========================================================================

if (session_status() === PHP_SESSION_NONE) {
    if (!isset($_SESSION)) {
        session_start();
    }
}

require_once __DIR__ . '/../../../init.php';
$db = Database::getConnection();

$brand_name = "SIREMO";
$admin_name = $_SESSION['user_name'] ?? 'Admin Staff';
$id_lokasi_cabang = $_SESSION['id_lokasi'] ?? null;

$action = $_GET['action'] ?? 'home';

// =========================================================
// STATISTIK KESELURUHAN TRANSAKSI DASHBOARD ADMIN
// =========================================================

$total_armada = 0;
$total_booking = 0;
$total_pending = 0;
$total_disewa = 0;
$total_selesai = 0;
$total_maintenance = 0;
$total_pelanggan = 0;
$total_fasilitas = 0;
$total_nilai_transaksi = 0;
$total_biaya_kerusakan = 0;

try {

    // Total armada
    $total_armada = $db->query(
        "SELECT COUNT(*) FROM mobil"
    )->fetchColumn() ?: 0;


    // Total seluruh penyewaan
    $total_booking = $db->query(
        "SELECT COUNT(*) FROM penyewaan"
    )->fetchColumn() ?: 0;


    // Total booking pending
    $total_pending = $db->query(
        "SELECT COUNT(*)
        FROM penyewaan
        WHERE status_penyewaan = 'Pending'"
    )->fetchColumn() ?: 0;


    // Total mobil sedang disewa
    $total_disewa = $db->query(
        "SELECT COUNT(*)
        FROM mobil
        WHERE status_mobil = 'Disewa'"
    )->fetchColumn() ?: 0;


    // Total transaksi selesai
    $total_selesai = $db->query(
        "SELECT COUNT(*)
        FROM penyerahan
        WHERE status_sewa = 'completed'"
    )->fetchColumn() ?: 0;


    // Total mobil maintenance
    $total_maintenance = $db->query(
        "SELECT COUNT(*)
        FROM mobil
        WHERE status_mobil = 'Maintenance'"
    )->fetchColumn() ?: 0;


    // Total pelanggan
    $total_pelanggan = $db->query(
        "SELECT COUNT(*) FROM pelanggan"
    )->fetchColumn() ?: 0;


    // Total fasilitas
    $total_fasilitas = $db->query(
        "SELECT COUNT(*) FROM fasilitas"
    )->fetchColumn() ?: 0;


    // Total nilai seluruh transaksi penyewaan
    $total_nilai_transaksi = $db->query(
        "SELECT COALESCE(SUM(total_harga), 0)
        FROM penyewaan"
    )->fetchColumn() ?: 0;


    // Total biaya kerusakan
    $total_biaya_kerusakan = $db->query(
        "SELECT COALESCE(SUM(biaya_kerusakan), 0)
        FROM pengembalian"
    )->fetchColumn() ?: 0;
} catch (Exception $e) {

    $dashboard_error = $e->getMessage();
}

// AKTIVITAS TRANSAKSI TERBARU
$transaksi_terbaru = [];

try {

    $stmtTransaksi = $db->query(
        "SELECT
            p.id_penyewaan,
            p.kode_penyewaan,
            p.tgl_penyewaan,
            p.tgl_mulai_sewa,
            p.tgl_selesai_sewa,
            p.total_harga,
            p.status_penyewaan,
            pl.nama_lengkap,
            m.merk_mobil,
            m.plat_nomor

        FROM penyewaan p

        JOIN pelanggan pl
            ON p.id_pelanggan = pl.id_pelanggan

        JOIN mobil m
            ON p.id_mobil = m.id_mobil

        ORDER BY p.id_penyewaan DESC

        LIMIT 10"
    );


    $transaksi_terbaru = $stmtTransaksi->fetchAll(
        PDO::FETCH_ASSOC
    );
} catch (Exception $e) {

    $transaksi_terbaru = [];
}

// LOGIKA GRAFIK KOMPARASI KATEGORI SEWA: Sering vs Tidak Disewa
$chart_categories = [];
$chart_sering = [];
$chart_tidak = [];
try {
    $stmtC = $db->query("
        SELECT nama_kategori,
               SUM(CASE WHEN status_mobil = 'Disewa' THEN 1 ELSE 0 END) AS sering_disewa,
               SUM(CASE WHEN status_mobil = 'Tersedia' THEN 1 ELSE 0 END) AS tidak_disewa
        FROM mobil
        GROUP BY nama_kategori
    ");
    $chartData = $stmtC->fetchAll(PDO::FETCH_ASSOC);
    foreach ($chartData as $row) {
        $chart_categories[] = $row['nama_kategori'];
        $chart_sering[] = $row['sering_disewa'];
        $chart_tidak[] = $row['tidak_disewa'];
    }
} catch (Exception $e) {
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
    <!-- Memuat Chart.js untuk render grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100 font-sans flex h-screen overflow-hidden text-sm">

    <?php include __DIR__ . '/../sidebar/staffadmin.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8 flex-shrink-0">
            <h1 class="text-base font-bold text-gray-800 uppercase"><?= str_replace('_', ' ', $action); ?></h1>
            <div class="text-xs font-medium text-gray-600">Admin: <span class="text-indigo-600 font-bold"><?= $admin_name; ?></span></div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">

            <!-- Notifikasi Sesi (Sukses / Gagal Upgrade) -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-4 font-bold">
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4 font-bold flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= $_SESSION['error'];
                            unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <!-- ==================== HOME OPERASIONAL ==================== -->
            <?php if ($action === 'home'): ?>
                <!-- ====================================================== -->
                <!-- RINGKASAN KESELURUHAN TRANSAKSI SIREMO -->
                <!-- ====================================================== -->

                <div class="mb-6">

                    <div class="mb-5">

                        <h2 class="text-lg font-black text-gray-800">
                            Ringkasan Operasional SIREMO
                        </h2>

                        <p class="text-xs text-gray-400 mt-1">
                            Informasi keseluruhan aktivitas transaksi rental mobil
                        </p>

                    </div>


                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">


                        <!-- TOTAL PENYEWAAN -->

                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">

                            <div>

                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Total Penyewaan
                                </p>

                                <p class="text-3xl font-black text-gray-800 mt-1">
                                    <?= $total_booking; ?>
                                </p>

                                <p class="text-[10px] text-gray-400 mt-1">
                                    Seluruh transaksi
                                </p>

                            </div>

                            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">

                                <i class="fa-solid fa-receipt text-lg"></i>

                            </div>

                        </div>


                        <!-- BOOKING PENDING -->

                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">

                            <div>

                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Booking Pending
                                </p>

                                <p class="text-3xl font-black text-amber-600 mt-1">
                                    <?= $total_pending; ?>
                                </p>

                                <p class="text-[10px] text-gray-400 mt-1">
                                    Menunggu proses
                                </p>

                            </div>

                            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">

                                <i class="fa-solid fa-clock text-lg"></i>

                            </div>

                        </div>


                        <!-- MOBIL DISEWA -->

                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">

                            <div>

                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Mobil Sedang Disewa
                                </p>

                                <p class="text-3xl font-black text-blue-600 mt-1">
                                    <?= $total_disewa; ?>
                                </p>

                                <p class="text-[10px] text-gray-400 mt-1">
                                    Unit aktif digunakan
                                </p>

                            </div>

                            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">

                                <i class="fa-solid fa-car-side text-lg"></i>

                            </div>

                        </div>


                        <!-- TRANSAKSI SELESAI -->

                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">

                            <div>

                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Transaksi Selesai
                                </p>

                                <p class="text-3xl font-black text-green-600 mt-1">
                                    <?= $total_selesai; ?>
                                </p>

                                <p class="text-[10px] text-gray-400 mt-1">
                                    Penyewaan selesai
                                </p>

                            </div>

                            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">

                                <i class="fa-solid fa-circle-check text-lg"></i>

                            </div>

                        </div>


                        <!-- MAINTENANCE -->

                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">

                            <div>

                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Mobil Maintenance
                                </p>

                                <p class="text-3xl font-black text-red-600 mt-1">
                                    <?= $total_maintenance; ?>
                                </p>

                                <p class="text-[10px] text-gray-400 mt-1">
                                    Dalam perbaikan
                                </p>

                            </div>

                            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-600">

                                <i class="fa-solid fa-screwdriver-wrench text-lg"></i>

                            </div>

                        </div>


                        <!-- TOTAL PELANGGAN -->

                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">

                            <div>

                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Total Pelanggan
                                </p>

                                <p class="text-3xl font-black text-gray-800 mt-1">
                                    <?= $total_pelanggan; ?>
                                </p>

                                <p class="text-[10px] text-gray-400 mt-1">
                                    Pelanggan terdaftar
                                </p>

                            </div>

                            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">

                                <i class="fa-solid fa-user-group text-lg"></i>

                            </div>

                        </div>


                        <!-- NILAI TRANSAKSI -->

                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">

                            <div>

                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Nilai Transaksi
                                </p>

                                <p class="text-xl font-black text-green-600 mt-2">

                                    Rp <?= number_format(
                                            $total_nilai_transaksi,
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                </p>

                                <p class="text-[10px] text-gray-400 mt-1">
                                    Total transaksi rental
                                </p>

                            </div>

                            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">

                                <i class="fa-solid fa-money-bill-wave text-lg"></i>

                            </div>

                        </div>


                        <!-- BIAYA KERUSAKAN -->

                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">

                            <div>

                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Biaya Kerusakan
                                </p>

                                <p class="text-xl font-black text-red-600 mt-2">

                                    Rp <?= number_format(
                                            $total_biaya_kerusakan,
                                            0,
                                            ',',
                                            '.'
                                        ); ?>

                                </p>

                                <p class="text-[10px] text-gray-400 mt-1">
                                    Total biaya kerusakan
                                </p>

                            </div>

                            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-600">

                                <i class="fa-solid fa-car-burst text-lg"></i>

                            </div>

                        </div>


                    </div>

                </div>

                <!-- ====================================================== -->
                <!-- AKTIVITAS TRANSAKSI TERBARU -->
                <!-- ====================================================== -->

                <div class="bg-white p-6 rounded-2xl border shadow-sm mb-6">

                    <div class="flex items-center justify-between mb-5">

                        <div>

                            <h3 class="font-bold text-gray-800 text-sm">
                                Aktivitas Transaksi Terbaru
                            </h3>

                            <p class="text-[11px] text-gray-400 mt-1">
                                10 transaksi penyewaan terbaru di SIREMO
                            </p>

                        </div>

                    </div>


                    <div class="overflow-x-auto">

                        <table class="w-full text-xs text-left text-gray-500">

                            <thead>

                                <tr class="bg-gray-50 border-b">

                                    <th class="p-4 font-bold">
                                        Kode Transaksi
                                    </th>

                                    <th class="p-4 font-bold">
                                        Pelanggan
                                    </th>

                                    <th class="p-4 font-bold">
                                        Mobil
                                    </th>

                                    <th class="p-4 font-bold">
                                        Periode Sewa
                                    </th>

                                    <th class="p-4 font-bold text-right">
                                        Total
                                    </th>

                                    <th class="p-4 font-bold text-center">
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php if (!empty($transaksi_terbaru)): ?>

                                    <?php foreach ($transaksi_terbaru as $transaksi): ?>

                                        <tr class="border-b hover:bg-gray-50/50">

                                            <td class="p-4">

                                                <p class="font-bold text-indigo-600">

                                                    <?= htmlspecialchars(
                                                        $transaksi['kode_penyewaan']
                                                    ); ?>

                                                </p>

                                                <p class="text-[10px] text-gray-400 mt-1">

                                                    <?= !empty($transaksi['tgl_penyewaan'])
                                                        ? date(
                                                            'd/m/Y H:i',
                                                            strtotime(
                                                                $transaksi['tgl_penyewaan']
                                                            )
                                                        )
                                                        : '-';
                                                    ?>

                                                </p>

                                            </td>


                                            <td class="p-4 font-bold text-gray-700">

                                                <?= htmlspecialchars(
                                                    $transaksi['nama_lengkap']
                                                ); ?>

                                            </td>


                                            <td class="p-4">

                                                <p class="font-bold text-gray-700">

                                                    <?= htmlspecialchars(
                                                        $transaksi['merk_mobil']
                                                    ); ?>

                                                </p>

                                                <p class="text-[10px] text-gray-400 font-mono">

                                                    <?= htmlspecialchars(
                                                        $transaksi['plat_nomor']
                                                    ); ?>

                                                </p>

                                            </td>


                                            <td class="p-4">

                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime(
                                                        $transaksi['tgl_mulai_sewa']
                                                    )
                                                ); ?>

                                                <span class="text-gray-300 mx-1">
                                                    -
                                                </span>

                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime(
                                                        $transaksi['tgl_selesai_sewa']
                                                    )
                                                ); ?>

                                            </td>


                                            <td class="p-4 text-right font-bold text-gray-800">

                                                Rp <?= number_format(
                                                        $transaksi['total_harga'],
                                                        0,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                            </td>


                                            <td class="p-4 text-center">

                                                <?php

                                                $status = $transaksi['status_penyewaan'];

                                                if ($status === 'Pending') {

                                                    $statusClass =
                                                        'bg-amber-100 text-amber-700';
                                                } elseif (
                                                    $status === 'Selesai'
                                                    || $status === 'Completed'
                                                    || $status === 'completed'
                                                ) {

                                                    $statusClass =
                                                        'bg-green-100 text-green-700';
                                                } elseif (
                                                    $status === 'Disewa'
                                                    || $status === 'Aktif'
                                                ) {

                                                    $statusClass =
                                                        'bg-blue-100 text-blue-700';
                                                } else {

                                                    $statusClass =
                                                        'bg-gray-100 text-gray-600';
                                                }

                                                ?>


                                                <span
                                                    class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?= $statusClass; ?>">

                                                    <?= htmlspecialchars($status); ?>

                                                </span>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>


                                <?php else: ?>


                                    <tr>

                                        <td
                                            colspan="6"
                                            class="p-10 text-center italic text-gray-400">

                                            Belum ada aktivitas transaksi penyewaan.

                                        </td>

                                    </tr>


                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

                <!-- GRAFIK BATANG DI DASHBOARD ADMIN (KOMPARASI SEWA KATEGORI) -->
                <div class="bg-white p-6 rounded-2xl border shadow-sm h-96">
                    <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-4">Grafik Komparasi Mobil Sering vs Tidak Disewa Per Kategori</h3>
                    <div class="h-64"><canvas id="adminComparisonChart"></canvas></div>
                </div>

                <script>
                    new Chart(document.getElementById('adminComparisonChart'), {
                        type: 'bar',
                        data: {
                            labels: <?= json_encode($chart_categories); ?>,
                            datasets: [{
                                    label: 'Sering Disewa (In Use)',
                                    data: <?= json_encode($chart_sering); ?>,
                                    backgroundColor: '#4F46E5', // Indigo
                                    borderRadius: 6
                                },
                                {
                                    label: 'Tidak Disewa (Tersedia)',
                                    data: <?= json_encode($chart_tidak); ?>,
                                    backgroundColor: '#E2E8F0', // Slate Gray
                                    borderRadius: 6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                </script>

                <!-- FORM MASTER: TAMBAH UNIT -->
            <?php elseif ($action === 'tambah_mobil'): ?>
                <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl border shadow-sm">
                    <h2 class="text-base font-bold mb-6">Tambah Unit Mobil Baru</h2>
                    <form action="index.php?page=Admin&action=proses_tambah_mobil" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="nama_kategori" placeholder="Kategori (ex: SUV)" class="border p-3 rounded-xl w-full text-xs" required>
                            <input type="text" name="merk_mobil" placeholder="Merk Mobil" class="border p-3 rounded-xl w-full text-xs" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="plat_nomor" placeholder="Plat Nomor" class="border p-3 rounded-xl w-full text-xs" required>
                            <input type="number" name="tahun" placeholder="Tahun" class="border p-3 rounded-xl w-full text-xs" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="number" name="harga_dinamis" placeholder="Harga Sewa / Hari" class="border p-3 rounded-xl w-full text-xs" required>
                            <input type="text" name="warna" placeholder="Warna" class="border p-3 rounded-xl w-full text-xs">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="number" name="cc" placeholder="CC" class="border p-3 rounded-xl w-full text-xs">
                            <select name="status_mobil" class="border p-3 rounded-xl w-full text-xs bg-white">
                                <option value="Tersedia">Tersedia</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="border p-3 rounded-xl bg-gray-50">
                            <input type="file" name="gambar" class="text-xs">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3.5 rounded-xl transition">Simpan Unit</button>
                    </form>
                </div>

                <!-- FORM MASTER: TAMBAH FASILITAS -->
            <?php elseif ($action === 'tambah_fasilitas'): ?>
                <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl border shadow-sm">
                    <h2 class="text-base font-bold mb-6">Tambah Fasilitas Baru</h2>
                    <form action="index.php?page=Admin&action=proses_tambah_fasilitas" method="POST" class="space-y-4">
                        <input type="text" name="nama_fasilitas" placeholder="Nama Fasilitas" class="border p-3 rounded-xl w-full text-xs" required>
                        <textarea name="deskripsi" placeholder="Deskripsi Fasilitas" class="border p-3 rounded-xl w-full text-xs h-24 resize-none"></textarea>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="number" name="harga" placeholder="Harga" class="border p-3 rounded-xl w-full text-xs" required>
                            <input type="number" name="stok" placeholder="Stok" class="border p-3 rounded-xl w-full text-xs" required>
                        </div>
                        <select name="status" class="border p-3 rounded-xl w-full text-xs bg-white">
                            <option value="Tersedia">Tersedia</option>
                            <option value="Habis">Habis</option>
                        </select>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl transition">Simpan Fasilitas</button>
                    </form>
                </div>

                <!-- DATA TABEL: DATA MOBIL -->
            <?php elseif ($action === 'data_mobil'): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm overflow-hidden">
                    <h3 class="font-bold text-gray-800 text-sm mb-4">Daftar Armada Mobil</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500 border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="p-4 font-bold">Mobil</th>
                                    <th class="p-4 font-bold">Plat Nomor</th>
                                    <th class="p-4 font-bold text-right">Harga Harian</th>
                                    <th class="p-4 font-bold text-center">Status</th>
                                    <th class="p-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtM = $db->query("SELECT * FROM mobil ORDER BY id_mobil DESC");
                                $mobils = $stmtM->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($mobils)): foreach ($mobils as $m): ?>
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="p-4 font-bold text-gray-800"><?= $m['merk_mobil']; ?></td>
                                            <td class="p-4 font-mono font-semibold"><?= $m['plat_nomor']; ?></td>
                                            <td class="p-4 text-right">Rp <?= number_format($m['harga_dinamis']); ?></td>
                                            <td class="p-4 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $m['status_mobil'] === 'Tersedia' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>"><?= $m['status_mobil']; ?></span>
                                            </td>
                                            <td class="p-4 text-center whitespace-nowrap">
                                                <a
                                                    href="index.php?page=Admin&action=edit_mobil&id=<?= $m['id_mobil']; ?>"
                                                    class="bg-amber-500 text-white font-bold px-3 py-1.5 rounded-lg mr-1">
                                                    Update
                                                </a>
                                                <a
                                                    href="index.php?page=Admin&action=proses_hapus_mobil&id=<?= $m['id_mobil']; ?>"
                                                    onclick="return confirm('Hapus data mobil ini?')"
                                                    class="bg-red-600 text-white font-bold px-3 py-1.5 rounded-lg">
                                                    Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center italic">Tidak ada armada mobil terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FORM UPDATE DATA MOBIL -->
            <?php elseif ($action === 'edit_mobil'):

                $stmtEdit = $db->prepare(
                    "SELECT * FROM mobil WHERE id_mobil = ?"
                );

                $stmtEdit->execute([
                    $_GET['id'] ?? 0
                ]);

                $em = $stmtEdit->fetch(PDO::FETCH_ASSOC);

            ?>

                <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl border shadow-sm">

                    <h2 class="text-base font-bold mb-6">
                        Update Data Mobil
                    </h2>

                    <form
                        action="index.php?page=Admin&action=proses_update_mobil"
                        method="POST"
                        enctype="multipart/form-data"
                        class="space-y-4">

                        <input
                            type="hidden"
                            name="id_mobil"
                            value="<?= $em['id_mobil']; ?>">


                        <div class="grid grid-cols-2 gap-4">

                            <input
                                type="text"
                                name="nama_kategori"
                                value="<?= htmlspecialchars($em['nama_kategori']); ?>"
                                class="border p-3 rounded-xl"
                                required>

                            <input
                                type="text"
                                name="merk_mobil"
                                value="<?= htmlspecialchars($em['merk_mobil']); ?>"
                                class="border p-3 rounded-xl"
                                required>

                        </div>


                        <div class="grid grid-cols-2 gap-4">

                            <input
                                type="text"
                                name="plat_nomor"
                                value="<?= htmlspecialchars($em['plat_nomor']); ?>"
                                class="border p-3 rounded-xl"
                                required>

                            <input
                                type="number"
                                name="tahun"
                                value="<?= $em['tahun']; ?>"
                                class="border p-3 rounded-xl"
                                required>

                        </div>


                        <div class="grid grid-cols-2 gap-4">

                            <input
                                type="number"
                                name="harga_dinamis"
                                value="<?= $em['harga_dinamis']; ?>"
                                class="border p-3 rounded-xl"
                                required>

                            <input
                                type="text"
                                name="warna"
                                value="<?= htmlspecialchars($em['warna']); ?>"
                                class="border p-3 rounded-xl">

                        </div>


                        <div class="grid grid-cols-2 gap-4">

                            <input
                                type="number"
                                name="cc"
                                value="<?= $em['cc']; ?>"
                                class="border p-3 rounded-xl">

                            <select
                                name="status_mobil"
                                class="border p-3 rounded-xl bg-white">

                                <?php
                                foreach (
                                    ['Tersedia', 'Disewa', 'Maintenance']
                                    as $status
                                ):
                                ?>

                                    <option
                                        value="<?= $status; ?>"
                                        <?= $em['status_mobil'] === $status
                                            ? 'selected'
                                            : ''; ?>>
                                        <?= $status; ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <input
                            type="file"
                            name="gambar"
                            class="border p-3 rounded-xl w-full text-xs">


                        <div class="flex gap-3">

                            <button
                                type="submit"
                                class="flex-1 bg-indigo-600 text-white font-bold py-3 rounded-xl">
                                Simpan Perubahan
                            </button>

                            <a
                                href="index.php?page=Admin&action=data_mobil"
                                class="flex-1 bg-gray-100 text-center font-bold py-3 rounded-xl">
                                Batal
                            </a>

                        </div>

                    </form>

                </div>

                <!-- RE-IMPLEMENTASI DATA FASILITAS -->
            <?php elseif ($action === 'data_fasilitas'): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <h3 class="font-bold text-gray-800 text-sm mb-4">Daftar Fasilitas Rental</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500 border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="p-4 font-bold">Nama Fasilitas</th>
                                    <th class="p-4 font-bold">Deskripsi</th>
                                    <th class="p-4 font-bold text-right">Harga</th>
                                    <th class="p-4 font-bold text-center">Status</th>
                                    <th class="p-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtF = $db->query("SELECT * FROM fasilitas ORDER BY id_fasilitas DESC");
                                $fasilitas = $stmtF->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($fasilitas)): foreach ($fasilitas as $f): ?>
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="p-4 font-bold text-gray-800"><?= $f['nama_fasilitas']; ?></td>
                                            <td class="p-4 text-gray-400"><?= htmlspecialchars($f['deskripsi'] ?? '-'); ?></td>
                                            <td class="p-4 text-right font-semibold text-indigo-600">Rp <?= number_format($f['harga']); ?></td>
                                            <td class="p-4 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700"><?= $f['status']; ?></span>
                                            </td>
                                            <td class="p-4 text-center whitespace-nowrap">

                                                <a
                                                    href="index.php?page=Admin&action=edit_fasilitas&id=<?= $f['id_fasilitas']; ?>"
                                                    class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-bold px-3 py-1.5 rounded-lg mr-1">

                                                    Update

                                                </a>


                                                <a
                                                    href="index.php?page=Admin&action=proses_hapus_fasilitas&id=<?= $f['id_fasilitas']; ?>"
                                                    onclick="return confirm('Yakin ingin menghapus fasilitas ini?')"
                                                    class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold px-3 py-1.5 rounded-lg">

                                                    Delete

                                                </a>

                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center italic">Belum ada data fasilitas terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FORM UPDATE FASILITAS -->
            <?php elseif ($action === 'edit_fasilitas'): ?>
                <?php
                $stmtEditFasilitas = $db->prepare(
                    "SELECT * FROM fasilitas
        WHERE id_fasilitas = ?"
                );
                $stmtEditFasilitas->execute([
                    $_GET['id'] ?? 0
                ]);
                $ef = $stmtEditFasilitas->fetch(
                    PDO::FETCH_ASSOC
                );
                ?>
                <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl border shadow-sm">
                    <h2 class="text-base font-bold mb-6">
                        Update Data Fasilitas
                    </h2>
                    <form
                        action="index.php?page=Admin&action=proses_update_fasilitas"
                        method="POST"
                        class="space-y-4">
                        <input
                            type="hidden"
                            name="id_fasilitas"
                            value="<?= $ef['id_fasilitas']; ?>">
                        <input
                            type="text"
                            name="nama_fasilitas"
                            placeholder="Nama Fasilitas"
                            value="<?= htmlspecialchars(
                                        $ef['nama_fasilitas']
                                    ); ?>"
                            class="border p-3 rounded-xl w-full text-xs"
                            required>
                        <textarea
                            name="deskripsi"
                            placeholder="Deskripsi Fasilitas"
                            class="border p-3 rounded-xl w-full text-xs h-24 resize-none"><?= htmlspecialchars(
                                                                                                $ef['deskripsi'] ?? ''
                                                                                            ); ?></textarea>
                        <div class="grid grid-cols-2 gap-4">
                            <input
                                type="number"
                                name="harga"
                                placeholder="Harga"
                                value="<?= $ef['harga']; ?>"
                                class="border p-3 rounded-xl w-full text-xs"
                                required>
                            <input
                                type="number"
                                name="stok"
                                placeholder="Stok"
                                value="<?= $ef['stok']; ?>"
                                class="border p-3 rounded-xl w-full text-xs"
                                required>
                        </div>
                        <select
                            name="status"
                            class="border p-3 rounded-xl w-full text-xs bg-white">
                            <option
                                value="Tersedia"
                                <?= $ef['status'] === 'Tersedia'
                                    ? 'selected'
                                    : '';
                                ?>>
                                Tersedia
                            </option>
                            <option
                                value="Habis"
                                <?= $ef['status'] === 'Habis'
                                    ? 'selected'
                                    : '';
                                ?>>
                                Habis
                            </option>
                        </select>
                        <div class="flex gap-3">
                            <button
                                type="submit"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl transition">
                                Simpan Perubahan
                            </button>
                            <a
                                href="index.php?page=Admin&action=data_fasilitas"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-center font-bold py-3.5 rounded-xl transition">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>

                <!-- RE-IMPLEMENTASI DATA VOUCHER -->
            <?php elseif ($action === 'data_voucher'): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <h3 class="font-bold text-gray-800 text-sm mb-4">Daftar Voucher Promo</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500 border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="p-4 font-bold">Kode Voucher</th>
                                    <th class="p-4 font-bold">Nama Voucher</th>
                                    <th class="p-4 font-bold text-right">Diskon Persen</th>
                                    <th class="p-4 font-bold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtV = $db->query("SELECT * FROM voucher ORDER BY id_voucher DESC");
                                $vouchers = $stmtV->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($vouchers)): foreach ($vouchers as $v): ?>
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="p-4 font-mono font-bold text-indigo-600"><?= $v['kode_voucher']; ?></td>
                                            <td class="p-4"><?= $v['nama_voucher']; ?></td>
                                            <td class="p-4 text-right font-bold text-green-600"><?= $v['diskon_persen']; ?>%</td>
                                            <td class="p-4 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700"><?= $v['status']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center italic">Belum ada data voucher terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABEL DATA PELANGGAN + MODAL UPGRADE DENGAN VALIDASI SYARAT POIN -->
            <?php elseif ($action === 'data_pelanggan'): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <h3 class="font-bold text-gray-800 text-sm mb-4">Daftar Pelanggan / Member SIREMO</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500 border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b text-gray-600">
                                    <th class="p-4 font-bold">Nama Lengkap</th>
                                    <th class="p-4 font-bold text-center">Level Loyalitas</th>
                                    <th class="p-4 font-bold text-center">Email / No Telp</th>
                                    <th class="p-4 font-bold text-right">Aksi Poin & Upgrade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtPel = $db->query("SELECT p.*, l.nama_level FROM pelanggan p LEFT JOIN loyalitas l ON p.id_level = l.id_level ORDER BY p.id_pelanggan DESC");
                                $pels = $stmtPel->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($pels)): foreach ($pels as $p): ?>
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($p['nama_lengkap']); ?></td>
                                            <td class="p-4 text-center">
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                                    <?= $p['nama_level'] ?? 'Regular / Bronze'; ?>
                                                </span>
                                            </td>
                                            <td class="p-4 text-center">
                                                <div><?= htmlspecialchars($p['email']); ?></div>
                                                <div class="text-[10px] text-gray-400 mt-0.5"><?= htmlspecialchars($p['no_telp']); ?></div>
                                            </td>
                                            <td class="p-4 flex items-center justify-end gap-3">
                                                <span class="font-black text-amber-500 text-base"><?= $p['poin'] ?? 0; ?> pts</span>
                                                <button onclick="bukaModalUpgrade(<?= $p['id_pelanggan']; ?>, '<?= htmlspecialchars($p['nama_lengkap']); ?>')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[10px] px-3.5 py-1.5 rounded-xl transition shadow">
                                                    Upgrade
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="4" class="p-8 text-center italic">Belum ada pelanggan terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- MODAL POPUP UPGRADE LOYALITAS -->
                <div id="modalConfirmUpgrade" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                    <div class="bg-white p-6 rounded-3xl max-w-sm w-full shadow-2xl space-y-4">
                        <h3 class="font-black text-base text-gray-800 text-center">Yakin untuk upgrade?</h3>
                        <p class="text-xs text-gray-500 text-center">Tingkatkan status tingkat loyalitas pelanggan <strong id="nama_target" class="text-indigo-600"></strong>.</p>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Pilih Tingkat Loyalitas</label>
                            <select id="select_loyalitas_id" class="w-full border p-2.5 rounded-xl bg-gray-50 text-xs font-bold text-gray-700">
                                <?php
                                $stmtL = $db->query("SELECT * FROM loyalitas WHERE status = 'Aktif'");
                                while ($l = $stmtL->fetch(PDO::FETCH_ASSOC)):
                                ?>
                                    <option value="<?= $l['id_level']; ?>"><?= $l['nama_level']; ?> Member</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button onclick="eksekusiUpgrade()" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition">Ya, Upgrade</button>
                            <button onclick="tutupModalUpgrade()" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">Kembali</button>
                        </div>
                    </div>
                </div>

                <script>
                    let currentPelangganId = null;

                    function bukaModalUpgrade(id, nama) {
                        currentPelangganId = id;
                        document.getElementById('nama_target').innerText = nama;
                        document.getElementById('modalConfirmUpgrade').classList.remove('hidden');
                    }

                    function tutupModalUpgrade() {
                        document.getElementById('modalConfirmUpgrade').classList.add('hidden');
                    }

                    function eksekusiUpgrade() {
                        const idLevelTujuan = document.getElementById('select_loyalitas_id').value;
                        window.location.href = `index.php?page=Admin&action=proses_upgrade_loyalitas&id_pelanggan=${currentPelangganId}&id_level=${idLevelTujuan}`;
                    }
                </script>

                <!-- DATA KERUSAKAN MOBIL -->
            <?php elseif ($action === 'data_kerusakan'): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">
                                Data Kerusakan Mobil
                            </h3>
                            <p class="text-[11px] text-gray-400 mt-1">
                                Data hasil pemeriksaan kerusakan oleh Staff Lapangan
                            </p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500 border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="p-4 font-bold">
                                        Mobil
                                    </th>
                                    <th class="p-4 font-bold">
                                        Jenis Kerusakan
                                    </th>
                                    <th class="p-4 font-bold">
                                        Kondisi
                                    </th>
                                    <th class="p-4 font-bold">
                                        Catatan Staff Lapangan
                                    </th>
                                    <th class="p-4 font-bold text-right">
                                        Biaya Kerusakan
                                    </th>
                                    <th class="p-4 font-bold text-center">
                                        Status Mobil
                                    </th>
                                    <th class="p-4 font-bold text-center">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtKerusakan = $db->query(
                                    "SELECT
                        k.id_kondisi AS id_pengembalian,
                        k.tingkat_kerusakan AS kondisi_mobil,
                        k.deskripsi_kerusakan AS catatan,
                        k.deskripsi_kerusakan AS nama_kerusakan,
                        k.estimasi_biaya AS biaya_kerusakan,
                        m.id_mobil,
                        m.merk_mobil,
                        m.plat_nomor,
                        m.status_mobil
                    FROM kondisi_mobil k
                    JOIN mobil m
                        ON k.id_mobil = m.id_mobil
                    ORDER BY k.id_kondisi DESC"
                                );
                                $dataKerusakan = $stmtKerusakan->fetchAll(
                                    PDO::FETCH_ASSOC
                                );
                                if (!empty($dataKerusakan)):
                                    foreach ($dataKerusakan as $kerusakan):
                                ?>
                                        <tr class="border-b hover:bg-gray-50/50">
                                            <td class="p-4">
                                                <p class="font-bold text-gray-800">
                                                    <?= htmlspecialchars(
                                                        $kerusakan['merk_mobil']
                                                    ); ?>
                                                </p>
                                                <p class="text-[10px] text-gray-400 font-mono">
                                                    <?= htmlspecialchars(
                                                        $kerusakan['plat_nomor']
                                                    ); ?>
                                                </p>
                                            </td>
                                            <td class="p-4 font-bold text-gray-700">
                                                <?= htmlspecialchars(
                                                    $kerusakan['nama_kerusakan']
                                                        ?? 'Kerusakan Lainnya'
                                                ); ?>
                                            </td>
                                            <td class="p-4">
                                                <?= htmlspecialchars(
                                                    $kerusakan['kondisi_mobil']
                                                ); ?>
                                            </td>
                                            <td class="p-4 max-w-xs">
                                                <?= htmlspecialchars(
                                                    $kerusakan['catatan']
                                                        ?? '-'
                                                ); ?>
                                            </td>
                                            <td class="p-4 text-right font-bold text-red-600">
                                                Rp <?= number_format(
                                                        $kerusakan['biaya_kerusakan']
                                                    ); ?>
                                            </td>
                                            <td class="p-4 text-center">
                                                <?php if (
                                                    $kerusakan['status_mobil']
                                                    === 'Maintenance'
                                                ): ?>
                                                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-[10px] font-bold">
                                                        Maintenance
                                                    </span>
                                                <?php else: ?>
                                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-[10px] font-bold">
                                                        Tersedia
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="p-4 text-center whitespace-nowrap">
                                                <?php if (
                                                    $kerusakan['status_mobil']
                                                    === 'Maintenance'
                                                ): ?>
                                                    <a
                                                        href="index.php?page=Admin&action=proses_selesai_perbaikan&id_pengembalian=<?= $kerusakan['id_pengembalian']; ?>"
                                                        onclick="return confirm('Apakah perbaikan mobil sudah selesai? Status mobil akan berubah menjadi Tersedia.')"
                                                        class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold px-3 py-2 rounded-lg">
                                                        Selesai Perbaikan
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-green-600 font-bold">
                                                        Selesai
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php
                                    endforeach;
                                else:
                                    ?>
                                    <tr>
                                        <td
                                            colspan="7"
                                            class="p-10 text-center italic text-gray-400">
                                            Belum ada laporan kerusakan dari Staff Lapangan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>