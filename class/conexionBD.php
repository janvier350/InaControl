<?php

function conectarse()

{

$db_host="localhost"; // Host BD al que conectarse, habitualmente es localhost

$db_nombre="overcloc_INASAR"; // Nombre de la Base de Datos que se desea utilizar

$db_user="overcloc_INASAR"; // Nombre del usuario con permisos para acceder a la BD

$db_pass="SjYF{dL9ddMR"; // Contrase

// Ahora estamos realizando una conexión y la llamamos $link

$link= mysqli_connect($db_host, $db_user, $db_pass);

// Seleccionamos la base de datos que nos interesa

mysqli_select_db($link, $db_nombre) or die("Error seleccionando la base de datos.");

// Retornamos $link  para hacer consultas a la BD.

mysqli_set_charset($link, "utf8");
return $link;

}
//$link=conectarse();

?>