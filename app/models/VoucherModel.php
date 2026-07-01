<?php
require_once __DIR__ . '/../../config/database.php';

class VoucherModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Fungsi untuk menambah voucher baru
    public function createVoucher($data) {
        try {
            // PASTIKAN: id_level ditambahkan ke dalam query agar terhubung dengan tabel level loyalitas
            $sql = "INSERT INTO voucher (id_level, nama_voucher, harga_poin, tipe_potongan, nilai_potongan, kuota, tgl_berlaku_mulai, tgl_berlaku_selesai, status) 
                    VALUES (:id_level, :nama_voucher, :harga_poin, :tipe_potongan, :nilai_potongan, :kuota, :tgl_berlaku_mulai, :tgl_berlaku_selesai, :status)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_level'            => $data['id_level'], // Menambahkan input id_level
                ':nama_voucher'        => $data['nama_voucher'],
                ':harga_poin'          => $data['harga_poin'],
                ':tipe_potongan'       => $data['tipe_potongan'], // Pastikan di database sudah pakai ejaan 'Persentase'
                ':nilai_potongan'      => $data['nilai_potongan'],
                ':kuota'               => $data['kuota'],
                ':tgl_berlaku_mulai'   => $data['tgl_berlaku_mulai'],
                ':tgl_berlaku_selesai' => $data['tgl_berlaku_selesai'],
                ':status'              => $data['status']
            ]);
        } catch (Exception $e) {
            error_log("Error di createVoucher: " . $e->getMessage());
            return false;
        }
    }

    // Fungsi untuk mengambil semua data voucher
    public function getAllVoucher() {
        try {
            $sql = "SELECT * FROM voucher ORDER BY id_voucher DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getAllVoucher: " . $e->getMessage());
            return [];
        }
    }
}