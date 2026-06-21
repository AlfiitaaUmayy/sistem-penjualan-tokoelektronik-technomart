<?php
require_once __DIR__ . '/../config/Database.php';

class Barang
{
    private $conn;

    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "stock_elektronik");
    }

    public function getConn() {
        return $this->conn;
    }

    // ================= TAMPIL =================
    public function tampil()
    {
        return $this->conn->query(
            "SELECT * FROM barang ORDER BY id DESC"
        );
    }

    // ================= TAMBAH =================
    // $warna = array warna
    public function tambah($foto, $nama, $kategori, $harga, $stok, $deskripsi, $warna = [])
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO barang (foto, nama_barang, kategori, harga, stok, deskripsi)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssdis", $foto, $nama, $kategori, $harga, $stok, $deskripsi);
        $stmt->execute();

        $barang_id = $this->conn->insert_id;

        // simpan warna
        foreach ($warna as $w) {
            $this->tambahWarna($barang_id, $w);
        }

        return $barang_id;
    }

    // ================= AMBIL BY ID =================
    public function getById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM barang WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ================= EDIT =================
    public function edit($id, $foto, $nama, $kategori, $harga, $stok, $deskripsi, $warna = [])
    {
        $stmt = $this->conn->prepare(
            "UPDATE barang SET
                foto = ?,
                nama_barang = ?,
                kategori = ?,
                harga = ?,
                stok = ?,
                deskripsi = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            "sssdisi",
            $foto,
            $nama,
            $kategori,
            $harga,
            $stok,
            $deskripsi,
            $id
        );
        $stmt->execute();

        // reset warna
        $this->hapusWarnaByBarang($id);

        // simpan ulang warna
        foreach ($warna as $w) {
            $this->tambahWarna($id, $w);
        }

        return true;
    }

    // ================= HAPUS =================
    public function hapus($id)
    {
        $this->hapusWarnaByBarang($id);

        $stmt = $this->conn->prepare(
            "DELETE FROM barang WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // ================= WARNA =================
    public function tambahWarna($barang_id, $warna)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO barang_warna (barang_id, warna)
             VALUES (?, ?)"
        );
        $stmt->bind_param("is", $barang_id, $warna);
        return $stmt->execute();
    }

    public function getWarnaByBarang($barang_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT warna FROM barang_warna WHERE barang_id = ?"
        );
        $stmt->bind_param("i", $barang_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function hapusWarnaByBarang($barang_id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM barang_warna WHERE barang_id = ?"
        );
        $stmt->bind_param("i", $barang_id);
        return $stmt->execute();
    }

    // kurangi stok
    public function kurangiStok($id, $qty)
    {
        $stmt = $this->conn->prepare(
            "UPDATE barang SET stok = stok - ? WHERE id = ?"
        );
        $stmt->bind_param("ii", $qty, $id);
        return $stmt->execute();
    }

    // ================= SEARCH BARANG =================
public function cari($keyword)
{
    return $this->conn->query("
        SELECT * FROM barang
        WHERE 
            nama_barang LIKE '%$keyword%' OR
            kategori LIKE '%$keyword%' OR
            deskripsi LIKE '%$keyword%'
        ORDER BY nama_barang ASC
    ");
}

// ================= FILTER KATEGORI =================
public function filterKategori($kategori)
{
    return $this->conn->query("
        SELECT * FROM barang
        WHERE kategori = '$kategori'
        ORDER BY nama_barang ASC
    ");
}

// ================= FILTER + PAGINATION =================
public function filter($keyword, $kategori, $limit, $offset)
{
    $sql = "SELECT * FROM barang WHERE 1";

    if ($keyword != '') {
        $sql .= " AND (nama_barang LIKE '%$keyword%' 
                  OR deskripsi LIKE '%$keyword%')";
    }

    if ($kategori != '') {
        $sql .= " AND kategori = '$kategori'";
    }

    $sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";

    return $this->conn->query($sql);
}

// ================= HITUNG TOTAL DATA =================
public function count($keyword, $kategori)
{
    $sql = "SELECT COUNT(*) AS total FROM barang WHERE 1";

    if ($keyword != '') {
        $sql .= " AND (nama_barang LIKE '%$keyword%' 
                  OR deskripsi LIKE '%$keyword%')";
    }

    if ($kategori != '') {
        $sql .= " AND kategori = '$kategori'";
    }

    $result = $this->conn->query($sql)->fetch_assoc();
    return $result['total'];
}

// ================= LIST KATEGORI =================
public function kategori()
{
    return $this->conn->query(
        "SELECT DISTINCT kategori FROM barang ORDER BY kategori ASC"
    );
}

public function tambahDenganDeskripsi($foto, $nama, $kategori, $harga, $stok, $deskripsi)
{
    $stmt = $this->conn->prepare(
        "INSERT INTO barang 
        (foto, nama_barang, kategori, harga, stok, deskripsi)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "sssdis",
        $foto,
        $nama,
        $kategori,
        $harga,
        $stok,
        $deskripsi
    );

    $stmt->execute();

    return $this->conn->insert_id;
}

public function getLimit($limit = 8){
    return $this->conn->query("
        SELECT * FROM barang
        ORDER BY id DESC
        LIMIT $limit
    ");
}

}
