<?php
require_once __DIR__ . '/../init.php';

class LoyalModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Fungsi untuk menambah level loyalitas baru
    public function createLoyal($data) {
        try {
            $sql = "INSERT INTO loyalitas (nama_level, syarat, poin, keterangan, status) 
                    VALUES (:nama_level, :syarat, :poin, :keterangan, :status)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nama_level' => $data['nama_level'],
                ':syarat'     => !empty($data['syarat']) ? $data['syarat'] : 0,
                ':poin'       => !empty($data['poin']) ? $data['poin'] : 0.0,
                ':keterangan' => $data['keterangan'],
                ':status'     => !empty($data['status']) ? $data['status'] : 'Aktif'
            ]);
        } catch (Exception $e) {
            error_log("Error di createLoyal: " . $e->getMessage());
            return false;
        }
    }

    // Mengambil semua data level loyalitas
    public function getAllLoyal() {
        try {
            $sql = "SELECT * FROM loyalitas ORDER BY id_level DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getAllLoyal: " . $e->getMessage());
            return [];
        }
    }

    // Mengambil satu data loyalitas berdasarkan ID (untuk form edit)
    public function getLoyalById($id) {
        try {
            $sql = "SELECT * FROM loyalitas WHERE id_level = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getLoyalById: " . $e->getMessage());
            return false;
        }
    }

    // Memperbarui data level loyalitas
    public function updateLoyal($id, $data) {
        try {
            $sql = "UPDATE loyalitas 
                    SET nama_level = :nama_level, syarat = :syarat, poin = :poin, keterangan = :keterangan, status = :status 
                    WHERE id_level = :id_level";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nama_level' => $data['nama_level'],
                ':syarat'     => $data['syarat'],
                ':poin'       => $data['poin'],
                ':keterangan' => $data['keterangan'],
                ':status'     => $data['status'],
                ':id_level'   => $id
            ]);
        } catch (Exception $e) {
            error_log("Error di updateLoyal: " . $e->getMessage());
            return false;
        }
    }

    // Menghapus data level loyalitas
    public function deleteLoyal($id) {
        try {
            $sql = "DELETE FROM loyalitas WHERE id_level = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error di deleteLoyal: " . $e->getMessage());
            return false;
        }
    }
}