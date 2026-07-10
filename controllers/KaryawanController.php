<?php
require_once __DIR__ . '/../init.php';

class KaryawanController {
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

    // Memproses update data dari form edit
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_karyawan = $_POST['id_karyawan'];
            
            // DISESUAIKAN: Menangkap data alamat, no_ktp, dan status_supir dari form edit
            $data = [
                'nama_karyawan'   => trim($_POST['nama_karyawan']),
                'email'           => trim($_POST['email']),
                'no_telp'         => trim($_POST['no_telp']),
                'role'            => trim($_POST['role']),
                'status_karyawan' => trim($_POST['status_karyawan']),
                'id_lokasi'       => trim($_POST['id_lokasi']),
                'alamat'          => isset($_POST['alamat']) ? trim($_POST['alamat']) : '',
                'no_ktp'          => isset($_POST['no_ktp']) ? trim($_POST['no_ktp']) : '',
                'status_supir'    => isset($_POST['status_supir']) ? trim($_POST['status_supir']) : '',
                'password'        => !empty($_POST['password']) ? trim($_POST['password']) : null
            ];

            if ($this->karyawanModel->updateKaryawan($id_karyawan, $data)) {
                $_SESSION['success'] = 'Data karyawan berhasil diperbarui!';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui data karyawan.';
            }
            header('Location: index.php?page=manager_dashboard&action=buat_akun');
            exit;
        }
    }

    // Memproses aksi hapus karyawan
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->karyawanModel->deleteKaryawan($id)) {
                $_SESSION['success'] = 'Akun karyawan berhasil dihapus!';
            } else {
                $_SESSION['error'] = 'Gagal menghapus karyawan. Data mungkin terikat entitas lain.';
            }
        }
        header('Location: index.php?page=manager_dashboard&action=buat_akun');
        exit;
    }

    // Memproses Penyimpanan Data Karyawan Baru
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_karyawan    = isset($_POST['nama_karyawan']) ? trim($_POST['nama_karyawan']) : '';
            $email            = isset($_POST['email']) ? trim($_POST['email']) : '';
            $id_lokasi        = isset($_POST['id_lokasi']) ? trim($_POST['id_lokasi']) : ''; 
            $no_telp          = isset($_POST['no_telp']) ? trim($_POST['no_telp']) : '';
            $password         = isset($_POST['password']) ? trim($_POST['password']) : '';
            $role             = isset($_POST['role']) ? trim($_POST['role']) : '';
            $status_karyawan  = isset($_POST['status_karyawan']) ? trim($_POST['status_karyawan']) : 'Aktif';
            
            // BARU: Menangkap input baru dari form tambah data
            $alamat           = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
            $no_ktp           = isset($_POST['no_ktp']) ? trim($_POST['no_ktp']) : '';
            $status_supir     = isset($_POST['status_supir']) ? trim($_POST['status_supir']) : '';

            // Validasi field utama yang wajib diisi
            if (empty($nama_karyawan) || empty($email) || empty($password) || empty($role)) {
                $_SESSION['error'] = 'Nama, Email, Password, dan Role wajib diisi!';
                header('Location: index.php?page=manager_dashboard&action=buat_akun');
                exit;
            }

            // Hash password secara aman
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database via model dengan menyertakan data baru
            $success = $this->karyawanModel->createKaryawan([
                'nama_karyawan'   => $nama_karyawan,
                'email'           => $email,
                'id_lokasi'       => $id_lokasi,
                'no_telp'         => $no_telp,
                'password'        => $hashed_password,
                'role'            => $role,
                'status_karyawan' => $status_karyawan,
                'alamat'          => $alamat,
                'no_ktp'          => $no_ktp,
                'status_supir'    => $status_supir
            ]);

            if ($success) {
                $_SESSION['success'] = 'Akun karyawan baru berhasil dibuat!';
                header('Location: index.php?page=manager_dashboard&action=data_karyawan');
            } else {
                $_SESSION['error'] = 'Gagal menambahkan karyawan. Email mungkin sudah terdaftar.';
                header('Location: index.php?page=manager_dashboard&action=buat_akun');
            }
            exit;
        }
    }
}