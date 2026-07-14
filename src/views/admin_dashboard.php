<?php
require_once __DIR__ . '/../../includes/auth.php';
requerir_rol('admin'); // solo admin o super_admin pueden entrar
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Tuya's Barber</title>
    <link rel="stylesheet" href="../../public/css/admin.css">
</head>
<body>
    <h1>Panel de Administración</h1>
    <p>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> (rol: <?= htmlspecialchars($_SESSION['role']) ?>)</p>

    <?php if (tiene_rol('super_admin')): ?>
        <p><a href="superadmin_dashboard.php">Ir al panel de Super Admin</a></p>
    <?php endif; ?>
</body>
</html>
