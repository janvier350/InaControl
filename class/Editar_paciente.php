<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();
?>
<?php       
    $IdPaciente =$_REQUEST['IdPaciente'];
    $nombres =$_POST['nombres'];
    $apellidos =$_POST['apellidos'];
    $email =$_POST['email'];
    $feNac =$_POST['feNac'];
    $telefono =$_POST['telefono'];
        $sql= "UPDATE AG_PACIENTE SET NOMBRES ='".$nombres."', APELLIDOS='".$apellidos."',EMAIL ='".$email."',FECHANACIMIENTO='".$feNac."',TELEFONO='".$telefono."' WHERE IDPACIENTE = ".$IdPaciente ;                    
        $consulta = $conexion->query ($sql) or die ("Problemas al Editar datos:<br>".mysqli_error($conexion));
        //Insert status
        if($consulta){
            echo 'Event data inserted successfully.. Event ID: '.$conexion->insert_id;
            echo "<script>javascript: alert('Datos Editados Correctamente!') </script>";    
            echo "<Script language='JavaScript'>";
            echo 'self.location = "../Pacientes.php"';
            echo"</script>"; 
        }else{
            echo 'Failed to insert '.$consulta.'event data'.mysqli_error($conexion);
        }    
    

?>

