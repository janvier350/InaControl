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
    <title>CCTV - Cámaras</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="./main.css" rel="stylesheet">
    <style>
        @media print {
            .no-print, .app-sidebar, .app-header, .nav-tabs, .app-page-title, #tab-registrar, th.no-print, td.no-print { display: none !important; }
            #tab-listado { display: block !important; opacity: 1 !important; }
            .app-main__outer { padding-left: 0 !important; }
            .print-header { display: block !important; }
            body { background: #fff !important; }
        }
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
                            <div class="page-title-icon"><i class="pe-7s-camera icon-gradient bg-warm-flame"></i></div>
                            <div>
                                <div class="page-title-subheading text-black">CCTV - Cámaras</div>
                                <p class="text-dark">Registro de cámaras IP, PTZ y análogas.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-registrar">Registrar</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-listado">Listado</a></li>
                </ul>
                <div class="tab-content pt-3">
                    <!-- Registrar -->
                    <div class="tab-pane fade show active" id="tab-registrar">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <form method="POST" action="class/Insert_Camara.php" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Tipo de cámara</label>
                                            <select class="form-select" name="tipoCamara" required>
                                                <option value="IP">IP</option>
                                                <option value="PTZ">PTZ</option>
                                                <option value="Analoga">Análoga</option>
                                                <option value="Domo">Domo</option>
                                                <option value="Bullet">Bullet</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Marca</label>
                                            <input type="text" class="form-control" name="marca" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Modelo</label>
                                            <input type="text" class="form-control" name="modelo">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Conectada a (DVR / NVR)</label>
                                            <select class="form-select" name="idDvr">
                                                <option value="">Independiente / Sin DVR</option>
                                                <?php
                                                $qd = $conexion->query("SELECT ID_DVR, TIPO, MARCA, MODELO, UBICACION FROM CCTV_DVR WHERE ESTADO = 'A' ORDER BY UBICACION, MARCA");
                                                while ($dv = mysqli_fetch_assoc($qd)) {
                                                    $label = $dv['TIPO'].' '.$dv['MARCA'].' '.$dv['MODELO'].' ('.$dv['UBICACION'].')';
                                                    echo '<option value="'.$dv['ID_DVR'].'">'.htmlspecialchars($label).'</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">IP</label>
                                            <input type="text" class="form-control" name="ip" placeholder="192.168.1.x">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Número de serie</label>
                                            <input type="text" class="form-control" name="numeroSerie">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Usuario</label>
                                            <input type="text" class="form-control" name="usuario">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Clave</label>
                                            <input type="text" class="form-control" name="clave">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Clave HikConnect</label>
                                            <input type="text" class="form-control" name="claveHikconnect">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Ubicación / área cubierta</label>
                                            <input type="text" class="form-control" name="ubicacion" placeholder="Bodega, Entrada principal...">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Fecha de compra</label>
                                            <input type="date" class="form-control" name="fechaCompra">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" name="compartidaHikconnect" id="compartidaHikconnect" value="1">
                                                <label class="form-check-label" for="compartidaHikconnect">
                                                    <i class="bi bi-share-fill text-success"></i> Compartida vía HikConnect con proveedor de seguridad
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Foto (ubicación / estado de la cámara)</label>
                                            <input type="file" class="form-control" name="foto" accept="image/*">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Captura de vista (lo que apunta la cámara)</label>
                                            <input type="file" class="form-control" name="capturaVista" accept="image/*">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Observación</label>
                                            <textarea class="form-control" name="observacion" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Listado -->
                    <div class="tab-pane fade" id="tab-listado">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <div class="row g-2 mb-3 no-print">
                                    <div class="col-md-4">
                                        <input type="text" id="buscadorCam" class="form-control" placeholder="Buscar por marca, IP o ubicación..." onkeyup="filtrarCamaras()">
                                    </div>
                                    <div class="col-md-3">
                                        <select id="filtroDvrCam" class="form-select" onchange="filtrarCamaras()">
                                            <option value="">Todos los DVR/NVR</option>
                                            <?php
                                            $qd2 = $conexion->query("SELECT ID_DVR, TIPO, MARCA, UBICACION FROM CCTV_DVR WHERE ESTADO = 'A' ORDER BY UBICACION");
                                            while ($dv2 = mysqli_fetch_assoc($qd2)) {
                                                echo '<option value="'.$dv2['ID_DVR'].'">'.htmlspecialchars($dv2['TIPO'].' '.$dv2['MARCA'].' ('.$dv2['UBICACION'].')').'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="filtroTipoCam" class="form-select" onchange="filtrarCamaras()">
                                            <option value="">Todos los tipos</option>
                                            <option value="IP">IP</option>
                                            <option value="PTZ">PTZ</option>
                                            <option value="Analoga">Análoga</option>
                                            <option value="Domo">Domo</option>
                                            <option value="Bullet">Bullet</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex gap-1">
                                        <a class="btn btn-success flex-fill" href="class/CCTV_Export_Camaras.php" title="Exportar Excel">
                                            <i class="bi bi-file-earmark-excel"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger flex-fill" onclick="window.print()" title="Exportar PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="print-header mb-3" style="display:none;">
                                    <h4>Reporte de Cámaras CCTV</h4>
                                    <p>Generado: <?php echo date('d/m/Y H:i'); ?></p>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tablaCamaras">
                                        <thead>
                                            <tr>
                                                <th>FOTO</th><th>TIPO</th><th>MARCA / MODELO</th><th>IP</th>
                                                <th>DVR/NVR</th><th>UBICACIÓN</th><th>ESTADO</th><th class="no-print">ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $q = $conexion->query(
                                            "SELECT C.*, D.TIPO AS DVR_TIPO, D.MARCA AS DVR_MARCA, D.UBICACION AS DVR_UBICACION
                                             FROM CCTV_CAMARA C
                                             LEFT JOIN CCTV_DVR D ON C.ID_DVR = D.ID_DVR
                                             WHERE C.ESTADO = 'A' ORDER BY C.UBICACION, C.MARCA"
                                        );
                                        while ($c = mysqli_fetch_assoc($q)):
                                            $foto = !empty($c['FOTO']) ? $c['FOTO'] : 'https://mdbootstrap.com/img/new/avatars/8.jpg';
                                            $dvrLabel = $c['ID_DVR'] ? trim($c['DVR_TIPO'].' '.$c['DVR_MARCA'].' ('.$c['DVR_UBICACION'].')') : 'Independiente';
                                            $filtro = strtolower($c['MARCA'].' '.$c['MODELO'].' '.$c['IP'].' '.$c['UBICACION']);
                                        ?>
                                            <tr data-filtro="<?php echo htmlspecialchars($filtro); ?>" data-dvr="<?php echo (int)$c['ID_DVR']; ?>" data-tipo="<?php echo htmlspecialchars($c['TIPO_CAMARA']); ?>">
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($foto); ?>" title="Ubicación" style="width:44px;height:44px;object-fit:cover;border-radius:6px;cursor:pointer;" onclick="verImagenCam('<?php echo htmlspecialchars($foto); ?>')">
                                                    <?php if (!empty($c['CAPTURA_VISTA'])): ?>
                                                    <img src="<?php echo htmlspecialchars($c['CAPTURA_VISTA']); ?>" title="Vista de la cámara" style="width:44px;height:44px;object-fit:cover;border-radius:6px;cursor:pointer;margin-left:3px;" onclick="verImagenCam('<?php echo htmlspecialchars($c['CAPTURA_VISTA']); ?>')">
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($c['TIPO_CAMARA']); ?></span></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($c['MARCA']); ?></strong>
                                                    <?php if (!empty($c['COMPARTIDA_HIKCONNECT'])): ?>
                                                        <i class="bi bi-share-fill text-success" title="Compartida vía HikConnect con proveedor de seguridad"></i>
                                                    <?php endif; ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($c['MODELO']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($c['IP'] ?: '-'); ?></td>
                                                <td class="small"><?php echo htmlspecialchars($dvrLabel); ?></td>
                                                <td><?php echo htmlspecialchars($c['UBICACION'] ?: '-'); ?></td>
                                                <td><span class="badge bg-success">Activa</span></td>
                                                <td class="no-print">
                                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditarCam"
                                                        onclick='cargarCamara(<?php echo json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
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
    </div>
</div>

<!-- Modal editar cámara -->
<div class="modal fade" id="modalEditarCam" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar Cámara</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/Editar_Camara.php" enctype="multipart/form-data">
                    <input type="hidden" name="idCamara" id="editIdCamara">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipoCamara" id="editTipoCamara">
                                <option value="IP">IP</option>
                                <option value="PTZ">PTZ</option>
                                <option value="Analoga">Análoga</option>
                                <option value="Domo">Domo</option>
                                <option value="Bullet">Bullet</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3"><label class="form-label">Marca</label><input class="form-control" name="marca" id="editMarcaCam" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Modelo</label><input class="form-control" name="modelo" id="editModeloCam"></div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">DVR / NVR</label>
                            <select class="form-select" name="idDvr" id="editIdDvrCam">
                                <option value="">Independiente / Sin DVR</option>
                                <?php
                                $qd3 = $conexion->query("SELECT ID_DVR, TIPO, MARCA, MODELO, UBICACION FROM CCTV_DVR WHERE ESTADO = 'A' ORDER BY UBICACION, MARCA");
                                while ($dv3 = mysqli_fetch_assoc($qd3)) {
                                    $label = $dv3['TIPO'].' '.$dv3['MARCA'].' '.$dv3['MODELO'].' ('.$dv3['UBICACION'].')';
                                    echo '<option value="'.$dv3['ID_DVR'].'">'.htmlspecialchars($label).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">IP</label><input class="form-control" name="ip" id="editIpCam"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Serie</label><input class="form-control" name="numeroSerie" id="editNumeroSerieCam"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Usuario</label><input class="form-control" name="usuario" id="editUsuarioCam"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Clave</label><input class="form-control" name="clave" id="editClaveCam"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Clave HikConnect</label><input class="form-control" name="claveHikconnect" id="editClaveHikconnectCam"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Ubicación</label><input class="form-control" name="ubicacion" id="editUbicacionCam"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Fecha compra</label><input type="date" class="form-control" name="fechaCompra" id="editFechaCompraCam"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="compartidaHikconnect" id="editCompartidaHikconnect" value="1">
                                <label class="form-check-label" for="editCompartidaHikconnect">
                                    <i class="bi bi-share-fill text-success"></i> Compartida vía HikConnect con proveedor de seguridad
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Foto de ubicación</label>
                            <div class="mb-2">
                                <img id="editFotoPreview" src="" style="width:90px;height:90px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;display:none;cursor:pointer;" onclick="verImagenCam(this.src)">
                                <div id="editFotoVacio" class="text-muted small">Sin foto registrada.</div>
                            </div>
                            <label class="form-label">Reemplazar (opcional)</label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Captura de vista</label>
                            <div class="mb-2">
                                <img id="editCapturaPreview" src="" style="width:90px;height:90px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;display:none;cursor:pointer;" onclick="verImagenCam(this.src)">
                                <div id="editCapturaVacio" class="text-muted small">Sin captura registrada.</div>
                            </div>
                            <label class="form-label">Reemplazar (opcional)</label>
                            <input type="file" class="form-control" name="capturaVista" accept="image/*">
                        </div>
                        <div class="col-md-4 mb-3"><label class="form-label">Observación</label><textarea class="form-control" name="observacion" id="editObservacionCam" rows="2"></textarea></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado" id="editEstadoCam">
                            <option value="A">Activa</option>
                            <option value="I">Inactiva / Dada de baja</option>
                        </select>
                    </div>
                    <div class="text-end"><button type="submit" class="btn btn-primary">Guardar cambios</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cargarCamara(c) {
    document.getElementById('editIdCamara').value = c.ID_CAMARA;
    document.getElementById('editTipoCamara').value = c.TIPO_CAMARA || 'IP';
    document.getElementById('editMarcaCam').value = c.MARCA || '';
    document.getElementById('editModeloCam').value = c.MODELO || '';
    document.getElementById('editIdDvrCam').value = c.ID_DVR || '';
    document.getElementById('editIpCam').value = c.IP || '';
    document.getElementById('editNumeroSerieCam').value = c.NUMERO_SERIE || '';
    document.getElementById('editUsuarioCam').value = c.USUARIO || '';
    document.getElementById('editClaveCam').value = c.CLAVE || '';
    document.getElementById('editClaveHikconnectCam').value = c.CLAVE_HIKCONNECT || '';
    document.getElementById('editUbicacionCam').value = c.UBICACION || '';
    document.getElementById('editFechaCompraCam').value = c.FECHA_COMPRA || '';
    document.getElementById('editObservacionCam').value = c.OBSERVACION || '';
    document.getElementById('editEstadoCam').value = c.ESTADO || 'A';
    document.getElementById('editCompartidaHikconnect').checked = c.COMPARTIDA_HIKCONNECT == 1;

    const fotoImg = document.getElementById('editFotoPreview');
    const fotoVacio = document.getElementById('editFotoVacio');
    if (c.FOTO) { fotoImg.src = c.FOTO; fotoImg.style.display = 'inline-block'; fotoVacio.style.display = 'none'; }
    else { fotoImg.style.display = 'none'; fotoVacio.style.display = 'block'; }

    const capturaImg = document.getElementById('editCapturaPreview');
    const capturaVacio = document.getElementById('editCapturaVacio');
    if (c.CAPTURA_VISTA) { capturaImg.src = c.CAPTURA_VISTA; capturaImg.style.display = 'inline-block'; capturaVacio.style.display = 'none'; }
    else { capturaImg.style.display = 'none'; capturaVacio.style.display = 'block'; }
}
function filtrarCamaras() {
    const texto = document.getElementById('buscadorCam').value.toLowerCase();
    const dvr = document.getElementById('filtroDvrCam').value;
    const tipo = document.getElementById('filtroTipoCam').value;
    document.querySelectorAll('#tablaCamaras tbody tr').forEach(function(fila) {
        const okTexto = fila.dataset.filtro.indexOf(texto) !== -1;
        const okDvr = !dvr || fila.dataset.dvr === dvr;
        const okTipo = !tipo || fila.dataset.tipo === tipo;
        fila.style.display = (okTexto && okDvr && okTipo) ? '' : 'none';
    });
}
function verImagenCam(src) {
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
