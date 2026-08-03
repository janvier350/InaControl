<?php
ob_start();
session_start();
require_once("class/conexionBD_MT.php");

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] === "SUPERADMIN" || !isset($_SESSION['id_empresa'])) {
    header("Location: break.php");
    exit();
}
if (time() > $_SESSION['expire']) {
    session_destroy();
    header("Location: expirada.php");
    exit();
}

$con = conectarse_MT();
if (!$con) {
    die("<h3 style='font-family:sans-serif;color:#900;padding:2rem;'>No se pudo conectar a la base de datos.</h3>");
}
mysqli_report(MYSQLI_REPORT_OFF);

$idEmpresa = (int)$_SESSION['id_empresa'];
$rol_usuario = $_SESSION["rol"];

$stmt = mysqli_prepare($con, "SELECT * FROM COTI_CLIENTE WHERE ID_EMPRESA = ? ORDER BY ESTADO DESC, NOMBRES ASC, RAZON_SOCIAL ASC");
mysqli_stmt_bind_param($stmt, "i", $idEmpresa);
mysqli_stmt_execute($stmt);
$clientes = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Clientes</title>
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
                    <div class="widget-heading"><?php echo htmlspecialchars($_SESSION["username"]); ?></div>
                    <div class="widget-subheading"><?php echo htmlspecialchars($_SESSION['nombre_empresa'] ?? ''); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="app-main">
        <div class="app-sidebar sidebar-shadow">
            <?php include("./menu/menu_MT.php"); ?>
        </div>
        <div class="app-main__outer">
            <div class="app-main__inner">
                <div class="app-page-title">
                    <div class="page-title-wrapper">
                        <div class="page-title-heading">
                            <div class="page-title-icon"><i class="pe-7s-users icon-gradient bg-warm-flame"></i></div>
                            <div>
                                <div class="page-title-subheading text-black">Clientes</div>
                            </div>
                            <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                                <i class="bi bi-plus-lg"></i> Nuevo Cliente
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        Operación realizada correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        Error: <?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <input type="text" id="buscador" class="form-control mb-3" placeholder="Buscar por nombre, razón social o correo..." onkeyup="filtrarClientes()">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaClientes">
                                <thead>
                                    <tr>
                                        <th>NOMBRE / RAZÓN SOCIAL</th>
                                        <th>RUC</th>
                                        <th>EMAIL</th>
                                        <th>TELÉFONO</th>
                                        <th>ESTADO</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while ($c = mysqli_fetch_assoc($clientes)):
                                    $nombreCompleto = trim($c['NOMBRES'].' '.$c['APELLIDOS']);
                                    $filtro = strtolower($nombreCompleto.' '.$c['RAZON_SOCIAL'].' '.$c['EMAIL']);
                                ?>
                                    <tr data-filtro="<?php echo htmlspecialchars($filtro); ?>">
                                        <td>
                                            <?php if ($nombreCompleto) echo '<div>'.htmlspecialchars($nombreCompleto).'</div>'; ?>
                                            <?php if ($c['RAZON_SOCIAL']) echo '<small class="text-muted">'.htmlspecialchars($c['RAZON_SOCIAL']).'</small>'; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['RUC'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($c['EMAIL'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($c['TELEFONO'] ?: '-'); ?></td>
                                        <td>
                                            <?php if ($c['ESTADO'] === 'A'): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal" data-bs-target="#modalEditarCliente"
                                                onclick='cargarCliente(<?php echo json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                <i class="bi bi-pencil"></i> Editar
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

<!-- Modal Nuevo Cliente -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="class/MT_Insert_Cliente.php">
                    <input type="hidden" name="redirect" value="MT_Clientes.php">
                    <div class="mb-2"><label class="form-label">Nombres</label><input class="form-control" name="nombres"></div>
                    <div class="mb-2"><label class="form-label">Apellidos</label><input class="form-control" name="apellidos"></div>
                    <div class="mb-2"><label class="form-label">Razón Social (opcional)</label><input class="form-control" name="razonSocial"></div>
                    <div class="mb-2"><label class="form-label">RUC</label><input class="form-control" name="ruc"></div>
                    <div class="mb-2"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
                    <div class="mb-2"><label class="form-label">Teléfono</label><input class="form-control" name="telefono"></div>
                    <div class="text-end"><button type="submit" class="btn btn-success">Guardar</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="class/MT_Editar_Cliente.php">
                    <input type="hidden" name="idCliente" id="editIdCliente">
                    <div class="mb-2"><label class="form-label">Nombres</label><input class="form-control" name="nombres" id="editNombres"></div>
                    <div class="mb-2"><label class="form-label">Apellidos</label><input class="form-control" name="apellidos" id="editApellidos"></div>
                    <div class="mb-2"><label class="form-label">Razón Social</label><input class="form-control" name="razonSocial" id="editRazonSocial"></div>
                    <div class="mb-2"><label class="form-label">RUC</label><input class="form-control" name="ruc" id="editRuc"></div>
                    <div class="mb-2"><label class="form-label">Email</label><input type="email" class="form-control" name="email" id="editEmail"></div>
                    <div class="mb-2"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" id="editTelefono"></div>
                    <div class="mb-2">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado" id="editEstado">
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
function cargarCliente(c) {
    document.getElementById('editIdCliente').value   = c.ID_CLIENTE;
    document.getElementById('editNombres').value     = c.NOMBRES || '';
    document.getElementById('editApellidos').value   = c.APELLIDOS || '';
    document.getElementById('editRazonSocial').value = c.RAZON_SOCIAL || '';
    document.getElementById('editRuc').value         = c.RUC || '';
    document.getElementById('editEmail').value       = c.EMAIL || '';
    document.getElementById('editTelefono').value    = c.TELEFONO || '';
    document.getElementById('editEstado').value      = c.ESTADO || 'A';
}

function filtrarClientes() {
    const texto = document.getElementById('buscador').value.toLowerCase();
    document.querySelectorAll('#tablaClientes tbody tr').forEach(function(fila) {
        fila.style.display = fila.dataset.filtro.indexOf(texto) !== -1 ? '' : 'none';
    });
}
</script>
</body>
</html>
