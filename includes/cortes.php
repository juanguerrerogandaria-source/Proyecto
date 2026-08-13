<?php
// includes/cortes.php
// Funciones relacionadas a los cortes/servicios ofrecidos

function get_cortes_por_categoria(string $categoria): array
{
    global $conexion;

    $stmt = mysqli_prepare(
        $conexion,
        "SELECT id, nombre, descripcion, precio, media_tipo, media_path
        FROM cortes
        WHERE categoria = ?
        ORDER BY nombre ASC"
    );

    if (!$stmt) {
        throw new RuntimeException("Error al preparar la consulta de cortes.");
    }

    mysqli_stmt_bind_param($stmt, "s", $categoria);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $cortes = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $cortes[] = $fila;
    }

    return $cortes;
}