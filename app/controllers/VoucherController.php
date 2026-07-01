<?php
require_once __DIR__ . '/../models/VoucherModel.php';

class VoucherController {
    private $voucherModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->voucherModel = new VoucherModel();
    }

    // Memproses simpan voucher baru
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sesuaikan key array dengan parameter yang diminta oleh VoucherModel
            $data = [
                'id_level'            => $_POST['id_level'],
                'nama_voucher'        => trim($_POST['nama_voucher']),
                'harga_poin'          => $_POST['harga_poin'],
                'tipe_potongan'       => $_POST['tipe_potongan'], 
                'nilai_potongan'      => $_POST['nilai_potongan'],
                'kuota'               => $_POST['kuota'],
                'tgl_berlaku_mulai'   => $_POST['tgl_berlaku_mulai'],
                'tgl_berlaku_selesai' => $_POST['tgl_berlaku_selesai'],
                'status'              => $_POST['status'] ?? 'aktif' // Default 'aktif' jika kosong
            ];

            if ($this->voucherModel->createVoucher($data)) {
                $_SESSION['success'] = "Voucher berhasil dibuat!";
            } else {
                $_SESSION['error'] = "Gagal membuat voucher. Pastikan ID Level valid dan data terisi benar.";
            }
            
            // Arahkan kembali ke halaman manajemen voucher
            header('Location: index.php?page=manager_dashboard&action=buat_voucher');
            exit;
        }
    }
}