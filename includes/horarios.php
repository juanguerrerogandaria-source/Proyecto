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

function update_horario(int $id, ?string $apertura, ?string $cierre, bool $cerrado): bool
{
    global $conexion;

    $cerradoInt = $cerrado ? 1 : 0;

    $stmt = mysqli_prepare(
        $conexion,
        "UPDATE horarios SET hora_apertura = ?, hora_cierre = ?, cerrado = ? WHERE id = ?"
    );

    if (!$stmt) {
        throw new RuntimeException("Error al preparar la actualización del horario.");
    }

    mysqli_stmt_bind_param($stmt, "ssii", $apertura, $cierre, $cerradoInt, $id);
    return mysqli_stmt_execute($stmt);
}

function get_horario_para_fecha(string $fecha): ?array
{
    global $conexion;

    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $diaSemana = $dias[(int) date('w', strtotime($fecha))];

    $stmt = mysqli_prepare($conexion, "SELECT * FROM horarios WHERE dia = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la consulta de horario.");
    }

    mysqli_stmt_bind_param($stmt, "s", $diaSemana);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($resultado);

    return $fila ?: null;
}

function generar_turnos_para_fecha(string $fecha): array
{
    $horario = get_horario_para_fecha($fecha);

    if (!$horario || (int) $horario['cerrado'] === 1 || empty($horario['hora_apertura']) || empty($horario['hora_cierre'])) {
        return [];
    }

    $intervaloMinutos = 30; // duración de cada turno

    $inicio = strtotime($fecha . ' ' . $horario['hora_apertura']);
    $fin    = strtotime($fecha . ' ' . $horario['hora_cierre']);

    $turnos = [];
    for ($t = $inicio; $t < $fin; $t += $intervaloMinutos * 60) {
        $turnos[] = date('H:i', $t);
    }

    return $turnos;
}