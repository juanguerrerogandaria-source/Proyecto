<?php
// includes/barberos.php
// Barberos, horario semanal propio de cada uno y disponibilidad de turnos.

const TURNO_INTERVALO_MIN = 30;                 // misma duración de turno que usa includes/horarios.php
const BARBERO_FOTO_MAX    = 5 * 1024 * 1024;    // 5MB
const BARBERO_FOTO_MIMES  = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

// 0 = domingo ... 6 = sábado (igual que date('w'))
function nombres_dias_semana(): array
{
    return [
        0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
    ];
}

// ---------------------------------------------------------------
// Barberos
// ---------------------------------------------------------------

function get_barberos(bool $soloActivos = false): array
{
    global $conexion;

    $where = $soloActivos ? 'WHERE b.activo = 1' : '';

    $resultado = mysqli_query(
        $conexion,
        "SELECT b.id, b.nombre, b.foto, b.activo,
                (SELECT COUNT(*) FROM reservas r
                  WHERE r.barbero_id = b.id
                    AND r.fecha >= CURDATE()
                    AND r.estado != 'cancelada') AS proximos
         FROM barberos b
         $where
         ORDER BY b.activo DESC, b.nombre ASC"
    );

    if (!$resultado) {
        throw new RuntimeException("Error al obtener los barberos.");
    }

    $barberos = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $barberos[] = $fila;
    }

    return $barberos;
}

function get_barbero(int $id): ?array
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "SELECT id, nombre, foto, activo FROM barberos WHERE id = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la consulta del barbero.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $fila ?: null;
}

function contar_barberos_activos(): int
{
    global $conexion;

    $resultado = mysqli_query($conexion, "SELECT COUNT(*) FROM barberos WHERE activo = 1");
    if (!$resultado) {
        throw new RuntimeException("Error al contar los barberos.");
    }

    return (int) mysqli_fetch_row($resultado)[0];
}

// Devuelve el id del barbero nuevo, o false si no se pudo crear
function create_barbero(string $nombre, ?string $foto): int|false
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "INSERT INTO barberos (nombre, foto) VALUES (?, ?)");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la creación del barbero.");
    }

    mysqli_stmt_bind_param($stmt, "ss", $nombre, $foto);

    if (!mysqli_stmt_execute($stmt)) {
        return false;
    }

    return (int) mysqli_insert_id($conexion);
}

// Si $foto es null se conserva la foto actual
function update_barbero(int $id, string $nombre, ?string $foto): bool
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "UPDATE barberos SET nombre = ?, foto = COALESCE(?, foto) WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la actualización del barbero.");
    }

    mysqli_stmt_bind_param($stmt, "ssi", $nombre, $foto, $id);
    return mysqli_stmt_execute($stmt);
}

function set_barbero_activo(int $id, bool $activo): bool
{
    global $conexion;

    $valor = $activo ? 1 : 0;

    $stmt = mysqli_prepare($conexion, "UPDATE barberos SET activo = ? WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar el cambio de estado del barbero.");
    }

    mysqli_stmt_bind_param($stmt, "ii", $valor, $id);
    return mysqli_stmt_execute($stmt);
}

function inicial_barbero(string $nombre): string
{
    $nombre = trim($nombre);
    if ($nombre === '') {
        return '?';
    }

    return function_exists('mb_substr')
        ? mb_strtoupper(mb_substr($nombre, 0, 1, 'UTF-8'), 'UTF-8')
        : strtoupper($nombre[0]);
}

// ---------------------------------------------------------------
// Fotos
// ---------------------------------------------------------------

// Devuelve ['path' => 'uploads/barberos/xxx.jpg' | null, 'error' => string | null]
// Si no se eligió ningún archivo, path y error vienen en null.
function guardar_foto_barbero(array $archivo): array
{
    $codigo = $archivo['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($codigo === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if ($codigo !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Hubo un error al subir la foto.'];
    }

    if ($archivo['size'] > BARBERO_FOTO_MAX) {
        return ['path' => null, 'error' => 'La foto no puede superar los 5MB.'];
    }

    // Se verifica el contenido real del archivo, no solo la extensión
    $info = @getimagesize($archivo['tmp_name']);
    if ($info === false || !array_key_exists($info['mime'], BARBERO_FOTO_MIMES)) {
        return ['path' => null, 'error' => 'La foto tiene que ser JPG, PNG o WebP.'];
    }

    $carpeta = __DIR__ . '/../public/uploads/barberos/';
    if (!is_dir($carpeta) && !@mkdir($carpeta, 0755, true)) {
        return ['path' => null, 'error' => 'No se pudo crear la carpeta de fotos en el servidor.'];
    }

    $nombreArchivo = 'barbero_' . bin2hex(random_bytes(8)) . '.' . BARBERO_FOTO_MIMES[$info['mime']];

    if (!move_uploaded_file($archivo['tmp_name'], $carpeta . $nombreArchivo)) {
        return ['path' => null, 'error' => 'No se pudo guardar la foto en el servidor.'];
    }

    return ['path' => 'uploads/barberos/' . $nombreArchivo, 'error' => null];
}

function borrar_foto_barbero(?string $path): void
{
    if ($path === null || !str_starts_with($path, 'uploads/barberos/')) {
        return;
    }

    $archivo = __DIR__ . '/../public/uploads/barberos/' . basename($path);
    if (is_file($archivo)) {
        @unlink($archivo);
    }
}

// ---------------------------------------------------------------
// Horario semanal de cada barbero
// ---------------------------------------------------------------

// Copia el horario general del local (tabla `horarios`) al barbero. No pisa días que ya tenga cargados.
function copiar_horario_general_a_barbero(int $barberoId): void
{
    global $conexion;

    $barberoId = (int) $barberoId;

    $ok = mysqli_query(
        $conexion,
        "INSERT IGNORE INTO barbero_horarios (barbero_id, dia_semana, hora_apertura, hora_cierre, cerrado)
         SELECT $barberoId,
                CASE
                    WHEN h.dia LIKE 'Lu%'  THEN 1
                    WHEN h.dia LIKE 'Mar%' THEN 2
                    WHEN h.dia LIKE 'Mi%'  THEN 3
                    WHEN h.dia LIKE 'Ju%'  THEN 4
                    WHEN h.dia LIKE 'Vi%'  THEN 5
                    WHEN h.dia LIKE 'S%'   THEN 6
                    WHEN h.dia LIKE 'Do%'  THEN 0
                END,
                h.hora_apertura, h.hora_cierre, h.cerrado
         FROM horarios h
         WHERE h.dia REGEXP '^(Lu|Mar|Mi|Ju|Vi|S|Do)'"
    );

    if (!$ok) {
        throw new RuntimeException("Error al copiar el horario general al barbero.");
    }
}

// Devuelve los 7 días (0..6). Los días sin fila se consideran cerrados.
function get_horario_barbero(int $barberoId): array
{
    global $conexion;

    $horario = [];
    for ($d = 0; $d <= 6; $d++) {
        $horario[$d] = ['dia_semana' => $d, 'hora_apertura' => null, 'hora_cierre' => null, 'cerrado' => 1];
    }

    $stmt = mysqli_prepare(
        $conexion,
        "SELECT dia_semana, hora_apertura, hora_cierre, cerrado FROM barbero_horarios WHERE barbero_id = ?"
    );
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la consulta del horario del barbero.");
    }

    mysqli_stmt_bind_param($stmt, "i", $barberoId);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $horario[(int) $fila['dia_semana']] = $fila;
    }

    return $horario;
}

// $dias: [dia_semana => ['apertura' => 'HH:MM'|null, 'cierre' => 'HH:MM'|null, 'cerrado' => bool]]
function guardar_horario_barbero(int $barberoId, array $dias): bool
{
    global $conexion;

    $stmt = mysqli_prepare(
        $conexion,
        "INSERT INTO barbero_horarios (barbero_id, dia_semana, hora_apertura, hora_cierre, cerrado)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            hora_apertura = VALUES(hora_apertura),
            hora_cierre   = VALUES(hora_cierre),
            cerrado       = VALUES(cerrado)"
    );
    if (!$stmt) {
        throw new RuntimeException("Error al preparar el guardado del horario.");
    }

    foreach ($dias as $diaSemana => $d) {
        $dia      = (int) $diaSemana;
        $apertura = $d['apertura'];
        $cierre   = $d['cierre'];
        $cerrado  = $d['cerrado'] ? 1 : 0;

        mysqli_stmt_bind_param($stmt, "iissi", $barberoId, $dia, $apertura, $cierre, $cerrado);

        if (!mysqli_stmt_execute($stmt)) {
            return false;
        }
    }

    return true;
}

// ---------------------------------------------------------------
// Disponibilidad
// ---------------------------------------------------------------

/**
 * Turnos de un día con los barberos que están libres en cada uno.
 *
 * - $barberoId = null  -> considera a todos los barberos activos ("cualquiera disponible").
 * - $barberoId = N     -> solo a ese barbero (si está activo).
 *
 * Devuelve ['09:00' => [1, 3], '09:30' => [], ...]
 * Una hora aparece si al menos un barbero trabaja a esa hora; si el array está vacío, está todo ocupado.
 */
function disponibilidad_para_fecha(string $fecha, ?int $barberoId = null): array
{
    global $conexion;

    $diaSemana = (int) date('w', strtotime($fecha));
    $filtro    = $barberoId !== null ? ' AND bh.barbero_id = ' . (int) $barberoId : '';

    $resultado = mysqli_query(
        $conexion,
        "SELECT bh.barbero_id, bh.hora_apertura, bh.hora_cierre
         FROM barbero_horarios bh
         JOIN barberos b ON b.id = bh.barbero_id AND b.activo = 1
         WHERE bh.dia_semana = $diaSemana
           AND bh.cerrado = 0
           AND bh.hora_apertura IS NOT NULL
           AND bh.hora_cierre IS NOT NULL
           $filtro"
    );

    if (!$resultado) {
        throw new RuntimeException("Error al consultar los horarios de los barberos.");
    }

    $libres = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $inicio = strtotime($fecha . ' ' . $fila['hora_apertura']);
        $fin    = strtotime($fecha . ' ' . $fila['hora_cierre']);

        for ($t = $inicio; $t < $fin; $t += TURNO_INTERVALO_MIN * 60) {
            $libres[date('H:i', $t)][] = (int) $fila['barbero_id'];
        }
    }

    if (empty($libres)) {
        return [];
    }

    // Se descuentan los barberos que ya tienen un turno (no cancelado) a esa hora
    $stmt = mysqli_prepare(
        $conexion,
        "SELECT barbero_id, hora FROM reservas
         WHERE fecha = ? AND estado != 'cancelada' AND barbero_id IS NOT NULL"
    );
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la consulta de turnos ocupados.");
    }

    mysqli_stmt_bind_param($stmt, "s", $fecha);
    mysqli_stmt_execute($stmt);
    $ocupados = mysqli_stmt_get_result($stmt);

    while ($fila = mysqli_fetch_assoc($ocupados)) {
        $hora = substr($fila['hora'], 0, 5);

        if (isset($libres[$hora])) {
            $libres[$hora] = array_values(array_diff($libres[$hora], [(int) $fila['barbero_id']]));
        }
    }

    ksort($libres);
    return $libres;
}

// Entre los barberos libres, elige al que tenga menos turnos ese día (si hay empate, al azar)
function elegir_barbero_menos_cargado(string $fecha, array $barberoIds): int
{
    global $conexion;

    $ids = array_values(array_map('intval', $barberoIds));

    if (count($ids) === 1) {
        return $ids[0];
    }

    $lista = implode(',', $ids);

    $stmt = mysqli_prepare(
        $conexion,
        "SELECT barbero_id, COUNT(*) AS total
         FROM reservas
         WHERE fecha = ? AND estado != 'cancelada' AND barbero_id IN ($lista)
         GROUP BY barbero_id"
    );
    if (!$stmt) {
        throw new RuntimeException("Error al preparar el reparto de turnos.");
    }

    mysqli_stmt_bind_param($stmt, "s", $fecha);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $cargas = array_fill_keys($ids, 0);
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $cargas[(int) $fila['barbero_id']] = (int) $fila['total'];
    }

    $minimo    = min($cargas);
    $empatados = array_keys(array_filter($cargas, fn($c) => $c === $minimo));

    return (int) $empatados[random_int(0, count($empatados) - 1)];
}
