<?php

class UtilidadBrutaController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}
    
	function Run() {
		include 'reportes/m_utilidad_bruta.php';
		include 'reportes/t_utilidad_bruta.php';
	
		$this->Init();
	
		$result = '';
		$result_f = '';
		$search_form = false;

		switch ($this->action) {
			case "Reporte":
				$almacen = $_REQUEST['estacion'];
				$desde 	 = $_REQUEST['desde'];
				$hasta 	 = $_REQUEST['hasta'];
				$tipo 	 = $_REQUEST['tipo'];
				$uprecio = $_REQUEST['uprecio'];
				$iDetalladoPorDia = trim($_REQUEST['iDetalladoPorDia']);

				$hanio 	 = substr($hasta, 6, 4);
				$hmes 	 = substr($hasta, 3, 2);
				$anio 	 = substr($desde, 6, 4);
				$mes 	 = substr($desde, 3, 2);

				$ucosto_combu  	= UtilidadBrutaModel::ultimo_costo_combustibles($desde,$hasta,$almacen,$uprecio);
				$vta_combu   	= UtilidadBrutaModel::costo_vta_combustibles($desde,$hasta);

				$results  	= UtilidadBrutaModel::obtieneVentas($almacen, $desde, $hasta, $anio, $mes, $iDetalladoPorDia);
				if($results['sStatus'] != 'success'){
					?><script>alert("<?php echo $results['sMessage']; ?> ");</script><?php
				}

				$market  	= UtilidadBrutaModel::obtieneVentasMarket($almacen, $desde, $hasta, $anio, $mes, $tipo, $hanio, $hmes, $uprecio);
				
				if($market == ''){
					?><script>alert("<?php echo 'Ambas fechas deben coincidir en el mismo mes' ; ?> ");</script><?php
				}

				$result_f 	= UtilidadBrutaTemplate::reporte($results['arrData'],$market,$ucosto_combu,$vta_combu, $almacen, $hasta, $uprecio, $iDetalladoPorDia);
				
				break;

			default:
				$search_form = true;
				break;
		}

		if ($search_form) {
		    	$result = UtilidadBrutaTemplate::search_form();
		}
	
		if ($result != '') 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '') 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);
    	}
}
