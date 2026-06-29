<?php
require_once __DIR__ . '/../models/PelangganModel.php';

class ProfilController {
    private $pelangganModel;

    public function __construct() {
        $this->pelangganModel = new PelangganModel();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $user = $this->pelangganModel->getUserById($_SESSION['user_id']);
        
        if (!$user) {
            session_destroy();
            header('Location: index.php?page=login');
            exit;
        }

        $brand_name = "Rental Mobil";
        $current_page = 'profil';

        // Tampilkan view
        include __DIR__ . '/../views/profil.php';
    }
}