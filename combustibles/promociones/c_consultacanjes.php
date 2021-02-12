<?php
	
class ConsultaCanjesController extends Controller {
	
	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}

	function Run() {
		$this->Init();
		$result = '';
		$bolMensaje ='0';	

		include('promociones/m_consultacanjes.php');
		include('promociones/t_consultacanjes.php'); 
		include('../include/paginador_new.php');
		require("../clases/funciones.php");	

		$funcion = new class_funciones;

		$this->visor->addComponent('ContentT', 'content_title', ConsultaCanjesTemplate::titulo());

		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']) {
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}

		$fechaini = date("d/m/Y");
		$fechafin = date("d/m/Y");
		
		switch ($this->request) {
			case 'CONSULTACANJES':
				$tablaNombre = 'CONSULTACANJES';
				$listado = false;

				switch ($this->action) {
					case 'Consultar':
						$almacen	= trim($_REQUEST['almacen']);
						$fechaini 	= trim($_REQUEST['fechainicio']);
						$fechafin 	= trim($_REQUEST['fechafin']);
						$filtro 	= strtoupper(trim($_REQUEST['busquedatarjeta']));
						$filtroitem 	= strtoupper(trim($_REQUEST['busquedaitem']));
						$iditemcanje 	= strtoupper(trim($_REQUEST['iditemcanje']));
						$busqueda 	= ConsultaCanjesModel::tmListado($almacen,$filtro,$filtroitem,$fechaini,$fechafin,$_REQUEST['rxp'],$_REQUEST['pagina']);
						$result 	= ConsultaCanjesTemplate::formBuscar($fechaini, $fechafin);
						$tamaniopuntos 	= count($busqueda['datos']);
	
						$result .= ConsultaCanjesTemplate::formPaginacion($busqueda['paginacion'],$filtro,$filtroitem,$fechaini,$fechafin,$tamaniopuntos);
						$result .= ConsultaCanjesTemplate::listado($busqueda['datos']);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;	
    
					default:
						$listado = true;
						break;
				}
				if ($listado) {
	    			$result	= ConsultaCanjesTemplate::formBuscar($fechaini, $fechafin);
		    		$this->visor->addComponent("ContentB", "content_body", $result);
				}
			break;
      	}
	}
}
