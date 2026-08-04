<?php
session_start();
require_once("conexionBD_MT.php");

if (!isset($_SESSION['id_empresa'])) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Sesión inválida."));
    exit();
}

$con = conectarse_MT();
if (!$con) {
    die("No se pudo conectar a la base de datos.");
}
mysqli_report(MYSQLI_REPORT_OFF);

$idEmpresa = (int)$_SESSION['id_empresa'];

$hoy = date('Y-m-d');
$primerDiaMes = date('Y-m-01');
$fechaDesde = isset($_GET['fecha_desde']) && $_GET['fecha_desde'] !== '' ? $_GET['fecha_desde'] : $primerDiaMes;
$fechaHasta = isset($_GET['fecha_hasta']) && $_GET['fecha_hasta'] !== '' ? $_GET['fecha_hasta'] : $hoy;
$estadoFiltro = $_GET['estado'] ?? '';
$idClienteFiltro = isset($_GET['idCliente']) ? (int)$_GET['idCliente'] : 0;

$sql = "SELECT A.FECHA_SOPORTE, A.HORA_INICIO, A.HORA_FIN, A.ESTADO_SOPORTE, A.COMENTARIO,
               CONCAT(B.NOMBRES,' ',B.APELLIDOS,' ',IFNULL(B.RAZON_SOCIAL,'')) AS CLIENTE,
               C.SOPORTE AS TIPO_SOPORTE,
               CONCAT(D.NOMBRES,' ',D.APELLIDOS) AS TECNICO
        FROM COTI_CALENDARIO A
        INNER JOIN COTI_CLIENTE B ON A.ID_CLIENTE = B.ID_CLIENTE
        INNER JOIN COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
        INNER JOIN ADM_USUARIO D ON A.ID_USUARIO = D.IDADM_USUARIO
        WHERE A.ID_EMPRESA = ? AND A.ESTADO = 'A'
          AND A.FECHA_SOPORTE BETWEEN ? AND ?";
$types = "iss";
$params = [$idEmpresa, $fechaDesde, $fechaHasta];

if ($estadoFiltro !== '') {
    $sql .= " AND A.ESTADO_SOPORTE = ?";
    $types .= "s";
    $params[] = $estadoFiltro;
}
if ($idClienteFiltro) {
    $sql .= " AND A.ID_CLIENTE = ?";
    $types .= "i";
    $params[] = $idClienteFiltro;
}
$sql .= " ORDER BY A.FECHA_SOPORTE ASC, A.HORA_INICIO ASC";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$resultados = mysqli_stmt_get_result($stmt);

$nombreArchivo = "reporte_soportes_{$fechaDesde}_a_{$fechaHasta}.csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

echo chr(0xEF) . chr(0xBB) . chr(0xBF); // BOM para Excel

$out = fopen('php://output', 'w');
fputcsv($out, ['Fecha', 'Hora Inicio', 'Hora Fin', 'Cliente', 'Tipo de Soporte', 'Técnico', 'Estado', 'Comentario'], ';');

while ($f = mysqli_fetch_assoc($resultados)) {
    fputcsv($out, [
        date('d/m/Y', strtotime($f['FECHA_SOPORTE'])),
        substr($f['HORA_INICIO'], 0, 5),
        substr($f['HORA_FIN'], 0, 5),
        trim($f['CLIENTE']),
        $f['TIPO_SOPORTE'],
        $f['TECNICO'],
        $f['ESTADO_SOPORTE'],
        $f['COMENTARIO'],
    ], ';');
}

fclose($out);
mysqli_stmt_close($stmt);
mysqli_close($con);
exit();
