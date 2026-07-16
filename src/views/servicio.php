<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/db.php';

if (!esta_logueado()) {
    header('Location: loginbarber.php');
    exit;
}

if (empty($_SESSION['reserva']['fecha']) || empty($_SESSION['reserva']['hora']) || empty($_SESSION['reserva']['metodo_pago'])) {
    header('Location: reservar.php');
    exit;
}

$servicios = [
    'corte'               => ['nombre' => 'Corte',                       'precio' => 450],
    'corte_barba'         => ['nombre' => 'Corte + Barba',                'precio' => 650],
    'corte_color'         => ['nombre' => 'Corte + Color',                'precio' => 900],
    'corte_color_barba'   => ['nombre' => 'Corte + Color + Barba',        'precio' => 1100],
    'bano_crema'          => ['nombre' => 'Baño de crema',                'precio' => 350],
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave = $_POST['servicio'] ?? '';

    if (!isset($servicios[$clave])) {
        $error = 'Elegí un servicio de la lista.';
    } else {
        $reserva = $_SESSION['reserva'];

        $creada = crear_reserva(
            (int) $_SESSION['user_id'],
            $servicios[$clave]['nombre'],
            $reserva['fecha'],
            $reserva['hora'],
            $reserva['metodo_pago'],
            (float) $servicios[$clave]['precio']
        );

        if ($creada) {
            $_SESSION['reserva']['servicio'] = $servicios[$clave]['nombre'];
            $_SESSION['reserva']['monto']    = $servicios[$clave]['precio'];
            header('Location: comprobante.php');
            exit;
        }

        $error = 'Ese horario se acaba de ocupar. Elegí otro, por favor.';
        unset($_SESSION['reserva']['metodo_pago']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/flow.css">
    <title>Elegí tu servicio - Tuya's Barber</title>
</head>
<body>
    <div class="sparkles" id="sparkles"></div>

    <div class="flow-wrapper">
        <a href="pagina_principal.php" class="flow-logo">
            <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber">
            <span>Tuya's Barber</span>
        </a>

        <div class="steps">
            <span class="step step--done">1. Horario</span>
            <span class="step step--done">2. Pago</span>
            <span class="step step--active">3. Servicio</span>
            <span class="step">4. Listo</span>
        </div>

        <h2>Elegí tu <span>servicio</span></h2>
        <p class="flow-sub">Pago con <strong><?= htmlspecialchars(ucfirst($_SESSION['reserva']['metodo_pago'])) ?></strong></p>

        <?php if ($error): ?>
            <p class="flow-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" class="flow-form">
            <div class="servicios-grid">
                <?php foreach ($servicios as $clave => $s): ?>
                    <label class="servicio-opcion">
                        <input type="radio" name="servicio" value="<?= htmlspecialchars($clave) ?>" required>
                        <span class="servicio-nombre"><?= htmlspecialchars($s['nombre']) ?></span>
                        <span class="servicio-precio">$<?= number_format($s['precio'], 0, ',', '.') ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit">Confirmar reserva</button>
        </form>

        <a href="pago.php" class="flow-back">&larr; Cambiar método de pago</a>
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
