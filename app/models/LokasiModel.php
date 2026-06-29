<?php
require_once __DIR__ . '/../../config/database.php';

class LokasiModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function createLokasi($data) {
        try {
            // Kolom created_at dan kota tidak perlu ditulis jika ingin menggunakan default-nya
            $sql = "INSERT INTO lokasi (nama_lokasi, alamat, kota) 
                    VALUES (:nama_lokasi, :alamat, :kota)";
                    
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nama_lokasi' => $data['nama_lokasi'],
                ':alamat'      => $data['alamat'],
                ':kota'        => $data['kota']
            ]);
        } catch (Exception $e) {
            error_log("Error saat createLokasi: " . $e->getMessage());
            return false;
        }
    }

    public function getAllLokasi() {
        try {
            $sql = "SELECT id_lokasi, nama_lokasi, kota, alamat FROM lokasi ORDER BY id_lokasi DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getAllLokasi: " . $e->getMessage());
            return [];
        }
    }
}