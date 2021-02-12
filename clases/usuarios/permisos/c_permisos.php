<?php

class PermisosController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
    }
    
    function Run()
    {
	$this->Init();
	$Controller = null;
	switch ($this->request) {
	    case "MODULOS":
		include "permisos/c_modulos.php";
		$Controller = new ModulosController("MODULOS");
		break;
	    case "ALMACENES":
		include "permisos/c_almacenes.php";
		$Controller = new AlmacenesController("ALMACENES");
		break;
	    case "OPCIONES":
		include "permisos/c_opciones.php";
		$Controller = new OpcionesController("OPCIONES");
		break;
	    case "SELFPWD":
		include "permisos/c_selfpwd.php";
		$Controller = new SelfPwdController("SELFPWD");
		break;
	    case "SISTEMAS":
		include "permisos/c_sistemas.php";
		$Controller = new SistemasController("SISTEMAS");
		break;
	    default:
		$this->visor->addComponent("ContentB", "content_body", "<h2><b>Funcion de permisos no conocida</b></h2>");
		break;
	}
	
	if ($Controller != null) {
	    $Controller->Run();
	    $this->visor = $Controller->visor;
	}
    }
}

