<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// database connection
require_once __DIR__ . '/config/database.php';

//controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/PelangganController.php';
require_once __DIR__ . '/controllers/KaryawanController.php';
require_once __DIR__ . '/controllers/LokasiController.php';
require_once __DIR__ . '/controllers/ManagerController.php';
require_once __DIR__ . '/controllers/VoucherController.php';
require_once __DIR__ . '/controllers/LoyalController.php';

//models
require_once __DIR__ . '/models/PelangganModel.php';
require_once __DIR__ . '/models/KaryawanModel.php';
require_once __DIR__ . '/models/LokasiModel.php';
require_once __DIR__ . '/models/VoucherModel.php';
require_once __DIR__ . '/models/LoyalModel.php';