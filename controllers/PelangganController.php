<?php
require_once __DIR__ . '/../init.php';

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
    }

public function proses_redeem_voucher() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_voucher = $_POST['id_voucher'] ?? null;
        $user_id = $_SESSION['user_id'];

        if ($id_voucher) {
            $result = $this->pelangganModel->redeemVoucher($user_id, $id_voucher);
            
            if ($result['status'] === true) {
                $_SESSION['success'] = $result['message'];
                // Jika SUKSES, lempar ke halaman list semua voucher
                header('Location: index.php?page=home&action=voucher');
                exit;
            } else {
                $_SESSION['error'] = $result['message'];
                // Jika GAGAL, balikkan ke halaman konfirmasi tadi agar user tahu error-nya apa!
                header('Location: index.php?page=home&action=redeem_voucher&id=' . $id_voucher);
                exit;
            }
        } else {
            $_SESSION['error'] = 'ID Voucher tidak ditemukan!';
            header('Location: index.php?page=home&action=voucher');
            exit;
        }
    }
}
}
?>