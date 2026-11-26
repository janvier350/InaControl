
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
    <style>
        .app-sidebar .scrollbar-sidebar { overflow-y: auto !important; max-height: calc(100vh - 60px) !important; }
    </style>
    <?php $paginaActual = basename($_SERVER['PHP_SELF']); ?>
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                <li class="app-sidebar__heading">Contraloría</li>
                <li>
                    <a href="CONT_Estadisticas.php" class="<?php echo $paginaActual === 'CONT_Estadisticas.php' ? 'mm-active' : ''; ?>">
                        <i class="metismenu-icon pe-7s-graph3"></i>
                        Estadísticas de Ventas
                    </a>
                </li>
                <li>
                    <a href="CONT_Ventas.php" class="<?php echo $paginaActual === 'CONT_Ventas.php' ? 'mm-active' : ''; ?>">
                        <i class="metismenu-icon pe-7s-cash"></i>
                        Ventas
                    </a>
                </li>
                <li>
                    <a href="CONT_Productos.php" class="<?php echo $paginaActual === 'CONT_Productos.php' ? 'mm-active' : ''; ?>">
                        <i class="metismenu-icon pe-7s-box1"></i>
                        Catálogo de Productos
                    </a>
                </li>
                <li>
                    <a href="CONT_Bascula.php" class="<?php echo $paginaActual === 'CONT_Bascula.php' ? 'mm-active' : ''; ?>">
                        <i class="metismenu-icon pe-7s-note2"></i>
                        Secuencia de Báscula
                    </a>
                </li>
                <li>
                    <a href="CONT_Compras.php" class="<?php echo $paginaActual === 'CONT_Compras.php' ? 'mm-active' : ''; ?>">
                        <i class="metismenu-icon pe-7s-car"></i>
                        Compras
                    </a>
                </li>
                <li class="app-sidebar__heading">Sesión</li>
                <li>
                    <a href="salir.php">
                        <i class="metismenu-icon pe-7s-close-circle"></i>
                        Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
