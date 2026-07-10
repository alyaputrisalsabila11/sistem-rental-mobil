<?php
if (session_status() === PHP_SESSION_NONE) {
    if (!isset($_SESSION)) { session_start(); }
}

require_once __DIR__ . '/../../../init.php';
$db = Database::getConnection();

$brand_name = "SIREMO";
$admin_name = $_SESSION['user_name'] ?? 'Admin Staff';
$admin_email = $_SESSION['user_email'] ?? '@staffadmin.swm';

// Menentukan halaman aktif berdasarkan parameter 'action' di URL (Default: home)
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

if ($action === 'home') {
    // Inisialisasi awal agar tidak fatal error jika tabel belum ada
    $total_mobil = 0; $total_booking = 0; $total_pelanggan = 0; $total_fasilitas = 0;

    try {
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
    } catch (Exception $e) { /* Abaikan error jika tabel belum ada */ }

} elseif ($action === 'data_mobil') {
    $stmtMobil = $db->query("SELECT * FROM mobil ORDER BY id_mobil DESC");
    $mobil_list = $stmtMobil->fetchAll(PDO::FETCH_ASSOC);

} elseif ($action === 'data_pelanggan') {
    $pelangganModel = new PelangganModel();
    $daftar_pelanggan = $pelangganModel->getAllPelanggan();

} elseif ($action === 'konfirmasi_booking') {
    $pendingBookings = [];
    try {
        $sqlPending = "SELECT b.*, m.merk_mobil, p.nama_lengkap as nama_pelanggan
                       FROM booking b
                       JOIN mobil m ON b.id_mobil = m.id_mobil
                       JOIN pelanggan p ON b.id_pelanggan = p.id
                       WHERE b.status_booking = 'Pending'
                       ORDER BY b.id_booking ASC";
        $stmtPending = $db->query($sqlPending);
        $pendingBookings = $stmtPending->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { /* Tabel booking mungkin belum ada */ }
} elseif ($action === 'data_fasilitas') {
    $stmtFasilitas = $db->query("SELECT * FROM fasilitas ORDER BY id_fasilitas DESC");
    $fasilitas_list = $stmtFasilitas->fetchAll(PDO::FETCH_ASSOC);
} elseif ($action === 'data_voucher') {
    $stmtVoucher = $db->query("SELECT * FROM voucher ORDER BY id_voucher DESC");
    $voucher_list = $stmtVoucher->fetchAll(PDO::FETCH_ASSOC);
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

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8 flex-shrink-0">
        <h1 class="text-lg font-bold text-gray-800 uppercase"><?= str_replace('_', ' ', $action); ?></h1>
        <div class="text-sm font-medium text-gray-600">Selamat datang, <span class="text-indigo-600"><?= $admin_name; ?></span></div>
    </header>

    <main class="flex-1 overflow-y-auto p-8">
        
        <?php if ($action === 'home'): ?>
            <!-- Dashboard Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <!-- Card 1: Total Armada -->
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 flex items-center justify-between group hover:border-indigo-200 transition-all cursor-default">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Total Armada</p>
                        <p class="text-4xl font-black text-gray-800 group-hover:text-indigo-600 transition-colors"><?= $total_mobil; ?></p>
                    </div>
                    <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                        <i class="fa-solid fa-car-side text-2xl"></i>
                    </div>
                </div>

                <!-- Card 2: Total Booking -->
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 flex items-center justify-between group hover:border-amber-200 transition-all cursor-default">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Total Booking</p>
                        <p class="text-4xl font-black text-gray-800 group-hover:text-amber-600 transition-colors"><?= $total_booking; ?></p>
                    </div>
                    <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600">
                        <i class="fa-solid fa-receipt text-2xl"></i>
                    </div>
                </div>

                <!-- Card 3: Pelanggan -->
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 flex items-center justify-between group hover:border-green-200 transition-all cursor-default">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Pelanggan</p>
                        <p class="text-4xl font-black text-gray-800 group-hover:text-green-600 transition-colors"><?= $total_pelanggan; ?></p>
                    </div>
                    <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600">
                        <i class="fa-solid fa-user-group text-2xl"></i>
                    </div>
                </div>

                <!-- Card 4: Fasilitas -->
                <div class="bg-white p-8 rounded-[32px] shadow-sm border border-gray-100 flex items-center justify-between group hover:border-rose-200 transition-all cursor-default">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Fasilitas</p>
                        <p class="text-4xl font-black text-gray-800 group-hover:text-rose-600 transition-colors"><?= $total_fasilitas; ?></p>
                    </div>
                    <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600">
                        <i class="fa-solid fa-suitcase text-2xl"></i>
                    </div>
                </div>

            </div>

        <!-- FORM MASTER: TAMBAH UNIT -->
        <?php elseif ($action === 'tambah_mobil'): ?>
            <div class="max-w-2xl mx-auto bg-white p-10 rounded-3xl shadow-sm border">
                <h2 class="text-2xl font-black mb-8">Tambah Unit Mobil Baru</h2>
                <form action="index.php?page=Admin&action=proses_tambah_mobil" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <div class="grid grid-cols-2 gap-5">
                        <input type="text" name="nama_kategori" placeholder="Kategori (ex: SUV)" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <input type="text" name="merk_mobil" placeholder="Merk Mobil" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <input type="text" name="plat_nomor" placeholder="Plat Nomor" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <input type="number" name="tahun" placeholder="Tahun" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <input type="number" name="harga_dinamis" placeholder="Harga Sewa / Hari" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <input type="text" name="warna" placeholder="Warna" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <input type="number" name="cc" placeholder="CC" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <select name="status_mobil" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="Tersedia">Tersedia</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="border border-gray-200 p-4 rounded-2xl bg-gray-50 flex items-center">
                        <input type="file" name="gambar" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-white file:text-indigo-700 hover:file:bg-indigo-50">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">Simpan Unit</button>
                </form>
            </div>

        <!-- FORM MASTER: TAMBAH FASILITAS -->
        <?php elseif ($action === 'tambah_fasilitas'): ?>
            <div class="max-w-2xl mx-auto bg-white p-10 rounded-3xl shadow-sm border">
                <h2 class="text-2xl font-black mb-8">Tambah Fasilitas Baru</h2>
                <form action="index.php?page=Admin&action=proses_tambah_fasilitas" method="POST" class="space-y-5">
                    <input type="text" name="nama_fasilitas" placeholder="Nama Fasilitas" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    <textarea name="deskripsi" placeholder="Deskripsi Fasilitas" class="border border-gray-200 p-4 rounded-2xl w-full text-sm h-32 focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                    <div class="grid grid-cols-2 gap-5">
                        <input type="number" name="harga" placeholder="Harga" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <input type="number" name="stok" placeholder="Stok" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    </div>
                    <select name="status" class="border border-gray-200 p-4 rounded-2xl w-full text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Habis">Habis</option>
                    </select>
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">Simpan Fasilitas</button>
                </form>
            </div>

        <!-- DATA TABEL: DATA MOBIL (MODIFIED LIKE IMAGE 1) -->
        <?php elseif ($action === 'data_mobil'): ?>
            <div class="bg-white p-10 rounded-3xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <div class="min-w-[800px]">
                        <div class="flex items-center mb-6 px-4 border-b pb-6">
                            <span class="w-[25%] text-xl font-black text-gray-800">Mobil</span>
                            <span class="w-[25%] text-xl font-black text-gray-800">Plat</span>
                            <span class="w-[25%] text-xl font-black text-gray-800 text-center">Harga</span>
                            <span class="w-[25%] text-xl font-black text-gray-800 text-right">Status</span>
                        </div>
                        
                        <?php if(!empty($mobil_list)): ?>
                            <div class="space-y-4">
                                <?php foreach ($mobil_list as $m): ?>
                                <div class="flex items-center px-4 py-3 hover:bg-gray-50 rounded-xl transition">
                                    <div class="w-[25%] flex flex-col">
                                        <span class="font-bold text-gray-800"><?= $m['merk_mobil']; ?></span>
                                        <span class="text-[11px] text-gray-400 uppercase tracking-widest"><?= $m['nama_kategori']; ?> (<?= $m['tahun']; ?>)</span>
                                    </div>
                                    <span class="w-[25%] text-gray-600 font-medium"><?= $m['plat_nomor']; ?></span>
                                    <span class="w-[25%] text-center font-bold text-indigo-600">Rp <?= number_format($m['harga_dinamis']); ?></span>
                                    <div class="w-[25%] text-right">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?= $m['status_mobil'] === 'Tersedia' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= $m['status_mobil']; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="py-20 text-center">
                                <p class="text-gray-300 text-lg">Belum ada data mobil.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- DATA TABEL: DATA VOUCHER (MODIFIED LIKE IMAGE 1) -->
        <?php elseif ($action === 'data_voucher'): ?>
            <div class="bg-white p-10 rounded-3xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <div class="min-w-[800px]">
                        <div class="flex items-center mb-6 px-4 border-b pb-6">
                            <span class="w-[20%] text-xl font-black text-gray-800">Kode</span>
                            <span class="w-[30%] text-xl font-black text-gray-800">Voucher</span>
                            <span class="w-[30%] text-xl font-black text-gray-800 text-center">Diskon</span>
                            <span class="w-[20%] text-xl font-black text-gray-800 text-right">Status</span>
                        </div>

                        <?php if(!empty($voucher_list)): ?>
                            <div class="space-y-4">
                                <?php foreach ($voucher_list as $v): ?>
                                <div class="flex items-center px-4 py-3 hover:bg-gray-50 rounded-xl transition">
                                    <span class="w-[20%] font-mono font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-lg inline-block w-fit"><?= $v['kode_voucher']; ?></span>
                                    <span class="w-[30%] font-bold text-gray-800"><?= $v['nama_voucher']; ?></span>
                                    <div class="w-[30%] text-center flex flex-col">
                                        <span class="font-bold text-green-600 text-lg"><?= $v['diskon_persen']; ?>%</span>
                                        <span class="text-[10px] text-gray-400 uppercase tracking-widest">Sisa Kuota: <?= $v['kuota']; ?></span>
                                    </div>
                                    <div class="w-[20%] text-right">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?= $v['status'] === 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= $v['status']; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="py-20 text-center">
                                <p class="text-gray-300 text-lg uppercase font-bold tracking-widest">Belum ada data voucher.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <!-- DATA TABEL: DATA FASILITAS (MODIFIED LIKE IMAGE 1) -->
        <?php elseif ($action === 'data_fasilitas'): ?>
            <div class="bg-white p-10 rounded-3xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <div class="min-w-[700px]">
                        <div class="flex items-center mb-6 px-4 border-b pb-6">
                            <span class="w-[40%] text-xl font-black text-gray-800">Nama Fasilitas</span>
                            <span class="w-[40%] text-xl font-black text-gray-800 text-center">Harga / Stok</span>
                            <span class="w-[20%] text-xl font-black text-gray-800 text-right">Status</span>
                        </div>

                        <?php if(!empty($fasilitas_list)): ?>
                            <div class="space-y-4">
                                <?php foreach ($fasilitas_list as $f): ?>
                                <div class="flex items-center px-4 py-3 hover:bg-gray-50 rounded-xl transition">
                                    <span class="w-[40%] font-bold text-gray-800"><?= $f['nama_fasilitas']; ?></span>
                                    <div class="w-[40%] text-center flex flex-col">
                                        <span class="font-bold text-indigo-600 text-sm">Rp <?= number_format($f['harga']); ?></span>
                                        <span class="text-[10px] text-gray-400">Stok: <?= $f['stok']; ?> unit</span>
                                    </div>
                                    <div class="w-[20%] text-right">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?= $f['status'] === 'Tersedia' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= $f['status']; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="py-20 text-center">
                                <p class="text-gray-300 text-lg">Belum ada data fasilitas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

     <!-- DATA TABEL: DATA PELANGGAN -->
        <?php elseif ($action === 'data_pelanggan'): ?>
            <div class="bg-white p-10 rounded-3xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <div class="min-w-[900px]">
                        <!-- Header Tabel: Menggunakan Flex agar rapi dan pas dengan isinya -->
                        <div class="flex items-center mb-6 px-4 border-b pb-6">
                            <div class="w-[25%] text-xl font-black text-gray-800">Nama Lengkap</div>
                            <div class="w-[15%] text-xl font-black text-gray-800 text-center">Loyalitas</div>
                            <div class="w-[45%] text-xl font-black text-gray-800 text-center">Email / No Telp</div>
                            <div class="w-[15%] text-xl font-black text-gray-800 text-right">Poin</div>
                        </div>
                        
                        <?php if(!empty($daftar_pelanggan)): ?>
                            <div class="space-y-4">
                            <?php foreach ($daftar_pelanggan as $p): ?>
                                <div class="flex items-center px-4 py-4 hover:bg-gray-50 rounded-2xl transition border border-transparent hover:border-indigo-100">
                                    <!-- Kolom Nama -->
                                    <div class="w-[25%] flex flex-col pr-4">
                                        <span class="font-bold text-gray-800 text-lg leading-tight"><?= $p['nama_lengkap']; ?></span>
                                        <span class="text-[10px] text-gray-400 mt-1 font-medium tracking-wider uppercase">#ID-<?= $p['id_pelanggan']; ?></span>
                                    </div>
                                    
                                    <!-- Kolom Loyalitas -->
                                    <div class="w-[15%] text-center">
                                        <?php if(!empty($p['nama_level'])): ?>
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-700 border border-amber-200">
                                                <?= $p['nama_level']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-300 font-bold text-xl">-</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Kolom Email & No Telp -->
                                    <div class="w-[45%] text-center flex flex-col">
                                        <span class="text-sm text-gray-600 font-medium"><?= $p['email']; ?></span>
                                        <span class="text-[11px] text-indigo-500 font-bold mt-1"><?= $p['no_telp']; ?></span>
                                    </div>

                                    <!-- Kolom Poin -->
                                    <div class="w-[15%] text-right">
                                        <span class="font-black text-amber-500 text-2xl"><?= $p['poin'] ?? 0; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="py-24 text-center">
                                <i class="fa-solid fa-users-slash text-5xl text-gray-100 mb-4 block"></i>
                                <p class="text-gray-300 text-lg font-black uppercase tracking-[0.2em] italic">Belum ada data pelanggan.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>