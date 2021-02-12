<?php

class DocumentosController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {

		include 'reportes/m_documentos.php';
		include 'reportes/t_documentos.php';

		$this->Init();
		$result 	= '';
		$result_f 	= '';
		$buscar 	= false;

		switch($this->action) {

			case "Buscar":

				$busqueda    	= DocumentosModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['documentos']);
				if($busqueda == ''){
					?><script>alert("<?php echo 'No hay registros en este Banco';?> ");</script><?php
				}else{
					$busqueda    	= DocumentosModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['documentos']);
					$result     	= DocumentosTemplate::formSearch($busqueda['paginacion']);
					$result_f 	= DocumentosTemplate::resultadosBusqueda($busqueda['documentos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			break;

			default:

				$data    	= DocumentosModel::Paginacion(null);
				$result     	= DocumentosTemplate::formSearch();
				$result_f 	= DocumentosTemplate::resultadosBusqueda($data);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", DocumentosTemplate::titulo());

	}
}
