<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Karyawan Baru - Rent Car</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-2xl mx-auto my-10 p-8 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Registrasi Akun Karyawan Baru</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?page=karyawan_proses_tambah" method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Nama Lengkap Karyawan</label>
                <input type="text" name="nama_karyawan" required class="w-full border p-2 rounded focus:outline-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Email Resmi Karyawan</label>
                <input type="email" name="email" required class="w-full border p-2 rounded focus:outline-blue-500" placeholder="contoh: budi@rentcar.com">
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
                <label class="block text-gray-700 font-semibold mb-1">No. Telepon / WhatsApp</label>
                <input type="text" name="no_telp" class="w-full border p-2 rounded focus:outline-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Password Sementara</label>
                <input type="password" name="password" required class="w-full border p-2 rounded focus:outline-blue-500" placeholder="Minimal 6 karakter">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Role / Jabatan</label>
                    <select name="role" required class="w-full border p-2 rounded focus:outline-blue-500">
                        <option value="">-- Pilih Role --</option>
                        <option value="Manager">Manager</option>
                        <option value="Staff Admin">Staff Admin</option>
                        <option value="Staff Lapangan">Staff Lapangan</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="index.php?page=manager_dashboard" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">Buat Akun</button>
            </div>
        </form>
    </div>
</body>
</html>