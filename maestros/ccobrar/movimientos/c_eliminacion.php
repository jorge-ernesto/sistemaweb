<?php

class EliminacionController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
      //otras variables de entorno
    }

    function Run(){

      $this->Init();
      $result = '';
      $result_f = '';

      include('movimientos/m_eliminacion.php');
      include('movimientos/t_eliminacion.php');

      $this->visor->addComponent('ContentT', 'content_title', EliminacionTemplate::titulo());
      
      switch ($this->request){//task	

	 case 'ELIMINACION':
	      $listado = false;

		switch ($this->action){
	
			case 'setRegistroCli':
			
			    	$result = EliminacionTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
			        $this->visor->addComponent("desc_cliente", "desc_cliente", $result);
			      	break;
			
			case 'Eliminar':
								
				$delet = EliminacionModel::EliminarCuenta($_REQUEST['codigo'],$_REQUEST['tipo'],$_REQUEST['serie'],$_REQUEST['numero'], $_REQUEST['importe']);

				if($delet == ok){

					$dato['codigo'] = $_REQUEST['buscando'];
					$listado    = EliminacionModel::tmListado($dato);
			    		$result     = EliminacionTemplate::formBuscar($dato['codigo']);
			    		$result    .= EliminacionTemplate::listado($listado['datos'],$_REQUEST['busqueda']);
					$this->visor->addComponent("ContentB", "content_body", $result);

				}

		  		break;
	            
	      	        case 'Buscar':
		       
				$listado = true;

		    	default:

		      		$listado = true;
		       		break;
	}
	
	if ($listado){

		print_r($_REQUEST['busqueda']);
	    	$listado    = EliminacionModel::tmListado($_REQUEST['busqueda']);
	    	$result     = EliminacionTemplate::formBuscar("");
	    	$result    .= EliminacionTemplate::listado($listado['datos'],$_REQUEST['busqueda']);
        	$this->visor->addComponent("ContentB", "content_body", $result);

	}

      			break;
      			case 'ELIMINACIONDET':
        			//Si hay detalles
      			break;

        default:
        	$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
        break;

      }
    }
  }
