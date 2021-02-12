<?php

class Gasto_Controller extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_man_gastos.php';
		include 'reportes/t_man_gastos.php';
		include('../include/paginador_new.php');

		$this->Init();
		$result = '';
		$result_f = '';
		$buscar = false;

		switch($this->action) {

			case "Buscar":

				$busqueda    	= Gasto_Model::Paginacion($_REQUEST['id']);
				$result     	= Gasto_Template::formSearch();
				$result_f 	= Gasto_Template::resultadosBusqueda($busqueda);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

			break;

			case "Agregar":

				echo 'Entro a Agregar'."\n";	
				$result 	= Gasto_Template::formAgregar("");
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				echo 'Entro a Modificar'."\n";	
				$resultado = Gasto_Model::recuperarRegistroArray($_REQUEST['id']);
				$result	= Gasto_Template::formAgregar($resultado);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				$resultado 	= Gasto_Model::eliminarRegistro($_REQUEST['id']);
				$busqueda    	= Gasto_Model::Paginacion();
				$result     	= Gasto_Template::formSearch();
				$result_f 	= Gasto_Template::resultadosBusqueda($busqueda);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

			    	break;

		
			case "Guardar":

				if(isset($_REQUEST['name'])){
					$res = Gasto_Model::agregar($_REQUEST['name']);	
				}else{
                                	?><script>alert('Falta ingresar datos');</script><?php
				}
				
				if($res == 1){
					?><script>alert('Registro guardado correctamente');</script><?php
					$busqueda    	= Gasto_Model::Paginacion();
					$result     	= Gasto_Template::formSearch();
					$result_f 	= Gasto_Template::resultadosBusqueda($busqueda);
					$buscar = true;
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{
					?><script>alert("<?php echo 'No se pudo guardar' ;?> ");</script><?php
					$result_f = "&nbsp;";
				}

				break;

			case "Actualizar":

				$var = Gasto_Model::actualizar($_REQUEST['c_cash_operation_id'],$_REQUEST['name']);

				if ($var == ''){
					?><script>alert('Registro actualizado correctamente')</script><?php
					$busqueda    	= Gasto_Model::Paginacion();
					$result     	= Gasto_Template::formSearch();
					$result_f 	= Gasto_Template::resultadosBusqueda($busqueda);
					$buscar = true;
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{					
					?><script>alert('No ha realizado ningun cambio')</script><?php
				}

				break;

			default:

				$busqueda    	= Gasto_Model::Paginacion("");
                                $result     	= Gasto_Template::formSearch($busqueda['paginacion']);
				$result_f 	= Gasto_Template::resultadosBusqueda($busqueda);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", Gasto_Template::titulo());

	}
}
