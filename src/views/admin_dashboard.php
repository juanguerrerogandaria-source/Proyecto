<?php
require_once __DIR__ . '/../../includes/auth.php';
requerir_rol('admin'); // solo admin o super_admin pueden entrar
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/admin.css">
    <title>Panel Admin - Tuya's Barber</title>
</head>
<body>
    <div class="panel">
        <h1>Panel de Administración</h1>
        <p class="bienvenida">
            Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
            &middot; <span class="badge badge-<?= htmlspecialchars($_SESSION['role']) ?>"><?= htmlspecialchars($_SESSION['role']) ?></span>
        </p>

        <div class="grid-accesos">
            <a class="tarjeta-acceso" href="gestionar_cortes.php">
                <span class="tarjeta-acceso__icono">&#9986;&#65039;</span>
                <h3>Cortes y Servicios</h3>
                <p>Agregá cortes con foto o video y modificá sus precios.</p>
            </a>

            <a class="tarjeta-acceso" href="gestionar_horarios.php">
                <span class="tarjeta-acceso__icono">&#128337;</span>
                <h3>Horarios</h3>
                <p>Configurá los horarios de atención de cada día.</p>
            </a>

            <a class="tarjeta-acceso" href="gestionar_ofertas.php">
                <span class="tarjeta-acceso__icono">&#127991;&#65039;</span>
                <h3>Ofertas</h3>
                <p>Creá promociones y descuentos por tiempo limitado.</p>
            </a>
        </div>

        <?php if (tiene_rol('super_admin')): ?>
            <div class="acciones">
                <a class="btn-link" href="superadmin_dashboard.php">Ir al panel de Super Admin</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
