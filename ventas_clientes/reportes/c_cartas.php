<?php

class CartasController extends Controller
{
    function Init()
    {
	  $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
    }
        
    function Run(){
		//include 'reportes/m_cartas.php';
		
		
		$this->Init();
		include 't_cartas.php';
		include 'm_cartas.php';
		$this->visor->addComponent("ContentT", "content_title", CartasTemplate::titulo());
		switch ($this->request){
	    	case 'CARTAS':
				switch ($this->action) {
				    
					case 'Reporte':
						$datos = CartasModel::verificarRango($_REQUEST['reporte']['desde'],$_REQUEST['reporte']['hasta']);
						//print_r($datos);
						if (!is_array($datos)){
							print_r('el rango esta errado');
						}else{
							//$totaldatos =array();
							/*$cadenafinal = '{';
							for ($i=0; $i<count($datos); $i++){
								if ($i==count($datos)-1)	
									$cadenafinal .= '"'.$datos[$i].'"';
								else $cadenafinal .= '"'.$datos[$i].'",';
							}
							$cadenafinal .= '}';*/
							//print_r($datos);
							$totaldatos = CartasModel::obtenerCarta($datos[0]);
							print_r($totaldatos);
							$this->visor->addComponent('Listadogrid', 'resultados_grid', CartasTemplate::pdfImprimir($totaldatos));	
							//$this->visor->addComponent('Listadogrid', 'resultados_grid', 'fgfgd');	
						}
						break;
					
				    default:
						$listado = true;
						break;
				}
					
				if ($listado) {
				    $resultados = CartasTemplate::formImprimir();
				    $this->visor->addComponent('ContentB', 'content_body', $resultados);
				}
				break;
			default:
		  		$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
				break;
		  	}
    }
}

