<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-fluid">

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body pb-0">

            <h2 class="fw-bold text-dark mb-1">
                <i class="fas fa-key text-warning"></i>
                Penyerahan Mobil
            </h2>

            <p class="text-muted mb-3">
                Kelola proses serah terima kendaraan kepada pelanggan.
            </p>

        </div>

        <div class="card-body">

            <?php if (!empty($booking)) : ?>
                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead class="table-light">

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

                            <?php if (!empty($booking)) : ?>

                                <?php $no = 1; ?>

                                <?php foreach ($booking as $row) : ?>

                                    <tr>

                                        <td><?= $no++; ?></td>

                                        <td><?= $row['kode_booking']; ?></td>

                                        <td><?= $row['nama_lengkap']; ?></td>

                                        <td><?= $row['merk_mobil']; ?></td>

                                        <td><?= $row['plat_nomor']; ?></td>

                                        <td>

                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                <?= $row['status_booking']; ?>
                                            </span>

                                        </td>

                                        <td>

                                            <a href="index.php?page=penyerahan_create&id=<?= $row['id_booking']; ?>"
                                                class="btn btn-warning btn-sm rounded-pill px-3">

                                                <i class="fas fa-file-signature me-1"></i>

                                                Isi Form

                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>
                        </tbody>

                    </table>

                </div>
            <?php else : ?>

                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body text-center py-5">

                        <i class="fas fa-key fa-4x text-warning mb-3"></i>

                        <h4 class="fw-bold">

                            Belum Ada Penyerahan Mobil

                        </h4>

                        <p class="text-muted">

                            Booking yang telah disetujui Staff Admin akan muncul di halaman ini.

                        </p>

                    </div>

                </div>

            <?php endif; ?>
        <?php endif; ?>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>