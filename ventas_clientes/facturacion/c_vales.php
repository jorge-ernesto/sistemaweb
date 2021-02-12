<?php
  // Controlador del Modulo x

  Class ValesController extends Controller{
    function Init(){
      //Verificar seguridad
      $this->visor = new Visor();
      $this->task = @$_REQUEST["task"];
      $this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
      $this->datos = isset($_REQUEST["vales"])?$_REQUEST["vales"]:'';
      //otras variables de entorno
    }

    function Run()
    {
      $this->Init();
      include('facturacion/m_vales.php');
      include('facturacion/t_vales.php');
      $this->visor->addComponent('ContentT', 'content_title', ValesTemplate::titulo());
      $listado=false;
      switch ($this->request){//task
	      case 'LISTADO':
	      	switch ($this->action)
			{
				case 'setRegistroVale':
					$result = ValesTemplate::setRegistrosVale($_REQUEST["codigo"]);
            		$this->visor->addComponent("desc_vales", "desc_vales", $result);
					break;
					
				case 'setRegistro':
					$result = ValesTemplate::setRegistros($_REQUEST["codigo"]);
            		$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
					break;
					
				case 'Agregar':
					$result = ValesTemplate::formAgregar(array());
					$this->visor->addComponent("error_body", "error_body", '');
					$this->visor->addComponent("ListadoB", "resultados_grid", '');
					$this->visor->addComponent("ContentB", "content_body", $result);
					break;
					
				case 'setRegistroCli':
					$result = ValesTemplate::setRegistrosCliente($_REQUEST["codigocli"]);
            		$this->visor->addComponent("desc_cliente", "desc_cliente", $result);
					break;
				
				case 'Modificar':
					$datos = ValesModel::getValesporTarjeta($_REQUEST['tipo'],$_REQUEST['inicio'],$_REQUEST['fin']);
					$result = ValesTemplate::formEditar($datos);
					$this->visor->addComponent("ListadoB", "resultados_grid", '');
					$this->visor->addComponent("error_body", "error_body", '');
					$this->visor->addComponent("ContentB", "content_body", $result);
					$listado=false;
					break;	
				
				case 'Regresar':
					$datos   = ValesModel::getlistadoVales($_REQUEST['busqueda']['codigo'],$_REQUEST['busqueda']['radio']);
					$result2 = ValesTemplate::formBuscar();
					$result  = ValesTemplate::listado($datos['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result2);
					$this->visor->addComponent("ListadoB", "resultados_grid", $result);
					$listado=false;
					break;	
						
				case 'Eliminar':
					$dat = ValesModel::EliminarValesTarjeta($_REQUEST['cliente'],$_REQUEST['tipovale'],$_REQUEST['inicio'],$_REQUEST['fin']);
					$listado=false;
					if ($dat==''){
						$datos = ValesModel::getlistadoVales($_REQUEST['busqueda']['codigo'],$_REQUEST['busqueda']['radio']);
						$result = ValesTemplate::listado($datos['datos']);
						$this->visor->addComponent("ListadoB", "resultados_grid", $result);
						$listado=false;
					}else{
						$result = valesTemplate::errorResultado($dat);
						$this->visor->addComponent("error", "error_body", $result);
					}
					break;
					
				case 'Guardar':
					$otros = ValesModel::TarjetasMagneticas($_REQUEST['vales']['ch_cliente']);
			    	$listado = false;
					if ($otros!='NO_GRUPO'){
				    	$result = ValesModel::guardarCabecera($this->datos);
				    	if ($result!=''){
							$result = ValesTemplate::errorResultado($result);
							$this->visor->addComponent("error", "error_body", $result);
						}else{
							
							$result = ValesTemplate::formAgregar();
							$this->visor->addComponent("ContentB", "content_body", $result);
							$result = ValesTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LOS VALES DE LA TARJETA '.$_REQUEST['vales']['ch_tarjeta'].' !!!');
							$this->visor->addComponent("error", "error_body", $result);
						}
					} else {
				    		$result = valesTemplate::errorResultado('ERROR: EL CLIENTE ESTA ERRADO');
							$this->visor->addComponent("error", "error_body", $result);
				    }
					break;
				case 'Buscar':
					$datos = ValesModel::getlistadoVales($_REQUEST['busqueda']['codigo'],$_REQUEST['busqueda']['radio']);
					$result = ValesTemplate::listado($datos['datos']);
					//print_r($result);
					$this->visor->addComponent("ListadoB", "resultados_grid", $result);
					$listado = false;
					break;
					
				case 'Bloquear':
					$datos=ValesModel::bloquear($_REQUEST['tipo'],$_REQUEST['inicio'],$_REQUEST['fin'],$_REQUEST['vale']);
					$datos = ValesModel::getValesporTarjeta($_REQUEST['tipo'],$_REQUEST['inicio'],$_REQUEST['fin']);
					$result = ValesTemplate::formEditar($datos);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$listado=false;
					break;
				
				case 'Desbloquear':
					$datos = ValesModel::Desbloquear($_REQUEST['tipo'],$_REQUEST['inicio'],$_REQUEST['fin'],$_REQUEST['vale']);
					$datos = ValesModel::getValesporTarjeta($_REQUEST['tipo'],$_REQUEST['inicio'],$_REQUEST['fin']);
					$result = ValesTemplate::formEditar($datos);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$listado=false;
					break;	
					
				default:
			       $listado = true;
			    	break;
			}
			if ($listado) {
				$result = ValesTemplate::formBuscar();
			    $this->visor->addComponent("ContentB", "content_body", $result);
			}
	        break;
      
      default:
        $this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
      break;
      }
    }
  }
