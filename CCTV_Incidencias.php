<!doctype html>
<html lang="en">
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
    <title>CCTV - Incidencias</title>
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
        <div class="app-header__mobile-menu">
            <div><button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box"><span class="hamburger-inner"></span></span>
            </button></div>
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
                            <div class="page-title-icon"><i class="pe-7s-tools icon-gradient bg-warm-flame"></i></div>
                            <div>
                                <div class="page-title-subheading text-black">CCTV - Incidencias</div>
                                <p class="text-dark">Seguimiento de fallas, reparaciones y reemplazos.</p>
                            </div>
                            <button type="button" class="btn btn-danger ms-auto" data-bs-toggle="modal" data-bs-target="#modalNuevaIncidencia">
                                <i class="bi bi-exclamation-triangle"></i> Registrar Incidencia
                            </button>
                        </div>
                    </div>
                </div>

                <?php
                $filtroEquipo = isset($_GET['equipo']) ? $_GET['equipo'] : '';
                $sql = "SELECT I.*,
                            C.MARCA AS CAM_MARCA, C.MODELO AS CAM_MODELO, C.UBICACION AS CAM_UBICACION,
                            D.MARCA AS DVR_MARCA, D.MODELO AS DVR_MODELO, D.TIPO AS DVR_TIPO, D.UBICACION AS DVR_UBICACION
                        FROM CCTV_INCIDENCIA I
                        LEFT JOIN CCTV_CAMARA C ON I.ID_CAMARA = C.ID_CAMARA
                        LEFT JOIN CCTV_DVR D ON I.ID_DVR = D.ID_DVR
                        ORDER BY I.ESTADO ASC, I.FECHA DESC";
                $q = $conexion->query($sql);
                ?>

                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr><th>FECHA</th><th>EQUIPO</th><th>TIPO</th><th>DESCRIPCIÓN</th><th>EVIDENCIAS</th><th>ESTADO</th><th>ACCIONES</th></tr>
                                </thead>
                                <tbody>
                                <?php if (mysqli_num_rows($q) === 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Sin incidencias registradas.</td></tr>
                                <?php else: while ($i = mysqli_fetch_assoc($q)):
                                    if ($i['ID_CAMARA']) {
                                        $equipoLabel = 'Cámara: ' . trim($i['CAM_MARCA'].' '.$i['CAM_MODELO']) . ' (' . ($i['CAM_UBICACION'] ?: 's/u') . ')';
                                    } elseif ($i['ID_DVR']) {
                                        $equipoLabel = $i['DVR_TIPO'] . ': ' . trim($i['DVR_MARCA'].' '.$i['DVR_MODELO']) . ' (' . ($i['DVR_UBICACION'] ?: 's/u') . ')';
                                    } else {
                                        $equipoLabel = '-';
                                    }
                                    $badgeTipo = ['Falla'=>'bg-danger','Reparacion'=>'bg-warning text-dark','Reemplazo'=>'bg-info text-dark','Mantenimiento'=>'bg-secondary'];
                                ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($i['FECHA'])); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($equipoLabel); ?></td>
                                        <td><span class="badge <?php echo $badgeTipo[$i['TIPO']] ?? 'bg-secondary'; ?>"><?php echo htmlspecialchars($i['TIPO']); ?></span></td>
                                        <td class="small"><?php echo htmlspecialchars($i['DESCRIPCION']); ?></td>
                                        <td>
                                            <?php
                                            $evids = !empty($i['EVIDENCIAS']) ? explode(',', $i['EVIDENCIAS']) : [];
                                            foreach ($evids as $ev): ?>
                                                <img src="<?php echo htmlspecialchars($ev); ?>" style="width:34px;height:34px;object-fit:cover;border-radius:5px;cursor:pointer;margin-right:3px;"
                                                     onclick="verImagenCctv('<?php echo htmlspecialchars($ev); ?>')">
                                            <?php endforeach; ?>
                                            <?php if (empty($evids)): echo '<span class="text-muted small">-</span>'; endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($i['ESTADO'] === 'Abierta'): ?>
                                                <span class="badge bg-danger">Abierta</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Resuelta</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($i['ESTADO'] === 'Abierta'): ?>
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalCerrarIncidencia"
                                                onclick="cargarCierre(<?php echo $i['ID_INCIDENCIA']; ?>)">
                                                <i class="bi bi-check-lg"></i> Resolver
                                            </button>
                                            <?php else: ?>
                                                <span class="small text-muted"><?php echo htmlspecialchars($i['SOLUCION']); ?></span>
                                            <?php endif; ?>
                                        </td>
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

<!-- Modal Nueva Incidencia -->
<div class="modal fade" id="modalNuevaIncidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar Incidencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/Insert_Incidencia.php" enctype="multipart/form-data">
                    <div class="mb-2">
                        <label class="form-label">Equipo afectado</label>
                        <select class="form-select" name="equipo" id="selEquipoIncidencia" required>
                            <option value="">Seleccione...</option>
                            <optgroup label="Cámaras">
                                <?php
                                $qc = $conexion->query("SELECT ID_CAMARA, MARCA, MODELO, UBICACION FROM CCTV_CAMARA WHERE ESTADO = 'A' ORDER BY UBICACION, MARCA");
                                while ($cam = mysqli_fetch_assoc($qc)) {
                                    $label = trim($cam['MARCA'].' '.$cam['MODELO']).' ('.$cam['UBICACION'].')';
                                    echo '<option value="camara-'.$cam['ID_CAMARA'].'">'.htmlspecialchars($label).'</option>';
                                }
                                ?>
                            </optgroup>
                            <optgroup label="DVR / NVR">
                                <?php
                                $qd = $conexion->query("SELECT ID_DVR, TIPO, MARCA, MODELO, UBICACION FROM CCTV_DVR WHERE ESTADO = 'A' ORDER BY UBICACION, MARCA");
                                while ($dv = mysqli_fetch_assoc($qd)) {
                                    $label = $dv['TIPO'].': '.trim($dv['MARCA'].' '.$dv['MODELO']).' ('.$dv['UBICACION'].')';
                                    echo '<option value="dvr-'.$dv['ID_DVR'].'">'.htmlspecialchars($label).'</option>';
                                }
                                ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo" required>
                            <option value="Falla">Falla</option>
                            <option value="Reparacion">Reparación</option>
                            <option value="Reemplazo">Reemplazo</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Evidencias (fotos del fallo)</label>
                        <input type="file" class="form-control" name="evidencias[]" multiple accept="image/*">
                    </div>
                    <div class="text-end"><button type="submit" class="btn btn-danger">Registrar</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cerrar Incidencia -->
<div class="modal fade" id="modalCerrarIncidencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Resolver Incidencia</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/Cerrar_Incidencia.php">
                    <input type="hidden" name="idIncidencia" id="idIncidenciaCerrar">
                    <div class="mb-2">
                        <label class="form-label">Solución aplicada</label>
                        <textarea class="form-control" name="solucion" rows="3" required></textarea>
                    </div>
                    <div class="text-end"><button type="submit" class="btn btn-success">Marcar como Resuelta</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cargarCierre(id) {
    document.getElementById('idIncidenciaCerrar').value = id;
}
function verImagenCctv(src) {
    var overlay = document.createElement('div');
    overlay.style.cssText = 'display:flex;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.85);z-index:99999;cursor:zoom-out;align-items:center;justify-content:center;';
    overlay.onclick = function() { overlay.remove(); };
    var img = document.createElement('img');
    img.src = src;
    img.style.cssText = 'width:80vw;max-width:900px;height:80vh;object-fit:contain;border-radius:6px;';
    overlay.appendChild(img);
    document.body.appendChild(overlay);
}
</script>
</body>
</html>
<?php ob_end_flush(); ?>
