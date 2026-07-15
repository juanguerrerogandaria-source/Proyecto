<?php
// includes/mailer.php
// Envío de emails por SMTP escrito a mano con sockets (sin PHPMailer ni Composer).
// Habla el protocolo SMTP directamente: EHLO -> STARTTLS -> AUTH LOGIN -> MAIL FROM -> RCPT TO -> DATA.
// Funciona con Gmail usando una contraseña de aplicación (ver includes/config.php).

require_once __DIR__ . '/config.php';

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
function enviar_comprobante_reserva(array $r): bool
{
    $filas = '';

    $agregar = function (string $etiqueta, string $valor) use (&$filas) {
        $filas .= '<tr>'
            . '<td style="padding:8px 0; color:#888; font-size:13px;">' . htmlspecialchars($etiqueta) . '</td>'
            . '<td style="padding:8px 0; color:#222; font-size:14px; font-weight:bold; text-align:right;">' . htmlspecialchars($valor) . '</td>'
            . '</tr>';
    };

    $agregar('Código de reserva', $r['codigo']);
    $agregar('Cliente', $r['nombre']);
    $agregar('Corte', $r['corte'] . ' (' . ucfirst($r['categoria']) . ')');
    $agregar('Fecha', $r['fecha_legible']);
    $agregar('Hora', $r['hora'] . ' hs');
    $agregar('Método de pago', ucfirst($r['metodo_pago']));
    if (!empty($r['notas'])) {
        $agregar('Notas', $r['notas']);
    }

    $html = '
    <div style="background:#f4f4f4; padding:24px; font-family:Arial,sans-serif;">
      <div style="max-width:480px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden; border:1px solid #e5e5e5;">
        <div style="background:#111; padding:20px; text-align:center;">
          <span style="color:#a855f7; font-size:20px; font-weight:bold; letter-spacing:1px;">Tuya&#39;s Barber</span>
        </div>
        <div style="padding:24px;">
          <h2 style="margin:0 0 4px; color:#222;">¡Turno confirmado!</h2>
          <p style="margin:0 0 20px; color:#666; font-size:14px;">Hola ' . htmlspecialchars($r['nombre']) . ', tu reserva quedó registrada. Guardá este comprobante.</p>
          <table style="width:100%; border-collapse:collapse; border-top:1px solid #eee;">' . $filas . '</table>
          <div style="margin-top:16px; padding:14px; background:#f7f2ff; border-radius:8px; text-align:center;">
            <span style="color:#666; font-size:13px;">Monto a pagar</span><br>
            <span style="color:#7c3aed; font-size:24px; font-weight:bold;">$' . number_format((float) $r['monto'], 0, ',', '.') . '</span>
          </div>
          <p style="margin:20px 0 0; color:#999; font-size:12px; text-align:center;">Te esperamos puntual. Ante cualquier cambio, avisanos con anticipación.</p>
        </div>
      </div>
    </div>';

    return enviar_email($r['email'], "Comprobante de reserva {$r['codigo']} - Tuya's Barber", $html);
}
