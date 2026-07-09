<?php
$brand_name = "Rental Mobil";

// Get dan clear error message
$error_message = '';
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Get dan clear success message
$success_message = '';
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= $brand_name; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-[#2d1b4e] via-[#3d2463] to-[#4d3073] min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Card Login -->
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#5138bc] to-[#6d44b8] text-white p-8 text-center">
                <div class="flex justify-center items-center space-x-3 mb-2">
                    <i class="fas fa-car text-4xl"></i>
                    <h1 class="text-3xl font-bold"><?= $brand_name; ?></h1>
                </div>
                <p class="text-sm text-gray-200">Masuk ke Akun Anda</p>
            </div>

            <!-- Body -->
            <div class="p-8">
                <!-- Back Link -->
                <a href="index.php?page=landing" class="inline-flex items-center text-[#5138bc] hover:text-[#6d44b8] text-sm font-medium mb-6 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>

                <!-- Error Message -->
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm flex items-start">
                        <i class="fas fa-exclamation-circle mr-2 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Success Message -->
                <?php if (!empty($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm flex items-start">
                        <i class="fas fa-check-circle mr-2 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" class="space-y-4" autocomplete="off">
                    
                    <!-- Username/Email -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-user text-[#ff5722] mr-1"></i> Username atau Email
                        </label>
                        <input type="text" name="username" id="input_user_login" placeholder="Masukkan username atau email" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm"
                               autocomplete="new-password"
                               required>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-gray-700 font-semibold text-sm mb-2">
                            <i class="fas fa-lock text-[#ff5722] mr-1"></i> Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="Masukkan password Anda" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#5138bc] focus:ring-2 focus:ring-[#5138bc] focus:ring-opacity-20 transition text-sm pr-10"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
                                <i id="toggleIcon" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-gradient-to-r from-[#ff5722] to-[#e64a19] text-white font-bold py-3 px-4 rounded-lg hover:shadow-lg transition duration-300 flex items-center justify-center space-x-2 mt-6">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-200">
                <p class="text-gray-600 text-sm">
                    Belum punya akun? 
                    <a href="index.php?page=register" class="text-[#5138bc] font-semibold hover:text-[#6d44b8] transition">Daftar di sini</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
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