<?php
require_once __DIR__ . '/../models/PelangganModel.php';
require_once __DIR__ . '/../models/KaryawanModel.php';

class AuthController {
    private $pelangganModel;
    private $KaryawanModel;

    public function __construct() {
        $this->pelangganModel = new PelangganModel();
        $this->KaryawanModel = new KaryawanModel();
    }

    // Halaman Login
    public function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Validasi input kosong
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Username dan password harus diisi!';
            include __DIR__ . '/../views/auth/login.php';
            return;
        }

        // 1. CARI DI TABEL PELANGGAN TERLEBIH DAHULU
        $user = $this->pelangganModel->getUserByUsername($username);
        if (!$user) {
            $user = $this->pelangganModel->getUserByEmail($username);
        }

        if ($user && password_verify($password, $user['password'])) {
            // Jika ketemu di tabel pelanggan, otomatis dia adalah Pelanggan
            unset($_SESSION['error']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama_lengkap'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = 'pelanggan'; // Set role manual untuk pelanggan

            session_write_close();
            header('Location: index.php?page=home');
            exit;
        }

        // 2. JIKA TIDAK KETEMU DI PELANGGAN, CARI DI TABEL KARYAWAN
       $karyawan = $this->KaryawanModel->getByUsername($username); 
        
        if ($karyawan) {
            // PERBAIKAN: Mendukung password_verify ATAU MD5 (biar gampang insert manual lewat phpMyAdmin)
            if (password_verify($password, $karyawan['password']) || md5($password) === $karyawan['password']) {
                unset($_SESSION['error']);
                $_SESSION['user_id'] = $karyawan['id_karyawan'];
                $_SESSION['user_name'] = $karyawan['nama_karyawan']; // Pastikan mengambil nama_karyawan
                
                // Ambil role OTOMATIS dari kolom 'role' di tabel karyawan
                $_SESSION['role'] = $karyawan['role']; 

                session_write_close();

                // Cek jika rolenya adalah Manager (Sesuai ENUM database dengan M kapital)
                if ($_SESSION['role'] === 'Manager') {
                    header('Location: index.php?page=manager_dashboard');
                } elseif ($_SESSION['role'] === 'Staff Admin') {
                    header('Location: index.php?page=Admin');
                } elseif ($_SESSION['role'] === 'Staff Lapangan') {
                    header('Location: index.php?page=home_lapangan');
                } 
                exit;
            }
        }

        // Jika di kedua tabel tidak ditemukan atau password salah
        $_SESSION['error'] = 'Username/Email atau password salah!';
        include __DIR__ . '/../views/auth/login.php';
        return;
    }

    include __DIR__ . '/../views/auth/login.php';
}
    // Halaman Register
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_lengkap = isset($_POST['nama_lengkap']) ? trim($_POST['nama_lengkap']) : '';
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
            $no_telp = isset($_POST['no_telp']) ? trim($_POST['no_telp']) : '';
            $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
            $no_ktp = isset($_POST['no_ktp']) ? trim($_POST['no_ktp']) : null;

            // Validasi sederhana
            if (empty($nama_lengkap) || empty($username) || empty($email) || empty($password)) {
                $_SESSION['error'] = 'Field utama harus diisi!';
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            // TAMBAHAN KEAMANAN: Jangan biarkan pelanggan mendaftar pakai kata @manager
            if (str_contains($username, '@manager') || str_contains($email, '@manager')) {
                $_SESSION['error'] = 'Gunakan username atau email standar tanpa tanda khusus!';
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = 'Password tidak cocok!';
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            if ($this->pelangganModel->emailExists($email) || $this->pelangganModel->usernameExists($username)) {
                $_SESSION['error'] = 'Username atau Email sudah terdaftar!';
                include __DIR__ . '/../views/auth/register.php';
                return;
            }

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            if ($this->pelangganModel->createUser($nama_lengkap, $username, $email, $hashed_password, $no_telp, $alamat, $no_ktp)) {
                unset($_SESSION['error']);
                $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
                header('Location: index.php?page=login');
                exit;
            } else {
                $_SESSION['error'] = 'Registrasi gagal!';
                include __DIR__ . '/../views/auth/register.php';
                return;
            }
        }
        include __DIR__ . '/../views/auth/register.php';
    }

    // Logout
    public function logout() {
        session_destroy();
        header('Location: index.php?page=landing');
        exit;
    }
}
?>