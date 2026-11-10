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
?>
<head>
    <meta charset="utf-8">
    <title>Catálogo de Productos</title>
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
                            <div class="page-title-icon"><i class="pe-7s-box1 icon-gradient bg-warm-flame"></i></div>
                            <div>
                                <div class="page-title-subheading text-black">Catálogo de Productos</div>
                                <p class="text-dark">Registro único de productos para ventas y compras.</p>
                            </div>
                            <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                                <i class="bi bi-plus-lg"></i> Nuevo Producto
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
                        <input type="text" id="buscadorProd" class="form-control mb-3" placeholder="Buscar por código o nombre..." onkeyup="filtrarProductos()">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaProductos">
                                <thead>
                                    <tr><th>CÓDIGO</th><th>NOMBRE</th><th>ESTADO</th><th>ACCIONES</th></tr>
                                </thead>
                                <tbody>
                                <?php
                                $q = $conexion->query("SELECT * FROM CONT_PRODUCTO ORDER BY ESTADO DESC, NOMBRE ASC");
                                while ($p = mysqli_fetch_assoc($q)):
                                    $filtro = strtolower($p['CODIGO'].' '.$p['NOMBRE']);
                                ?>
                                    <tr data-filtro="<?php echo htmlspecialchars($filtro); ?>">
                                        <td><?php echo htmlspecialchars($p['CODIGO'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($p['NOMBRE']); ?></td>
                                        <td>
                                            <?php if ($p['ESTADO'] === 'A'): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditarProducto"
                                                onclick='cargarProducto(<?php echo json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
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

<!-- Modal Nuevo Producto -->
<div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Nuevo Producto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/Insert_Producto.php">
                    <div class="mb-2"><label class="form-label">Código (opcional)</label><input class="form-control" name="codigo"></div>
                    <div class="mb-2"><label class="form-label">Nombre del producto</label><input class="form-control" name="nombre" required></div>
                    <div class="text-end"><button type="submit" class="btn btn-success">Guardar</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar Producto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/Editar_Producto.php">
                    <input type="hidden" name="idProducto" id="editIdProducto">
                    <div class="mb-2"><label class="form-label">Código</label><input class="form-control" name="codigo" id="editCodigo"></div>
                    <div class="mb-2"><label class="form-label">Nombre</label><input class="form-control" name="nombre" id="editNombre" required></div>
                    <div class="mb-2">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado" id="editEstadoProd">
                            <option value="A">Activo</option>
                            <option value="I">Inactivo</option>
                        </select>
                    </div>
                    <div class="text-end"><button type="submit" class="btn btn-primary">Guardar cambios</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cargarProducto(p) {
    document.getElementById('editIdProducto').value = p.ID_PRODUCTO;
    document.getElementById('editCodigo').value = p.CODIGO || '';
    document.getElementById('editNombre').value = p.NOMBRE || '';
    document.getElementById('editEstadoProd').value = p.ESTADO || 'A';
}
function filtrarProductos() {
    const texto = document.getElementById('buscadorProd').value.toLowerCase();
    document.querySelectorAll('#tablaProductos tbody tr').forEach(function(fila) {
        fila.style.display = fila.dataset.filtro.indexOf(texto) !== -1 ? '' : 'none';
    });
}
</script>
</body>
</html>
<?php ob_end_flush(); ?>
