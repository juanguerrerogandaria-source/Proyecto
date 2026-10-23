<?php
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexcion_BD.php';
requerir_rol('super_admin'); 

$messages = [];
$success  = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario          = trim($_POST["usuario"] ?? "");
    $email            = trim($_POST["email"] ?? "");
    $password         = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");

    if (empty($usuario) || empty($email) || empty($password) || empty($confirm_password)) {
        $messages[] = "Todos los campos son obligatorios.";
    }

    if (strlen($password) < 6) {
        $messages[] = "La contraseña debe tener al menos 6 caracteres.";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $messages[] = "La contraseña debe tener al menos una letra mayúscula.";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $messages[] = "La contraseña debe tener al menos una letra minúscula.";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $messages[] = "La contraseña debe tener al menos un número.";
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $messages[] = "La contraseña debe tener al menos un carácter especial.";
    }

    if ($password !== $confirm_password) {
        $messages[] = "Las contraseñas no coinciden.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messages[] = "El correo electrónico no es válido.";
    }

    if (empty($messages)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            if (create_user($usuario, $email, $password_hash, "admin")) {
                $success = true;
                $messages[] = "Administrador \"$usuario\" creado correctamente.";
            } else {
                $messages[] = "Error al crear el administrador (¿usuario o correo ya existen?).";
            }
        } catch (RuntimeException $e) {
            $messages[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/superadmin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=block">
    <link rel="icon" type="image/png" href="../../public/img/tuyas_sin_fondo.png">
    <title>Panel Super Admin - Tuya's Barber</title>
</head>
<body>
    <div class="panel">
        <h1>Panel de Super Administrador</h1>
        <p class="bienvenida">
            Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
            &middot; <span class="badge badge-<?= htmlspecialchars($_SESSION['role']) ?>"><?= htmlspecialchars($_SESSION['role']) ?></span>
        </p>

        <div class="grid-accesos">
            <a class="tarjeta-acceso" href="gestionar_cortes.php">
                <span class="material-symbols-outlined tarjeta-acceso__icono">content_cut</span>
                <h3>Cortes y Servicios</h3>
                <p>Agregá cortes con foto o video y modificá sus precios.</p>
            </a>

            <a class="tarjeta-acceso" href="estadisticas.php">
                <span class="material-symbols-outlined tarjeta-acceso__icono">bar_chart</span>
                <h3>Estadísticas</h3>
                <p>Ingresos, días y horarios más pedidos, barbero más elegido y más.</p>
            </a>

            <a class="tarjeta-acceso" href="gestionar_barberos.php">
                <span class="material-symbols-outlined tarjeta-acceso__icono">groups</span>
                <h3>Barberos</h3>
                <p>Agregá barberos con su foto y configurá el horario de cada uno.</p>
            </a>

            <a class="tarjeta-acceso" href="gestionar_horarios.php">
                <span class="material-symbols-outlined tarjeta-acceso__icono">schedule</span>
                <h3>Horarios</h3>
                <p>Horario general del local (base para los barberos nuevos).</p>
            </a>

            <a class="tarjeta-acceso" href="gestionar_ofertas.php">
                <span class="material-symbols-outlined tarjeta-acceso__icono">sell</span>
                <h3>Ofertas</h3>
                <p>Creá promociones y descuentos por tiempo limitado.</p>
            </a>

            <a class="tarjeta-acceso" href="gestionar_roles.php">
                <span class="material-symbols-outlined tarjeta-acceso__icono">manage_accounts</span>
                <h3>Usuarios y Roles</h3>
                <p>Modificá el rol de cualquier usuario registrado.</p>
            </a>

            <a class="tarjeta-acceso" href="gestionar_reservas.php">
               <span class="material-symbols-outlined tarjeta-acceso__icono">calendar_month</span>
                <h3>Turnos Agendados</h3>
                <p>Mirá el detalle completo de cada reserva y confirmá o cancelá turnos.</p>
            </a>
        </div>

        <div class="card-form">
            <h2>Crear nuevo administrador</h2>

            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <p class="mensaje<?= $success ? '' : ' error' ?>"><?= htmlspecialchars($message) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <form action="superadmin_dashboard.php" method="post">
                <div class="grid-2">
                    <div class="campo">
                        <label for="usuario">Usuario</label>
                        <input type="text" id="usuario" name="usuario" placeholder="Usuario del admin" required>
                    </div>
                    <div class="campo">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="campo">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                    <div class="campo">
                        <label for="confirm_password">Confirmar contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repetí la contraseña" required minlength="6">
                    </div>
                </div>

                <button type="submit" class="btn-primario">Crear administrador</button>
            </form>
        </div>
        <a href="index.php" class="flow-back">&larr; Volver al inicio</a>
    </div>
</body>
</html>
