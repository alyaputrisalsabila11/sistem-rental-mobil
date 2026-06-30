<?php

class LokasiController {
    private $lokasiModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $this->lokasiModel = new LokasiModel(); 
    }

    public function create() {
        include __DIR__ . '/../views/lokasi_add.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_lokasi' => $_POST['nama_lokasi'],
                'alamat'      => $_POST['alamat'],
                'kota'        => $_POST['kota'] 
            ];

            if ($this->lokasiModel->createLokasi($data)) {
                $_SESSION['success'] = "Cabang baru berhasil ditambahkan!";
            } else {
                $_SESSION['error'] = "Gagal menyimpan data cabang.";
            }
            header('Location: index.php?page=manager_dashboard&action=buat_lokasi');
            exit;
        }
    }

    // FUNGSI BARU: Proses tangkapan form edit
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_lokasi = $_POST['id_lokasi'];
            $data = [
                'nama_lokasi' => $_POST['nama_lokasi'],
                'alamat'      => $_POST['alamat'],
                'kota'        => $_POST['kota']
            ];

            if ($this->lokasiModel->updateLokasi($id_lokasi, $data)) {
                $_SESSION['success'] = "Data cabang berhasil diperbarui!";
            } else {
                $_SESSION['error'] = "Gagal memperbarui data cabang.";
            }
            header('Location: index.php?page=manager_dashboard&action=buat_lokasi');
            exit;
        }
    }

    // FUNGSI BARU: Proses tangkapan tombol hapus
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->lokasiModel->deleteLokasi($id)) {
                $_SESSION['success'] = "Cabang berhasil dihapus!";
            } else {
                $_SESSION['error'] = "Gagal menghapus cabang. Pastikan tidak ada karyawan yang masih terdaftar di cabang ini.";
            }
        }
        header('Location: index.php?page=manager_dashboard&action=buat_lokasi');
        exit;
    }
}