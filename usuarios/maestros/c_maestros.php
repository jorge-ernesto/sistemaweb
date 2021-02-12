<?php

class MaestrosController extends Controller
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
	    case "USERS":
		include "maestros/c_usrmaster.php";
		$Controller = new UserMasterController("USERS");
		break;
	    case "GROUPS":
		include "maestros/c_grpmaster.php";
		$Controller = new GroupMasterController("GROUPS");
		break;
	    case "MODULES":
		include "maestros/c_modmaster.php";
		$Controller = new ModMasterController("MODULES");
		break;
	    default:
		$this->visor->addComponent("ContentB", "content_body", "<h2><b>Funcion de usuarios no conocida</b></h2>");
		break;
	}
	
	if ($Controller != null) {
	    $Controller->Run();
	    $this->visor = $Controller->visor;
	}
    }
}

