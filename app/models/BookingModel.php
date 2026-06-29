<?php
require_once 'C:\xampp\htdocs\Sistem Rental Mobil\config\database.php';

class BookingModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function createBooking($id_pelanggan, $id_mobil, $tgl_mulai, $tgl_selesai) {
        try {
            $this->db->beginTransaction();

            $d1 = new DateTime($tgl_mulai);
            $d2 = new DateTime($tgl_selesai);
            $durasi = $d2->diff($d1)->days + 1;
            $kode = 'BK' . strtoupper(uniqid());

            // 1. Insert ke tabel booking
            $sql = "INSERT INTO booking (kode_booking, id_pelanggan, id_mobil, tgl_mulai_sewa, tgl_selesai_sewa, durasi_hari, status_booking) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$kode, $id_pelanggan, $id_mobil, $tgl_mulai, $tgl_selesai, $durasi]);

            // 2. Update status mobil menjadi Dibooking sesuai ENUM database
            $sqlMob = "UPDATE mobil SET status_mobil = 'Dibooking' WHERE id_mobil = ?";
            $stmtMob = $this->db->prepare($sqlMob);
            $stmtMob->execute([$id_mobil]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    // Fungsi getBookingByUser yang sudah diperbaiki strukturnya
    public function getBookingByUser($user_id) {
        try {
            $sql = "SELECT b.*, m.merk_mobil, m.plat_nomor, m.gambar 
                    FROM booking b
                    JOIN mobil m ON b.id_mobil = m.id_mobil
                    WHERE b.id_pelanggan = :id_pelanggan
                    ORDER BY b.id_booking DESC";
            
            $stmt = $this->db->prepare($sql);
            // Menggunakan variabel $user_id yang dikirim dari HistoriController
            $stmt->execute([':id_pelanggan' => $user_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
} // Penutup Class BookingModel
?>