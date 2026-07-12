<?php
// =========================================================================
// FILE: views/user/sidebar/pelangganside.php (Sidebar Member Navigasi)
// =========================================================================

// Menentukan menu aktif berdasarkan parameter action dari URL
$action = isset($_GET['action']) ? $_GET['action'] : 'home';
?>
<!-- Navigasi samping menggunakan warna slate-900 gelap untuk kesan premium -->
<aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 shadow-xl h-screen sticky top-0">
    <!-- Logotype Siremo -->
    <div class="p-5 flex items-center space-x-3 border-b border-slate-800">
        <span class="text-xl font-black tracking-wider uppercase text-indigo-500">SIREMO</span>
    </div>

    <!-- Profil Singkat Pengguna Aktif -->
    <div class="p-5 border-b border-slate-800 bg-slate-950/40 flex items-center space-x-3">
        <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-bold text-white shadow shadow-indigo-500/50 uppercase">
            <!-- Menampilkan huruf pertama nama pelanggan -->
            <?= substr($_SESSION['user_name'] ?? 'P', 0, 1); ?>
        </div>
        <div class="overflow-hidden">
            <p class="text-sm font-bold truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Member'); ?></p>
            <p class="text-[11px] text-indigo-300 font-medium">Pelanggan Member</p>
        </div>
    </div>

    <!-- Daftar Link Menu -->
    <nav class="flex-1 p-4 space-y-4 overflow-y-auto">
        <!-- Halaman Dashboard Utama -->
        <a href="index.php?page=home&action=home" 
           class="flex items-center space-x-3 py-2.5 px-4 rounded-xl text-sm font-bold transition <?= $action === 'home' ? 'bg-white/10 text-white border-l-4 border-indigo-500 shadow-inner' : 'text-slate-400 hover:bg-slate-800' ?>">
            <span>Dashboard</span>
        </a>

        <!-- Menu Transaksi -->
        <div>
            <p class="px-4 text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Transaksi</p>
            <a href="index.php?page=home&action=gallery" class="flex items-center py-2 px-4 rounded-lg text-xs font-bold transition <?= $action === 'gallery' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Gallery Mobil</a>
        </div>

        <!-- Menu Data Saya -->
        <div>
            <p class="px-4 text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Data Saya</p>
            <div class="space-y-1 text-xs">
                <a href="index.php?page=home&action=sewa_saya" class="flex items-center py-2 px-4 rounded-lg font-bold transition <?= $action === 'sewa_saya' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Sewa Saya</a>
                <a href="index.php?page=home&action=history_sewa" class="flex items-center py-2 px-4 rounded-lg font-bold transition <?= $action === 'history_sewa' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">History Sewa</a>
                
                <!-- PENAMBAHAN MENU MANDATORI: Denda Saya -->
                <a href="index.php?page=home&action=denda_saya" class="flex items-center py-2 px-4 rounded-lg font-bold transition <?= $action === 'denda_saya' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Denda Saya</a>
                
                <a href="index.php?page=home&action=voucher_saya" class="flex items-center py-2 px-4 rounded-lg font-bold transition <?= $action === 'voucher_saya' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Voucher Saya</a>
            </div>
        </div>

        <!-- Menu Profil -->
        <a href="index.php?page=home&action=akun_saya" class="flex items-center space-x-3 py-2.5 px-4 rounded-xl text-sm font-bold transition <?= $action === 'akun_saya' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800' ?>">
            <span>Akun Saya</span>
        </a>
    </nav>

    <!-- Tombol Keluar Aplikasi -->
    <div class="p-4 border-t border-slate-800">
        <a href="index.php?page=logout" class="flex items-center justify-center space-x-2 w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition">
            <span>Keluar</span>
        </a>
    </div>
</aside>