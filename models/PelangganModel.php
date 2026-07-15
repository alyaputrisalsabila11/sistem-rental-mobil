<?php
require_once __DIR__ . '/../init.php';

class PelangganModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Cek email sudah terdaftar
    public function emailExists($email) {
        try {
            // PERBAIKAN: Nama kolom disesuaikan menjadi id_pelanggan
            $stmt = $this->db->prepare("SELECT id_pelanggan FROM pelanggan WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking email: " . $e->getMessage());
            return false;
        }
    }

    // Cek username sudah terdaftar
    public function usernameExists($username) {
        try {
            // PERBAIKAN: Nama kolom disesuaikan menjadi id_pelanggan
            $stmt = $this->db->prepare("SELECT id_pelanggan FROM pelanggan WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking username: " . $e->getMessage());
            return false;
        }
    }

    // Ambil user berdasarkan email
    public function getUserByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return false;
        }
    }

    // Ambil user berdasarkan username
    public function getUserByUsername($username) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by username: " . $e->getMessage());
            return false;
        }
    }

    // Ambil user berdasarkan ID (Disesuaikan ke kolom 'id_pelanggan')
    public function getUserById($id) {
        try {
            // PERBAIKAN: Nama kolom disesuaikan menjadi id_pelanggan
            $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by id: " . $e->getMessage());
            return false;
        }
    }

    // Buat user baru
    public function createUser($nama_lengkap, $username, $email, $password, $no_telp, $alamat, $no_ktp = null) {
        try {
            // id_level langsung diisi 1 (asumsi id_level = 1 adalah level Regular/Bronze di tabel loyalitas)
            $stmt = $this->db->prepare(
                "INSERT INTO pelanggan (nama_lengkap, username, email, password, no_telp, alamat, no_ktp, poin, id_level) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)"
            );
            $result = $stmt->execute([$nama_lengkap, $username, $email, $password, $no_telp, $alamat, $no_ktp]);
            
            if (!$result) {
                error_log("Insert failed: " . implode(", ", $stmt->errorInfo()));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

   public function getAllPelanggan() {
        try {
            // Kita coba LEFT JOIN untuk mendapatkan nama levelnya
            // Menggunakan query() langsung agar lebih stabil untuk SELECT statis
            $sql = "SELECT p.*, l.nama_level 
                    FROM pelanggan p 
                    LEFT JOIN loyal l ON p.id_level = l.id_level 
                    ORDER BY p.id_pelanggan DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // FALLBACK: Jika tabel loyal bermasalah, ambil data pelanggan saja agar tetap muncul di dashboard
            $stmt = $this->db->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }


    // Update profil user (Disesuaikan ke kolom 'id_pelanggan')
    public function updateUser($id, $nama_lengkap, $no_telp, $alamat) {
        try {
            // PERBAIKAN: Nama kolom disesuaikan menjadi id_pelanggan
            $stmt = $this->db->prepare(
                "UPDATE pelanggan SET nama_lengkap = ?, no_telp = ?, alamat = ? 
                 WHERE id_pelanggan = ?"
            );
            return $stmt->execute([$nama_lengkap, $no_telp, $alamat, $id]);
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    public function redeemVoucher($id_pelanggan, $id_voucher) {
        try {
            // 1. Ambil data voucher & cek keaktifannya
            $stmtVoucher = $this->db->prepare("SELECT * FROM voucher WHERE id_voucher = ? AND status = 'Aktif' LIMIT 1");
            $stmtVoucher->execute([$id_voucher]);
            $voucher = $stmtVoucher->fetch(PDO::FETCH_ASSOC);

            if (!$voucher) {
                throw new Exception("Voucher tidak ditemukan atau sudah tidak aktif!");
            }

            // Cek masa berlaku voucher
            $today = date('Y-m-d');
            if ($today < $voucher['tgl_mulai'] || $today > $voucher['tgl_selesai']) {
                throw new Exception("Voucher ini sudah kedaluwarsa atau belum bisa digunakan!");
            }

            // Cek ketersediaan kuota voucher
            if ($voucher['kuota'] <= 0) {
                throw new Exception("Kuota penukaran voucher ini sudah habis!");
            }

            // 2. Ambil data poin & level pelanggan saat ini
            $pelanggan = $this->getUserById($id_pelanggan);
            if (!$pelanggan) {
                throw new Exception("Data pelanggan tidak ditemukan!");
            }

            // Cek apakah poin pelanggan mencukupi
            if ($pelanggan['poin'] < $voucher['harga_poin']) {
                throw new Exception("Poin Anda tidak cukup! Poin saat ini: " . $pelanggan['poin'] . " Poin, dibutuhkan: " . $voucher['harga_poin'] . " Poin.");
            }

            // Cek level minimal pelanggan (jika voucher membatasi id_level tertentu)
            if (isset($voucher['id_level']) && !empty($voucher['id_level'])) {
                if ($pelanggan['id_level'] < $voucher['id_level']) {
                    throw new Exception("Level loyalitas Anda belum mencukupi untuk menukar voucher ini!");
                }
            }

            // 3. MULAI TRANSAKSI AMAN (DATABASE TRANSACTION)
            $this->db->beginTransaction();

            // A. Kurangi poin pelanggan di tabel pelanggan
            $updatePoin = $this->db->prepare("UPDATE pelanggan SET poin = poin - ? WHERE id_pelanggan = ?");
            $updatePoin->execute([$voucher['harga_poin'], $id_pelanggan]);

            // B. Kurangi kuota voucher di tabel voucher
            $updateKuota = $this->db->prepare("UPDATE voucher SET kuota = kuota - 1 WHERE id_voucher = ?");
            $updateKuota->execute([$id_voucher]);

            // C. Masukkan riwayat klaim ke tabel penukaran_voucher
            $insertPenukaran = $this->db->prepare("
                INSERT INTO penukaran_voucher (id_pelanggan, id_voucher, status_pakai, tgl_klaim) 
                VALUES (?, ?, 'belum_dipakai', NOW())
            ");
            $insertPenukaran->execute([$id_pelanggan, $id_voucher]);

            // Jika semua operasi di atas sukses, simpan secara permanen ke database
            $this->db->commit();
            return [
                'status' => true,
                'message' => "Selamat! Voucher '" . htmlspecialchars($voucher['nama_voucher']) . "' berhasil ditukarkan."
            ];

        } catch (Exception $e) {
            // Jika ada yang gagal, batalkan semua manipulasi di atas agar poin tidak hilang
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>