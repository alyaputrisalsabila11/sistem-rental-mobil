<?php

class BookingController {
    
    public function simpan() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Sesuaikan jumlah "../" agar mengarah ke folder config/database.php dengan benar
        // Jika file ini di app/controllers/transaksi/BookingController.php, maka mundur 3 kali:
        require_once __DIR__ . '/../../../config/database.php'; 

        try {
            $db = Database::getConnection();

            // 1. Ambil data dari form dan session login pelanggan
            $id_pelanggan     = $_SESSION['user_id'] ?? null; 
            $id_mobil         = isset($_POST['id_mobil']) ? intval($_POST['id_mobil']) : 0;
            $tgl_mulai_sewa   = $_POST['tanggal_sewa'] ?? null;
            $tgl_selesai_sewa = $_POST['tanggal_kembali'] ?? null;
            
            // Proteksi keamanan: pastikan user sudah login
            if (!$id_pelanggan) {
                throw new Exception("Sesi Anda telah berakhir. Silakan login kembali!");
            }

            if (!$id_mobil || !$tgl_mulai_sewa || !$tgl_selesai_sewa) {
                throw new Exception("Data formulir tidak lengkap!");
            }

            // Format data penunjang (Sopir & Asuransi)
            $sopir_booking    = isset($_POST['pakai_sopir']) ? 'Pakai Sopir' : 'Tanpa Sopir';
            $asuransi         = isset($_POST['pakai_asuransi']) ? 500000 : 0;

            // 2. Kalkulasi durasi hari sewa di sisi server (Back-end)
            $date1 = new DateTime($tgl_mulai_sewa);
            $date2 = new DateTime($tgl_selesai_sewa);
            $durasi_hari = $date1->diff($date2)->days + 1;

            if ($durasi_hari <= 0) {
                throw new Exception("Tanggal kembali tidak boleh mendahului tanggal mulai sewa!");
            }

            // Ambil data harga mobil asli dari database demi keamanan nominal transaksi
            $stmtMobil = $db->prepare("SELECT harga_dinamis FROM mobil WHERE id_mobil = :id");
            $stmtMobil->execute([':id' => $id_mobil]);
            $mobil = $stmtMobil->fetch(PDO::FETCH_ASSOC);
            
            if (!$mobil) {
                throw new Exception("Unit armada mobil tidak ditemukan!");
            }

            $harga_mobil = $mobil['harga_dinamis'];
            $biaya_sopir = (isset($_POST['pakai_sopir'])) ? 200000 : 0;
            
            // Rumus Total Harga: (Hari x (Harga Mobil + Biaya Sopir)) + Asuransi Flat
            $total_harga = ($durasi_hari * ($harga_mobil + $biaya_sopir)) + $asuransi;

            // 3. Membuat Kode Booking Unik Otomatis
            $kode_booking = "BKG-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -4));

            // 4. Memproses file Bukti Pembayaran ke bentuk Biner (BLOB)
            $blob_bukti = null;
            if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === 0) {
                $file_tmp = $_FILES['bukti_pembayaran']['tmp_name'];
                $blob_bukti = file_get_contents($file_tmp); // Mengubah file gambar menjadi teks biner
            } else {
                throw new Exception("Anda wajib mengunggah foto bukti transfer pembayaran!");
            }

            // 5. Query INSERT data transaksi ke tabel booking kamu
            $sql = "INSERT INTO booking (kode_booking, id_pelanggan, id_mobil, sopir_booking, asuransi, tgl_mulai_sewa, tgl_selesai_sewa, durasi_hari, total_harga, status_booking, bukti_bayar) 
                    VALUES (:kode_booking, :id_pelanggan, :id_mobil, :sopir_booking, :asuransi, :tgl_mulai_sewa, :tgl_selesai_sewa, :durasi_hari, :total_harga, 'Pending', :bukti_bayar)";
            
            $stmt = $db->prepare($sql);
            $simpan = $stmt->execute([
                ':kode_booking'     => $kode_booking,
                ':id_pelanggan'     => $id_pelanggan,
                ':id_mobil'         => $id_mobil,
                ':sopir_booking'    => $sopir_booking,
                ':asuransi'         => $asuransi,
                ':tgl_mulai_sewa'   => $tgl_mulai_sewa,
                ':tgl_selesai_sewa' => $tgl_selesai_sewa,
                ':durasi_hari'      => $durasi_hari,
                ':total_harga'      => $total_harga,
                ':bukti_bayar'      => $blob_bukti
            ]);

            if ($simpan) {
                // Otomatis ubah status mobil jadi 'Disewa' agar tidak bisa dipilih orang lain di galeri
                $updateMobil = $db->prepare("UPDATE mobil SET status_mobil = 'Disewa' WHERE id_mobil = :id_mobil");
                $updateMobil->execute([':id_mobil' => $id_mobil]);

                echo "<script>
                        alert('Booking Berhasil Diajukan! Kode Booking Anda: $kode_booking');
                        window.location.href = 'index.php?page=histori';
                      </script>";
                exit;
            }

        } catch (Exception $e) {
            echo "<script>
                    alert('Gagal Memproses Transaksi: " . $e->getMessage() . "');
                    window.location.href = 'index.php?page=sewa&id_mobil=" . $id_mobil . "';
                  </script>";
            exit;
        }
    }
}