<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Formulario registro - envio correo</title>
</head>
<body>
	<form method="post">
		<label>Nombres</label>
		<input type="text" name="nombres" value="Nombres completos">
		<input type="email" name="email" value="jvaras@overclocking.com.ec">
		<input type="text" name="asunto" value="Link De Acceso">
		<textarea name="mensaje" value="Mensaje"> </textarea>
		<input type="submit" name="Enviar">


	</form>

	<?php 
		include("correo.php");
	 ?>
</body>

</html>