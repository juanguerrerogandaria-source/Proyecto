-- ================================================================
-- Migración: cortes (servicios con foto/video), horarios y ofertas
-- Ejecutar en phpMyAdmin sobre la base `login_re_proyecto`
-- ================================================================

-- 1. Cortes / servicios (con imagen o video y precio)
CREATE TABLE IF NOT EXISTS `cortes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `precio` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `media_path` VARCHAR(255) DEFAULT NULL,
  `media_tipo` ENUM('imagen','video') DEFAULT NULL,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Horarios de atención (un registro por día de la semana)
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `dia` VARCHAR(20) NOT NULL,
  `hora_apertura` TIME DEFAULT NULL,
  `hora_cierre` TIME DEFAULT NULL,
  `cerrado` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dia` (`dia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `horarios` (`dia`, `hora_apertura`, `hora_cierre`, `cerrado`) VALUES
('Lunes',     '10:00:00', '20:00:00', 0),
('Martes',    '10:00:00', '20:00:00', 0),
('Miércoles', '10:00:00', '20:00:00', 0),
('Jueves',    '10:00:00', '20:00:00', 0),
('Viernes',   '10:00:00', '20:00:00', 0),
('Sábado',    '09:00:00', '18:00:00', 0),
('Domingo',   NULL,       NULL,       1)
ON DUPLICATE KEY UPDATE `dia` = VALUES(`dia`);

-- 3. Ofertas / promociones
CREATE TABLE IF NOT EXISTS `ofertas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(150) NOT NULL,
  `descripcion` TEXT,
  `descuento_porcentaje` INT NOT NULL DEFAULT 0,
  `fecha_inicio` DATE DEFAULT NULL,
  `fecha_fin` DATE DEFAULT NULL,
  `activa` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
