<?php

class CuentasBancariasController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_cuentas_bancarias.php';
		include 'reportes/t_cuentas_bancarias.php';
		include('../include/paginador_new.php');

		$this->Init();
		$result = '';
		$result_f = '';
		$buscar = false;

	      	if(!isset($_REQUEST['rxp'],$_REQUEST['pagina'])) {
			$_REQUEST['rxp'] = 30;
		 	$_REQUEST['pagina'] = 1;
	      	}

		switch($this->action) {

			case "Buscar":

				echo 'Entro al Reporte'."\n";
				$busqueda    	= CuentasBancariasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['banco']);
				if($busqueda == ''){
					?><script>alert("<?php echo 'No hay registros en este Banco';?> ");</script><?php
				}else{
					$busqueda    	= CuentasBancariasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['banco']);
					$result     	= CuentasBancariasTemplate::formPag($busqueda['paginacion']);
					$result_f 	= CuentasBancariasTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			break;

			case "Agregar":

				echo 'Entro a Agregar'."\n";	
				$result 	= CuentasBancariasTemplate::formAgregar("");
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				echo 'Entro a Modificar'."\n";	
				$resultado = CuentasBancariasModel::recuperarRegistroArray($_REQUEST['ncuenta'], $_REQUEST['idbanco']);
				$result	= CuentasBancariasTemplate::formAgregar($resultado);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				$resultado 	= CuentasBancariasModel::eliminarRegistro($_REQUEST['ncuenta'], $_REQUEST['idbanco']);
				$busqueda    	= CuentasBancariasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['banco']);
				$result     	= CuentasBancariasTemplate::formPag($busqueda['paginacion']);
				$result_f 	= CuentasBancariasTemplate::resultadosBusqueda($busqueda['datos']);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

			    	break;

		
			case "Guardar":

				$res = CuentasBancariasModel::agregar($_REQUEST['ncuenta'],$_REQUEST['banco'],$_REQUEST['currency'],$_REQUEST['nombre'],$_REQUEST['ini']);	

				if($res == 1){
					?><script>alert('Registro guardado correctamente');</script><?php
					$busqueda    	= CuentasBancariasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['banco']);
					$result     	= CuentasBancariasTemplate::formSearch($busqueda['paginacion']);
					$result_f 	= CuentasBancariasTemplate::resultadosBusqueda($busqueda['datos']);
					$buscar = true;
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{
					?><script>alert("<?php echo 'Ya existe el Nro. de cuenta '.$_REQUEST['ncuenta'].' ';?> ");</script><?php
					$result_f = "&nbsp;";
				}

				break;

			case "Actualizar":

				$var = CuentasBancariasModel::actualizar($_REQUEST['ncuenta'], $_REQUEST['name'], $_REQUEST['ini'], $_REQUEST['idbanco']);	

				if ($var == ''){
					?><script>alert('Registro actualizado correctamente')</script><?php
					$busqueda    	= CuentasBancariasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['idbanco']);
					$result     	= CuentasBancariasTemplate::formSearch($busqueda['paginacion']);
					$result_f 	= CuentasBancariasTemplate::resultadosBusqueda($busqueda['datos']);
					$buscar = true;
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{					
					?><script>alert('No ha echo ningun cambio')</script><?php
				}

				break;

			default:

				$busqueda    	= CuentasBancariasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['banco']);
				$result     	= CuentasBancariasTemplate::formSearch($busqueda['paginacion']);
				$result_f 	= CuentasBancariasTemplate::resultadosBusqueda($busqueda['datos']);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", CuentasBancariasTemplate::titulo());

	}
}
