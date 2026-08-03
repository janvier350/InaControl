-- ============================================================
-- FASE 1 - Script 03: Módulo Administración
-- ADM_ROL: global (compartido por todas las empresas)
-- ADM_USUARIO: por empresa (cada empresa tiene sus usuarios)
-- ============================================================

USE `overcloc_INACONTROL_DEV`;

-- Roles del sistema (globales, no cambian por empresa)
CREATE TABLE `ADM_ROL` (
  `IDADM_ROL`   int(11)      NOT NULL AUTO_INCREMENT,
  `ROL`         varchar(50)  NOT NULL,
  `DESCRIPCION` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`IDADM_ROL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ADM_ROL` (`ROL`, `DESCRIPCION`) VALUES
('SUPERADMIN', 'Acceso total a todas las empresas - solo para el desarrollador'),
('SISTEMA',    'Administrador de la empresa'),
('GERENTES',   'Gerencia - vista completa'),
('adm',        'Administración interna'),
('cotizador',  'Módulo de ventas y cotizaciones'),
('vta',        'Vendedor'),
('control_vta','Control de ventas');

-- Usuarios (cada usuario pertenece a UNA empresa)
CREATE TABLE `ADM_USUARIO` (
  `IDADM_USUARIO` int(11)      NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`    int(11)      NOT NULL,               -- FK a EMPRESA
  `NOMBRES`       varchar(100) NOT NULL,
  `APELLIDOS`     varchar(100) NOT NULL,
  `TELEFONO`      varchar(20)  DEFAULT NULL,
  `USUARIO`       varchar(50)  NOT NULL,
  `CONTRASENA`    varchar(255) NOT NULL,
  `IDADM_ROL`     int(11)      NOT NULL,
  `IDAGENCIA`     int(11)      DEFAULT NULL,
  `IMG`           varchar(255) DEFAULT NULL,
  `ESTADO`        varchar(2)   NOT NULL DEFAULT 'A',
  PRIMARY KEY (`IDADM_USUARIO`),
  KEY `IDX_USUARIO_EMPRESA` (`ID_EMPRESA`),
  KEY `IDX_USUARIO_ROL`     (`IDADM_ROL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario SUPERADMIN (tú - ves todas las empresas)
INSERT INTO `ADM_USUARIO`
  (`ID_EMPRESA`, `NOMBRES`, `APELLIDOS`, `USUARIO`, `CONTRASENA`, `IDADM_ROL`, `ESTADO`)
VALUES
  (1, 'Santiago', 'Varas', 'superadmin', MD5('cambiar_clave_segura'), 1, 'A');
