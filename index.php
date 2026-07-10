<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. PERBAIKAN: Autoloader otomatis mencari berkas Model & Controller berdasarkan nama class
spl_autoload_register(function ($class) {
    // Array folder tempat menyimpan berkas class Anda
    $directories = [
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/' // Untuk file root jika ada class di sana
    ];
    
    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load init.php secara manual jika di dalamnya berisi konfigurasi database / helper non-class
if (file_exists(__DIR__ . '/init.php')) {
    require_once __DIR__ . '/init.php';
}

$page = $_GET['page'] ?? 'landing';

// Proteksi Login
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

    // ======== MANAGER DASHBOARD ========
    case 'manager_dashboard':
        $controller = new ManagerController();
        $controller->index();
        break;

    // Kelola Lokasi
    case 'lokasi_proses_tambah':
        (new LokasiController())->store();
        break;
    case 'lokasi_proses_edit':
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

    // Kelola Loyalitas (Sesuai dengan Model & Controller sebelumnya)
    case 'loyal_proses_tambah':
        (new LoyalController())->store();
        break;
    case 'loyal_proses_edit':
        (new LoyalController())->update();
        break;
    case 'loyal_hapus':
        (new LoyalController())->delete();
        break;

    // Kelola Voucher (Diselaraskan & Ditambahkan Rute Update/Delete)
    case 'voucher_proses_tambah':
        (new VoucherController())->store();
        break;
    case 'voucher_proses_edit':
        (new VoucherController())->update();
        break;
    case 'voucher_hapus':
        (new VoucherController())->delete();
        break;

    case 'staffadmin_dashboard':
        include 'views/user/dashboard/staffadmin.php';
        break;

            case 'staffadmin_dashboard':
        include 'views/user/dashboard/staffadmin.php';
        break;

    case 'Admin':
        $controller = new AdminController();
        $action = $_GET['action'] ?? '';
        
        if ($action === 'proses_tambah_mobil') {
            $controller->proses_tambah_mobil();
        } elseif ($action === 'proses_tambah_fasilitas') {
            $controller->proses_tambah_fasilitas();
        } else {
            // Jika tidak ada action, arahkan ke dashboard agar tidak layar putih
            include 'views/user/dashboard/staffadmin.php';
        }
        break;

    case 'home_lapangan':
        include 'views/user/dashboard/stafflapangan.php';
        break;
    case 'home':
        include 'views/user/dashboard/pelanggan.php';
        break;
    case 'gallery':
        include 'views/user/dashboard/pelanggan.php'; // Digabung di file yang sama dengan parameter action
        break;
    case 'proses_sewa':
        (new SewaController())->store();
        break;

    default:
        include 'views/public/landing.php';
        break;
}