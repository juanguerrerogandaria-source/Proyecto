<?php
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requerir_rol('admin'); // admin y super_admin pueden entrar

$mensaje  = "";
$es_error = false;

const EXT_IMAGEN = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
const EXT_VIDEO  = ['mp4', 'webm', 'mov'];
const MAX_BYTES  = 20 * 1024 * 1024; // 20MB

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    if ($accion === "crear") {
        $nombre      = trim($_POST["nombre"] ?? "");
        $categoria   = $_POST["categoria"] ?? "";
        $descripcion = trim($_POST["descripcion"] ?? "");
        $precio      = (float) str_replace(",", ".", $_POST["precio"] ?? "0");

        $media_path = null;
        $media_tipo = null;

        if (empty($nombre)) {
            $mensaje  = "El nombre del corte es obligatorio.";
            $es_error = true;
        } elseif (!in_array($categoria, ['hombre', 'mujer'], true)) {
            $mensaje  = "Elegí si el corte es para hombre o para mujer.";
            $es_error = true;
        } elseif ($precio <= 0) {
            $mensaje  = "El precio tiene que ser mayor a 0.";
            $es_error = true;
        } elseif (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['media']['error'] !== UPLOAD_ERR_OK) {
                $mensaje  = "Hubo un error al subir el archivo.";
                $es_error = true;
            } elseif ($_FILES['media']['size'] > MAX_BYTES) {
                $mensaje  = "El archivo no puede superar los 20MB.";
                $es_error = true;
            } else {
                $extension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));

                if (in_array($extension, EXT_IMAGEN, true)) {
                    $media_tipo = 'imagen';
                } elseif (in_array($extension, EXT_VIDEO, true)) {
                    $media_tipo = 'video';
                } else {
                    $mensaje  = "Formato no permitido. Usá jpg, png, webp, gif, mp4, webm o mov.";
                    $es_error = true;
                }

                if (!$es_error) {
                    $carpetaDestino = __DIR__ . '/../../public/uploads/cortes/';
                    $nombreArchivo  = uniqid('corte_', true) . '.' . $extension;

                    if (move_uploaded_file($_FILES['media']['tmp_name'], $carpetaDestino . $nombreArchivo)) {
                        $media_path = 'uploads/cortes/' . $nombreArchivo;
                    } else {
                        $mensaje  = "No se pudo guardar el archivo en el servidor.";
                        $es_error = true;
                    }
                }
            }
        }

        if (!$es_error) {
            if (create_corte($nombre, $categoria, $descripcion, $precio, $media_path, $media_tipo)) {
                $mensaje = "Corte \"$nombre\" agregado correctamente.";
            } else {
                $mensaje  = "No se pudo guardar el corte.";
                $es_error = true;
            }
        }
    } elseif ($accion === "actualizar_precio") {
        $id     = (int) ($_POST["id"] ?? 0);
        $precio = (float) str_replace(",", ".", $_POST["precio"] ?? "0");

        if ($precio <= 0) {
            $mensaje  = "El precio tiene que ser mayor a 0.";
            $es_error = true;
        } elseif (update_corte_precio($id, $precio)) {
            $mensaje = "Precio actualizado.";
        } else {
            $mensaje  = "No se pudo actualizar el precio.";
            $es_error = true;
        }
    } elseif ($accion === "eliminar") {
        $id = (int) ($_POST["id"] ?? 0);

        if (delete_corte($id)) {
            $mensaje = "Corte eliminado.";
        } else {
            $mensaje  = "No se pudo eliminar el corte.";
            $es_error = true;
        }
    }
}

$cortes = get_all_cortes();
$es_super = tiene_rol('super_admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/<?= $es_super ? 'superadmin.css' : 'admin.css' ?>">
    <title>Gestionar Cortes - Tuya's Barber</title>
</head>
<body>
    <div class="panel panel--ancho">
        <a class="btn-volver" href="<?= $es_super ? 'superadmin_dashboard.php' : 'admin_dashboard.php' ?>">&larr; Volver al panel</a>

        <h1>Cortes y Servicios</h1>
        <p class="subtitulo">Agregá cortes con foto o video, y administrá sus precios.</p>

        <?php if ($mensaje): ?>
            <p class="mensaje<?= $es_error ? ' error' : '' ?>"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <div class="card-form">
            <h2>Agregar nuevo corte</h2>
            <form action="gestionar_cortes.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear">

                <div class="grid-2">
                    <div class="campo">
                        <label for="nombre">Nombre del corte</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Fade clásico" required>
                    </div>
                    <div class="campo">
                        <label for="precio">Precio ($)</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" placeholder="Ej: 800" required>
                    </div>
                </div>

                <div class="campo">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Seleccioná una categoría...</option>
                        <option value="hombre">Hombre</option>
                        <option value="mujer">Mujer</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2" placeholder="Detalles del corte..."></textarea>
                </div>

                <div class="campo">
                    <label for="media">Foto o video (opcional)</label>
                    <input type="file" id="media" name="media" accept="image/*,video/mp4,video/webm,video/quicktime">
                </div>

                <button type="submit" class="btn-primario">Agregar corte</button>
            </form>
        </div>

        <h2 class="titulo-lista">Cortes cargados (<?= count($cortes) ?>)</h2>

        <div class="grid-cortes">
            <?php if (empty($cortes)): ?>
                <p class="vacio">Todavía no hay cortes cargados.</p>
            <?php endif; ?>

            <?php foreach ($cortes as $c): ?>
                <div class="tarjeta-corte">
                    <div class="tarjeta-corte__media">
                        <?php if ($c['media_tipo'] === 'imagen'): ?>
                            <img src="../../public/<?= htmlspecialchars($c['media_path']) ?>" alt="<?= htmlspecialchars($c['nombre']) ?>">
                        <?php elseif ($c['media_tipo'] === 'video'): ?>
                            <video controls muted>
                                <source src="../../public/<?= htmlspecialchars($c['media_path']) ?>">
                            </video>
                        <?php else: ?>
                            <div class="sin-media">Sin foto/video</div>
                        <?php endif; ?>
                    </div>

                    <div class="tarjeta-corte__body">
                        <h3><?= htmlspecialchars($c['nombre']) ?></h3>
                        <span class="badge <?= $c['categoria'] === 'hombre' ? 'badge-admin' : 'badge-super_admin' ?>">
                            <?= $c['categoria'] === 'hombre' ? 'Hombre' : 'Mujer' ?>
                        </span>
                        <?php if (!empty($c['descripcion'])): ?>
                            <p class="descripcion"><?= htmlspecialchars($c['descripcion']) ?></p>
                        <?php endif; ?>

                        <form action="gestionar_cortes.php" method="post" class="form-precio">
                            <input type="hidden" name="accion" value="actualizar_precio">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <span class="prefijo">$</span>
                            <input type="number" name="precio" step="0.01" min="0" value="<?= htmlspecialchars((string) $c['precio']) ?>">
                            <button type="submit" class="btn-chico">Guardar</button>
                        </form>

                        <form action="gestionar_cortes.php" method="post" onsubmit="return confirm('¿Eliminar este corte?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button type="submit" class="btn-eliminar">Eliminar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
