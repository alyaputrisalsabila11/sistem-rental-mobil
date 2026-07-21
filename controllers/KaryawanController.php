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

        // Ambil tanggal & jam pengembalian dari input petugas
        $tgl_pengembalian = !empty($_POST['tgl_pengembalian']) ? $_POST['tgl_pengembalian'] : date('Y-m-d');
        $jam_pengembalian = !empty($_POST['jam_pengembalian']) ? $_POST['jam_pengembalian'] : date('H:i');

        // 1. Masukkan data pengembalian sesuai struktur tabel pengembalian yang baru (7 Kolom)
        $stmt = $db->prepare("
            INSERT INTO pengembalian (
                id_penyerahan,
                id_lokasi,
                id_karyawan,
                tgl_dikembalikan,
                jam_dikembalikan,
                denda_telat
            ) VALUES (?, ?, ?, ?, ?, 0)
        ");
        
        $stmt->execute([
            $id_penyerahan,
            $id_lokasi,
            $id_karyawan,
            $tgl_pengembalian,
            $jam_pengembalian
        ]);

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

    // FIX BUGS: Memperbaiki variabel penampung data untuk view edit_kondisi.php
public function edit_kondisi() {
        $id_kondisi = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        
        if (!$id_kondisi) {
            $_SESSION['error'] = "ID Kondisi tidak valid.";
            header('Location: index.php?page=home_lapangan&action=home');
            exit;
        }

        // Memanggil instance model untuk mengambil data kerusakan
        $evData = $this->karyawanModel->getKondisiMobilById($id_kondisi);

        if (!$evData) {
            $_SESSION['error'] = "Data kondisi tidak ditemukan.";
            header('Location: index.php?page=home_lapangan&action=home');
            exit;
        }

        // GANTI DI SINI: Panggil file dashboard staff lapangan tempat form kamu berada saat ini
        include 'views/user/dashboard/stafflapangan.php';
    }

    // 2. Memproses Update Data Kondisi
public function proses_edit_kondisi() {
        $db = Database::getConnection();
        
        $id_kondisi          = filter_var($_POST['id_kondisi'] ?? 0, FILTER_VALIDATE_INT);
        $tingkat_kerusakan   = $_POST['tingkat_kerusakan'] ?? 'Ringan';
        $estimasi_biaya      = filter_var($_POST['estimasi_biaya'] ?? 0, FILTER_VALIDATE_INT);
        $deskripsi_kerusakan = $_POST['deskripsi_kerusakan'] ?? '';
        
        // 1. Ambil input tgl_selesai dan amankan jika kosong agar menjadi NULL di database
        $tgl_selesai = !empty($_POST['tgl_selesai']) ? $_POST['tgl_selesai'] : null;
        
        if (!$id_kondisi) {
            $_SESSION['error'] = "Data tidak valid.";
            header('Location: index.php?page=home_lapangan&action=home');
            exit;
        }

        try {
            // Cek apakah user mengunggah gambar baru
            if (isset($_FILES['gambar_kerusakan']) && $_FILES['gambar_kerusakan']['error'] === UPLOAD_ERR_OK) {
                $gambar_biner = file_get_contents($_FILES['gambar_kerusakan']['tmp_name']);
                
                // 2. Tambahkan tgl_selesai = ? di dalam query ini
                $stmt = $db->prepare("
                    UPDATE kondisi_mobil 
                    SET deskripsi_kerusakan = ?, tingkat_kerusakan = ?, gambar_kerusakan = ?, estimasi_biaya = ?, tgl_selesai = ? 
                    WHERE id_kondisi = ?
                ");
                // Jangan lupa urutan datanya harus pas dengan tanda tanya (?) di atas
                $stmt->execute([$deskripsi_kerusakan, $tingkat_kerusakan, $gambar_biner, $estimasi_biaya, $tgl_selesai, $id_kondisi]);
                
            } else {
                // 3. Tambahkan tgl_selesai = ? juga di query alternatif (tanpa gambar)
                $stmt = $db->prepare("
                    UPDATE kondisi_mobil 
                    SET deskripsi_kerusakan = ?, tingkat_kerusakan = ?, estimasi_biaya = ?, tgl_selesai = ? 
                    WHERE id_kondisi = ?
                ");
                // Urutan datanya disesuaikan
                $stmt->execute([$deskripsi_kerusakan, $tingkat_kerusakan, $estimasi_biaya, $tgl_selesai, $id_kondisi]);
            }

            $_SESSION['success'] = "Data kondisi mobil berhasil diperbarui!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Gagal memperbarui data: " . $e->getMessage();
        }

        header('Location: index.php?page=home_lapangan&action=home');
        exit;
    }
}