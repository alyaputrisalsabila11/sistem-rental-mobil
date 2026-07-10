<?php
if (session_status() === PHP_SESSION_NONE) {
    if (!isset($_SESSION)) { session_start(); }
}

require_once __DIR__ . '/../../../init.php';
$db = Database::getConnection();

$brand_name = "SIREMO";
$staff_name = $_SESSION['user_name'] ?? 'Staff Lapangan';
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

// PERBAIKAN: Inisialisasi controller di luar IF agar bisa dipakai di semua action (cek_kondisi, dll)
$ctrlKaryawan = new KaryawanController();

// Logic Dashboard Ringkasan
if ($action === 'home') {
    $ready_count = 0;
    $maintenance_count = 0;
    $disewa_count = 0;
    
    // Ambil jumlah jadwal tugas dari Controller
    $jadwal_count = $ctrlKaryawan->getCountTugas();

    try {
        $stmt = $db->query("SELECT status_mobil, COUNT(*) as jumlah FROM mobil GROUP BY status_mobil");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['status_mobil'] == 'Tersedia') $ready_count = $row['jumlah'];
            if ($row['status_mobil'] == 'Maintenance') $maintenance_count = $row['jumlah'];
            if ($row['status_mobil'] == 'Disewa') $disewa_count = $row['jumlah'];
        }
    } catch (Exception $e) { /* Error Handling */ }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Lapangan Dashboard - <?= $brand_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans flex h-screen overflow-hidden">

<!-- Sidebar -->
<?php include __DIR__ . '/../sidebar/stafflapangan.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8 flex-shrink-0">
        <h1 class="text-lg font-bold text-gray-800 uppercase"><?= str_replace('_', ' ', $action); ?></h1>
        <div class="text-sm font-medium text-gray-600">Selamat bekerja, <span class="text-indigo-600"><?= $staff_name; ?></span></div>
    </header>

    <main class="flex-1 overflow-y-auto p-8">
        
        <?php if ($action === 'home'): ?>
            <!-- Dashboard Stats: Dibuat lebih compact dan tidak terlalu tinggi -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                
                <!-- CARD: Info Jadwal Tugas (Dibuat lebih pendek) -->
                <div class="bg-indigo-600 p-5 rounded-2xl shadow-md border border-indigo-700 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-[10px] font-bold text-indigo-100 uppercase italic">Jadwal Tugas</p>
                            <i class="fa-solid fa-calendar-check text-indigo-300 text-xs"></i>
                        </div>
                        <p class="text-2xl font-black text-white"><?= $jadwal_count; ?> <span class="text-sm font-normal">Unit</span></p>
                    </div>
                    <a href="index.php?page=stafflapangan&action=cek_kondisi" class="text-[10px] text-white mt-3 font-bold hover:underline inline-flex items-center gap-1">
                        LIHAT DETAIL <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    </a>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Unit Ready</p>
                    <p class="text-xl font-black text-green-600"><?= $ready_count; ?></p>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Maintenance</p>
                    <p class="text-xl font-black text-orange-500"><?= $maintenance_count; ?></p>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Sedang Disewa</p>
                    <p class="text-xl font-black text-indigo-600"><?= $disewa_count; ?></p>
                </div>
            </div>

            <!-- Instruksi Kerja (Tetap) -->
            <div class="mt-6 p-5 bg-white border border-gray-200 rounded-2xl">
                <h3 class="font-bold text-gray-900 text-sm mb-2">Instruksi Kerja Sopir & Lapangan</h3>
                <ul class="text-xs text-gray-500 space-y-1.5 list-disc ml-5 leading-relaxed">
                    <li>Cek jadwal tugas di card biru di atas.</li>
                    <li>Lakukan pengecekan fisik (KM & BBM) sebelum kunci diserahkan ke pelanggan.</li>
                    <li>Pastikan mencatat kerusakan baru jika ditemukan saat mobil kembali.</li>
                </ul>
            </div>

        <?php elseif ($action === 'cek_kondisi'): ?>
             <!-- Bagian list tugas pengecekan dan antar unit -->
             <div class="bg-white p-6 rounded-2xl shadow-sm border">
                <h2 class="font-bold text-xl mb-4 text-gray-800">Daftar Pengecekan Mobil</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Mobil & Pelanggan</th>
                                <th class="px-4 py-3">Lokasi</th>
                                <th class="px-4 py-3 text-center">Tipe Tugas</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php 
                            $my_id = $_SESSION['user_id'] ?? 0;
                            // PERBAIKAN: Variabel $ctrlKaryawan sudah ada karena inisialisasi dipindah ke atas
                            $tugas = $ctrlKaryawan->getMyTasks($my_id);
                            
                            if(empty($tugas)): ?>
                                <tr><td colspan="4" class="py-10 text-center text-gray-400 italic">Tidak ada jadwal pengecekan/antar unit untuk Anda saat ini.</td></tr>
                            <?php else: foreach($tugas as $t): ?>
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-800"><?= $t['merk_mobil'] ?> (<?= $t['plat_nomor'] ?>)</div>
                                        <div class="text-xs text-gray-500">Customer: <?= $t['nama_pelanggan'] ?></div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600"><?= $t['nama_lokasi'] ?? 'Pusat' ?></td>
                                    <td class="px-4 py-4 text-center">
                                        <?php if($t['status_penyewaan'] == 'Confirmed'): ?>
                                            <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-1 rounded font-bold uppercase">Antar Unit (Out)</span>
                                        <?php else: ?>
                                            <span class="text-[10px] bg-purple-100 text-purple-700 px-2 py-1 rounded font-bold uppercase">Jemput Unit (In)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <a href="index.php?page=stafflapangan&action=form_cek&id=<?= $t['id_penyewaan'] ?>" 
                                           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-700 transition">
                                            Mulai Cek
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($action === 'data_kerusakan'): ?>
            <!-- Menampilkan data dari tabel kerusakan -->
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-700">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Jenis Kerusakan</th>
                            <th class="px-6 py-4 text-right">Estimasi Biaya Perbaikan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php
                        // PERBAIKAN: Gunakan try-catch agar tidak fatal error jika ada masalah koneksi tabel
                        try {
                            $stmtKrs = $db->query("SELECT * FROM kerusakan ORDER BY id_kerusakan ASC");
                            while($krs = $stmtKrs->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td class="px-6 py-4"><?= $krs['id_kerusakan']; ?></td>
                                <td class="px-6 py-4 font-bold text-gray-800"><?= $krs['nama_kerusakan']; ?></td>
                                <td class="px-6 py-4 text-right font-semibold text-red-600">Rp <?= number_format($krs['biaya_perbaikan']); ?></td>
                            </tr>
                            <?php endwhile;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='3' class='px-6 py-10 text-center text-gray-400 italic'>Gagal mengambil data: " . $e->getMessage() . "</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>