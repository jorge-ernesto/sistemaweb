<?php

class AfericionReportController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
		include 'reportes/m_afericion_rep.php';
		include 'reportes/t_afericion_rep.php';

		$objAfericionReportModel = new AfericionReportModel();
		$objAfericionReportTemplate = new AfericionReportTemplate();

		$this->Init();

		//Obtener la fecha del ultimo del cierre dia - tabla PA = pos_aprosys
		$dUltimoCierre = $objAfericionReportModel->getLastDatePA();

		$result   = "";
		$result_f = "";

		$formPrincipal 		= FALSE;
		$viewListadoHTML 	= FALSE;

		switch ($this->action) {
		    case 'Buscar':
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				break;

	    	default:
				$formPrincipal = TRUE;
				break;
		}

		if ($formPrincipal) {
			$fe_inicial = (isset($_POST['txt-dInicial']) ? trim($_POST['txt-dInicial']) : $dUltimoCierre);
			$fe_final 	= (isset($_POST['txt-dFinal']) ? trim($_POST['txt-dFinal']) : $dUltimoCierre);

			$result 	= $objAfericionReportTemplate->formPrincipal($fe_inicial, $fe_final, $dUltimoCierre);
		}

		if ($viewListadoHTML) {
			$fe_inicial = trim($fe_inicial);
			$fe_inicial = strip_tags($fe_inicial);
			$fe_inicial = explode('/', $fe_inicial);
			$fe_inicial = $fe_inicial[2] . '-' . $fe_inicial[1] . '-' . $fe_inicial[0];

			$fe_final = trim($fe_final);
			$fe_final = strip_tags($fe_final);
			$fe_final = explode('/', $fe_final);
			$fe_final = $fe_final[2] . '-' . $fe_final[1] . '-' . $fe_final[0];

			$arrResult 	= $objAfericionReportModel->listarAfericiones($fe_inicial, $fe_final);
			$result_f 	= $objAfericionReportTemplate->gridViewHTML($arrResult);
		}

		$this->visor->addComponent("ContentT", "content_title", $objAfericionReportTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}
