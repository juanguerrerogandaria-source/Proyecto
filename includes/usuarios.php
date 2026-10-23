<?php
// includes/usuarios.php
// Funciones relacionadas a los usuarios y sus roles

function get_all_users(): array
{
    global $conexion;

    $resultado = mysqli_query(
        $conexion,
        "SELECT id, usuario, email, role
        FROM usuarios
        ORDER BY usuario ASC"
    );

    if (!$resultado) {
        throw new RuntimeException("Error al obtener los usuarios.");
    }

    $usuarios = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $usuarios[] = $fila;
    }

    return $usuarios;
}

function create_user(string $usuario, string $email, string $password_hash, string $role = 'admin', bool $verificado = true): bool
{
    global $conexion;

    // Verificar que el email no esté en uso
    $check = mysqli_prepare($conexion, "SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($check, "s", $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        return false;
    }

    $stmt = mysqli_prepare(
        $conexion,
        "INSERT INTO usuarios (usuario, email, password, role, email_verificado) VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new RuntimeException("Error al preparar la creación del usuario.");
    }

    $v = $verificado ? 1 : 0;
    mysqli_stmt_bind_param($stmt, "ssssi", $usuario, $email, $password_hash, $role, $v);
    return mysqli_stmt_execute($stmt);
}

function update_user_role(int $userId, string $newRole): bool
{
    global $conexion;

    $rolesValidos = ['user', 'admin', 'super_admin'];
    if (!in_array($newRole, $rolesValidos, true)) {
        throw new RuntimeException("Rol inválido.");
    }

    $stmt = mysqli_prepare($conexion, "UPDATE usuarios SET role = ? WHERE id = ?");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la actualización del rol.");
    }

    mysqli_stmt_bind_param($stmt, "si", $newRole, $userId);
    return mysqli_stmt_execute($stmt);
}

function get_email_usuario(int $id): ?string
{
    global $conexion;

    $stmt = mysqli_prepare($conexion, "SELECT email FROM usuarios WHERE id = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException("Error al preparar la consulta de email.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $fila = mysqli_fetch_assoc($resultado);

    return $fila['email'] ?? null;
}

// ---------------------------------------------------------------
// Verificación de email
// ---------------------------------------------------------------

function get_usuario_por_id(int $id): ?array
{
    global $conexion;
    $stmt = mysqli_prepare($conexion, "SELECT id, usuario, email, role, email_verificado FROM usuarios WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $fila ?: null;
}

// Genera un código de 6 dígitos, guarda su hash y lo devuelve para enviarlo por email
function generar_codigo_verificacion(int $userId): string
{
    global $conexion;

    $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hash   = password_hash($codigo, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
        $conexion,
        "UPDATE usuarios
         SET codigo_verificacion = ?, codigo_expira = DATE_ADD(NOW(), INTERVAL 15 MINUTE),
             codigo_intentos = 0, codigo_enviado_at = NOW()
         WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "si", $hash, $userId);
    mysqli_stmt_execute($stmt);

    return $codigo;
}

// Se puede pedir un código nuevo cada 60 segundos
function puede_reenviar_codigo(int $userId): bool
{
    global $conexion;
    $stmt = mysqli_prepare(
        $conexion,
        "SELECT (codigo_enviado_at IS NULL OR codigo_enviado_at < DATE_SUB(NOW(), INTERVAL 60 SECOND)) AS ok
         FROM usuarios WHERE id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return (int) ($fila['ok'] ?? 0) === 1;
}

// Devuelve: 'ok' | 'invalido' | 'expirado' | 'bloqueado'
function verificar_codigo_email(int $userId, string $codigo): string
{
    global $conexion;

    $stmt = mysqli_prepare(
        $conexion,
        "SELECT codigo_verificacion, codigo_intentos, (codigo_expira < NOW()) AS vencido
         FROM usuarios WHERE id = ? AND email_verificado = 0 LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$fila || empty($fila['codigo_verificacion'])) {
        return 'expirado';
    }
    if ((int) $fila['codigo_intentos'] >= 5) {
        return 'bloqueado';
    }
    if ((int) $fila['vencido'] === 1) {
        return 'expirado';
    }

    if (!password_verify($codigo, $fila['codigo_verificacion'])) {
        $mas = mysqli_prepare($conexion, "UPDATE usuarios SET codigo_intentos = codigo_intentos + 1 WHERE id = ?");
        mysqli_stmt_bind_param($mas, "i", $userId);
        mysqli_stmt_execute($mas);
        return 'invalido';
    }

    $ok = mysqli_prepare(
        $conexion,
        "UPDATE usuarios
         SET email_verificado = 1, codigo_verificacion = NULL, codigo_expira = NULL, codigo_intentos = 0
         WHERE id = ?"
    );
    mysqli_stmt_bind_param($ok, "i", $userId);
    mysqli_stmt_execute($ok);

    return 'ok';
}