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
                class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= in_array($action, ['penyerahan', 'penyerahan_create']) ? 'bg-white/15 text-white shadow-inner border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-key text-base w-5 text-center group-hover:text-amber-400 transition"></i>
                <span>Penyerahan Mobil</span>
            </a>

            <a href="index.php?page=home_lapangan&action=pengembalian"
                class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= in_array($action, ['pengembalian', 'pengembalian_create']) ? 'bg-white/15 text-white shadow-inner border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">

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
                    elseif (in_array($action, ['penyerahan', 'penyerahan_create'])) echo 'PENYERAHAN MOBIL';
                    elseif (in_array($action, ['pengembalian', 'pengembalian_create'])) echo 'DAFTAR PENGEMBALIAN MOBIL';
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
                    <?php if (isset($_GET['form'])): ?>

                        <?php
                        require_once __DIR__ . '/../../models/PenyerahanModel.php';
                        require_once __DIR__ . '/../../models/LokasiModel.php';

                        $id = $_GET['id'] ?? 0;

                        $penyerahanModel = new PenyerahanModel();
                        $booking = $penyerahanModel->getBookingById($id);

                        $lokasiModel = new LokasiModel();
                        $lokasi = $lokasiModel->getAllLokasi();

                        include __DIR__ . '/../transaksi/penyerahan/create.php';
                        ?>

                    <?php else: ?>
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
                                                    <a href="index.php?page=home_lapangan&action=penyerahan&form=1&id=<?= $row['id_booking']; ?>"
                                                        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">

                                                        <i class="fas fa-key"></i>

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

            <?php endif; ?>
        <?php elseif ($action === 'pengembalian'): ?>

            <?php
                require_once __DIR__ . '/../../models/PengembalianModel.php';

                $pengembalianModel = new PengembalianModel();
                $penyerahan = $pengembalianModel->getPenyerahanOngoing();
            ?>


            <div class="bg-white rounded-2xl shadow-sm border border-gray-200">

                <?php if (isset($_GET['form'])): ?>

                    <?php
                    $id = $_GET['id'] ?? 0;
                    $penyerahan = $pengembalianModel->getPenyerahanById($id);

                    include __DIR__ . '/../transaksi/pengembalian/create.php';
                    ?>

                <?php else: ?>

                    <div class="p-6 border-b border-gray-100">

                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-undo text-blue-500"></i>
                            Daftar Pengembalian Mobil
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            Kendaraan yang sedang disewa dan siap dikembalikan.
                        </p>

                    </div>

                    <?php if (!empty($penyerahan)): ?>

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

                                    <?php foreach ($penyerahan as $row): ?>

                                        <tr class="border-b hover:bg-gray-50">

                                            <td class="px-4 py-3"><?= $no++; ?></td>

                                            <td class="px-4 py-3"><?= $row['kode_booking']; ?></td>

                                            <td class="px-4 py-3"><?= $row['nama_lengkap']; ?></td>

                                            <td class="px-4 py-3"><?= $row['merk_mobil']; ?></td>

                                            <td class="px-4 py-3"><?= $row['plat_nomor']; ?></td>

                                            <td class="px-4 py-3 text-center">

                                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">

                                                    Ongoing

                                                </span>

                                            </td>

                                            <td class="px-4 py-3 text-center">

                                                <a href="index.php?page=home_lapangan&action=pengembalian&form=1&id=<?= $row['id_penyerahan']; ?>"
                                                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold">

                                                    <i class="fas fa-undo"></i>

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

                            <i class="fas fa-check-circle text-5xl text-gray-300 mb-4"></i>

                            <h3 class="font-bold text-gray-500">

                                Belum Ada Pengembalian Mobil

                            </h3>

                            <p class="text-sm text-gray-400 mt-2">

                                Mobil yang sedang disewa akan muncul di sini.

                            </p>

                        </div>

                    <?php endif; ?>

                <?php endif; ?>

            </div>


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
<?php endif; ?>

</html>