<?php
class MovPuntosFidelizaController extends Controller{
		
	function Init(){
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}
	
	function Run(){
		$this->Init();

		$result = '';
		$bolMensaje ='0';

		include('promociones/m_movpuntosfideliza.php');
		include('promociones/t_movpuntosfideliza.php'); 
		include('../include/paginador_new.php');
		require("../clases/funciones.php");	
		$funcion = new class_funciones;
		
		$this->visor->addComponent('ContentT', 'content_title',MovPuntosFidelizaTemplate::titulo());

		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']){
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}

		$fechaini = date("d/m/Y");
		$fechafin = date("d/m/Y");

		switch ($this->request){
		case 'MOVPUNTOSFIDELIZA':

			$tablaNombre = 'MOVPUNTOSFIDELIZA';
			$listado = false;

			switch ($this->action){
				case 'Consultar':
					$almacen 		= trim($_REQUEST['almacen']);
					$numeroveces 	= trim($_REQUEST['numveces']);
					$fechaini 		= trim($_REQUEST['fechainicio']);
					$fechafin 		= trim($_REQUEST['fechafin']);
					$ruc 			= trim($_REQUEST['ruc']);

					$busqueda =MovPuntosFidelizaModel::tmListado($almacen,$numeroveces,$fechaini,$fechafin,$ruc,$_REQUEST['rxp'],$_REQUEST['pagina']);
					$result = MovPuntosFidelizaTemplate::formBuscar($fechaini, $fechafin);
					$tamaniopuntos = count($busqueda['datos']);
					$result .= MovPuntosFidelizaTemplate::formMovimientopuntos($tamaniopuntos);
					$result .= MovPuntosFidelizaTemplate::formPaginacion($busqueda['paginacion'],$numeroveces,$fechaini,$fechafin,$tamaniopuntos);
					$result .= MovPuntosFidelizaTemplate::listado($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					break;

				default:
					$listado = true;
					break;
			}

			if ($listado) { 
				$result    = MovPuntosFidelizaTemplate::formBuscar($fechaini, $fechafin);
				$this->visor->addComponent("ContentB", "content_body", $result);
			}
		}
	}
  }

