<?php
require_once __DIR__ . '/../../database/conexcion_BD.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/usuarios.php';

$message = "";
$success = false;

if (isset($_GET['registrado'])) {
    $message = "Cuenta creada correctamente. Iniciá sesión para continuar.";
    $success = true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario  = htmlspecialchars(trim($_POST["usuario"] ?? ""));
    $password = htmlspecialchars(trim($_POST["password"] ?? ""));

    if (empty($usuario) || empty($password)) {
        $message = "Por favor completá todos los campos.";
    } else {
        try {
            $fila = find_user_by_username($usuario);

            if ($fila === null) {
                $message = "Usuario no encontrado.";
            } elseif (password_verify($password, $fila["password"])) {

    // Si todavía no verificó su correo, lo mandamos a la pantalla del código
    if ((int) ($fila["email_verificado"] ?? 1) === 0) {
        $_SESSION['verificar_user_id'] = (int) $fila["id"];
        header('Location: verificar_email.php?pendiente=1');
        exit;
    }

    iniciar_sesion_usuario((int) $fila["id"], $usuario, $fila["role"]);
    ...
            } else {
                $message = "Contraseña incorrecta.";
            }
        } catch (RuntimeException $e) {
            $message = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/loginbarber.css">
    <title>Login - Tuya's Barber</title>
    <link rel="icon" type="image/png" href="../../public/img/tuyas_sin_fondo.png">
</head>
<body>
    <?php if ($message): ?>
        <p style="color: <?php echo $success ? 'green' : 'red'; ?>; text-align:center;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form action="loginbarber.php" method="post">
        <label for="usuario">Usuario:</label>
        <input type="text" id="usuario" name="usuario" placeholder="Tu usuario" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" placeholder="Tu contraseña" required minlength="6">

        <a href="registrarse.php">¿No tenés cuenta? Registrate</a>

        <button type="submit">Verificar</button>
    </form>
</body>
</html>
