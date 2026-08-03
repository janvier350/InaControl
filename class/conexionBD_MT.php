<?php
function conectarse_MT() {
    $config = require __DIR__ . '/db_config_MT.php';

    // En PHP 8.1+ mysqli lanza excepciones por defecto ante errores de conexión.
    // Desactivamos ese modo para poder manejar el fallo sin tumbar la página.
    mysqli_report(MYSQLI_REPORT_OFF);

    try {
        $link = @mysqli_connect($config['host'], $config['user'], $config['pass']);
    } catch (\Throwable $e) {
        return null;
    }

    if (!$link) {
        return null;
    }

    if (!@mysqli_select_db($link, $config['name'])) {
        mysqli_close($link);
        return null;
    }

    mysqli_set_charset($link, "utf8mb4");
    return $link;
}
