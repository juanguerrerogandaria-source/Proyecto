<?php
require_once __DIR__ . '/../../includes/auth.php';
requerir_rol('user'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/user.css">
    <title>Panel de Usuario - Tuya's Barber</title>
</head>
<body>
    <div class="panel">
        <h1>Panel de Usuario</h1>
    </div>
    <div class="bienvenida">
        Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
        &middot; <span class="badge badge-<?= htmlspecialchars($_SESSION['role']) ?>"><?= htmlspecialchars($_SESSION['role']) ?></span>
    
        <a href="reservar.php" class="btn-primario">Reservar Turno</a>
        <a href="mis_turnos.php" class="btn-secundario">Mis Turnos</a>
        
    </div>
</body>
</html>