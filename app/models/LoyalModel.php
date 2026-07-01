<?php
require_once __DIR__ . '/../../config/database.php';

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

    // Fungsi untuk mengambil semua data loyalitas
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
}