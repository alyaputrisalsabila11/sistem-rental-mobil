    <?php
    // Menentukan halaman aktif berdasarkan parameter 'action' di URL
    $action = isset($_GET['action']) ? $_GET['action'] : 'home';
    ?>
    <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 shadow-xl">
        <div class="p-5 flex items-center space-x-3 border-b border-slate-800">
            <span class="text-xl font-black tracking-wider uppercase">SIREMO</span>
        </div>

        <div class="p-5 border-b border-slate-800 bg-slate-950/40 flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-bold text-white shadow shadow-indigo-500/50 flex-shrink-0">
                M
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?></p>
                <p class="text-xs text-slate-400 truncate"><?= htmlspecialchars($_SESSION['user_email'] ?? '@Manager.swm'); ?></p>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <a href="index.php?page=manager_dashboard&action=home" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'home' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <span>Dashboard</span>
            </a>

            <a href="index.php?page=manager_dashboard&action=buat_lokasi" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'buat_lokasi' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <span>Buat Cabang</span>
            </a>

            <a href="index.php?page=manager_dashboard&action=buat_akun" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'buat_akun' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <span>Buat Akun & Staff</span>
            </a>

            <a href="index.php?page=manager_dashboard&action=buat_loyal" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'buat_loyal' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <span>Kelola Loyalitas</span>
            </a>

            <a href="index.php?page=manager_dashboard&action=buat_voucher" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'buat_voucher' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <span>Kelola Voucher</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="index.php?page=logout" class="flex items-center justify-center space-x-2 w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition shadow-md">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar Sistem</span>
            </a>
        </div>
    </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
        
        <header class="bg-white shadow-sm border-b border-gray-100 h-16 flex items-center justify-between px-8 flex-shrink-0">
            <div>
                <h1 class="text-lg font-bold text-gray-800 capitalize"><?= $action === 'home' ? 'DASHBOARD' : ($action === 'buat_lokasi' ? 'BUAT CABANG' : 'BUAT AKUN BARU'); ?></h1>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span>Selamat datang,</span>
                <span class="font-bold text-gray-800"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?></span>
                <span class="w-2 h-2 bg-red-500 rounded-full inline-block animate-pulse ml-1"></span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center space-x-3 shadow-sm">
                    <span class="text-lg">✅</span>
                    <span class="text-sm font-medium"><?= $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>