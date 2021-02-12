<?php
	class VentasxProveedorController extends Controller
	{
		function Init()
		{
			$this->visor = new Visor();
			isset($_REQUEST['action']) ? $this->action = $_REQUEST['action'] : $this->action = "";
		}

		function Run()
		{
			ob_start();
			include 'reportes/m_ventasxproveedor.php';
			include 'reportes/t_ventasxproveedor.php';
			
			$this->Init();
			$result = "";
			$result_f = "";
			$form_search = true;
			$listado = false;

			switch ($this->action) 
			{
				case "TIPO":
					$this->visor->addComponent("SpaceTipos", "space", VentasxProveedorTemplate::listaTipos($_REQUEST['cod']));
					return;
				case "LINEA":
					$this->visor->addComponent("SpaceLineas", "space", VentasxProveedorTemplate::listaLineas($_REQUEST['cod']));
					return;
				case "ARTICULO":
					$this->visor->addComponent("SpaceArticulos", "space", VentasxProveedorTemplate::listaArticulos($_REQUEST['cod']));
					return;
				case "PROVEEDOR":
					$this->visor->addComponent("SpaceArticulos", "space", VentasxProveedorTemplate::listaProveedores($_REQUEST['cod']));
					return;
				case "Buscar":
					$listado = true;
					break;

				case "Excel":

					$resultado = VentasxProveedorModel::obtenerReporteVentasExcel($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['detallado'], $_REQUEST['conigv'], $_REQUEST['forma'], $_REQUEST['orden'], $_REQUEST['condicion'], $_REQUEST['linea'], $_REQUEST['tipo'], $_REQUEST['codigo'], $_REQUEST['proveedor'],$_REQUEST['exalma']);
					$resultt   = VentasxProveedorTemplate::reporteExcel($resultado, $_REQUEST['exalma'], $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['conigv']) ;

					break;

				default:
					$form_search = true;
					break;
			}
			
			if ($form_search){
				$desde = $_REQUEST['desde'];
				$hasta = $_REQUEST['hasta'];
				$almacenes = VentasxProveedorModel::obtenerAlmacenes();
				$result = VentasxProveedorTemplate::formSearch($almacenes, $desde, $hasta);
				$this->visor->addComponent("ContentB", "content_body", $result);
			}

			if ($listado)
			{
				$resultados = VentasxProveedorModel::obtenerReporteVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['detallado'], $_REQUEST['conigv'], $_REQUEST['forma'], $_REQUEST['orden'], $_REQUEST['condicion'], $_REQUEST['linea'], $_REQUEST['tipo'], $_REQUEST['codigo'], $_REQUEST['proveedor'],$_REQUEST['exalma']);
				$result_f = VentasxProveedorTemplate::reporte($resultados, $_REQUEST['conigv']);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
			}
			
			$this->visor->addComponent("ContentT", "content_title", VentasxProveedorTemplate::titulo());
			//if ($result != "")
			//	$this->visor->addComponent("ContentB", "content_body", $result);
			//if ($result_f != "")
			//	$this->visor->addComponent("ContentF", "content_footer", $result_f);
		}
	}
