<?php
	session_start();
	$tmp = $_SESSION['auth_usuario'];
	session_destroy();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<!-- Roberto Palma Garcia - rpalma@integrado.com - cel: 99656728 -->
<html><link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
	<HEAD>
		<title><?php echo "$title Finalizar Sesi&oacute;n"; ?></title>
		<META NAME="Author" CONTENT="Roberto Palma Garcia">
		<META NAME="Description" CONTENT="rpalma@integrado.com.pe">
		<meta http-equiv='Refresh' content='2;URL=login.php'>
	</HEAD>
	<BODY>
		<div align=center>
			<br/><br/>
			<font size=4 face="Verdana, Arial, Helvetica, sans-serif">Sesion terminada por el usuario <?php echo $tmp; ?></font>
		</div>
	</BODY>
</HTML>
