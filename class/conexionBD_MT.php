<?php
function conectarse_MT() {
    $config = require __DIR__ . '/db_config_MT.php';
    $link = mysqli_connect($config['host'], $config['user'], $config['pass']);
    if (!$link) {
        die("Error de conexión multi-tenant: " . mysqli_connect_error());
    }
    mysqli_select_db($link, $config['name']) or die("BD multi-tenant no encontrada: " . $config['name']);
    mysqli_set_charset($link, "utf8mb4");
    return $link;
}
