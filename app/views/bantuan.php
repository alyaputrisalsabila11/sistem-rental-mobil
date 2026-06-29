<?php $brand_name = "Rental Mobil"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bantuan - <?= $brand_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Fix Path: Karena bantuan.php ada di app/views/ -->
        <?php include __DIR__ . '/layouts/sidebar.php'; ?>

        <main class="flex-1 ml-64 p-8 flex justify-center items-center">
            <div class="w-full max-w-xl bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white px-6 py-4 flex items-center">
                    <i class="fas fa-question-circle mr-2 text-lg"></i>
                    <h2 class="text-lg font-bold">Pusat Layanan & Formulir Keluhan</h2>
                </div>
                
                <form action="index.php?page=kirim_keluhan" method="POST" class="p-6 space-y-4">
                    <!-- Form Konten... -->
                </form>
            </div>
        </main>
    </div>
</body>
</html>