-- Indica si la cámara está compartida vía HikConnect con el proveedor de seguridad
ALTER TABLE `CCTV_CAMARA` ADD `COMPARTIDA_HIKCONNECT` tinyint(1) NOT NULL DEFAULT 0 AFTER `CLAVE_HIKCONNECT`;
