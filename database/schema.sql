-- Base ya existente: login_re_proyecto
-- Tabla usuarios (ya la tenías creada, se deja de referencia):
-- CREATE TABLE usuarios (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     usuario VARCHAR(50) NOT NULL UNIQUE,
--     email VARCHAR(100) NOT NULL UNIQUE,
--     password VARCHAR(255) NOT NULL,
--     role VARCHAR(20) NOT NULL DEFAULT 'user'
-- );

-- Tabla nueva: reservas de turnos
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    servicio VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    hora VARCHAR(5) NOT NULL,
    metodo_pago VARCHAR(20) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY fecha_hora_unica (fecha, hora),
    FOREIGN KEY (user_id) REFERENCES usuarios(id)
);
