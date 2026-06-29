<?php

class LokasiController {
    // Pastikan kamu punya properti atau cara memanggil model/database di sini
    private $lokasiModel;

    public function __construct() {
        // Contoh inisialisasi model Lokasi (sesuaikan dengan struktur projekmu)
        $this->lokasiModel = new LokasiModel(); 
    }

    public function create() {
        // Ini fungsi yang menampilkan form tambah lokasi
        include __DIR__ . '/../views/lokasi_add.php';
    }

    public function store() {
        // Ini fungsi yang memproses input form (lokasi_proses_tambah)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_lokasi' => $_POST['nama_lokasi'],
                'alamat'      => $_POST['alamat'],
                'kota'        => $_POST['kota'] // Ambil data kota dari input baru
            ];

            if ($this->lokasiModel->createLokasi($data)) {
                // Jika berhasil simpan, redirect balik ke halaman lokasi_tambah agar daftar ter-refresh
                header('Location: index.php?page=manager_dashboard&action=buat_lokasi');
                exit;
            } else {
                echo "Gagal menyimpan data.";
            }
        }
    }
}