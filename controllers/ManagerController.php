<?php
// controllers/ManagerController.php

class ManagerController {
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
            header('Location: index.php?page=login');
            exit;
        }
        $this->db = Database::getConnection();
    }

    public function index() {
        // Ambil data untuk sidebar/dropdown jika diperlukan
        $karyawanModel = new KaryawanModel();
        $daftarKaryawan = $karyawanModel->getAllKaryawan();
        
        $lokasiModel = new LokasiModel();
        $daftarLokasi = $lokasiModel->getAllLokasi();

        $action = $_GET['action'] ?? 'home';

        //edit
        $lokasiEdit = null; // Sediakan variabel default agar tidak error undefined
        if ($action === 'edit_lokasi') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                // Gunakan objek $lokasiModel yang sudah di-instansiasi di atas
                $lokasiEdit = $lokasiModel->getLokasiById($id);
            }
        }

        $karyawanEdit = null; // Sediakan variabel default agar tidak error undefined
        if ($action === 'edit_karyawan') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                // Gunakan objek $karyawanModel yang sudah di-instansiasi di atas
                $karyawanEdit = $karyawanModel->getKaryawanById($id);
            }
        }

        $loyalEdit = null; // Sediakan variabel default agar tidak error undefined
        if ($action === 'edit_loyal') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $loyalModel = new LoyalModel();
                $loyalEdit = $loyalModel->getLoyalById($id);
            }
        }

        $voucherEdit = null; // Sediakan variabel default agar tidak error undefined
        if ($action === 'edit_voucher') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $voucherModel = new VoucherModel();
                $voucherEdit = $voucherModel->getVoucherById($id);
            }
        }

        // Tambahkan ini untuk handle detail perbaikan/kerusakan
        $detailKerusakan = null;
        if ($action === 'detail') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                // Sesuai dengan screenshot "Formulir Penyelesaian & Estimasi Biaya Perbaikan"
                // Silakan sesuaikan nama tabel dan primary key-nya jika berbeda di databasemu
                $stmt = $this->db->prepare("SELECT * FROM kondisi_mobil WHERE id_kondisi = :id");
                $stmt->execute(['id' => $id]);
                $detailKerusakan = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }

        // ========== DATA DASHBOARD (hanya dijalankan jika action = home) ==========
        if ($action === 'home') {
            // 1. Total Pendapatan (Status 'complete' ada di tabel penyerahan)
            $totalPendapatan = $this->db->query(
                "SELECT SUM(p.total_harga) FROM penyewaan p
                 JOIN penyerahan ps ON p.id_penyewaan = ps.id_penyewaan
                 WHERE ps.status_sewa = 'complete'"
            )->fetchColumn() ?: 0;

            // 2. Total Transaksi (Selain yang dibatalkan)
            $totalTransaksi = $this->db->query(
                "SELECT COUNT(*) FROM penyewaan WHERE status_penyewaan != 'Canceled'"
            )->fetchColumn() ?: 0;

            // 3. Pelanggan Baru (30 hari terakhir)
            $pelangganBaru = $this->db->query(
                "SELECT COUNT(*) FROM pelanggan WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            )->fetchColumn() ?: 0;

            // 4. Unit Maintenance
            $unitMaintenance = $this->db->query(
                "SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Maintenance'"
            )->fetchColumn() ?: 0;

            // 5. GRAFIK: Transaksi per Cabang (DI PERBAIKI JOIN-NYA)
            $grafikCabang = $this->db->query(
                "SELECT l.nama_lokasi, DATE_FORMAT(p.tgl_penyewaan, '%Y-%m') AS bulan, COUNT(p.id_penyewaan) AS jumlah
                 FROM penyewaan p
                 JOIN penyerahan ps ON p.id_penyewaan = ps.id_penyewaan
                 JOIN lokasi l ON ps.id_lokasi = l.id_lokasi
                 WHERE p.tgl_penyewaan >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY l.id_lokasi, bulan
                 ORDER BY bulan ASC"
            )->fetchAll(PDO::FETCH_ASSOC);

            // 6. Grafik Minat Kategori
            $grafikKategori = $this->db->query(
                "SELECT m.nama_kategori, COUNT(p.id_penyewaan) AS total
                 FROM penyewaan p JOIN mobil m ON p.id_mobil = m.id_mobil
                 GROUP BY m.nama_kategori ORDER BY total DESC"
            )->fetchAll(PDO::FETCH_ASSOC);

            // 7. Top 5 Mobil Primadona
            $topMobil = $this->db->query(
                "SELECT m.merk_mobil, m.plat_nomor, COUNT(p.id_penyewaan) AS total_sewa
                 FROM penyewaan p JOIN mobil m ON p.id_mobil = m.id_mobil
                 GROUP BY p.id_mobil ORDER BY total_sewa DESC LIMIT 5"
            )->fetchAll(PDO::FETCH_ASSOC);

            // 8. Top 5 Pelanggan Teraktif
            $topPelanggan = $this->db->query(
                "SELECT pl.nama_lengkap, COUNT(p.id_penyewaan) AS total_sewa
                 FROM penyewaan p JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                 GROUP BY p.id_pelanggan ORDER BY total_sewa DESC LIMIT 5"
            )->fetchAll(PDO::FETCH_ASSOC);

            // 9. Distribusi Loyalitas
            $loyalitasDist = $this->db->query(
                "SELECT l.nama_level, COUNT(pl.id_pelanggan) AS jumlah
                 FROM pelanggan pl LEFT JOIN loyalitas l ON pl.id_level = l.id_level
                 GROUP BY l.id_level ORDER BY l.id_level"
            )->fetchAll(PDO::FETCH_ASSOC);

        }

        // Load View
        include __DIR__ . '/../views/user/dashboard/manager.php';
    }
}