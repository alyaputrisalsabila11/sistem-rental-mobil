<?php
// =========================================================================
// FILE: views/user/dashboard/stafflapangan.php (Dashboard Lapangan Komplet)
// =========================================================================

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
$db = Database::getConnection();

$staff_name = $_SESSION['user_name'] ?? 'Staff Lapangan';
$id_lokasi_cabang = $_SESSION['id_lokasi'] ?? null; 
$action = $_GET['action'] ?? 'home';

$ready_count = 0; $maintenance_count = 0; $disewa_count = 0;
try {
    $ready_count = $db->query("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Tersedia'")->fetchColumn();
    $maintenance_count = $db->query("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Maintenance'")->fetchColumn();
    $disewa_count = $db->query("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Disewa'")->fetchColumn();
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Staff Lapangan - SIREMO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden text-sm">

<?php include __DIR__ . '/../sidebar/stafflapangan.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8 flex-shrink-0">
        <h1 class="text-base font-bold text-gray-800 uppercase flex items-center gap-2">
            <i class="fa-solid fa-screwdriver-wrench text-indigo-600"></i>
            <span>Cabang Petugas Lapangan #<?= $id_lokasi_cabang; ?></span>
        </h1>
        <div class="text-xs font-bold text-indigo-600">Petugas: <span class="text-indigo-600 font-bold"><?= $staff_name; ?></span></div>
    </header>

    <main class="flex-1 overflow-y-auto p-6">

        <!-- ==================== HOME LAPANGAN ==================== -->
        <?php if ($action === 'home'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                <!-- Card Prioritas Tinggi Tugas Sopir -->
                <div class="bg-indigo-600 text-white p-5 rounded-2xl shadow-lg flex flex-col justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-indigo-200 uppercase italic">Tugas Sopir Prioritas</p>
                        <h2 class="text-xl font-black mt-2">Siaga Driver Cabang</h2>
                        <p class="text-xs text-indigo-100 mt-1">Status: Antar kunci & serah unit ke alamat pelanggan.</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border flex flex-col justify-center shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Armada Ready</p>
                    <p class="text-2xl font-black text-green-600 mt-1"><?= $ready_count; ?></p>
                </div>

                <div class="bg-white p-5 rounded-2xl border flex flex-col justify-center shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Maintenance</p>
                    <p class="text-2xl font-black text-orange-500 mt-1"><?= $maintenance_count; ?></p>
                </div>

                <div class="bg-white p-5 rounded-2xl border flex flex-col justify-center shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Sedang Disewa</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1"><?= $disewa_count; ?></p>
                </div>
            </div>

            <!-- Panduan Serah Terima Kunci -->
            <div class="p-5 bg-white border rounded-2xl">
                <h3 class="font-bold text-gray-900 text-xs mb-2 uppercase">Panduan Handover & Return</h3>
                <ul class="text-xs text-gray-500 space-y-1.5 list-inside list-decimal">
                    <li>Cocokkan nomor bodi & pelat nomor kendaraan sebelum kunci dilepas.</li>
                    <li>Ambil foto bersama pelanggan dan unit mobil di area depan kantor cabang.</li>
                    <li>Gunakan diagram interaktif di halaman cek kondisi untuk menandai goresan bodi.</li>
                </ul>
            </div>

        <!-- ==================== CEK KONDISI (INTERAKTIF DIAGRAM BODI) ==================== -->
        <?php elseif ($action === 'cek_kondisi'): ?>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <h2 class="font-bold text-base text-gray-800 mb-4">Pemeriksaan & Serah Terima Kunci</h2>
                <!-- Daftar Unit untuk handover dan return -->
                <div class="py-12 text-center text-gray-400 italic">Tidak ada jadwal serah terima unit berjalan hari ini.</div>
            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>