<?php
    // Menentukan halaman aktif berdasarkan parameter 'action' di URL
    $action = isset($_GET['action']) ? $_GET['action'] : 'home';
    ?>
    <!-- Menggunakan w-64 fixed dan flex-shrink-0 untuk mencegah perubahan ukuran grid saat diklik -->
    <aside class="w-64 bg-[#0F172A] text-white flex flex-col flex-shrink-0 shadow-xl h-screen overflow-y-auto">
        <div class="p-5 flex items-center space-x-3 border-b border-slate-800 flex-shrink-0">
            <span class="text-xl font-black tracking-wider uppercase">SIREMO</span>
        </div>

        <div class="p-5 border-b border-slate-800 bg-slate-900/50 flex items-center space-x-3 flex-shrink-0">
            <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-bold text-white shadow shadow-indigo-500/50 flex-shrink-0">
                M
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?></p>
                <p class="text-xs text-slate-400 truncate"><?= htmlspecialchars($_SESSION['user_email'] ?? '@Manager.swm'); ?></p>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-4">
            <!-- DASHBOARD -->
            <a href="index.php?page=manager_dashboard&action=home" 
               class="flex items-center space-x-3 py-2.5 px-4 rounded-xl text-sm font-bold transition <?= $action === 'home' ? 'bg-slate-800/80 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <span>Dashboard</span>
            </a>

            <!-- MASTER -->
            <div>
                <p class="px-4 text-[10px] font-bold uppercase text-slate-500 tracking-wider mb-2">Master</p>
                <div class="space-y-1">
                    <a href="index.php?page=manager_dashboard&action=buat_lokasi" class="flex items-center py-2 px-4 rounded-lg text-xs font-bold transition <?= $action === 'buat_lokasi' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Buat Cabang</a>
                    <a href="index.php?page=manager_dashboard&action=buat_akun" class="flex items-center py-2 px-4 rounded-lg text-xs font-bold transition <?= $action === 'buat_akun' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Buat Akun Karyawan</a>
                    <a href="index.php?page=manager_dashboard&action=buat_loyal" class="flex items-center py-2 px-4 rounded-lg text-xs font-bold transition <?= $action === 'buat_loyal' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Kelola Loyalitas</a>
                    <a href="index.php?page=manager_dashboard&action=buat_voucher" class="flex items-center py-2 px-4 rounded-lg text-xs font-bold transition <?= $action === 'buat_voucher' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Kelola Voucher</a>
                </div>
            </div>

            <!-- DATA -->
            <div>
                <p class="px-4 text-[10px] font-bold uppercase text-slate-500 tracking-wider mb-2">Data</p>
                <div class="space-y-1">
                    <a href="index.php?page=manager_dashboard&action=data_cabang" class="flex items-center py-2 px-4 rounded-lg text-xs font-semibold transition <?= $action === 'data_cabang' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Data Cabang</a>
                    <a href="index.php?page=manager_dashboard&action=data_karyawan" class="flex items-center py-2 px-4 rounded-lg text-xs font-semibold transition <?= $action === 'data_karyawan' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Data Akun Karyawan</a>
                    <a href="index.php?page=manager_dashboard&action=data_pelanggan" class="flex items-center py-2 px-4 rounded-lg text-xs font-semibold transition <?= $action === 'data_pelanggan' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Data Pelanggan</a>
                    <a href="index.php?page=manager_dashboard&action=data_mobil" class="flex items-center py-2 px-4 rounded-lg text-xs font-semibold transition <?= $action === 'data_mobil' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Data Mobil</a>
                    <a href="index.php?page=manager_dashboard&action=data_loyalitas" class="flex items-center py-2 px-4 rounded-lg text-xs font-semibold transition <?= $action === 'data_loyalitas' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Data Loyalitas</a>
                    <a href="index.php?page=manager_dashboard&action=data_voucher" class="flex items-center py-2 px-4 rounded-lg text-xs font-semibold transition <?= $action === 'data_voucher' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Data Voucher</a>
                    <a href="index.php?page=manager_dashboard&action=data_fasilitas" class="flex items-center py-2 px-4 rounded-lg text-xs font-semibold transition <?= $action === 'data_fasilitas' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Data Fasilitas</a>
                    <a href="index.php?page=manager_dashboard&action=data_kerusakan" class="flex items-center py-2 px-4 rounded-lg text-xs font-semibold transition <?= $action === 'data_kerusakan' ? 'bg-slate-800/80 text-white border-l-4 border-indigo-500' : 'text-slate-400 hover:text-white hover:bg-slate-800' ?>">Data Kondisi Mobil</a>
                </div>
            </div>
        </nav>

        <div class="p-4 border-t border-slate-800 flex-shrink-0">
            <a href="index.php?page=logout" class="flex items-center justify-center space-x-2 w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition shadow-md">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar Sistem</span>
            </a>
        </div>
    </aside>