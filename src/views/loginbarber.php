<?php
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario  = trim($_POST["usuario"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($usuario) || empty($password)) {
        $message = "Por favor completá todos los campos.";
    } else {
        try {
            $fila = find_user_by_username($usuario);

            if ($fila === null) {
                $message = "Usuario no encontrado.";
            } elseif (password_verify($password, $fila["password"])) {
                iniciar_sesion_usuario((int) $fila["id"], $usuario, $fila["role"]);

                header('Location: ' . url_destino_segun_rol($fila["role"]));
                exit;
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
