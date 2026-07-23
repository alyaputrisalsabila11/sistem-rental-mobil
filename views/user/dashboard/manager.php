<?php
// =========================================================================
// FILE: views/user/dashboard/manager.php (Dashboard & Tabel Data Manager)
// =========================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menghubungkan ke file inisialisasi koneksi database utama
require_once __DIR__ . '/../../../init.php';
$db = Database::getConnection(); 

$action = $_GET['action'] ?? 'home';

// DETEKSI KOLOM TABEL VOUCHER SECARA DINAMIS HARGA POIN & TANGGAL UNTUK MENCEGAH WARNING UNDEFINED ARRAY KEY
$voucher_cols_query = $db->query("DESCRIBE voucher");
$v_cols = $voucher_cols_query->fetchAll(PDO::FETCH_COLUMN);

// 1. Deteksi kolom harga poin secara cerdas (mencari substring 'poin' atau 'point')
$col_harga_poin = 'harga_poin';
foreach ($v_cols as $col) {
    if (stripos($col, 'poin') !== false || stripos($col, 'point') !== false) {
        $col_harga_poin = $col;
        break;
    }
}
if ($col_harga_poin === 'harga_poin') {
    foreach ($v_cols as $col) {
        if (stripos($col, 'harga') !== false) {
            $col_harga_poin = $col;
            break;
        }
    }
}

// 2. Deteksi kolom tgl mulai secara cerdas (mencari substring 'mulai', 'awal', atau 'start')
$col_tgl_mulai = 'tgl_mulai';
foreach ($v_cols as $col) {
    if (stripos($col, 'mulai') !== false || stripos($col, 'awal') !== false || stripos($col, 'start') !== false) {
        $col_tgl_mulai = $col;
        break;
    }
}

// 3. Deteksi kolom tgl selesai secara cerdas (mencari substring 'selesai', 'akhir', 'expired', 'end', atau 'habis')
$col_tgl_selesai = 'tgl_selesai';
foreach ($v_cols as $col) {
    if (stripos($col, 'selesai') !== false || stripos($col, 'akhir') !== false || stripos($col, 'expired') !== false || stripos($col, 'end') !== false || stripos($col, 'habis') !== false) {
        $col_tgl_selesai = $col;
        break;
    }
}

// --- PROSES ACTION HANDLER VOUCHER CRUD (TAMBAH, EDIT, HAPUS) ---
if ($action === 'voucher_proses_tambah' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_voucher = $_POST['kode_voucher'] ?? '';
    $nama_voucher = $_POST['nama_voucher'] ?? '';
    $diskon_persen = floatval($_POST['diskon_persen'] ?? 0);
    $harga_poin_val = intval($_POST['harga_poin'] ?? 0);
    $kuota = intval($_POST['kuota'] ?? 0);
    $status = $_POST['status'] ?? 'Aktif';
    $id_level = !empty($_POST['id_level']) ? intval($_POST['id_level']) : null;
    $tgl_mulai_val = $_POST['tgl_mulai'] ?? null;
    $tgl_selesai_val = $_POST['tgl_selesai'] ?? null;

    try {
        $db->beginTransaction();

        $sql_insert = "INSERT INTO voucher (kode_voucher, nama_voucher, diskon_persen, kuota, `$col_harga_poin`, `$col_tgl_mulai`, `$col_tgl_selesai`, status, id_level) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql_insert);
        $stmt->execute([
            $kode_voucher,
            $nama_voucher,
            $diskon_persen,
            $kuota,
            $harga_poin_val,
            $tgl_mulai_val,
            $tgl_selesai_val,
            $status,
            $id_level
        ]);

        $db->commit();
        $_SESSION['success'] = "Voucher baru berhasil disimpan!";
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            try { $db->rollBack(); } catch (Exception $rbEx) {}
        }
        $_SESSION['error'] = "Gagal menyimpan voucher: " . $e->getMessage();
    }

    header("Location: index.php?page=manager_dashboard&action=data_voucher");
    exit;
}

if ($action === 'voucher_proses_edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_voucher = intval($_POST['id_voucher'] ?? 0);
    $kode_voucher = $_POST['kode_voucher'] ?? '';
    $nama_voucher = $_POST['nama_voucher'] ?? '';
    $diskon_persen = floatval($_POST['diskon_persen'] ?? 0);
    $harga_poin_val = intval($_POST['harga_poin'] ?? 0);
    $kuota = intval($_POST['kuota'] ?? 0);
    $status = $_POST['status'] ?? 'Aktif';
    $id_level = !empty($_POST['id_level']) ? intval($_POST['id_level']) : null;
    $tgl_mulai_val = $_POST['tgl_mulai'] ?? null;
    $tgl_selesai_val = $_POST['tgl_selesai'] ?? null;

    try {
        $db->beginTransaction();

        $sql_update = "UPDATE voucher 
                       SET kode_voucher = ?, 
                           nama_voucher = ?, 
                           diskon_persen = ?, 
                           kuota = ?, 
                           `$col_harga_poin` = ?, 
                           `$col_tgl_mulai` = ?, 
                           `$col_tgl_selesai` = ?, 
                           status = ?, 
                           id_level = ? 
                       WHERE id_voucher = ?";
        $stmt = $db->prepare($sql_update);
        $stmt->execute([
            $kode_voucher,
            $nama_voucher,
            $diskon_persen,
            $kuota,
            $harga_poin_val,
            $tgl_mulai_val,
            $tgl_selesai_val,
            $status,
            $id_level,
            $id_voucher
        ]);

        $db->commit();
        $_SESSION['success'] = "Voucher berhasil diperbarui!";
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            try { $db->rollBack(); } catch (Exception $rbEx) {}
        }
        $_SESSION['error'] = "Gagal memperbarui voucher: " . $e->getMessage();
    }

    header("Location: index.php?page=manager_dashboard&action=data_voucher");
    exit;
}

if ($action === 'voucher_proses_hapus') {
    $id_voucher = intval($_GET['id'] ?? 0);
    try {
        $db->beginTransaction();

        $stmt = $db->prepare("DELETE FROM voucher WHERE id_voucher = ?");
        $stmt->execute([$id_voucher]);

        $db->commit();
        $_SESSION['success'] = "Voucher berhasil dihapus!";
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            try { $db->rollBack(); } catch (Exception $rbEx) {}
        }
        $_SESSION['error'] = "Gagal menghapus voucher: " . $e->getMessage();
    }

    header("Location: index.php?page=manager_dashboard&action=data_voucher");
    exit;
}
// --- END PROSES ACTION HANDLER VOUCHER CRUD ---
?>
<!-- DEBUG INFO - UNTUK MELIHAT STRUKTUR KOLOM TABEL VOUCHER ANDA (Klik Kanan -> View Source):
     Kolom Harga Poin Terdeteksi: <?= $col_harga_poin; ?> 
     Kolom Tanggal Mulai Terdeteksi: <?= $col_tgl_mulai; ?> 
     Kolom Tanggal Selesai Terdeteksi: <?= $col_tgl_selesai; ?> 
     Semua Kolom Riil di Database: <?= implode(', ', $v_cols); ?> 
-->

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manager - SIREMO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Cetak Khusus Laporan Resmi (PDF Print Reset) -->
    <style>
        @media print {
            aside, header, button, .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 11pt !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                width: 100% !important;
            }
            .flex-1 {
                flex: none !important;
                width: 100% !important;
            }
            .shadow-sm, .border, .rounded-2xl, .rounded-xl {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px !important;
                page-break-inside: auto !important;
            }
            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            th, td {
                border: 1px solid #000000 !important;
                padding: 6px 8px !important;
                color: #000000 !important;
                text-align: left !important;
            }
            th {
                background-color: #f2f2f2 !important;
                font-weight: bold !important;
            }
        }
    </style>
    
    <!-- JavaScript Export Excel Dinamis dengan Formatting CSS -->
    <script>
        function exportTableToExcel(tableID, filename = 'laporan') {
            var table = document.getElementById(tableID);
            var html = table.outerHTML;

            // Tambahkan inline styles agar border dan header tabel rapi di Microsoft Excel
            var styles = "<style>table { border-collapse: collapse; } th, td { border: 1px solid #999; padding: 6px; font-family: sans-serif; font-size: 12px; } th { background-color: #e5e7eb; font-weight: bold; }</style>";
            var fullHtml = "<html><head><meta charset='utf-8'>" + styles + "</head><body>" + html + "</body></html>";

            var blob = new Blob(['\ufeff' + fullHtml], {
                type: 'application/vnd.ms-excel'
            });

            var downloadLink = document.createElement("a");
            downloadLink.href = URL.createObjectURL(blob);
            downloadLink.download = filename + '.xls';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>
</head>
<body class="bg-gray-50 font-sans flex h-screen overflow-hidden text-sm">

    <!-- Memuat komponen Sidebar Manager -->
    <?php include __DIR__ . '/../sidebar/managerside.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header Dashboard Manager -->
        <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8 flex-shrink-0">
            <h1 class="text-base font-bold text-gray-800 uppercase flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-indigo-600"></i>
                <span>Menu Manager: <?= str_replace('_', ' ', $action); ?></span>
            </h1>
            <div class="text-xs font-bold text-gray-500">Selamat datang kembali, <span class="text-indigo-600"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?></span></div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">

            <!-- TAMPILAN PESAN NOTIFIKASI -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-4 font-bold flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4 font-bold flex items-center gap-2 border border-red-200">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <!-- ==================== MAIN HOME DASHBOARD ==================== -->
            <?php if ($action === 'home'): 
                // --- QUERY METRIK DASHBOARD MANAGER ---
                // 1. Total Cabang
                $stmtCountCab = $db->query("SELECT COUNT(*) FROM lokasi");
                $count_cabang = $stmtCountCab->fetchColumn() ?: 0;

                // 2. Total Karyawan
                $stmtCountKar = $db->query("SELECT COUNT(*) FROM karyawan");
                $count_karyawan = $stmtCountKar->fetchColumn() ?: 0;

                // 3. Total Pelanggan
                $stmtCountPel = $db->query("SELECT COUNT(*) FROM pelanggan");
                $count_pelanggan = $stmtCountPel->fetchColumn() ?: 0;

                // 4. Total Armada Mobil
                $stmtCountMob = $db->query("SELECT COUNT(*) FROM mobil");
                $count_armada = $stmtCountMob->fetchColumn() ?: 0;

                // 5. Total Omset Pendapatan
                $stmtCountOmset = $db->query("SELECT COALESCE(SUM(total_harga), 0) FROM penyewaan WHERE status_penyewaan = 'Confirmed'");
                $total_omset = $stmtCountOmset->fetchColumn() ?: 0;

                // 6. Total Denda Terbayar (tgl_selesai tidak null)
                $stmtCountDenda = $db->query("SELECT COALESCE(SUM(estimasi_biaya), 0) FROM kondisi_mobil WHERE tgl_selesai IS NOT NULL");
                $total_denda_lunas = $stmtCountDenda->fetchColumn() ?: 0;

                // 7. Total Voucher Aktif
                $stmtCountVouch = $db->query("SELECT COUNT(*) FROM voucher WHERE status = 'Aktif'");
                $count_voucher_aktif = $stmtCountVouch->fetchColumn() ?: 0;

                // 8. Total Level Loyalitas
                $stmtCountLoyal = $db->query("SELECT COUNT(*) FROM loyalitas WHERE status = 'Aktif'");
                $count_loyal = $stmtCountLoyal->fetchColumn() ?: 0;

                // --- QUERY LIST HARIAN LAPORAN ---
                // Karyawan Terbaru
                $stmtRecentKar = $db->query("
                    SELECT k.*, l.nama_lokasi 
                    FROM karyawan k 
                    LEFT JOIN lokasi l ON k.id_lokasi = l.id_lokasi 
                    ORDER BY k.id_karyawan DESC 
                    LIMIT 3
                ");
                $recent_karyawan = $stmtRecentKar->fetchAll(PDO::FETCH_ASSOC);

                // Cabang Terdaftar Terbaru
                $stmtRecentCab = $db->query("
                    SELECT * 
                    FROM lokasi 
                    ORDER BY id_lokasi DESC 
                    LIMIT 3
                ");
                $recent_cabang = $stmtRecentCab->fetchAll(PDO::FETCH_ASSOC);
            ?>
                <!-- Welcome Banner -->
                <div class="bg-white p-6 rounded-2xl border shadow-sm mb-6">
                    <h2 class="text-base font-bold text-gray-800 mb-2">Selamat Datang di Panel Sistem Informasi SIREMO</h2>
                    <p class="text-xs text-gray-400">Pilih menu di sidebar kiri untuk mengelola cabang, akun karyawan, loyalitas, voucher, dan laporan bulanan perusahaan [PDF 1, PDF 2, PDF 3, PDF 4, PDF 5].</p>
                </div>

                <!-- METRIK KINERJA OPERASIONAL EXECUTIVE -->
                <div class="mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <!-- CABANG AKTIF -->
                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Cabang Aktif
                                </p>
                                <p class="text-3xl font-black text-gray-800 mt-1">
                                    <?= $count_cabang; ?>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Cabang terdaftar
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                                <i class="fa-solid fa-location-dot text-lg"></i>
                            </div>
                        </div>

                        <!-- TOTAL KARYAWAN -->
                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Total Karyawan
                                </p>
                                <p class="text-3xl font-black text-gray-800 mt-1">
                                    <?= $count_karyawan; ?>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Staf & Driver aktif
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                                <i class="fa-solid fa-user-tie text-lg"></i>
                            </div>
                        </div>

                        <!-- PELANGGAN TERDAFTAR -->
                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Pelanggan Terdaftar
                                </p>
                                <p class="text-3xl font-black text-gray-800 mt-1">
                                    <?= $count_pelanggan; ?>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Member SIREMO
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                                <i class="fa-solid fa-users text-lg"></i>
                            </div>
                        </div>

                        <!-- TOTAL ARMADA -->
                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Total Armada
                                </p>
                                <p class="text-3xl font-black text-gray-800 mt-1">
                                    <?= $count_armada; ?>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Unit kendaraan
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-car text-lg"></i>
                            </div>
                        </div>

                        <!-- OMSET REVENUE -->
                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Omset Revenue
                                </p>
                                <p class="text-xl font-black text-emerald-600 mt-2">
                                    Rp <?= number_format($total_omset, 0, ',', '.'); ?>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Transaksi sukses
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-money-bill-wave text-lg"></i>
                            </div>
                        </div>

                        <!-- DENDA TERBAYAR -->
                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Denda Terbayar
                                </p>
                                <p class="text-xl font-black text-red-600 mt-2">
                                    Rp <?= number_format($total_denda_lunas, 0, ',', '.'); ?>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Denda kerusakan lunas
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-600">
                                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                            </div>
                        </div>

                        <!-- VOUCHER AKTIF -->
                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Voucher Aktif
                                </p>
                                <p class="text-3xl font-black text-purple-600 mt-1">
                                    <?= $count_voucher_aktif; ?>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Kupon promo berjalan
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                                <i class="fa-solid fa-ticket text-lg"></i>
                            </div>
                        </div>

                        <!-- LEVEL LOYALITAS -->
                        <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Level Loyalitas
                                </p>
                                <p class="text-3xl font-black text-amber-500 mt-1">
                                    <?= $count_loyal; ?>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Tier keanggotaan aktif
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                                <i class="fa-solid fa-crown text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DETAIL OPERASIONAL & PERKEMBANGAN HARIAN -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- DAFTAR CABANG HARIAN HARIAN -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm">
                        <div class="flex items-center justify-between border-b pb-3 mb-4">
                            <h3 class="font-bold text-gray-800 text-xs uppercase flex items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-indigo-600"></i>
                                <span>Cabang Paling Baru Didaftarkan</span>
                            </h3>
                            <a href="index.php?page=manager_dashboard&action=data_cabang" class="text-[10px] font-black text-indigo-600 hover:underline uppercase">Kelola Cabang &rarr;</a>
                        </div>
                        <div class="space-y-3">
                            <?php if(!empty($recent_cabang)): foreach($recent_cabang as $rc): ?>
                                <div class="p-3 bg-gray-50 rounded-xl border flex justify-between items-center text-xs">
                                    <div>
                                        <span class="font-bold text-gray-800 block"><?= htmlspecialchars($rc['nama_lokasi']); ?></span>
                                        <span class="text-gray-400 block text-[10px] mt-0.5">Kota: <strong><?= htmlspecialchars($rc['kota']); ?></strong></span>
                                        <span class="text-[10px] text-gray-400 block truncate max-w-xs mt-0.5"><?= htmlspecialchars($rc['alamat']); ?></span>
                                    </div>
                                    <span class="px-2.5 py-1 text-[9px] font-black uppercase rounded bg-indigo-50 text-indigo-600">Aktif</span>
                                </div>
                            <?php endforeach; else: ?>
                                <p class="py-10 text-center text-gray-400 italic text-xs">Belum ada data cabang baru.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- KARYAWAN HARIAN BARU BERGABUNG -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm">
                        <div class="flex items-center justify-between border-b pb-3 mb-4">
                            <h3 class="font-bold text-gray-800 text-xs uppercase flex items-center gap-2">
                                <i class="fa-solid fa-user-group text-indigo-600"></i>
                                <span>Karyawan Baru Bergabung</span>
                            </h3>
                            <a href="index.php?page=manager_dashboard&action=data_karyawan" class="text-[10px] font-black text-indigo-600 hover:underline uppercase">Kelola Akun &rarr;</a>
                        </div>
                        <div class="space-y-3">
                            <?php if(!empty($recent_karyawan)): foreach($recent_karyawan as $rk): ?>
                                <div class="p-3 bg-gray-50 rounded-xl border flex justify-between items-center text-xs">
                                    <div>
                                        <span class="font-bold text-gray-800 block"><?= htmlspecialchars($rk['nama_karyawan']); ?></span>
                                        <span class="text-gray-400 block text-[10px] mt-0.5">Jabatan: <strong><?= htmlspecialchars($rk['role']); ?></strong></span>
                                        <span class="text-[10px] text-gray-400 block mt-0.5">Penempatan: <strong><?= htmlspecialchars($rk['nama_lokasi'] ?? 'Belum Ditentukan'); ?></strong></span>
                                    </div>
                                    <span class="px-2.5 py-1 text-[9px] font-black uppercase rounded bg-green-50 text-green-700 border border-green-100"><?= htmlspecialchars($rk['status_karyawan']); ?></span>
                                </div>
                            <?php endforeach; else: ?>
                                <p class="py-10 text-center text-gray-400 italic text-xs">Belum ada karyawan baru bergabung.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <!-- ==================== KELOLA CABANG LOKASI (MANAGER) ==================== -->
            <?php elseif ($action === 'buat_lokasi'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>📍</span> <span>Registrasi Cabang Baru</span>
                        </h2>
                        <form action="index.php?page=lokasi_proses_tambah" method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Cabang</label>
                                <input type="text" name="nama_lokasi" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kota</label>
                                <input type="text" name="kota" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Alamat Lengkap</label>
                                <textarea name="alamat" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500" rows="3"></textarea>
                            </div>
                            <!-- PENYELARASAN TOMBOL: Menggunakan warna Indigo Premium (bg-indigo-600) -->
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Cabang Baru
                            </button>
                        </form>
                    </div>
                </div>

            <?php elseif ($action === 'data_cabang'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>📋</span> <span>Daftar Cabang Terdaftar</span>
                    </h2>
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm text-left text-gray-500 border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Nama Cabang</th>
                                    <th class="px-4 py-3">Kota</th>
                                    <th class="px-4 py-3">Alamat Lengkap</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php
                                $stmtCab = $db->query("SELECT * FROM lokasi ORDER BY id_lokasi DESC");
                                $cabs = $stmtCab->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($cabs)): foreach ($cabs as $lokasi): ?>
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-4 py-4 font-bold text-gray-900"><?= htmlspecialchars($lokasi['nama_lokasi']); ?></td>
                                        <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($lokasi['kota']); ?></td>
                                        <td class="px-4 py-4 text-xs max-w-xs truncate"><?= htmlspecialchars($lokasi['alamat']); ?></td>
                                        <td class="px-4 py-4">
                                            <a href="index.php?page=manager_dashboard&action=edit_lokasi&id=<?= htmlspecialchars($lokasi['id_lokasi']); ?>" 
                                            class="text-blue-500 hover:text-blue-700">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?page=lokasi_hapus&id=<?= htmlspecialchars($lokasi['id_lokasi']); ?>"
                                            class="text-red-500 hover:text-red-700"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus cabang ini?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada cabang terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($action === 'edit_lokasi' && isset($lokasiEdit) && $lokasiEdit): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>✏️</span> <span>Edit Data Cabang</span>
                        </h2>
                        <form action="index.php?page=proses_edit_lokasi" method="POST" class="space-y-4">
                            <input type="hidden" name="id_lokasi" value="<?= htmlspecialchars($lokasiEdit['id_lokasi']); ?>">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Cabang</label>
                                <input type="text" name="nama_lokasi" value="<?= htmlspecialchars($lokasiEdit['nama_lokasi']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kota</label>
                                <input type="text" name="kota" value="<?= htmlspecialchars($lokasiEdit['kota']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Alamat Lengkap</label>
                                <textarea name="alamat" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500" rows="3"><?= htmlspecialchars($lokasiEdit['alamat']); ?></textarea>
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

            <!-- ==================== KELOLA KARYAWAN (MANAGER) ==================== -->
            <?php elseif ($action === 'buat_akun'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 border-b pb-2 mb-4 flex items-center space-x-2">
                        <span>Registrasi Karyawan Baru</span>
                    </h2>
                    <form action="index.php?page=karyawan_proses_tambah" method="POST" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-0.5">Nama Lengkap</label>
                                <input type="text" name="nama_karyawan" required class="w-full border p-2 rounded-lg text-xs focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-0.5">No. KTP</label>
                                <input type="text" name="no_ktp" class="w-full border p-2 rounded-lg text-xs focus:outline-indigo-500" placeholder="3171xxxxxxxxxxxx">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-0.5">Email Resmi</label>
                                <input type="email" name="email" required class="w-full border p-2 rounded-lg text-xs focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-0.5">No. Telp</label>
                                <input type="text" name="no_telp" class="w-full border p-2 rounded-lg text-xs focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-0.5">Status Supir</label>
                                <input type="text" name="status_supir" class="w-full border p-2 rounded-lg text-xs focus:outline-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3 mt-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-0.5">Cabang / Penempatan</label>
                                <select name="id_lokasi" required class="w-full border p-2 rounded-lg text-xs bg-white focus:outline-indigo-500">
                                    <option value="">-- Pilih Cabang --</option>
                                    <?php 
                                    $stmtC = $db->query("SELECT * FROM lokasi");
                                    $daftarLokasi = $stmtC->fetchAll(PDO::FETCH_ASSOC);
                                    if (!empty($daftarLokasi)): foreach ($daftarLokasi as $lokasi): ?>
                                        <option value="<?= htmlspecialchars($lokasi['id_lokasi']); ?>"><?= htmlspecialchars($lokasi['nama_lokasi']); ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-0.5">Password</label>
                                <input type="password" name="password" required class="w-full border p-2 rounded-lg text-xs focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-0.5">Jabatan / Role</label>
                                <select name="role" required class="w-full border p-2 rounded-lg text-xs bg-white focus:outline-indigo-500">
                                    <option value="Manager">Manager</option>
                                    <option value="Staff Admin">Staff Admin</option>
                                    <option value="Staff Lapangan">Staff Lapangan</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="status_karyawan" value="Aktif">
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition mt-2">
                            Simpan Akun Baru
                        </button>
                    </form>
                </div>

            <?php elseif ($action === 'data_karyawan'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>📋</span> <span>Daftar Karyawan Terdaftar</span>
                    </h2>
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap min-w-[750px] border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Nama Karyawan</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Cabang</th>
                                    <th class="px-4 py-3">Jabatan</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php
                                $stmtKar = $db->query("SELECT * FROM karyawan ORDER BY id_karyawan DESC");
                                $kars = $stmtKar->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($kars)): foreach ($kars as $karyawan): ?>
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-4 py-4 font-bold text-gray-900"><?= htmlspecialchars($karyawan['nama_karyawan'] ?? 'Tanpa Nama'); ?></td>
                                        <td class="px-4 py-4 text-xs text-gray-600"><?= htmlspecialchars($karyawan['email']); ?></td>
                                        <td class="px-4 py-4 text-gray-600">
                                            <?php
                                            $stmtLok = $db->prepare("SELECT nama_lokasi FROM lokasi WHERE id_lokasi = :id_lokasi");
                                            $stmtLok->execute(['id_lokasi' => $karyawan['id_lokasi']]);
                                            $lokasi = $stmtLok->fetch(PDO::FETCH_ASSOC);
                                            echo htmlspecialchars($lokasi['nama_lokasi'] ?? 'Belum Ditentukan');
                                            ?>
                                        </td>
                                        <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($karyawan['role']); ?></td>
                                        <td class="px-4 py-4">
                                            <?php
                                            $status = $karyawan['status_karyawan'];
                                            if ($status === 'Aktif') {
                                                $colorClass = 'bg-green-50 text-green-700 border-green-200';
                                            } elseif ($status === 'Cuti') {
                                                $colorClass = 'bg-amber-50 text-amber-700 border-amber-200';
                                            } else {
                                                $colorClass = 'bg-red-50 text-red-700 border-red-200';
                                            }
                                            ?>
                                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full border <?= $colorClass; ?>">
                                                <?= htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex space-x-2">
                                                <a href="index.php?page=manager_dashboard&action=edit_karyawan&id=<?= $karyawan['id_karyawan']; ?>" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?page=hapus_karyawan&id=<?= $karyawan['id_karyawan']; ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada karyawan terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($action === 'edit_karyawan' && isset($karyawanEdit) && $karyawanEdit): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>✏️</span> <span>Edit Data Karyawan</span>
                        </h2>
                        <form action="index.php?page=karyawan_proses_edit" method="POST" class="space-y-4">
                            <input type="hidden" name="id_karyawan" value="<?= htmlspecialchars($karyawanEdit['id_karyawan']); ?>">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama_karyawan" value="<?= htmlspecialchars($karyawanEdit['nama_karyawan']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($karyawanEdit['email']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Cabang</label>
                                <select name="id_lokasi" required class="w-full border p-2 rounded-lg text-sm bg-white focus:outline-indigo-500">
                                    <?php 
                                    $stmtC = $db->query("SELECT * FROM lokasi");
                                    $daftarLokasi = $stmtC->fetchAll(PDO::FETCH_ASSOC);
                                    if (!empty($daftarLokasi)): foreach ($daftarLokasi as $lokasi): ?>
                                        <option value="<?= htmlspecialchars($lokasi['id_lokasi']); ?>" <?= $karyawanEdit['id_lokasi'] == $lokasi['id_lokasi'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($lokasi['nama_lokasi']); ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Jabatan / Role</label>
                                <select name="role" required class="w-full border p-2 rounded-lg text-sm bg-white focus:outline-indigo-500">
                                    <option value="Manager" <?= $karyawanEdit['role'] === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="Staff Admin" <?= $karyawanEdit['role'] === 'Staff Admin' ? 'selected' : ''; ?>>Staff Admin</option>
                                    <option value="Staff Lapangan" <?= $karyawanEdit['role'] === 'Staff Lapangan' ? 'selected' : ''; ?>>Staff Lapangan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Status Karyawan</label>
                                <select name="status_karyawan" required class="w-full border p-2 rounded-lg text-sm bg-white focus:outline-indigo-500">
                                    <option value="Aktif" <?= ($karyawanEdit['status_karyawan'] === 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Tidak Aktif" <?= ($karyawanEdit['status_karyawan'] === 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    <option value="Cuti" <?= ($karyawanEdit['status_karyawan'] === 'Cuti') ? 'selected' : ''; ?>>Cuti</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

            <!-- ==================== FORM LOYALITAS (PENYELESAIAN FORM LOYALITAS BLANK - SCREENSHOT 1) ==================== -->
            <?php elseif ($action === 'buat_loyal'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>💎</span> <span>Buat Tingkat Loyalitas Baru</span>
                        </h2>
                        <form action="index.php?page=loyal_proses_tambah" method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Level</label>
                                <input type="text" name="nama_level" placeholder="Contoh: Platinum, Gold" required class="w-full border p-2.5 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Syarat Penggunaan (Min Sewa)</label>
                                <input type="number" name="syarat" placeholder="Contoh: 10" required class="w-full border p-2.5 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Poin Loyalitas</label>
                                <input type="number" name="poin" step="0.1" placeholder="Contoh: 100" required class="w-full border p-2.5 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Keterangan / Benefit</label>
                                <textarea name="keterangan" placeholder="Contoh: Akses asuransi proteksi gratis bodi mobil..." class="w-full border p-2.5 rounded-lg text-sm focus:outline-indigo-500" rows="3"></textarea>
                            </div>
                            <input type="hidden" name="status" value="Aktif">
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Level Loyalitas Baru
                            </button>
                        </form>
                    </div>
                </div>

            <?php elseif ($action === 'data_loyalitas'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>💎</span> <span>Daftar Level Loyalitas</span>
                    </h2>
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap min-w-[700px] border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Nama Level</th>
                                    <th class="px-4 py-3">Syarat</th>
                                    <th class="px-4 py-3">Poin</th>
                                    <th class="px-4 py-3">Keterangan</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php 
                                $stmtLoy = $db->query("SELECT * FROM loyalitas ORDER BY id_level ASC");
                                $loyalLevels = $stmtLoy->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($loyalLevels)): foreach ($loyalLevels as $level): ?>
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-4 py-4 font-bold text-gray-900"><?= htmlspecialchars($level['nama_level']); ?></td>
                                        <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($level['syarat']); ?> Kali Sewa</td>
                                        <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($level['poin']); ?> pts</td>
                                        <td class="px-4 py-4 text-gray-600 max-w-xs truncate"><?= htmlspecialchars($level['keterangan'] ?? '-'); ?></td>
                                        <td class="px-4 py-4">
                                            <?php
                                            $status = $level['status'];
                                            if ($status === 'Aktif') {
                                                $colorClass = 'bg-green-50 text-green-700 border-green-200';
                                            } elseif ($status === 'Tidak Aktif') {
                                                $colorClass = 'bg-red-50 text-red-700 border-red-200';
                                            }
                                            ?>
                                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full border <?= $colorClass; ?>">
                                                <?= htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex space-x-2">
                                                <a href="index.php?page=manager_dashboard&action=edit_loyal&id=<?= $level['id_level']; ?>" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?page=loyalitas_hapus&id=<?= $level['id_level']; ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Apakah Anda yakin ingin menghapus level loyalitas ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada data tingkat loyalitas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($action === 'edit_loyal' && isset($loyalEdit) && $loyalEdit): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>✏️</span> <span>Edit Level Loyalitas</span>
                        </h2>
                        <form action="index.php?page=loyal_proses_edit" method="POST" class="space-y-4">
                            <input type="hidden" name="id_level" value="<?= htmlspecialchars($loyalEdit['id_level']); ?>">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Level</label>
                                <input type="text" name="nama_level" value="<?= htmlspecialchars($loyalEdit['nama_level']); ?>" required class="w-full border p-2.5 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Syarat Penggunaan (Min Sewa)</label>
                                <input type="number" name="syarat" value="<?= htmlspecialchars($loyalEdit['syarat']); ?>" required class="w-full border p-2.5 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Poin Loyalitas</label>
                                <input type="number" name="poin" step="0.1" value="<?= htmlspecialchars($loyalEdit['poin']); ?>" required class="w-full border p-2.5 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Keterangan / Benefit</label>
                                <textarea name="keterangan" class="w-full border p-2.5 rounded-lg text-sm focus:outline-indigo-500" rows="3"><?= htmlspecialchars($loyalEdit['keterangan'] ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Status</label>
                                <select name="status" required class="w-full border p-2.5 rounded-lg text-sm bg-white focus:outline-indigo-500">
                                    <option value="Aktif" <?= ($loyalEdit['status'] === 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Tidak Aktif" <?= ($loyalEdit['status'] === 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

            <!-- ==================== KELOLA VOUCHER ==================== -->
            <?php elseif ($action === 'buat_voucher'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit lg:col-span-1">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>Buat Voucher Baru</span>
                        </h2>
                        <form action="index.php?page=manager_dashboard&action=voucher_proses_tambah" method="POST" class="space-y-4 flex flex-col">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kode Voucher</label>
                                <input type="text" name="kode_voucher" placeholder="ex: VCHR-EMAS50" class="border p-2 rounded w-full text-sm focus:outline-indigo-500 uppercase" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Voucher</label>
                                <input type="text" name="nama_voucher" placeholder="ex: DISKON RENTAL EMAS" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Diskon (%)</label>
                                <input type="number" name="diskon_persen" step="0.01" min="1" max="100" placeholder="Contoh: 10.50" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Harga Poin</label>
                                <input type="number" name="harga_poin" min="1" placeholder="Contoh: 1000" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Kuota (Lembar)</label>
                                    <input type="number" name="kuota" min="1" placeholder="Contoh: 50" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Status</label>
                                    <select name="status" required class="w-full border p-2 rounded text-sm bg-white focus:outline-indigo-500">
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Target Level Loyalitas</label>
                                <select name="id_level" class="w-full border p-2 rounded text-sm bg-white focus:outline-indigo-500">
                                    <option value="">-- Semua Level (Tanpa Batasan) --</option>
                                    <?php
                                    $stmtL = $db->query("SELECT * FROM loyalitas");
                                    $loyalLevels = $stmtL->fetchAll(PDO::FETCH_ASSOC);
                                    if (!empty($loyalLevels)): foreach ($loyalLevels as $level): ?>
                                        <option value="<?= htmlspecialchars($level['id_level']); ?>"><?= htmlspecialchars($level['nama_level']); ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <!-- ==================== PERBAIKAN: PERUBAHAN INPUT TANGGAL BERLAKU ==================== -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Mulai</label>
                                    <input type="date" name="tgl_mulai" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Selesai</label>
                                    <input type="date" name="tgl_selesai" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
                                </div>
                            </div>
                            <!-- =================================================================================== -->

                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2.5 rounded-xl w-full mt-2 transition-colors text-sm shadow-sm">
                                Simpan Voucher
                            </button>
                        </form>
                    </div>
                </div>

<?php elseif ($action === 'data_voucher'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-3 mb-4 gap-2">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center space-x-2">
                            <span>🎟️</span> <span>Daftar Voucher</span>
                        </h2>
                    </div>
                    
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap min-w-[950px] border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Kode & Nama Voucher</th>
                                    <th class="px-4 py-3">Target Level</th>
                                    <th class="px-4 py-3">Diskon</th>
                                    <th class="px-4 py-3">Kuota</th>
                                    <th class="px-4 py-3">Harga Poin</th>
                                    <th class="px-4 py-3">Mulai Berlaku</th>
                                    <th class="px-4 py-3">Selesai Berlaku</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php
                                // Ambil data menggunakan query JOIN agar nama_level terbaca (Sinkron dengan VoucherModel)
                                $stmtV = $db->query("SELECT v.*, l.nama_level FROM voucher v LEFT JOIN loyalitas l ON v.id_level = l.id_level ORDER BY v.id_voucher DESC");
                                $vouchers = $stmtV->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (!empty($vouchers)): foreach ($vouchers as $v): ?>
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <!-- Gabungan Kode dan Nama Voucher agar hemat ruang -->
                                        <td class="px-4 py-4">
                                            <div class="font-bold text-gray-900"><?= htmlspecialchars($v['nama_voucher']); ?></div>
                                            <div class="text-xs text-indigo-600 font-mono tracking-wider mt-0.5">[ <?= htmlspecialchars($v['kode_voucher']); ?> ]</div>
                                        </td>
                                        <td class="px-4 py-4 text-gray-600 font-medium">
                                            <?= !empty($v['nama_level']) ? htmlspecialchars($v['nama_level']) : '<span class="text-gray-400 italic text-xs">Semua Level</span>'; ?>
                                        </td>
                                        <td class="px-4 py-4 text-emerald-600 font-bold"><?= htmlspecialchars(floatval($v['diskon_persen'])); ?>%</td>
                                        <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($v['kuota']); ?> Lembar</td>
                                        <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($v[$col_harga_poin] ?? 0); ?> Poin</td>
                                        <td class="px-4 py-4 text-xs text-gray-600 font-medium"><?= !empty($v[$col_tgl_mulai]) ? date('d M Y', strtotime($v[$col_tgl_mulai])) : '-'; ?></td>
                                        <td class="px-4 py-4 text-xs text-gray-600 font-medium"><?= !empty($v[$col_tgl_selesai]) ? date('d M Y', strtotime($v[$col_tgl_selesai])) : '-'; ?></td>
                                        
                                        <!-- Status Badge -->
                                        <td class="px-4 py-4 text-center">
                                            <?php if ($v['status'] === 'Aktif'): ?>
                                                <span class="px-2.5 py-1 text-xs font-bold bg-green-50 text-green-700 rounded-full border border-green-200">Aktif</span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 text-xs font-bold bg-red-50 text-red-600 rounded-full border border-red-100">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex space-x-2">
                                                <a href="index.php?page=manager_dashboard&action=edit_voucher&id=<?= $v['id_voucher']; ?>" class="text-blue-500 hover:text-blue-700">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?page=manager_dashboard&action=voucher_proses_hapus&id=<?= $v['id_voucher']; ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Apakah Anda yakin ingin menghapus voucher <?= htmlspecialchars($v['kode_voucher']); ?>?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="8" class="px-4 py-12 text-center text-gray-400 italic text-xs bg-gray-50/30">
                                            Belum ada data voucher terdaftar.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($action === 'edit_voucher' && isset($voucherEdit) && $voucherEdit): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>✏️</span> <span>Edit Data Voucher</span>
                        </h2>
                        <form action="index.php?page=manager_dashboard&action=voucher_proses_edit" method="POST" class="space-y-4">
                            <input type="hidden" name="id_voucher" value="<?= htmlspecialchars($voucherEdit['id_voucher']); ?>">
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kode Voucher</label>
                                <input type="text" name="kode_voucher" value="<?= htmlspecialchars($voucherEdit['kode_voucher']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500 uppercase">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Voucher</label>
                                <input type="text" name="nama_voucher" value="<?= htmlspecialchars($voucherEdit['nama_voucher']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Target Level Loyalitas</label>
                                <select name="id_level" class="w-full border p-2 rounded-lg text-sm bg-white focus:outline-indigo-500">
                                    <option value="">Semua Level</option>
                                    <?php 
                                    $stmtL = $db->query("SELECT * FROM loyalitas");
                                    $daftarLoyalitas = $stmtL->fetchAll(PDO::FETCH_ASSOC);
                                    if (!empty($daftarLoyalitas)): foreach ($daftarLoyalitas as $loyal): ?>
                                        <option value="<?= htmlspecialchars($loyal['id_level']); ?>" <?= $voucherEdit['id_level'] == $loyal['id_level'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($loyal['nama_level']); ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Diskon (%)</label>
                                <input type="number" step="0.01" min="0" max="100" name="diskon_persen" value="<?= htmlspecialchars(floatval($voucherEdit['diskon_persen'])); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kuota (Lembar)</label>
                                <input type="number" min="0" name="kuota" value="<?= htmlspecialchars($voucherEdit['kuota']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Harga Poin</label>
                                <input type="number" min="0" name="harga_poin" value="<?= htmlspecialchars($voucherEdit[$col_harga_poin] ?? ''); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Mulai Berlaku</label>
                                <input type="date" name="tgl_mulai" value="<?= htmlspecialchars($voucherEdit[$col_tgl_mulai] ?? ''); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Selesai Berlaku</label>
                                <input type="date" name="tgl_selesai" value="<?= htmlspecialchars($voucherEdit[$col_tgl_selesai] ?? ''); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Status Voucher</label>
                                <select name="status" required class="w-full border p-2 rounded-lg text-sm bg-white focus:outline-indigo-500">
                                    <option value="Aktif" <?= $voucherEdit['status'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Nonaktif" <?= $voucherEdit['status'] === 'Nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

            <!-- ==================== 5. PENAMPILAN SELURUH DATA SIDEBAR (MANAGER - SCREENSHOT 4) ==================== -->
            <?php elseif ($action === 'data_pelanggan'): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 border-b pb-2 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-users text-indigo-600"></i>
                        <span>Daftar Pelanggan / Member SIREMO</span>
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500 border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="p-4 font-bold">Nama Lengkap</th>
                                    <th class="p-4 font-bold">Email</th>
                                    <th class="p-4 font-bold">No. Telpon</th>
                                    <th class="p-4 font-bold">Poin Loyalty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtPel = $db->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
                                $pels = $stmtPel->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($pels)): foreach ($pels as $p): ?>
                                <tr class="hover:bg-gray-50/50">
                                    <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($p['nama_lengkap']); ?></td>
                                    <td class="p-4"><?= htmlspecialchars($p['email']); ?></td>
                                    <td class="p-4"><?= htmlspecialchars($p['no_telp']); ?></td>
                                    <td class="p-4 font-bold text-indigo-600"><?= number_format($p['poin']); ?> pts</td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="4" class="p-8 text-center italic">Belum ada pelanggan terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($action === 'data_mobil'): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 border-b pb-2 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-car text-indigo-600"></i>
                        <span>Daftar Armada Mobil SIREMO</span>
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500 border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="p-4 font-bold">Unit Mobil</th>
                                    <th class="p-4 font-bold">Plat Nomor</th>
                                    <th class="p-4 font-bold">Warna</th>
                                    <th class="p-4 font-bold text-right">Harga Harian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtMob = $db->query("SELECT * FROM mobil ORDER BY id_mobil DESC");
                                $mobs = $stmtMob->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($mobs)): foreach ($mobs as $m): ?>
                                <tr class="hover:bg-gray-50/50">
                                    <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($m['merk_mobil']); ?></td>
                                    <td class="p-4 font-mono font-semibold"><?= htmlspecialchars($m['plat_nomor']); ?></td>
                                    <td class="p-4"><?= htmlspecialchars($m['warna'] ?? '-'); ?></td>
                                    <td class="p-4 text-right font-bold text-indigo-600">Rp <?= number_format($m['harga_dinamis']); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="4" class="p-8 text-center italic">Belum ada armada mobil terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($action === 'data_fasilitas'): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 border-b pb-2 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-wrench text-indigo-600"></i>
                        <span>Daftar Fasilitas Mobil SIREMO</span>
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left text-gray-500 border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b">
                                    <th class="p-4 font-bold">Nama Fasilitas</th>
                                    <th class="p-4 font-bold">Harga</th>
                                    <th class="p-4 font-bold">Stok</th>
                                    <th class="p-4 font-bold">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmtFas = $db->query("SELECT * FROM fasilitas ORDER BY id_fasilitas DESC");
                                $fases = $stmtFas->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($fases)): foreach ($fases as $f): ?>
                                <tr class="hover:bg-gray-50/50">
                                    <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($f['nama_fasilitas']); ?></td>
                                    <td class="p-4 text-indigo-600 font-bold">Rp <?= number_format($f['harga']); ?></td>
                                    <td class="p-4"><?= htmlspecialchars($f['stok']); ?> Unit</td>
                                    <td class="p-4"><?= htmlspecialchars($f['deskripsi'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="2" class="p-8 text-center italic">Belum ada fasilitas mobil terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($action === 'data_kerusakan'): ?>
                <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                    <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 border-b text-gray-700">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Detail Kerusakan</th>
                            <th class="px-6 py-4">Tingkat</th>
                            <th class="px-6 py-4">Estimasi Biaya</th>
                            <th class="px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php
                        try {
                            // Mengambil data dari tabel kondisi_mobil sesuai skema database kamu
                            $stmtKrs = $db->query("SELECT * FROM kondisi_mobil ORDER BY id_kondisi ASC");
                            while($krs = $stmtKrs->fetch(PDO::FETCH_ASSOC)):

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
                                <td class="px-6 py-4 font-semibold text-gray-800 max-w-xs truncate">
                                    <?= htmlspecialchars($krs['deskripsi_kerusakan']); ?>
                                </td>
                                
                                <td class="px-6 py-4 text-xs">
                                    <span class="px-2.5 py-1 rounded-full font-bold <?= $badgeColor; ?>">
                                        <?= htmlspecialchars($krs['tingkat_kerusakan']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold text-red-600">
                                    Rp <?= number_format($krs['estimasi_biaya'] ?? 0); ?>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex space-x-2">
                                        <a href="index.php?page=manager_dashboard&action=detail&id=<?= $krs['id_kondisi']; ?>" 
                                        class="inline-block px-3 py-1 bg-blue-100 text-blue-800 font-semibold text-xs rounded-full hover:bg-blue-200 transition">
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile;
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='5' class='px-6 py-10 text-center text-gray-400 italic'>Gagal mengambil data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>

            <?php elseif($action === 'detail'): ?>
                <div class="w-full max-w-3xl mx-auto bg-white p-6 rounded-2xl border shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-sm border-b pb-2">Formulir Penyelesaian & Estimasi Biaya Perbaikan</h3>
                    
                    <form action="" method="POST" class="space-y-4">
                        <!-- ID Kondisi Hidden -->
                        <input type="hidden" name="id_kondisi" value="<?= htmlspecialchars($evData['id_kondisi'] ?? 0); ?>">
                        
                        <!-- SECTION 1: DATA BERSIFAT READ-ONLY (DIKUNCI) -->
                        <div class="bg-gray-50 p-4 rounded-xl space-y-3 border border-gray-100">
                            <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider block">Informasi Kerusakan Lapangan (Read-Only)</span>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Tingkat Kerusakan -->
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

                            <!-- Deskripsi Kerusakan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Deskripsi Kerusakan</label>
                                <textarea readonly class="w-full border p-3 rounded-xl text-xs bg-gray-100 text-gray-500 cursor-not-allowed" rows="3"><?= htmlspecialchars($evData['deskripsi_kerusakan'] ?? ''); ?></textarea>
                            </div>

                            <!-- Foto Kerusakan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Bukti Gambar Kerusakan</label>
                                <div class="border rounded-xl p-2 bg-white flex justify-center items-center h-48 overflow-hidden">
                                    <?php if (!empty($evData['gambar_kerusakan'])): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($evData['gambar_kerusakan']); ?>" class="max-h-full object-contain rounded-lg" alt="Foto Kerusakan">
                                    <?php else: ?>
                                        <span class="text-xs italic text-gray-400">Tidak ada gambar kerusakan yang diunggah.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: ESTIMASI & PENYELESAIAN (DIJADIKAN READ-ONLY JUGA) -->
                        <div class="p-4 rounded-xl space-y-3 border border-gray-100 bg-gray-50">
                            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Estimasi & Status Perbaikan (Read-Only)</span>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Estimasi Biaya -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Estimasi Biaya (Rp)</label>
                                    <input type="text" readonly class="w-full border p-2.5 rounded-xl text-xs bg-gray-100 text-gray-500 cursor-not-allowed font-medium" 
                                        value="<?= htmlspecialchars($evData['estimasi_biaya'] ?? '0'); ?>">
                                </div>

                                <!-- Status Kondisi/Perbaikan -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Status Perbaikan</label>
                                    <select disabled class="w-full border p-2.5 rounded-xl text-xs bg-gray-100 text-gray-500 font-medium cursor-not-allowed">
                                        <option value="Pending" <?= ($evData['status_kondisi'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Proses" <?= ($evData['status_kondisi'] ?? '') === 'Proses' ? 'selected' : ''; ?>>Dalam Perbaikan</option>
                                        <option value="Selesai" <?= ($evData['status_kondisi'] ?? '') === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Catatan Perbaikan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Catatan / Solusi Perbaikan</label>
                                <textarea readonly class="w-full border p-3 rounded-xl text-xs bg-gray-100 text-gray-500 cursor-not-allowed" rows="3"><?= htmlspecialchars($evData['catatan_perbaikan'] ?? '-'); ?></textarea>
                            </div>
                        </div>

                        <!-- BUTTON AKSI: Hanya tombol kembali -->
                        <div class="flex justify-end pt-2">
                            <a href="index.php?page=manager_dashboard" class="px-4 py-2 bg-gray-500 text-white text-xs font-semibold rounded-xl hover:bg-gray-600 transition-colors">
                                Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
                </div>
                
            <!-- ==================== LAPORAN PENDAPATAN UTAMA ==================== -->
            <?php elseif ($action === 'laporan_pendapatan'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm space-y-6">
                    <div class="border-b pb-3 mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center space-x-2">
                            <span>💰</span> <span>Laporan Pendapatan Utama</span>
                        </h2>
                        
                        <div class="flex gap-2 no-print">
                            <button onclick="exportTableToExcel('tabel_laporan_pendapatan', 'Laporan_Pendapatan_Utama')" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                                <i class="fa-solid fa-file-excel"></i> Export Excel
                            </button>
                            <button onclick="window.print()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                                <i class="fa-solid fa-print"></i> Cetak PDF
                            </button>
                        </div>
                    </div>

                    <!-- KOP SURAT PRINT RESMI (Hanya muncul saat cetak PDF) -->
                    <div class="hidden print:block text-center border-b-4 border-double border-black pb-3 mb-6">
                        <h1 class="text-2xl font-bold uppercase tracking-wider text-black">SIREMO CAR RENTAL</h1>
                        <p class="text-xs text-gray-600">Sistem Informasi Manajemen Rental Mobil Terintegrasi</p>
                        <p class="text-[10px] text-gray-500">Alamat Kantor Pusat: Jl. Jenderal Sudirman No. 12, Jakarta Selatan, DKI Jakarta</p>
                        <h2 class="text-sm font-bold uppercase tracking-widest text-black mt-4">LAPORAN PENDAPATAN UTAMA PERUSAHAAN</h2>
                    </div>

                    <!-- RINGKASAN REVENUE -->
                    <?php
                    $stmtRevSum = $db->query("
                        SELECT 
                            COALESCE(SUM(total_harga), 0) AS total_omset,
                            COUNT(*) AS total_transaksi,
                            COALESCE(AVG(total_harga), 0) AS rata_transaksi
                        FROM penyewaan
                        WHERE status_penyewaan = 'Confirmed'
                    ");
                    $revSum = $stmtRevSum->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl">
                            <span class="text-xs font-bold text-emerald-800 uppercase block tracking-wider">Total Omset Sukses</span>
                            <span class="text-2xl font-black text-emerald-700 mt-1 inline-block">Rp <?= number_format($revSum['total_omset'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl">
                            <span class="text-xs font-bold text-indigo-800 uppercase block tracking-wider">Total Transaksi Selesai</span>
                            <span class="text-2xl font-black text-indigo-700 mt-1 inline-block"><?= number_format($revSum['total_transaksi']); ?> Transaksi</span>
                        </div>
                        <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl">
                            <span class="text-xs font-bold text-amber-800 uppercase block tracking-wider">Rata-rata Pendapatan / Transaksi</span>
                            <span class="text-2xl font-black text-amber-700 mt-1 inline-block">Rp <?= number_format($revSum['rata_transaksi'], 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <!-- BULANAN DETAIL -->
                    <div class="space-y-4">
                        <h3 class="font-bold text-gray-800 text-sm no-print">Akumulasi Pendapatan Bulanan</h3>
                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table id="tabel_laporan_pendapatan" class="w-full text-sm text-left text-gray-500 border-collapse">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3">Bulan</th>
                                        <th class="px-4 py-3 text-center">Jumlah Sewa</th>
                                        <th class="px-4 py-3 text-right">Pendapatan Bersih</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <?php
                                    $stmtMonth = $db->query("
                                        SELECT 
                                            DATE_FORMAT(tgl_penyewaan, '%Y-%m') AS bulan, 
                                            COUNT(*) AS total_sewa, 
                                            SUM(total_harga) AS total_pendapatan
                                        FROM penyewaan
                                        WHERE status_penyewaan = 'Confirmed'
                                        GROUP BY DATE_FORMAT(tgl_penyewaan, '%Y-%m')
                                        ORDER BY bulan DESC
                                    ");
                                    $monthlyData = $stmtMonth->fetchAll(PDO::FETCH_ASSOC);
                                    if (!empty($monthlyData)): foreach ($monthlyData as $row): ?>
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-4 py-3.5 font-bold text-gray-900"><?= date('F Y', strtotime($row['bulan'] . '-01')); ?></td>
                                            <td class="px-4 py-3.5 text-center text-gray-600"><?= htmlspecialchars($row['total_sewa']); ?> Kali Sewa</td>
                                            <td class="px-4 py-3.5 text-right font-bold text-emerald-600">Rp <?= number_format($row['total_pendapatan'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada akumulasi bulanan terdaftar.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- BLOK TANDA TANGAN RESMI (Hanya muncul saat cetak PDF) -->
                    <div class="hidden print:block mt-12 text-right">
                        <div class="inline-block text-center w-64 border-t-0">
                            <p class="text-xs text-black">Jakarta, <?= date('d F Y'); ?></p>
                            <p class="text-xs font-bold text-black mt-1">Manager Operasional</p>
                            <div class="h-20"></div>
                            <p class="text-xs font-bold text-black underline">( <?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?> )</p>
                        </div>
                    </div>
                </div>

            <!-- ==================== LAPORAN PELANGGAN TERAKTIF ==================== -->
            <?php elseif ($action === 'laporan_pelanggan'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm space-y-6">
                    <div class="border-b pb-3 mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center space-x-2">
                            <span>⭐</span> <span>Laporan Pelanggan Teraktif</span>
                        </h2>
                        
                        <div class="flex gap-2 no-print">
                            <button onclick="exportTableToExcel('tabel_laporan_pelanggan', 'Laporan_Pelanggan_Teraktif')" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                                <i class="fa-solid fa-file-excel"></i> Export Excel
                            </button>
                            <button onclick="window.print()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                                <i class="fa-solid fa-print"></i> Cetak PDF
                            </button>
                        </div>
                    </div>

                    <!-- KOP SURAT PRINT RESMI (Hanya muncul saat cetak PDF) -->
                    <div class="hidden print:block text-center border-b-4 border-double border-black pb-3 mb-6">
                        <h1 class="text-2xl font-bold uppercase tracking-wider text-black">SIREMO CAR RENTAL</h1>
                        <p class="text-xs text-gray-600">Sistem Informasi Manajemen Rental Mobil Terintegrasi</p>
                        <p class="text-[10px] text-gray-500">Alamat Kantor Pusat: Jl. Jenderal Sudirman No. 12, Jakarta Selatan, DKI Jakarta</p>
                        <h2 class="text-sm font-bold uppercase tracking-widest text-black mt-4">LAPORAN AKTIVITAS & KEAKTIFAN PELANGGAN</h2>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table id="tabel_laporan_pelanggan" class="w-full text-sm text-left text-gray-500 whitespace-nowrap border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Nama Pelanggan</th>
                                    <th class="px-4 py-3">Email / No Telp</th>
                                    <th class="px-4 py-3 text-center">Jumlah Sewa</th>
                                    <th class="px-4 py-3 text-right">Total Transaksi Spend</th>
                                    <th class="px-4 py-3 text-center">Loyalty Tier</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php
                                $stmtActPel = $db->query("
                                    SELECT 
                                        pl.id_pelanggan, 
                                        pl.nama_lengkap, 
                                        pl.email, 
                                        pl.no_telp,
                                        l.nama_level,
                                        COUNT(p.id_penyewaan) AS jumlah_sewa,
                                        SUM(p.total_harga) AS total_spend
                                    FROM pelanggan pl
                                    JOIN penyewaan p ON pl.id_pelanggan = p.id_pelanggan
                                    LEFT JOIN loyalitas l ON pl.id_level = l.id_level
                                    WHERE p.status_penyewaan = 'Confirmed'
                                    GROUP BY pl.id_pelanggan
                                    ORDER BY jumlah_sewa DESC, total_spend DESC
                                ");
                                $activePels = $stmtActPel->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($activePels)): foreach ($activePels as $p): ?>
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-4 py-4 font-bold text-gray-900"><?= htmlspecialchars($p['nama_lengkap']); ?></td>
                                        <td class="px-4 py-4 text-xs text-gray-600">
                                            <div><?= htmlspecialchars($p['email']); ?></div>
                                            <div class="text-[10px] text-gray-400 mt-0.5"><?= htmlspecialchars($p['no_telp']); ?></div>
                                        </td>
                                        <td class="px-4 py-4 text-center text-gray-800 font-bold"><?= htmlspecialchars($p['jumlah_sewa']); ?> Kali Sewa</td>
                                        <td class="px-4 py-4 text-right font-black text-indigo-600">Rp <?= number_format($p['total_spend'], 0, ',', '.'); ?></td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                                ★ <?= htmlspecialchars($p['nama_level'] ?? 'Bronze Member'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada riwayat keaktifan pelanggan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- BLOK TANDA TANGAN RESMI (Hanya muncul saat cetak PDF) -->
                    <div class="hidden print:block mt-12 text-right">
                        <div class="inline-block text-center w-64 border-t-0">
                            <p class="text-xs text-black">Jakarta, <?= date('d F Y'); ?></p>
                            <p class="text-xs font-bold text-black mt-1">Manager Operasional</p>
                            <div class="h-20"></div>
                            <p class="text-xs font-bold text-black underline">( <?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?> )</p>
                        </div>
                    </div>
                </div>

            <!-- ==================== LAPORAN RIWAYAT KERUSAKAN MOBIL ==================== -->
            <?php elseif ($action === 'laporan_kerusakan'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm space-y-6">
                    <div class="border-b pb-3 mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center space-x-2">
                            <span>🛠️</span> <span>Laporan Riwayat Kerusakan Mobil</span>
                        </h2>
                        
                        <div class="flex gap-2 no-print">
                            <button onclick="exportTableToExcel('tabel_laporan_kerusakan', 'Laporan_Riwayat_Kerusakan_Mobil')" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                                <i class="fa-solid fa-file-excel"></i> Export Excel
                            </button>
                            <button onclick="window.print()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                                <i class="fa-solid fa-print"></i> Cetak PDF
                            </button>
                        </div>
                    </div>

                    <!-- KOP SURAT PRINT RESMI (Hanya muncul saat cetak PDF) -->
                    <div class="hidden print:block text-center border-b-4 border-double border-black pb-3 mb-6">
                        <h1 class="text-2xl font-bold uppercase tracking-wider text-black">SIREMO CAR RENTAL</h1>
                        <p class="text-xs text-gray-600">Sistem Informasi Manajemen Rental Mobil Terintegrasi</p>
                        <p class="text-[10px] text-gray-500">Alamat Kantor Pusat: Jl. Jenderal Sudirman No. 12, Jakarta Selatan, DKI Jakarta</p>
                        <h2 class="text-sm font-bold uppercase tracking-widest text-black mt-4">LAPORAN DETAIL RIWAYAT KERUSAKAN ARMADA</h2>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table id="tabel_laporan_kerusakan" class="w-full text-sm text-left text-gray-500 whitespace-nowrap border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Armada Mobil</th>
                                    <th class="px-4 py-3">Tanggal Dilaporkan</th>
                                    <th class="px-4 py-3">Deskripsi Kerusakan</th>
                                    <th class="px-4 py-3 text-center">Tingkat Kerusakan</th>
                                    <th class="px-4 py-3 text-right">Biaya Kerusakan</th>
                                    <th class="px-4 py-3 text-center">Tanggal Selesai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php
                                $stmtKrsRep = $db->query("
                                    SELECT k.*, m.merk_mobil, m.plat_nomor 
                                    FROM kondisi_mobil k
                                    JOIN mobil m ON k.id_mobil = m.id_mobil
                                    ORDER BY k.tgl_dilaporkan DESC
                                ");
                                $damageReports = $stmtKrsRep->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($damageReports)): foreach ($damageReports as $krs): 
                                    $badgeColor = 'bg-green-100 text-green-800';
                                    if ($krs['tingkat_kerusakan'] === 'Ringan') {
                                        $badgeColor = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($krs['tingkat_kerusakan'] === 'Sedang') {
                                        $badgeColor = 'bg-orange-100 text-orange-800';
                                    } elseif ($krs['tingkat_kerusakan'] === 'Parah') {
                                        $badgeColor = 'bg-red-100 text-red-800';
                                    }
                                ?>
                                    <tr class="hover:bg-gray-50/80 transition-colors text-xs">
                                        <td class="px-4 py-4">
                                            <div class="font-bold text-gray-900"><?= htmlspecialchars($krs['merk_mobil']); ?></div>
                                            <div class="text-[10px] text-gray-400 font-mono mt-0.5">[ <?= htmlspecialchars($krs['plat_nomor']); ?> ]</div>
                                        </td>
                                        <td class="px-4 py-4 text-gray-600 font-medium"><?= htmlspecialchars($krs['tgl_dilaporkan']); ?></td>
                                        <td class="px-4 py-4 text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($krs['deskripsi_kerusakan']); ?>">
                                            <?= htmlspecialchars($krs['deskripsi_kerusakan']); ?>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full font-bold text-[9px] uppercase <?= $badgeColor; ?>">
                                                <?= htmlspecialchars($krs['tingkat_kerusakan']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-right font-bold text-red-600">Rp <?= number_format($krs['estimasi_biaya']); ?></td>
                                        <td class="px-4 py-4 text-center text-gray-600 font-medium">
                                            <?= !empty($krs['tgl_selesai']) ? htmlspecialchars($krs['tgl_selesai']) : '<span class="text-orange-500 font-bold">Dalam Perbaikan</span>'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada riwayat kerusakan dilaporkan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- BLOK TANDA TANGAN RESMI (Hanya muncul saat cetak PDF) -->
                    <div class="hidden print:block mt-12 text-right">
                        <div class="inline-block text-center w-64 border-t-0">
                            <p class="text-xs text-black">Jakarta, <?= date('d F Y'); ?></p>
                            <p class="text-xs font-bold text-black mt-1">Manager Operasional</p>
                            <div class="h-20"></div>
                            <p class="text-xs font-bold text-black underline">( <?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?> )</p>
                        </div>
                    </div>
                </div>

            <!-- ==================== LAPORAN KETERSEDIAAN MOBIL ==================== -->
            <?php elseif ($action === 'laporan_ketersediaan'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm space-y-6">
                    <div class="border-b pb-3 mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center space-x-2">
                            <span>🚗</span> <span>Laporan Ketersediaan Mobil</span>
                        </h2>
                        
                        <div class="flex gap-2 no-print">
                            <button onclick="exportTableToExcel('tabel_laporan_ketersediaan', 'Laporan_Ketersediaan_Mobil')" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                                <i class="fa-solid fa-file-excel"></i> Export Excel
                            </button>
                            <button onclick="window.print()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                                <i class="fa-solid fa-print"></i> Cetak PDF
                            </button>
                        </div>
                    </div>

                    <!-- KOP SURAT PRINT RESMI (Hanya muncul saat cetak PDF) -->
                    <div class="hidden print:block text-center border-b-4 border-double border-black pb-3 mb-6">
                        <h1 class="text-2xl font-bold uppercase tracking-wider text-black">SIREMO CAR RENTAL</h1>
                        <p class="text-xs text-gray-600">Sistem Informasi Manajemen Rental Mobil Terintegrasi</p>
                        <p class="text-[10px] text-gray-500">Alamat Kantor Pusat: Jl. Jenderal Sudirman No. 12, Jakarta Selatan, DKI Jakarta</p>
                        <h2 class="text-sm font-bold uppercase tracking-widest text-black mt-4">LAPORAN KETERSEDIAAN & STATUS ARMADA</h2>
                    </div>

                    <!-- RINGKASAN ARMADA STATUS -->
                    <?php
                    $stmtReadyMob = $db->query("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Tersedia'");
                    $readyMob = $stmtReadyMob->fetchColumn() ?: 0;

                    $stmtMaintMob = $db->query("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Maintenance'");
                    $maintMob = $stmtMaintMob->fetchColumn() ?: 0;

                    $stmtDisewaMob = $db->query("SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Disewa'");
                    $disewaMob = $stmtDisewaMob->fetchColumn() ?: 0;
                    ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-black">
                        <div class="bg-green-50 border border-green-100 p-4 rounded-xl flex justify-between items-center">
                            <div>
                                <span class="text-xs font-bold text-green-800 uppercase block tracking-wider">Tersedia (Ready)</span>
                                <span class="text-2xl font-black text-green-700 mt-1 inline-block"><?= $readyMob; ?> Unit</span>
                            </div>
                            <i class="fa-solid fa-circle-check text-green-400 text-2xl"></i>
                        </div>
                        <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl flex justify-between items-center">
                            <div>
                                <span class="text-xs font-bold text-orange-800 uppercase block tracking-wider">Perbaikan (Maintenance)</span>
                                <span class="text-2xl font-black text-orange-700 mt-1 inline-block"><?= $maintMob; ?> Unit</span>
                            </div>
                            <i class="fa-solid fa-screwdriver-wrench text-orange-400 text-2xl"></i>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl flex justify-between items-center">
                            <div>
                                <span class="text-xs font-bold text-indigo-800 uppercase block tracking-wider">Sedang Disewa (Rented)</span>
                                <span class="text-2xl font-black text-indigo-700 mt-1 inline-block"><?= $disewaMob; ?> Unit</span>
                            </div>
                            <i class="fa-solid fa-car-side text-indigo-400 text-2xl"></i>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table id="tabel_laporan_ketersediaan" class="w-full text-sm text-left text-gray-500 whitespace-nowrap border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Armada Mobil</th>
                                    <th class="px-4 py-3">Kategori</th>
                                    <th class="px-4 py-3 text-right">Tarif Harian</th>
                                    <th class="px-4 py-3 text-center">Status Sekarang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php
                                $stmtMobAll = $db->query("
                                    SELECT m.merk_mobil, m.plat_nomor, m.status_mobil, m.nama_kategori, m.harga_dinamis
                                    FROM mobil m
                                    ORDER BY m.status_mobil ASC, m.merk_mobil ASC
                                ");
                                $mobList = $stmtMobAll->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($mobList)): foreach ($mobList as $m): 
                                    $statusClass = 'bg-green-50 text-green-700 border-green-200';
                                    if ($m['status_mobil'] === 'Maintenance') {
                                        $statusClass = 'bg-orange-50 text-orange-700 border-orange-200';
                                    } elseif ($m['status_mobil'] === 'Disewa') {
                                        $statusClass = 'bg-blue-50 text-blue-700 border-blue-200';
                                    }
                                ?>
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-4 py-4 font-bold text-gray-900">
                                            <?= htmlspecialchars($m['merk_mobil']); ?>
                                            <span class="text-xs font-mono font-medium text-gray-400 ml-1.5">(<?= htmlspecialchars($m['plat_nomor']); ?>)</span>
                                        </td>
                                        <td class="px-4 py-4 text-gray-600 font-medium"><?= htmlspecialchars($m['nama_kategori']); ?></td>
                                        <td class="px-4 py-4 text-right font-black text-indigo-600">Rp <?= number_format($m['harga_dinamis'], 0, ',', '.'); ?></td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full border uppercase <?= $statusClass; ?>">
                                                <?= htmlspecialchars($m['status_mobil']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada unit armada mobil terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- BLOK TANDA TANGAN RESMI (Hanya muncul saat cetak PDF) -->
                    <div class="hidden print:block mt-12 text-right">
                        <div class="inline-block text-center w-64 border-t-0">
                            <p class="text-xs text-black">Jakarta, <?= date('d F Y'); ?></p>
                            <p class="text-xs font-bold text-black mt-1">Manager Operasional</p>
                            <div class="h-20"></div>
                            <p class="text-xs font-bold text-black underline">( <?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?> )</p>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </main>
    </div>
</body>
</html>