-- Agrega 6 ventas que tenian QQ registrado pero el precio quedo vacio en el
-- Excel (posible cortesia / dato no completado). Se migraron con PRECIO=0 y
-- TOTAL=0 porque no hay precio real que asignar, asi que no afectan el monto
-- en dolares, solo corrige el conteo de quintales (QQ) para que coincida con
-- el resumen del Excel. Se excluyo una septima fila cuyo "cliente" era
-- claramente basura de digitacion ("AQUÍ", sin nombre real).

INSERT INTO CONT_VENTA (FECHA, ID_PRODUCTO, CLIENTE, FACTURA_NO, TIPO, QQ, PRECIO, TOTAL, PORC_RETENCION, VALOR_RETENCION, TOTAL_A_COBRAR, ESTADO) VALUES
('2024-12-16', (SELECT ID_PRODUCTO FROM CONT_PRODUCTO WHERE CODIGO='ARV0002' AND NOMBRE='Arroz Envejecido Macareño' LIMIT 1), 'Darwin Victoriano Gavilanez Sanchez', '14222', 'CON_FACTURA', 6, 0, 0, 0, 0, 0, 'A'),
('2025-01-17', (SELECT ID_PRODUCTO FROM CONT_PRODUCTO WHERE CODIGO='PTN0005' AND NOMBRE='ARROZ NATURAL GRANO LARGO 011 QQ' LIMIT 1), 'Comunidad de Misioneras Laycas', '14447', 'CON_FACTURA', 1, 0, 0, 0, 0, 0, 'A'),
('2025-10-08', (SELECT ID_PRODUCTO FROM CONT_PRODUCTO WHERE CODIGO='PTN0008' AND NOMBRE='ARROZ NATURAL GRANO LARGO 011 10 LBS' LIMIT 1), 'Karla Gabriela Leon Robles', '16510', 'CON_FACTURA', 3, 0, 0, 0, 0, 0, 'A'),
('2025-11-27', (SELECT ID_PRODUCTO FROM CONT_PRODUCTO WHERE CODIGO='PTN0008' AND NOMBRE='ARROZ NATURAL GRANO LARGO 011 10 LBS' LIMIT 1), 'Comercial La Feria Cia.Ltda.', '16792', 'CON_FACTURA', 40, 0, 0, 0, 0, 0, 'A'),
('2025-12-03', (SELECT ID_PRODUCTO FROM CONT_PRODUCTO WHERE CODIGO='PTN0013' AND NOMBRE='ARROZ NATURALGRADO 2 011 EN 10 LBS' LIMIT 1), 'Mariana del Rocio Sarmiento Orellana', '16832', 'CON_FACTURA', 1, 0, 0, 0, 0, 0, 'A'),
('2025-12-29', (SELECT ID_PRODUCTO FROM CONT_PRODUCTO WHERE CODIGO='PTN0012' AND NOMBRE='ARROZ NATURALGRADO 2 011 EN 25 LBS' LIMIT 1), 'Hugo Mario Galvez Nagua', '17017', 'CON_FACTURA', 2.5, 0, 0, 0, 0, 0, 'A');
