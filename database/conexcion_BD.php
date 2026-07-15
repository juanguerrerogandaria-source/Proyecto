<?php

$conexion = mysqli_connect(
    "sql304.infinityfree.com",
    "if0_42417739",
    "x0n6Vzjk0WCj",
    "if0_42417739_proyecto_web"
);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

echo "Conexión exitosa";
?>