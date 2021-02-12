<?php

class SIIGOProductosCRUDController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    }
    
    function Run() {
		include 'maestros/m_siigo_productos.php';
		include 'maestros/t_siigo_productos.php';

		$objSIIGOProductosCRUDModel = new SIIGOProductosCRUDModel();
		$objSIIGOProductosCRUDTemplate = new SIIGOProductosCRUDTemplate();

		$this->Init();

		$sNombreProducto = "";

		$result   = "";
		$result_f = "";

		$formPrincipal 		= FALSE;
		$viewListadoHTML 	= FALSE;
		$viewListadoHTMLADD = FALSE;
		$saveProducto 		= FALSE;

		switch ($this->action) {
		    case 'Buscar':
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				$viewListadoHTMLADD = FALSE;
				break;

		    case 'Add':
				$formPrincipal = FALSE;
				$viewListadoHTML = FALSE;
				$viewListadoHTMLADD = TRUE;
				break;

			case 'Save':
				$saveProducto = TRUE;
				$formPrincipal = TRUE;
				$viewListadoHTML = TRUE;
				break;

	    	default:
				$formPrincipal = TRUE;
				break;
		}

		if ($formPrincipal) {
			$sNombreProducto 	= (isset($_POST['No_Producto']) ? trim($_POST['No_Producto']) : NULL);

			$result 	= $objSIIGOProductosCRUDTemplate->formPrincipal($sNombreProducto);
			$arrResult 	= $objSIIGOProductosCRUDModel->listarProductos($sNombreProducto);
			$result_f  = $objSIIGOProductosCRUDTemplate->gridViewHTML($arrResult);
		}

		if ($viewListadoHTML) {
			$sNombreProducto = trim($sNombreProducto);
			$sNombreProducto = strip_tags($sNombreProducto);

			$arrResult 	= $objSIIGOProductosCRUDModel->listarProductos($sNombreProducto);
			$result_f 	= $objSIIGOProductosCRUDTemplate->gridViewHTML($arrResult);
		}

		if ($viewListadoHTMLADD) {
			$sNombreProducto = trim($sNombreProducto);
			$sNombreProducto = strip_tags($sNombreProducto);

			$result 	= $objSIIGOProductosCRUDTemplate->formAdd();
			$result_f 	= TRUE;//Para no mostrar el resultados de los registros buscar en el footer
		}

		if($saveProducto){
			$arrResult 	= $objSIIGOProductosCRUDModel->saveProducto($_POST);
		}

		$this->visor->addComponent("ContentT", "content_title", $objSIIGOProductosCRUDTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
    }
}
