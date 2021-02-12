<?php

class man_rubro_Controller extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_man_rubro.php';
		include 'reportes/t_man_rubro.php';
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

				$busqueda    	= man_rubro_Model::Paginacion($_REQUEST['rxp'], $_REQUEST['pagina'], trim($_REQUEST['rubro']));

				if($busqueda == ''){
					?><script>alert("<?php echo 'No hay datos'; ?>");</script><?php
				}else{
					$result     	= man_rubro_Template::formSearch($busqueda['paginacion'],$vec);
					$result_f 	= man_rubro_Template::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			break;

			case "Agregar":

				$result 	= man_rubro_Template::formAgregar($_REQUEST['fecha'],$_REQUEST['fecha2'],"");
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				$resultado = man_rubro_Model::recuperarRegistroArray($_REQUEST['ncuenta']);
                                var_dump($resultado);
				$result	= man_rubro_Template::formAgregar($resultado,"","");
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				$resultado 	= man_rubro_Model::eliminarRegistro($_REQUEST['ncuenta']);
				$busqueda    	= man_rubro_Model::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina']);
				$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= man_rubro_Template::formSearch($busqueda['paginacion'],$vec);
				$result_f 	= man_rubro_Template::resultadosBusqueda($busqueda['datos']);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

			    	break;

		
			case "Guardar":

				if(isset($_REQUEST['cod_rubro']) && isset($_REQUEST['descripcion_id']) && isset($_REQUEST['desc_breve']) && isset($_REQUEST['tipo_item'])){
					$res = man_rubro_Model::agregar($_REQUEST['cod_rubro'],$_REQUEST['descripcion_id'],$_REQUEST['desc_breve'],$_REQUEST['tipo_item']);	
				}else{
					?><script>alert('Error no se pudo insertar por que falto completar campos.');</script><?php
				}

				if($res == 1){
					?><script>alert('Registro guardado correctamente');</script><?php
					$busqueda    	= man_rubro_Model::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina']);
					$result     	= man_rubro_Template::formSearch($busqueda['paginacion']);
					$result_f 	= man_rubro_Template::resultadosBusqueda($busqueda['datos']);
					$buscar = true;
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{
					?><script>alert("<?php echo 'Ya existe el codigo Rubro '.$_REQUEST['ncuenta'].' ';?> ");</script><?php
					$result_f = "&nbsp;";
				}

				break;

			case "Actualizar":

				$var = man_rubro_Model::actualizar($_REQUEST['cod_rubro'],$_REQUEST['descripcion_id'],$_REQUEST['desc_breve'],$_REQUEST['tipo_item']);	

				if ($var == ''){
					?><script>alert('Registro actualizado correctamente')</script><?php
					$busqueda    	= man_rubro_Model::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina']);
					$result     	= man_rubro_Template::formSearch($busqueda['paginacion']);
					$result_f 	= man_rubro_Template::resultadosBusqueda($busqueda['datos']);
					$buscar = true;
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{					
					?><script>alert('No ha echo nongun cambio')</script><?php
				}

				break;

			default:

				$busqueda    	= man_rubro_Model::Paginacion($_REQUEST['rxp'], $_REQUEST['pagina'], trim($_REQUEST['rubro']));
                               
                                $result     	= man_rubro_Template::formSearch($busqueda['paginacion']);
				$result_f 	= man_rubro_Template::resultadosBusqueda($busqueda['datos']);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", man_rubro_Template::titulo());

	}
}
