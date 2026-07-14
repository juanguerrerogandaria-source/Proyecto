<?php
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requerir_rol('super_admin'); // solo super_admin puede entrar a esta página

$mensaje  = "";
$es_error = false;

// Cambiar el rol de un usuario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = (int) ($_POST["id_usuario"] ?? 0);
    $nuevo_rol  = $_POST["nuevo_rol"] ?? "";

    if ($id_usuario === (int) $_SESSION['user_id'] && $nuevo_rol !== 'super_admin') {
        // Evita que el super_admin se quite el rol a sí mismo por error y se quede afuera del panel
        $mensaje  = "No podés quitarte tu propio rol de super_admin.";
        $es_error = true;
    } elseif (update_user_role($id_usuario, $nuevo_rol)) {
        $mensaje = "Rol actualizado correctamente.";
    } else {
        $mensaje  = "No se pudo actualizar el rol (valor inválido).";
        $es_error = true;
    }
}

$usuarios = get_all_users();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/superadmin.css">
    <title>Gestión de Roles - Tuya's Barber</title>
</head>
<body>
    <div class="panel panel--ancho">
        <a class="btn-volver" href="superadmin_dashboard.php">&larr; Volver al panel</a>

        <h1>Gestión de Roles</h1>
        <p class="subtitulo">Asigná o quitá roles a los usuarios registrados.</p>

        <?php if ($mensaje): ?>
            <p class="mensaje<?= $es_error ? ' error' : '' ?>"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <div class="tabla-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol actual</th>
                        <th>Cambiar rol</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['usuario']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge badge-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                            <td>
                                <form class="form-rol" action="gestionar_roles.php" method="post">
                                    <input type="hidden" name="id_usuario" value="<?= (int) $u['id'] ?>">
                                    <select name="nuevo_rol">
                                        <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>Usuario</option>
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                        <option value="super_admin" <?= $u['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                                    </select>
                                    <button type="submit" class="btn-chico">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
