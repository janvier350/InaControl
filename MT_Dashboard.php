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

function mt_count2($con, $sql, $idEmpresa, $extraTypes = "", $extraParams = []) {
    $stmt = mysqli_prepare($con, $sql);
    $types = "i" . $extraTypes;
    $params = array_merge([$idEmpresa], $extraParams);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_row($r);
    mysqli_stmt_close($stmt);
    return $row ? (int)$row[0] : 0;
}

$totalTickets = mt_count2($con, "SELECT COUNT(*) FROM COTI_CALENDARIO WHERE ID_EMPRESA = ? AND ESTADO = 'A'", $idEmpresa);
$pendientes   = mt_count2($con, "SELECT COUNT(*) FROM COTI_CALENDARIO WHERE ID_EMPRESA = ? AND ESTADO = 'A' AND ESTADO_SOPORTE = 'Pendiente'", $idEmpresa);
$confirmados  = mt_count2($con, "SELECT COUNT(*) FROM COTI_CALENDARIO WHERE ID_EMPRESA = ? AND ESTADO = 'A' AND ESTADO_SOPORTE = 'Confirmada'", $idEmpresa);
$cancelados   = mt_count2($con, "SELECT COUNT(*) FROM COTI_CALENDARIO WHERE ID_EMPRESA = ? AND ESTADO = 'A' AND ESTADO_SOPORTE IN ('Cancelada','Cancelado')", $idEmpresa);
$totalClientes= mt_count2($con, "SELECT COUNT(*) FROM COTI_CLIENTE WHERE ID_EMPRESA = ? AND ESTADO = 'A'", $idEmpresa);

// Tickets por mes (últimos 6 meses)
$stmtMes = mysqli_prepare($con,
    "SELECT DATE_FORMAT(FECHA_SOPORTE, '%Y-%m') AS MES, COUNT(*) AS TOTAL
     FROM COTI_CALENDARIO WHERE ID_EMPRESA = ? AND ESTADO = 'A' AND FECHA_SOPORTE >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY MES ORDER BY MES ASC"
);
mysqli_stmt_bind_param($stmtMes, "i", $idEmpresa);
mysqli_stmt_execute($stmtMes);
$rMes = mysqli_stmt_get_result($stmtMes);
$labelsMes = []; $dataMes = [];
while ($m = mysqli_fetch_assoc($rMes)) { $labelsMes[] = $m['MES']; $dataMes[] = (int)$m['TOTAL']; }
mysqli_stmt_close($stmtMes);

// Top tipos de soporte
$stmtTipo = mysqli_prepare($con,
    "SELECT C.SOPORTE, COUNT(*) AS TOTAL FROM COTI_CALENDARIO A
     INNER JOIN COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
     WHERE A.ID_EMPRESA = ? AND A.ESTADO = 'A' GROUP BY C.ID_TIPO_SOPORTE ORDER BY TOTAL DESC LIMIT 5"
);
mysqli_stmt_bind_param($stmtTipo, "i", $idEmpresa);
mysqli_stmt_execute($stmtTipo);
$rTipo = mysqli_stmt_get_result($stmtTipo);
$labelsTipo = []; $dataTipo = [];
while ($t = mysqli_fetch_assoc($rTipo)) { $labelsTipo[] = $t['SOPORTE']; $dataTipo[] = (int)$t['TOTAL']; }
mysqli_stmt_close($stmtTipo);

// Próximos soportes
$stmtProx = mysqli_prepare($con,
    "SELECT A.FECHA_SOPORTE, A.HORA_INICIO, A.ESTADO_SOPORTE, CONCAT(B.NOMBRES,' ',B.APELLIDOS,' ',IFNULL(B.RAZON_SOCIAL,'')) AS CLIENTE
     FROM COTI_CALENDARIO A INNER JOIN COTI_CLIENTE B ON A.ID_CLIENTE = B.ID_CLIENTE
     WHERE A.ID_EMPRESA = ? AND A.ESTADO = 'A' AND A.FECHA_SOPORTE >= CURDATE() AND A.ESTADO_SOPORTE NOT IN ('Cancelada','Cancelado')
     ORDER BY A.FECHA_SOPORTE ASC, A.HORA_INICIO ASC LIMIT 6"
);
mysqli_stmt_bind_param($stmtProx, "i", $idEmpresa);
mysqli_stmt_execute($stmtProx);
$rProx = mysqli_stmt_get_result($stmtProx);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="./main.css" rel="stylesheet">
</head>
<body>
<div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">
    <div class="app-header header-shadow">
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
        <div class="app-sidebar sidebar-shadow">
            <?php include("./menu/menu_MT.php"); ?>
        </div>
        <div class="app-main__outer">
            <div class="app-main__inner">
                <div class="app-page-title">
                    <div class="page-title-wrapper">
                        <div class="page-title-heading">
                            <div class="page-title-icon"><i class="pe-7s-graph icon-gradient bg-warm-flame"></i></div>
                            <div><div class="page-title-subheading text-black">Dashboard de Soportes</div></div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div style="font-size:1.8rem;color:#0f3460;"><i class="bi bi-ticket-perforated"></i></div>
                                <h3 class="mb-0"><?php echo $totalTickets; ?></h3>
                                <small class="text-muted">Soportes totales</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div style="font-size:1.8rem;color:#F57F17;"><i class="bi bi-hourglass-split"></i></div>
                                <h3 class="mb-0"><?php echo $pendientes; ?></h3>
                                <small class="text-muted">Pendientes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div style="font-size:1.8rem;color:#2E7D32;"><i class="bi bi-check-circle"></i></div>
                                <h3 class="mb-0"><?php echo $confirmados; ?></h3>
                                <small class="text-muted">Confirmados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div style="font-size:1.8rem;color:#455A64;"><i class="bi bi-people"></i></div>
                                <h3 class="mb-0"><?php echo $totalClientes; ?></h3>
                                <small class="text-muted">Clientes activos</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="mb-3">Soportes por mes (últimos 6 meses)</h6>
                                <canvas id="chartMeses" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="mb-3">Servicios más solicitados</h6>
                                <canvas id="chartTipos" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <h6 class="mb-3">Próximos soportes agendados</h6>
                        <table class="table table-sm">
                            <thead><tr><th>Fecha</th><th>Hora</th><th>Cliente</th><th>Estado</th></tr></thead>
                            <tbody>
                            <?php if (mysqli_num_rows($rProx) === 0): ?>
                                <tr><td colspan="4" class="text-center text-muted">No hay soportes próximos agendados.</td></tr>
                            <?php else: while ($p = mysqli_fetch_assoc($rProx)): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($p['FECHA_SOPORTE'])); ?></td>
                                    <td><?php echo substr($p['HORA_INICIO'], 0, 5); ?></td>
                                    <td><?php echo htmlspecialchars(trim($p['CLIENTE'])); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = $p['ESTADO_SOPORTE'] === 'Confirmada' ? 'bg-success' : 'bg-warning text-dark';
                                        echo '<span class="badge '.$badgeClass.'">'.htmlspecialchars($p['ESTADO_SOPORTE']).'</span>';
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('chartMeses'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labelsMes); ?>,
        datasets: [{ label: 'Soportes', data: <?php echo json_encode($dataMes); ?>, backgroundColor: '#0f3460' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('chartTipos'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($labelsTipo); ?>,
        datasets: [{ data: <?php echo json_encode($dataTipo); ?>, backgroundColor: ['#0f3460','#1a6b8a','#e94560','#f5a623','#8e44ad'] }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
</body>
</html>
