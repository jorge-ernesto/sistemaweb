<?php

class StkMinMaxController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    	}
    
    	function Run() {
		ob_start();
		include 'reportes/m_stkminmax.php';
		include 'reportes/t_stkminmax.php';
		//include 'libexcel/Workbook.php';
		//include 'libexcel/Worksheet.php';
	
		$this->Init();
		$result = '';
		$result_f = '';
		$search_form = false;

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		switch ($this->action) { 

			case "Buscar":
				$busqueda    	= StkMinMaxModel::buscar($_REQUEST['almacen'],$_REQUEST['periodo'],$_REQUEST['mes'],$_REQUEST['opcion'],$_REQUEST['orden']);
				$result_f 	= StkMinMaxTemplate::reporte($busqueda, $_REQUEST['opcion'], $_REQUEST['almacen'], $_REQUEST['periodo'], $_REQUEST['mes'],$_REQUEST['orden']);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;

			case "Excel":
				$busqueda    	= StkMinMaxModel::buscar($_REQUEST['almacen'],$_REQUEST['periodo'],$_REQUEST['mes'],$_REQUEST['opcion'],$_REQUEST['orden']);
				$result_f 	= StkMinMaxTemplate::reporteExcel($busqueda, $_REQUEST['opcion'], $_REQUEST['almacen'], $_REQUEST['periodo'], $_REQUEST['mes'],$_REQUEST['orden']);
				break;

		    	default:
				$result     	= StkMinMaxTemplate::searchForm();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;
		}		
	}
}
