<?php
	
class TiposCuentaController extends Controller {
		
	function Init() {
		$this->visor = new Visor();
		$this->task = @$_REQUEST["task"];
		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
	}
		
	function Run() {
		$this->Init();
		$result = '';
		$bolMensaje = '0';	
		include('promociones/m_tiposcuenta.php');
		include('promociones/t_tiposcuenta.php');
		include('../include/paginador_new.php'); 
		require("../clases/funciones.php");	
		$funcion = new class_funciones;				
		$this->visor->addComponent('ContentT', 'content_title',TiposCuentaTemplate::titulo());

		switch ($this->request) {
			case 'TIPOSCUENTA':
				$tablaNombre = 'TIPOSCUENTA';
				$listado = false;

				switch ($this->action) {
	
					case 'Nuevo':
						$_REQUEST['titulo'] ='NUEVO TIPO DE CUENTA';
						$result = TiposCuentaTemplate::formTiposcuenta(array());
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;

					case 'Guardar':
						$listado = false;
						$exito   = "";
						$tiposcuenta['idtipocuenta'] = trim($_REQUEST['idtipocuenta']);
						$tiposcuenta['descripcion']  = trim($_REQUEST['descripcion']);	
							
						if($_REQUEST['accion'] == '') {
							$result = TiposCuentaModel::ingresartiposcuenta($tiposcuenta['idtipocuenta'], $tiposcuenta['descripcion']);
						} else {
							$result = TiposCuentaModel::actualizartiposcuenta($tiposcuenta['idtipocuenta'], $tiposcuenta['descripcion']);
						}

						$exito = ($result == "0")?"0":"1";
						if ($exito == "1") {
							$_REQUEST['titulo'] ='INGRESAR TIPO DE CUENTA';
							$result = TiposCuentaTemplate::formTiposcuenta(array());
							$this->visor->addComponent("ContentB", "content_body", $result);
							$result = TiposCuentaTemplate::errorResultado('SE GRABO/ACTUALIZO CORRECTAMENTE LOS DATOS !');
							$this->visor->addComponent("error", "error_body", $result);	
						} else {
							$result =  TiposCuentaTemplate::errorResultado('ERROR: AL REGISTRAR EL REGISTRO, VERIQUE LOS DATOS !');
							$this->visor->addComponent("error", "error_body", $result);
						}	
						break;	

					case 'Modificar':
						$_REQUEST['titulo'] ='MODIFICAR TIPO DE CUENTA';
						$tiposcuenta['idtipocuenta'] = trim($_REQUEST['idtipocuenta']);
						$tiposcuenta['descripcion']  = trim($_REQUEST['descripcion']);	
						$result = TiposCuentaTemplate::formTiposcuenta($tiposcuenta);
						$this->visor->addComponent("ContentB", "content_body", $result);
						break;	

					case 'Eliminar':
						$result  = TiposCuentaModel::eliminartiposcuenta(trim($_REQUEST['idtipocuenta']));
						$listado = TiposCuentaModel::tmListado(' ','default');
						$result  = TiposCuentaTemplate::listado($listado['datos']);
						$this->visor->addComponent("ListadoB", "resultados_grid", $result);
						break;

					default:
						$listado = true;
						break;
				}
				if ($listado) { 
					$listado  = TiposCuentaModel::tmListado(' ','default');
					$result   = TiposCuentaTemplate::formBuscar($listado['paginacion']);   
					$result  .= TiposCuentaTemplate::listado($listado['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
				}				
		}
	}
}	
