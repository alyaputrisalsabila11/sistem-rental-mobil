<?php
    // Menentukan halaman aktif berdasarkan parameter 'action' di URL
    $action = isset($_GET['action']) ? $_GET['action'] : 'home';
    ?>
    <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 shadow-xl h-screen sticky top-0">
        <div class="p-5 flex items-center space-x-3 border-b border-slate-800">
            <span class="text-xl font-black tracking-wider uppercase">SIREMO</span>
        </div>

        <div class="p-5 border-b border-slate-800 bg-slate-950/40 flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-bold text-white shadow shadow-indigo-500/50 flex-shrink-0">
                SA
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'staffadmin'); ?></p>
                <p class="text-[11px] text-indigo-300 font-medium truncate"><?= htmlspecialchars($_SESSION['user_email'] ?? '@staffadmin.swm'); ?></p>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-4 overflow-y-auto">
            <!-- DASHBOARD -->
            <a href="index.php?page=Admin&action=home" 
               class="flex items-center space-x-3 py-2.5 px-4 rounded-xl text-sm font-bold transition <?= $action === 'home' ? 'bg-white/10 text-white border-l-4 border-indigo-500 shadow-inner' : 'text-slate-400 hover:bg-slate-800' ?>">
                <span>Dashboard</span>
            </a>

            <!-- MASTER -->
            <div>
                <p class="px-4 text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Master</p>
                <div class="space-y-1">
                    <a href="index.php?page=Admin&action=tambah_mobil" class="flex items-center py-2 px-4 rounded-lg text-xs font-bold transition <?= $action === 'tambah_mobil' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Tambah Unit</a>
                    <a href="index.php?page=Admin&action=tambah_fasilitas" class="flex items-center py-2 px-4 rounded-lg text-xs font-bold transition <?= $action === 'tambah_fasilitas' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Tambah Fasilitas</a>
                </div>
            </div>

            <!-- DATA -->
            <div>
                <p class="px-4 text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Data</p>
                <div class="space-y-1 text-xs">
                    <a href="index.php?page=Admin&action=data_mobil" class="flex items-center py-2 px-4 rounded-lg font-bold transition <?= $action === 'data_mobil' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Data Mobil</a>
                    <a href="index.php?page=Admin&action=data_voucher" class="flex items-center py-2 px-4 rounded-lg font-bold transition <?= $action === 'data_voucher' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Data Voucher</a>
                    <a href="index.php?page=Admin&action=data_fasilitas" class="flex items-center py-2 px-4 rounded-lg font-bold transition <?= $action === 'data_fasilitas' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Data Fasilitas</a>
                    <a href="index.php?page=Admin&action=data_pelanggan" class="flex items-center py-2 px-4 rounded-lg font-bold transition <?= $action === 'data_pelanggan' ? 'bg-white/10 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white' ?>">Data Pelanggan</a>
                </div>
            </div>
        </nav>

        <div class="p-4 border-t border-slate-800 flex-shrink-0">
            <a href="index.php?page=logout" class="flex items-center justify-center space-x-2 w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition shadow-md">
                <span>Keluar Sistem</span>
            </a>
        </div>
    </aside>