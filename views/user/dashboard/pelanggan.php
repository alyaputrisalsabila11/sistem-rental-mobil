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

// DETEKSI BLOKIR JIKA ADA DENDA AKTIF YANG BELUM TERVERIFIKASI LUNAS OLEH PETUGAS
$stmtDenda = $db->prepare("
    SELECT pg.*, m.merk_mobil 
    FROM pengembalian pg
    JOIN penyerahan pen ON pg.id_penyerahan = pen.id_penyerahan
    JOIN penyewaan pny ON pen.id_penyewaan = pny.id_penyewaan
    JOIN mobil m ON pny.id_mobil = m.id_mobil
    WHERE pny.id_pelanggan = ? AND (pg.biaya_kerusakan > 0 OR pg.denda_telat > 0) AND pg.checklist IS NULL
");
$stmtDenda->execute([$user_id]);
$dendaTertunggak = $stmtDenda->fetch(PDO::FETCH_ASSOC);
$is_blocked = ($dendaTertunggak) ? true : false; // Status banned aktif jika denda belum lunas

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
            <i class="fa-solid fa-gauge-high text-indigo-600"></i>
            <span>Dashboard Member</span>
        </h1>
        <div class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-xl">Level: <?= $user_level_nama; ?></div>
    </header>

    <main class="flex-1 overflow-y-auto p-6">

        <!-- NOTIFIKASI MERAH BESAR DENDA TERBAN -->
        <?php if ($is_blocked): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-5 rounded-2xl mb-6 shadow-sm">
                <div class="flex items-center gap-3 text-red-700 font-bold text-sm mb-1">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 animate-pulse text-base"></i>
                    <span>AKUN ANDA DIBLOKIR / BANNED AKTIF!</span>
                </div>
                <p class="text-xs text-red-600 leading-relaxed mb-3">
                    Anda dideteksi memiliki denda tertunggak sebesar 
                    <strong>Rp <?= number_format($dendaTertunggak['biaya_kerusakan'] + $dendaTertunggak['denda_telat']); ?></strong>. 
                    Pemesanan sewa baru terkunci otomatis sebelum denda dinyatakan Lunas.
                </p>
            </div>
        <?php endif; ?>

        <!-- ==================== MENU HOME (PADAT DATA & ANALIS) ==================== -->
        <?php if ($action === 'home'): ?>
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Loyalty Poin Box -->
                <div class="bg-white p-5 rounded-2xl border shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="text-gray-400 text-[10px] font-bold uppercase tracking-wider">Total Poin Saya</span>
                        <p class="text-3xl font-black text-indigo-600 mt-2"><?= number_format($user_poin); ?> <span class="text-xs font-normal">pts</span></p>
                    </div>
                    <span class="text-[10px] text-gray-400">Naikkan poin dengan menyelesaikan rental.</span>
                </div>
                <!-- Banner Penawaran Voucher -->
                <div class="bg-indigo-50 border border-indigo-100 p-5 rounded-2xl flex flex-col justify-between col-span-2">
                    <div>
                        <h3 class="font-bold text-indigo-900">Voucher Spesial Menantimu!</h3>
                        <p class="text-xs text-indigo-700 mt-1">Tukarkan poin loyalitas Anda untuk potongan sewa harian hingga 50%.</p>
                    </div>
                    <a href="index.php?page=home&action=voucher_saya" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 rounded-xl text-center w-36 mt-4 block transition">Tukar Poin</a>
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
                                <div class="w-full h-full flex items-center justify-center text-gray-300"><i class="fa-solid fa-car text-3xl"></i></div>
                            <?php endif; ?>
                        </div>
                        <h4 class="font-bold text-gray-800"><?= $r['merk_mobil']; ?></h4>
                        <p class="text-indigo-600 font-bold text-xs mt-1">Rp <?= number_format($r['harga_dinamis']); ?> / Hari</p>
                    </div>
                <?php endforeach; else: ?>
                    <div class="col-span-3 p-8 text-center text-gray-400 italic">Belum ada data rekomendasi mobil yang tersedia.</div>
                <?php endif; ?>
            </div>

        <!-- ==================== GALLERY MOBIL (LENGKAP SPESIFIKASI + INFO CABANG) ==================== -->
        <?php elseif ($action === 'gallery'): ?>
            <?php if ($is_blocked): ?>
                <div class="bg-white p-12 text-center rounded-2xl border italic text-gray-400">Gallery terkunci karena denda tertunggak.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php
                    // Join ke tabel karyawan & lokasi untuk mengambil info cabang fisik di card gallery pelanggan
                    $stmtM = $db->query("
                        SELECT m.*, l.nama_lokasi, l.kota 
                        FROM mobil m 
                        LEFT JOIN karyawan k ON m.id_mobil = k.id_karyawan
                        LEFT JOIN lokasi l ON k.id_lokasi = l.id_lokasi
                        WHERE m.status_mobil = 'Tersedia' 
                        ORDER BY m.id_mobil DESC
                    ");
                    $mobils = $stmtM->fetchAll(PDO::FETCH_ASSOC);
                    if(!empty($mobils)): foreach($mobils as $m): ?>
                        <div class="bg-white rounded-3xl border shadow-sm overflow-hidden group hover:shadow-md transition">
                            <div class="h-44 bg-gray-100 relative">
                                <?php if (!empty($m['gambar']) && @base64_encode($m['gambar'])): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($m['gambar']); ?>" class="w-full h-full object-cover">
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
                                <p class="text-lg font-black text-indigo-600">Rp <?= number_format($m['harga_dinamis']); ?> <span class="text-xs text-gray-400 font-normal">/hari</span></p>
                                
                                <!-- INFO LOKASI/CABANG TAMBAHAN DI CARD GALLERY -->
                                <div class="text-xs text-gray-500 font-semibold flex items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-indigo-500"></i>
                                    <span>Cabang: <?= htmlspecialchars($m['nama_lokasi'] ?? 'Pusat Utama'); ?> (<?= htmlspecialchars($m['kota'] ?? 'Jakarta'); ?>)</span>
                                </div>

                                <div class="grid grid-cols-2 gap-1 text-[10px] text-gray-400 border-t pt-2 mt-2">
                                    <span>CC: <strong><?= number_format($m['cc'] ?: 1500); ?> cc</strong></span>
                                    <span>Tahun: <strong><?= $m['tahun']; ?></strong></span>
                                </div>
                                <?php if($activeRental): ?>
                                    <button disabled class="w-full py-2.5 bg-gray-100 text-gray-400 text-xs font-bold rounded-xl cursor-not-allowed">Ada Sewa Aktif</button>
                                <?php else: ?>
                                    <a href="index.php?page=home&action=form_sewa&id=<?= $m['id_mobil']; ?>" class="block text-center w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">Sewa Sekarang</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="col-span-3 p-12 text-center text-gray-400 italic">Belum ada armada mobil yang tersedia di gallery.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <!-- ==================== FORM SEWA KOMPLET (KALKULATOR REAL-TIME) ==================== -->
        <?php elseif ($action === 'form_sewa'): 
            $id_mobil = $_GET['id'] ?? 0;
            $stmtMob = $db->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
            $stmtMob->execute([$id_mobil]);
            $m = $stmtMob->fetch(PDO::FETCH_ASSOC);
        ?>
            <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kolom Kiri: Formulir Isian -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl border shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-800 text-base border-b pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-file-signature text-indigo-600"></i>
                        <span>Formulir Pengajuan Sewa: <?= $m['merk_mobil']; ?></span>
                    </h3>
                    <form action="index.php?page=proses_sewa" method="POST" class="space-y-4" id="formRent">
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
                                $stmtF = $db->query("SELECT * FROM fasilitas WHERE status = 'Tersedia'");
                                while($f = $stmtF->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= $f['id_fasilitas']; ?>" data-harga="<?= $f['harga']; ?>"><?= $f['nama_fasilitas']; ?> (+Rp <?= number_format($f['harga']); ?>)</option>
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
                                    <option value="<?= $v['id_penukaran']; ?>" data-diskon="<?= $v['diskon_persen']; ?>"><?= $v['nama_voucher']; ?> (Diskon <?= $v['diskon_persen']; ?>%)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Opsi Proteksi Asuransi Bodi -->
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

                <!-- Kolom Kanan: Ringkasan Kalkulasi Real-time -->
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

            <!-- JAVASCRIPT KALKULATOR REAL-TIME -->
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
                        durasi = Math.ceil(diff / (1000 * 60 * 60 * 24)) + 1; // Ditambah 1 hari agar masuk akal
                    }

                    let totalSewa = durasi * hargaHarian;
                    let hargaFasilitas = parseInt(selF.options[selF.selectedIndex].getAttribute('data-harga') || 0);
                    let diskonPersen = parseFloat(selV.options[selV.selectedIndex].getAttribute('data-diskon') || 0);
                    let biayaAsuransi = chkAsuransi.checked ? 50000 : 0; // Flat Rp 50.000 jika asuransi dicentang

                    let subTotal = totalSewa + hargaFasilitas + biayaAsuransi;
                    let potonganDiskon = subTotal * (diskonPersen / 100);
                    let grandTotal = subTotal - potonganDiskon;

                    // Update Tampilan Ringkasan Biaya di Samping Kanan
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

        <!-- ==================== TAB SEWA SAYA ==================== -->
        <?php elseif ($action === 'sewa_saya'): ?>
            <div class="bg-white p-6 rounded-2xl border">
                <h3 class="font-bold text-gray-800 mb-4">Sewa Saya (Aktif / Pending)</h3>
                <div class="space-y-4">
                    <?php
                    $stmtMy = $db->prepare("SELECT p.*, m.merk_mobil, m.plat_nomor FROM penyewaan p JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.id_pelanggan = ? AND p.status_penyewaan != 'Canceled' ORDER BY p.id_penyewaan DESC");
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
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider inline-block mt-1 <?= $r['status_penyewaan'] === 'Confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>"><?= $r['status_penyewaan']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="py-12 text-center text-gray-400 italic">Belum ada pengajuan atau transaksi sewa mobil terdaftar.</div>
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
                        SELECT p.*, m.merk_mobil, m.plat_nomor, pen.tgl_penyerahan 
                        FROM penyewaan p 
                        JOIN mobil m ON p.id_mobil = m.id_mobil 
                        JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan
                        WHERE p.id_pelanggan = ? AND pen.status_sewa = 'complete'
                        ORDER BY p.id_penyewaan DESC
                    ");
                    $stmtHist->execute([$user_id]);
                    $histories = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

                    if(!empty($histories)): foreach($histories as $h): ?>
                        <div class="p-4 bg-gray-50 rounded-xl border flex justify-between items-center text-xs">
                            <div>
                                <span class="font-bold text-gray-800 text-sm block"><?= $h['merk_mobil']; ?> (<?= $h['plat_nomor']; ?>)</span>
                                <span class="text-gray-400">Kembali pada: <?= $h['tgl_penyerahan']; ?></span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-gray-800 block">Rp <?= number_format($h['total_harga']); ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase inline-block mt-1">Selesai / Lunas</span>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="py-12 text-center text-gray-400 italic">Belum ada riwayat transaksi sewa selesai.</div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== TAB DENDA SAYA (PENYELESAIAN DATA TABEL DENDA) ==================== -->
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmtD = $db->prepare("
                                SELECT pg.*, m.merk_mobil 
                                FROM pengembalian pg
                                JOIN penyerahan pen ON pg.id_penyerahan = pen.id_penyerahan
                                JOIN penyewaan pny ON pen.id_penyewaan = pny.id_penyewaan
                                JOIN mobil m ON pny.id_mobil = m.id_mobil
                                WHERE pny.id_pelanggan = ? AND (pg.biaya_kerusakan > 0 OR pg.denda_telat > 0)
                            ");
                            $stmtD->execute([$user_id]);
                            $dendas = $stmtD->fetchAll(PDO::FETCH_ASSOC);

                            if(!empty($dendas)): foreach($dendas as $d): ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 font-bold text-gray-800"><?= $d['merk_mobil']; ?></td>
                                <td class="p-4 text-gray-600">Rp <?= number_format($d['denda_telat']); ?></td>
                                <td class="p-4 text-red-600 font-bold">Rp <?= number_format($d['biaya_kerusakan']); ?></td>
                                <td class="p-4 text-center">
                                    <?php if($d['checklist'] === 'Lunas'): ?>
                                        <span class="px-2 py-0.5 rounded bg-green-100 text-green-700 font-bold">LUNAS</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 font-bold">BELUM LUNAS</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" class="p-8 text-center italic text-gray-400">Anda tidak memiliki tunggakan denda.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ==================== TAB VOUCHER SAYA ==================== -->
        <?php elseif ($action === 'voucher_saya'): ?>
            <div class="bg-white p-6 rounded-2xl border">
                <h3 class="font-bold text-gray-800 mb-4">Daftar Voucher Saya</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php
                    $stmtVouc = $db->prepare("
                        SELECT pv.id_penukaran, pv.status_pakai, v.kode_voucher, v.nama_voucher, v.diskon_persen 
                        FROM penukaran_voucher pv
                        JOIN voucher v ON pv.id_voucher = v.id_voucher
                        WHERE pv.id_pelanggan = ?
                    ");
                    $stmtVouc->execute([$user_id]);
                    $vouchers = $stmtVouc->fetchAll(PDO::FETCH_ASSOC);

                    if(!empty($vouchers)): foreach($vouchers as $v): ?>
                        <div class="p-4 rounded-xl border bg-indigo-50/50 border-indigo-100 flex justify-between items-center text-xs">
                            <div>
                                <span class="font-mono font-bold text-indigo-700 block text-sm"><?= $v['kode_voucher']; ?></span>
                                <span class="text-gray-500 font-bold mt-1 block"><?= $v['nama_voucher']; ?></span>
                                <span class="text-[10px] text-gray-400 uppercase tracking-widest block mt-0.5">Potongan Harga: <?= $v['diskon_persen']; ?>%</span>
                            </div>
                            <span class="px-2 py-1 rounded text-[9px] font-black uppercase <?= $v['status_pakai'] === 'belum_dipakai' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400'; ?>"><?= $v['status_pakai']; ?></span>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="py-12 text-center text-gray-400 italic col-span-2">Belum ada voucher yang terhubung dengan akun Anda.</div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== TAB AKUN SAYA ==================== -->
        <?php elseif ($action === 'akun_saya'): ?>
            <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Member Card Premium -->
                <div class="bg-gradient-to-br from-[#1E293B] to-[#0F172A] text-white p-6 rounded-3xl shadow-xl flex flex-col justify-between h-56 relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-indigo-500/10 rounded-full blur-2xl"></div>
                    <div class="flex justify-between items-start z-10">
                        <div>
                            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-400">SIREMO PREMIUM</span>
                            <h2 class="text-2xl font-black mt-1"><?= htmlspecialchars($userData['nama_lengkap']); ?></h2>
                        </div>
                        <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-black text-sm text-white uppercase shadow shadow-indigo-500/50">
                            <?= substr($userData['nama_lengkap'], 0, 1); ?>
                        </div>
                    </div>
                    <div class="z-10">
                        <span class="text-[9px] text-gray-400 uppercase block">Loyal Member</span>
                        <span class="text-sm font-bold text-indigo-300 uppercase tracking-widest mt-0.5 inline-block">★ <?= $user_level_nama; ?></span>
                    </div>
                </div>

                <!-- Formulir Profil Mandiri -->
                <div class="lg:col-span-2 bg-white p-6 rounded-3xl border shadow-sm">
                    <h3 class="font-bold text-gray-800 text-sm mb-4 border-b pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-user-pen text-indigo-600"></i>
                        <span>Perbarui Informasi Profil</span>
                    </h3>
                    <form action="index.php?page=home&action=update_profil_proses" method="POST" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($userData['nama_lengkap']); ?>" required class="w-full border p-2.5 rounded-xl text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Email Aktif</label>
                                <input type="email" value="<?= htmlspecialchars($userData['email']); ?>" class="w-full border p-2.5 rounded-xl text-xs bg-gray-50 text-gray-400 outline-none" readonly>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">No. Telpon / WhatsApp</label>
                            <input type="text" name="no_telp" value="<?= htmlspecialchars($userData['no_telp']); ?>" required class="w-full border p-2.5 rounded-xl text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" required rows="3" class="w-full border p-2.5 rounded-xl text-xs resize-none outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($userData['alamat']); ?></textarea>
                        </div>
                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-indigo-100">
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