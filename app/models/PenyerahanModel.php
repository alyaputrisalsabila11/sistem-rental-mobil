<?php

require_once __DIR__ . '/../../config/database.php';

class PenyerahanModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getBookingConfirmed()
    {
        $sql = "SELECT
                b.*,
                p.nama_lengkap,
                p.no_telp,
                m.merk_mobil,
                m.plat_nomor

            FROM booking b

            JOIN pelanggan p
                ON p.id = b.id_pelanggan

            JOIN mobil m
                ON m.id_mobil = b.id_mobil

            WHERE b.status_booking='Confirmed'

            ORDER BY b.id_booking DESC";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingById($id)
    {
        $sql = "SELECT
                b.*,
                p.*,
                m.*

            FROM booking b

            JOIN pelanggan p
                ON p.id = b.id_pelanggan

            JOIN mobil m
                ON m.id_mobil = b.id_mobil

            WHERE b.id_booking=?";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function simpanPenyerahan($data)
    {

        try {

            $this->db->beginTransaction();

            $sql = "INSERT INTO penyerahan(

                id_booking,

                id_karyawan,

                id_lokasi,

                tgl_penyerahan,

                jam_penyerahan,

                km_awal,

                bbm_awal,

                kondisi_mobil,

                checklist,

                catatan,

                foto_kondisi,

                total_biaya_awal,

                status_sewa

            )

            VALUES(

                :id_booking,

                :id_karyawan,

                :id_lokasi,

                :tgl_penyerahan,

                :jam_penyerahan,

                :km_awal,

                :bbm_awal,

                :kondisi_mobil,

                :checklist,

                :catatan,

                :foto_kondisi,

                :total_biaya_awal,

                'Ongoing'

            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_booking' => $data['id_booking'],
                ':id_karyawan' => $data['id_karyawan'],
                ':id_lokasi' => $data['id_lokasi'],
                ':tgl_penyerahan' => $data['tgl_penyerahan'],
                ':jam_penyerahan' => $data['jam_penyerahan'],
                ':km_awal' => $data['km_awal'],
                ':bbm_awal' => $data['bbm_awal'],
                ':kondisi_mobil' => $data['kondisi_mobil'],
                ':checklist' => $data['checklist'],
                ':catatan' => $data['catatan'],
                ':foto_kondisi' => null,
                ':total_biaya_awal' => $data['total_biaya_awal']
            ]);

            // UPDATE STATUS BOOKING
            $sqlBooking = "
            UPDATE booking
            SET status_booking='Ongoing'
            WHERE id_booking=:id
            ";

            $stmtBooking = $this->db->prepare($sqlBooking);

            $stmtBooking->execute([
                ':id' => $data['id_booking']
            ]);

            // ambil id_mobil dari booking
            $sqlCariMobil = "
            SELECT id_mobil
            FROM booking
            WHERE id_booking = :id_booking
            ";

            $stmtCariMobil = $this->db->prepare($sqlCariMobil);

            $stmtCariMobil->execute([
                ':id_booking' => $data['id_booking']
            ]);

            $booking = $stmtCariMobil->fetch(PDO::FETCH_ASSOC);

            $idMobil = $booking['id_mobil'];

            // UPDATE STATUS MOBIL
            $sqlMobil = "
            UPDATE mobil
            SET status_mobil='Disewa'
            WHERE id_mobil=:id
            ";

            $stmtMobil = $this->db->prepare($sqlMobil);

            $stmtMobil->execute([
                ':id' => $idMobil
            ]);

            $this->db->commit();

            return true;
        } catch (Exception $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
