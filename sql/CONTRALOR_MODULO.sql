-- ============================================================
-- Módulo CONTRALOR: catálogo de productos + ventas (reemplaza las
-- hojas de Excel con bloques anchos y fórmulas frágiles por una
-- tabla relacional real).
-- Ejecutar en phpMyAdmin, sobre la base de datos de INASAR
-- (overcloc_INASAR), en este orden: primero este script,
-- luego sql/CONTRALOR_SEED_HISTORICO.sql
-- ============================================================

-- Catálogo de productos
CREATE TABLE `CONT_PRODUCTO` (
  `ID_PRODUCTO`     int(11)      NOT NULL AUTO_INCREMENT,
  `CODIGO`          varchar(20)  DEFAULT NULL,
  `NOMBRE`          varchar(200) NOT NULL,
  `ESTADO`          varchar(2)   NOT NULL DEFAULT 'A',
  `FECHA_REGISTRO`  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_PRODUCTO`),
  KEY `IDX_PRODUCTO_NOMBRE` (`NOMBRE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Ventas (con y sin factura), una fila por venta/mes registrado
CREATE TABLE `CONT_VENTA` (
  `ID_VENTA`        int(11)       NOT NULL AUTO_INCREMENT,
  `FECHA`           date          NOT NULL,
  `ID_PRODUCTO`     int(11)       NOT NULL,
  `CLIENTE`         varchar(200)  DEFAULT NULL,
  `TIPO`            varchar(20)   NOT NULL DEFAULT 'CON_FACTURA', -- CON_FACTURA | SIN_FACTURA
  `QQ`              decimal(12,2) NOT NULL,
  `PRECIO`          decimal(12,4) NOT NULL,
  `TOTAL`           decimal(14,2) NOT NULL,
  `OBSERVACION`     varchar(300)  DEFAULT NULL,
  `ESTADO`          varchar(2)    NOT NULL DEFAULT 'A',
  `FECHA_REGISTRO`  timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_VENTA`),
  KEY `IDX_VENTA_PRODUCTO` (`ID_PRODUCTO`),
  KEY `IDX_VENTA_FECHA` (`FECHA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Nuevo rol CONTRALOR
INSERT INTO `ADM_ROL` (`CARGO`) VALUES ('CONTRALOR');

-- Usuario del CONTRALOR (ajusta USUARIO si quieres otro nombre de ingreso)
-- Clave inicial: Contralor2026* (cámbiala apenas entres)
INSERT INTO `ADM_USUARIO` (`NOMBRES`, `APELLIDOS`, `USUARIO`, `CONTRASENA`, `IDADM_ROL`, `ESTADO`)
VALUES ('Contralor', 'INASAR', 'contralor', MD5('Contralor2026*'), (SELECT IDADM_ROL FROM ADM_ROL WHERE CARGO = 'CONTRALOR'), 'A');
