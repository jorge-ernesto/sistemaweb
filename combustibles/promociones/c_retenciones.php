<?php
   
class RetencionesController extends Controller {
		
	function Init(){
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		//otros variables de entorno
	}
	
	function Run(){
		$this->Init();
		$result = '';
		$bolMensaje ='0';	
		include('promociones/m_retenciones.php');
		include('promociones/t_retenciones.php'); 
		include('../include/paginador_new.php');
			require("../clases/funciones.php");	
		$funcion = new class_funciones;
		
		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']){
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}
		switch ($this->request){//task
		case 'RETENCIONES':

			$tablaNombre = 'RETENCIONES';
			$listado = false;

			switch ($this->action){
				case 'Consultar':
					$fechaini = trim($_REQUEST['fechainicio']);
					$fechafin = trim($_REQUEST['fechafin']);

					$busqueda = RetencionesModel::tmListado($fechaini,$fechafin,$_REQUEST['rxp'],$_REQUEST['pagina']);
					$result = RetencionesTemplate::formBuscar();
					$tamaniopuntos = count($busqueda['datos']);
					$result  .= RetencionesTemplate::formRetenciones($tamaniopuntos);
					//agregado por DPC 09/05/09
					$result .= RetencionesTemplate::formPaginacion($busqueda['paginacion'],$fechaini,$fechafin,$tamaniopuntos,100);
					$result .= RetencionesTemplate::listado($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					break;
				case 'libera':
					$id = $_REQUEST['id'];
					$res = RetencionesModel::liberaRetencion($id);
					if ($res==true)
						$result = "<script langauge=\"javascript\">alert(\"Retencion liberada!\");top.document.getElementById(\"listado_retenciones_tr_$id\").style.display=\"none\";</script>";
					else
						$result = "<script langauge=\"javascript\">alert(\"No se pudo liberar retencion!\");</script>";
					//$this->visor->addComponent("ContentB", "content_body", $result);
					echo $result;
					break;
				default:
					$listado = true;
					break;
			}

			if ($listado) { 
				$this->visor->addComponent('ContentT', 'content_title',RetencionesTemplate::titulo());
				$result    = RetencionesTemplate::formBuscar();
				$this->visor->addComponent("ContentB", "content_body", $result);
			}
		}
	}
  }

