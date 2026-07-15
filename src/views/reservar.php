<?php
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$mensaje  = "";
$reserva_ok = false;
$comprobante = null;

$cortes = get_all_cortes();
$cortes_hombre = array_filter($cortes, fn($c) => $c['categoria'] === 'hombre');
$cortes_mujer  = array_filter($cortes, fn($c) => $c['categoria'] === 'mujer');

$corte_preseleccionado = (int) ($_GET['corte_id'] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_cliente   = trim($_POST["nombre_cliente"] ?? "");
    $email_cliente    = trim($_POST["email_cliente"] ?? "");
    $telefono_cliente = trim($_POST["telefono_cliente"] ?? "");
    $corte_id         = (int) ($_POST["corte_id"] ?? 0);
    $fecha            = $_POST["fecha"] ?? "";
    $hora             = $_POST["hora"] ?? "";
    $notas            = trim($_POST["notas"] ?? "");

    $corte_elegido = null;
    foreach ($cortes as $c) {
        if ((int) $c['id'] === $corte_id) {
            $corte_elegido = $c;
            break;
        }
    }

    if (empty($nombre_cliente) || empty($email_cliente) || empty($fecha) || empty($hora)) {
        $mensaje = "Completá todos los campos obligatorios.";
    } elseif (!filter_var($email_cliente, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El correo electrónico no es válido.";
    } elseif ($corte_elegido === null) {
        $mensaje = "Elegí un corte válido de la lista.";
    } elseif (strtotime($fecha) < strtotime(date('Y-m-d'))) {
        $mensaje = "La fecha no puede ser anterior a hoy.";
    } else {
        $codigo = 'TB-' . strtoupper(bin2hex(random_bytes(3)));

        if (create_reserva($codigo, $nombre_cliente, $email_cliente, $telefono_cliente ?: null, $corte_id, $fecha, $hora, $notas ?: null)) {
            $reserva_ok = true;
            $comprobante = [
                'codigo'   => $codigo,
                'nombre'   => $nombre_cliente,
                'email'    => $email_cliente,
                'telefono' => $telefono_cliente,
                'corte'    => $corte_elegido['nombre'],
                'categoria'=> $corte_elegido['categoria'],
                'precio'   => $corte_elegido['precio'],
                'fecha'    => $fecha,
                'hora'     => $hora,
                'notas'    => $notas,
            ];

            // Intento de envío de comprobante por correo (best-effort).
            // En XAMPP por defecto mail() no tiene un servidor SMTP configurado,
            // así que esto puede no llegar a destino sin configurar sendmail/SMTP en php.ini.
            $asunto = "Comprobante de reserva - Tuya's Barber";
            $cuerpo  = "Hola $nombre_cliente,\n\n";
            $cuerpo .= "Tu reserva fue confirmada. Estos son los datos:\n\n";
            $cuerpo .= "Código de reserva: $codigo\n";
            $cuerpo .= "Corte: {$corte_elegido['nombre']} (" . ucfirst($corte_elegido['categoria']) . ")\n";
            $cuerpo .= "Precio: \${$corte_elegido['precio']}\n";
            $cuerpo .= "Fecha: $fecha\n";
            $cuerpo .= "Hora: $hora\n";
            if (!empty($notas)) {
                $cuerpo .= "Notas: $notas\n";
            }
            $cuerpo .= "\nTe esperamos en Tuya's Barber. ¡Gracias por reservar!";

            $headers = "From: no-responder@tuyasbarber.local\r\n";
            @mail($email_cliente, $asunto, $cuerpo, $headers);
        } else {
            $mensaje = "No se pudo generar la reserva, probá de nuevo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/reservar.css">
    <title>Reservar turno - Tuya's Barber</title>
</head>
<body>
    <div class="tarjeta-reserva">
        <?php if ($reserva_ok && $comprobante): ?>
            <h1>¡Reserva confirmada!</h1>
            <p class="subtitulo">Guardá o imprimí tu comprobante.</p>

            <div class="comprobante">
                <p class="comprobante__ok">&#10003; Turno reservado con éxito</p>
                <p class="comprobante__codigo"><?= htmlspecialchars($comprobante['codigo']) ?></p>

                <div class="comprobante__fila"><span>Cliente</span><span><?= htmlspecialchars($comprobante['nombre']) ?></span></div>
                <div class="comprobante__fila"><span>Email</span><span><?= htmlspecialchars($comprobante['email']) ?></span></div>
                <?php if (!empty($comprobante['telefono'])): ?>
                    <div class="comprobante__fila"><span>Teléfono</span><span><?= htmlspecialchars($comprobante['telefono']) ?></span></div>
                <?php endif; ?>
                <div class="comprobante__fila"><span>Corte</span><span><?= htmlspecialchars($comprobante['corte']) ?> (<?= htmlspecialchars(ucfirst($comprobante['categoria'])) ?>)</span></div>
                <div class="comprobante__fila"><span>Precio</span><span>$<?= htmlspecialchars((string) $comprobante['precio']) ?></span></div>
                <div class="comprobante__fila"><span>Fecha</span><span><?= htmlspecialchars($comprobante['fecha']) ?></span></div>
                <div class="comprobante__fila"><span>Hora</span><span><?= htmlspecialchars($comprobante['hora']) ?></span></div>
                <?php if (!empty($comprobante['notas'])): ?>
                    <div class="comprobante__fila"><span>Notas</span><span><?= htmlspecialchars($comprobante['notas']) ?></span></div>
                <?php endif; ?>
            </div>

            <p class="aviso-email">Te enviamos este comprobante a tu correo (si el servidor de mail está configurado).</p>

            <div class="comprobante__acciones">
                <button type="button" class="btn-secundario" onclick="window.print()">Imprimir</button>
                <a class="volver" href="reservar.php" style="flex:1; align-self:center;">Hacer otra reserva</a>
            </div>
            <a class="volver" href="index.php">&larr; Volver al inicio</a>
        <?php else: ?>
            <h1>Reservá tu turno</h1>
            <p class="subtitulo">Elegí tu corte, día y horario preferido.</p>

            <?php if ($mensaje): ?>
                <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
            <?php endif; ?>

            <form action="reservar.php" method="post">
                <div class="grid-2">
                    <div>
                        <label for="nombre_cliente">Nombre completo</label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" placeholder="Tu nombre" required>
                    </div>
                    <div>
                        <label for="telefono_cliente">Teléfono (opcional)</label>
                        <input type="text" id="telefono_cliente" name="telefono_cliente" placeholder="Tu teléfono">
                    </div>
                </div>

                <label for="email_cliente">Correo electrónico</label>
                <input type="email" id="email_cliente" name="email_cliente" placeholder="correo@ejemplo.com" required>

                <label for="corte_id">Corte</label>
                <select id="corte_id" name="corte_id" required>
                    <option value="">Seleccioná un corte...</option>
                    <?php if (!empty($cortes_hombre)): ?>
                        <optgroup label="Hombre">
                            <?php foreach ($cortes_hombre as $c): ?>
                                <option value="<?= (int) $c['id'] ?>" <?= $corte_preseleccionado === (int) $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?> - $<?= htmlspecialchars((string) $c['precio']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if (!empty($cortes_mujer)): ?>
                        <optgroup label="Mujer">
                            <?php foreach ($cortes_mujer as $c): ?>
                                <option value="<?= (int) $c['id'] ?>" <?= $corte_preseleccionado === (int) $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?> - $<?= htmlspecialchars((string) $c['precio']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>

                <div class="grid-2">
                    <div>
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label for="hora">Hora</label>
                        <input type="time" id="hora" name="hora" required>
                    </div>
                </div>

                <label for="notas">Notas (opcional)</label>
                <textarea id="notas" name="notas" rows="2" placeholder="Alguna aclaración para tu turno..."></textarea>

                <button type="submit">Confirmar reserva</button>
            </form>

            <a class="volver" href="index.php">&larr; Volver al inicio</a>
        <?php endif; ?>
    </div>
</body>
</html>
