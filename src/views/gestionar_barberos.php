<?php
require_once __DIR__ . '/../../database/conexcion_BD.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/barberos.php';

requerir_rol('admin'); // admin y super_admin pueden entrar

$mensaje  = "";
$es_error = false;

$dias      = nombres_dias_semana();
$ordenDias = [1, 2, 3, 4, 5, 6, 0]; // se muestra de lunes a domingo

$accion  = $_POST["accion"] ?? "";
$abrirId = 0; // barbero cuyo horario queda desplegado después de guardar

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        if ($accion === "crear") {
            $nombre = trim($_POST["nombre"] ?? "");

            if ($nombre === "") {
                $mensaje  = "El nombre del barbero es obligatorio.";
                $es_error = true;
            } elseif (mb_strlen($nombre) > 80) {
                $mensaje  = "El nombre no puede superar los 80 caracteres.";
                $es_error = true;
            } else {
                $foto = guardar_foto_barbero($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE]);

                if ($foto['error'] !== null) {
                    $mensaje  = $foto['error'];
                    $es_error = true;
                } else {
                    $nuevoId = create_barbero($nombre, $foto['path']);

                    if ($nuevoId === false) {
                        borrar_foto_barbero($foto['path']);
                        $mensaje  = "No se pudo guardar el barbero.";
                        $es_error = true;
                    } else {
                        // Arranca con el horario general del local; después se ajusta
                        copiar_horario_general_a_barbero($nuevoId);
                        $abrirId = $nuevoId;
                        $mensaje = "Barbero \"$nombre\" agregado. Revisá su horario semanal y ajustalo si hace falta.";
                    }
                }
            }
        } elseif ($accion === "editar") {
            $id      = (int) ($_POST["id"] ?? 0);
            $nombre  = trim($_POST["nombre"] ?? "");
            $barbero = get_barbero($id);

            if (!$barbero) {
                $mensaje  = "El barbero no existe.";
                $es_error = true;
            } elseif ($nombre === "") {
                $mensaje  = "El nombre del barbero es obligatorio.";
                $es_error = true;
            } elseif (mb_strlen($nombre) > 80) {
                $mensaje  = "El nombre no puede superar los 80 caracteres.";
                $es_error = true;
            } else {
                $foto = guardar_foto_barbero($_FILES['foto'] ?? ['error' => UPLOAD_ERR_NO_FILE]);

                if ($foto['error'] !== null) {
                    $mensaje  = $foto['error'];
                    $es_error = true;
                } elseif (update_barbero($id, $nombre, $foto['path'])) {
                    if ($foto['path'] !== null) {
                        borrar_foto_barbero($barbero['foto']); // la foto anterior ya no se usa
                    }
                    $mensaje = "Datos de \"$nombre\" actualizados.";
                } else {
                    borrar_foto_barbero($foto['path']);
                    $mensaje  = "No se pudieron guardar los cambios.";
                    $es_error = true;
                }
            }
        } elseif ($accion === "activar" || $accion === "desactivar") {
            $id      = (int) ($_POST["id"] ?? 0);
            $barbero = get_barbero($id);

            if (!$barbero) {
                $mensaje  = "El barbero no existe.";
                $es_error = true;
            } elseif ($accion === "desactivar" && (int) $barbero['activo'] === 1 && contar_barberos_activos() <= 1) {
                $mensaje  = "Tiene que quedar al menos un barbero activo para que se puedan reservar turnos.";
                $es_error = true;
            } elseif (set_barbero_activo($id, $accion === "activar")) {
                $mensaje = $accion === "activar"
                    ? "\"" . $barbero['nombre'] . "\" ahora aparece para reservar."
                    : "\"" . $barbero['nombre'] . "\" ya no aparece para reservar.";
            } else {
                $mensaje  = "No se pudo cambiar el estado del barbero.";
                $es_error = true;
            }
        } elseif ($accion === "horario") {
            $id      = (int) ($_POST["id"] ?? 0);
            $barbero = get_barbero($id);
            $abrirId = $id;

            if (!$barbero) {
                $mensaje  = "El barbero no existe.";
                $es_error = true;
            } else {
                $enviado     = is_array($_POST['h'] ?? null) ? $_POST['h'] : [];
                $paraGuardar = [];
                $errorHorario = null;

                foreach ($ordenDias as $d) {
                    $fila    = is_array($enviado[$d] ?? null) ? $enviado[$d] : [];
                    $cerrado = isset($fila['cerrado']);

                    if ($cerrado) {
                        $paraGuardar[$d] = ['apertura' => null, 'cierre' => null, 'cerrado' => true];
                        continue;
                    }

                    $apertura = trim((string) ($fila['apertura'] ?? ''));
                    $cierre   = trim((string) ($fila['cierre'] ?? ''));

                    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $apertura) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $cierre)) {
                        $errorHorario = "Completá la apertura y el cierre del {$dias[$d]}, o marcalo como cerrado.";
                        break;
                    }

                    $apertura = substr($apertura, 0, 5);
                    $cierre   = substr($cierre, 0, 5);

                    if ($apertura >= $cierre) {
                        $errorHorario = "El {$dias[$d]} la apertura tiene que ser antes del cierre.";
                        break;
                    }

                    $paraGuardar[$d] = ['apertura' => $apertura, 'cierre' => $cierre, 'cerrado' => false];
                }

                if ($errorHorario !== null) {
                    $mensaje  = $errorHorario;
                    $es_error = true;
                } elseif (guardar_horario_barbero($id, $paraGuardar)) {
                    $mensaje = "Horario de \"" . $barbero['nombre'] . "\" actualizado.";
                } else {
                    $mensaje  = "No se pudo guardar el horario.";
                    $es_error = true;
                }
            }
        }
    } catch (Throwable $e) {
        error_log('gestionar_barberos: ' . $e->getMessage());
        $mensaje  = "No se pudo completar la acción. Verificá que la migración database/migration_barberos.sql esté importada.";
        $es_error = true;
    }
}

try {
    $barberos = get_barberos();
} catch (Throwable $e) {
    error_log('gestionar_barberos: ' . $e->getMessage());
    $barberos = [];
    $mensaje  = "No se pudieron cargar los barberos. Verificá que la migración database/migration_barberos.sql esté importada.";
    $es_error = true;
}

$es_super = tiene_rol('super_admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/<?= $es_super ? 'superadmin.css' : 'admin.css' ?>">
    <title>Gestionar Barberos - Tuya's Barber</title>
    <script src="../../public/js/gestionar_barberos.js" defer></script>
    <link rel="icon" type="image/png" href="../../public/img/tuyas_sin_fondo.png">
</head>
<body>
    <div class="panel panel--ancho">
        <a class="btn-volver" href="<?= $es_super ? 'superadmin_dashboard.php' : 'admin_dashboard.php' ?>">&larr; Volver al panel</a>

        <h1>Barberos</h1>
        <p class="subtitulo">Agregá barberos con su foto y configurá el horario de cada uno. Los clientes eligen con quién cortarse al reservar.</p>

        <?php if ($mensaje): ?>
            <p class="mensaje<?= $es_error ? ' error' : '' ?>"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <div class="card-form">
            <h2>Agregar nuevo barbero</h2>
            <form action="gestionar_barberos.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear">

                <div class="grid-2">
                    <div class="campo">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" maxlength="80" placeholder="Ej: Martín" required>
                    </div>
                    <div class="campo">
                        <label for="foto">Foto (JPG, PNG o WebP, hasta 5MB)</label>
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                    </div>
                </div>

                <button type="submit" class="btn-primario">Agregar barbero</button>
            </form>
        </div>

        <h2 class="titulo-lista">Barberos cargados (<?= count($barberos) ?>)</h2>

        <div class="lista-barberos">
            <?php if (empty($barberos)): ?>
                <p class="vacio">Todavía no hay barberos cargados.</p>
            <?php endif; ?>

            <?php foreach ($barberos as $b): ?>
                <?php
                    $bid      = (int) $b['id'];
                    $activo   = (int) $b['activo'] === 1;
                    $proximos = (int) $b['proximos'];
                    $horario  = get_horario_barbero($bid);

                    $textoConfirmar = $proximos > 0
                        ? "{$b['nombre']} tiene $proximos turno(s) próximo(s). Se mantienen, pero nadie más va a poder reservar con este barbero. ¿Desactivarlo?"
                        : "¿Desactivar a {$b['nombre']}? Dejará de aparecer para reservar.";
                ?>
                <div class="tarjeta-barbero<?= $activo ? '' : ' tarjeta-barbero--inactivo' ?>">
                    <?php if (!empty($b['foto'])): ?>
                        <img class="tarjeta-barbero__foto" src="../../public/<?= htmlspecialchars($b['foto']) ?>" alt="<?= htmlspecialchars($b['nombre']) ?>">
                    <?php else: ?>
                        <div class="tarjeta-barbero__foto tarjeta-barbero__foto--inicial"><?= htmlspecialchars(inicial_barbero($b['nombre'])) ?></div>
                    <?php endif; ?>

                    <div class="tarjeta-barbero__body">
                        <div class="tarjeta-barbero__titulo">
                            <h3><?= htmlspecialchars($b['nombre']) ?></h3>
                            <span class="badge <?= $activo ? 'badge-activo' : 'badge-inactivo' ?>"><?= $activo ? 'Activo' : 'Inactivo' ?></span>
                            <span class="tarjeta-barbero__proximos"><?= $proximos ?> turno(s) próximo(s)</span>
                        </div>

                        <form id="form-editar-<?= $bid ?>" action="gestionar_barberos.php" method="post" enctype="multipart/form-data" class="form-barbero">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="id" value="<?= $bid ?>">

                            <div class="campo">
                                <label for="nombre-<?= $bid ?>">Nombre</label>
                                <input type="text" id="nombre-<?= $bid ?>" name="nombre" maxlength="80" value="<?= htmlspecialchars($b['nombre']) ?>" required>
                            </div>
                            <div class="campo">
                                <label for="foto-<?= $bid ?>">Cambiar foto</label>
                                <input type="file" id="foto-<?= $bid ?>" name="foto" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </form>

                        <div class="barbero-acciones">
                            <button type="submit" form="form-editar-<?= $bid ?>" class="btn-chico">Guardar cambios</button>

                            <form action="gestionar_barberos.php" method="post"<?= $activo ? ' data-confirmar="' . htmlspecialchars($textoConfirmar) . '"' : '' ?>>
                                <input type="hidden" name="accion" value="<?= $activo ? 'desactivar' : 'activar' ?>">
                                <input type="hidden" name="id" value="<?= $bid ?>">
                                <button type="submit" class="<?= $activo ? 'btn-eliminar' : 'btn-chico' ?>"><?= $activo ? 'Desactivar' : 'Activar' ?></button>
                            </form>
                        </div>

                        <details class="barbero-horario"<?= $abrirId === $bid ? ' open' : '' ?>>
                            <summary>Horario semanal</summary>

                            <form action="gestionar_barberos.php" method="post">
                                <input type="hidden" name="accion" value="horario">
                                <input type="hidden" name="id" value="<?= $bid ?>">

                                <div class="tabla-wrapper">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Día</th>
                                                <th>Apertura</th>
                                                <th>Cierre</th>
                                                <th>Cerrado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ordenDias as $d): ?>
                                                <?php
                                                    $h = $horario[$d];
                                                    $c = (int) $h['cerrado'] === 1;
                                                ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($dias[$d]) ?></strong></td>
                                                    <td><input type="time" name="h[<?= $d ?>][apertura]" value="<?= htmlspecialchars(substr((string) $h['hora_apertura'], 0, 5)) ?>"<?= $c ? ' disabled' : '' ?>></td>
                                                    <td><input type="time" name="h[<?= $d ?>][cierre]" value="<?= htmlspecialchars(substr((string) $h['hora_cierre'], 0, 5)) ?>"<?= $c ? ' disabled' : '' ?>></td>
                                                    <td class="celda-checkbox">
                                                        <input type="checkbox" name="h[<?= $d ?>][cerrado]" class="check-cerrado"<?= $c ? ' checked' : '' ?>>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <button type="submit" class="btn-chico">Guardar horario</button>
                            </form>
                        </details>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
