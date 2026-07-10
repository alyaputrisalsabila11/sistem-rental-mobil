    <?php
    // Menentukan halaman aktif berdasarkan parameter 'action' di URL
    $action = isset($_GET['action']) ? $_GET['action'] : 'home';
    ?>
    <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 shadow-xl">
        <div class="p-5 flex items-center space-x-3 border-b border-slate-800">
            <i class="fas fa-sliders-h text-xl text-indigo-400"></i>
            <span class="text-xl font-black tracking-wider uppercase"><?= $brand_name; ?></span>
        </div>

        <div class="p-5 border-b border-slate-800 bg-slate-950/40 flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-bold text-white shadow shadow-indigo-500/50 flex-shrink-0">
                SA
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold truncate"><?= htmlspecialchars($admin_name); ?></p>
                <p class="text-[11px] text-indigo-300 font-medium truncate"><?= htmlspecialchars($admin_email); ?></p>
                <span class="inline-block mt-1 px-1.5 py-0.5 text-[9px] font-bold bg-indigo-500/20 text-indigo-300 rounded border border-indigo-500/30">Staff Admin</span>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <a href="index.php?page=home_admin&action=home" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'home' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-chart-line text-base w-5 text-center group-hover:text-indigo-400 transition"></i>
                <span>Dashboard</span>
            </a>

            <a href="index.php?page=home_admin&action=tambah_mobil" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'tambah_mobil' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-plus-circle text-base w-5 text-center group-hover:text-indigo-400 transition"></i>
                <span>Tambah Unit</span>
            </a>

            <a href="index.php?page=home_admin&action=data_mobil" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'data_mobil' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-car-side text-base w-5 text-center group-hover:text-indigo-400 transition"></i>
                <span>Data Mobil</span>
            </a>

            <a href="index.php?page=home_admin&action=konfirmasi_booking" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'konfirmasi_booking' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-file-invoice-dollar text-base w-5 text-center group-hover:text-indigo-400 transition"></i> 
                <span>Konfirmasi Booking</span>
            </a>

            <a href="index.php?page=home_admin&action=data_pelanggan" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'data_pelanggan' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-users text-base w-5 text-center group-hover:text-indigo-400 transition"></i>
                <span>Data Pelanggan</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="index.php?page=logout" class="flex items-center justify-center space-x-2 w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition shadow-md">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar Sistem</span>
            </a>
        </div>
    </aside>