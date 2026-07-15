<?php
// includes/config.php
// Configuración del envío de emails (comprobantes de reserva).
//
// CÓMO CONFIGURARLO CON GMAIL (recomendado para el proyecto):
//   1. Entrá a tu cuenta de Google -> Seguridad -> Verificación en 2 pasos (activala si no está).
//   2. Buscá "Contraseñas de aplicaciones" y generá una nueva (te da 16 letras).
//   3. Poné tu Gmail en SMTP_USUARIO y esas 16 letras (sin espacios) en SMTP_PASSWORD.
//
// Si dejás SMTP_USUARIO vacío, el sistema intenta usar mail() de PHP como plan B
// (en XAMPP normalmente no está configurado, así que el email no va a salir).

const SMTP_HOST     = 'smtp.gmail.com';
const SMTP_PUERTO   = 587;                 // 587 = STARTTLS
const SMTP_USUARIO  = '';                  // ej: 'tuyasbarber@gmail.com'
const SMTP_PASSWORD = '';                  // contraseña de aplicación de 16 letras
const SMTP_DESDE    = '';                  // remitente, normalmente igual a SMTP_USUARIO
const SMTP_NOMBRE   = "Tuya's Barber";
