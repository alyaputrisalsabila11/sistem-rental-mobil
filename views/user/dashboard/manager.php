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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manager - SIREMO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

            <!-- ==================== MAIN HOME DASHBOARD ==================== -->
            <?php if ($action === 'home'): ?>
                <div class="bg-white p-6 rounded-2xl border shadow-sm">
                    <h2 class="text-base font-bold text-gray-800 mb-2">Selamat Datang di Panel Sistem Informasi SIREMO</h2>
                    <p class="text-xs text-gray-400">Pilih menu di sidebar kiri untuk mengelola cabang, akun karyawan, loyalitas, voucher, dan laporan bulanan perusahaan [PDF 1, PDF 2, PDF 3, PDF 4, PDF 5].</p>
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
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada cabang terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
                                    <th class="px-4 py-3">Jabatan</th>
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
                                        <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($karyawan['role']); ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada karyawan terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada data tingkat loyalitas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ==================== KELOLA VOUCHER ==================== -->
            <?php elseif ($action === 'buat_voucher'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit lg:col-span-1">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>Buat Voucher Baru</span>
                        </h2>
                        <form action="index.php?page=voucher_proses_tambah" method="POST" class="flex flex-col space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kode Voucher</label>
                                <input type="text" name="kode_voucher" placeholder="ex: VCHR-EMAS50" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Voucher</label>
                                <input type="text" name="nama_voucher" placeholder="ex: DISKON RENTAL EMAS" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Diskon (%)</label>
                                <input type="number" name="diskon_persen" step="0.01" min="1" max="100" placeholder="Contoh: 10.50" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
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
                                <select name="id_level" required class="w-full border p-2 rounded text-sm bg-white focus:outline-indigo-500">
                                    <option value="">-- Pilih Level --</option>
                                    <?php 
                                    $stmtL = $db->query("SELECT * FROM loyalitas");
                                    $loyalLevels = $stmtL->fetchAll(PDO::FETCH_ASSOC);
                                    if (!empty($loyalLevels)): foreach ($loyalLevels as $level): ?>
                                        <option value="<?= htmlspecialchars($level['id_level']); ?>"><?= htmlspecialchars($level['nama_level']); ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Berlaku</label>
                                <input type="date" name="tgl_berlaku" class="border p-2 rounded w-full text-sm focus:outline-indigo-500" required>
                            </div>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2.5 rounded-xl w-full mt-2 transition-colors text-sm shadow-sm">
                                Simpan Voucher
                            </button>
                        </form>
                    </div>
                </div>

            <?php elseif ($action === 'data_voucher'): ?>
                <div class="w-full bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>🎟️</span> <span>Daftar Voucher</span>
                    </h2>
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap min-w-[850px] border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Nama Voucher</th>
                                    <th class="px-4 py-3">Diskon</th>
                                    <th class="px-4 py-3">Kuota</th>
                                    <th class="px-4 py-3">Tanggal Berlaku</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php 
                                $stmtV = $db->query("SELECT * FROM voucher ORDER BY id_voucher DESC");
                                $vouchers = $stmtV->fetchAll(PDO::FETCH_ASSOC);
                                if (!empty($vouchers)): foreach ($vouchers as $v): ?>
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-4 py-4 font-bold text-gray-900"><?= htmlspecialchars($v['nama_voucher']); ?></td>
                                        <td class="px-4 py-4 text-gray-700 font-semibold"><?= htmlspecialchars($v['diskon_persen']); ?>%</td>
                                        <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($v['kuota']); ?> Lembar</td>
                                        <td class="px-4 py-4 text-xs text-gray-600"><?= date('d M Y', strtotime($v['tgl_berlaku'])); ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 italic text-xs">Belum ada data voucher terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
            <?php endif; ?>

        </main>
    </div>
</body>
</html>