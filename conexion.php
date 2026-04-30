<?php
// conexion.php — Conexión a la base de datos
// Usamos los mismos datos que ya tenías, solo se corrigió el archivo

$ubicacion = "127.0.0.1";
$usuario   = "root";
$clave     = "";
$base      = "boxinc";
$port      = "3307";

// Creamos la conexión con mysqli
$BD = new mysqli($ubicacion, $usuario, $clave, $base, $port);

// Si hay error, mostramos el mensaje y paramos
if ($BD->connect_error) {
    die("Error de conexión: " . $BD->connect_error);
}

// Configuramos el charset para que los tildes funcionen bien
$BD->set_charset("utf8mb4");
?>
