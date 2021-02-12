<?php

class RegistroVentasClientesController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
	    ob_start();
		include 'reportes/m_registro_ventas_clientes.php';
		include 'reportes/t_registro_ventas_clientes.php';

		$objRegistroVentasClientesModel = new RegistroVentasClientesModel();
		$objRegistroVentasClientesTemplate = new RegistroVentasClientesTemplate();

		$this->Init();

		//Obtener la fecha del ultimo del cierre
		$dUltimoCierre = $objRegistroVentasClientesModel->getFechaSistemaPA();

		$result   = "";
		$result_f = "";
		$resultado = "";

		$formPrincipal 		= FALSE;
		$viewListadoHTML 	= FALSE;
		$viewListadoExcel 	= FALSE;
		$viewListadoPDF 	= FALSE;

		switch ($this->action) {
		    case "Buscar":
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				break;

			case "Excel":
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				$viewListadoExcel = TRUE;
				break;

			case "PDF":
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				$viewListadoExcel = FALSE;
				$viewListadoPDF = TRUE;
				break;

	    	default:
				$formPrincipal = TRUE;
				break;
		}

		if ($formPrincipal) {
			$nu_almacen 			= (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
			$fe_inicial 			= (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
			$fe_final 				= (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre);
			$iFormaPago 			= (isset($_POST['cbo-iFormaPago']) ? trim($_POST['cbo-iFormaPago']) : "T");
			$iTipoVenta 			= (isset($_POST['cbo-iTipoVenta']) ? trim($_POST['cbo-iTipoVenta']) : "T");
			$iDocumentoIdentidad 	= (isset($_POST['Nu_Documento_Identidad']) ? trim($_POST['Nu_Documento_Identidad']) : NULL);
			$sRazSocial 			= (isset($_POST['No_Razsocial']) ? trim($_POST['No_Razsocial']) : NULL);
			$sTipoVista 			= trim($_POST['radio-iTipoVista']);

			$sTipoVistaDetallado = "checked";
			$sTipoVistaResumido = "";
			if(isset($_POST['radio-iTipoVista'])){
				$sTipoVistaDetallado = ($sTipoVista == "D" ? "checked" : "");
				$sTipoVistaResumido = ($sTipoVista == "R" ? "checked" : "");
			}

			$arrAlmacenes 	= $objRegistroVentasClientesModel->getAlmacenes();
			$result 		= $objRegistroVentasClientesTemplate->formPrincipal($arrAlmacenes, $nu_almacen, $fe_inicial, $fe_final, $iTipoVenta, $iFormaPago, $iDocumentoIdentidad, $sRazSocial, $dUltimoCierre, $sTipoVistaDetallado, $sTipoVistaResumido);
		}

		if ($viewListadoHTML) {
			$fe_inicial = trim($fe_inicial);
			$fe_inicial = strip_tags($fe_inicial);
			$fe_inicial = explode("/", $fe_inicial);
			$fe_inicial = $fe_inicial[2] . "-" . $fe_inicial[1] . "-" . $fe_inicial[0];

			$fe_final = trim($fe_final);
			$fe_final = strip_tags($fe_final);
			$fe_final = explode("/", $fe_final);
			$fe_final = $fe_final[2] . "-" . $fe_final[1] . "-" . $fe_final[0];
		
			$arrData 	= $objRegistroVentasClientesModel->getListaDocumentosManualesVentas($nu_almacen, $fe_inicial, $fe_final, $iTipoVenta, $iFormaPago, $iDocumentoIdentidad, $sRazSocial);
			$sIdDocumento = "";
			$result_f 	= $objRegistroVentasClientesTemplate->gridViewHTML($arrData, $sTipoVista);
		}

		if ($viewListadoExcel)
			$result_f = $objRegistroVentasClientesTemplate->gridViewExcel($arrData, $sTipoVista);

		if ($viewListadoPDF){
			$resultado = $objRegistroVentasClientesTemplate->gridViewPDF($arrData, $sTipoVista);
			$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/RegistroVentasClientes.pdf";
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="RegistroVentasClientes.pdf"');
			readfile($mi_pdf);
		}

		$this->visor->addComponent("ContentT", "content_title", $objRegistroVentasClientesTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}
