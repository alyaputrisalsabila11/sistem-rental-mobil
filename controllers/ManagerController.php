<?php
class ManagerController {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // PERBAIKAN: Ubah 'manager' menjadi 'Manager' (M Kapital) sesuai ENUM database
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
            $_SESSION['error'] = 'Akses ditolak! Halaman ini khusus Manager.';
            header('Location: index.php?page=login');
            exit;
        }
    }

    public function index() {
        require_once __DIR__ . '/../init.php';
        
        $karyawanModel = new KaryawanModel();
        $daftarKaryawan = $karyawanModel->getAllKaryawan();
        
        $lokasiModel = new LokasiModel();
        $daftarLokasi = $lokasiModel->getAllLokasi();

        // Letakkan ini tepat sebelum baris kode "include/require view Manager.php"
        $karyawanEdit = null;
        $lokasiEdit = null; // Baris ini wajib ada agar saat tidak edit, variabelnya tetap terdefinisi (isinya kosong)
        
        // 1. TAMBAHAN: Definisikan variabel loyalEdit agar tidak error (Undefinded Variable) saat di halaman lain
        $loyalEdit = null;

        // 2. TENTUKAN VARIABELNYA DULU DI SINI
        $action = isset($_GET['action']) ? $_GET['action'] : 'home';

        if ($action === 'edit_karyawan' && isset($_GET['id'])) {
            $karyawanEdit = $karyawanModel->getKaryawanById($_GET['id']);
        }
        
        if ($action === 'edit_cabang' && isset($_GET['id'])) {
            $lokasiEdit = $lokasiModel->getLokasiById($_GET['id']);
        }

        // 3. TAMBAHAN: Logika untuk mengambil data lama level loyalitas yang mau diedit
        if ($action === 'edit_loyal' && isset($_GET['id'])) {
            $loyalModel = new LoyalModel();
            $loyalEdit = $loyalModel->getLoyalById($_GET['id']);
        }

        // 4. BARU PANGGIL VIEW-NYA DI PALING BAWAH
        // (Sesuaikan nama file/path view Manager Anda jika berbeda)
        include __DIR__ . '/../views/user/dashboard/manager.php';
    }
}