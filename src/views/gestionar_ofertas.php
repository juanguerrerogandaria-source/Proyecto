<?php
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requerir_rol('admin'); // admin y super_admin pueden entrar

$mensaje  = "";
$es_error = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    if ($accion === "crear") {
        $titulo       = trim($_POST["titulo"] ?? "");
        $descripcion  = trim($_POST["descripcion"] ?? "");
        $descuento    = (int) ($_POST["descuento"] ?? 0);
        $fecha_inicio = $_POST["fecha_inicio"] ?: null;
        $fecha_fin    = $_POST["fecha_fin"] ?: null;

        if (empty($titulo)) {
            $mensaje  = "El título de la oferta es obligatorio.";
            $es_error = true;
        } elseif ($descuento <= 0 || $descuento > 100) {
            $mensaje  = "El descuento tiene que estar entre 1 y 100.";
            $es_error = true;
        } elseif (create_oferta($titulo, $descripcion, $descuento, $fecha_inicio, $fecha_fin)) {
            $mensaje = "Oferta \"$titulo\" creada correctamente.";
        } else {
            $mensaje  = "No se pudo crear la oferta.";
            $es_error = true;
        }
    } elseif ($accion === "toggle") {
        $id     = (int) ($_POST["id"] ?? 0);
        $activa = ($_POST["activa"] ?? "0") === "1";

        toggle_oferta($id, $activa);
        $mensaje = $activa ? "Oferta activada." : "Oferta desactivada.";
    } elseif ($accion === "eliminar") {
        $id = (int) ($_POST["id"] ?? 0);

        if (delete_oferta($id)) {
            $mensaje = "Oferta eliminada.";
        } else {
            $mensaje  = "No se pudo eliminar la oferta.";
            $es_error = true;
        }
    }
}

$ofertas  = get_ofertas();
$es_super = tiene_rol('super_admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/<?= $es_super ? 'superadmin.css' : 'admin.css' ?>">
    <title>Gestionar Ofertas - Tuya's Barber</title>
</head>
<body>
    <div class="panel panel--ancho">
        <a class="btn-volver" href="<?= $es_super ? 'superadmin_dashboard.php' : 'admin_dashboard.php' ?>">&larr; Volver al panel</a>

        <h1>Ofertas y Promociones</h1>
        <p class="subtitulo">Creá descuentos por tiempo limitado para tus clientes.</p>

        <?php if ($mensaje): ?>
            <p class="mensaje<?= $es_error ? ' error' : '' ?>"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <div class="card-form">
            <h2>Nueva oferta</h2>
            <form action="gestionar_ofertas.php" method="post">
                <input type="hidden" name="accion" value="crear">

                <div class="campo">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" placeholder="Ej: 2x1 en cortes los lunes" required>
                </div>

                <div class="campo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2" placeholder="Detalles de la oferta..."></textarea>
                </div>

                <div class="grid-3">
                    <div class="campo">
                        <label for="descuento">Descuento (%)</label>
                        <input type="number" id="descuento" name="descuento" min="1" max="100" placeholder="Ej: 20" required>
                    </div>
                    <div class="campo">
                        <label for="fecha_inicio">Desde</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio">
                    </div>
                    <div class="campo">
                        <label for="fecha_fin">Hasta</label>
                        <input type="date" id="fecha_fin" name="fecha_fin">
                    </div>
                </div>

                <button type="submit" class="btn-primario">Crear oferta</button>
            </form>
        </div>

        <h2 class="titulo-lista">Ofertas cargadas (<?= count($ofertas) ?>)</h2>

        <div class="lista-ofertas">
            <?php if (empty($ofertas)): ?>
                <p class="vacio">Todavía no hay ofertas cargadas.</p>
            <?php endif; ?>

            <?php foreach ($ofertas as $o): ?>
                <div class="tarjeta-oferta <?= $o['activa'] ? '' : 'tarjeta-oferta--inactiva' ?>">
                    <div class="tarjeta-oferta__descuento"><?= (int) $o['descuento_porcentaje'] ?>%</div>
                    <div class="tarjeta-oferta__body">
                        <h3><?= htmlspecialchars($o['titulo']) ?></h3>
                        <?php if (!empty($o['descripcion'])): ?>
                            <p class="descripcion"><?= htmlspecialchars($o['descripcion']) ?></p>
                        <?php endif; ?>
                        <?php if ($o['fecha_inicio'] || $o['fecha_fin']): ?>
                            <p class="fechas">
                                <?= $o['fecha_inicio'] ? htmlspecialchars($o['fecha_inicio']) : '...' ?>
                                &rarr;
                                <?= $o['fecha_fin'] ? htmlspecialchars($o['fecha_fin']) : '...' ?>
                            </p>
                        <?php endif; ?>
                        <span class="badge <?= $o['activa'] ? 'badge-admin' : 'badge-user' ?>">
                            <?= $o['activa'] ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </div>
                    <div class="tarjeta-oferta__acciones">
                        <form action="gestionar_ofertas.php" method="post">
                            <input type="hidden" name="accion" value="toggle">
                            <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                            <input type="hidden" name="activa" value="<?= $o['activa'] ? '0' : '1' ?>">
                            <button type="submit" class="btn-chico"><?= $o['activa'] ? 'Desactivar' : 'Activar' ?></button>
                        </form>
                        <form action="gestionar_ofertas.php" method="post" onsubmit="return confirm('¿Eliminar esta oferta?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                            <button type="submit" class="btn-eliminar">Eliminar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
