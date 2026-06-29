<?php
require_once __DIR__ . '/../../config/database.php';

class MobilModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function createMobil($data) {
        try {
            $sql = "INSERT INTO mobil (nama_kategori, merk_mobil , plat_nomor, tahun, harga_dinamis, tipe, warna, cc) 
                    VALUES (:nama_kategori, :merk_mobil, :plat_nomor, :tahun, :harga_dinamis, :tipe, :warna, :cc)";
            
            $stmt = $this->db->prepare($sql);

            $success = $stmt->execute([
                ':nama_kategori'  => $data['nama_kategori'],
                ':merk_mobil'    => $data['merk_mobil'],
                ':plat_nomor'    => $data['plat_nomor'],
                ':tahun'         => $data['tahun'],
                ':harga_dinamis' => $data['harga_dinamis'],
                ':tipe'          => $data['tipe'],
                ':warna'         => $data['warna'],
                ':cc'            => $data['cc']
            ]);

            return $success;
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return false;
        }
    }

    // Ambil semua mobil yang statusnya 'Tersedia'
    public function getAvailableCars() {
        try {
            // Query disederhanakan tanpa JOIN karena nama_kategori sudah ada di tabel mobil
            $query = "SELECT * FROM mobil WHERE status_mobil = 'Tersedia' ORDER BY id_mobil DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }
    }

    // Ambil mobil berdasarkan filter nama kategori (Teks)
    public function getMobilByKategori($nama_kategori) {
        try {
            // Mencari berdasarkan kolom teks nama_kategori
            $query = "SELECT * FROM mobil WHERE nama_kategori = ? AND status_mobil = 'Tersedia' ORDER BY id_mobil DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$nama_kategori]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }
    }
    }