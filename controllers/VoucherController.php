<?php
require_once __DIR__ . '/../init.php';

class VoucherController {
    private $voucherModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->voucherModel = new VoucherModel();
    }

    // Memproses simpan voucher baru
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // PERBAIKAN: Menyelaraskan data dengan Form HTML baru dan VoucherModel
            $data = [
                'id_level'            => $_POST['id_level'],
                'nama_voucher'        => trim($_POST['nama_voucher']),
                'harga_poin'          => $_POST['harga_poin'],
                
                // TANGKAP INPUT DISKON DI SINI 👇
                'diskon_potongan'     => $_POST['diskon_potongan'], 
                
                'tgl_berlaku_mulai'   => $_POST['tgl_berlaku_mulai'],
                'tgl_berlaku_selesai' => $_POST['tgl_berlaku_selesai'],
                'status'              => $_POST['status'] ?? 'aktif' // Default 'aktif' jika kosong
            ];

            // Variabel lama seperti 'tipe_potongan', 'nilai_potongan', dan 'kuota' 
            // sudah dihapus dari array karena tidak ada di form & dihitung otomatis oleh Model.

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