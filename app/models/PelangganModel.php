<?php
require_once __DIR__ . '/../../config/database.php';

class PelangganModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Cek email sudah terdaftar
    public function emailExists($email) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM pelanggan WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking email: " . $e->getMessage());
            return false;
        }
    }

    // Cek username sudah terdaftar
    public function usernameExists($username) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM pelanggan WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking username: " . $e->getMessage());
            return false;
        }
    }

    // Ambil user berdasarkan email
    public function getUserByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return false;
        }
    }

    // Ambil user berdasarkan username
    public function getUserByUsername($username) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by username: " . $e->getMessage());
            return false;
        }
    }

    // Ambil user berdasarkan ID (Disesuaikan ke kolom 'id')
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by id: " . $e->getMessage());
            return false;
        }
    }

    // Buat user baru
    public function createUser($nama_lengkap, $username, $email, $password, $no_telp, $alamat, $no_ktp = null) {
        try {
            // id_level langsung diisi 1 (asumsi id_level = 1 adalah level Regular/Bronze di tabel loyalitas)
            $stmt = $this->db->prepare(
                "INSERT INTO pelanggan (nama_lengkap, username, email, password, no_telp, alamat, no_ktp, poin, id_level) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)"
            );
            $result = $stmt->execute([$nama_lengkap, $username, $email, $password, $no_telp, $alamat, $no_ktp]);
            
            if (!$result) {
                error_log("Insert failed: " . implode(", ", $stmt->errorInfo()));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    public function getAllPelanggan() {
        try {
            $sql = "SELECT id, nama_lengkap, username, email, no_telp, alamat FROM pelanggan ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error saat getAllPelanggan: " . $e->getMessage());
            return [];
        }
    }


    // Update profil user (Disesuaikan ke kolom 'id')
    public function updateUser($id, $nama_lengkap, $no_telp, $alamat) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE pelanggan SET nama_lengkap = ?, no_telp = ?, alamat = ? 
                 WHERE id = ?"
            );
            return $stmt->execute([$nama_lengkap, $no_telp, $alamat, $id]);
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }
}
?>