<?php
// Pastikan path ke database sudah benar
require_once __DIR__ . '/../../config/database.php';

class KaryawanModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Fungsi untuk membuat / mendaftarkan akun karyawan baru oleh Manager
    public function createKaryawan($data) {
        try {
            // Tambahkan :lokasi_id ke dalam SQL
            $sql = "INSERT INTO karyawan (nama_karyawan, email, no_telp, password, role, status_karyawan, lokasi_id, created_at, updated_at) 
                    VALUES (:nama_karyawan, :email, :no_telp, :password, :role, :status_karyawan, :lokasi_id, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $success = $stmt->execute([
                ':nama_karyawan'   => $data['nama_karyawan'],
                ':email'           => $data['email'],
                ':no_telp'         => $data['no_telp'],
                ':password'        => $data['password'], 
                ':role'            => $data['role'],    
                ':status_karyawan' => $data['status_karyawan'],
                ':lokasi_id'       => $data['lokasi_id'] // <-- Jangan lupa diikat di sini!
            ]);
            return $success;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllKaryawan() {
        try {
            $sql = "SELECT k.id_karyawan, k.nama_karyawan, k.email, k.role, k.status_karyawan, l.nama_lokasi 
                    FROM karyawan k
                    LEFT JOIN lokasi l ON k.lokasi_id = l.id
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
            // PERBAIKAN: Menghapus kolom 'username' dari SELECT dan WHERE karena tidak ada di tabel kamu
            $sql = "SELECT id_karyawan, nama_karyawan, email, password, role 
                    FROM karyawan 
                    WHERE email = ? LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            // $username di sini akan menerima input teks yang kamu ketik di form login
            $stmt->execute([$username]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error di KaryawanModel: " . $e->getMessage());
            return false;
        }
    }
}