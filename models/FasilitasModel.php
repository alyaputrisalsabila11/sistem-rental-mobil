<?php
class FasilitasModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Mengambil semua data fasilitas
    public function getAllFasilitas() {
        $stmt = $this->db->query("SELECT * FROM fasilitas ORDER BY id_fasilitas DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menyimpan fasilitas baru
    public function tambahFasilitas($data) {
        $sql = "INSERT INTO fasilitas (nama_fasilitas, deskripsi, harga, stok, status) 
                VALUES (:nama_fasilitas, :deskripsi, :harga, :stok, :status)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // Update fasilitas
    public function updateFasilitas($id, $data) {
        $sql = "UPDATE fasilitas SET
                    nama_fasilitas = :nama_fasilitas,
                    deskripsi = :deskripsi,
                    harga = :harga,
                    stok = :stok,
                    status = :status
                WHERE id_fasilitas = :id_fasilitas";

        $data['id_fasilitas'] = $id;

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    // Hapus fasilitas
    public function hapusFasilitas($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM fasilitas
            WHERE id_fasilitas = ?"
        );

        return $stmt->execute([$id]);
    }
}