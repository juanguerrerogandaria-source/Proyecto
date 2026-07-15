<?php
// Paso 4 del flujo de reserva: comprobante final.
require_once __DIR__ . '/../../includes/auth.php';

$c = $_SESSION['flow_comprobante'] ?? null;

if (!$c) {
    header('Location: reservar.php');
    exit;
}

$emailEnviado = $_SESSION['flow_email_enviado'] ?? false;

// Ya se mostró el comprobante: lo sacamos de la sesión para no repetirlo.
unset($_SESSION['flow_comprobante'], $_SESSION['flow_email_enviado']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/flow.css">
    <title>¡Turno confirmado! - Tuya's Barber</title>
</head>
<body>
    <div class="sparkles" id="sparkles"></div>

    <div class="flow-wrapper flow-wrapper--comprobante">
        <div class="steps">
            <span class="step step--done">1. Corte</span>
            <span class="step step--done">2. Horario</span>
            <span class="step step--done">3. Datos y pago</span>
            <span class="step step--active">4. Listo</span>
        </div>

        <div class="comprobante-final">
            <div class="comprobante-final__membrete">
                <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber" class="comprobante-final__logo">
                <span>Tuya's Barber</span>
            </div>

            <h2>¡Muchas gracias, <?= htmlspecialchars($c['nombre']) ?>!</h2>
            <p class="comprobante-final__sub">Tu turno fue reservado con éxito.</p>

            <div class="comprobante-final__detalle">
                <div>
                    <span>Código de reserva</span>
                    <strong><?= htmlspecialchars($c['codigo']) ?></strong>
                </div>
                <div>
                    <span>Corte</span>
                    <strong><?= htmlspecialchars($c['corte']) ?> (<?= htmlspecialchars(ucfirst($c['categoria'])) ?>)</strong>
                </div>
                <div>
                    <span>Fecha</span>
                    <strong><?= htmlspecialchars($c['fecha_legible']) ?></strong>
                </div>
                <div>
                    <span>Horario</span>
                    <strong><?= htmlspecialchars($c['hora']) ?> hs</strong>
                </div>
                <div>
                    <span>Método de pago</span>
                    <strong><?= htmlspecialchars(ucfirst($c['metodo_pago'])) ?></strong>
                </div>
                <?php if (!empty($c['notas'])): ?>
                    <div>
                        <span>Notas</span>
                        <strong><?= htmlspecialchars($c['notas']) ?></strong>
                    </div>
                <?php endif; ?>
                <div class="comprobante-final__monto">
                    <span>Monto a pagar</span>
                    <strong>$<?= number_format((float) $c['monto'], 0, ',', '.') ?></strong>
                </div>
            </div>

            <?php if ($emailEnviado): ?>
                <p class="comprobante-final__nota">📧 Te enviamos el comprobante a <strong><?= htmlspecialchars($c['email']) ?></strong>. Revisá también la carpeta de spam.</p>
            <?php else: ?>
                <p class="comprobante-final__nota">No pudimos enviarte el email del comprobante, así que sacale una captura o imprimilo desde acá.</p>
            <?php endif; ?>

            <p class="comprobante-final__nota">Te esperamos puntual. Ante cualquier cambio, contactanos con anticipación.</p>

            <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                <button type="button" class="btn-volver" style="cursor:pointer; border:none; font:inherit;" onclick="window.print()">Imprimir</button>
                <a href="index.php" class="btn-volver">Volver al inicio</a>
            </div>
        </div>
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
