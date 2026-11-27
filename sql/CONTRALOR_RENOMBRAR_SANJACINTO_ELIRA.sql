-- Renombra "SAN JACINTO FLOR E.LIRA QQ" (PTN0005) a un nombre mas parecido
-- al que usa el Excel nuevo ("Arroz Extra Lira Flor"), para que sea mas
-- facil de encontrar en el buscador mientras el usuario se acostumbra.
UPDATE CONT_PRODUCTO
SET NOMBRE = 'Arroz Extra Lira Flor QQ'
WHERE CODIGO = 'PTN0005' AND NOMBRE = 'SAN JACINTO FLOR E.LIRA QQ';
