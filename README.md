# Tuya's Barber

Página principal rediseñada respetando la identidad de marca (morado `#7c3aed` / negro,
tipografía Cinzel + Inter, logo de calavera) con la misma estructura de secciones que
un sitio de barbería profesional: hero, quiénes somos, servicios, horarios y footer.

## Cómo abrirlo

1. Descomprimí la carpeta dentro de tu servidor local (por ej. `htdocs/` de XAMPP o `www/` de WAMP/Laragon).
2. Abrí la carpeta en VS Code.
3. Iniciá Apache y MySQL, creá la base `login_re_proyecto` con una tabla `usuarios`
   (columnas: `id`, `usuario`, `email`, `password`, `role`).
4. Entrá a `src/views/pagina_principal.php` desde el navegador (por ej.
   `http://localhost/proyecto/src/views/pagina_principal.php`).

## Estructura

```
proyecto/
├── includes/auth.php              → sesión y control de roles
├── database/db.php                → conexión y consultas (PDO/mysqli seguro)
├── src/views/
│   ├── pagina_principal.php       → landing rediseñada
│   ├── loginbarber.php
│   ├── registrarse.php
│   ├── admin_dashboard.php
│   └── superadmin_dashboard.php
└── public/
    ├── css/
    │   ├── pagina_principal.css   → estilo nuevo de la landing
    │   ├── loginbarber.css
    │   └── registrarse.css
    └── img/tuyasbarber.jpeg       → logo
```

## Notas de diseño

- Paleta: negro `#08070a` de fondo, acentos morados `#7c3aed` / `#a855f7`, tal cual
  ya usabas en `loginbarber.css` y `registrarse.css`.
- El hero usa el propio logo con un glow morado en vez de una foto de local (no había
  ninguna en el proyecto). Si tenés fotos reales de la barbería, se pueden usar como
  fondo del hero reemplazando el bloque `.hero` en el CSS.
- La navbar detecta si hay sesión iniciada (`esta_logueado()`) y muestra "Iniciar sesión /
  Registrarse" o el nombre del usuario logueado, con acceso directo al panel si es admin.
