<?php

class HelperController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
    }
    
    function Run()
    {
	$this->Init();
	
	$Controlador = null;

	switch ($this->request) {
	    case "ARTICULO":
		include 'helper/c_articulo.php';
		$Controlador = new ArticuloController("ARTICULO");
		break;
	    default:
		break;
	}
	
	if ($Controlador != null) {
	    $Controlador->Run();
	    $this->visor = $Controlador->visor;
	}
    }
    
}

?>