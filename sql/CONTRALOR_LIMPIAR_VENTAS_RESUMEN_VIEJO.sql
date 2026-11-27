-- Elimina los residuos del PRIMER seed de Ventas (resumen mensual sin cliente,
-- que cubria 2024, 2025 y 2026). La parte de 2024 ya fue reemplazada por el
-- detalle real; esto limpia lo que quedo de 2025 y 2026 (identificable por
-- la observacion que dejo aquel primer seed).
DELETE FROM CONT_VENTA WHERE OBSERVACION = 'Migrado desde Excel - ventas mensuales históricas';
