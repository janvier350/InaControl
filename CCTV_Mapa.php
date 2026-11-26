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

$dvrs = [];
$q = $conexion->query("SELECT * FROM CCTV_DVR WHERE ESTADO = 'A' ORDER BY UBICACION, MARCA");
while ($d = mysqli_fetch_assoc($q)) {
    $d['CAMARAS'] = [];
    $dvrs[$d['ID_DVR']] = $d;
}

$independientes = [];
$qc = $conexion->query("SELECT * FROM CCTV_CAMARA WHERE ESTADO = 'A' ORDER BY UBICACION, MARCA");
while ($c = mysqli_fetch_assoc($qc)) {
    if ($c['ID_DVR'] && isset($dvrs[$c['ID_DVR']])) {
        $dvrs[$c['ID_DVR']]['CAMARAS'][] = $c;
    } else {
        $independientes[] = $c;
    }
}

$totalCamarasMapa = array_sum(array_map(fn($d) => count($d['CAMARAS']), $dvrs)) + count($independientes);

$elaboradoPor = $_SESSION["username"];
if (!empty($_SESSION['iduser'])) {
    $stmtU = $conexion->prepare("SELECT NOMBRES, APELLIDOS FROM ADM_USUARIO WHERE IDADM_USUARIO = ?");
    $stmtU->bind_param("i", $_SESSION['iduser']);
    $stmtU->execute();
    $rowU = $stmtU->get_result()->fetch_assoc();
    $stmtU->close();
    if ($rowU) {
        $elaboradoPor = trim($rowU['NOMBRES'] . ' ' . $rowU['APELLIDOS']);
    }
}
?>
<head>
    <meta charset="utf-8">
    <title>CCTV - Mapa de Conexiones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="./main.css" rel="stylesheet">
    <style>
        .dvr-card { border-left: 5px solid #0f3460; }
        .dvr-card .card-header { background: #eef2f9; font-weight: 600; }
        .cam-item {
            display: flex; align-items: center; gap: 10px; padding: 8px 10px;
            border: 1px solid #eee; border-radius: 8px; margin-bottom: 6px; background: #fafbfc;
        }
        .cam-dot { width: 10px; height: 10px; border-radius: 50%; background: #3ac47d; flex-shrink: 0; }
        @media print {
            .no-print, .app-sidebar, .app-header, .app-page-title { display: none !important; }
            .app-main__outer { padding-left: 0 !important; }
            .print-header { display: block !important; }
            body { background: #fff !important; }
        }
    </style>
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
                            <div class="page-title-icon"><i class="pe-7s-map icon-gradient bg-warm-flame"></i></div>
                            <div>
                                <div class="page-title-subheading text-black">Mapa de Conexiones CCTV</div>
                                <p class="text-dark">Qué cámaras están conectadas a cada DVR/NVR y en qué área.</p>
                            </div>
                            <div class="ms-auto d-flex gap-1 no-print">
                                <a class="btn btn-success" href="class/CCTV_Export_Mapa.php" title="Exportar Excel">
                                    <i class="bi bi-file-earmark-excel"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="window.print()" title="Exportar PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="print-header mb-3" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-2" style="border-bottom:3px solid #0f3460; padding-bottom:8px;">
                        <div>
                            <h4 class="mb-0">INASAR</h4>
                            <small class="text-muted">Mapa de Conexiones CCTV (DVR/NVR y cámaras conectadas)</small>
                        </div>
                        <div class="text-end small">
                            <strong>Fecha de emisión:</strong> <?php echo date('d/m/Y H:i'); ?>
                        </div>
                    </div>
                    <table style="width:100%; border-collapse:collapse; font-size:0.85rem; margin-bottom:10px;">
                        <tr>
                            <td style="border:1px solid #000; padding:4px 8px; font-weight:bold; width:20%;">Elaborado por:</td>
                            <td style="border:1px solid #000; padding:4px 8px; width:30%;"><?php echo htmlspecialchars($elaboradoPor); ?></td>
                            <td style="border:1px solid #000; padding:4px 8px; font-weight:bold; width:20%;">Total de cámaras:</td>
                            <td style="border:1px solid #000; padding:4px 8px; width:30%;"><?php echo $totalCamarasMapa; ?></td>
                        </tr>
                        <tr>
                            <td style="border:1px solid #000; padding:4px 8px; font-weight:bold;">Departamento:</td>
                            <td style="border:1px solid #000; padding:4px 8px;">Sistemas / Soporte Técnico</td>
                            <td style="border:1px solid #000; padding:4px 8px; font-weight:bold;">Documento:</td>
                            <td style="border:1px solid #000; padding:4px 8px;">Mapa de Conexiones CCTV</td>
                        </tr>
                    </table>
                </div>

                <div class="row g-3 align-items-start">
                    <?php foreach ($dvrs as $d): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card dvr-card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-hdd-network me-1"></i> <?php echo htmlspecialchars($d['TIPO'].' - '.$d['MARCA'].' '.$d['MODELO']); ?></span>
                                <span class="badge bg-primary"><?php echo count($d['CAMARAS']); ?>/<?php echo $d['CANALES'] ?: '?'; ?> canales</span>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($d['UBICACION'] ?: 'Sin ubicación'); ?>
                                    <?php if ($d['IP']): ?> &nbsp;|&nbsp; <i class="bi bi-hdd"></i> <?php echo htmlspecialchars($d['IP']); ?><?php endif; ?>
                                </p>
                                <div class="cam-list">
                                <?php if (empty($d['CAMARAS'])): ?>
                                    <p class="text-muted small">Sin cámaras conectadas.</p>
                                <?php else: foreach ($d['CAMARAS'] as $cam): ?>
                                    <div class="cam-item">
                                        <span class="cam-dot"></span>
                                        <div>
                                            <div class="small fw-bold"><?php echo htmlspecialchars($cam['MARCA'].' '.$cam['MODELO']); ?>
                                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($cam['TIPO_CAMARA']); ?></span>
                                                <?php if (!empty($cam['COMPARTIDA_HIKCONNECT'])): ?>
                                                    <i class="bi bi-share-fill text-success" title="Compartida vía HikConnect con proveedor de seguridad"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($cam['UBICACION'] ?: 'Sin ubicación'); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (!empty($independientes)): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm" style="border-left:5px solid #6c757d;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-camera-video me-1"></i> Cámaras independientes</span>
                                <span class="badge bg-secondary"><?php echo count($independientes); ?></span>
                            </div>
                            <div class="card-body">
                                <div class="cam-list">
                                <?php foreach ($independientes as $cam): ?>
                                    <div class="cam-item">
                                        <span class="cam-dot" style="background:#6c757d;"></span>
                                        <div>
                                            <div class="small fw-bold"><?php echo htmlspecialchars($cam['MARCA'].' '.$cam['MODELO']); ?>
                                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($cam['TIPO_CAMARA']); ?></span>
                                                <?php if (!empty($cam['COMPARTIDA_HIKCONNECT'])): ?>
                                                    <i class="bi bi-share-fill text-success" title="Compartida vía HikConnect con proveedor de seguridad"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($cam['UBICACION'] ?: 'Sin ubicación'); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($dvrs) && empty($independientes)): ?>
                        <div class="col-12"><div class="alert alert-info">Aún no hay DVR/NVR ni cámaras registradas.</div></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="app-wrapper-footer">
                <div class="app-footer">
                    <div class="app-footer__inner">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/jquery.min.js"></script>
<script type="text/javascript" src="./assets/scripts/main.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
