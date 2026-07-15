<?php
require_once __DIR__ . '/../../includes/auth.php';

$logueado = esta_logueado();
$rol      = obtener_rol();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/pagina_principal.css">
    <title>Tuya's Barber</title>
</head>
<body>

    <nav class="navbar">
        <div class="container">
            <a href="pagina_principal.php" class="navbar__brand">
                <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber">
                Tuya's Barber
            </a>
            <div class="navbar__links">
                <a href="#quienes-somos">Quiénes somos</a>
                <a href="#servicios">Servicios</a>
                <a href="#horarios">Horarios</a>

                <?php if ($logueado): ?>
                    <?php if (tiene_rol('admin')): ?>
                        <a href="admin_dashboard.php">Panel</a>
                    <?php endif; ?>
                    <span class="btn btn--ghost">Hola, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
                <?php else: ?>
                    <a href="loginbarber.php" class="btn btn--ghost">Iniciar sesión</a>
                    <a href="reservar.php" class="btn btn--primary">Reservá tu turno</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="hero">
        <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber" class="hero__logo">
        <p class="hero__eyebrow">Barbería &amp; estudio de estilo</p>
        <h1>Es más que un corte,<br><span>es tuyo.</span></h1>
        <p>Cortes con precisión de navaja, diseño de barba y una experiencia pensada para que salgas de acá sintiéndote otro.</p>
        <div class="hero__actions">
            <a href="reservar.php" class="btn btn--primary">Reservá tu turno</a>
            <a href="#servicios" class="btn btn--ghost">Ver servicios</a>
        </div>
    </section>

    <div class="divider">
        <span class="divider__line"></span>
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M4 4l16 16M4 20L20 4" stroke-linecap="round"/>
        </svg>
        <span class="divider__line"></span>
    </div>

    <section class="section" id="quienes-somos">
        <div class="container">
            <p class="section__eyebrow">Nuestra esencia</p>
            <h2 class="section__title">Quiénes somos</h2>
            <p class="section__lead">
                En Tuya's Barber combinamos el oficio clásico de la barbería con una identidad propia:
                filo, actitud y detalle en cada corte. Un espacio donde cada cliente construye su estilo, a su manera.
            </p>

            <div class="about__grid">
                <div class="about__card">
                    <span class="num">Oficio</span>
                    <h3>Manos con experiencia</h3>
                    <p>Barberos formados en técnica clásica y tendencias actuales, cuidando cada detalle del corte.</p>
                </div>
                <div class="about__card">
                    <span class="num">Ambiente</span>
                    <h3>Tu espacio</h3>
                    <p>Un lugar pensado para relajarte mientras te transformás: buena música, buena onda, cero apuro.</p>
                </div>
                <div class="about__card">
                    <span class="num">Estilo</span>
                    <h3>Identidad propia</h3>
                    <p>No copiamos tendencias, las adaptamos a vos. Tu corte, tu barba, tu manera de llevarlo.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="services" id="servicios">
        <div class="container section">
            <p class="section__eyebrow">Lo que hacemos</p>
            <h2 class="section__title">Servicios</h2>
            <p class="section__lead">Elegí tu categoría y descubrí todos los cortes disponibles.</p>

            <div class="categorias__grid">
                <a href="cortes_hombre.php" class="service-card">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 3v18M6 3a3 3 0 1 1 0 6M6 15a3 3 0 1 0 0 6M6 9l14 6M6 15l14-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <h3>Hombre</h3>
                    <p>Fades, cortes clásicos, diseño de barba y más.</p>
                </a>

                <a href="cortes_mujer.php" class="service-card">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 4h16v3H4Z"/><path d="M6 7v13M10 7v10M14 7v13M18 7v10" stroke-linecap="round"/></svg>
                    <h3>Mujer</h3>
                    <p>Cortes, color, peinados y tratamientos.</p>
                </a>
            </div>
        </div>
    </section>

    <section class="cta" id="horarios">
        <h2>Reservá tu lugar</h2>
        <p>Creá tu cuenta en un minuto y elegí el día y horario que más te convenga.</p>
        <a href="registrarse.php" class="btn btn--primary">Crear cuenta</a>

        <div class="hours">
            <div>
                <span>Lunes a viernes</span>
                18:30 – 21:30
            </div>
            <div>
                <span>Sábados y Domingos</span>
                08:30 – 20:30
            </div>
        </div>
    </section>

    <footer>
        <a href="pagina_principal.php" class="navbar__brand">
            <img src="../../public/img/tuyasbarber.png" alt="Tuya's Barber" style="height:32px;width:32px;">
            Tuya's Barber
        </a>
        <p>&copy; <?= date('Y') ?> Tuya's Barber. Todos los derechos reservados.</p>
    </footer>

</body>
</html>
