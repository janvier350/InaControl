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

$rol_usuario  = $_SESSION["rol"];
$idEmpresa    = (int)$_SESSION['id_empresa'];

// Consulta para obtener las citas de ESTA empresa
$query = mysqli_prepare($con, "SELECT
            A.ID_CALENDARIO_SOPORTE,
            CONCAT(B.NOMBRES, ' ', B.APELLIDOS, ' ', IFNULL(B.RAZON_SOCIAL,'')) AS CLIENTE,
            C.SOPORTE AS TIPO_SOPORTE,
            A.ID_SOPORTE,
            A.ID_USUARIO,
            A.FECHA_SOPORTE,
            A.HORA_INICIO,
            A.HORA_FIN,
            A.ESTADO_SOPORTE,
            A.COMENTARIO,
            A.EVIDENCIAS,
            CONCAT(D.NOMBRES, ' ', D.APELLIDOS) AS TECNICO
          FROM COTI_CALENDARIO A
          INNER JOIN COTI_CLIENTE B ON A.ID_CLIENTE = B.ID_CLIENTE
          INNER JOIN COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
          INNER JOIN ADM_USUARIO D ON A.ID_USUARIO = D.IDADM_USUARIO
          WHERE A.ID_EMPRESA = ? AND A.ESTADO = 'A'");
mysqli_stmt_bind_param($query, "i", $idEmpresa);
mysqli_stmt_execute($query);
$resultado = mysqli_stmt_get_result($query);

$eventos = array();

while ($row = mysqli_fetch_assoc($resultado)) {
    switch($row['ESTADO_SOPORTE']) {
        case 'Confirmada': $color = '#81C784'; break;
        case 'Pendiente':  $color = '#FFF176'; break;
        case 'Facturado':  $color = '#FF8A65'; break;
        case 'Cobrado':    $color = '#64B5F6'; break;
        default:           $color = '#B0BEC5';
    }

    $start = !empty($row['HORA_INICIO']) ? $row['FECHA_SOPORTE'] . 'T' . $row['HORA_INICIO'] : $row['FECHA_SOPORTE'];
    $end   = !empty($row['HORA_FIN'])    ? $row['FECHA_SOPORTE'] . 'T' . $row['HORA_FIN']    : null;

    $eventos[] = array(
        'id' => $row['ID_CALENDARIO_SOPORTE'],
        'title' => $row['CLIENTE'],
        'start' => $start,
        'end' => $end,
        'backgroundColor' => $color,
        'borderColor' => $color,
        'classNames' => ['customizable-event'],
        'extendedProps' => array(
            'cita' => $row['ESTADO_SOPORTE'],
            'tecnico' => $row['TECNICO'],
            'idTecnico' => $row['ID_USUARIO'],
            'comentario' => $row['COMENTARIO'],
            'evidencias' => !empty($row['EVIDENCIAS']) ? explode(',', $row['EVIDENCIAS']) : [],
            'consulta' => $row['TIPO_SOPORTE'],
            'idSoporte' => $row['ID_SOPORTE'],
            'fecha' => $row['FECHA_SOPORTE'],
            'horaInicio' => $row['HORA_INICIO'],
            'horaFin' => $row['HORA_FIN']
        )
    );
}
mysqli_stmt_close($query);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kontrol - Calendario de Soportes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./fullcalendar/main.css" rel="stylesheet">
    <link href="./main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/jquery.min.js"></script>
    <style>
        .fc-event { background-color:#C3D4EF !important; border:none !important; color:#000 !important; font-weight:bold; border-radius:8px; padding:2px 6px; }
        .fc-event:hover { background-color:#fcbf08 !important; cursor:pointer; }
        .Confirmada { background:#E6F4EA; color:#2E7D32; border-radius:6px; padding:0 .75em; font-size:.75em; }
        .Pendiente  { background:#FFFDE7; color:#F57F17; border-radius:6px; padding:0 .75em; font-size:.75em; }
        .Cancelada  { background:#ECEFF1; color:#455A64; border-radius:6px; padding:0 .75em; font-size:.75em; }
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
                            <div class="page-title-icon"><i class="pe-7s-date icon-gradient bg-warm-flame"></i></div>
                            <div>
                                <?php if ($rol_usuario == "SISTEMA"): ?>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#editModal">
                                    Abrir Ticket
                                </button>
                                <?php endif; ?>
                                <div class="page-title-subheading text-black">Soportes agendados.
                                    <p class="text-dark">Hola, <?php echo htmlspecialchars($_SESSION["username"]); ?>, no olvides de registrar todos los soportes.</p>
                                </div>
                            </div>
                            <div style="padding: 1em; display: flex; gap: 10px; flex-wrap: wrap;">
                                <h6>Nomenclatura de Estados</h6>
                                <span class="Pendiente">Pendiente</span>
                                <span class="Cancelada">Cancelada</span>
                                <span class="Confirmada">Confirmada</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="tab-pane tabs-animation fade show active" id="tab-content-0" role="tabpanel">
                        <div class="main-card mb-3 card">
                            <div class="card-body">
                                <div id="calendar1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agendar Soporte -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agendar Soporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="class/MT_Insert_Soporte.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fechaSoporte" id="fechaSoporte">
                        <script>document.getElementById('fechaSoporte').value = new Date().toISOString().substring(0, 10);</script>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <div class="input-group">
                            <select class="form-select" name="idCliente" id="selCliente" required>
                                <option value="">Seleccione cliente:</option>
                                <?php
                                  $qC = mysqli_prepare($con, "SELECT ID_CLIENTE, NOMBRES, APELLIDOS, RAZON_SOCIAL FROM COTI_CLIENTE WHERE ID_EMPRESA = ? AND ESTADO = 'A'");
                                  mysqli_stmt_bind_param($qC, "i", $idEmpresa);
                                  mysqli_stmt_execute($qC);
                                  $rC = mysqli_stmt_get_result($qC);
                                  while ($c = mysqli_fetch_assoc($rC)) {
                                      $nom = trim($c['NOMBRES'].' '.$c['APELLIDOS'].' '.$c['RAZON_SOCIAL']);
                                      echo '<option value="'.$c['ID_CLIENTE'].'">'.htmlspecialchars($nom).'</option>';
                                  }
                                  mysqli_stmt_close($qC);
                                ?>
                            </select>
                            <button type="button" class="btn btn-outline-secondary" onclick="agregarClienteRapido()"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora Inicio</label>
                        <input type="time" class="form-control" name="timeIni" max="23:45" min="07:00" step="1800" value="08:00:00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora Fin</label>
                        <input type="time" class="form-control" name="timeFin" max="23:45" min="07:00" step="1800" value="08:00:00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Soporte</label>
                        <div class="input-group">
                            <select class="form-select" name="idSoporte" id="selTipoSoporte" required>
                                <option value="">Seleccione Tipo Soporte:</option>
                                <?php
                                  $qT = mysqli_prepare($con, "SELECT ID_TIPO_SOPORTE, SOPORTE FROM COTI_TIPO_SOPORTE WHERE ID_EMPRESA = ? AND ESTADO = 'A'");
                                  mysqli_stmt_bind_param($qT, "i", $idEmpresa);
                                  mysqli_stmt_execute($qT);
                                  $rT = mysqli_stmt_get_result($qT);
                                  while ($t = mysqli_fetch_assoc($rT)) {
                                      echo '<option value="'.$t['ID_TIPO_SOPORTE'].'">'.htmlspecialchars($t['SOPORTE']).'</option>';
                                  }
                                  mysqli_stmt_close($qT);
                                ?>
                            </select>
                            <button type="button" class="btn btn-outline-secondary" onclick="agregarServicioRapido()"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Técnico</label>
                        <select class="form-select" name="idUsuario" required>
                            <option value="">Seleccione Técnico:</option>
                            <?php
                              $qU = mysqli_prepare($con, "SELECT IDADM_USUARIO, NOMBRES, APELLIDOS FROM ADM_USUARIO WHERE ID_EMPRESA = ? AND ESTADO = 'A'");
                              mysqli_stmt_bind_param($qU, "i", $idEmpresa);
                              mysqli_stmt_execute($qU);
                              $rU = mysqli_stmt_get_result($qU);
                              while ($u = mysqli_fetch_assoc($rU)) {
                                  echo '<option value="'.$u['IDADM_USUARIO'].'">'.htmlspecialchars($u['NOMBRES'].' '.$u['APELLIDOS']).'</option>';
                              }
                              mysqli_stmt_close($qU);
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción del problema</label>
                        <div class="input-group">
                            <textarea class="form-control" name="comentario" id="comentarioSoporte" rows="4" maxlength="1000"></textarea>
                            <button type="button" class="btn btn-outline-secondary" id="btnDictado" title="Dictar por voz">
                                <i class="bi bi-mic-fill"></i>
                            </button>
                        </div>
                        <div class="form-text">Presiona el micrófono para dictar la descripción por voz.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Evidencias (imágenes)</label>
                        <input type="file" class="form-control" name="evidencias[]" multiple accept="image/*">
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Agendar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal quick-add cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="class/MT_Insert_Cliente.php">
                    <div class="mb-2"><label class="form-label">Nombres</label><input class="form-control" name="nombres"></div>
                    <div class="mb-2"><label class="form-label">Apellidos</label><input class="form-control" name="apellidos"></div>
                    <div class="mb-2"><label class="form-label">Razón Social (opcional)</label><input class="form-control" name="razonSocial"></div>
                    <div class="mb-2"><label class="form-label">Email</label><input class="form-control" name="email"></div>
                    <div class="mb-2"><label class="form-label">Teléfono</label><input class="form-control" name="telefono"></div>
                    <div class="text-end"><button type="submit" class="btn btn-success">Guardar</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal quick-add servicio -->
<div class="modal fade" id="modalServicio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2"><label class="form-label">Nombre del servicio</label><input class="form-control" id="nuevoServicioNombre" required></div>
                <div class="mb-2"><label class="form-label">Descripción</label><textarea class="form-control" id="nuevoServicioDescripcion" rows="2"></textarea></div>
                <div class="text-end"><button type="button" class="btn btn-success" onclick="guardarServicioRapido()">Guardar</button></div>
            </div>
        </div>
    </div>
</div>

<script>
function agregarClienteRapido() {
    new bootstrap.Modal(document.getElementById('modalCliente')).show();
}

function agregarServicioRapido() {
    new bootstrap.Modal(document.getElementById('modalServicio')).show();
}

function guardarServicioRapido() {
    const nombre = document.getElementById('nuevoServicioNombre').value.trim();
    const descripcion = document.getElementById('nuevoServicioDescripcion').value.trim();
    if (!nombre) { alert('Ingrese el nombre del servicio.'); return; }

    fetch('class/MT_Insert_TipoSoporte.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ soporte: nombre, descripcion: descripcion, ajax: '1' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('selTipoSoporte');
            const opt = document.createElement('option');
            opt.value = data.id;
            opt.textContent = data.soporte;
            select.appendChild(opt);
            select.value = data.id;
            document.getElementById('nuevoServicioNombre').value = '';
            document.getElementById('nuevoServicioDescripcion').value = '';
            bootstrap.Modal.getInstance(document.getElementById('modalServicio')).hide();
        } else {
            alert('Error: ' + (data.message || 'No se pudo guardar el servicio.'));
        }
    })
    .catch(() => alert('Error de comunicación con el servidor.'));
}

(function() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const btnDictado = document.getElementById('btnDictado');
    if (!SpeechRecognition || !btnDictado) {
        if (btnDictado) btnDictado.style.display = 'none';
        return;
    }
    const recognition = new SpeechRecognition();
    recognition.lang = 'es-ES';
    recognition.continuous = true;
    recognition.interimResults = false;

    let escuchando = false;
    const textarea = document.getElementById('comentarioSoporte');

    recognition.onresult = function(event) {
        let textoNuevo = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
            textoNuevo += event.results[i][0].transcript;
        }
        textarea.value = (textarea.value ? textarea.value + ' ' : '') + textoNuevo.trim();
    };
    recognition.onend = function() {
        escuchando = false;
        btnDictado.classList.remove('btn-danger');
        btnDictado.classList.add('btn-outline-secondary');
    };
    recognition.onerror = function() {
        escuchando = false;
        btnDictado.classList.remove('btn-danger');
        btnDictado.classList.add('btn-outline-secondary');
    };

    btnDictado.addEventListener('click', function() {
        if (escuchando) {
            recognition.stop();
            return;
        }
        escuchando = true;
        btnDictado.classList.remove('btn-outline-secondary');
        btnDictado.classList.add('btn-danger');
        recognition.start();
    });
})();
</script>

<!-- Modal detalle/gestión de cita -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gestión de Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEstado" method="POST" action="class/MT_actualizar_estado_soporte.php" onsubmit="return validarFormulario();">
                <div class="modal-body">
                    <div id="eventDetails" class="mb-4"></div>
                    <div id="eventEditForm" class="mb-4" style="display:none;">
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label">Fecha</label><input type="date" class="form-control" id="editFechaSoporte"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Hora Inicio</label><input type="time" class="form-control" id="editHoraInicio" step="1800"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Hora Fin</label><input type="time" class="form-control" id="editHoraFin" step="1800"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Técnico</label>
                                <select class="form-select" id="editTecnico">
                                    <?php
                                      $qU2 = mysqli_prepare($con, "SELECT IDADM_USUARIO, NOMBRES, APELLIDOS FROM ADM_USUARIO WHERE ID_EMPRESA = ? AND ESTADO = 'A'");
                                      mysqli_stmt_bind_param($qU2, "i", $idEmpresa);
                                      mysqli_stmt_execute($qU2);
                                      $rU2 = mysqli_stmt_get_result($qU2);
                                      while ($u2 = mysqli_fetch_assoc($rU2)) {
                                          echo '<option value="'.$u2['IDADM_USUARIO'].'">'.htmlspecialchars($u2['NOMBRES'].' '.$u2['APELLIDOS']).'</option>';
                                      }
                                      mysqli_stmt_close($qU2);
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Soporte</label>
                                <select class="form-select" id="editTipoSoporte">
                                    <?php
                                      $qT2 = mysqli_prepare($con, "SELECT ID_TIPO_SOPORTE, SOPORTE FROM COTI_TIPO_SOPORTE WHERE ID_EMPRESA = ? AND ESTADO = 'A'");
                                      mysqli_stmt_bind_param($qT2, "i", $idEmpresa);
                                      mysqli_stmt_execute($qT2);
                                      $rT2 = mysqli_stmt_get_result($qT2);
                                      while ($t2 = mysqli_fetch_assoc($rT2)) {
                                          echo '<option value="'.$t2['ID_TIPO_SOPORTE'].'">'.htmlspecialchars($t2['SOPORTE']).'</option>';
                                      }
                                      mysqli_stmt_close($qT2);
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3"><label class="form-label">Descripción</label><textarea class="form-control" id="editComentario" rows="4" maxlength="1000"></textarea></div>
                    </div>
                    <input type="hidden" id="idCita" name="id">
                    <input type="hidden" id="estadoCita" name="estado">
                </div>
                <div class="modal-footer">
                    <?php if ($rol_usuario == "SISTEMA"): ?>
                        <button id="btnConfirmar" class="btn btn-success" type="submit" onclick="setEstado('Confirmada')">Confirmar</button>
                        <button id="btnCancelar" class="btn btn-danger" type="submit" onclick="setEstado('Cancelada')">Cancelar</button>
                        <button id="btnEditarCita" type="button" class="btn btn-outline-primary" onclick="toggleEditCita(true)"><i class="bi bi-pencil-square"></i> Editar</button>
                        <button id="btnGuardarCita" type="button" class="btn btn-primary" style="display:none;" onclick="guardarEdicionCita()"><i class="bi bi-check-lg"></i> Guardar cambios</button>
                        <button id="btnCancelarEdicion" type="button" class="btn btn-outline-secondary" style="display:none;" onclick="toggleEditCita(false)">Cancelar edición</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function validarFormulario() {
    let id = document.getElementById('idCita').value;
    let estado = document.getElementById('estadoCita').value;
    if (!id || !estado) { alert("Error: Los datos del formulario no están completos."); return false; }
    return true;
}
function setEstado(estado) { document.getElementById('estadoCita').value = estado; }

function toggleEditCita(mostrar) {
    document.getElementById('eventDetails').style.display = mostrar ? 'none' : 'block';
    document.getElementById('eventEditForm').style.display = mostrar ? 'block' : 'none';
    const btnEditar = document.getElementById('btnEditarCita');
    const btnGuardar = document.getElementById('btnGuardarCita');
    const btnCancelarEdicion = document.getElementById('btnCancelarEdicion');
    const btnConfirmar = document.getElementById('btnConfirmar');
    const btnCancelar = document.getElementById('btnCancelar');
    if (btnEditar) btnEditar.style.display = mostrar ? 'none' : 'inline-block';
    if (btnGuardar) btnGuardar.style.display = mostrar ? 'inline-block' : 'none';
    if (btnCancelarEdicion) btnCancelarEdicion.style.display = mostrar ? 'inline-block' : 'none';
    if (btnConfirmar) btnConfirmar.style.display = mostrar ? 'none' : 'inline-block';
    if (btnCancelar) btnCancelar.style.display = mostrar ? 'none' : 'inline-block';
}

function guardarEdicionCita() {
    const id = document.getElementById('idCita').value;
    const fechaSoporte = document.getElementById('editFechaSoporte').value;
    const horaInicio = document.getElementById('editHoraInicio').value;
    const horaFin = document.getElementById('editHoraFin').value;
    const idUsuario = document.getElementById('editTecnico').value;
    const idSoporte = document.getElementById('editTipoSoporte').value;
    const comentario = document.getElementById('editComentario').value;
    if (!fechaSoporte || !horaInicio || !horaFin || !idUsuario || !idSoporte) {
        alert('Por favor complete todos los campos antes de guardar.');
        return;
    }
    const btnGuardar = document.getElementById('btnGuardarCita');
    btnGuardar.disabled = true;
    btnGuardar.textContent = 'Guardando...';
    fetch('class/MT_Editar_Soporte.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ id, fechaSoporte, horaInicio, horaFin, idUsuario, idSoporte, comentario })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { alert('Cita actualizada correctamente'); location.reload(); }
        else {
            alert('Error al actualizar la cita: ' + (data.message || 'Desconocido'));
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-check-lg"></i> Guardar cambios';
        }
    })
    .catch(() => {
        alert('Error de comunicación con el servidor.');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-check-lg"></i> Guardar cambios';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar1');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
        initialView: 'dayGridMonth',
        events: <?php echo json_encode($eventos ?? []); ?>,
        themeSystem: 'bootstrap5',
        eventClick: function(info) {
            function nl2br(str) {
                if (!str) return 'Sin descripción';
                return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                          .replace(/\\r\\n/g,'<br>').replace(/\\n/g,'<br>').replace(/\r\n/g,'<br>').replace(/\n/g,'<br>');
            }
            const estadoColors = { 'Confirmada':'bg-success', 'Pendiente':'bg-warning text-dark', 'Cancelada':'bg-danger' };
            const est = info.event.extendedProps.cita || 'Pendiente';
            const badge = `<span class="badge ${estadoColors[est] || 'bg-secondary'}">${est}</span>`;
            const descHtml = nl2br(info.event.extendedProps.comentario);
            const evidencias = info.event.extendedProps.evidencias || [];
            const evidenciasHtml = evidencias.length ? `
                <div class="mt-2"><strong><i class="bi bi-camera"></i> Evidencias:</strong>
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:6px;">
                    ${evidencias.map(src => `<img src="${src}" style="width:80px;height:80px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid #dee2e6;" onclick="verImagenEvidencia('${src}')">`).join('')}
                </div></div>` : '';
            var details = `
                <p><strong><i class="bi bi-person-fill"></i> Cliente:</strong> ${info.event.title}</p>
                <p><strong><i class="bi bi-clock"></i> Inicio:</strong> ${info.event.start.toLocaleString()}</p>
                <p><strong><i class="bi bi-alarm"></i> Fin:</strong> ${info.event.end ? info.event.end.toLocaleString() : '--'}</p>
                <p><strong><i class="bi bi-tools"></i> Técnico:</strong> ${info.event.extendedProps.tecnico || '--'}</p>
                <p><strong><i class="bi bi-wrench-adjustable"></i> Tipo Soporte:</strong> ${info.event.extendedProps.consulta || '--'}</p>
                <p><strong><i class="bi bi-triangle-half"></i> Estado:</strong> ${badge}</p>
                ${descHtml !== 'Sin descripción' ? `<div><strong><i class="bi bi-textarea-t"></i> Descripción:</strong>
                <div style="background:#f8f9fa;border-left:3px solid #1a3a5c;padding:8px 12px;margin-top:4px;border-radius:4px;font-size:0.9rem;line-height:1.6;">${descHtml}</div></div>` : ''}
                ${evidenciasHtml}
            `;
            document.getElementById('eventDetails').innerHTML = details;
            document.getElementById('idCita').value = info.event.id;
            document.getElementById('estadoCita').value = est;
            const props = info.event.extendedProps;
            document.getElementById('editFechaSoporte').value = props.fecha || '';
            document.getElementById('editHoraInicio').value = (props.horaInicio || '').substring(0, 5);
            document.getElementById('editHoraFin').value = (props.horaFin || '').substring(0, 5);
            document.getElementById('editTecnico').value = props.idTecnico || '';
            document.getElementById('editTipoSoporte').value = props.idSoporte || '';
            document.getElementById('editComentario').value = (props.comentario || '').replace(/\\r\\n|\\n/g, '\n');
            toggleEditCita(false);
            new bootstrap.Modal(document.getElementById('eventModal')).show();
        }
    });
    calendar.render();
});

function verImagenEvidencia(src) {
    var overlay = document.createElement('div');
    overlay.style.cssText = 'display:flex;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.8);z-index:99999;cursor:zoom-out;align-items:center;justify-content:center;';
    overlay.onclick = function() { overlay.remove(); };
    var img = document.createElement('img');
    img.src = src;
    img.style.cssText = 'max-width:90%;max-height:90%;border-radius:6px;';
    overlay.appendChild(img);
    document.body.appendChild(overlay);
}
</script>
</body>
</html>
