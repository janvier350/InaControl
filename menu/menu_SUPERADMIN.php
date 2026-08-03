<!-- SUPERADMIN Sidebar -->
<nav class="col-md-2 d-none d-md-block bg-dark sidebar" style="min-height:100vh; padding-top:1rem;">
    <div class="position-sticky">
        <div class="text-center mb-4 px-3">
            <span class="badge bg-danger fs-6 w-100 py-2">SUPERADMIN</span>
            <div class="text-white-50 small mt-1"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?></div>
        </div>
        <ul class="nav flex-column px-2">
            <li class="nav-item mb-1">
                <span class="text-uppercase text-secondary small px-2 d-block mb-1" style="font-size:0.7rem; letter-spacing:1px;">Panel Principal</span>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center gap-2 rounded <?php echo basename($_SERVER['PHP_SELF']) === 'ADMIN_Empresas.php' ? 'active bg-primary' : ''; ?>"
                   href="../ADMIN_Empresas.php">
                    <i class="fa fa-building"></i> Empresas
                </a>
            </li>
            <li class="nav-item mt-3 mb-1">
                <span class="text-uppercase text-secondary small px-2 d-block mb-1" style="font-size:0.7rem; letter-spacing:1px;">Sesión</span>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center gap-2 rounded"
                   href="../class/logout.php">
                    <i class="fa fa-sign-out"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</nav>
