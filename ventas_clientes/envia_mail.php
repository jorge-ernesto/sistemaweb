<html>
<head>
<title>Envio de puntos bonus del dia</title>
</head>
<body>

<?php
require_once('/sistemaweb/phpmailer/class.phpmailer.php');
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

$mail->IsSMTP(); // telling the class to use SMTP

try {
 // $mail->Host       = "mail.opensysperu.com"; // SMTP server
  $mail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
  $mail->SMTPAuth   = true;                  // enable SMTP authentication
  $mail->Host       = "mail.opensysperu.com"; // sets the SMTP server
  $mail->Port       = 25;                    // set the SMTP port for the GMAIL server
  $mail->Username   = "rrosales@opensysperu.com"; // SMTP account username
  $mail->Password   = "rrosales";        // SMTP account password
//  $mail->AddReplyTo('rrosales@opensysperu.com', 'Rocio Rosales');
  $mail->AddAddress('rrosales@opensysperu.com', 'Rocio Rosales');
  $mail->SetFrom('rrosales@opensysperu.com', 'Rocio Alva');
  $mail->AddReplyTo('rrosales@opensysperu.com', 'Rocio Alva');
  $mail->Subject = 'Prueba de PHPMailer mail()';
$body = "Puntos bonus generados en el dia";
$mail->MsgHTML($body);
  $mail->AddAttachment('/sistemaweb/images/tux2.png');      // attachment
  $mail->Send();http://172.18.5.60/sistemaweb/ventas_clientes/envia_mail.php
  echo "Message Sent OK</p>\n";
} catch (phpmailerException $e) {
  echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
  echo $e->getMessage(); //Boring error messages from anything else!
}
?>





<?php
/*require_once('/sistemaweb/phpmailer/class.phpmailer.php');

$mail = new PHPMailer();
$mail->AddReplyTo("rrosales@opensysperu.com","Rocio Rosales");
$mail->SetFrom('rrosales@opensysperu.com', 'Rocio Rosales');
$mail->AddReplyTo("rrosales@opensysperu.com","Rocio Rosales");

//**********
$address = "pcevallos@smv.gob.pe";
$mail->AddAddress($address, "Antonio Cevallos Alianza");
//**********

$mail->Subject    = "Puntos bonus del dia";
$body = "Puntos bonus generados en el dia";
$mail->MsgHTML($body);
$mail->AddAttachment("/tmp/imprimir/bonus_mail.txt");  

if(!$mail->Send()) {
	echo "Error en el envio: " . $mail->ErrorInfo;
} else {
	echo "Mensaje Enviado !";
}*/
?>

</body>
</html>
