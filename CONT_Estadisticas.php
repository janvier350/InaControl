<!doctype html>
<html lang="es">
<?php
ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD.php");
$conexion = conectarse();

if(!isset($_SESSION["rol"])){
    header("Location: break.php");
    exit();
}else {
    $now = time();
    if ($now > $_SESSION['expire']) {
        session_destroy();
        header("Location: expirada.php");
        exit();
    }
}
$rol_usuario = $_SESSION["rol"];

$anios = [];
$qA = $conexion->query("SELECT DISTINCT YEAR(FECHA) AS ANIO FROM CONT_VENTA ORDER BY ANIO DESC");
while ($a = mysqli_fetch_assoc($qA)) { $anios[] = (int)$a['ANIO']; }
if (empty($anios)) { $anios[] = (int)date('Y'); }

$anioSel = isset($_GET['anio']) ? (int)$_GET['anio'] : $anios[0];
$anioAnterior = $anioSel - 1;

function kpiNum($conexion, $sql, $anio) {
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $anio);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_row($res);
    mysqli_stmt_close($stmt);
    return $row ? $row[0] : 0;
}

$totalQQ   = kpiNum($conexion, "SELECT IFNULL(SUM(QQ),0) FROM CONT_VENTA WHERE ESTADO='A' AND YEAR(FECHA)=?", $anioSel);
$totalUSD  = kpiNum($conexion, "SELECT IFNULL(SUM(TOTAL),0) FROM CONT_VENTA WHERE ESTADO='A' AND YEAR(FECHA)=?", $anioSel);
$totalTrans= kpiNum($conexion, "SELECT COUNT(*) FROM CONT_VENTA WHERE ESTADO='A' AND YEAR(FECHA)=?", $anioSel);
$precioProm= $totalQQ > 0 ? $totalUSD / $totalQQ : 0;

// Ventas por mes, año seleccionado vs año anterior
function ventasPorMes($conexion, $anio) {
    $datos = array_fill(1, 12, 0.0);
    $stmt = mysqli_prepare($conexion, "SELECT MONTH(FECHA) AS MES, SUM(TOTAL) AS TOTAL FROM CONT_VENTA WHERE ESTADO='A' AND YEAR(FECHA)=? GROUP BY MES");
    mysqli_stmt_bind_param($stmt, "i", $anio);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) { $datos[(int)$r['MES']] = (float)$r['TOTAL']; }
    mysqli_stmt_close($stmt);
    return array_values($datos);
}
$ventasMesActual = ventasPorMes($conexion, $anioSel);
$ventasMesAnterior = ventasPorMes($conexion, $anioAnterior);

// Top 10 productos por $ en el año seleccionado
$topProductos = [];
$stmtTop = mysqli_prepare($conexion, "SELECT P.NOMBRE, SUM(V.TOTAL) AS TOTAL, SUM(V.QQ) AS QQ
                                       FROM CONT_VENTA V INNER JOIN CONT_PRODUCTO P ON V.ID_PRODUCTO = P.ID_PRODUCTO
                                       WHERE V.ESTADO='A' AND YEAR(V.FECHA)=?
                                       GROUP BY V.ID_PRODUCTO ORDER BY TOTAL DESC LIMIT 10");
mysqli_stmt_bind_param($stmtTop, "i", $anioSel);
mysqli_stmt_execute($stmtTop);
$resTop = mysqli_stmt_get_result($stmtTop);
while ($r = mysqli_fetch_assoc($resTop)) { $topProductos[] = $r; }
mysqli_stmt_close($stmtTop);

// Con factura vs sin factura
$tipoLabels = ['Con Factura', 'Sin Factura'];
$tipoData = [
    kpiNum($conexion, "SELECT IFNULL(SUM(TOTAL),0) FROM CONT_VENTA WHERE ESTADO='A' AND YEAR(FECHA)=? AND TIPO='CON_FACTURA'", $anioSel),
    kpiNum($conexion, "SELECT IFNULL(SUM(TOTAL),0) FROM CONT_VENTA WHERE ESTADO='A' AND YEAR(FECHA)=? AND TIPO='SIN_FACTURA'", $anioSel),
];

// Resumen completo por producto (tabla)
$resumenProductos = [];
$stmtRes = mysqli_prepare($conexion, "SELECT P.CODIGO, P.NOMBRE, SUM(V.QQ) AS QQ, SUM(V.TOTAL) AS TOTAL, AVG(V.PRECIO) AS PRECIO_PROM
                                       FROM CONT_VENTA V INNER JOIN CONT_PRODUCTO P ON V.ID_PRODUCTO = P.ID_PRODUCTO
                                       WHERE V.ESTADO='A' AND YEAR(V.FECHA)=?
                                       GROUP BY V.ID_PRODUCTO ORDER BY TOTAL DESC");
mysqli_stmt_bind_param($stmtRes, "i", $anioSel);
mysqli_stmt_execute($stmtRes);
$resResumen = mysqli_stmt_get_result($stmtRes);
?>
<head>
    <meta charset="utf-8">
    <title>Estadísticas de Ventas</title>
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
                    <div class="widget-heading">Admin <?php echo htmlspecialchars($_SESSION["username"]); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="app-main">
        <div class="app-sidebar sidebar-shadow">
            <?php include("./menu/menu_$rol_usuario.php"); ?>
        </div>
        <div class="app-main__outer">
            <div class="app-main__inner">
                <div class="app-page-title">
                    <div class="page-title-wrapper">
                        <div class="page-title-heading">
                            <div class="page-title-icon"><i class="pe-7s-graph3 icon-gradient bg-warm-flame"></i></div>
                            <div><div class="page-title-subheading text-black">Estadísticas de Ventas</div></div>
                            <form method="GET" class="ms-auto">
                                <select class="form-select" name="anio" onchange="this.form.submit()">
                                    <?php foreach ($anios as $a): ?>
                                        <option value="<?php echo $a; ?>" <?php echo $a == $anioSel ? 'selected' : ''; ?>><?php echo $a; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body">
                            <div style="font-size:1.8rem;color:#0f3460;"><i class="bi bi-boxes"></i></div>
                            <h4 class="mb-0"><?php echo number_format($totalQQ, 2); ?></h4><small class="text-muted">Quintales vendidos</small>
                        </div></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body">
                            <div style="font-size:1.8rem;color:#2E7D32;"><i class="bi bi-cash-stack"></i></div>
                            <h4 class="mb-0">$<?php echo number_format($totalUSD, 2); ?></h4><small class="text-muted">Total vendido</small>
                        </div></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body">
                            <div style="font-size:1.8rem;color:#F57F17;"><i class="bi bi-receipt"></i></div>
                            <h4 class="mb-0"><?php echo number_format($totalTrans); ?></h4><small class="text-muted">Registros de venta</small>
                        </div></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body">
                            <div style="font-size:1.8rem;color:#8e44ad;"><i class="bi bi-tag"></i></div>
                            <h4 class="mb-0">$<?php echo number_format($precioProm, 2); ?></h4><small class="text-muted">Precio promedio / QQ</small>
                        </div></div>
                    </div>
                </div>

                <div class="row g-3 align-items-start">
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-3">Ventas Confirmadas por Mes — Comparativo <?php echo $anioAnterior; ?> vs <?php echo $anioSel; ?></h6>
                                <div style="position:relative; height:280px;"><canvas id="chartMeses"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-3">Con Factura vs Sin Factura</h6>
                                <div style="position:relative; height:280px;"><canvas id="chartTipo"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1 align-items-start">
                    <div class="col-md-5">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-3">Top 10 Productos por Ventas</h6>
                                <div style="position:relative; height:320px;"><canvas id="chartTop"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-3">Resumen por Producto — <?php echo $anioSel; ?></h6>
                                <div class="table-responsive" style="max-height:340px; overflow-y:auto;">
                                    <table class="table table-sm table-hover">
                                        <thead class="bg-light"><tr><th>Código</th><th>Producto</th><th>QQ</th><th>Precio Prom.</th><th>Total $</th></tr></thead>
                                        <tbody>
                                        <?php if (mysqli_num_rows($resResumen) === 0): ?>
                                            <tr><td colspan="5" class="text-center text-muted">Sin ventas registradas para <?php echo $anioSel; ?>.</td></tr>
                                        <?php else: while ($r = mysqli_fetch_assoc($resResumen)): ?>
                                            <tr>
                                                <td class="small"><?php echo htmlspecialchars($r['CODIGO'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($r['NOMBRE']); ?></td>
                                                <td><?php echo number_format($r['QQ'], 2); ?></td>
                                                <td>$<?php echo number_format($r['PRECIO_PROM'], 2); ?></td>
                                                <td>$<?php echo number_format($r['TOTAL'], 2); ?></td>
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
        </div>
    </div>
</div>
<script>
new Chart(document.getElementById('chartMeses'), {
    type: 'line',
    data: {
        labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        datasets: [
            { label: '<?php echo $anioSel; ?>', data: <?php echo json_encode($ventasMesActual); ?>, borderColor: '#0f3460', backgroundColor: 'rgba(15,52,96,0.1)', fill: true, tension: 0.3 },
            { label: '<?php echo $anioAnterior; ?>', data: <?php echo json_encode($ventasMesAnterior); ?>, borderColor: '#b0bec5', borderDash: [5,5], fill: false, tension: 0.3 }
        ]
    },
    options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
});
new Chart(document.getElementById('chartTipo'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($tipoLabels); ?>,
        datasets: [{ data: <?php echo json_encode($tipoData); ?>, backgroundColor: ['#0f3460','#e94560'] }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('chartTop'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($topProductos, 'NOMBRE')); ?>,
        datasets: [{ label: 'Total $', data: <?php echo json_encode(array_map('floatval', array_column($topProductos, 'TOTAL'))); ?>, backgroundColor: '#0f3460' }]
    },
    options: { maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>
