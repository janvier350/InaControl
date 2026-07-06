<!doctype html>
<html lang="es">
<?php
ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD.php");
$conexion = conectarse();

if (!isset($_SESSION["rol"])) {
    header("Location: break.php");
    exit();
}
$now = time();
if ($now > $_SESSION['expire']) {
    session_destroy();
    header("Location: expirada.php");
    exit();
}
$rol_usuario = $_SESSION["rol"];
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="es">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Correos Corporativos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <link href="./main.css" rel="stylesheet">
    <style>
        .pass-cell { font-family: monospace; letter-spacing: 1px; }
        .badge-activo  { background: #28a745; color:#fff; padding:3px 10px; border-radius:12px; font-size:.78em; }
        .badge-inactivo{ background: #dc3545; color:#fff; padding:3px 10px; border-radius:12px; font-size:.78em; }
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
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box"><span class="hamburger-inner"></span></span>
            </button>
        </div>
        <div class="app-header__menu">
            <span>
                <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                    <span class="btn-icon-wrapper"><i class="fa fa-ellipsis-v fa-w-6"></i></span>
                </button>
            </span>
        </div>
        <div class="app-header__content">
            <div class="app-header-left"></div>
            <div class="app-header-right">
                <div class="header-btn-lg pr-0">
                    <div class="widget-content p-0">
                        <div class="widget-content-wrapper">
                            <div class="widget-content-left ml-3 header-user-info">
                                <div class="widget-heading">Admin <?php echo $_SESSION["username"]; ?></div>
                                <div class="widget-subheading">Administrator</div>
                            </div>
                        </div>
                    </div>
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
                            <div class="page-title-icon">
                                <i class="pe-7s-mail icon-gradient bg-warm-flame"></i>
                            </div>
                            <div>Correos Corporativos
                                <div class="page-title-subheading">Gestión de cuentas de correo y asignación a usuarios.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php
                    $msgs = array(
                        'correo_registrado'  => 'Correo registrado correctamente.',
                        'correo_actualizado' => 'Correo actualizado correctamente.',
                        'correo_eliminado'   => 'Correo desactivado correctamente.',
                    );
                    echo isset($msgs[$_GET['success']]) ? htmlspecialchars($msgs[$_GET['success']]) : 'Operación exitosa.';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error: <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <ul class="body-tabs body-tabs-layout tabs-animated body-tabs-animated nav">
                    <li class="nav-item">
                        <a role="tab" class="nav-link active" id="tab-0" data-toggle="tab" href="#tab-content-0"><span>Registrar</span></a>
                    </li>
                    <li class="nav-item">
                        <a role="tab" class="nav-link" id="tab-1" data-toggle="tab" href="#tab-content-1"><span>Listado</span></a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- TAB REGISTRAR -->
                    <div class="tab-pane tabs-animation fade show active" id="tab-content-0" role="tabpanel">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <h5 class="card-title">Nueva cuenta de correo</h5>
                                <form method="POST" action="class/Insert_Correo.php" class="needs-validation" novalidate>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label>Correo electrónico</label>
                                            <input type="email" class="form-control" name="correo" placeholder="usuario@dominio.com" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Contraseña</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="contrasena" id="nuevaContrasena" placeholder="Contraseña de la cuenta" required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePass('nuevaContrasena', this)"><i class="bi bi-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-md-6 mb-3">
                                            <label>Almacenamiento</label>
                                            <input type="text" class="form-control" name="almacenamiento" placeholder="Ej: 5 GB, 10 GB, ilimitado">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label>Asignar a usuario <small class="text-muted">(opcional)</small></label>
                                            <select class="form-select" name="idUsuario">
                                                <option value="">Sin asignar</option>
                                                <?php
                                                  $qu = $conexion->query("SELECT IDADM_USUARIO, NOMBRES, APELLIDOS FROM ADM_USUARIO WHERE ESTADO='A'");
                                                  while ($u = mysqli_fetch_array($qu)) {
                                                    echo '<option value="'.$u['IDADM_USUARIO'].'">'.htmlspecialchars($u['NOMBRES'].' '.$u['APELLIDOS']).'</option>';
                                                  }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle"></i> Registrar Correo</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- TAB LISTADO -->
                    <div class="tab-pane tabs-animation fade" id="tab-content-1" role="tabpanel">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <table class="table align-middle mb-0 bg-white" id="tablaCorreos">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>CORREO</th>
                                            <th>CONTRASEÑA</th>
                                            <th>ALMACENAMIENTO</th>
                                            <th>ASIGNADO A</th>
                                            <th>ESTADO</th>
                                            <th>REGISTRADO</th>
                                            <th>ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sqlC = "SELECT C.ID_CORREO, C.CORREO, C.CONTRASENA, C.ALMACENAMIENTO, C.ESTADO, C.FECHA_REGISTRO, C.IDADM_USUARIO,
                                                        U.NOMBRES, U.APELLIDOS
                                                 FROM COR_CORREO C
                                                 LEFT JOIN ADM_USUARIO U ON C.IDADM_USUARIO = U.IDADM_USUARIO
                                                 ORDER BY C.ESTADO DESC, C.CORREO ASC";
                                        $qC = $conexion->query($sqlC);

                                        if ($qC && $qC->num_rows > 0) {
                                            while ($c = mysqli_fetch_array($qC)) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($c['CORREO']); ?></strong></td>
                                            <td class="pass-cell">
                                                <span class="pass-hidden" data-pass="<?php echo htmlspecialchars($c['CONTRASENA']); ?>">••••••••</span>
                                                <button class="btn btn-sm btn-link p-0 ms-1" onclick="togglePassCell(this)" title="Ver contraseña"><i class="bi bi-eye"></i></button>
                                            </td>
                                            <td><?php echo htmlspecialchars($c['ALMACENAMIENTO'] ?: '-'); ?></td>
                                            <td><?php echo $c['NOMBRES'] ? htmlspecialchars($c['NOMBRES'].' '.$c['APELLIDOS']) : '<span class="text-muted">Sin asignar</span>'; ?></td>
                                            <td>
                                                <?php if ($c['ESTADO'] === 'A') { ?>
                                                    <span class="badge-activo">Activo</span>
                                                <?php } else { ?>
                                                    <span class="badge-inactivo">Inactivo</span>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($c['FECHA_REGISTRO'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-warning fa fa-edit"
                                                    data-bs-toggle="modal" data-bs-target="#editModalCorreo"
                                                    onclick="cargarCorreo(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8'); ?>)">
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger fa fa-minus-circle"
                                                    onclick="confirmarEliminarCorreo(<?php echo $c['ID_CORREO']; ?>)">
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center text-muted'>No hay correos registrados.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-wrapper-footer">
                <div class="app-footer"><div class="app-footer__inner"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Correo -->
<div class="modal fade" id="editModalCorreo" tabindex="-1" aria-labelledby="editModalCorreoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalCorreoLabel">Editar correo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="class/Editar_Correo.php">
                    <input type="hidden" id="editIdCorreo" name="idCorreo">
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="editCorreo" name="correo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="editContrasena" name="contrasena" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePass('editContrasena', this)"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Almacenamiento</label>
                            <input type="text" class="form-control" id="editAlmacenamiento" name="almacenamiento">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Asignar a usuario</label>
                            <select class="form-select" id="editIdUsuario" name="idUsuario">
                                <option value="">Sin asignar</option>
                                <?php
                                  $qu2 = $conexion->query("SELECT IDADM_USUARIO, NOMBRES, APELLIDOS FROM ADM_USUARIO WHERE ESTADO='A'");
                                  while ($u2 = mysqli_fetch_array($qu2)) {
                                    echo '<option value="'.$u2['IDADM_USUARIO'].'">'.htmlspecialchars($u2['NOMBRES'].' '.$u2['APELLIDOS']).'</option>';
                                  }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="editEstadoCorreo" name="estado">
                                <option value="A">Activo</option>
                                <option value="I">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cargarCorreo(c) {
    document.getElementById('editIdCorreo').value    = c.ID_CORREO;
    document.getElementById('editCorreo').value      = c.CORREO;
    document.getElementById('editContrasena').value  = c.CONTRASENA;
    document.getElementById('editAlmacenamiento').value = c.ALMACENAMIENTO || '';
    document.getElementById('editIdUsuario').value   = c.IDADM_USUARIO || '';
    document.getElementById('editEstadoCorreo').value = c.ESTADO;
}

function confirmarEliminarCorreo(id) {
    if (confirm('¿Desactivar este correo?')) {
        window.location.href = 'class/Eliminar_Correo.php?idCorreo=' + id;
    }
}

function togglePass(inputId, btn) {
    var input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        input.type = 'password';
        btn.innerHTML = '<i class="bi bi-eye"></i>';
    }
}

function togglePassCell(btn) {
    var span = btn.previousElementSibling;
    if (span.textContent === '••••••••') {
        span.textContent = span.dataset.pass;
        btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        span.textContent = '••••••••';
        btn.innerHTML = '<i class="bi bi-eye"></i>';
    }
}
</script>

<script type="text/javascript" src="./assets/scripts/main.js"></script>
</body>
</html>
