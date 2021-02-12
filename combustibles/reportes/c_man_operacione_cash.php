<?php

class MAN_CASHOPEController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_man_operacione_cash.php';
		include 'reportes/t_man_operacione_cash.php';
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
				$busqueda    	= MAN_CASHOPEModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				if($busqueda == ''){
					?><script>alert("<?php echo 'No hay datos desde '.$_REQUEST['fecha'].' al '.$_REQUEST['fecha2'];?> ");</script><?php
				}else{
					$busqueda    	= MAN_CASHOPEModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= MAN_CASHOPETemplate::formSearch($busqueda['paginacion'],$vec);
					$result_f 	= MAN_CASHOPETemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			break;

			case "Agregar":

				echo 'Entro a Agregar'."\n";	
				$result 	= MAN_CASHOPETemplate::formAgregar($_REQUEST['fecha'],$_REQUEST['fecha2'],"");
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				echo 'Entro a Modificar'."\n";	
				$resultado = MAN_CASHOPEModel::recuperarRegistroArray($_REQUEST['ncuenta']);
                                var_dump($resultado);
				$result	= MAN_CASHOPETemplate::formAgregar($resultado,"","");
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				$resultado 	= MAN_CASHOPEModel::eliminarRegistro($_REQUEST['ncuenta']);
				$busqueda    	= MAN_CASHOPEModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina']);
				$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= MAN_CASHOPETemplate::formSearch($busqueda['paginacion'],$vec);
				$result_f 	= MAN_CASHOPETemplate::resultadosBusqueda($busqueda['datos']);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

			    	break;

		
			case "Guardar":
                            if( isset($_REQUEST['name']) && isset($_REQUEST['type']) && isset($_REQUEST['accounts'])){
                             $res = MAN_CASHOPEModel::agregar($_REQUEST['name'],$_REQUEST['accounts'],$_REQUEST['type'] );	
   
                            }else{
                                ?><script>alert('Error no se pudo insertar por que falto completar campos.');</script><?php
                            }

				
				if($res == 1){
					?><script>alert('Registro guardado correctamente');</script><?php
					$busqueda    	= MAN_CASHOPEModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina']);
					$result     	= MAN_CASHOPETemplate::formSearch($busqueda['paginacion']);
					$result_f 	= MAN_CASHOPETemplate::resultadosBusqueda($busqueda['datos']);
					$buscar = true;
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{
					?><script>alert("<?php echo 'Ya existe el codigo Rubro '.$_REQUEST['ncuenta'].' ';?> ");</script><?php
					$result_f = "&nbsp;";
				}

				break;

			case "Actualizar":
//var_dump($_REQUEST);
				$var = MAN_CASHOPEModel::actualizar($_REQUEST['c_cash_operation_id'] ,$_REQUEST['name'], $_REQUEST['type'],$_REQUEST['accounts']);	

				if ($var == ''){
					?><script>alert('Registro actualizado correctamente')</script><?php
					$busqueda    	= MAN_CASHOPEModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina']);
					$result     	= MAN_CASHOPETemplate::formSearch($busqueda['paginacion']);
					$result_f 	= MAN_CASHOPETemplate::resultadosBusqueda($busqueda['datos']);
					$buscar = true;
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}else{					
					?><script>alert('No ha echo nongun cambio')</script><?php
				}

				break;

			default:

				$busqueda    	= MAN_CASHOPEModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina']);
				var_dump($busqueda['paginacion']);
                                 echo "*---------------";
                                var_dump($busqueda['datos']);
                               
                                $result     	= MAN_CASHOPETemplate::formSearch($busqueda['paginacion']);
				$result_f 	= MAN_CASHOPETemplate::resultadosBusqueda($busqueda['datos']);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", MAN_CASHOPETemplate::titulo());

	}
}
