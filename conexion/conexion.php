<?php
class Conexion
{
    private $host = "localhost";
    private $user = "root"; // Cambia si tu usuario es otro
    private $password = ""; // Cambia si tu contraseña es distinta
    private $database = "car_project";

    public function conectar()
    {
        $conn = new mysqli($this->host, $this->user, $this->password, $this->database);
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }
        return $conn;
    }
}
