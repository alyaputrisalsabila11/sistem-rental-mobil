<?php

require_once __DIR__ . '/../../models/PengembalianModel.php';
require_once __DIR__ . '/../../models/LokasiModel.php';

class PengembalianController
{
    private $pengembalianModel;

    public function __construct()
    {
        $this->pengembalianModel = new PengembalianModel();
    }

    public function index()
{
    $penyerahan = $this->pengembalianModel->getPenyerahanOngoing();

    require_once __DIR__ . '/../../views/transaksi/pengembalian/index.php';
}

public function create()
{
    if (!isset($_GET['id'])) {
        die("ID Penyerahan tidak ditemukan.");
    }

    $id = intval($_GET['id']);

    $penyerahan = $this->pengembalianModel->getPenyerahanById($id);

    $lokasiModel = new LokasiModel();

    $lokasi = $lokasiModel->getAllLokasi();

    require_once __DIR__ . '/../../views/transaksi/pengembalian/create.php';
}

public function store()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $data = $_POST;

        // sementara upload foto belum diproses
        $data['foto_kondisi'] = null;

        $this->pengembalianModel->simpanPengembalian($data);

        echo "<script>

        alert('Data pengembalian berhasil disimpan.');

        window.location='index.php?page=pengembalian';

        </script>";
    }
}


}