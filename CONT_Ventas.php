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

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-registrar">Registrar</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-listado">Listado</a></li>
                </ul>
                <div class="tab-content pt-3">
                    <!-- Registrar -->
                    <div class="tab-pane fade show active" id="tab-registrar">
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
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Cliente</label>
                                            <input type="text" class="form-control" name="cliente">
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
                    <div class="tab-pane fade" id="tab-listado">
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
                                        <thead><tr><th>FECHA</th><th>PRODUCTO</th><th>CLIENTE</th><th>TIPO</th><th>QQ</th><th>PRECIO</th><th>TOTAL</th></tr></thead>
                                        <tbody>
                                        <?php if (mysqli_num_rows($resV) === 0): ?>
                                            <tr><td colspan="7" class="text-center text-muted">Sin ventas en este filtro.</td></tr>
                                        <?php else: while ($v = mysqli_fetch_assoc($resV)): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($v['FECHA'])); ?></td>
                                                <td><?php echo htmlspecialchars($v['PRODUCTO_NOMBRE']); ?></td>
                                                <td><?php echo htmlspecialchars($v['CLIENTE'] ?: '-'); ?></td>
                                                <td><span class="badge <?php echo $v['TIPO'] === 'CON_FACTURA' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $v['TIPO'] === 'CON_FACTURA' ? 'Con Factura' : 'Sin Factura'; ?></span></td>
                                                <td><?php echo number_format($v['QQ'], 2); ?></td>
                                                <td>$<?php echo number_format($v['PRECIO'], 4); ?></td>
                                                <td>$<?php echo number_format($v['TOTAL'], 2); ?></td>
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
var productosVenta = <?php echo json_encode($productosJs, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

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
})();

function calcularTotalVenta() {
    var qq = parseFloat(document.getElementById('ventaQQ').value) || 0;
    var precio = parseFloat(document.getElementById('ventaPrecio').value) || 0;
    document.getElementById('ventaTotalDisplay').value = '$' + (qq * precio).toFixed(2);
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
