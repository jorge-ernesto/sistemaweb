<?php

class MovimientosCajaController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
	    ob_start();

		include 'movimientos/m_movimientos_caja.php';
		include 'movimientos/t_movimientos_caja.php';
	
		$objModel = new MovimientosCajaModel();
		$objTemplate = new MovimientosCajaTemplate();

		$this->Init();

		//Obtener la fecha del ultimo del cierre
		$dUltimoCierre = $objModel->getFechaSistemaPA();

		$result   = "";
		$result_f = "";

		$form_search = false;
		$viewListadoHTML = false;
		$viewListadoPDF = false;
		$viewListadoEXCEL = false;

		switch ($this->action) { 

		    case "HTML":
				$form_search = true;
				$viewListadoHTML = true;
			break;

			case "PDF":
				$form_search = true;
				$viewListadoHTML = true;
				$viewListadoPDF = true;
			break;

			case "EXCEL":
				$form_search = true;
				$viewListadoHTML = true;
				$viewListadoEXCEL = true;
			break;

		    default:
				$form_search = true;
			break;
		}
	
		if ($form_search){
			$iAlmacen 				= (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
			$fe_inicial 			= (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
			$fe_final 				= (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre);
			$iCaja 					= (isset($_POST['cbo-iCaja']) ? trim($_POST['cbo-iCaja']) : 0);
			$iTipoMovimientoCaja 	= (isset($_POST['cbo-iTipoMovimientoCaja']) ? $_POST['cbo-iTipoMovimientoCaja'] : "");
			$iMedioPago 			= (isset($_POST['cbo-iMedioPago']) ? $_POST['cbo-iMedioPago'] : "");
			$iCuentaBancaria 		= (isset($_POST['cbo-iCuentaBancaria']) ? $_POST['cbo-iCuentaBancaria'] : "");

			$arrAlmacenes				= $objModel->obtenerSucursales("");
			$arrCajas 					= $objModel->Caja();
			$arrTipoMovimientoCaja		= array("0"=>"Caja Ingresos", "1"=>"Caja Egresos");
			$arrTipoMovimientoCaja[""]	= "TODOS";
			$arrMediosPago				= $objModel->getMediosPago();
			$arrMediosPago[""]			= "TODOS";
			$arrCuentasBancarias		= $objModel->CuentasBancarias();
			$arrCuentasBancarias[""]	= "TODOS";

			$result = $objTemplate->formSearch($arrAlmacenes, $iAlmacen, $fe_inicial, $fe_final, $dUltimoCierre, $arrCajas, $iCaja, $arrTipoMovimientoCaja, $iTipoMovimientoCaja, $arrMediosPago, $iMedioPago, $arrCuentasBancarias, $iCuentaBancaria);
		}

		if ($viewListadoHTML) {
			$fe_inicial = trim($fe_inicial);
			$fe_inicial = strip_tags($fe_inicial);
			$fe_inicial_pdf = $fe_inicial;
			$fe_inicial = explode("/", $fe_inicial);
			$fe_inicial = $fe_inicial[2] . "-" . $fe_inicial[1] . "-" . $fe_inicial[0];

			$fe_final = trim($fe_final);
			$fe_final = strip_tags($fe_final);
			$fe_final_pdf = $fe_final;
			$fe_final = explode("/", $fe_final);
			$fe_final = $fe_final[2] . "-" . $fe_final[1] . "-" . $fe_final[0];
		
			$arrSaldoInicial = $objModel->getSaldoInicial($iAlmacen, $fe_inicial, $iCaja, $iTipoMovimientoCaja, $iMedioPago, $iCuentaBancaria);
		    $arrMovimientosIngresosEgresos = $objModel->getMovimientosIngresosEgresos($iAlmacen, $fe_inicial, $fe_final, $iCaja, $iTipoMovimientoCaja, $iMedioPago, $iCuentaBancaria);
			$result_f = $objTemplate->gridViewHTML($arrSaldoInicial, $arrMovimientosIngresosEgresos);
		}

		if ($viewListadoPDF) {
			$saldo_inicial 		= $objModel->getSaldoInicial($iAlmacen, $fe_inicial, $iCaja, $iTipoMovimientoCaja, $iMedioPago, $iCuentaBancaria);
			$resultado 			= $objModel->getMovimientosIngresosEgresos($iAlmacen, $fe_inicial, $fe_final, $iCaja, $iTipoMovimientoCaja, $iMedioPago, $iCuentaBancaria);
			$arrNombreAlmacen 	= $objModel->obtenerSucursales($iAlmacen);
			$result_f 			= $objTemplate->gridViewPDF($arrSaldoInicial, $arrMovimientosIngresosEgresos, $arrNombreAlmacen, $iAlmacen, $fe_inicial_pdf, $fe_final_pdf);

			$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/MovimientosCaja.pdf";
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="'."MovimientosCaja.pdf".'"');
			readfile($mi_pdf);
		}

		if ($viewListadoEXCEL) {
			$saldo_inicial 		= $objModel->getSaldoInicial($iAlmacen, $fe_inicial, $iCaja, $iTipoMovimientoCaja, $iMedioPago, $iCuentaBancaria);
			$resultado 			= $objModel->getMovimientosIngresosEgresos($iAlmacen, $fe_inicial, $fe_final, $iCaja, $iTipoMovimientoCaja, $iMedioPago, $iCuentaBancaria);
			$arrNombreAlmacen 	= $objModel->obtenerSucursales($iAlmacen);
			$result_f   		= $objTemplate->gridViewEXCEL($arrSaldoInicial, $arrMovimientosIngresosEgresos, $arrNombreAlmacen, $iAlmacen, $fe_inicial_pdf, $fe_final_pdf);
		}

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}
