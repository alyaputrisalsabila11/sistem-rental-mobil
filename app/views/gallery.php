<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Mobil - <?= $brand_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 ml-64">
        <!-- Top Navbar -->
        <nav class="bg-white shadow-sm sticky top-0 z-40">
            <div class="px-8 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Gallery Mobil</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600 text-sm">Selamat datang, <strong><?= htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                    <a href="index.php?page=logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="p-8">
            <!-- Banner Section -->
            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white rounded-2xl p-10 mb-10 shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <h2 class="text-4xl font-black mb-3 tracking-tight">Koleksi Mobil Kami</h2>
                    <p class="text-indigo-100 text-lg max-w-2xl">Pilih mobil impian Anda dari koleksi lengkap kami dengan kondisi terbaik, mesin terawat, dan harga paling kompetitif.</p>
                </div>
                <!-- Dekorasi Background -->
                <i class="fas fa-car-side absolute -right-10 -bottom-10 text-[180px] text-white opacity-10 rotate-12"></i>
            </div>

            <!-- Filter Kategori (Opsi tambahan jika ingin digunakan) -->
            <?php if (!empty($categories)): ?>
            <div class="flex space-x-4 mb-8 overflow-x-auto pb-2">
                <a href="index.php?page=gallery" class="px-6 py-2 rounded-full font-bold text-sm <?= !isset($_GET['kategori']) ? 'bg-[#5138bc] text-white' : 'bg-white text-gray-500 border border-gray-200' ?>">Semua</a>
                <?php foreach($categories as $cat): ?>
                <a href="index.php?page=gallery&kategori=<?= $cat['id_kategori']; ?>" 
                   class="px-6 py-2 rounded-full font-bold text-sm <?= (isset($_GET['kategori']) && $_GET['kategori'] == $cat['id_kategori']) ? 'bg-[#5138bc] text-white' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50' ?>">
                    <?= $cat['nama_kategori']; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Grid Mobil -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($cars)): ?>
                    <?php foreach ($cars as $car): ?>

                        <div class="bg-white rounded-3xl shadow-sm overflow-hidden hover:shadow-2xl transition duration-500 transform hover:-translate-y-2 border border-gray-100 group">
                            <!-- Image Area -->
                            <div class="bg-[#5138bc]/5 h-52 flex items-center justify-center relative overflow-hidden">
                                <?php 
                                // Jika kolom gambar di database tidak kosong, ubah biner ke Base64
                                if (!empty($car['gambar'])) {
                                    // Mengonversi data biner dari database menjadi teks yang bisa dibaca browser
                                    $base64_image = base64_encode($car['gambar']);
                                    $img_src = "data:image/jpeg;base64," . $base64_image;
                                } else {
                                    // Jika kosong, gunakan gambar placeholder online biar tidak pecah
                                    $img_src = "https://placehold.co/600x400?text=Tanpa+Foto";
                                }
                                ?>
                                
                                <img src="<?= $img_src; ?>" 
                                     alt="<?= htmlspecialchars($car['merk_mobil']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                                
                                <span class="absolute top-4 left-4 bg-white/90 backdrop-blur text-[#5138bc] text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm">
                                    <?= htmlspecialchars($car['nama_kategori'] ?? 'Premium'); ?>
                                </span>
                            </div>

                            <!-- Details -->
                            <div class="p-8">
                                <h4 class="text-2xl font-black text-gray-800 tracking-tight"><?= htmlspecialchars($car['merk_mobil']); ?></h4>
                                <div class="mt-4 mb-6">
                                    <span class="text-gray-400 text-xs font-bold uppercase">Harga Sewa</span>
                                    <p class="text-[#ff5722] font-black text-3xl mt-1">
                                        <span class="text-sm">Rp</span> <?= number_format($car['harga_dinamis'], 0, ',', '.'); ?>
                                        <span class="text-xs font-normal text-gray-400 italic">/hari</span>
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4 text-xs font-bold text-gray-500 mb-8 pt-6 border-t border-gray-100">
                                    <div class="flex items-center"><i class="fas fa-calendar-alt text-indigo-400 w-5"></i><?= $car['tahun']; ?></div>
                                    <div class="flex items-center"><i class="fas fa-cog text-indigo-400 w-5"></i><?= ucfirst($car['tipe']); ?></div>
                                    <div class="flex items-center"><i class="fas fa-tachometer-alt text-indigo-400 w-5"></i><?= $car['cc']; ?> CC</div>
                                    <div class="flex items-center"><i class="fas fa-palette text-indigo-400 w-5"></i><?= htmlspecialchars($car['warna']); ?></div>
                                </div>

                                <a href="index.php?page=sewa&id_mobil=<?= $car['id_mobil']; ?>" 
                                   class="block w-full text-center bg-[#5138bc] hover:bg-[#ff5722] text-white font-black py-4 rounded-2xl transition duration-300 shadow-lg shadow-indigo-100 hover:shadow-orange-100">
                                    PESAN SEKARANG
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full py-32 text-center bg-white rounded-3xl border-2 border-dashed border-gray-100">
                        <i class="fas fa-car-crash text-7xl text-gray-100 mb-6"></i>
                        <p class="text-gray-400 font-bold uppercase tracking-widest">Belum ada armada tersedia saat ini</p>
                        <a href="index.php?page=gallery" class="text-[#5138bc] text-sm font-bold mt-4 inline-block underline">Reset Filter</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>