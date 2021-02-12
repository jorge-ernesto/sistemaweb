<?php

class CodigosRapidoController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
    }
    
    function Run() {
		include 'movimientos/m_codigos_rapido.php';
		include 'movimientos/t_codigos_rapido.php';
	
		$objCodigosRapidoTemplate = new CodigosRapidoTemplate();
		$objCodigosRapidoModel = new CodigosRapidoModel();

		$this->Init();

		$result   = "";
		$result_f = "";
		$formPrincipal = FALSE;

		//echo "<script>alert('".$this->action."!')</script>";
		switch ($this->action) {
			case "Buscar":
				$formPrincipal = TRUE;
				break;

			case "Agregar":
				$ingresa    	= $objCodigosRapidoModel->ingresarCodigo(trim($_REQUEST['identificador']), trim($_REQUEST['articulo']));
				if ($ingresa == 2) $result = '<script name="accion">alert("El identificador ya existe.") </script>';
				if ($ingresa == 1) $result = '<script name="accion">alert("Agregado correctamente.") </script>';
				if ($ingresa == 0) $result = '<script name="accion">alert("Falta completar datos.") </script>';				

				echo $result;
				$busqueda    	= $objCodigosRapidoModel->obtenerDatos("", "");
				$result = $objCodigosRapidoTemplate->search_form($busqueda);
				$result_f = $objCodigosRapidoTemplate->reporte($busqueda);
				if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
				if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;

			case "Eliminar":
				$elimina    	= $objCodigosRapidoModel->eliminarCodigo(trim($_REQUEST['identificador']));
				$busqueda    	= $objCodigosRapidoModel->obtenerDatos("", "");
				echo "<script>window.location='cmb_codigos_rapido.php';</script>";
				break;

		    default:
				$formPrincipal = TRUE;
				break;
		}

		if($formPrincipal){
			$identificador = isset($_REQUEST['identificador']) ? trim($_REQUEST['identificador']) : '';
			$articulo = isset($_REQUEST['articulo']) ? trim($_REQUEST['articulo']) : '';
			$busqueda = $objCodigosRapidoModel->obtenerDatos($identificador, $articulo);
			$result = $objCodigosRapidoTemplate->search_form($busqueda);
			$result_f = $objCodigosRapidoTemplate->reporte($busqueda);
		}

		$this->visor->addComponent("ContentT", "content_title", $objCodigosRapidoTemplate->getTitulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);	
	}
}
