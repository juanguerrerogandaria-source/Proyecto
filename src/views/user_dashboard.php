<?php
require_once __DIR__ . '/../../includes/auth.php';
requerir_rol('admin'); // solo admin o super_admin pueden entrar
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
</body>
</html>