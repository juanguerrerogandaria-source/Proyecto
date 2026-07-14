

ALTER TABLE `usuarios`
    ADD COLUMN `role` ENUM('user', 'admin', 'super_admin') NOT NULL DEFAULT 'user';

-- (Opcional) Convertir un usuario existente en super_admin, reemplazá 'maite' si hace falta:
-- UPDATE usuarios SET role = 'super_admin' WHERE usuario = 'maite' AND id = 1;
