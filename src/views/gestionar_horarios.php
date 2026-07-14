<?php
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requerir_rol('admin'); // admin y super_admin pueden entrar

$mensaje  = "";
$es_error = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id       = (int) ($_POST["id"] ?? 0);
    $cerrado  = isset($_POST["cerrado"]);
    $apertura = $cerrado ? null : ($_POST["hora_apertura"] ?? null);
    $cierre   = $cerrado ? null : ($_POST["hora_cierre"] ?? null);

    if (update_horario($id, $apertura, $cierre, $cerrado)) {
        $mensaje = "Horario actualizado.";
    } else {
        $mensaje  = "No se pudo actualizar el horario.";
        $es_error = true;
    }
}

$horarios = get_horarios();
$es_super = tiene_rol('super_admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/<?= $es_super ? 'superadmin.css' : 'admin.css' ?>">
    <title>Gestionar Horarios - Tuya's Barber</title>
</head>
<body>
    <div class="panel panel--ancho">
        <a class="btn-volver" href="<?= $es_super ? 'superadmin_dashboard.php' : 'admin_dashboard.php' ?>">&larr; Volver al panel</a>

        <h1>Horarios de Atención</h1>
        <p class="subtitulo">Configurá el horario de apertura y cierre de cada día.</p>

        <?php if ($mensaje): ?>
            <p class="mensaje<?= $es_error ? ' error' : '' ?>"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <div class="tabla-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Cerrado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horarios as $h): ?>
                        <?php $fid = 'form-horario-' . (int) $h['id']; ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($h['dia']) ?></strong></td>
                            <td><input type="time" form="<?= $fid ?>" name="hora_apertura" value="<?= htmlspecialchars((string) $h['hora_apertura']) ?>" <?= $h['cerrado'] ? 'disabled' : '' ?>></td>
                            <td><input type="time" form="<?= $fid ?>" name="hora_cierre" value="<?= htmlspecialchars((string) $h['hora_cierre']) ?>" <?= $h['cerrado'] ? 'disabled' : '' ?>></td>
                            <td class="celda-checkbox">
                                <input type="checkbox" form="<?= $fid ?>" name="cerrado" class="check-cerrado" <?= $h['cerrado'] ? 'checked' : '' ?>>
                            </td>
                            <td><button type="submit" form="<?= $fid ?>" class="btn-chico">Guardar</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php foreach ($horarios as $h): ?>
            <form id="form-horario-<?= (int) $h['id'] ?>" action="gestionar_horarios.php" method="post" class="form-oculto">
                <input type="hidden" name="id" value="<?= (int) $h['id'] ?>">
            </form>
        <?php endforeach; ?>
    </div>

    <script>
        // Deshabilita los inputs de hora de la fila cuando se marca "cerrado"
        document.querySelectorAll('.check-cerrado').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const fila = checkbox.closest('tr');
                fila.querySelectorAll('input[type="time"]').forEach(function (input) {
                    input.disabled = checkbox.checked;
                });
            });
        });
    </script>
</body>
</html>
