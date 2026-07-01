<?php
// Mengamankan data session agar jika kosong tidak memicu error
$user_name = $_SESSION['user_name'] ?? 'Pelanggan';
$brand_name = "SIREMO"; // Nama brand aplikasi
?>

<style>
    /* Menghancurkan paksaan font besar dari Bootstrap global */
    html, body {
        font-size: 14px !important;
        line-height: 1.5 !important;
    }
    h1, h2, h3, h4, h5, h6 {
        font-size: inherit;
        font-weight: inherit;
        line-height: inherit;
    }
    .card, .container-fluid, .row {
        all: unset; /* Buang semua sisa-sisa style Bootstrap jika masih tersisa */
    }
</style>

<div class="flex min-h-screen bg-gray-50 antialiased text-sm font-sans">
    
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-1 ml-64 min-w-0">
        
        <nav class="bg-white shadow-sm sticky top-0 z-40">
            <div class="px-8 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800 flex items-center" style="font-size: 1.25rem !important;">
                    <i class="fas fa-home text-[#5138bc] mr-3"></i>Dashboard Pelanggan
                </h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600 text-xs">Selamat datang, <strong><?php echo htmlspecialchars($user_name); ?></strong></span>
                    <a href="index.php?page=logout" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs transition duration-300 flex items-center">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="p-8">
            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white rounded-2xl p-8 mb-8 shadow-md relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-3xl font-black mb-2 tracking-tight" style="font-size: 1.875rem !important;">Selamat Datang, <?php echo htmlspecialchars($user_name); ?>! 👋</h2>
                    <p class="text-indigo-100 text-sm max-w-2xl">Anda berhasil masuk ke panel pelanggan aplikasi rental mobil kami. Nikmati kemudahan memesan armada terbaik langsung dari genggaman Anda.</p>
                </div>
                <i class="fas fa-chart-line absolute -right-6 -bottom-6 text-[120px] text-white opacity-10 rotate-12"></i>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-[#5138bc] hover:shadow-md transition">
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Poin Loyalitas</p>
                    <p class="text-2xl font-black text-[#5138bc] mt-1" style="font-size: 1.5rem !important;">0 Poin</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500 hover:shadow-md transition">
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Tier Membership</p>
<p class="text-2xl font-black text-purple-600 mt-1" style="font-size: 1.5rem !important;">Bronze</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500 hover:shadow-md transition">
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Status Akun</p>
                    <p class="text-2xl font-black text-orange-500 mt-1" style="font-size: 1.5rem !important;">Aktif</p>
                </div>
            </div>

            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-gray-800 flex items-center" style="font-size: 1rem !important;">
                        <i class="fas fa-star text-yellow-500 mr-2"></i> Rekomendasi Armada Terpopuler
                    </h3>
                    <a href="index.php?page=gallery" class="text-[#5138bc] hover:underline text-xs font-bold">
                        Lihat Semua Mobil <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php if (!empty($cars)): ?>
                        <?php 
                        $limited_cars = array_slice($cars, 0, 3);
                        foreach ($limited_cars as $car): 
                        ?>
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition duration-300">
                                <div class="p-5">
                                    <h4 class="font-bold text-gray-800 text-base mb-1" style="font-size: 1rem !important;"><?php echo htmlspecialchars($car['merk_mobil']); ?></h4>
                                    <p class="text-xs text-gray-400 mb-3">Tahun <?php echo htmlspecialchars($car['tahun']); ?> | <?php echo htmlspecialchars(ucfirst($car['tipe'])); ?></p>
                                    
                                    <div class="text-[#ff5722] font-black text-base border-t border-b border-gray-50 py-3 mb-4" style="font-size: 1rem !important;">
                                        Rp <?php echo number_format($car['harga_dinamis'] ?? 500000, 0, ',', '.'); ?> <span class="text-xs text-gray-400 font-normal">/hari</span>
                                    </div>
                                    
                                    <a href="index.php?page=booking&id=<?php echo $car['id_mobil']; ?>" 
                                       class="block w-full text-center bg-[#5138bc] hover:bg-[#ff5722] text-white text-xs font-black py-3 rounded-xl transition duration-300 uppercase tracking-wider">
                                        Pesan Sekarang
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-dashed border-gray-200">
                            <i class="fas fa-car-crash text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-400 font-medium text-xs">Belum ada rekomendasi armada tersedia saat ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>