-- Agrega capacidad de disco duro a DVR/NVR
ALTER TABLE `CCTV_DVR` ADD `CAPACIDAD_DISCO` varchar(50) DEFAULT NULL AFTER `CANALES`;

-- Agrega evidencias (fotos) a las incidencias
ALTER TABLE `CCTV_INCIDENCIA` ADD `EVIDENCIAS` text DEFAULT NULL AFTER `DESCRIPCION`;
