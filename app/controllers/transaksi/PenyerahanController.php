<?php

require_once __DIR__ . '/../../models/PenyerahanModel.php';
require_once __DIR__ . '/../../models/BookingModel.php';
class PenyerahanController
{
    private $penyerahanModel;
    private $bookingModel;

    public function __construct()
    {
        $this->penyerahanModel = new PenyerahanModel();
        $this->bookingModel = new BookingModel();
    }

    public function index()
{
    $booking = $this->penyerahanModel->getBookingConfirmed();

    require_once __DIR__ . '/../../views/transaksi/penyerahan/index.php';
}

public function create()
{
    if (!isset($_GET['id'])) {
        die("ID Booking tidak ditemukan.");
    }
    $id = intval($_GET['id']);
    $booking = $this->penyerahanModel->getBookingById($id);
    require_once __DIR__ . '/../../models/LokasiModel.php';
    $lokasiModel = new LokasiModel();
    $lokasi = $lokasiModel->getAllLokasi();
    require_once __DIR__ . '/../../views/transaksi/penyerahan/create.php';
}

public function store()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $data = $_POST;

        // sementara upload foto belum diproses
        $data['foto_kondisi'] = null;

        // Simpan data penyerahan
        $this->penyerahanModel->simpanPenyerahan($data);

        $this->bookingModel->updateStatusBooking(
    $data['id_booking'],
    'Ongoing'
);

$this->bookingModel->updateStatusMobil(
    $data['id_mobil'],
    'Disewa'
);

        echo "<script>
                alert('Data penyerahan berhasil disimpan.');
                window.location='index.php?page=home_lapangan&action=penyerahan';
              </script>";
    }
}
}