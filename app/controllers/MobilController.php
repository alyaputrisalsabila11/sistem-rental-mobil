<?php
require_once __DIR__ . '/../models/MobilModel.php';

class MobilController {
    private $MobilModel;

    public function __construct() {
        // Proteksi: Pastikan hanya yang rolenya 'Admin' yang bisa akses controller ini
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff Admin') {
            $_SESSION['error'] = 'Akses ditolak! Fitur ini khusus Admin.';
            header('Location: index.php?page=login');
            exit;
        }
        $this->MobilModel = new MobilModel();
    }

    public function create() {
        include __DIR__ . '/../views/tambah_mobil.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_kategori'  => $_POST['nama_kategori'] ?? '',
                'merk_mobil'     => $_POST['merk_mobil'] ?? '',
                'plat_nomor'     => $_POST['plat_nomor'] ?? '',
                'tahun'          => $_POST['tahun'] ?? '',
                'harga_dinamis'  => $_POST['harga_dinamis'] ?? '',
                'tipe'           => $_POST['tipe'] ?? '',
                'warna'          => $_POST['warna'] ?? '',
                'cc'             => $_POST['cc'] ?? ''
            ];

            // Validasi sederhana
            if (empty($data['nama_kategori']) || empty($data['merk_mobil']) || empty($data['plat_nomor']) || empty($data['tahun']) || empty($data['harga_dinamis'])) {
                $_SESSION['error'] = 'Semua field wajib diisi!';
                header('Location: index.php?page=mobil_tambah');
                exit;
            }

            $success = $this->MobilModel->createMobil($data);

            if ($success) {
                $_SESSION['success'] = 'Mobil baru berhasil ditambahkan!';
                header('Location: index.php?page=admin_dashboard');
            } else {
                $_SESSION['error'] = 'Gagal menambahkan mobil. Pastikan data sudah benar.';
                header('Location: index.php?page=mobil_tambah');
            }
            exit;
        }
    }
}