<?php
// File: models/MobilModel.php

class MobilModel
{
    private $db; // Variabel koneksi database

    public function __construct()
    {
        // Mengambil koneksi database singleton yang sudah dibuat
        $this->db = Database::getConnection();
    }

    // Mengambil semua data mobil untuk ditampilkan di tabel
    public function getAllMobil()
    {
        // Query SQL mengambil seluruh data mobil diurutkan dari yang terbaru
        $stmt = $this->db->query("SELECT * FROM mobil ORDER BY id_mobil DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menyimpan data mobil baru ke database
    public function tambahMobil($data)
    {
        // PERBAIKAN: Menggunakan nama kolom 'status_mobil' sesuai dengan PDM di phpMyAdmin Anda
        $sql = "INSERT INTO mobil (nama_kategori, merk_mobil, plat_nomor, tahun, harga_dinamis, warna, cc, gambar, status_mobil) 
                VALUES (:nama_kategori, :merk_mobil, :plat_nomor, :tahun, :harga_dinamis, :warna, :cc, :gambar, :status_mobil)";

        $stmt = $this->db->prepare($sql); // Menyiapkan statement PDO
        return $stmt->execute($data); // Menjalankan query dengan data biner gambar & status_mobil
    }

    // Mengambil data mobil berdasarkan ID
    public function getMobilById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM mobil WHERE id_mobil = ?"
        );

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Update data mobil
    public function updateMobil($id, $data)
    {
        $setGambar = !empty($data['gambar'])
            ? ", gambar = :gambar"
            : "";

        $sql = "UPDATE mobil SET
                nama_kategori = :nama_kategori,
                merk_mobil = :merk_mobil,
                plat_nomor = :plat_nomor,
                tahun = :tahun,
                harga_dinamis = :harga_dinamis,
                warna = :warna,
                cc = :cc,
                status_mobil = :status_mobil
                {$setGambar}
            WHERE id_mobil = :id_mobil";

        $data['id_mobil'] = $id;

        // Kalau gambar tidak diganti,
        // gambar lama tetap digunakan
        if (empty($data['gambar'])) {
            unset($data['gambar']);
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }


    // Hapus data mobil
    public function hapusMobil($id)
    {
        $stmt = $this->db->prepare(
            "DELETE FROM mobil WHERE id_mobil = ?"
        );

        return $stmt->execute([$id]);
    }
}
