-- ============================================================
-- FASE 1 - Script 02: Tabla maestra de empresas
-- Es el corazón del multi-tenant. Cada empresa tiene su
-- propio ID que se usará como filtro en todas las tablas.
-- ============================================================

USE `overcloc_INACONTROL_DEV`;

CREATE TABLE `EMPRESA` (
  `ID_EMPRESA`       int(11)      NOT NULL AUTO_INCREMENT,
  `NOMBRE`           varchar(200) NOT NULL,
  `RUC`              varchar(20)  DEFAULT NULL,
  `EMAIL_CONTACTO`   varchar(150) DEFAULT NULL,
  `TELEFONO`         varchar(20)  DEFAULT NULL,
  `DIRECCION`        varchar(300) DEFAULT NULL,
  `LOGO`             varchar(255) DEFAULT NULL,
  `PLAN`             varchar(20)  NOT NULL DEFAULT 'BASICO',
                     -- Valores: BASICO | ESTANDAR | PREMIUM
  `ESTADO`           varchar(2)   NOT NULL DEFAULT 'A',
  `FECHA_REGISTRO`   timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Empresa 1: INASAR (migración futura desde overcloc_INASAR)
-- Empresa 2: Piloto Ing. Eléctrico (nuevo cliente)
INSERT INTO `EMPRESA` (`NOMBRE`, `RUC`, `EMAIL_CONTACTO`, `PLAN`, `ESTADO`) VALUES
('INASAR',          '0992584246001', 'soporte@inasar.com',   'PREMIUM',   'A'),
('Piloto Electrico', NULL,           NULL,                   'BASICO',    'A');
