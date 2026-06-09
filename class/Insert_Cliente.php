<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

    //Insert datetime into the database
$cedula = $_POST['cedula'];
$razon_social = $_POST['razon_social'];
$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];
// $sex = $_POST['sex'];
// $gender = $_POST['gender'];
// $feNac = $_POST['feNac'];

$sqlValida = "SELECT * FROM COTI_CLIENTE WHERE CELULAR = '".$telefono."' and ESTADO ='A'";                
$result = $conexion->query($sqlValida);
if ($result->num_rows > 0) {     
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $existe = FALSE;
} else {
    $existe = TRUE;
}  

if ($existe) {
    $sql = "INSERT INTO COTI_CLIENTE (IDENTIFICACION, RAZON_SOCIAL, CELULAR, NOMBRES, APELLIDOS, CORREO, ESTADO) 
            VALUES ('".$cedula."','".$razon_social."','".$telefono."','".$nombres."', '".$apellidos."', '".$email."', 'A')";    

    // Mostrar la consulta SQL antes de ejecutarla
    echo "<script>alert('Consulta SQL: " . addslashes($sql) . "');</script>";

    $consulta = $conexion->query($sql) or die("Problemas al insertar datos:<br>".mysqli_error($conexion));
    
    // Insert status
    if ($consulta) {
        echo 'Event data inserted successfully.. Event ID: '.$conexion->insert_id;
        echo "<script>javascript: alert('Datos Creados Correctamente!') </script>";    
        echo "<Script language='JavaScript'>";
        echo 'self.location = "../PNC_PacienteCrear.php"';
        echo "</script>"; 
    } else {
        echo 'Failed to insert '.$consulta.'event data'.mysqli_error($conexion);
    }    
} else {
    echo "<script>javascript: alert('Paciente ya existe!') </script>";    
    echo "<Script language='JavaScript'>";
    echo 'self.location = "../PNC_PacienteCrear.php"';
    echo "</script>"; 
}


?>

