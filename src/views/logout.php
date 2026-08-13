<?php
require_once __DIR__ . '/../../includes/auth.php';

cerrar_sesion_usuario();

header('Location: index.php');
exit;