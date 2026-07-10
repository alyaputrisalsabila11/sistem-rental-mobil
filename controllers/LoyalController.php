<?php
require_once __DIR__ . '/../init.php';

class LoyalController {
    private $loyalModel;

    public function __construct() {
        // Proteksi akses khusus Manager
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
            $_SESSION['error'] = 'Akses ditolak! Fitur ini khusus Manager.';
            header('Location: index.php?page=login');
            exit;
        }
        $this->loyalModel = new LoyalModel();
    }

    // Memproses penyimpanan data loyalitas baru (loyal_proses_tambah)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_level  = isset($_POST['nama_level']) ? trim($_POST['nama_level']) : '';
            $syarat      = isset($_POST['syarat']) ? trim($_POST['syarat']) : '';
            $poin        = isset($_POST['poin']) ? trim($_POST['poin']) : '';
            $keterangan  = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : '';
            $status      = isset($_POST['status']) ? trim($_POST['status']) : 'Aktif';

            // Validasi field utama yang wajib
            if (empty($nama_level)) {
                $_SESSION['error'] = 'Nama Level wajib diisi!';
                header('Location: index.php?page=manager_dashboard&action=buat_loyalitas'); // Sesuaikan action view Anda
                exit;
            }

            $success = $this->loyalModel->createLoyal([
                'nama_level' => $nama_level,
                'syarat'     => $syarat,
                'poin'       => $poin,
                'keterangan' => $keterangan,
                'status'     => $status
            ]);

            if ($success) {
                $_SESSION['success'] = 'Level loyalitas baru berhasil ditambahkan!';
                header('Location: index.php?page=manager_dashboard&action=data_loyalitas');
            } else {
                $_SESSION['error'] = 'Gagal menambahkan data loyalitas.';
                header('Location: index.php?page=manager_dashboard&action=buat_loyalitas');
            }
            exit;
        }
    }

    // Memproses update data loyalitas (loyal_proses_edit)
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_level   = $_POST['id_level'];
            $data = [
                'nama_level' => trim($_POST['nama_level']),
                'syarat'     => trim($_POST['syarat']),
                'poin'       => trim($_POST['poin']),
                'keterangan' => trim($_POST['keterangan']),
                'status'     => trim($_POST['status'])
            ];

            if ($this->loyalModel->updateLoyal($id_level, $data)) {
                $_SESSION['success'] = 'Data loyalitas berhasil diperbarui!';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui data loyalitas.';
            }
            header('Location: index.php?page=manager_dashboard&action=data_loyalitas');
            exit;
        }
    }

    // Memproses penghapusan data loyalitas (loyal_hapus)
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->loyalModel->deleteLoyal($id)) {
                $_SESSION['success'] = 'Level loyalitas berhasil dihapus!';
            } else {
                $_SESSION['error'] = 'Gagal menghapus data loyalitas. Kemungkinan data sedang digunakan pelanggan.';
            }
        }
        header('Location: index.php?page=manager_dashboard&action=data_loyalitas');
        exit;
    }
}