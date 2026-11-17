<?php
// config/database.php

class Database {
    private $host = "localhost";
    private $db_name = "db_kriptografi_uts";
    private $username = "root";
    private $password = ""; // Kosongkan jika menggunakan Laragon/XAMPP default
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Koneksi database gagal: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>