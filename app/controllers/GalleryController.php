<?php
require_once __DIR__ . '/../models/MobilModel.php';

class GalleryController {
    private $mobilModel;

    public function __construct() {
        $this->mobilModel = new MobilModel();
    }

    public function index() {
        // Cek login
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }
        
        // Cek jika ada filter nama kategori berupa teks di URL (Contoh: index.php?page=gallery&kategori=SUV)
        $nama_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : null;
        
        if ($nama_kategori) {
            // Memanggil fungsi model dengan parameter teks nama kategori
            $cars = $this->mobilModel->getMobilByKategori($nama_kategori);
        } else {
            $cars = $this->mobilModel->getAvailableCars();
        }

        $brand_name = "Rental Mobil";
        $current_page = 'gallery';

        // Load View Tampilan Gallery Pelanggan
        include __DIR__ . '/../views/gallery.php';
    }
}