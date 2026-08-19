-- Agrega captura de lo que está apuntando la cámara (además de la foto de ubicación/estado)
ALTER TABLE `CCTV_CAMARA` ADD `CAPTURA_VISTA` varchar(255) DEFAULT NULL AFTER `FOTO`;
