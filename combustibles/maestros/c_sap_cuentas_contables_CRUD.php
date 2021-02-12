<?php

class SAPMapeoTablasCRUDController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
		include 'maestros/m_sap_cuentas_contables_CRUD.php';
		include 'maestros/t_sap_cuentas_contables_CRUD.php';

		$objTemplate = new SAPMapeoTablasCRUDTemplate();
		$objModel = new SAPMapeoTablasCRUDModel();

		$this->Init();

		$result   = "";
		$result_f = "";

		$nu_id_tipo_tabla 	= 1;//TABLA sap_mapeo_tabla CAMPO id_tipo_tabla VALUE 1 = Centro Costo
		$formPrincipal 		= FALSE;
		$viewListadoHTML 	= FALSE;
		$viewListadoHTMLUPD = FALSE;
		$save 				= FALSE;

		switch ($this->action) {
		    case 'Buscar':
				$formPrincipal = TRUE;
				break;

			case 'SAVE':
				$arrSapCodigo = $_POST['arrSapCodigo'];
				$fAdd = TRUE;

				foreach ($arrSapCodigo as $key => $value) {
					if(empty($value)){
						?><script>alert("<?php echo 'Campos vacio(s)'; ?> ");</script><?php
						$fAdd = FALSE;
						break;
					}
				}

				if($fAdd){
					$response = $objModel->guardarSapMapeoTablas($_POST);
					if ($response) {
						?><script>alert("<?php echo 'Guardado satisfactoriamente'; ?> ");</script><?php
						$formPrincipal = TRUE;
					} else {
						?><script>alert("<?php echo 'Erro al guardar datos'; ?> ");</script><?php
					}
				}
				break;

			case 'UPD':
				$response = $objModel->actualizarSapMapeoTablas($_GET);
				if ($response){
					?><script>alert("<?php echo 'Actualizado satisfactoriamente'; ?> ");</script><?php
					$formPrincipal = TRUE;
				} else {
					?><script>alert("<?php echo 'Error'; ?> ");</script><?php
				}
				break;

	    	default:
				$formPrincipal = TRUE;
				break;
		}

		if ($formPrincipal) {
			$nu_id_tipo_tabla 	= (isset($_REQUEST['id_tipo_tabla']) ? trim($_REQUEST['id_tipo_tabla']) : $nu_id_tipo_tabla);
			/* Body */
			$arrSapMapeoTablas 	= $objModel->obtenerSapMapeoTablas($nu_id_tipo_tabla);
			$result 			= $objTemplate->formPrincipal($arrSapMapeoTablas, $nu_id_tipo_tabla);
			/* Footer */
			$arrResult 			= $objModel->listarTablasOpenComb($nu_id_tipo_tabla);//Para el mapeo de tablas entre OpenComb y SAP
			$result_f 			= $objTemplate->gridViewHTML($arrResult, $nu_id_tipo_tabla);
		}

		$this->visor->addComponent("ContentT", "content_title", $objTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}
