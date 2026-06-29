<?php
$brand_name = "SIREMO";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Mobil Impian Kamu - <?= $brand_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .animate-slide-in-left {
            animation: slideInLeft 0.6s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen font-sans">

    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white px-6 py-4 flex justify-between items-center shadow-lg sticky top-0 z-50">
        <div class="flex items-center space-x-3 font-bold text-xl">
            <i class="fas fa-car text-2xl"></i>
            <span><?= $brand_name; ?></span>
        </div>
        <div class="flex space-x-4 text-sm">
            <a href="index.php?page=login" class="hover:bg-white hover:text-[#5138bc] px-4 py-2 rounded-lg transition duration-300 flex items-center space-x-1">
                <i class="fas fa-sign-in-alt"></i> <span>Masuk</span>
            </a>
            <a href="index.php?page=register" class="bg-[#ff5722] hover:bg-[#e64a19] px-4 py-2 rounded-lg transition duration-300 flex items-center space-x-1">
                <i class="fas fa-user-plus"></i> <span>Daftar</span>
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-[#2d1b4e] via-[#3d2463] to-[#4d3073] text-white py-24 px-4 text-center flex-1 flex items-center justify-center">
        <div class="max-w-4xl mx-auto animate-fade-in-up">
            <div class="flex justify-center items-center space-x-4 mb-6">
                <i class="fas fa-car text-6xl"></i>
                <h1 class="text-5xl md:text-6xl font-bold">Sewa Mobil Impian Kamu</h1>
            </div>
            <p class="text-xl md:text-2xl text-gray-200 mb-8">
                Layanan rental mobil terpercaya dengan harga terjangkau, armada lengkap, dan pelayanan terbaik
            </p>
            <div class="flex justify-center space-x-4 flex-wrap gap-4">
                <a href="index.php?page=login" class="bg-white text-[#5138bc] px-8 py-3 rounded-lg font-bold text-lg shadow-lg hover:shadow-xl transition duration-300 flex items-center space-x-2">
                    <i class="fas fa-sign-in-alt"></i> <span>Masuk</span>
                </a>
                <a href="index.php?page=register" class="bg-[#ff5722] text-white px-8 py-3 rounded-lg font-bold text-lg shadow-lg hover:bg-[#e64a19] transition duration-300 flex items-center space-x-2">
                    <i class="fas fa-user-plus"></i> <span>Daftar Sekarang</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-white py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-4xl font-bold text-center text-gray-800 mb-16">Mengapa Memilih Kami?</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gradient-to-br from-[#5138bc] to-[#6d44b8] text-white rounded-xl p-8 shadow-lg hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Armada Lengkap</h3>
                    <p class="text-gray-100">Pilihan mobil yang beragam dari berbagai kategori, mulai dari city car hingga SUV mewah sesuai kebutuhan Anda.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gradient-to-br from-[#ff5722] to-[#e64a19] text-white rounded-xl p-8 shadow-lg hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Harga Terjangkau</h3>
                    <p class="text-gray-100">Harga kompetitif dengan berbagai paket dan diskon menarik untuk pelanggan setia kami.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gradient-to-br from-[#4CAF50] to-[#45a049] text-white rounded-xl p-8 shadow-lg hover:shadow-xl transition duration-300 transform hover:-translate-y-2">
                    <div class="text-5xl mb-4">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3">Aman & Terpercaya</h3>
                    <p class="text-gray-100">Layanan asuransi lengkap, customer service 24/7, dan proses yang transparan dan mudah.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="bg-gray-100 py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-4xl font-bold text-center text-gray-800 mb-16">Keunggulan Layanan Kami</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Benefit 1 -->
                <div class="bg-white rounded-lg p-8 shadow-md flex items-start space-x-4">
                    <div class="text-3xl text-[#ff5722] flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Proses Booking Mudah</h3>
                        <p class="text-gray-600">Cukup beberapa klik, Anda sudah bisa memesan mobil impian dengan sistem online yang user-friendly.</p>
                    </div>
                </div>

                <!-- Benefit 2 -->
                <div class="bg-white rounded-lg p-8 shadow-md flex items-start space-x-4">
                    <div class="text-3xl text-[#5138bc] flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Mobil Terawat</h3>
                        <p class="text-gray-600">Semua mobil kami dalam kondisi prima dengan perawatan rutin dan inspeksi berkala.</p>
                    </div>
                </div>

                <!-- Benefit 3 -->
                <div class="bg-white rounded-lg p-8 shadow-md flex items-start space-x-4">
                    <div class="text-3xl text-[#4CAF50] flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Asuransi Komprehensif</h3>
                        <p class="text-gray-600">Perlindungan menyeluruh untuk setiap perjalanan Anda dengan berbagai pilihan paket asuransi.</p>
                    </div>
                </div>

                <!-- Benefit 4 -->
                <div class="bg-white rounded-lg p-8 shadow-md flex items-start space-x-4">
                    <div class="text-3xl text-[#2196F3] flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Layanan 24/7</h3>
                        <p class="text-gray-600">Tim customer service kami siap membantu Anda kapan saja, siang atau malam.</p>
                    </div>
                </div>

                <!-- Benefit 5 -->
                <div class="bg-white rounded-lg p-8 shadow-md flex items-start space-x-4">
                    <div class="text-3xl text-[#FF9800] flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Fleksibel & Transparan</h3>
                        <p class="text-gray-600">Tidak ada biaya tersembunyi, semua harga sudah termasuk pajak dan asuransi dasar.</p>
                    </div>
                </div>

                <!-- Benefit 6 -->
                <div class="bg-white rounded-lg p-8 shadow-md flex items-start space-x-4">
                    <div class="text-3xl text-[#E91E63] flex-shrink-0">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Program Loyalitas</h3>
                        <p class="text-gray-600">Dapatkan poin reward setiap kali menyewa dan tukarkan dengan diskon menarik.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-bold mb-4">Siap Memulai Petualangan Anda?</h2>
            <p class="text-xl text-gray-200 mb-8">Daftar sekarang dan dapatkan diskon spesial 10% untuk penyewaan pertama Anda!</p>
            <a href="index.php?page=register" class="inline-flex items-center space-x-2 bg-[#ff5722] text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg hover:bg-[#e64a19] transition duration-300">
                <i class="fas fa-user-plus"></i> <span>Daftar Sekarang</span>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8 px-4 text-center border-t border-gray-800">
        <p>&copy; <?= date('Y'); ?> <?= $brand_name; ?>. Semua hak dilindungi. | <a href="#" class="text-gray-300 hover:text-white">Kebijakan Privasi</a> | <a href="#" class="text-gray-300 hover:text-white">Syarat & Ketentuan</a></p>
    </footer>

</body>
</html>
