-- ============================================================
-- FASE 1 - Script 05: Tabla de configuración por empresa
-- Permite personalizar módulos activos, logo, colores, etc.
-- Aquí el SUPERADMIN controla qué ve cada empresa.
-- ============================================================

USE `overcloc_INACONTROL_DEV`;

CREATE TABLE `EMPRESA_CONFIG` (
  `ID_CONFIG`          int(11)     NOT NULL AUTO_INCREMENT,
  `ID_EMPRESA`         int(11)     NOT NULL,
  -- Módulos activos (1 = activo, 0 = desactivado para esa empresa)
  `MOD_SOPORTE`        tinyint(1)  NOT NULL DEFAULT 1,
  `MOD_INVENTARIO`     tinyint(1)  NOT NULL DEFAULT 1,
  `MOD_CORREOS`        tinyint(1)  NOT NULL DEFAULT 1,
  `MOD_VENTAS`         tinyint(1)  NOT NULL DEFAULT 0,
  `MOD_REPORTES`       tinyint(1)  NOT NULL DEFAULT 1,
  -- Personalización visual
  `COLOR_PRIMARIO`     varchar(10) DEFAULT '#0f3460',
  `COLOR_SECUNDARIO`   varchar(10) DEFAULT '#e94560',
  `NOMBRE_APP`         varchar(100) DEFAULT NULL,   -- nombre personalizado de la app
  PRIMARY KEY (`ID_CONFIG`),
  UNIQUE KEY `UQ_CONFIG_EMPRESA` (`ID_EMPRESA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuración inicial por empresa
INSERT INTO `EMPRESA_CONFIG` (`ID_EMPRESA`, `MOD_SOPORTE`, `MOD_INVENTARIO`, `MOD_CORREOS`, `MOD_VENTAS`, `MOD_REPORTES`) VALUES
(1, 1, 1, 1, 1, 1),   -- INASAR: todos los módulos activos
(2, 1, 1, 0, 0, 1);   -- Piloto Ing. Eléctrico: soporte + inventario + reportes
