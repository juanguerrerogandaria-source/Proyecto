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

function create_oferta(string $titulo, string $descripcion, int $descuento, ?string $fechaInicio, ?string $fechaFin): bool
{
    global $conexion;

    $stmt = mysqli_prepare(
        $conexion,
        "INSERT INTO ofertas (titulo, descripcion, descuento_porcentaje, fecha_inicio, fecha_fin, activa)
         VALUES (?, ?, ?, ?, ?, 1)"
    );

    if (!$stmt) {
        throw new RuntimeException("Error al preparar la creación de la oferta.");
    }

    mysqli_stmt_bind_param($stmt, "ssiss", $titulo, $descripcion, $descuento, $fechaInicio, $fechaFin);
    return mysqli_stmt_execute($stmt);
}

function toggle_oferta(int $id, bool $activa): bool
{
    global $conexion;

    $activaInt = $activa ? 1 : 0;

    $stmt = mysqli_prepare($conexion, "UPDATE ofertas SET activa = ? WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la actualización de la oferta.");
    }

    mysqli_stmt_bind_param($stmt, "ii", $activaInt, $id);
    return mysqli_stmt_execute($stmt);
}

function delete_oferta(int $id): bool
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "DELETE FROM ofertas WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la eliminación de la oferta.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

function get_mejor_oferta_activa(): ?array
{
    global $conexion;

    $resultado = mysqli_query(
        $conexion,
        "SELECT id, titulo, descripcion, descuento_porcentaje, fecha_inicio, fecha_fin, activa
         FROM ofertas
         WHERE activa = 1
           AND (fecha_inicio IS NULL OR fecha_inicio <= CURDATE())
           AND (fecha_fin IS NULL OR fecha_fin >= CURDATE())
         ORDER BY descuento_porcentaje DESC
         LIMIT 1"
    );

    if (!$resultado) {
        throw new RuntimeException("Error al obtener la oferta activa.");
    }

    $oferta = mysqli_fetch_assoc($resultado);
    return $oferta ?: null;
}