<!doctype html>
<html lang="en">
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

$rol_usuario = $_SESSION["rol"];
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="es">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Calendar - Calendars are used in a lot of apps. We thought to include one for React.</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    <meta name="description" content="Calendars are used in a lot of apps. We thought to include one for React.">
    <meta name="msapplication-tap-highlight" content="no">
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css' rel='stylesheet'>
     <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS (necesario para que funcionen los modales) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    
    <script src="js/jquery.min.js"></script>
    
    <link href="./main.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap4.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap4.min.css">



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
                            </ul>        </div>
                        <div class="app-header-right">
                            <div class="header-btn-lg pr-0">
                                <div class="widget-content p-0">
                                    <div class="widget-content-wrapper">
                                        <div class="widget-content-left">
                                            <div class="btn-group">
                                                <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
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
                                                Admin
                                            </div>
                                            <div class="widget-subheading">
                                                Administrator
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
                                        <i class="pe-7s-monitor icon-gradient bg-warm-flame">
                                        </i>
                                    </div>
                                    <div>Registrar Nuevo Equipo
                                        <div class="page-title-subheading">Computo.
                                        </div>
                                    </div>
                                </div>
                                <div class="page-title-actions">
                                  <!--   <button type="button" data-toggle="tooltip" title="Example Tooltip" data-placement="bottom" class="btn-shadow mr-3 btn btn-dark">
                                        <i class="fa fa-star"></i>
                                    </button>
                                    <div class="d-inline-block dropdown">
                                        <button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn-shadow dropdown-toggle btn btn-info">
                                            <span class="btn-icon-wrapper pr-2 opacity-7">
                                                <i class="fa fa-business-time fa-w-20"></i>
                                            </span>
                                            Buttons
                                        </button>
                                        <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                            <ul class="nav flex-column">
                                                <li class="nav-item">
                                                    <a href="javascript:void(0);" class="nav-link">
                                                        <i class="nav-link-icon lnr-inbox"></i>
                                                        <span>
                                                            Inbox
                                                        </span>
                                                        <div class="ml-auto badge badge-pill badge-secondary">86</div>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="javascript:void(0);" class="nav-link">
                                                        <i class="nav-link-icon lnr-book"></i>
                                                        <span>
                                                            Book
                                                        </span>
                                                        <div class="ml-auto badge badge-pill badge-danger">5</div>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="javascript:void(0);" class="nav-link">
                                                        <i class="nav-link-icon lnr-picture"></i>
                                                        <span>
                                                            Picture
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a disabled href="javascript:void(0);" class="nav-link disabled">
                                                        <i class="nav-link-icon lnr-file-empty"></i>
                                                        <span>
                                                            File Disabled
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div> -->
                                </div>    
                            </div>
                        </div>            
                        <ul class="body-tabs body-tabs-layout tabs-animated body-tabs-animated nav">
                            <li class="nav-item">
                                <a role="tab" class="nav-link active" id="tab-0" data-toggle="tab" href="#tab-content-0">
                                    <span>Registrar</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a role="tab" class="nav-link" id="tab-1" data-toggle="tab" href="#tab-content-1">
                                    <span>Listado</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a role="tab" class="nav-link" id="tab-2" data-toggle="tab" href="#tab-content-2">
                                    <span>Historial Mantenimientos</span>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane tabs-animation fade show active" id="tab-content-0" role="tabpanel">
                                <div class="main-card mb-3 card">
                                    <div class="card-body">
                                        <!-- <div id='calendar1'></div> -->
                                        <div class="main-card mb-3 card">
                                            <div class="card-body">
                                                <h5 class="card-title">Informacion del equipo</h5>
                                                <form class="needs-validation" novalidate method="post" action="class/Insert_Equipo.php" enctype="multipart/form-data">
                                                    <div class="form-row">
                                                        <div class="col-md-4 mb-3">
                                                            <label for="validationCustom05">Dispositivo</label>
                                                            <div class="position-relative form-group">
                                                                <select id="validationCustom05" name="dispositivo" class="form-control" required>
                                                                <option value="">Seleccione dispositivo:</option>
                                                                <?php
                                                                  $query = $conexion -> query ("SELECT * FROM INV_DISPOSITIVO WHERE ESTADO = 'A'");
                                                                  while ($valores = mysqli_fetch_array($query)) {
                                                                      
                                                                    echo '<option value="'.$valores['DISPOSITIVO'].'">'.$valores['DISPOSITIVO'].'</option>';
                                                                  }
                                                                ?>
                                                            </select>
                                                            
                                                            
                                                        </div>
                                                         <div class="invalid-feedback">
                                                                Please provide a valid rol.
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="validationCustom01">MARCA</label>
                                                            <input type="text" class="form-control" id="validationCustom01" name="marca" placeholder="Dell, Hp, Lenovo"   required>
                                                            <div class="valid-feedback">
                                                                Looks good!
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="validationCustom02">Modelo</label>
                                                            <input type="text" class="form-control" id="validationCustom02"  name="modelo" placeholder="Spin, Series, ThinkPad"  required>
                                                            <div class="valid-feedback">
                                                                Looks good!
                                                            </div>
                                                        </div>
                                                       
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-4 mb-3">
                                                            <label for="validationCustomUsername">Procesador</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="inputGroupPrepend"><i class="pe-7s-user"> </i></span>
                                                                </div>
                                                                <input type="text" class="form-control" id="validationCustomUsername" name="procesador" placeholder="procesador"  aria-describedby="inputGroupPrepend" required>
                                                                <div class="invalid-feedback">
                                                                    Please choose a username.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="validationCustom03">HDD</label>
                                                            <input type="text" class="form-control" id="validationCustom03" name="hdd" placeholder="HDD"  required>
                                                            <div class="invalid-feedback">
                                                                Please provide a valid Password.
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-4 mb-3">
                                                           <label for="validationCustom04">Serial</label>
                                                           <input type="text" class="form-control" id="validationCustom04" name="serial" placeholder="Numero de serie del dispositivo"  required>
                                                           <div class="invalid-feedback">
                                                               Please provide a valid state.
                                                           </div>
                                                       </div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-4 mb-3">
                                                            <label for="validationCustom01">RAM</label>
                                                            <input type="text" class="form-control" id="validationCustom01" name="ram" placeholder="16 GB"   required>
                                                            <div class="valid-feedback">
                                                                Looks good!
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="validationCustom02">Pantalla</label>
                                                            <input type="text" class="form-control" id="validationCustom02"  name="pantalla" placeholder="TACTIL, NO TACTIL, 15'"  required>
                                                            <div class="valid-feedback">
                                                                Looks good!
                                                            </div>
                                                        </div>
                                                       <div class="col-md-4 mb-3">
                                                           <label for="validationCustom04">Observaciones</label>
                                                           <input type="text" class="form-control" id="validationCustom04" name="observaciones" placeholder="nueva, de segunda, etc"  required>
                                                           <div class="invalid-feedback">
                                                               Please provide a valid state.
                                                           </div>
                                                       </div>
                                                    </div>
                                                    
                                                    <div class="form-row">
                                                        <div class="col-md-4 mb-3">
                                                            <label for="validationCustom01">Fecha compra</label>
                                                            <input type="date" class="form-control" id="validationCustom01" name="fechaCompra"  >
                                                            <div class="valid-feedback">
                                                                Looks good!
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="validationCustom02">Departamento</label>
                                                            <input type="text" class="form-control" id="validationCustom02"  name="departamento" placeholder="Contabilidad, administracion, laboratorio..." required>
                                                            <div class="valid-feedback">
                                                                Looks good!
                                                            </div>
                                                        </div>
                                                       <div class="col-md-4 mb-3">
                                                           <label for="imagenEquipo">Imagen del equipo</label>
                                                           <input type="file" class="form-control" id="imagenEquipo" name="imagen" accept="image/*">
                                                       </div>
                                                    </div>


                                                    <button class="btn btn-primary" type="submit">Registrar Equipo</button>
                                                </form>
                                        
                                                <script>
                                                    // Example starter JavaScript for disabling form submissions if there are invalid fields
                                                    (function() {
                                                        'use strict';
                                                        window.addEventListener('load', function() {
                                                            // Fetch all the forms we want to apply custom Bootstrap validation styles to
                                                            var forms = document.getElementsByClassName('needs-validation');
                                                            // Loop over them and prevent submission
                                                            var validation = Array.prototype.filter.call(forms, function(form) {
                                                                form.addEventListener('submit', function(event) {
                                                                    if (form.checkValidity() === false) {
                                                                        event.preventDefault();
                                                                        event.stopPropagation();
                                                                    }
                                                                    form.classList.add('was-validated');
                                                                }, false);
                                                            });
                                                        }, false);
                                                    })();
                                                </script>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-1" role="tabpanel">
                                <div class="main-card mb-3 card">
                                    <div class="card-body">
                                        <!-- <div id='calendar-list'></div> -->
                                        <div class="main-card mb-3 card">
                                    <div class="card-body">
                                        <!-- <div id='calendar1'></div> -->
                                        <table class="table align-middle mb-0 bg-white">
    <thead class="bg-light">
        <tr>
            <th>EQUIPO</th>
            <th>ALMACENAMIENTO</th>
            <th>MARCA</th>
            <th>DISPOSITIVO</th>
            <th>MODELO</th>
            <th>SERIE</th>
            <th>DEPARTAMENTO</th>
            <th>OBSERVACIONES</th>
            <th>COMPRA</th>
            <th>ESTADO</th>
            <th>ASIGNADO A</th>
            <th>FECHA ASIGNACIÓN</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT ID_EQUIPO, FECHA_COMPRA, DEPARTAMENTO, MARCA, MODELO, SERIAL, PROCESADOR, HDD, RAM, PANTALLA, OBSERVACIONES, ESTADO, DISPOSITIVO, IMAGEN FROM INV_EQUIPO WHERE ESTADO_AI = 'A'";
        $query = $conexion->query($sql);

        $sqlAsig = "SELECT A.ID_EQUIPO, A.FECHA_ASIGNACION, U.NOMBRES, U.APELLIDOS
                    FROM INV_ASIGNACION A
                    INNER JOIN ADM_USUARIO U ON A.ID_ADM_USUARIO = U.IDADM_USUARIO
                    WHERE A.ESTADO = 'A'";
        $queryAsig = $conexion->query($sqlAsig);
        $asignaciones = array();
        if ($queryAsig) {
            while ($asig = mysqli_fetch_array($queryAsig)) {
                $asignaciones[$asig['ID_EQUIPO']] = $asig;
            }
        }

        if (!$query) {
            die("Error en la consulta: " . $conexion->error);
        }

        if ($query->num_rows > 0) {
            while ($valores = mysqli_fetch_array($query)) {
        ?>    
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <?php $imgEquipo = !empty($valores['IMAGEN']) ? $valores['IMAGEN'] : 'https://mdbootstrap.com/img/new/avatars/8.jpg'; ?>
                    <img src="<?php echo htmlspecialchars($imgEquipo); ?>"
                         alt=""
                         style="width: 45px; height: 45px; object-fit: cover; cursor: pointer;"
                         class="rounded-circle"
                         onclick="verImagenEquipo(this.src)"/>
                    <div class="ms-3">
                        <p class="fw-bold mb-1">Procesador: <?php echo $valores['PROCESADOR']; ?></p>
                        <p class="fw-bold mb-1">RAM: <?php echo $valores['RAM']; ?></p>
                        
                        <p class="text-muted mb-0">Pantalla: <?php echo $valores['PANTALLA']; ?></p>
                    </div>
                </div>
            </td>
            <td>
            <p class="fw-normal mb-1"><?php echo $valores['HDD']; ?></p>
            </td>
            <td>
            <p class="fw-normal mb-1"><?php echo $valores['MARCA']; ?></p>
            </td>
            <td>
            <p class="fw-normal mb-1"><?php echo $valores['DISPOSITIVO']; ?></p>
            </td>
            <td>
            <p class="fw-normal mb-1"><?php echo $valores['MODELO']; ?></p>
            </td>
            <td>
            <p class="fw-normal mb-1"><?php echo $valores['SERIAL']; ?></p>
            </td>
             <td>
            <p class="fw-normal mb-1"><?php echo $valores['DEPARTAMENTO']; ?></p>
            </td>
            <td>
            <p class="fw-normal mb-1"><?php echo $valores['OBSERVACIONES']; ?></p>
            </td>
             <td>
            <p class="fw-normal mb-1"><?php echo $valores['FECHA_COMPRA']; ?></p>
            </td>
            <td>
            <p class="fw-normal mb-1"><?php echo $valores['ESTADO']; ?></p>
            </td>
            <td>
            <p class="fw-normal mb-1"><?php echo isset($asignaciones[$valores['ID_EQUIPO']]) ? htmlspecialchars($asignaciones[$valores['ID_EQUIPO']]['NOMBRES'].' '.$asignaciones[$valores['ID_EQUIPO']]['APELLIDOS']) : '-'; ?></p>
            </td>
            <td>
            <p class="fw-normal mb-1"><?php echo isset($asignaciones[$valores['ID_EQUIPO']]) ? htmlspecialchars($asignaciones[$valores['ID_EQUIPO']]['FECHA_ASIGNACION']) : '-'; ?></p>
            </td>

            <td>

                                                            <button class="btn btn-outline-warning fa fa-edit"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editModalEquipo"
                                                                onclick="cargarDatosEquipo(<?php echo htmlspecialchars(json_encode($valores), ENT_QUOTES, 'UTF-8'); ?>)">
                                                            </button>
                                                            <!-- Botón para eliminar con confirmación -->
                                                            <button class="btn-shadow btn btn-outline-danger fa fa-minus-circle"
                                                                onclick="confirmarEliminacionEquipo(<?php echo $valores['ID_EQUIPO']; ?>)">
                                                            </button>
                                                            <!-- Botón para asignar equipo a un usuario -->
                                                            <button class="btn-shadow btn btn-outline-primary fa fa-user-plus"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#asignarModalEquipo"
                                                                onclick="prepararAsignacion(<?php echo $valores['ID_EQUIPO']; ?>, '<?php echo htmlspecialchars($valores['MARCA'].' '.$valores['MODELO'].' - '.$valores['SERIAL'], ENT_QUOTES, 'UTF-8'); ?>')">
                                                            </button>
            </td>
        </tr>
        <?php 
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'>No hay datos disponibles</td></tr>";
        }
        ?>
    </tbody>
</table>



                                    </div>
                                </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane tabs-animation fade" id="tab-content-2" role="tabpanel">
                                <div class="main-card mb-3 card">
                                    <div class="card-body">
                                        <table class="table align-middle mb-0 bg-white">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>EQUIPO</th>
                                                    <th>SERIE</th>
                                                    <th>FECHA SALIDA</th>
                                                    <th>DAÑO REPORTADO</th>
                                                    <th>FECHA ENTREGA</th>
                                                    <th>SOLUCIÓN APLICADA</th>
                                                    <th>ESTADO</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sqlMant = "SELECT M.FECHA_SALIDA, M.DANIO_REPORTADO, M.FECHA_ENTREGA, M.SOLUCION_APLICADA, M.ESTADO,
                                                                   E.MARCA, E.MODELO, E.SERIAL
                                                            FROM INV_MANTENIMIENTOS M
                                                            INNER JOIN INV_EQUIPO E ON M.ID_EQUIPO = E.ID_EQUIPO
                                                            ORDER BY M.FECHA_SALIDA DESC";
                                                $queryMant = $conexion->query($sqlMant);

                                                if ($queryMant && $queryMant->num_rows > 0) {
                                                    while ($mant = mysqli_fetch_array($queryMant)) {
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($mant['MARCA'].' '.$mant['MODELO']); ?></td>
                                                    <td><?php echo htmlspecialchars($mant['SERIAL']); ?></td>
                                                    <td><?php echo htmlspecialchars($mant['FECHA_SALIDA']); ?></td>
                                                    <td><?php echo htmlspecialchars($mant['DANIO_REPORTADO']); ?></td>
                                                    <td><?php echo htmlspecialchars($mant['FECHA_ENTREGA'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($mant['SOLUCION_APLICADA'] ?? '-'); ?></td>
                                                    <td>
                                                        <?php if ($mant['ESTADO'] === 'En Reparacion') { ?>
                                                            <span class="badge bg-warning text-dark">En Reparación</span>
                                                        <?php } else { ?>
                                                            <span class="badge bg-success">Completado</span>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='7' class='text-center'>No hay mantenimientos registrados</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <!-- <div id="calendar-bg-events"></div> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="app-wrapper-footer">
                        <div class="app-footer">
                            <div class="app-footer__inner">
                                
                            </div>
                        </div>
                    </div>    
                </div>
        </div>
    </div>
    <script>
    function confirmarEliminacionEquipo(idEquipo) {
        if (confirm("¿Estás seguro de eliminar este equipo?")) {
            window.location.href = "class/Eliminar_Equipo.php?idEquipo=" + idEquipo;
        }
    }

    function cargarDatosEquipo(equipo) {
        document.getElementById('idEquipo').value = equipo.ID_EQUIPO;
        document.getElementById('editDispositivo').value = equipo.DISPOSITIVO;
        document.getElementById('editMarca').value = equipo.MARCA;
        document.getElementById('editModelo').value = equipo.MODELO;
        document.getElementById('editProcesador').value = equipo.PROCESADOR;
        document.getElementById('editHdd').value = equipo.HDD;
        document.getElementById('editSerial').value = equipo.SERIAL;
        document.getElementById('editRam').value = equipo.RAM;
        document.getElementById('editPantalla').value = equipo.PANTALLA;
        document.getElementById('editObservaciones').value = equipo.OBSERVACIONES;
        document.getElementById('editFechaCompra').value = equipo.FECHA_COMPRA;
        document.getElementById('editDepartamento').value = equipo.DEPARTAMENTO;
        document.getElementById('editEstado').value = equipo.ESTADO;
        document.getElementById('editImagen').value = '';
        document.getElementById('editImagenPreview').src = equipo.IMAGEN ? equipo.IMAGEN : 'https://mdbootstrap.com/img/new/avatars/8.jpg';
    }

    function guardarEdicionEquipo() {
        var formData = new FormData(document.getElementById("formEditarEquipo"));

        fetch("class/Editar_Equipo.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload();
        })
        .catch(error => console.error("Error:", error));
    }
</script>

<div class="modal fade" id="editModalEquipo" tabindex="-1" aria-labelledby="editModalEquipoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalEquipoLabel">Editar equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarEquipo" enctype="multipart/form-data">
                    <input type="hidden" id="idEquipo" name="idEquipo">

                    <div class="form-row">
                        <div class="col-md-4 mb-3 text-center">
                            <label class="form-label d-block">Imagen actual</label>
                            <img id="editImagenPreview" src="" alt="Imagen del equipo"
                                 style="width:100px; height:100px; object-fit:cover; border-radius:6px; border:1px solid #dee2e6;">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Cambiar imagen del equipo</label>
                            <input type="file" class="form-control" id="editImagen" name="imagen" accept="image/*">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dispositivo</label>
                            <select id="editDispositivo" name="dispositivo" class="form-control">
                                <?php
                                  $query = $conexion -> query ("SELECT * FROM INV_DISPOSITIVO WHERE ESTADO = 'A'");
                                  while ($valores = mysqli_fetch_array($query)) {
                                    echo '<option value="'.$valores['DISPOSITIVO'].'">'.$valores['DISPOSITIVO'].'</option>';
                                  }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" id="editMarca" name="marca">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="editModelo" name="modelo">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Procesador</label>
                            <input type="text" class="form-control" id="editProcesador" name="procesador">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">HDD</label>
                            <input type="text" class="form-control" id="editHdd" name="hdd">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Serial</label>
                            <input type="text" class="form-control" id="editSerial" name="serial">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">RAM</label>
                            <input type="text" class="form-control" id="editRam" name="ram">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pantalla</label>
                            <input type="text" class="form-control" id="editPantalla" name="pantalla">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Observaciones</label>
                            <input type="text" class="form-control" id="editObservaciones" name="observaciones">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha compra</label>
                            <input type="date" class="form-control" id="editFechaCompra" name="fechaCompra">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Departamento</label>
                            <input type="text" class="form-control" id="editDepartamento" name="departamento">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <input type="text" class="form-control" id="editEstado" name="estado">
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="guardarEdicionEquipo()">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar equipo a un usuario -->
<div class="modal fade" id="asignarModalEquipo" tabindex="-1" aria-labelledby="asignarModalEquipoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asignarModalEquipoLabel">Asignar equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="class/Insert_Asignacion.php">
                    <input type="hidden" id="asignarIdEquipo" name="idEquipo">
                    <div class="mb-3">
                        <label class="form-label">Equipo</label>
                        <input type="text" class="form-control" id="asignarEquipoLabel" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <select class="form-select" name="idUsuario" required>
                            <option value="">Seleccione usuario:</option>
                            <?php
                              $queryUsr = $conexion->query("SELECT IDADM_USUARIO, NOMBRES, APELLIDOS FROM ADM_USUARIO WHERE ESTADO = 'A'");
                              while ($valoresUsr = mysqli_fetch_array($queryUsr)) {
                                echo '<option value="'.$valoresUsr['IDADM_USUARIO'].'">'.$valoresUsr['NOMBRES'].' '.$valoresUsr['APELLIDOS'].'</option>';
                              }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de asignación</label>
                        <input type="date" class="form-control" name="fechaAsignacion" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function prepararAsignacion(idEquipo, equipoLabel) {
    document.getElementById('asignarIdEquipo').value = idEquipo;
    document.getElementById('asignarEquipoLabel').value = equipoLabel;
}

function verImagenEquipo(src) {
    var overlay = document.createElement('div');
    overlay.id = 'imagenEquipoOverlay';
    overlay.style.cssText = 'display:flex; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.8); z-index:99999; cursor:zoom-out; align-items:center; justify-content:center;';
    overlay.onclick = function() { overlay.remove(); };

    var cerrar = document.createElement('span');
    cerrar.innerHTML = '&times;';
    cerrar.style.cssText = 'position:absolute; top:20px; right:30px; color:#fff; font-size:2.5rem; cursor:pointer; line-height:1;';
    cerrar.onclick = function() { overlay.remove(); };

    var img = document.createElement('img');
    img.src = src;
    img.style.cssText = 'width:80vw; max-width:900px; height:80vh; object-fit:contain; border-radius:6px; background:#fff;';

    overlay.appendChild(cerrar);
    overlay.appendChild(img);
    document.body.appendChild(overlay);
}
</script>

<script type="text/javascript" src="./assets/scripts/main.js"></script>

</body>
</html>
