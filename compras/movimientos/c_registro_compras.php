<?php

date_default_timezone_set('UTC');

class RegistroComprasController extends Controller {

	function Init() {
		$this->visor = new Visor();
  		$this->task = @$_REQUEST["task"];
  		$this->action = isset($_REQUEST["action"])?$_REQUEST["action"]:'';
  		$this->datos = isset($_REQUEST["datos"])?$_REQUEST["datos"]:'';
	}

	function Run() {
		$this->Init();

		include('movimientos/m_registro_compras.php');
		include('movimientos/t_registro_compras.php');
		include('../include/paginador_new.php');
		include('store_procedures.php'); 

		//Obtener clase model de registro de compras
		$objComprasModel = new RegistroComprasModel();

		$result		= '';
		$result_f 	= '';
		$hoy 		= date('d/m/Y');

		$this->visor->addComponent("ContentT", "content_title", RegistroComprasTemplate::titulo());

		if(!isset($_REQUEST['rxp'],$_REQUEST['pagina'])) {
			$_REQUEST['rxp'] 	= 30;
		 	$_REQUEST['pagina'] = 1;
	    }

		$ip = "";

		if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			$ip = getenv("REMOTE_ADDR");
		else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			$ip = $_SERVER['REMOTE_ADDR'];

		switch ($this->request){
			case 'REGISTROS':

				switch($this->action) {

					case 'setRegistroPro':
						$result = RegistroComprasTemplate::setRegistrosProveedor($_REQUEST["proveedor"]);
						$this->visor->addComponent("desc_proveedor", "desc_proveedor", $result);
					break;

					case 'setRegistroProB':
						$result = RegistroComprasTemplate::setRegistrosProveedorB($_REQUEST["proveedorb"]);
						$this->visor->addComponent("desc_proveedor", "desc_proveedor", $result);
					break;

					case "Imprimir":

						$documento 	= trim($_REQUEST['documento']);

						echo '<script> window.open("/sistemaweb/compras/imprimir_registro_compra.php?documento='.$documento.'","miwin","width=1000,height=500,scrollbars=yes, resizable=yes, menubar=no");</script>';

					break;

					case "Buscar":

						$rxp		= $_REQUEST['rxp'];
						$pagina		= $_REQUEST['pagina'];
						$fecha		= $_REQUEST['fecha'];
						$fecha2		= $_REQUEST['fecha2'];
						$almacen	= $_REQUEST['estacion'];
						$proveedor	= trim($_REQUEST['proveedorb']);
						$documento	= trim($_REQUEST['documento']);
						$tdocu		= $_REQUEST['tdocu'];
						$tmoneda	= $_REQUEST['tmoneda'];

						$busqueda    	= RegistroComprasModel::Paginacion($rxp, $pagina, $fecha, $fecha2, $almacen,$proveedor,$documento, $tdocu, $tmoneda);

						if($busqueda == ''){

							$result     	= RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion'], $almacen, $proveedor, $documento, $tdocu, $tmoneda);
							$result_f 	= "<center><blink style='color: red'><<<  No hay datos desde $fecha al $fecha2 >>></blink></center>";

							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}else{

							$result     	= RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion'], $almacen, $proveedor, $documento, $tdocu, $tmoneda, "");
							$result_f 	= RegistroComprasTemplate::resultadosBusqueda($busqueda['datos'], $fecha, $fecha2, $rxp, $pagina, $almacen, $proveedor, $documento, $tdocu, $tmoneda);

							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}

					break;

					case "Regresar":

						$rxp		= $_REQUEST['rxp'];
						$pagina		= $_REQUEST['pagina'];
						$fecha		= $_REQUEST['fecha'];
						$fecha2		= $_REQUEST['fecha2'];
						$almacen	= $_REQUEST['almacen'];
						$proveedor	= trim($_REQUEST['pro']);
						$doc		= trim($_REQUEST['doc']);
						$tdocu		= $_REQUEST['tdocu'];
						$tmoneda	= $_REQUEST['tmoneda'];

						if(empty($almacen)){
							$busqueda    	= RegistroComprasModel::Paginacion($rxp, $pagina, $hoy, $hoy, '', '', '', 'TODOS', 'TODOS');
							$result     	= RegistroComprasTemplate::formSearch($hoy, $hoy, $busqueda['paginacion'], $almacen, $proveedor, $documento, $tdocu, $tmoneda);
						}else{
							$busqueda    	= RegistroComprasModel::Paginacion($rxp, $pagina, $fecha, $fecha2, $almacen, $proveedor, $doc, $tdocu, $tmoneda);
							$result     	= RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion'], $almacen, $proveedor, $documento, $tdocu, $tmoneda);
						}

						if($busqueda == ''){

							$result_f 	= "<center><blink style='color: red'><<<  No hay datos desde $fecha al $fecha2 >>></blink></center>";

							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}else{

							if(empty($almacen))
								$result_f 	= RegistroComprasTemplate::resultadosBusqueda($busqueda['datos'], $hoy, $hoy, $rxp, $pagina, '', '', '', 'TODOS', 'TODOS');
							else
								$result_f 	= RegistroComprasTemplate::resultadosBusqueda($busqueda['datos'], $fecha, $fecha2, $rxp, $pagina, $estacion, $proveedorb, $doc, $tdocu, $tmoneda);

							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}

					break;

					case "Agregar":

						$rxp		= $_REQUEST['rxp'];
						$pagina		= $_REQUEST['pagina'];
						$fecha		= $_REQUEST['fecha'];
						$fecha2		= $_REQUEST['fecha2'];
						$almacen	= $_REQUEST['estacion'];
						$proveedor	= trim($_REQUEST['proveedor']);
						$documento	= trim($_REQUEST['documento']);
						$tdocu		= $_REQUEST['tdocu'];
						$tmoneda	= $_REQUEST['tmoneda'];

						$result 	= RegistroComprasTemplate::formAgregar($rxp, $pagina, $fecha, $fecha2, $almacen,$proveedor,$documento, $tdocu, $tmoneda);
						$this->visor->addComponent("ContentB", "content_body", $result);
						$this->visor->addComponent("ContentF", "content_footer", "");
						break;

					case "Reporte":

						$fecha		= $_REQUEST['fecha'];
						$fecha2		= $_REQUEST['fecha2'];
						$almacen	= $_REQUEST['estacion'];
						$proveedor	= trim($_REQUEST['proveedorb']);
						$documento	= trim($_REQUEST['documento']);
						$tdocu		= $_REQUEST['tdocu'];
						$tmoneda	= $_REQUEST['tmoneda'];
						$type_ple	= $_REQUEST['pletype'];

						$busqueda    	= RegistroComprasModel::PaginacionPDF($fecha, $fecha2, $almacen, $proveedor, $documento, $tdocu, $type_ple);

						if($busqueda){
							$result = RegistroComprasTemplate::reportePDF($busqueda, $fecha, $fecha2);
							$result     	.= RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion']);
							$this->visor->addComponent("ContentB", "content_body", $result);
							$mi_pdf = "/sistemaweb/compras/movimientos/pdf/RegistroCompras.pdf";
							header('Content-type: application/pdf');
							header('Content-Disposition: attachment; filename="'."RegistroCompras.pdf".'"');
							readfile($mi_pdf);
						}else{
							$result     	= RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion']);
							$this->visor->addComponent("ContentB", "content_body", $result);
							$result_f = "<center><blink style='color: red'><<<  No hay datos PDF desde $fecha al $fecha2 >>></blink></center>";
							$this->visor->addComponent("ContentF", "content_footer", $result_f);
						}

					break;

					case "Libros":
					
							$fecha		= $_REQUEST['fecha'];
							$fecha2		= $_REQUEST['fecha2'];
							$almacen	= $_REQUEST['estacion'];
							$proveedor	= trim($_REQUEST['proveedorb']);
							$documento	= trim($_REQUEST['documento']);
							$tdocu		= $_REQUEST['tdocu'];
							$tmoneda	= $_REQUEST['tmoneda'];
							$type_ple	= $_REQUEST['pletype'];

							$v 			= RegistroComprasModel::obtenerAlma($almacen);
							$ple    	= RegistroComprasModel::PaginacionPDF($fecha, $fecha2, $almacen, $proveedor, $documento, $tdocu, $type_ple);

							if(count($ple) > 0){
								if($type_ple == 'RC')
									$this->LibrosElectronicosRC($ple, $v, $fecha);
								else if ($type_ple == 'RCD')//Registro de Compras No Domiciliado
									$this->LibrosElectronicosRCD($ple, $v, $fecha);
								else
									$this->LibrosElectronicosRCS($ple, $v, $fecha);
							}else{
								$result     	= RegistroComprasTemplate::formSearch($fecha, $fecha2, $ple['paginacion'], $almacen, $proveedor, $documento, $tdocu, $tmoneda, $type_ple);
								$this->visor->addComponent("ContentB", "content_body", $result);
								$result_f = "<center><blink style='color: red'><<<  No hay registros para PLE desde $fecha al $fecha2 >>></blink></center>";
								$this->visor->addComponent("ContentF", "content_footer", $result_f);
							}

					break;

					case "Almacen":

						//VARIABLES DE BUSQUEDA

						$codalmacen	= $_REQUEST['almacen'];
						$fecha		= $_REQUEST['fecha'];
						$fecha2		= $_REQUEST['fecha2'];
						$tdocu		= $_REQUEST['tdocu'];
						$pro		= trim($_REQUEST['pro']);
						$doc		= trim($_REQUEST['doc']);
						$tmoneda	= $_REQUEST['tmoneda'];
						$rxp		= $_REQUEST['rxp'];
						$pagina		= $_REQUEST['pagina'];

						$busqueda    	= RegistroComprasModel::ComprasDevolucion(trim($_REQUEST['proveedor']));

						if($busqueda == false){
							?><script>alert("<?php echo 'No hay ninguna compra del Codigo Proveedor: '.$_REQUEST['proveedor'];?> ");</script><?php
						}else{
							$result_f 	= RegistroComprasTemplate::resultadosComprasDevolucion($busqueda, $_REQUEST['estacion'], $_REQUEST['femision'], $_REQUEST['proveedor'], $_REQUEST['rubro'], $_REQUEST['tipo'], $_REQUEST['serie'], $_REQUEST['documento'], $_REQUEST['dvec'], $_REQUEST['fvencimiento'], $_REQUEST['tc'], $_REQUEST['moneda'], $_REQUEST['fecha'], $_REQUEST['fecha2'], $rxp, $pagina, $codalmacen, $pro, $doc, $tdocu, $tmoneda);

						}

						$this->visor->addComponent("ContentF", "content_footer", $result_f);

					break;

					case "Guardar": //MERCADERIA
		
						//VARIABLES DE BUSQUEDA
						$codalmacen	= $_REQUEST['codalmacen'];
						$fecha		= $_REQUEST['fecha'];
						$fecha2		= $_REQUEST['fecha2'];
						$tdocu		= $_REQUEST['tdocu'];
						$pro		= trim($_REQUEST['pro']);
						$doc		= trim($_REQUEST['doc']);
						$tmoneda	= $_REQUEST['tmoneda'];
						$rxp		= $_REQUEST['rxp'];
						$pagina		= $_REQUEST['pagina'];

						$validar_consolidacion = RegistroComprasModel::verifyConsolidacion($_REQUEST['estacion'],$_REQUEST['femision']);
						if($validar_consolidacion){
							// GUARDO VALORES E INICIO TRANSACCION
							$begin = RegistroComprasModel::BEGINTransaccion();

							$setcorrelativo = RegistroComprasModel::ActDayCorrelativo($_REQUEST['fperiodo']);

							//$setcorrelativo[0] es para validar TRUE O FALSE
							//$setcorrelativo[1] es el valor de la tupla act_day campo numerator

							$insertcab 	= RegistroComprasModel::AgregarComprasCabecera($_REQUEST['estacion'],$_REQUEST['femision'],$_REQUEST['proveedor'],$_REQUEST['rubro'],$_REQUEST['tipo'],$_REQUEST['serie'],$_REQUEST['documento'],$_REQUEST['dvec'],$_REQUEST['fvencimiento'],$_REQUEST['tc'],$_REQUEST['moneda'],$_REQUEST['base'],$_REQUEST['impuesto'],$_REQUEST['total'],$_REQUEST['perce'],$_REQUEST['tiporef'],$_REQUEST['serieref'],$_REQUEST['documentoref'],$_REQUEST['rubros'],$_REQUEST['inafecto'], $_REQUEST['fperiodo'], $setcorrelativo[1], $_REQUEST['txt_glosa']);
							$insertdet 	= RegistroComprasModel::AgregarComprasDetalle($_REQUEST['estacion'],$_REQUEST['femision'],$_REQUEST['proveedor'],$_REQUEST['rubro'],$_REQUEST['tipo'],$_REQUEST['serie'],$_REQUEST['documento'],$_REQUEST['dvec'],$_REQUEST['fvencimiento'],$_REQUEST['tc'],$_REQUEST['moneda'],$_REQUEST['base'],$_REQUEST['impuesto'],$_REQUEST['total'],$_REQUEST['perce'],$_REQUEST['tiporef'],$_REQUEST['serieref'],$_REQUEST['documentoref']);
							$update 	= RegistroComprasModel::ActualizarCompra($_REQUEST['proveedor'],$_REQUEST['tipo'],$_REQUEST['serie'],$_REQUEST['documento'],$ip,$_REQUEST['id']);

							$tipo 		= $_REQUEST['tipo'];
							$serie 		= $_REQUEST['serie'];
							$documento 	= $_REQUEST['documento'];
							$proveedor 	= $_REQUEST['proveedor'];

							if($insertcab == 'ingreso' && ($insertdet) && ($update) && ($setcorrelativo[0])){
								$commit		= RegistroComprasModel::COMMITransaccion();
								?><script>alert("<?php echo 'Datos Guardados Correctamente';?> ");</script><?php

								$busqueda    	= RegistroComprasModel::Paginacion($rxp, $pagina, $fecha, $fecha2, $codalmacen, $pro, $doc, $tdocu, $tmoneda);
								$result     	= RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion'], $codalmacen, $pro, $doc, $tdocu, $tmoneda);
								$this->visor->addComponent("ContentB", "content_body", $result);

								if($busqueda == ''){
									$result_f = "<center><blink style='color: red'><<< No hay datos desde $fecha al $fecha2 >>></blink></center>";
									$this->visor->addComponent("ContentF", "content_footer", $result_f);
								}else{
									$result_f 	= RegistroComprasTemplate::resultadosBusqueda($busqueda['datos'], $fecha, $fecha2, $rxp, $pagina, $codalmacen, $pro, $doc, $tdocu, $tmoneda);
									$this->visor->addComponent("ContentF", "content_footer", $result_f);
								}
							}else{
								$rollback = RegistroComprasModel::ROLLBACKTransaccion();
								$result_f = "<center><blink style='color: red'><<< Error al guardar Registro Compra >>></blink></center>";
								$this->visor->addComponent("ContentF", "content_footer", $result_f);
							}
						} else {
							$result_f = "<center><blink style='color: orange'><<< Mensaje: No se puede modificar, d&iacute;a consolidado: " . $femision . ">>></blink></center>";
							$this->visor->addComponent("ContentF", "content_footer", $result_f);
						}

					break;

					case "GuardarOtros": //SERVICIOS

						//VARIABLES DE BUSQUEDA
						$codalmacen	= $_REQUEST['codalmacen'];
						$fecha		= $_REQUEST['fecha'];
						$fecha2		= $_REQUEST['fecha2'];
						$tdocu		= $_REQUEST['tdocu'];
						$pro		= trim($_REQUEST['pro']);
						$doc		= trim($_REQUEST['doc']);
						$tmoneda	= $_REQUEST['tmoneda'];
						$rxp		= $_REQUEST['rxp'];
						$pagina		= $_REQUEST['pagina'];

						$validar_consolidacion = RegistroComprasModel::verifyConsolidacion($_REQUEST['estacion'],$_REQUEST['femision']);
						if($validar_consolidacion){

							// GUARDO VALORES E INICIO TRANSACCION
							$begin 		= RegistroComprasModel::BEGINTransaccion();
							$setcorrelativo = RegistroComprasModel::ActDayCorrelativo($_REQUEST['fperiodo']);

							//$setcorrelativo[0] es para validar TRUE O FALSE
							//$setcorrelativo[1] es el valor de la tupla act_day campo numerator

							$insertcab 	= RegistroComprasModel::AgregarComprasCabecera($_REQUEST['estacion'],$_REQUEST['femision'],$_REQUEST['proveedor'],$_REQUEST['rubro'],$_REQUEST['tipo'],$_REQUEST['serie'],$_REQUEST['documento'],$_REQUEST['dvec'],$_REQUEST['fvencimiento'],$_REQUEST['tc'],$_REQUEST['moneda'],$_REQUEST['base'],$_REQUEST['impuesto'],$_REQUEST['total'],$_REQUEST['perce'],$_REQUEST['tiporef'],$_REQUEST['serieref'],$_REQUEST['documentoref'],$_REQUEST['rubros'],$_REQUEST['inafecto'],$_REQUEST['fperiodo'], $setcorrelativo[1], $_REQUEST['txt_glosa']);
							$insertdet 	= RegistroComprasModel::AgregarComprasDetalle($_REQUEST['estacion'],$_REQUEST['femision'],$_REQUEST['proveedor'],$_REQUEST['rubro'],$_REQUEST['tipo'],$_REQUEST['serie'],$_REQUEST['documento'],$_REQUEST['dvec'],$_REQUEST['fvencimiento'],$_REQUEST['tc'],$_REQUEST['moneda'],$_REQUEST['base'],$_REQUEST['impuesto'],$_REQUEST['total'],$_REQUEST['perce'],$_REQUEST['tiporef'],$_REQUEST['serieref'],$_REQUEST['documentoref']);

							if($insertcab == 'ingreso' && ($insertdet) && ($setcorrelativo[0])){

								$commit = RegistroComprasModel::COMMITransaccion();

								?><script>alert("<?php echo 'Datos Guardados Correctamente';?> ");</script><?php

								$busqueda    	= RegistroComprasModel::Paginacion($rxp, $pagina, $fecha, $fecha2, $codalmacen, $pro, $doc, $tdocu, $tmoneda);
								$result     	= RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion'], $codalmacen, $pro, $doc, $tdocu, $tmoneda);
								$this->visor->addComponent("ContentB", "content_body", $result);

								if($busqueda == ''){
									$result_f = "<center><blink style='color: red'><<< No hay datos desde $fecha al $fecha2 >>></blink></center>";
									$this->visor->addComponent("ContentF", "content_footer", $result_f);
								}else{
									$result_f 	= RegistroComprasTemplate::resultadosBusqueda($busqueda['datos'], $fecha, $fecha2, $rxp, $pagina, $codalmacen, $pro, $doc, $tdocu, $tmoneda);
									$this->visor->addComponent("ContentF", "content_footer", $result_f);
								}
							}else{
								$rollback = RegistroComprasModel::ROLLBACKTransaccion();
								$result_f = "<center><blink style='color: red'><<< Error al guardar Registro Compra >>></blink></center>";
								$this->visor->addComponent("ContentF", "content_footer", $result_f);
							}
						} else {
							$result_f = "<center><blink style='color: orange'><<< Mensaje: No se puede modificar, d&iacute;a consolidado: " . $femision . ">>></blink></center>";
							$this->visor->addComponent("ContentF", "content_footer", $result_f);
						}

					break;

					case "Eliminar":
						$arrDataGET = 
						array(
							'iAlmacen' => strip_tags(stripslashes($_GET["nu_almacen"])),
							'sDocumento' => strip_tags(stripslashes($_GET["documento"])),
						);

						$arrResponseVerifyPayments = $objComprasModel->verify_payments($arrDataGET);
						if ($arrResponseVerifyPayments["sStatus"] != "success") {
							$result_f = "<center><blink style='color: " . $arrResponseVerifyPayments["sColor"] . "'><<< " . $arrResponseVerifyPayments["sMessage"] . ">>></blink></center>";
							$this->visor->addComponent("ContentF", "content_footer", $result_f);
						} else {//El documento no presenta pagos de EGRESOS
							//VARIABLES DE BUSQUEDA

							$almacen	= $_REQUEST['estacion'];
							$nu_almacen	= $_REQUEST['nu_almacen'];
							$femision	= $_REQUEST['femision'];
							$fecha		= $_REQUEST['fecha'];
							$fecha2		= $_REQUEST['fecha2'];
							$tdocu		= $_REQUEST['tdocu'];
							$pro		= trim($_REQUEST['pro']);
							$doc		= trim($_REQUEST['doc']);
							$tmoneda	= $_REQUEST['tmoneda'];
							$rxp		= $_REQUEST['rxp'];
							$pagina		= $_REQUEST['pagina'];

							$validar_consolidacion = RegistroComprasModel::verifyConsolidacion($nu_almacen, $femision);
							if($validar_consolidacion){
								$begin 		= RegistroComprasModel::BEGINTransaccion();

								$updatedev 	= RegistroComprasModel::ActualizarRegistroComprasDev($_REQUEST['documento']);
								$deletedet 	= RegistroComprasModel::EliminarRegistroComprasDet($_REQUEST['documento']);
								$deletecab 	= RegistroComprasModel::EliminarRegistroComprasCab($_REQUEST['documento']);

								$savecorrelativo = RegistroComprasModel::ActCorrelativoPre($_REQUEST['fperiodo'], $_REQUEST['correlativo']);

								if(($deletedet) && ($deletecab) && ($updatedev) && ($savecorrelativo)){

									$commit = RegistroComprasModel::COMMITransaccion();

									$busqueda   = RegistroComprasModel::Paginacion($rxp, $pagina, $fecha, $fecha2, $almacen, $pro, $doc, $tdocu, $tmoneda);
									$result     = RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion'], $almacen, $pro, $doc, $tdocu, $tmoneda);
									$result_f 	= RegistroComprasTemplate::resultadosBusqueda($busqueda['datos'], $fecha, $fecha2, $rxp, $pagina, $almacen, $pro, $doc, $tdocu, $tmoneda);

									$this->visor->addComponent("ContentB", "content_body", $result);
									$this->visor->addComponent("ContentF", "content_footer", $result_f);

								}else{
									$rollback = RegistroComprasModel::ROLLBACKTransaccion();
									$result_f = "<center><blink style='color: red'><<< Error: No se puede eliminar compra >>></blink></center>";

									if(!($savecorrelativo)){
										$fperiodoyear 	= substr($_REQUEST['fperiodo'],6,4);
										$fperiodomonth	= substr($_REQUEST['fperiodo'],3,2);
										$result_f 	.= "<br /><center><blink style='color: red'><<< Mensaje: Tienes un numero correlativo pendiente a usar en la fecha del periodo del a&ntildeo: " . $fperiodoyear . " y mes: " . $fperiodomonth . ">>></blink></center>";
										$result_f 	.= "<br /><center><blink style='color: red'><<< Solucion: Debes de agregar una compra en el a&ntildeo y mes que se le indica, para que el sistema le pueda asignar el numero correlativo pendiente. Luego podra eliminar la compra, sin problemas.>>></blink></center>";
									}
									$this->visor->addComponent("ContentF", "content_footer", $result_f);
								}
							}else{
								$result_f = "<center><blink style='color: orange'><<< Mensaje: No se puede eliminar, d&iacute;a consolidado: " . $femision . ">>></blink></center>";
								$this->visor->addComponent("ContentF", "content_footer", $result_f);
							}
						}

					    break;

					case "Update":
						$arrDataGET = 
						array(
							'iAlmacen' => strip_tags(stripslashes($_GET["nu_almacen"])),
							'sDocumento' => strip_tags(stripslashes($_GET["documento"])),
						);

						$arrResponseVerifyPayments = $objComprasModel->verify_payments($arrDataGET);
						if ($arrResponseVerifyPayments["sStatus"] != "success") {
							$result_f = "<center><blink style='color: " . $arrResponseVerifyPayments["sColor"] . "'><<< " . $arrResponseVerifyPayments["sMessage"] . ">>></blink></center>";
							$this->visor->addComponent("ContentF", "content_footer", $result_f);
						} else {//El documento no presenta pagos de EGRESOS
							$doctype	= $_REQUEST['doctype'];
							//VARIABLES DE BUSQUEDA
							$almacen	= $_REQUEST['estacion'];
							$nu_almacen	= $_REQUEST['nu_almacen'];
							$femision	= $_REQUEST['femision'];
							$fecha		= $_REQUEST['fecha'];
							$fecha2		= $_REQUEST['fecha2'];
							$tdocu		= $_REQUEST['tdocu'];
							$pro		= trim($_REQUEST['pro']);
							$doc		= trim($_REQUEST['doc']);
							$tmoneda	= $_REQUEST['tmoneda'];
							$rxp		= $_REQUEST['rxp'];
							$pagina		= $_REQUEST['pagina'];

							$validar_consolidacion = RegistroComprasModel::verifyConsolidacion($nu_almacen, $femision);

							if($validar_consolidacion){
								$recuperar	= RegistroComprasModel::recuperarRegistroArray($_REQUEST['documento']);
								$result	= RegistroComprasTemplate::FormUpdate($_REQUEST['documento'], $fecha, $fecha2, $rxp, $pagina, $recuperar, $almacen, $pro, $doc, $tdocu, $doctype, $tmoneda);
								$this->visor->addComponent("ContentB", "content_body", $result);
								$this->visor->addComponent("ContentF", "content_footer", "");
							}else{
								$result_f = "<center><blink style='color: orange'><<< Mensaje: No se puede modificar, d&iacute;a consolidado: " . $femision . ">>></blink></center>";
								$this->visor->addComponent("ContentF", "content_footer", $result_f);
							}
						}

					    break;

					case "Actualizar":

						//VARIABLES DE BUSQUEDA

						$almacen	= $_REQUEST['almacen'];
						$fecha		= $_REQUEST['fecha'];
						$fecha2		= $_REQUEST['fecha2'];
						$tdocu		= $_REQUEST['tdocu'];
						$pro		= trim($_REQUEST['pro']);
						$doc		= trim($_REQUEST['doc']);
						$tmoneda	= $_REQUEST['tmoneda'];
						$rxp		= $_REQUEST['rxp'];
						$pagina		= $_REQUEST['pagina'];

						$modificar	= RegistroComprasModel::ModificarRegistroCompras($_REQUEST['fregistro'],$_REQUEST['documento'],$_REQUEST['moneda'],$_REQUEST['imponible'],$_REQUEST['impuesto'],$_REQUEST['total'],$_REQUEST['perce'],$_REQUEST['inafecto'],$_REQUEST['tiporef'],$_REQUEST['serieref'],$_REQUEST['docuref'],$_REQUEST['freferencia'],$_REQUEST['fperiodo']);
						$modificardet	= RegistroComprasModel::ModificarRegistroComprasDet($_REQUEST['documento'],$_REQUEST['moneda']);

						if($modificar && $modificardet){

							$busqueda    	= RegistroComprasModel::Paginacion($rxp, $pagina, $fecha, $fecha2, $almacen, $pro, $doc, $tdocu, $tmoneda);
							$result     	= RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion'], $almacen, $pro, $doc, $tdocu, $tmoneda);
							$result_f 	= RegistroComprasTemplate::resultadosBusqueda($busqueda['datos'], $fecha, $fecha2, $rxp, $pagina, $almacen, $pro, $doc, $tdocu, $tmoneda);
							$this->visor->addComponent("ContentB", "content_body", $result);
							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}else{
							$result_f = "<center><blink style='color: red'><<< Error al Modificar Fecha Registro Compras >>></blink></center>";
							$this->visor->addComponent("ContentF", "content_footer", $result_f);
						}
					
					break;

					case "VerTotales":

							$rxp		= $_REQUEST['rxp'];
							$pagina		= $_REQUEST['pagina'];
							$almacen	= $_REQUEST['almacen'];
							$pro		= trim($_REQUEST['pro']);
							$doc		= trim($_REQUEST['doc']);
							$tdocu		= $_REQUEST['tdocu'];
							$tmoneda	= $_REQUEST['tmoneda'];

							$TotalMontos 	= RegistroComprasTemplate::verTotalesOtros($_REQUEST['base'], $_REQUEST['fecha'], $_REQUEST['fecha2'], $_REQUEST['rubro'], $rxp, $pagina, $almacen, $pro, $doc, $tdocu, $tmoneda);

							$this->visor->addComponent("ContentF", "content_footer", $TotalMontos);

					break;

					default:


						$rxp		= $_REQUEST['rxp'];
						$pagina		= $_REQUEST['pagina'];

						if(empty($_REQUEST['estacion'])){//ESTA CONDICION ES POR EL BOTON DE EXCEL, PORQUE NO ENTRA A UN CASE Y EJECUTA EL DEFAULT. LUEGO SE VA AL JAVASCRIPT

							$busqueda    	= RegistroComprasModel::Paginacion($rxp, $pagina, $hoy, $hoy, '', '', '', 'TODOS', 'TODOS');
							$result     	= RegistroComprasTemplate::formSearch($hoy, $hoy, $busqueda['paginacion'], '', '', '', 'TODOS', 'TODOS', '');

						}else{

							$fecha		= $_REQUEST['fecha'];
							$fecha2		= $_REQUEST['fecha2'];
							$almacen	= $_REQUEST['estacion'];
							$proveedor	= trim($_REQUEST['pro']);
							$doc		= trim($_REQUEST['doc']);
							$tdocu		= $_REQUEST['tdocu'];
							$tmoneda	= $_REQUEST['tmoneda'];

							$busqueda    	= RegistroComprasModel::Paginacion($rxp, $pagina, $fecha, $fecha2, $almacen, $proveedor, $doc, $tdocu, $tmoneda);
							$result     	= RegistroComprasTemplate::formSearch($fecha, $fecha2, $busqueda['paginacion'], $almacen, $proveedor, $documento, $tdocu, $tmoneda);

						}

						$this->visor->addComponent("ContentB", "content_body", $result);

						if($busqueda == ''){

							$result_f	= "<center><blink style='color: red'><<< No hay datos desde $hoy al $hoy >>></blink></center>";

							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}else{

							if(empty($almacen))
								$result_f 	= RegistroComprasTemplate::resultadosBusqueda($busqueda['datos'], $hoy, $hoy, $rxp, $pagina, '', '', '', 'TODOS', 'TODOS');
							else
								$result_f 	= RegistroComprasTemplate::resultadosBusqueda($busqueda['datos'], $fecha, $fecha2, $rxp, $pagina, $estacion, $proveedorb, $doc, $tdocu, $tmoneda);

							$this->visor->addComponent("ContentF", "content_footer", $result_f);

						}

					break;

				}

			break;

			case "APLICACIONESDET":

					//VARIABLES DE BUSQUEDA

					$almacen	= $_REQUEST['almacen'];
					$fecha		= $_REQUEST['fecha'];
					$fecha2		= $_REQUEST['fecha2'];
					$tdocu		= $_REQUEST['tdocu'];
					$pro		= trim($_REQUEST['pro']);
					$doc		= trim($_REQUEST['doc']);
					$tmoneda	= $_REQUEST['tmoneda'];
					$rxp		= $_REQUEST['rxp'];
					$pagina		= $_REQUEST['pagina'];

					// FIN //

					$TotalMontos = RegistroComprasTemplate::verTotales($_REQUEST['base'], $fecha, $fecha2, $_REQUEST['rubro'], $_REQUEST['id'], $rxp, $pagina, $almacen, $pro, $doc, $tdocu, $tmoneda);
					$this->visor->addComponent("Totales","Totales",$TotalMontos);

	      				break;

			default:
	       				$this->visor->addComponent('ContentB', 'content_body', '<h2>TAREA "'.$this->request.'" NO CONOCIDA EN REGISTROS</h2>');
			break;


		}

	}

	function LibrosElectronicosRC($resultado, $v, $fecha) {//RC = "Registro Compras"
		ob_clean();

		$ruc	= $v[1];
		$mes	= substr($fecha,3,2);
		$anio	= substr($fecha,6,4);

		for ($i = 0; $i < count($resultado); $i++) {
			$a = $resultado[$i];

			$anioemi	= substr($a['femision'],6,4);
			$mesemi		= substr($a['femision'],3,2);

			$result .= $anio . "" . $mes . "00" . "|";//1
			$result .= $a['idcompra'] . "|";//2
			$result .= "M".$a['id'] . "|";//3 
			$result .= $a['femision'] . "|";//4
			$result .= $a['fvencimiento'] . "|";//5
			$result .= $a['tipo'] . "|";//6
			$result .= trim($a['serie']) . "|";//7
			$result .= "0|";//8
			$result .= trim($a['numero']) . "|";//9
			$result .= "|";//10
			$result .= trim($a['identidad']). "|";//11

			if($a['tipo'] == '01' || $a['tipo'] == '07' || $a['tipo'] == '08'){
				$result .= trim($a['ruc']) . "|";//12
				$result .= trim($a['razonsocial']) . "|";//13
			}else{
				$result .= "|";//12
				$result .= "|";//13
			}

			$result .= trim($a['imponible']) . "|";//14
			$result .= trim($a['impuesto']) . "|";//15
			$result .= "0.00|";//16
			$result .= "0.00|";//17
			$result .= "0.00|";//18
			$result .= "0.00|";//19
			$result .= trim($a['inafecto']) . "|";//20
			$result .= "0.00|";//21
			$result .= "0.00|";//22 (ICBPER: AGREGADO 2020-01-11)
			$result .= "0.00|";//23
			$result .= trim($a['total'] + $a['inafecto']) . "|";//24 Suma de los campos 14 al 22
			$result .= trim($a['moneda']) . "|";//25
			$result .= $a['tc'] . "|";//26

			if($a['tipo'] == '07' || $a['tipo'] == '08'){
				$result .= $a['fecharef'] . "|";//27
				$result .= $a['tiporef'] . "|";//28
				$result .= $a['serieref'] . "|";//29
				$result .= "|";//30
				$result .= $a['docuref'] . "|";//31
			}else{
				$result .= "01/01/0001|";//27
				$result .= "|";//28
				$result .= "|";//29
				$result .= "|";//30
				$result .= "|";//31
			}

			$result .= $a['dfecha'] . "|";//32
			$result .= $a['dnumero'] . "|";//33
			$result .= "|";//34
			$result .= $a['codigo_bienes_servicios'] . "|";//35 (CODIGO DE BIENES Y SERVICIOS)
			$result .= "|";//36
			$result .= "|";//37
			$result .= "|";//38
			$result .= "|";//39
			$result .= "|";//40
			$result .= "|";//41

			/* VALIDACIONES DEL PLE FECHA DE EMISION */

			$newanio = ($anioemi + 1);//PARA VALIDAR EL DOCUMENTO QUE SE DECLARE DESPUES DE LOS SIGUIENTES 12 MESES

			if($anio.$mes == $anioemi.$mesemi && abs($a['inafecto']) > 0 && abs($a['impuesto']) == 0)//documento no da derecho al crédito fiscal
				$result .= "0|";//42
			elseif($anio.$mes == $anioemi.$mesemi && abs($a['impuesto']) > 0)//documento da derecho al crédito fiscal
				$result .= "1|";//42
			elseif($newanio.$mesemi >= $anio.$mes && abs($a['impuesto']) > 0)//DENTRO LOS 12 MESES SIGUIENTES DE LA FECHA DE EMISION DEL DOCUMENTO IGV
				$result .= "6|";//42
			elseif($newanio.$mesemi >= $anio && abs($a['inafecto']) > 0 && abs($a['impuesto']) == 0)//DENTRO LOS 12 MESES SIGUIENTES DE LA FECHA DE EMISION DEL DOCUMENTO SIN IGV
				$result .= "7|";//42
			else
				$result .= "ERROR|";//42

			/* -------------------------------------- */

			$result .= "\n";

		}

		$estado_info = 0;

		if($resultado)
			$estado_info = 1;

		$nombre_archivo = "LE" . $ruc . "" . $anio . "" . $mes . "00080100001" . $estado_info . "11.txt";

		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

		$result = trim($result);

		die($result);

	}

	function LibrosElectronicosRCD($resultado, $v, $fecha) {//RC = "Registro Compras No Domiciliado"
		ob_clean();

	    $ruc		= $v[1];
		$mes		= substr($fecha,3,2);
		$anio		= substr($fecha,6,4);

		for ($i = 0; $i < count($resultado); $i++) {
			$a = $resultado[$i];

			$anioemi	= substr($a['femision'],6,4);
			$mesemi		= substr($a['femision'],3,2);

			$result .= $anio . "" . $mes . "00" . "|";//1
			$result .= $a['idcompra'] . "|";//2
			$result .= "M".$a['id'] . "|";//3 
			$result .= $a['femision'] . "|";//4
			$result .= $a['fvencimiento'] . "|";//5
			$result .= $a['tipo'] . "|";//6
			$result .= trim($a['serie']) . "|";//7
			$result .= "0|";//8
			$result .= trim($a['numero']) . "|";//9
			$result .= "|";//10
			$result .= trim($a['identidad']). "|";//11

			if($a['tipo'] == '01' || $a['tipo'] == '07' || $a['tipo'] == '08'){
				$result .= trim($a['ruc']) . "|";//12
				$result .= trim($a['razonsocial']) . "|";//13
			}else{
				$result .= "|";//12
				$result .= "|";//13
			}

			$result .= trim($a['imponible']) . "|";//14
			$result .= trim($a['impuesto']) . "|";//15
			$result .= "0.00|";//16
			$result .= "0.00|";//17
			$result .= "0.00|";//18
			$result .= "0.00|";//19
			$result .= trim($a['inafecto']) . "|";//20
			$result .= "0.00|";//21
			$result .= "0.00|";//22 (ICBPER: AGREGADO 2020-01-11)
			$result .= "0.00|";//23
			$result .= trim($a['total'] + $a['inafecto']) . "|";//24 Suma de los campos 14 al 22
			$result .= trim($a['moneda']) . "|";//25
			$result .= $a['tc'] . "|";//26

			if($a['tipo'] == '07' || $a['tipo'] == '08'){
				$result .= $a['fecharef'] . "|";//27
				$result .= $a['tiporef'] . "|";//28
				$result .= $a['serieref'] . "|";//29
				$result .= "|";//30
				$result .= $a['docuref'] . "|";//31
			}else{
				$result .= "01/01/0001|";//27
				$result .= "|";//28
				$result .= "|";//29
				$result .= "|";//30
				$result .= "|";//31
			}

			$result .= $a['dfecha'] . "|";//32
			$result .= $a['dnumero'] . "|";//33
			$result .= "|";//34
			$result .= $a['codigo_bienes_servicios'] . "|";//35
			$result .= "|";//36
			$result .= "|";//37
			$result .= "|";//38
			$result .= "|";//39
			$result .= "|";//40
			$result .= "|";//41

			/* VALIDACIONES DEL PLE FECHA DE EMISION */

			$newanio = ($anioemi + 1);//PARA VALIDAR EL DOCUMENTO QUE SE DECLARE DESPUES DE LOS SIGUIENTES 12 MESES

			if($anio.$mes == $anioemi.$mesemi && abs($a['inafecto']) > 0 && abs($a['impuesto']) == 0)//documento no da derecho al crédito fiscal
				$result .= "0|";//42
			elseif($anio.$mes == $anioemi.$mesemi && abs($a['impuesto']) > 0)//documento da derecho al crédito fiscal
				$result .= "1|";//42
			elseif($newanio.$mesemi >= $anio.$mes && abs($a['impuesto']) > 0)//DENTRO LOS 12 MESES SIGUIENTES DE LA FECHA DE EMISION DEL DOCUMENTO IGV
				$result .= "6|";//42
			elseif($newanio.$mesemi >= $anio && abs($a['inafecto']) > 0 && abs($a['impuesto']) == 0)//DENTRO LOS 12 MESES SIGUIENTES DE LA FECHA DE EMISION DEL DOCUMENTO SIN IGV
				$result .= "7|";//42
			else
				$result .= "ERROR|";//42

			/* -------------------------------------- */

			$result .= "\n";

		}

		$estado_info = 0;

		if($resultado)
			$estado_info = 1;

		$nombre_archivo = "LE" . $ruc . "" . $anio . "" . $mes . "00080200001" . $estado_info . "11.txt";

		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

		$result = trim($result);

		die($result);

	}

	function LibrosElectronicosRCS($resultado, $v, $fecha) {//RCS = "Registro Compras Simplificado"
		ob_clean();

		$ruc	= $v[1];
		$mes	= substr($fecha,3,2);
		$anio	= substr($fecha,6,4);

		for ($i = 0; $i < count($resultado); $i++) {
			$a = $resultado[$i];

			$anioemi	= substr($a['femision'], 6, 4);
			$mesemi		= substr($a['femision'], 3, 2);

			$result .= $anio . "" . $mes . "00" . "|";//1
			$result .= $a['idcompra'] . "|";//2
			$result .= "M".$a['id'] . "|";//3 
			$result .= $a['femision'] . "|";//4
			$result .= $a['fvencimiento'] . "|";//5
			$result .= trim($a['tipo']) . "|";//6
			$result .= trim($a['serie']) . "|";//7
			$result .= trim($a['numero']) . "|";//8
			$result .= "|";//9
			$result .= trim($a['identidad']). "|";//10

			if($a['tipo'] == '01'){
				$result .= trim($a['ruc']) . "|";//11
				$result .= trim($a['razonsocial']) . "|";//12
			}else{
				$result .= "|";//11
				$result .= "|";//12
			}

			$result .= trim($a['imponible']) . "|";//13
			$result .= trim($a['impuesto']) . "|";//14
			$result .= "0.00|";//15 (ICBPER: AGREGADO 2020-01-11)
			$result .= trim($a['inafecto']) . "|";//16
			$result .= trim($a['total'] + $a['inafecto']) . "|";//17 SUMA DE LOS CAMPOS 13,14 Y 15
			$result .= trim($a['moneda']) . "|";//18
			$result .=  $a['tc'] . "|";//19
			$result .= "01/01/0001|";//20
			$result .= "|";//21
			$result .= "|";//22
			$result .= "|";//23
			$result .= $a['dnumero'] . "|";//24
			$result .= $a['dfecha'] . "|";//25
			$result .= "|";//26
			$result .= $a['codigo_bienes_servicios'] . "|";//27
			$result .= "|";//28
			$result .= "|";//29
			$result .= "|";//30
			$result .= "|";//31

			/* VALIDACIONES DEL PLE FECHA DE EMISION */

			$newanio = ($anioemi + 1);//PARA VALIDAR EL DOCUMENTO QUE SE DECLARE DESPUES DE LOS SIGUIENTES 12 MESES

			if($anio.$mes == $anioemi.$mesemi && abs($a['inafecto']) > 0 && abs($a['impuesto']) == 0)//documento no da derecho al crédito fiscal
				$result .= "0|";//32
			elseif($anio.$mes == $anioemi.$mesemi && abs($a['impuesto']) > 0)//documento da derecho al crédito fiscal
				$result .= "1|";//32
			elseif($newanio.$mesemi >= $anio.$mes && abs($a['impuesto']) > 0)//DENTRO LOS 12 MESES SIGUIENTES DE LA FECHA DE EMISION DEL DOCUMENTO IGV
				$result .= "6|";//32
			elseif($newanio.$mesemi >= $anio && abs($a['inafecto']) > 0 && abs($a['impuesto']) == 0)//DENTRO LOS 12 MESES SIGUIENTES DE LA FECHA DE EMISION DEL DOCUMENTO SIN IGV
				$result .= "7|";//32
			else
				$result .= "ERROR|";//32

			/* -------------------------------------- */

			$result .= "\n";

		}

		$estado_info = 0;

		if($resultado)
			$estado_info = 1;

		$nombre_archivo = "LE" . $ruc . "" . $anio . "" . $mes . "00080300001" . $estado_info . "11.txt";

		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

		$result = trim($result);

		die($result);

	}

}
