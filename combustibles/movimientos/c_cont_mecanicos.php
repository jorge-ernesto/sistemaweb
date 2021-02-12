<?php

class ContMecanicosController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
    	}
    
    	function Run() {

		include 'movimientos/m_cont_mecanicos.php';
		include 'movimientos/t_cont_mecanicos.php';
	
		$this->Init();
	
		$result   = "";
		$result_f = "";

		$form_search = false;
		$listado     = false;

		switch ($this->action) { 

		    	case "Buscar":
				$listado = true;
			break;

		    	case "Parte":

				$result       = ContMecanicosTemplate::formSearch(trim($_REQUEST['fecha']), trim($_REQUEST['fecha2']),$_REQUEST['estacion'],trim($_REQUEST['turno']));	 		
		    		$resultado    = ContMecanicosModel::ReporteContometros(trim($_REQUEST['fecha']), trim($_REQUEST['fecha2']),$_REQUEST['estacion'],trim($_REQUEST['turno']));	     	
		    		$result_f     = ContMecanicosTemplate::ParteVenta($resultado,trim($_REQUEST['fecha']),trim($_REQUEST['fecha2']),$_REQUEST['estacion'],$_REQUEST['turno']);

			break;

			case "Eliminar":
				$consolida   	= ContMecanicosModel::Consolidacion($_REQUEST['systemdate']);
				if($consolida == 1){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{
					$resultado 	= ContMecanicosModel::eliminarRegistro($_REQUEST['systemdate'],$_REQUEST['shift']);
			    		$resta  	= ContMecanicosModel::BuscarContometros(trim($_REQUEST['fecha']), trim($_REQUEST['fecha2']),$_REQUEST['estacion'],trim($_REQUEST['turno']));
					$result    	= ContMecanicosTemplate::formSearch($_REQUEST['fecha'], $_REQUEST['fecha2'],$_REQUEST['estacion'],$_REQUEST['turno']);		
		    			$result_f  	= ContMecanicosTemplate::listado($resta,$_REQUEST['fecha'], $_REQUEST['fecha2'],$_REQUEST['estacion'],$_REQUEST['turno']);

				}
		    	break;

		    	default:
					$form_search = true;
			break;
		}

		if ($form_search) {
		    	$result = ContMecanicosTemplate::formSearch(date("01/".m."/".Y), date(d."/".m."/".Y),$_REQUEST['estacion'],$_REQUEST['turno']);
		}

		if ($listado) {
			$result    = ContMecanicosTemplate::formSearch($_REQUEST['fecha'], $_REQUEST['fecha2'],$_REQUEST['estacion'],$_REQUEST['turno']);		
		    	$resultado = ContMecanicosModel::BuscarContometros($_REQUEST['fecha'], $_REQUEST['fecha2'],$_REQUEST['estacion'],$_REQUEST['turno']);	    	
			if($resultado == false){
				if(empty($_REQUEST['turno'])){
					?><script>alert("<?php echo 'No hay contometros Del '.$_REQUEST['fecha'].' Al '.$_REQUEST['fecha2'] ?> ");</script><?php
				}else{
					?><script>alert("<?php echo 'No hay contometros Del '.$_REQUEST['fecha'].' Al '.$_REQUEST['fecha2'].' con turno '.$_REQUEST['turno'] ?> ");</script><?php
				}
			}else{
			    	$result_f  = ContMecanicosTemplate::listado($resultado,$_REQUEST['fecha'], $_REQUEST['fecha2'],$_REQUEST['turno']);
			}
		}

		$this->visor->addComponent("ContentT", "content_title", ContMecanicosTemplate::titulo());
		if ($result != "") 
			$this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") 
			$this->visor->addComponent("ContentF", "content_footer", $result_f);

    	}
}
