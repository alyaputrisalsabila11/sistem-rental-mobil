<?php
// =========================================================================
// FILE: views/user/dashboard/pelanggan.php (Dashboard Pelanggan Komplet)
// =========================================================================

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once __DIR__ . '/../../../init.php';
$db = Database::getConnection(); // Koneksi basis data menggunakan PDO

$action = $_GET['action'] ?? 'home'; // Default tab
$user_id = $_SESSION['user_id'] ?? null;

// --- PROSES ACTION HANDLER (POST & GET) ---
if ($action === 'proses_bayar_denda' && isset($_GET['id_kondisi'])) {
    $id_kondisi = filter_var($_GET['id_kondisi'] ?? 0, FILTER_VALIDATE_INT);
    try {
        $db->beginTransaction();

        // Update tgl_selesai menjadi waktu pembayaran sekarang (berarti Lunas)
        $stmtBayar = $db->prepare("UPDATE kondisi_mobil SET tgl_selesai = NOW() WHERE id_kondisi = ?");
        $stmtBayar->execute([$id_kondisi]);

        $db->commit();
        $_SESSION['success'] = "Denda berhasil dilunasi! Anda sekarang dapat melakukan pemesanan sewa kembali.";
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            try {
                $db->rollBack();
            } catch (Exception $rollbackException) {
            }
        }
        $_SESSION['error'] = "Gagal memproses pembayaran: " . $e->getMessage();
    }

    header("Location: index.php?page=home&action=denda_saya");
    exit;
}
// --- END PROSES ACTION HANDLER ---

// Mengambil profil data pelanggan & tingkat loyalitas saat ini
$stmtUser = $db->prepare("
    SELECT p.*, l.nama_level 
    FROM pelanggan p 
    LEFT JOIN loyalitas l ON p.id_level = l.id_level 
    WHERE p.id_pelanggan = ?
");
$stmtUser->execute([$user_id]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

$user_poin = $userData['poin'] ?? 0;
$user_level_nama = $userData['nama_level'] ?? 'Bronze';

// DETEKSI BLOKIR JIKA ADA DENDA AKTIF YANG BELUM VERIFIKASI LUNAS OLEH PETUGAS
$columns_query = $db->query("DESCRIBE pengembalian");
$existing_cols = $columns_query->fetchAll(PDO::FETCH_COLUMN);

$denda_clause = "1=0"; // Default jika tidak ada kolom denda
if (in_array('biaya_kerusakan', $existing_cols) && in_array('denda_telat', $existing_cols)) {
    $denda_clause = "(pg.biaya_kerusakan > 0 OR pg.denda_telat > 0)";
} elseif (in_array('denda', $existing_cols)) {
    $denda_clause = "pg.denda > 0";
} elseif (in_array('denda_telat', $existing_cols)) {
    $denda_clause = "pg.denda_telat > 0";
}

$status_col = 'checklist';
if (in_array('status', $existing_cols)) {
    $status_col = 'status';
} elseif (!in_array('checklist', $existing_cols)) {
    $status_col = null;
}

$status_clause = "";
if ($status_col) {
    if ($status_col === 'status') {
        $status_clause = " AND pg.$status_col != 'Lunas'";
    } else {
        $status_clause = " AND pg.$status_col IS NULL";
    }
}

$stmtDenda = $db->prepare("
    SELECT pg.*, m.merk_mobil
    FROM pengembalian pg
    JOIN penyerahan pen ON pg.id_penyerahan = pen.id_penyerahan
    JOIN penyewaan pny ON pen.id_penyewaan = pny.id_penyewaan
    JOIN mobil m ON pny.id_mobil = m.id_mobil
    WHERE pny.id_pelanggan = ? AND $denda_clause $status_clause
");
$stmtDenda->execute([$user_id]);
$dendaTertunggak = $stmtDenda->fetch(PDO::FETCH_ASSOC);

// Deteksi denda aktif dari tabel kondisi_mobil yang belum dibayar
$stmtKondisiDenda = $db->prepare("
    SELECT k.estimasi_biaya
    FROM kondisi_mobil k
    JOIN (
        SELECT id_mobil, MAX(id_penyewaan) as max_pny
        FROM penyewaan
        WHERE id_pelanggan = ?
        GROUP BY id_mobil
    ) p_max ON k.id_mobil = p_max.id_mobil
    WHERE k.estimasi_biaya > 0 AND k.tgl_selesai IS NULL
    LIMIT 1
");
$stmtKondisiDenda->execute([$user_id]);
$kondisiDenda = $stmtKondisiDenda->fetch(PDO::FETCH_ASSOC);

$is_blocked = ($dendaTertunggak || $kondisiDenda) ? true : false; // Status banned aktif jika denda belum lunas

// DETEKSI SEWA AKTIF UNTUK COUNTDOWN & LIMITASI 1 SEWA MAKSIMAL
$stmtActive = $db->prepare("
    SELECT p.*, m.merk_mobil, m.plat_nomor, pen.status_sewa
    FROM penyewaan p
    JOIN mobil m ON p.id_mobil = m.id_mobil
    LEFT JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan
    WHERE p.id_pelanggan = ? AND p.status_penyewaan = 'Confirmed' AND (pen.status_sewa = 'ongoing' OR pen.status_sewa IS NULL)
    LIMIT 1
");
$stmtActive->execute([$user_id]);
$activeRental = $stmtActive->fetch(PDO::FETCH_ASSOC);

// DETEKSI KOLOM TABEL VOUCHER SECARA DINAMIS UNTUK MENCEGAH UNDEFINED ARRAY KEY WARNINGS
$voucher_cols_query = $db->query("DESCRIBE voucher");
$v_cols = $voucher_cols_query->fetchAll(PDO::FETCH_COLUMN);

$col_harga_poin = 'harga_poin';
if (!in_array('harga_poin', $v_cols)) {
    if (in_array('poin', $v_cols)) {
        $col_harga_poin = 'poin';
    } elseif (in_array('harga', $v_cols)) {
        $col_harga_poin = 'harga';
    }
}

$col_tgl_mulai = 'tgl_mulai';
if (!in_array('tgl_mulai', $v_cols)) {
    if (in_array('tanggal_mulai', $v_cols)) {
        $col_tgl_mulai = 'tanggal_mulai';
    } elseif (in_array('tgl_berlaku', $v_cols)) {
        $col_tgl_mulai = 'tgl_berlaku';
    }
}

$col_tgl_selesai = 'tgl_selesai';
if (!in_array('tgl_selesai', $v_cols)) {
    if (in_array('tanggal_selesai', $v_cols)) {
        $col_tgl_selesai = 'tanggal_selesai';
    } elseif (in_array('tgl_expired', $v_cols)) {
        $col_tgl_selesai = 'tgl_expired';
    } elseif (in_array('expired', $v_cols)) {
        $col_tgl_selesai = 'expired';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Member Area - SIREMO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden text-sm">

<?php include __DIR__ . '/../sidebar/pelangganside.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header Atas -->
    <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8 flex-shrink-0">
        <h1 class="text-base font-bold text-gray-800 uppercase flex items-center gap-2">
            <i class="fa-solid fa-car text-indigo-600"></i>
            <span>Menu: <?= str_replace('_', ' ', $action); ?></span>
        </h1>
        <div class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-xl">Level: <?= $user_level_nama; ?></div>
    </header>

    <main class="flex-1 overflow-y-auto p-6">

        <!-- ALERT BLOKIR DENDA -->
        <?php if ($is_blocked): 
            $total_denda = ($dendaTertunggak['biaya_kerusakan'] ?? 0) + 
                          ($dendaTertunggak['denda_telat'] ?? $dendaTertunggak['denda'] ?? 0) +
                          ($kondisiDenda['estimasi_biaya'] ?? 0);
        ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-5 rounded-2xl mb-6 shadow-sm">
                <div class="flex items-center gap-3 text-red-700 font-bold text-sm mb-1">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 animate-pulse text-base"></i>
                    <span>AKUN ANDA DIBLOKIR / BANNED AKTIF!</span>
                </div>
                <p class="text-xs text-red-600 leading-relaxed mb-3">
                    Anda dideteksi memiliki denda tertunggak sebesar
                    <strong>Rp <?= number_format($total_denda); ?></strong>. 
                    Pemesanan sewa baru terkunci otomatis sebelum denda dinyatakan Lunas.
                </p>
            </div>
        <?php endif; ?>

        <!-- ==================== MENU HOME (PADAT DATA & ANALIS) ==================== -->
        <?php if ($action === 'home'): 
            // --- QUERY STATISTIK MEMBER DASHBOARD ---
            $my_total_sewa = $db->prepare("SELECT COUNT(*) FROM penyewaan WHERE id_pelanggan = ?");
            $my_total_sewa->execute([$user_id]);
            $count_sewa = $my_total_sewa->fetchColumn() ?: 0;

            $my_total_pending = $db->prepare("SELECT COUNT(*) FROM penyewaan WHERE id_pelanggan = ? AND status_penyewaan = 'Pending'");
            $my_total_pending->execute([$user_id]);
            $count_pending = $my_total_pending->fetchColumn() ?: 0;

            $my_total_ongoing = $db->prepare("
                SELECT COUNT(*) 
                FROM penyewaan p 
                JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan 
                WHERE p.id_pelanggan = ? AND pen.status_sewa = 'ongoing'
            ");
            $my_total_ongoing->execute([$user_id]);
            $count_ongoing = $my_total_ongoing->fetchColumn() ?: 0;

            $my_total_complete = $db->prepare("
                SELECT COUNT(*) 
                FROM penyewaan p 
                JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan 
                WHERE p.id_pelanggan = ? AND (pen.status_sewa = 'complete' OR pen.status_sewa = 'completed')
            ");
            $my_total_complete->execute([$user_id]);
            $count_complete = $my_total_complete->fetchColumn() ?: 0;

            $my_total_voucher = $db->prepare("SELECT COUNT(*) FROM penukaran_voucher WHERE id_pelanggan = ? AND status_pakai = 'belum_dipakai'");
            $my_total_voucher->execute([$user_id]);
            $count_voucher = $my_total_voucher->fetchColumn() ?: 0;

            $my_total_pengeluaran = $db->prepare("SELECT COALESCE(SUM(total_harga), 0) FROM penyewaan WHERE id_pelanggan = ? AND status_penyewaan != 'Canceled'");
            $my_total_pengeluaran->execute([$user_id]);
            $sum_pengeluaran = $my_total_pengeluaran->fetchColumn() ?: 0;

            $my_total_denda = 0;
            if ($kondisiDenda) {
                $my_total_denda += $kondisiDenda['estimasi_biaya'] ?? 0;
            }
            if ($dendaTertunggak) {
                $my_total_denda += ($dendaTertunggak['biaya_kerusakan'] ?? 0) + ($dendaTertunggak['denda_telat'] ?? $dendaTertunggak['denda'] ?? 0);
            }

            $stmtMyTrans = $db->prepare("
                SELECT p.*, m.merk_mobil, m.plat_nomor 
                FROM penyewaan p 
                JOIN mobil m ON p.id_mobil = m.id_mobil 
                WHERE p.id_pelanggan = ? 
                ORDER BY p.id_penyewaan DESC 
                LIMIT 5
            ");
            $stmtMyTrans->execute([$user_id]);
            $my_transactions = $stmtMyTrans->fetchAll(PDO::FETCH_ASSOC);
        ?>
            <!-- Active Rental Status Countdown -->
            <?php if ($activeRental): ?>
                <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white p-6 rounded-3xl shadow-lg mb-6 flex justify-between items-center">
                    <div>
                        <span class="text-[9px] text-indigo-300 font-bold uppercase tracking-widest block">Unit Sedang Anda Sewa</span>
                        <h2 class="text-xl font-black mt-1"><?= $activeRental['merk_mobil']; ?> <span class="text-xs font-mono bg-indigo-800 px-2 py-0.5 rounded text-indigo-200"><?= $activeRental['plat_nomor']; ?></span></h2>
                        <div class="flex gap-6 mt-4 text-xs">
                            <div>
                                <span class="text-gray-400 block">JATUH TEMPO</span>
                                <span class="font-bold text-white" id="target_tgl"><?= $activeRental['tgl_selesai_sewa']; ?></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">SISA WAKTU</span>
                                <span class="font-black text-amber-400" id="countdown_timer">Menghitung...</span>
                            </div>
                        </div>
                    </div>
                    <i class="fa-solid fa-hourglass-half text-indigo-400 text-3xl animate-spin"></i>
                </div>
                <script>
                    const tglEnd = document.getElementById('target_tgl').innerText;
                    const endTime = new Date(tglEnd + " 23:59:59").getTime();
                    setInterval(function() {
                        const now = new Date().getTime();
                        const sisa = endTime - now;
                        if (sisa < 0) {
                            document.getElementById("countdown_timer").innerText = "MASA SEWA SELESAI / TERLAMBAT";
                        } else {
                            const d = Math.floor(sisa / (1000 * 60 * 60 * 24));
                            const h = Math.floor((sisa % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const m = Math.floor((sisa % (1000 * 60 * 60)) / (1000 * 60));
                            document.getElementById("countdown_timer").innerText = d + " Hari " + h + " Jam " + m + " Menit";
                        }
                    }, 1000);
                </script>
            <?php endif; ?>

            <div class="mb-6">
                <div class="mb-5">
                    <h2 class="text-lg font-black text-gray-800">
                        Ringkasan Akun & Transaksi Anda
                    </h2>
                    <p class="text-xs text-gray-400 mt-1">
                        Informasi lengkap aktivitas sewa Anda di SIREMO
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- TOTAL PENYEWAAN SAYA -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Total Sewa Saya
                            </p>
                            <p class="text-3xl font-black text-gray-800 mt-1">
                                <?= $count_sewa; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Seluruh transaksi sewa
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                            <i class="fa-solid fa-receipt text-lg"></i>
                        </div>
                    </div>

                    <!-- BOOKING PENDING SAYA -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Sewa Pending
                            </p>
                            <p class="text-3xl font-black text-amber-600 mt-1">
                                <?= $count_pending; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Menunggu persetujuan
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                            <i class="fa-solid fa-clock text-lg"></i>
                        </div>
                    </div>

                    <!-- MOBIL SEDANG SAYA SEWA -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Sedang Disewa
                            </p>
                            <p class="text-3xl font-black text-blue-600 mt-1">
                                <?= $count_ongoing; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Unit aktif digunakan
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-car-side text-lg"></i>
                        </div>
                    </div>

                    <!-- TRANSAKSI SELESAI SAYA -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Sewa Selesai
                            </p>
                            <p class="text-3xl font-black text-green-600 mt-1">
                                <?= $count_complete; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Riwayat sewa selesai
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </div>
                    </div>

                    <!-- LOYALTY POIN SAYA -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Sisa Poin Akun
                            </p>
                            <p class="text-3xl font-black text-indigo-600 mt-1">
                                <?= number_format($user_poin); ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Sisa poin Anda
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                            <i class="fa-solid fa-star text-lg"></i>
                        </div>
                    </div>

                    <!-- TOTAL VOUCHER SAYA -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Voucher Tersedia
                            </p>
                            <p class="text-3xl font-black text-purple-600 mt-1">
                                <?= $count_voucher; ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Voucher belum terpakai
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600">
                            <i class="fa-solid fa-ticket text-lg"></i>
                        </div>
                    </div>

                    <!-- TOTAL PENGELUARAN SAYA -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Total Pengeluaran
                            </p>
                            <p class="text-xl font-black text-green-600 mt-2">
                                Rp <?= number_format($sum_pengeluaran, 0, ',', '.'); ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Total transaksi rental
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                            <i class="fa-solid fa-money-bill-wave text-lg"></i>
                        </div>
                    </div>

                    <!-- TOTAL DENDA AKTIF SAYA -->
                    <div class="bg-white p-5 rounded-2xl border shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Total Denda Saya
                            </p>
                            <p class="text-xl font-black text-red-600 mt-2">
                                Rp <?= number_format($my_total_denda, 0, ',', '.'); ?>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Denda aktif belum lunas
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-600">
                            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABEL AKTIVITAS TRANSAKSI SAYA -->
            <div class="bg-white p-6 rounded-2xl border shadow-sm mb-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm">
                            Aktivitas Transaksi Saya
                        </h3>
                        <p class="text-[11px] text-gray-400 mt-1">
                            5 transaksi penyewaan terbaru Anda di SIREMO
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-500">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="p-4 font-bold">
                                    Kode Transaksi
                                </th>
                                <th class="p-4 font-bold">
                                    Mobil
                                </th>
                                <th class="p-4 font-bold">
                                    Periode Sewa
                                </th>
                                <th class="p-4 font-bold text-right">
                                    Total
                                </th>
                                <th class="p-4 font-bold text-center">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($my_transactions)): ?>
                                <?php foreach ($my_transactions as $transaksi): ?>
                                    <tr class="border-b hover:bg-gray-50/50">
                                        <td class="p-4">
                                            <p class="font-bold text-indigo-600">
                                                <?= htmlspecialchars($transaksi['kode_penyewaan']); ?>
                                            </p>
                                            <p class="text-[10px] text-gray-400 mt-1">
                                                <?= !empty($transaksi['tgl_penyewaan']) ? date('d/m/Y H:i', strtotime($transaksi['tgl_penyewaan'])) : '-'; ?>
                                            </p>
                                        </td>
                                        <td class="p-4">
                                            <p class="font-bold text-gray-700">
                                                <?= htmlspecialchars($transaksi['merk_mobil']); ?>
                                            </p>
                                            <p class="text-[10px] text-gray-400 font-mono">
                                                <?= htmlspecialchars($transaksi['plat_nomor']); ?>
                                            </p>
                                        </td>
                                        <td class="p-4">
                                            <?= date('d/m/Y', strtotime($transaksi['tgl_mulai_sewa'])); ?>
                                            <span class="text-gray-300 mx-1">-</span>
                                            <?= date('d/m/Y', strtotime($transaksi['tgl_selesai_sewa'])); ?>
                                        </td>
                                        <td class="p-4 text-right font-bold text-gray-800">
                                            Rp <?= number_format($transaksi['total_harga'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="p-4 text-center">
                                            <?php
                                            $status = $transaksi['status_penyewaan'];
                                            if ($status === 'Pending') {
                                                $statusClass = 'bg-amber-100 text-amber-700';
                                            } elseif ($status === 'Confirmed') {
                                                $statusClass = 'bg-green-100 text-green-700';
                                            } elseif ($status === 'Canceled') {
                                                $statusClass = 'bg-red-100 text-red-700';
                                            } else {
                                                $statusClass = 'bg-gray-100 text-gray-600';
                                            }
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?= $statusClass; ?>">
                                                <?= htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-10 text-center italic text-gray-400">
                                        Belum ada aktivitas transaksi penyewaan yang terdaftar.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Rekomendasi Mobil Hemat -->
            <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-3">Rekomendasi Terbaik Untukmu</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php
                $stmtRek = $db->query("SELECT * FROM mobil WHERE status_mobil = 'Tersedia' LIMIT 3");
                $reks = $stmtRek->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($reks)): foreach($reks as $r): ?>
                    <div class="bg-white rounded-2xl border p-4 shadow-sm">
                        <div class="h-32 bg-gray-100 rounded-xl mb-3 overflow-hidden">
                            <?php if(!empty($r['gambar'])): ?>
                                <img src="data:image/jpeg;base64,<?= base64_encode($r['gambar']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fa-solid fa-car text-3xl"></i></div>
                            <?php endif; ?>
                        </div>
                        <h4 class="font-bold text-gray-800"><?= $r['merk_mobil']; ?></h4>
                        <p class="text-indigo-600 font-bold text-xs mt-1">Rp <?= number_format($r['harga_dinamis']); ?> / Hari</p>
                    </div>
                <?php endforeach; else: ?>
                    <div class="col-span-3 p-8 text-center text-gray-400 italic">Belum ada data rekomendasi mobil yang tersedia.</div>
                <?php endif; ?>
            </div>
        <?php elseif ($action === 'gallery'): ?>
            <?php if ($is_blocked): ?>
                <div class="bg-white p-12 text-center rounded-2xl border italic text-gray-400">Gallery terkunci karena denda tertunggak.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php
                    // PERBAIKAN: Mengambil semua mobil atau disesuaikan agar status selain 'Tersedia' tetap muncul di gallery
                    $stmtM = $db->query("SELECT * FROM mobil WHERE status_mobil = 'Tersedia' ORDER BY id_mobil DESC");
                    $mobils = $stmtM->fetchAll(PDO::FETCH_ASSOC);
                    if(!empty($mobils)): foreach($mobils as $m): ?>
                        <div class="bg-white rounded-3xl border shadow-sm overflow-hidden group hover:shadow-md transition relative">
                            
                            <div class="absolute top-3 left-3 z-10">
                                <?php if ($m['status_mobil'] === 'Tersedia'): ?>
                                    <span class="bg-emerald-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg shadow-sm">
                                        <i class="fa-solid fa-circle-check mr-1"></i> Tersedia
                                    </span>
                                <?php elseif ($m['status_mobil'] === 'Disewa'): ?>
                                    <span class="bg-amber-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg shadow-sm">
                                        <i class="fa-solid fa-car-side mr-1"></i> Sedang Disewa
                                    </span>
                                <?php elseif ($m['status_mobil'] === 'Maintenance'): ?>
                                    <span class="bg-rose-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg shadow-sm">
                                        <i class="fa-solid fa-wrench mr-1"></i> Perbaikan
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="h-44 bg-gray-100 relative">
                                <?php if (!empty($m['gambar']) && @base64_encode($m['gambar'])): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($m['gambar']); ?>" class="w-full h-full object-cover <?= $m['status_mobil'] !== 'Tersedia' ? 'brightness-75' : ''; ?>">
                                <?php else: ?>
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                                        <i class="fa-solid fa-car text-5xl"></i>
                                        <span class="text-[9px] font-black uppercase mt-1">No Image Preview</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-5 space-y-3">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-gray-800 text-sm"><?= $m['merk_mobil']; ?></h3>
                                    <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase"><?= $m['nama_kategori']; ?></span>
                                </div>
                                <div class="text-[10px] text-gray-400 border-t pt-2 mt-2">
                                    <span class="block">Plat Nomor: <strong><?= $m['plat_nomor']; ?></strong></span>
                                    <span class="block">Warna: <strong><?= $m['warna']; ?></strong></span>
                                </div>
                                <p class="text-lg font-black text-indigo-600">Rp <?= number_format($m['harga_dinamis']); ?> <span class="text-xs text-gray-400 font-normal">/hari</span></p>
                                <div class="grid grid-cols-2 gap-1 text-[10px] text-gray-400 border-t pt-2 mt-2">
                                    <span>CC: <strong><?= number_format($m['cc']); ?> cc</strong></span>
                                    <span>Tahun: <strong><?= $m['tahun']; ?></strong></span>
                                </div>
                                
                                <?php if($activeRental): ?>
                                    <button disabled class="w-full py-2.5 bg-gray-100 text-gray-400 text-xs font-bold rounded-xl cursor-not-allowed">Ada Sewa Aktif</button>
                                <?php else: ?>
                                    <?php if ($m['status_mobil'] === 'Tersedia'): ?>
                                        <a href="index.php?page=home&action=form_sewa&id=<?= $m['id_mobil']; ?>" class="block text-center w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-md shadow-indigo-100">Sewa Sekarang</a>
                                    <?php elseif ($m['status_mobil'] === 'Disewa'): ?>
                                        <button disabled class="w-full py-2.5 bg-amber-50 text-amber-600 text-xs font-bold rounded-xl cursor-not-allowed border border-amber-200">Tidak Tersedia (Disewa)</button>
                                    <?php elseif ($m['status_mobil'] === 'Maintenance'): ?>
                                        <button disabled class="w-full py-2.5 bg-rose-50 text-rose-600 text-xs font-bold rounded-xl cursor-not-allowed border border-rose-200">Dalam Perbaikan</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="col-span-3 p-12 text-center text-gray-400 italic">Belum ada armada mobil yang terdaftar di gallery.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

<?php elseif ($action === 'voucher'): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php
        $stmtV = $db->query("SELECT * FROM voucher WHERE status = 'Aktif' ORDER BY id_voucher DESC");
        $vouchers = $stmtV->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($vouchers)): foreach($vouchers as $v): ?>
            
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden group hover:shadow-md transition flex flex-col justify-between">
                
                <div class="bg-gradient-to-r from-indigo-50/80 to-slate-50 text-center py-4 px-5 border-b border-dashed border-gray-200 relative">
                    <h3 class="font-black text-gray-800 text-sm uppercase tracking-wider">
                        <?= htmlspecialchars($v['nama_voucher']); ?>
                    </h3>
                    <div class="absolute -left-2 -bottom-2 w-4 h-4 bg-slate-50 border-r border-gray-200 rounded-full hidden md:block"></div>
                    <div class="absolute -right-2 -bottom-2 w-4 h-4 bg-slate-50 border-l border-gray-200 rounded-full hidden md:block"></div>
                </div>

                <div class="p-6 space-y-4 flex-1 flex flex-col justify-between">
                    <div class="text-center space-y-2">
                        <p class="text-2xl font-black text-indigo-600">
                            <?= htmlspecialchars(floatval($v['diskon_persen'])); ?>%
                            <span class="text-xs text-indigo-400 font-normal tracking-wide">Diskon</span>
                        </p>
                        
                        <span class="inline-block bg-slate-50 border border-gray-200 text-gray-500 font-mono text-[11px] px-3 py-1 rounded-xl">
                            Kode: <strong class="text-gray-800 uppercase"><?= htmlspecialchars($v['kode_voucher']); ?></strong>
                        </span>
                    </div>

                    <div class="space-y-1.5 text-[11px] text-gray-400 border-t border-gray-100 pt-3.5">
                        <div class="flex justify-between items-center">
                            <span>Harga Poin:</span>
                            <strong class="text-gray-600 font-semibold"><?= number_format($v[$col_harga_poin] ?? 0); ?> pts</strong>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Mulai Berlaku:</span>
                            <strong class="text-gray-600 font-semibold"><?= !empty($v[$col_tgl_mulai]) ? date('d M Y', strtotime($v[$col_tgl_mulai])) : '-'; ?></strong>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Hingga Sampai:</span>
                            <strong class="text-gray-600 font-semibold"><?= !empty($v[$col_tgl_selesai]) ? date('d M Y', strtotime($v[$col_tgl_selesai])) : '-'; ?></strong>
                        </div>
                    </div>

                    <a href="index.php?page=home&action=redeem_voucher&id=<?= $v['id_voucher'] ?>" 
                       class="block text-center bg-blue-600 text-white font-semibold py-2 px-4 rounded-xl hover:bg-blue-700 transition w-full">
                        Tukar Voucher
                    </a>
                </div> </div> <?php endforeach; else: ?>
            <div class="col-span-3 p-12 text-center text-gray-400 italic">Belum ada voucher aktif yang tersedia.</div>
        <?php endif; ?>
    </div>

<?php elseif ($action === 'redeem_voucher'): ?>
    <?php
    // 1. Ambil ID Voucher dari parameter URL GET
    $id_voucher = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    // 2. Koneksi database & ambil data voucher
    $db = Database::getConnection();
    $stmtVoucher = $db->prepare("SELECT * FROM voucher WHERE id_voucher = ? LIMIT 1");
    $stmtVoucher->execute([$id_voucher]);
    $voucher = $stmtVoucher->fetch(PDO::FETCH_ASSOC);

    // 3. Ambil data pelanggan untuk menampilkan poin saat ini
    $id_pelanggan = $_SESSION['user_id'] ?? 0;
    $stmtPelanggan = $db->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ? LIMIT 1");
    $stmtPelanggan->execute([$id_pelanggan]);
    $pelanggan = $stmtPelanggan->fetch(PDO::FETCH_ASSOC);

    // Proteksi jika data tidak ditemukan
    if (!$voucher || !$pelanggan) {
        echo "<div class='p-6 text-center text-red-500 font-bold'>Data voucher atau pelanggan tidak valid!</div>";
        echo "<script>window.location.href='index.php?page=home&action=voucher';</script>";
        exit;
    }

    // Kalkulasi kecukupan poin
    $sisa_poin = $pelanggan['poin'] - ($voucher[$col_harga_poin] ?? 0);
    $is_poin_cukup = $sisa_poin >= 0;
    ?>

    <div class="max-w-2xl mx-auto my-10 px-4">
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-2xl font-bold text-sm shadow-sm flex items-center gap-2">
                <span>⚠️</span> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-4 bg-emerald-100 border border-emerald-300 text-emerald-700 rounded-2xl font-bold text-sm shadow-sm flex items-center gap-2">
                <span>✅</span> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6 text-white text-center">
                <span class="text-xs uppercase font-bold tracking-widest bg-white/20 px-3 py-1 rounded-full">Konfirmasi Penukaran</span>
                <h2 class="text-2xl font-black mt-2">Apakah Anda Yakin?</h2>
                <p class="text-blue-100 text-xs mt-1">Silakan periksa detail penukaran voucher di bawah ini.</p>
            </div>

            <form action="index.php?page=proses_redeem_voucher" method="POST" class="p-8 space-y-6">
                <input type="hidden" name="id_voucher" value="<?= $voucher['id_voucher'] ?>">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Voucher</label>
                    <input type="text" value="<?= htmlspecialchars($voucher['nama_voucher']) ?>" 
                           class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 font-semibold focus:outline-none cursor-not-allowed" 
                           readonly>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Poin Anda Sekarang</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 text-sm font-bold">⭐</span>
                            <input type="text" value="<?= number_format($pelanggan['poin']) ?> Poin" 
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm text-gray-700 font-bold focus:outline-none cursor-not-allowed" 
                                   readonly>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-red-500 uppercase tracking-wider mb-2">Biaya Poin</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-red-500 text-sm font-bold">-</span>
                            <input type="text" value="<?= number_format($voucher[$col_harga_poin] ?? 0) ?> Poin" 
                                   class="w-full bg-red-50 border border-red-200 rounded-xl pl-10 pr-4 py-3 text-sm text-red-600 font-bold focus:outline-none cursor-not-allowed" 
                                   readonly>
                        </div>
                    </div>
                </div>

                <hr class="border-dashed border-gray-200 my-4">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Estimasi Sisa Poin Anda</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500 text-sm font-bold">✨</span>
                        <input type="text" value="<?= $is_poin_cukup ? number_format($sisa_poin) . ' Poin' : 'Poin Tidak Cukup!' ?>" 
                               class="w-full <?= $is_poin_cukup ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-100 border-red-300 text-red-600 font-black' ?> border rounded-xl pl-10 pr-4 py-3 text-sm font-bold focus:outline-none cursor-not-allowed" 
                               readonly>
                    </div>
                </div>

                <?php if (!$is_poin_cukup): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-xs font-bold text-red-800">Maaf, Poin Anda Kurang!</p>
                                <p class="text-[11px] text-red-700 mt-1">Anda membutuhkan minimal <?= number_format(($voucher[$col_harga_poin] ?? 0) - $pelanggan['poin']) ?> poin tambahan untuk menukar voucher ini.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                    <a href="index.php?page=home&action=voucher"
                       class="w-full sm:w-1/2 text-center bg-gray-100 text-gray-700 font-semibold py-3 px-4 rounded-xl hover:bg-gray-200 transition text-sm">
                        Kembali
                    </a>

                    <button type="submit" 
                            class="w-full sm:w-1/2 bg-blue-600 text-white font-semibold py-3 px-4 rounded-xl hover:bg-blue-700 transition text-sm flex items-center justify-center gap-2 shadow-lg shadow-blue-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                            <?= !$is_poin_cukup ? 'disabled' : '' ?>>
                        <span>Ya, Konfirmasi Tukar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

        <!-- ==================== FORM SEWA KOMPLET ==================== -->
<?php elseif ($action === 'form_sewa'):
            $id_mobil = $_GET['id'] ?? 0;
            $stmtMob = $db->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
            $stmtMob->execute([$id_mobil]);
            $m = $stmtMob->fetch(PDO::FETCH_ASSOC);
        ?>
            <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-base border-b pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-file-signature text-indigo-600"></i>
                        <span>Formulir Pengajuan Sewa: <?= htmlspecialchars($m['merk_mobil']); ?></span>
                    </h3>
                    
                    <form action="index.php?page=proses_sewa" method="POST" enctype="multipart/form-data" class="space-y-4" id="formRent">
                        <input type="hidden" name="id_mobil" id="id_mobil" value="<?= $id_mobil; ?>">
                        <input type="hidden" id="harga_harian" value="<?= $m['harga_dinamis']; ?>">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Mulai</label>
                                <input type="date" name="tgl_mulai" id="tgl_mulai" min="<?= date('Y-m-d'); ?>" required class="w-full border p-2.5 rounded-xl text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Selesai</label>
                                <input type="date" name="tgl_selesai" id="tgl_selesai" min="<?= date('Y-m-d'); ?>" required class="w-full border p-2.5 rounded-xl text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Fasilitas Tambahan (Opsional)</label>
                            <select name="id_fasilitas" id="id_fasilitas" class="w-full border p-2.5 rounded-xl text-xs bg-white outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="0" data-harga="0">-- Tanpa Fasilitas Tambahan --</option>
                                <?php 
                                // PERBAIKAN: Mengambil fasilitas yang berstatus 'Tersedia' (tidak sensitif huruf besar/kecil) dan stok lebih dari 0
                                $stmtF = $db->query("SELECT * FROM fasilitas WHERE (LOWER(status) = 'tersedia' OR status IS NULL OR status = '') AND (stok > 0 OR stok IS NULL)");
                                while($f = $stmtF->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= $f['id_fasilitas']; ?>" data-harga="<?= $f['harga']; ?>"><?= htmlspecialchars($f['nama_fasilitas']); ?> (+Rp <?= number_format($f['harga']); ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Gunakan Voucher Saya (Diskon)</label>
                            <select name="id_penukaran" id="id_penukaran" class="w-full border p-2.5 rounded-xl text-xs bg-white outline-none focus:ring-2 focus:ring-indigo-500 text-indigo-600 font-bold">
                                <option value="0" data-diskon="0">-- Pilih Voucher --</option>
                                <?php 
                                $stmtV = $db->prepare("SELECT pv.*, v.nama_voucher, v.diskon_persen FROM penukaran_voucher pv JOIN voucher v ON pv.id_voucher = v.id_voucher WHERE pv.id_pelanggan = ? AND pv.status_pakai = 'belum_dipakai'");
                                $stmtV->execute([$user_id]);
                                while($v = $stmtV->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= $v['id_penukaran']; ?>" data-diskon="<?= $v['diskon_persen']; ?>"><?= htmlspecialchars($v['nama_voucher']); ?> (Diskon <?= $v['diskon_persen']; ?>%)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Upload Bukti Pembayaran</label>
                            <input type="file" name="bukti_bayar" id="bukti_bayar" accept="image/*" required 
                                class="w-full border p-2 rounded-xl text-xs bg-white outline-none focus:ring-2 focus:ring-indigo-500 
                                       file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs 
                                       file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 mt-1">* Format yang didukung: JPG, JPEG, PNG.</p>
                        </div>

                        <div class="p-3 bg-orange-50 border border-orange-200 rounded-2xl flex justify-between items-center text-xs">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-shield-halved text-orange-500 text-lg"></i>
                                <div>
                                    <span class="font-bold text-orange-800 block">Aktifkan Proteksi Asuransi</span>
                                    <span class="text-[10px] text-orange-600 block">Menutup denda jika terjadi goresan/insiden di jalan.</span>
                                </div>
                            </div>
                            <input type="checkbox" name="pake_asuransi" id="pake_asuransi" value="1" class="w-4 h-4 accent-indigo-600">
                        </div>

                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-indigo-100">
                            Kirim Pengajuan Rental
                        </button>
                    </form>
                </div>

                <div class="bg-slate-900 text-white p-5 rounded-3xl h-fit space-y-4">
                    <h4 class="font-bold text-xs uppercase tracking-widest text-slate-400 border-b border-slate-800 pb-2">Kalkulasi Ringkasan Biaya</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Sewa (<span id="disp_durasi">0</span> hari)</span>
                            <span id="disp_sewa">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Fasilitas Tambahan</span>
                            <span id="disp_fasilitas">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Asuransi Proteksi</span>
                            <span id="disp_asuransi">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-green-400 font-bold border-t border-slate-800 pt-2">
                            <span>Diskon Voucher</span>
                            <span id="disp_diskon">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-indigo-400 text-lg font-black border-t border-slate-800 pt-3">
                            <span>GRAND TOTAL:</span>
                            <span id="disp_total">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- JAVASCRIPT KALKULATOR -->
            <script>
                const tglM = document.getElementById('tgl_mulai');
                const tglS = document.getElementById('tgl_selesai');
                const selF = document.getElementById('id_fasilitas');
                const selV = document.getElementById('id_penukaran');
                const chkAsuransi = document.getElementById('pake_asuransi');
                
                const hargaHarian = parseInt(document.getElementById('harga_harian').value);

                function hitungTotalSewa() {
                    let start = new Date(tglM.value);
                    let end = new Date(tglS.value);
                    let durasi = 0;

                    if (tglM.value && tglS.value && end >= start) {
                        let diff = Math.abs(end - start);
                        durasi = Math.ceil(diff / (1000 * 60 * 60 * 24)) + 1;
                    }

                    let totalSewa = durasi * hargaHarian;
                    let hargaFasilitas = parseInt(selF.options[selF.selectedIndex].getAttribute('data-harga') || 0);
                    let diskonPersen = parseFloat(selV.options[selV.selectedIndex].getAttribute('data-diskon') || 0);
                    let biayaAsuransi = chkAsuransi.checked ? 50000 : 0;

                    let subTotal = totalSewa + hargaFasilitas + biayaAsuransi;
                    let potonganDiskon = subTotal * (diskonPersen / 100);
                    let grandTotal = subTotal - potonganDiskon;

                    document.getElementById('disp_durasi').innerText = durasi;
                    document.getElementById('disp_sewa').innerText = "Rp " + totalSewa.toLocaleString('id-ID');
                    document.getElementById('disp_fasilitas').innerText = "Rp " + hargaFasilitas.toLocaleString('id-ID');
                    document.getElementById('disp_asuransi').innerText = "Rp " + biayaAsuransi.toLocaleString('id-ID');
                    document.getElementById('disp_diskon').innerText = "-Rp " + potonganDiskon.toLocaleString('id-ID');
                    document.getElementById('disp_total').innerText = "Rp " + grandTotal.toLocaleString('id-ID');
                }

                [tglM, tglS, selF, selV, chkAsuransi].forEach(element => {
                    element.addEventListener('change', hitungTotalSewa);
                });
            </script>

        <!-- ==================== TAB SEWA SAYA (PERBAIKAN STATUS DI SEWA) ==================== -->
        <?php elseif ($action === 'sewa_saya'): ?>
            <div class="bg-white p-6 rounded-2xl border">
                <h3 class="font-bold text-gray-800 mb-4">Sewa Saya (Aktif / Pending)</h3>
                <div class="space-y-4">
                    <?php
                    $stmtMy = $db->prepare("
                        SELECT p.*, m.merk_mobil, m.plat_nomor 
                        FROM penyewaan p 
                        JOIN mobil m ON p.id_mobil = m.id_mobil 
                        LEFT JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan
                        WHERE p.id_pelanggan = ? 
                          AND p.status_penyewaan != 'Canceled' 
                          AND (pen.id_penyerahan IS NULL OR (pen.status_sewa != 'complete' AND pen.status_sewa != 'completed'))
                        ORDER BY p.id_penyewaan DESC
                    ");
                    $stmtMy->execute([$user_id]);
                    $myRents = $stmtMy->fetchAll(PDO::FETCH_ASSOC);

                    if(!empty($myRents)): foreach($myRents as $r): ?>
                        <div class="p-4 bg-gray-50 rounded-xl border flex justify-between items-center text-xs">
                            <div>
                                <span class="font-bold text-gray-800 text-sm block"><?= $r['merk_mobil']; ?> (<?= $r['plat_nomor']; ?>)</span>
                                <span class="text-gray-400">Periode: <?= $r['tgl_mulai_sewa']; ?> s.d <?= $r['tgl_selesai_sewa']; ?> (<?= $r['durasi_hari']; ?> Hari)</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-indigo-600 block">Rp <?= number_format($r['total_harga']); ?></span>
                                <?php
                                // PERBAIKAN: Jika status transaksi 'Confirmed', render lencana tulisan lokal "Di Sewa"
                                $status = $r['status_penyewaan'];
                                if ($status === 'Confirmed') {
                                    $displayStatus = "Di Sewa";
                                    $badgeColor = "bg-green-100 text-green-700 border-green-200";
                                } elseif ($status === 'Pending') {
                                    $displayStatus = "Pending";
                                    $badgeColor = "bg-yellow-100 text-yellow-700 border-yellow-200";
                                } else {
                                    $displayStatus = "Canceled";
                                    $badgeColor = "bg-red-100 text-red-700 border-red-200";
                                }
                                ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider inline-block mt-1 border <?= $badgeColor; ?>"><?= $displayStatus; ?></span>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="py-12 text-center text-gray-400 italic">Belum ada pengajuan sewa mobil terdaftar.</div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== TAB HISTORY SEWA ==================== -->
        <?php elseif ($action === 'history_sewa'): ?>
            <div class="bg-white p-6 rounded-2xl border">
                <h3 class="font-bold text-gray-800 mb-4">History Sewa (Selesai)</h3>
                <div class="space-y-4">
                    <?php
                    $stmtHist = $db->prepare("
                        SELECT p.*, m.id_mobil, m.merk_mobil, m.plat_nomor, m.status_mobil, pen.tgl_penyerahan
                        FROM penyewaan p
                        JOIN mobil m ON p.id_mobil = m.id_mobil
                        JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan
                        WHERE p.id_pelanggan = ? AND (pen.status_sewa = 'complete' OR pen.status_sewa = 'completed')
                        ORDER BY p.id_penyewaan DESC
                    ");
                    $stmtHist->execute([$user_id]);
                    $histories = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

                    if(!empty($histories)): foreach($histories as $h): 
                        $is_available = ($h['status_mobil'] === 'Tersedia');
                        $cardStyle = $is_available ? 'bg-gray-50 border-gray-200' : 'bg-gray-100 border-gray-200 text-gray-400 opacity-60 select-none';
                        $textStyle = $is_available ? 'text-gray-800' : 'text-gray-400';
                        $titleStyle = $is_available ? 'text-gray-800' : 'text-gray-500';
                    ?>
                        <div class="p-4 rounded-xl border flex justify-between items-center text-xs <?= $cardStyle; ?>">
                            <div class="space-y-1">
                                <span class="font-bold text-sm block <?= $titleStyle; ?>"><?= htmlspecialchars($h['merk_mobil']); ?> (<?= htmlspecialchars($h['plat_nomor']); ?>)</span>
                                <span class="text-gray-400 block">Kembali pada: <?= htmlspecialchars($h['tgl_penyerahan']); ?></span>
                                <span class="text-[10px] block font-semibold <?= $is_available ? 'text-green-600' : 'text-red-500'; ?>">
                                    Status Mobil: <?= $is_available ? 'Tersedia' : 'Tidak Tersedia (' . htmlspecialchars($h['status_mobil']) . ')'; ?>
                                </span>
                            </div>
                            <div class="text-right flex flex-col items-end gap-1.5">
                                <span class="font-bold block <?= $textStyle; ?>">Rp <?= number_format($h['total_harga']); ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase inline-block">Selesai / Lunas</span>
                                
                                <?php if ($is_available): ?>
                                    <a href="index.php?page=home&action=form_sewa&id=<?= $h['id_mobil']; ?>" 
                                       class="mt-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded-xl transition shadow text-[10px] inline-block">
                                        Sewa Lagi
                                    </a>
                                <?php else: ?>
                                    <button disabled 
                                            class="mt-1 bg-gray-200 text-gray-400 font-bold px-3 py-1.5 rounded-xl text-[10px] cursor-not-allowed border border-gray-300">
                                        Belum Tersedia
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="py-12 text-center text-gray-400 italic">Belum ada riwayat transaksi sewa selesai.</div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== TAB DENDA SAYA ==================== -->
        <?php elseif ($action === 'denda_saya'): ?>
            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                    <span>Tunggakan & Riwayat Denda Saya</span>
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-500 border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="p-4 font-bold">Mobil</th>
                                <th class="p-4 font-bold">Denda Telat</th>
                                <th class="p-4 font-bold">Denda Rusak</th>
                                <th class="p-4 font-bold text-center">Status</th>
                                <th class="p-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmtD = $db->prepare("
                                SELECT k.id_kondisi, k.estimasi_biaya AS biaya_kerusakan, k.tgl_selesai, m.merk_mobil, m.plat_nomor
                                FROM kondisi_mobil k
                                JOIN mobil m ON k.id_mobil = m.id_mobil
                                JOIN (
                                    SELECT id_mobil, MAX(id_penyewaan) as max_pny
                                    FROM penyewaan
                                    WHERE id_pelanggan = ?
                                    GROUP BY id_mobil
                                ) p_max ON k.id_mobil = p_max.id_mobil
                            ");
                            $stmtD->execute([$user_id]);
                            $dendas = $stmtD->fetchAll(PDO::FETCH_ASSOC);

                            if(!empty($dendas)): foreach($dendas as $d): 
                                $is_lunas = !empty($d['tgl_selesai']);
                            ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($d['merk_mobil']); ?> (<?= htmlspecialchars($d['plat_nomor']); ?>)</td>
                                <td class="p-4 text-gray-600">Rp 0</td>
                                <td class="p-4 text-red-600 font-bold">Rp <?= number_format($d['biaya_kerusakan'] ?? 0); ?></td>
                                <td class="p-4 text-center">
                                    <?php if ($is_lunas): ?>
                                        <span class="px-2.5 py-1 rounded-xl bg-green-100 text-green-700 font-bold text-[10px]">LUNAS</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-xl bg-red-100 text-red-700 font-bold text-[10px]">BELUM LUNAS</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <?php if (!$is_lunas): ?>
                                        <a href="index.php?page=home&action=proses_bayar_denda&id_kondisi=<?= $d['id_kondisi']; ?>" 
                                           onclick="return confirm('Lunasi sekarang?');" 
                                           class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded-xl transition shadow text-[10px] inline-block">
                                            Bayar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-green-600 font-bold text-xs"><i class="fa-solid fa-circle-check"></i> Terbayar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="5" class="p-8 text-center italic text-gray-400">Anda tidak memiliki tunggakan denda.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== TAB VOUCHER SAYA ==================== -->
<?php elseif ($action === 'voucher_saya'): ?>
            <?php
            // 1. Ambil seluruh data penukaran voucher milik pelanggan
            $stmtVouc = $db->prepare("
                SELECT pv.id_penukaran, pv.status_pakai, v.kode_voucher, v.nama_voucher, v.diskon_persen 
                FROM penukaran_voucher pv
                JOIN voucher v ON pv.id_voucher = v.id_voucher
                WHERE pv.id_pelanggan = ?
                ORDER BY pv.id_penukaran DESC
            ");
            $stmtVouc->execute([$user_id]);
            $all_vouchers = $stmtVouc->fetchAll(PDO::FETCH_ASSOC);

            // 2. Pisahkan data ke dalam kelompok array berdasarkan status pakai
            $vouchers_belum = [];
            $vouchers_sudah = [];
            foreach ($all_vouchers as $v) {
                if ($v['status_pakai'] === 'belum_dipakai') {
                    $vouchers_belum[] = $v;
                } else {
                    $vouchers_sudah[] = $v;
                }
            }
            ?>

            <div class="bg-white p-6 rounded-2xl border shadow-sm">
                <h3 class="font-bold text-gray-800 text-base mb-6 border-b pb-3">Daftar Koleksi Voucher Saya</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-indigo-100 pb-2">
                            <h4 class="font-bold text-indigo-600 text-xs flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                Belum Dipakai (<?= count($vouchers_belum); ?>)
                            </h4>
                        </div>
                        
                        <div class="space-y-3">
                            <?php if(!empty($vouchers_belum)): foreach($vouchers_belum as $v): ?>
                                <div class="p-4 rounded-xl border bg-indigo-50/40 border-indigo-100 flex justify-between items-center text-xs transition hover:border-indigo-300">
                                    <div class="space-y-1">
                                        <span class="font-mono font-black text-indigo-700 block text-sm tracking-wide"><?= htmlspecialchars($v['kode_voucher']); ?></span>
                                        <span class="text-gray-700 font-bold block mt-0.5"><?= htmlspecialchars($v['nama_voucher']); ?></span>
                                        <span class="text-[10px] text-gray-400 uppercase tracking-widest block mt-0.5">Potongan Harga: <?= htmlspecialchars(floatval($v['diskon_persen'])); ?>%</span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase bg-indigo-100 text-indigo-700 tracking-wider shadow-sm">
                                        Belum Dipakai
                                    </span>
                                </div>
                            <?php endforeach; else: ?>
                                <div class="py-10 text-center text-gray-400 text-xs italic bg-slate-50 rounded-xl border border-dashed">
                                    Tidak ada voucher aktif yang tersedia.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                            <h4 class="font-bold text-gray-500 text-xs flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                Sudah Terpakai / Hangus (<?= count($vouchers_sudah); ?>)
                            </h4>
                        </div>
                        
                        <div class="space-y-3">
                            <?php if(!empty($vouchers_sudah)): foreach($vouchers_sudah as $v): ?>
                                <div class="p-4 rounded-xl border bg-gray-50 border-gray-200 flex justify-between items-center text-xs opacity-65 select-none">
                                    <div class="space-y-1">
                                        <span class="font-mono font-bold text-gray-400 block text-sm line-through tracking-wide"><?= htmlspecialchars($v['kode_voucher']); ?></span>
                                        <span class="text-gray-400 font-medium block mt-0.5 line-through"><?= htmlspecialchars($v['nama_voucher']); ?></span>
                                        <span class="text-[10px] text-gray-400 block mt-0.5">Potongan Harga: <?= htmlspecialchars(floatval($v['diskon_persen'])); ?>%</span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase bg-gray-200 text-gray-400 tracking-wider">
                                        Sudah Dipakai
                                    </span>
                                </div>
                            <?php endforeach; else: ?>
                                <div class="py-10 text-center text-gray-400 text-xs italic bg-slate-50 rounded-xl border border-dashed">
                                    Belum ada riwayat penggunaan voucher.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        <!-- ==================== TAB AKUN SAYA ==================== -->
        <?php elseif ($action === 'akun_saya'): ?>
            <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Member Card Premium -->
                <div class="bg-gradient-to-br from-[#1E293B] to-[#0F172A] text-white p-8 rounded-3xl shadow-xl flex flex-col justify-between h-64 relative overflow-hidden border border-slate-800">
                    <div class="absolute -right-10 -bottom-10 w-44 h-36 bg-indigo-500/10 rounded-full blur-2xl"></div>
                    <div class="flex justify-between items-start z-10">
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-[0.2em] text-indigo-400">SIREMO PREMIUM</span>
                            <h2 class="text-2xl font-black mt-1.5"><?= htmlspecialchars($userData['nama_lengkap']); ?></h2>
                        </div>
                        <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center font-black text-lg text-white uppercase shadow-md shadow-indigo-500/30">
                            <?= substr($userData['nama_lengkap'], 0, 1); ?>
                        </div>
                    </div>
                    <div class="text-left z-10">
                        <span class="text-[11px] text-indigo-300 uppercase block tracking-wider font-bold">Poin Anda</span>
                        <span class="text-3xl font-black text-amber-400 mt-1 inline-block">
                            <?= number_format($userData['poin'] ?? 0, 0, ',', '.'); ?>
                            <span class="text-xs font-bold text-white">Pts</span>
                        </span>
                    </div>
                    <div class="z-10">
                        <span class="text-[10px] text-slate-400 uppercase block tracking-wider font-semibold">Status Loyalitas</span>
                        <span class="text-base font-black text-indigo-300 uppercase tracking-widest mt-1 inline-block">★ <?= $user_level_nama; ?></span>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white p-6 rounded-3xl border shadow-sm">
                    <h3 class="font-bold text-gray-800 text-sm mb-4 border-b pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-user-pen text-indigo-600"></i>
                        <span>Perbarui Informasi Profil</span>
                    </h3>
                    <form action="index.php?page=home&action=update_profil_proses" method="POST" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($userData['nama_lengkap']); ?>" required class="w-full border p-3 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Email Aktif</label>
                                <input type="email" value="<?= htmlspecialchars($userData['email']); ?>" class="w-full border p-3 rounded-xl text-sm bg-gray-50 text-gray-500 outline-none" readonly>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">No. Telpon / WhatsApp</label>
                            <input type="text" name="no_telp" value="<?= htmlspecialchars($userData['no_telp']); ?>" required class="w-full border p-3 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" required rows="3" class="w-full border p-3 rounded-xl text-sm resize-none outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($userData['alamat']); ?></textarea>
                        </div>
                        <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-md shadow-indigo-100">
                            Perbarui Informasi Profil
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </main>
</div>
</body>
</html>