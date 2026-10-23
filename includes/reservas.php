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
        $horas[] = substr($fila['hora'], 0, 5);
    }
    return $horas;
}

function crear_reserva_flow(
    string $codigo,
    string $nombreCliente,
    string $emailCliente,
    ?string $telefonoCliente,
    int $corteId,
    string $fecha,
    string $hora,
    ?string $notas,
    string $metodoPago,
    float $monto
): bool {
    global $conexion;

    $check = mysqli_prepare(
        $conexion,
        "SELECT id FROM reservas WHERE fecha = ? AND hora = ? AND estado != 'cancelada' LIMIT 1"
    );
    mysqli_stmt_bind_param($check, "ss", $fecha, $hora);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        return false;
    }

    $stmt = mysqli_prepare(
        $conexion,
        "INSERT INTO reservas
            (codigo, nombre_cliente, email_cliente, telefono_cliente, corte_id, fecha, hora, notas, metodo_pago, monto, estado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')"
    );

    if (!$stmt) {
        throw new RuntimeException("Error al preparar la creación de la reserva.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssssissssd",
        $codigo, $nombreCliente, $emailCliente, $telefonoCliente,
        $corteId, $fecha, $hora, $notas, $metodoPago, $monto
    );

    return mysqli_stmt_execute($stmt);
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

function get_reserva_por_id(int $id): ?array
{
    global $conexion;
    $stmt = mysqli_prepare(
        $conexion,
        "SELECT r.id, r.codigo, r.nombre_cliente, r.email_cliente, r.fecha, r.hora,
                r.metodo_pago, r.monto, r.estado,
                c.nombre AS corte_nombre, c.categoria AS corte_categoria
         FROM reservas r
         LEFT JOIN cortes c ON c.id = r.corte_id
         WHERE r.id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $fila ?: null;
}

// Código único: TB- + año + mes + día + hora + letra del día + 3 números al azar
// Ej.: TB-2026100520L483
function generar_codigo_reserva(string $fecha, string $hora): string
{
    global $conexion;

    $dt = new DateTime($fecha . ' ' . $hora);

    // N: 1 = lunes ... 7 = domingo (X para miércoles, así no se confunde con martes)
    $letras = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
    $base   = 'TB-' . $dt->format('Ymd') . $dt->format('H') . $letras[(int) $dt->format('N')];

    $existe = mysqli_prepare($conexion, "SELECT 1 FROM reservas WHERE codigo = ? LIMIT 1");

    for ($i = 0; $i < 20; $i++) {
        $codigo = $base . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);

        mysqli_stmt_bind_param($existe, "s", $codigo);
        mysqli_stmt_execute($existe);
        mysqli_stmt_store_result($existe);

        if (mysqli_stmt_num_rows($existe) === 0) {
            return $codigo;
        }
    }

    // Casi imposible: 20 choques seguidos. Agregamos segundos para desempatar.
    return $base . date('s') . random_int(0, 9);
}