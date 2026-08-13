<?php
// includes/horarios.php
// Funciones relacionadas a los horarios de atención

function get_horarios(): array
{
    global $conexion;

    $resultado = mysqli_query(
        $conexion,
        "SELECT id, dia, hora_apertura, hora_cierre, cerrado
        FROM horarios
        ORDER BY id ASC"
    );

    if (!$resultado) {
        throw new RuntimeException("Error al obtener los horarios.");
    }

    $horarios = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $horarios[] = $fila;
    }

    return $horarios;
}