-- Agrega una venta que se perdio por completo en la migracion: la fila
-- tenia '*' en la celda de fecha (significa "misma fecha que la fila de
-- arriba", parte de la misma factura 14364 del 07-ene-2025), y el
-- extractor no sabia interpretar ese marcador, asi que descarto la fila
-- en vez de heredar la fecha de la venta anterior.
INSERT INTO CONT_VENTA (FECHA, ID_PRODUCTO, CLIENTE, FACTURA_NO, TIPO, QQ, PRECIO, TOTAL, PORC_RETENCION, VALOR_RETENCION, TOTAL_A_COBRAR, ESTADO) VALUES
('2025-01-07', (SELECT ID_PRODUCTO FROM CONT_PRODUCTO WHERE CODIGO='SUB0007' AND NOMBRE='Polvillo' LIMIT 1), 'Agro Industria Sarmiento Nottbohm S.A.', '14364', 'CON_FACTURA', 1868, 11.25, 21015, 0, 0, 21015, 'A');
