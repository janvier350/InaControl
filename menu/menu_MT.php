
<style>
    .mt-hamburger-btn { display: none; }
    @media (max-width: 991.98px) {
        .mt-hamburger-btn {
            display: flex !important;
            position: fixed; top: 12px; left: 12px; z-index: 1070;
            width: 44px; height: 44px; border-radius: 10px;
            background: #0f3460; color: #fff; border: none;
            align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            font-size: 1.3rem;
        }
        .app-main__outer { padding-top: 56px; }
        .sidebar-mobile-overlay { cursor: pointer; z-index: 1050 !important; }
        body.sidebar-mobile-open .app-sidebar { z-index: 1060 !important; }
    }
</style>
<script>
(function() {
    function toggleSidebar() { document.body.classList.toggle('sidebar-mobile-open'); }
    function closeSidebar() { document.body.classList.remove('sidebar-mobile-open'); }

    document.addEventListener('DOMContentLoaded', function() {
        // El botón y el overlay se crean por JS y se agregan directo al <body>,
        // porque .app-sidebar tiene "transform" en móvil y eso lo convierte en el
        // contenedor de referencia para cualquier elemento "fixed" dentro de él
        // (quedaría oculto junto con el sidebar en vez de flotar sobre la pantalla).
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'mtHamburgerBtn';
        btn.className = 'mt-hamburger-btn';
        btn.innerHTML = '<i class="bi bi-list"></i>';
        btn.addEventListener('click', toggleSidebar);
        document.body.appendChild(btn);

        var overlay = document.createElement('div');
        overlay.id = 'mtSidebarOverlay';
        overlay.className = 'sidebar-mobile-overlay';
        overlay.addEventListener('click', closeSidebar);
        document.body.appendChild(overlay);
    });
})();
</script>
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
