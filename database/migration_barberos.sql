-- Migración: barberos, horario propio de cada barbero y barbero en cada reserva.
-- Importar en phpMyAdmin DESPUÉS de las migraciones anteriores.
-- Si la corrés dos veces, el ALTER TABLE va a dar "columna/índice duplicado": ignoralo.

CREATE TABLE IF NOT EXISTS barberos (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nombre    VARCHAR(80)  NOT NULL,
    foto      VARCHAR(255) NULL,
    activo    TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Un renglón por barbero y por día de la semana (0 = domingo ... 6 = sábado, como date('w') de PHP)
CREATE TABLE IF NOT EXISTS barbero_horarios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    barbero_id    INT        NOT NULL,
    dia_semana    TINYINT    NOT NULL,
    hora_apertura TIME       NULL,
    hora_cierre   TIME       NULL,
    cerrado       TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_barbero_dia (barbero_id, dia_semana),
    CONSTRAINT fk_barbero_horarios_barbero
        FOREIGN KEY (barbero_id) REFERENCES barberos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cada reserva queda asociada a un barbero
ALTER TABLE reservas ADD COLUMN barbero_id INT NULL;
ALTER TABLE reservas ADD INDEX idx_reservas_barbero_fecha (barbero_id, fecha, hora);

-- Barbero inicial (se puede renombrar y cambiarle la foto desde el panel)
INSERT INTO barberos (nombre)
SELECT 'Santiago Tuya' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM barberos);

-- Su horario arranca copiando el horario general que ya tenías en la tabla `horarios`
INSERT INTO barbero_horarios (barbero_id, dia_semana, hora_apertura, hora_cierre, cerrado)
SELECT
    (SELECT MIN(id) FROM barberos),
    CASE
        WHEN h.dia LIKE 'Lu%'  THEN 1
        WHEN h.dia LIKE 'Mar%' THEN 2
        WHEN h.dia LIKE 'Mi%'  THEN 3
        WHEN h.dia LIKE 'Ju%'  THEN 4
        WHEN h.dia LIKE 'Vi%'  THEN 5
        WHEN h.dia LIKE 'S%'   THEN 6
        WHEN h.dia LIKE 'Do%'  THEN 0
    END,
    h.hora_apertura,
    h.hora_cierre,
    h.cerrado
FROM horarios h
WHERE h.dia REGEXP '^(Lu|Mar|Mi|Ju|Vi|S|Do)'
  AND NOT EXISTS (SELECT 1 FROM barbero_horarios);

-- Las reservas que ya existían pasan a ser de ese barbero
UPDATE reservas SET barbero_id = (SELECT MIN(id) FROM barberos) WHERE barbero_id IS NULL;
