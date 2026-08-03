<?php
@session_start();
session_destroy();
header("Location: EMPRESA_login.php");
exit();
