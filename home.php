
<!doctype html>
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
?>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Language" content="en">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>      
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
        <meta name="description" content="System Abordo">
        <meta name="msapplication-tap-highlight" content="no">
        <link rel="apple-touch-icon" href="images/icono.png">
        <link rel="shortcut icon" href="images/icono.png">
        <link href="./main.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script type="text/javascript" src="./js/charts.js"></script>
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
                                                    <button type="button" tabindex="0" class="dropdown-item">Configuraci贸n</button>
                                                    <div tabindex="-1" class="dropdown-divider"></div>
                                                    <a type="button" tabindex="0" href="salir.php" class="dropdown-item">Cerrar Sesi贸n</a>
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
        <?php include("./menu/menu_cotizador.php"); ?>       

                               
            </div>             
            <div class="app-main__outer">
                    <div class="app-main__inner">
                        <div class="row">                            
                            <div class="col-md-6 col-xl-4">
                                <div class="card mb-3 widget-content bg-warning">
                                    <div class="widget-content-wrapper text-white">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">Cotizador</div>
                                            <div class="widget-subheading">V: 2.1.3</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-white"><span>V: 2.1.3</span></div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                                // Configura la zona horaria si es necesario
                                date_default_timezone_set('America/Guayaquil'); // Cambia según tu zona horaria
                            
                                $dia = date('d');
                                $mes = date('F'); // Puedes usar 'M' para abreviado
                                $hora = date('H:i:s');
                                
                                setlocale(LC_TIME, 'es_ES.UTF-8');
                                $mes = strftime('%B'); // Mes en español, como "abril"
                            ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="card mb-3 widget-content bg-success">
                                    <div class="widget-content-wrapper text-white">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">Hora actual</div>
                                            <div class="widget-subheading"><?= $hora ?></div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-white">
                                                <span><?= $dia ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="widget-content-wrapper text-white mt-2 px-3">
                                        <div class="widget-heading"></div>
                                        <div class="widget-subheading"><?= $mes ?></div>
                                    </div>
                                </div>
                            </div>
                            <!--<div class="col-md-6 col-xl-4">-->
                            <!--    <div class="card mb-3 widget-content bg-success">-->
                            <!--        <div class="widget-content-wrapper text-white">-->
                            <!--            <div class="widget-content-left">-->
                            <!--                <div class="widget-heading">Mes</div>-->
                            <!--                <div class="widget-subheading">Mayo </div>-->
                            <!--            </div>-->
                            <!--            <div class="widget-content-right">-->
                            <!--                <div class="widget-numbers text-white"><span>10</span></div>-->
                            <!--            </div>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="col-md-6 col-xl-4">
                              <!--   <div class="card mb-3 widget-content bg-danger">
                                    <div class="widget-content-wrapper text-white">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">Citas Vencidas</div>
                                            <div class="widget-subheading">No Concretadas</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-white"><span>15</span></div>                                          
                                        </div>
                                    </div>
                                </div> -->
                            </div>
                            <div class="d-xl-none d-lg-block col-md-6 col-xl-4">
                                <div class="card mb-3 widget-content bg-premium-dark">
                                    <div class="widget-content-wrapper text-white">
                                        <div class="widget-content-left">
                                            <div class="widget-heading">Products Sold</div>
                                            <div class="widget-subheading">Revenue streams</div>
                                        </div>
                                        <div class="widget-content-right">
                                            <div class="widget-numbers text-warning"><span>$14M</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-lg-8">
                                <div class="mb-3 card">
                                    <div class="card-header-tab card-header-tab-animation card-header">
                                        <div class="card-header-title">
                                            <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                                            COTIZADOR WEB
                                        </div>
                                        <ul class="nav">
                                            <li class="nav-item"><a href="javascript:void(0);" class="active nav-link">Last</a></li>
                                            <li class="nav-item"><a href="javascript:void(0);" class="nav-link second-tab-toggle">Current</a></li>
                                        </ul>
                                    </div>
                                    <div class="card-body">
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="tabs-eg-77">
                                                <div class="card mb-3 widget-chart widget-chart2 text-left w-100">
                                                    <div class="widget-chat-wrapper-outer">
                                                        <div class="widget-chart-wrapper widget-chart-wrapper-lg opacity-10 m-0">
                                                             <!--<canvas id="ventasChart"></canvas> -->
                                                            <div class="tab-pane tabs-animation fade show active" id="tab-content-0" role="tabpanel">
                                                                    <div class="main-card mb-3 card">
                                                                        <div class="card-body">
                                                                            <!-- <div id='calendar1'></div> -->
                                                                            <div class="main-card mb-3 card">
                                                                                <div class="card-body">
                                                                                    <h5 class="card-title">Costo Proveedor</h5>
                                                                                    <form class="needs-validation" novalidate name="CostoProveedor">
                                                                                     
                                                                                        <div class="form-row">
                                                                                            <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername">Valor</label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text"  >$</span>
                                                                                                    </div>
                        <input type="number" class="form-control" id="validationCustomUsername" name="costoProveedor" step="0.001" placeholder="0" aria-describedby="inputGroupPrepend" onChange="suma()">
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                         
                                                                                               <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername">IVA  15%</label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" >$</span>
                                                                                                    </div>
                            <input type="text" class="form-control" id="validationCustomUsername" name="ivaCostoProveedor" readonly placeholder="0" aria-describedby="inputGroupPrepend"  >
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>

                                                                                              <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername" class="text-danger"><b> TOTAL </b></label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text text-danger" id="inputGroupPrepend">$</span>
                                                                                                    </div>
                            <input type="text" class="form-control text-danger" id="validationCustomUsername" name="totalCostoProveedor" readonly step="0.001"  placeholder="0" aria-describedby="inputGroupPrepend"  >
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            
                                                                                           
                                                                                            
                                                                                        </div>
                                                                                        
                                                                                         
                                                                                      <!--   <div class="form-group">
                                                                                            <div class="form-check">
                                                                                                <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
                                                                                                <label class="form-check-label" for="invalidCheck">
                                                                                                    Agree to terms and conditions
                                                                                                </label>
                                                                                                <div class="invalid-feedback">
                                                                                                    You must agree before submitting.
                                                                                                </div>
                                                                                            </div>
                                                                                        </div> -->
                                                                                        <button class="btn btn-danger" type="submit">RESET</button>
                                                                                       
                                                                                    </form>
                                                                                    <script >
                                                                                        function suma()
                                                                                                {
                                                                                                    var costo= parseFloat(document.CostoProveedor.costoProveedor.value);
                                                                                                    var ivaCosto= parseFloat(document.CostoProveedor.ivaCostoProveedor.value);

                                                                                                    document.CostoProveedor.ivaCostoProveedor.value = costo * 0.15;
                                                                                                    document.CostoProveedor.totalCostoProveedor.value =  (costo * 0.15) + costo;

                                                                                                   
                                                                                                }    

                                                                                          function utilidadCal()
                                                                                                {
                                                                                                    var costoU= parseFloat(document.CostoProveedor.costoProveedor.value);
                                                                                                    var utilidad= parseFloat(document.Utilidad.utilidad.value);
                                                                                                    var TotalCostoProveedor= parseFloat(document.CostoProveedor.totalCostoProveedor.value);

                                                                                                    document.Utilidad.ganancia.value = (costoU * utilidad) / 100;
                                                                                                   

                                                                                                    var costoU= parseFloat(document.CostoProveedor.costoProveedor.value);
                                                                                                    var ganancia= parseFloat(document.Utilidad.ganancia.value);
                                                                                                    

                                                                                                    document.Cliente.subTotal.value = TotalCostoProveedor + ganancia; 

                                                                                                    

                                                                                                    var subTotalCliente = parseFloat(document.Cliente.subTotal.value);
                                                                                                    document.Cliente.iva.value = subTotalCliente * 0.15; 
                                                                                                    var ivaCliente = parseFloat(document.Cliente.iva.value);
                                                                                                    document.Cliente.total.value = ivaCliente + subTotalCliente; 
                                                                                                   
                                                                                                   document.Impuestos.rtIva30.value = ivaCliente * 0.30;
                                                                                                   document.Impuestos.rtFuente.value = subTotalCliente * 0.01;


                                                                                                   var rtIva30 = parseFloat(document.Impuestos.rtIva30.value);
                                                                                                   var rtFuente = parseFloat(document.Impuestos.rtFuente.value);

                                                                                                   document.Impuestos.totalImpuestos.value = rtIva30 + rtFuente;

                                                                                                   var total = parseFloat(document.Cliente.total.value);
                                                                                                   document.Utilidad.utilidadNeta.value = total - TotalCostoProveedor - rtIva30 - rtFuente;

                                                                                                } 
                                                                                                                                                                        
                                                                                    </script>
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
                                                                            <div class="main-card mb-3 card">
                                                                                <div class="card-body">
                                                                                    <h5 class="card-title">Utilidad</h5>
                                                                                    <form class="needs-validation" name="Utilidad" novalidate>
                                                                                     
                                                                                        <div class="form-row">
                                                                                            <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername">Utilidad</label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" id="inputGroupPrepend">%</span>
                                                                                                    </div>
                                                                                                    <input type="number" class="form-control" id="validationCustomUsername" name="utilidad" step="0.001" placeholder="0" aria-describedby="inputGroupPrepend" required onchange="utilidadCal()">
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                         
                                                                                               <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername">Ganancia</label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" id="inputGroupPrepend">$</span>
                                                                                                    </div>
                                                                                                    <input type="number" class="form-control" id="validationCustomUsername" name="ganancia" step="0.001" readonly placeholder="0" aria-describedby="inputGroupPrepend" >
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>

                                                                                              <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername" class="text-success"> <b>Utilidad Neta </b></label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" id="inputGroupPrepend">$</span>
                                                                                                    </div>
                                                                                                    <input type="text" class="form-control text-success" id="validationCustomUsername" name="utilidadNeta" step="0.001" readonly placeholder="0" aria-describedby="inputGroupPrepend" required>
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            
                                                                                           
                                                                                            
                                                                                        </div>
                                                                                        
                                                                                         
                                                                                      <!--   <div class="form-group">
                                                                                            <div class="form-check">
                                                                                                <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
                                                                                                <label class="form-check-label" for="invalidCheck">
                                                                                                    Agree to terms and conditions
                                                                                                </label>
                                                                                                <div class="invalid-feedback">
                                                                                                    You must agree before submitting.
                                                                                                </div>
                                                                                            </div>
                                                                                        </div> -->
                                                                                        <!-- <button class="btn btn-primary" type="submit">Submit form</button> -->
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
                                                                                    <script >
                                                                                                                                                                         
                                                                                    </script>
                                                                                </div>
                                                                            </div>
                                                                            <div class="main-card mb-3 card">
                                                                                <div class="card-body">
                                                                                    <h5 class="card-title">Cliente</h5>
                                                                                    <form class="needs-validation" name="Cliente" novalidate>
                                                                                     
                                                                                        <div class="form-row">
                                                                                            <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername">Sub-Total</label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" id="inputGroupPrepend">$</span>
                                                                                                    </div>
                                                                                                    <input type="text" class="form-control" id="validationCustomUsername" name = "subTotal" placeholder="0" readonly step="0.001" aria-describedby="inputGroupPrepend" required >
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                         
                                                                                               <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername">IVA  15%</label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" id="inputGroupPrepend">$</span>
                                                                                                    </div>
                                                                                                    <input type="text" class="form-control" id="validationCustomUsername" placeholder="0" name="iva" step="0.001" aria-describedby="inputGroupPrepend" required readonly>
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>

                                                                                              <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername" class="text-danger"><b> TOTAL</b></label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" id="inputGroupPrepend">$</span>
                                                                                                    </div>
                                                                                                    <input type="text" class="form-control text-danger" id="validationCustomUsername" step="0.001" aria-describedby="inputGroupPrepend" name="total" readonly required value="0">
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            
                                                                                           
                                                                                            
                                                                                        </div>
                                                                                        
                                                                                         
                                                                                      <!--   <div class="form-group">
                                                                                            <div class="form-check">
                                                                                                <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
                                                                                                <label class="form-check-label" for="invalidCheck">
                                                                                                    Agree to terms and conditions
                                                                                                </label>
                                                                                                <div class="invalid-feedback">
                                                                                                    You must agree before submitting.
                                                                                                </div>
                                                                                            </div>
                                                                                        </div> -->
                                                                                        <!-- <button class="btn btn-primary" type="submit">Submit form</button> -->
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
                                                                            
                                                                            <Div class="main-card mb-3 card">
                                                                                <div class="card-body">
                                                                                    <h5 class="card-title">Impuestos</h5>
                                                                                    <form class="needs-validation" name="Impuestos" novalidate>
                                                                                     
                                                                                        <div class="form-row">
                                                                                            <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername">Rt. IVA 30%</label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" id="inputGroupPrepend">%</span>
                                                                                                    </div>
                                                                                                    <input type="text" class="form-control" id="validationCustomUsername" placeholder="0" readonly name="rtIva30" aria-describedby="inputGroupPrepend" required>
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                         
                                                                                               <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername">Rt. Fuente 1%</label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" id="inputGroupPrepend">$</span>
                                                                                                    </div>
                                                                                                    <input type="text" class="form-control" id="validationCustomUsername" placeholder="0" readonly name="rtFuente" aria-describedby="inputGroupPrepend" required>
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>

                                                                                              <div class="col-md-4 mb-4">
                                                                                                <label for="validationCustomUsername">TOTAL IMPUESTOS</label>
                                                                                                <div class="input-group">
                                                                                                    <div class="input-group-prepend">
                                                                                                        <span class="input-group-text" id="inputGroupPrepend">$</span>
                                                                                                    </div>
                                                                                                    <input type="text" class="form-control" id="validationCustomUsername" placeholder="0" readonly name="totalImpuestos" aria-describedby="inputGroupPrepend" required>
                                                                                                    <div class="invalid-feedback">
                                                                                                        Please choose a E-MAIL.
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            
                                                                                           
                                                                                            
                                                                                        </div>
                                                                                        
                                                                                         
                                                                                      <!--   <div class="form-group">
                                                                                            <div class="form-check">
                                                                                                <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
                                                                                                <label class="form-check-label" for="invalidCheck">
                                                                                                    Agree to terms and conditions
                                                                                                </label>
                                                                                                <div class="invalid-feedback">
                                                                                                    You must agree before submitting.
                                                                                                </div>
                                                                                            </div>
                                                                                        </div> -->
                                                                                        <!-- <button class="btn btn-primary" type="submit">Submit form</button> -->
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

                                                                        

                                                                    </div>
                                                                </div>
                                                        </div>
                                                    </div>
                                                </div>
                                               <h6 class="text-muted text-uppercase font-size-md opacity-5 font-weight-normal">Ranking</h6>
                                                <div class="scroll-area-sm">
                                                    <div class="scrollbar-container">
                                                        <ul class="rm-list-borders rm-list-borders-scroll list-group list-group-flush">
                                                       
                                                                    <li class="list-group-item">
                                                                        <div class="widget-content p-0">
                                                                            <div class="widget-content-wrapper">
                                                                                <div class="widget-content-left mr-3">
                                                                                    <img width="42" class="rounded-circle" src="assets/images/avatars/client.jpg" alt="">
                                                                                </div>
                                                                                <div class="widget-content-left">
                                                                                    <div class="widget-heading">Prueba</div>
                                                                                    <div class="widget-subheading">Admin</div>
                                                                                </div>
                                                                                <div class="widget-content-right">
                                                                                    <div class="font-size-xlg text-muted">
                                                                                        <small class="opacity-5 pr-1">Total citas</small>
                                                                                        <span>12</span>
                                                                                        <small class="text-success pl-2">
                                                                                            <i class="fa fa-angle-up"></i>
                                                                                        </small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                              
                                                        </ul>
                                                    </div>
                                                </div> 


                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          
                        </div>
                        
            </div>
        </div>                     
    </div>
    
    
</body>
<script type="text/javascript" src="./assets/scripts/main.js"></script>
<!--<script>-->
<!--  const myChart = new Chart(-->
<!--    document.getElementById('ventasChart'),-->
<!--    config-->
<!--  );-->
<!--</script>-->
</html>
