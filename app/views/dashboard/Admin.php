<?php
if (session_status() === PHP_SESSION_NONE) {
    if (!isset($_SESSION)) { session_start(); }
}

require_once __DIR__ . '/../../../config/database.php';
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
    require_once __DIR__ . '/../../models/PelangganModel.php';
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

    <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 shadow-xl">
        <div class="p-5 flex items-center space-x-3 border-b border-slate-800">
            <i class="fas fa-sliders-h text-xl text-indigo-400"></i>
            <span class="text-xl font-black tracking-wider uppercase"><?= $brand_name; ?></span>
        </div>

        <div class="p-5 border-b border-slate-800 bg-slate-950/40 flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-bold text-white shadow shadow-indigo-500/50 flex-shrink-0">
                SA
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold truncate"><?= htmlspecialchars($admin_name); ?></p>
                <p class="text-[11px] text-indigo-300 font-medium truncate"><?= htmlspecialchars($admin_email); ?></p>
                <span class="inline-block mt-1 px-1.5 py-0.5 text-[9px] font-bold bg-indigo-500/20 text-indigo-300 rounded border border-indigo-500/30">Staff Admin</span>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <a href="index.php?page=home_admin&action=home" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'home' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-chart-line text-base w-5 text-center group-hover:text-indigo-400 transition"></i>
                <span>Dashboard</span>
            </a>

            <a href="index.php?page=home_admin&action=tambah_mobil" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'tambah_mobil' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-plus-circle text-base w-5 text-center group-hover:text-indigo-400 transition"></i>
                <span>Tambah Unit</span>
            </a>

            <a href="index.php?page=home_admin&action=data_mobil" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'data_mobil' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-car-side text-base w-5 text-center group-hover:text-indigo-400 transition"></i>
                <span>Data Mobil</span>
            </a>

            <a href="index.php?page=home_admin&action=konfirmasi_booking" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'konfirmasi_booking' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-file-invoice-dollar text-base w-5 text-center group-hover:text-indigo-400 transition"></i> 
                <span>Konfirmasi Booking</span>
            </a>

            <a href="index.php?page=home_admin&action=data_pelanggan" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'data_pelanggan' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-users text-base w-5 text-center group-hover:text-indigo-400 transition"></i>
                <span>Data Pelanggan</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="index.php?page=logout" class="flex items-center justify-center space-x-2 w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition shadow-md">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar Sistem</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        
        <header class="bg-white shadow-sm border-b border-gray-100 h-16 flex items-center justify-between px-8 flex-shrink-0">
            <div>
                <h1 class="text-lg font-bold text-gray-800 uppercase tracking-wide">
                    <?php 
                        if ($action === 'home') echo 'DASHBOARD UTAMA';
                        elseif ($action === 'tambah_mobil') echo 'TAMBAH UNIT BARU';
                        elseif ($action === 'data_mobil') echo 'MANAJEMEN ARMADA';
                        elseif ($action === 'data_pelanggan') echo 'DATA PELANGGAN';
                        elseif ($action === 'konfirmasi_booking') echo 'VALIDASI PENYEWAAN';
                    ?>
                </h1>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span>Log masuk sebagai:</span>
                <span class="font-bold text-gray-800"><?= htmlspecialchars($admin_name); ?></span>
                <span class="w-2 h-2 bg-emerald-500 rounded-full inline-block animate-pulse ml-1"></span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl flex items-center space-x-3 shadow-sm">
                    <i class="fas fa-check-circle text-lg"></i>
                    <span class="text-sm font-medium"><?= $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($action === 'home'): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Armada Mobil</p>
                            <h3 class="text-2xl font-black text-gray-800 mt-1"><?= $total_mobil; ?> Unit</h3>
                        </div>
                        <div class="p-3.5 bg-indigo-50 text-indigo-600 rounded-xl"><i class="fas fa-car text-xl"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Fasilitas</p>
                            <h3 class="text-2xl font-black text-gray-800 mt-1"><?= $total_fasilitas; ?> Unit</h3>
                        </div>
                        <div class="p-3.5 bg-indigo-50 text-indigo-600 rounded-xl"><i class="fas fa-car text-xl"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pelanggan Terdaftar</p>
                            <h3 class="text-2xl font-black text-gray-800 mt-1"><?= $total_pelanggan; ?> User</h3>
                        </div>
                        <div class="p-3.5 bg-emerald-50 text-emerald-600 rounded-xl"><i class="fas fa-users text-xl"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Unit Sedang Dibooking/Disewa</p>
                            <h3 class="text-2xl font-black text-gray-800 mt-1"><?= $total_booking; ?> Unit</h3>
                        </div>
                        <div class="p-3.5 bg-amber-50 text-amber-600 rounded-xl"><i class="fas fa-key text-xl"></i></div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b pb-4 mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 flex items-center space-x-2">
                                <i class="fas fa-fire text-amber-500"></i> <span>Unit Terlaris Bulan Ini</span>
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Koleksi armada terfavorit dengan frekuensi sewa tertinggi</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50 flex items-center space-x-4">
                            <div class="p-3 bg-amber-500 text-white rounded-lg shadow-sm shadow-amber-500/40"><i class="fas fa-crown"></i></div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Toyota Innova Zenix</h4>
                                <p class="text-xs text-gray-500 mt-0.5">148 Transaksi</p>
                            </div>
                        </div>
                        <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50 flex items-center space-x-4">
                            <div class="p-3 bg-slate-400 text-white rounded-lg shadow-sm"><i class="fas fa-medal"></i></div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Toyota Avanza Facelift</h4>
                                <p class="text-xs text-gray-500 mt-0.5">112 Transaksi</p>
                            </div>
                        </div>
                        <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50 flex items-center space-x-4">
                            <div class="p-3 bg-amber-700 text-white rounded-lg shadow-sm"><i class="fas fa-award"></i></div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Mitsubishi Pajero Sport</h4>
                                <p class="text-xs text-gray-500 mt-0.5">89 Transaksi</p>
                            </div>
                        </div>
                    </div>
                </div>

<?php elseif ($action === 'tambah_mobil'): ?>
                <?php
                // ==========================================================
                // 1. PROSES FORM ATAS: SIMPAN UNIT MOBIL
                // ==========================================================
                if (isset($_POST['submit_mobil'])) {
                    try {
                        $nama_kategori = $_POST['nama_kategori'];
                        $merk_mobil    = $_POST['merk_mobil'];
                        $plat_nomor    = $_POST['plat_nomor'];
                        $tahun         = $_POST['tahun'];
                        $harga_dinamis = $_POST['harga_dinamis'];
                        $tipe          = $_POST['tipe'];
                        $warna         = $_POST['warna'];
                        $cc            = $_POST['cc'];
                        $status_mobil  = "Tersedia";
                        $isi_gambar = null;

                        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
                            $file_tmp  = $_FILES['gambar']['tmp_name'];
                            $file_ext  = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
                            $ekstensi_boleh = ['jpg', 'jpeg', 'png', 'webp'];
                            
                            if (in_array($file_ext, $ekstensi_boleh)) {
                                $isi_gambar = file_get_contents($file_tmp); 
                            } else {
                                throw new Exception("Format gambar harus JPG, JPEG, PNG, atau WEBP!");
                            }
                        }

                        $sql = "INSERT INTO mobil (nama_kategori, merk_mobil, plat_nomor, tahun, harga_dinamis, tipe, warna, cc, gambar, status_mobil) 
                                VALUES (:nama_kategori, :merk_mobil, :plat_nomor, :tahun, :harga_dinamis, :tipe, :warna, :cc, :gambar, :status_mobil)";

                        $stmt = $db->prepare($sql);
                        $simpan = $stmt->execute([
                            ':nama_kategori' => $nama_kategori,
                            ':merk_mobil'    => $merk_mobil,
                            ':plat_nomor'    => $plat_nomor,
                            ':tahun'         => $tahun,
                            ':harga_dinamis' => $harga_dinamis,
                            ':tipe'          => $tipe,
                            ':warna'         => $warna,
                            ':cc'            => $cc,
                            ':gambar'        => $isi_gambar,
                            ':status_mobil'  => $status_mobil
                        ]);
                        
                        if ($simpan) {
                            $_SESSION['success'] = "Unit armada baru ($merk_mobil) berhasil disimpan!";
                            echo "<script>window.location.href = 'index.php?page=home_admin&action=data_mobil';</script>";
                            exit;
                        }
                    } catch (Exception $e) {
                        $error_mobil = $e->getMessage();
                    }
                }

                if (isset($_POST['submit_fasilitas'])) {
                    try {
                        $nama_fasilitas  = $_POST['nama_fasilitas'];
                        $harga_fasilitas = $_POST['harga'];
                        $deskripsi       = $_POST['deskripsi'];
                        $stok            = $_POST['stok'];
                        $status          = "Tersedia";

                        $sqlFas = "INSERT INTO fasilitas (nama_fasilitas, harga, stok, deskripsi, status) 
                                   VALUES (:nama_fasilitas, :harga, :stok, :deskripsi, :status)";

                        $stmtFas = $db->prepare($sqlFas);
                        $simpanFas = $stmtFas->execute([
                            ':nama_fasilitas'   => $nama_fasilitas,
                            ':harga'            => $harga_fasilitas,
                            ':stok'             => $stok,
                            ':deskripsi'        => $deskripsi,
                            ':status'           => $status
                        ]);
                        
                        if ($simpanFas) {
                            $_SESSION['success'] = "Fasilitas baru ($nama_fasilitas) berhasil ditambahkan!";
                            echo "<script>window.location.href = 'index.php?page=home_admin&action=tambah_mobil';</script>";
                            exit;
                        }
                    } catch (Exception $e) {
                        $error_fasilitas = $e->getMessage();
                    }
                }
                ?>

                <div class="space-y-8">
                    
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <h2 class="border-b pb-3 mb-5 text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-plus-circle text-indigo-500"></i> Form Tambah Unit Kendaraan
                        </h2>

                        <?php if (isset($error_mobil)): ?>
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs font-medium flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle"></i> <?= $error_mobil; ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Nama / Merk Mobil</label>
                                    <input type="text" name="merk_mobil" required placeholder="Contoh: Toyota Avanza" class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:outline-indigo-500 bg-gray-50/50">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Kategori Mobil</label>
                                    <select name="nama_kategori" required class="w-full border border-gray-200 p-2.5 rounded-xl text-sm bg-white focus:outline-indigo-500">
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="MPV">MPV</option>
                                        <option value="SUV">SUV</option>
                                        <option value="Pick-up">Pick-up</option>
                                        <option value="Sedan">Sedan</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Nomor Polisi (Plat)</label>
                                    <input type="text" name="plat_nomor" required placeholder="B 1234 ABC" class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:outline-indigo-500 bg-gray-50/50">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Harga Sewa / Hari</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-xs font-bold">Rp</span>
                                        <input type="number" name="harga_dinamis" required placeholder="500000" class="w-full pl-9 pr-3 border border-gray-200 p-2.5 rounded-xl text-sm focus:outline-indigo-500 bg-gray-50/50">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Tahun Mobil</label>
                                    <input type="number" name="tahun" min="2018" max="2027" required placeholder="2024" class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:outline-indigo-500 bg-gray-50/50">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Transmisi</label>
                                    <div class="flex gap-4 mt-2.5 text-sm font-medium text-gray-700">
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="tipe" value="matic" checked class="w-4 h-4 text-indigo-600 border-gray-300 mr-2"> Automatic</label>
                                        <label class="flex items-center cursor-pointer"><input type="radio" name="tipe" value="manual" class="w-4 h-4 text-indigo-600 border-gray-300 mr-2"> Manual</label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Kapasitas Mesin (CC)</label>
                                    <input type="number" name="cc" required placeholder="1500" class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:outline-indigo-500 bg-gray-50/50">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Warna Kendaraan</label>
                                    <input type="text" name="warna" required placeholder="Contoh: Hitam Metalik" class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:outline-indigo-500 bg-gray-50/50">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Foto Unit Mobil</label>
                                    <input type="file" name="gambar" class="w-full border border-gray-200 p-2 rounded-xl text-sm bg-white focus:outline-indigo-500 file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="text-[10px] text-gray-400 mt-1">*Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</p>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" name="submit_mobil" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                                    <i class="fas fa-save mr-1.5"></i> Simpan Unit Mobil
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <h2 class="border-b pb-3 mb-5 text-lg font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-plus-circle text-emerald-500"></i> Form Tambah Pilihan Fasilitas Baru
                        </h2>

                        <?php if (isset($error_fasilitas)): ?>
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs font-medium flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle"></i> <?= $error_fasilitas; ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Nama Fasilitas / Layanan</label>
                                    <input type="text" name="nama_fasilitas" required placeholder="Contoh: Roofbox" class="w-full border border-gray-200 p-2.5 rounded-xl text-xs focus:outline-emerald-500 bg-gray-50/50">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Biaya Layanan / Hari</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-xs font-bold">Rp</span>
                                        <input type="number" name="harga" required placeholder="150000" class="w-full pl-9 pr-3 border border-gray-200 p-2.5 rounded-xl text-xs focus:outline-emerald-500 bg-gray-50/50">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Stok</label>
                                    <input type="number" name="stok" required placeholder="10" class="w-full border border-gray-200 p-2.5 rounded-xl text-xs focus:outline-emerald-500 bg-gray-50/50">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Deskripsi Fasilitas</label>
                                    <textarea name="deskripsi" rows="3" required placeholder="Tuliskan keterangan singkat kegunaan layanan tambahan ini..." class="w-full border border-gray-200 p-2.5 rounded-xl text-xs focus:outline-emerald-500 bg-gray-50/50"></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" name="submit_fasilitas" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                                    <i class="fas fa-plus-circle mr-1.5"></i> Daftarkan Fasilitas Baru
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($action === 'data_mobil'): ?>
                <div class="space-y-8">
                    
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base font-bold text-gray-800 uppercase tracking-wide">Daftar Armada Kendaraan</h2>
                                <p class="text-xs text-gray-500 mt-0.5">Menampilkan seluruh unit mobil yang terdaftar di dalam sistem.</p>
                            </div>
                            <a href="index.php?page=home_admin&action=tambah_mobil" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Unit
                            </a>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table class="w-full min-w-[1000px] text-sm text-left text-gray-500 table-auto">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3">Unit Mobil</th>
                                        <th class="px-4 py-3">Kategori</th>
                                        <th class="px-4 py-3">No. Plat</th>
                                        <th class="px-4 py-3">Transmisi</th>
                                        <th class="px-4 py-3">Warna</th>
                                        <th class="px-4 py-3">Kapasitas (CC)</th>
                                        <th class="px-4 py-3">Harga/Hari</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <?php if (!empty($mobil_list)): ?>
                                        <?php foreach ($mobil_list as $mobil): ?>
                                            <tr class="hover:bg-gray-50/75 transition">
                                                <td class="px-4 py-4 font-bold text-gray-900 whitespace-nowrap">
                                                    <?= htmlspecialchars($mobil['merk_mobil']); ?> 
                                                    <span class="text-xs text-gray-400 font-normal">(<?= htmlspecialchars($mobil['tahun']); ?>)</span>
                                                </td>
                                                <td class="px-4 py-4 text-xs font-medium text-gray-600 whitespace-nowrap">
                                                    <?= htmlspecialchars($mobil['nama_kategori'] ?? '-'); ?>
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap">
                                                    <span class="text-xs font-mono bg-gray-100 border border-gray-200 rounded-lg px-2 py-0.5 text-gray-800">
                                                        <?= htmlspecialchars($mobil['plat_nomor']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 text-xs capitalize text-gray-700 whitespace-nowrap">
                                                    <?= htmlspecialchars($mobil['tipe']); ?>
                                                </td>
                                                <td class="px-4 py-4 text-xs text-gray-600 whitespace-nowrap">
                                                    <?= htmlspecialchars($mobil['warna']); ?>
                                                </td>
                                                <td class="px-4 py-4 text-xs text-gray-600 whitespace-nowrap">
                                                    <?= htmlspecialchars($mobil['cc']); ?> CC
                                                </td>
                                                <td class="px-4 py-4 font-semibold text-gray-800 whitespace-nowrap">
                                                    Rp <?= number_format($mobil['harga_dinamis'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="px-4 py-4 text-xs whitespace-nowrap">
                                                    <?php 
                                                    $status = $mobil['status_mobil'];
                                                    if ($status === 'Tersedia') {
                                                        echo '<span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-medium">Tersedia</span>';
                                                    } elseif ($status === 'Disewa' || $status === 'Dibooking') {
                                                        echo '<span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full font-medium">'.$status.'</span>';
                                                    } else {
                                                        echo '<span class="px-2 py-0.5 bg-gray-50 text-gray-600 border border-gray-200 rounded-full font-medium">'.$status.'</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="px-4 py-4 text-center space-x-1 whitespace-nowrap">
                                                    <a href="index.php?page=home_admin&action=edit_mobil&id=<?= $mobil['id_mobil']; ?>" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded text-xs font-bold border border-amber-200 transition">Edit</a>
                                                    <a href="index.php?page=home_admin&action=hapus_mobil&id=<?= $mobil['id_mobil']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus mobil ini?')" class="px-2 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded text-xs font-bold border border-red-200 transition">Hapus</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan=\"9\" class=\"px-4 py-8 text-center text-gray-400 italic\">
                                                Belum ada data unit mobil di database.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-base font-bold text-gray-800 uppercase tracking-wide">Daftar Pilihan Fasilitas / Layanan</h2>
                                <p class="text-xs text-gray-500 mt-0.5">Menampilkan seluruh layanan tambahan opsional yang bisa dipilih pelanggan.</p>
                            </div>
                            <a href="index.php?page=home_admin&action=tambah_mobil" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Fasilitas
                            </a>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table class="w-full text-sm text-left text-gray-500 table-auto">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 w-[25%]">Nama Fasilitas</th>
                                        <th class="px-4 py-3 w-[20%]">Biaya / Hari</th>
                                        <th class="px-4 py-3 w-[20%]">Stok</th>
                                        <th class="px-4 py-3 w-[40%]">Deskripsi Keterangan</th>
                                        <th class="px-4 py-3 text-center w-[15%]">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <?php if (!empty($fasilitas_list)): ?>
                                        <?php foreach ($fasilitas_list as $fasilitas): ?>
                                            <tr class="hover:bg-gray-50/75 transition">
                                                <td class="px-4 py-4 font-bold text-gray-900 whitespace-nowrap">
                                                    <i class="fas fa-concierge-bell text-emerald-500 mr-1.5 text-xs"></i>
                                                    <?= htmlspecialchars($fasilitas['nama_fasilitas']); ?>
                                                </td>
                                                <td class="px-4 py-4 font-semibold text-gray-800 whitespace-nowrap">
                                                    Rp <?= number_format($fasilitas['harga'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="px-4 py-4 font-semibold text-gray-800 whitespace-nowrap">
                                                    <?= $fasilitas['stok']; ?>
                                                </td>
                                                <td class="px-4 py-4 text-xs text-gray-600 line-clamp-2 mt-2">
                                                    <?= htmlspecialchars($fasilitas['deskripsi'] ?? '-'); ?>
                                                </td>
                                                <td class="px-4 py-4 text-center space-x-1 whitespace-nowrap">
                                                    <a href="index.php?page=home_admin&action=edit_fasilitas&id=<?= $fasilitas['id_fasilitas']; ?>" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded text-xs font-bold border border-amber-200 transition">Edit</a>
                                                    <a href="index.php?page=home_admin&action=hapus_fasilitas&id=<?= $fasilitas['id_fasilitas']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus fasilitas ini?')" class="px-2 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded text-xs font-bold border border-red-200 transition">Hapus</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">
                                                Belum ada data pilihan fasilitas di database.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($action === 'data_pelanggan'): ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between border-b pb-4 mb-4">
                        <div>
                            <h2 class="text-base font-bold text-gray-800">👥 DATA PELANGGAN TERDAFTAR</h2>
                            <p class="text-xs text-gray-400 mt-0.5">*Menampilkan rekam data seluruh pelanggan/customer yang terdaftar di sistem.</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Nama Lengkap</th>
                                    <th class="px-4 py-3">Username</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">No. Telepon</th>
                                    <th class="px-4 py-3">Alamat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php if (!empty($daftar_pelanggan)): ?>
                                    <?php foreach ($daftar_pelanggan as $pelanggan): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4 font-bold text-gray-900">
                                                <?= htmlspecialchars($pelanggan['nama_lengkap']); ?>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-700">
                                                <?= htmlspecialchars($pelanggan['username']); ?>
                                            </td>
                                            <td class="px-4 py-4 text-xs text-slate-500">
                                                <?= htmlspecialchars($pelanggan['email']); ?>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-600">
                                                <?= htmlspecialchars($pelanggan['no_telp'] ?? '-'); ?>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-600 truncate max-w-[200px]">
                                                <?= htmlspecialchars($pelanggan['alamat'] ?? '-'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">
                                            Belum ada data pelanggan yang terdaftar.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($action === 'konfirmasi_booking'): ?>
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm max-w-full overflow-hidden">
                    <h3 class="text-sm font-black text-gray-800 mb-5 flex items-center gap-2">
                        <i class="fas fa-clock text-amber-500"></i> Antrean Permintaan Pending
                    </h3>

                    <div class="w-full overflow-x-auto rounded-xl border border-gray-100 block">
                        <table class="w-full min-w-[1000px] text-sm text-left text-gray-500 table-auto border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap">Kode / Pelanggan</th>
                                    <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap">Armada Mobil</th>
                                    <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap">Rentang Waktu</th>
                                    <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap">Sopir</th>
                                    <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap text-center">Form Tindakan Operasional</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php if(!empty($pendingBookings)): ?>
                                    <?php foreach($pendingBookings as $b): ?>
                                    <tr class="hover:bg-gray-50/60 transition duration-200">
                                        <td class="px-6 py-4">
                                            <span class="font-black text-indigo-600 block text-[10px] uppercase tracking-wider mb-0.5"><?= $b['kode_booking']; ?></span>
                                            <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($b['nama_pelanggan']); ?></span>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-700 text-sm">
                                            <i class="fas fa-car text-gray-400 mr-1.5 text-xs"></i><?= htmlspecialchars($b['merk_mobil']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-xs font-semibold text-gray-600">
                                            <div class="flex flex-col space-y-0.5">
                                                <span><?= date('d M Y', strtotime($b['tgl_mulai_sewa'])); ?> ➔ <?= date('d M Y', strtotime($b['tgl_selesai_sewa'])); ?></span>
                                                <span class="text-[10px] text-gray-400 font-medium">(Durasi: <?= $b['durasi_hari']; ?> Hari)</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if($b['sopir_booking'] !== 'Tanpa Sopir'): ?>
                                            <div class="relative flex-1 min-w-[200px]">
                                                <select name="nama_sopir" required 
                                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3 pr-8 py-2 text-xs focus:outline-none focus:border-indigo-500 focus:bg-white transition appearance-none cursor-pointer font-medium text-gray-700">
                                                    <option value="">-- Pilih Sopir Lapangan --</option>
                                                    <?php if(!empty($daftar_sopir)): ?>
                                                        <?php foreach($daftar_sopir as $sopir): ?>
                                                            <option value="<?= htmlspecialchars($sopir['nama_karyawan'] . ' - ' . $sopir['no_telp']); ?>">
                                                                <?= htmlspecialchars($sopir['nama_karyawan']); ?> (<?= htmlspecialchars($sopir['no_telp']); ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <option value="" disabled>Sopir lapangan belum tersedia</option>
                                                    <?php endif; ?>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-gray-400 text-[10px]">
                                                    <i class="fas fa-chevron-down"></i>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <form action="index.php?page=proses_aksi_admin" method="POST" class="flex items-center justify-center space-x-2 w-full max-w-[420px] mx-auto">
                                                <input type="hidden" name="id_booking" value="<?= $b['id_booking']; ?>">
                                                <input type="hidden" name="id_mobil" value="<?= $b['id_mobil']; ?>">

                                                <div class="flex items-center space-x-1.5 flex-shrink-0">
                                                    <button type="submit" name="aksi" value="setuju" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-3.5 rounded-xl transition shadow-sm flex items-center gap-1">
                                                        <i class="fas fa-check-circle"></i> Setuju
                                                    </button>
                                                    <button type="submit" name="aksi" value="tolak" onclick="return confirm('Apakah Anda yakin ingin menolak permohonan sewa armada ini?')" class="bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 font-bold text-xs py-2 px-3.5 rounded-xl transition flex items-center gap-1">
                                                        <i class="fas fa-times-circle"></i> Tolak
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-20 text-center text-gray-400 font-bold uppercase tracking-widest text-xs bg-gray-50/30">
                                            <i class="fas fa-check-double text-5xl text-emerald-400/40 mb-4 block animate-bounce"></i> 
                                            Semua pengajuan rental bersih divalidasi!
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </main>

        <footer class="text-center text-xs text-gray-400 border-t border-gray-100 py-4 bg-white flex-shrink-0">
            &copy; 2026 Sistem Rental Mobil Internal • Modul Kendali Admin Selesai.
        </footer>
    </div>

</body>
</html>