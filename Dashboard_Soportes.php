<?php
ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD.php");
$conexion = conectarse();

if(!isset($_SESSION["rol"])){
    header("Location: break.php");
}else {
    $now = time();
    if ($now > $_SESSION['expire']) {
        session_destroy();
        header("Location: expirada.php");
    }
}

// Filtro de año
$anio_filtro = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$anio_anterior = $anio_filtro - 1;

// ---- TOTALES GENERALES ----
$sql_total = "SELECT COUNT(*) as total FROM COTI_CALENDARIO
              WHERE ESTADO='A' AND YEAR(FECHA_SOPORTE) = $anio_filtro";
$total = mysqli_fetch_assoc(mysqli_query($conexion, $sql_total))['total'];

$sql_confirmadas = "SELECT COUNT(*) as total FROM COTI_CALENDARIO
                    WHERE ESTADO='A' AND ESTADO_SOPORTE='Confirmada' AND YEAR(FECHA_SOPORTE) = $anio_filtro";
$total_confirmadas = mysqli_fetch_assoc(mysqli_query($conexion, $sql_confirmadas))['total'];

$sql_pendientes = "SELECT COUNT(*) as total FROM COTI_CALENDARIO
                   WHERE ESTADO='A' AND ESTADO_SOPORTE='Pendiente' AND YEAR(FECHA_SOPORTE) = $anio_filtro";
$total_pendientes = mysqli_fetch_assoc(mysqli_query($conexion, $sql_pendientes))['total'];

// Horas totales trabajadas
$sql_horas = "SELECT SUM(TIMESTAMPDIFF(MINUTE, HORA_INICIO, HORA_FIN)) as minutos
              FROM COTI_CALENDARIO
              WHERE ESTADO='A' AND ESTADO_SOPORTE='Confirmada' AND YEAR(FECHA_SOPORTE) = $anio_filtro";
$minutos = mysqli_fetch_assoc(mysqli_query($conexion, $sql_horas))['minutos'];
$horas_totales = round($minutos / 60, 1);

// ---- SOPORTES POR MES ----
$sql_mes = "SELECT MONTH(FECHA_SOPORTE) as mes, COUNT(*) as total
            FROM COTI_CALENDARIO
            WHERE ESTADO='A' AND ESTADO_SOPORTE='Confirmada' AND YEAR(FECHA_SOPORTE) = $anio_filtro
            GROUP BY MONTH(FECHA_SOPORTE) ORDER BY mes";
$res_mes = mysqli_query($conexion, $sql_mes);
$datos_mes = array_fill(1, 12, 0);
while($row = mysqli_fetch_assoc($res_mes)) {
    $datos_mes[$row['mes']] = (int)$row['total'];
}

// ---- SOPORTES POR TIPO ----
$sql_tipo = "SELECT ts.SOPORTE, COUNT(c.ID_CALENDARIO_SOPORTE) as total
             FROM COTI_CALENDARIO c
             INNER JOIN COTI_TIPO_SOPORTE ts ON c.ID_SOPORTE = ts.ID_TIPO_SOPORTE
             WHERE c.ESTADO='A' AND YEAR(c.FECHA_SOPORTE) = $anio_filtro
             GROUP BY c.ID_SOPORTE ORDER BY total DESC";
$res_tipo = mysqli_query($conexion, $sql_tipo);
$tipos_labels = []; $tipos_data = [];
while($row = mysqli_fetch_assoc($res_tipo)) {
    $tipos_labels[] = $row['SOPORTE'];
    $tipos_data[] = (int)$row['total'];
}

// ---- COMPARATIVO AÑO ANTERIOR ----
$sql_comp = "SELECT MONTH(FECHA_SOPORTE) as mes, COUNT(*) as total
             FROM COTI_CALENDARIO
             WHERE ESTADO='A' AND ESTADO_SOPORTE='Confirmada' AND YEAR(FECHA_SOPORTE) = $anio_anterior
             GROUP BY MONTH(FECHA_SOPORTE) ORDER BY mes";
$res_comp = mysqli_query($conexion, $sql_comp);
$datos_anterior = array_fill(1, 12, 0);
while($row = mysqli_fetch_assoc($res_comp)) {
    $datos_anterior[$row['mes']] = (int)$row['total'];
}

// ---- ULTIMOS 10 SOPORTES ----
$sql_ultimos = "SELECT c.FECHA_SOPORTE, c.HORA_INICIO, c.HORA_FIN, c.ESTADO_SOPORTE,
                       ts.SOPORTE as TIPO, c.COMENTARIO
                FROM COTI_CALENDARIO c
                INNER JOIN COTI_TIPO_SOPORTE ts ON c.ID_SOPORTE = ts.ID_TIPO_SOPORTE
                WHERE c.ESTADO='A'
                ORDER BY c.FECHA_SOPORTE DESC, c.HORA_INICIO DESC
                LIMIT 10";
$res_ultimos = mysqli_query($conexion, $sql_ultimos);

// ---- HORAS POR TIPO ----
$sql_horas_tipo = "SELECT ts.SOPORTE,
                          ROUND(SUM(TIMESTAMPDIFF(MINUTE, c.HORA_INICIO, c.HORA_FIN))/60, 1) as horas
                   FROM COTI_CALENDARIO c
                   INNER JOIN COTI_TIPO_SOPORTE ts ON c.ID_SOPORTE = ts.ID_TIPO_SOPORTE
                   WHERE c.ESTADO='A' AND ESTADO_SOPORTE='Confirmada' AND YEAR(c.FECHA_SOPORTE) = $anio_filtro
                   GROUP BY c.ID_SOPORTE ORDER BY horas DESC";
$res_horas_tipo = mysqli_query($conexion, $sql_horas_tipo);
$ht_labels = []; $ht_data = [];
while($row = mysqli_fetch_assoc($res_horas_tipo)) {
    $ht_labels[] = $row['SOPORTE'];
    $ht_data[] = (float)$row['horas'];
}

// Años disponibles para filtro
$sql_anios = "SELECT DISTINCT YEAR(FECHA_SOPORTE) as anio FROM COTI_CALENDARIO WHERE ESTADO='A' ORDER BY anio DESC";
$res_anios = mysqli_query($conexion, $sql_anios);
$anios = [];
while($row = mysqli_fetch_assoc($res_anios)) $anios[] = $row['anio'];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <title>Dashboard Soportes Técnicos</title>
    <link rel="shortcut icon" href="images/icono.png">
    <link href="./main.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .dash-card { border-radius: 10px; padding: 20px; color: #fff; margin-bottom: 20px; }
        .dash-card .number { font-size: 2.5rem; font-weight: 700; }
        .dash-card .label  { font-size: 0.9rem; opacity: 0.85; }
        .bg-confirmada  { background: linear-gradient(135deg, #28a745, #20c997); }
        .bg-pendiente   { background: linear-gradient(135deg, #fd7e14, #ffc107); }
        .bg-total       { background: linear-gradient(135deg, #007bff, #6610f2); }
        .bg-horas       { background: linear-gradient(135deg, #e83e8c, #fd7e14); }
        .chart-box { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        .badge-confirmada { background:#28a745; color:#fff; padding:3px 8px; border-radius:4px; font-size:.8rem; }
        .badge-pendiente  { background:#fd7e14; color:#fff; padding:3px 8px; border-radius:4px; font-size:.8rem; }
        .filtro-anio { display:inline-flex; gap:8px; align-items:center; }
        .table-ultimos td, .table-ultimos th { vertical-align: middle; font-size: .88rem; }
        .comentario-cell { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>
<div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">

    <?php include("menu/header.php"); ?>
    <div class="app-sidebar sidebar-shadow">
        <?php
            $rol = $_SESSION['rol'] ?? '';
            if($rol == 'SISTEMA' || $rol == 'ADMINISTRADOR') {
                include("menu/menu_adm.php");
            } elseif($rol == 'GERENTES') {
                include("menu/menu_GERENTES.php");
            } else {
                include("menu/menu_SISTEMA.php");
            }
        ?>
    </div>

    <div class="app-main">
        <div class="app-main__outer">
            <div class="app-main__inner">

                <!-- Encabezado -->
                <div class="app-page-title">
                    <div class="page-title-wrapper">
                        <div class="page-title-heading">
                            <div class="page-title-icon">
                                <i class="pe-7s-graph3 icon-gradient bg-mean-fruit"></i>
                            </div>
                            <div>Dashboard Soportes Técnicos
                                <div class="page-title-subheading">Resumen de actividades de soporte técnico</div>
                            </div>
                        </div>
                        <div class="page-title-actions">
                            <form method="GET" class="filtro-anio">
                                <label class="mb-0"><strong>Año:</strong></label>
                                <select name="anio" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <?php foreach($anios as $a): ?>
                                        <option value="<?= $a ?>" <?= $a==$anio_filtro?'selected':'' ?>><?= $a ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tarjetas resumen -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="dash-card bg-total">
                            <div class="number"><?= $total ?></div>
                            <div class="label"><i class="pe-7s-note2"></i> Total Soportes <?= $anio_filtro ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-card bg-confirmada">
                            <div class="number"><?= $total_confirmadas ?></div>
                            <div class="label"><i class="pe-7s-check"></i> Confirmados</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-card bg-pendiente">
                            <div class="number"><?= $total_pendientes ?></div>
                            <div class="label"><i class="pe-7s-clock"></i> Pendientes</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dash-card bg-horas">
                            <div class="number"><?= $horas_totales ?>h</div>
                            <div class="label"><i class="pe-7s-timer"></i> Horas Trabajadas</div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos fila 1 -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="chart-box">
                            <h6 class="mb-3"><strong>Soportes Confirmados por Mes — Comparativo <?= $anio_anterior ?> vs <?= $anio_filtro ?></strong></h6>
                            <canvas id="chartMes" height="100"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="chart-box">
                            <h6 class="mb-3"><strong>Distribución por Tipo</strong></h6>
                            <canvas id="chartTipo" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico horas por tipo -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-box">
                            <h6 class="mb-3"><strong>Horas por Tipo de Soporte — <?= $anio_filtro ?></strong></h6>
                            <canvas id="chartHorasTipo" height="140"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-box">
                            <h6 class="mb-3"><strong>Soportes por Mes — <?= $anio_filtro ?></strong></h6>
                            <canvas id="chartBarMes" height="140"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Últimos soportes -->
                <div class="row">
                    <div class="col-12">
                        <div class="chart-box">
                            <h6 class="mb-3"><strong>Últimos 10 Soportes Registrados</strong></h6>
                            <table class="table table-hover table-ultimos">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Hora Inicio</th>
                                        <th>Hora Fin</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Comentario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while($row = mysqli_fetch_assoc($res_ultimos)): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($row['FECHA_SOPORTE'])) ?></td>
                                        <td><?= substr($row['HORA_INICIO'],0,5) ?></td>
                                        <td><?= substr($row['HORA_FIN'],0,5) ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($row['TIPO']) ?></span></td>
                                        <td>
                                            <?php if($row['ESTADO_SOPORTE']=='Confirmada'): ?>
                                                <span class="badge-confirmada">Confirmada</span>
                                            <?php else: ?>
                                                <span class="badge-pendiente"><?= $row['ESTADO_SOPORTE'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="comentario-cell" title="<?= htmlspecialchars($row['COMENTARIO']) ?>">
                                            <?= htmlspecialchars($row['COMENTARIO']) ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const datosActual  = <?= json_encode(array_values($datos_mes)) ?>;
const datosAnterior = <?= json_encode(array_values($datos_anterior)) ?>;
const tiposLabels  = <?= json_encode($tipos_labels) ?>;
const tiposData    = <?= json_encode($tipos_data) ?>;
const htLabels     = <?= json_encode($ht_labels) ?>;
const htData       = <?= json_encode($ht_data) ?>;

// Comparativo líneas
new Chart(document.getElementById('chartMes'), {
    type: 'line',
    data: {
        labels: meses,
        datasets: [
            {
                label: '<?= $anio_filtro ?>',
                data: datosActual,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0,123,255,0.1)',
                tension: 0.4, fill: true, pointRadius: 4
            },
            {
                label: '<?= $anio_anterior ?>',
                data: datosAnterior,
                borderColor: '#adb5bd',
                backgroundColor: 'rgba(173,181,189,0.1)',
                tension: 0.4, fill: true, pointRadius: 4,
                borderDash: [5,5]
            }
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

// Dona tipos
const colores = ['#007bff','#28a745','#fd7e14','#e83e8c','#6610f2','#20c997','#ffc107','#17a2b8'];
new Chart(document.getElementById('chartTipo'), {
    type: 'doughnut',
    data: { labels: tiposLabels, datasets: [{ data: tiposData, backgroundColor: colores }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Horas por tipo (barras horizontales)
new Chart(document.getElementById('chartHorasTipo'), {
    type: 'bar',
    data: {
        labels: htLabels,
        datasets: [{ label: 'Horas', data: htData, backgroundColor: colores }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
    }
});

// Barras por mes
new Chart(document.getElementById('chartBarMes'), {
    type: 'bar',
    data: {
        labels: meses,
        datasets: [{
            label: 'Soportes <?= $anio_filtro ?>',
            data: datosActual,
            backgroundColor: 'rgba(0,123,255,0.7)'
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
</body>
</html>
