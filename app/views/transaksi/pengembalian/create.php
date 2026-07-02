<?php
/** @var array $penyerahan */
/** @var array $lokasi */
?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header">
            <h4>Form Pengembalian Mobil</h4>
        </div>

        <div class="card-body">

            <form action="index.php?page=pengembalian_store" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="id_penyerahan" value="<?= $penyerahan['id_penyerahan']; ?>">

                <div class="row">

                    <div class="col-md-6">

                        <div class="mb-3">
                            <label>Kode Booking</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= $penyerahan['kode_booking']; ?>"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label>Nama Pelanggan</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= $penyerahan['nama_lengkap']; ?>"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label>Mobil</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= $penyerahan['merk_mobil']; ?>"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label>Plat Nomor</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= $penyerahan['plat_nomor']; ?>"
                                   readonly>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="mb-3">
                            <label>Tanggal Dikembalikan</label>
                            <input type="date"
                                   name="tgl_dikembalikan"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label>Jam Dikembalikan</label>
                            <input type="time"
                                   name="jam_dikembalikan"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label>KM Akhir</label>
                            <input type="number"
                                   name="km_akhir"
                                   class="form-control"
                                   required>
                        </div>

                    </div>

                </div>

                <hr>

                <div class="row">

                    <div class="col-md-4">

                        <label>BBM Akhir</label>

                        <select name="bbm_akhir" class="form-control">

                            <option>Full</option>
                            <option>3/4</option>
                            <option>1/2</option>
                            <option>1/4</option>
                            <option>Kosong</option>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <label>Kondisi Mobil</label>

                        <select name="kondisi_mobil" class="form-control">

                            <option>Sangat Baik</option>
                            <option>Baik</option>
                            <option>Cukup</option>
                            <option>Rusak Ringan</option>
                            <option>Rusak Berat</option>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <label>Denda Telat</label>

                        <input type="number"
                               name="denda_telat"
                               value="0"
                               class="form-control">

                    </div>

                </div>

                <div class="mt-3">

                    <label>Checklist</label>

                    <textarea
                        name="checklist"
                        rows="3"
                        class="form-control"></textarea>

                </div>

                <div class="mt-3">

                    <label>Catatan</label>

                    <textarea
                        name="catatan"
                        rows="3"
                        class="form-control"></textarea>

                </div>

                <div class="mt-3">

                    <label>Biaya Kerusakan</label>

                    <input type="number"
                           name="biaya_kerusakan"
                           value="0"
                           class="form-control">

                </div>

                <div class="mt-3">

                    <label>Foto Kondisi</label>

                    <input
                        type="file"
                        name="foto_kondisi"
                        class="form-control">

                </div>

                <hr>

                <button
                    class="btn btn-success"
                    type="submit">

                    Simpan Pengembalian

                </button>

                <a
                    href="index.php?page=home_lapangan&action=pengembalian"
                    class="btn btn-secondary">

                    Kembali

                </a>

            </form>

        </div>

    </div>

</div>