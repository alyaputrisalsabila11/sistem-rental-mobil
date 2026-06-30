<?php
require_once __DIR__ . '/../models/KaryawanModel.php';

class EmployeeController {
    private $karyawanModel;

    public function __construct() {
        // Proteksi: Pastikan hanya yang rolenya 'Manager' yang bisa akses controller ini
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
            $_SESSION['error'] = 'Akses ditolak! Fitur ini khusus Manager.';
            header('Location: index.php?page=login');
            exit;
        }
        $this->karyawanModel = new KaryawanModel();
    }

    // Menampilkan Form Tambah Karyawan
    public function create() {
        include __DIR__ . '/../views/employee_add.php';
    }

    // Memproses Penyimpanan Data Karyawan Baru
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_karyawan    = isset($_POST['nama_karyawan']) ? trim($_POST['nama_karyawan']) : '';
            $email            = isset($_POST['email']) ? trim($_POST['email']) : '';
            $lokasi_id        = isset($_POST['lokasi_id']) ? trim($_POST['lokasi_id']) : ''; // <-- TAMBAHKAN INI
            $no_telp          = isset($_POST['no_telp']) ? trim($_POST['no_telp']) : '';
            $password         = isset($_POST['password']) ? trim($_POST['password']) : '';
            $role             = isset($_POST['role']) ? trim($_POST['role']) : '';
            $status_karyawan  = isset($_POST['status_karyawan']) ? trim($_POST['status_karyawan']) : 'Aktif';

            // Validasi sederhana (lokasi_id dimasukkan ke dalam pengecekan kosong)
            if (empty($nama_karyawan) || empty($email) || empty($lokasi_id) || empty($password) || empty($role)) {
                $_SESSION['error'] = 'Semua field wajib diisi!';
                header('Location: index.php?page=karyawan_tambah');
                exit;
            }

            // Hash password secara aman
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database via model
            $success = $this->karyawanModel->createKaryawan([
                'nama_karyawan'   => $nama_karyawan,
                'email'           => $email,
                'lokasi_id'       => $lokasi_id, // <-- PASTIKAN DIKIRIM KE MODEL
                'no_telp'         => $no_telp,
                'password'        => $hashed_password,
                'role'            => $role,
                'status_karyawan' => $status_karyawan
            ]);

            if ($success) {
                $_SESSION['success'] = 'Akun karyawan baru berhasil dibuat!';
                header('Location: index.php?page=manager_dashboard');
            } else {
                $_SESSION['error'] = 'Gagal menambahkan karyawan. Email mungkin sudah terdaftar.';
                header('Location: index.php?page=karyawan_tambah');
            }
            exit;
        }
    }
}