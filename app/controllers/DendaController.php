<?php
class DendaController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $page_title = 'Halaman Denda - Sistem Rental Mobil';

        include __DIR__ . '/../views/denda.php';
    }
}
?>
