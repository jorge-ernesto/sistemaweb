<?php

class VarillasController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {
	    ob_start();

		include 'reportes/m_venta_gnv.php';
		include 'reportes/t_venta_gnv.php';
		include('../include/paginador_new.php');

		$this->Init();

		$result 	= '';
		$result_f 	= '';
		$buscar 	= false;

	    if(!isset($_REQUEST['rxp'], $_REQUEST['pagina'])) {
			$_REQUEST['rxp'] 		= 40;
		 	$_REQUEST['pagina'] 	= 1;
	    }

		switch($this->action) {

			case "Buscar":

				$busqueda    	= VarillasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['estacion']);

				if($busqueda == ''){
					?><script>alert("<?php echo 'No hay datos en este rango de fecha '.$_REQUEST['fecha'].' - '.$_REQUEST['fecha2'];?> ");</script><?php
				}else{
					$busqueda   = VarillasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['estacion']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     = VarillasTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= VarillasTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			break;

			
			case "Excel":
				$busqueda    	= VarillasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['estacion']);
				if($busqueda == ''){
					?><script>alert("<?php echo 'No hay datos en este rango de fecha '.$_REQUEST['fecha'].' - '.$_REQUEST['fecha2'];?> ");</script><?php
				}else{
					$busqueda    	= VarillasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['estacion']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= VarillasTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f	= VarillasTemplate::reporteExcel($busqueda['datos'], $_REQUEST['fecha'], $_REQUEST['fecha2']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}					


			break;

			case "Agregar":
				$continicial	= VarillasModel::ContometroInicial();
				$result 	= VarillasTemplate::formAgregar($continicial);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				$resultado = VarillasModel::recuperarRegistroArray($_REQUEST['ch_almacen'],$_REQUEST['dt_fecha']);
				$result	= VarillasTemplate::formAgregar($resultado);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				$consolida   	= VarillasModel::Consolidacion($_REQUEST['dt_fecha'], $_REQUEST['ch_almacen']);
				if($consolida == 0){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{
					$resultado 	= VarillasModel::eliminarRegistro($_REQUEST['ch_almacen'],$_REQUEST['dt_fecha']);
					$busqueda    	= VarillasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['estacion']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= VarillasTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= VarillasTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			    break;

			case "Guardar":

				$consolida   	= VarillasModel::Consolidacion($_REQUEST['dt_fecha'], $_REQUEST['ch_almacen']);

				if($consolida == 0){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{

					$res = VarillasModel::agregar($_REQUEST['ch_almacen'],$_REQUEST['dt_fecha'],$_REQUEST['cnt_inicial'],$_REQUEST['cnt_final'],$_REQUEST['tot_cantidad'],$_REQUEST['tot_venta'],$_REQUEST['tot_abono'],$_REQUEST['tot_afericion'],$_REQUEST['tot_cli_credito'],$_REQUEST['tot_cli_anticipo'],$_REQUEST['tot_tar_credito'],$_REQUEST['tot_descuentos'],$_REQUEST['tot_trab_faltantes'],$_REQUEST['tot_trab_sobrantes'],$_REQUEST['tot_soles'],$_REQUEST['tot_dolares'],$_REQUEST['surtidor_soles'],$_REQUEST['surtidor_m3'],$_REQUEST['mermas'],$_REQUEST['nu_costo_unitario']);

					if($res == 0){
							?><script>alert('Error al guardar el registro');</script><?php
					}else{
						if($res == 1){
							?><script>alert('Registro guardado correctamente');</script><?php
							$busqueda    	= VarillasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['estacion']);
							$result     	= VarillasTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
							$result_f 	= VarillasTemplate::resultadosBusqueda($busqueda['datos']);
							$buscar = true;
							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);
						}else{
							?><script>alert("<?php echo 'Ya existe el Registro en el Almacen: '.$_REQUEST['ch_almacen'].' con Fecha: '.$_REQUEST['dt_fecha'].' ingresar otro' ; ?> ");</script><?php
							$result_f = "&nbsp;";
						}
					}
				}
			
				break;

			case "Actualizar":

				$consolida   	= VarillasModel::ConsolidacionA($_REQUEST['dt_fecha'], $_REQUEST['ch_almacen']);
				if($consolida == 0){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{

					$var = VarillasModel::ActualizarRegistro($_REQUEST['ch_almacen'],$_REQUEST['dt_fecha'],$_REQUEST['cnt_inicial'],$_REQUEST['cnt_final'],$_REQUEST['tot_cantidad'],$_REQUEST['tot_venta'],$_REQUEST['tot_abono'],$_REQUEST['tot_afericion'],$_REQUEST['tot_cli_credito'],$_REQUEST['tot_cli_anticipo'],$_REQUEST['tot_tar_credito'],$_REQUEST['tot_descuentos'],$_REQUEST['tot_trab_faltantes'],$_REQUEST['tot_trab_sobrantes'],$_REQUEST['tot_soles'],$_REQUEST['tot_dolares'],$_REQUEST['surtidor_soles'],$_REQUEST['surtidor_m3'],$_REQUEST['mermas'],$_REQUEST['nu_costo_unitario']);

						if ($var == ''){

							?><script>alert('Registro actualizado correctamente')</script><?php
							$busqueda    	= VarillasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['estacion']);
							$result     	= VarillasTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
							$result_f 	= VarillasTemplate::resultadosBusqueda($busqueda['datos']);
							$buscar = true;
							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}else{					
							?><script>alert('No ha echo nongun cambio')</script><?php
						}

				}

				break;

			default:

				$busqueda   = VarillasModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2'],$_REQUEST['almacen']);
				$result     = VarillasTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$result_f 	= VarillasTemplate::resultadosBusqueda($busqueda['datos'], "", "");
				$buscar 	= true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

			break;
		}
		$this->visor->addComponent("ContentT", "content_title", VarillasTemplate::titulo());
	}
}