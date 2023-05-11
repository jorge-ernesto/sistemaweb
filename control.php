<?php
	include('start.php');

	$Controlador='';

	switch (substr($rqst, 0 ,strcspn($rqst,'.'))){
		case "LOGIN":
			error_log("LOGIN");
			include('login/c_login.php');
		    $Controlador = new LoginController(substr($rqst, strcspn($rqst,'.')+1));
		    break;
		default:
			error_log("default");
		    include('main/c_main.php');
		    $Controlador = new MainController(substr($rqst, strcspn($rqst,'.')+1));
			break;
	}

	$Controlador->Run();
	echo $Controlador->outputVisor();
