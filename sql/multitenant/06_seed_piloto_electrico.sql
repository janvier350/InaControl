-- ============================================================
-- FASE 3 - Datos semilla para "Piloto Electrico" (ID_EMPRESA = 2)
-- Necesarios para que el módulo de Calendario/Soporte tenga
-- opciones que mostrar en los desplegables.
-- Ajusta el ID_EMPRESA si tu empresa piloto tiene otro ID.
-- ============================================================

USE `overcloc_INACONTROL_DEV`;

INSERT INTO `COTI_TIPO_SOPORTE` (`ID_EMPRESA`, `SOPORTE`, `DESCRIPCION`, `ESTADO`) VALUES
(2, 'Visita Presencial', 'Mantenimiento eléctrico en sitio', 'A'),
(2, 'Soporte Remoto',    'Asesoría técnica remota',          'A');
