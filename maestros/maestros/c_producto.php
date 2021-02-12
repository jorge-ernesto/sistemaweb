<?php

class ProductoController extends Controller {

	function Init() {	      	
	      	$this->visor = new Visor();
	      	$this->task = @$_REQUEST["task"];
	      	$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';		
    	}

	function Run() {
		include 'maestros/m_producto.php';
		include 'maestros/t_producto.php';
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
				$busqueda    	= ProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				if($busqueda == ''){
					?><script>alert("<?php echo ' No hay ningun registro '; ?> ");</script><?php
				}else{
					$busqueda    	= ProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= ProductoTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= ProductoTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}break;

			case "Agregar":

				echo 'Entro a Agregar'."\n";	
				$result 	= ProductoTemplate::formAgregar($_REQUEST['fecha'],$_REQUEST['fecha2']);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				echo 'Entro a Modificar'."\n";	

				$resultado = ProductoModel::recuperarRegistroArray($_REQUEST['codigo_combu'],$_REQUEST['nombre_combu']);
				$result	= ProductoTemplate::formAgregar($resultado);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				//echo"<script language=\"JavaScript\" type=\"text/javascript\">confirm('Deseas eliminar el codigo')</script>";  
				?><script>confirm("<?php echo 'Deseas eliminar el codigo '.$_REQUEST['codigo_combu'].' ? ' ; ?> ");</script><?php

				$resultado 	= ProductoModel::eliminarRegistro($_REQUEST['codigo_combu']);
				if($resultado  == 'OK'){
				$busqueda    	= ProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= ProductoTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$result_f 	= ProductoTemplate::resultadosBusqueda($busqueda['datos']);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			    	break;

		
			case "Guardar":

				if ($_REQUEST['ch_codigocombustible'] == '' or $_REQUEST['nu_preciocombustible'] == '' or $_REQUEST['ch_codigopec'] == '' or $_REQUEST['ch_codigocombex'] == '' or $_REQUEST['ch_nombrebreve']  == '' ){
					$result_f = "<script>alert('Llenar campos faltantes..!!');</script>";				
				}else{
					$res = ProductoModel::agregar($_REQUEST['ch_codigocombustible'],$_REQUEST['ch_nombrecombustible'],$_REQUEST['nu_preciocombustible'],$_REQUEST['ch_codigopec'],$_REQUEST['ch_codigocombex'],$_REQUEST['ch_nombrebreve']);

					if($res == 1){

						?><script>alert('Registro guardado correctamente');</script><?php
						$busqueda    	= ProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);

						$result     	= ProductoTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
						$result_f 	= ProductoTemplate::resultadosBusqueda($busqueda['datos']);
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

				$var = ProductoModel::actualizar($_REQUEST['ch_codigocombustible'],$_REQUEST['ch_nombrecombustible'],$_REQUEST['nu_preciocombustible'],$_REQUEST['ch_codigopec'],$_REQUEST['ch_codigocombex'],$_REQUEST['ch_nombrebreve']);

					if ($var == ''){

						?><script>alert('Registro actualizado correctamente')</script><?php
						$busqueda    	= ProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
						$result     	= ProductoTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
						$result_f 	= ProductoTemplate::resultadosBusqueda($busqueda['datos']);
						$buscar = true;
						$this->visor->addComponent("ContentB", "content_body", $result);
						$this->visor->addComponent("ContentF", "content_footer", $result_f);

					}else{					
						?><script>alert('No ha echo nongun cambio')</script><?php
					}
				break;

			default:

				$busqueda    	= ProductoModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= ProductoTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$result_f 	= ProductoTemplate::resultadosBusqueda($busqueda['datos']);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", ProductoTemplate::titulo());

	}
}
