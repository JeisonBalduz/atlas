-- Migración PostgreSQL: Tabla local de días feriados (nacionales, contractuales INCES y móviles)
-- Idempotente y 100% offline.

CREATE TABLE IF NOT EXISTS public.dias_feriados (
    feriado_id SERIAL PRIMARY KEY,
    fecha DATE NULL,
    mes SMALLINT NULL CHECK (mes BETWEEN 1 AND 12),
    dia SMALLINT NULL CHECK (dia BETWEEN 1 AND 31),
    tipo VARCHAR(30) NOT NULL DEFAULT 'Nacional',
    descripcion VARCHAR(150) NOT NULL,
    es_recurrente BOOLEAN NOT NULL DEFAULT FALSE,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT chk_dias_feriados_modalidad CHECK (
        (es_recurrente = TRUE AND mes IS NOT NULL AND dia IS NOT NULL AND fecha IS NULL)
        OR (es_recurrente = FALSE AND fecha IS NOT NULL)
    )
);

CREATE UNIQUE INDEX IF NOT EXISTS uq_dias_feriados_recurrente
    ON public.dias_feriados (mes, dia)
    WHERE es_recurrente = TRUE;

CREATE UNIQUE INDEX IF NOT EXISTS uq_dias_feriados_fecha
    ON public.dias_feriados (fecha)
    WHERE es_recurrente = FALSE AND fecha IS NOT NULL;

CREATE INDEX IF NOT EXISTS idx_dias_feriados_activo
    ON public.dias_feriados (activo);

-- Feriados fijos nacionales (Venezuela) y contractuales INCES
INSERT INTO public.dias_feriados (fecha, mes, dia, tipo, descripcion, es_recurrente, activo)
VALUES
    (NULL, 1, 1, 'Nacional', 'Año Nuevo', TRUE, TRUE),
    (NULL, 3, 19, 'Contractual INCES', 'Día de San José (Convención Colectiva INCES)', TRUE, TRUE),
    (NULL, 4, 19, 'Nacional', 'Declaración de la Independencia', TRUE, TRUE),
    (NULL, 5, 1, 'Nacional', 'Día del Trabajador', TRUE, TRUE),
    (NULL, 6, 24, 'Nacional', 'Batalla de Carabobo', TRUE, TRUE),
    (NULL, 7, 5, 'Nacional', 'Firma del Acta de Independencia', TRUE, TRUE),
    (NULL, 7, 24, 'Nacional', 'Natalicio del Libertador Simón Bolívar', TRUE, TRUE),
    (NULL, 10, 12, 'Nacional', 'Día de la Resistencia Indígena', TRUE, TRUE),
    (NULL, 11, 20, 'Contractual INCES', 'Día del Trabajador INCES', TRUE, TRUE),
    (NULL, 12, 17, 'Contractual INCES', 'Conmemoración Muerte del Libertador (INCES)', TRUE, TRUE),
    (NULL, 12, 24, 'Contractual INCES', 'Víspera de Navidad (Convención Colectiva INCES)', TRUE, TRUE),
    (NULL, 12, 25, 'Nacional', 'Navidad', TRUE, TRUE),
    (NULL, 12, 31, 'Contractual INCES', 'Fin de Año (Convención Colectiva INCES)', TRUE, TRUE)
ON CONFLICT DO NOTHING;

-- Feriados móviles 2026 y 2027 (Carnaval y Semana Santa)
INSERT INTO public.dias_feriados (fecha, mes, dia, tipo, descripcion, es_recurrente, activo)
VALUES
    ('2026-02-16', NULL, NULL, 'Móvil', 'Lunes de Carnaval 2026', FALSE, TRUE),
    ('2026-02-17', NULL, NULL, 'Móvil', 'Martes de Carnaval 2026', FALSE, TRUE),
    ('2026-04-02', NULL, NULL, 'Móvil', 'Jueves Santo 2026', FALSE, TRUE),
    ('2026-04-03', NULL, NULL, 'Móvil', 'Viernes Santo 2026', FALSE, TRUE),
    ('2027-02-08', NULL, NULL, 'Móvil', 'Lunes de Carnaval 2027', FALSE, TRUE),
    ('2027-02-09', NULL, NULL, 'Móvil', 'Martes de Carnaval 2027', FALSE, TRUE),
    ('2027-03-25', NULL, NULL, 'Móvil', 'Jueves Santo 2027', FALSE, TRUE),
    ('2027-03-26', NULL, NULL, 'Móvil', 'Viernes Santo 2027', FALSE, TRUE)
ON CONFLICT DO NOTHING;
