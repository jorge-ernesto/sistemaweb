<?php

class CierresZController extends Controller {
	function Init()  {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_cierres_z.php';
		include 'reportes/t_cierres_z.php';

		$this->Init();
		$result = "";
		$result_f = "";
		$form_search = false;
		$listado = false;

		switch($this->action) {		
			case "Buscar":
			$listado = true;
			break;
			
			default:
			$form_search = true;
			break;
		}

		if($form_search) {
			$almacenes = CierresZModel::obtenerListaEstaciones();
			$result = CierresZTemplate::formSearch($almacenes);
		}

		if($listado) {			
			$fecha_desde = explode("-", $_REQUEST['ch_fecha_del']);
			$fecha_desde = $fecha_desde[2]."/".$fecha_desde[1]."/".$fecha_desde[0];

			$fecha_hasta = explode("-", $_REQUEST['ch_fecha_al']);
			$fecha_hasta = $fecha_hasta[2]."/".$fecha_hasta[1]."/".$fecha_hasta[0];

			$_REQUEST['ch_fecha_del'] = $fecha_desde;
			$_REQUEST['ch_fecha_al'] = $fecha_hasta;

			//echo "<script>console.log('" . json_encode($_REQUEST) . "')</script>";

			$resultados = CierresZModel::busqueda($_REQUEST['ch_fecha_del'], $_REQUEST['ch_fecha_al'], $_REQUEST['ch_almacen'], $_REQUEST['ch_caja'], "");
			$totales = CierresZModel::totales($_REQUEST['ch_fecha_del'], $_REQUEST['ch_fecha_al'], $_REQUEST['ch_almacen'], $_REQUEST['ch_caja'], "");
			$result_f = CierresZTemplate::listado($resultados, $totales);
		}
	
		$this->visor->addComponent("ContentT", "content_title", CierresZTemplate::titulo());
		if($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
