<?php
function conectarse_MT() {
    $config = require __DIR__ . '/db_config_MT.php';
    $link = @mysqli_connect($config['host'], $config['user'], $config['pass']);
    if (!$link) {
        return null;
    }
    if (!mysqli_select_db($link, $config['name'])) {
        mysqli_close($link);
        return null;
    }
    mysqli_set_charset($link, "utf8mb4");
    return $link;
}
