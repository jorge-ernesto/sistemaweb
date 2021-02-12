<?php
  // Controlador del Modulo Generales

Class LadosController extends Controller{
	function Init(){
		//Verificar seguridad
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
		//otros variables de entorno
		}

	function Run(){
		$this->Init();
		$result = '';
		require('maestros/m_lados.php');
		require('maestros/t_lados.php');
		include('../include/paginador_new.php');
		$this->visor->addComponent('ContentT', 'content_title', LadosTemplate::titulo());
		if(!$_REQUEST['rxp'] && !$_REQUEST['pagina']){
			$_REQUEST['rxp'] = 100;
			$_REQUEST['pagina'] = 0;
		}
		switch ($this->request){//task
			case 'LADOS':
			//echo "ENTRO";
				$tablaNombre = 'LADOS';
				$listado = false;
				//evaluar y ejecutar $action
				switch ($this->action){
				
					case 'Agregar':
						$result = LadosTemplate::formLados(array());
						$this->visor->addComponent("ContentB", "content_body", $result);
						
						break;
					case 'Modificar':
						$record = LadosModel::recuperarRegistroArray($_REQUEST["registroid"]);
					
						$result = LadosTemplate::formLados($record);
						$this->visor->addComponent("ContentB", "content_body", $result);
						
						break;
					case 'Eliminar':
					
						$result = LadosModel::eliminarRegistro($_REQUEST["registroid"]);
						if ($result == OK){
							$listado= true;
						} else {
							$result = LadosTemplate::errorResultado($result);
							$this->visor->addComponent("ContentB", "content_body", $result);
						}
						break;
					case 'TEXTO':
						$listado=false;
						break;
					case 'Guardar':
						$listado = false;
						if($_REQUEST['accion']=='actualizar'){
						$result = LadosModel::actualizarRegistro(strtoupper($_REQUEST['lado']['txtlado']),
											strtoupper($_REQUEST['lado']['cbxprod1']),
											strtoupper($_REQUEST['lado']['cbxprod2']),
											strtoupper($_REQUEST['lado']['cbxprod3']),
											strtoupper($_REQUEST['lado']['cbxprod4']),
											strtoupper($_REQUEST['lado']['txtndcantidad']),
											strtoupper($_REQUEST['lado']['txtndprecio']),
											strtoupper($_REQUEST['lado']['txtndimporte']),
											strtoupper($_REQUEST['lado']['txtndcantidadcontometro']),
											strtoupper($_REQUEST['lado']['txtndimportecontometro']),
											strtoupper($_REQUEST['lado']['cbxidinterfase']),
											strtoupper($_REQUEST['lado']['txtladointerfase']));

						}
						else{
						$result = LadosModel::guardarRegistro(strtoupper($_REQUEST['lado']['txtlado']),
											strtoupper($_REQUEST['lado']['cbxprod1']),
											strtoupper($_REQUEST['lado']['cbxprod2']),
											strtoupper($_REQUEST['lado']['cbxprod3']),
											strtoupper($_REQUEST['lado']['cbxprod4']),
											strtoupper($_REQUEST['lado']['txtndcantidad']),
											strtoupper($_REQUEST['lado']['txtndprecio']),
											strtoupper($_REQUEST['lado']['txtndimporte']),
											strtoupper($_REQUEST['lado']['txtndcantidadcontometro']),
											strtoupper($_REQUEST['lado']['txtndimportecontometro']),
											strtoupper($_REQUEST['lado']['cbxidinterfase']),
											strtoupper($_REQUEST['lado']['txtladointerfase']));

						}echo "Result: $result<br>";
						if ($result!=''){
								$result = LadosTemplate::errorResultado('ERROR: LADO YA EXISTENTE');
								$this->visor->addComponent("error", "error_body", $result);
						}else{
								
								$result = LadosTemplate::formLados(array());
								$this->visor->addComponent("ContentB", "content_body", $result);
								$result = LadosTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LOS 					DATOS '.$_REQUEST['lado']['txtlado'].' !!!');
								$this->visor->addComponent("error", "error_body", $result);
						}
						break;
				case 'Buscar':
					//Listo
					$busqueda = LadosModel::tmListado($_REQUEST['busqueda'],$_REQUEST['rxp'],$_REQUEST['pagina']);
			
					$result = LadosTemplate::listado($busqueda['datos']);
					$this->visor->addComponent("ListadoB", "resultados_grid", $result);
					break;
			
				default:
					//listado
					$listado = true;
					break;
				}
				if ($listado) {
					$listado   = LadosModel::tmListado('',$_REQUEST['rxp'],$_REQUEST['pagina']);
					$result    = LadosTemplate::formBuscar($listado['paginacion']);
					$result   .= LadosTemplate::listado($listado['datos']);
			
					$this->visor->addComponent("ContentB", "content_body", $result);
				}
				//break;
		}
	}
}

