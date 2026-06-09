<?php
 @session_start(); //Iniciar una nueva sesión o reanudar la existente
    session_destroy(); //Destruye la sesión
//**** Redireccionar página web *****
header ("Location: index.php"); 
exit();
?>
