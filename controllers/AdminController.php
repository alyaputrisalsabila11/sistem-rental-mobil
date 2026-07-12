<?php
// File: controllers/AdminController.php

class AdminController {
    private $mobilModel;
    private $fasilitasModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff Admin') {
            header('Location: index.php?page=login');
            exit;
        }
        $this->mobilModel = new MobilModel();
        $this->fasilitasModel = new FasilitasModel();
    }

    // Memproses form tambah mobil baru oleh karyawan admin
    public function proses_tambah_mobil() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $gambar_biner = null; // Default biner gambar kosong
            
            // PERBAIKAN: Membaca file gambar sebagai data biner beneran agar cocok dengan kolom longblob database Anda
            if (!empty($_FILES['gambar']['tmp_name'])) {
                // Membaca konten file gambar menjadi data biner untuk disimpan langsung ke database
                $gambar_biner = file_get_contents($_FILES['gambar']['tmp_name']);
            }

            // Menyusun array parameter binding untuk query insert model
            $data = [
                'nama_kategori' => $_POST['nama_kategori'],
                'merk_mobil'    => $_POST['merk_mobil'],
                'plat_nomor'    => $_POST['plat_nomor'],
                'tahun'         => $_POST['tahun'],
                'harga_dinamis' => $_POST['harga_dinamis'],
                'warna'         => $_POST['warna'],
                'cc'            => $_POST['cc'],
                'gambar'        => $gambar_biner, // Data biner gambar dimasukkan ke placeholder :gambar
                'status_mobil'  => $_POST['status_mobil'] // Memasukkan nilai status awal armada
            ];

            // Panggil model untuk menyimpan data biner ke database
            if ($this->mobilModel->tambahMobil($data)) {
                // Redirect ke tabel data mobil
                header('Location: index.php?page=Admin&action=data_mobil');
            } else {
                die("Gagal menyimpan data mobil biner.");
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
            } else {
                die("Gagal menyimpan data fasilitas.");
            }
            exit;
        }
    }
}
?>