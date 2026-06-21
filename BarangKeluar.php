<?php
require_once __DIR__ . '/../config/Database.php';

class BarangKeluar
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->conn;
    }

    // ================= TAMPIL =================
    public function tampil()
    {
        return $this->conn->query(
            "SELECT * FROM barang_keluar ORDER BY tanggal DESC"
        );
    }

    // ================= TAMBAH =================
    public function tambah($id_barang, $tanggal, $penerima, $quantity)
    {
        // 🔹 ambil nama barang
        $stmt = $this->conn->prepare(
            "SELECT nama_barang FROM barang WHERE id = ?"
        );
        if (!$stmt) return false;

        $stmt->bind_param("i", $id_barang);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$result) {
            return false; // 🔥 JANGAN throw → biar finalize yang handle
        }
        
        $nama_barang = $result['nama_barang'];

        // 🔹 insert barang keluar
        $stmt = $this->conn->prepare(
            "INSERT INTO barang_keluar
            (id_barang, nama_barang, tanggal, penerima, quantity)
            VALUES (?, ?, ?, ?, ?)"
        );
        if (!$stmt) return false;

        $stmt->bind_param(
            "isssi",
            $id_barang,
            $nama_barang,
            $tanggal,
            $penerima,
            $quantity
        );

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $stmt->close();
        return true; // 🔥 INI KUNCI NYAWA STOK
    }

    // ================= SEARCH =================
    public function cari($keyword)
    {
        $keyword = "%$keyword%";
        $stmt = $this->conn->prepare(
            "SELECT * FROM barang_keluar
             WHERE nama_barang LIKE ? OR penerima LIKE ?
             ORDER BY tanggal DESC"
        );
        $stmt->bind_param("ss", $keyword, $keyword);
        $stmt->execute();
        return $stmt->get_result();
    }

    // ================= GRAFIK =================
    public function totalPerBulan()
    {
        return $this->conn->query(
            "SELECT MONTH(tanggal) AS bulan, SUM(quantity) AS total
             FROM barang_keluar
             GROUP BY MONTH(tanggal)
             ORDER BY MONTH(tanggal)"
        );
    }
}