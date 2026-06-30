<?php
require_once __DIR__ . '/../../config/database.php';

class LokasiModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function createLokasi($data) {
        try {
            $sql = "INSERT INTO lokasi (nama_lokasi, alamat, kota) VALUES (:nama_lokasi, :alamat, :kota)";
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

    // FUNGSI BARU: Ambil satu data cabang berdasarkan ID untuk form Edit
    public function getLokasiById($id) {
        try {
            $sql = "SELECT * FROM lokasi WHERE id_lokasi = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getLokasiById: " . $e->getMessage());
            return false;
        }
    }

    // FUNGSI BARU: Proses simpan perubahan data cabang
    public function updateLokasi($id, $data) {
        try {
            $sql = "UPDATE lokasi SET nama_lokasi = :nama_lokasi, alamat = :alamat, kota = :kota WHERE id_lokasi = :id_lokasi";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nama_lokasi' => $data['nama_lokasi'],
                ':alamat'      => $data['alamat'],
                ':kota'        => $data['kota'],
                ':id_lokasi'   => $id
            ]);
        } catch (Exception $e) {
            error_log("Error di updateLokasi: " . $e->getMessage());
            return false;
        }
    }

    // FUNGSI BARU: Proses hapus cabang
    public function deleteLokasi($id) {
        try {
            $sql = "DELETE FROM lokasi WHERE id_lokasi = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error di deleteLokasi: " . $e->getMessage());
            return false; // Gagal biasanya karena masih ada karyawan di cabang ini (Constraint Foreign Key)
        }
    }
}