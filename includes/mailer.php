<?php
// includes/mailer.php
// Envío de emails por SMTP escrito a mano con sockets (sin PHPMailer ni Composer).
// Habla el protocolo SMTP directamente: EHLO -> STARTTLS -> AUTH LOGIN -> MAIL FROM -> RCPT TO -> DATA.
// Funciona con Gmail usando una contraseña de aplicación (ver includes/config.php).

$archivoConfig = __DIR__ . '/config.php';
if (is_file($archivoConfig)) {
    require_once $archivoConfig;
}

/**
 * Envía un email. Devuelve true si salió bien, false si no.
 * Si hay SMTP configurado lo usa; si no, intenta con mail() de PHP.
 */
function enviar_email(string $para, string $asunto, string $html, string $textoPlano = ''): bool
{
    if (SMTP_USUARIO !== '' && SMTP_PASSWORD !== '') {
        return enviar_email_smtp($para, $asunto, $html, $textoPlano);
    }

    // Plan B: mail() nativo (solo funciona si el servidor tiene sendmail/SMTP en php.ini)
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SMTP_NOMBRE . " <no-responder@tuyasbarber.local>\r\n";

    return @mail($para, codificar_asunto($asunto), $html, $headers);
}

/**
 * Cliente SMTP mínimo con STARTTLS y AUTH LOGIN.
 */
function enviar_email_smtp(string $para, string $asunto, string $html, string $textoPlano = ''): bool
{
    $desde = SMTP_DESDE !== '' ? SMTP_DESDE : SMTP_USUARIO;

    $socket = @fsockopen(SMTP_HOST, SMTP_PUERTO, $errno, $errstr, 15);
    if (!$socket) {
        error_log("Mailer: no se pudo conectar a " . SMTP_HOST . ":" . SMTP_PUERTO . " ($errno $errstr)");
        return false;
    }

    stream_set_timeout($socket, 15);

    try {
        if (!smtp_esperar($socket, '220')) return false;

        // Saludo inicial
        smtp_enviar($socket, 'EHLO tuyasbarber.local');
        if (!smtp_esperar($socket, '250')) return false;

        // Pasamos la conexión a TLS (cifrada)
        smtp_enviar($socket, 'STARTTLS');
        if (!smtp_esperar($socket, '220')) return false;

        if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log('Mailer: falló el handshake TLS');
            return false;
        }

        // Hay que volver a saludar después del TLS
        smtp_enviar($socket, 'EHLO tuyasbarber.local');
        if (!smtp_esperar($socket, '250')) return false;

        // Login (usuario y contraseña van en base64, es parte del protocolo)
        smtp_enviar($socket, 'AUTH LOGIN');
        if (!smtp_esperar($socket, '334')) return false;

        smtp_enviar($socket, base64_encode(SMTP_USUARIO));
        if (!smtp_esperar($socket, '334')) return false;

        smtp_enviar($socket, base64_encode(SMTP_PASSWORD));
        if (!smtp_esperar($socket, '235')) {
            error_log('Mailer: usuario o contraseña rechazados por el servidor SMTP');
            return false;
        }

        // Sobre: remitente y destinatario
        smtp_enviar($socket, "MAIL FROM:<$desde>");
        if (!smtp_esperar($socket, '250')) return false;

        smtp_enviar($socket, "RCPT TO:<$para>");
        if (!smtp_esperar($socket, '250')) return false;

        // Contenido del mensaje
        smtp_enviar($socket, 'DATA');
        if (!smtp_esperar($socket, '354')) return false;

        $mensaje = construir_mensaje_mime($desde, $para, $asunto, $html, $textoPlano);

        // En SMTP una línea que empieza con "." corta el mensaje, así que se duplica ("dot stuffing")
        $mensaje = preg_replace('/^\./m', '..', $mensaje);

        smtp_enviar($socket, $mensaje . "\r\n.");
        if (!smtp_esperar($socket, '250')) return false;

        smtp_enviar($socket, 'QUIT');

        return true;
    } finally {
        fclose($socket);
    }
}

// ---------------------------------------------------------------
// Ayudantes internos del protocolo
// ---------------------------------------------------------------

function smtp_enviar($socket, string $linea): void
{
    fwrite($socket, $linea . "\r\n");
}

// Lee la respuesta del servidor y verifica que empiece con el código esperado (ej: 250)
function smtp_esperar($socket, string $codigoEsperado): bool
{
    $respuesta = '';

    while (($linea = fgets($socket, 515)) !== false) {
        $respuesta .= $linea;
        // Las respuestas multilínea llevan "-" después del código; la última lleva espacio
        if (isset($linea[3]) && $linea[3] === ' ') {
            break;
        }
    }

    if (strpos($respuesta, $codigoEsperado) !== 0) {
        error_log("Mailer: esperaba $codigoEsperado y el servidor respondió: " . trim($respuesta));
        return false;
    }

    return true;
}

// Arma el email completo con versión texto y versión HTML (multipart/alternative)
function construir_mensaje_mime(string $desde, string $para, string $asunto, string $html, string $textoPlano): string
{
    $limite = 'tb-' . bin2hex(random_bytes(12));

    if ($textoPlano === '') {
        $textoPlano = trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html)));
    }

    $m  = "From: " . codificar_asunto(SMTP_NOMBRE) . " <$desde>\r\n";
    $m .= "To: <$para>\r\n";
    $m .= "Subject: " . codificar_asunto($asunto) . "\r\n";
    $m .= "Date: " . date('r') . "\r\n";
    $m .= "Message-ID: <" . bin2hex(random_bytes(8)) . "@tuyasbarber.local>\r\n";
    $m .= "MIME-Version: 1.0\r\n";
    $m .= "Content-Type: multipart/alternative; boundary=\"$limite\"\r\n";
    $m .= "\r\n";
    $m .= "--$limite\r\n";
    $m .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $m .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $m .= chunk_split(base64_encode($textoPlano)) . "\r\n";
    $m .= "--$limite\r\n";
    $m .= "Content-Type: text/html; charset=UTF-8\r\n";
    $m .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $m .= chunk_split(base64_encode($html)) . "\r\n";
    $m .= "--$limite--";

    return $m;
}

// Codifica el asunto para que los acentos y la ñ lleguen bien
function codificar_asunto(string $texto): string
{
    return '=?UTF-8?B?' . base64_encode($texto) . '?=';
}

// ---------------------------------------------------------------
// Comprobante de reserva
// ---------------------------------------------------------------

// Arma el HTML del comprobante y lo manda al cliente. Devuelve true/false.
// ---------------------------------------------------------------
// Plantilla de los emails (estilo de la página)
// ---------------------------------------------------------------

function email_filas(array $pares): string
{
    $html = '';
    foreach ($pares as [$etiqueta, $valor]) {
        $html .= '<tr>'
            . '<td style="padding:11px 0; border-bottom:1px solid #26232d; color:#b0a8b8; font-size:13px;">' . htmlspecialchars($etiqueta) . '</td>'
            . '<td align="right" style="padding:11px 0; border-bottom:1px solid #26232d; color:#f5f0f5; font-size:14px; font-weight:bold;">' . htmlspecialchars($valor) . '</td>'
            . '</tr>';
    }
    return $html;
}

function email_plantilla(string $titulo, string $colorTitulo, string $mensaje, array $pares, string $extra = '', string $pie = ''): string
{
    $logo   = 'https://tuyasbarber.kesug.com/public/img/logo_email.jpg';
    $fuente = 'font-family:Arial,Helvetica,sans-serif;';

    return '
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0a0a0c;">
  <tr><td align="center" style="padding:24px 12px;">
    <table role="presentation" width="520" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:520px; background:#121116; border:1px solid #3b2a5c; border-radius:16px;">
      <tr><td align="center" style="background:#000000; padding:26px 20px 18px; border-radius:16px 16px 0 0;">
        <img src="' . $logo . '" width="88" height="88" alt="Tuya&#39;s Barber" style="display:block; border:2px solid #a855f7; border-radius:50%;">
        <div style="margin-top:12px; font-family:Georgia,serif; font-size:22px; letter-spacing:2px; color:#f5f0f5; font-variant:small-caps;">Tuya&#39;s Barber</div>
      </td></tr>
      <tr><td style="height:3px; line-height:3px; font-size:0; background:#7c3aed;">&nbsp;</td></tr>
      <tr><td style="padding:28px 28px 8px; ' . $fuente . '">
        <h1 style="margin:0 0 8px; font-size:22px; color:' . $colorTitulo . ';">' . htmlspecialchars($titulo) . '</h1>
        <p style="margin:0 0 20px; font-size:14px; line-height:1.5; color:#b0a8b8;">' . htmlspecialchars($mensaje) . '</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #26232d;">' . email_filas($pares) . '</table>
        ' . $extra . '
      </td></tr>
      <tr><td align="center" style="padding:20px 28px 28px; ' . $fuente . ' font-size:12px; line-height:1.5; color:#756c80;">' . htmlspecialchars($pie) . '</td></tr>
    </table>
  </td></tr>
</table>';
}

// ---------------------------------------------------------------
// Reserva recibida (al reservar)
// ---------------------------------------------------------------

function enviar_comprobante_reserva(array $r): bool
{
    $pares = [
        ['Código de reserva', $r['codigo']],
        ['Cliente', $r['nombre']],
        ['Corte', $r['corte'] . ' (' . ucfirst($r['categoria']) . ')'],
        ['Fecha', $r['fecha_legible']],
        ['Hora', $r['hora'] . ' hs'],
        ['Método de pago', ucfirst($r['metodo_pago'])],
    ];
    if (!empty($r['notas'])) {
        $pares[] = ['Notas', $r['notas']];
    }

    $monto = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:20px;">'
        . '<tr><td align="center" style="background:#1b1424; border:1px solid #3b2a5c; border-radius:10px; padding:14px;">'
        . '<div style="font-size:13px; color:#b0a8b8;">Monto a pagar</div>'
        . '<div style="font-size:26px; font-weight:bold; color:#a855f7;">$' . number_format((float) $r['monto'], 0, ',', '.') . '</div>'
        . '</td></tr></table>';

    $html = email_plantilla(
        '¡Reserva recibida!',
        '#a855f7',
        'Hola ' . $r['nombre'] . ', recibimos tu reserva y quedó pendiente de confirmación. Te avisaremos por email cuando la confirmemos. Guardá este comprobante.',
        $pares,
        $monto,
        "Ante cualquier cambio, avisanos con anticipación.\nTuya's Barber · tuyasbarber.kesug.com"
    );

    return enviar_email($r['email'], "Comprobante de reserva {$r['codigo']} - Tuya's Barber", $html);
}

// ---------------------------------------------------------------
// Turno confirmado / cancelado (desde el panel)
// ---------------------------------------------------------------

function enviar_estado_reserva(array $r, string $estado): bool
{
    if (empty($r['email_cliente']) || !filter_var($r['email_cliente'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    [$anio, $mes, $dia] = explode('-', $r['fecha']);
    $fecha = (int) $dia . ' de ' . $meses[(int) $mes - 1] . ' de ' . $anio;
    $hora  = substr($r['hora'], 0, 5);

    $confirmada = ($estado === 'confirmada');
    $titulo  = $confirmada ? '¡Tu turno fue confirmado!' : 'Tu turno fue cancelado';
    $mensaje = $confirmada
        ? 'tu reserva está confirmada. Te esperamos puntual.'
        : 'lamentablemente tuvimos que cancelar tu reserva. Podés reservar otro horario cuando quieras desde nuestra web.';
    $asunto  = ($confirmada ? 'Turno confirmado' : 'Turno cancelado') . " {$r['codigo']} - Tuya's Barber";

    $html = email_plantilla(
        $titulo,
        $confirmada ? '#4ade80' : '#f87171',
        'Hola ' . $r['nombre_cliente'] . ', ' . $mensaje,
        [
            ['Código de reserva', $r['codigo']],
            ['Corte', ($r['corte_nombre'] ?? '—') . ' (' . ucfirst($r['corte_categoria'] ?? '') . ')'],
            ['Fecha', $fecha],
            ['Hora', $hora . ' hs'],
        ],
        '',
        "Tuya's Barber · tuyasbarber.kesug.com"
    );

    return enviar_email($r['email_cliente'], $asunto, $html);
}

function enviar_codigo_verificacion(string $nombre, string $email, string $codigo): bool
{
    $caja = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:20px;">'
        . '<tr><td align="center" style="background:#1b1424; border:1px solid #3b2a5c; border-radius:10px; padding:18px;">'
        . '<div style="font-size:13px; color:#b0a8b8; margin-bottom:6px;">Tu código de verificación</div>'
        . '<div style="font-family:\'Courier New\',monospace; font-size:34px; font-weight:bold; letter-spacing:8px; color:#a855f7;">' . htmlspecialchars($codigo) . '</div>'
        . '</td></tr></table>';

    $html = email_plantilla(
        'Verificá tu correo',
        '#f5f0f5',
        'Hola ' . $nombre . ', para terminar de crear tu cuenta ingresá este código en la página. Vence en 15 minutos.',
        [],
        $caja,
        "Si no creaste una cuenta en Tuya's Barber, ignorá este mensaje."
    );

    return enviar_email($email, "Tu código de verificación es $codigo - Tuya's Barber", $html);
}