<?php
ob_start();
session_start();
require_once("class/conexionBD_MT.php");

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] === "SUPERADMIN" || !isset($_SESSION['id_empresa'])) {
    header("Location: break.php");
    exit();
}
if (time() > $_SESSION['expire']) {
    session_destroy();
    header("Location: expirada.php");
    exit();
}

$con = conectarse_MT();
if (!$con) {
    die("<h3 style='font-family:sans-serif;color:#900;padding:2rem;'>No se pudo conectar a la base de datos.</h3>");
}
mysqli_report(MYSQLI_REPORT_OFF);

$idEmpresa = (int)$_SESSION['id_empresa'];

$hoy = date('Y-m-d');
$primerDiaMes = date('Y-m-01');
$fechaDesde = isset($_GET['fecha_desde']) && $_GET['fecha_desde'] !== '' ? $_GET['fecha_desde'] : $primerDiaMes;
$fechaHasta = isset($_GET['fecha_hasta']) && $_GET['fecha_hasta'] !== '' ? $_GET['fecha_hasta'] : $hoy;
$estadoFiltro = $_GET['estado'] ?? '';
$idClienteFiltro = isset($_GET['idCliente']) ? (int)$_GET['idCliente'] : 0;

$sql = "SELECT A.ID_CALENDARIO_SOPORTE, A.FECHA_SOPORTE, A.HORA_INICIO, A.HORA_FIN, A.ESTADO_SOPORTE, A.COMENTARIO,
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
$filas = [];
while ($r = mysqli_fetch_assoc($resultados)) { $filas[] = $r; }
mysqli_stmt_close($stmt);

$totalRegistros = count($filas);
$totalConfirmados = 0; $totalPendientes = 0; $totalCancelados = 0;
foreach ($filas as $f) {
    if ($f['ESTADO_SOPORTE'] === 'Confirmada') $totalConfirmados++;
    elseif ($f['ESTADO_SOPORTE'] === 'Pendiente') $totalPendientes++;
    elseif (in_array($f['ESTADO_SOPORTE'], ['Cancelada','Cancelado'])) $totalCancelados++;
}

$stmtCli = mysqli_prepare($con, "SELECT ID_CLIENTE, NOMBRES, APELLIDOS, RAZON_SOCIAL FROM COTI_CLIENTE WHERE ID_EMPRESA = ? AND ESTADO = 'A' ORDER BY NOMBRES");
mysqli_stmt_bind_param($stmtCli, "i", $idEmpresa);
mysqli_stmt_execute($stmtCli);
$listaClientes = mysqli_stmt_get_result($stmtCli);

$queryStringExport = http_build_query([
    'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta,
    'estado' => $estadoFiltro, 'idCliente' => $idClienteFiltro
]);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kontrol - Reportes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="./main.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .app-sidebar, .app-header { display: none !important; }
            .app-main__outer { padding-left: 0 !important; }
            body { background: #fff !important; }
            .print-header { display: block !important; }
        }
        .print-header { display: none; }
    </style>
</head>
<body>
<div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">
    <div class="app-header header-shadow no-print">
        <div class="app-header__logo">
            <div class="logo-src"></div>
            <div class="header__pane ml-auto">
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box"><span class="hamburger-inner"></span></span>
                </button>
            </div>
        </div>
        <div class="app-header__content">
            <div class="app-header-right">
                <div class="widget-content-left header-user-info ms-auto">
                    <div class="widget-heading"><?php echo htmlspecialchars($_SESSION["username"]); ?></div>
                    <div class="widget-subheading"><?php echo htmlspecialchars($_SESSION['nombre_empresa'] ?? ''); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="app-main">
        <div class="app-sidebar sidebar-shadow no-print">
            <?php include("./menu/menu_MT.php"); ?>
        </div>
        <div class="app-main__outer">
            <div class="app-main__inner">
                <div class="print-header mb-4">
                    <h3><?php echo htmlspecialchars($_SESSION['nombre_empresa'] ?? ''); ?></h3>
                    <p>Reporte de Soportes: <?php echo date('d/m/Y', strtotime($fechaDesde)); ?> al <?php echo date('d/m/Y', strtotime($fechaHasta)); ?></p>
                </div>

                <div class="app-page-title no-print">
                    <div class="page-title-wrapper">
                        <div class="page-title-heading">
                            <div class="page-title-icon"><i class="pe-7s-note2 icon-gradient bg-warm-flame"></i></div>
                            <div><div class="page-title-subheading text-black">Reportes</div></div>
                        </div>
                    </div>
                </div>

                <div class="main-card mb-3 card no-print">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label">Desde</label>
                                <input type="date" class="form-control" name="fecha_desde" value="<?php echo htmlspecialchars($fechaDesde); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Hasta</label>
                                <input type="date" class="form-control" name="fecha_hasta" value="<?php echo htmlspecialchars($fechaHasta); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos</option>
                                    <option value="Pendiente" <?php echo $estadoFiltro === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="Confirmada" <?php echo $estadoFiltro === 'Confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                                    <option value="Cancelada" <?php echo $estadoFiltro === 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cliente</label>
                                <select class="form-select" name="idCliente">
                                    <option value="0">Todos</option>
                                    <?php while ($c = mysqli_fetch_assoc($listaClientes)):
                                        $nom = trim($c['NOMBRES'].' '.$c['APELLIDOS'].' '.$c['RAZON_SOCIAL']); ?>
                                        <option value="<?php echo $c['ID_CLIENTE']; ?>" <?php echo $idClienteFiltro == $c['ID_CLIENTE'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($nom); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
                                <a class="btn btn-success" href="class/MT_Export_Reporte.php?<?php echo $queryStringExport; ?>">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="window.print()">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row g-3 mb-3 no-print">
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body py-2">
                            <h5 class="mb-0"><?php echo $totalRegistros; ?></h5><small class="text-muted">Total</small>
                        </div></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body py-2">
                            <h5 class="mb-0 text-success"><?php echo $totalConfirmados; ?></h5><small class="text-muted">Confirmados</small>
                        </div></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body py-2">
                            <h5 class="mb-0 text-warning"><?php echo $totalPendientes; ?></h5><small class="text-muted">Pendientes</small>
                        </div></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body py-2">
                            <h5 class="mb-0 text-secondary"><?php echo $totalCancelados; ?></h5><small class="text-muted">Cancelados</small>
                        </div></div>
                    </div>
                </div>

                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr><th>FECHA</th><th>HORA</th><th>CLIENTE</th><th>TIPO SOPORTE</th><th>TÉCNICO</th><th>ESTADO</th></tr>
                                </thead>
                                <tbody>
                                <?php if (empty($filas)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">No hay soportes en el rango seleccionado.</td></tr>
                                <?php else: foreach ($filas as $f):
                                    $badge = 'bg-secondary';
                                    if ($f['ESTADO_SOPORTE'] === 'Confirmada') $badge = 'bg-success';
                                    elseif ($f['ESTADO_SOPORTE'] === 'Pendiente') $badge = 'bg-warning text-dark';
                                    elseif (in_array($f['ESTADO_SOPORTE'], ['Cancelada','Cancelado'])) $badge = 'bg-danger';
                                ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($f['FECHA_SOPORTE'])); ?></td>
                                        <td><?php echo substr($f['HORA_INICIO'],0,5).' - '.substr($f['HORA_FIN'],0,5); ?></td>
                                        <td><?php echo htmlspecialchars(trim($f['CLIENTE'])); ?></td>
                                        <td><?php echo htmlspecialchars($f['TIPO_SOPORTE']); ?></td>
                                        <td><?php echo htmlspecialchars($f['TECNICO']); ?></td>
                                        <td><span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($f['ESTADO_SOPORTE']); ?></span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
