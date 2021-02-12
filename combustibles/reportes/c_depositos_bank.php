<?php

class DepositosBankController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'reportes/m_depositos_bank.php';
		include 'reportes/t_depositos_bank.php';
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
				$busqueda    	= DepositosBankModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				if($busqueda == ''){
					?><script>alert("<?php echo 'No hay datos en este rango de fecha '.$_REQUEST['fecha'].' - '.$_REQUEST['fecha2'];?> ");</script><?php
				}else{
					$busqueda    	= DepositosBankModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= DepositosBankTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= DepositosBankTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			break;

			case "Agregar":

				echo 'Entro a Agregar'."\n";	
				$result 	= DepositosBankTemplate::formAgregar($_REQUEST['fecha'],$_REQUEST['fecha2']);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				echo 'Entro a Modificar'."\n";	

				$resultado = DepositosBankModel::recuperarRegistroArray($_REQUEST['id']);
				$result	= DepositosBankTemplate::formAgregar($resultado);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				echo 'Entro a Eliminar'."\n";	
				$consolida   	= DepositosBankModel::Consolidacion($_REQUEST['dt_fecha'],$_REQUEST['ch_almacen']);
				if($consolida == 1){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{
					$resultado 	= DepositosBankModel::eliminarRegistro($_REQUEST['id']);
					$busqueda    	= DepositosBankModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= DepositosBankTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= DepositosBankTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			    	break;

		
			case "Guardar":

				$consolida   	= DepositosBankModel::Consolidacion($_REQUEST['dt_fecha'],$_REQUEST['ch_almacen']);
				if($consolida == 1){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{

					if($_REQUEST['docu'] == '')
						$docu = 0;
					else
						$docu = $_REQUEST['docu'];

					if($_REQUEST['refe'] == '')
						$refe = 0;
					else
						$refe = $_REQUEST['refe'];

					if($_REQUEST['total'] == '')
						$total = 0;
					else
						$total = $_REQUEST['total'];

					$res = DepositosBankModel::agregar($_REQUEST['ch_almacen'],$_REQUEST['dt_fecha'],$_REQUEST['moneda'],$_REQUEST['banco'],$docu,$refe,$total);	

					if($res == 0){
							?><script>alert('Error al guardar el registro');</script><?php
					}else{
						if($res == 1){
							?><script>alert('Registro guardado correctamente');</script><?php
							$busqueda    	= DepositosBankModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
							$result     	= DepositosBankTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
							$result_f 	= DepositosBankTemplate::resultadosBusqueda($busqueda['datos']);
							$buscar = true;
							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);
						}else{
							?><script>alert("<?php echo 'Ya existe el registro con '.$_REQUEST['ch_almacen'].' y la fecha '.$_REQUEST['dt_fecha'].' debes de ingresar otra' ; ?> ");</script><?php
							$result_f = "&nbsp;";
						}
					}
				}
			
				break;

			case "Actualizar":

				$consolida   	= DepositosBankModel::ConsolidacionA($_REQUEST['dt_fecha'],$_REQUEST['ch_almacen']);
				if($consolida == 1){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{
				
					$var = DepositosBankModel::actualizar($_REQUEST['nombre'],$_REQUEST['moneda'],$_REQUEST['docu'],$_REQUEST['refe'],$_REQUEST['total'],$_REQUEST['idred']);	

						if ($var == ''){

							?><script>alert('Registro actualizado correctamente')</script><?php
							$busqueda    	= DepositosBankModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
							$result     	= DepositosBankTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
							$result_f 	= DepositosBankTemplate::resultadosBusqueda($busqueda['datos']);
							$buscar = true;
							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}else{					
							?><script>alert('No ha echo nongun cambio')</script><?php
						}

				}

				break;

			default:

				$busqueda    	= DepositosBankModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= DepositosBankTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$result_f 	= DepositosBankTemplate::resultadosBusqueda($busqueda['datos']);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", DepositosBankTemplate::titulo());


	}
}
