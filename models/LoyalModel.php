<?php
require_once __DIR__ . '/../init.php';

class LoyalModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Fungsi untuk menambah loyalitas baru
    public function createLoyal($data) {
        try {
            // Kolom 'aktif' diganti menjadi 'status' sesuai tabel baru
            $sql = "INSERT INTO loyalitas (nama_level, syarat, poin, keterangan, status)
                    VALUES (:nama_level, :syarat, :poin, :keterangan, :status)";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':nama_level' => $data['nama_level'],
                ':syarat'     => $data['syarat'],
                ':poin'       => $data['poin'],
                ':keterangan' => $data['keterangan'],
                ':status'     => $data['status'] // Binding ke parameter status
            ]);
        } catch (Exception $e) {
            error_log("Error di createLoyal: " . $e->getMessage());
            return false;
        }
    }

    public function getAllLoyal() {
        try {
            // ORDER BY diubah menyesuaikan Primary Key yang baru (id_level)
            $sql = "SELECT * FROM loyalitas ORDER BY id_level DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getAllLoyal: " . $e->getMessage());
            return [];
        }
    }

    public function getLoyalById($id_level) {
        try {
            $sql = "SELECT * FROM loyalitas WHERE id_level = :id_level";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_level' => $id_level]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getLoyalById: " . $e->getMessage());
            return false;
        }
    }

    public function updateLoyal($id_level, $data) {
        try {
            $sql = "UPDATE loyalitas 
                    SET nama_level = :nama_level, syarat = :syarat, poin = :poin, keterangan = :keterangan, status = :status 
                    WHERE id_level = :id_level";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':id_level'   => $id_level,
                ':nama_level' => $data['nama_level'],
                ':syarat'     => $data['syarat'],
                ':poin'       => $data['poin'],
                ':keterangan' => $data['keterangan'],
                ':status'     => $data['status']
            ]);
        } catch (Exception $e) {
            error_log("Error di updateLoyal: " . $e->getMessage());
            return false;
        }
    }

    public function deleteLoyal($id_level) {
        try {
            $sql = "DELETE FROM loyalitas WHERE id_level = :id_level";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id_level' => $id_level]);
        } catch (Exception $e) {
            error_log("Error di deleteLoyal: " . $e->getMessage());
            return false;
        }
    }
}