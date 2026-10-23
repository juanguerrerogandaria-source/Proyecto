<?php
require_once __DIR__ . '/../../database/conexcion_BD.php';
require_once __DIR__ . '/../../includes/auth.php';

$comprobante = $_SESSION['flow_comprobante'] ?? null;

if (empty($comprobante)) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/flow.css">
    <title>Reserva confirmada - Tuya's Barber</title>
    <link rel="icon" type="image/png" href="../../public/img/tuyas_sin_fondo.png">
    <style>
        .comprobante-resumen {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            align-items: center;
            text-align: center;
        }
        .comprobante-resumen li {
            font-size: 1.05rem;
        }
        .comprobante-acciones {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .btn-descargar-pdf {
            display: inline-block;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #fff;
            font-weight: 600;
            padding: .85rem 2rem;
            border-radius: .75rem;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease;
            box-shadow: 0 4px 14px rgba(124, 58, 237, .35);
        }
        .btn-descargar-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(124, 58, 237, .45);
        }
    </style>
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
            <span class="step step--done">3. Datos y pago</span>
            <span class="step step--active">4. Listo</span>
        </div>

        <h2>¡Reserva <span>recibida</span>!</h2>
        <p class="flow-sub">Código de reserva: <strong><?= htmlspecialchars($comprobante['codigo']) ?></strong></p>

        <ul class="comprobante-resumen">
            <li><strong>Corte:</strong> <?= htmlspecialchars($comprobante['corte']) ?></li>
            <li><strong>Fecha:</strong> <?= htmlspecialchars($comprobante['fecha_legible']) ?></li>
            <li><strong>Hora:</strong> <?= htmlspecialchars($comprobante['hora']) ?> hs</li>
            <li><strong>Método de pago:</strong> <?= htmlspecialchars($comprobante['metodo_pago']) ?></li>
            <li><strong>Monto:</strong> $<?= number_format($comprobante['monto'], 0, ',', '.') ?></li>
        </ul>
        <?php if (isset($comprobante['email_enviado'])): ?>
    <p class="flow-sub">
        <?php if ($comprobante['email_enviado']): ?>
            Te enviamos el comprobante a <strong><?= htmlspecialchars($comprobante['email']) ?></strong>.
        <?php else: ?>
            No pudimos enviarte el email, pero podés descargar el comprobante en PDF.
        <?php endif; ?>
    </p>
<?php endif; ?>

        <div class="comprobante-acciones">
            <a href="comprobante_pdf.php" class="btn-descargar-pdf" target="_blank">Descargar comprobante (PDF)</a>
            <a href="index.php" class="flow-back">&larr; Volver al inicio</a>
        </div>
    </div>
</body>
</html>
