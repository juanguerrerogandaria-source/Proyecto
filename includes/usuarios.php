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

function create_user(string $usuario, string $email, string $password, string $role = 'admin'): int
{
    global $conexion;

    // Verificar que el email no esté en uso
    $check = mysqli_prepare($conexion, "SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($check, "s", $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        throw new RuntimeException("Ya existe un usuario con ese correo electrónico.");
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
        $conexion,
        "INSERT INTO usuarios (usuario, email, password, role) VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new RuntimeException("Error al preparar la creación del usuario.");
    }

    mysqli_stmt_bind_param($stmt, "ssss", $usuario, $email, $passwordHash, $role);
    mysqli_stmt_execute($stmt);

    return mysqli_insert_id($conexion);
}