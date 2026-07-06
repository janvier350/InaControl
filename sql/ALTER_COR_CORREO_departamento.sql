-- Ejecutar en phpMyAdmin sobre la base de datos overcloc_INASAR
ALTER TABLE `COR_CORREO` ADD `DEPARTAMENTO` varchar(150) DEFAULT NULL AFTER `ALMACENAMIENTO`;
