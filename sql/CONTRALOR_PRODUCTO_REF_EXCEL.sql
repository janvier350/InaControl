-- Columna de referencia: el codigo numerico que el producto tiene en la
-- hoja de Excel del usuario (ej. 13, 33), util mientras se familiariza
-- con la app. No reemplaza al CODIGO interno del catalogo.
ALTER TABLE CONT_PRODUCTO ADD COLUMN REF_EXCEL VARCHAR(20) NULL AFTER CODIGO;
