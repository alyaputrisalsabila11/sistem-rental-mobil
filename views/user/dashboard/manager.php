<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard Manager - Rent Car</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body class="bg-gray-100 font-sans flex h-screen overflow-hidden">
        <!-- sidebar -->
        <?php include __DIR__ . '/../sidebar/managerside.php'; ?>

        <!-- main content -->
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

<!-- cabang -->
            <?php elseif ($action === 'buat_lokasi'): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>📍</span> <span>Registrasi Cabang</span>
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
                            
                            <button type="submit" class="w-full py-2.5 bg-[#6347C7] hover:bg-[#5239a7] text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Cabang Baru
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                            <span>📋</span> <span>Daftar Cabang Terdaftar</span>
                        </h2>

                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3">Nama Cabang</th>
                                        <th class="px-4 py-3">Kota</th>
                                        <th class="px-4 py-3">Alamat Lengkap</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
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
                                                <td class="px-4 py-4 text-sm">
                                                    <a href="index.php?page=manager_dashboard&action=edit_cabang&id=<?= $lokasi['id_lokasi']; ?>"
                                                    class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition border border-blue-200">
                                                    Edit
                                                    </a>
                                                    
                                                    <a href="index.php?page=lokasi_hapus&id=<?= $lokasi['id_lokasi']; ?>" 
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus cabang ini?');" 
                                                    class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-bold transition border border-red-200">
                                                    Hapus
                                                    </a>
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
                        </div>
                    </div>
                </div>

<!--edit cabang -->
            <?php elseif ($action === 'edit_cabang' && isset($lokasiEdit) && $lokasiEdit): ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm max-w-2xl mx-auto mt-8">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>✏️</span> <span>Edit Data Cabang</span>
                    </h2>

                    <form action="index.php?page=lokasi_proses_edit" method="POST" class="space-y-4">
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
                        
                        <div class="flex space-x-3 pt-4 border-t mt-4">
                            <button type="submit" class="flex-1 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Perubahan
                            </button>
                            <a href="index.php?page=manager_dashboard&action=buat_lokasi" class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-bold rounded-xl shadow-sm transition flex items-center justify-center">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>

<!-- KARYAWAN -->
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
                                <select name="id_lokasi" required class="w-full border p-2 rounded-lg text-sm bg-white focus:outline-indigo-500">
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
                            <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap min-w-[750px]">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3">Nama Karyawan</th>
                                        <th class="px-4 py-3">Email</th>
                                        <th class="px-4 py-3">Cabang</th>
                                        <th class="px-4 py-3">Jabatan</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
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
                                                    <?= htmlspecialchars($karyawan['nama_lokasi'] ?? 'Belum Ditentukan'); ?>
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
                                                <td class="px-4 py-4 text-center text-xs space-x-1">
                                                    <a href="index.php?page=manager_dashboard&action=edit_karyawan&id=<?= $karyawan['id_karyawan']; ?>" 
                                                    class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition border border-blue-200">
                                                    Edit
                                                    </a>

                                                    <a href="index.php?page=karyawan_hapus&id=<?= $karyawan['id_karyawan']; ?>" 
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?');" 
                                                    class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-bold transition border border-red-200">
                                                    Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="px-4 py-4 text-center text-gray-400 text-xs">Belum ada karyawan yang terdaftar.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
<!-- edit karyawan -->
                <?php elseif ($action === 'edit_karyawan' && isset($karyawanEdit) && $karyawanEdit): ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm max-w-2xl mx-auto mt-8">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>✏️</span> <span>Edit Data Akun Karyawan</span>
                    </h2>

                    <form action="index.php?page=karyawan_proses_edit" method="POST" class="space-y-4">
                        <input type="hidden" name="id_karyawan" value="<?= htmlspecialchars($karyawanEdit['id_karyawan']); ?>">
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Nama Lengkap Karyawan</label>
                            <input type="text" name="nama_karyawan" value="<?= htmlspecialchars($karyawanEdit['nama_karyawan']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($karyawanEdit['email']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">No. Telp / WhatsApp</label>
                            <input type="text" name="no_telp" value="<?= htmlspecialchars($karyawanEdit['no_telp']); ?>" class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Password Baru (Kosongkan jika tidak diganti)</label>
                            <input type="password" name="password" class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500" placeholder="Masukkan password baru jika ingin diubah">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Role / Jabatan</label>
                                <select name="role" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                                    <option value="Manager" <?= $karyawanEdit['role'] === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="Staff Admin" <?= $karyawanEdit['role'] === 'Staff Admin' ? 'selected' : ''; ?>>Staff Admin</option>
                                    <option value="Staff Lapangan" <?= $karyawanEdit['role'] === 'Staff Lapangan' ? 'selected' : ''; ?>>Staff Lapangan</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Status Karyawan</label>
                                <select name="status_karyawan" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                                    <option value="Aktif" <?= $karyawanEdit['status_karyawan'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Nonaktif" <?= $karyawanEdit['status_karyawan'] === 'Nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Penempatan Cabang</label>
                            <select name="id_lokasi" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                                <option value="">-- Pilih Cabang --</option>
                                <?php if (isset($daftarLokasi) && !empty($daftarLokasi)): ?>
                                    <?php foreach ($daftarLokasi as $lokasi): ?>
                                        <option value="<?= $lokasi['id_lokasi']; ?>" <?= $karyawanEdit['id_lokasi'] == $lokasi['id_lokasi'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($lokasi['nama_lokasi']); ?> (<?= htmlspecialchars($lokasi['kota']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="flex space-x-3 pt-4 border-t mt-4">
                            <button type="submit" class="flex-1 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                Simpan Perubahan
                            </button>
                            <a href="index.php?page=manager_dashboard&action=buat_akun" class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-bold rounded-xl shadow-sm transition flex items-center justify-center">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
<!-- loyalitas -->
        <?php elseif ($action === 'buat_loyal'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit lg:col-span-1">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>Buat Loyalitas</span>
                    </h2>
                    
                    <form action="index.php?page=loyal_proses_tambah" method="POST" class="flex flex-col space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Nama Level</label>
                            <input type="text" name="nama_level" placeholder="Nama Level (ex: Silver)" class="border p-2 rounded w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Syarat</label>
                            <input type="number" name="syarat" placeholder="Syarat (ex: 5)" class="border p-2 rounded w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Poin</label>
                            <input type="number" name="poin" placeholder="Poin yang Diberikan" class="border p-2 rounded w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Keterangan</label>
                            <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="border p-2 rounded w-full">
                        </div>
                        
                        <div class="flex items-center space-x-2 pt-2">
                            <input type="checkbox" name="aktif" class="form-checkbox h-4 w-4 text-indigo-600">
                            <span class="text-sm font-medium text-gray-700">Aktifkan Level</span>
                        </div>
                        
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2 rounded w-full mt-2 transition-colors">
                            Simpan
                        </button>
                    </form>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm lg:col-span-2 overflow-x-auto">
                    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                        <span>Daftar Level Loyalitas</span>
                    </h2>
                    
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="p-3 rounded-tl-lg">Nama Level</th>
                                <th class="p-3">Syarat</th>
                                <th class="p-3">Poin</th>
                                <th class="p-3">Keterangan</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 rounded-tr-lg text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $loyalLevels = (new LoyalModel())->getAllLoyal();
                            foreach ($loyalLevels as $level): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-3 font-medium text-gray-900"><?= htmlspecialchars($level['nama_level']); ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($level['syarat']); ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($level['poin']); ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($level['keterangan']); ?></td>
                                <td class="p-3">
                                    <?= ($level['status'] === 'Aktif')
                                        ? '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Aktif</span>' 
                                        : '<span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">Nonaktif</span>'; ?>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                <a href="index.php?page=manager_dashboard&action=edit_loyal&id=<?= $level['id_level']; ?>"
                                class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition border border-blue-200">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
<!-- edit loyal -->
        <?php elseif ($action === 'edit_loyal' && isset($loyalEdit) && $loyalEdit): ?>
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm max-w-2xl mx-auto">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                    <span>Edit Level Loyalitas</span>
                </h2>

                <form action="index.php?page=loyal_proses_edit" method="POST" class="space-y-4">
                    <input type="hidden" name="id_loyal" value="<?= htmlspecialchars($loyalEdit['id_level']); ?>">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Nama Level Loyalitas</label>
                        <input type="text" name="nama_level" value="<?= htmlspecialchars($loyalEdit['nama_level']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500" placeholder="Contoh: Gold, Platinum">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Syarat</label>
                            <input type="text" name="syarat" value="<?= htmlspecialchars($loyalEdit['syarat']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500" placeholder="Contoh: 1000000">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Poin</label>
                            <input type="number" name="poin" value="<?= htmlspecialchars($loyalEdit['poin']); ?>" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500" placeholder="Contoh: 100">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Status Level</label>
                        <select name="status" required class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500">
                            <option value="Aktif" <?= $loyalEdit['status'] === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Nonakftif" <?= $loyalEdit['status'] === 'Nonakftif' ? 'selected' : ''; ?>>Nonaktif</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Keterangan / Benefit</label>
                        <textarea name="keterangan" rows="3" class="w-full border p-2 rounded-lg text-sm focus:outline-indigo-500" placeholder="Masukkan keterangan atau benefit level ini..."><?= htmlspecialchars($loyalEdit['keterangan']); ?></textarea>
                    </div>
                    
                    <div class="flex space-x-3 pt-4 border-t mt-4">
                        <button type="submit" class="flex-1 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-bold rounded-xl shadow-sm transition">
                            Simpan Perubahan
                        </button>
                        <a href="index.php?page=manager_dashboard&action=buat_loyal" class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-bold rounded-xl shadow-sm transition flex items-center justify-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
<!-- voucher -->
<?php elseif ($action === 'buat_voucher'): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-fit lg:col-span-1">
                <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
                    <span>Buat Voucher</span>
                </h2>
                
                <form action="index.php?page=voucher_proses_tambah" method="POST" class="flex flex-col space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Nama Voucher</label>
                        <input type="text" name="nama_voucher" placeholder="ex(DISKON RENTAL EMAS)" class="border p-2 rounded w-full" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Diskon (%)</label>
                        <input type="number" name="diskon_potongan" min="1" max="100" placeholder="Contoh: 10" class="border p-2 rounded w-full" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Harga Poin (Syarat Klaim)</label>
                        <input type="number" name="harga_poin" min="0" placeholder="Contoh: 500" class="border p-2 rounded w-full" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Target Level Loyalitas</label>
                        <select name="id_level" required class="w-full border p-2 rounded-lg text-sm bg-white focus:outline-indigo-500">
                            <option value="">-- Pilih Level --</option>
                            <?php
                            $loyalLevels = (new LoyalModel())->getAllLoyal();
                            if (!empty($loyalLevels)):
                                foreach ($loyalLevels as $level):
                            ?>
                                <option value="<?= htmlspecialchars($level['id_level']); ?>">
                                    <?= htmlspecialchars($level['nama_level']); ?>
                                </option>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Berlaku Mulai</label>
                        <input type="datetime-local" name="tgl_berlaku_mulai" class="border p-2 rounded w-full" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Berlaku Selesai</label>
                        <input type="datetime-local" name="tgl_berlaku_selesai" class="border p-2 rounded w-full" required>
                    </div>
                    
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold px-4 py-2 rounded w-full mt-2 transition-colors">
                        Simpan
                    </button>
                </form>
            </div>

<div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
    <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 flex items-center space-x-2">
        <span>Daftar Voucher</span>
    </h2>

    <div class="overflow-x-auto rounded-xl border border-gray-100">
        <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap min-w-[850px]">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3">Nama Voucher</th>
                    <th class="px-4 py-3">Diskon</th>
                    <th class="px-4 py-3">Poin Diperlukan</th>
                    <th class="px-4 py-3">Tgl. Mulai</th>
                    <th class="px-4 py-3">Tgl. Selesai</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php
                $vouchers = (new VoucherModel())->getAllVoucher();
                if (!empty($vouchers)):
                    foreach ($vouchers as $v): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 font-bold text-gray-900">
                            <?= htmlspecialchars($v['nama_voucher']); ?>
                        </td>
                        
                        <td class="px-4 py-4 text-gray-700 font-semibold">
                            <?= htmlspecialchars($v['diskon_potongan']); ?>%
                        </td>
                        
                        <td class="px-4 py-4">
                            <?= number_format($v['harga_poin']); ?> Poin
                        </td>
                        
                        <td class="px-4 py-4 text-xs">
                            <?= date('d M Y H:i', strtotime($v['tgl_berlaku_mulai'])); ?>
                        </td>
                        
                        <td class="px-4 py-4 text-xs">
                            <?= date('d M Y H:i', strtotime($v['tgl_berlaku_selesai'])); ?>
                        </td>
                        
                        <td class="px-4 py-4 text-center">
                            <?php if (strtolower($v['status']) === 'aktif'): ?>
                                <span class="text-xs text-green-600 font-bold">
                                    ● Aktif
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-red-600 font-bold">
                                    ● Inaktif
                                </span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="px-4 py-4 text-center text-xs">
                            <a href="index.php?page=voucher_hapus&id=<?= $v['id_voucher'] ?>"
                               onclick="return confirm('Apakah Anda yakin ingin menghapus voucher <?= htmlspecialchars($v['nama_voucher']); ?>?')"
                               class="inline-block px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-bold transition border border-red-200">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php 
                    endforeach;
                else: ?>
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-400 text-xs">
                            Belum ada data voucher yang terdaftar.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
        </div>
        <?php endif; ?>
    </body>
</html>