<?php
require_once __DIR__ . '/../init.php';

class SewaController {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_pelanggan = $_SESSION['user_id'];
            $id_mobil     = $_POST['id_mobil'];
            $tgl_mulai    = $_POST['tgl_mulai'];
            $tgl_selesai  = $_POST['tgl_selesai'];
            $id_fasilitas = $_POST['id_fasilitas'] != "0" ? $_POST['id_fasilitas'] : null;
            $id_penukaran = $_POST['id_penukaran'] != "0" ? $_POST['id_penukaran'] : null;
            $pake_asuransi = isset($_POST['pake_asuransi']) ? 1 : 0;

            // 1. Hitung Durasi
            $start = new DateTime($tgl_mulai);
            $end   = new DateTime($tgl_selesai);
            $durasi = $start->diff($end)->days;
            if ($durasi <= 0) $durasi = 1;

            // 2. Hitung Total Harga (Server Side Validation)
            // Ambil harga mobil
            $stmtM = $this->db->prepare("SELECT harga_dinamis FROM mobil WHERE id_mobil = ?");
            $stmtM->execute([$id_mobil]);
            $harga_mobil = $stmtM->fetchColumn();

            $total_harga = $harga_mobil * $durasi;

            // Tambah Fasilitas jika ada
            if ($id_fasilitas) {
                $stmtF = $this->db->prepare("SELECT harga FROM fasilitas WHERE id_fasilitas = ?");
                $stmtF->execute([$id_fasilitas]);
                $total_harga += $stmtF->fetchColumn();
            }

            // Tambah Asuransi (Misal Flat 50rb)
            if ($pake_asuransi) {
                $total_harga += 50000;
            }

            // Kurangi Voucher jika ada
            if ($id_penukaran) {
                $stmtV = $this->db->prepare("SELECT v.diskon FROM penukaran p JOIN voucher v ON p.id_voucher = v.id_voucher WHERE p.id_penukaran = ?");
                $stmtV->execute([$id_penukaran]);
                $total_harga -= $stmtV->fetchColumn();
            }

            // 3. Handle Upload Bukti Bayar
            $nama_file = null;
            if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
                $target_dir = "public/uploads/bukti_bayar/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                
                $nama_file = time() . '_' . $_FILES['bukti_bayar']['name'];
                move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $target_dir . $nama_file);
            }

            // 4. Insert ke Tabel Penyewaan
            $kode_sewa = "INV-" . strtoupper(bin2hex(random_bytes(3)));
            
            try {
                $this->db->beginTransaction();

                $sql = "INSERT INTO penyewaan (kode_penyewaan, id_pelanggan, id_mobil, id_fasilitas, tgl_penyewaan, tgl_mulai_sewa, tgl_selesai_sewa, durasi_hari, total_harga, status_penyewaan, bukti_bayar) 
                        VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, 'Pending', ?)";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$kode_sewa, $id_pelanggan, $id_mobil, $id_fasilitas, $tgl_mulai, $tgl_selesai, $durasi, $total_harga, $nama_file]);
                
                $id_penyewaan_baru = $this->db->lastInsertId();

                // 5. Update Status Voucher jika digunakan
                if ($id_penukaran) {
                    $stmtUpdVoucher = $this->db->prepare("UPDATE penukaran SET status_pakai = 'sudah_dipakai', tgl_pakai = NOW(), id_penyewaan = ? WHERE id_penukaran = ?");
                    $stmtUpdVoucher->execute([$id_penyewaan_baru, $id_penukaran]);
                }

                $this->db->commit();
                header("Location: index.php?page=home&action=sewa_saya&status=success");
            } catch (Exception $e) {
                $this->db->rollBack();
                die("Gagal menyimpan penyewaan: " . $e->getMessage());
            }
        }
    }
}