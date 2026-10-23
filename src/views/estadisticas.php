<?php
require_once __DIR__ . '/../../database/conexcion_BD.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/estadisticas.php';

requerir_rol('admin'); // solo admin y super_admin

$es_super = tiene_rol('super_admin');
$periodos = estadisticas_periodos();
$periodo  = $_GET['periodo'] ?? 'semana';

if (!array_key_exists($periodo, $periodos)) {
    $periodo = 'semana';
}

$rango = estadisticas_rango($periodo);
$desde = $rango['desde'];
$hasta = $rango['hasta'];

$error = '';

try {
    $resumen   = estadisticas_resumen($desde, $hasta);
    $anterior  = $rango['anterior'] ? estadisticas_resumen($rango['anterior'][0], $rango['anterior'][1]) : null;
    $porDia    = estadisticas_por_dia_semana($desde, $hasta);
    $porHora   = estadisticas_por_hora($desde, $hasta);
    $barberos  = estadisticas_por_barbero($desde, $hasta);
    $cortes    = estadisticas_cortes_top($desde, $hasta);
    $categorias = estadisticas_por_categoria($desde, $hasta);
    $pagos     = estadisticas_por_metodo_pago($desde, $hasta);
    $clientes  = estadisticas_clientes_frecuentes($desde, $hasta);
    $porFecha  = $periodo !== 'todo' ? estadisticas_ingresos_por_fecha($desde, $hasta) : [];
} catch (Throwable $e) {
    error_log('estadisticas: ' . $e->getMessage());
    $error    = 'No se pudieron cargar las estadísticas. Verificá que la migración database/migration_barberos.sql esté importada.';
    $resumen  = ['agendados' => 0, 'confirmados' => 0, 'pendientes' => 0, 'cancelados' => 0, 'ingresos' => 0.0, 'por_confirmar' => 0.0, 'ticket_promedio' => 0.0];
    $anterior = null;
    $porDia   = array_fill(0, 7, 0);
    $porHora = $barberos = $cortes = $categorias = $pagos = $clientes = $porFecha = [];
}

$nombresDias = nombres_dias_semana();
$ordenDias   = [1, 2, 3, 4, 5, 6, 0]; // lunes a domingo
$meses       = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

function est_dinero(float $monto): string
{
    return '$' . number_format($monto, 0, ',', '.');
}

function est_fecha_corta(string $fecha, array $meses): string
{
    [$a, $m, $d] = explode('-', $fecha);
    return (int) $d . ' ' . $meses[(int) $m - 1];
}

// Variación porcentual contra el período anterior
function est_variacion(float $actual, ?float $previo): string
{
    if ($previo === null) {
        return '';
    }
    if ($previo <= 0) {
        return $actual > 0 ? 'Sin datos en el período anterior' : '';
    }

    $pct = (($actual - $previo) / $previo) * 100;
    $signo = $pct > 0 ? '+' : '';

    return $signo . number_format($pct, 0, ',', '.') . '% vs período anterior';
}

// Elemento con mayor valor de un arreglo [clave => cantidad]; devuelve [clave, cantidad] o null
function est_maximo(array $datos): ?array
{
    if (empty($datos) || max($datos) <= 0) {
        return null;
    }

    $clave = array_search(max($datos), $datos, true);
    return [$clave, max($datos)];
}

$diaTop   = est_maximo($porDia);
$horaTop  = est_maximo($porHora);
$barbTop  = $barberos ? $barberos[0] : null;
$corteTop = $cortes ? $cortes[0] : null;

$maxDia   = max($porDia) ?: 1;
$maxHora  = $porHora ? max($porHora) : 1;
$maxBarb  = $barberos ? max(array_map(fn($b) => (int) $b['total'], $barberos)) : 1;
$maxCorte = $cortes ? (int) $cortes[0]['total'] : 1;
$maxCli   = $clientes ? (int) $clientes[0]['total'] : 1;
$maxIng   = $porFecha ? max(array_map(fn($f) => (float) $f['ingresos'], $porFecha)) : 0;
$maxIng   = $maxIng > 0 ? $maxIng : 1;

$totalCat  = array_sum(array_map(fn($c) => (int) $c['total'], $categorias)) ?: 1;
$totalPago = array_sum(array_map(fn($p) => (int) $p['total'], $pagos)) ?: 1;

$variacionIngresos = est_variacion($resumen['ingresos'], $anterior ? $anterior['ingresos'] : null);
$variacionTurnos   = est_variacion((float) $resumen['agendados'], $anterior ? (float) $anterior['agendados'] : null);

$etiquetaPeriodo = $periodos[$periodo];
if ($desde !== null && $hasta !== null) {
    $etiquetaPeriodo .= ' (' . est_fecha_corta($desde, $meses) . ' al ' . est_fecha_corta($hasta, $meses) . ')';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/<?= $es_super ? 'superadmin.css' : 'admin.css' ?>">
    <title>Estadísticas - Tuya's Barber</title>
    <link rel="icon" type="image/png" href="../../public/img/tuyas_sin_fondo.png">
</head>
<body>
    <div class="panel panel--ancho">
        <a class="btn-volver" href="<?= $es_super ? 'superadmin_dashboard.php' : 'admin_dashboard.php' ?>">&larr; Volver al panel</a>

        <h1>Estadísticas</h1>
        <p class="subtitulo">Cómo viene el negocio. Los turnos cancelados no se cuentan; los ingresos suman solo los turnos confirmados.</p>

        <nav class="selector-periodo">
            <?php foreach ($periodos as $clave => $nombre): ?>
                <a href="estadisticas.php?periodo=<?= htmlspecialchars($clave) ?>" class="<?= $clave === $periodo ? 'activo' : '' ?>"><?= htmlspecialchars($nombre) ?></a>
            <?php endforeach; ?>
        </nav>

        <p class="periodo-actual"><?= htmlspecialchars($etiquetaPeriodo) ?></p>

        <?php if ($error): ?>
            <p class="mensaje error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="grid-kpis">
            <div class="kpi kpi--destacado">
                <span class="kpi__etiqueta">Ingresos confirmados</span>
                <strong class="kpi__valor"><?= est_dinero($resumen['ingresos']) ?></strong>
                <?php if ($variacionIngresos !== ''): ?><span class="kpi__detalle"><?= htmlspecialchars($variacionIngresos) ?></span><?php endif; ?>
            </div>
            <div class="kpi">
                <span class="kpi__etiqueta">Turnos agendados</span>
                <strong class="kpi__valor"><?= (int) $resumen['agendados'] ?></strong>
                <?php if ($variacionTurnos !== ''): ?><span class="kpi__detalle"><?= htmlspecialchars($variacionTurnos) ?></span><?php endif; ?>
            </div>
            <div class="kpi">
                <span class="kpi__etiqueta">Por confirmar</span>
                <strong class="kpi__valor"><?= est_dinero($resumen['por_confirmar']) ?></strong>
                <span class="kpi__detalle"><?= (int) $resumen['pendientes'] ?> turno(s) pendiente(s)</span>
            </div>
            <div class="kpi">
                <span class="kpi__etiqueta">Ticket promedio</span>
                <strong class="kpi__valor"><?= est_dinero($resumen['ticket_promedio']) ?></strong>
                <span class="kpi__detalle">por turno confirmado</span>
            </div>
            <div class="kpi">
                <span class="kpi__etiqueta">Cancelados</span>
                <strong class="kpi__valor"><?= (int) $resumen['cancelados'] ?></strong>
                <?php $totalConCancelados = $resumen['agendados'] + $resumen['cancelados']; ?>
                <span class="kpi__detalle"><?= $totalConCancelados > 0 ? number_format(($resumen['cancelados'] / $totalConCancelados) * 100, 0) . '% de los turnos' : 'sin turnos' ?></span>
            </div>
        </div>

        <?php if ($resumen['agendados'] === 0): ?>
            <p class="vacio">Todavía no hay turnos en este período.</p>
        <?php else: ?>

            <div class="grid-kpis grid-kpis--destacados">
                <div class="kpi">
                    <span class="kpi__etiqueta">Día que más se corta</span>
                    <strong class="kpi__valor"><?= $diaTop ? htmlspecialchars($nombresDias[(int) $diaTop[0]]) : '—' ?></strong>
                    <?php if ($diaTop): ?><span class="kpi__detalle"><?= (int) $diaTop[1] ?> turno(s)</span><?php endif; ?>
                </div>
                <div class="kpi">
                    <span class="kpi__etiqueta">Horario que más se corta</span>
                    <strong class="kpi__valor"><?= $horaTop ? htmlspecialchars((string) $horaTop[0]) . ' hs' : '—' ?></strong>
                    <?php if ($horaTop): ?><span class="kpi__detalle"><?= (int) $horaTop[1] ?> turno(s)</span><?php endif; ?>
                </div>
                <div class="kpi">
                    <span class="kpi__etiqueta">Barbero más elegido</span>
                    <strong class="kpi__valor"><?= $barbTop ? htmlspecialchars($barbTop['nombre']) : '—' ?></strong>
                    <?php if ($barbTop): ?><span class="kpi__detalle"><?= (int) $barbTop['total'] ?> turno(s)</span><?php endif; ?>
                </div>
                <div class="kpi">
                    <span class="kpi__etiqueta">Corte más pedido</span>
                    <strong class="kpi__valor"><?= $corteTop ? htmlspecialchars($corteTop['nombre']) : '—' ?></strong>
                    <?php if ($corteTop): ?><span class="kpi__detalle"><?= (int) $corteTop['total'] ?> turno(s)</span><?php endif; ?>
                </div>
            </div>

            <div class="grid-stats">
                <div class="card-form">
                    <h2>Turnos por día de la semana</h2>
                    <?php foreach ($ordenDias as $d): ?>
                        <div class="fila-barra">
                            <span class="fila-barra__etiqueta"><?= htmlspecialchars($nombresDias[$d]) ?></span>
                            <progress max="<?= (int) $maxDia ?>" value="<?= (int) $porDia[$d] ?>"></progress>
                            <span class="fila-barra__valor"><?= (int) $porDia[$d] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-form">
                    <h2>Turnos por horario</h2>
                    <?php foreach ($porHora as $hora => $total): ?>
                        <div class="fila-barra">
                            <span class="fila-barra__etiqueta"><?= htmlspecialchars((string) $hora) ?> hs</span>
                            <progress max="<?= (int) $maxHora ?>" value="<?= (int) $total ?>"></progress>
                            <span class="fila-barra__valor"><?= (int) $total ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-form">
                    <h2>Por barbero</h2>
                    <?php foreach ($barberos as $b): ?>
                        <div class="fila-barra fila-barra--doble">
                            <span class="fila-barra__etiqueta"><?= htmlspecialchars($b['nombre']) ?></span>
                            <progress max="<?= (int) $maxBarb ?>" value="<?= (int) $b['total'] ?>"></progress>
                            <span class="fila-barra__valor"><?= (int) $b['total'] ?></span>
                            <span class="fila-barra__extra"><?= est_dinero((float) $b['ingresos']) ?> confirmados</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-form">
                    <h2>Cortes más pedidos</h2>
                    <?php foreach ($cortes as $c): ?>
                        <div class="fila-barra">
                            <span class="fila-barra__etiqueta"><?= htmlspecialchars($c['nombre']) ?></span>
                            <progress max="<?= (int) $maxCorte ?>" value="<?= (int) $c['total'] ?>"></progress>
                            <span class="fila-barra__valor"><?= (int) $c['total'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-form">
                    <h2>Hombre y mujer</h2>
                    <?php foreach ($categorias as $c): ?>
                        <?php $etiqueta = $c['nombre'] === 'hombre' ? 'Hombre' : ($c['nombre'] === 'mujer' ? 'Mujer' : 'Sin dato'); ?>
                        <div class="fila-barra">
                            <span class="fila-barra__etiqueta"><?= $etiqueta ?></span>
                            <progress max="<?= (int) $totalCat ?>" value="<?= (int) $c['total'] ?>"></progress>
                            <span class="fila-barra__valor"><?= number_format(((int) $c['total'] / $totalCat) * 100, 0) ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-form">
                    <h2>Método de pago</h2>
                    <?php foreach ($pagos as $p): ?>
                        <div class="fila-barra">
                            <span class="fila-barra__etiqueta"><?= htmlspecialchars(ucfirst($p['nombre'])) ?></span>
                            <progress max="<?= (int) $totalPago ?>" value="<?= (int) $p['total'] ?>"></progress>
                            <span class="fila-barra__valor"><?= number_format(((int) $p['total'] / $totalPago) * 100, 0) ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-form">
                    <h2>Clientes más frecuentes</h2>
                    <?php foreach ($clientes as $c): ?>
                        <div class="fila-barra">
                            <span class="fila-barra__etiqueta"><?= htmlspecialchars($c['nombre']) ?></span>
                            <progress max="<?= (int) $maxCli ?>" value="<?= (int) $c['total'] ?>"></progress>
                            <span class="fila-barra__valor"><?= (int) $c['total'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($porFecha)): ?>
                    <div class="card-form">
                        <h2>Ingresos por día</h2>
                        <?php foreach ($porFecha as $f): ?>
                            <div class="fila-barra fila-barra--ancha">
                                <span class="fila-barra__etiqueta"><?= htmlspecialchars(est_fecha_corta($f['fecha'], $meses)) ?></span>
                                <progress max="<?= (float) $maxIng ?>" value="<?= (float) $f['ingresos'] ?>"></progress>
                                <span class="fila-barra__valor"><?= est_dinero((float) $f['ingresos']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>
