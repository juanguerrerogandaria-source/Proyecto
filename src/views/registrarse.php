<?php
    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/usuarios.php';
require_once __DIR__ . '/../../includes/mailer.php';

$messages = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario          = htmlspecialchars(trim($_POST["usuario"] ?? ""));
    $email            = htmlspecialchars(trim($_POST["email"] ?? ""));
    $password         = htmlspecialchars(trim($_POST["password"] ?? ""));
    $confirm_password = htmlspecialchars(trim($_POST["confirm_password"] ?? ""));

    
    $role = "user";

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

            if (create_user($usuario, $email, $password_hash, $role, false)) {
    $nuevoId = (int) mysqli_insert_id($conexion);
    $codigo  = generar_codigo_verificacion($nuevoId);
    enviar_codigo_verificacion($usuario, $email, $codigo);   // si falla, se reenvía desde la pantalla del código

    $_SESSION['verificar_user_id'] = $nuevoId;
    header('Location: verificar_email.php');
    exit;
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
    <link rel="stylesheet" href="../../public/css/registrarse.css">
    <title>Registrarse - Tuya's Barber</title>
    <link rel="icon" type="image/png" href="../../public/img/tuyas_sin_fondo.png">
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
       <ul id="password-checklist" class="password-checklist">
        <li id="req-length">Mínimo 6 caracteres</li>
        <li id="req-mayus">Una letra mayúscula</li>
        <li id="req-minus">Una letra minúscula</li>
        <li id="req-numero">Un número</li>
        <li id="req-especial">Un carácter especial</li>
        </ul>

        <label for="confirm_password">Confirmar Contraseña:</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repetí tu contraseña" required minlength="6">

        <a href="loginbarber.php">¿Ya tenés cuenta? Iniciar sesión</a>

        <button type="submit">Registrarse</button>
    </form>
    <script>
    const passwordInput = document.getElementById('password');
    const reqs = {
        length:  { el: document.getElementById('req-length'),  test: v => v.length >= 6 },
        mayus:   { el: document.getElementById('req-mayus'),   test: v => /[A-Z]/.test(v) },
        minus:   { el: document.getElementById('req-minus'),   test: v => /[a-z]/.test(v) },
        numero:  { el: document.getElementById('req-numero'),  test: v => /[0-9]/.test(v) },
        especial:{ el: document.getElementById('req-especial'),test: v => /[^a-zA-Z0-9]/.test(v) },
    };

    passwordInput.addEventListener('input', function () {
        const valor = this.value;
        for (const key in reqs) {
            const cumple = reqs[key].test(valor);
            reqs[key].el.classList.toggle('cumple', cumple);
            reqs[key].el.classList.toggle('falta', !cumple);
        }
    });
</script>
</body>
</html>
