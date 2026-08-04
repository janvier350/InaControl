<!doctype html>
<html lang="es">
<?php
ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD_MT.php");

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "SUPERADMIN") {
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
    die("<h3 style='font-family:sans-serif;color:#900;padding:2rem;'>No se pudo conectar a la base de datos multi-tenant. Revisa class/db_config_MT.php.</h3>");
}
mysqli_report(MYSQLI_REPORT_OFF);

// Empresa seleccionada para gestionar usuarios
$empresaActiva = isset($_GET['empresa']) ? (int)$_GET['empresa'] : null;
?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>Kontrol - Panel SUPERADMIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <link href="./main.css" rel="stylesheet">
    <style>
        .card-empresa { border-left: 5px solid #0f3460; transition: box-shadow .2s; }
        .card-empresa:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); }
        .badge-premium  { background:#0f3460; color:#fff; }
        .badge-estandar { background:#1a6b8a; color:#fff; }
        .badge-basico   { background:#6c757d; color:#fff; }
        .badge-activo   { background:#28a745; color:#fff; padding:3px 10px; border-radius:12px; font-size:.78em; }
        .badge-inactivo { background:#dc3545; color:#fff; padding:3px 10px; border-radius:12px; font-size:.78em; }
        .mod-badge { font-size:.72em; padding:2px 8px; border-radius:10px; margin:1px; display:inline-block; }
        .mod-on  { background:#d4edda; color:#155724; }
        .mod-off { background:#f8d7da; color:#721c24; text-decoration:line-through; }
        .superadmin-bar { background:#0f3460; color:#fff; padding:8px 20px; font-size:.85em; }
    </style>
</head>
<body>
<div class="superadmin-bar d-flex justify-content-between align-items-center">
    <span><i class="bi bi-shield-lock-fill me-2"></i><strong>KONTROL</strong> — Panel SUPERADMIN Multi-Empresa</span>
    <span>
        <?php echo htmlspecialchars($_SESSION["username"]); ?>
        &nbsp;|&nbsp;
        <a href="salir.php" class="text-white text-decoration-none"><i class="bi bi-box-arrow-right"></i> Salir</a>
    </span>
</div>

<div class="container-fluid py-4 px-4">

    <?php
    $msgs = [
        'empresa_creada'    => ['success', 'Empresa creada correctamente. El usuario administrador ya puede iniciar sesión.'],
        'empresa_actualizada'=>['success', 'Empresa actualizada correctamente.'],
        'usuario_creado'    => ['success', 'Usuario creado correctamente.'],
        'datos_incompletos' => ['danger',  'Faltan datos obligatorios.'],
        'usuario_duplicado' => ['danger',  'El nombre de usuario ya existe en esta empresa.'],
        'acceso_denegado'   => ['danger',  'Acceso denegado.'],
    ];
    if (isset($_GET['success']) && isset($msgs[$_GET['success']])) {
        echo '<div class="alert alert-'.$msgs[$_GET['success']][0].' alert-dismissible fade show">'.
             $msgs[$_GET['success']][1].
             '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    if (isset($_GET['error']) && isset($msgs[$_GET['error']])) {
        echo '<div class="alert alert-'.$msgs[$_GET['error']][0].' alert-dismissible fade show">'.
             $msgs[$_GET['error']][1].
             '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    if (isset($_GET['error']) && !isset($msgs[$_GET['error']])) {
        echo '<div class="alert alert-danger alert-dismissible fade show">Error: '.htmlspecialchars($_GET['error']).
             '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    ?>

    <!-- KPIs rápidos -->
    <?php
    function mt_count($con, $sql) {
        $r = mysqli_query($con, $sql);
        if (!$r) {
            echo "<div class='alert alert-danger'>Error SQL: " . htmlspecialchars(mysqli_error($con)) . " (consulta: " . htmlspecialchars($sql) . ")</div>";
            return 0;
        }
        return mysqli_fetch_row($r)[0];
    }
    $totalEmpresas   = mt_count($con, "SELECT COUNT(*) FROM EMPRESA WHERE ESTADO='A'");
    $totalUsuarios   = mt_count($con, "SELECT COUNT(*) FROM ADM_USUARIO WHERE ESTADO='A'");
    $empresasPremium = mt_count($con, "SELECT COUNT(*) FROM EMPRESA WHERE PLAN='PREMIUM' AND ESTADO='A'");
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <div style="font-size:2rem;color:#0f3460;"><i class="bi bi-building"></i></div>
                    <h3 class="mb-0"><?php echo $totalEmpresas; ?></h3>
                    <small class="text-muted">Empresas activas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <div style="font-size:2rem;color:#1a6b8a;"><i class="bi bi-people-fill"></i></div>
                    <h3 class="mb-0"><?php echo $totalUsuarios; ?></h3>
                    <small class="text-muted">Usuarios totales</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <div style="font-size:2rem;color:#e94560;"><i class="bi bi-star-fill"></i></div>
                    <h3 class="mb-0"><?php echo $empresasPremium; ?></h3>
                    <small class="text-muted">Plan Premium</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 d-flex align-items-center">
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalNuevaEmpresa">
                <i class="bi bi-plus-circle me-1"></i> Nueva Empresa
            </button>
        </div>
    </div>

    <!-- Listado de empresas -->
    <h5 class="mb-3"><i class="bi bi-building me-2"></i>Empresas registradas</h5>
    <div class="row g-3" id="listaEmpresas">
    <?php
    $sqlE = "SELECT E.*, C.MOD_SOPORTE, C.MOD_INVENTARIO, C.MOD_CORREOS, C.MOD_VENTAS, C.MOD_REPORTES,
                    (SELECT COUNT(*) FROM ADM_USUARIO U WHERE U.ID_EMPRESA = E.ID_EMPRESA AND U.ESTADO='A') AS TOTAL_USUARIOS
             FROM EMPRESA E
             LEFT JOIN EMPRESA_CONFIG C ON E.ID_EMPRESA = C.ID_EMPRESA
             ORDER BY E.ESTADO DESC, E.NOMBRE ASC";
    $qE = mysqli_query($con, $sqlE);
    if (!$qE) {
        echo "<div class='alert alert-danger'>Error SQL en listado de empresas: " . htmlspecialchars(mysqli_error($con)) . "</div>";
        $qE = [];
    }
    while ($qE && ($e = mysqli_fetch_array($qE))):
        $planClass = strtolower($e['PLAN']);
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card card-empresa shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0 fw-bold"><?php echo htmlspecialchars($e['NOMBRE']); ?></h6>
                    <span class="badge badge-<?php echo $planClass; ?> ms-2"><?php echo $e['PLAN']; ?></span>
                </div>
                <div class="mb-1" style="font-size:.82em; color:#666;">
                    <?php if ($e['RUC'])           echo '<i class="bi bi-card-text me-1"></i>RUC: '.htmlspecialchars($e['RUC']).'<br>'; ?>
                    <?php if ($e['EMAIL_CONTACTO'])echo '<i class="bi bi-envelope me-1"></i>'.htmlspecialchars($e['EMAIL_CONTACTO']).'<br>'; ?>
                    <?php if ($e['TELEFONO'])       echo '<i class="bi bi-telephone me-1"></i>'.htmlspecialchars($e['TELEFONO']).'<br>'; ?>
                </div>
                <div class="mb-2">
                    <span class="mod-badge <?php echo $e['MOD_SOPORTE']    ? 'mod-on':'mod-off'; ?>">Soporte</span>
                    <span class="mod-badge <?php echo $e['MOD_INVENTARIO'] ? 'mod-on':'mod-off'; ?>">Inventario</span>
                    <span class="mod-badge <?php echo $e['MOD_CORREOS']    ? 'mod-on':'mod-off'; ?>">Correos</span>
                    <span class="mod-badge <?php echo $e['MOD_VENTAS']     ? 'mod-on':'mod-off'; ?>">Ventas</span>
                    <span class="mod-badge <?php echo $e['MOD_REPORTES']   ? 'mod-on':'mod-off'; ?>">Reportes</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small><i class="bi bi-people me-1"></i><?php echo $e['TOTAL_USUARIOS']; ?> usuario(s)
                    &nbsp; <?php echo $e['ESTADO']==='A' ? '<span class="badge-activo">Activa</span>' : '<span class="badge-inactivo">Inactiva</span>'; ?>
                    </small>
                    <div>
                        <button class="btn btn-sm btn-outline-warning me-1"
                            data-bs-toggle="modal" data-bs-target="#modalEditarEmpresa"
                            onclick="cargarEmpresa(<?php echo htmlspecialchars(json_encode($e), ENT_QUOTES, 'UTF-8'); ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal" data-bs-target="#modalUsuarios"
                            onclick="cargarUsuarios(<?php echo $e['ID_EMPRESA']; ?>, '<?php echo htmlspecialchars($e['NOMBRE'], ENT_QUOTES); ?>')">
                            <i class="bi bi-people"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted" style="font-size:.75em;">
                Registrada: <?php echo date('d/m/Y', strtotime($e['FECHA_REGISTRO'])); ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    </div>

    <!-- Panel de usuarios (se despliega al hacer clic en el ícono de personas) -->
    <div id="panelUsuarios" class="mt-4" style="display:none;">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:#f8f9fa;">
                <h6 class="mb-0"><i class="bi bi-people-fill me-2"></i>Usuarios de: <span id="lblEmpresaUsuarios"></span></h6>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
                    <i class="bi bi-person-plus"></i> Agregar usuario
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0" id="tablaUsuariosEmpresa">
                    <thead class="bg-light">
                        <tr>
                            <th>NOMBRE</th><th>USUARIO</th><th>ROL</th><th>TELÉFONO</th><th>ESTADO</th>
                        </tr>
                    </thead>
                    <tbody id="bodyUsuariosEmpresa">
                        <tr><td colspan="5" class="text-center text-muted">Selecciona una empresa.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- /container -->

<!-- Modal: Nueva Empresa -->
<div class="modal fade" id="modalNuevaEmpresa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#0f3460; color:#fff;">
                <h5 class="modal-title"><i class="bi bi-building-add me-2"></i>Registrar Nueva Empresa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="class/MT_Insert_Empresa.php">
                    <h6 class="text-muted mb-3">Datos de la empresa</h6>
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">RUC</label>
                            <input type="text" class="form-control" name="ruc">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Plan</label>
                            <select class="form-select" name="plan">
                                <option value="BASICO">Básico</option>
                                <option value="ESTANDAR">Estándar</option>
                                <option value="PREMIUM">Premium</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Email de contacto</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion">
                        </div>
                    </div>
                    <h6 class="text-muted mt-3 mb-2">Módulos activos</h6>
                    <div class="row g-2 mb-3">
                        <?php foreach (['soporte'=>'Soporte','inventario'=>'Inventario','correos'=>'Correos','ventas'=>'Ventas','reportes'=>'Reportes'] as $k => $v): ?>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="mod_<?php echo $k; ?>" id="new_mod_<?php echo $k; ?>"
                                    <?php echo in_array($k, ['soporte','inventario','reportes']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="new_mod_<?php echo $k; ?>"><?php echo $v; ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <h6 class="text-muted mb-2">Usuario administrador de la empresa <span class="text-danger">*</span></h6>
                    <div class="row g-2">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" name="admin_nombres" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="admin_apellidos" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Usuario (login)</label>
                            <input type="text" class="form-control" name="admin_usuario" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Contraseña inicial</label>
                            <input type="text" class="form-control" name="admin_clave" required placeholder="La verás una sola vez">
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Crear Empresa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Empresa -->
<div class="modal fade" id="modalEditarEmpresa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a6b8a; color:#fff;">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Empresa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="class/MT_Editar_Empresa.php">
                    <input type="hidden" id="editIdEmpresa" name="idEmpresa">
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editNombreEmpresa" name="nombre" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">RUC</label>
                            <input type="text" class="form-control" id="editRucEmpresa" name="ruc">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Plan</label>
                            <select class="form-select" id="editPlanEmpresa" name="plan">
                                <option value="BASICO">Básico</option>
                                <option value="ESTANDAR">Estándar</option>
                                <option value="PREMIUM">Premium</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmailEmpresa" name="email">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="editTelefonoEmpresa" name="telefono">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="editEstadoEmpresa" name="estado">
                                <option value="A">Activa</option>
                                <option value="I">Suspendida</option>
                            </select>
                        </div>
                    </div>
                    <h6 class="text-muted mt-3 mb-2">Módulos activos</h6>
                    <div class="row g-2 mb-3">
                        <?php foreach (['soporte'=>'Soporte','inventario'=>'Inventario','correos'=>'Correos','ventas'=>'Ventas','reportes'=>'Reportes'] as $k => $v): ?>
                        <div class="col-auto">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="mod_<?php echo $k; ?>" id="edit_mod_<?php echo $k; ?>">
                                <label class="form-check-label" for="edit_mod_<?php echo $k; ?>"><?php echo $v; ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nuevo Usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#28a745; color:#fff;">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Agregar Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="class/MT_Insert_UsuarioEmpresa.php">
                    <input type="hidden" id="nuevoUsuarioIdEmpresa" name="idEmpresa">
                    <div class="mb-2">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control" name="nombres" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" name="apellidos" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Usuario (login)</label>
                        <input type="text" class="form-control" name="usuario" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contraseña</label>
                        <input type="text" class="form-control" name="clave" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="idRol">
                            <?php
                            $qR = $con->query("SELECT IDADM_ROL, ROL, DESCRIPCION FROM ADM_ROL WHERE IDADM_ROL > 1 ORDER BY IDADM_ROL");
                            while ($r = mysqli_fetch_array($qR)) {
                                echo '<option value="'.$r['IDADM_ROL'].'">'.htmlspecialchars($r['ROL']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
var usuariosData = {};

function cargarEmpresa(e) {
    document.getElementById('editIdEmpresa').value       = e.ID_EMPRESA;
    document.getElementById('editNombreEmpresa').value   = e.NOMBRE;
    document.getElementById('editRucEmpresa').value      = e.RUC || '';
    document.getElementById('editEmailEmpresa').value    = e.EMAIL_CONTACTO || '';
    document.getElementById('editTelefonoEmpresa').value = e.TELEFONO || '';
    document.getElementById('editPlanEmpresa').value     = e.PLAN;
    document.getElementById('editEstadoEmpresa').value   = e.ESTADO;
    ['soporte','inventario','correos','ventas','reportes'].forEach(function(m) {
        var key = 'MOD_' + m.toUpperCase();
        document.getElementById('edit_mod_' + m).checked = e[key] == '1';
    });
}

function cargarUsuarios(idEmpresa, nombreEmpresa) {
    document.getElementById('lblEmpresaUsuarios').textContent = nombreEmpresa;
    document.getElementById('nuevoUsuarioIdEmpresa').value = idEmpresa;
    document.getElementById('panelUsuarios').style.display = 'block';

    fetch('class/MT_GetUsuarios.php?empresa=' + idEmpresa)
        .then(r => r.json())
        .then(data => {
            var tbody = document.getElementById('bodyUsuariosEmpresa');
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sin usuarios.</td></tr>';
                return;
            }
            tbody.innerHTML = data.map(function(u) {
                var estadoBadge = u.ESTADO === 'A'
                    ? '<span style="background:#28a745;color:#fff;padding:2px 8px;border-radius:10px;font-size:.78em;">Activo</span>'
                    : '<span style="background:#dc3545;color:#fff;padding:2px 8px;border-radius:10px;font-size:.78em;">Inactivo</span>';
                return '<tr><td>'+u.NOMBRES+' '+u.APELLIDOS+'</td><td>'+u.USUARIO+'</td><td>'+u.ROL+'</td><td>'+(u.TELEFONO||'-')+'</td><td>'+estadoBadge+'</td></tr>';
            }).join('');
        });

    document.getElementById('panelUsuarios').scrollIntoView({behavior:'smooth'});
}
</script>
</body>
</html>
