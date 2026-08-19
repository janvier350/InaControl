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
    <title>CCTV - DVR / NVR</title>
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
                            <div class="page-title-icon"><i class="pe-7s-video icon-gradient bg-warm-flame"></i></div>
                            <div>
                                <div class="page-title-subheading text-black">CCTV - DVR / NVR</div>
                                <p class="text-dark">Registro de grabadores DVR y NVR de video vigilancia.</p>
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
                                <form method="POST" action="class/Insert_DVR.php" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Tipo</label>
                                            <select class="form-select" name="tipo" required>
                                                <option value="DVR">DVR</option>
                                                <option value="NVR">NVR</option>
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
                                            <label class="form-label">Número de canales</label>
                                            <input type="number" class="form-control" name="canales" min="1">
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
                                            <label class="form-label">Capacidad de disco</label>
                                            <select class="form-select" name="capacidadDisco">
                                                <option value="">Seleccione...</option>
                                                <option value="500 GB">500 GB</option>
                                                <option value="1 TB">1 TB</option>
                                                <option value="2 TB">2 TB</option>
                                                <option value="3 TB">3 TB</option>
                                                <option value="4 TB">4 TB</option>
                                                <option value="6 TB">6 TB</option>
                                                <option value="8 TB">8 TB</option>
                                                <option value="Sin disco">Sin disco</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Clave de acceso (equipo)</label>
                                            <input type="text" class="form-control" name="claveAcceso">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Clave HikConnect</label>
                                            <input type="text" class="form-control" name="claveHikconnect">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Ubicación (rack, oficina, etc.)</label>
                                            <input type="text" class="form-control" name="ubicacion" placeholder="Rack Principal">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Fecha de compra</label>
                                            <input type="date" class="form-control" name="fechaCompra">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Foto (que se aprecie ubicación y equipo)</label>
                                            <input type="file" class="form-control" name="foto" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Observación</label>
                                            <textarea class="form-control" name="observacion" rows="2"></textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Comentario</label>
                                            <textarea class="form-control" name="comentario" rows="2"></textarea>
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
                                <input type="text" id="buscadorDvr" class="form-control mb-3" placeholder="Buscar por marca, modelo, IP o ubicación..." onkeyup="filtrarDvr()">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="tablaDvr">
                                        <thead>
                                            <tr>
                                                <th>FOTO</th><th>TIPO</th><th>MARCA / MODELO</th><th>IP</th>
                                                <th>CANALES</th><th>DISCO</th><th>UBICACIÓN</th><th>SERIE</th><th>ESTADO</th><th>ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $q = $conexion->query("SELECT * FROM CCTV_DVR WHERE ESTADO = 'A' ORDER BY UBICACION, MARCA");
                                        while ($d = mysqli_fetch_assoc($q)):
                                            $foto = !empty($d['FOTO']) ? $d['FOTO'] : 'https://mdbootstrap.com/img/new/avatars/8.jpg';
                                            $filtro = strtolower($d['MARCA'].' '.$d['MODELO'].' '.$d['IP'].' '.$d['UBICACION']);
                                        ?>
                                            <tr data-filtro="<?php echo htmlspecialchars($filtro); ?>">
                                                <td><img src="<?php echo htmlspecialchars($foto); ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;cursor:pointer;" onclick="verImagenDvr('<?php echo htmlspecialchars($foto); ?>')"></td>
                                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($d['TIPO']); ?></span></td>
                                                <td><strong><?php echo htmlspecialchars($d['MARCA']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($d['MODELO']); ?></small></td>
                                                <td><?php echo htmlspecialchars($d['IP'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($d['CANALES'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($d['CAPACIDAD_DISCO'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($d['UBICACION'] ?: '-'); ?></td>
                                                <td class="small"><?php echo htmlspecialchars($d['NUMERO_SERIE'] ?: '-'); ?></td>
                                                <td><span class="badge bg-success">Activo</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditarDvr"
                                                        onclick='cargarDvr(<?php echo json_encode($d, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
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

<!-- Modal editar DVR -->
<div class="modal fade" id="modalEditarDvr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar DVR / NVR</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form method="POST" action="class/Editar_DVR.php" enctype="multipart/form-data">
                    <input type="hidden" name="idDvr" id="editIdDvr">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" name="tipo" id="editTipo">
                                <option value="DVR">DVR</option>
                                <option value="NVR">NVR</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3"><label class="form-label">Marca</label><input class="form-control" name="marca" id="editMarca" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Modelo</label><input class="form-control" name="modelo" id="editModelo"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Canales</label><input type="number" class="form-control" name="canales" id="editCanales"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">IP</label><input class="form-control" name="ip" id="editIp"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Serie</label><input class="form-control" name="numeroSerie" id="editNumeroSerie"></div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Capacidad de disco</label>
                            <select class="form-select" name="capacidadDisco" id="editCapacidadDisco">
                                <option value="">Seleccione...</option>
                                <option value="500 GB">500 GB</option>
                                <option value="1 TB">1 TB</option>
                                <option value="2 TB">2 TB</option>
                                <option value="3 TB">3 TB</option>
                                <option value="4 TB">4 TB</option>
                                <option value="6 TB">6 TB</option>
                                <option value="8 TB">8 TB</option>
                                <option value="Sin disco">Sin disco</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3"><label class="form-label">Clave acceso</label><input class="form-control" name="claveAcceso" id="editClaveAcceso"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">Clave HikConnect</label><input class="form-control" name="claveHikconnect" id="editClaveHikconnect"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Ubicación</label><input class="form-control" name="ubicacion" id="editUbicacion"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Fecha compra</label><input type="date" class="form-control" name="fechaCompra" id="editFechaCompra"></div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nueva foto (opcional)</label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Observación</label><textarea class="form-control" name="observacion" id="editObservacion" rows="2"></textarea></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Comentario</label><textarea class="form-control" name="comentario" id="editComentario" rows="2"></textarea></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado" id="editEstadoDvr">
                            <option value="A">Activo</option>
                            <option value="I">Inactivo / Dado de baja</option>
                        </select>
                    </div>
                    <div class="text-end"><button type="submit" class="btn btn-primary">Guardar cambios</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cargarDvr(d) {
    document.getElementById('editIdDvr').value = d.ID_DVR;
    document.getElementById('editTipo').value = d.TIPO || 'DVR';
    document.getElementById('editMarca').value = d.MARCA || '';
    document.getElementById('editModelo').value = d.MODELO || '';
    document.getElementById('editCanales').value = d.CANALES || '';
    document.getElementById('editCapacidadDisco').value = d.CAPACIDAD_DISCO || '';
    document.getElementById('editIp').value = d.IP || '';
    document.getElementById('editNumeroSerie').value = d.NUMERO_SERIE || '';
    document.getElementById('editClaveAcceso').value = d.CLAVE_ACCESO || '';
    document.getElementById('editClaveHikconnect').value = d.CLAVE_HIKCONNECT || '';
    document.getElementById('editUbicacion').value = d.UBICACION || '';
    document.getElementById('editFechaCompra').value = d.FECHA_COMPRA || '';
    document.getElementById('editObservacion').value = d.OBSERVACION || '';
    document.getElementById('editComentario').value = d.COMENTARIO || '';
    document.getElementById('editEstadoDvr').value = d.ESTADO || 'A';
}
function filtrarDvr() {
    const texto = document.getElementById('buscadorDvr').value.toLowerCase();
    document.querySelectorAll('#tablaDvr tbody tr').forEach(function(fila) {
        fila.style.display = fila.dataset.filtro.indexOf(texto) !== -1 ? '' : 'none';
    });
}
function verImagenDvr(src) {
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
