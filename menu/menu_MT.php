
<style>
    @media (max-width: 991px) {
        .app-sidebar {
            position: fixed !important;
            top: 0; left: -280px;
            height: 100vh !important;
            width: 260px !important;
            z-index: 1060;
            transition: left .25s ease;
            overflow-y: auto;
        }
        body.mt-sidebar-open .app-sidebar { left: 0 !important; }
        .mt-sidebar-overlay {
            display: none;
            position: fixed; top:0; left:0; width:100%; height:100%;
            background: rgba(0,0,0,0.45); z-index: 1055;
        }
        body.mt-sidebar-open .mt-sidebar-overlay { display: block; }
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
    }
    .mt-hamburger-btn { display: none; }
</style>
<button type="button" class="mt-hamburger-btn" id="mtHamburgerBtn"><i class="bi bi-list"></i></button>
<div class="mt-sidebar-overlay" id="mtSidebarOverlay"></div>
<script>
(function() {
    function toggleSidebar() { document.body.classList.toggle('mt-sidebar-open'); }
    function closeSidebar() { document.body.classList.remove('mt-sidebar-open'); }
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('mtHamburgerBtn');
        var overlay = document.getElementById('mtSidebarOverlay');
        if (btn) btn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
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
