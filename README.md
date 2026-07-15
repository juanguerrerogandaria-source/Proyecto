# Tuya's Barber

Proyecto unificado: gestión completa (roles, cortes, horarios, ofertas) + flujo de
reserva en 4 pasos con el diseño "flow" (fondo morado animado, sparkles, barra de pasos).

## Cómo abrirlo

1. Descomprimí la carpeta dentro de tu servidor local (por ej. `htdocs/` de XAMPP).
2. Iniciá Apache y MySQL desde el panel de XAMPP.
3. En phpMyAdmin, creá la base `login_re_proyecto` e importá **en este orden**:
   1. `database/login_re_proyecto.sql`
   2. `database/alter_add_role.sql` (si tu dump ya trae la columna `role`, va a dar error de columna duplicada: ignoralo)
   3. `database/migration_cortes_horarios_ofertas.sql`
   4. `database/migration_categoria_cortes.sql`
   5. `database/migration_reservas.sql`
   6. `database/migration_flow_pago.sql`  ← **nueva** (método de pago y monto)
4. Entrá a `http://localhost/tuyas-barber-final/src/views/index.php`.

## Flujo de reserva (4 pasos)

```
reservar.php  →  horario.php  →  pago.php  →  comprobante.php
 (1. Corte)     (2. Horario)   (3. Datos      (4. Comprobante
  cortes reales   turnos según    y pago)        + email)
  de la BD, con   la tabla
  ofertas         horarios y
  aplicadas       ocupados
                  tachados
```

- **Paso 1**: lista los cortes cargados por el admin (hombre/mujer) con sus precios.
  Si hay una oferta activa vigente, el descuento ya viene aplicado y se muestra el
  precio tachado. Los botones "Reservar" de las galerías siguen preseleccionando
  el corte con `?corte_id=`.
- **Paso 2**: los turnos se generan cada 1 hora según la apertura y cierre de ese día
  en la tabla `horarios` (la misma que se edita desde el panel de admin). Si el día
  está cerrado, avisa. Los horarios ya reservados aparecen tachados y se revalida la
  disponibilidad justo antes de insertar para que dos personas no tomen el mismo turno.
- **Paso 3**: nombre, email, teléfono y notas (si el usuario está logueado, nombre y
  email vienen precargados) + método de pago (transferencia o efectivo).
- **Paso 4**: comprobante con código único (`TB-XXXXXX`), botón de imprimir y aviso
  de si el email salió o no.

## Envío del comprobante por email (sin librerías externas)

`includes/mailer.php` implementa el protocolo SMTP a mano con sockets
(EHLO → STARTTLS → AUTH LOGIN → MAIL FROM → RCPT TO → DATA), así que **no necesita
PHPMailer ni Composer**. Manda un email HTML con el comprobante (y versión texto plano).

Para activarlo con Gmail:

1. En tu cuenta de Google: Seguridad → activá la verificación en 2 pasos.
2. Buscá "Contraseñas de aplicaciones" y generá una (te da 16 letras).
3. En `includes/config.php` completá `SMTP_USUARIO` (tu Gmail) y `SMTP_PASSWORD`
   (las 16 letras sin espacios).

Si no configurás nada, intenta con `mail()` de PHP como plan B (en XAMPP normalmente
no funciona) y el comprobante en pantalla avisa que el email no salió.

## Qué se agregó / cambió respecto a la versión anterior

- `src/views/reservar.php` reescrito como paso 1 del flow (antes era un formulario único).
- Nuevos: `src/views/horario.php`, `src/views/pago.php`, `src/views/comprobante.php`,
  `includes/mailer.php`, `database/migration_flow_pago.sql`, `public/css/flow.css`.
- `includes/config.php` ahora tiene la configuración SMTP.
- `database/db.php`: se fijó el charset `utf8mb4` en la conexión (antes los acentos
  llegaban rotos a MySQL) y se agregaron `obtener_horas_ocupadas()`,
  `get_horario_para_fecha()` (tolerante a acentos), `generar_turnos_para_fecha()`,
  `get_mejor_oferta_activa()`, `get_email_usuario()` y `crear_reserva_flow()`.
- Se eliminó `public/css/reservar.css` (ya no lo usa nadie; el flow usa `flow.css`).
