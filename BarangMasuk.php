<?php
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../config/Database.php';

class BarangMasuk
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getSupplier()
{
    return $this->db->conn->query("
        SELECT *
        FROM supplier
        ORDER BY nama_supplier ASC
    ");
}

    // ================= TAMPIL =================
    public function tampil()
{
    return $this->db->conn->query("
        SELECT
            bm.*,
            b.nama_barang,
            s.nama_supplier,
            s.kontak,
            s.alamat
        FROM barang_masuk bm
        LEFT JOIN barang b
            ON bm.id_barang = b.id
        LEFT JOIN supplier s
            ON bm.id_supplier = s.id_supplier
        ORDER BY bm.tanggal DESC
    ");
}

    // ================= GET ID BARANG BY NAMA =================
    public function getIdBarangByNama($nama_barang)
    {
        $stmt = $this->db->conn->prepare(
            "SELECT id FROM barang WHERE nama_barang = ?"
        );
        $stmt->bind_param("s", $nama_barang);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

  // ================= TAMBAH =================
public function tambah(
    $tanggal,
    $id_supplier,
    $id_barang,
    $keterangan,
    $harga_beli,
    $quantity
)
{
    $tanggal = date('Y-m-d H:i:s');
    // Bersihkan format rupiah
    $harga_beli = str_replace('.', '', $harga_beli);

    $sql = "
        INSERT INTO barang_masuk
        (
            tanggal,
            id_supplier,
            id_barang,
            keterangan,
            harga_beli,
            quantity
        )
        VALUES
        (
            '$tanggal',
            '$id_supplier',
            '$id_barang',
            '$keterangan',
            '$harga_beli',
            '$quantity'
        )
    ";

    if (!$this->db->conn->query($sql)) {
        die('Error Insert Barang Masuk: ' . $this->db->conn->error);
    }

    // Update stok barang
    $sqlStok = "
        UPDATE barang
        SET stok = stok + $quantity
        WHERE id = $id_barang
    "; 

    if (!$this->db->conn->query($sqlStok)) {
        die('Error Update Stok: ' . $this->db->conn->error);
    }

    return true;
}

    // ================= CARI =================
    public function cari($keyword)
{
    return $this->db->conn->query("
        SELECT
            bm.*,
            b.nama_barang,
            s.nama_supplier,
            s.kontak,
            s.alamat
        FROM barang_masuk bm
        LEFT JOIN barang b
            ON bm.id_barang = b.id
        LEFT JOIN supplier s
            ON bm.id_supplier = s.id_supplier
        WHERE
            b.nama_barang LIKE '%$keyword%'
            OR bm.keterangan LIKE '%$keyword%'
            OR s.nama_supplier LIKE '%$keyword%'
        ORDER BY bm.tanggal DESC
    ");
}

    // ================= GET BY ID =================
    public function getById($id)
{
    return $this->db->conn->query("
        SELECT
            bm.*,
            b.nama_barang,
            s.nama_supplier,
            s.kontak,
            s.alamat
        FROM barang_masuk bm
        LEFT JOIN barang b
            ON bm.id_barang = b.id
        LEFT JOIN supplier s
            ON bm.id_supplier = s.id_supplier
        WHERE bm.id = $id
    ")->fetch_assoc();
}

    // ================= UPDATE =================
    // ❌ id_supplier DIHAPUS
    public function update(
    $id,
    $tanggal,
    $id_supplier,
    $id_barang,
    $keterangan,
    $harga_beli,
    $quantity
)
{
    $this->db->conn->query("
        UPDATE barang_masuk SET
            tanggal='$tanggal',
            id_supplier='$id_supplier',
            id_barang='$id_barang',
            keterangan='$keterangan',
            harga_beli='$harga_beli',
            quantity='$quantity'
        WHERE id=$id
    ");
}

    public function totalPerBulan()
{
    return $this->db->conn->query("
        SELECT MONTH(tanggal) as bulan,
               SUM(quantity) as total
        FROM barang_masuk
        GROUP BY MONTH(tanggal)
    ");
}

// ================= HAPUS =================
public function hapus($id)
{
    $stmt = $this->db->conn->prepare("
        DELETE FROM barang_masuk
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);

    return $stmt->execute();
}

}