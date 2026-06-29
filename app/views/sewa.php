<?php
require_once __DIR__ . '/../../config/database.php';

$db = Database::getConnection();
// Mengambil mobil yang tersedia
$stmtMobil = $db->query("SELECT id_mobil, merk_mobil, warna, harga_dinamis FROM mobil WHERE status_mobil = 'Tersedia' ORDER BY merk_mobil ASC");
$daftar_mobil = $stmtMobil->fetchAll(PDO::FETCH_ASSOC);

$id_mobil_terpilih = isset($_GET['id_mobil']) ? intval($_GET['id_mobil']) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Mobil - Rental Mobil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <?php include __DIR__ . '/layouts/sidebar.php'; ?>

        <main class="flex-1 ml-64 p-8 flex justify-center items-center">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white px-8 py-5">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-file-invoice-dollar text-2xl"></i>
                        <h2 class="text-xl font-bold">Formulir Transaksi Sewa</h2>
                    </div>
                </div>
                
                <form action="index.php?page=proses_booking" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Penyewa</label>
                            <input type="text" name="nama_penyewa" value="<?= htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" required readonly 
                                   class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#5138bc] focus:outline-none text-sm bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No. Telepon Aktif</label>
                            <input type="tel" name="no_telp" required placeholder="08xxxxxxxxxx" 
                                   class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#5138bc] focus:outline-none text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Mobil & Warna</label>
                        <?php 
                        // Cari data mobil yang sesuai dengan ID yang dipilih dari gallery
                        $mobil_terpilih_data = null;
                        foreach($daftar_mobil as $m) {
                            if ($id_mobil_terpilih == $m['id_mobil']) {
                                $mobil_terpilih_data = $m;
                                break;
                            }
                        }
                        
                        // Jika ada mobil terpilih, tampilkan datanya
                        if ($mobil_terpilih_data): 
                        ?>
                            <input type="text" 
                                   readonly 
                                   class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-gray-100 text-gray-700 text-sm font-medium cursor-not-allowed focus:outline-none"
                                   value="<?= htmlspecialchars($mobil_terpilih_data['merk_mobil']); ?> (Warna: <?= htmlspecialchars($mobil_terpilih_data['warna']); ?>) - Rp <?= number_format($mobil_terpilih_data['harga_dinamis'], 0, ',', '.'); ?>/Hari">
                            
                            <input type="hidden" name="id_mobil" id="id_mobil" value="<?= $mobil_terpilih_data['id_mobil']; ?>">
                            
                            <select id="id_mobil_harga_helper" class="hidden">
                                <option data-price="<?= $mobil_terpilih_data['harga_dinamis']; ?>" selected></option>
                            </select>
                        <?php else: ?>
                            <div class="text-xs text-red-500 bg-red-50 p-3 rounded-xl border border-red-200">
                                <i class="fas fa-exclamation-circle mr-1"></i> Silakan pilih mobil terlebih dahulu melalui menu Gallery.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Sewa</label>
                            <input type="date" name="tanggal_sewa" id="tanggal_sewa" required onchange="hitungTotal()" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#5138bc] focus:outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali" id="tanggal_kembali" required onchange="hitungTotal()" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#5138bc] focus:outline-none text-sm">
                        </div>
                    </div>

                    <!-- BARU: Asuransi & Voucher (Opsional) -->
                    <div class="bg-gray-50 p-5 rounded-xl border border-dashed border-gray-300 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" name="pakai_sopir" id="pakai_sopir" value="200000" onchange="hitungTotal()" class="w-5 h-5 text-[#5138bc] rounded focus:ring-[#5138bc]">
                                <label for="pakai_sopir" class="text-sm font-medium text-gray-700">Gunakan Sopir <span class="text-indigo-600 font-bold">(+Rp 200.000/Hari)</span></label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" name="pakai_asuransi" id="pakai_asuransi" value="500000" onchange="hitungTotal()" class="w-5 h-5 text-[#5138bc] rounded focus:ring-[#5138bc]">
                                <label for="pakai_asuransi" class="text-sm font-medium text-gray-700">Gunakan Asuransi <span class="text-blue-600 font-bold">(+Rp 500.000 flat)</span></label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kode Voucher</label>
                            <input type="text" name="kode_voucher" placeholder="Masukkan kode voucher" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none uppercase">
                        </div>
                    </div>

                    <!-- BARU: Ringkasan Pembayaran -->
                    <div class="grid grid-cols-2 gap-4 bg-[#5138bc] bg-opacity-5 p-5 rounded-2xl border border-[#5138bc] border-opacity-20">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Durasi Pinjam</p>
                            <p class="text-xl font-bold text-gray-800" id="display_hari">0 Hari</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Estimasi Total</p>
                            <p class="text-2xl font-black text-[#ff5722]" id="display_total">Rp 0</p>
                        </div>
                    </div>

                    <!-- Info Pembayaran -->
                    <div class="bg-indigo-50 p-5 rounded-2xl border border-indigo-100">
                        <p class="text-sm font-bold text-[#5138bc] mb-4 text-center"><i class="fas fa-university mr-2"></i>Informasi Pembayaran</p>
                        <div class="flex flex-col md:flex-row items-center justify-around gap-6">
                            <div class="text-center">
                                <p class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">BCA Virtual Account</p>
                                <span class="bg-white border border-indigo-200 px-4 py-2 rounded-lg font-mono text-lg font-bold text-gray-800">
                                    88301-64935673
                                </span>
                            </div>
                            <div class="text-center">
                                <div class="p-3 bg-white border border-indigo-200 rounded-xl shadow-sm">
                                    <i class="fas fa-qrcode text-6xl text-gray-800"></i>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2 font-bold uppercase">Scan QRIS Resmi</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Bukti Transfer (Max 10MB)</label>
                        <input type="file" name="bukti_pembayaran" id="file_bukti" accept="image/*" required 
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-[#5138bc] hover:file:bg-indigo-100">
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="flex-1 bg-[#ff5722] hover:bg-orange-600 text-white font-bold py-4 rounded-xl transition shadow-lg">
                            Pesan Sekarang
                        </button>
                        <a href="index.php?page=home" class="px-8 py-4 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl transition text-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
// Fungsi mengecek apakah tanggal masuk musim libur akhir tahun (Nataru) tanpa memandang tahun
function isNataruSeason(dateObj) {
    const bulan = dateObj.getMonth() + 1; // Januari = 1, Desember = 12
    const tanggal = dateObj.getDate();

    // Deteksi rentang 20 Desember s/id 5 Januari
    if ((bulan === 12 && tanggal >= 20) || (bulan === 1 && tanggal <= 5)) {
        return true;
    }
    return false;
}

// Fungsi utama hitung total sewa
async function hitungTotal() {
    const helperMobil = document.getElementById('id_mobil_harga_helper');
    let pricePerDay = helperMobil ? (parseInt(helperMobil.options[0].getAttribute('data-price')) || 0) : 0;
    
    const inputSewa = document.getElementById('tanggal_sewa').value;
    const inputKembali = document.getElementById('tanggal_kembali').value;
    const biayaAsuransi = document.getElementById('pakai_asuransi').checked ? 500000 : 0;

    if (inputSewa && inputKembali) {
        const tglSewa = new Date(inputSewa);
        const tglKembali = new Date(inputKembali);

        if (tglKembali >= tglSewa) {
            const diffTime = Math.abs(tglKembali - tglSewa);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

            // Ambil data tahun dari tanggal yang dipilih user secara dinamis
            const tahunSewa = tglSewa.getFullYear(); 
            let kenaikanTarif = 0;
            let pesanTarif = "";

            // 1. CEK LIBUR TETAP (NATARU)
            if (isNataruSeason(tglSewa)) {
                kenaikanTarif = 0.20; // Naik 20%
                pesanTarif = " (Tarif Libur Akhir Tahun +20%)";
            } 
            
            // 2. CEK LIBUR DINAMIS (LEBARAN & LIBUR NASIONAL LAINNYA VIA API)
            else {
                try {
                    // Mengambil data hari libur resmi Indonesia berdasarkan tahun sewa kendaraan
                    const response = await fetch(`https://dayoffapi.vercel.app/api?year=${tahunSewa}`);
                    const dataLibur = await response.json();
                    
                    // Format tanggal input sewa agar cocok dengan format API (YYYY-MM-DD)
                    const formatTglSewa = inputSewa; 

                    // Cari apakah tanggal sewa terdaftar sebagai hari libur nasional (Termasuk Lebaran)
                    const liburDitemukan = dataLibur.find(item => item.is_holiday && item.calendar_date === formatTglSewa);

                    if (liburDitemukan) {
                        kenaikanTarif = 0.25; // Libur keagamaan/Lebaran umumnya naik lebih tinggi, misal 25%
                        pesanTarif = ` (Tarif ${liburDitemukan.local_name} +25%)`;
                    }
                } catch (error) {
                    console.log("Gagal memuat API hari libur, menggunakan tarif normal.", error);
                }
            }

            // Hitung kalkulasi akhir harga sewa per hari setelah ditambah persentase kenaikan
            pricePerDay = pricePerDay + (pricePerDay * kenaikanTarif);
            const totalBiaya = (diffDays * pricePerDay) + biayaAsuransi;

            // Tampilkan hasil ke UI pelanggan
            document.getElementById('display_hari').innerText = diffDays + " Hari" + pesanTarif;
            document.getElementById('display_total').innerText = "Rp " + totalBiaya.toLocaleString('id-ID');
        } else {
            document.getElementById('display_hari').innerText = "0 Hari";
            document.getElementById('display_total').innerText = "Rp 0";
        }
    }
}
</script>
</body>
</html>