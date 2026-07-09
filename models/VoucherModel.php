<?php
require_once __DIR__ . '/../init.php';

class VoucherModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Fungsi untuk menambah voucher baru (Disesuaikan dengan Schema Baru)
    public function createVoucher($data) {
        try {
            // Query disesuaikan dengan gambar DB baru: kuota & tipe_potongan dihapus
            $sql = "INSERT INTO voucher (id_level, nama_voucher, harga_poin, diskon_potongan, nilai_potongan, tgl_berlaku_mulai, tgl_berlaku_selesai, status) 
                    VALUES (:id_level, :nama_voucher, :harga_poin, :diskon_potongan, :nilai_potongan, :tgl_berlaku_mulai, :tgl_berlaku_selesai, :status)";
            
            $stmt = $this->db->prepare($sql);

            // =========================================================================
            // LOGIKA OTOMATIS DI MODEL:
            // Ambil input angka diskon (misal 10), lalu hitung desimalnya untuk nilai_potongan
            // =========================================================================
            $diskonInt = isset($data['diskon_potongan']) ? intval($data['diskon_potongan']) : 0;
            $nilaiDecimal = $diskonInt / 100; // Logika pembagian 100 masuk di sini

            // Harmonisasi status ENUM agar otomatis lowercase mengikuti aturan DB ('aktif' / 'inaktif')
            $statusInput = strtolower($data['status'] ?? 'aktif');
            $statusFinal = ($statusInput === 'aktif' || $statusInput === 'inaktif') ? $statusInput : 'aktif';

            return $stmt->execute([
                ':id_level'            => $data['id_level'], 
                ':nama_voucher'        => $data['nama_voucher'],
                ':harga_poin'          => $data['harga_poin'],
                ':diskon_potongan'     => $diskonInt,     // Masuk ke kolom int(11) -> 10
                ':nilai_potongan'      => $nilaiDecimal,   // Masuk ke kolom decimal(10,2) -> 0.10
                ':tgl_berlaku_mulai'   => $data['tgl_berlaku_mulai'],
                ':tgl_berlaku_selesai' => $data['tgl_berlaku_selesai'],
                ':status'              => $statusFinal
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