<?php

class TipodeCambioController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'maestros/m_tipodecambio.php';
		include 'maestros/t_tipodecambio.php';
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
				$busqueda    	= TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				if($busqueda == ''){
					?><script>alert("<?php echo ' No hay ningun registro '; ?> ");</script><?php
				}else{
					$busqueda    	= TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= TipodeCambioTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= TipodeCambioTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			break;

			case "Agregar":

				echo 'Entro a Agregar'."\n";	
				$result 	= TipodeCambioTemplate::formAgregar($_REQUEST['fecha'],$_REQUEST['fecha2'],"");
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				echo 'Entro a Modificar'."\n";	

				$resultado = TipodeCambioModel::recuperarRegistroArray($_REQUEST['tca_moneda'],$_REQUEST['tca_fecha']);
				$result	= TipodeCambioTemplate::formAgregar($resultado,"","");
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				$resultado 	= TipodeCambioModel::eliminarRegistro($_REQUEST['tca_moneda'],$_REQUEST['tca_fecha']);
				$busqueda    	= TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= TipodeCambioTemplate::formPag($busqueda['paginacion'],$vec);
				$result_f 	= TipodeCambioTemplate::resultadosBusqueda($busqueda['datos']);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
			    	break;

		
			case "Guardar":

				if ($_REQUEST['tca_moneda'] == '' or $_REQUEST['compra_libre'] == '' or $_REQUEST['venta_libre'] == '' or $_REQUEST['compra_banco'] == '' or $_REQUEST['venta_banco']  == '' or $_REQUEST['compra_oficial'] == '' or $_REQUEST['venta_oficial']  == ''){
					$result_f = "<script>alert('Llenar campos faltantes..!!');</script>";				
				}else{
					$res = TipodeCambioModel::agregar($_REQUEST['tca_moneda'],$_REQUEST['tca_fecha'],$_REQUEST['compra_libre'],$_REQUEST['venta_libre'],$_REQUEST['compra_banco'],$_REQUEST['venta_banco'],$_REQUEST['compra_oficial'],$_REQUEST['venta_oficial']);

					if($res == 1){

						?><script>alert('Registro guardado correctamente');</script><?php
						$busqueda    	= TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);

						$result     	= TipodeCambioTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
						$result_f 	= TipodeCambioTemplate::resultadosBusqueda($busqueda['datos'],"","");
						$buscar = true;
						$this->visor->addComponent("ContentB", "content_body", $result);
						$this->visor->addComponent("ContentF", "content_footer", $result_f);

					}else{
						?><script>alert("<?php echo 'Ya existe la moneda '.$_REQUEST['tca_moneda'].' o la fecha '.$_REQUEST['tca_fecha'].' debe ingresar otra' ; ?> ");</script><?php
						$result_f = "&nbsp;";
					}
								
				}

				break;

			case "Actualizar":

				$var = TipodeCambioModel::actualizar($_REQUEST['tca_moneda'],$_REQUEST['tca_fecha'],$_REQUEST['compra_libre'],$_REQUEST['venta_libre'],$_REQUEST['compra_banco'],$_REQUEST['venta_banco'],$_REQUEST['compra_oficial'],$_REQUEST['venta_oficial']);

					if ($var == ''){

						?><script>alert('Registro actualizado correctamente')</script><?php
						$busqueda    	= TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
						$result     	= TipodeCambioTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
						$result_f 	= TipodeCambioTemplate::resultadosBusqueda($busqueda['datos']);
						$buscar = true;
						$this->visor->addComponent("ContentB", "content_body", $result);
						$this->visor->addComponent("ContentF", "content_footer", $result_f);

					}else{					
						?><script>alert('No ha echo nongun cambio')</script><?php
					}
				break;

			default:

				$busqueda    	= TipodeCambioModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= TipodeCambioTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$result_f 	= TipodeCambioTemplate::resultadosBusqueda($busqueda['datos'], "", "");
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", TipodeCambioTemplate::titulo());


	}
}
