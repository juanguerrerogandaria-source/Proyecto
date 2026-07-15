<?php
// Paso 3 del flujo de reserva: datos del cliente y método de pago.
// Acá se guarda la reserva en la base y se envía el comprobante por email.
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mailer.php';

$flow = $_SESSION['flow_reserva'] ?? null;

if (empty($flow['corte_id']) || empty($flow['fecha']) || empty($flow['hora'])) {
    header('Location: reservar.php');
    exit;
}

// Si el usuario está logueado, precargamos sus datos para que no tipee de más
$nombrePrecargado = $_SESSION['usuario'] ?? '';
$emailPrecargado  = esta_logueado() ? (get_email_usuario((int) $_SESSION['user_id']) ?? '') : '';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre_cliente'] ?? '');
    $email    = trim($_POST['email_cliente'] ?? '');
    $telefono = trim($_POST['telefono_cliente'] ?? '');
    $notas    = trim($_POST['notas'] ?? '');
    $metodo   = $_POST['metodo_pago'] ?? '';

    if ($nombre === '' || $email === '') {
        $error = 'Completá tu nombre y tu email (ahí te mandamos el comprobante).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (!in_array($metodo, ['transferencia', 'efectivo'], true)) {
        $error = 'Elegí un método de pago.';
    } else {
        $codigo = 'TB-' . strtoupper(bin2hex(random_bytes(3)));

        $creada = crear_reserva_flow(
            $codigo,
            $nombre,
            $email,
            $telefono !== '' ? $telefono : null,
            (int) $flow['corte_id'],
            $flow['fecha'],
            $flow['hora'],
            $notas !== '' ? $notas : null,
            $metodo,
            (float) $flow['monto']
        );

        if ($creada) {
            $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
            [$anio, $mes, $dia] = explode('-', $flow['fecha']);
            $fechaLegible = (int) $dia . ' de ' . $meses[(int) $mes - 1] . ' de ' . $anio;

            $comprobante = [
                'codigo'        => $codigo,
                'nombre'        => $nombre,
                'email'         => $email,
                'corte'         => $flow['corte_nombre'],
                'categoria'     => $flow['corte_categoria'],
                'fecha_legible' => $fechaLegible,
                'hora'          => $flow['hora'],
                'metodo_pago'   => $metodo,
                'monto'         => (float) $flow['monto'],
                'notas'         => $notas,
            ];

            // Envío real del comprobante por email (SMTP propio, ver includes/mailer.php)
            $emailEnviado = enviar_comprobante_reserva($comprobante);

            $_SESSION['flow_comprobante']   = $comprobante;
            $_SESSION['flow_email_enviado'] = $emailEnviado;
            unset($_SESSION['flow_reserva']);

            header('Location: comprobante.php');
            exit;
        }

        $error = 'Ese horario se acaba de ocupar. Volvé al paso anterior y elegí otro, por favor.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/flow.css">
    <title>Datos y pago - Tuya's Barber</title>
</head>
<body>
    <div class="sparkles" id="sparkles"></div>

    <div class="flow-wrapper">
        <a href="index.php" class="flow-logo">
            <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber">
            <span>Tuya's Barber</span>
        </a>

        <div class="steps">
            <span class="step step--done">1. Corte</span>
            <span class="step step--done">2. Horario</span>
            <span class="step step--active">3. Datos y pago</span>
            <span class="step">4. Listo</span>
        </div>

        <h2>Tus datos y <span>pago</span></h2>
        <p class="flow-sub">
            <strong><?= htmlspecialchars($flow['corte_nombre']) ?></strong> &middot;
            <?= htmlspecialchars($flow['fecha']) ?> a las <?= htmlspecialchars($flow['hora']) ?> hs &middot;
            $<?= number_format((float) $flow['monto'], 0, ',', '.') ?>
        </p>

        <?php if ($error): ?>
            <p class="flow-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" class="flow-form">
            <label for="nombre_cliente">Nombre completo</label>
            <input type="text" id="nombre_cliente" name="nombre_cliente" placeholder="Tu nombre"
                   value="<?= htmlspecialchars($_POST['nombre_cliente'] ?? $nombrePrecargado) ?>" required>

            <label for="email_cliente">Correo electrónico (ahí llega el comprobante)</label>
            <input type="email" id="email_cliente" name="email_cliente" placeholder="correo@ejemplo.com"
                   value="<?= htmlspecialchars($_POST['email_cliente'] ?? $emailPrecargado) ?>" required>

            <label for="telefono_cliente">Teléfono (opcional)</label>
            <input type="text" id="telefono_cliente" name="telefono_cliente" placeholder="Tu teléfono"
                   value="<?= htmlspecialchars($_POST['telefono_cliente'] ?? '') ?>">

            <label for="notas">Notas (opcional)</label>
            <input type="text" id="notas" name="notas" placeholder="Alguna aclaración para tu turno..."
                   value="<?= htmlspecialchars($_POST['notas'] ?? '') ?>">

            <label>¿Cómo vas a pagar?</label>
            <div class="pago-grid">
                <button type="submit" name="metodo_pago" value="transferencia" class="pago-opcion">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M17 3l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 7H9a4 4 0 0 0-4 4v1" stroke-linecap="round"/><path d="M7 21l-4-4 4-4" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 17h12a4 4 0 0 0 4-4v-1" stroke-linecap="round"/></svg>
                    <span>Transferencia</span>
                </button>
                <button type="submit" name="metodo_pago" value="efectivo" class="pago-opcion">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Efectivo</span>
                </button>
            </div>
        </form>

        <a href="horario.php" class="flow-back">&larr; Cambiar horario</a>
    </div>

    <script>
        const container = document.getElementById('sparkles');
        for (let i = 0; i < 40; i++) {
            const s = document.createElement('div');
            s.className = 'sparkle';
            s.style.left = Math.random() * 100 + '%';
            s.style.top = Math.random() * 100 + '%';
            s.style.animationDelay = (Math.random() * 3) + 's';
            s.style.animationDuration = (2 + Math.random() * 3) + 's';
            container.appendChild(s);
        }
    </script>
</body>
</html>
