<?php

class AsientosController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    	}
    
    	function Run() {
	    	ob_start();
		include 'movimientos/m_asientos.php';
		include 'movimientos/t_asientos.php';
	
		$this->Init();
	
		$result   = "";
		$result_f = "";

		$form_search = false;

		switch ($this->action) { 

			case "Excel":
				$anio = trim($_REQUEST['anio']);
				$mes  = trim($_REQUEST['mes']);
				$resultado 	= AsientosModel::AsientosCombustibles($_REQUEST['estacion'], $anio, $mes);
				//$resultados[count($resultados)-1]['total_venta_comb'];
				for ($j=0; $j<count($resultado); $j++) {
					$debe = $debe + $resultado[$j]['debe'];
					$abono = $abono + $resultado[$j]['abono'];
				}
				$resultadoLubri = AsientosModel::AsientosLubricantes($_REQUEST['estacion'], $anio, $mes);
				$resultt  	= AsientosTemplate::reporteExcel($resultado,$resultadoLubri,$debe,$abono);
			break;

		    	default:
				$form_search = true;
			break;
		}
	
		if ($form_search) {
		    	$result = AsientosTemplate::formSearch($_REQUEST['ch_almacen'],date(d."/".m."/".Y), date(d."/".m."/".Y));
		}

		$this->visor->addComponent("ContentT", "content_title", AsientosTemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);	
    	}
}
