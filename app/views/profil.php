<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - <?= $brand_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php 
    /** @var array $user */
    /** @var string $brand_name */
    /** @var string $current_page */
    include __DIR__ . '/layouts/sidebar.php'; ?>

    <main class="flex-1 ml-64">
        <!-- Top Navbar -->
        <nav class="bg-white shadow-md sticky top-0 z-40">
            <div class="px-8 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600 text-sm">Halo, <strong><?= htmlspecialchars($user['nama_lengkap']); ?></strong></span>
                    <a href="index.php?page=logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="p-8">
            <!-- Header Profil -->
            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white rounded-2xl p-10 mb-8 shadow-lg flex items-center space-x-8">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-4xl font-bold border-2 border-white/50">
                    <?= strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                </div>
                <div>
                    <h2 class="text-4xl font-bold mb-1"><?= htmlspecialchars($user['nama_lengkap']); ?></h2>
                    <p class="text-indigo-100"><i class="fas fa-award mr-2"></i><?= htmlspecialchars($user['nama_level'] ?? 'Regular'); ?> Member</p>
                    <p class="text-xs text-white/40 mt-2 font-mono">Customer ID: #<?= $user['id']; ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Info Utama -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <h3 class="text-xl font-bold text-gray-800 mb-8 flex items-center">
                            <i class="fas fa-user-circle text-[#5138bc] mr-3"></i> Informasi Akun
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Lengkap</label>
                                <p class="text-gray-800 font-bold text-lg border-b border-gray-100 pb-2"><?= htmlspecialchars($user['nama_lengkap']); ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Username</label>
                                <p class="text-gray-800 font-bold text-lg border-b border-gray-100 pb-2">@<?= htmlspecialchars($user['username']); ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Email Terdaftar</label>
                                <p class="text-gray-800 font-bold text-lg border-b border-gray-100 pb-2"><?= htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nomor Telepon</label>
                                <p class="text-gray-800 font-bold text-lg border-b border-gray-100 pb-2"><?= htmlspecialchars($user['no_telp']); ?></p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Alamat Lengkap</label>
                                <p class="text-gray-800 font-bold text-lg border-b border-gray-100 pb-2"><?= htmlspecialchars($user['alamat']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistik Samping -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <h3 class="font-bold text-gray-800 mb-6 flex items-center"><i class="fas fa-chart-line mr-2 text-indigo-500"></i> Aktifitas</h3>
                        <div class="space-y-4">
                            <div class="p-4 bg-indigo-50 rounded-2xl flex items-center justify-between">
                                <span class="text-indigo-600 text-sm font-bold uppercase tracking-tighter">Poin</span>
                                <span class="text-2xl font-black text-indigo-900">250</span>
                            </div>
                            <div class="p-4 bg-orange-50 rounded-2xl flex items-center justify-between">
                                <span class="text-orange-600 text-sm font-bold uppercase tracking-tighter">Total Sewa</span>
                                <span class="text-2xl font-black text-orange-900">5</span>
                            </div>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-50">
                            <p class="text-[10px] text-gray-400 text-center">Bergabung Sejak: <?= date('d F Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>