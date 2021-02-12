<?php

class EspecialesController extends Controller{

	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
	}

	function Run(){

		ob_start();
		include 'reportes/m_especiales.php';
		include 'reportes/t_especiales.php';
		
		$this->Init();
		$result = "";
		$result_f = "";
		$form_search = false;
		$listado = false;

		switch ($this->action) {

				case "TIPO":
					$this->visor->addComponent("SpaceTipos", "space", EspecialesTemplate::listaTipos($_REQUEST['cod']));
				return;

				case "LINEA":
					$this->visor->addComponent("SpaceLineas", "space", EspecialesTemplate::listaLineas($_REQUEST['cod']));
				return;

				case "ARTICULO":
					$this->visor->addComponent("SpaceArticulos", "space", EspecialesTemplate::listaArticulos($_REQUEST['cod']));
				return;

				case "PROVEEDOR":
					$this->visor->addComponent("SpaceArticulos", "space", EspecialesTemplate::listaProveedores($_REQUEST['cod']));
				return;

				case "Reporte":
					$listado = true;
				break;
			
				case "Excel":

					$resultado = EspecialesModel::obtenerReporteVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['detallado'], $_REQUEST['conigv'], $_REQUEST['forma'], $_REQUEST['orden'], $_REQUEST['condicion'], $_REQUEST['linea'], $_REQUEST['tipo'], $_REQUEST['codigo'], $_REQUEST['proveedor'],$_REQUEST['exalma']);
					$resultt   = EspecialesTemplate::reporteExcel($resultado, $_REQUEST['ch_almacen'], $_REQUEST['desde'], $_REQUEST['hasta']) ;

				break;

				default:
					$form_search = true;
				break;
			}
	
			if ($form_search){

				$desde = $_REQUEST['desde'];
				$hasta = $_REQUEST['hasta'];
				$almacenes = EspecialesModel::obtenerAlmacenes();
				$result = EspecialesTemplate::formSearch($almacenes, $desde, $hasta);
				$this->visor->addComponent("ContentB", "content_body", $result);
			}

			if ($listado){

				$resultados = EspecialesModel::obtenerReporteVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['detallado'], $_REQUEST['conigv'], $_REQUEST['forma'], $_REQUEST['orden'], $_REQUEST['condicion'], $_REQUEST['linea'], $_REQUEST['tipo'], $_REQUEST['codigo'], $_REQUEST['proveedor'],$_REQUEST['exalma']);
				$result_f = EspecialesTemplate::reporte($resultados, $_REQUEST['conigv'], "");
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
			}

			
			$this->visor->addComponent("ContentT", "content_title", EspecialesTemplate::titulo());

		}

	}


