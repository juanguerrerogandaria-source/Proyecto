-- Ejecutar en phpMyAdmin sobre la base `login_re_proyecto`
-- Agrega método de pago y monto a las reservas (para el flujo de reserva en 4 pasos)

ALTER TABLE `reservas`
    ADD COLUMN `metodo_pago` ENUM('transferencia', 'efectivo') DEFAULT NULL AFTER `notas`,
    ADD COLUMN `monto` DECIMAL(10,2) DEFAULT NULL AFTER `metodo_pago`;
