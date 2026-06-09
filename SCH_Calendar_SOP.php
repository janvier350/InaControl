<?php
ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD.php");
 $conexion = conectarse("utf8");
 $conexion->set_charset("utf8");

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$rol_usuario = $_SESSION["rol"];

$conexion->set_charset("utf8mb4");
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
   // Determinar color según estado con paleta mejorada UX pastel
switch($row['ESTADO_SOPORTE']) {
    case 'Confirmada':
        $color = '#81C784'; // Verde pastel
        break;
    case 'Pendiente':
        $color = '#FFF176'; // Amarillo pastel
        break;
    case 'Facturado':
        $color = '#FF8A65'; // Rojo coral suave
        break;
    case 'Cobrado':
        $color = '#64B5F6'; // Azul pastel
        break;
    default:
        $color = '#B0BEC5'; // Gris lavanda
    }
    
    $start = !empty($row['HORA_INICIO']) ? $row['FECHA_SOPORTE'] . 'T' . $row['HORA_INICIO'] : $row['FECHA_SOPORTE'];
$end = !empty($row['HORA_FIN']) ? $row['FECHA_SOPORTE'] . 'T' . $row['HORA_FIN'] : null;

    $eventos[] = array(
        'id' => $row['ID_CALENDARIO_SOPORTE'],
        'title' => $row['CLIENTE'] ,
        // 'start' => $row['FECHA_SOPORTE'] . 'T' . $row['HORA_INICIO'],
        // 'end' => $row['FECHA_SOPORTE'] . 'T' . $row['HORA_FIN'],
        // Validar y formatear fechas
    'start' => $start,
    'end' => $end,
        'doctor' => $row['TECNICO'],
        'description' => 'Estado: ' . $row['ESTADO_SOPORTE'] . ' - ' . $row['COMENTARIO'],
        'backgroundColor' => $color, // Fondo del evento con el color del estado
        'borderColor' => $color, // Borde del evento con el mismo color
        // 'textColor' => '#ffffff', // Texto en blanco para contraste
        'classNames' => ['customizable-event'],
        'extendedProps' => array(
            'cita' => $row['ESTADO_SOPORTE'],
            'tecnico' => $row['TECNICO'],
            'comentario' => $row['COMENTARIO'],
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
 .fc-event {
    background-color: #C3D4EF !important; /* Lavanda pastel */
    border: none !important;
    color: #000 !important; /* Mejor contraste con fondo claro */
    font-weight: bold;
    border-radius: 8px;
    padding: 2px 6px;
  }

  .fc-event:hover {
    background-color: #fcbf08 !important; /* Hover más oscuro */
    cursor: pointer;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
  }
  
  .Publicado {
  border-top: 1px solid #b2dba1;
  border-bottom: 1px solid #b2dba1;
  background-image: linear-gradient(to bottom, #dff0d8 0px, #c8e5bc 100%);
  background-repeat: repeat-x;
  color: #3c763d;
  border-width: 1px;
  font-size: .75em;
  padding: 0 .75em;
  line-height: 2em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
}
.Atrasado {
  border-top: 1px solid #C0392B;
  border-bottom: 1px solid #C0392B;
  background-image: linear-gradient(to bottom, #E57373 0%, #EF9A9A 100%);
  background-repeat: repeat-x;
  color: #B71C1C;
  border-width: 1px;
  font-size: 0.75em;
  padding: 0 0.75em;
  line-height: 2em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
}

.EnRevision {
  border-top: 1px solid #F39C12  ;
  border-bottom: 1px solid #F39C12  ;
  background-image: linear-gradient(to bottom, #F8C471 0px, #FAD7A0 100%);
  background-repeat: repeat-x;
  color: #B9770E;
  border-width: 1px;
  font-size: .75em;
  padding: 0 .75em;
  line-height: 2em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
}
.Default {
  border-top: 1px solid #D81B60;
  border-bottom: 1px solid #D81B60;
  background-image: linear-gradient(to bottom, #F8BBD0 0px, #F48FB1 100%);
  background-repeat: repeat-x;
  color: #AD1457;
  border-width: 1px;
  font-size: .75em;
  padding: 0 .75em;
  line-height: 2em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
}
/*.Confirmada {*/
/*  border-top: 1px solid #9acfea  ;*/
/*  border-bottom: 1px solid #9acfea  ;*/
/*  background-image: linear-gradient(to bottom, #d9edf7 0px, #b9def0 100%);*/
/*  background-repeat: repeat-x;*/
/*  color: #31708f;*/
/*  border-width: 1px;*/
/*  font-size: .75em;*/
/*  padding: 0 .75em;*/
/*  line-height: 2em;*/
/*  white-space: nowrap;*/
/*  overflow: hidden;*/
/*  text-overflow: ellipsis;*/
/*  margin-bottom: 1px;*/
/*} */
.Confirmada {
  border-top: 1px solid #81C784;
  border-bottom: 1px solid #81C784;
  background-color: #E6F4EA; /* un fondo muy suave para complementar el verde */
  background-image: linear-gradient(to bottom, #D4EDDA 0%, #B2DFDB 100%);
  background-repeat: no-repeat;
  color: #2E7D32; /* un verde oscuro legible */
  border-width: 1px;
  font-size: .75em;
  padding: 0 .75em;
  line-height: 2em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
  border-radius: 6px;
}
/*.Pendiente {*/
/*  border-top: 1px solid #F3F702;*/
/*  border-bottom: 1px solid #F3F702;*/
/*  background-image: linear-gradient(to bottom, #FFFF99 0px, #F3F702 100%);*/
/*  background-repeat: repeat-x;*/
/*  color: #6B6B00;*/
/*  border-width: 1px;*/
/*  font-size: .75em;*/
/*  padding: 0 .75em;*/
/*  line-height: 2em;*/
/*  white-space: nowrap;*/
/*  overflow: hidden;*/
/*  text-overflow: ellipsis;*/
/*  margin-bottom: 1px;*/
/*}*/
.Pendiente {
  border-top: 1px solid #FFF176;
  border-bottom: 1px solid #FFF176;
  background-color: #FFFDE7;
  background-image: linear-gradient(to bottom, #FFF9C4 0%, #f9e37c 100%);
  color: #F57F17;
  border-width: 1px;
  font-size: .75em;
  padding: 0 .75em;
  line-height: 2em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
  border-radius: 6px;
}
.Cobrado {
  border-top: 1px solid #64B5F6;
  border-bottom: 1px solid #64B5F6;
  background-color: #E3F2FD;
  background-image: linear-gradient(to bottom, #BBDEFB 0%, #90CAF9 100%);
  color: #1565C0;
  border-width: 1px;
  font-size: .75em;
  padding: 0 .75em;
  line-height: 2em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
  border-radius: 6px;
}
.Cancelada {
  border-top: 1px solid #B0BEC5;
  border-bottom: 1px solid #B0BEC5;
  background-color: #ECEFF1;
  background-image: linear-gradient(to bottom, #CFD8DC 0%, #B0BEC5 100%);
  color: #455A64;
  border-width: 1px;
  font-size: .75em;
  padding: 0 .75em;
  line-height: 2em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
  border-radius: 6px;
}


/*.Cobrado {*/
/*  border-top: 1px solid #9B59B6;*/
/*  border-bottom: 1px solid #9B59B6;*/
/*  background-image: linear-gradient(to bottom, #DAB6E3 0px, #9B59B6 100%);*/
/*  background-repeat: repeat-x;*/
/*  color: #4A235A;*/
/*  border-width: 1px;*/
/*  font-size: .75em;*/
/*  padding: 0 .75em;*/
/*  line-height: 2em;*/
/*  white-space: nowrap;*/
/*  overflow: hidden;*/
/*  text-overflow: ellipsis;*/
/*  margin-bottom: 1px;*/
/*}*/
/*.Facturado {*/
/*  border-top: 1px solid #E67E22;*/
/*  border-bottom: 1px solid #E67E22;*/
/*  background-image: linear-gradient(to bottom, #FAD7A0 0px, #F39C12 100%);*/
/*  background-repeat: repeat-x;*/
/*  color: #B9770E;*/
/*  border-width: 1px;*/
/*  font-size: .75em;*/
/*  padding: 0 .75em;*/
/*  line-height: 2em;*/
/*  white-space: nowrap;*/
/*  overflow: hidden;*/
/*  text-overflow: ellipsis;*/
/*  margin-bottom: 1px;*/
/*}*/
.Facturado {
  border-top: 1px solid #E57373;
  border-bottom: 1px solid #E57373;
  background-color: #FDECEA;
  background-image: linear-gradient(to bottom, #FFCDD2 0%, #EF9A9A 100%);
  color: #C62828;
  border-width: 1px;
  font-size: .75em;
  padding: 0 .75em;
  line-height: 2em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 1px;
  border-radius: 6px;
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
                                               Admin <?php echo $_SESSION["username"] ?>
                                            </div>
                                            <div class="widget-subheading">
                                                <!--Administrator - <?php echo $startDate ?>-->
                                                
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
            <?php include("./menu/menu_$rol_usuario.php"); ?>          
                
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
                                <!--    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editModal">-->
                                <!--Agendar Cita-->
                                <!--</button> -->
                                
                                <?php
                                    if($rol_usuario == "SISTEMA"){?>
                                        
                                         <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#editModal">
                                Abrir Ticket
                                </button> 
                                    <?php 
                                        
                                    }else{
                                       
                                        
                                    }
                                ?>
                                
                               
                                        <div class="page-title-subheading text-black">Soportes agendados.
                                        <p class="text-dark">Hola, <?php echo $_SESSION["username"]; ?> no olvides de registar todos los soportes.</p>
                                        </div>
                                    </div>
                                <div style="padding: 1em; display: flex; gap: 10px; flex-wrap: wrap;">
                      <h4>Nomenclatura de Estados</h4>
                  
                  <span class="EnRevision">Pendiente</span>
                  <!--<span class="Pendiente">Pendiente</span>-->
                  <span class="Cancelada">Cancelada</span>
                 <span class="Confirmada">Confirmada</span>
                 <!--<span class="Facturado">Facturado</span>-->
                 <!--<span class="Cobrado">Cobrado</span>-->
                 
                  
                  <!--<span class="Aprobado">Aprobado</span>-->
                  <!--<span class="Atrasado">Atrasado</span>-->
                  
                  <!--<span class="Confirmada">En Curso</span>-->
                   
                  <!--<span class="EnEdicion">En Edición</span>-->
                  
                   <!--<span class="Cancelado">Cancelado</span>-->
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
                    <form method="post" accept-charset="UTF-8">
    <meta charset="UTF-8">
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fechaSoporte" id="fechaSoporte">
                        <script>
                            document.getElementById('fechaSoporte').value = new Date().toISOString().substring(0, 10);
                        </script>
                    </div>

                    <div class="mb-3">
                        <!--<label class="form-label">Cliente</label>-->
                      <input type="hidden" class="form-control" name="idCliente" id="idCliente" value="3" >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hora Inicio</label>
                        <input type="time" class="form-control" name="timeIni" max="23:45" min="07:00" step="1800" value="08:00:00"  >
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora Fin</label>
                        <input type="time" class="form-control" name="timeFin" max="23:45" min="07:00" step="1800" value="08:00:00"  >
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
                        <textarea class="form-control" id="comentario" name="comentario" rows="4" maxlength="1000" oninput="actualizarContador()"></textarea>
                        <div class="form-text text-end"><span id="contador">1000</span> caracteres disponibles</div>
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
        const max = 1000;
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
                     
                     
                    <input type="hidden" id="idCita" name="id">
                    <input type="hidden" id="estadoCita" name="estado">
                </div>
                <div class="modal-footer">
                    
                    
                   
                     <?php
                     $rol_usuario = $_SESSION["rol"];
                                    if($rol_usuario == "SISTEMA"){?>
                                        
                                         <button id="btnConfirmar" class="btn btn-success" type="submit" onclick="setEstado('Confirmada')">Confirmar</button>
                    <button id="btnCancelar" class="btn btn-danger" type="submit" onclick="setEstado('Cancelada')">Cancelar</button>
                                
                                </button> 
                                    <?php 
                                        
                                    }else{ ?>
                                        <button id="btnConfirmar" class="btn btn-success" type="submit" onclick="setEstado('Confirmada')" disabled  hidden >Confirmar</button>
                    <button id="btnCancelar" class="btn btn-danger" type="submit" onclick="setEstado('Cancelada') " disabled hidden >Cancelar</button>
                                 <?php          
                                    }
                                ?>
                    <!--<button id="btnFacturar" class="btn btn-warning" type="submit" onclick="setEstado('Facturado')">Facturar</button>-->
                    <!--<button id="btnCobrado" class="btn btn-primary" type="submit" onclick="setEstado('Cobrado')">Cobrado</button>-->
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
        //  events: <?php echo json_encode($eventos); ?>,
        //  events: [],
        events: <?php echo json_encode($eventos ?? []); ?>, // Usa array vacío si $eventos es null

        themeSystem: 'bootstrap5',
     eventClick: function(info) {

            // Convierte saltos de línea (reales o literales \r\n) en <br> para HTML
            function nl2br(str) {
                if (!str) return 'Sin descripción';
                return str
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/\\r\\n/g, '<br>')   // datos viejos: \r\n literal
                    .replace(/\\n/g,   '<br>')    // datos viejos: \n literal
                    .replace(/\r\n/g,  '<br>')    // datos nuevos: CRLF real
                    .replace(/\n/g,    '<br>');   // datos nuevos: LF real
            }

            // Badge de estado
            const estadoColors = {
                'Confirmada': 'bg-success',
                'Pendiente':  'bg-warning text-dark',
                'Cancelada':  'bg-danger',
                'Cancelado':  'bg-danger',
            };
            const est    = info.event.extendedProps.cita || 'Pendiente';
            const badge  = `<span class="badge ${estadoColors[est] || 'bg-secondary'}">${est}</span>`;
            const descHtml = nl2br(info.event.extendedProps.comentario);

            var details = `
                <p><strong><i class="bi bi-person-fill"></i> Cliente:</strong> ${info.event.title}</p>
                <p><strong><i class="bi bi-clock"></i> Inicio:</strong> ${info.event.start.toLocaleString()}</p>
                <p><strong><i class="bi bi-alarm"></i> Fin:</strong> ${info.event.end ? info.event.end.toLocaleString() : '--'}</p>
                <p><strong><i class="bi bi-tools"></i> Técnico:</strong> ${info.event.extendedProps.tecnico || '--'}</p>
                <p><strong><i class="bi bi-wrench-adjustable"></i> Tipo Soporte:</strong> ${info.event.extendedProps.consulta || '--'}</p>
                <p><strong><i class="bi bi-triangle-half"></i> Estado:</strong> ${badge}</p>
                ${descHtml !== 'Sin descripción' ? `
                <div>
                    <strong><i class="bi bi-textarea-t"></i> Descripción:</strong>
                    <div style="background:#f8f9fa;border-left:3px solid #1a3a5c;padding:8px 12px;
                                margin-top:4px;border-radius:4px;font-size:0.9rem;line-height:1.6;">
                        ${descHtml}
                    </div>
                </div>` : ''}
            `;

            document.getElementById('eventDetails').innerHTML = details;
            document.getElementById('idCita').value     = info.event.id;
            document.getElementById('estadoCita').value = est;

            document.getElementById('btnConfirmar').dataset.eventId = info.event.id;
            document.getElementById('btnCancelar').dataset.eventId  = info.event.id;

            new bootstrap.Modal(document.getElementById('eventModal')).show();
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
