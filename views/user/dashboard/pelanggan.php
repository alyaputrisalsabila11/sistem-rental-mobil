<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../../init.php';
$db = Database::getConnection();

$action = $_GET['action'] ?? 'home';

// PERBAIKAN: Definisikan variabel $user_id dari session (Mengatasi Warning baris 9)
$user_id = $_SESSION['user_id'] ?? null;

// Ambil Data Pelanggan (Poin & Level)
// PERBAIKAN: Ubah 'id' menjadi 'id_pelanggan' sesuai struktur tabel Anda (Mengatasi Fatal Error baris 14)
$stmtUser = $db->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ?");
$stmtUser->execute([$user_id]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Pastikan data ditemukan agar tidak error di baris bawahnya
$user_poin = $userData['poin'] ?? 0;
$user_level = $userData['id_level'] ?? 1;

// Logika Klaim Voucher (Contoh Sederhana)
$message = "";
if (isset($_POST['claim_voucher'])) {
    $v_id = $_POST['id_voucher'];
    $v_poin_req = $_POST['min_poin'];
    $v_level_req = $_POST['min_level'];

    if ($user_poin >= $v_poin_req && $user_level >= $v_level_req) {
        // Proses simpan ke tabel voucher_pelanggan dan kurangi poin jika perlu
        $message = "<div class='bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-xs font-bold'>Berhasil mengklaim voucher!</div>";
    } else {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-xs font-bold'>Gagal! Poin atau Level Anda tidak mencukupi.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pelanggan - SIREMO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 font-sans flex h-screen overflow-hidden">

<?php include __DIR__ . '/../sidebar/pelangganside.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b h-16 flex items-center justify-between px-8">
        <h1 class="text-lg font-bold text-gray-800 uppercase"><?= str_replace('_', ' ', $action); ?></h1>
        <div class="text-sm font-medium">Poin Anda: <span class="text-indigo-600 font-bold"><?= number_format($user_poin); ?> pts</span></div>
    </header>

    <main class="flex-1 overflow-y-auto p-8">
        <?= $message; ?>

        <?php if ($action === 'home'): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Card Poin -->
                <div class="bg-gradient-to-br from-indigo-600 to-blue-700 p-6 rounded-2xl shadow-lg text-white">
                    <p class="text-xs font-bold uppercase opacity-80">Total Poin Didapat</p>
                    <p class="text-4xl font-black mt-2"><?= number_format($user_poin); ?> <span class="text-sm font-normal">pts</span></p>
                </div>

                <!-- Info Voucher (Klaim) -->
                <div class="md:col-span-2 bg-blue-50 border border-blue-100 p-6 rounded-2xl flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-blue-900">Voucher Spesial Menantimu!</h3>
                        <p class="text-xs text-blue-700 mt-1">Tukarkan poinmu dengan diskon sewa hingga 50%.</p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id_voucher" value="1">
                        <input type="hidden" name="min_poin" value="100">
                        <input type="hidden" name="min_level" value="2">
                        <button name="claim_voucher" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl text-sm font-bold transition shadow-md shadow-blue-200">Klaim Sekarang</button>
                    </form>
                </div>
            </div>

            <!-- Mobil Laris -->
            <h2 class="font-bold text-gray-800 mb-4">Paling Banyak Disewa</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <?php for($i=1; $i<=3; $i++): ?>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="h-32 bg-gray-200 rounded-xl mb-3 overflow-hidden">
                         <img src="https://via.placeholder.com/300x150" class="w-full h-full object-cover">
                    </div>
                    <h4 class="font-bold text-gray-800">Toyota Avanza 2023</h4>
                    <p class="text-indigo-600 font-bold text-sm">Rp 350.000 / Hari</p>
                </div>
                <?php endfor; ?>
            </div>

            <!-- Slider Rekomendasi -->
            <h2 class="font-bold text-gray-800 mb-4">Rekomendasi Untukmu</h2>
            <div id="slider" class="flex overflow-x-auto space-x-6 no-scrollbar scroll-smooth pb-4">
                <?php for($i=1; $i<=6; $i++): ?>
                <div class="min-w-[30%] bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex-shrink-0">
                    <div class="h-40 bg-gray-100 rounded-xl mb-4"></div>
                    <h4 class="font-bold">Mobil Mewah <?= $i ?></h4>
                    <button class="w-full mt-4 bg-gray-100 text-gray-800 py-2 rounded-lg text-xs font-bold hover:bg-indigo-600 hover:text-white transition">Lihat Detail</button>
                </div>
                <?php endfor; ?>
            </div>

            <script>
                // Simple Auto Slider Logic
                const slider = document.getElementById('slider');
                setInterval(() => {
                    if (slider.scrollLeft + slider.offsetWidth >= slider.scrollWidth) {
                        slider.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                        slider.scrollBy({ left: 300, behavior: 'smooth' });
                    }
                }, 4000);
            </script>

        <?php elseif ($action === 'gallery'): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php 
                $stmtM = $db->query("SELECT * FROM mobil WHERE status_mobil = 'Tersedia'");
                while($m = $stmtM->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="bg-white rounded-3xl shadow-sm border overflow-hidden group">
                    <div class="h-48 bg-gray-200 relative overflow-hidden">
                        <?php if($m['gambar']): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($m['gambar']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg"><?= $m['merk_mobil'] ?></h3>
                            <span class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full font-bold uppercase"><?= $m['nama_kategori'] ?></span>
                        </div>
                        <p class="text-2xl font-black text-indigo-600">Rp <?= number_format($m['harga_dinamis']) ?><span class="text-xs font-normal text-gray-400">/hari</span></p>
                        <a href="index.php?page=home&action=form_sewa&id=<?= $m['id_mobil'] ?>" class="block w-full mt-6 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 rounded-2xl font-bold transition shadow-lg shadow-indigo-100">Sewa Sekarang</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

        <?php elseif ($action === 'history_sewa'): ?>
            <div class="space-y-4">
                <div class="bg-gray-100 p-6 rounded-2xl flex justify-between items-center opacity-70 grayscale">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gray-300 rounded-xl"></div>
                        <div>
                            <h4 class="font-bold text-gray-500">Honda Brio 2021</h4>
                            <p class="text-xs">Selesai pada: 12 Jan 2024</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-black text-gray-400">Total: Rp 700.000</p>
                        <span class="text-[10px] font-bold uppercase text-gray-400">Status: Complete</span>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'akun_saya'): ?>
            <div class="max-w-2xl bg-white p-8 rounded-3xl shadow-sm border">
                <div class="flex items-center space-x-6 mb-8">
                    <div class="w-24 h-24 bg-indigo-600 rounded-full flex items-center justify-center text-3xl font-bold text-white uppercase">
                        <?= substr($userData['nama_lengkap'], 0, 1); ?>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black"><?= $userData['nama_lengkap'] ?></h2>
                        <p class="text-gray-400"><?= $userData['email'] ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div class="p-4 bg-gray-50 rounded-2xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Poin Loyalty</p>
                        <p class="text-xl font-bold text-indigo-600"><?= number_format($user_poin) ?> pts</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Status Level</p>
                        <p class="text-xl font-bold text-gray-800">Level <?= $user_level ?></p>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'form_sewa'): 
            $id_mobil = $_GET['id'] ?? 0;
            
            // 1. Ambil Data Mobil
            $stmtMobil = $db->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
            $stmtMobil->execute([$id_mobil]);
            $mobil = $stmtMobil->fetch(PDO::FETCH_ASSOC);

            // 2. Ambil Voucher yang sudah diklaim (tabel penukaran join voucher)
            $stmtVoucher = $db->prepare("SELECT p.id_penukaran, v.nama_voucher, v.diskon 
                                        FROM penukaran p 
                                        JOIN voucher v ON p.id_voucher = v.id_voucher 
                                        WHERE p.id_pelanggan = ? AND p.status_pakai = 'belum_dipakai'");
            $stmtVoucher->execute([$user_id]);
            $vouchers = $stmtVoucher->fetchAll(PDO::FETCH_ASSOC);

            // 3. Ambil Data Fasilitas
            $stmtFasilitas = $db->query("SELECT * FROM fasilitas WHERE status = 'Tersedia'");
            $fasilitas_list = $stmtFasilitas->fetchAll(PDO::FETCH_ASSOC);

            // Definisi Biaya Asuransi (misal flat 50.000 per transaksi)
            $biaya_asuransi = 50000;
        ?>

        <div class="max-w-4xl mx-auto">
            <a href="index.php?page=home&action=gallery" class="text-indigo-600 font-bold text-sm mb-4 inline-block">← Kembali ke Gallery</a>
            
            <form action="index.php?page=proses_sewa" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <input type="hidden" name="id_mobil" id="id_mobil" value="<?= $id_mobil ?>">
                <input type="hidden" name="harga_harian" id="harga_harian" value="<?= $mobil['harga_dinamis'] ?>">
                <input type="hidden" name="biaya_asuransi_val" id="biaya_asuransi_val" value="<?= $biaya_asuransi ?>">

                <!-- Kolom Kiri: Detail Mobil & Pilihan -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white p-6 rounded-3xl shadow-sm border">
                        <h2 class="text-xl font-black mb-4">Detail Penyewaan: <?= $mobil['merk_mobil'] ?></h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal Mulai</label>
                                <input type="date" name="tgl_mulai" id="tgl_mulai" class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal Selesai</label>
                                <input type="date" name="tgl_selesai" id="tgl_selesai" class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" required>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            <!-- Dropdown Fasilitas -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pilih Fasilitas Tambahan</label>
                                <select name="id_fasilitas" id="id_fasilitas" class="w-full border p-3 rounded-xl appearance-none bg-gray-50">
                                    <option value="0" data-harga="0">Tanpa Fasilitas Tambahan</option>
                                    <?php foreach($fasilitas_list as $f): ?>
                                        <option value="<?= $f['id_fasilitas'] ?>" data-harga="<?= $f['harga'] ?>">
                                            <?= $f['nama_fasilitas'] ?> (+Rp <?= number_format($f['harga']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Dropdown Voucher -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Gunakan Voucher Saya</label>
                                <select name="id_penukaran" id="id_penukaran" class="w-full border p-3 rounded-xl bg-indigo-50 text-indigo-700 font-bold">
                                    <option value="0" data-diskon="0">Pilih Voucher (Opsional)</option>
                                    <?php foreach($vouchers as $v): ?>
                                        <option value="<?= $v['id_penukaran'] ?>" data-diskon="<?= $v['diskon'] ?>">
                                            <?= $v['nama_voucher'] ?> (Potongan Rp <?= number_format($v['diskon']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Pilihan Asuransi -->
                            <div class="flex items-center justify-between p-4 bg-orange-50 border border-orange-100 rounded-2xl">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-orange-500 text-white rounded-lg"><i class="fas fa-shield-alt"></i></div>
                                    <div>
                                        <p class="text-sm font-bold text-orange-900">Proteksi Asuransi</p>
                                        <p class="text-[10px] text-orange-700">Cover kerusakan & denda jika terjadi insiden.</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-bold text-orange-800">Rp <?= number_format($biaya_asuransi) ?></span>
                                    <input type="checkbox" name="pake_asuransi" id="pake_asuransi" value="1" class="w-5 h-5 accent-orange-600">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Ringkasan Biaya -->
                <div class="space-y-6">
                    <div class="bg-slate-900 text-white p-6 rounded-3xl shadow-xl sticky top-8">
                        <h3 class="font-bold mb-4 border-b border-slate-700 pb-2">Ringkasan Pembayaran</h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Sewa (<span id="display_durasi">0</span> hari)</span>
                                <span id="display_sewa">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Fasilitas</span>
                                <span id="display_fasilitas">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Asuransi</span>
                                <span id="display_asuransi">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-green-400 font-bold border-t border-slate-700 pt-2">
                                <span>Voucher Diskon</span>
                                <span id="display_voucher">-Rp 0</span>
                            </div>
                        </div>

                        <div class="mt-8">
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mb-1">Total Bayar</p>
                            <p class="text-3xl font-black text-indigo-400">Rp <span id="display_total">0</span></p>
                        </div>

                        <button type="submit" class="w-full mt-6 bg-indigo-600 hover:bg-indigo-700 py-4 rounded-2xl font-bold transition shadow-lg shadow-indigo-900">
                            Konfirmasi & Bayar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <script>
            const tglMulai = document.getElementById('tgl_mulai');
            const tglSelesai = document.getElementById('tgl_selesai');
            const selectFasilitas = document.getElementById('id_fasilitas');
            const selectVoucher = document.getElementById('id_penukaran');
            const checkAsuransi = document.getElementById('pake_asuransi');
            
            const hargaHarian = parseInt(document.getElementById('harga_harian').value);
            const biayaAsuransiDefault = parseInt(document.getElementById('biaya_asuransi_val').value);

            function hitungTotal() {
                let start = new Date(tglMulai.value);
                let end = new Date(tglSelesai.value);
                let durasi = 0;
                
                if (start && end && end > start) {
                    let diffTime = Math.abs(end - start);
                    durasi = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                }

                let totalSewa = durasi * hargaHarian;
                let hargaFasilitas = parseInt(selectFasilitas.options[selectFasilitas.selectedIndex].getAttribute('data-harga') || 0);
                let diskonVoucher = parseInt(selectVoucher.options[selectVoucher.selectedIndex].getAttribute('data-diskon') || 0);
                let biayaAsuransi = checkAsuransi.checked ? biayaAsuransiDefault : 0;

                let grandTotal = (totalSewa + hargaFasilitas + biayaAsuransi) - diskonVoucher;
                if (grandTotal < 0) grandTotal = 0;

                // Update Display
                document.getElementById('display_durasi').innerText = durasi;
                document.getElementById('display_sewa').innerText = "Rp " + totalSewa.toLocaleString();
                document.getElementById('display_fasilitas').innerText = "Rp " + hargaFasilitas.toLocaleString();
                document.getElementById('display_asuransi').innerText = "Rp " + biayaAsuransi.toLocaleString();
                document.getElementById('display_voucher').innerText = "-Rp " + diskonVoucher.toLocaleString();
                document.getElementById('display_total').innerText = grandTotal.toLocaleString();
            }

            [tglMulai, tglSelesai, selectFasilitas, selectVoucher, checkAsuransi].forEach(el => {
                el.addEventListener('change', hitungTotal);
            });
        </script>

    <?php endif; ?>
    </main>
</div>
</body>
</html>