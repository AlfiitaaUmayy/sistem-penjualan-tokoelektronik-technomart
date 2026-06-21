<?php
require_once __DIR__ . '/../config/Database.php';

class User
{
    public $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->conn; 
    }

    public function login($username, $password)
{
    $username = mysqli_real_escape_string(
        $this->conn,
        $username
    );

    $query = $this->conn->query(
        "SELECT * FROM users
         WHERE username='$username'
         LIMIT 1"
    );

    if ($query && $query->num_rows > 0) {

        $user = $query->fetch_assoc();

        // PASSWORD HASH BARU
        if (password_verify($password, $user['password'])) {
            return $user;
        }

        // BACKUP UNTUK USER LAMA YANG MASIH PLAINTEXT
        if ($password === $user['password']) {
            return $user;
        }
    }

    return false;
}

    public function getProfile($id)
    {
        $id = (int)$id;
        return mysqli_fetch_assoc(
            mysqli_query($this->conn, "SELECT * FROM users WHERE id=$id")
        );
    }

    public function getAlamat($id)
    {
        $id = (int)$id;
        return mysqli_fetch_assoc(
            mysqli_query($this->conn, "SELECT * FROM alamat_user WHERE user_id=$id")
        );
    }

    public function simpanAlamat($id, $alamat, $kota, $provinsi)
    {
        $id = (int)$id;
        $alamat   = mysqli_real_escape_string($this->conn, $alamat);
        $kota     = mysqli_real_escape_string($this->conn, $kota);
        $provinsi = mysqli_real_escape_string($this->conn, $provinsi);

        $cek = mysqli_query(
            $this->conn,
            "SELECT * FROM alamat_user WHERE user_id=$id"
        );

        if (mysqli_num_rows($cek) > 0) {
            // UPDATE
            mysqli_query($this->conn, "
                UPDATE alamat_user
                SET alamat='$alamat',
                    kota='$kota',
                    provinsi='$provinsi'
                WHERE user_id=$id
            ");
        } else {
            // INSERT
            mysqli_query($this->conn, "
                INSERT INTO alamat_user (user_id, alamat, kota, provinsi)
                VALUES ($id, '$alamat', '$kota', '$provinsi')
            ");
        }
    }
    public function updateFoto($user_id, $foto)
    {
    $stmt = $this->conn->prepare("
        UPDATE users 
        SET foto = ?
        WHERE id = ?
    ");

    $stmt->bind_param("si", $foto, $user_id);
    return $stmt->execute();
    }
}
