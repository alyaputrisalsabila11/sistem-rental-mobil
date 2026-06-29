<?php

class HistoriController {
    
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Pastikan user sudah login
        $id_pelanggan = $_SESSION['user_id'] ?? null;
        if (!$id_pelanggan) {
            header('Location: index.php?page=login');
            exit;
        }

        require_once __DIR__ . '/../../config/database.php'; // Sesuaikan path database

        try {
            $db = Database::getConnection();

            // 2. Ambil data booking milik user ini, di-JOIN dengan data mobil
            $sql = "SELECT b.*, m.merk_mobil, m.gambar as gambar_mobil, m.plat_nomor 
                    FROM booking b 
                    JOIN mobil m ON b.id_mobil = m.id_mobil 
                    WHERE b.id_pelanggan = :id_pelanggan 
                    ORDER BY b.tgl_booking DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_pelanggan' => $id_pelanggan]);
            $daftar_booking = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Lempar data ke halaman view histori
            // Sesuaikan letak folder views histori kamu
            include __DIR__ . '/../views/histori.php'; 

        } catch (Exception $e) {
            echo "Gagal mengambil data histori: " . $e->getMessage();
        }
    }
}