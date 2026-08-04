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

$stmt = mysqli_prepare($con, "SELECT * FROM COTI_TIPO_SOPORTE WHERE ID_EMPRESA = ? ORDER BY ESTADO DESC, SOPORTE ASC");
mysqli_stmt_bind_param($stmt, "i", $idEmpresa);
mysqli_stmt_execute($stmt);
$tipos = mysqli_stmt_get_result($stmt);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kontrol - Tipos de Soporte</title>
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
                            <div class="page-title-icon"><i class="pe-7s-wrench icon-gradient bg-warm-flame"></i></div>
                            <div><div class="page-title-subheading text-black">Tipos de Soporte / Servicios</div></div>
                            <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#modalNuevoTipo">
                                <i class="bi bi-plus-lg"></i> Nuevo Servicio
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
                        <table class="table table-hover">
                            <thead><tr><th>SERVICIO</th><th>DESCRIPCIÓN</th><th>ESTADO</th><th>ACCIONES</th></tr></thead>
                            <tbody>
                            <?php while ($t = mysqli_fetch_assoc($tipos)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($t['SOPORTE']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($t['DESCRIPCION'] ?: '-'); ?></td>
                                    <td>
                                        <?php if ($t['ESTADO'] === 'A'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal" data-bs-target="#modalEditarTipo"
                                            onclick='cargarTipo(<?php echo json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
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

<div class="modal fade" id="modalNuevoTipo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Nuevo Servicio</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/MT_Insert_TipoSoporte.php">
                    <div class="mb-2"><label class="form-label">Nombre del servicio</label><input class="form-control" name="soporte" required></div>
                    <div class="mb-2"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="2"></textarea></div>
                    <div class="text-end"><button type="submit" class="btn btn-success">Guardar</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarTipo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar Servicio</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/MT_Editar_TipoSoporte.php">
                    <input type="hidden" name="idTipo" id="editIdTipo">
                    <div class="mb-2"><label class="form-label">Nombre del servicio</label><input class="form-control" name="soporte" id="editSoporte" required></div>
                    <div class="mb-2"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" id="editDescripcion" rows="2"></textarea></div>
                    <div class="mb-2">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado" id="editEstadoTipo">
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
function cargarTipo(t) {
    document.getElementById('editIdTipo').value = t.ID_TIPO_SOPORTE;
    document.getElementById('editSoporte').value = t.SOPORTE || '';
    document.getElementById('editDescripcion').value = t.DESCRIPCION || '';
    document.getElementById('editEstadoTipo').value = t.ESTADO || 'A';
}
</script>
</body>
</html>
