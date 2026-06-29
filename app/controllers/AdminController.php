<?php
require_once __DIR__ . '/../../config/database.php';

class AdminController {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // 1. Menampilkan halaman konfirmasi pesanan yang masuk
    public function konfirmasiPersetujuan() {
        // Ambil semua data booking yang statusnya masih 'Pending'
        $sql = "SELECT b.*, m.merk_mobil, p.nama_lengkap as nama_pelanggan 
                FROM booking b
                JOIN mobil m ON b.id_mobil = m.id_mobil
                JOIN pelanggan p ON b.id_pelanggan = p.id
                WHERE b.status_booking = 'Pending'
                ORDER BY b.id_booking ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pendingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kirim data ke file view admin
        include __DIR__ . '/../views/dashboard/Admin.php';
    }

    // 2. Proses aksi dari tombol Approve / Reject
    public function prosesAksi() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_booking = intval($_POST['id_booking']);
            $id_mobil   = intval($_POST['id_mobil']);
            $aksi       = $_POST['aksi']; // 'setuju' atau 'tolak'
            
            try {
                if ($aksi === 'setuju') {
                    // Cek apakah pakai sopir, jika ya ambil nama sopir dari input form
                    $sopir = isset($_POST['nama_sopir']) && !empty($_POST['nama_sopir']) ? $_POST['nama_sopir'] : 'Tanpa Sopir';

                    $sql = "UPDATE booking SET status_booking = 'Confirmed', sopir_booking = :sopir WHERE id_booking = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':sopir' => $sopir, ':id' => $id_booking]);
                    
                    // Status mobil tetap 'Disewa' (sudah diubah saat user booking)
                    $msg = "Pesanan berhasil disetujui!";
                } else {
                    // Jika ditolak, batalkan transaksi dan kembalikan status mobil jadi 'Tersedia'
                    $sql = "UPDATE booking SET status_booking = 'Canceled' WHERE id_booking = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':id' => $id_booking]);

                    $sqlMob = "UPDATE mobil SET status_mobil = 'Tersedia' WHERE id_mobil = :id_mobil";
                    $stmtMob = $this->db->prepare($sqlMob);
                    $stmtMob->execute([':id_mobil' => $id_mobil]);
                    
                    $msg = "Pesanan berhasil ditolak dan armada mobil telah dibebaskan!";
                }

                echo "<script>alert('$msg'); window.location.href = 'index.php?page=konfirmasi_admin';</script>";
            } catch (Exception $e) {
                echo "<script>alert('Gagal: " . $e->getMessage() . "'); window.location.href = 'index.php?page=konfirmasi_admin';</script>";
            }
        }
    }
}