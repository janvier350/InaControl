<!doctype html>
<html lang="en">
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

$totalDvr    = (int) mysqli_fetch_row($conexion->query("SELECT COUNT(*) FROM CCTV_DVR WHERE ESTADO='A'"))[0];
$totalCam    = (int) mysqli_fetch_row($conexion->query("SELECT COUNT(*) FROM CCTV_CAMARA WHERE ESTADO='A'"))[0];
$totalAbiertas = (int) mysqli_fetch_row($conexion->query("SELECT COUNT(*) FROM CCTV_INCIDENCIA WHERE ESTADO='Abierta'"))[0];

// Cámaras con más de 4 años de antigüedad (compra registrada)
$camarasViejas = (int) mysqli_fetch_row($conexion->query(
    "SELECT COUNT(*) FROM CCTV_CAMARA WHERE ESTADO='A' AND FECHA_COMPRA IS NOT NULL AND FECHA_COMPRA <= DATE_SUB(CURDATE(), INTERVAL 4 YEAR)"
))[0];

// Distribución por tipo de cámara
$tiposLabels = []; $tiposData = [];
$qt = $conexion->query("SELECT TIPO_CAMARA, COUNT(*) AS TOTAL FROM CCTV_CAMARA WHERE ESTADO='A' GROUP BY TIPO_CAMARA");
while ($t = mysqli_fetch_assoc($qt)) { $tiposLabels[] = $t['TIPO_CAMARA']; $tiposData[] = (int)$t['TOTAL']; }

// Top 5 equipos con más incidencias (fallas)
$rankingLabels = []; $rankingData = [];
$qr = $conexion->query(
    "SELECT
        CASE WHEN I.ID_CAMARA IS NOT NULL THEN CONCAT('Cámara: ', C.MARCA, ' ', C.MODELO, ' (', IFNULL(C.UBICACION,'s/u'), ')')
             ELSE CONCAT(D.TIPO, ': ', D.MARCA, ' ', D.MODELO, ' (', IFNULL(D.UBICACION,'s/u'), ')') END AS EQUIPO,
        COUNT(*) AS TOTAL
     FROM CCTV_INCIDENCIA I
     LEFT JOIN CCTV_CAMARA C ON I.ID_CAMARA = C.ID_CAMARA
     LEFT JOIN CCTV_DVR D ON I.ID_DVR = D.ID_DVR
     WHERE I.TIPO IN ('Falla','Reparacion')
     GROUP BY EQUIPO
     ORDER BY TOTAL DESC
     LIMIT 5"
);
while ($r = mysqli_fetch_assoc($qr)) { $rankingLabels[] = $r['EQUIPO']; $rankingData[] = (int)$r['TOTAL']; }

// Cámaras próximas a antigüedad crítica (entre 3 y 4 años) - alerta preventiva
$qAlerta = $conexion->query(
    "SELECT MARCA, MODELO, UBICACION, FECHA_COMPRA FROM CCTV_CAMARA
     WHERE ESTADO='A' AND FECHA_COMPRA IS NOT NULL
       AND FECHA_COMPRA BETWEEN DATE_SUB(CURDATE(), INTERVAL 4 YEAR) AND DATE_SUB(CURDATE(), INTERVAL 3 YEAR)
     ORDER BY FECHA_COMPRA ASC"
);
?>
<head>
    <meta charset="utf-8">
    <title>CCTV - Dashboard</title>
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
        <div class="app-header__mobile-menu">
            <div><button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box"><span class="hamburger-inner"></span></span>
            </button></div>
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
                            <div>
                                <div class="page-title-subheading text-black">CCTV - Dashboard</div>
                                <p class="text-dark">Estado general del sistema de video vigilancia.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body">
                            <div style="font-size:1.8rem;color:#0f3460;"><i class="bi bi-hdd-network"></i></div>
                            <h3 class="mb-0"><?php echo $totalDvr; ?></h3><small class="text-muted">DVR / NVR</small>
                        </div></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body">
                            <div style="font-size:1.8rem;color:#1a6b8a;"><i class="bi bi-camera-video"></i></div>
                            <h3 class="mb-0"><?php echo $totalCam; ?></h3><small class="text-muted">Cámaras activas</small>
                        </div></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body">
                            <div style="font-size:1.8rem;color:#d92550;"><i class="bi bi-exclamation-triangle"></i></div>
                            <h3 class="mb-0"><?php echo $totalAbiertas; ?></h3><small class="text-muted">Incidencias abiertas</small>
                        </div></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card text-center border-0 shadow-sm"><div class="card-body">
                            <div style="font-size:1.8rem;color:#f7b924;"><i class="bi bi-clock-history"></i></div>
                            <h3 class="mb-0"><?php echo $camarasViejas; ?></h3><small class="text-muted">Cámaras +4 años</small>
                        </div></div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100"><div class="card-body">
                            <h6 class="mb-3">Distribución por tipo de cámara</h6>
                            <div style="position:relative; height:260px;">
                                <canvas id="chartTipos"></canvas>
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100"><div class="card-body">
                            <h6 class="mb-3">Top equipos con más fallas / reparaciones</h6>
                            <div style="position:relative; height:260px;">
                                <canvas id="chartRanking"></canvas>
                            </div>
                        </div></div>
                    </div>
                </div>

                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="bi bi-exclamation-circle text-warning"></i> Cámaras a vigilar (entre 3 y 4 años de uso — considerar plan de reemplazo)</h6>
                        <table class="table table-sm">
                            <thead><tr><th>Marca / Modelo</th><th>Ubicación</th><th>Fecha de compra</th></tr></thead>
                            <tbody>
                            <?php if (mysqli_num_rows($qAlerta) === 0): ?>
                                <tr><td colspan="3" class="text-center text-muted">Sin cámaras en este rango de antigüedad.</td></tr>
                            <?php else: while ($a = mysqli_fetch_assoc($qAlerta)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['MARCA'].' '.$a['MODELO']); ?></td>
                                    <td><?php echo htmlspecialchars($a['UBICACION'] ?: '-'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($a['FECHA_COMPRA'])); ?></td>
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
new Chart(document.getElementById('chartTipos'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($tiposLabels); ?>,
        datasets: [{ data: <?php echo json_encode($tiposData); ?>, backgroundColor: ['#0f3460','#1a6b8a','#e94560','#f5a623','#8e44ad'] }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('chartRanking'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($rankingLabels); ?>,
        datasets: [{ label: 'Incidencias', data: <?php echo json_encode($rankingData); ?>, backgroundColor: '#d92550' }]
    },
    options: { maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>
