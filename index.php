<?php
// ==========================================
// FILE: index.php (Router Utama Ter-Filter)
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Memulai sesi aplikasi secara global
}

// Autoloader otomatis mencari berkas Model & Controller berdasarkan nama class
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/'
    ];
    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

if (file_exists(__DIR__ . '/init.php')) {
    require_once __DIR__ . '/init.php'; // Memuat file database init jika ada
}

$page = $_GET['page'] ?? 'landing'; // Default ke landing page

// Proteksi Login Global
if (!isset($_SESSION['user_id']) && !in_array($page, ['landing', 'login', 'register'])) {
    header('Location: index.php?page=login');
    exit;
}

switch ($page) {
    case 'login':
        (new AuthController())->login();
        break;
    case 'register':
        (new AuthController())->register();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;

    // ======== MANAGER DASHBOARD ========
    case 'manager_dashboard':
        $controller = new ManagerController();
        $controller->index();
        break;

    // Kelola Lokasi
    case 'lokasi_proses_tambah':
        (new LokasiController())->store();
        break;
    case 'proses_edit_lokasi':
        (new LokasiController())->update();
        break;
    case 'lokasi_hapus':
        (new LokasiController())->delete();
        break;

    // Kelola Karyawan
    case 'karyawan_proses_tambah':
        (new KaryawanController())->store();
        break;
    case 'karyawan_proses_edit':
        (new KaryawanController())->update();
        break;
    case 'karyawan_hapus':
        (new KaryawanController())->delete();
        break;

    // Kelola Loyalitas
    case 'loyal_proses_tambah':
        (new LoyalController())->store();
        break;
    case 'loyal_proses_edit':
        (new LoyalController())->update();
        break;
    case 'loyal_hapus':
        (new LoyalController())->delete();
        break;

    // Kelola Voucher
    case 'voucher_proses_tambah':
        (new VoucherController())->store();
        break;
    case 'voucher_proses_edit':
        (new VoucherController())->update();
        break;
    case 'voucher_hapus':
        (new VoucherController())->delete();
        break;

    // ======== STAFF ADMIN DASHBOARD ========
    case 'Admin':
        $controller = new AdminController();
        $action = $_GET['action'] ?? '';
        
        if ($action === 'proses_tambah_mobil') {
            $controller->proses_tambah_mobil();
        } elseif ($action === 'proses_tambah_fasilitas') {
            $controller->proses_tambah_fasilitas();
        } elseif ($action === 'proses_upgrade_loyalitas') {
            // PERBAIKAN: Menjalankan logika upgrade melalui fungsi controller
            $controller->proses_upgrade_loyalitas();
        } else {
            include 'views/user/dashboard/staffadmin.php';
        }
        break;

    // ======== STAFF LAPANGAN DASHBOARD ========
    case 'home_lapangan':
        include 'views/user/dashboard/stafflapangan.php';
        break;

    // ======== PELANGGAN DASHBOARD ========
    case 'home':
    case 'home_pelanggan':
        include 'views/user/dashboard/pelanggan.php';
        break;

    case 'proses_sewa':
        (new SewaController())->store();
        break;

    default:
        include 'views/public/landing.php';
        break;
}
?>