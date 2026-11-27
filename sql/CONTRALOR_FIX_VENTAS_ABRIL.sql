-- Una venta de abril 2025 se perdio porque la celda de fecha tenia el
-- numero suelto "99" en vez de una fecha (factura 15074, entre las
-- facturas 15073 del 04-abr-2025 y 15075 del 05-abr-2025, por lo que
-- pertenece claramente al 04-abr-2025).
INSERT INTO CONT_VENTA (FECHA, ID_PRODUCTO, CLIENTE, FACTURA_NO, TIPO, QQ, PRECIO, TOTAL, PORC_RETENCION, VALOR_RETENCION, TOTAL_A_COBRAR, ESTADO) VALUES
('2025-04-04', (SELECT ID_PRODUCTO FROM CONT_PRODUCTO WHERE CODIGO='PTN0012' AND NOMBRE='ARROZ NATURALGRADO 2 011 EN 25 LBS' LIMIT 1), 'Santiago Cesar Vargas Murillo', '15074', 'CON_FACTURA', 1, 34.5, 34.5, 0, 0, 34.5, 'A');
