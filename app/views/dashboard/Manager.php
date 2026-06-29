<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manager - Rent Car</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 font-sans flex h-screen overflow-hidden">

    <?php
    // Menentukan halaman aktif berdasarkan parameter 'action' di URL
    $action = isset($_GET['action']) ? $_GET['action'] : 'home';
    ?>

    <aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 shadow-xl">
        <div class="p-5 flex items-center space-x-3 border-b border-slate-800">
            <span class="text-xl font-black tracking-wider uppercase">SIREMO</span>
        </div>

        <div class="p-5 border-b border-slate-800 bg-slate-950/40 flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-bold text-white shadow shadow-indigo-500/50 flex-shrink-0">
                M
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?></p>
                <p class="text-xs text-slate-400 truncate"><?= htmlspecialchars($_SESSION['user_email'] ?? '@Manager.swm'); ?></p>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <a href="index.php?page=manager_dashboard&action=home" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'home' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <span>Dashboard</span>
            </a>

            <a href="index.php?page=manager_dashboard&action=buat_lokasi" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'buat_lokasi' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <span>Buat Cabang</span>
            </a>
            
            <a href="index.php?page=manager_dashboard&action=buat_akun" 
               class="flex items-center space-x-3 py-3 px-4 rounded-xl text-sm font-bold transition group <?= $action === 'buat_akun' ? 'bg-white/15 text-white shadow-inner border-l-4 border-indigo-500' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <span>Buat Akun & Staff</span>
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
                <h1 class="text-lg font-bold text-gray-800 capitalize"><?= $action === 'home' ? 'DASHBOARD' : ($action === 'buat_lokasi' ? 'BUAT CABANG' : 'BUAT AKUN BARU'); ?></h1>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span>Selamat datang,</span>
                <span class="font-bold text-gray-800"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Manager'); ?></span>
                <span class="w-2 h-2 bg-red-500 rounded-full inline-block animate-pulse ml-1"></span>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center space-x-3 shadow-sm">
                    <span class="text-lg">✅</span>
                    <span class="text-sm font-medium"><?= $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>


            <?php if ($action === 'home'): ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b pb-4 mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 flex items-center space-x-2">
                                <span>📊</span> <span>Laporan Transaksi Perusahaan</span>
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Data pendapatan real-time internal rental mobil</p>
                        </div>
                        
                        <div class="flex items-center space-x-2 mt-4 sm:mt-0">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-1">Ekspor:</span>
                            <a href="index.php?page=laporan_export_pdf" target="_blank" class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-bold transition border border-red-200">
                                📕 PDF
                            </a>
                            <a href="index.php?page=laporan_export_excel" class="px-3 py-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg text-xs font-bold transition border border-green-200">
                                📄 Excel
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3">ID Transaksi</th>
                                    <th class="px-6 py-3">Pelanggan</th>
                                    <th class="px-6 py-3">Mobil Unit</th>
                                    <th class="px-6 py-3">Tanggal Sewa</th>
                                    <th class="px-6 py-3 text-right">Total Bayar</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-bold text-gray-900">TRX001</td>
                                    <td class="px-6 py-4">Reyza</td>
                                    <td class="px-6 py-4 text-blue-600 font-medium">Avanza Veloz</td>
                                    <td class="px-6 py-4">2026-06-01</td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">Rp 350.000</td>
                                    <td class="px-6 py-4 text-center"><span class="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full font-medium">Selesai</span></td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-bold text-gray-900">TRX002</td>
                                    <td class="px-6 py-4">Aprilia</td>
                                    <td class="px-6 py-4 text-blue-600 font-medium">Honda Civic</td>
                                    <td class="px-6 py-4">2026-06-03</td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">Rp 700.000</td>
                                    <td class="px-6 py-4 text-center"><span class="px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full font-medium">Selesai</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif ($action === 'buat_lokasi'): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                    <span>Registrasi Cabang</span>
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
                            <textarea name="alamat" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500"></textarea>
                        </div>
                        
                        <button type="submit" class="w-full py-2.5 bg-[#6347C7] hover:bg-[#5239a7] text-white text-sm font-bold rounded-xl shadow-sm transition">
                            Simpan Cabang Baru
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>Daftar Cabang Terdaftar</span>
                    </h2>

                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Nama Cabang</th>
                                    <th class="px-4 py-3">Kota</th>
                                    <th class="px-4 py-3">Alamat Lengkap</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <?php if (!empty($daftarLokasi)): ?>
                                    <?php foreach ($daftarLokasi as $lokasi): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4 font-bold text-gray-900">
                                                <?= htmlspecialchars($lokasi['nama_lokasi']); ?>
                                            </td>
                                            <td class="px-4 py-4">
                                                <?= htmlspecialchars($lokasi['kota']); ?>
                                            </td>
                                            <td class="px-4 py-4 text-xs">
                                                <?= htmlspecialchars($lokasi['alamat']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-gray-400 text-xs">Belum ada cabang yang terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>


            <?php elseif ($action === 'buat_akun'): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>Registrasi Karyawan</span>
                        </h2>
                        
                        <form action="index.php?page=karyawan_proses_tambah" method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama_karyawan" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Email Resmi</label>
                                <input type="email" name="email" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500" placeholder="budi@rentcar.com">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Cabang / Lokasi</label>
                                <select name="lokasi_id" required class="w-full border p-2 rounded-lg text-sm bg-white focus:outline-indigo-500">
                                    <option value="">-- Pilih Cabang --</option>
                                    <?php if (!empty($daftarLokasi)): ?>
                                    <?php foreach ($daftarLokasi as $lokasi): ?>
                                        <option value="<?= htmlspecialchars($lokasi['id_lokasi']); ?>"><?= htmlspecialchars($lokasi['nama_lokasi'] . ' - ' . $lokasi['kota']); ?></option>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">No. Telp</label>
                                <input type="text" name="no_telp" class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Password Sementara</label>
                                <input type="password" name="password" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Jabatan / Role</label>
                                <select name="role" required class="w-full border p-2 rounded-lg text-sm bg-white focus:outline-indigo-500">
                                    <option value="Manager">Manager</option>
                                    <option value="Staff Admin">Staff Admin</option>
                                    <option value="Staff Lapangan">Staff Lapangan</option>
                                </select>
                            </div>
                            
                            <input type="hidden" name="status_karyawan" value="Aktif">
                            
                            <button type="submit" class="w-full py-2.5 bg-[#6347C7] hover:bg-[#5239a7] text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Akun Baru
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>Daftar Karyawan Terdaftar</span>
                        </h2>

                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3">Nama Karyawan</th>
                                        <th class="px-4 py-3">Email</th>
                                        <th class="px-4 py-3">Cabang</th>
                                        <th class="px-4 py-3">Jabatan</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <?php if (!empty($daftarKaryawan)): ?>
                                        <?php foreach ($daftarKaryawan as $karyawan): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-4 font-bold text-gray-900">
                                                    <?= htmlspecialchars($karyawan['nama_lengkap'] ?? $karyawan['nama_karyawan'] ?? 'Tanpa Nama'); ?>
                                                </td>
                                                <td class="px-4 py-4 text-xs">
                                                    <?= htmlspecialchars($karyawan['email']); ?>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <span class="px-2 py-0.5 text-xs font-semibold bg-purple-50 text-purple-700 rounded border border-purple-200">
                                                        <?= htmlspecialchars($karyawan['role']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-4 text-center">
                                                    <span class="text-xs text-green-600 font-bold">
                                                        ● <?= htmlspecialchars($karyawan['status_karyawan'] ?? 'Aktif'); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-4 py-4 text-center text-gray-400 text-xs">Belum ada karyawan yang terdaftar.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            <?php endif; ?>

        </main>

        <footer class="text-center text-xs text-gray-400 border-t border-gray-100 py-4 bg-white flex-shrink-0">
            &copy; 2026 Sistem Rental Mobil Internal • Modul Kontrol Terpadu Selesai.
        </footer>
    </div>

</body>
</html>