<?php
// Pastikan path ke database sudah benar
require_once __DIR__ . '/../../config/database.php';

class KaryawanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Fungsi untuk membuat / mendaftarkan akun karyawan baru oleh Manager
    // Fungsi untuk membuat / mendaftarkan akun karyawan baru oleh Manager
    public function createKaryawan($data) {
        try {
            // PERBAIKAN: Ubah 'lokasi_id' menjadi 'id_lokasi' sesuai tabel karyawan
            $sql = "INSERT INTO karyawan (nama_karyawan, email, no_telp, password, role, status_karyawan, id_lokasi, created_at, updated_at) 
                    VALUES (:nama_karyawan, :email, :no_telp, :password, :role, :status_karyawan, :id_lokasi, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $success = $stmt->execute([
                ':nama_karyawan'   => $data['nama_karyawan'],
                ':email'           => $data['email'],
                ':no_telp'         => $data['no_telp'],
                ':password'        => $data['password'], 
                ':role'            => $data['role'],    
                ':status_karyawan' => $data['status_karyawan'],
                ':id_lokasi'       => $data['id_lokasi'] // <-- Sudah disesuaikan
            ]);
            return $success;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllKaryawan() {
        try {
            // PERBAIKAN: Sesuaikan relasi JOIN menggunakan k.id_lokasi = l.id_lokasi
            $sql = "SELECT k.id_karyawan, k.nama_karyawan, k.email, k.role, k.status_karyawan, l.nama_lokasi 
                    FROM karyawan k
                    LEFT JOIN lokasi l ON k.id_lokasi = l.id_lokasi
                    ORDER BY k.id_karyawan DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Kita kembalikan log error ke bentuk semula agar aman
            error_log("Error saat getAllKaryawan: " . $e->getMessage());
            return [];
        }
    }

    // Fungsi untuk mencari karyawan berdasarkan email saat login
    public function getByUsername($username) {
        try {
            $sql = "SELECT id_karyawan, nama_karyawan, email, password, role 
                    FROM karyawan 
                    WHERE email = ? LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di KaryawanModel: " . $e->getMessage());
            return false;
        }
    }

    // FUNGSI BARU: Mengambil 1 data karyawan spesifik berdasarkan ID untuk form edit
    public function getKaryawanById($id) {
        try {
            $sql = "SELECT * FROM karyawan WHERE id_karyawan = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di getKaryawanById: " . $e->getMessage());
            return false;
        }
    }

    // FUNGSI BARU: Menyimpan perubahan data karyawan
    public function updateKaryawan($id, $data) {
        try {
            // Jika password diisi baru, ikut di-update. Jika kosong, pakai password lama.
            if (!empty($data['password'])) {
                $sql = "UPDATE karyawan SET nama_karyawan = :nama_karyawan, email = :email, no_telp = :no_telp, password = :password, role = :role, status_karyawan = :status_karyawan, id_lokasi = :id_lokasi, updated_at = NOW() WHERE id_karyawan = :id_karyawan";
            } else {
                $sql = "UPDATE karyawan SET nama_karyawan = :nama_karyawan, email = :email, no_telp = :no_telp, role = :role, status_karyawan = :status_karyawan, id_lokasi = :id_lokasi, updated_at = NOW() WHERE id_karyawan = :id_karyawan";
            }

            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':nama_karyawan'   => $data['nama_karyawan'],
                ':email'           => $data['email'],
                ':no_telp'         => $data['no_telp'],
                ':role'            => $data['role'],
                ':status_karyawan' => $data['status_karyawan'],
                ':id_lokasi'       => $data['id_lokasi'],
                ':id_karyawan'     => $id
            ];
            
            if (!empty($data['password'])) {
                $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Error di updateKaryawan: " . $e->getMessage());
            return false;
        }
    }

    // FUNGSI BARU: Menghapus data karyawan
    public function deleteKaryawan($id) {
        try {
            $sql = "DELETE FROM karyawan WHERE id_karyawan = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error di deleteKaryawan: " . $e->getMessage());
            return false;
        }
    }
}