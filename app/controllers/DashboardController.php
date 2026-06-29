<?php
// JANGAN panggil session_start() di sini
require_once __DIR__ . '/../models/MobilModel.php';
require_once __DIR__ . '/../models/BookingModel.php';

class DashboardController {
    private $mobilModel;
    private $bookingModel;

    public function __construct() {
        $this->mobilModel = new MobilModel();
        $this->bookingModel = new BookingModel();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $cars = $this->mobilModel->getAvailableCars();
        $page_title = 'Dashboard - Sistem Rental Mobil';

        
        include __DIR__ . '/../views/dashboard/index.php';
    }
}
?>
