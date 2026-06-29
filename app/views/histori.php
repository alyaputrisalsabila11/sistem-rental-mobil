<?php
// Path disesuaikan karena letak filenya sekarang di app/views/histori.php (sejajar gallery.php)
require_once __DIR__ . '/../models/BookingModel.php';

$bookingModel = new BookingModel();
$bookings = $bookingModel->getBookingByUser($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Sewa - Rental Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="flex min-h-screen">
    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <main class="flex-1 ml-64 max-w-[calc(100%-16rem)] overflow-hidden flex flex-col min-h-screen">
        <nav class="bg-white shadow-sm sticky top-0 z-40 w-full">
            <div class="px-8 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Riwayat Penyewaan</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600 text-sm">Selamat datang, <strong><?= htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                    <a href="index.php?page=logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="p-8 flex-1 w-full max-w-full box-border">
            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white rounded-2xl p-10 mb-10 shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-4xl font-black mb-3 tracking-tight">Riwayat Penyewaan Anda</h2>
                    <p class="text-indigo-100 text-lg max-w-2xl">Daftar rekam jejak transaksi rental mobil yang pernah Anda ajukan di platform kami.</p>
                </div>
                <i class="fas fa-history absolute -right-10 -bottom-10 text-[180px] text-white opacity-10 rotate-12"></i>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 p-8 shadow-sm max-w-full overflow-hidden">
                
                <div class="w-full overflow-x-auto rounded-2xl border border-gray-100 block clear-both">
                    
                    <table class="w-full min-w-[950px] text-sm text-left text-gray-500 table-auto border-collapse">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap">Mobil</th>
                                <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap">Tanggal Sewa</th>
                                <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap">Durasi</th>
                                <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap">Total Biaya</th>
                                <th class="px-6 py-4 font-black tracking-wider whitespace-nowrap">Status & Driver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <?php if(!empty($bookings)): ?>
                                <?php foreach($bookings as $b): ?>
                                <tr class="hover:bg-gray-50/80 transition duration-200">
                                    <td class="px-6 py-4 font-bold text-gray-800">
                                        <div class="flex items-center space-x-3">
                                            <div class="bg-[#5138bc]/10 text-[#5138bc] p-2 rounded-lg">
                                                <i class="fas fa-car text-sm"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="whitespace-nowrap"><?= htmlspecialchars($b['merk_mobil']); ?></span>
                                                <?php if(isset($b['plat_nomor'])): ?>
                                                    <span class="text-[10px] text-gray-400 font-normal tracking-wider whitespace-nowrap"><?= htmlspecialchars($b['plat_nomor']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-xs font-semibold text-gray-600">
                                        <div class="flex items-center space-x-1 whitespace-nowrap">
                                            <span class="bg-gray-100 px-2.5 py-1 rounded-md text-gray-700">
                                                <?= date('d M Y', strtotime($b['tgl_mulai_sewa'])); ?>
                                            </span>
                                            <span class="text-gray-400">➔</span>
                                            <span class="bg-gray-100 px-2.5 py-1 rounded-md text-gray-700">
                                                <?= date('d M Y', strtotime($b['tgl_selesai_sewa'])); ?>
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 font-bold text-gray-700 whitespace-nowrap"><?= htmlspecialchars($b['durasi_hari']); ?> Hari</td>
                                    
                                    <td class="px-6 py-4 text-[#ff5722] font-black text-base whitespace-nowrap">
                                        Rp <?= number_format($b['total_harga'], 0, ',', '.'); ?>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col space-y-2 items-start min-w-[200px]">
                                            <?php if ($b['status_booking'] === 'Pending'): ?>
                                                <span class="px-4 py-1.5 text-[10px] font-black rounded-full uppercase bg-amber-50 text-amber-700 border border-amber-200 tracking-wider whitespace-nowrap">
                                                    <i class="fas fa-spinner animate-spin mr-1"></i> <?= htmlspecialchars($b['status_booking']); ?>
                                                </span>
                                            <?php elseif ($b['status_booking'] === 'Confirmed'): ?>
                                                <span class="px-4 py-1.5 text-[10px] font-black rounded-full uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 tracking-wider whitespace-nowrap">
                                                    <i class="fas fa-check-circle mr-1"></i> <?= htmlspecialchars($b['status_booking']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="px-4 py-1.5 text-[10px] font-black rounded-full uppercase bg-rose-50 text-rose-700 border border-rose-200 tracking-wider whitespace-nowrap">
                                                    <i class="fas fa-times-circle mr-1"></i> <?= htmlspecialchars($b['status_booking']); ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($b['sopir_booking'] === 'Tanpa Sopir'): ?>
                                                <span class="text-[10px] text-blue-600 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded font-bold whitespace-nowrap"><i class="fas fa-key mr-1"></i> Lepas Kunci</span>
                                            <?php else: ?>
                                                <?php if ($b['status_booking'] === 'Pending'): ?>
                                                    <div class="text-[11px] text-amber-800 bg-amber-50/50 border border-amber-100 rounded-lg p-1.5 w-full">
                                                        <span class="font-bold block text-[9px] text-amber-600 uppercase">Layanan:</span>
                                                        <i class="fas fa-user-clock mr-1"></i> Pakai Sopir (Menunggu Driver)
                                                    </div>
                                                <?php elseif ($b['status_booking'] === 'Confirmed'): ?>
                                                    <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-2 text-xs text-indigo-900 font-medium w-full flex flex-col space-y-1 shadow-sm">
                                                        <span class="text-[9px] font-bold text-indigo-500 uppercase tracking-wider block">👤 Sopir Ditugaskan:</span>
                                                        <span class="font-bold text-indigo-900"><?= htmlspecialchars($b['sopir_booking']); ?></span>
                                                        
                                                        <?php 
                                                        preg_match('/08[0-9]+/', $b['sopir_booking'], $matches);
                                                        $no_wa = $matches[0] ?? '';
                                                        ?>
                                                        <?php if (!empty($no_wa)): ?>
                                                            <a href="https://wa.me/<?= $no_wa; ?>" target="_blank" class="mt-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold py-1 px-2 rounded flex items-center justify-center gap-1 transition whitespace-nowrap shadow-sm">
                                                                <i class="fab fa-whatsapp"></i> Hubungi via WA
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-24 text-center bg-white rounded-3xl">
                                        <i class="fas fa-folder-open text-7xl text-[#5138bc]/20 mb-6 block"></i>
                                        <p class="text-gray-400 font-bold uppercase tracking-widest text-sm">Belum ada riwayat transaksi rental</p>
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