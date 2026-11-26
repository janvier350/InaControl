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

$productosJs = [];
$qProd = $conexion->query("SELECT ID_PRODUCTO, CODIGO, NOMBRE FROM CONT_PRODUCTO WHERE ESTADO = 'A' ORDER BY NOMBRE");
while ($p = mysqli_fetch_assoc($qProd)) {
    $label = $p['CODIGO'] ? $p['NOMBRE'] . ' (' . $p['CODIGO'] . ')' : $p['NOMBRE'];
    $productosJs[] = ['value' => $p['ID_PRODUCTO'], 'label' => $label];
}

$clientesJs = [];
$qCli = $conexion->query("SELECT NOMBRE FROM CONT_CLIENTE WHERE ESTADO = 'A' ORDER BY NOMBRE");
while ($c = mysqli_fetch_assoc($qCli)) {
    $clientesJs[] = $c['NOMBRE'];
}
?>
<head>
    <meta charset="utf-8">
    <title>Ventas</title>
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
                        <div class="page-title-heading">
                            <div class="page-title-icon"><i class="pe-7s-cash icon-gradient bg-warm-flame"></i></div>
                            <div>
                                <div class="page-title-subheading text-black">Ventas</div>
                                <p class="text-dark">Registro de ventas con y sin factura.</p>
                            </div>
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

                <?php $verListado = isset($_GET['anio']) || isset($_GET['mes']) || isset($_GET['tipo']); ?>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link <?php echo $verListado ? '' : 'active'; ?>" data-bs-toggle="tab" href="#tab-registrar">Registrar</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $verListado ? 'active' : ''; ?>" data-bs-toggle="tab" href="#tab-listado">Listado</a></li>
                </ul>
                <div class="tab-content pt-3">
                    <!-- Registrar -->
                    <div class="tab-pane fade <?php echo $verListado ? '' : 'show active'; ?>" id="tab-registrar">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <form method="POST" action="class/Insert_Venta.php" onsubmit="return validarVenta();">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Fecha</label>
                                            <input type="date" class="form-control" name="fecha" id="ventaFecha" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Tipo de venta</label>
                                            <select class="form-select" name="tipo" required>
                                                <option value="CON_FACTURA">Con factura</option>
                                                <option value="SIN_FACTURA">Sin factura</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3 position-relative">
                                            <label class="form-label">Cliente</label>
                                            <input type="text" class="form-control" name="cliente" id="buscarClienteVenta" placeholder="Escriba para buscar o agregar..." autocomplete="off">
                                            <div id="listaClientesVenta" class="list-group shadow-sm" style="display:none; position:absolute; z-index:1060; width:100%; max-height:220px; overflow-y:auto;"></div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">N° Factura <span class="text-muted small">(solo secuencial)</span></label>
                                            <input type="text" class="form-control" name="facturaNo" placeholder="Ej. 001234">
                                        </div>
                                    </div>
                                    <div class="row position-relative">
                                        <div class="col-md-6 mb-3 position-relative">
                                            <label class="form-label">Producto</label>
                                            <input type="text" class="form-control" id="buscarProductoVenta" placeholder="Escriba para buscar..." autocomplete="off" required>
                                            <input type="hidden" name="idProducto" id="ventaIdProducto">
                                            <div id="listaProductosVenta" class="list-group shadow-sm" style="display:none; position:absolute; z-index:1060; width:100%; max-height:220px; overflow-y:auto;"></div>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="form-label">QQ</label>
                                            <input type="number" step="0.01" class="form-control" name="qq" id="ventaQQ" oninput="calcularTotalVenta()" required>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="form-label">Precio</label>
                                            <input type="number" step="0.0001" class="form-control" name="precio" id="ventaPrecio" oninput="calcularTotalVenta()" required>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label class="form-label">Total</label>
                                            <input type="text" class="form-control" id="ventaTotalDisplay" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalRetencion" onclick="abrirModalRetencion()">
                                            <i class="bi bi-receipt-cutoff"></i> Calcular Retención
                                        </button>
                                        <span id="retencionResumen" class="small text-muted ms-2"></span>
                                        <input type="hidden" name="porcRetencion" id="ventaPorcRetencion" value="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Observación</label>
                                        <textarea class="form-control" name="observacion" rows="2"></textarea>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar Venta</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Listado -->
                    <div class="tab-pane fade <?php echo $verListado ? 'show active' : ''; ?>" id="tab-listado">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <?php
                                $anios = [];
                                $qA = $conexion->query("SELECT DISTINCT YEAR(FECHA) AS ANIO FROM CONT_VENTA ORDER BY ANIO DESC");
                                while ($a = mysqli_fetch_assoc($qA)) { $anios[] = $a['ANIO']; }

                                $anioFiltro = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
                                $mesFiltro = isset($_GET['mes']) ? (int)$_GET['mes'] : 0;
                                $tipoFiltro = $_GET['tipo'] ?? '';

                                $sqlV = "SELECT V.*, P.NOMBRE AS PRODUCTO_NOMBRE, P.CODIGO AS PRODUCTO_CODIGO
                                         FROM CONT_VENTA V INNER JOIN CONT_PRODUCTO P ON V.ID_PRODUCTO = P.ID_PRODUCTO
                                         WHERE V.ESTADO = 'A' AND YEAR(V.FECHA) = ?";
                                $types = "i"; $params = [$anioFiltro];
                                if ($mesFiltro) { $sqlV .= " AND MONTH(V.FECHA) = ?"; $types .= "i"; $params[] = $mesFiltro; }
                                if ($tipoFiltro) { $sqlV .= " AND V.TIPO = ?"; $types .= "s"; $params[] = $tipoFiltro; }
                                $sqlV .= " ORDER BY V.FECHA DESC";

                                $stmtV = mysqli_prepare($conexion, $sqlV);
                                mysqli_stmt_bind_param($stmtV, $types, ...$params);
                                mysqli_stmt_execute($stmtV);
                                $resV = mysqli_stmt_get_result($stmtV);
                                ?>
                                <form method="GET" class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <select class="form-select" name="anio" onchange="this.form.submit()">
                                            <?php foreach ($anios as $a): ?>
                                                <option value="<?php echo $a; ?>" <?php echo $a == $anioFiltro ? 'selected' : ''; ?>><?php echo $a; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="mes" onchange="this.form.submit()">
                                            <option value="0">Todos los meses</option>
                                            <?php
                                            $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                                            for ($m = 1; $m <= 12; $m++): ?>
                                                <option value="<?php echo $m; ?>" <?php echo $m == $mesFiltro ? 'selected' : ''; ?>><?php echo $meses[$m]; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="tipo" onchange="this.form.submit()">
                                            <option value="">Todos los tipos</option>
                                            <option value="CON_FACTURA" <?php echo $tipoFiltro === 'CON_FACTURA' ? 'selected' : ''; ?>>Con factura</option>
                                            <option value="SIN_FACTURA" <?php echo $tipoFiltro === 'SIN_FACTURA' ? 'selected' : ''; ?>>Sin factura</option>
                                        </select>
                                    </div>
                                </form>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead><tr><th>FECHA</th><th>PRODUCTO</th><th>CLIENTE</th><th>N° FACTURA</th><th>TIPO</th><th>QQ</th><th>PRECIO</th><th>TOTAL</th><th>RETENCIÓN</th><th>A COBRAR</th></tr></thead>
                                        <tbody>
                                        <?php if (mysqli_num_rows($resV) === 0): ?>
                                            <tr><td colspan="10" class="text-center text-muted">Sin ventas en este filtro.</td></tr>
                                        <?php else: while ($v = mysqli_fetch_assoc($resV)): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($v['FECHA'])); ?></td>
                                                <td><?php echo htmlspecialchars($v['PRODUCTO_NOMBRE']); ?></td>
                                                <td><?php echo htmlspecialchars($v['CLIENTE'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($v['FACTURA_NO'] ?: '-'); ?></td>
                                                <td><span class="badge <?php echo $v['TIPO'] === 'CON_FACTURA' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $v['TIPO'] === 'CON_FACTURA' ? 'Con Factura' : 'Sin Factura'; ?></span></td>
                                                <td><?php echo number_format($v['QQ'], 2); ?></td>
                                                <td>$<?php echo number_format($v['PRECIO'], 4); ?></td>
                                                <td>$<?php echo number_format($v['TOTAL'], 2); ?></td>
                                                <td><?php echo $v['VALOR_RETENCION'] > 0 ? '$'.number_format($v['VALOR_RETENCION'], 2).' ('.number_format($v['PORC_RETENCION']*100, 2).'%)' : '-'; ?></td>
                                                <td>$<?php echo number_format($v['TOTAL_A_COBRAR'] ?? $v['TOTAL'], 2); ?></td>
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

<!-- Modal Retención -->
<div class="modal fade" id="modalRetencion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Calcular Retención</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Base (Total de la venta)</label>
                    <input type="text" class="form-control" id="retBase" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label">% Retención</label>
                    <select class="form-select" id="retPorcentaje" onchange="calcularRetencion()">
                        <option value="0">0% (Nota de Venta)</option>
                        <option value="0.01">1%</option>
                        <option value="0.0175">1.75%</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Valor Retención</label>
                    <input type="text" class="form-control" id="retValor" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label">Valor a Cobrar</label>
                    <input type="text" class="form-control" id="retACobrar" readonly>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="aplicarRetencion()">Aplicar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Cliente (rápido, desde Ventas) -->
<div class="modal fade" id="modalNuevoClienteVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Nuevo Cliente</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/Insert_Cliente.php">
                    <input type="hidden" name="origen" value="ventas">
                    <div class="mb-2">
                        <label class="form-label">Nombre del cliente</label>
                        <input class="form-control" name="nombre" id="nuevoClienteNombre" required>
                    </div>
                    <div class="text-end"><button type="submit" class="btn btn-success">Guardar</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
var productosVenta = <?php echo json_encode($productosJs, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var clientesVenta = <?php echo json_encode($clientesJs, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

(function() {
    var input = document.getElementById('buscarClienteVenta');
    var lista = document.getElementById('listaClientesVenta');

    function renderResultados(texto) {
        var t = texto.trim().toLowerCase();
        var resultados = !t ? clientesVenta.slice(0, 20)
            : clientesVenta.filter(function(c) { return c.toLowerCase().indexOf(t) !== -1; }).slice(0, 20);

        lista.innerHTML = '';
        if (!resultados.length) {
            var vacio = document.createElement('div');
            vacio.className = 'list-group-item text-muted small';
            vacio.textContent = 'Sin resultados.';
            lista.appendChild(vacio);
        } else {
            resultados.forEach(function(c) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.dataset.value = c;
                btn.textContent = c;
                lista.appendChild(btn);
            });
        }
        var btnNuevo = document.createElement('button');
        btnNuevo.type = 'button';
        btnNuevo.className = 'list-group-item list-group-item-action text-success fw-bold';
        btnNuevo.innerHTML = '<i class="bi bi-plus-lg"></i> Agregar cliente nuevo' + (t ? ': "' + t + '"' : '');
        btnNuevo.dataset.nuevo = '1';
        lista.appendChild(btnNuevo);
        lista.style.display = 'block';
    }

    input.addEventListener('focus', function() { renderResultados(input.value); });
    input.addEventListener('input', function() { renderResultados(input.value); });
    lista.addEventListener('click', function(e) {
        var btnN = e.target.closest('[data-nuevo]');
        if (btnN) {
            document.getElementById('nuevoClienteNombre').value = input.value.trim();
            lista.style.display = 'none';
            new bootstrap.Modal(document.getElementById('modalNuevoClienteVenta')).show();
            return;
        }
        var btn = e.target.closest('[data-value]');
        if (!btn) return;
        input.value = btn.dataset.value;
        lista.style.display = 'none';
    });
    document.addEventListener('click', function(e) {
        if (e.target !== input && !lista.contains(e.target)) { lista.style.display = 'none'; }
    });
})();

(function() {
    var input = document.getElementById('buscarProductoVenta');
    var hidden = document.getElementById('ventaIdProducto');
    var lista = document.getElementById('listaProductosVenta');

    function renderResultados(texto) {
        var t = texto.trim().toLowerCase();
        var resultados = !t ? productosVenta.slice(0, 20)
            : productosVenta.filter(function(p) { return p.label.toLowerCase().indexOf(t) !== -1; }).slice(0, 20);

        if (!resultados.length) {
            lista.innerHTML = '<div class="list-group-item text-muted small">Sin resultados.</div>';
        } else {
            lista.innerHTML = resultados.map(function(p) {
                return '<button type="button" class="list-group-item list-group-item-action" data-value="' + p.value + '">' + p.label + '</button>';
            }).join('');
        }
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
        if (e.target !== input && !lista.contains(e.target)) {
            lista.style.display = 'none';
        }
    });

    document.getElementById('ventaFecha').value = new Date().toISOString().substring(0, 10);

    <?php if (!empty($_GET['clienteNuevo'])): ?>
    document.getElementById('buscarClienteVenta').value = <?php echo json_encode($_GET['clienteNuevo']); ?>;
    <?php endif; ?>
})();

function calcularTotalVenta() {
    var qq = parseFloat(document.getElementById('ventaQQ').value) || 0;
    var precio = parseFloat(document.getElementById('ventaPrecio').value) || 0;
    document.getElementById('ventaTotalDisplay').value = '$' + (qq * precio).toFixed(2);
}

function abrirModalRetencion() {
    var qq = parseFloat(document.getElementById('ventaQQ').value) || 0;
    var precio = parseFloat(document.getElementById('ventaPrecio').value) || 0;
    var base = qq * precio;
    document.getElementById('retBase').value = '$' + base.toFixed(2);
    document.getElementById('retPorcentaje').value = document.getElementById('ventaPorcRetencion').value || '0';
    calcularRetencion();
}

function calcularRetencion() {
    var qq = parseFloat(document.getElementById('ventaQQ').value) || 0;
    var precio = parseFloat(document.getElementById('ventaPrecio').value) || 0;
    var base = qq * precio;
    var porc = parseFloat(document.getElementById('retPorcentaje').value) || 0;
    var valorRet = base * porc;
    document.getElementById('retValor').value = '$' + valorRet.toFixed(2);
    document.getElementById('retACobrar').value = '$' + (base - valorRet).toFixed(2);
}

function aplicarRetencion() {
    var porc = document.getElementById('retPorcentaje').value;
    document.getElementById('ventaPorcRetencion').value = porc;
    var texto = porc == 0 ? 'Sin retención' : (parseFloat(porc) * 100).toFixed(2) + '% de retención aplicada';
    document.getElementById('retencionResumen').textContent = texto + ' — Valor a cobrar: ' + document.getElementById('retACobrar').value;
}

function validarVenta() {
    if (!document.getElementById('ventaIdProducto').value) {
        alert('Seleccione un producto de la lista de resultados.');
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
