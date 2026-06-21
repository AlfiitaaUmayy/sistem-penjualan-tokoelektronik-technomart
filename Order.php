<?php

require_once '../config/Database.php';

class Order {

    private $pdo;
    private $conn;

    public function __construct() {
        $this->pdo = new PDO(
            "mysql:host=localhost;dbname=stock_elektronik",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $db = new Database();
        $this->conn = $db->conn;
    }

    public function createOrder($userId, $total, $metode, $alamat, $ongkir) {
        $stmt = $this->pdo->prepare("
            INSERT INTO orders 
            (user_id, total, metode, alamat_kirim, ongkir, status)
            VALUES (?, ?, ?, ?, ?, 'Menunggu')
        ");
        $stmt->execute([$userId, $total, $metode, $alamat, $ongkir]);
        return $this->pdo->lastInsertId();
    }

    public function addItem($orderId, $item) {
        $subtotal = $item['harga'] * $item['qty'];

        $stmt = $this->pdo->prepare("
            INSERT INTO order_items
            (order_id, barang, nama_barang, harga, qty, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $orderId,
            $item['barang_id'],
            $item['nama'],
            $item['harga'],
            $item['qty'],
            $subtotal
        ]);
    }

    public function getOrdersByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM orders
            WHERE user_id = ?
            ORDER BY tanggal DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderByIdOld($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM orders
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ===== DETAIL TRANSAKSI ===== */
    public function getOrderById($idTransaksi) {
        $stmt = $this->conn->prepare("
            SELECT 
                id_transaksi,
                user,
                tanggal,
                metode_pembayaran,
                bank,
                alamat,
                ongkir,
                total_bayar
            FROM transaksi
            WHERE id_transaksi = ?
        ");
        $stmt->bind_param("i", $idTransaksi);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /* ===== ITEM TRANSAKSI ===== */
    public function getItemsByOrder($idTransaksi) {
        $stmt = $this->conn->prepare("
            SELECT 
                td.nama_barang,
                td.warna,
                td.qty,
                td.harga,
                td.subtotal,
                b.foto
            FROM transaksi_detail td
            JOIN barang b ON b.id = td.id_barang
            WHERE td.id_transaksi = ?
        ");
        $stmt->bind_param("i", $idTransaksi);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* ================= ITEM PERTAMA (UNTUK RIWAYAT) ================= */
    public function getFirstItem($orderId) {
        $stmt = $this->db->prepare("
            SELECT 
                oi.qty,
                oi.harga,
                b.nama_barang,
                b.foto
            FROM order_items oi
            JOIN barang b ON oi.barang = b.id
            WHERE oi.order_id = ?
            LIMIT 1
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ================= AUTO STATUS DIPROSES ================= */
    public function autoProcessOrder($userId) {
        $stmt = $this->db->prepare("
            UPDATE orders
            SET status = 'Diproses'
            WHERE user_id = ?
              AND status = 'Menunggu'
              AND TIMESTAMPDIFF(MINUTE, tanggal, NOW()) >= 2
        ");
        $stmt->execute([$userId]);
    }
}