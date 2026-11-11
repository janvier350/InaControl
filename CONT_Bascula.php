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

$estadoFiltro = $_GET['estado'] ?? '';
$sqlSec = "SELECT * FROM CONT_BASCULA_SECUENCIA";
if ($estadoFiltro !== '') {
    $sqlSec .= " WHERE ESTADO = '" . mysqli_real_escape_string($conexion, $estadoFiltro) . "'";
}
$sqlSec .= " ORDER BY NUMERO ASC";
$resSec = $conexion->query($sqlSec);
$secuenciasJs = [];
$filas = [];
while ($s = mysqli_fetch_assoc($resSec)) {
    $filas[] = $s;
    $secuenciasJs[$s['ID_SECUENCIA']] = $s;
}
?>
<head>
    <meta charset="utf-8">
    <title>Secuencia de Báscula</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="./main.css" rel="stylesheet">
    <style>
        .btn-num { width: 68px; margin: 2px; font-size: 0.8rem; }
        .btn-num.estado-pendiente { background-color: #f4f4f4; border-color: #ddd; color: #666; }
        .btn-num.estado-usado { background-color: #28a745; border-color: #28a745; color: #fff; }
        .btn-num.estado-anulado { background-color: #dc3545; border-color: #dc3545; color: #fff; }
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
                            <div class="page-title-icon"><i class="pe-7s-note2 icon-gradient bg-warm-flame"></i></div>
                            <div>
                                <div class="page-title-subheading text-black">Secuencia de Báscula</div>
                                <p class="text-dark">Control de números de comprobante de báscula.</p>
                            </div>
                            <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#modalRango">
                                <i class="bi bi-plus-lg"></i> Cargar Rango
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">Operación realizada correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">Error: <?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3 gap-2 flex-wrap">
                            <span class="badge estado-pendiente border">. Pendiente</span>
                            <span class="badge estado-usado">✓ Usado</span>
                            <span class="badge estado-anulado">A Anulado</span>
                            <form method="GET" class="ms-auto">
                                <select class="form-select form-select-sm" name="estado" onchange="this.form.submit()">
                                    <option value="">Todos los estados</option>
                                    <option value="PENDIENTE" <?php echo $estadoFiltro === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="USADO" <?php echo $estadoFiltro === 'USADO' ? 'selected' : ''; ?>>Usado</option>
                                    <option value="ANULADO" <?php echo $estadoFiltro === 'ANULADO' ? 'selected' : ''; ?>>Anulado</option>
                                </select>
                            </form>
                        </div>
                        <?php if (empty($filas)): ?>
                            <p class="text-muted">No hay secuencias cargadas. Usa "Cargar Rango" para comenzar.</p>
                        <?php else: ?>
                            <div>
                                <?php foreach ($filas as $s):
                                    $clase = $s['ESTADO'] === 'USADO' ? 'estado-usado' : ($s['ESTADO'] === 'ANULADO' ? 'estado-anulado' : 'estado-pendiente');
                                ?>
                                    <button type="button" class="btn btn-num <?php echo $clase; ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalSecuencia"
                                        onclick='cargarSecuencia(<?php echo json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                        <?php echo $s['NUMERO']; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cargar Rango -->
<div class="modal fade" id="modalRango" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Cargar Rango de Secuencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/Insert_RangoBascula.php">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Desde</label>
                            <input type="number" class="form-control" name="desde" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Hasta</label>
                            <input type="number" class="form-control" name="hasta" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Observación (opcional)</label>
                        <input type="text" class="form-control" name="observacion" placeholder="Ej. talonario nuevo">
                    </div>
                    <div class="text-end"><button type="submit" class="btn btn-success">Guardar</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Secuencia -->
<div class="modal fade" id="modalSecuencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Secuencia No. <span id="secNumero"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/Editar_Secuencia.php">
                    <input type="hidden" name="idSecuencia" id="secId">
                    <div class="mb-2">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado" id="secEstado">
                            <option value="PENDIENTE">Pendiente</option>
                            <option value="USADO">Usado</option>
                            <option value="ANULADO">Anulado</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha" id="secFecha">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Proveedor / Cliente</label>
                        <input type="text" class="form-control" name="proveedorCliente" id="secProveedorCliente">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Peso Bruto</label>
                            <input type="number" step="0.01" class="form-control" name="pesoBruto" id="secPesoBruto" oninput="calcularNeto()">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Tara</label>
                            <input type="number" step="0.01" class="form-control" name="tara" id="secTara" oninput="calcularNeto()">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Peso Neto</label>
                            <input type="text" class="form-control" id="secPesoNeto" readonly>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Observación</label>
                        <textarea class="form-control" name="observacion" id="secObservacion" rows="2"></textarea>
                    </div>
                    <div class="text-end"><button type="submit" class="btn btn-primary">Guardar cambios</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cargarSecuencia(s) {
    document.getElementById('secId').value = s.ID_SECUENCIA;
    document.getElementById('secNumero').textContent = s.NUMERO;
    document.getElementById('secEstado').value = s.ESTADO || 'PENDIENTE';
    document.getElementById('secFecha').value = s.FECHA || '';
    document.getElementById('secProveedorCliente').value = s.PROVEEDOR_CLIENTE || '';
    document.getElementById('secPesoBruto').value = s.PESO_BRUTO || '';
    document.getElementById('secTara').value = s.TARA || '';
    document.getElementById('secObservacion').value = s.OBSERVACION || '';
    calcularNeto();
}
function calcularNeto() {
    var bruto = parseFloat(document.getElementById('secPesoBruto').value) || 0;
    var tara = parseFloat(document.getElementById('secTara').value) || 0;
    document.getElementById('secPesoNeto').value = (bruto - tara).toFixed(2);
}
</script>
<script src="js/jquery.min.js"></script>
<script type="text/javascript" src="./assets/scripts/main.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
