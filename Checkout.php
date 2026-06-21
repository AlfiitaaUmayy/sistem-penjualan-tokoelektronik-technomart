<?php
class Checkout {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function simpanCheckout($user, $alamat, $metode, $total, $tanggal) {
        mysqli_query($this->conn, "
            INSERT INTO checkout (username, alamat, metode, total, tanggal)
            VALUES ('$user', '$alamat', '$metode', '$total', '$tanggal')
        ");
    }
}