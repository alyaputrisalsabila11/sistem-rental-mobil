<?php
// Pastikan path ke database sudah benar
require_once __DIR__ . '/../init.php';

class KaryawanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Fungsi untuk membuat / mendaftarkan akun karyawan baru oleh Manager
    public function createKaryawan($data) {
        try {
            // DISESUAIKAN: Menambahkan kolom alamat, no_ktp, status_supir, dan mengubah updated_at menjadi update_at
            $sql = "INSERT INTO karyawan (nama_karyawan, email, no_telp, password, role, status_karyawan, id_lokasi, alamat, no_ktp, status_supir, created_at, update_at) 
                    VALUES (:nama_karyawan, :email, :no_telp, :password, :role, :status_karyawan, :id_lokasi, :alamat, :no_ktp, :status_supir, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $success = $stmt->execute([
                ':nama_karyawan'   => $data['nama_karyawan'],
                ':email'           => $data['email'],
                ':no_telp'         => $data['no_telp'],
                ':password'        => $data['password'],
                ':role'            => $data['role'],
                ':status_karyawan' => $data['status_karyawan'],
                ':id_lokasi'       => !empty($data['id_lokasi']) ? $data['id_lokasi'] : null,
                ':alamat'          => $data['alamat'],
                ':no_ktp'          => $data['no_ktp'],
                ':status_supir'    => $data['status_supir']
            ]);
            return $success;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllKaryawan() {
        try {
            // Tetap mempertahankan join ke tabel lokasi, namun mengambil field baru jika dibutuhkan di view
            $sql = "SELECT k.id_karyawan, k.nama_karyawan, k.email, k.no_telp, k.role, k.status_karyawan, k.alamat, k.no_ktp, k.status_supir, l.nama_lokasi 
                    FROM karyawan k
                    LEFT JOIN lokasi l ON k.id_lokasi = l.id_lokasi
                    ORDER BY k.id_karyawan DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
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

    // Mengambil 1 data karyawan spesifik berdasarkan ID untuk form edit
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

    // Menyimpan perubahan data karyawan
    public function updateKaryawan($id, $data) {
        try {
            // DISESUAIKAN: Mengubah nama kolom timestamp ke update_at dan menyertakan data alamat, no_ktp, status_supir
            if (!empty($data['password'])) {
                $sql = "UPDATE karyawan SET nama_karyawan = :nama_karyawan, email = :email, no_telp = :no_telp, password = :password, role = :role, status_karyawan = :status_karyawan, id_lokasi = :id_lokasi, alamat = :alamat, no_ktp = :no_ktp, status_supir = :status_supir, update_at = NOW() WHERE id_karyawan = :id_karyawan";
            } else {
                $sql = "UPDATE karyawan SET nama_karyawan = :nama_karyawan, email = :email, no_telp = :no_telp, role = :role, status_karyawan = :status_karyawan, id_lokasi = :id_lokasi, alamat = :alamat, no_ktp = :no_ktp, status_supir = :status_supir, update_at = NOW() WHERE id_karyawan = :id_karyawan";
            }

            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':nama_karyawan'   => $data['nama_karyawan'],
                ':email'           => $data['email'],
                ':no_telp'         => $data['no_telp'],
                ':role'            => $data['role'],
                ':status_karyawan' => $data['status_karyawan'],
                ':id_lokasi'       => !empty($data['id_lokasi']) ? $data['id_lokasi'] : null,
                ':alamat'          => $data['alamat'],
                ':no_ktp'          => $data['no_ktp'],
                ':status_supir'    => $data['status_supir'],
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

    // Menghapus data karyawan
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