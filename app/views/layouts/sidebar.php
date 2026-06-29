<?php $current_page = $_GET['page'] ?? 'home'; ?>
<aside class="w-64 bg-gradient-to-b from-[#5138bc] to-[#6d44b8] text-white shadow-lg fixed h-screen overflow-y-auto">
    <div class="p-6 border-b border-white border-opacity-20">
        <div class="flex items-center space-x-3 font-bold text-2xl">
            <i class="fas fa-car"></i> <span>SIREMO</span>
        </div>
    </div>

    <div class="p-6 border-b border-white border-opacity-20 flex items-center space-x-3">
        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
            <i class="fas fa-user"></i>
        </div>
        
        <div class="overflow-hidden">
            <p class="font-bold text-sm truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Pelanggan'); ?></p>
            <p class="text-xs text-gray-200 truncate"><?= htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
        </div>
    </div>

    <nav class="p-4 space-y-2">
        <a href="index.php?page=home" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $current_page == 'home' ? 'bg-white bg-opacity-20 shadow-inner' : 'hover:bg-white hover:bg-opacity-10' ?>">
            <i class="fas fa-home w-5"></i> <span>Dashboard</span>
        </a>
        <a href="index.php?page=gallery" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $current_page == 'gallery' ? 'bg-white bg-opacity-20 shadow-inner' : 'hover:bg-white hover:bg-opacity-10' ?>">
            <i class="fas fa-images w-5"></i> <span>Gallery</span>
        </a>
        <a href="index.php?page=histori" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $current_page == 'histori' ? 'bg-white bg-opacity-20 shadow-inner' : 'hover:bg-white hover:bg-opacity-10' ?>">
            <i class="fas fa-history w-5"></i> <span>Histori Sewa</span>
        </a>
        <a href="index.php?page=denda" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $current_page == 'denda' ? 'bg-white bg-opacity-20 shadow-inner' : 'hover:bg-white hover:bg-opacity-10' ?>">
            <i class="fas fa-exclamation-circle w-5"></i> <span>Denda</span>
        </a>
        <a href="index.php?page=profil" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $current_page == 'profil' ? 'bg-white bg-opacity-20 shadow-inner' : 'hover:bg-white hover:bg-opacity-10' ?>">
            <i class="fas fa-user-circle w-5"></i> <span>Profil Saya</span>
        </a>
        <a href="index.php?page=bantuan" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= $current_page == 'bantuan' ? 'bg-white bg-opacity-20 shadow-inner' : 'hover:bg-white hover:bg-opacity-10' ?>">
            <i class="fas fa-question-circle w-5"></i> <span>Bantuan</span>
        </a>
    </nav>

    <div class="p-4 mt-10">
        <a href="index.php?page=logout" class="flex items-center justify-center space-x-2 bg-red-500 hover:bg-red-600 p-3 rounded-xl font-bold transition text-white">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</aside>