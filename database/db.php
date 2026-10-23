<?php

function get_db_connection(): mysqli
{
    $conexion = new mysqli("localhost", "root", "", "proyecto_web");

    if ($conexion->connect_error) {
        throw new RuntimeException("Error de conexión: " . $conexion->connect_error);
    }

    // Importante: sin esto los acentos (Sábado, Miércoles, ñ) llegan rotos a MySQL
    $conexion->set_charset('utf8mb4');

    return $conexion;
}