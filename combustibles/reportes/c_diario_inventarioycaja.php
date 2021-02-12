<?php

class DiarioInventarioYCajaController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
	    ob_start();
		include 'reportes/m_diario_inventarioycaja.php';
		include 'reportes/t_diario_inventarioycaja.php';

		$objDiarioinventarioYCajaModel = new DiarioinventarioYCajaModel();
		$objDiarioinventarioYCajaTemplate = new DiarioinventarioYCajaTemplate();

		$this->Init();

		$date  		= time();
		$_fe_ayer 	= $date - (24 * 60 * 60);
		$fe_ayer 	= date("d/m/Y", $_fe_ayer);

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
			$nu_almacen = (isset($_POST['nu_almacen']) ? trim($_POST['cbo-nu_almacen']) : $_SESSION['almacen']);
			$fe_inicial = (isset($_POST['txt-fe_inicial']) ? trim($_POST['txt-fe_inicial']) : $fe_ayer);
			$fe_final 	= (isset($_POST['txt-fe_final']) ? trim($_POST['txt-fe_final']) : $fe_ayer);

			$arrAlmacenes 	= $objDiarioinventarioYCajaModel->getAlmacenes();
			$result 		= $objDiarioinventarioYCajaTemplate->formPrincipal($arrAlmacenes, $nu_almacen, $fe_inicial, $fe_final);
		}

		if ($viewListadoHTML) {
			$fe_inicial = trim($fe_inicial);
			$fe_inicial = strip_tags($fe_inicial);
			$fe_inicial = explode("/", $fe_inicial);
			$pos_trans = "pos_trans".$fe_inicial[2].$fe_inicial[1];
			$fe_inicial = $fe_inicial[2] . "-" . $fe_inicial[1]  . "-" . $fe_inicial[0];

			$fe_final = trim($fe_final);
			$fe_final = strip_tags($fe_final);
			$fe_final = explode("/", $fe_final);
			$fe_final = $fe_final[2] . "-" . $fe_final[1]  . "-" . $fe_final[0];

			$arrCuadreDiarioInventarioYCaja = $objDiarioinventarioYCajaModel->getCuadreDiarioInventarioYCaja($nu_almacen, $fe_inicial, $fe_final);
			$nu_soles_gastos_varios 		= $objDiarioinventarioYCajaModel->getGastosVarios($nu_almacen, $fe_inicial, $fe_final);
			$nu_soles_creditos_clientes		= $objDiarioinventarioYCajaModel->getCreditosClientes($nu_almacen, $fe_inicial, $fe_final);
			$nu_soles_depositos_bancarios 	= $objDiarioinventarioYCajaModel->getDepositosBancarios($nu_almacen, $fe_inicial, $fe_final);
			$arrTarjetasCreditos 			= $objDiarioinventarioYCajaModel->getTarjetasCreditos($nu_almacen, $fe_inicial, $fe_final, $pos_trans);
			$nu_soles_depositos_pos			= $objDiarioinventarioYCajaModel->getDepositosPOS($nu_almacen, $fe_inicial, $fe_final);

			$result_f  = $objDiarioinventarioYCajaTemplate->gridViewHTML($arrCuadreDiarioInventarioYCaja[1], $nu_soles_gastos_varios, $nu_soles_creditos_clientes, $nu_soles_depositos_bancarios, $arrTarjetasCreditos[0], $nu_soles_depositos_pos);
		}

		if ($viewListadoExcel)
			$result_f = $objDiarioinventarioYCajaTemplate->gridViewExcel($arrCuadreDiarioInventarioYCaja[1], $nu_soles_gastos_varios, $nu_soles_creditos_clientes, $nu_soles_depositos_bancarios, $arrTarjetasCreditos[0], $nu_soles_depositos_pos);

		if ($viewListadoPDF){
			$result_f = $objDiarioinventarioYCajaTemplate->gridViewPDF($arrCuadreDiarioInventarioYCaja[1], $nu_soles_gastos_varios, $nu_soles_creditos_clientes, $nu_soles_depositos_bancarios, $arrTarjetasCreditos[0], $nu_soles_depositos_pos);
			$mi_pdf = "/sistemaweb/ventas_clientes/reportes/pdf/Cuadre_Diario_Inventarios_Y_Caja.pdf";
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="Cuadre_Diario_Inventarios_Y_Caja.pdf"');
			readfile($mi_pdf);
		}

		$this->visor->addComponent("ContentT", "content_title", $objDiarioinventarioYCajaTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}
