<?php

class Reporte_NumTransFidelizaController extends Controller{
		
	function Init(){
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}
	
	function Run(){
		$this->Init();
		$result = '';
		$bolMensaje ='0';	
		include('promociones/m_reporte_numtransfideliza.php');
		include('promociones/t_reporte_numtransfideliza.php'); 
		include('../include/paginador_new.php');
		require("../clases/funciones.php");	
		$funcion = new class_funciones;
		
		$this->visor->addComponent('ContentT', 'content_title',Reporte_NumTransFidelizaTemplate::titulo());

		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']){
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}

		$fechaini = date("d/m/Y");
		$fechafin = date("d/m/Y");

		switch ($this->request){
			case 'REPORTE_NUMTRANSFIDELIZA':
			$tablaNombre = 'REPORTE_NUMTRANSFIDELIZA';
			$listado = false;

			switch ($this->action){	
				case 'Consultar':
					$almacen 	= trim($_REQUEST['almacen']);
					$turno 		= trim($_REQUEST['turno']);
					$fechaini 	= trim($_REQUEST['fechainicio']);
					$fechafin 	= trim($_REQUEST['fechafin']);

					Reporte_NumTransFidelizaModel::generarReporte($almacen,$turno,$fechaini,$fechafin);

					$busqueda = Reporte_NumTransFidelizaModel::tmListado($almacen,$turno,$fechaini,$fechafin,$_REQUEST['rxp'],$_REQUEST['pagina']);
					$info = Reporte_NumTransFidelizaModel:: obtenerAlmacenes();
					$result = Reporte_NumTransFidelizaTemplate::formBuscar($fechaini, $fechafin);
					$tamaniopuntos = count($busqueda['datos']);
					$result  .= Reporte_NumTransFidelizaTemplate::formMovimientopuntos($tamaniopuntos);
					$result .= Reporte_NumTransFidelizaTemplate::formPaginacion($busqueda['paginacion'],$fechaini,$fechafin, $tamaniopuntos);
					$result .= Reporte_NumTransFidelizaTemplate::listado($busqueda['datos']);

					$this->visor->addComponent("ContentB", "content_body", $result);
					break;	
				default:
					$listado = true;
					break;
			}

			if ($listado) {
				$result    = Reporte_NumTransFidelizaTemplate::formBuscar($fechaini, $fechafin);
				$this->visor->addComponent("ContentB", "content_body", $result);
			}
		}
	}
}

