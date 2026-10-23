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

function get_all_cortes(): array
{
    global $conexion;

    $resultado = mysqli_query(
        $conexion,
        "SELECT id, nombre, categoria, descripcion, precio, media_path, media_tipo, creado_en
         FROM cortes
         ORDER BY categoria ASC, nombre ASC"
    );

    if (!$resultado) {
        throw new RuntimeException("Error al obtener los cortes.");
    }

    $cortes = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $cortes[] = $fila;
    }

    return $cortes;
}

function create_corte(string $nombre, string $categoria, string $descripcion, float $precio, ?string $mediaPath, ?string $mediaTipo): bool
{
    global $conexion;

    $stmt = mysqli_prepare(
        $conexion,
        "INSERT INTO cortes (nombre, categoria, descripcion, precio, media_path, media_tipo)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new RuntimeException("Error al preparar la creación del corte.");
    }

    mysqli_stmt_bind_param($stmt, "sssdss", $nombre, $categoria, $descripcion, $precio, $mediaPath, $mediaTipo);
    return mysqli_stmt_execute($stmt);
}

function update_corte_precio(int $id, float $precio): bool
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "UPDATE cortes SET precio = ? WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la actualización del precio.");
    }

    mysqli_stmt_bind_param($stmt, "di", $precio, $id);
    return mysqli_stmt_execute($stmt);
}

function delete_corte(int $id): bool
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "DELETE FROM cortes WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la eliminación del corte.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}