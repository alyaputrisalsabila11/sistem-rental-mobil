<?php
require_once 'C:\xampp\htdocs\sistem-rental-mobil\config\database.php';

class BookingModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function createBooking($id_pelanggan, $id_mobil, $tgl_mulai, $tgl_selesai)
    {
        try {
            $this->db->beginTransaction();

            $d1 = new DateTime($tgl_mulai);
            $d2 = new DateTime($tgl_selesai);
            $durasi = $d2->diff($d1)->days + 1;
            $kode = 'BK' . strtoupper(uniqid());

            // 1. Insert ke tabel booking
            $sql = "INSERT INTO booking (kode_booking, id_pelanggan, id_mobil, tgl_mulai_sewa, tgl_selesai_sewa, durasi_hari, status_booking) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
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
    public function getBookingByUser($user_id)
    {
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

    public function getPendingBooking()
    {

        $sql = "SELECT
            b.*,
            p.nama_lengkap,
            m.merk_mobil,
            m.plat_nomor

        FROM booking b

        JOIN pelanggan p
            ON p.id=b.id_pelanggan

        JOIN mobil m
            ON m.id_mobil=b.id_mobil

        WHERE b.status_booking='Pending'

        ORDER BY b.id_booking DESC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingById($id)
    {

        $sql = "SELECT
            b.*,
            p.nama_lengkap,
            p.no_telp,
            p.alamat,
            p.no_ktp,

            m.*

        FROM booking b

        JOIN pelanggan p
            ON p.id=b.id_pelanggan

        JOIN mobil m
            ON m.id_mobil=b.id_mobil

        WHERE b.id_booking=?";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatusBooking($id_booking, $status)
{
    $sql = "UPDATE booking
            SET status_booking = ?
            WHERE id_booking = ?";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        $status,
        $id_booking
    ]);
}

public function updateStatusMobil($id_mobil, $status)
{
    $sql = "UPDATE mobil
            SET status_mobil = ?
            WHERE id_mobil = ?";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        $status,
        $id_mobil
    ]);
}
} // Penutup Class BookingModel
