<?php 
/** @var array $booking */
/** @var array $lokasi */
include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header">
            <h4>Form Penyerahan Mobil</h4>
        </div>

        <div class="card-body">

            <form action="index.php?page=penyerahan_store" method="POST" enctype="multipart/form-data">

                <!-- Hidden -->
                <input type="hidden" name="id_booking" value="<?= $booking['id_booking']; ?>">
                <input type="hidden" name="id_mobil" value="<?= $booking['id_mobil']; ?>">
                <input type="hidden" name="total_biaya_awal" value="<?= $booking['total_harga']; ?>">

                <!-- Nanti diambil dari session login -->
                <input type="hidden" name="id_karyawan" value="<?= $_SESSION['user_id']; ?>">

                <div class="row">

                    <div class="col-md-6">

                        <div class="mb-3">
                            <label>Kode Booking</label>
                            <input type="text"
                                class="form-control"
                                value="<?= $booking['kode_booking']; ?>"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label>Nama Pelanggan</label>
                            <input type="text"
                                class="form-control"
                                value="<?= $booking['nama_lengkap']; ?>"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label>Mobil</label>
                            <input type="text"
                                class="form-control"
                                value="<?= $booking['merk_mobil']; ?>"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label>Plat Nomor</label>
                            <input type="text"
                                class="form-control"
                                value="<?= $booking['plat_nomor']; ?>"
                                readonly>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="mb-3">
                            <label>Tanggal Penyerahan</label>
                            <input type="date"
                                name="tgl_penyerahan"
                                class="form-control"
                                required>
                        </div>

                        <div class="mb-3">
                            <label>Jam Penyerahan</label>
                            <input type="time"
                                name="jam_penyerahan"
                                class="form-control"
                                required>
                        </div>

                        <div class="mb-3">
                            <label>Lokasi Penyerahan</label>
                            <select name="id_lokasi"
                                class="form-control"
                                required>

                                <option value="">-- Pilih Lokasi --</option>

                                <?php foreach ($lokasi as $item): ?>

                                    <option value="<?= $item['id_lokasi']; ?>">

                                        <?= $item['nama_lokasi']; ?> - <?= $item['kota']; ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>

                    </div>

                </div>

                <hr>

                <div class="row">

                    <div class="col-md-4">

                        <div class="mb-3">
                            <label>KM Awal</label>

                            <input type="number"
                                name="km_awal"
                                class="form-control"
                                required>
                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="mb-3">

                            <label>BBM Awal</label>

                            <select name="bbm_awal"
                                class="form-control">

                                <option value="Full">Full</option>
                                <option value="3/4">3/4</option>
                                <option value="1/2">1/2</option>
                                <option value="1/4">1/4</option>

                            </select>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="mb-3">

                            <label>Kondisi Mobil</label>

                            <select name="kondisi_mobil"
                                class="form-control">

                                <option value="Sangat Baik">Sangat Baik</option>
                                <option value="Baik">Baik</option>
                                <option value="Cukup">Cukup</option>
                                <option value="Perlu Perhatian">Perlu Perhatian</option>

                            </select>

                        </div>

                    </div>

                </div>

                <div class="mb-3">

                    <label>Checklist</label>

                    <textarea name="checklist"
                        class="form-control"
                        rows="3"
                        placeholder="Contoh: STNK, Dongkrak, Ban Cadangan"></textarea>

                </div>

                <div class="mb-3">

                    <label>Catatan</label>

                    <textarea name="catatan"
                        class="form-control"
                        rows="3"></textarea>

                </div>

                <div class="mb-3">

                    <label>Foto Kondisi Mobil</label>

                    <input type="file"
                        name="foto_kondisi"
                        class="form-control">

                </div>

                <button type="submit"
                    class="btn btn-success">

                    Simpan Penyerahan

                </button>

                <a href="index.php?page=penyerahan"
                    class="btn btn-secondary">

                    Kembali

                </a>

            </form>

        </div>

    </div>

</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>