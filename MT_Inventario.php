<?php
ob_start();
session_start();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] === "SUPERADMIN" || !isset($_SESSION['id_empresa'])) {
    header("Location: break.php");
    exit();
}
if (time() > $_SESSION['expire']) {
    session_destroy();
    header("Location: expirada.php");
    exit();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kontrol - Inventario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./main.css" rel="stylesheet">
</head>
<body>
<div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">
    <div class="app-main">
        <div class="app-sidebar sidebar-shadow">
            <?php include("./menu/menu_MT.php"); ?>
        </div>
        <div class="app-main__outer">
            <div class="app-main__inner">
                <div class="alert alert-info mt-4">
                    <i class="bi bi-tools"></i> El módulo de Inventario para tu empresa está en construcción. Muy pronto estará disponible.
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
