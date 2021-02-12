<?php

class VentaCombustiblexLadoController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
	    ob_start();
		include 'reportes/m_venta_combustible_x_lado.php';
		include 'reportes/t_venta_combustible_x_lado.php';

		$objModel = new VentaCombustiblexLadoModel();
		$objTemplate = new VentaCombustiblexLadoTemplate();

		$this->Init();

		//Obtener la fecha del ultimo del cierre
		$dUltimoCierre = $objModel->getFechaSistemaPA();

		$result   = "";
		$result_f = "";

		$arrLadosForm = array();

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
			$nu_almacen 		= (isset($_POST['cbo-iAlmacen']) ? trim($_POST['cbo-iAlmacen']) : $_SESSION['almacen']);
			$fe_inicial 		= (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
			$fe_final 			= (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre);
			$nu_codigo_producto	= (isset($_POST['cbo-iProducto']) ? trim($_POST['cbo-iProducto']) : 0);
			$arrLadosForm		= (isset($_POST['check-arrLados']) ? $_POST['check-arrLados'] : 0);
			$sTipoVista 		= (isset($_POST['radio-iTipoVista']) ? trim($_POST['radio-iTipoVista']) : NULL);

			$sTipoVistaDetallado = "checked";
			$sTipoVistaResumido = "";
			
			if(isset($_POST['radio-iTipoVista'])){
				$sTipoVistaDetallado = ($sTipoVista == "D" ? "checked" : "");
				$sTipoVistaResumido = ($sTipoVista == "R" ? "checked" : "");
			}

			$arrAlmacenes = $objModel->getAlmacenes();
			$arrLados = $objModel->getLados();
			$arrProductos = $objModel->getProductos();
			$result = $objTemplate->formPrincipal($arrAlmacenes, $nu_almacen, $fe_inicial, $fe_final, $dUltimoCierre, $arrLados, $arrLadosForm, $arrProductos, $nu_codigo_producto, $sTipoVistaDetallado, $sTipoVistaResumido);
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
		
			$arrResult 	= $objModel->getVentaCOmbustiblexLado($nu_almacen, $fe_inicial, $fe_final, $arrLadosForm, $nu_codigo_producto);
			$result_f 	= $objTemplate->gridViewHTML($arrResult, $sTipoVista);
		}

		if ($viewListadoExcel)
			$result_f = $objTemplate->gridViewExcel($arrResult, $sTipoVista);

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}
