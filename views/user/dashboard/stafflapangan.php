<?php
// =========================================================================
// FILE: views/user/dashboard/stafflapangan.php (Dashboard Lapangan Komplet)
// =========================================================================

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
$db = Database::getConnection(); // Koneksi basis data menggunakan PDO

$staff_name = $_SESSION['user_name'] ?? 'Staff Lapangan';
$id_lokasi_cabang = $_SESSION['id_lokasi'] ?? null; 
$action = $_GET['action'] ?? 'home';

// Query ringkasan status armada
$ready_count = 0; $maintenance_count = 0; $disewa_count = 0;
try {
    $ready_count = $db->query("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Tersedia'")->fetchColumn();
    $maintenance_count = $db->query("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Maintenance'")->fetchColumn();
    $disewa_count = $db->query("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Disewa'")->fetchColumn();
} catch (Exception $e) {}
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

<?php include __DIR__ . '/../sidebar/stafflapangan.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8 flex-shrink-0">
        <h1 class="text-base font-bold text-gray-800 uppercase flex items-center gap-2">
            <i class="fa-solid fa-screwdriver-wrench text-indigo-600"></i>
            <span>Cabang Petugas Lapangan #<?= $id_lokasi_cabang; ?></span>
        </h1>
        <div class="text-xs font-bold text-indigo-600">Petugas: <span class="text-indigo-600 font-bold"><?= $staff_name; ?></span></div>
    </header>

    <main class="flex-1 overflow-y-auto p-6">

        <!-- Pesan Sesi Berhasil -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-4 font-bold">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- ==================== HOME LAPANGAN ==================== -->
        <?php if ($action === 'home'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                <!-- Card Prioritas Tinggi Tugas Sopir -->
                <div class="bg-indigo-600 text-white p-5 rounded-2xl shadow-lg flex flex-col justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-indigo-200 uppercase italic">Tugas Sopir Prioritas</p>
                        <h2 class="text-xl font-black mt-2">Siaga Driver Cabang</h2>
                        <p class="text-xs text-indigo-100 mt-1">Status: Antar kunci & serah unit ke alamat pelanggan.</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border flex flex-col justify-center shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Armada Ready</p>
                    <p class="text-2xl font-black text-green-600 mt-1"><?= $ready_count; ?></p>
                </div>

                <div class="bg-white p-5 rounded-2xl border flex flex-col justify-center shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Maintenance</p>
                    <p class="text-2xl font-black text-orange-500 mt-1"><?= $maintenance_count; ?></p>
                </div>

                <div class="bg-white p-5 rounded-2xl border flex flex-col justify-center shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Sedang Disewa</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1"><?= $disewa_count; ?></p>
                </div>
            </div>

            <!-- Panduan Serah Terima Kunci -->
            <div class="p-5 bg-white border rounded-2xl">
                <h3 class="font-bold text-gray-900 text-xs mb-2 uppercase">Panduan Handover & Return</h3>
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
                            // PERBAIKAN: Mengubah filter query status_penyewaan ke IN ('Confirmed', 'Pending') agar data simulasi Anda langsung tampil
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
                                    <td class="p-4 font-bold text-gray-800"><?= $h['merk_mobil']; ?> (<?= $h['plat_nomor']; ?>)</td>
                                    <td class="p-4"><?= $h['nama_lengkap']; ?> (<?= $h['no_telp']; ?>)</td>
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
            $id_penyewaan = $_GET['id'] ?? 0;
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
                            <span class="font-bold text-gray-800"><?= $d['nama_lengkap']; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-bold">No Telp Peminjam:</span>
                            <span class="font-bold text-gray-800"><?= $d['no_telp']; ?></span>
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
                        <input type="text" name="nama_petugas" value="<?= $staff_name; ?>" readonly class="w-full border p-2.5 rounded-xl text-xs bg-gray-50 text-gray-400">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Upload Foto Bukti bersama Pelanggan</label>
                        <input type="file" name="bukti_serah" required class="w-full border p-2.5 rounded-xl text-xs bg-gray-50">
                    </div>

                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow">
                        Kirim & Serahkan Kunci
                    </button>
                </form>
            </div>

        <!-- HALAMAN DETAIL BOOKING (READ-ONLY, sebelum serah kunci) -->
        <?php elseif ($action === 'detail_handover'):
            $id_penyewaan = $_GET['id'] ?? 0;
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
                                    <td class="p-4 font-bold text-gray-800"><?= $o['merk_mobil']; ?> (<?= $o['plat_nomor']; ?>)</td>
                                    <td class="p-4"><?= $o['nama_lengkap']; ?></td>
                                    <td class="p-4"><?= $o['tgl_penyerahan']; ?> (<?= $o['jam_penyerahan']; ?>)</td>
                                    <td class="p-4 text-center">
                                        <a href="index.php?page=home_lapangan&action=form_return&id=<?= $o['id_penyerahan']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl transition shadow">Unit Kembali</a>
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
            $id_penyerahan = $_GET['id'] ?? 0;
            $stmtRet = $db->prepare("SELECT pen.*, m.merk_mobil, m.plat_nomor, pl.nama_lengkap FROM penyerahan pen JOIN penyewaan p ON pen.id_penyewaan = p.id_penyewaan JOIN mobil m ON p.id_mobil = m.id_mobil JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan WHERE pen.id_penyerahan = ?");
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
                            <span class="font-bold text-gray-800"><?= $r['nama_lengkap']; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-bold">Armada Mobil:</span>
                            <span class="font-bold text-gray-800"><?= $r['merk_mobil']; ?> (<?= $r['plat_nomor']; ?>)</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-bold">Nama Karyawan:</span>
                            <span class="font-bold text-gray-800"><?= $staff_name; ?></span>
                        </div>
                        <div>
                            <label class="text-gray-400 block font-bold mb-1">Tanggal Pengembalian</label>
                            <input type="date" name="tgl_pengembalian" value="<?= date('Y-m-d'); ?>" required class="w-full border p-2.5 rounded-xl text-xs bg-white">
                        </div>
                        <div>
                            <label class="text-gray-400 block font-bold mb-1">Waktu Pengembalian</label>
                            <input type="time" name="jam_pengembalian" value="<?= date('H:i'); ?>" required class="w-full border p-2.5 rounded-xl text-xs bg-white">
                        </div>
                        <div>
                            <span class="text-gray-400 block font-bold mb-1">Kondisi Mobil</span>
                            <select name="kondisi_mobil_awal" required class="w-full border p-2.5 rounded-xl text-xs bg-white">
                                <option value="Normal">Normal</option>
                                <option value="Rusak">Rusak</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">KM Akhir Kendaraan</label>
                            <input type="number" name="km_akhir" required class="w-full border p-2.5 rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">BBM Akhir Kendaraan</label>
                            <select name="bbm_akhir" required class="w-full border p-2.5 rounded-xl text-xs bg-white">
                                <option value="Penuh">Penuh</option>
                                <option value="3/4">3/4</option>
                                <option value="1/2">1/2</option>
                                <option value="1/4">1/4</option>
                                <option value="Kosong">Kosong</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Foto Kondisi Kendaraan</label>
                        <input type="file" name="foto_kondisi[]" accept="image/*" multiple required class="w-full border p-2.5 rounded-xl text-xs bg-gray-50">
                        <p class="text-[10px] text-gray-400 mt-1">Bisa unggah lebih dari satu foto (tampak depan, belakang, samping, dsb).</p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition">Konfirmasi Penerimaan</button>
                        <a href="index.php?page=home_lapangan&action=di_sewa" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition flex items-center justify-center">Batal</a>
                    </div>
                </form>
            </div>

        <!-- ==================== 3. CEK MOBIL (FORMULIR INPUT KERUSAKAN BARU + BUTTON SUBMIT) ==================== -->
        <?php elseif ($action === 'cek_mobil'): ?>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <h3 class="font-bold text-gray-800 text-sm mb-4 border-b pb-2">Pilih Unit Pengembalian untuk Dicek</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-500 border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="p-4 font-bold">Mobil</th>
                                <th class="p-4 font-bold">KM Akhir</th>
                                <th class="p-4 font-bold">BBM Akhir</th>
                                <th class="p-4 text-center font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ambil pengembalian yang baru diserahkan namun bodi pemeriksaan kerusakannya belum dievaluasi
                            $stmtC = $db->prepare("
                                SELECT pg.*, m.merk_mobil, m.plat_nomor 
                                FROM pengembalian pg
                                JOIN penyerahan pen ON pg.id_penyerahan = pen.id_penyerahan
                                JOIN penyewaan pny ON pen.id_penyewaan = pny.id_penyewaan
                                JOIN mobil m ON pny.id_mobil = m.id_mobil
                                WHERE pg.checklist IS NULL
                            ");
                            $stmtC->execute();
                            $evaluations = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                            if(!empty($evaluations)): foreach($evaluations as $ev): ?>
                                <tr>
                                    <td class="p-4 font-bold text-gray-800"><?= $ev['merk_mobil']; ?> (<?= $ev['plat_nomor']; ?>)</td>
                                    <td class="p-4"><?= number_format($ev['km_akhir']); ?> km</td>
                                    <td class="p-4"><?= $ev['bbm_akhir']; ?></td>
                                    <td class="p-4 text-center">
                                        <a href="index.php?page=home_lapangan&action=form_cek&id=<?= $ev['id_pengembalian']; ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl transition shadow">Mulai Pengecekan</a>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" class="p-8 text-center italic text-gray-400">Tidak ada antrean pengecekan kerusakan unit bodi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- FORM INPUT CEK DETAIL KERUSAKAN & SINKRONISASI KE TINGKAT ASURANSI -->
        <?php elseif ($action === 'form_cek'): 
            $id_pengembalian = $_GET['id'] ?? 0;
            $stmtEva = $db->prepare("
                SELECT pg.*, m.merk_mobil, m.plat_nomor, pl.nama_lengkap, pl.id_level
                FROM pengembalian pg
                JOIN penyerahan pen ON pg.id_penyerahan = pen.id_penyerahan
                JOIN penyewaan pny ON pen.id_penyewaan = pny.id_penyewaan
                JOIN mobil m ON pny.id_mobil = m.id_mobil
                JOIN pelanggan pl ON pny.id_pelanggan = pl.id_pelanggan
                WHERE pg.id_pengembalian = ?
            ");
            $stmtEva->execute([$id_pengembalian]);
            $evData = $stmtEva->fetch(PDO::FETCH_ASSOC);

            // Cek tingkat asuransi: jika level member selain bronze (id_level > 1), asuransi aktif!
            $id_level_pelanggan = $evData['id_level'];
            $asuransi_aktif = ($id_level_pelanggan > 1) ? true : false;
        ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kolom Kiri: Formulir Log Input ke DB -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm border-b pb-2">Formulir Laporan Kerusakan Unit</h3>
                    
                    <form action="index.php?page=home_lapangan&action=proses_cek_kondisi" method="POST" class="space-y-4">
                        <input type="hidden" name="id_pengembalian" value="<?= $id_pengembalian; ?>">
                        <input type="hidden" name="catatan_visual" id="catatan_visual" value="">
                        
                        <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 p-4 rounded-xl">
                            <div>
                                <span class="text-gray-400 block font-bold">Nama Peminjam:</span>
                                <span class="font-bold text-gray-800 text-sm"><?= $evData['nama_lengkap']; ?></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block font-bold">Armada Mobil:</span>
                                <span class="font-bold text-gray-800 text-sm"><?= $evData['merk_mobil']; ?></span>
                            </div>
                        </div>

                        <!-- Check asuransi box -->
                        <?php if ($asuransi_aktif): ?>
                            <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-shield-halved text-green-500"></i>
                                <span>Asuransi Aktif! Denda kerusakan fisik akan otomatis dipotong 100%.</span>
                            </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kondisi Pengembalian</label>
                                <select name="kondisi_mobil" id="kondisi_mobil" onchange="kondisiChanged()" class="w-full border p-2.5 rounded-xl text-xs bg-white">
                                    <option value="Baik">Baik / Mulus</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Pilih Kerusakan Utama</label>
                                <select name="id_kerusakan" id="id_kerusakan" onchange="kalkulasiDenda()" class="w-full border p-2.5 rounded-xl text-xs bg-white">
                                    <option value="0" data-biaya="0">Tidak Ada Kerusakan (Rp 0)</option>
                                    <?php 
                                    $stmtKer = $db->query("SELECT * FROM jenis_kerusakan");
                                    while($k = $stmtKer->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?= $k['id_kerusakan']; ?>" data-biaya="<?= $k['biaya_perbaikan']; ?>"><?= $k['nama_kerusakan']; ?> (Rp <?= number_format($k['biaya_perbaikan']); ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Output Perhitungan denda dan asuransi -->
                        <div class="bg-gray-50 p-4 rounded-xl text-xs space-y-2">
                            <div class="flex justify-between">
                                <span>Estimasi Biaya Perbaikan:</span>
                                <span id="display_kerusakan" class="font-bold text-gray-700">Rp 0</span>
                            </div>
                            <?php if ($asuransi_aktif): ?>
                                <div class="flex justify-between text-green-600">
                                    <span>Bypass Potongan Asuransi:</span>
                                    <span id="display_potongan" class="font-bold">-Rp 0</span>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between border-t pt-2 text-red-600 font-bold">
                                <span>TOTAL BIAYA DENDA YANG HARUS DIBAYAR:</span>
                                <input type="hidden" name="biaya_kerusakan" id="biaya_kerusakan_val" value="0">
                                <input type="hidden" name="denda_telat" value="0">
                                <span id="display_total_final">Rp 0</span>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow">
                            Kirim Catatan Kerusakan & Selesaikan Cek
                        </button>
                    </form>
                </div>

                <!-- Kolom Kanan: Diagram Bodi Klik Visual -->
                <div class="bg-white p-5 rounded-2xl border shadow-sm h-fit">
                    <h4 class="font-bold text-gray-800 text-xs mb-3 border-b pb-2 uppercase">Klik Bagian Bodi Mobil untuk Mencatat Kerusakan</h4>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <button onclick="catatKerusakanVisual('Kaca Depan')" class="bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs p-2.5 rounded-xl font-bold hover:bg-indigo-100 transition">Kaca Depan</button>
                        <button onclick="catatKerusakanVisual('Bamper Depan')" class="bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs p-2.5 rounded-xl font-bold hover:bg-indigo-100 transition">Bamper Depan</button>
                        <button onclick="catatKerusakanVisual('Pintu Samping')" class="bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs p-2.5 rounded-xl font-bold hover:bg-indigo-100 transition">Pintu Samping</button>
                        <button onclick="catatKerusakanVisual('Ban Mobil')" class="bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs p-2.5 rounded-xl font-bold hover:bg-indigo-100 transition">Ban Mobil</button>
                    </div>
                    <div class="bg-amber-50 p-4 border border-amber-200 rounded-xl text-xs text-amber-700">
                        <h5 class="font-bold mb-1">Catatan Visual Hasil Klik:</h5>
                        <div id="visual_notes_list" class="space-y-1">
                            <span class="italic text-gray-400">Belum ada bagian bodi klik yang ditandai rusak.</span>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                const isAsuransiActive = <?= $asuransi_aktif ? 'true' : 'false'; ?>;
                let visualArray = [];

                function catatKerusakanVisual(bagian) {
                    const note = prompt("Detail kerusakan bagian " + bagian + ":", "Lecet / Retak");
                    if (note) {
                        visualArray.push(bagian + ": " + note);
                        document.getElementById('catatan_visual').value = visualArray.join(" | ");
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
                        document.getElementById('display_potongan').innerText = "-Rp " + harga.toLocaleString('id-ID');
                        finalHarga = 0; // Bypass asuransi aktif denda kerusakan terpotong 100%
                    }

                    document.getElementById('biaya_kerusakan_val').value = finalHarga;
                    document.getElementById('display_total_final').innerText = "Rp " + finalHarga.toLocaleString('id-ID');
                }

                function kondisiChanged() {
                    const kond = document.getElementById('kondisi_mobil').value;
                    if (kond === 'Baik') {
                        document.getElementById('id_kerusakan').value = "0";
                        kalkulasiDenda();
                    }
                }
            </script>

        <!-- ==================== DATA JENIS KERUSAKAN REFERENSI ==================== -->
        <?php elseif ($action === 'data_kerusakan'): ?>
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-700">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Jenis Kerusakan</th>
                            <th class="px-6 py-4 text-right">Estimasi Biaya Perbaikan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php
                        try {
                            $stmtKrs = $db->query("SELECT * FROM jenis_kerusakan ORDER BY id_kerusakan ASC");
                            while($krs = $stmtKrs->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td class="px-6 py-4"><?= $krs['id_kerusakan']; ?></td>
                                <td class="px-6 py-4 font-bold text-gray-800"><?= $krs['nama_kerusakan']; ?></td>
                                <td class="px-6 py-4 text-right font-semibold text-red-600">Rp <?= number_format($krs['biaya_perbaikan']); ?></td>
                            </tr>
                            <?php endwhile;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='3' class='px-6 py-10 text-center text-gray-400 italic'>Gagal mengambil data: " . $e->getMessage() . "</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>