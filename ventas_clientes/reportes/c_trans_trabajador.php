<?php

class TransTrabajadorController extends Controller{
	function Init(){
		$this->visor	= new Visor();
		$this->task 	= @$_REQUEST["task"];
		$this->action 	= isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}

	function Run(){
		include('reportes/m_trans_trabajador.php');
		include('reportes/t_trans_trabajador.php'); 
		
		$this->Init();	
		$result 		= '';
		$result_f 		= '';
		$search_form 	= false;

		$objModel = new TransTrabajadorModel();
		$objTemplate = new TransTrabajadorTemplate();

		switch ($this->action){
			case "Reporte":
				$periodo 		= $_REQUEST["periodo"];
				$mes 			= $_REQUEST["mes"];
				$diadesde 		= $_REQUEST["diadesde"];
				$diahasta 		= $_REQUEST["diahasta"];
				$tipo 			= $_REQUEST["tipo"];
				$tiporeporte 	= $_REQUEST["tiporeporte"];
				$t 				= (isset($_REQUEST["t"]) ? $_REQUEST["t"] : '');

				// Add new parameter cash
				$sConsiderarCaja = (isset($_REQUEST["sConsiderarCaja"]) ? $_REQUEST["sConsiderarCaja"] : '');
				$column_caja = ($sConsiderarCaja == "1" ? "t.caja," : "");

				// Add new parameter product
				$iActiveProduct = (isset($_REQUEST["iActiveProduct"]) ? $_REQUEST["iActiveProduct"] : '');
				$column_product = ($iActiveProduct == "1" ? "t.codigo," : "");

				// Add new parameter quantity
				$iActiveQuantity = (isset($_REQUEST["iActiveQuantity"]) ? $_REQUEST["iActiveQuantity"] : '');
				$column_quantity = ($iActiveQuantity == "1" ? "SUM(t.cantidad) AS cantidad," : "");

				// Array new parameter
				$arrParams = array(
					"iActiveProduct" => $iActiveProduct,
					"column_product" => $column_product,
					"iActiveQuantity" => $iActiveQuantity,
					"column_quantity" => $column_quantity,
				);

				if( $tipo=="M" && $tiporeporte=="D" ){ // market detallado
					$arrResponse = $objModel->busquedaMD($periodo, $mes, $diadesde, $diahasta, $t, $column_caja, $arrParams);
					$result_f = $objTemplate->mostrarMD($arrResponse, $tiporeporte, $sConsiderarCaja, $arrParams);
				} else {
					$arrResponse = $objModel->busqueda($periodo, $mes, $diadesde, $diahasta, $tipo, $tiporeporte, $column_caja, $arrParams);
					$result_f = $objTemplate->mostrar($arrResponse, $tiporeporte, $sConsiderarCaja, $arrParams);					
				}
			break;
					
			default:
				$result = $objTemplate->formBuscar();		
			break;
		}

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
