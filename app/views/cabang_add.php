<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Cabang Baru - Rent Car</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-2xl mx-auto my-10 p-8 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Registrasi Cabang Baru</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="index.php?page=cabang_proses_tambah" method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-1">Nama Cabang</label>
                <input type="text" name="nama_cabang" required class="w-full border p-2 rounded focus:outline-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Kota</label>
                <input type="text" name="kota" required class="w-full border p-2 rounded focus:outline-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Alamat Lengkap</label>
                <input type="text" name="alamat" required class="w-full border p-2 rounded focus:outline-blue-500">
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="index.php?page=manager_dashboard" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">Buat Cabang</button>
            </div>
        </form>
    </div>
</body>
</html>