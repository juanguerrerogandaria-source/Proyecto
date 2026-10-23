<?php
// includes/estadisticas.php
// Consultas para el panel de estadísticas (solo admin y super_admin).
// Los turnos "agendados" son los que no están cancelados. Los ingresos cuentan solo turnos confirmados.

require_once __DIR__ . '/barberos.php'; // nombres_dias_semana()

// Períodos disponibles en el selector
function estadisticas_periodos(): array
{
    return [
        'semana' => 'Esta semana',
        'mes'    => 'Este mes',
        '30dias' => 'Últimos 30 días',
        'todo'   => 'Todo el historial',
    ];
}

/**
 * Devuelve ['desde' => 'Y-m-d'|null, 'hasta' => 'Y-m-d'|null, 'anterior' => [desde, hasta]|null]
 * La semana va de lunes a domingo.
 */
function estadisticas_rango(string $periodo): array
{
    $hoy = strtotime(date('Y-m-d'));

    switch ($periodo) {
        case 'mes':
            $desde = date('Y-m-01', $hoy);
            $hasta = date('Y-m-t', $hoy);
            $antDesde = date('Y-m-01', strtotime($desde . ' -1 month'));
            $antHasta = date('Y-m-t', strtotime($antDesde));
            return ['desde' => $desde, 'hasta' => $hasta, 'anterior' => [$antDesde, $antHasta]];

        case '30dias':
            $desde = date('Y-m-d', strtotime('-29 days', $hoy));
            $hasta = date('Y-m-d', $hoy);
            return [
                'desde'    => $desde,
                'hasta'    => $hasta,
                'anterior' => [date('Y-m-d', strtotime('-59 days', $hoy)), date('Y-m-d', strtotime('-30 days', $hoy))],
            ];

        case 'todo':
            return ['desde' => null, 'hasta' => null, 'anterior' => null];

        case 'semana':
        default:
            $desde = date('Y-m-d', strtotime('monday this week', $hoy));
            $hasta = date('Y-m-d', strtotime('sunday this week', $hoy));
            return [
                'desde'    => $desde,
                'hasta'    => $hasta,
                'anterior' => [date('Y-m-d', strtotime('-7 days', strtotime($desde))), date('Y-m-d', strtotime('-7 days', strtotime($hasta)))],
            ];
    }
}

// Ejecuta una consulta que contiene {RANGO} (filtro opcional de fechas sobre r.fecha)
function estadisticas_filas(string $sql, ?string $desde, ?string $hasta): array
{
    global $conexion;

    $rango  = '';
    $tipos  = '';
    $params = [];

    if ($desde !== null) {
        $rango   .= ' AND r.fecha >= ?';
        $tipos   .= 's';
        $params[] = $desde;
    }
    if ($hasta !== null) {
        $rango   .= ' AND r.fecha <= ?';
        $tipos   .= 's';
        $params[] = $hasta;
    }

    $stmt = mysqli_prepare($conexion, str_replace('{RANGO}', $rango, $sql));
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la consulta de estadísticas.");
    }

    if ($params) {
        mysqli_stmt_bind_param($stmt, $tipos, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $filas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $filas[] = $fila;
    }

    return $filas;
}

function estadisticas_resumen(?string $desde, ?string $hasta): array
{
    $fila = estadisticas_filas(
        "SELECT
            COALESCE(SUM(r.estado != 'cancelada'), 0)  AS agendados,
            COALESCE(SUM(r.estado = 'confirmada'), 0)  AS confirmados,
            COALESCE(SUM(r.estado = 'pendiente'), 0)   AS pendientes,
            COALESCE(SUM(r.estado = 'cancelada'), 0)   AS cancelados,
            COALESCE(SUM(CASE WHEN r.estado = 'confirmada' THEN r.monto END), 0) AS ingresos,
            COALESCE(SUM(CASE WHEN r.estado = 'pendiente'  THEN r.monto END), 0) AS por_confirmar
         FROM reservas r
         WHERE 1 = 1 {RANGO}",
        $desde,
        $hasta
    )[0] ?? [];

    $res = [
        'agendados'     => (int) ($fila['agendados'] ?? 0),
        'confirmados'   => (int) ($fila['confirmados'] ?? 0),
        'pendientes'    => (int) ($fila['pendientes'] ?? 0),
        'cancelados'    => (int) ($fila['cancelados'] ?? 0),
        'ingresos'      => (float) ($fila['ingresos'] ?? 0),
        'por_confirmar' => (float) ($fila['por_confirmar'] ?? 0),
    ];

    $res['ticket_promedio'] = $res['confirmados'] > 0 ? $res['ingresos'] / $res['confirmados'] : 0.0;

    return $res;
}

// Turnos agendados por día de la semana: [0 => n, ..., 6 => n] (0 = domingo)
function estadisticas_por_dia_semana(?string $desde, ?string $hasta): array
{
    $filas = estadisticas_filas(
        "SELECT DAYOFWEEK(r.fecha) - 1 AS dia, COUNT(*) AS total
         FROM reservas r
         WHERE r.estado != 'cancelada' {RANGO}
         GROUP BY dia",
        $desde,
        $hasta
    );

    $dias = array_fill(0, 7, 0);
    foreach ($filas as $f) {
        $dias[(int) $f['dia']] = (int) $f['total'];
    }

    return $dias;
}

// ['09:00' => n, ...] ordenado por hora
function estadisticas_por_hora(?string $desde, ?string $hasta): array
{
    $filas = estadisticas_filas(
        "SELECT TIME_FORMAT(r.hora, '%H:%i') AS h, COUNT(*) AS total
         FROM reservas r
         WHERE r.estado != 'cancelada' {RANGO}
         GROUP BY h
         ORDER BY h ASC",
        $desde,
        $hasta
    );

    $horas = [];
    foreach ($filas as $f) {
        $horas[$f['h']] = (int) $f['total'];
    }

    return $horas;
}

function estadisticas_por_barbero(?string $desde, ?string $hasta): array
{
    return estadisticas_filas(
        "SELECT COALESCE(b.nombre, 'Sin asignar') AS nombre,
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN r.estado = 'confirmada' THEN r.monto END), 0) AS ingresos
         FROM reservas r
         LEFT JOIN barberos b ON b.id = r.barbero_id
         WHERE r.estado != 'cancelada' {RANGO}
         GROUP BY r.barbero_id, b.nombre
         ORDER BY total DESC, nombre ASC",
        $desde,
        $hasta
    );
}

function estadisticas_cortes_top(?string $desde, ?string $hasta, int $limite = 5): array
{
    $limite = max(1, $limite);

    return estadisticas_filas(
        "SELECT COALESCE(c.nombre, 'Corte eliminado') AS nombre, COUNT(*) AS total
         FROM reservas r
         LEFT JOIN cortes c ON c.id = r.corte_id
         WHERE r.estado != 'cancelada' {RANGO}
         GROUP BY r.corte_id, c.nombre
         ORDER BY total DESC, nombre ASC
         LIMIT $limite",
        $desde,
        $hasta
    );
}

function estadisticas_por_categoria(?string $desde, ?string $hasta): array
{
    return estadisticas_filas(
        "SELECT COALESCE(c.categoria, 'sin dato') AS nombre, COUNT(*) AS total
         FROM reservas r
         LEFT JOIN cortes c ON c.id = r.corte_id
         WHERE r.estado != 'cancelada' {RANGO}
         GROUP BY c.categoria
         ORDER BY total DESC",
        $desde,
        $hasta
    );
}

function estadisticas_por_metodo_pago(?string $desde, ?string $hasta): array
{
    return estadisticas_filas(
        "SELECT COALESCE(r.metodo_pago, 'sin dato') AS nombre, COUNT(*) AS total
         FROM reservas r
         WHERE r.estado != 'cancelada' {RANGO}
         GROUP BY r.metodo_pago
         ORDER BY total DESC",
        $desde,
        $hasta
    );
}

function estadisticas_clientes_frecuentes(?string $desde, ?string $hasta, int $limite = 5): array
{
    $limite = max(1, $limite);

    return estadisticas_filas(
        "SELECT MAX(r.nombre_cliente) AS nombre, COUNT(*) AS total
         FROM reservas r
         WHERE r.estado != 'cancelada' {RANGO}
         GROUP BY LOWER(r.email_cliente)
         ORDER BY total DESC, nombre ASC
         LIMIT $limite",
        $desde,
        $hasta
    );
}

// Ingresos confirmados y turnos de cada fecha del período, ordenados por fecha
function estadisticas_ingresos_por_fecha(?string $desde, ?string $hasta): array
{
    return estadisticas_filas(
        "SELECT r.fecha,
                COUNT(*) AS turnos,
                COALESCE(SUM(CASE WHEN r.estado = 'confirmada' THEN r.monto END), 0) AS ingresos
         FROM reservas r
         WHERE r.estado != 'cancelada' {RANGO}
         GROUP BY r.fecha
         ORDER BY r.fecha ASC",
        $desde,
        $hasta
    );
}
