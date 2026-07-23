<?php
require_once __DIR__ . '/../init.php';

class SewaController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

public function store()
    {
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

            // 2. Hitung Total Harga
            $stmtM = $this->db->prepare("SELECT harga_dinamis FROM mobil WHERE id_mobil = ?");
            $stmtM->execute([$id_mobil]);
            $harga_mobil = $stmtM->fetchColumn();

            $total_harga = $harga_mobil * $durasi;

            if ($id_fasilitas) {
                $stmtF = $this->db->prepare("SELECT harga FROM fasilitas WHERE id_fasilitas = ?");
                $stmtF->execute([$id_fasilitas]);
                $total_harga += $stmtF->fetchColumn();
            }

            if ($pake_asuransi) {
                $total_harga += 50000;
            }

            if ($id_penukaran) {
                $stmtV = $this->db->prepare("SELECT v.diskon_persen FROM penukaran_voucher p JOIN voucher v ON p.id_voucher = v.id_voucher WHERE p.id_penukaran = ?");
                $stmtV->execute([$id_penukaran]);
                $diskon_persen = $stmtV->fetchColumn();
                
                if ($diskon_persen) {
                    $potongan = ($diskon_persen / 100) * ($harga_mobil * $durasi);
                    $total_harga -= $potongan;
                }
            }

            // 3. OPTIMASI: Membuka file gambar sebagai Resource Stream (bukan string biner mentah)
            $bukti_bayar_stream = null;
            if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
                $bukti_bayar_stream = fopen($_FILES['bukti_bayar']['tmp_name'], 'rb');
            } else {
                die("Gagal: Bukti pembayaran wajib diunggah.");
            }

            $kode_sewa = "INV-" . strtoupper(bin2hex(random_bytes(3)));

            try {
                $this->db->beginTransaction();

                // Menggunakan Named Parameter agar binding data LOB lebih rapi dan aman
                $sql = "INSERT INTO penyewaan (kode_penyewaan, id_pelanggan, id_mobil, id_fasilitas, tgl_penyewaan, tgl_mulai_sewa, tgl_selesai_sewa, durasi_hari, total_harga, status_penyewaan, bukti_bayar) 
                        VALUES (:kode_sewa, :id_pelanggan, :id_mobil, :id_fasilitas, NOW(), :tgl_mulai, :tgl_selesai, :durasi, :total_harga, 'Confirmed', :bukti_bayar)";
                
                $stmt = $this->db->prepare($sql);
                
                $stmt->bindValue(':kode_sewa', $kode_sewa);
                $stmt->bindValue(':id_pelanggan', $id_pelanggan);
                $stmt->bindValue(':id_mobil', $id_mobil);
                $stmt->bindValue(':id_fasilitas', $id_fasilitas);
                $stmt->bindValue(':tgl_mulai', $tgl_mulai);
                $stmt->bindValue(':tgl_selesai', $tgl_selesai);
                $stmt->bindValue(':durasi', $durasi);
                $stmt->bindValue(':total_harga', $total_harga);
                
                // Menggunakan PDO::PARAM_LOB khusus untuk kolom data gambar/blob besar
                $stmt->bindParam(':bukti_bayar', $bukti_bayar_stream, PDO::PARAM_LOB);
                $stmt->execute();
                
                $id_penyewaan_baru = $this->db->lastInsertId();

                // Tambah Poin
                $stmtTambahPoin = $this->db->prepare("UPDATE pelanggan SET poin = COALESCE(poin, 0) + 1 WHERE id_pelanggan = ?");
                $stmtTambahPoin->execute([$id_pelanggan]);

                // Update Voucher
                if ($id_penukaran) {
                    $stmtUpdVoucher = $this->db->prepare("UPDATE penukaran_voucher SET status_pakai = 'sudah_dipakai', tgl_pakai = NOW(), id_penyewaan = ? WHERE id_penukaran = ?");
                    $stmtUpdVoucher->execute([$id_penyewaan_baru, $id_penukaran]);
                }
                
                $this->db->commit();
                header("Location: index.php?page=home&action=sewa_saya&status=success");
            } catch (Exception $e) {
                // OPTIMASI: Cek koneksi aktif sebelum melakukan rollback guna mencegah error beruntun
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                die("Gagal menyimpan penyewaan: " . $e->getMessage());
            }
        }
    }
}