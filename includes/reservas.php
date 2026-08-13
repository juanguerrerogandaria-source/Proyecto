<?php
// includes/reservas.php
// Funciones relacionadas a las reservas de turnos

function obtener_horas_ocupadas(string $fecha): array
{
    global $conexion;

    $stmt = mysqli_prepare(
        $conexion,
        "SELECT hora FROM reservas WHERE fecha = ? AND estado != 'cancelada'"
    );

    if (!$stmt) {
        throw new RuntimeException("Error al preparar la consulta de horas ocupadas.");
    }

    mysqli_stmt_bind_param($stmt, "s", $fecha);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $horas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        // La columna es TIME, viene como "18:30:00" — la recortamos a "18:30"
        $horas[] = substr($fila['hora'], 0, 5);
    }

    return $horas;
}

function get_all_reservas(): array
{
    global $conexion;

    $resultado = mysqli_query(
        $conexion,
        "SELECT r.id, r.codigo, r.nombre_cliente, r.email_cliente, r.telefono_cliente,
                r.fecha, r.hora, r.notas, r.metodo_pago, r.monto, r.estado,
                c.nombre AS corte_nombre, c.categoria AS corte_categoria
         FROM reservas r
         LEFT JOIN cortes c ON c.id = r.corte_id
         ORDER BY r.fecha DESC, r.hora DESC"
    );

    if (!$resultado) {
        throw new RuntimeException("Error al obtener las reservas.");
    }

    $reservas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $reservas[] = $fila;
    }

    return $reservas;
}

function update_estado_reserva(int $id, string $estado): bool
{
    global $conexion;

    $estadosValidos = ['pendiente', 'confirmada', 'cancelada'];
    if (!in_array($estado, $estadosValidos, true)) {
        throw new RuntimeException("Estado inválido.");
    }

    $stmt = mysqli_prepare($conexion, "UPDATE reservas SET estado = ? WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la actualización de la reserva.");
    }

    mysqli_stmt_bind_param($stmt, "si", $estado, $id);
    return mysqli_stmt_execute($stmt);
}