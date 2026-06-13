<?php
ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD.php");
$conexion = conectarse();
$conexion->set_charset("utf8");

// Verificación de sesión y tiempo de expiración
if(!isset($_SESSION["rol"])){
    header("Location: break.php");
}else {
    $now = time();
    if ($now > $_SESSION['expire']) {
        session_destroy();
        header("Location: expirada.php");
    }
}


$sql_chart = "
  SELECT 
    C.SOPORTE AS tipo_soporte,
    MONTH(A.FECHA_SOPORTE) AS mes,
    YEAR(A.FECHA_SOPORTE) AS anio,
    SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, A.HORA_INICIO, A.HORA_FIN))) AS total_horas,
    SUM(TIMESTAMPDIFF(SECOND, A.HORA_INICIO, A.HORA_FIN)) AS total_segundos
  FROM COTI_CALENDARIO A
  INNER JOIN COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
  GROUP BY anio, mes, tipo_soporte
  ORDER BY anio DESC, mes DESC
";
$query_chart = $conexion->query($sql_chart);


$datos_por_mes = [];
while ($row = mysqli_fetch_assoc($query_chart)) {
    $mes = $row['anio'] . '-' . str_pad($row['mes'], 2, '0', STR_PAD_LEFT);
    $datos_por_mes[$mes]['tipos'][] = $row['tipo_soporte'];
    $datos_por_mes[$mes]['valores'][] = round($row['total_segundos'] / 3600, 2); // en horas
}

// --- Manejo de Filtros ---
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');
$tipo_soporte = isset($_GET['tipo_soporte']) ? $_GET['tipo_soporte'] : '';

// --- Consulta Principal con Filtros ---
$sql_horas_dia = "
    SELECT 
        DAYNAME(FECHA_SOPORTE) AS dia_semana,
        DAY(FECHA_SOPORTE) AS dia_mes,
        C.SOPORTE AS tipo_soporte,
        SUM(TIMESTAMPDIFF(MINUTE, HORA_INICIO, HORA_FIN)) / 60 AS horas
    FROM
        COTI_CALENDARIO A
    LEFT JOIN 
        COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
    WHERE 
        A.ESTADO_SOPORTE = 'Confirmada'
        AND MONTH(A.FECHA_SOPORTE) = ?
        AND YEAR(A.FECHA_SOPORTE) = ?
        " . ($tipo_soporte ? " AND C.SOPORTE = ?" : "") . "
    GROUP BY 
        A.FECHA_SOPORTE, C.SOPORTE
    ORDER BY 
        A.FECHA_SOPORTE DESC";
        
        $stmt = $conexion->prepare($sql_horas_dia);
if ($tipo_soporte) {
    $stmt->bind_param("iis", $mes, $anio, $tipo_soporte);
} else {
    $stmt->bind_param("ii", $mes, $anio);
}
$stmt->execute();
$result_horas = $stmt->get_result();
$rol_usuario = $_SESSION["rol"];

// --- Exportar a Excel ---
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="reporte_horas_' . date('Y-m-d') . '.xls"');
    
    echo "<table border='1'>
            <tr>
                <th>Día</th>
                <th>Fecha</th>
                <th>Tipo Soporte</th>
                <th>Horas</th>
            </tr>";
    
    while ($row = $result_horas->fetch_assoc()) {
        $horas_decimal = $row['horas'];
        $horas_enteras = floor($horas_decimal);
        $minutos = round(($horas_decimal - $horas_enteras) * 60);
        echo "<tr>
                <td>" . $row['dia_semana'] . "</td>
                <td>" . $row['dia_mes'] . "</td>
                <td>" . $row['tipo_soporte'] . "</td>
                <td>" . $horas_enteras . " hrs " . $minutos . " min</td>
              </tr>";
    }
    echo "</table>";
    exit;
}

// --- Exportar a PDF ---
if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
    require_once('lib/tcpdf/tcpdf.php'); // Asegúrate de tener TCPDF instalado
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('InaControl');
    $pdf->SetTitle('Reporte de Horas');
    $pdf->AddPage();
    
    // Contenido PDF
    $html = '<h1>Reporte de Horas - ' . date('F Y') . '</h1>
             <table border="1" cellpadding="5">
                <tr>
                    <th>Día</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Horas</th>
                </tr>';
    
    $result_horas->data_seek(0); // Reiniciar puntero
    while ($row = $result_horas->fetch_assoc()) {
        $html .= '<tr>
                    <td>' . $row['dia_semana'] . '</td>
                    <td>' . $row['dia_mes'] . '</td>
                    <td>' . $row['tipo_soporte'] . '</td>
                    <td>' . $row['horas'] . '</td>
                  </tr>';
    }
    $html .= '</table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('reporte_horas.pdf', 'D');
    exit;
}


// --- NUEVA CONSULTA PARA HORAS POR DÍA (MES ACTUAL) ---
$sql_horas_dia = "
    SELECT 
        DAYNAME(FECHA_SOPORTE) AS dia_semana,
        DAY(FECHA_SOPORTE) AS dia_mes,
        SUM(TIMESTAMPDIFF(HOUR, HORA_INICIO, HORA_FIN)) AS horas
    FROM 
        COTI_CALENDARIO
    WHERE 
        ESTADO_SOPORTE = 'Confirmada'
        AND MONTH(FECHA_SOPORTE) = MONTH(CURRENT_DATE())
        AND YEAR(FECHA_SOPORTE) = YEAR(CURRENT_DATE())
    GROUP BY 
        FECHA_SOPORTE
    ORDER BY 
        FECHA_SOPORTE DESC"; // Orden descendente para ver días recientes primero

$result_horas = $conexion->query($sql_horas_dia);

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      <title>InaControl - Ayuda Tecnologica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="description" content="Huge selection of charts created with the React ChartJS Plugin">
    <meta name="msapplication-tap-highlight" content="no">
    
    <!--<canvas id="graficoSoportes" width="400" height="400"></canvas>-->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
        .filter-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .export-btn {
            margin-left: 10px;
        }
    </style>
    <!--
    =========================================================
    * ArchitectUI HTML Theme Dashboard - v1.0.0
    =========================================================
    * Product Page: https://dashboardpack.com
    * Copyright 2019 DashboardPack (https://dashboardpack.com)
    * Licensed under MIT (https://github.com/DashboardPack/architectui-html-theme-free/blob/master/LICENSE)
    =========================================================
    * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
    -->
<link href="./main.css" rel="stylesheet">
</head>
<body>
    <div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">
        <div class="app-header header-shadow">
            <div class="app-header__logo">
                <div class="logo-src"></div>
                <div class="header__pane ml-auto">
                    <div>
                        <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                            <span class="hamburger-box">
                                <span class="hamburger-inner"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="app-header__mobile-menu">
                <div>
                    <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                        <span class="hamburger-box">
                            <span class="hamburger-inner"></span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="app-header__menu">
                <span>
                    <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                        <span class="btn-icon-wrapper">
                            <i class="fa fa-ellipsis-v fa-w-6"></i>
                        </span>
                    </button>
                </span>
            </div>    <div class="app-header__content">
                <div class="app-header-left">
                    <div class="search-wrapper">
                        <div class="input-holder">
                            <input type="text" class="search-input" placeholder="Type to search">
                            <button class="search-icon"><span></span></button>
                        </div>
                        <button class="close"></button>
                    </div>
                    <ul class="header-menu nav">
                        <li class="nav-item">
                            <a href="javascript:void(0);" class="nav-link">
                                <i class="nav-link-icon fa fa-database"> </i>
                                Statistics
                            </a>
                        </li>
                        <li class="btn-group nav-item">
                            <a href="javascript:void(0);" class="nav-link">
                                <i class="nav-link-icon fa fa-edit"></i>
                                Projects
                            </a>
                        </li>
                        <li class="dropdown nav-item">
                            <a href="javascript:void(0);" class="nav-link">
                                <i class="nav-link-icon fa fa-cog"></i>
                                Settings
                            </a>
                        </li>
                    </ul>        </div>
                    
                    <?php
                        $ultimoMes = array_key_first($datos_por_mes);
                        $labels = json_encode($datos_por_mes[$ultimoMes]['tipos']);
                        $datos = json_encode($datos_por_mes[$ultimoMes]['valores']);
                        ?>
                        
                        <script>
                          const ctx = document.getElementById('graficoSoportes').getContext('2d');
                          new Chart(ctx, {
                            type: 'pie',
                            data: {
                              labels: <?= $labels ?>,
                              datasets: [{
                                label: 'Horas por tipo de soporte - <?= $ultimoMes ?>',
                                data: <?= $datos ?>,
                                backgroundColor: [
                                  '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                                ],
                                borderWidth: 1
                              }]
                            },
                            options: {
                              responsive: true,
                              plugins: {
                                title: {
                                  display: true,
                                  text: 'Distribución de Soporte Técnico por Tipo (<?= $ultimoMes ?>)'
                                }
                              }
                            }
                          });
                        </script>

                <div class="app-header-right">
                    <div class="header-btn-lg pr-0">
                        <div class="widget-content p-0">
                            <div class="widget-content-wrapper">
                                <div class="widget-content-left">
                                    <div class="btn-group">
                                        <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                            <img width="42" class="rounded-circle" src="assets/images/avatars/10.jpg" alt="">
                                            <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                        </a>
                                        <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                            <!--<button type="button" tabindex="0" class="dropdown-item">User Account</button>
                                            <button type="button" tabindex="0" class="dropdown-item">Settings</button>
                                            <h6 tabindex="-1" class="dropdown-header">Header</h6>
                                            <button type="button" tabindex="0" class="dropdown-item">Actions</button>-->
                                             <button type="button" tabindex="0" class="dropdown-item">Perfil de Usuario</button>
                                                    <button type="button" tabindex="0" class="dropdown-item">Configuración</button>
                                                    <div tabindex="-1" class="dropdown-divider"></div>
                                                    <a type="button" tabindex="0" href="salir.php" class="dropdown-item">Cerrar Sesión</a>
                                           <!--    
                                            <div tabindex="-1" class="dropdown-divider"></div>
                                            <button type="button" tabindex="0" class="dropdown-item">Dividers</button>-->
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content-left  ml-3 header-user-info">
                                    <div class="widget-heading">
                                         <?php echo $_SESSION["username"] ?>
                                    </div>
                                    <div class="widget-subheading">
                                     <?php echo   $rol_usuario ?>
                                    </div>
                                </div>
                                <div class="widget-content-right header-user-info ml-3">
                                    <button type="button" class="btn-shadow p-1 btn btn-primary btn-sm show-toastr-example">
                                        <i class="fa text-white fa-calendar pr-1 pl-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>        </div>
            </div>
        </div>        <div class="ui-theme-settings">
            <button type="button" id="TooltipDemo" class="btn-open-options btn btn-warning">
                <i class="fa fa-cog fa-w-16 fa-spin fa-2x"></i>
            </button>
            <div class="theme-settings__inner">
                <div class="scrollbar-container">
                    <div class="theme-settings__options-wrapper">
                        <h3 class="themeoptions-heading">Layout Options
                        </h3>
                        <div class="p-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-wrapper">
                                            <div class="widget-content-left mr-3">
                                                <div class="switch has-switch switch-container-class" data-class="fixed-header">
                                                    <div class="switch-animate switch-on">
                                                        <input type="checkbox" checked data-toggle="toggle" data-onstyle="success">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="widget-content-left">
                                                <div class="widget-heading">Fixed Header
                                                </div>
                                                <div class="widget-subheading">Makes the header top fixed, always visible!
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-wrapper">
                                            <div class="widget-content-left mr-3">
                                                <div class="switch has-switch switch-container-class" data-class="fixed-sidebar">
                                                    <div class="switch-animate switch-on">
                                                        <input type="checkbox" checked data-toggle="toggle" data-onstyle="success">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="widget-content-left">
                                                <div class="widget-heading">Fixed Sidebar
                                                </div>
                                                <div class="widget-subheading">Makes the sidebar left fixed, always visible!
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-wrapper">
                                            <div class="widget-content-left mr-3">
                                                <div class="switch has-switch switch-container-class" data-class="fixed-footer">
                                                    <div class="switch-animate switch-off">
                                                        <input type="checkbox" data-toggle="toggle" data-onstyle="success">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="widget-content-left">
                                                <div class="widget-heading">Fixed Footer
                                                </div>
                                                <div class="widget-subheading">Makes the app footer bottom fixed, always visible!
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <h3 class="themeoptions-heading">
                            <div>
                                Header Options
                            </div>
                            <button type="button" class="btn-pill btn-shadow btn-wide ml-auto btn btn-focus btn-sm switch-header-cs-class" data-class="">
                                Restore Default
                            </button>
                        </h3>
                        <div class="p-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <h5 class="pb-2">Choose Color Scheme
                                    </h5>
                                    <div class="theme-settings-swatches">
                                        <div class="swatch-holder bg-primary switch-header-cs-class" data-class="bg-primary header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-secondary switch-header-cs-class" data-class="bg-secondary header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-success switch-header-cs-class" data-class="bg-success header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-info switch-header-cs-class" data-class="bg-info header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-warning switch-header-cs-class" data-class="bg-warning header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-danger switch-header-cs-class" data-class="bg-danger header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-light switch-header-cs-class" data-class="bg-light header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-dark switch-header-cs-class" data-class="bg-dark header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-focus switch-header-cs-class" data-class="bg-focus header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-alternate switch-header-cs-class" data-class="bg-alternate header-text-light">
                                        </div>
                                        <div class="divider">
                                        </div>
                                        <div class="swatch-holder bg-vicious-stance switch-header-cs-class" data-class="bg-vicious-stance header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-midnight-bloom switch-header-cs-class" data-class="bg-midnight-bloom header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-night-sky switch-header-cs-class" data-class="bg-night-sky header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-slick-carbon switch-header-cs-class" data-class="bg-slick-carbon header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-asteroid switch-header-cs-class" data-class="bg-asteroid header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-royal switch-header-cs-class" data-class="bg-royal header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-warm-flame switch-header-cs-class" data-class="bg-warm-flame header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-night-fade switch-header-cs-class" data-class="bg-night-fade header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-sunny-morning switch-header-cs-class" data-class="bg-sunny-morning header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-tempting-azure switch-header-cs-class" data-class="bg-tempting-azure header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-amy-crisp switch-header-cs-class" data-class="bg-amy-crisp header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-heavy-rain switch-header-cs-class" data-class="bg-heavy-rain header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-mean-fruit switch-header-cs-class" data-class="bg-mean-fruit header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-malibu-beach switch-header-cs-class" data-class="bg-malibu-beach header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-deep-blue switch-header-cs-class" data-class="bg-deep-blue header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-ripe-malin switch-header-cs-class" data-class="bg-ripe-malin header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-arielle-smile switch-header-cs-class" data-class="bg-arielle-smile header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-plum-plate switch-header-cs-class" data-class="bg-plum-plate header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-happy-fisher switch-header-cs-class" data-class="bg-happy-fisher header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-happy-itmeo switch-header-cs-class" data-class="bg-happy-itmeo header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-mixed-hopes switch-header-cs-class" data-class="bg-mixed-hopes header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-strong-bliss switch-header-cs-class" data-class="bg-strong-bliss header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-grow-early switch-header-cs-class" data-class="bg-grow-early header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-love-kiss switch-header-cs-class" data-class="bg-love-kiss header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-premium-dark switch-header-cs-class" data-class="bg-premium-dark header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-happy-green switch-header-cs-class" data-class="bg-happy-green header-text-light">
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <h3 class="themeoptions-heading">
                            <div>Sidebar Options</div>
                            <button type="button" class="btn-pill btn-shadow btn-wide ml-auto btn btn-focus btn-sm switch-sidebar-cs-class" data-class="">
                                Restore Default
                            </button>
                        </h3>
                        <div class="p-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <h5 class="pb-2">Choose Color Scheme
                                    </h5>
                                    <div class="theme-settings-swatches">
                                        <div class="swatch-holder bg-primary switch-sidebar-cs-class" data-class="bg-primary sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-secondary switch-sidebar-cs-class" data-class="bg-secondary sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-success switch-sidebar-cs-class" data-class="bg-success sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-info switch-sidebar-cs-class" data-class="bg-info sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-warning switch-sidebar-cs-class" data-class="bg-warning sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-danger switch-sidebar-cs-class" data-class="bg-danger sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-light switch-sidebar-cs-class" data-class="bg-light sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-dark switch-sidebar-cs-class" data-class="bg-dark sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-focus switch-sidebar-cs-class" data-class="bg-focus sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-alternate switch-sidebar-cs-class" data-class="bg-alternate sidebar-text-light">
                                        </div>
                                        <div class="divider">
                                        </div>
                                        <div class="swatch-holder bg-vicious-stance switch-sidebar-cs-class" data-class="bg-vicious-stance sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-midnight-bloom switch-sidebar-cs-class" data-class="bg-midnight-bloom sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-night-sky switch-sidebar-cs-class" data-class="bg-night-sky sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-slick-carbon switch-sidebar-cs-class" data-class="bg-slick-carbon sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-asteroid switch-sidebar-cs-class" data-class="bg-asteroid sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-royal switch-sidebar-cs-class" data-class="bg-royal sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-warm-flame switch-sidebar-cs-class" data-class="bg-warm-flame sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-night-fade switch-sidebar-cs-class" data-class="bg-night-fade sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-sunny-morning switch-sidebar-cs-class" data-class="bg-sunny-morning sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-tempting-azure switch-sidebar-cs-class" data-class="bg-tempting-azure sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-amy-crisp switch-sidebar-cs-class" data-class="bg-amy-crisp sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-heavy-rain switch-sidebar-cs-class" data-class="bg-heavy-rain sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-mean-fruit switch-sidebar-cs-class" data-class="bg-mean-fruit sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-malibu-beach switch-sidebar-cs-class" data-class="bg-malibu-beach sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-deep-blue switch-sidebar-cs-class" data-class="bg-deep-blue sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-ripe-malin switch-sidebar-cs-class" data-class="bg-ripe-malin sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-arielle-smile switch-sidebar-cs-class" data-class="bg-arielle-smile sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-plum-plate switch-sidebar-cs-class" data-class="bg-plum-plate sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-happy-fisher switch-sidebar-cs-class" data-class="bg-happy-fisher sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-happy-itmeo switch-sidebar-cs-class" data-class="bg-happy-itmeo sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-mixed-hopes switch-sidebar-cs-class" data-class="bg-mixed-hopes sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-strong-bliss switch-sidebar-cs-class" data-class="bg-strong-bliss sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-grow-early switch-sidebar-cs-class" data-class="bg-grow-early sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-love-kiss switch-sidebar-cs-class" data-class="bg-love-kiss sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-premium-dark switch-sidebar-cs-class" data-class="bg-premium-dark sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-happy-green switch-sidebar-cs-class" data-class="bg-happy-green sidebar-text-light">
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <h3 class="themeoptions-heading">
                            <div>Main Content Options</div>
                            <button type="button" class="btn-pill btn-shadow btn-wide ml-auto active btn btn-focus btn-sm">Restore Default
                            </button>
                        </h3>
                        <div class="p-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <h5 class="pb-2">Page Section Tabs
                                    </h5>
                                    <div class="theme-settings-swatches">
                                        <div role="group" class="mt-2 btn-group">
                                            <button type="button" class="btn-wide btn-shadow btn-primary btn btn-secondary switch-theme-class" data-class="body-tabs-line">
                                                Line
                                            </button>
                                            <button type="button" class="btn-wide btn-shadow btn-primary active btn btn-secondary switch-theme-class" data-class="body-tabs-shadow">
                                                Shadow
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>        <div class="app-main">
            
                <div class="app-sidebar sidebar-shadow">
                                <!-- ======= MENU========================  -->

                    <?php include("./menu/menu_$rol_usuario.php"); ?>          
                
                       
                 
                </div>    
                <div class="app-main__outer">
                    <div class="app-main__inner">
                        <div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-date icon-gradient bg-amy-crisp">
                                        </i>
                                    </div>
                                    <div>Total visitas tecnicas
                                        <div class="page-title-subheading">Visitas tecnicas del mes en curso y anual.
                                        </div>
                                    </div>
                                </div>
                                <div class="page-title-actions">
                                    <button type="button" data-toggle="tooltip" title="Example Tooltip" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark">
                                        <i class="fa fa-star"></i>
                                    </button>
                                    <div class="d-inline-block dropdown">
                                        <button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn-shadow dropdown-toggle btn btn-info">
                                            <span class="btn-icon-wrapper pr-2 opacity-7">
                                                <i class="fa fa-business-time fa-w-20"></i>
                                            </span>
                                            Buttons
                                        </button>
                                        <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                            <ul class="nav flex-column">
                                                <li class="nav-item">
                                                    <a href="javascript:void(0);" class="nav-link">
                                                        <i class="nav-link-icon lnr-inbox"></i>
                                                        <span>
                                                            Inbox
                                                        </span>
                                                        <div class="ml-auto badge badge-pill badge-secondary">86</div>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="javascript:void(0);" class="nav-link">
                                                        <i class="nav-link-icon lnr-book"></i>
                                                        <span>
                                                            Book
                                                        </span>
                                                        <div class="ml-auto badge badge-pill badge-danger">5</div>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="javascript:void(0);" class="nav-link">
                                                        <i class="nav-link-icon lnr-picture"></i>
                                                        <span>
                                                            Picture
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a disabled href="javascript:void(0);" class="nav-link disabled">
                                                        <i class="nav-link-icon lnr-file-empty"></i>
                                                        <span>
                                                            File Disabled
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>    </div>
                        </div>            <ul class="body-tabs body-tabs-layout tabs-animated body-tabs-animated nav">
                            <li class="nav-item">
                                <a role="tab" class="nav-link active" id="tab-0" data-toggle="tab" href="#tab-content-0">
                                    <span>Visitas Mes en curso</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a role="tab" class="nav-link" id="tab-1" data-toggle="tab" href="#tab-content-1">
                                    <span>Visitas Anual</span>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane tabs-animation fade show active" id="tab-content-0" role="tabpanel">
                                
                                
    <div class="row">
        <div class="col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-body">
                    <!-- --- FILTROS --- -->
                                        <?php
                        // Variables de filtro
                        $mes_actual = date('n');
                        $anio_actual = date('Y');
                        
                        $mes_seleccionado = isset($_GET['mes']) ? (int)$_GET['mes'] : $mes_actual;
                        $anio_seleccionado = isset($_GET['anio']) ? (int)$_GET['anio'] : $anio_actual;
                        $tipo_soporte = isset($_GET['tipo_soporte']) ? $_GET['tipo_soporte'] : '';
                        
                        // Meses en español
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
                            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
                            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        ?>
                        
                        <div class="filter-box">
                            <form method="get" class="form-inline">
                                <input type="hidden" name="tab" value="tab-content-0">
                        
                                <!-- MES -->
                                <div class="form-group mr-3">
                                    <label for="mes" class="mr-2">Mes:</label>
                                    <select name="mes" id="mes" class="form-control">
                                        <?php foreach ($meses as $numero => $nombre): ?>
                                            <option value="<?= $numero ?>" <?= $numero == $mes_seleccionado ? 'selected' : '' ?>>
                                                <?= $nombre ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                        
                                <!-- AÑO -->
                                <div class="form-group mr-3">
                                    <label for="anio" class="mr-2">Año:</label>
                                    <select name="anio" id="anio" class="form-control">
                                        <?php for ($i = $anio_actual; $i >= 2020; $i--): ?>
                                            <option value="<?= $i ?>" <?= $i == $anio_seleccionado ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                        
                                <!-- TIPO DE SOPORTE -->
                                <div class="form-group mr-3">
                                    <label for="tipo_soporte" class="mr-2">Tipo:</label>
                                    <select name="tipo_soporte" id="tipo_soporte" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="REMOTO" <?= $tipo_soporte === 'REMOTO' ? 'selected' : '' ?>>Remoto</option>
                                        <option value="PRESENCIAL" <?= $tipo_soporte === 'PRESENCIAL' ? 'selected' : '' ?>>Presencial</option>
                                    </select>
                                </div>
                        
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                        
                                <!-- Exportar -->
                                <a href="?export=excel&mes=<?= $mes_seleccionado ?>&anio=<?= $anio_seleccionado ?>&tipo_soporte=<?= $tipo_soporte ?>" 
                                   class="btn btn-success export-btn">
                                    <i class="fa fa-file-excel"></i> Excel
                                </a>
                        
                                <!--<a href="?export=pdf&mes=<?= $mes_seleccionado ?>&anio=<?= $anio_seleccionado ?>&tipo_soporte=<?= $tipo_soporte ?>" -->
                                <!--   class="btn btn-danger export-btn">-->
                                <!--    <i class="fa fa-file-pdf"></i> PDF-->
                                <!--</a>-->
                            </form>
                        </div>


                    <!-- --- TABLA DE HORAS POR DÍA --- -->
                    <div class="main-card mb-3 card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="header-icon lnr-clock icon-gradient bg-plum-plate"></i>
                                Horas por día - <?= date('F Y', mktime(0, 0, 0, $mes_seleccionado, 1, $anio_seleccionado)) ?>
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Día</th>
                                            <th>Fecha</th>
                                            <th>Tipo Soporte</th>
                                            <th>Horas Trabajadas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        <?php

                                       $conexion->query("SET lc_time_names = 'es_ES'");
                                     $sql_horas_dia = "SELECT 
                                                        DAYNAME(FECHA_SOPORTE) AS dia_semana,
                                                        DAY(FECHA_SOPORTE) AS dia_mes,
                                                        C.SOPORTE AS tipo_soporte,
                                                        ROUND(SUM(TIMESTAMPDIFF(MINUTE, HORA_INICIO, HORA_FIN)) / 60, 2) AS horas
                                                    FROM 
                                                        COTI_CALENDARIO A
                                                    LEFT JOIN 
                                                        COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
                                                    WHERE 
                                                        A.ESTADO_SOPORTE = 'Confirmada'
                                                        AND MONTH(A.FECHA_SOPORTE) = ?
                                                        AND YEAR(A.FECHA_SOPORTE) = ?";
                                        if ($tipo_soporte) {
                                            $sql_horas_dia .= " AND C.SOPORTE = ?";
                                        }
                                        
                                        $sql_horas_dia .= " GROUP BY A.FECHA_SOPORTE, C.SOPORTE ORDER BY A.FECHA_SOPORTE";
                                        
                                        $stmt = $conexion->prepare($sql_horas_dia);
                                        
                                        if ($tipo_soporte) {
                                            $stmt->bind_param("iis", $mes_seleccionado, $anio_seleccionado, $tipo_soporte);
                                        } else {
                                            $stmt->bind_param("ii", $mes_seleccionado, $anio_seleccionado);
                                        }
                                        
                                        $stmt->execute();
                                        $result_horas = $stmt->get_result();
                                        $total_horas = 0;
                                        while ($row = $result_horas->fetch_assoc()): 
                                            $total_horas += $row['horas'];
                                        ?>
                                            <tr>
                                                <td><?= ucfirst(strtolower($row['dia_semana'])) ?></td>
                                                <td><?= $row['dia_mes'] ?></td>
                                                <td><?= $row['tipo_soporte'] ?></td>
                                                <td><strong><?php 
                                                            $horas_decimal = $row['horas'];
                                                            $horas = floor($horas_decimal);
                                                            $minutos = round(($horas_decimal - $horas) * 60);
                                                            echo "$horas hrs $minutos min";
                                                            ?></strong></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot>
    <tr class="table-primary">
        <td colspan="3" class="text-right"><strong>Total Mensual:</strong></td>
        <td>
            <strong>
                <?php
                // Convertir $total_horas (ej: 45.75) a "45 hrs 45 min"
                $horas_total = floor($total_horas); // Parte entera (horas)
                $minutos_total = round(($total_horas - $horas_total) * 60); // Parte decimal a minutos
                echo "$horas_total hrs $minutos_total min";
                ?>
            </strong>
        </td>
    </tr>
    <tr class="table-warning">
        <td colspan="3" class="text-right"><strong>Disponibles:</strong></td>
        <td>
            <strong>
                <?php
                $horas_disponibles = 48 - $total_horas;
                $horas_disp = floor($horas_disponibles);
                $minutos_disp = round(($horas_disponibles - $horas_disp) * 60);
                // Evitar minutos negativos (ej: si total_horas > 48)
                if ($minutos_disp < 0) {
                    $minutos_disp = 0;
                }
                echo "$horas_disp hrs $minutos_disp min";
                ?>
            </strong>
        </td>
    </tr>
</tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
                            <div class="tab-pane tabs-animation fade" id="tab-content-1" role="tabpanel">
                                <div class="row">
                                <?php
                                for ($m = 1; $m <= 12; $m++):
                                    $mes_anual  = $m;
                                    $anio_anual = $anio_actual;

                                    $sql_horas_anual = "SELECT
                                            DAYNAME(FECHA_SOPORTE) AS dia_semana,
                                            DAY(FECHA_SOPORTE) AS dia_mes,
                                            C.SOPORTE AS tipo_soporte,
                                            ROUND(SUM(TIMESTAMPDIFF(MINUTE, HORA_INICIO, HORA_FIN)) / 60, 2) AS horas
                                        FROM
                                            COTI_CALENDARIO A
                                        LEFT JOIN
                                            COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
                                        WHERE
                                            A.ESTADO_SOPORTE = 'Confirmada'
                                            AND MONTH(A.FECHA_SOPORTE) = ?
                                            AND YEAR(A.FECHA_SOPORTE) = ?";

                                    if ($tipo_soporte) {
                                        $sql_horas_anual .= " AND C.SOPORTE = ?";
                                    }

                                    $sql_horas_anual .= " GROUP BY A.FECHA_SOPORTE, C.SOPORTE ORDER BY A.FECHA_SOPORTE";

                                    $stmt_anual = $conexion->prepare($sql_horas_anual);

                                    if ($tipo_soporte) {
                                        $stmt_anual->bind_param("iis", $mes_anual, $anio_anual, $tipo_soporte);
                                    } else {
                                        $stmt_anual->bind_param("ii", $mes_anual, $anio_anual);
                                    }

                                    $stmt_anual->execute();
                                    $result_anual = $stmt_anual->get_result();
                                    $total_horas_anual = 0;
                                ?>
                                    <div class="col-md-6">
                                        <div class="main-card mb-3 card">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <i class="header-icon lnr-clock icon-gradient bg-plum-plate"></i>
                                                    Horas por día - <?= $meses[$mes_anual] ?> <?= $anio_anual ?>
                                                </h5>
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-striped table-bordered">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th>Día</th>
                                                                <th>Fecha</th>
                                                                <th>Tipo Soporte</th>
                                                                <th>Horas Trabajadas</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php while ($row = $result_anual->fetch_assoc()):
                                                                $total_horas_anual += $row['horas'];
                                                            ?>
                                                                <tr>
                                                                    <td><?= ucfirst(strtolower($row['dia_semana'])) ?></td>
                                                                    <td><?= $row['dia_mes'] ?></td>
                                                                    <td><?= $row['tipo_soporte'] ?></td>
                                                                    <td><strong><?php
                                                                                $horas_decimal = $row['horas'];
                                                                                $horas = floor($horas_decimal);
                                                                                $minutos = round(($horas_decimal - $horas) * 60);
                                                                                echo "$horas hrs $minutos min";
                                                                                ?></strong></td>
                                                                </tr>
                                                            <?php endwhile; ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="table-primary">
                                                                <td colspan="3" class="text-right"><strong>Total Mensual:</strong></td>
                                                                <td>
                                                                    <strong>
                                                                        <?php
                                                                        $horas_total_anual = floor($total_horas_anual);
                                                                        $minutos_total_anual = round(($total_horas_anual - $horas_total_anual) * 60);
                                                                        echo "$horas_total_anual hrs $minutos_total_anual min";
                                                                        ?>
                                                                    </strong>
                                                                </td>
                                                            </tr>
                                                            <tr class="table-warning">
                                                                <td colspan="3" class="text-right"><strong>Disponibles:</strong></td>
                                                                <td>
                                                                    <strong>
                                                                        <?php
                                                                        $horas_disponibles_anual = 48 - $total_horas_anual;
                                                                        $horas_disp_anual = floor($horas_disponibles_anual);
                                                                        $minutos_disp_anual = round(($horas_disponibles_anual - $horas_disp_anual) * 60);
                                                                        if ($minutos_disp_anual < 0) {
                                                                            $minutos_disp_anual = 0;
                                                                        }
                                                                        echo "$horas_disp_anual hrs $minutos_disp_anual min";
                                                                        ?>
                                                                    </strong>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                                </div>
                                </div>
                            </div>
                        </div>
            
                    </div>
                    <div class="app-wrapper-footer">
                        <div class="app-footer">
                            <div class="app-footer__inner">
                                <div class="app-footer-left">
                                    <ul class="nav">
                                        <li class="nav-item">
                                            <a href="javascript:void(0);" class="nav-link">
                                                Footer Link 1
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="javascript:void(0);" class="nav-link">
                                                Footer Link 2
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="app-footer-right">
                                    <ul class="nav">
                                        <li class="nav-item">
                                            <a href="javascript:void(0);" class="nav-link">
                                                Footer Link 3
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="javascript:void(0);" class="nav-link">
                                                <div class="badge badge-success mr-1 ml-0">
                                                    <small>NEW</small>
                                                </div>
                                                Footer Link 4
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>    </div>
        </div>
    </div>
<script type="text/javascript" src="./assets/scripts/main.js"></script></body>
</html>
<?php
ob_end_flush();
?>