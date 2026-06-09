<?php
ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD.php");
$conexion = conectarse();

// Verificación de sesión y tiempo de expiración
if(!isset($_SESSION["rol"])){
    header("Location: break.php");
}else {
    $now = time();
    if ($now > $_SESSION['expire']) {
        session_destroy();
        header("Location: expirada.php");
    }
}

// Consulta para obtener las citas
$query = "SELECT 
            A.ID_CALENDARIO_SOPORTE, 
            CONCAT(B.NOMBRES, ' ', B.APELLIDOS, ' ', B.RAZON_SOCIAL) AS CLIENTE,
            C.SOPORTE AS TIPO_SOPORTE,
            A.FECHA_SOPORTE, 
            A.HORA_INICIO, 
            A.HORA_FIN,
            A.ESTADO_SOPORTE,
            A.COMENTARIO,
            CONCAT(D.NOMBRES, ' ', D.APELLIDOS) AS TECNICO
          FROM COTI_CALENDARIO A 
          INNER JOIN COTI_CLIENTE B ON A.ID_CLIENTE = B.ID_CLIENTE 
          INNER JOIN COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
          INNER JOIN ADM_USUARIO D ON A.ID_USUARIO = D.IDADM_USUARIO";

$resultado = $conexion->query($query);

$eventos = array();

while ($row = $resultado->fetch_assoc()) {
    // Determinar color según estado
    switch($row['ESTADO_SOPORTE']) {
        case 'Confirmada':
            $color = '#28a745'; // Verde
            break;
        case 'Pendiente':
            $color = '#ffc107'; // Amarillo
            break;
        case 'Facturado':
            $color = '#dc3545'; // rojo
            break;
        case 'Cobrado':
            $color = '#1212F2'; // Azul
            break;
        case 'Atrasado':
            $color = '#17a2b8'; // Celeste
            break;
        case 'Cancelado':
            $color = '#6c757d'; // Gris
            break;
        default:
            $color = '#adb5bd'; // Gris claro por defecto
    }
    
    $eventos[] = array(
        'id' => $row['ID_CALENDARIO_SOPORTE'],
        'title' => $row['CLIENTE'] ,
        'start' => $row['FECHA_SOPORTE'] . 'T' . $row['HORA_INICIO'],
        'end' => $row['FECHA_SOPORTE'] . 'T' . $row['HORA_FIN'],
        'doctor' => $row['TECNICO'],
        'description' => 'Estado: ' . $row['ESTADO_SOPORTE'] . ' - ' . $row['COMENTARIO'],
        'backgroundColor' => $color, // Fondo del evento con el color del estado
        'borderColor' => $color, // Borde del evento con el mismo color
        'textColor' => '#ffffff', // Texto en blanco para contraste
        'classNames' => ['customizable-event'],
        'extendedProps' => array(
            'cita' => $row['ESTADO_SOPORTE'],
            'tecnico' => $row['TECNICO'] ,
            'comentario' => $row['COMENTARIO'] ,
            'consulta' => $row['TIPO_SOPORTE'] 
        )
    );
}



?>
<!doctype html>
<html lang="es">


<head>
     <meta charset="utf-8">
  <title>Calendario de citas programas.</title>

  <!-- Meta -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="./fullcalendar/main.css" rel="stylesheet">
  <link href="./main.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

  <!-- JS necesarios antes de tus scripts -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="./js/jquery.min.js"></script>

 
    
    <style>
  /* Estilo base para los eventos del calendario */
  .fc-event {
    background-color: #8FAADC    !important; /* Morado pastel (puedes cambiarlo) */
    border: none !important;
    color: #fff !important;
    font-weight: bold;
    border-radius: 8px;
    padding: 2px 6px;
  }

  /* Al pasar el mouse por encima del evento */
  .fc-event:hover {
    background-color: #fcbf08 !important; /* Un poco más claro */
    cursor: pointer;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
  }
</style>

</head>

<body>


<div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">
        
        
        <div class="app-header header-shadow">
            <div class="app-header__logo">
                                <div class="logo-src"></div>
                                <div class="header__pane ml-auto">
                                    <div>
                                        <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                                            <span class="hamburger-box">
                                                <span class="hamburger-inner"></span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
            </div>
            <div class="app-header__mobile-menu">
                        <div>
                            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                                <span class="hamburger-box">
                                    <span class="hamburger-inner"></span>
                                </span>
                            </button>
                        </div>
            </div>
            <div class="app-header__menu">
                <span>
                    <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                        <span class="btn-icon-wrapper">
                            <i class="fa fa-ellipsis-v fa-w-6"></i>
                        </span>
                    </button>
                </span>
            </div>    
            <div class="app-header__content">
                        <div class="app-header-left">
                         
                            <ul class="header-menu nav">
                                <li class="nav-item">
                                    <a href="javascript:void(0);" class="nav-link">
                                        <i class="nav-link-icon fa fa-database"> </i>
                                        Estadistica
                                    </a>
                                </li>
                                <li class="dropdown nav-item">
                                    <a href="javascript:void(0);" class="nav-link">
                                        <i class="nav-link-icon fa fa-cog"></i>
                                        Configuracion
                                    </a>
                                </li>
                            </ul>        
                        </div>
                        <div class="app-header-right">
                            <div class="header-btn-lg pr-0">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="btn-group">
                                                <!--<a href="#" class="btn p-0 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">-->
                                                <a data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                                    <img width="42" class="rounded-circle" >
                                                    <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                                </a>
                                                <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                                    <button type="button" tabindex="0" class="dropdown-item">Perfil de Usuario</button>
                                                    <button type="button" tabindex="0" class="dropdown-item">Configuración</button>
                                                    <div tabindex="-1" class="dropdown-divider"></div>
                                                    <a type="button" tabindex="0" href="salir.php" class="dropdown-item">Cerrar Sesión</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="widget-content-left  ml-3 header-user-info">
                                            <div class="widget-heading">
                                               Admin <?php echo $_SESSION["username"]?>
                                            </div>
                                            <div class="widget-subheading">
                                                Administrator - <?php echo $startDate?>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>        
                        </div>
            </div>
        </div>
        <div class="ui-theme-settings" style="visibility:hidden">
                    <button type="button" id="TooltipDemo" class="btn-open-options btn btn-warning">
                        <i class="fa fa-cog fa-w-16 fa-spin fa-2x"></i>
                    </button>
                    <div class="theme-settings__inner">
                        <div class="scrollbar-container">
                            <div class="theme-settings__options-wrapper">
                               
                                
                                <h3 class="themeoptions-heading">
                                    <div>
                                        Cabecera
                                    </div>
                                    <button type="button" class="btn-pill btn-shadow btn-wide ml-auto btn btn-focus btn-sm switch-header-cs-class" data-class="">
                                        Restablecer
                                    </button>
                                </h3>
                                
                                <h3 class="themeoptions-heading">
                                    <div>Menu</div>
                                    <button type="button" class="btn-pill btn-shadow btn-wide ml-auto btn btn-focus btn-sm switch-sidebar-cs-class" data-class="">
                                        Restablecer
                                    </button>
                                </h3>  
                                <!-- Button trigger modal -->
                                
                                              
                                
                            </div>
                        </div>
                    </div>
        </div> 

        <div class="app-main">
            <div class="app-sidebar sidebar-shadow">
            <!-- ======= MENU========================  -->
            <?php include("./menu/menu_cotizador.php"); ?>          
                
                </div>    
                <div class="app-main__outer">
                    <div class="app-main__inner">
                        <div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-date icon-gradient bg-warm-flame">
                                        </i>
                                    </div>
                                    <div>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editModal">
                                Agendar Cita
                                </button> 
                                        <div class="page-title-subheading text-danger">Soportes agendados.
                                        <p class="text-dark">Hola, <?php echo $_SESSION["username"]?> no olvides de registar todos los soportes.</p>
                                        </div>
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
    
    
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Ampliado para mejor visibilidad -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Agendar Soporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="insertCita" method="POST" action="class/Insert_Soporte.php">
                    
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fechaSoporte" id="fechaSoporte">
                        <script>
                            document.getElementById('fechaSoporte').value = new Date().toISOString().substring(0, 10);
                        </script>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <select class="form-select" name="idCliente" required>
                            <option value="">Seleccione Cliente:</option>
                            <?php
                              $query = $conexion -> query ("SELECT * FROM COTI_CLIENTE WHERE ESTADO = 'A'");
                              while ($valores = mysqli_fetch_array($query)) {
                                echo '<option value="'.$valores['ID_CLIENTE'].'">'.$valores['NOMBRES'].' '.$valores['APELLIDOS'].' '.$valores['RAZON_SOCIAL'].'</option>';
                              }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hora Inicio</label>
                        <input type="time" class="form-control" name="timeIni" max="22:00" min="07:00" step="1800" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de Soporte</label>
                        <select class="form-select" name="idSoporte" required>
                            <option value="">Seleccione Tipo Soporte:</option>
                            <?php
                              $query = $conexion -> query ("SELECT * FROM COTI_TIPO_SOPORTE WHERE ESTADO = 'A'");
                              while ($valores = mysqli_fetch_array($query)) {
                                echo '<option value="'.$valores['ID_TIPO_SOPORTE'].'">'.$valores['SOPORTE'].'</option>';
                              }
                            ?>
                        </select> 
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Técnico</label>
                        <select class="form-select" name="idUsuario" required>   
                            <option value="">Seleccione Técnico:</option>                                                         
                            <?php
                              $query = $conexion -> query ("SELECT * FROM ADM_USUARIO WHERE ESTADO = 'A'");
                              while ($valores = mysqli_fetch_array($query)) {
                                echo '<option value="'.$valores['IDADM_USUARIO'].'">'.$valores['NOMBRES'].' '.$valores['APELLIDOS'].'</option>';
                              }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción del problema</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="4" maxlength="500" oninput="actualizarContador()"></textarea>
                        <div class="form-text text-end"><span id="contador">500</span> caracteres disponibles</div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Agendar</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function actualizarContador() {
        const textarea = document.getElementById('comentario');
        const contador = document.getElementById('contador');
        const max = 500;
        const restante = max - textarea.value.length;
        contador.textContent = restante;
    }
</script>



<!-- Modal para citas -->

<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Gestión de Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formEstado" method="POST" action="class/actualizar_estado_soporte.php" onsubmit="return validarFormulario();">
                <div class="modal-body">
                    <div id="eventDetails" class="mb-4"></div>
                     Input oculto para enviar el ID de la cita 
                    <input type="hidden" id="idCita" name="id">
                    <input type="hidden" id="estadoCita" name="estado">
                </div>
                <div class="modal-footer">
                    <button id="btnConfirmar" class="btn btn-success" type="submit" onclick="setEstado('Confirmada')">Confirmar</button>
                    <button id="btnCancelar" class="btn btn-danger" type="submit" onclick="setEstado('Cancelada')">Cancelar</button>
                    <button id="btnFacturar" class="btn btn-warning" type="submit" onclick="setEstado('Facturado')">Facturar</button>
                    <button id="btnCobrado" class="btn btn-primary" type="submit" onclick="setEstado('Cobrado')">Cobrado</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
function updateStatus(nuevoEstado, event) {
    const eventId = event.target.closest('button').dataset.eventId;

    if (confirm(`¿Está seguro de cambiar el estado a ${nuevoEstado}?`)) {
        fetch("class/actualizar_estado.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `id=${eventId}&estado=${encodeURIComponent(nuevoEstado)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Estado actualizado con éxito");
                bootstrap.Modal.getInstance(document.getElementById("eventModal")).hide();
                window.location.href = data.redirect;
            } else {
                alert("Error al actualizar el estado: " + (data.message || "Desconocido"));
            }
        })
        .catch(error => console.error("Error:", error));
    }
}

function validarFormulario() {
    let id = document.getElementById('idCita').value;
    let estado = document.getElementById('estadoCita').value;

    if (!id || !estado) {
        alert("Error: Los datos del formulario no están completos.");
        return false;
    }

    return true;
}

function setEstado(estado) {
    document.getElementById('estadoCita').value = estado;
}

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar1');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        initialView: 'dayGridMonth',
        events: <?php echo json_encode($eventos); ?>,
        
        themeSystem: 'bootstrap5',
        eventClick: function(info) {
            var details = `
                <p><strong><i class="bi bi-person-fill"></i> Cliente:</strong> ${info.event.title}</p>
                <p><strong><i class="bi bi-clock"></i> Inicio:</strong> ${info.event.start.toLocaleString()}</p>
                <p><strong><i class="bi bi-alarm"></i> Fin:</strong> ${info.event.end ? info.event.end.toLocaleString() : '--'}</p>
                <p><strong><i class="bi bi-tools"></i> Tecnico:</strong> ${info.event.extendedProps.tecnico || 'Sin descripción'}</p>
                <p><strong><i class="bi bi-twitch"></i> Tipo Soporte:</strong> ${info.event.extendedProps.consulta || 'Sin descripción'}</p>
                <p><strong><i class="bi bi-triangle-half"></i> Estado:</strong> ${info.event.extendedProps.cita || 'Sin descripción'}</p>
                <p><strong><i class="bi bi-textarea-t"></i> Descripción:</strong> ${info.event.extendedProps.comentario || 'Sin descripción'}</p>
            `;

            document.getElementById('eventDetails').innerHTML = details;
            document.getElementById('idCita').value = info.event.id;
            document.getElementById('estadoCita').value = info.event.extendedProps.cita || 'Pendiente';

            // Asignar ID a botones para updateStatus
            document.getElementById('btnConfirmar').dataset.eventId = info.event.id;
            document.getElementById('btnCancelar').dataset.eventId = info.event.id;

            var modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        },
        eventDidMount: function(info) {
            if (info.event.extendedProps.status === 'done') {
                info.el.style.backgroundColor = 'green';
                info.el.style.color = 'white';
                var dotEl = info.el.getElementsByClassName('fc-event-dot')[0];
                if (dotEl) {
                    dotEl.style.backgroundColor = 'white';
                }
            }
        }
    });

    calendar.render();
});
</script>




</body>
</html>
