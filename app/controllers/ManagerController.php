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
        require_once __DIR__ . '/../models/KaryawanModel.php';
        require_once __DIR__ . '/../models/LokasiModel.php';
        // 1. Ambil data karyawan dari model
        $karyawanModel = new KaryawanModel();
        $daftarKaryawan = $karyawanModel->getAllKaryawan();
        $lokasiModel = new LokasiModel();
        $daftarLokasi = $lokasiModel->getAllLokasi();

        // 2. Ambil data laporan transaksi (Sementara kita pakai array dulu, atau jika kamu sudah punya TransaksiModel bisa panggil di sini)
        $laporanData = [
            ['id' => 'TRX001', 'pelanggan' => 'Reyza', 'mobil' => 'Avanza Veloz', 'tgl_sewa' => '2026-06-01', 'total' => 350000, 'status' => 'Selesai'],
            ['id' => 'TRX002', 'pelanggan' => 'Aprilia', 'mobil' => 'Honda Civic', 'tgl_sewa' => '2026-06-03', 'total' => 700000, 'status' => 'Selesai'],
        ];

        // Lempar data ke view dashboard manager
        include __DIR__ . '/../views/dashboard/Manager.php';
    }
}