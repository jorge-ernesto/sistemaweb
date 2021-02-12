<?php

class LiquidacionGNVController extends Controller {

	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action']) ? $this->action = $_REQUEST['action']:$this->action='';
	}

	function Run() {

	    	ob_start();

		include 'reportes/m_liquidacion_gnv.php';
		include 'reportes/t_liquidacion_gnv.php';
		include('../include/paginador_new.php');

		$this->Init();

		$result = '';
		$result_f = '';
		$buscar = false;

	      	if(!isset($_REQUEST['rxp'],$_REQUEST['pagina'])) {
			$_REQUEST['rxp'] = 40;
		 	$_REQUEST['pagina'] = 1;
	      	}

		switch($this->action) {

			case "Buscar":

				$busqueda    	= LiquidacionGNVModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				if($busqueda == ''){
					?><script>alert("<?php echo 'No hay datos en este rango de fecha '.$_REQUEST['fecha'].' - '.$_REQUEST['fecha2'];?> ");</script><?php
				}else{
					$busqueda    	= LiquidacionGNVModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= LiquidacionGNVTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= LiquidacionGNVTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			break;

			
			case "Excel":
					$resultado	= LiquidacionGNVModel::Paginacion($_REQUEST['ch_almacen'], $_REQUEST['desde'], $_REQUEST['hasta']);
					$resultt	= LiquidacionGNVTemplate::reporteExcel($resultado, $_REQUEST['ch_almacen'], $_REQUEST['desde'], $_REQUEST['hasta']);
					$result_f	= LiquidacionGNVTemplateExcel::reporteExcel();

			break;

			case "Agregar":
				$continicial	= LiquidacionGNVModel::ContometroInicial();
				$result 	= LiquidacionGNVTemplate::formAgregar($continicial);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Modificar":

				$resultado = LiquidacionGNVModel::recuperarRegistroArray($_REQUEST['ch_almacen'],$_REQUEST['dt_fecha']);
				$result	= LiquidacionGNVTemplate::formAgregar($resultado);
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", "");
				break;

			case "Eliminar":

				$consolida   	= LiquidacionGNVModel::Consolidacion($_REQUEST['dt_fecha']);
				if($consolida == 1){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{
					$resultado 	= LiquidacionGNVModel::eliminarRegistro($_REQUEST['ch_almacen'],$_REQUEST['dt_fecha']);
					$busqueda    	= LiquidacionGNVModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
					$vec 		= array($_REQUEST['fecha'], $_REQUEST['fecha2']);
					$result     	= LiquidacionGNVTemplate::formPag($busqueda['paginacion'],$vec);
					$result_f 	= LiquidacionGNVTemplate::resultadosBusqueda($busqueda['datos']);
					$this->visor->addComponent("ContentB", "content_body", $result);
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
				}
			    	break;

		
			case "Guardar":

				$consolida   	= LiquidacionGNVModel::Consolidacion($_REQUEST['dt_fecha']);

				if($consolida == 1){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{

					$res = LiquidacionGNVModel::agregar($_REQUEST['ch_almacen'],$_REQUEST['dt_fecha'],$_REQUEST['cnt_inicial'],$_REQUEST['cnt_final'],$_REQUEST['tot_cantidad'],$_REQUEST['tot_venta'],$_REQUEST['tot_abono'],$_REQUEST['tot_afericion'],$_REQUEST['tot_cli_credito'],$_REQUEST['tot_cli_anticipo'],$_REQUEST['tot_tar_credito'],$_REQUEST['tot_descuentos'],$_REQUEST['tot_trab_faltantes'],$_REQUEST['tot_trab_sobrantes'],$_REQUEST['tot_soles'],$_REQUEST['tot_dolares'],$_REQUEST['surtidor_soles'],$_REQUEST['surtidor_m3'],$_REQUEST['mermas']);	

					if($res == 0){
							?><script>alert('Error al guardar el registro');</script><?php
					}else{
						if($res == 1){
							?><script>alert('Registro guardado correctamente');</script><?php
							$busqueda    	= LiquidacionGNVModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
							$result     	= LiquidacionGNVTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
							$result_f 	= LiquidacionGNVTemplate::resultadosBusqueda($busqueda['datos']);
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

				$consolida   	= LiquidacionGNVModel::ConsolidacionA($_REQUEST['dt_fecha']);
				if($consolida == 1){
					?><script>alert("<?php echo 'La fecha ya esta consolidada';?> ");</script><?php
				}else{

					$var = LiquidacionGNVModel::ActualizarRegistro($_REQUEST['ch_almacen'],$_REQUEST['dt_fecha'],$_REQUEST['cnt_inicial'],$_REQUEST['cnt_final'],$_REQUEST['tot_cantidad'],$_REQUEST['tot_venta'],$_REQUEST['tot_abono'],$_REQUEST['tot_afericion'],$_REQUEST['tot_cli_credito'],$_REQUEST['tot_cli_anticipo'],$_REQUEST['tot_tar_credito'],$_REQUEST['tot_descuentos'],$_REQUEST['tot_trab_faltantes'],$_REQUEST['tot_trab_sobrantes'],$_REQUEST['tot_soles'],$_REQUEST['tot_dolares'],$_REQUEST['surtidor_soles'],$_REQUEST['surtidor_m3'],$_REQUEST['mermas']);

						if ($var == ''){

							?><script>alert('Registro actualizado correctamente')</script><?php
							$busqueda    	= LiquidacionGNVModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
							$result     	= LiquidacionGNVTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
							$result_f 	= LiquidacionGNVTemplate::resultadosBusqueda($busqueda['datos']);
							$buscar = true;
							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}else{					
							?><script>alert('No ha echo nongun cambio')</script><?php
						}

				}

				break;

			default:

				$busqueda    	= LiquidacionGNVModel::Paginacion($_REQUEST['rxp'],$_REQUEST['pagina'],$_REQUEST['fecha'], $_REQUEST['fecha2']);
				$result     	= LiquidacionGNVTemplate::formSearch(date(d."/".m."/".Y), date(d."/".m."/".Y),$busqueda['paginacion']);
				$result_f 	= LiquidacionGNVTemplate::resultadosBusqueda($busqueda['datos']);
				$buscar = true;
				$this->visor->addComponent("ContentB", "content_body", $result);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);

				break;

		}

		$this->visor->addComponent("ContentT", "content_title", LiquidacionGNVTemplate::titulo());


	}
}
