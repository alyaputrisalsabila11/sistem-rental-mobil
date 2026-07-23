<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
$db = Database::getConnection(); 

$staff_name = $_SESSION['user_name'] ?? 'Staff Lapangan';
$id_lokasi_cabang = $_SESSION['id_lokasi'] ?? null; 
$action = $_GET['action'] ?? 'home';

// --- PROSES ACTION HANDLER (POST & GET) ---
if ($action === 'proses_cek_kondisi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengembalian = filter_var($_POST['id_pengembalian'] ?? 0, FILTER_VALIDATE_INT);
    $id_mobil = filter_var($_POST['id_mobil'] ?? 0, FILTER_VALIDATE_INT);
    $tingkat_kerusakan = $_POST['tingkat_kerusakan'] ?? 'Ringan';
    $tgl_cek = $_POST['tgl_cek'] ?? date('Y-m-d');
    $estimasi_biaya = $_POST['estimasi_biaya'] ?? 0;
    $deskripsi_kerusakan = $_POST['deskripsi_kerusakan'] ?? '';

    // Upload file gambar bukti kerusakan
    $gambar_data = null;
    if (isset($_FILES['gambar_kerusakan']) && $_FILES['gambar_kerusakan']['error'] === UPLOAD_ERR_OK) {
        $gambar_data = file_get_contents($_FILES['gambar_kerusakan']['tmp_name']);
    }

    // Ambil batas max_allowed_packet dari MySQL untuk mencegah crash "MySQL server has gone away"
    $max_packet_size = 1048576; // Default fallback 1MB
    try {
        $stmtPacket = $db->query("SELECT @@global.max_allowed_packet");
        if ($stmtPacket) {
            $max_packet_size = (int)$stmtPacket->fetchColumn();
        }
    } catch (Exception $ex) {
        // Abaikan jika gagal membaca variabel global
    }

    // Validasi ukuran gambar agar tidak melebihi kapasitas paket MySQL
    if ($gambar_data !== null && strlen($gambar_data) >= ($max_packet_size - 10240)) {
        $_SESSION['error'] = "Gagal: Ukuran gambar bukti kerusakan terlalu besar (" . number_format(strlen($gambar_data) / 1048576, 2) . " MB) untuk konfigurasi database Anda. Silakan kompres gambar atau upload gambar di bawah " . number_format($max_packet_size / 1048576, 1) . " MB.";
        header("Location: index.php?page=home_lapangan&action=form_cek&id=" . $id_pengembalian);
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. Simpan laporan ke tabel kondisi_mobil
        $stmtInsert = $db->prepare("
            INSERT INTO kondisi_mobil (id_mobil, tgl_dilaporkan, deskripsi_kerusakan, tingkat_kerusakan, gambar_kerusakan, estimasi_biaya, tgl_selesai)
            VALUES (?, ?, ?, ?, ?, ?, NULL)
        ");
        $stmtInsert->execute([
            $id_mobil,
            $tgl_cek . ' ' . date('H:i:s'),
            $deskripsi_kerusakan,
            $tingkat_kerusakan,
            $gambar_data,
            $estimasi_biaya
        ]);

        // 2. Ubah status mobil di tabel mobil menjadi Maintenance
        $stmtUpdateMobil = $db->prepare("UPDATE mobil SET status_mobil = 'Maintenance' WHERE id_mobil = ?");
        $stmtUpdateMobil->execute([$id_mobil]);

        // 3. Hapus data antrean dari tabel pengembalian agar tidak muncul lagi di antrean cek
        $stmtDelPengembalian = $db->prepare("DELETE FROM pengembalian WHERE id_pengembalian = ?");
        $stmtDelPengembalian->execute([$id_pengembalian]);

        $db->commit();
        $_SESSION['success'] = "Laporan kerusakan berhasil disimpan. Status armada diubah menjadi Maintenance.";
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            try {
                $db->rollBack();
            } catch (Exception $rollbackException) {
                // Abaikan jika rollback gagal karena koneksi database terputus
            }
        }
        $_SESSION['error'] = "Gagal memproses data kondisi: " . $e->getMessage();
    }

    header("Location: index.php?page=home_lapangan&action=data_kerusakan");
    exit;
}

if ($action === 'proses_edit_kondisi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kondisi = filter_var($_POST['id_kondisi'] ?? 0, FILTER_VALIDATE_INT);
    $estimasi_biaya = $_POST['estimasi_biaya'] ?? 0;
    $tgl_selesai = !empty($_POST['tgl_selesai']) ? date('Y-m-d H:i:s', strtotime($_POST['tgl_selesai'])) : null;

    try {
        $stmtUpdateKondisi = $db->prepare("
            UPDATE kondisi_mobil 
            SET estimasi_biaya = ?, tgl_selesai = ? 
            WHERE id_kondisi = ?
        ");
        $stmtUpdateKondisi->execute([$estimasi_biaya, $tgl_selesai, $id_kondisi]);
        $_SESSION['success'] = "Estimasi biaya perbaikan dan tanggal selesai berhasil diperbarui.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal memperbarui data: " . $e->getMessage();
    }

    header("Location: index.php?page=home_lapangan&action=data_kerusakan");
    exit;
}

// Intersepsi logika Selesai melalui aksi terdaftar agar tidak melanggar aturan rute index.php
if ($action === 'data_kerusakan' && isset($_GET['selesai_id'])) {
    $id_kondisi = filter_var($_GET['selesai_id'] ?? 0, FILTER_VALIDATE_INT);
    try {
        $db->beginTransaction();

        // 1. Dapatkan id_mobil terlebih dahulu sebelum data kondisi dihapus
        $stmtGetMobil = $db->prepare("SELECT id_mobil FROM kondisi_mobil WHERE id_kondisi = ?");
        $stmtGetMobil->execute([$id_kondisi]);
        $id_mobil = $stmtGetMobil->fetchColumn();

        if ($id_mobil) {
            // 2. Kembalikan status mobil di tabel mobil menjadi Tersedia (masuk kembali ke gallery)
            $stmtUpdateMobil = $db->prepare("UPDATE mobil SET status_mobil = 'Tersedia' WHERE id_mobil = ?");
            $stmtUpdateMobil->execute([$id_mobil]);

            // 3. Hapus data dari tabel kondisi_mobil
            $stmtDeleteKondisi = $db->prepare("DELETE FROM kondisi_mobil WHERE id_kondisi = ?");
            $stmtDeleteKondisi->execute([$id_kondisi]);

            $db->commit();
            $_SESSION['success'] = "Perawatan mobil selesai. Status armada kembali Tersedia.";
        } else {
            throw new Exception("Data kondisi mobil tidak ditemukan.");
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            try {
                $db->rollBack();
            } catch (Exception $rollbackException) {
                // Abaikan jika rollback gagal karena koneksi database terputus
            }
        }
        $_SESSION['error'] = "Gagal memproses penyelesaian perawatan: " . $e->getMessage();
    }

    header("Location: index.php?page=home_lapangan&action=data_kerusakan");
    exit;
}
// --- END PROSES ACTION HANDLER ---

// Query ringkasan status armada - Menggunakan Prepared Statement demi keamanan ke depan
$ready_count = 0; $maintenance_count = 0; $disewa_count = 0;
try {
    // Jika sistem SIREMO membagi armada per cabang, tambahkan klausul WHERE id_lokasi = ?
    $stmtReady = $db->prepare("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Tersedia'");
    $stmtReady->execute();
    $ready_count = $stmtReady->fetchColumn();

    $stmtMaint = $db->prepare("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Maintenance'");
    $stmtMaint->execute();
    $maintenance_count = $stmtMaint->fetchColumn();

    $stmtSewa = $db->prepare("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Disewa'");
    $stmtSewa->execute();
    $disewa_count = $stmtSewa->fetchColumn();
} catch (Exception $e) {
    // Tampilkan log error internal jika dalam mode development
    error_log("Database Error pada Summary Dashboard: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Staff Lapangan - SIREMO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden text-sm">

<?php include __DIR__ . '/../sidebar/stafflapanganside.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8 flex-shrink-0">
        <h1 class="text-base font-bold text-gray-800 uppercase flex items-center gap-2">
            <i class="fa-solid fa-screwdriver-wrench text-indigo-600"></i>
            <span>Cabang Petugas Lapangan #<?= htmlspecialchars($id_lokasi_cabang ?? '-'); ?></span>
        </h1>
        <div class="text-xs font-bold text-indigo-600">Petugas: <span class="text-indigo-600 font-bold"><?= htmlspecialchars($staff_name); ?></span></div>
    </header>

    <main class="flex-1 overflow-y-auto p-6">

        <!-- Pesan Sesi Berhasil / Gagal -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-4 font-bold border border-green-200">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4 font-bold border border-red-200">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- ==================== HOME LAPANGAN ==================== -->
        <?php if ($action === 'home'): 
            // --- QUERY METRIK DASHBOARD LAPANGAN ---
            // 1. Antrean Handover
            $stmtCountH = $db->prepare("
                SELECT COUNT(*) 
                FROM penyewaan p 
                LEFT JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan
                WHERE p.status_penyewaan IN ('Confirmed', 'Pending') AND pen.id_penyerahan IS NULL
            ");
            $stmtCountH->execute();
            $count_handover = $stmtCountH->fetchColumn() ?: 0;

            // 2. Antrean Return
            $stmtCountR = $db->prepare("
                SELECT COUNT(*) 
                FROM penyerahan pen
                WHERE pen.status_sewa = 'ongoing'
            ");
            $stmtCountR->execute();
            $count_return = $stmtCountR->fetchColumn() ?: 0;

            // 3. Antrean Cek Kondisi
            $stmtCountC = $db->prepare("
                SELECT COUNT(*) 
                FROM pengembalian pg
                JOIN penyerahan pen ON pg.id_penyerahan = pen.id_penyerahan
                JOIN penyewaan pny ON pen.id_penyewaan = pny.id_penyewaan
            ");
            $stmtCountC->execute();
            $count_cek = $stmtCountC->fetchColumn() ?: 0;

            // 4. Mobil Diperbaiki (kondisi_mobil)
            $stmtCountKM = $db->prepare("SELECT COUNT(*) FROM kondisi_mobil");
            $stmtCountKM->execute();
            $count_kondisi = $stmtCountKM->fetchColumn() ?: 0;

            // 5. Riwayat Antrean Handover Terbaru (Maksimal 3)
            $stmtRecentH = $db->prepare("
                SELECT p.*, m.merk_mobil, m.plat_nomor, pl.nama_lengkap 
                FROM penyewaan p 
                JOIN mobil m ON p.id_mobil = m.id_mobil 
                JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                LEFT JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan
                WHERE p.status_penyewaan IN ('Confirmed', 'Pending') AND pen.id_penyerahan IS NULL
                ORDER BY p.id_penyewaan DESC
                LIMIT 3
            ");
            $stmtRecentH->execute();
            $recent_handovers = $stmtRecentH->fetchAll(PDO::FETCH_ASSOC);

            // 6. Riwayat Antrean Cek Terbaru (Maksimal 3)
            $stmtRecentC = $db->prepare("
                SELECT pg.id_pengembalian, m.merk_mobil, m.plat_nomor, pl.nama_lengkap 
                FROM pengembalian pg
                JOIN penyerahan pen ON pg.id_penyerahan = pen.id_penyerahan
                JOIN penyewaan pny ON pen.id_penyewaan = pny.id_penyewaan
                JOIN mobil m ON pny.id_mobil = m.id_mobil
                JOIN pelanggan pl ON pny.id_pelanggan = pl.id_pelanggan
                ORDER BY pg.id_pengembalian DESC
                LIMIT 3
            ");
            $stmtRecentC->execute();
            $recent_checks = $stmtRecentC->fetchAll(PDO::FETCH_ASSOC);
        ?>
            <!-- TATA LETAK CARD DASHBOARD UTAMA -->
            <div class="mb-6">
                <div class="mb-5">
                    <h2 class="text-lg font-black text-gray-800">
                        Ringkasan Tugas & Operasional Lapangan
                    </h2>
                    <p class="text-xs text-gray-400 mt-1">
                        Informasi lengkap antrean tugas fisik armada di cabang Anda
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- ARMADA READY -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Armada Ready
                            </p>
                            <p class="text-3xl font-black text-green-600 mt-1">
                                <?= $ready_count; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Unit siap disewa
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </div>
                    </div>

                    <!-- ARMADA MAINTENANCE -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Armada Maintenance
                            </p>
                            <p class="text-3xl font-black text-orange-500 mt-1">
                                <?= $maintenance_count; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Sedang diperbaiki
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-orange-500">
                            <i class="fa-solid fa-screwdriver-wrench text-lg"></i>
                        </div>
                    </div>

                    <!-- ARMADA SEDANG DISEWA -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Sedang Disewa
                            </p>
                            <p class="text-3xl font-black text-indigo-600 mt-1">
                                <?= $disewa_count; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Unit aktif di jalanan
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                            <i class="fa-solid fa-car-side text-lg"></i>
                        </div>
                    </div>

                    <!-- TUGAS HANDOVER -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Tugas Handover
                            </p>
                            <p class="text-3xl font-black text-blue-600 mt-1">
                                <?= $count_handover; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Menunggu serah unit
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-key text-lg"></i>
                        </div>
                    </div>

                    <!-- TUGAS PENGEMBALIAN -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Tugas Pengembalian
                            </p>
                            <p class="text-3xl font-black text-amber-600 mt-1">
                                <?= $count_return; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Unit ongoing aktif
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                            <i class="fa-solid fa-arrow-rotate-left text-lg"></i>
                        </div>
                    </div>

                    <!-- PENGECEKAN BODI -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Pengecekan Bodi
                            </p>
                            <p class="text-3xl font-black text-red-500 mt-1">
                                <?= $count_cek; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Menunggu cek kondisi
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-500">
                            <i class="fa-solid fa-magnifying-glass text-lg"></i>
                        </div>
                    </div>

                    <!-- MOBIL DIPERBAIKI -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Mobil Diperbaiki
                            </p>
                            <p class="text-3xl font-black text-purple-600 mt-1">
                                <?= $count_kondisi; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Tercatat kondisi_mobil
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                            <i class="fa-solid fa-car-burst text-lg"></i>
                        </div>
                    </div>

                    <!-- PETUGAS LAPANGAN -->
                    <div class="bg-indigo-600 text-white p-5 rounded-2xl shadow-md flex items-center justify-between col-span-1 sm:col-span-1 lg:col-span-1">
                        <div>
                            <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-wider">
                                Petugas Lapangan
                            </p>
                            <p class="text-lg font-black mt-2 leading-none">
                                <?= htmlspecialchars(substr($staff_name, 0, 12)); ?>
                            </p>
                            <p class="text-[9px] text-indigo-100 mt-2 uppercase tracking-widest font-semibold italic">
                                Siaga Cabang #<?= htmlspecialchars($id_lokasi_cabang ?? '1'); ?>
                            </p>
                        </div>
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white">
                            <i class="fa-solid fa-user-shield text-base"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AKTIVITAS TUGAS HARIAN LAPANGAN -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- TUGAS SERAH TERIMA (HANDOVER) -->
                <div class="bg-white p-5 rounded-2xl border shadow-sm">
                    <div class="flex items-center justify-between border-b pb-3 mb-4">
                        <h3 class="font-bold text-gray-800 text-xs uppercase flex items-center gap-2">
                            <i class="fa-solid fa-key text-indigo-600"></i>
                            <span>Tugas Serah Terima (Handover)</span>
                        </h3>
                        <a href="index.php?page=home_lapangan&action=serah_mobil" class="text-[10px] font-black text-indigo-600 hover:underline uppercase">Detail &rarr;</a>
                    </div>
                    <div class="space-y-3">
                        <?php if(!empty($recent_handovers)): foreach($recent_handovers as $rh): ?>
                            <div class="p-3 bg-gray-50 rounded-xl border flex justify-between items-center text-xs">
                                <div>
                                    <span class="font-bold text-gray-800 block"><?= htmlspecialchars($rh['merk_mobil']); ?> (<?= htmlspecialchars($rh['plat_nomor']); ?>)</span>
                                    <span class="text-gray-400 block text-[10px] mt-0.5">Penyewa: <strong><?= htmlspecialchars($rh['nama_lengkap']); ?></strong></span>
                                </div>
                                <a href="index.php?page=home_lapangan&action=detail_handover&id=<?= $rh['id_penyewaan']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded-lg text-[10px]">Lanjut</a>
                            </div>
                        <?php endforeach; else: ?>
                            <p class="py-10 text-center text-gray-400 italic text-xs">Tidak ada tugas serah terima unit saat ini.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TUGAS PENGECEKAN BODI (CONDITION CHECKS) -->
                <div class="bg-white p-5 rounded-2xl border shadow-sm">
                    <div class="flex items-center justify-between border-b pb-3 mb-4">
                        <h3 class="font-bold text-gray-800 text-xs uppercase flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass text-indigo-600"></i>
                            <span>Tugas Pengecekan Bodi</span>
                        </h3>
                        <a href="index.php?page=home_lapangan&action=cek_mobil" class="text-[10px] font-black text-indigo-600 hover:underline uppercase">Detail &rarr;</a>
                    </div>
                    <div class="space-y-3">
                        <?php if(!empty($recent_checks)): foreach($recent_checks as $rc): ?>
                            <div class="p-3 bg-gray-50 rounded-xl border flex justify-between items-center text-xs">
                                <div>
                                    <span class="font-bold text-gray-800 block"><?= htmlspecialchars($rc['merk_mobil']); ?> (<?= htmlspecialchars($rc['plat_nomor']); ?>)</span>
                                    <span class="text-gray-400 block text-[10px] mt-0.5">Penyewa: <strong><?= htmlspecialchars($rc['nama_lengkap']); ?></strong></span>
                                </div>
                                <a href="index.php?page=home_lapangan&action=form_cek&id=<?= $rc['id_pengembalian']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded-lg text-[10px]">Cek Fisik</a>
                            </div>
                        <?php endforeach; else: ?>
                            <p class="py-10 text-center text-gray-400 italic text-xs">Tidak ada antrean pengecekan fisik unit saat ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- PANDUAN HARIAN LAPANGAN -->
            <div class="p-5 bg-white border rounded-2xl shadow-sm">
                <h3 class="font-bold text-gray-900 text-xs mb-2 uppercase flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-indigo-600"></i>
                    <span>Panduan Handover & Return</span>
                </h3>
                <ul class="text-xs text-gray-500 space-y-1.5 list-inside list-decimal">
                    <li>Cocokkan nomor bodi & pelat nomor kendaraan sebelum kunci dilepas.</li>
                    <li>Ambil foto bersama pelanggan dan unit mobil di area depan kantor cabang.</li>
                    <li>Gunakan diagram interaktif di halaman cek kondisi untuk menandai goresan bodi.</li>
                </ul>
            </div>

        <!-- ==================== 1. SERAH MOBIL (HANDOVER) ==================== -->
        <?php elseif ($action === 'serah_mobil'): ?>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <h3 class="font-bold text-gray-800 text-sm mb-4 border-b pb-2">Antrean Serah Terima Kunci (Handover)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-500 border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="p-4 font-bold">Mobil</th>
                                <th class="p-4 font-bold">Pelanggan</th>
                                <th class="p-4 font-bold">Tanggal Sewa</th>
                                <th class="p-4 text-center font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmtH = $db->prepare("
                                SELECT p.*, m.merk_mobil, m.plat_nomor, pl.nama_lengkap, pl.no_telp 
                                FROM penyewaan p 
                                JOIN mobil m ON p.id_mobil = m.id_mobil 
                                JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                                LEFT JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan
                                WHERE p.status_penyewaan IN ('Confirmed', 'Pending') AND pen.id_penyerahan IS NULL
                            ");
                            $stmtH->execute();
                            $handovers = $stmtH->fetchAll(PDO::FETCH_ASSOC);

                            if(!empty($handovers)): foreach($handovers as $h): ?>
                                <tr class="hover:bg-gray-50/50">
                                    <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($h['merk_mobil']); ?> (<?= htmlspecialchars($h['plat_nomor']); ?>)</td>
                                    <td class="p-4"><?= htmlspecialchars($h['nama_lengkap']); ?> (<?= htmlspecialchars($h['no_telp'] ?? '-'); ?>)</td>
                                    <td class="p-4"><?= $h['tgl_mulai_sewa']; ?> s.d <?= $h['tgl_selesai_sewa']; ?></td>
                                    <td class="p-4">
                                        <div class="flex flex-col items-stretch gap-2 max-w-[160px] mx-auto">
                                            <a href="index.php?page=home_lapangan&action=detail_handover&id=<?= $h['id_penyewaan']; ?>"
                                               class="text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl transition shadow text-xs">
                                                Detail
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" class="p-8 text-center italic text-gray-400">Tidak ada antrean serah terima kunci.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- FORM DETAIL HANDOVER -->
        <?php elseif ($action === 'form_handover'): 
            $id_penyewaan = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
            $stmtD = $db->prepare("SELECT p.*, m.merk_mobil, m.plat_nomor, pl.nama_lengkap, pl.no_telp FROM penyewaan p JOIN mobil m ON p.id_mobil = m.id_mobil JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan WHERE p.id_penyewaan = ?");
            $stmtD->execute([$id_penyewaan]);
            $d = $stmtD->fetch(PDO::FETCH_ASSOC);
        ?>
            <div class="max-w-xl bg-white p-6 rounded-2xl border shadow-sm space-y-4 mx-auto">
                <div class="flex items-center justify-between border-b pb-2">
                    <h3 class="font-bold text-gray-800 text-sm">Formulir Serah Terima Mobil</h3>
                    <a href="index.php?page=home_lapangan&action=detail_handover&id=<?= $id_penyewaan; ?>" class="text-xs font-bold text-indigo-600 hover:underline">&larr; Kembali</a>
                </div>
                <form action="index.php?page=home_lapangan&action=proses_handover" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="id_penyewaan" value="<?= $id_penyewaan; ?>">
                    
                    <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 p-4 rounded-xl">
                        <div>
                            <span class="text-gray-400 block font-bold">Nama Peminjam:</span>
                            <span class="font-bold text-gray-800"><?= htmlspecialchars($d['nama_lengkap'] ?? ''); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-bold">No Telp Peminjam:</span>
                            <span class="font-bold text-gray-800"><?= htmlspecialchars($d['no_telp'] ?? '-'); ?></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Penyerahan</label>
                            <input type="date" name="tgl_penyerahan" value="<?= date('Y-m-d'); ?>" required class="w-full border p-2.5 rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Waktu Penyerahan Unit</label>
                            <input type="time" name="jam_penyerahan" value="<?= date('H:i'); ?>" required class="w-full border p-2.5 rounded-xl text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Nama Petugas Lapangan</label>
                        <input type="text" name="nama_petugas" value="<?= htmlspecialchars($staff_name); ?>" readonly class="w-full border p-2.5 rounded-xl text-xs bg-gray-50 text-gray-400">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Upload Foto Bukti bersama Pelanggan</label>
                        <input type="file" name="bukti_serah" accept="image/*" required class="w-full border p-2.5 rounded-xl text-xs bg-gray-50">
                    </div>

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow">
                        Kirim & Serahkan Kunci
                    </button>
                </form>
            </div>

        <!-- HALAMAN DETAIL BOOKING -->
        <?php elseif ($action === 'detail_handover'):
            $id_penyewaan = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
            $stmtDt = $db->prepare("
                SELECT p.*, m.merk_mobil, m.plat_nomor, m.tahun, m.warna, m.cc, m.harga_dinamis,
                       pl.nama_lengkap, pl.email, pl.no_telp, pl.alamat, pl.no_ktp,
                       f.nama_fasilitas, f.harga AS harga_fasilitas
                FROM penyewaan p
                JOIN mobil m ON p.id_mobil = m.id_mobil
                JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                LEFT JOIN fasilitas f ON p.id_fasilitas = f.id_fasilitas
                WHERE p.id_penyewaan = ?
            ");
            $stmtDt->execute([$id_penyewaan]);
            $dt = $stmtDt->fetch(PDO::FETCH_ASSOC);
        ?>
            <div class="max-w-xl bg-white p-6 rounded-2xl border shadow-sm space-y-4 mx-auto">
                <div class="flex items-center justify-between border-b pb-2">
                    <h3 class="font-bold text-gray-800 text-sm">Detail Booking — <?= htmlspecialchars($dt['kode_penyewaan'] ?? ''); ?></h3>
                    <a href="index.php?page=home_lapangan&action=serah_mobil" class="text-xs font-bold text-indigo-600 hover:underline">&larr; Kembali</a>
                </div>
                <?php if ($dt): ?>
                    <div>
                        <p class="font-bold text-gray-800 uppercase text-[11px] mb-2">Data Pelanggan</p>
                        <div class="grid grid-cols-2 gap-y-1.5 text-xs">
                            <p class="text-gray-400">Nama</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($dt['nama_lengkap']); ?></p>
                            <p class="text-gray-400">No. Telp</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($dt['no_telp'] ?? '-'); ?></p>
                            <p class="text-gray-400">Email</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($dt['email'] ?? '-'); ?></p>
                            <p class="text-gray-400">Alamat</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($dt['alamat'] ?? '-'); ?></p>
                        </div>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 uppercase text-[11px] mb-2 border-t pt-3">Data Mobil</p>
                        <div class="grid grid-cols-2 gap-y-1.5 text-xs">
                            <p class="text-gray-400">Mobil</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($dt['merk_mobil']); ?> (<?= htmlspecialchars($dt['plat_nomor']); ?>)</p>
                            <p class="text-gray-400">Tahun / Warna</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($dt['tahun']); ?> / <?= htmlspecialchars($dt['warna']); ?></p>
                            <p class="text-gray-400">Fasilitas</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($dt['nama_fasilitas'] ?? 'Tidak ada'); ?></p>
                            <p class="text-gray-400">Total Harga</p><p class="font-bold text-indigo-600">Rp <?= number_format((float)$dt['total_harga'], 0, ',', '.'); ?></p>
                        </div>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 uppercase text-[11px] mb-2 border-t pt-3">Tanggal Penyewaan</p>
                        <div class="grid grid-cols-2 gap-y-1.5 text-xs">
                            <p class="text-gray-400">Tanggal Mulai</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($dt['tgl_mulai_sewa'] ?? '-'); ?></p>
                            <p class="text-gray-400">Tanggal Selesai</p><p class="font-semibold text-gray-800"><?= htmlspecialchars($dt['tgl_selesai_sewa'] ?? '-'); ?></p>
                        </div>
                    </div>
                    <a href="index.php?page=home_lapangan&action=form_handover&id=<?= $id_penyewaan; ?>" class="block text-center w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow">
                        Lanjut Serahkan Kunci
                    </a>
                <?php else: ?>
                    <p class="text-xs text-gray-400 italic">Data tidak ditemukan.</p>
                <?php endif; ?>
            </div>

        <!-- ==================== 2. DATA DI SEWA (RETURN) ==================== -->
        <?php elseif ($action === 'di_sewa'): ?>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <h3 class="font-bold text-gray-800 text-sm mb-4 border-b pb-2">Daftar Mobil Sedang Di Sewa (Ongoing)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-500 border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="p-4 font-bold">Mobil</th>
                                <th class="p-4 font-bold">Pelanggan</th>
                                <th class="p-4 font-bold">Tanggal Handover</th>
                                <th class="p-4 text-center font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmtS = $db->prepare("
                                SELECT pen.*, p.id_penyewaan, m.merk_mobil, m.plat_nomor, pl.nama_lengkap 
                                FROM penyerahan pen
                                JOIN penyewaan p ON pen.id_penyewaan = p.id_penyewaan 
                                JOIN mobil m ON p.id_mobil = m.id_mobil 
                                JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                                WHERE pen.status_sewa = 'ongoing'
                            ");
                            $stmtS->execute();
                            $ongoings = $stmtS->fetchAll(PDO::FETCH_ASSOC);

                            if(!empty($ongoings)): foreach($ongoings as $o): ?>
                                <tr>
                                    <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($o['merk_mobil']); ?> (<?= htmlspecialchars($o['plat_nomor']); ?>)</td>
                                    <td class="p-4"><?= htmlspecialchars($o['nama_lengkap']); ?></td>
                                    <td class="p-4"><?= $o['tgl_penyerahan']; ?> (<?= $o['jam_penyerahan']; ?>)</td>
                                    <td class="p-4 text-center">
                                        <a href="index.php?page=home_lapangan&action=form_return&id=<?= $o['id_penyerahan']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl transition shadow inline-block">Unit Kembali</a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" class="p-8 text-center italic text-gray-400">Tidak ada armada mobil sedang disewa.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- FORM RETURN PENGEMBALIAN -->
        <?php elseif ($action === 'form_return'):
            $id_penyerahan = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
$stmtRet = $db->prepare("
        SELECT
            pen.*,
            m.merk_mobil,
            m.plat_nomor,
            pl.nama_lengkap,
            p.tgl_mulai_sewa,
            p.tgl_selesai_sewa
        FROM penyerahan pen
        JOIN penyewaan p ON pen.id_penyewaan = p.id_penyewaan
        JOIN mobil m ON p.id_mobil = m.id_mobil
        JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
        WHERE pen.id_penyerahan = ?
    ");
    
    $stmtRet->execute([$id_penyerahan]);
    $r = $stmtRet->fetch(PDO::FETCH_ASSOC);
        ?>
            <div class="max-w-xl bg-white p-6 rounded-2xl border shadow-sm space-y-4 mx-auto">
                <h3 class="font-bold text-gray-800 text-sm border-b pb-2">Formulir Penerimaan Pengembalian Mobil</h3>
                <form action="index.php?page=home_lapangan&action=proses_return" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="id_penyerahan" value="<?= $id_penyerahan; ?>">
                    
                    <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 p-4 rounded-xl">
                        <div>
                            <span class="text-gray-400 block font-bold">Nama Peminjam:</span>
                            <span class="font-bold text-gray-800"><?= htmlspecialchars($r['nama_lengkap'] ?? ''); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-bold">Armada Mobil:</span>
                            <span class="font-bold text-gray-800"><?= htmlspecialchars($r['merk_mobil'] ?? ''); ?> (<?= htmlspecialchars($r['plat_nomor'] ?? ''); ?>)</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-bold">Tanggal Mulai</span>
                            <span class="font-bold text-gray-800"><?= htmlspecialchars($r['tgl_mulai_sewa'] ?? ''); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-bold">Tanggal Selesai</span>
                            <span class="font-bold text-gray-800"><?= htmlspecialchars($r['tgl_selesai_sewa'] ?? ''); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-bold">Nama Karyawan:</span>
                            <span class="font-bold text-gray-800"><?= htmlspecialchars($staff_name); ?></span>
                        </div>
                        <div>
                            <label class="text-gray-400 block font-bold mb-1">Tanggal Pengembalian</label>
                            <input type="date" name="tgl_pengembalian" value="<?= date('Y-m-d'); ?>" required class="w-full border p-2.5 rounded-xl text-xs bg-white">
                        </div>
                        <div>
                            <label class="text-gray-400 block font-bold mb-1">Waktu Pengembalian</label>
                            <input type="time" name="jam_pengembalian" value="<?= date('H:i'); ?>" required class="w-full border p-2.5 rounded-xl text-xs bg-white">
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition">Konfirmasi Penerimaan</button>
                        <a href="index.php?page=home_lapangan&action=di_sewa" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition flex items-center justify-center">Batal</a>
                    </div>
                </form>
            </div>

<?php elseif ($action === 'cek_mobil'): ?>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <h3 class="font-bold text-gray-800 text-sm mb-4 border-b pb-2">Pilih Unit Pengembalian untuk Dicek</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-500 border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="p-4 font-bold">Mobil</th>
                                <th class="p-4 font-bold">Nama Pelanggan</th>
                                <th class="p-4 font-bold">Tgl Selesai Sewa</th>
                                <th class="p-4 text-center font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mengambil data pengembalian, detail mobil, tgl selesai sewa, dan nama pelanggan
                            $stmtC = $db->prepare("
                                SELECT 
                                    pg.id_pengembalian, 
                                    m.merk_mobil, 
                                    m.plat_nomor, 
                                    pny.tgl_selesai_sewa, 
                                    pl.nama_lengkap AS nama_pelanggan
                                FROM pengembalian pg
                                JOIN penyerahan pen ON pg.id_penyerahan = pen.id_penyerahan
                                JOIN penyewaan pny ON pen.id_penyewaan = pny.id_penyewaan
                                JOIN mobil m ON pny.id_mobil = m.id_mobil
                                JOIN pelanggan pl ON pny.id_pelanggan = pl.id_pelanggan
                            ");
                            $stmtC->execute();
                            $evaluations = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                            if(!empty($evaluations)): foreach($evaluations as $ev): ?>
                                <tr class="border-b hover:bg-gray-50/50 transition">
                                    <td class="p-4 font-bold text-gray-800">
                                        <?= htmlspecialchars($ev['merk_mobil']); ?> (<?= htmlspecialchars($ev['plat_nomor']); ?>)
                                    </td>
                                    <td class="p-4 font-medium text-gray-700">
                                        <?= htmlspecialchars($ev['nama_pelanggan']); ?>
                                    </td>
                                    <td class="p-4 text-gray-600">
                                        <?= htmlspecialchars($ev['tgl_selesai_sewa']); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="index.php?page=home_lapangan&action=form_cek&id=<?= $ev['id_pengembalian']; ?>" 
                                           class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl transition shadow inline-block">
                                            Mulai Pengecekan
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="4" class="p-8 text-center italic text-gray-400">
                                        Tidak ada antrean pengecekan kerusakan unit bodi.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- FORM INPUT CEK DETAIL KERUSAKAN -->
<?php elseif ($action === 'form_cek'):
            $id_pengembalian = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
            $stmtEva = $db->prepare("
                SELECT pg.*, m.id_mobil, m.merk_mobil, m.plat_nomor, pl.nama_lengkap, pl.id_level
                FROM pengembalian pg
                JOIN penyerahan pen ON pg.id_penyerahan = pen.id_penyerahan
                JOIN penyewaan pny ON pen.id_penyewaan = pny.id_penyewaan
                JOIN mobil m ON pny.id_mobil = m.id_mobil
                JOIN pelanggan pl ON pny.id_pelanggan = pl.id_pelanggan
                WHERE pg.id_pengembalian = ?
            ");
            $stmtEva->execute([$id_pengembalian]);
            $evData = $stmtEva->fetch(PDO::FETCH_ASSOC);

            $id_level_pelanggan = $evData['id_level'] ?? 1;
        ?>
            <!-- BUNGKUSAN UTAMA GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start w-full">
                
                <!-- KOLOM KIRI: FORMULIR UTAMA (2 Kolom) -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm border-b pb-2">Formulir Laporan Kondisi & Kerusakan Unit</h3>
                    
<!-- PENTING: Menambahkan enctype="multipart/form-data" agar file gambar bisa terkirim -->
                    <form action="index.php?page=home_lapangan&action=proses_cek_kondisi" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="id_pengembalian" value="<?= $id_pengembalian; ?>">
                        <input type="hidden" name="id_mobil" value="<?= htmlspecialchars($evData['id_mobil'] ?? ''); ?>">
                        
                        <!-- Rincian Informasi Kendaraan -->
                        <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 p-4 rounded-xl">
                            <div>
                                <span class="text-gray-400 block font-bold">Nama Peminjam:</span>
                                <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($evData['nama_lengkap'] ?? ''); ?></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block font-bold">Armada Mobil:</span>
                                <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($evData['merk_mobil'] ?? ''); ?> (<?= htmlspecialchars($evData['plat_nomor'] ?? ''); ?>)</span>
                            </div>
                            <div class="col-span-2 border-t pt-2 mt-1">
                                <span class="text-gray-400 block font-bold">Plat Nomor:</span>
                                <span class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($evData['plat_nomor'] ?? ''); ?></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Tingkat Kerusakan</label>
                                <select name="tingkat_kerusakan" id="tingkat_kerusakan" class="w-full border p-2.5 rounded-xl text-xs bg-white focus:outline-indigo-500 font-medium">
                                    <option value="Ringan" selected>Ringan (Default)</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Parah">Parah</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Pengecekan</label>
                                <input type="date" name="tgl_cek" value="<?= date('Y-m-d'); ?>" required class="w-full border p-2.5 rounded-xl text-xs bg-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Estimasi Biaya</label>
                                <div class="flex items-center border rounded-xl text-xs bg-white focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 overflow-hidden">
                                    <span class="bg-gray-100 px-3 py-2.5 text-gray-500 border-r select-none font-medium">
                                        Rp.
                                    </span>
                                    <input type="number" name="estimasi_biaya" class="w-full p-2 bg-transparent outline-none border-none focus:ring-0 text-xs" placeholder="0" min="0">
                                </div>
                            </div>
                            
                            <!-- INPUT BARU: Upload Gambar Bukti Kerusakan (Sesuai kolom gambar_kerusakan longblob) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Upload Gambar Bukti Kerusakan</label>
                                <input type="file" name="gambar_kerusakan" accept="image/*" required
                                    class="w-full text-xs border file:mr-4 file:py-2 file:px-4 file:rounded-l-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 rounded-xl bg-white cursor-pointer p-0.5 focus:outline-indigo-500">
                                <p class="text-[10px] text-gray-400 mt-1">*Format: JPG/PNG. Wajib diisi (No Null).</p>
                            </div>
                        </div>

                        <!-- Input Deskripsi Kerusakan (Wajib Terisi / No Null di DB) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Deskripsi Kerusakan</label>
                            <textarea name="deskripsi_kerusakan" id="deskripsi_kerusakan" rows="4" required 
                                class="w-full border p-3 rounded-xl text-xs focus:outline-indigo-500 bg-white" 
                                placeholder="Tulis detail kerusakan di sini"></textarea>
                        </div>

                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow">
                            Kirim Catatan Kerusakan & Simpan Kondisi Mobil
                        </button>
                    </form>
                </div>

            <!-- JAVASCRIPT LOGIC -->
            <script>
                function catatKerusakanVisual(bagian) {
                    const note = prompt("Detail kerusakan bagian " + bagian + ":", "Lecet / Retak");
                    if (note) {
                        visualArray.push(bagian + ": " + note);
                        document.getElementById('catatan_visual').value = visualArray.join(" | ");
                        
                        const tingkatSelect = document.getElementById('tingkat_kerusakan');
                        if(tingkatSelect.value === 'Aman') {
                            tingkatSelect.value = 'Ringan';
                        }
                        
                        renderVisualList();
                    }
                }

                function renderVisualList() {
                    const cont = document.getElementById('visual_notes_list');
                    if (visualArray.length === 0) {
                        cont.innerHTML = '<span class="italic text-gray-400">Belum ada bagian bodi klik yang ditandai rusak.</span>';
                    } else {
                        cont.innerHTML = visualArray.map(n => `<div>● ${n}</div>`).join('');
                    }
                }

                function kalkulasiDenda() {
                    const sel = document.getElementById('id_kerusakan');
                    const harga = parseInt(sel.options[sel.selectedIndex].getAttribute('data-biaya') || 0);

                    document.getElementById('display_kerusakan').innerText = "Rp " + harga.toLocaleString('id-ID');
                    
                    let finalHarga = harga;
                    if (isAsuransiActive) {
                        if(document.getElementById('display_potongan')) {
                            document.getElementById('display_potongan').innerText = "-Rp " + harga.toLocaleString('id-ID');
                        }
                        finalHarga = 0;
                    }

                    document.getElementById('estimasi_biaya').value = finalHarga;
                    document.getElementById('display_total_final').innerText = "Rp " + finalHarga.toLocaleString('id-ID');
                }

                function kondisiChanged() {
                    const kond = document.getElementById('tingkat_kerusakan').value;
                    if (kond === 'Aman') {
                        document.getElementById('id_kerusakan').value = "0";
                        visualArray = [];
                        document.getElementById('catatan_visual').value = "";
                        renderVisualList();
                        kalkulasiDenda();
                    }
                }
            </script>

        <!-- ==================== DATA JENIS KERUSAKAN REFERENSI ==================== -->
        <?php elseif ($action === 'data_kerusakan'): ?>
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 border-b text-gray-700 font-bold uppercase">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Armada Mobil</th>
                            <th class="px-6 py-4">Detail Kerusakan</th>
                            <th class="px-6 py-4">Tingkat</th>
                            <th class="px-6 py-4">Tanggal Dilaporkan</th>
                            <th class="px-6 py-4">Estimasi Biaya</th>
                            <th class="px-6 py-4">Tanggal Selesai</th>
                            <th class="px-6 py-4">Status Mobil</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php
                        try {
                            // Mengambil data dari tabel kondisi_mobil sesuai skema database kamu
                            $stmtKrs = $db->query("
                                SELECT k.*, m.merk_mobil, m.plat_nomor, m.status_mobil 
                                FROM kondisi_mobil k
                                JOIN mobil m ON k.id_mobil = m.id_mobil
                                ORDER BY k.id_kondisi ASC
                            ");
                            $krsData = $stmtKrs->fetchAll(PDO::FETCH_ASSOC);

                            if (!empty($krsData)):
                                foreach($krsData as $krs):

                                    $badgeColor = 'bg-green-100 text-green-800';
                                    if ($krs['tingkat_kerusakan'] === 'Ringan') {
                                        $badgeColor = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($krs['tingkat_kerusakan'] === 'Sedang') {
                                        $badgeColor = 'bg-orange-100 text-orange-800';
                                    } elseif ($krs['tingkat_kerusakan'] === 'Parah') {
                                        $badgeColor = 'bg-red-100 text-red-800';
                                    }
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-gray-600 font-medium"><?= $krs['id_kondisi']; ?></td>
                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        <?= htmlspecialchars($krs['merk_mobil']); ?> (<?= htmlspecialchars($krs['plat_nomor']); ?>)
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 max-w-xs truncate">
                                        <?= htmlspecialchars($krs['deskripsi_kerusakan']); ?>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[10px] <?= $badgeColor; ?>">
                                            <?= htmlspecialchars($krs['tingkat_kerusakan']); ?>
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-gray-600">
                                        <?= htmlspecialchars($krs['tgl_dilaporkan']); ?>
                                    </td>

                                    <td class="px-6 py-4 font-bold text-red-600">
                                        Rp <?= number_format($krs['estimasi_biaya'] ?? 0); ?>
                                    </td>

                                    <td class="px-6 py-4 text-gray-600">
                                        <?= htmlspecialchars($krs['tgl_selesai'] ?? '-'); ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($krs['status_mobil']); ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center space-x-3">
                                            <a href="index.php?page=home_lapangan&action=edit_kondisi&id=<?= $krs['id_kondisi']; ?>" class="text-blue-500 hover:text-blue-700 font-bold" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?page=home_lapangan&action=data_kerusakan&selesai_id=<?= $krs['id_kondisi']; ?>" 
                                               onclick="return confirm('Apakah mobil sudah selesai melakukan perawatan?');" 
                                               class="bg-green-600 hover:bg-green-700 text-white font-bold px-2.5 py-1 rounded-xl text-[10px] transition shadow" 
                                               title="Selesai">
                                                Selesai
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-10 text-center text-gray-400 italic font-medium">
                                        Tidak ada data kondisi & kerusakan mobil yang tersedia saat ini.
                                    </td>
                                </tr>
                            <?php endif;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='9' class='px-6 py-10 text-center text-gray-400 italic'>Gagal mengambil data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>

        <?php elseif($action === 'edit_kondisi'): 
            $id_kondisi = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
            $stmtEva = $db->prepare("
                SELECT k.*, m.merk_mobil, m.plat_nomor 
                FROM kondisi_mobil k
                JOIN mobil m ON k.id_mobil = m.id_mobil
                WHERE k.id_kondisi = ?
            ");
            $stmtEva->execute([$id_kondisi]);
            $evData = $stmtEva->fetch(PDO::FETCH_ASSOC);
        ?>
    <!-- BUNGKUSAN UTAMA -->
    <div class="w-full max-w-3xl mx-auto bg-white p-6 rounded-2xl border shadow-sm space-y-4">
        <h3 class="font-bold text-gray-800 text-sm border-b pb-2">Formulir Penyelesaian & Estimasi Biaya Perbaikan</h3>
        
        <form action="index.php?page=home_lapangan&action=proses_edit_kondisi" method="POST" class="space-y-4">
            <!-- ID Kondisi Hidden untuk klausa WHERE saat update -->
            <input type="hidden" name="id_kondisi" value="<?= htmlspecialchars($evData['id_kondisi'] ?? 0); ?>">
            
            <!-- SECTION 1: DATA BERSIFAT READ-ONLY (DIKUNCI) -->
            <div class="bg-gray-50 p-4 rounded-xl space-y-3 border border-gray-100">
                <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider block">Informasi Kerusakan Lapangan (Read-Only)</span>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Tingkat Kerusakan (Read-Only/Disabled) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Tingkat Kerusakan</label>
                        <select disabled class="w-full border p-2.5 rounded-xl text-xs bg-gray-100 text-gray-500 font-medium cursor-not-allowed">
                            <option value="Ringan" <?= ($evData['tingkat_kerusakan'] ?? '') === 'Ringan' ? 'selected' : ''; ?>>Ringan</option>
                            <option value="Sedang" <?= ($evData['tingkat_kerusakan'] ?? '') === 'Sedang' ? 'selected' : ''; ?>>Sedang</option>
                            <option value="Parah" <?= ($evData['tingkat_kerusakan'] ?? '') === 'Parah' ? 'selected' : ''; ?>>Parah</option>
                        </select>
                    </div>

                    <!-- Tanggal Dilaporkan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal Dilaporkan</label>
                        <input type="text" readonly class="w-full border p-2.5 rounded-xl text-xs bg-gray-100 text-gray-500 cursor-not-allowed font-medium" 
                               value="<?= htmlspecialchars($evData['tgl_dilaporkan'] ?? '-'); ?>">
                    </div>
                </div>

                <!-- Deskripsi Kerusakan (Read-Only) -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Deskripsi Kerusakan</label>
                    <textarea readonly class="w-full border p-3 rounded-xl text-xs bg-gray-100 text-gray-500 cursor-not-allowed" rows="3"><?= htmlspecialchars($evData['deskripsi_kerusakan'] ?? ''); ?></textarea>
                </div>

                <!-- Foto Kerusakan (Hanya Menampilkan Data Biner Gambar dari Database) -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Bukti Gambar Kerusakan</label>
                    <div class="border rounded-xl p-2 bg-white flex justify-center items-center h-48 overflow-hidden">
                        <?php if (!empty($evData['gambar_kerusakan'])): ?>
                            <!-- Menampilkan blob biner ke format src image base64 -->
                            <img src="data:image/jpeg;base64,<?= base64_encode($evData['gambar_kerusakan']); ?>" class="max-h-full object-contain rounded-lg" alt="Foto Kerusakan">
                        <?php else: ?>
                            <span class="text-xs italic text-gray-400">Tidak ada gambar kerusakan yang diunggah.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: INPUT DATA YANG BISA DIUBAH (ESTIMASI & TANGGAL SELESAI) -->
            <div class="grid grid-cols-2 gap-4 pt-2">
                <!-- Estimasi Biaya (Bisa Diubah) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Estimasi Biaya Perbaikan</label>
                    <div class="flex items-center border rounded-xl text-xs bg-white focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 overflow-hidden shadow-sm">
                        <span class="bg-gray-100 px-3 py-2.5 text-gray-500 border-r select-none font-medium">
                            Rp.
                        </span>
                        <input type="number" name="estimasi_biaya" class="w-full p-2 bg-transparent outline-none border-none focus:ring-0 text-xs font-semibold text-gray-800" 
                               value="<?= htmlspecialchars($evData['estimasi_biaya'] ?? 0); ?>" min="0" placeholder="0" required>
                    </div>
                </div>

                <!-- Tanggal Selesai (Bisa Diubah) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Selesai Perbaikan</label>
                    <input type="datetime-local" name="tgl_selesai" class="w-full border p-2.5 rounded-xl text-xs bg-white focus:outline-indigo-500 font-medium text-gray-800 shadow-sm"
                           value="<?= !empty($evData['tgl_selesai']) ? date('Y-m-d\TH:i', strtotime($evData['tgl_selesai'])) : ''; ?>" required>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex space-x-3 pt-4 border-t">
                <a href="index.php?page=home_lapangan&action=home" class="w-1/3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition text-center shadow-sm">
                    Batal
                </a>
                <button type="submit" class="w-2/3 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow">
                    Perbarui Biaya & Tanggal Selesai
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>
    </main>
</div>
</body>
</html>