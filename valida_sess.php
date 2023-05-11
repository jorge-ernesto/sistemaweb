<?php
	include_once "/sistemaweb/lib/usuarios.inc.php";

	//Impide que las paginas se guarden en cache en muchos casos

	ini_set("post_max_size", "15M");
	ini_set("upload_max_filesize", "15M");
	if (!headers_sent()) {
		header('Cache-Control: no-cache, must-revalidate');	// HTTP/1.1
		header('Expires: Mon, 1 Jan 1970 00:00:00 GMT');	// Date in the past
	}

	// error_log("valida_sess");
	// error_log( json_encode($_SESSION['usuario']) );

	// if (!isset($_SESSION['usuario']))
	// {
	// 	header("Location: /sistemaweb/");
	// 	exit;
	// }

	$usuario = $_SESSION['usuario'];

	// if ($usuario->getUID() == -1)
	// {
	// 	header("Location: /sistemaweb/");
	// 	exit;
	// }
