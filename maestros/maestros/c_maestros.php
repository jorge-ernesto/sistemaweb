<?php

class MaestrosController extends Controller
{
    function Init()
    {
	$this->visor = new Visor();
	$this->task = @$_REQUEST["task"];
	isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action="";
    }
    
    function Run()
    {
	$this->Init();
	$Controlador = null;
	switch ($this->request) {

	    case "DIACIERRE":
	    include "maestros/c_cierre_turno.php";
	    $Controlador = new CierreTurnoController("DIACIERRE");
	    break;

	    case 'TIPODECAMBIO':
	    include('maestros/c_tipodecambio.php');
	    $Controlador = new TipodeCambioController();
	    break;

	    case "CONFIGPRODUCTO":
            include('maestros/c_configuracion_producto.php');
       	    $Controlador = new ConfiguracionProductoController();
            break;

	    case "PRODXPROV":
		include "maestros/c_productoxproveedor.php";
		$Controlador = new ProductoxProveedorController("PRODXPROV");
		break;

	    case "ITEMS":
		include "maestros/c_items.php";
		$Controlador = new ItemsController("ITEMS");
		break;
	    case 'PROVEEDOR':
		//echo "ENTRO AKI";
		include('maestros/c_proveedores.php');
		$Controlador = new ProveedoresController($this->task);
	    break;
	    
	    case 'ESPECIALES':
	    
	    include('maestros/c_especiales.php');
		$Controlador = new EspecialesController($this->task);
	    break;
	    
	    case "TRABAJADOR":
            include('maestros/c_trabajador.php');
       		$Controlador = new TrabajadorController($this->task);
        	break;
  	    
	    case "TRABAJADORXISLA":
            include('maestros/c_trabajadorxIsla.php');
       		$Controlador = new TrabajadorxIslaController($this->task);
        	break;

	    case "TANQUES":
            include('maestros/c_tanques.php');
       		$Controlador = new TanquesController($this->task);
        	break;
	    
	   case 'SERIEDOCUMENTO':
	   include('maestros/c_serie_documento.php');
	   $Controlador = new SerieDocumentoController();
	   break;

	    default:
		$this->visor->AddComponent("ContentB", "content_body", "<h2>Maestro no conocido</h2>");
		break;
	}
	
	if ($Controlador != null) {
	    $Controlador->Run();
	    $this->visor = $Controlador->visor;
	}
    }
}

