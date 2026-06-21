<?php
class Transaksi {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function simpanTransaksi($data) {
        $sql = "
            INSERT INTO transaksi 
            (id_transaksi, user, tanggal, metode_pembayaran, bank, alamat, ongkir, total_bayar)
            VALUES (
                '{$data['id_transaksi']}',
                '{$data['user']}',
                '{$data['tanggal']}',
                '{$data['metode']}',
                '{$data['bank']}',
                '{$data['alamat']}',
                '{$data['ongkir']}',
                '{$data['total']}'
            )
        ";
        return mysqli_query($this->conn, $sql);
    }

    public function simpanDetail($id_transaksi, $cart) {
        foreach ($cart as $item) {
            $id  = $item['id'];
            $nama = $item['nama'];
            $warna = $item['warna'];
            $qty = $item['qty'];
            $harga = $item['harga'];
            $subtotal = $qty * $harga;

            mysqli_query($this->conn, "
                INSERT INTO transaksi_detail
                (id_transaksi, id_barang, nama_barang, warna, qty, harga, subtotal)
                VALUES
                ('$id_transaksi', '$id', '$nama', '$warna', '$qty', '$harga', '$subtotal')
            ");
        }
    }

    public function prosesBarangKeluar($cart, $user, $tanggal) {
        foreach ($cart as $item) {
            $id  = $item['id'];
            $qty = $item['qty'];

            mysqli_query($this->conn, "
                INSERT INTO barang_keluar (id_barang, tanggal, penerima, quantity)
                VALUES ('$id', '$tanggal', '$user', '$qty')
            ");

            mysqli_query($this->conn, "
                UPDATE barang SET stok = stok - $qty WHERE id='$id'
            ");
        }
    }
}
