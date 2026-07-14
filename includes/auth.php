<?php
// includes/auth.php
// Funciones de sesión y control de acceso por roles (user, admin, super_admin)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Guarda los datos del usuario logueado en la sesión
function iniciar_sesion_usuario(int $id, string $usuario, string $role): void
{
    $_SESSION['user_id']  = $id;
    $_SESSION['usuario']  = $usuario;
    $_SESSION['role']     = $role;
}

// Cierra la sesión actual
function cerrar_sesion_usuario(): void
{
    $_SESSION = [];
    session_destroy();
}

// Verifica si hay un usuario logueado
function esta_logueado(): bool
{
    return isset($_SESSION['user_id']);
}

// Devuelve el rol del usuario actual (o 'user' por defecto si no está logueado)
function obtener_rol(): string
{
    return $_SESSION['role'] ?? 'user';
}

// Verifica si el usuario tiene al menos el rol requerido (jerarquía: user < admin < super_admin)
function tiene_rol(string $rolRequerido): bool
{
    $jerarquia = ['user' => 1, 'admin' => 2, 'super_admin' => 3];
    $rolActual = obtener_rol();

    return ($jerarquia[$rolActual] ?? 0) >= ($jerarquia[$rolRequerido] ?? 0);
}

// Corta la ejecución y redirige si el usuario no está logueado o no tiene el rol requerido
function requerir_rol(string $rolRequerido): void
{
    if (!esta_logueado() || !tiene_rol($rolRequerido)) {
        header('Location: loginbarber.php?error=sin_permiso');
        exit;
    }
}

// Devuelve la URL de destino según el rol (útil para redirigir tras el login)
function url_destino_segun_rol(string $role): string
{
    return match ($role) {
        'super_admin' => 'superadmin_dashboard.php',
        'admin'       => 'admin_dashboard.php',
        default       => 'index.php',
    };
}
