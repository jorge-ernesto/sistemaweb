<?php

class MainController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    }
    
    function Run()
    {
	include "login/m_main.php";
	include "login/t_main.php";
	include "lib/usuarios.inc.php";
	$this->Init();

	$result = "";
	$result_f = "";

	$form_login = false;
	$bFailed = 0;

	if (isset($_REQUEST['almacen']))	//Alvaro
		$_SESSION['almacen'] = $_REQUEST['almacen'];	//Alvaro

	switch ($this->action) {
/*	    case "mainmenu":	//Alvaro
		$form_login = true;	//Alvaro
		$usuario = new CUsuarios();	//Alvaro
		$almacen=$_SESSION['almacen'];	//Alvaro
		break;	//Alvaro*/
	    case "check":
		$form_login = true;
		$usuario = CUsuarios::login($_REQUEST['user'], $_REQUEST['password']);
		var_dump($usuario);
		$almacen=$_SESSION['almacen'];
		if (!$usuario || !$usuario->almacenPermitido($almacen)) {
			$bFailed = 1;
			$form_login = true;
		} else {
			$usuario->ponerAlmacenActual($almacen);
			$_SESSION['sist'] = "000004";
			$_SESSION['usuario'] = $usuario;
			die("<script>top.location.href=\"/sistemaweb/menu_princ.php\"</script>");
		}
		break;
/*	    case "login":
		//$almacen = $_REQUEST['almacen'];	//Alvaro
		$almacen=$_SESSION['almacen'];	//Alvaro
		$usuario = new CUsuarios();
		if (!$usuario || !$usuario->almacenPermitido($almacen)) {
				$form_login = true;
		    $bFailed = 2;
		    break;
		}
		$usuario->ponerAlmacenActual($almacen);
		$result = MainTemplate::Redirect($sistema, $almacen, $usuario);
		break;*/
	    case "logout":
		if (isset($_SESSION['usuario'])) {
		    $_SESSION['usuario']->logout();
		    unset($_SESSION['usuario']);
		}
		header("Location: /sistemaweb/");
		return;
	    default:
		$form_login = true;
		break;
	}
	
	if ($form_login) {
		$almacenes = MainModel::obtenerAlmacenes();
//		$almacenes = $usuario->getAlmacenes();
		$result = MainTemplate::formLogin($bFailed, $_REQUEST['user'], $almacenes);
	}

	$this->visor->addComponent("ContentT", "content_title", MainTemplate::Header());
	if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
	if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}

?>
