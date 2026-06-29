<?php
// Pastikan session sudah aktif. Jika di index.php sudah ada session_start(), baris di bawah ini bisa dihapus/dikomentari.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$brand_name = "Rental Mobil";

// Mengamankan data session agar jika kosong tidak memicu error Undefined Array Key
$user_name = $_SESSION['user_name'] ?? 'Pelanggan';
$user_email = $_SESSION['user_email'] ?? 'Email belum diatur';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - <?= $brand_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

    <div class="flex min-h-screen">
        <?php include __DIR__ . '/../views/layouts/sidebar.php'; ?>

        <main class="flex-1 ml-64">
            <nav class="bg-white shadow-md sticky top-0 z-40">
                <div class="px-8 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Selamat datang, <strong><?php echo htmlspecialchars($user_name); ?></strong></span>
                        <a href="index.php?page=logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition duration-300">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
            </nav>

            <div class="p-8">
                <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white rounded-xl p-8 mb-8 shadow-lg">
                    <h2 class="text-3xl font-bold mb-2">Selamat Datang, <?php echo htmlspecialchars($user_name); ?>! 👋</h2>
                    <p class="text-gray-200">Anda berhasil login ke sistem rental mobil kami. Silakan jelajahi layanan kami.</p>
                </div>

                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-fire text-orange-500 mr-2"></i> Unit Terlaris Bulan Ini</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-100 flex items-center space-x-4">
                            <div class="p-3 bg-orange-100 rounded-full text-orange-600"><i class="fas fa-trophy text-xl"></i></div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Toyota Avanza</h4>
                                <p class="text-xs text-gray-500">120+ Transaksi</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-100 flex items-center space-x-4">
                            <div class="p-3 bg-purple-100 rounded-full text-[#5138bc]"><i class="fas fa-medal text-xl"></i></div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Toyota Innova Zenix</h4>
                                <p class="text-xs text-gray-500">95+ Transaksi</p>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-md p-4 border border-gray-100 flex items-center space-x-4">
                            <div class="p-3 bg-green-100 rounded-full text-green-600"><i class="fas fa-star text-xl"></i></div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Mitsubishi Pajero</h4>
                                <p class="text-xs text-gray-500">80+ Transaksi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-chart-pie text-[#5138bc] mr-2"></i> Statistik Sewa Anda</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                            <p class="text-gray-600 text-sm">Total Penyewaan</p>
                            <p class="text-2xl font-bold text-gray-800">5</p>
                        </div>
                        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                            <p class="text-gray-600 text-sm">Total Biaya</p>
                            <p class="text-2xl font-bold text-gray-800">Rp 2.500.000</p>
                        </div>
                        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                            <p class="text-gray-600 text-sm">Poin Loyalitas</p>
                            <p class="text-2xl font-bold text-gray-800">250</p>
                        </div>
                        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                            <p class="text-gray-600 text-sm">Denda Belum Dibayar</p>
                            <p class="text-2xl font-bold text-gray-800">Rp 350.000</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#ff5722]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Email</p>
                                <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($user_email); ?></p>
                            </div>
                            <i class="fas fa-envelope text-3xl text-[#ff5722] opacity-20"></i>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#5138bc]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Status Akun</p>
                                <p class="text-lg font-bold text-gray-800">Aktif</p>
                            </div>
                            <i class="fas fa-check-circle text-3xl text-[#5138bc] opacity-20"></i>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#6d44b8]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Tier Membership</p>
                                <p class="text-lg font-bold text-gray-800">Regular</p>
                            </div>
                            <i class="fas fa-star text-3xl text-[#6d44b8] opacity-20"></i>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Aksi Cepat</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <a href="index.php?page=sewa" class="bg-gradient-to-br from-[#5138bc] to-[#6d44b8] text-white rounded-lg p-6 text-center hover:shadow-lg transition duration-300 flex flex-col items-center justify-center">
                            <i class="fas fa-car text-3xl mb-2"></i>
                            <p class="font-bold">Sewa Mobil</p>
                        </a>
                        <a href="index.php?page=histori" class="bg-gradient-to-br from-[#ff5722] to-[#e64a19] text-white rounded-lg p-6 text-center hover:shadow-lg transition duration-300 flex flex-col items-center justify-center">
                            <i class="fas fa-history text-3xl mb-2"></i>
                            <p class="font-bold">Histori Sewa</p>
                        </a>
                        <a href="index.php?page=profil" class="bg-gradient-to-br from-[#4CAF50] to-[#45a049] text-white rounded-lg p-6 text-center hover:shadow-lg transition duration-300 flex flex-col items-center justify-center">
                            <i class="fas fa-user text-3xl mb-2"></i>
                            <p class="font-bold">Profil Saya</p>
                        </a>
                        <a href="index.php?page=bantuan" class="bg-gradient-to-br from-[#2196F3] to-[#0b7dda] text-white rounded-lg p-6 text-center hover:shadow-lg transition duration-300 flex flex-col items-center justify-center">
                            <i class="fas fa-question-circle text-3xl mb-2"></i>
                            <p class="font-bold">Bantuan</p>
                        </a>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Rekomendasi Mobil</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300 transform hover:-translate-y-2">
                            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] h-40 flex items-center justify-center">
                                <i class="fas fa-car text-6xl text-white opacity-30"></i>
                            </div>
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-2">Toyota Innova Zenix</h4>
                                <p class="text-[#ff5722] font-bold text-lg mb-4">Rp 750.000/hari</p>
                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    <p><i class="fas fa-calendar text-[#ff5722] mr-2"></i>2023</p>
                                    <p><i class="fas fa-cog text-[#ff5722] mr-2"></i>Matic</p>
                                    <p><i class="fas fa-tachometer-alt text-[#ff5722] mr-2"></i>2000 CC</p>
                                    <p><i class="fas fa-cube text-[#ff5722] mr-2"></i>7 Penumpang</p>
                                    <p><i class="fas fa-palette text-[#ff5722] mr-2"></i>Putih</p>
                                </div>
                                <a href="index.php?page=sewa&id_mobil=1" class="block w-full text-center bg-gradient-to-r from-[#ff5722] to-[#e64a19] text-white font-bold py-2 rounded-lg hover:shadow-lg transition duration-300">
                                    <i class="fas fa-book mr-2"></i>Sewa Sekarang
                                </a>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300 transform hover:-translate-y-2">
                            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] h-40 flex items-center justify-center">
                                <i class="fas fa-car text-6xl text-white opacity-30"></i>
                            </div>
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-2">Toyota Alphard</h4>
                                <p class="text-[#ff5722] font-bold text-lg mb-4">Rp 1.500.000/hari</p>
                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    <p><i class="fas fa-calendar text-[#ff5722] mr-2"></i>2022</p>
                                    <p><i class="fas fa-cog text-[#ff5722] mr-2"></i>Matic</p>
                                    <p><i class="fas fa-tachometer-alt text-[#ff5722] mr-2"></i>2500 CC</p>
                                    <p><i class="fas fa-cube text-[#ff5722] mr-2"></i>7 Penumpang</p>
                                    <p><i class="fas fa-palette text-[#ff5722] mr-2"></i>Putih</p>
                                </div>
                                <a href="index.php?page=sewa&id_mobil=2" class="block w-full text-center bg-gradient-to-r from-[#ff5722] to-[#e64a19] text-white font-bold py-2 rounded-lg hover:shadow-lg transition duration-300">
                                    <i class="fas fa-book mr-2"></i>Sewa Sekarang
                                </a>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300 transform hover:-translate-y-2">
                            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] h-40 flex items-center justify-center">
                                <i class="fas fa-car text-6xl text-white opacity-30"></i>
                            </div>
                            <div class="p-6">
                                <h4 class="text-xl font-bold text-gray-800 mb-2">Daihatsu Sigra</h4>
                                <p class="text-[#ff5722] font-bold text-lg mb-4">Rp 300.000/hari</p>
                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    <p><i class="fas fa-calendar text-[#ff5722] mr-2"></i>2023</p>
                                    <p><i class="fas fa-cog text-[#ff5722] mr-2"></i>Manual</p>
                                    <p><i class="fas fa-tachometer-alt text-[#ff5722] mr-2"></i>1200 CC</p>
                                    <p><i class="fas fa-cube text-[#ff5722] mr-2"></i>7 Penumpang</p>
                                    <p><i class="fas fa-palette text-[#ff5722] mr-2"></i>Orange</p>
                                </div>
                                <a href="index.php?page=sewa&id_mobil=3" class="block w-full text-center bg-gradient-to-r from-[#ff5722] to-[#e64a19] text-white font-bold py-2 rounded-lg hover:shadow-lg transition duration-300">
                                    <i class="fas fa-book mr-2"></i>Sewa Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Testimoni Pelanggan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-[#ff5722]">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-[#5138bc] to-[#6d44b8] rounded-full flex items-center justify-center text-white font-bold">
                                    AH
                                </div>
                                <div class="ml-4">
                                    <p class="font-bold text-gray-800">Ahmad Hidayat</p>
                                    <p class="text-sm text-gray-600">Pelanggan Setia</p>
                                </div>
                            </div>
                            <div class="flex mb-3">
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                            </div>
                            <p class="text-gray-600 text-sm">"Layanan rental mobil yang sangat memuaskan! Mobil dalam kondisi prima, sopir profesional, dan harga yang kompetitif. Saya akan merekomendasikan ke teman-teman saya."</p>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-[#5138bc]">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-[#5138bc] to-[#6d44b8] rounded-full flex items-center justify-center text-white font-bold">
                                    SR
                                </div>
                                <div class="ml-4">
                                    <p class="font-bold text-gray-800">Siti Rahayu</p>
                                    <p class="text-sm text-gray-600">Pelanggan Baru</p>
                                </div>
                            </div>
                            <div class="flex mb-3">
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-gray-300"></i>
                            </div>
                            <p class="text-gray-600 text-sm">"Proses booking sangat mudah dan cepat. Customer service responsif and membantu. Mobil yang saya sewa dalam kondisi bersih dan terawat. Puas dengan layanannya!"</p>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-[#4CAF50]">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-[#5138bc] to-[#6d44b8] rounded-full flex items-center justify-center text-white font-bold">
                                    BW
                                </div>
                                <div class="ml-4">
                                    <p class="font-bold text-gray-800">Budi Wijaya</p>
                                    <p class="text-sm text-gray-600">Pelanggan Korporat</p>
                                </div>
                            </div>
                            <div class="flex mb-3">
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                                <i class="fas fa-star text-[#ff5722]"></i>
                            </div>
                            <p class="text-gray-600 text-sm">"Sebagai perusahaan, kami sering menggunakan layanan rental mobil ini untuk kebutuhan bisnis. Armada lengkap, harga kompetitif, dan layanan profesional. Sangat merekomendasikan!"</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Informasi Penting</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border-l-4 border-[#ff5722] pl-4">
                            <h4 class="font-bold text-gray-800 mb-2">Syarat & Ketentuan</h4>
                            <p class="text-gray-600 text-sm">Pastikan Anda telah membaca dan memahami semua syarat dan ketentuan sebelum melakukan penyewaan mobil.</p>
                        </div>
                        <div class="border-l-4 border-[#5138bc] pl-4">
                            <h4 class="font-bold text-gray-800 mb-2">Kebijakan Pembatalan</h4>
                            <p class="text-gray-600 text-sm">Pembatalan dapat dilakukan hingga 24 jam sebelum waktu penyewaan dimulai tanpa biaya tambahan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>