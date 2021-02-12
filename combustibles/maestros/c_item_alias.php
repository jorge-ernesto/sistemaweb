<?php
class CRUDItemAliasController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
		include 'maestros/m_item_alias.php';
		include 'maestros/t_item_alias.php';
		include '/sistemaweb/helper.php';

		$objModel = new CRUDItemAliasModel();
		$objTemplate = new CRUDItemAliasTemplate();
		$objHelper = new HelperClass();

		$this->Init();

		$result   = "";
		$result_f = "";

		$formPrincipal 		= FALSE;
		$viewListadoHTML 	= FALSE;

		switch ($this->action) {
		    case "Buscar":
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				break;

	    	default:
				$formPrincipal = TRUE;
				break;
		}

		if ($formPrincipal) {
			$fe_inicial	= (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
			$fe_final = (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre);
			$result = $objTemplate->formPrincipal($fe_inicial, $fe_final);
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

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
    }
}
