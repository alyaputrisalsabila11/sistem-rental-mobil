<?php
class MobilModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Mengambil semua data mobil untuk ditampilkan di tabel
    public function getAllMobil() {
        $stmt = $this->db->query("SELECT * FROM mobil ORDER BY id_mobil DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menyimpan data mobil baru ke database
    public function tambahMobil($data) {
        $sql = "INSERT INTO mobil (nama_kategori, merk_mobil, plat_nomor, tahun, harga_dinamis, warna, cc, gambar, status_mobil) 
                VALUES (:nama_kategori, :merk_mobil, :plat_nomor, :tahun, :harga_dinamis, :warna, :cc, :gambar, :status_mobil)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}