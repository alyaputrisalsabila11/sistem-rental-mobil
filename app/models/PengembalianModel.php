<?php

require_once __DIR__ . '/../../config/database.php';

class PengembalianModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    public function getPenyerahanOngoing()
    {
        $sql = "
        SELECT
            p.*,
            b.kode_booking,
            b.id_mobil,
            pl.nama_lengkap,
            m.merk_mobil,
            m.plat_nomor
        FROM penyerahan p
        INNER JOIN booking b
            ON p.id_booking = b.id_booking
        INNER JOIN pelanggan pl
            ON b.id_pelanggan = pl.id
        INNER JOIN mobil m
            ON b.id_mobil = m.id_mobil
        WHERE p.status_sewa='ongoing'
        ORDER BY p.id_penyerahan DESC
    ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPenyerahanById($id)
    {
        $sql = "
        SELECT
            p.*,
            b.kode_booking,
            b.id_booking,
            b.id_mobil,
            b.total_harga,
            pl.nama_lengkap,
            m.merk_mobil,
            m.plat_nomor
        FROM penyerahan p
        INNER JOIN booking b
            ON p.id_booking = b.id_booking
        INNER JOIN pelanggan pl
            ON b.id_pelanggan = pl.id
        INNER JOIN mobil m
            ON b.id_mobil = m.id_mobil
        WHERE p.id_penyerahan = :id
        LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function simpanPengembalian($data)
    {
        try {
            $this->db->beginTransaction();
            $sql = "
            INSERT INTO pengembalian
            (
                id_penyerahan,
                tgl_dikembalikan,
                jam_dikembalikan,
                km_akhir,
                bbm_akhir,
                kondisi_mobil,
                checklist,
                catatan,
                foto_kondisi,
                biaya_kerusakan,
                denda_telat
            )
            VALUES
            (
                :id_penyerahan,
                :tgl_dikembalikan,
                :jam_dikembalikan,
                :km_akhir,
                :bbm_akhir,
                :kondisi_mobil,
                :checklist,
                :catatan,
                :foto_kondisi,
                :biaya_kerusakan,
                :denda_telat
            )";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([

                ':id_penyerahan'    => $data['id_penyerahan'],
                ':tgl_dikembalikan' => $data['tgl_dikembalikan'],
                ':jam_dikembalikan' => $data['jam_dikembalikan'],
                ':km_akhir'         => $data['km_akhir'],
                ':bbm_akhir'        => $data['bbm_akhir'],
                ':kondisi_mobil'    => $data['kondisi_mobil'],
                ':checklist'        => $data['checklist'],
                ':catatan'          => $data['catatan'],
                ':foto_kondisi'     => $data['foto_kondisi'],
                ':biaya_kerusakan'  => $data['biaya_kerusakan'],
                ':denda_telat'      => $data['denda_telat']

            ]);
            $sqlPenyerahan = "
            UPDATE penyerahan
            SET status_sewa='complete'
            WHERE id_penyerahan=:id
            ";
            $stmtPenyerahan = $this->db->prepare($sqlPenyerahan);
            $stmtPenyerahan->execute([
                ':id' => $data['id_penyerahan']
            ]);
            $sqlBooking = "
            SELECT id_booking
            FROM penyerahan
            WHERE id_penyerahan=:id
            ";
            $stmtBooking = $this->db->prepare($sqlBooking);
            $stmtBooking->execute([
                ':id' => $data['id_penyerahan']
            ]);
            $booking = $stmtBooking->fetch(PDO::FETCH_ASSOC);
            $idBooking = $booking['id_booking'];
            $sqlUpdateBooking = "
            UPDATE booking
            SET status_booking='Completed'
            WHERE id_booking=:id
            ";
            $stmtUpdateBooking = $this->db->prepare($sqlUpdateBooking);
            $stmtUpdateBooking->execute([
                ':id' => $idBooking
            ]);
            $sqlMobil = "
            SELECT id_mobil
            FROM booking
            WHERE id_booking=:id
            ";
            $stmtMobil = $this->db->prepare($sqlMobil);
            $stmtMobil->execute([
                ':id' => $idBooking
            ]);
            $mobil = $stmtMobil->fetch(PDO::FETCH_ASSOC);
            $idMobil = $mobil['id_mobil'];
            $sqlStatusMobil = "
            UPDATE mobil
            SET status_mobil='Tersedia'
            WHERE id_mobil=:id
            ";
            $stmtStatusMobil = $this->db->prepare($sqlStatusMobil);
            $stmtStatusMobil->execute([
                ':id' => $idMobil
            ]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
