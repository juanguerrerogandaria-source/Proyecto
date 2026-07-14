<?php
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

$logueado = esta_logueado();
$cortes   = get_cortes_por_categoria('hombre');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/pagina_principal.css">
    <title>Cortes de Hombre - Tuya's Barber</title>
</head>
<body>

    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar__brand">
                <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber">
                Tuya's Barber
            </a>
            <div class="navbar__links">
                <a href="index.php#servicios">Servicios</a>
                <a href="cortes_mujer.php">Cortes de Mujer</a>

                <?php if ($logueado): ?>
                    <?php if (tiene_rol('admin')): ?>
                        <a href="admin_dashboard.php">Panel</a>
                    <?php endif; ?>
                    <span class="btn btn--ghost">Hola, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
                <?php else: ?>
                    <a href="loginbarber.php" class="btn btn--ghost">Iniciar sesión</a>
                    <a href="registrarse.php" class="btn btn--primary">Reservá tu turno</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="page-header">
        <p class="hero__eyebrow">Para hombre</p>
        <h1>Cortes de Hombre</h1>
        <p>Fades, clásicos, diseño de barba y mucho más.</p>
    </header>

    <div class="container">
        <?php if (empty($cortes)): ?>
            <p class="vacio-cortes">Todavía no hay cortes cargados en esta categoría.</p>
        <?php else: ?>
            <div class="cortes-grid">
                <?php foreach ($cortes as $c): ?>
                    <div class="corte-card">
                        <div class="corte-card__media">
                            <?php if ($c['media_tipo'] === 'imagen'): ?>
                                <img src="../../public/<?= htmlspecialchars($c['media_path']) ?>" alt="<?= htmlspecialchars($c['nombre']) ?>">
                            <?php elseif ($c['media_tipo'] === 'video'): ?>
                                <video controls muted>
                                    <source src="../../public/<?= htmlspecialchars($c['media_path']) ?>">
                                </video>
                            <?php else: ?>
                                <span class="corte-card__sin-media">Sin foto/video</span>
                            <?php endif; ?>
                        </div>
                        <div class="corte-card__body">
                            <h3><?= htmlspecialchars($c['nombre']) ?></h3>
                            <?php if (!empty($c['descripcion'])): ?>
                                <p><?= htmlspecialchars($c['descripcion']) ?></p>
                            <?php endif; ?>
                            <span class="corte-card__precio">$<?= htmlspecialchars((string) $c['precio']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <a href="index.php" class="navbar__brand">
            <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber" style="height:32px;width:32px;">
            Tuya's Barber
        </a>
        <p>&copy; <?= date('Y') ?> Tuya's Barber. Todos los derechos reservados.</p>
    </footer>

</body>
</html>
