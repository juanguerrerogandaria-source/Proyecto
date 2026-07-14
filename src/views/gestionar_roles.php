<?php
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requerir_rol('super_admin'); // solo super_admin puede entrar a esta página

$mensaje = "";

// Cambiar el rol de un usuario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario   = (int) ($_POST["id_usuario"] ?? 0);
    $nuevo_rol    = $_POST["nuevo_rol"] ?? "";

    if ($id_usuario === (int) $_SESSION['user_id'] && $nuevo_rol !== 'super_admin') {
        // Evita que el super_admin se quite el rol a sí mismo por error y se quede afuera del panel
        $mensaje = "No podés quitarte tu propio rol de super_admin.";
    } elseif (update_user_role($id_usuario, $nuevo_rol)) {
        $mensaje = "Rol actualizado correctamente.";
    } else {
        $mensaje = "No se pudo actualizar el rol (valor inválido).";
    }
}

$usuarios = get_all_users();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Roles - Tuya's Barber</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 2rem auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.5rem; border-bottom: 1px solid #ddd; text-align: left; }
        .mensaje { color: green; }
    </style>
</head>
<body>
    <h1>Gestión de Roles</h1>
    <p><a href="superadmin_dashboard.php">&larr; Volver al panel</a></p>

    <?php if ($mensaje): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

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
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td>
                        <form action="gestionar_roles.php" method="post" style="display:flex; gap:0.5rem;">
                            <input type="hidden" name="id_usuario" value="<?= (int) $u['id'] ?>">
                            <select name="nuevo_rol">
                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>Usuario</option>
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                <option value="super_admin" <?= $u['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                            </select>
                            <button type="submit">Guardar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
