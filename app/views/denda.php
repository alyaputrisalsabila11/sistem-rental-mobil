<?php
require_once __DIR__ . '/../../config/database.php';

$db = Database::getConnection();

$stmt = $db->prepare("SELECT 
                        b.kode_booking, 
                        m.merk_mobil, 
                        (g.biaya_kerusakan + g.denda_telat) AS total_denda
                      FROM pengembalian g
                      JOIN penyerahan p ON g.id_penyerahan = p.id_penyerahan
                      JOIN booking b ON p.id_booking = b.id_booking
                      JOIN mobil m ON b.id_mobil = m.id_mobil
                      WHERE b.id_pelanggan = ? AND (g.biaya_kerusakan > 0 OR g.denda_telat > 0)");
$stmt->execute([$_SESSION['user_id']]);
$denda_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denda Saya - Rental Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="flex min-h-screen">
    <!-- Sidebar (Mengikuti path include file gallery.php) -->
    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-64">
        <!-- Top Navbar (Sama persis dengan gallery.php) -->
        <nav class="bg-white shadow-sm sticky top-0 z-40">
            <div class="px-8 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Denda Saya</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600 text-sm">Selamat datang, <strong><?= htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                    <a href="index.php?page=logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="p-8">
            <!-- Banner Section (Menyamakan style gradasi warna ungu dari gallery) -->
            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white rounded-2xl p-10 mb-10 shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-4xl font-black mb-3 tracking-tight">Informasi Denda Anda</h2>
                    <p class="text-indigo-100 text-lg max-w-2xl">Pantau berkala data denda keterlambatan pengembalian armada atau klaim kerusakan fisik di sini.</p>
                </div>
                <!-- Dekorasi Background -->
                <i class="fas fa-exclamation-circle absolute -right-10 -bottom-10 text-[180px] text-white opacity-10 rotate-12"></i>
            </div>

            <!-- Bagian Tabel / Konten Data -->
            <div class="bg-white rounded-3xl border border-gray-100 p-8 shadow-sm">
                <div class="overflow-x-auto rounded-2xl border border-gray-100">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 font-black tracking-wider">Kode Booking</th>
                                <th class="px-6 py-4 font-black tracking-wider">Mobil</th>
                                <th class="px-6 py-4 font-black tracking-wider">Total Denda</th>
                                <th class="px-6 py-4 font-black tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php if (!empty($denda_list)): ?>
                                <?php foreach ($denda_list as $d): ?>
                                <tr class="hover:bg-gray-50/80 transition duration-200">
                                    <td class="px-6 py-4 font-mono font-bold text-[#5138bc]"><?= htmlspecialchars($d['kode_booking']); ?></td>
                                    <td class="px-6 py-4 font-bold text-gray-800"><?= htmlspecialchars($d['merk_mobil']); ?></td>
                                    <td class="px-6 py-4 text-red-500 font-black">Rp <?= number_format($d['total_denda'], 0, ',', '.'); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase bg-yellow-100 text-yellow-700 border border-yellow-200">
                                            Belum Lunas
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="py-20 text-center bg-white rounded-3xl">
                                        <i class="fas fa-check-circle text-7xl text-[#5138bc]/20 mb-6 block"></i>
                                        <p class="text-gray-400 font-bold uppercase tracking-widest">Hebat! Anda tidak memiliki catatan denda aktif</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>