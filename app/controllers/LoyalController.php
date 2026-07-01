<?php
require_once __DIR__ . '/../models/LoyalModel.php';

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
}