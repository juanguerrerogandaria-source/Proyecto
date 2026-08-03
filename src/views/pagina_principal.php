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
                    <a href="reservar.php" class="btn btn--primary">Reservá tu turno</a>
                <?php else: ?>
                    <a href="loginbarber.php" class="btn btn--ghost">Iniciar sesión</a>
                    <a href="registrarse.php" class="btn btn--primary">Registrarte</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="hero-info hero-info--sola">
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="../../public/video/hero.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>

        <div class="hero-badge">
            <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber">
        </div>

        <div class="hero-info__content">
            <p class="hero__eyebrow">Es más que un simple corte</p>
            <h1 class="hero__titulo">Viví una experiencia diferente</h1>

            <button type="button" class="hero__ig" id="btn-quienes-somos" aria-label="Quiénes somos">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="1.8">
                    <rect x="3" y="3" width="18" height="18" rx="5"/>
                    <circle cx="12" cy="12" r="4"/>
                    <circle cx="17.2" cy="6.8" r="1"/>
                </svg>
            </button>
        </div>

        <div class="hero__barra">
            <?php if ($logueado): ?>
                <a href="reservar.php" class="hero__btn hero__btn--solido">Reserva online</a>
            <?php else: ?>
                <a href="registrarse.php" class="hero__btn hero__btn--solido" id="btn-registrarte">Reserva online</a>
            <?php endif; ?>
            <a href="#horarios" class="hero__btn hero__btn--claro">Agendate aquí</a>
        </div>
    </section>

    <!-- Modal "Quiénes somos" -->
    <div class="overlay" id="overlay-quienes">
        <div class="comprobante quienes-modal">
            <button type="button" class="comprobante__close" id="cerrar-quienes" aria-label="Cerrar">&times;</button>
            <p class="comprobante__eyebrow">Quiénes somos</p>
            <h2>¡Hola!</h2>
            <p class="quienes-modal__texto">
                Soy <strong>Santiago Tuya</strong>, esta es mi peluquería. Hacemos cortes, barba y color.
            </p>
            <img src="../../public/img/santiago_tuya.jpeg" alt="Santiago Tuya" class="quienes-modal__foto">
            <button type="button" class="btn btn--primary" id="ok-quienes">Listo</button>
        </div>
    </div>

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
            <div class="about__perfil">
                <img src="../../public/img/santiago_tuya.jpeg" alt="Santiago Tuya" class="about__foto">
                <p class="section__lead about__lead">
                    Soy Santiago Tuya. En Tuya's Barber combinamos el oficio clásico de la barbería con una identidad propia:
                    filo, actitud y detalle en cada corte. Hacemos cortes, barba y color, en un espacio donde cada cliente
                    construye su estilo, a su manera.
                </p>
            </div>

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
            <p class="section__lead">Elegí tu experiencia. Reservá online y evitá esperas.</p>

            <div class="services__grid">
                <div class="service-card">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 3v18M6 3a3 3 0 1 1 0 6M6 15a3 3 0 1 0 0 6M6 9l14 6M6 15l14-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <h3>Corte clásico</h3>
                    <p>Técnica de tijera y máquina, terminación con navaja.</p>
                </div>
                <div class="service-card">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 12c0-4 3-8 8-8s8 4 8 8-3 8-8 8-8-4-8-8Z" /><path d="M9 12h6" stroke-linecap="round"/></svg>
                    <h3>Diseño de barba</h3>
                    <p>Perfilado, afeitado a navaja y tratamiento de hidratación.</p>
                </div>
                <div class="service-card">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M4 10h16" stroke-linecap="round"/></svg>
                    <h3>Combo completo</h3>
                    <p>Corte + barba + detalle facial. La experiencia completa.</p>
                </div>
                <div class="service-card">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2 3 7v6c0 5 4 8 9 9 5-1 9-4 9-9V7l-9-5Z"/></svg>
                    <h3>Diseño &amp; líneas</h3>
                    <p>Dibujos, degradados y líneas de precisión a pedido.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta" id="horarios">
        <h2>Reservá tu lugar</h2>

        <?php if ($logueado): ?>
            <p>Elegí el servicio, el día y el horario que más te convenga.</p>
            <a href="reservar.php" class="btn btn--primary">Reservar turno</a>
        <?php else: ?>
            <p>Creá tu cuenta en un minuto y elegí el día y horario que más te convenga.</p>
            <a href="registrarse.php" class="btn btn--primary">Crear cuenta</a>
        <?php endif; ?>
    </section>

    <footer>
        <a href="pagina_principal.php" class="navbar__brand">
            <img src="../../public/img/tuyasbarber.jpeg" alt="Tuya's Barber" style="height:32px;width:32px;">
            Tuya's Barber
        </a>
        <p>&copy; <?= date('Y') ?> Tuya's Barber. Todos los derechos reservados.</p>
    </footer>

    <script>
        // ---------- Modal "Quiénes somos" ----------
        const btnQuienes    = document.getElementById('btn-quienes-somos');
        const overlayQuienes = document.getElementById('overlay-quienes');
        const cerrarQuienes  = document.getElementById('cerrar-quienes');
        const okQuienes      = document.getElementById('ok-quienes');

        function abrirQuienes() { overlayQuienes.classList.add('overlay--visible'); }
        function cerrarModalQuienes() { overlayQuienes.classList.remove('overlay--visible'); }

        if (btnQuienes) btnQuienes.addEventListener('click', abrirQuienes);
        if (cerrarQuienes) cerrarQuienes.addEventListener('click', cerrarModalQuienes);
        if (okQuienes) okQuienes.addEventListener('click', cerrarModalQuienes);
        if (overlayQuienes) {
            overlayQuienes.addEventListener('click', function (e) {
                if (e.target === overlayQuienes) cerrarModalQuienes();
            });
        }
    </script>
</body>
</html>
