<?php

class CajaBancoController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }

    function Run() {
	   	ob_start();
		include 'movimientos/m_cajabanco.php';
		include 'movimientos/t_cajabanco.php';

		$objModel = new CajaBancoModel();
		$objTemplate = new CajaBancoTemplate();

		$this->Init();

		//Obtener fecha año y mes de periodo de inicio de sistema.
		$arrPeriodo = $objModel->getPeriodDate();

		$result   = "";
		$result_f = "";

		$form_search 	= false;
		$viewListadoHTML = false;
		$viewListadoEXCEL = false;
		$viewListadoPDF = false;

		switch ($this->action) {
		    case "Buscar":
				$form_search = TRUE;
				$viewListadoHTML = TRUE;
				$viewListadoPDF = FALSE;
				$viewListadoEXCEL = FALSE;
				break;

			case "PDF":
				$form_search = TRUE;
				$viewListadoHTML = TRUE;
				$viewListadoPDF = TRUE;
				$viewListadoEXCEL = FALSE;
				break;

			case "Excel":
				$viewListadoEXCEL = TRUE;
				/*
				$form_search = TRUE;
				$viewListadoHTML = TRUE;
				$viewListadoEXCEL = TRUE;
				*/
				break;

		    default:
				$form_search = true;
				$viewListadoPDF = FALSE;
				$viewListadoEXCEL = FALSE;
				break;
		}
	
		if ($form_search) {
			$iAlmacen 	= (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
			$dYear 		= (isset($_POST['cbo-year']) ? trim($_POST['cbo-year']) : date('Y'));
			$dMonth 	= (isset($_POST['cbo-year']) ? trim($_POST['cbo-month']) : date('m'));

			$arrWarehouse 	= $objModel->getAlmacenes("");
			if ($arrWarehouse['sStatus'] != "success") {
				echo '<script type="text/javascript">alert("' . $arrWarehouse['sMessage'] . '");window.close();</script>';
			} else {
		   		$result = $objTemplate->formSearch($arrWarehouse['arrData'], $iAlmacen, $dYear, $dMonth, $arrPeriodo['nu_year']);
		   	}
		}

		if ($viewListadoHTML) {
			$pos_transYM = "pos_trans" . $dYear . $dMonth;

			/*Agregado 2020-01-08*/
			echo "<script>console.log('estacion: " . json_encode($iAlmacen) . "')</script>";				
			echo "<script>console.log('dYear: " . json_encode($dYear) . "')</script>";				
			echo "<script>console.log('dMonth: " . json_encode($dMonth) . "')</script>";				
			echo "<script>console.log('pos_transYM: " . json_encode($pos_transYM) . "')</script>";				
			/***/

	    	$resultado = $objModel->search($iAlmacen, $dYear, $dMonth, $pos_transYM);
	    	$result_f  = $objTemplate->listado($resultado, $iAlmacen, $dYear, $dMonth);
		}

		if ($viewListadoEXCEL) {
			$sWarehouse = trim($_POST['cbo-iAlmacen']);
			$dYear = trim($_POST['cbo-year']);
			$dMonth = trim($_POST['cbo-month']);
			$pos_transYM = "pos_trans" . $dYear . $dMonth;
			$arrWarehouse = $objModel->getAlmacenes($sWarehouse);
			if ($arrWarehouse['sStatus'] != "success") {
				echo '<script type="text/javascript">alert("' . $arrWarehouse['sMessage'] . '");window.close();</script>';
			} else {
				$sNameWarehouse = $arrWarehouse['arrData'][$sWarehouse];
		    	$arrResponse = $objModel->search($sWarehouse, $dYear, $dMonth, $pos_transYM);
		    	$result_excel = $objTemplate->reporteExcel($arrResponse, $dYear, $dMonth, $sNameWarehouse, $sWarehouse);
			}
		}

		if ($viewListadoPDF) {
			$sWarehouse = trim($_POST['cbo-iAlmacen']);
			$dYear = trim($_POST['cbo-year']);
			$dMonth = trim($_POST['cbo-month']);
			$pos_transYM = "pos_trans" . $dYear . $dMonth;
			$arrWarehouse = $objModel->getAlmacenes($sWarehouse);

			if ($arrWarehouse['sStatus'] != "success") {
				echo '<script type="text/javascript">alert("' . $arrWarehouse['sMessage'] . '");window.close();</script>';
			} else {
				$sNameWarehouse = $arrWarehouse['arrData'][$sWarehouse];
				$arrResponse = $objModel->search($iAlmacen, $dYear, $dMonth, $pos_transYM);
				$result = $objTemplate->reportePDF($arrResponse, $dYear, $dMonth, $sNameWarehouse, $sWarehouse);
				$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/cajaybanco.pdf";
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="'."cajaybanco.pdf".'"');
				readfile($mi_pdf);
			}
		}

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}
