-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql304.infinityfree.com
-- Tiempo de generación: 23-10-2026 a las 16:32:26
-- Versión del servidor: 11.4.13-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_42417739_proyecto_web`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barberos`
--

CREATE TABLE `barberos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `barberos`
--

INSERT INTO `barberos` (`id`, `nombre`, `foto`, `activo`, `creado_en`) VALUES
(1, 'Santiago Tuya', 'uploads/barberos/barbero_80b9c9667873faa9.jpg', 1, '2026-10-22 22:45:37'),
(2, 'Emilio Carro', 'uploads/barberos/barbero_33528cd70ef211d5.jpg', 1, '2026-10-23 16:03:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barbero_horarios`
--

CREATE TABLE `barbero_horarios` (
  `id` int(11) NOT NULL,
  `barbero_id` int(11) NOT NULL,
  `dia_semana` tinyint(4) NOT NULL,
  `hora_apertura` time DEFAULT NULL,
  `hora_cierre` time DEFAULT NULL,
  `cerrado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `barbero_horarios`
--

INSERT INTO `barbero_horarios` (`id`, `barbero_id`, `dia_semana`, `hora_apertura`, `hora_cierre`, `cerrado`) VALUES
(1, 1, 1, '18:30:00', '21:30:00', 0),
(2, 1, 2, '18:30:00', '21:30:00', 0),
(3, 1, 3, '18:30:00', '21:30:00', 0),
(4, 1, 4, '18:30:00', '21:30:00', 0),
(5, 1, 5, '18:30:00', '21:30:00', 0),
(6, 1, 6, '08:30:00', '21:30:00', 0),
(7, 1, 0, '08:30:00', '21:30:00', 0),
(8, 2, 1, '18:30:00', '21:30:00', 0),
(9, 2, 2, '18:30:00', '21:30:00', 0),
(10, 2, 3, '18:30:00', '21:30:00', 0),
(11, 2, 4, '18:30:00', '21:30:00', 0),
(12, 2, 5, '18:30:00', '21:30:00', 0),
(13, 2, 6, NULL, NULL, 1),
(14, 2, 0, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cortes`
--

CREATE TABLE `cortes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` enum('hombre','mujer') NOT NULL DEFAULT 'hombre',
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `media_path` varchar(255) DEFAULT NULL,
  `media_tipo` enum('imagen','video') DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cortes`
--

INSERT INTO `cortes` (`id`, `nombre`, `categoria`, `descripcion`, `precio`, `media_path`, `media_tipo`, `creado_en`) VALUES
(2, 'Mullet bajo', 'hombre', 'El mullet bajo es una versiÃ³n moderna y sutil del clÃ¡sico corte asimÃ©trico de los aÃ±os 80', '300.00', 'uploads/cortes/corte_6a7a52935ad5d9.49803572.jpg', 'imagen', '2026-08-10 22:37:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
  `dia` varchar(20) NOT NULL,
  `hora_apertura` time DEFAULT NULL,
  `hora_cierre` time DEFAULT NULL,
  `cerrado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `dia`, `hora_apertura`, `hora_cierre`, `cerrado`) VALUES
(1, 'Lunes', '18:30:00', '21:30:00', 0),
(2, 'Martes', '18:30:00', '21:30:00', 0),
(3, 'Miércoles', '18:30:00', '21:30:00', 0),
(4, 'Jueves', '18:30:00', '21:30:00', 0),
(5, 'Viernes', '18:30:00', '21:30:00', 0),
(6, 'Sábado', '08:30:00', '21:30:00', 0),
(7, 'Domingo', '08:30:00', '21:30:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas`
--

CREATE TABLE `ofertas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `descuento_porcentaje` int(11) NOT NULL DEFAULT 0,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros_pendientes`
--

CREATE TABLE `registros_pendientes` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `codigo_hash` varchar(255) NOT NULL,
  `expira` datetime NOT NULL,
  `intentos` tinyint(4) NOT NULL DEFAULT 0,
  `enviado_at` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `nombre_cliente` varchar(100) NOT NULL,
  `email_cliente` varchar(100) NOT NULL,
  `telefono_cliente` varchar(30) DEFAULT NULL,
  `corte_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `notas` text DEFAULT NULL,
  `metodo_pago` enum('transferencia','efectivo') DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `estado` enum('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `barbero_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `codigo`, `nombre_cliente`, `email_cliente`, `telefono_cliente`, `corte_id`, `fecha`, `hora`, `notas`, `metodo_pago`, `monto`, `estado`, `creado_en`, `barbero_id`) VALUES
(4, 'TB-760EEA', 'Ignacio Gandaria', 'juanitogg317@gmail.com', NULL, 2, '2026-08-15', '13:30:00', NULL, 'transferencia', '300.00', 'cancelada', '2026-08-11 14:20:20', 1),
(5, 'TB-B3C153', 'Ignacio Gandaria', 'juanitogg317@gmail.com', NULL, 2, '2026-08-15', '16:00:00', NULL, 'efectivo', '300.00', 'cancelada', '2026-08-11 14:22:54', 1),
(6, 'TB-F8AF4C', 'Santi Lecor', 'santiagotuya9@gmail.com', '098 161 935', 2, '2026-08-15', '12:30:00', 'Cuidado con cortar la oreja', 'transferencia', '300.00', 'cancelada', '2026-08-12 01:06:27', 1),
(7, 'TB-7E1314', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-08-12', '19:00:00', NULL, 'efectivo', '300.00', 'cancelada', '2026-08-12 12:57:03', 1),
(8, 'TB-6339C5', 'Emilio Carro', 'emiliocarro2008@gmail.com', '092928272', 2, '2026-08-13', '21:00:00', 'oo2', 'efectivo', '300.00', 'cancelada', '2026-08-13 14:27:38', 1),
(9, 'TB-7B0B2D', 'Emilio Carro', 'emiliocarro2008@gmail.com', NULL, 2, '2026-08-13', '19:00:00', NULL, 'transferencia', '300.00', 'cancelada', '2026-08-13 14:27:53', 1),
(10, 'TB-08BC1C', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-08-27', '19:00:00', NULL, 'transferencia', '300.00', 'cancelada', '2026-08-27 14:36:09', 1),
(11, 'TB-54ACC2', 'comunale', 'kaka@gmail.com', '092928262', 2, '2026-08-31', '20:30:00', 'oo2', 'efectivo', '300.00', 'cancelada', '2026-08-31 14:52:34', 1),
(12, 'TB-D18DA9', 'gbraito18', 'gbraito18@gmail.com', NULL, 2, '2026-09-23', '19:30:00', NULL, 'transferencia', '300.00', 'confirmada', '2026-09-07 15:12:48', 1),
(13, 'TB-FE0C99', 'gbraito18', 'gbraito18@gmail.com', NULL, 2, '2026-09-07', '18:30:00', NULL, 'transferencia', '300.00', 'pendiente', '2026-09-07 15:12:58', 1),
(14, 'TB-E660B9', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-09-09', '18:30:00', NULL, 'transferencia', '300.00', 'confirmada', '2026-09-09 17:03:12', 1),
(15, 'TB-636B14', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-09-09', '19:00:00', NULL, 'efectivo', '300.00', 'cancelada', '2026-09-09 17:03:24', 1),
(16, 'TB-291164', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-09-10', '18:30:00', NULL, 'efectivo', '300.00', 'pendiente', '2026-09-10 14:53:32', 1),
(17, 'TB-7C607E', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-09-11', '19:00:00', NULL, 'efectivo', '300.00', 'confirmada', '2026-09-11 23:27:46', 1),
(18, 'TB-8BC24D', 'santi', 'santiagotuya09@gmail.com', NULL, 2, '2026-09-11', '20:00:00', NULL, 'transferencia', '300.00', 'pendiente', '2026-09-12 01:56:16', 1),
(19, 'TB-59C23A', 'gbraito18', 'gbraito18@gmail.com', '098101072', NULL, '2026-09-18', '19:30:00', 'Quiero verme hermoso', 'transferencia', '600.00', 'pendiente', '2026-09-17 15:05:15', 1),
(20, 'TB-320F7E', 'Ignacio Gandaria', 'juanitogg317@gmail.com', NULL, 2, '2026-10-05', '20:30:00', NULL, 'transferencia', '300.00', 'confirmada', '2026-10-05 16:24:47', 1),
(21, 'TB-39CF18', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-10-09', '21:00:00', NULL, 'efectivo', '300.00', 'confirmada', '2026-10-06 14:23:56', 1),
(22, 'TB-979A9F', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-10-11', '21:00:00', NULL, 'transferencia', '300.00', 'confirmada', '2026-10-06 18:03:30', 1),
(23, 'TB-632F57', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-10-24', '15:00:00', NULL, 'efectivo', '300.00', 'confirmada', '2026-10-06 23:16:20', 1),
(24, 'TB-4AB796', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-10-24', '18:30:00', NULL, 'transferencia', '300.00', 'confirmada', '2026-10-07 01:11:54', 1),
(25, 'TB-2026100720X171', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-10-07', '20:30:00', NULL, 'efectivo', '300.00', 'confirmada', '2026-10-07 13:23:22', 1),
(26, 'TB-2026102021M101', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-10-20', '21:00:00', NULL, 'efectivo', '300.00', 'confirmada', '2026-10-20 16:51:29', 1),
(27, 'TB-2026102120X761', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-10-21', '20:30:00', NULL, 'transferencia', '300.00', 'pendiente', '2026-10-21 15:11:11', 1),
(28, 'TB-2026102318V591', 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', NULL, 2, '2026-10-23', '18:30:00', NULL, 'efectivo', '300.00', 'confirmada', '2026-10-23 13:15:11', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','super_admin') NOT NULL DEFAULT 'user',
  `email_verificado` tinyint(1) NOT NULL DEFAULT 0,
  `codigo_verificacion` varchar(255) DEFAULT NULL,
  `codigo_expira` datetime DEFAULT NULL,
  `codigo_intentos` tinyint(4) NOT NULL DEFAULT 0,
  `codigo_enviado_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish2_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `email`, `password`, `role`, `email_verificado`, `codigo_verificacion`, `codigo_expira`, `codigo_intentos`, `codigo_enviado_at`) VALUES
(4, 'Juan Guerrero', 'juanguerrerogandaria@gmail.com', '$2y$10$NejEXu9Ao9htJ7SLUW3Bou75T1Bd6n9PLfxdMVz6LE9tHoCvwLpP.', 'super_admin', 1, NULL, NULL, 0, NULL),
(6, 'Emilio Carro', 'emiliocarro2008@gmail.com', '$2y$10$nePAFnml/5cDhLMp/qG22.UEzAMUlPyTaDWnrm0WONRo2VHE6/H42', 'super_admin', 1, NULL, NULL, 0, NULL),
(7, 'Ignacio Gandaria', 'juanitogg317@gmail.com', '$2y$10$c2/DqEKhq1s2Dri1.JjbduoCn/vyD.j/3DrSk4PFXndQpLFNi6HKG', 'user', 1, NULL, NULL, 0, NULL),
(8, 'adanib', 'adanbertoletti7@gmail.com', '$2y$10$5YMR3tW5S7H.m0WdpsU6c.kWn2tVW8iMDdYcPocrs512yChb0T/Me', 'user', 1, NULL, NULL, 0, NULL),
(9, 'gbraito18', 'gbraito18@gmail.com', '$2y$10$hWkYAj8M8wp1qnffJcFXtOkpwxkX6pz/6oISn.gjcmqn610JJRJ/i', 'user', 1, NULL, NULL, 0, NULL),
(10, 'maite', 'maite7879@gmail.com', '$2y$10$rGtVsdGgA7W.iJq29KH/budGxgE2PNxdnsP8ynHqZfUDRR9hRHfYi', 'super_admin', 1, NULL, NULL, 0, NULL),
(11, 'comunale', 'kaka@gmail.com', '$2y$10$2lVbEBFTt8JVc8jtT8EYTO4SYRTXtopauUHbjoJBiIghPe9A.mDmq', 'user', 1, NULL, NULL, 0, NULL),
(12, 'santi', 'santiagotuya09@gmail.com', '$2y$10$93X8E8ToQly4DtyeDkOBxO7edhHxMCjLmVavgMxEQWGih6WK8w24m', 'admin', 1, NULL, NULL, 0, NULL),
(13, 'Tuyas Barber', 'tuyasbarber@gmail.com', '$2y$12$KxyPB8gh3IhWzryt5eLwfuyQF47k2gSk/UFSW8SjFcKtIiVbHw54i', 'admin', 1, NULL, NULL, 0, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `barberos`
--
ALTER TABLE `barberos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `barbero_horarios`
--
ALTER TABLE `barbero_horarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_barbero_dia` (`barbero_id`,`dia_semana`);

--
-- Indices de la tabla `cortes`
--
ALTER TABLE `cortes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dia` (`dia`);

--
-- Indices de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `registros_pendientes`
--
ALTER TABLE `registros_pendientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pendiente_email` (`email`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `uq_reservas_codigo` (`codigo`),
  ADD KEY `corte_id` (`corte_id`),
  ADD KEY `idx_reservas_barbero_fecha` (`barbero_id`,`fecha`,`hora`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `barberos`
--
ALTER TABLE `barberos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `barbero_horarios`
--
ALTER TABLE `barbero_horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `cortes`
--
ALTER TABLE `cortes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `registros_pendientes`
--
ALTER TABLE `registros_pendientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `barbero_horarios`
--
ALTER TABLE `barbero_horarios`
  ADD CONSTRAINT `fk_barbero_horarios_barbero` FOREIGN KEY (`barbero_id`) REFERENCES `barberos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reservas_corte` FOREIGN KEY (`corte_id`) REFERENCES `cortes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
