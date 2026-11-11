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

$secuenciasJs = [];
$qSec = $conexion->query("SELECT ID_SECUENCIA, NUMERO FROM CONT_BASCULA_SECUENCIA WHERE ESTADO = 'PENDIENTE' ORDER BY NUMERO ASC");
while ($s = mysqli_fetch_assoc($qSec)) {
    $secuenciasJs[] = ['value' => $s['ID_SECUENCIA'], 'label' => $s['NUMERO']];
}

$pendientes = [];
$qPend = $conexion->query("SELECT * FROM CONT_COMPRA_BOLETA WHERE ID_LIQUIDACION IS NULL AND ESTADO = 'A' ORDER BY PROVEEDOR, FECHA");
while ($b = mysqli_fetch_assoc($qPend)) { $pendientes[] = $b; }

$proveedoresJs = [];
$qProv = $conexion->query("SELECT DISTINCT PROVEEDOR FROM CONT_COMPRA_BOLETA WHERE ID_LIQUIDACION IS NULL AND ESTADO = 'A' ORDER BY PROVEEDOR");
while ($pv = mysqli_fetch_assoc($qProv)) { $proveedoresJs[] = $pv['PROVEEDOR']; }

$anios = [];
$qA = $conexion->query("SELECT DISTINCT YEAR(FECHA) AS ANIO FROM CONT_COMPRA_LIQUIDACION ORDER BY ANIO DESC");
while ($a = mysqli_fetch_assoc($qA)) { $anios[] = $a['ANIO']; }
if (empty($anios)) { $anios[] = (int)date('Y'); }

$anioFiltro = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
$resLiq = $conexion->prepare("SELECT * FROM CONT_COMPRA_LIQUIDACION WHERE YEAR(FECHA) = ? AND ESTADO = 'A' ORDER BY FECHA DESC");
$resLiq->bind_param("i", $anioFiltro);
$resLiq->execute();
$liquidaciones = $resLiq->get_result();
?>
<head>
    <meta charset="utf-8">
    <title>Compras</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
                        <div class="page-title-icon"><i class="pe-7s-car icon-gradient bg-warm-flame"></i></div>
                        <div>
                            <div class="page-title-subheading text-black">Compras</div>
                            <p class="text-dark">Boletas de báscula y liquidación de facturas a proveedores.</p>
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

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-boleta">Registrar Boleta</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-liquidar">Liquidar Proveedor</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-liquidaciones">Liquidaciones</a></li>
                </ul>
                <div class="tab-content pt-3">

                    <!-- Registrar Boleta -->
                    <div class="tab-pane fade show active" id="tab-boleta">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <form method="POST" action="class/Insert_Boleta.php">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Fecha</label>
                                            <input type="date" class="form-control" name="fecha" id="boletaFecha" required>
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Proveedor</label>
                                            <input type="text" class="form-control" name="proveedor" required>
                                        </div>
                                        <div class="col-md-4 mb-3 position-relative">
                                            <label class="form-label">No. Báscula (secuencia pendiente)</label>
                                            <input type="text" class="form-control" id="buscarSecuencia" placeholder="Escriba el número..." autocomplete="off">
                                            <input type="hidden" name="idSecuencia" id="boletaIdSecuencia">
                                            <div id="listaSecuencias" class="list-group shadow-sm" style="display:none; position:absolute; z-index:1060; width:100%; max-height:200px; overflow-y:auto;"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Peso Bruto (kg)</label>
                                            <input type="number" step="0.01" class="form-control" name="pesoBruto" id="boletaPesoBruto" oninput="calcularBoleta()" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Tara (kg)</label>
                                            <input type="number" step="0.01" class="form-control" name="tara" id="boletaTara" oninput="calcularBoleta()" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">% Calificación (merma)</label>
                                            <input type="number" step="0.01" class="form-control" name="porcCalificacion" id="boletaPorc" placeholder="Ej. 0.11 = 11%" oninput="calcularBoleta()" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">QQ Netos (calculado)</label>
                                            <input type="text" class="form-control" id="boletaQqNeto" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Observación</label>
                                        <textarea class="form-control" name="observacion" rows="2"></textarea>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar Boleta</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Liquidar Proveedor -->
                    <div class="tab-pane fade" id="tab-liquidar">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <?php if (empty($pendientes)): ?>
                                    <p class="text-muted">No hay boletas pendientes de liquidar.</p>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <label class="form-label">Filtrar por proveedor</label>
                                        <select class="form-select" id="filtroProveedorLiq" onchange="filtrarProveedor()">
                                            <option value="">-- Seleccione un proveedor --</option>
                                            <?php foreach ($proveedoresJs as $pv): ?>
                                                <option value="<?php echo htmlspecialchars($pv); ?>"><?php echo htmlspecialchars($pv); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <form method="POST" action="class/Insert_Liquidacion.php" onsubmit="return validarLiquidacion();">
                                        <input type="hidden" name="proveedor" id="liqProveedor">
                                        <div class="table-responsive mb-3">
                                            <table class="table table-sm table-hover">
                                                <thead><tr><th></th><th>FECHA</th><th>PROVEEDOR</th><th>N° BÁSCULA</th><th>PESO NETO</th><th>QQ NETO</th></tr></thead>
                                                <tbody id="tbodyPendientes">
                                                <?php foreach ($pendientes as $b): ?>
                                                    <tr class="fila-pendiente" data-proveedor="<?php echo htmlspecialchars($b['PROVEEDOR']); ?>" style="display:none;">
                                                        <td><input type="checkbox" name="idsBoletas[]" value="<?php echo $b['ID_BOLETA']; ?>" class="chk-boleta" data-qq="<?php echo $b['QQ_NETO']; ?>" onchange="recalcularTotal()"></td>
                                                        <td><?php echo date('d/m/Y', strtotime($b['FECHA'])); ?></td>
                                                        <td><?php echo htmlspecialchars($b['PROVEEDOR']); ?></td>
                                                        <td><?php echo htmlspecialchars($b['NUMERO_BASCULA'] ?: '-'); ?></td>
                                                        <td><?php echo number_format($b['PESO_NETO'], 2); ?></td>
                                                        <td><?php echo number_format($b['QQ_NETO'], 4); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Fecha de liquidación</label>
                                                <input type="date" class="form-control" name="fecha" id="liqFecha" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Factura N°</label>
                                                <input type="text" class="form-control" name="facturaNo">
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">QQ Total</label>
                                                <input type="text" class="form-control" id="liqQqTotal" readonly>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Precio por QQ</label>
                                                <input type="number" step="0.0001" class="form-control" name="precio" id="liqPrecio" oninput="recalcularTotal()" required>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Total</label>
                                                <input type="text" class="form-control" id="liqTotal" readonly>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Observación</label>
                                            <textarea class="form-control" name="observacion" rows="2"></textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Generar Liquidación</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Liquidaciones -->
                    <div class="tab-pane fade" id="tab-liquidaciones">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <form method="GET" class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <select class="form-select" name="anio" onchange="this.form.submit()">
                                            <?php foreach ($anios as $a): ?>
                                                <option value="<?php echo $a; ?>" <?php echo $a == $anioFiltro ? 'selected' : ''; ?>><?php echo $a; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead><tr><th>FECHA</th><th>PROVEEDOR</th><th>FACTURA</th><th>QQ TOTAL</th><th>PRECIO</th><th>TOTAL</th></tr></thead>
                                        <tbody>
                                        <?php if (mysqli_num_rows($liquidaciones) === 0): ?>
                                            <tr><td colspan="6" class="text-center text-muted">Sin liquidaciones para este año.</td></tr>
                                        <?php else: while ($l = mysqli_fetch_assoc($liquidaciones)): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($l['FECHA'])); ?></td>
                                                <td><?php echo htmlspecialchars($l['PROVEEDOR']); ?></td>
                                                <td><?php echo htmlspecialchars($l['FACTURA_NO'] ?: '-'); ?></td>
                                                <td><?php echo number_format($l['QQ_TOTAL'], 4); ?></td>
                                                <td>$<?php echo number_format($l['PRECIO'], 4); ?></td>
                                                <td>$<?php echo number_format($l['TOTAL'], 2); ?></td>
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
var secuenciasPendientes = <?php echo json_encode($secuenciasJs, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

(function() {
    var input = document.getElementById('buscarSecuencia');
    var hidden = document.getElementById('boletaIdSecuencia');
    var lista = document.getElementById('listaSecuencias');

    function renderResultados(texto) {
        var t = texto.trim();
        var resultados = !t ? secuenciasPendientes.slice(0, 20)
            : secuenciasPendientes.filter(function(p) { return String(p.label).indexOf(t) !== -1; }).slice(0, 20);

        lista.innerHTML = !resultados.length
            ? '<div class="list-group-item text-muted small">Sin resultados.</div>'
            : resultados.map(function(p) {
                return '<button type="button" class="list-group-item list-group-item-action" data-value="' + p.value + '">' + p.label + '</button>';
            }).join('');
        lista.style.display = 'block';
    }

    input.addEventListener('focus', function() { renderResultados(input.value); });
    input.addEventListener('input', function() {
        hidden.value = '';
        renderResultados(input.value);
    });
    lista.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-value]');
        if (!btn) return;
        hidden.value = btn.dataset.value;
        input.value = btn.textContent;
        lista.style.display = 'none';
    });
    document.addEventListener('click', function(e) {
        if (e.target !== input && !lista.contains(e.target)) { lista.style.display = 'none'; }
    });

    document.getElementById('boletaFecha').value = new Date().toISOString().substring(0, 10);
    var liqFecha = document.getElementById('liqFecha');
    if (liqFecha) { liqFecha.value = new Date().toISOString().substring(0, 10); }
})();

function calcularBoleta() {
    var bruto = parseFloat(document.getElementById('boletaPesoBruto').value) || 0;
    var tara = parseFloat(document.getElementById('boletaTara').value) || 0;
    var porc = parseFloat(document.getElementById('boletaPorc').value) || 0;
    var neto = bruto - tara;
    var qq = neto * 0.011;
    var qqNeto = qq - (qq * porc);
    document.getElementById('boletaQqNeto').value = qqNeto.toFixed(4);
}

function filtrarProveedor() {
    var proveedor = document.getElementById('filtroProveedorLiq').value;
    document.getElementById('liqProveedor').value = proveedor;
    document.querySelectorAll('.fila-pendiente').forEach(function(fila) {
        var coincide = proveedor && fila.dataset.proveedor === proveedor;
        fila.style.display = coincide ? '' : 'none';
        if (!coincide) { fila.querySelector('.chk-boleta').checked = false; }
    });
    recalcularTotal();
}

function recalcularTotal() {
    var qqTotal = 0;
    document.querySelectorAll('.chk-boleta:checked').forEach(function(chk) {
        qqTotal += parseFloat(chk.dataset.qq) || 0;
    });
    var precio = parseFloat(document.getElementById('liqPrecio').value) || 0;
    document.getElementById('liqQqTotal').value = qqTotal.toFixed(4);
    document.getElementById('liqTotal').value = '$' + (qqTotal * precio).toFixed(2);
}

function validarLiquidacion() {
    if (!document.getElementById('liqProveedor').value) {
        alert('Seleccione un proveedor.');
        return false;
    }
    if (!document.querySelectorAll('.chk-boleta:checked').length) {
        alert('Seleccione al menos una boleta.');
        return false;
    }
    return true;
}
</script>
<script src="js/jquery.min.js"></script>
<script type="text/javascript" src="./assets/scripts/main.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
