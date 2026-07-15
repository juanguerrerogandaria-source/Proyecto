<?php

function get_db_connection(): mysqli
{
    $conexion = new mysqli("localhost", "root", "", "login_re_proyecto");

    if ($conexion->connect_error) {
        throw new RuntimeException("Error de conexión: " . $conexion->connect_error);
    }

    // Importante: sin esto los acentos (Sábado, Miércoles, ñ) llegan rotos a MySQL
    $conexion->set_charset('utf8mb4');

    return $conexion;
}

function find_user_by_username(string $usuario): ?array
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("SELECT id, password, role FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows !== 1) {
            return null;
        }

        return $resultado->fetch_assoc();
    } finally {
        $conexion->close();
    }
}

function create_user(string $usuario, string $email, string $password_hash, string $role = 'user'): bool
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $usuario, $email, $password_hash, $role);

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

// ------------------------------------------------------------------
// Usuarios y roles (panel de super_admin)
// ------------------------------------------------------------------

function get_all_users(): array
{
    $conexion = get_db_connection();

    try {
        $resultado = $conexion->query("SELECT id, usuario, email, role FROM usuarios ORDER BY id ASC");

        return $resultado->fetch_all(MYSQLI_ASSOC);
    } finally {
        $conexion->close();
    }
}

function update_user_role(int $id, string $role): bool
{
    // Nunca confiar en lo que venga crudo del formulario.
    if (!in_array($role, ['user', 'admin', 'super_admin'], true)) {
        return false;
    }

    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("UPDATE usuarios SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $id);

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

// ------------------------------------------------------------------
// Cortes / servicios (nombre, descripción, precio, foto o video)
// ------------------------------------------------------------------

function get_all_cortes(): array
{
    $conexion = get_db_connection();

    try {
        $resultado = $conexion->query("SELECT id, nombre, categoria, descripcion, precio, media_path, media_tipo FROM cortes ORDER BY id DESC");

        return $resultado->fetch_all(MYSQLI_ASSOC);
    } finally {
        $conexion->close();
    }
}

function get_cortes_por_categoria(string $categoria): array
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("SELECT id, nombre, categoria, descripcion, precio, media_path, media_tipo FROM cortes WHERE categoria = ? ORDER BY id DESC");
        $stmt->bind_param("s", $categoria);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $conexion->close();
    }
}

function create_corte(string $nombre, string $categoria, string $descripcion, float $precio, ?string $media_path, ?string $media_tipo): bool
{
    if (!in_array($categoria, ['hombre', 'mujer'], true)) {
        $categoria = 'hombre';
    }

    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("INSERT INTO cortes (nombre, categoria, descripcion, precio, media_path, media_tipo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdss", $nombre, $categoria, $descripcion, $precio, $media_path, $media_tipo);

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

function update_corte_precio(int $id, float $precio): bool
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("UPDATE cortes SET precio = ? WHERE id = ?");
        $stmt->bind_param("di", $precio, $id);

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

function delete_corte(int $id): bool
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("DELETE FROM cortes WHERE id = ?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

// ------------------------------------------------------------------
// Horarios de atención (uno por día)
// ------------------------------------------------------------------

function get_horarios(): array
{
    $conexion = get_db_connection();

    try {
        $resultado = $conexion->query("SELECT id, dia, hora_apertura, hora_cierre, cerrado FROM horarios ORDER BY FIELD(dia, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')");

        return $resultado->fetch_all(MYSQLI_ASSOC);
    } finally {
        $conexion->close();
    }
}

function update_horario(int $id, ?string $apertura, ?string $cierre, bool $cerrado): bool
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("UPDATE horarios SET hora_apertura = ?, hora_cierre = ?, cerrado = ? WHERE id = ?");
        $cerradoInt = $cerrado ? 1 : 0;
        $stmt->bind_param("ssii", $apertura, $cierre, $cerradoInt, $id);

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

// ------------------------------------------------------------------
// Ofertas / promociones
// ------------------------------------------------------------------

function get_ofertas(): array
{
    $conexion = get_db_connection();

    try {
        $resultado = $conexion->query("SELECT id, titulo, descripcion, descuento_porcentaje, fecha_inicio, fecha_fin, activa FROM ofertas ORDER BY id DESC");

        return $resultado->fetch_all(MYSQLI_ASSOC);
    } finally {
        $conexion->close();
    }
}

function create_oferta(string $titulo, string $descripcion, int $descuento, ?string $fecha_inicio, ?string $fecha_fin): bool
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("INSERT INTO ofertas (titulo, descripcion, descuento_porcentaje, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $titulo, $descripcion, $descuento, $fecha_inicio, $fecha_fin);

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

function toggle_oferta(int $id, bool $activa): bool
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("UPDATE ofertas SET activa = ? WHERE id = ?");
        $activaInt = $activa ? 1 : 0;
        $stmt->bind_param("ii", $activaInt, $id);

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

function delete_oferta(int $id): bool
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("DELETE FROM ofertas WHERE id = ?");
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

// ------------------------------------------------------------------
// Reservas de turnos
// ------------------------------------------------------------------

function get_corte_por_id(int $id): ?array
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("SELECT id, nombre, categoria, precio FROM cortes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->fetch_assoc() ?: null;
    } finally {
        $conexion->close();
    }
}

function create_reserva(
    string $codigo,
    string $nombre_cliente,
    string $email_cliente,
    ?string $telefono_cliente,
    ?int $corte_id,
    string $fecha,
    string $hora,
    ?string $notas
): bool {
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare(
            "INSERT INTO reservas (codigo, nombre_cliente, email_cliente, telefono_cliente, corte_id, fecha, hora, notas)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssssisss",
            $codigo, $nombre_cliente, $email_cliente, $telefono_cliente, $corte_id, $fecha, $hora, $notas
        );

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}

// ------------------------------------------------------------------
// Flujo de reserva en pasos (horarios disponibles, ofertas, email)
// ------------------------------------------------------------------

// Devuelve las horas ya reservadas para una fecha, en formato HH:MM
function obtener_horas_ocupadas(string $fecha): array
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("SELECT hora FROM reservas WHERE fecha = ? AND estado <> 'cancelada'");
        $stmt->bind_param("s", $fecha);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $horas = [];

        while ($fila = $resultado->fetch_assoc()) {
            // normalizamos a HH:MM por si MySQL devuelve HH:MM:SS
            $horas[] = substr($fila['hora'], 0, 5);
        }

        return $horas;
    } finally {
        $conexion->close();
    }
}

// Devuelve el horario de atención (apertura, cierre, cerrado) para una fecha concreta,
// usando la tabla `horarios` que administra el panel de admin.
function get_horario_para_fecha(string $fecha): ?array
{
    // Comparamos por las primeras letras sin acento (mie, sab) para que funcione
    // aunque los nombres de los días se hayan guardado con problemas de codificación.
    $dias = [1 => 'lun', 2 => 'mar', 3 => 'mie', 4 => 'jue', 5 => 'vie', 6 => 'sab', 7 => 'dom'];
    $clave = $dias[(int) date('N', strtotime($fecha))] ?? null;

    if ($clave === null) {
        return null;
    }

    $conexion = get_db_connection();

    try {
        $resultado = $conexion->query("SELECT dia, hora_apertura, hora_cierre, cerrado FROM horarios");

        while ($fila = $resultado->fetch_assoc()) {
            // Nos quedamos solo con letras a-z: "Miércoles" -> "mircoles", "Sábado" -> "sbado"
            $normalizado = strtolower(preg_replace('/[^a-zA-Z]/', '', $fila['dia']));

            if ($normalizado === '') {
                continue;
            }

            // Identificamos el día por sus primeras letras (sin depender de los acentos)
            $claveFila = match (true) {
                str_starts_with($normalizado, 'lu')  => 'lun',
                str_starts_with($normalizado, 'ma')  => 'mar',
                str_starts_with($normalizado, 'mi')  => 'mie',
                str_starts_with($normalizado, 'ju')  => 'jue',
                str_starts_with($normalizado, 'vi')  => 'vie',
                str_starts_with($normalizado, 'sa'),
                str_starts_with($normalizado, 'sb')  => 'sab',
                str_starts_with($normalizado, 'do')  => 'dom',
                default => null,
            };

            if ($claveFila === $clave) {
                return $fila;
            }
        }

        return null;
    } finally {
        $conexion->close();
    }
}

// Genera los turnos posibles (cada 1 hora) entre apertura y cierre de ese día.
// Si el local está cerrado ese día, devuelve un array vacío.
function generar_turnos_para_fecha(string $fecha): array
{
    $horario = get_horario_para_fecha($fecha);

    if (!$horario || (int) $horario['cerrado'] === 1 || empty($horario['hora_apertura']) || empty($horario['hora_cierre'])) {
        return [];
    }

    $turnos  = [];
    $actual  = strtotime($fecha . ' ' . $horario['hora_apertura']);
    $cierre  = strtotime($fecha . ' ' . $horario['hora_cierre']);

    // El último turno arranca una hora antes del cierre
    while ($actual < $cierre) {
        $turnos[] = date('H:i', $actual);
        $actual  += 3600;
    }

    return $turnos;
}

// Devuelve la oferta activa con mayor descuento vigente hoy (o null si no hay)
function get_mejor_oferta_activa(): ?array
{
    $conexion = get_db_connection();

    try {
        $hoy = date('Y-m-d');
        $stmt = $conexion->prepare(
            "SELECT id, titulo, descuento_porcentaje
             FROM ofertas
             WHERE activa = 1
               AND (fecha_inicio IS NULL OR fecha_inicio <= ?)
               AND (fecha_fin IS NULL OR fecha_fin >= ?)
             ORDER BY descuento_porcentaje DESC
             LIMIT 1"
        );
        $stmt->bind_param("ss", $hoy, $hoy);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    } finally {
        $conexion->close();
    }
}

// Devuelve el email guardado de un usuario logueado (para precargar el formulario)
function get_email_usuario(int $id): ?string
{
    $conexion = get_db_connection();

    try {
        $stmt = $conexion->prepare("SELECT email FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $fila = $stmt->get_result()->fetch_assoc();

        return $fila['email'] ?? null;
    } finally {
        $conexion->close();
    }
}

// Crea la reserva del flujo en pasos. Revalida que el horario siga libre
// justo antes de insertar, para evitar que dos personas tomen el mismo turno.
function crear_reserva_flow(
    string $codigo,
    string $nombre_cliente,
    string $email_cliente,
    ?string $telefono_cliente,
    ?int $corte_id,
    string $fecha,
    string $hora,
    ?string $notas,
    string $metodo_pago,
    float $monto
): bool {
    $conexion = get_db_connection();

    try {
        $check = $conexion->prepare("SELECT id FROM reservas WHERE fecha = ? AND hora = ? AND estado <> 'cancelada'");
        $check->bind_param("ss", $fecha, $hora);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            return false;
        }

        $stmt = $conexion->prepare(
            "INSERT INTO reservas (codigo, nombre_cliente, email_cliente, telefono_cliente, corte_id, fecha, hora, notas, metodo_pago, monto)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssssissssd",
            $codigo, $nombre_cliente, $email_cliente, $telefono_cliente, $corte_id, $fecha, $hora, $notas, $metodo_pago, $monto
        );

        return $stmt->execute();
    } finally {
        $conexion->close();
    }
}
