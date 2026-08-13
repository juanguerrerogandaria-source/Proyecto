<?php
// includes/ofertas.php
// Funciones relacionadas a las ofertas y promociones

function get_ofertas(): array
{
    global $conexion;

    $resultado = mysqli_query(
        $conexion,
        "SELECT id, titulo, descripcion, descuento_porcentaje, fecha_inicio, fecha_fin, activa
        FROM ofertas
        ORDER BY fecha_inicio DESC"
    );

    if (!$resultado) {
        throw new RuntimeException("Error al obtener las ofertas.");
    }

    $ofertas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ofertas[] = $fila;
    }

    return $ofertas;
}