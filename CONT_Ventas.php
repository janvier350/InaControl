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

                <?php $verListado = isset($_GET['anio']) || isset($_GET['mes']) || isset($_GET['tipo']) || isset($_GET['semestre']); ?>
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
                                $semestreFiltro = isset($_GET['semestre']) ? (int)$_GET['semestre'] : 0;
                                $tipoFiltro = $_GET['tipo'] ?? '';

                                $sqlV = "SELECT V.*, P.NOMBRE AS PRODUCTO_NOMBRE, P.CODIGO AS PRODUCTO_CODIGO
                                         FROM CONT_VENTA V INNER JOIN CONT_PRODUCTO P ON V.ID_PRODUCTO = P.ID_PRODUCTO
                                         WHERE V.ESTADO = 'A' AND YEAR(V.FECHA) = ?";
                                $types = "i"; $params = [$anioFiltro];
                                if ($mesFiltro) { $sqlV .= " AND MONTH(V.FECHA) = ?"; $types .= "i"; $params[] = $mesFiltro; }
                                if ($semestreFiltro === 1) { $sqlV .= " AND MONTH(V.FECHA) BETWEEN 1 AND 6"; }
                                if ($semestreFiltro === 2) { $sqlV .= " AND MONTH(V.FECHA) BETWEEN 7 AND 12"; }
                                if ($tipoFiltro) { $sqlV .= " AND V.TIPO = ?"; $types .= "s"; $params[] = $tipoFiltro; }
                                $sqlV .= " ORDER BY V.FECHA DESC";

                                $stmtV = mysqli_prepare($conexion, $sqlV);
                                mysqli_stmt_bind_param($stmtV, $types, ...$params);
                                mysqli_stmt_execute($stmtV);
                                $resV = mysqli_stmt_get_result($stmtV);

                                $filasVenta = [];
                                $sumQQ = 0; $sumTotal = 0; $sumRetencion = 0; $sumACobrar = 0;
                                while ($v = mysqli_fetch_assoc($resV)) {
                                    $filasVenta[] = $v;
                                    $sumQQ += $v['QQ'];
                                    $sumTotal += $v['TOTAL'];
                                    $sumRetencion += $v['VALOR_RETENCION'];
                                    $sumACobrar += $v['TOTAL_A_COBRAR'] ?? $v['TOTAL'];
                                }

                                $pagosPorVenta = [];
                                if (!empty($filasVenta)) {
                                    $idsVenta = array_map(fn($v) => (int)$v['ID_VENTA'], $filasVenta);
                                    $placeholders = implode(',', array_fill(0, count($idsVenta), '?'));
                                    $typesPagos = str_repeat('i', count($idsVenta));
                                    $stmtPagos = mysqli_prepare($conexion, "SELECT * FROM CONT_VENTA_PAGO WHERE ID_VENTA IN ($placeholders) ORDER BY FECHA_PAGO ASC");
                                    mysqli_stmt_bind_param($stmtPagos, $typesPagos, ...$idsVenta);
                                    mysqli_stmt_execute($stmtPagos);
                                    $resPagos = mysqli_stmt_get_result($stmtPagos);
                                    while ($p = mysqli_fetch_assoc($resPagos)) {
                                        $pagosPorVenta[$p['ID_VENTA']][] = $p;
                                    }
                                }

                                foreach ($filasVenta as &$v) {
                                    $aCobrar = $v['TOTAL_A_COBRAR'] ?? $v['TOTAL'];
                                    $pagos = $pagosPorVenta[$v['ID_VENTA']] ?? [];
                                    $pagado = array_sum(array_column($pagos, 'MONTO'));
                                    $v['PAGOS'] = $pagos;
                                    $v['TOTAL_PAGADO'] = $pagado;
                                    $v['SALDO_PENDIENTE'] = round($aCobrar - $pagado, 2);
                                    if ($pagado <= 0) {
                                        $v['ESTADO_PAGO'] = 'PENDIENTE';
                                    } elseif ($v['SALDO_PENDIENTE'] > 0.009) {
                                        $v['ESTADO_PAGO'] = 'PARCIAL';
                                    } else {
                                        $v['ESTADO_PAGO'] = 'PAGADO';
                                    }
                                }
                                unset($v);
                                ?>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <div class="card text-center shadow-sm"><div class="card-body py-2">
                                            <div class="small text-muted">Quintales (QQ)</div>
                                            <div class="h5 mb-0"><?php echo number_format($sumQQ, 2); ?></div>
                                        </div></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center shadow-sm"><div class="card-body py-2">
                                            <div class="small text-muted">Total Vendido</div>
                                            <div class="h5 mb-0">$<?php echo number_format($sumTotal, 2); ?></div>
                                        </div></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center shadow-sm"><div class="card-body py-2">
                                            <div class="small text-muted">Retención</div>
                                            <div class="h5 mb-0">$<?php echo number_format($sumRetencion, 2); ?></div>
                                        </div></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center shadow-sm"><div class="card-body py-2">
                                            <div class="small text-muted">Total a Cobrar</div>
                                            <div class="h5 mb-0">$<?php echo number_format($sumACobrar, 2); ?></div>
                                        </div></div>
                                    </div>
                                </div>
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
                                        <select class="form-select" name="semestre" onchange="this.form.submit()">
                                            <option value="0">Todo el año</option>
                                            <option value="1" <?php echo $semestreFiltro === 1 ? 'selected' : ''; ?>>1er Semestre</option>
                                            <option value="2" <?php echo $semestreFiltro === 2 ? 'selected' : ''; ?>>2do Semestre</option>
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
                                        <thead><tr><th>FECHA</th><th>PRODUCTO</th><th>CLIENTE</th><th>N° FACTURA</th><th>TIPO</th><th>QQ</th><th>PRECIO</th><th>TOTAL</th><th>RETENCIÓN</th><th>A COBRAR</th><th>PAGO</th><th>ACCIONES</th></tr></thead>
                                        <tbody>
                                        <?php if (empty($filasVenta)): ?>
                                            <tr><td colspan="12" class="text-center text-muted">Sin ventas en este filtro.</td></tr>
                                        <?php else: foreach ($filasVenta as $v):
                                            $badgePago = ['PENDIENTE' => 'bg-secondary', 'PARCIAL' => 'bg-warning text-dark', 'PAGADO' => 'bg-success'][$v['ESTADO_PAGO']];
                                            $labelPago = ['PENDIENTE' => 'Pendiente', 'PARCIAL' => 'Parcial', 'PAGADO' => 'Pagado'][$v['ESTADO_PAGO']];
                                        ?>
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
                                                <td><span class="badge <?php echo $badgePago; ?>"><?php echo $labelPago; ?></span></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPagoVenta"
                                                        onclick='abrirModalPago(<?php echo json_encode([
                                                            "ID_VENTA" => $v["ID_VENTA"],
                                                            "PRODUCTO" => $v["PRODUCTO_NOMBRE"],
                                                            "CLIENTE" => $v["CLIENTE"],
                                                            "TOTAL_A_COBRAR" => $v["TOTAL_A_COBRAR"] ?? $v["TOTAL"],
                                                            "TOTAL_PAGADO" => $v["TOTAL_PAGADO"],
                                                            "SALDO_PENDIENTE" => $v["SALDO_PENDIENTE"],
                                                            "PAGOS" => $v["PAGOS"],
                                                        ], JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                        <i class="bi bi-cash-coin"></i>
                                                    </button>
                                                </td>
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

<!-- Modal Registrar Pago -->
<div class="modal fade" id="modalPagoVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar Pago</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="mb-1"><strong id="pagoProducto"></strong> — <span id="pagoCliente" class="text-muted"></span></p>
                <div class="row text-center mb-3">
                    <div class="col-4"><div class="small text-muted">Total a Cobrar</div><div class="fw-bold" id="pagoTotalACobrar"></div></div>
                    <div class="col-4"><div class="small text-muted">Pagado</div><div class="fw-bold text-success" id="pagoTotalPagado"></div></div>
                    <div class="col-4"><div class="small text-muted">Saldo Pendiente</div><div class="fw-bold text-danger" id="pagoSaldoPendiente"></div></div>
                </div>

                <div id="pagoHistorial" class="mb-3"></div>

                <form method="POST" action="class/Insert_PagoVenta.php" onsubmit="return validarPago();">
                    <input type="hidden" name="idVenta" id="pagoIdVenta">
                    <input type="hidden" name="redirectQuery" id="pagoRedirectQuery">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Fecha de Pago</label>
                            <input type="date" class="form-control" name="fechaPago" id="pagoFecha" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Forma de Pago</label>
                            <select class="form-select" name="formaPago" id="pagoForma">
                                <option value="DEPOSITO" selected>Depósito</option>
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="CHEQUE">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Monto</label>
                            <input type="number" step="0.01" class="form-control" name="monto" id="pagoMonto" required>
                        </div>
                        <div class="col-md-3 mb-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="document.getElementById('pagoMonto').value = document.getElementById('pagoMonto').dataset.saldo;">Usar saldo</button>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Referencia / Observación <span class="text-muted small">(N° de ingreso, banco, etc.)</span></label>
                        <input type="text" class="form-control" name="referencia" id="pagoReferencia" placeholder="Ej. Ingreso a Bco #422 por $5150,00">
                    </div>
                    <div class="text-end"><button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar Pago</button></div>
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

function abrirModalPago(v) {
    document.getElementById('pagoIdVenta').value = v.ID_VENTA;
    document.getElementById('pagoRedirectQuery').value = window.location.search.replace(/^\?/, '');
    document.getElementById('pagoProducto').textContent = v.PRODUCTO;
    document.getElementById('pagoCliente').textContent = v.CLIENTE || 'Sin cliente';
    document.getElementById('pagoTotalACobrar').textContent = '$' + Number(v.TOTAL_A_COBRAR).toFixed(2);
    document.getElementById('pagoTotalPagado').textContent = '$' + Number(v.TOTAL_PAGADO).toFixed(2);
    document.getElementById('pagoSaldoPendiente').textContent = '$' + Number(v.SALDO_PENDIENTE).toFixed(2);
    document.getElementById('pagoFecha').value = new Date().toISOString().substring(0, 10);
    document.getElementById('pagoForma').value = 'DEPOSITO';
    document.getElementById('pagoReferencia').value = '';
    var montoInput = document.getElementById('pagoMonto');
    montoInput.value = v.SALDO_PENDIENTE > 0 ? v.SALDO_PENDIENTE.toFixed(2) : '';
    montoInput.dataset.saldo = v.SALDO_PENDIENTE.toFixed(2);

    var historial = document.getElementById('pagoHistorial');
    if (!v.PAGOS || !v.PAGOS.length) {
        historial.innerHTML = '<p class="text-muted small mb-0">Sin pagos registrados todavía.</p>';
    } else {
        var filas = v.PAGOS.map(function(p) {
            var formaLabel = {EFECTIVO: 'Efectivo', DEPOSITO: 'Depósito', CHEQUE: 'Cheque'}[p.FORMA_PAGO] || p.FORMA_PAGO;
            return '<tr><td>' + p.FECHA_PAGO.split('-').reverse().join('/') + '</td><td>' + formaLabel + '</td><td>$' +
                Number(p.MONTO).toFixed(2) + '</td><td>' + (p.REFERENCIA || '-') + '</td></tr>';
        }).join('');
        historial.innerHTML = '<label class="form-label small text-muted">Historial de pagos</label>' +
            '<table class="table table-sm"><thead><tr><th>Fecha</th><th>Forma</th><th>Monto</th><th>Referencia</th></tr></thead><tbody>' +
            filas + '</tbody></table>';
    }
}

function validarPago() {
    var monto = parseFloat(document.getElementById('pagoMonto').value) || 0;
    if (monto <= 0) {
        alert('Ingrese un monto válido.');
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
