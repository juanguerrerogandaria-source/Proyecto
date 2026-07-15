<?php
// Paso 2 del flujo de reserva: elegir día y horario.
// Los turnos salen de la tabla `horarios` (la que administra el admin)
// y los ya reservados aparecen tachados.
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

if (empty($_SESSION['flow_reserva']['corte_id'])) {
    header('Location: reservar.php');
    exit;
}

$hoy   = date('Y-m-d');
$fecha = $_GET['fecha'] ?? ($_POST['fecha'] ?? ($_SESSION['flow_reserva']['fecha'] ?? $hoy));

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || $fecha < $hoy) {
    $fecha = $hoy;
}

$turnos        = generar_turnos_para_fecha($fecha);
$horasOcupadas = obtener_horas_ocupadas($fecha);
$horarioDia    = get_horario_para_fecha($fecha);
$cerrado       = empty($turnos);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $horaElegida = $_POST['hora'] ?? '';

    if ($fecha < $hoy) {
        $error = 'Elegí una fecha válida (no puede ser en el pasado).';
    } elseif (!in_array($horaElegida, $turnos, true)) {
        $error = 'Elegí un horario de la lista.';
    } elseif (in_array($horaElegida, $horasOcupadas, true)) {
        $error = 'Ese horario ya fue reservado por otra persona. Elegí otro.';
    } else {
        $_SESSION['flow_reserva']['fecha'] = $fecha;
        $_SESSION['flow_reserva']['hora']  = $horaElegida;
        header('Location: pago.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/flow.css">
    <title>Elegí tu horario - Tuya's Barber</title>
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
            <span class="step step--active">2. Horario</span>
            <span class="step">3. Datos y pago</span>
            <span class="step">4. Listo</span>
        </div>

        <h2>Elegí día y <span>horario</span></h2>
        <p class="flow-sub">
            Corte elegido: <strong><?= htmlspecialchars($_SESSION['flow_reserva']['corte_nombre']) ?></strong>
            &middot; $<?= number_format((float) $_SESSION['flow_reserva']['monto'], 0, ',', '.') ?>
        </p>

        <?php if ($error): ?>
            <p class="flow-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="get" class="flow-form" id="form-fecha">
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars($fecha) ?>" min="<?= htmlspecialchars($hoy) ?>">
        </form>

        <?php if ($cerrado): ?>
            <p class="flow-error">
                Ese día (<?= htmlspecialchars($horarioDia['dia'] ?? '') ?>) el local está cerrado. Probá con otra fecha.
            </p>
        <?php else: ?>
            <form method="post" class="flow-form">
                <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">

                <label>Horario disponible (<?= htmlspecialchars($horarioDia['dia']) ?>: <?= substr($horarioDia['hora_apertura'], 0, 5) ?> a <?= substr($horarioDia['hora_cierre'], 0, 5) ?> hs)</label>
                <div class="horarios-grid">
                    <?php foreach ($turnos as $h): ?>
                        <?php $ocupado = in_array($h, $horasOcupadas, true); ?>
                        <label class="horario-opcion <?= $ocupado ? 'horario-opcion--ocupado' : '' ?>">
                            <input type="radio" name="hora" value="<?= htmlspecialchars($h) ?>" <?= $ocupado ? 'disabled' : '' ?> required>
                            <span><?= htmlspecialchars($h) ?> hs</span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit">Continuar</button>
            </form>
        <?php endif; ?>

        <a href="reservar.php" class="flow-back">&larr; Cambiar corte</a>
    </div>

    <script>
        document.getElementById('fecha').addEventListener('change', function () {
            document.getElementById('form-fecha').submit();
        });

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
