-- Ejecutar en phpMyAdmin sobre la base `login_re_proyecto`
-- Agrega la categoría (hombre/mujer) a la tabla de cortes

ALTER TABLE `cortes`
    ADD COLUMN `categoria` ENUM('hombre', 'mujer') NOT NULL DEFAULT 'hombre' AFTER `nombre`;
