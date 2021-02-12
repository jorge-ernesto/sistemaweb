<?php
  // Controlador del Modulo x

  Class ResumenController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
      //otras variables de entorno
    }

    function Run()
    {
      $this->Init();
      include('facturacion/m_resumen.php');
      include('facturacion/t_resumen.php');
      $this->visor->addComponent('ContentT', 'content_title', ResumenTemplate::titulo());
      $listado=false;
      switch ($this->request){//task
      case 'RESUMEN':
	      	switch ($this->action)
			{
				case 'Buscar':
					$datos = ResumenModel::obtenerResumenes($_REQUEST['busqueda']);
					$result = ResumenTemplate::listado($datos['datos']);
					$this->visor->addComponent("error", "error_body", '');
				    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
					break;

				case 'setRegistroCli':
					$result = ResumenTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
            		$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
					break;
				
				case 'Eliminar':
					if (ResumenModel::eliminarResumen($_REQUEST['registroid']) == 'f') {
						$this->visor->addComponent("error", "error_body", ResumenTemplate::errorResultado('El resumen no se pudo eliminar porque tiene aplicaciones o cancelaciones'));
					}else{
						$this->visor->addComponent("error", "error_body", '');
					}
					$datos = ResumenModel::obtenerResumenes($_REQUEST['busqueda']);
					$result = ResumenTemplate::listado($datos['datos']);
				    $this->visor->addComponent("ListadoB", "resultados_grid", $result);
					break;	
					
			    default:
			       $listado = true;
			    	break;
			}
			if ($listado) {
				
			    $result = ResumenTemplate::formBuscar();
			    $this->visor->addComponent("ContentB", "content_body", $result);
			}
	        break;
      
      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
?>

