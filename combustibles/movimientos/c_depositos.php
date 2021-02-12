<?php

class DepositosPosController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
    	}

    	function Run() {
	    	ob_start();
		include 'movimientos/m_depositos.php';
		include 'movimientos/t_depositos.php';  

		$this->Init();

		$ip 		= "";
		$result 	= "";
		$result_f 	= "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

        	switch($this->action) {
        	
			case "Buscar":
					$result    = DepositosPosTemplate::formBuscar(@$_REQUEST['almacen'], @$_REQUEST['dia1'], @$_REQUEST['turno1'], @$_REQUEST['dia2'], @$_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find'],@$_REQUEST['denomina']);		
				    	$res       = DepositosPosModel::busqueda($_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find'],$_REQUEST['denomina']);	    	
						$result_f  = DepositosPosTemplate::listado($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find'],$_REQUEST['denomina']);						
					break;
					
			case "Validar": 
					$res	   = DepositosPosModel::validar($_REQUEST['conf'], $_REQUEST['vec_check'], $_REQUEST['val'],$_REQUEST['almacen'], $_REQUEST['dia'], $_REQUEST['turno']);	
					if($res==0) {
						?><script>alert("<?php echo 'No se puede validar dia y turno ya consolidados.'; ?> ");</script><?php
					}					
					$result    = DepositosPosTemplate::formBuscar($_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find'],$_REQUEST['denomina']);		
				    	$res       = DepositosPosModel::busqueda($_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find'],$_REQUEST['denomina']);	    	
				    	$result_f  = DepositosPosTemplate::listado($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find'],$_REQUEST['denomina']);				
					break;
					
			case "PDF":
					$res       = DepositosPosModel::busqueda($_SESSION['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find'],$_REQUEST['denomina']);
					$result    = DepositosPosTemplate::reportePDF($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2']) ;
					$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/Depositos.pdf";
					header('Content-type: application/pdf');
					header('Content-Disposition: attachment; filename="'."Depositos.pdf".'"');
					readfile($mi_pdf);
					break;
			
			case "Excel":
					$res       = DepositosPosModel::busqueda($_SESSION['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find'],@$_REQUEST['denomina']);
					$resultt   = DepositosPosTemplate::reporteExcel($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2']) ;
					break;

		    	default:
			
					$almacen = 'TODOS';

					$result    = DepositosPosTemplate::formBuscar($almacen, "", "", "", "", "", "");		
				    	$res       = DepositosPosModel::busqueda($almacen, date("d/m/Y"), "", date("d/m/Y"), "", "", "", "");	    	
				    	$result_f  = DepositosPosTemplate::listado($res, $_REQUEST['almacen'], $_REQUEST['dia1'], $_REQUEST['turno1'], $_REQUEST['dia2'], $_REQUEST['turno2'], $_REQUEST['busqueda'], $_REQUEST['find'],@$_REQUEST['denomina']);
					break;
		}

		// echo "<script>console.log('" . json_encode($res) . "')</script>";		
		// echo "<script>console.log('" . json_encode($_REQUEST['almacen']) . "')</script>";
		// echo "<script>console.log('" . json_encode($_REQUEST['dia1']) . "')</script>";
		// echo "<script>console.log('" . json_encode($_REQUEST['turno1']) . "')</script>";
		// echo "<script>console.log('" . json_encode($_REQUEST['dia2']) . "')</script>";		
		// echo "<script>console.log('" . json_encode($_REQUEST['turno2']) . "')</script>";
		// echo "<script>console.log('" . json_encode($_REQUEST['busqueda']) . "')</script>";
		// echo "<script>console.log('" . json_encode($_REQUEST['find']) . "')</script>";
		// echo "<script>console.log('" . json_encode($_REQUEST['denomina']) . "')</script>";		
		// die();

		$this->visor->addComponent("ContentT", "content_title", DepositosPosTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}
}
