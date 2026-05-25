<?php
require_once __DIR__ . '/../../database/db.php';

$messages = [];
$success = false;

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
            $password_hash = password_hash($confirm_password, PASSWORD_DEFAULT);

            if (create_user($usuario, $email, $password_hash)) {
                $success = true;
                $messages[] = "Usuario registrado correctamente.";
            } else {
                $messages[] = "Error al registrar el usuario.";
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
    <link rel="stylesheet" href="registrarse.css">
    <title>Registrarse - Tuya's Barber</title>
</head>
<body>
    <?php if (!empty($messages)): ?>
        <div style="text-align:center;">
            <?php foreach ($messages as $message): ?>
                <p style="color: <?php echo $success ? 'green' : 'red'; ?>; margin: 0.25rem 0;">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="registrarse.php" method="post">
        <label for="usuario">Usuario:</label>
        <input type="text" id="usuario" name="usuario" placeholder="Tu usuario" required>

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">

        <label for="confirm_password">Confirmar Contraseña:</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repetí tu contraseña" required minlength="6">

        <a href="loginbarber.php">¿Ya tenés cuenta? Iniciar sesión</a>

        <button type="submit">Registrarse</button>
    </form>
</body>
</html>
