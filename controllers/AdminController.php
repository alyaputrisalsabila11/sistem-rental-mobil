<?php
class AdminController {
    private $mobilModel;
    private $fasilitasModel;

    public function __construct() {
        // Proteksi: Pastikan hanya Staff Admin yang bisa akses
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        // Sesuaikan dengan isi database: 'Staff Admin'
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
            $nama_file = "";
            
            // Logika upload gambar sederhana (ke folder mobil/)
            if (!empty($_FILES['gambar']['name'])) {
                $nama_file = time() . "_" . $_FILES['gambar']['name'];
                // __DIR__ . "/../mobil/" berarti naik satu folder lalu cari folder mobil
                move_uploaded_file($_FILES['gambar']['tmp_name'], __DIR__ . "/../../mobil/" . $nama_file);
            }

            $data = [
                'nama_kategori' => $_POST['nama_kategori'],
                'merk_mobil'    => $_POST['merk_mobil'],
                'plat_nomor'    => $_POST['plat_nomor'],
                'tahun'         => $_POST['tahun'],
                'harga_dinamis' => $_POST['harga_dinamis'],
                'warna'         => $_POST['warna'],
                'cc'            => $_POST['cc'],
                'gambar'        => $nama_file,
                'status_mobil'  => $_POST['status_mobil']
            ];

            if ($this->mobilModel->tambahMobil($data)) {
                // Pindah ke halaman tabel data mobil
                header('Location: index.php?page=staffadmin_dashboard&action=data_mobil');
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
                // Pindah ke halaman tabel data fasilitas
                header('Location: index.php?page=staffadmin_dashboard&action=data_fasilitas');
            }
            exit;
        }
    }
}