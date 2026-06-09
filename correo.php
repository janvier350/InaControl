<?php 

if(isset($_POST['enviar'])){
	if(!empty($_POST['nombres']) && !empty($_POST['email']) && !empty($_POST['mensaje']) && !empty($_POST['asunto'])){
		$asunto = $_POST[asunto];
		$name = $_POST[nombres];
		$email = $_POST[email];
		$mensaje = $_POST[mensaje];

		$header = "From: noreply@example.com" . "\r\n";
		$header.= "Reply-To: noreply@example.com" . "\r\n";
		$header.="X-Mailer: PHP/". phpversion();
		$mail = @mail($email, $asunto, $mensaje, $header);
		if($mail){
			echo "<h4> E-Mail, enviado </h4>";
		}else{
			echo "<h4> Fallo </h4>";
		}
	}
}

 ?>