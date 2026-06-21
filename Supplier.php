<?php
require_once __DIR__ . '/../config/Database.php';

class Supplier {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->conn; 
    }

    public function tampil() {
        return $this->conn->query(
            "SELECT * FROM supplier ORDER BY nama_supplier ASC"
        );
    }

    public function getById($id) {
        $id = (int)$id;
        $res = $this->conn->query(
            "SELECT * FROM supplier WHERE id_supplier = $id"
        );
        return $res->fetch_assoc();
    }
}