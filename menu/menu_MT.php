
<style>
    .mt-hamburger-btn { display: none; }
    @media (max-width: 991.98px) {
        .mt-hamburger-btn {
            display: flex !important;
            position: fixed !important; top: 12px !important; left: 12px !important;
            z-index: 999999 !important;
            width: 44px; height: 44px; border-radius: 10px;
            background: #0f3460; color: #fff; border: none;
            align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            font-size: 1.3rem;
        }
        .app-main__outer { padding-top: 56px !important; }

        /* Menú a pantalla completa cuando está abierto: sin overlay separado,
           así no hay ningún otro elemento con el que pueda competir por el clic. */
        .app-sidebar {
            position: fixed !important;
            top: 0 !important; left: 0 !important;
            transform: translateX(-100%) !important;
            width: 100vw !important; min-width: 100vw !important; flex: none !important;
            height: 100vh !important;
            margin-top: 0 !important; padding-top: 60px !important;
            overflow-y: auto !important;
            z-index: 999998 !important;
            transition: transform .25s ease !important;
            background: #fff !important;
        }
        body.mt-open .app-sidebar { transform: translateX(0) !important; }

        .mt-close-btn {
            display: none;
            position: absolute; top: 12px; right: 16px; z-index: 999999;
            width: 40px; height: 40px; border-radius: 10px;
            background: #f1f1f1; border: none; font-size: 1.4rem; color: #333;
        }
        body.mt-open .mt-close-btn { display: block; }
    }
</style>
<script>
(function() {
    function openSidebar() { document.body.classList.add('mt-open'); }
    function closeSidebar() { document.body.classList.remove('mt-open'); }

    document.addEventListener('DOMContentLoaded', function() {
        var sidebar = document.querySelector('.app-sidebar');

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'mtHamburgerBtn';
        btn.className = 'mt-hamburger-btn';
        btn.innerHTML = '<i class="bi bi-list"></i>';
        btn.addEventListener('click', openSidebar);
        document.body.appendChild(btn);

        if (sidebar) {
            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'mt-close-btn';
            closeBtn.innerHTML = '&times;';
            closeBtn.addEventListener('click', closeSidebar);
            sidebar.appendChild(closeBtn);
        }

        document.querySelectorAll('.app-sidebar .vertical-nav-menu a').forEach(function(a) {
            a.addEventListener('click', closeSidebar);
        });
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
