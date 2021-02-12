<?php

class ConfiguracionProductoController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'maestros/m_configuracion_producto.php';
		include 'maestros/t_configuracion_producto.php';
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
				$busqueda    	= ConfiguracionProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				if($busqueda == ''){
					?><script>alert("<?php echo ' No hay ningun registro '; ?> ");</script><?php
				}else{
					$busqueda    	= ConfiguracionProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= ConfiguracionProductoTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= ConfiguracionProductoTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}break;

			case "Agregar":

				echo 'Entro a Agregar'."\n";	
				$result 	= ConfiguracionProductoTemplate::formAgregar();
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				echo 'Entro a Modificar'."\n";	

				$resultado = ConfiguracionProductoModel::recuperarRegistroArray($_REQUEST['ch_codigocombustible']);
				$result	= ConfiguracionProductoTemplate::formAgregar($resultado);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				$resultado 	= ConfiguracionProductoModel::eliminarRegistro($_REQUEST['ch_codigocombustible']);

				$busqueda    	= ConfiguracionProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= ConfiguracionProductoTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$result_f 	= ConfiguracionProductoTemplate::resultadosBusqueda($busqueda['datos']);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

			    	break;

		
			case "Guardar":

				if ($_REQUEST['ch_codigocombustible'] == '' or $_REQUEST['ch_nombrecombustible'] == '' or $_REQUEST['ch_nombrebreve'] == '' or $_REQUEST['nu_preciocombustible'] == '' or $_REQUEST['ch_codigopec']  == '' or $_REQUEST['ch_codigocombex']  == '' ){
					?><script>alert("<?php echo 'Llenar campos faltantes' ; ?> ");</script><?php
				}else{
					$res = ConfiguracionProductoModel::agregar($_REQUEST['ch_codigocombustible'],$_REQUEST['ch_nombrecombustible'],$_REQUEST['ch_nombrebreve'],$_REQUEST['nu_preciocombustible'],$_REQUEST['ch_codigopec'],$_REQUEST['ch_codigocombex']);

					if($res == 1){

						?><script>alert('Registro guardado correctamente');</script><?php

						$busqueda    	= ConfiguracionProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);

						$result     	= ConfiguracionProductoTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
						$result_f 	= ConfiguracionProductoTemplate::resultadosBusqueda($busqueda['datos']);
						$buscar = true;
						$this->visor->addComponent("ContentB", "content_body", $result);
						$this->visor->addComponent("ContentF", "content_footer", $result_f);

					}else{
						?><script>alert("<?php echo 'Ya existe codigo '.$_REQUEST['ch_codigocombustible'].' debes ingresar otro' ; ?> ");</script><?php
						$result_f = "&nbsp;";
					}
								
				}

				break;

			case "Actualizar":

				$var = ConfiguracionProductoModel::actualizar($_REQUEST['ch_codigocombustible'],$_REQUEST['ch_nombrecombustible'],$_REQUEST['nu_preciocombustible'],$_REQUEST['ch_codigopec'],$_REQUEST['ch_codigocombex'],$_REQUEST['ch_nombrebreve']);

					if ($var == ''){

						?><script>alert('Registro actualizado correctamente')</script><?php
						$busqueda    	= ConfiguracionProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
						$result     	= ConfiguracionProductoTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
						$result_f 	= ConfiguracionProductoTemplate::resultadosBusqueda($busqueda['datos']);
						$buscar = true;
						$this->visor->addComponent("ContentB", "content_body", $result);
						$this->visor->addComponent("ContentF", "content_footer", $result_f);

					}else{					
						?><script>alert('No ha echo nongun cambio')</script><?php
					}

				break;

			default:

				$busqueda    	= ConfiguracionProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= ConfiguracionProductoTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$result_f 	= ConfiguracionProductoTemplate::resultadosBusqueda($busqueda['datos']);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", ConfiguracionProductoTemplate::titulo());

	}
}
