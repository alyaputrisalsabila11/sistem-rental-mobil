<?php
require_once __DIR__ . '/../models/PelangganModel.php';

class AkunController {
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = trim($_POST['nama']);
            $no_telp = trim($_POST['no_telp']);
            $alamat = trim($_POST['alamat']);

            if (empty($nama) || empty($no_telp) || empty($alamat)) {
                $_SESSION['error'] = 'Semua field harus diisi!';
            } else {
                if ($this->pelangganModel->updateUser($_SESSION['user_id'], $nama, $no_telp, $alamat)) {
                    $_SESSION['user_name'] = $nama;
                    $_SESSION['success'] = 'Profil berhasil diperbarui!';
                    $user = $this->pelangganModel->getUserById($_SESSION['user_id']);
                } else {
                    $_SESSION['error'] = 'Gagal memperbarui profil!';
                }
            }
        }

        $page_title = 'Akun - Sistem Rental Mobil';

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/user/akun.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }
}
?>
