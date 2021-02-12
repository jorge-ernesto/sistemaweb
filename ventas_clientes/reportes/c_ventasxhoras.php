<?php
class VentasxHorasController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_ventasxhoras.php';
		include 'reportes/t_ventasxhoras.php';

		$this->Init();

		$result = '';
		$result_f = '';
		$search_form = false;

		switch ($this->action) {
			case "Reporte":
			echo ' Desde: '.$_REQUEST['desde'].' - Hasta:'. $_REQUEST['hasta'].' - Dia:'. $_REQUEST['diasemana'].' - Producto:'. $_REQUEST['producto'].' - Lado:'. $_REQUEST['lado'].' - Estacion:'. $_REQUEST['estacion'].' - Local:'. $_REQUEST['local'].' - Importe:'. $_REQUEST['importe'].' - Resumido:'.$_REQUEST['modo'];

			if ($_REQUEST['local'] == "COMBUSTIBLE") $local = false; else $local = true;
			if ($_REQUEST['importe'] == "CANTIDAD") $importe = false; else $importe = true;
			if ($_REQUEST['modo'] == "DETALLADO") $bResumido = false; else $bResumido = true;
			if (substr($_REQUEST['desde'],2,7) != substr($_REQUEST['hasta'],2,7)) {
				//$results = '<center><blink>El intervalo de fechas debe ser dentro del mismo mes y a&ntilde;o</blink></center>';
				$result_f = '<center><blink>El intervalo de fechas debe ser dentro del mismo mes y a&ntilde;o</blink></center>';

				//$this->visor->addComponent("ContentB", "content_body", $results);
				//$this->visor->addComponent("ContentF", "content_footer", $result_f);
			} else if ($_REQUEST['producto']== "TODOS" and $_REQUEST['dato']== "CANTIDAD") {
				$results = '<center><blink>El intervalo de fechas debe ser dentro del mismo mes y a&ntilde;o</blink></center>';
				$result_f = VentasxHorasTemplate::reporte($results, $_REQUEST['desde'], $_REQUEST['hasta'],$_REQUEST['diasemana'],$_REQUEST['producto'], $_REQUEST['lado'], $_REQUEST['estacion'], $_REQUEST['local'], $_REQUEST['importe'], $_REQUEST['modo']);

				//$this->visor->addComponent("ContentB", "content_body", $results);
				//$this->visor->addComponent("ContentF", "content_footer", $result_f);
			} else {
				$results = VentasxHorasModel::obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['diasemana'], $_REQUEST['producto'], $_REQUEST['lado'], $_REQUEST['estacion'], $local, $importe,$bResumido);
				
				echo "<script>console.log('" . json_encode( $_REQUEST ) . "')</script>";
				echo "<script>console.log('" . json_encode( $results ) . "')</script>";

				$result_f = $result_f1.'<br/>'.VentasxHorasTemplate::reporte($results, $_REQUEST['desde'], $_REQUEST['hasta'],$_REQUEST['diasemana'],$_REQUEST['producto'], $_REQUEST['lado'], $_REQUEST['estacion'], $_REQUEST['local'], $_REQUEST['importe'], $_REQUEST['modo']);

				//$this->visor->addComponent("ContentB", "content_body", $results);
				//$this->visor->addComponent("ContentF", "content_footer", $result_f);
			}
			break;

			case "pdf":
			if ($_REQUEST['modo'] == "DETALLADO") $bResumido = false; else $bResumido = true;
			$results = VentasxHorasModel::obtieneVentas($_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['diasemana'], $_REQUEST['producto'], $_REQUEST['lado'], $_REQUEST['estacion'], $local, $importe, $bResumido);
			VentasxHorasTemplate::reportePDF($results, $_REQUEST['desde'], $_REQUEST['hasta'], $_REQUEST['diasemana'], $_REQUEST['producto'], $_REQUEST['lado'], $_REQUEST['estacion'], $local, $importe, $bResumido);
			break;

			default:
			$search_form = true;
			break;
		}

		if ($search_form) {
			$result = VentasxHorasTemplate::search_form();
		}

		if ($result != '') $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != '') $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
