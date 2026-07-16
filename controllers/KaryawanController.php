<?php
require_once __DIR__ . '/../init.php';

class KaryawanController {
    private $karyawanModel;

    public function __construct() {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    
    // Izinkan Manager, Staff Admin, dan Staff Lapangan menggunakan controller ini
    $allowedRoles = ['Manager', 'Staff Admin', 'Staff Lapangan'];
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: index.php?page=login');
        exit;
    }
    $this->karyawanModel = new KaryawanModel();
}

    // FUNGSI BARU: Mengambil jumlah tugas untuk ditampilkan di card dashboard staff
    public function getCountTugas() {
        return $this->karyawanModel->getTugasCount();
    }

    // FUNGSI BARU: Mengambil tugas spesifik untuk karyawan yang sedang login
    public function getMyTasks($id_karyawan) {
        return $this->karyawanModel->getTasksByKaryawan($id_karyawan);
    }

    // Memproses update data dari form edit
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_karyawan = $_POST['id_karyawan'];
            
            // DISESUAIKAN: Menangkap data alamat, no_ktp, dan status_supir dari form edit
            $data = [
                'nama_karyawan'   => trim($_POST['nama_karyawan']),
                'email'           => trim($_POST['email']),
                'no_telp'         => trim($_POST['no_telp']),
                'role'            => trim($_POST['role']),
                'status_karyawan' => trim($_POST['status_karyawan']),
                'id_lokasi'       => trim($_POST['id_lokasi']),
                'alamat'          => isset($_POST['alamat']) ? trim($_POST['alamat']) : '',
                'no_ktp'          => isset($_POST['no_ktp']) ? trim($_POST['no_ktp']) : '',
                'status_supir'    => isset($_POST['status_supir']) ? trim($_POST['status_supir']) : '',
                'password'        => !empty($_POST['password']) ? trim($_POST['password']) : null
            ];

            if ($this->karyawanModel->updateKaryawan($id_karyawan, $data)) {
                $_SESSION['success'] = 'Data karyawan berhasil diperbarui!';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui data karyawan.';
            }
            header('Location: index.php?page=manager_dashboard&action=buat_akun');
            exit;
        }
    }

    // Memproses aksi hapus karyawan
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->karyawanModel->deleteKaryawan($id)) {
                $_SESSION['success'] = 'Akun karyawan berhasil dihapus!';
            } else {
                $_SESSION['error'] = 'Gagal menghapus karyawan. Data mungkin terikat entitas lain.';
            }
        }
        header('Location: index.php?page=manager_dashboard&action=buat_akun');
        exit;
    }

    // Memproses Penyimpanan Data Karyawan Baru
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama_karyawan    = isset($_POST['nama_karyawan']) ? trim($_POST['nama_karyawan']) : '';
            $email            = isset($_POST['email']) ? trim($_POST['email']) : '';
            $id_lokasi        = isset($_POST['id_lokasi']) ? trim($_POST['id_lokasi']) : ''; 
            $no_telp          = isset($_POST['no_telp']) ? trim($_POST['no_telp']) : '';
            $password         = isset($_POST['password']) ? trim($_POST['password']) : '';
            $role             = isset($_POST['role']) ? trim($_POST['role']) : '';
            $status_karyawan  = isset($_POST['status_karyawan']) ? trim($_POST['status_karyawan']) : 'Aktif';
            
            // BARU: Menangkap input baru dari form tambah data
            $alamat           = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
            $no_ktp           = isset($_POST['no_ktp']) ? trim($_POST['no_ktp']) : '';
            $status_supir     = isset($_POST['status_supir']) ? trim($_POST['status_supir']) : '';

            // Validasi field utama yang wajib diisi
            if (empty($nama_karyawan) || empty($email) || empty($password) || empty($role)) {
                $_SESSION['error'] = 'Nama, Email, Password, dan Role wajib diisi!';
                header('Location: index.php?page=manager_dashboard&action=buat_akun');
                exit;
            }

            // Hash password secara aman
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database via model dengan menyertakan data baru
            $success = $this->karyawanModel->createKaryawan([
                'nama_karyawan'   => $nama_karyawan,
                'email'           => $email,
                'id_lokasi'       => $id_lokasi,
                'no_telp'         => $no_telp,
                'password'        => $hashed_password,
                'role'            => $role,
                'status_karyawan' => $status_karyawan,
                'alamat'          => $alamat,
                'no_ktp'          => $no_ktp,
                'status_supir'    => $status_supir
            ]);

            if ($success) {
                $_SESSION['success'] = 'Akun karyawan baru berhasil dibuat!';
                header('Location: index.php?page=manager_dashboard&action=data_karyawan');
            } else {
                $_SESSION['error'] = 'Gagal menambahkan karyawan. Email mungkin sudah terdaftar.';
                header('Location: index.php?page=manager_dashboard&action=buat_akun');
            }
            exit;
        }
    }

    // ==========================================
    // METODE BARU: PROSES HANDOVER SERAH KUNCI
    // ==========================================
    public function proses_handover() {
        $db = Database::getConnection();
        
        $id_penyewaan = $_POST['id_penyewaan'];
        $id_karyawan = $_SESSION['user_id'];
        $id_lokasi = $_SESSION['id_lokasi'];
        $tgl_penyerahan = $_POST['tgl_penyerahan'];
        $jam_penyerahan = $_POST['jam_penyerahan'];
        
        // Unggah berkas foto bukti penyerahan bersama pelanggan
        $foto_bukti = "";
        if (!empty($_FILES['bukti_serah']['name'])) {
            $foto_bukti = time() . "_" . $_FILES['bukti_serah']['name'];
            move_uploaded_file($_FILES['bukti_serah']['tmp_name'], "public/uploads/bukti_serah/" . $foto_bukti);
        }

        // 1. Simpan data ke tabel penyerahan
        $stmt = $db->prepare("INSERT INTO penyerahan (id_penyewaan, id_karyawan, id_lokasi, tgl_penyerahan, jam_penyerahan, status_sewa) VALUES (?, ?, ?, ?, ?, 'ongoing')");
        $stmt->execute([$id_penyewaan, $id_karyawan, $id_lokasi, $tgl_penyerahan, $jam_penyerahan]);

        // 2. Update status mobil menjadi 'Disewa' agar tidak tampil di gallery sewa pelanggan
        $stmtMobil = $db->prepare("UPDATE mobil SET status_mobil = 'Disewa' WHERE id_mobil = (SELECT id_mobil FROM penyewaan WHERE id_penyewaan = ?)");
        $stmtMobil->execute([$id_penyewaan]);

        $_SESSION['success'] = "Mobil berhasil diserahterimakan! Status sewa sekarang: Ongoing.";
        header('Location: index.php?page=home_lapangan&action=di_sewa');
        exit;
    }

    // ==========================================
    // METODE BARU: PROSES UNIT KEMBALI (RETURN)
    // ==========================================
    public function proses_return() {
        $db = Database::getConnection();
        
        $id_penyerahan = $_POST['id_penyerahan'];
        $id_karyawan = $_SESSION['user_id'];
        $id_lokasi = $_SESSION['id_lokasi'];
        $km_akhir = $_POST['km_akhir'];
        $bbm_akhir = $_POST['bbm_akhir'];

        // PERBAIKAN: Ambil tanggal & jam pengembalian dari input petugas (bukan lagi otomatis CURDATE/NOW)
        $tgl_pengembalian = !empty($_POST['tgl_pengembalian']) ? $_POST['tgl_pengembalian'] : date('Y-m-d');
        $jam_pengembalian = !empty($_POST['jam_pengembalian']) ? $_POST['jam_pengembalian'] : date('H:i');

        // PERBAIKAN: Ambil kondisi awal pilihan petugas (Normal/Rusak) lalu petakan ke nilai kondisi_mobil di DB
        $kondisi_awal = $_POST['kondisi_mobil_awal'] ?? 'Normal';
        $kondisi_mobil = ($kondisi_awal === 'Rusak') ? 'Rusak Ringan' : 'Baik';

        // Unggah foto kondisi kendaraan (bisa lebih dari satu file)
        $foto_kondisi_list = [];
        if (!empty($_FILES['foto_kondisi']['name'][0])) {
            $uploadDir = "public/uploads/kondisi_return/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            foreach ($_FILES['foto_kondisi']['name'] as $i => $namaFile) {
                if (!empty($namaFile)) {
                    $namaUnik = time() . "_" . $i . "_" . $namaFile;
                    move_uploaded_file($_FILES['foto_kondisi']['tmp_name'][$i], $uploadDir . $namaUnik);
                    $foto_kondisi_list[] = $namaUnik;
                }
            }
        }
        $foto_kondisi_string = implode(',', $foto_kondisi_list);

        // 1. Masukkan data pengembalian awal dengan nilai denda default
        $stmt = $db->prepare("INSERT INTO pengembalian (id_penyerahan, id_lokasi, id_karyawan, tgl_dikembalikan, jam_dikembalikan, km_akhir, bbm_akhir, kondisi_mobil, foto_kondisi, biaya_kerusakan, denda_telat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)");
        $stmt->execute([$id_penyerahan, $id_lokasi, $id_karyawan, $tgl_pengembalian, $jam_pengembalian, $km_akhir, $bbm_akhir, $kondisi_mobil, $foto_kondisi_string]);

        // 2. Update status sewa penyerahan menjadi complete
        $stmtPen = $db->prepare("UPDATE penyerahan SET status_sewa = 'complete' WHERE id_penyerahan = ?");
        $stmtPen->execute([$id_penyerahan]);

        // 3. Ubah status mobil menjadi 'Maintenance' sebelum dialihkan ke pemeriksaan kerusakan bodi
        $stmtMobil = $db->prepare("UPDATE mobil SET status_mobil = 'Maintenance' WHERE id_mobil = (SELECT p.id_mobil FROM penyewaan p JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan WHERE pen.id_penyerahan = ?)");
        $stmtMobil->execute([$id_penyerahan]);

        $_SESSION['success'] = "Unit mobil berhasil diterima kembali! Silakan lakukan cek kerusakan bodi biner.";
        header('Location: index.php?page=home_lapangan&action=cek_mobil');
        exit;
    }

    // ==========================================
    // METODE BARU: PROSES CEK KERUSAKAN BODI & DENDA
    // ==========================================
    public function proses_cek_kondisi() {
        $db = Database::getConnection();
        
        $id_pengembalian = $_POST['id_pengembalian'];
        $id_kerusakan = $_POST['id_kerusakan'] != '0' ? $_POST['id_kerusakan'] : null;
        $kondisi_mobil = $_POST['kondisi_mobil'];
        $catatan = $_POST['catatan_visual'];
        $biaya_kerusakan = $_POST['biaya_kerusakan'];
        $denda_telat = $_POST['denda_telat'];

        // 1. Perbarui data kerusakan dan denda di tabel pengembalian
        $stmt = $db->prepare("UPDATE pengembalian SET id_kerusakan = ?, kondisi_mobil = ?, catatan = ?, biaya_kerusakan = ?, denda_telat = ?, checklist = 'Verified' WHERE id_pengembalian = ?");
        $stmt->execute([$id_kerusakan, $kondisi_mobil, $catatan, $biaya_kerusakan, $denda_telat, $id_pengembalian]);

        // 2. Kembalikan status mobil ke 'Tersedia' jika tidak ada kerusakan (kondisi mulus)
        if ($kondisi_mobil === 'Baik') {
            $stmtMobil = $db->prepare("UPDATE mobil SET status_mobil = 'Tersedia' WHERE id_mobil = (
                SELECT p.id_mobil FROM penyewaan p 
                JOIN penyerahan pen ON p.id_penyewaan = pen.id_penyewaan 
                JOIN pengembalian pg ON pen.id_penyerahan = pg.id_penyerahan 
                WHERE pg.id_pengembalian = ?
            )");
            $stmtMobil->execute([$id_pengembalian]);
        }

        $_SESSION['success'] = "Laporan cek bodi visual berhasil disimpan ke database!";
        header('Location: index.php?page=home_lapangan&action=home');
        exit;
    }
}