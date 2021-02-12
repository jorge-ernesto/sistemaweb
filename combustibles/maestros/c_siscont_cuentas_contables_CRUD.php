<?php

class SISCONTCtaContablesCRUDController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
		include 'maestros/m_siscont_cuentas_contables_CRUD.php';
		include 'maestros/t_siscont_cuentas_contables_CRUD.php';

		$objModel = new SISCONTCtaContablesCRUDModel();
		$objTemplate = new SISCONTCtaContablesCRUDTemplate();

		$this->Init();

		$result   = "";
		$result_f = "";

		$formPrincipal 		= FALSE;
		$viewListadoHTML 	= FALSE;
		$viewListadoHTMLUPD = FALSE;
		$save 				= FALSE;

		switch ($this->action) {
		    case 'Buscar':
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				$viewListadoHTMLUPD = FALSE;
				break;

		    case 'Upd':
				$formPrincipal = FALSE;
				$viewListadoHTML = FALSE;
				$viewListadoHTMLUPD = TRUE;
				break;

			case 'Save':
				$save = TRUE;
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				break;

	    	default:
				$formPrincipal = TRUE;
				break;
		}

		if ($formPrincipal) {
			$result 	= $objTemplate->formPrincipal();
			$arrResult 	= $objModel->listarCuentasContables();
			$result_f  = $objTemplate->gridViewHTML($arrResult);
		}

		if ($viewListadoHTML) {
			$arrResult 	= $objModel->listarCuentasContables();
			$result_f 	= $objTemplate->gridViewHTML($arrResult);
		}

		if ($viewListadoHTMLUPD) {
			$nu_id 		= trim($_GET['nu_id']);
			$nu_id 		= strip_tags($nu_id);

			$arrResult 	= $objModel->obtenerCuentaContable($nu_id);
			$result 	= $objTemplate->formUpdate($arrResult);
			$result_f 	= TRUE;//Para no mostrar el resultados de los registros buscar en el footer
		}

		if($save){
			$arrResult 	= $objModel->actualizarCuentaContable($_POST);
			$result 	= $objTemplate->formPrincipal();
			$arrResult 	= $objModel->listarCuentasContables();
			$result_f  = $objTemplate->gridViewHTML($arrResult);
		}

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}
