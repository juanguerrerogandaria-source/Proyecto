<?php
require_once __DIR__ . '/../../includes/auth.php';
requerir_rol('admin'); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=block">
    <link rel="icon" type="image/png" href="../../public/img/tuyas_sin_fondo.png">
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
            <a class="tarjeta-acceso" href="gestionar_reservas.php">
                <span class="material-symbols-outlined tarjeta-acceso__icono">calendar_month</span>
                <h3>Turnos Agendados</h3>
                <p>Mirá el detalle completo de cada reserva y confirmá o cancelá turnos.</p>
            </a>
        </div>

        <?php if (tiene_rol('super_admin')): ?>
            <div class="acciones">
                <a class="btn-link" href="superadmin_dashboard.php">Ir al panel de Super Admin</a>
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="flow-back">&larr; Volver al inicio</a>
    </div>
</body>
</html>
