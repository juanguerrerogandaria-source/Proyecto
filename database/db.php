<?php

    require_once __DIR__ . '/../includes/config.php';
     = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
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
