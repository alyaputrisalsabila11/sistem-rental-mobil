<?php
$brand_name = "Rental Mobil";

// Get error message
$error_message = '';
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= $brand_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-[#2d1b4e] via-[#3d2463] to-[#4d3073] min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Card Register -->
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white p-8 text-center">
                <div class="flex justify-center items-center space-x-3 mb-2">
                    <i class="fas fa-car text-4xl"></i>
                    <h1 class="text-3xl font-bold"><?= $brand_name; ?></h1>
                </div>
                <p class="text-sm text-gray-200">Buat Akun Baru</p>
            </div>

            <!-- Body -->
            <div class="p-8 max-h-[70vh] overflow-y-auto">
                <!-- Back Link -->
                <a href="index.php?page=landing" class="inline-flex items-center text-[#5138bc] hover:text-[#6d44b8] text-sm font-medium mb-6 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>

                <!-- Error Message -->
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm flex items-start">
                        <i class="fas fa-exclamation-circle mr-2 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <?php echo $error_message; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" class="space-y-4">
                    
                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-user text-[#ff5722] mr-1"></i> Nama Lengkap
                        </label>
                        <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm"
                               required>
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-at text-[#ff5722] mr-1"></i> Username
                        </label>
                        <input type="text" name="username" placeholder="Minimal 3 karakter (huruf, angka, underscore)" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm"
                               required>
                        <p class="text-gray-500 text-xs mt-1">Hanya huruf, angka, dan underscore</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-envelope text-[#ff5722] mr-1"></i> Email
                        </label>
                        <input type="email" name="email" placeholder="Masukkan email Anda" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm"
                               required>
                    </div>

                    <!-- Nomor Telepon -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-phone text-[#ff5722] mr-1"></i> Nomor Telepon
                        </label>
                        <input type="tel" name="no_telp" placeholder="Masukkan nomor telepon" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm"
                               required>
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-map-marker-alt text-[#ff5722] mr-1"></i> Alamat
                        </label>
                        <textarea name="alamat" placeholder="Masukkan alamat Anda" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm resize-none"
                                  required></textarea>
                    </div>

                    <!-- No. KTP (Opsional) -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-id-card text-[#ff5722] mr-1"></i> No. KTP <span class="text-gray-400 text-xs">(Opsional)</span>
                        </label>
                        <input type="text" name="no_ktp" placeholder="Masukkan nomor KTP" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-lock text-[#ff5722] mr-1"></i> Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm pr-10"
                                   required>
                            <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-2 text-gray-500 hover:text-gray-700">
                                <i id="toggleIcon1" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        <p class="text-gray-500 text-xs mt-1">Password harus minimal 6 karakter</p>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-lock text-[#ff5722] mr-1"></i> Konfirmasi Password
                        </label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm pr-10"
                                   required>
                            <button type="button" onclick="togglePassword('confirm_password')" class="absolute right-3 top-2 text-gray-500 hover:text-gray-700">
                                <i id="toggleIcon2" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-gradient-to-r from-[#ff5722] to-[#e64a19] text-white font-bold py-3 px-4 rounded-lg hover:shadow-lg transition duration-300 flex items-center justify-center space-x-2 mt-6">
                        <i class="fas fa-user-plus"></i>
                        <span>Daftar</span>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-200">
                <p class="text-gray-600 text-sm">
                    Sudah punya akun? 
                    <a href="index.php?page=login" class="text-[#5138bc] font-semibold hover:text-[#6d44b8] transition">Login di sini</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId === 'password' ? 'toggleIcon1' : 'toggleIcon2');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
