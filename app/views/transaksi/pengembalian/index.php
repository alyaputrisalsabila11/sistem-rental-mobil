<?php
/** @var array $penyerahan */
include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid">

    <div class="card shadow mb-4">

        <div class="card-header py-3">
            <h4 class="m-0 font-weight-bold text-primary">
                Daftar Pengembalian Mobil
            </h4>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>
                            <th width="5%">No</th>
                            <th>Kode Booking</th>
                            <th>Nama Pelanggan</th>
                            <th>Mobil</th>
                            <th>No. Plat</th>
                            <th>Status</th>
                            <th width="15%">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if (!empty($penyerahan)) : ?>

                        <?php $no = 1; ?>

                        <?php foreach ($penyerahan as $row) : ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td><?= $row['kode_booking']; ?></td>

                                <td><?= $row['nama_lengkap']; ?></td>

                                <td><?= $row['merk_mobil']; ?></td>

                                <td><?= $row['plat_nomor']; ?></td>

                                <td>

                                    <span class="badge bg-success">
                                        <?= $row['status_booking']; ?>
                                    </span>

                                </td>

                                <td>

                                    <a href="index.php?page=pengembalian_create&id=<?= $row['id_penyerahan']; ?>"
                                        class="btn btn-primary btn-sm">

                                        Form Pengembalian

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else : ?>

                        <tr>

                            <td colspan="7" class="text-center">

                                Belum ada data penyerahan yang siap dikembalikan.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>