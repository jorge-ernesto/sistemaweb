<?php

class ResumenDiarioValesXEmpresaController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
	    ob_start();
		include 'reportes/m_resumen_diario_vales_x_empresa.php';
		include 'reportes/t_resumen_diario_vales_x_empresa.php';

		$objResumenDiarioValesXEmpresaModel = new ResumenDiarioValesXEmpresaModel();
		$objResumenDiarioValesXEmpresaTemplate = new ResumenDiarioValesXEmpresaTemplate();

		$this->Init();

		//Obtener la fecha del ultimo del cierre
		$dUltimoCierre = $objResumenDiarioValesXEmpresaModel->getFechaSistemaPA();

		$result   = "";
		$result_f = "";

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

	    	default:
				$formPrincipal = TRUE;
				break;
		}

		if ($formPrincipal) {
			$nu_almacen 			= (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
			$fe_inicial 			= (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
			$fe_final 				= (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre);
			$iDocumentoIdentidad 	= (isset($_POST['Nu_Documento_Identidad']) ? trim($_POST['Nu_Documento_Identidad']) : NULL);
			$sRazSocial 			= (isset($_POST['No_Razsocial']) ? trim($_POST['No_Razsocial']) : NULL);
			$sTipoVista 			= (isset($_POST['radio-iTipoVista']) ? trim($_POST['radio-iTipoVista']) : NULL);

			$sTipoVistaDetallado = "checked";
			$sTipoVistaResumido = "";
			
			if(isset($_POST['radio-iTipoVista'])){
				$sTipoVistaDetallado = ($sTipoVista == "D" ? "checked" : "");
				$sTipoVistaResumido = ($sTipoVista == "R" ? "checked" : "");
			}

			$arrAlmacenes 	= $objResumenDiarioValesXEmpresaModel->getAlmacenes();
			$result 		= $objResumenDiarioValesXEmpresaTemplate->formPrincipal($arrAlmacenes, $nu_almacen, $fe_inicial, $fe_final, $dUltimoCierre, $iDocumentoIdentidad, $sRazSocial, $sTipoVistaDetallado, $sTipoVistaResumido);
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
		
			$arrData 	= $objResumenDiarioValesXEmpresaModel->getListaValesCredito($nu_almacen, $fe_inicial, $fe_final, $iDocumentoIdentidad, $sRazSocial);
			$result_f 	= $objResumenDiarioValesXEmpresaTemplate->gridViewHTML($arrData, $sTipoVista);

			// echo "<script>console.log('" . json_encode($arrData) . "')</script>";
			// echo "<script>console.log('" . json_encode($sTipoVista) . "')</script>";
		}

		if ($viewListadoExcel)			
			$result_f = $objResumenDiarioValesXEmpresaTemplate->gridViewExcel($arrData, $sTipoVista);

		$this->visor->addComponent("ContentT", "content_title", $objResumenDiarioValesXEmpresaTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}
