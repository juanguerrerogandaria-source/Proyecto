<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/db.php';
requerir_rol('user');

$stmtEmail = $pdo->prepare("SELECT email FROM usuarios WHERE usuario = ?");
$stmtEmail->execute([$_SESSION['usuario']]);
$usuarioActual = $stmtEmail->fetch(PDO::FETCH_ASSOC);

$turnos = [];
if ($usuarioActual) {
    $stmt = $pdo->prepare(
        "SELECT r.codigo, r.fecha, r.hora, r.estado, r.metodo_pago, r.monto,
                c.nombre AS servicio
        FROM reservas r
        LEFT JOIN cortes c ON c.id = r.corte_id
        WHERE r.email_cliente = ?
        ORDER BY r.fecha DESC, r.hora DESC"
    );
    $stmt->execute([$usuarioActual['email']]);
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/user.css">
    <title>Mis Turnos - Tuya's Barber</title>
</head>
<body>
    <div class="panel">
        <h1>Mis Turnos</h1>
    </div>
    <div class="bienvenida">
        Bienvenido, <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>
        &middot; <span class="badge badge-<?= htmlspecialchars($_SESSION['role']) ?>"><?= htmlspecialchars($_SESSION['role']) ?></span>

        <a href="reservar.php" class="btn-primario">Reservar Turno</a>
        <a href="user_dashboard.php" class="btn-secundario">Volver al Panel</a>
    </div>

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
                <tr><td colspan="7">Todavía no tenés turnos reservados.</td></tr>
            <?php else: ?>
                <?php foreach ($turnos as $turno): ?>
                    <tr>
                        <td><?= htmlspecialchars($turno['codigo']) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($turno['fecha']))) ?></td>
                        <td><?= htmlspecialchars(substr($turno['hora'], 0, 5)) ?></td>
                        <td><?= htmlspecialchars($turno['servicio'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($turno['metodo_pago'] ?? '—') ?></td>
                        <td><?= $turno['monto'] !== null ? '$' . htmlspecialchars(number_format($turno['monto'], 2)) : '—' ?></td>
                        <td class="estado-<?= htmlspecialchars($turno['estado']) ?>">
                            <?= htmlspecialchars(ucfirst($turno['estado'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>