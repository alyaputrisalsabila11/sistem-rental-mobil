<?php
require_once __DIR__ . '/../init.php';

class VoucherController {
    private $voucherModel;

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
        $this->voucherModel = new VoucherModel();
    }

    // Memproses penyimpanan voucher baru (voucher_proses_tambah)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_level       = isset($_POST['id_level']) ? trim($_POST['id_level']) : '';
            $kode_voucher   = isset($_POST['kode_voucher']) ? trim($_POST['kode_voucher']) : '';
            $nama_voucher   = isset($_POST['nama_voucher']) ? trim($_POST['nama_voucher']) : '';
            $diskon_persen  = isset($_POST['diskon_persen']) ? trim($_POST['diskon_persen']) : '';
            $kuota          = isset($_POST['kuota']) ? trim($_POST['kuota']) : '';
            $tgl_berlaku    = isset($_POST['tgl_berlaku']) ? trim($_POST['tgl_berlaku']) : '';
            $status         = isset($_POST['status']) ? trim($_POST['status']) : 'Aktif';

            // Validasi data wajib
            if (empty($kode_voucher) || empty($nama_voucher) || empty($diskon_persen) || empty($kuota) || empty($tgl_berlaku)) {
                $_SESSION['error'] = 'Semua field wajib diisi kecuali batasan level!';
                header('Location: index.php?page=manager_dashboard&action=buat_voucher');
                exit;
            }

            $success = $this->voucherModel->createVoucher([
                'id_level'       => $id_level,
                'kode_voucher'   => $kode_voucher,
                'nama_voucher'   => $nama_voucher,
                'diskon_persen'  => $diskon_persen,
                'kuota'          => $kuota,
                'tgl_berlaku'    => $tgl_berlaku,
                'status'         => $status
            ]);

            if ($success) {
                $_SESSION['success'] = 'Voucher baru berhasil diterbitkan!';
                header('Location: index.php?page=manager_dashboard&action=kelola_voucher');
            } else {
                $_SESSION['error'] = 'Gagal menambahkan voucher. Kode voucher mungkin sudah ada.';
                header('Location: index.php?page=manager_dashboard&action=buat_voucher');
            }
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_voucher = $_POST['id_voucher'];
            $data = [
                'id_level'       => isset($_POST['id_level']) ? trim($_POST['id_level']) : '',
                'kode_voucher'   => isset($_POST['kode_voucher']) ? trim($_POST['kode_voucher']) : '',
                'nama_voucher'   => isset($_POST['nama_voucher']) ? trim($_POST['nama_voucher']) : '',
                'diskon_persen'  => isset($_POST['diskon_persen']) ? trim($_POST['diskon_persen']) : '',
                'kuota'          => isset($_POST['kuota']) ? trim($_POST['kuota']) : '',
                'tgl_berlaku'    => isset($_POST['tgl_berlaku']) ? trim($_POST['tgl_berlaku']) : '',
                'status'         => isset($_POST['status']) ? trim($_POST['status']) : 'Aktif'
            ];

            // Validasi data wajib
            if (empty($data['kode_voucher']) || empty($data['nama_voucher']) || empty($data['diskon_persen']) || empty($data['kuota']) || empty($data['tgl_berlaku'])) {
                $_SESSION['error'] = 'Semua field wajib diisi kecuali batasan level!';
                header('Location: index.php?page=manager_dashboard&action=edit_voucher&id=' . $id_voucher);
                exit;
            }

            $success = $this->voucherModel->updateVoucher($id_voucher, $data);

            if ($success) {
                $_SESSION['success'] = 'Data voucher berhasil diperbarui!';
                header('Location: index.php?page=manager_dashboard&action=kelola_voucher');
            } else {
                $_SESSION['error'] = 'Gagal memperbarui data voucher.';
                header('Location: index.php?page=manager_dashboard&action=edit_voucher&id=' . $id_voucher);
            }
            exit;
        }
    }

    public function delete(){
        $id_voucher = isset($_GET['id']) ? trim($_GET['id']) : '';
        if ($id_voucher) {
            $success = $this->voucherModel->deleteVoucher($id_voucher);
            if ($success) {
                $_SESSION['success'] = 'Voucher berhasil dihapus!';
            } else {
                $_SESSION['error'] = 'Gagal menghapus voucher.';
            }
        } else {
            $_SESSION['error'] = 'ID voucher tidak valid.';
        }
        header('Location: index.php?page=manager_dashboard&action=kelola_voucher');
        exit;
    }

}