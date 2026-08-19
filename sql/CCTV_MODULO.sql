-- ============================================================
-- Módulo CCTV: DVR/NVR, Cámaras IP y seguimiento de incidencias
-- Sin FOREIGN KEY (mismo criterio que el resto del sistema:
-- integridad referencial manejada en PHP para evitar Error 150
-- por charset/engine).
-- ============================================================

CREATE TABLE `CCTV_DVR` (
  `ID_DVR`            int(11)      NOT NULL AUTO_INCREMENT,
  `TIPO`               varchar(10)  NOT NULL DEFAULT 'DVR',   -- DVR | NVR
  `MARCA`              varchar(100) NOT NULL,
  `MODELO`             varchar(150) DEFAULT NULL,
  `IP`                 varchar(50)  DEFAULT NULL,
  `CANALES`            int(11)      DEFAULT NULL,
  `NUMERO_SERIE`       varchar(150) DEFAULT NULL,
  `CLAVE_ACCESO`       varchar(150) DEFAULT NULL,
  `CLAVE_HIKCONNECT`   varchar(150) DEFAULT NULL,
  `UBICACION`          varchar(200) DEFAULT NULL,             -- Rack principal, Oficina, etc.
  `FECHA_COMPRA`       date         DEFAULT NULL,
  `OBSERVACION`        varchar(500) DEFAULT NULL,
  `COMENTARIO`         varchar(500) DEFAULT NULL,
  `FOTO`               varchar(255) DEFAULT NULL,
  `ESTADO`             varchar(2)   NOT NULL DEFAULT 'A',
  `FECHA_REGISTRO`     timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_DVR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `CCTV_CAMARA` (
  `ID_CAMARA`          int(11)      NOT NULL AUTO_INCREMENT,
  `ID_DVR`             int(11)      DEFAULT NULL,             -- NVR/DVR al que está conectada (NULL = independiente)
  `MARCA`              varchar(100) NOT NULL,
  `MODELO`             varchar(150) DEFAULT NULL,
  `IP`                 varchar(50)  DEFAULT NULL,
  `NUMERO_SERIE`       varchar(150) DEFAULT NULL,
  `USUARIO`            varchar(100) DEFAULT NULL,
  `CLAVE`              varchar(150) DEFAULT NULL,
  `CLAVE_HIKCONNECT`   varchar(150) DEFAULT NULL,
  `TIPO_CAMARA`        varchar(30)  DEFAULT 'IP',              -- IP, PTZ, Analoga, Domo, Bullet
  `UBICACION`          varchar(200) DEFAULT NULL,              -- área/zona física cubierta
  `FECHA_COMPRA`       date         DEFAULT NULL,
  `OBSERVACION`        varchar(500) DEFAULT NULL,
  `FOTO`               varchar(255) DEFAULT NULL,
  `ESTADO`             varchar(2)   NOT NULL DEFAULT 'A',
  `FECHA_REGISTRO`     timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_CAMARA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `CCTV_INCIDENCIA` (
  `ID_INCIDENCIA`      int(11)      NOT NULL AUTO_INCREMENT,
  `ID_CAMARA`          int(11)      DEFAULT NULL,              -- si aplica a una cámara puntual
  `ID_DVR`             int(11)      DEFAULT NULL,              -- si aplica al DVR/NVR
  `TIPO`               varchar(30)  NOT NULL DEFAULT 'Falla',  -- Falla, Reparacion, Reemplazo, Mantenimiento
  `FECHA`              date         NOT NULL,
  `DESCRIPCION`        varchar(500) NOT NULL,
  `SOLUCION`           varchar(500) DEFAULT NULL,
  `ESTADO`             varchar(25)  NOT NULL DEFAULT 'Abierta', -- Abierta, Resuelta
  `FECHA_REGISTRO`     timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_INCIDENCIA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
