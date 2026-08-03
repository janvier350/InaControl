
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
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                <li class="app-sidebar__heading"><?php echo htmlspecialchars($_SESSION['nombre_empresa'] ?? 'Empresa'); ?></li>
                <li>
                    <a href="MT_Dashboard.php">
                        <i class="metismenu-icon pe-7s-graph"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="MT_Calendar_SOP.php" class="mm-active">
                        <i class="metismenu-icon pe-7s-display2"></i>
                        Calendario Soportes
                    </a>
                </li>
                <li>
                    <a href="MT_Clientes.php">
                        <i class="metismenu-icon pe-7s-users"></i>
                        Clientes
                    </a>
                </li>
                <li>
                    <a href="MT_TiposSoporte.php">
                        <i class="metismenu-icon pe-7s-wrench"></i>
                        Tipos de Soporte
                    </a>
                </li>
                <?php if (!empty($_SESSION['mod_inventario'])): ?>
                <li>
                    <a href="MT_Inventario.php">
                        <i class="metismenu-icon pe-7s-server"></i>
                        Inventario
                    </a>
                </li>
                <?php endif; ?>
                <li class="app-sidebar__heading">Sesión</li>
                <li>
                    <a href="salir_empresa.php">
                        <i class="metismenu-icon pe-7s-close-circle"></i>
                        Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
