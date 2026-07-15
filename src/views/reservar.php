<?php
// Paso 1 del flujo de reserva: elegir el corte (viene de la base de datos real).
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$cortes = get_all_cortes();
$oferta = get_mejor_oferta_activa();

$cortes_hombre = array_filter($cortes, fn($c) => $c['categoria'] === 'hombre');
$cortes_mujer  = array_filter($cortes, fn($c) => $c['categoria'] === 'mujer');

// Si venís de "Reservar" en la galería de cortes, ya llega preseleccionado
$preseleccionado = (int) ($_GET['corte_id'] ?? ($_SESSION['flow_reserva']['corte_id'] ?? 0));

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $corte_id = (int) ($_POST['corte_id'] ?? 0);
    $elegido  = null;

    foreach ($cortes as $c) {
        if ((int) $c['id'] === $corte_id) {
            $elegido = $c;
            break;
        }
    }

    if ($elegido === null) {
        $error = 'Elegí un corte de la lista.';
    } else {
        $precioFinal = (float) $elegido['precio'];
        if ($oferta && (int) $oferta['descuento_porcentaje'] > 0) {
            $precioFinal = round($precioFinal * (1 - (int) $oferta['descuento_porcentaje'] / 100), 2);
        }

        $_SESSION['flow_reserva'] = [
            'corte_id'        => (int) $elegido['id'],
            'corte_nombre'    => $elegido['nombre'],
            'corte_categoria' => $elegido['categoria'],
            'precio_original' => (float) $elegido['precio'],
            'monto'           => $precioFinal,
            'oferta'          => $oferta ? $oferta['titulo'] : null,
        ];

        header('Location: horario.php');
        exit;
    }
}

function precio_con_oferta(float $precio, ?array $oferta): float
{
    if ($oferta && (int) $oferta['descuento_porcentaje'] > 0) {
        return round($precio * (1 - (int) $oferta['descuento_porcentaje'] / 100), 2);
    }
    return $precio;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/flow.css">
    <title>Reservá tu turno - Tuya's Barber</title>
</head>
<body>
    <div class="sparkles" id="sparkles"></div>

    <div class="flow-wrapper">
        <a href="index.php" class="flow-logo">
            <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber">
            <span>Tuya's Barber</span>
        </a>

        <div class="steps">
            <span class="step step--active">1. Corte</span>
            <span class="step">2. Horario</span>
            <span class="step">3. Datos y pago</span>
            <span class="step">4. Listo</span>
        </div>

        <h2>Elegí tu <span>corte</span></h2>
        <?php if ($oferta): ?>
            <p class="flow-sub">🔥 Oferta activa: <strong><?= htmlspecialchars($oferta['titulo']) ?></strong> (<?= (int) $oferta['descuento_porcentaje'] ?>% OFF, ya aplicado en los precios)</p>
        <?php else: ?>
            <p class="flow-sub">Precios actualizados desde nuestro catálogo.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="flow-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (empty($cortes)): ?>
            <p class="flow-sub">Todavía no hay cortes cargados. Volvé a intentarlo más tarde.</p>
        <?php else: ?>
            <form method="post" class="flow-form">
                <?php foreach ([['Hombre', $cortes_hombre], ['Mujer', $cortes_mujer]] as [$titulo, $grupo]): ?>
                    <?php if (!empty($grupo)): ?>
                        <label><?= $titulo ?></label>
                        <div class="servicios-grid">
                            <?php foreach ($grupo as $c): ?>
                                <?php $final = precio_con_oferta((float) $c['precio'], $oferta); ?>
                                <label class="servicio-opcion">
                                    <input type="radio" name="corte_id" value="<?= (int) $c['id'] ?>" <?= $preseleccionado === (int) $c['id'] ? 'checked' : '' ?> required>
                                    <span class="servicio-nombre"><?= htmlspecialchars($c['nombre']) ?></span>
                                    <span class="servicio-precio">
                                        <?php if ($final < (float) $c['precio']): ?>
                                            <s style="opacity:.5; font-size:.85em;">$<?= number_format((float) $c['precio'], 0, ',', '.') ?></s>
                                        <?php endif; ?>
                                        $<?= number_format($final, 0, ',', '.') ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <button type="submit">Continuar al horario</button>
            </form>
        <?php endif; ?>

        <a href="index.php" class="flow-back">&larr; Volver al inicio</a>
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
