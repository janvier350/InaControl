-- ============================================================
-- FASE 1 - Script 04: Tablas operacionales con ID_EMPRESA
-- Todas las tablas de negocio llevan ID_EMPRESA para que
-- cada empresa vea únicamente sus propios datos.
-- ============================================================

USE `overcloc_INACONTROL_DEV`;

-- ------------------------------------------------------------
-- MÓDULO: SOPORTE / CALENDARIO
-- ------------------------------------------------------------
CREATE TABLE `COTI_TIPO_SOPORTE` (
  `ID_TIPO_SOPORTE` int(11)      NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`      int(11)      NOT NULL,
  `SOPORTE`         varchar(100) NOT NULL,
  `DESCRIPCION`     varchar(300) DEFAULT NULL,
  `ESTADO`          varchar(2)   NOT NULL DEFAULT 'A',
  PRIMARY KEY (`ID_TIPO_SOPORTE`),
  KEY `IDX_TIPO_SOPORTE_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `COTI_CLIENTE` (
  `ID_CLIENTE`    int(11)      NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`    int(11)      NOT NULL,
  `NOMBRES`       varchar(100) DEFAULT NULL,
  `APELLIDOS`     varchar(100) DEFAULT NULL,
  `RAZON_SOCIAL`  varchar(200) DEFAULT NULL,
  `RUC`           varchar(20)  DEFAULT NULL,
  `EMAIL`         varchar(150) DEFAULT NULL,
  `TELEFONO`      varchar(20)  DEFAULT NULL,
  `ESTADO`        varchar(2)   NOT NULL DEFAULT 'A',
  PRIMARY KEY (`ID_CLIENTE`),
  KEY `IDX_CLIENTE_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `COTI_CALENDARIO` (
  `ID_CALENDARIO_SOPORTE` int(11)      NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`            int(11)      NOT NULL,
  `ID_CLIENTE`            int(11)      NOT NULL,
  `ID_SOPORTE`            int(11)      NOT NULL,
  `ID_USUARIO`            int(11)      NOT NULL,
  `FECHA_CREACION`        timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `FECHA_SOPORTE`         date         DEFAULT NULL,
  `HORA_INICIO`           time         DEFAULT NULL,
  `HORA_FIN`              time         DEFAULT NULL,
  `ESTADO_SOPORTE`        varchar(30)  DEFAULT 'Pendiente',
  `COMENTARIO`            text         DEFAULT NULL,
  `EVIDENCIAS`            text         DEFAULT NULL,
  `ESTADO`                varchar(2)   NOT NULL DEFAULT 'A',
  PRIMARY KEY (`ID_CALENDARIO_SOPORTE`),
  KEY `IDX_CALENDARIO_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- MÓDULO: INVENTARIO
-- ------------------------------------------------------------
CREATE TABLE `INV_DISPOSITIVO` (
  `ID_DISPOSITIVO` int(11)      NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`     int(11)      NOT NULL,
  `DISPOSITIVO`    varchar(100) NOT NULL,
  `ESTADO`         varchar(2)   NOT NULL DEFAULT 'A',
  PRIMARY KEY (`ID_DISPOSITIVO`),
  KEY `IDX_DISPOSITIVO_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `INV_EQUIPO` (
  `ID_EQUIPO`      int(11)      NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`     int(11)      NOT NULL,
  `FECHA_COMPRA`   date         DEFAULT NULL,
  `DEPARTAMENTO`   varchar(200) DEFAULT NULL,
  `MARCA`          varchar(100) NOT NULL,
  `MODELO`         varchar(200) DEFAULT NULL,
  `SERIAL`         varchar(200) DEFAULT NULL,
  `PROCESADOR`     varchar(100) DEFAULT NULL,
  `HDD`            varchar(100) DEFAULT NULL,
  `RAM`            varchar(20)  DEFAULT NULL,
  `PANTALLA`       varchar(20)  DEFAULT NULL,
  `OBSERVACIONES`  varchar(500) DEFAULT NULL,
  `ESTADO`         varchar(25)  DEFAULT NULL,
  `DISPOSITIVO`    varchar(100) DEFAULT NULL,
  `ESTADO_AI`      varchar(2)   NOT NULL DEFAULT 'A',
  `IMAGEN`         varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID_EQUIPO`),
  KEY `IDX_EQUIPO_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `INV_ASIGNACION` (
  `ID_INV_ASIGNACION` int(11) NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`        int(11) NOT NULL,
  `ID_EQUIPO`         int(11) NOT NULL,
  `FECHA_ASIGNACION`  date    NOT NULL,
  `ID_ADM_USUARIO`    int(11) NOT NULL,
  `ESTADO`            varchar(2) NOT NULL DEFAULT 'A',
  `FECHA_DEVOLUCION`  date    DEFAULT NULL,
  PRIMARY KEY (`ID_INV_ASIGNACION`),
  KEY `IDX_ASIGNACION_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `INV_MANTENIMIENTOS` (
  `ID_MANTENIMIENTO` int(11)      NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`       int(11)      NOT NULL,
  `ID_EQUIPO`        int(11)      NOT NULL,
  `FECHA_SALIDA`     date         NOT NULL,
  `DANIO_REPORTADO`  varchar(500) NOT NULL,
  `FECHA_ENTREGA`    date         DEFAULT NULL,
  `SOLUCION_APLICADA`varchar(500) DEFAULT NULL,
  `ESTADO`           varchar(25)  NOT NULL DEFAULT 'En Reparacion',
  `FECHA_REGISTRO`   timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_MANTENIMIENTO`),
  KEY `IDX_MANT_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- MÓDULO: CORREOS CORPORATIVOS
-- ------------------------------------------------------------
CREATE TABLE `COR_CORREO` (
  `ID_CORREO`      int(11)      NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`     int(11)      NOT NULL,
  `CORREO`         varchar(150) NOT NULL,
  `CONTRASENA`     varchar(100) NOT NULL,
  `DEPARTAMENTO`   varchar(150) DEFAULT NULL,
  `ALMACENAMIENTO` varchar(50)  DEFAULT NULL,
  `IDADM_USUARIO`  int(11)      DEFAULT NULL,
  `ESTADO`         varchar(2)   NOT NULL DEFAULT 'A',
  `FECHA_REGISTRO` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_CORREO`),
  KEY `IDX_CORREO_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- MÓDULO: ACCESOS / CLAVES
-- ------------------------------------------------------------
CREATE TABLE `ACC_REMOTO` (
  `ID_ACCESO`    int(11)      NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`   int(11)      NOT NULL,
  `NOMBRE`       varchar(100) DEFAULT NULL,
  `USUARIO`      varchar(100) DEFAULT NULL,
  `CONTRASENA`   varchar(100) DEFAULT NULL,
  `DESCRIPCION`  varchar(300) DEFAULT NULL,
  `ESTADO`       varchar(2)   NOT NULL DEFAULT 'A',
  PRIMARY KEY (`ID_ACCESO`),
  KEY `IDX_ACCESO_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
