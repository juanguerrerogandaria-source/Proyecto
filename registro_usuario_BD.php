<?php
include 'conexcion_BD.php';

$nombre_completo      = $_POST['usuario'];
$correo               = $_POST['email'];
$contrasena           = $_POST['password'];
$confirmar_contrasena = $_POST['confirm_password'];

// Validar que coincidan
if ($contrasena !== $confirmar_contrasena) {
    die("Las contraseñas no coinciden");
}

// Encriptar contraseña
$contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

$query = "INSERT INTO usuarios (usuario, email, password) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "sss", $nombre_completo, $correo, $contrasena_hash);
$ejecutar = mysqli_stmt_execute($stmt);

if ($ejecutar) {
    echo "Usuario registrado correctamente";
} else {
    echo "Error: " . mysqli_error($conexion);
}
?>