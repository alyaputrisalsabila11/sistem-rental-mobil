<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload manual sederhana
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/controllers/transaksi/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
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
    case 'home':
        (new HomeController())->index();
        break;
    case 'gallery':
        (new GalleryController())->index();
        break;
    case 'profil':
        (new ProfilController())->index();
        break;
    case 'login':
        (new AuthController())->login();
        break;
    case 'register':
        (new AuthController())->register();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
    case 'sewa':
        include __DIR__ . '/../app/views/sewa.php';
        break;
    case 'histori':
        (new HistoriController())->index();
        break;
    case 'denda':
        (new DendaController())->index();
        break;
    case 'proses_booking':
        // PENTING: Kita panggil langsung filenya secara manual untuk bypass kegagalan autoload
        // Sesuaikan jalur titik dua (..) di bawah ini dengan lokasi asli file BookingController.php kamu
        
        // JIKA file BookingController.php kamu berada di folder root (sejajar dengan index.php):
        if (file_exists(__DIR__ . '/BookingController.php')) {
            require_once __DIR__ . '/BookingController.php';
        } 
        // JIKA file BookingController.php kamu berada di dalam subfolder transaksi:
        elseif (file_exists(__DIR__ . '/../app/controllers/transaksi/BookingController.php')) {
            require_once __DIR__ . '/../app/controllers/transaksi/BookingController.php';
        }

        // Setelah file dipastikan terpanggil, barulah kita buat objeknya
        if (class_exists('BookingController')) {
            $bookingCtrl = new BookingController();
            $bookingCtrl->simpan();
        } else {
            echo "Error: Class BookingController tetap tidak ditemukan. Periksa nama class di dalam file!";
        }
        break;
    case 'manager_dashboard':
        $controller = new ManagerController();
        $controller->index();
        break;
    case 'Admin':
    case 'home_admin':
        include __DIR__ . '/../app/views/dashboard/Admin.php';
        break;
    case 'karyawan_tambah':
        (new EmployeeController())->create();
        break;
    case 'karyawan_proses_tambah':
        (new EmployeeController())->store();
        break;
    case 'home_lapangan':
        include __DIR__ . '/../app/views/dashboard/Lapangan.php';
        break;
    case 'mobil_tambah':
        (new MobilController())->create();
        break;
    case 'mobil_proses_tambah':
        (new MobilController())->store();
        break;
    // Tambahkan case baru ini di dalam switch ($page) index.php kamu
    case 'konfirmasi_admin':
        (new AdminController())->konfirmasiPersetujuan();
        break;
    case 'proses_aksi_admin':
        (new AdminController())->prosesAksi();
        break;
    case 'lokasi_proses_tambah':
        (new LokasiController())->store();
        break;
    case 'lokasi_tambah':
        (new LokasiController())->create();
        break;

    default:
        include __DIR__ . '/../app/views/landing.php';
        break;
}