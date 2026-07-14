<?php

function get_db_connection(): mysqli
{
    $conexion = new mysqli("localhost", "root", "", "login_re_proyecto");

    if ($conexion->connect_error) {
        throw new RuntimeException("Error de conexión: " . $conexion->connect_error);
    }

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
