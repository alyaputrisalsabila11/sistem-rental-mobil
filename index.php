<?php
// ==========================================
// FILE: index.php (Pembaruan Router Utama)
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Memulai sesi aplikasi secara global
}

// Autoloader Otomatis Kelas Model & Controller
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/'
    ];
    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

if (file_exists(__DIR__ . '/init.php')) {
    require_once __DIR__ . '/init.php'; // Memuat file database init jika ada
}

$page = $_GET['page'] ?? 'landing'; // Default ke landing page

// Proteksi Autentikasi Login Global
if (!isset($_SESSION['user_id']) && !in_array($page, ['landing', 'login', 'register'])) {
    header('Location: index.php?page=login');
    exit;
}

switch ($page) {
    case 'login':
        (new AuthController())->login();
        break;
    case 'register':
        (new AuthController())->register();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;

    // ======== ROUTE DASHBOARD MANAGER ========
    case 'manager_dashboard':
        $controller = new ManagerController();
        $controller->index();
        break;

    // ======== ROUTE PROSES CABANG/LOKASI (MANAGER) ========
    case 'lokasi_proses_tambah':
        (new LokasiController())->store();
        break;
    case 'lokasi_proses_edit':
        (new LokasiController())->update();
        break;
    case 'lokasi_hapus':
        (new LokasiController())->delete();
        break;

    // ======== ROUTE PROSES KARYAWAN (MANAGER) ========
    case 'karyawan_proses_tambah':
        (new KaryawanController())->store();
        break;
    case 'karyawan_proses_edit':
        (new KaryawanController())->update();
        break;
    case 'karyawan_hapus':
        (new KaryawanController())->delete();
        break;

    // ======== ROUTE PROSES LOYALITAS (MANAGER) ========
    case 'loyal_proses_tambah':
        (new LoyalController())->store();
        break;
    case 'loyal_proses_edit':
        (new LoyalController())->update();
        break;
    case 'loyal_hapus':
        (new LoyalController())->delete();
        break;

    // ======== ROUTE PROSES VOUCHER (MANAGER) ========
    case 'voucher_proses_tambah':
        (new VoucherController())->store();
        break;
    case 'voucher_proses_edit':
        (new VoucherController())->update();
        break;
    case 'voucher_hapus':
        (new VoucherController())->delete();
        break;

    // ======== ROUTE DASHBOARD STAFF ADMIN & FORM ========
    case 'Admin':
        $controller = new AdminController();
        $action = $_GET['action'] ?? '';
        
        if ($action === 'proses_tambah_mobil') {
            $controller->proses_tambah_mobil();
        } elseif ($action === 'proses_tambah_fasilitas') {
            $controller->proses_tambah_fasilitas();
        } elseif ($action === 'proses_upgrade_loyalitas') {
            // Logika admin melakukan upgrade manual tingkat loyalitas pelanggan
            $db = Database::getConnection();
            $id_pel = $_GET['id_pelanggan'];
            $id_lev = $_GET['id_level'];
            $stmt = $db->prepare("UPDATE pelanggan SET id_level = ? WHERE id_pelanggan = ?");
            $stmt->execute([$id_lev, $id_pel]);
            $_SESSION['success'] = "Tingkat loyalitas pelanggan berhasil ditingkatkan!";
            header('Location: index.php?page=Admin&action=home');
        } else {
            include 'views/user/dashboard/staffadmin.php'; // Load dashboard operasional admin
        }
        break;

    // ======== ROUTE DASHBOARD STAFF LAPANGAN & PROSES TRANSAKSI ========
    case 'home_lapangan':
        $action = $_GET['action'] ?? 'home';
        $db = Database::getConnection();

        if ($action === 'proses_handover') {
            // Aksi menyerahkan kunci mobil kepada pelanggan
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id_penyewaan = $_POST['id_penyewaan'];
                $id_karyawan = $_SESSION['user_id'];
                $id_lokasi = $_SESSION['id_lokasi'];
                $tgl_serah = date('Y-m-d');
                $jam_serah = date('H:i:s');

                // Unggah berkas foto bukti serah terima mobil bersama pelanggan
                $foto_bukti = "";
                if (!empty($_FILES['bukti_serah']['name'])) {
                    $foto_bukti = time() . "_" . $_FILES['bukti_serah']['name'];
                    move_uploaded_file($_FILES['bukti_serah']['tmp_name'], "public/uploads/bukti_serah/" . $foto_bukti);
                }

                // Masukkan data ke tabel penyerahan
                $stmt = $db->prepare("INSERT INTO penyerahan (id_penyewaan, id_karyawan, id_lokasi, tgl_penyerahan, jam_penyerahan, status_sewa) VALUES (?, ?, ?, ?, ?, 'ongoing')");
                $stmt->execute([$id_penyewaan, $id_karyawan, $id_lokasi, $tgl_serah, $jam_serah]);

                // Update status mobil menjadi 'Disewa' agar hilang dari gallery
                $stmtMobil = $db->prepare("UPDATE mobil SET status = 'Disewa' WHERE id_mobil = (SELECT id_mobil FROM penyewaan WHERE id_penyewaan = ?)");
                $stmtMobil->execute([$id_penyewaan]);

                $_SESSION['success'] = "Mobil berhasil diserahterimakan! Status sewa sekarang: Ongoing.";
                header('Location: index.php?page=home_lapangan&action=cek_kondisi');
            }
        } elseif ($action === 'proses_return') {
            // Aksi penjemputan/kembalian mobil dari pelanggan ke lapangan
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id_penyerahan = $_POST['id_penyerahan'];
                $id_karyawan = $_SESSION['user_id'];
                $id_lokasi = $_SESSION['id_lokasi'];
                $jam_kembali = date('H:i:s');
                $km_akhir = $_POST['km_akhir'];
                $bbm_akhir = $_POST['bbm_akhir'];
                $kondisi = $_POST['kondisi_mobil'];
                $catatan = $_POST['visual_notes']; // Berisi rangkuman kerusakan visual hasil klik diagram bodi
                
                // Kalkulasi otomatis denda kerusakan & denda telat
                $id_kerusakan = $_POST['id_kerusakan'] != '0' ? $_POST['id_kerusakan'] : null;
                $biaya_kerusakan = $_POST['biaya_kerusakan'];
                $denda_telat = $_POST['denda_telat'];

                // Insert ke tabel pengembalian
                $stmt = $db->prepare("INSERT INTO pengembalian (id_penyerahan, id_kerusakan, id_lokasi, id_karyawan, tgl_dikembaliakan, jam_dikembalikan, km_akhir, bbm_akhir, kondisi_mobil, catatan, biaya_kerusakan, denda_telat) VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_penyerahan, $id_kerusakan, $id_lokasi, $id_karyawan, $jam_kembali, $km_akhir, $bbm_akhir, $kondisi, $catatan, $biaya_kerusakan, $denda_telat]);

                // Update status sewa di penyerahan menjadi 'complete'
                $stmtSewa = $db->prepare("UPDATE penyerahan SET status_sewa = 'complete' WHERE id_penyerahan = ?");
                $stmtSewa->execute([$id_penyerahan]);

                // Update status mobil ke 'Maintenance' jika rusak, atau 'Tersedia' jika baik
                $status_baru = ($kondisi !== 'Baik') ? 'Maintenance' : 'Tersedia';
                $stmtMobil = $db->prepare("UPDATE mobil SET status = ? WHERE id_mobil = (SELECT p.id_mobil FROM penyewaan p JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan WHERE pen.id_penyerahan = ?)");
                $stmtMobil->execute([$status_baru, $id_penyerahan]);

                $_SESSION['success'] = "Proses pengembalian berhasil dicatat!";
                header('Location: index.php?page=home_lapangan&action=cek_kondisi');
            }
        } else {
            include 'views/user/dashboard/stafflapangan.php';
        }
        break;

    // ======== ROUTE DASHBOARD PELANGGAN ========
    case 'home_pelanggan':
    case 'home':
        include 'views/user/dashboard/pelanggan.php';
        break;

    case 'proses_sewa':
        (new SewaController())->store();
        break;

    default:
        include 'views/public/landing.php';
        break;
}
?>