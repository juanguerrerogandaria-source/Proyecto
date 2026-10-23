<?php
$config = require __DIR__ . '/../config.php';

$conexion = mysqli_connect(
    $config['DB_HOST'],
    $config['DB_USER'],
    $config['DB_PASS'],
    $config['DB_NAME']
);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4"); // <-- agregar esta línea