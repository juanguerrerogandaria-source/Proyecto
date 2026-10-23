<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexcion_BD.php';
requerir_rol('user');

$turnos = [];

// Email del usuario logueado
$stmtEmail = mysqli_prepare($conexion, "SELECT email FROM usuarios WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmtEmail, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmtEmail);
$usuarioActual = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtEmail));

if ($usuarioActual) {
    $stmt = mysqli_prepare(
        $conexion,
        "SELECT r.codigo, r.fecha, r.hora, r.estado, r.metodo_pago, r.monto,
                c.nombre AS servicio
         FROM reservas r
         LEFT JOIN cortes c ON c.id = r.corte_id
         WHERE r.email_cliente = ?
         ORDER BY r.fecha DESC, r.hora DESC"
    );
    mysqli_stmt_bind_param($stmt, "s", $usuarioActual['email']);
    mysqli_stmt_execute($stmt);
    $turnos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/user.css">
    <title>Mis Turnos - Tuya's Barber</title>
    <link rel="icon" type="image/png" href="../../public/img/tuyas_sin_fondo.png">
</head>
<body>
    <div class="panel panel--ancho">
        <h1>Mis Turnos</h1>

        <div class="bienvenida">
            Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
            &middot; <span class="badge badge-<?= htmlspecialchars($_SESSION['role']) ?>"><?= htmlspecialchars($_SESSION['role']) ?></span>
        </div>

        <div class="acciones">
            <a href="reservar.php" class="btn-primario">Reservar turno</a>
            <a href="index.php" class="btn-link">Volver al inicio</a>
        </div>

        <div class="tabla-wrapper">
            <table class="historial">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Servicio</th>
                        <th>Método de pago</th>
                        <th>Monto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($turnos) === 0): ?>
                        <tr><td colspan="7" class="vacio">Todavía no tenés turnos reservados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($turnos as $turno): ?>
                            <tr>
                                <td><?= htmlspecialchars($turno['codigo']) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($turno['fecha']))) ?></td>
                                <td><?= htmlspecialchars(substr($turno['hora'], 0, 5)) ?></td>
                                <td><?= htmlspecialchars($turno['servicio'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($turno['metodo_pago'] ?? '—') ?></td>
                                <td><?= $turno['monto'] !== null ? '$' . htmlspecialchars(number_format((float) $turno['monto'], 2)) : '—' ?></td>
                                <td class="estado-<?= htmlspecialchars($turno['estado']) ?>">
                                    <?= htmlspecialchars(ucfirst($turno['estado'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>