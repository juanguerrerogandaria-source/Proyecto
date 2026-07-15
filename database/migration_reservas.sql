-- Ejecutar en phpMyAdmin sobre la base `login_re_proyecto`
-- Tabla de reservas de turnos

CREATE TABLE IF NOT EXISTS `reservas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(12) NOT NULL,
  `nombre_cliente` VARCHAR(100) NOT NULL,
  `email_cliente` VARCHAR(100) NOT NULL,
  `telefono_cliente` VARCHAR(30) DEFAULT NULL,
  `corte_id` INT DEFAULT NULL,
  `fecha` DATE NOT NULL,
  `hora` TIME NOT NULL,
  `notas` TEXT,
  `estado` ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente',
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `corte_id` (`corte_id`),
  CONSTRAINT `fk_reservas_corte` FOREIGN KEY (`corte_id`) REFERENCES `cortes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
