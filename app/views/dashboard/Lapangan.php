<?php
if (session_status() === PHP_SESSION_NONE) {
    if (!isset($_SESSION)) {
        session_start();
    }
}

require_once __DIR__ . '/../../../config/database.php';
$db = Database::getConnection();

$brand_name = "SIREMO";
$staff_name = $_SESSION['user_name'] ?? 'Staff Lapangan';
$staff_email = $_SESSION['user_email'] ?? 'lapangan@showroom.com';

// Menentukan halaman aktif berdasarkan parameter 'action' di URL (Default: home)
$action = isset($_GET['action']) ? $_GET['action'] : 'home';

// --- AMBIL DATA DARI DATABASE SECARA DINAMIS ---
if ($action === 'home') {
    // 1. Menghitung armada yang sedang jalan (Status: Confirmed)
    $stmtJalan = $db->query("SELECT COUNT(*) AS total FROM booking WHERE status_booking = 'Confirmed'");
    $jumlah_belum_serah_kunci = $stmtJalan->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // 2. Data statis maintenance bawaan kode awalmu
    $jumlah_belum_maintenance = 5;
} elseif ($action === 'tugas_sopir') {
    // Ambil tugas mengemudi yang statusnya 'Confirmed' DAN nama kolom sopir cocok dengan nama staff yang login
    $stmtTugas = $db->prepare("SELECT b.*, m.merk_mobil, m.plat_nomor, p.nama_lengkap, p.no_telp 
                               FROM booking b
                               JOIN mobil m ON b.id_mobil = m.id_mobil
                               JOIN pelanggan p ON b.id_pelanggan = p.id
                               WHERE b.status_booking = 'Confirmed' 
                               AND b.sopir_booking LIKE :staff_name
                               ORDER BY b.tgl_mulai_sewa ASC");
    // Gunakan % agar pencarian LIKE fleksibel (karena di database formatnya "Nama - NoTelp")
    $stmtTugas->execute([':staff_name' => "%" . $staff_name . "%"]);
    $tugas_list = $stmtTugas->fetchAll(PDO::FETCH_ASSOC);
} elseif ($action === 'penyerahan') {
    require_once __DIR__ . '/../../models/PenyerahanModel.php';
    $penyerahanModel = new PenyerahanModel();
    $booking = $penyerahanModel->getBookingConfirmed();
} elseif ($action === 'pengembalian') {
    // Kelola Kembali HANYA menampilkan mobil yang disewa 'Tanpa Sopir' (Lepas Kunci)
    // Karena mobil yang pakai sopir akan otomatis dipulangkan oleh sopirnya lewat menu "Tugas Sopir" nanti
    $stmtKembali = $db->query("SELECT b.*, m.merk_mobil, m.plat_nomor, p.nama_lengkap 
                               FROM booking b
                               JOIN mobil m ON b.id_mobil = m.id_mobil
                               JOIN pelanggan p ON b.id_pelanggan = p.id
                               WHERE b.status_booking = 'Confirmed'
                               AND b.sopir_booking = 'Tanpa Sopir'
                               ORDER BY b.id_booking DESC");
    $kembali_list = $stmtKembali->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Lapangan Dashboard - <?= $brand_name; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans flex h-screen overflow-hidden">

    <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 shadow-xl">
        <div class="p-5 flex items-center space-x-3 border-b border-slate-800">
            <i class="fas fa-tools text-xl text-amber-500"></i>
            <span class="text-xl font-black tracking-wider uppercase"><?= $brand_name; ?></span>
        </div>

        <div class="p-5 border-b border-slate-800 bg-slate-950/40 flex items-center space-x-3">
            <div class="w-10 h-10 bg-amber-600 rounded-full flex items-center justify-center font-bold text-white shadow shadow-amber-500/50 flex-shrink-0">
                SL
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold truncate"><?= htmlspecialchars($staff_name); ?></p>
                <p class="text-[11px] text-amber-300 font-medium truncate"><?= htmlspecialchars($staff_email); ?></p>
                <span class="inline-block mt-1 px-1.5 py-0.5 text-[9px] font-bold bg-amber-500/20 text-amber-300 rounded border border-amber-500/30">Crew Lapangan</span>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <a href="index.php?page=home_lapangan&action=home"
                class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'home' ? 'bg-white/15 text-white shadow-inner border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-chart-line text-base w-5 text-center group-hover:text-amber-400 transition"></i>
                <span>Dashboard</span>
            </a>

            <a href="index.php?page=home_lapangan&action=tugas_sopir"
                class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'tugas_sopir' ? 'bg-white/15 text-white shadow-inner border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-id-card text-base w-5 text-center group-hover:text-amber-400 transition"></i>
                <span>Tugas Sopir</span>
            </a>

            <a href="index.php?page=home_lapangan&action=penyerahan"
   class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'penyerahan' ? 'bg-white/15 text-white shadow-inner border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">

    <i class="fas fa-key text-base w-5 text-center group-hover:text-amber-400 transition"></i>

    <span>Penyerahan Mobil</span>

</a>

            <a href="index.php?page=home_lapangan&action=pengembalian"
   class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'pengembalian' ? 'bg-white/15 text-white shadow-inner border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">

    <i class="fas fa-undo-alt text-base w-5 text-center group-hover:text-amber-400 transition"></i>

    <span>Pengembalian Mobil</span>

</a>

            <!-- <a href="index.php?page=home_lapangan&action=pengembalian"
                class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'pengembalian' ? 'bg-white/15 text-white shadow-inner border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-undo-alt text-base w-5 text-center group-hover:text-amber-400 transition"></i>
                <span>Kelola Kembali</span>
            </a> -->

            <a href="index.php?page=home_lapangan&action=lapor_kerusakan"
                class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'lapor_kerusakan' ? 'bg-white/15 text-white shadow-inner border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-exclamation-triangle text-base w-5 text-center group-hover:text-amber-400 transition"></i>
                <span>Lapor Kerusakan</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="index.php?page=logout" class="flex items-center justify-center space-x-2 w-full py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition shadow-md">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar Sistem</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">

        <header class="bg-white shadow-sm border-b border-gray-100 h-16 flex items-center justify-between px-8 flex-shrink-0">
            <div>
                <h1 class="text-lg font-bold text-gray-800 uppercase tracking-wide">
                    <?php
                    if ($action === 'home') echo 'DASHBOARD OPERASIONAL';
                    elseif ($action === 'tambah_mobil') echo 'TAMBAH UNIT BARU';
                    elseif ($action === 'tugas_sopir') echo 'JADWAL TUGAS SOPIR';
                    elseif ($action === 'penyerahan') echo 'PENYERAHAN MOBIL';
                    elseif ($action === 'pengembalian') echo 'VALIDASI MOBIL MASUK GARASI';
                    elseif ($action === 'lapor_kerusakan') echo 'LAPORAN KERUSAKAN FLEET';
                    ?>
                </h1>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span>Petugas:</span>
                <span class="font-bold text-gray-800"><?= htmlspecialchars($staff_name); ?></span>
                <span class="w-2 h-2 bg-emerald-500 rounded-full inline-block animate-pulse ml-1"></span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">

            <?php if ($action === 'home'): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Unit Sedang Disewa (Jalan)</p>
                            <h3 class="text-2xl font-black text-gray-800 mt-1"><?= $jumlah_belum_serah_kunci; ?> Unit</h3>
                        </div>
                        <div class="p-3.5 bg-amber-50 text-amber-600 rounded-xl"><i class="fas fa-key text-xl"></i></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Unit Butuh Maintenance</p>
                            <h3 class="text-2xl font-black text-gray-800 mt-1"><?= $jumlah_belum_maintenance; ?> Unit</h3>
                        </div>
                        <div class="p-3.5 bg-red-50 text-red-600 rounded-xl"><i class="fas fa-wrench text-xl"></i></div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-slate-800 to-slate-900 text-white rounded-2xl p-8 shadow-sm">
                    <h2 class="text-xl font-bold mb-2">Halo, <?= htmlspecialchars($staff_name); ?>! 👋</h2>
                    <p class="text-sm text-slate-300 max-w-xl">Tetap utamakan keselamatan berkendara saat mengantarkan unit. Selalu cek kondisi fisik, ketersediaan bahan bakar (BBM), serta kebersihan interior fleet sebelum diserahkan ke pelanggan.</p>
                </div>

            <?php elseif ($action === 'tugas_sopir'): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if (!empty($tugas_list)): ?>
                        <?php foreach ($tugas_list as $t): ?>
                            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                                <span class="absolute top-0 right-0 bg-indigo-50 text-indigo-600 font-mono text-[10px] font-bold px-3 py-1 rounded-bl-xl border-l border-b border-gray-100">
                                    <?= $t['kode_booking']; ?>
                                </span>
                                <div class="flex flex-col space-y-3">
                                    <div>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Armada yang Dikemudikan</span>
                                        <h3 class="text-base font-black text-gray-800"><?= htmlspecialchars($t['merk_mobil']); ?></h3>
                                        <span class="inline-block bg-slate-100 border text-slate-700 px-2 py-0.5 rounded-md font-mono font-bold text-xs mt-1"><?= htmlspecialchars($t['plat_nomor']); ?></span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-dashed text-xs">
                                        <div>
                                            <p class="text-gray-400 font-medium">Nama Penyewa:</p>
                                            <p class="font-bold text-gray-800"><?= htmlspecialchars($t['nama_lengkap']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-400 font-medium">Tanggal Mulai:</p>
                                            <p class="font-bold text-indigo-600"><?= date('d M Y', strtotime($t['tgl_mulai_sewa'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="pt-2">
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $t['no_telp']); ?>" target="_blank"
                                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-center font-bold text-xs py-2.5 rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm">
                                            <i class="fab fa-whatsapp text-sm"></i> Hubungi Kontak Pelanggan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="md:col-span-2 bg-white rounded-2xl p-16 text-center border border-gray-200">
                            <i class="fas fa-smile-beam text-5xl text-gray-300 mb-3 block"></i>
                            <p class="text-sm text-gray-400 font-bold uppercase tracking-wider">Belum ada tugas menyetir aktif untuk Anda hari ini.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($action === 'penyerahan'): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-key text-amber-500"></i>
                            Daftar Penyerahan Mobil
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Booking yang telah dikonfirmasi Admin akan muncul di sini untuk dilakukan proses penyerahan.
                        </p>
                    </div>
                    <?php if (!empty($booking)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 rounded-xl">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left">No</th>
                                    <th class="px-4 py-3 text-left">Kode Booking</th>
                                    <th class="px-4 py-3 text-left">Pelanggan</th>
                                    <th class="px-4 py-3 text-left">Mobil</th>
                                    <th class="px-4 py-3 text-left">Plat Nomor</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($booking as $row): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3"><?= $no++; ?></td>
                                        <td class="px-4 py-3"><?= $row['kode_booking']; ?></td>
                                        <td class="px-4 py-3"><?= $row['nama_lengkap']; ?></td>
                                        <td class="px-4 py-3"><?= $row['merk_mobil']; ?></td>
                                        <td class="px-4 py-3"><?= $row['plat_nomor']; ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                                                <?= $row['status_booking']; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="index.php?page=penyerahan_create&id=<?= $row['id_booking']; ?>"
                                                class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                                                Isi Form
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-16 text-center">
                            <i class="fas fa-key text-5xl text-gray-300 mb-4"></i>
                            <h3 class="font-bold text-gray-500">
                                Belum Ada Penyerahan Mobil
                            </h3>
                            <p class="text-sm text-gray-400 mt-2">
                                Booking yang telah disetujui Staff Admin akan muncul di halaman ini.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($action === 'pengembalian'): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if (!empty($kembali_list)): ?>
                        <?php foreach ($kembali_list as $k): ?>
                            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                                <div class="mb-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="text-base font-black text-gray-800"><?= htmlspecialchars($k['merk_mobil']); ?></h3>
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] font-bold border border-amber-200 rounded-md">Sedang Jalan</span>
                                    </div>
                                    <p class="text-xs font-mono font-bold text-gray-400"><?= htmlspecialchars($k['plat_nomor']); ?> • <span class="text-indigo-600 font-sans font-bold"><?= htmlspecialchars($k['nama_lengkap']); ?></span></p>
                                </div>

                                <form action="index.php?page=proses_kembali_lapangan" method="POST" class="bg-gray-50 border border-gray-100 p-4 rounded-xl space-y-3">
                                    <input type="hidden" name="id_booking" value="<?= $k['id_booking']; ?>">
                                    <input type="hidden" name="id_mobil" value="<?= $k['id_mobil']; ?>">

                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Pengecekan Fisik & BBM</label>
                                        <select name="status_denda" onchange="toggleDendaInput(this, <?= $k['id_booking']; ?>)" class="w-full bg-white border border-gray-200 p-2 rounded-xl text-xs font-medium focus:outline-none focus:border-indigo-500 cursor-pointer">
                                            <option value="Aman">Kondisi Aman / Sesuai (Tanpa Denda)</option>
                                            <option value="Terlambat">Terlambat Mengembalikan</option>
                                            <option value="BBM_Kurang">Bahan Bakar (BBM) Berkurang</option>
                                            <option value="Lecet_Rusak">Ada Lecet / Kerusakan Fisik</option>
                                        </select>
                                    </div>

                                    <div id="box_nominal_<?= $k['id_booking']; ?>" class="hidden">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nominal Denda Terhitung (Rp)</label>
                                        <input type="number" name="nominal_denda" placeholder="Masukkan biaya denda, misal: 250000" class="w-full bg-white border border-gray-200 p-2 rounded-xl text-xs focus:outline-none focus:border-indigo-500">
                                    </div>

                                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs py-2.5 rounded-xl transition flex items-center justify-center gap-1 shadow-sm">
                                        <i class="fas fa-clipboard-check"></i> Konfirmasi Mobil Masuk Garasi
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="md:col-span-2 bg-white rounded-2xl p-16 text-center border border-gray-200">
                            <i class="fas fa-check-circle text-5xl text-emerald-400 mb-3 block"></i>
                            <p class="text-sm text-gray-400 font-bold uppercase tracking-wider">Semua unit mobil sudah aman parkir di garasi utama.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($action === 'lapor_kerusakan'): ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <h2 class="border-b pb-3 mb-5 text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-amber-500"></i> Form Laporan Kendala Lapangan
                    </h2>

                    <form action="" method="POST" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Pilih Unit Armada</label>
                                <select name="id_mobil" required class="w-full border border-gray-200 p-2.5 rounded-xl text-sm bg-white focus:outline-amber-500">
                                    <option value="">-- Pilih Mobil --</option>
                                    <option value="1">Toyota Avanza (B 1234 ABC)</option>
                                    <option value="2">Innova Zenix (B 5678 DEF)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Jenis Masalah</label>
                                <div class="flex gap-4 mt-2.5 text-sm font-medium text-gray-700">
                                    <label class="flex items-center cursor-pointer"><input type="radio" name="jenis" value="Perbaikan" checked class="w-4 h-4 text-amber-600 border-gray-300 mr-2"> Perbaikan Ringan/Berat</label>
                                    <label class="flex items-center cursor-pointer"><input type="radio" name="jenis" value="Servis" class="w-4 h-4 text-amber-600 border-gray-300 mr-2"> Servis Berkala (Ganti Oli/Dll)</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Detail Deskripsi Kerusakan / Perbaikan</label>
                            <textarea name="deskripsi" rows="4" required placeholder="Contoh: Lampu utama sebelah kiri mati total, rem depan mulai menipis..." class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:outline-amber-500 bg-gray-50/50"></textarea>
                        </div>

                        <div class="flex justify-end pt-3 border-t">
                            <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                                <i class="fas fa-paper-plane mr-1.5"></i> Kirim Laporan Lapangan
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </main>

        <footer class="text-center text-xs text-gray-400 border-t border-gray-100 py-4 bg-white flex-shrink-0">
            &copy; 2026 Sistem Rental Mobil Internal • Modul Kru Operasional Lapangan Selesai.
        </footer>
    </div>

    <script>
        function toggleDendaInput(selectElement, bookingId) {
            var boxNominal = document.getElementById('box_nominal_' + bookingId);
            if (selectElement.value !== 'Aman') {
                boxNominal.classList.remove('hidden');
                boxNominal.querySelector('input').setAttribute('required', 'required');
            } else {
                boxNominal.classList.add('hidden');
                boxNominal.querySelector('input').removeAttribute('required');
                boxNominal.querySelector('input').value = '';
            }
        }
    </script>
</body>

</html>