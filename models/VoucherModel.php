<?php
require_once __DIR__ . '/../init.php';

class VoucherModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Fungsi menambah voucher baru
    public function createVoucher($data) {
        try {
            $sql = "INSERT INTO voucher (id_level, kode_voucher, nama_voucher, diskon_persen, kuota, tgl_berlaku, status) 
                    VALUES (:id_level, :kode_voucher, :nama_voucher, :diskon_persen, :kuota, :tgl_berlaku, :status)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_level'       => !empty($data['id_level']) ? $data['id_level'] : null,
                ':kode_voucher'   => $data['kode_voucher'],
                ':nama_voucher'   => $data['nama_voucher'],
                ':diskon_persen'  => $data['diskon_persen'],
                ':kuota'          => $data['kuota'],
                ':tgl_berlaku'    => $data['tgl_berlaku'],
                ':status'         => !empty($data['status']) ? $data['status'] : 'Aktif'
            ]);
        } catch (Exception $e) {
            error_log("Error di createVoucher: " . $e->getMessage());
            return false;
        }
    }

    // Mengambil semua data voucher (di-JOIN dengan tabel loyalitas/loyalitas untuk tahu khusus level apa)
    public function getAllVoucher() {
        try {
            $sql = "SELECT v.*, l.nama_level 
                    FROM voucher v
                    LEFT JOIN loyalitas l ON v.id_level = l.id_level
                    ORDER BY v.id_voucher DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getAllVoucher: " . $e->getMessage());
            return [];
        }
    }

    // Mengambil satu data voucher berdasarkan ID
    public function getVoucherById($id) {
        try {
            $sql = "SELECT * FROM voucher WHERE id_voucher = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getVoucherById: " . $e->getMessage());
            return false;
        }
    }

    // Memperbarui data voucher
    public function updateVoucher($id, $data) {
        try {
            $sql = "UPDATE voucher
                    SET id_level = :id_level, kode_voucher = :kode_voucher, nama_voucher = :nama_voucher, 
                        diskon_persen = :diskon_persen, kuota = :kuota, tgl_berlaku = :tgl_berlaku, status = :status 
                    WHERE id_voucher = :id_voucher";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_level'      => !empty($data['id_level']) ? $data['id_level'] : null,
                ':kode_voucher'  => $data['kode_voucher'],
                ':nama_voucher'  => $data['nama_voucher'],
                ':diskon_persen' => $data['diskon_persen'],
                ':kuota'         => $data['kuota'],
                ':tgl_berlaku'   => $data['tgl_berlaku'],
                ':status'        => $data['status'],
                ':id_voucher'    => $id
            ]);
        } catch (Exception $e) {
            error_log("Error di updateVoucher: " . $e->getMessage());
            return false;
        }
    }

    // Menghapus data voucher
    public function deleteVoucher($id) {
        try {
            $sql = "DELETE FROM voucher WHERE id_voucher = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error di deleteVoucher: " . $e->getMessage());
            return false;
        }
    }
}