<?php
require_once __DIR__ . '/../init.php';

class LoyalController {
    private $loyalModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->loyalModel = new LoyalModel();
    }

    // Memproses simpan loyalitas baru
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_level' => trim($_POST['nama_level']),
                'syarat'     => $_POST['syarat'],
                'poin'       => $_POST['poin'],
                'keterangan' => trim($_POST['keterangan']),
                // Mengonversi checkbox menjadi nilai ENUM database
                // Gunakan ejaan sesuai di database (Nonakftif)
                'status'     => isset($_POST['aktif']) ? 'Aktif' : 'Nonakftif' 
            ];

            if ($this->loyalModel->createLoyal($data)) {
                $_SESSION['success'] = "Level loyalitas berhasil dibuat!";
            } else {
                $_SESSION['error'] = "Gagal membuat level loyalitas.";
            }
            
            header('Location: index.php?page=manager_dashboard&action=buat_loyal');
            exit;
        }
    }

    // Memproses update data loyalitas
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_level = $_POST['id_loyal']; // Diambil dari input hidden di form
            
            $data = [
                'nama_level' => trim($_POST['nama_level']),
                'syarat'     => $_POST['syarat'],
                'poin'       => $_POST['poin'],
                'keterangan' => trim($_POST['keterangan']),
                'status'     => $_POST['status'] // Langsung mengambil nilai dari select option
            ];

            if ($this->loyalModel->updateLoyal($id_level, $data)) {
                $_SESSION['success'] = "Level loyalitas berhasil diperbarui!";
            } else {
                $_SESSION['error'] = "Gagal memperbarui level loyalitas.";
            }
            
            // Sesuaikan redirect halaman utama loyalitas kamu
            header('Location: index.php?page=manager_dashboard&action=loyalitas');
            exit;
        }
    }

    // Memproses hapus data loyalitas
    public function delete() {
        // Mengikuti pola karyawan_hapus / lokasi_hapus menggunakan $_GET
        $id_level = $_GET['id'] ?? null;

        if ($id_level) {
            if ($this->loyalModel->deleteLoyal($id_level)) {
                $_SESSION['success'] = "Level loyalitas berhasil dihapus!";
            } else {
                $_SESSION['error'] = "Gagal menghapus level loyalitas.";
            }
        }
        
        header('Location: index.php?page=manager_dashboard&action=loyalitas');
        exit;
    }
}