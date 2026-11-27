-- Corrige 3 bloques de ventas que en el Excel quedaron con el año mal
-- digitado (un año menos), aunque estaban ubicados dentro de la seccion
-- correcta de 2025 (numeros de factura consecutivos con los de alrededor,
-- en 2025). Esto es lo que causaba que agosto y enero 2025 se vieran por
-- debajo del total real del Excel. Se identifican por numero de factura
-- (unico) para no tocar ninguna venta real de esas mismas fechas en 2024.

UPDATE CONT_VENTA SET FECHA = '2025-01-09'
WHERE FECHA = '2024-01-09' AND FACTURA_NO IN ('14376','14377','14378','14379','14380','14381');

UPDATE CONT_VENTA SET FECHA = '2025-01-13'
WHERE FECHA = '2024-01-13' AND FACTURA_NO IN ('14398','14399','14400','14401','14402','14403','14404');

UPDATE CONT_VENTA SET FECHA = '2025-08-04'
WHERE FECHA = '2024-08-04' AND FACTURA_NO IN ('16000','16001','16002','16003','16004','16005','16006','16007','16008','16009','16010','16011','16012','16013');
