<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/init.php'
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

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
//manager
    case 'manager_dashboard':
        $controller = new ManagerController();
        $controller->index();
        break;
    case 'lokasi_proses_tambah':
        (new LokasiController())->store();
        break;
    case 'lokasi_tambah':
        (new LokasiController())->create();
        break;
    case 'lokasi_proses_edit':
        (new LokasiController())->update();
        break;
    case 'lokasi_hapus':
        (new LokasiController())->delete();
        break;
    case 'karyawan_tambah':
        (new KaryawanController())->store();
        break;
    case 'karyawan_proses_tambah':
        (new KaryawanController())->store();
        break;
    case 'karyawan_proses_edit':
        (new KaryawanController())->update();
        break;
    case 'karyawan_hapus':
        (new KaryawanController())->delete();
        break;
    case 'loyal_proses_tambah':
        (new LoyalController())->store();
        break;
    case 'loyal_proses_edit':
        (new LoyalController())->update();
        break;
    case 'loyal_hapus':
        (new LoyalController())->delete();
        break;
    case 'voucher_proses_tambah':
        (new VoucherController())->store();
        break;
    // Tambahkan case ini agar redirect dari controller tidak layar putih
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