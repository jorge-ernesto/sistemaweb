<?php
	class VentasEspecialesController extends Controller
	{
		function Init()
		{
			$this->visor = new Visor();
			isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
		}

		function Run(){
			ob_start();
			include 'reportes/m_ventas_especiales.php';
			include 'reportes/t_ventas_especiales.php';
			
			$this->Init();
			$result = "";
			$result_f = "";
			$form_search = true;
			$listado = false;

			switch ($this->action){

				case "TIPO":
					$this->visor->addComponent("SpaceTipos", "space", VentasEspecialesTemplate::listaTipos($_REQUEST['cod']));
				return;

				case "LINEA":
					$this->visor->addComponent("SpaceLineas", "space", VentasEspecialesTemplate::listaLineas($_REQUEST['cod']));
				return;

				case "ARTICULO":
					$this->visor->addComponent("SpaceArticulos", "space", VentasEspecialesTemplate::listaArticulos($_REQUEST['cod']));
				return;

				case "PROVEEDOR":
					$this->visor->addComponent("SpaceArticulos", "space", VentasEspecialesTemplate::listaProveedores($_REQUEST['cod']));
				return;

				case "Buscar":
					$listado = true;
				break;

				case "Excel":

					$resultado = VentasEspecialesModel::obtenerReporteVentasExcel($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['detallado'], $_REQUEST['conigv'], $_REQUEST['forma'], $_REQUEST['orden'], $_REQUEST['condicion'], $_REQUEST['linea'], $_REQUEST['tipo'], $_REQUEST['codigo'], $_REQUEST['exalma']);
					$resultt   = VentasEspecialesTemplate::reporteExcel($resultado, $_REQUEST['exalma'], $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['conigv']);

				break;

				case "ExcelTotal":

					$resultado = VentasEspecialesModel::obtenerReporteVentasExcelTotales($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['detallado'], $_REQUEST['conigv'], $_REQUEST['forma'], $_REQUEST['orden'], $_REQUEST['condicion'], $_REQUEST['linea'], $_REQUEST['tipo'], $_REQUEST['codigo'], $_REQUEST['exalma']);
					$resultt   = VentasEspecialesTemplate::reporteExcelTotales($resultado, $_REQUEST['exalma'], $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['conigv']);

				break;

				default:
					$form_search = true;
					break;
			}
			
			if ($form_search){
				$desde = $_REQUEST['desde'];
				$hasta = $_REQUEST['hasta'];
				$almacenes = VentasEspecialesModel::obtenerAlmacenes();
				$result = VentasEspecialesTemplate::formSearch($almacenes, $desde, $hasta);
				$this->visor->addComponent("ContentB", "content_body", $result);
			}

			if ($listado){

				$resultados = VentasEspecialesModel::obtenerReporteVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['detallado'], $_REQUEST['conigv'], $_REQUEST['forma'], $_REQUEST['orden'], $_REQUEST['condicion'], $_REQUEST['linea'], $_REQUEST['tipo'], $_REQUEST['codigo'], $_REQUEST['exalma']);
				$result_f = VentasEspecialesTemplate::reporte($resultados, $_REQUEST['conigv']);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
			}
			
			$this->visor->addComponent("ContentT", "content_title", VentasEspecialesTemplate::titulo());
		}
	}
