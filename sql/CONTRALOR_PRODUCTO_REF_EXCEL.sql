-- REVERTIDO: el numero de "codigo" visible junto a los productos en el
-- Excel (13, 33, etc.) no es un identificador fijo por producto -- se
-- repite para productos distintos (parece ser un numero de linea de la
-- transaccion del dia, no del producto), asi que no es confiable como
-- referencia y se elimina el campo.
ALTER TABLE CONT_PRODUCTO DROP COLUMN REF_EXCEL;
