<?php
// =========================================================================
// FILE: controllers/AdminController.php (Controller Staff Admin Lengkap)
// =========================================================================

class AdminController {
    private $mobilModel; // Instansiasi model mobil
    private $fasilitasModel; // Instansiasi model fasilitas

    public function __construct() {
        // Proteksi: Pastikan hanya Staff Admin yang bisa akses
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff Admin') {
            header('Location: index.php?page=login');
            exit;
        }
        $this->mobilModel = new MobilModel();
        $this->fasilitasModel = new FasilitasModel();
    }

    // Memproses form tambah mobil
    public function proses_tambah_mobil() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gambar_biner = null;
            
            // Logika membaca file gambar sebagai data biner (BLOB)
            if (!empty($_FILES['gambar']['tmp_name'])) {
                $gambar_biner = file_get_contents($_FILES['gambar']['tmp_name']);
            }

            $data = [
                'nama_kategori' => $_POST['nama_kategori'],
                'merk_mobil'    => $_POST['merk_mobil'],
                'plat_nomor'    => $_POST['plat_nomor'],
                'tahun'         => $_POST['tahun'],
                'harga_dinamis' => $_POST['harga_dinamis'],
                'warna'         => $_POST['warna'],
                'cc'            => $_POST['cc'],
                'gambar'        => $gambar_biner, // Menyimpan file biner ke DB
                'status_mobil'  => $_POST['status_mobil']
            ];

            if ($this->mobilModel->tambahMobil($data)) {
                header('Location: index.php?page=Admin&action=data_mobil');
            }
            exit;
        }
    }

    // Memproses form tambah fasilitas
    public function proses_tambah_fasilitas() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_fasilitas' => $_POST['nama_fasilitas'],
                'deskripsi'      => $_POST['deskripsi'],
                'harga'          => $_POST['harga'],
                'stok'           => $_POST['stok'],
                'status'         => $_POST['status']
            ];

            if ($this->fasilitasModel->tambahFasilitas($data)) {
                header('Location: index.php?page=Admin&action=data_fasilitas');
            }
            exit;
        }
    }

    // SINKRONISASI LOGIKA UPGRADE LOYALITAS TERINTEGRASI VALIDASI SYARAT POIN
    public function proses_upgrade_loyalitas() {
        $db = Database::getConnection();
        
        // Menangkap data ID Pelanggan dan ID Level Loyalitas tujuan dari URL
        $id_pel = $_GET['id_pelanggan'] ?? null;
        $id_lev = $_GET['id_level'] ?? null;
        
        if ($id_pel && $id_lev) {
            // 1. Ambil poin saat ini yang dimiliki oleh pelanggan
            $stmtPel = $db->prepare("SELECT poin, nama_lengkap FROM pelanggan WHERE id_pelanggan = ?");
            $stmtPel->execute([$id_pel]);
            $pelData = $stmtPel->fetch(PDO::FETCH_ASSOC);
            $poin_saat_ini = $pelData['poin'] ?? 0;
            $nama_pelanggan = $pelData['nama_lengkap'] ?? '';

            // 2. Ambil poin syarat minimal dari loyalitas level tujuan
            $stmtLoy = $db->prepare("SELECT syarat, nama_level FROM loyalitas WHERE id_level = ?");
            $stmtLoy->execute([$id_lev]);
            $loyData = $stmtLoy->fetch(PDO::FETCH_ASSOC);
            $syarat_minimal = $loyData['syarat'] ?? 0;
            $nama_level_tujuan = $loyData['nama_level'] ?? '';

            // 3. Validasi Syarat: Apakah poin pelanggan mencukupi syarat minimal naik level?
            if ($poin_saat_ini < $syarat_minimal) {
                // Set pesan error jika tidak memenuhi syarat denda/poin naik tingkat
                $_SESSION['error'] = "Gagal upgrade! Poin " . $nama_pelanggan . " tidak memenuhi syarat untuk menjadi member " . $nama_level_tujuan . " (Butuh " . $syarat_minimal . " pts, poin saat ini: " . $poin_saat_ini . " pts).";
            } else {
                // Jika mencukupi, eksekusi query update tingkat loyalitas pelanggan
                $stmt = $db->prepare("UPDATE pelanggan SET id_level = ? WHERE id_pelanggan = ?");
                $stmt->execute([$id_lev, $id_pel]);
                $_SESSION['success'] = "Tingkat loyalitas " . $nama_pelanggan . " berhasil ditingkatkan menjadi " . $nama_level_tujuan . "!";
            }
        }
        
        header('Location: index.php?page=Admin&action=data_pelanggan');
        exit;
    }
}
?>