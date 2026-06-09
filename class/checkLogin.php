<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
?>
<?php
if ($conexion->connect_error) {
 die("La conexion fall�: " . $conexion->connect_error);
}

$username = $_POST['user'];
$password = $_POST['password'];
 
$sql = "SELECT a.IDADM_USUARIO, a.NOMBRES, a.APELLIDOS,a.USUARIO, a.CONTRASENA, b.IDADM_ROL, b.CARGO
    FROM ADM_USUARIO a INNER JOIN ADM_ROL b ON a.IDADM_ROL = b.IDADM_ROL
    WHERE USUARIO = '$username' AND a.ESTADO = 'A'";
 
$result = $conexion->query($sql);


if ($result->num_rows > 0) {     
 }
 $row = mysqli_fetch_array($result);
    $PASS =$row['CONTRASENA'];
 if (password_verify($password, $row['CONTRASENA'])) { 
    session_start();
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['iduser'] = $row['IDADM_USUARIO'];
    $_SESSION['rol'] = $row['CARGO'];
    $_SESSION['start'] = time();
    $_SESSION['expire'] = $_SESSION['start'] + (60 * 60);

//    echo "Bienvenido! " . $_SESSION['username'];
//    echo "<br><br><a href=../Home.php>Panel de Control</a>";
    echo "<Script language='JavaScript'>";
    echo 'self.location = "../SCH_Calendar_SOP.php"';
     echo"</script>"; 

 } else { 
   echo "Usuario o Password estan incorrectos.";

   echo "<br><a href='../index.php'>Volver a Intentarlo</a>";
 }
 mysqli_close($conexion); 
 ?>

