<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../database/.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/reservas.php';

requerir_rol('admin'); // admin y super_admin pueden entrar

$mensaje  = "";
$es_error = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id     = (int) ($_POST["id"] ?? 0);
    $estado = $_POST["estado"] ?? "";

    if (update_estado_reserva($id, $estado)) {
        $mensaje = "Reserva actualizada.";
    } else {
        $mensaje  = "No se pudo actualizar la reserva.";
        $es_error = true;
    }
}

$reservas = get_all_reservas();
$es_super = tiene_rol('super_admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/<?= $es_super ? 'superadmin.css' : 'admin.css' ?>">
    <title>Gestionar Reservas - Tuya's Barber</title>
    <link rel="icon" type="image/png" href="../../public/img/tuyasbarber.jpeg">
</head>
<body>
    <div class="panel panel--ancho">
        <a class="btn-volver" href="<?= $es_super ? 'superadmin_dashboard.php' : 'admin_dashboard.php' ?>">&larr; Volver al panel</a>

        <h1>Turnos Agendados</h1>
        <p class="subtitulo">Detalle completo de cada reserva hecha por los clientes.</p>

        <?php if ($mensaje): ?>
            <p class="mensaje<?= $es_error ? ' error' : '' ?>"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <h2 class="titulo-lista">Reservas (<?= count($reservas) ?>)</h2>

        <div class="tabla-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Corte</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Pago</th>
                        <th>Monto</th>
                        <th>Notas</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservas)): ?>
                        <tr><td colspan="11" class="vacio">Todavía no hay reservas cargadas.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($reservas as $r): ?>
                        <?php $fid = 'form-reserva-' . (int) $r['id']; ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['codigo']) ?></strong></td>
                            <td><?= htmlspecialchars($r['nombre_cliente']) ?></td>
                            <td>
                                <?= htmlspecialchars($r['email_cliente']) ?>
                                <?php if (!empty($r['telefono_cliente'])): ?>
                                    <br><small><?= htmlspecialchars($r['telefono_cliente']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($r['corte_nombre'] ?? 'Corte eliminado') ?>
                                <?php if (!empty($r['corte_categoria'])): ?>
                                    <br><span class="badge <?= $r['corte_categoria'] === 'hombre' ? 'badge-admin' : 'badge-super_admin' ?>">
                                        <?= $r['corte_categoria'] === 'hombre' ? 'Hombre' : 'Mujer' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($r['fecha']) ?></td>
                            <td><?= htmlspecialchars(substr($r['hora'], 0, 5)) ?> hs</td>
                            <td><?= htmlspecialchars(ucfirst($r['metodo_pago'])) ?></td>
                            <td>$<?= number_format((float) $r['monto'], 0, ',', '.') ?></td>
                            <td><?= !empty($r['notas']) ? htmlspecialchars($r['notas']) : '—' ?></td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($r['estado']) ?>">
                                    <?= htmlspecialchars(ucfirst($r['estado'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($r['estado'] !== 'confirmada'): ?>
                                    <form id="<?= $fid ?>-conf" action="gestionar_reservas.php" method="post" class="form-oculto">
                                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                        <input type="hidden" name="estado" value="confirmada">
                                    </form>
                                    <button type="submit" form="<?= $fid ?>-conf" class="btn-chico">Confirmar</button>
                                <?php endif; ?>
                                <?php if ($r['estado'] !== 'cancelada'): ?>
                                    <form id="<?= $fid ?>-canc" action="gestionar_reservas.php" method="post" class="form-oculto" onsubmit="return confirm('¿Cancelar esta reserva?');">
                                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                        <input type="hidden" name="estado" value="cancelada">
                                    </form>
                                    <button type="submit" form="<?= $fid ?>-canc" class="btn-eliminar">Cancelar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>