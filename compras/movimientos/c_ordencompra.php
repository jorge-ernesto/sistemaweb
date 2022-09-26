<?php
//require("PHPMailer/class.phpmailer.php");
//include("lib/class.phpmailer.php");
//include("lib/class.smtp.php");

class OrdenCompraController extends Controller {
	function Init() {
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action=$_REQUEST['action']:$this->action='';
	}

	function Run() {
		include 'movimientos/m_ordencompra.php';
		include 'movimientos/t_ordencompra.php';

		$this->Init();

		/* Get Class Template y Model */
		$objOrdenModel = new OrdenCompraModel();
		$objOrdenTemplate = new OrdenCompraTemplate();

		$result = "";
		$result_f = "";
		$form_search = false;
		$listado = false;
		$editar = false;
		$actualizar = false;

		$dUltimoCierre = OrdenCompraModel::getFechaSistemaPA();

		switch($this->action) {
			case "Consultar":
				$percepcion2=trim($_REQUEST['xpercepcion']);
				if ($percepcion2==""){$percepcion=$_REQUEST['percepcion'];}else{$percepcion=$_REQUEST['xpercepcion'];} 	
				$almacenes = OrdenCompraModel::obtenerAlmacenes();
				$almacenes['TODAS'] = "Todos los almacenes";
				$resultados = OrdenCompraModel::buscar($_REQUEST['cbo-iAlmacen'],$_REQUEST['txt-dInicial'],$_REQUEST['txt-dFinal'],$_REQUEST['estado_pendiente'],$_REQUEST['estado_inventario'],$_REQUEST['estado_procesando'],$_REQUEST['estado_facturado'],$_REQUEST['estado_cerrado'],$percepcion);
				$result_f = OrdenCompraTemplate::resultadosBusqueda($resultados, $_REQUEST['cbo-iAlmacen'], $_REQUEST['txt-dInicial'], $_REQUEST['txt-dFinal']);
				break;

			case "Agregar":
				$alma = $_SESSION['almacen'];
				$almacenes = OrdenCompraModel::obtenerAlmacenes();
				$monedas = OrdenCompraModel::obtenerMonedas();
				$estados = OrdenCompraModel::obtenerEstados();
				$fpago1 = OrdenCompraModel::obtenerFPago('96'); //05 - Contado o 96 - Crédito
				$fpago2 = OrdenCompraModel::obtenerFPago('05'); //05 - Contado o 96 - Crédito
				$numero_orden = OrdenCompraModel::obtenerOrdenCorrelativa($alma);
				$serie_doc = OrdenCompraModel::obtenerSerie();

				$result = OrdenCompraTemplate::formAgregar($numero_orden,$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2);
				$result_f = "&nbsp;";
				break;

				//$busqueda = PedidoComprasModel::buscar($_REQUEST['almacen'],$_REQUEST['desde'],$_REQUEST['hasta']);
				//$result_f = PedidoComprasTemplate::reporte($busqueda);
				//$this->visor->addComponent("ContentF", "content_footer", $result_f);
				//break;


			case "BuscarPedido":
				$alma = $_SESSION['almacen'];
				$almacenes = OrdenCompraModel::obtenerAlmacenes();
				$resultados = OrdenCompraModel::buscarPedido($_REQUEST['almacen'],$_REQUEST['fe_inicio'],$_REQUEST['fe_final']);
				//$resultados = OrdenCompraModel::buscarPedido("001",$_REQUEST['fe_inicio'],$_REQUEST['fe_final']);
				$result_f = OrdenCompraTemplate::reportePedidoCompra($resultados);
				$this->visor->addComponent("ContentF", "content_footer", $result_f);
				break;

			case "ListarPedido":
					$resultado = PedidoComprasModel::listar($_REQUEST['num_pedido']);
					if($resultado == 0){ 
					echo '<script name="accion">alert("No hay información.'.$resultado.'") </script>';
					break;
					} else {
					$result_f 	= PedidoComprasTemplate::formAgregarBody($resultado, "A", $_REQUEST['modo']);				
					$this->visor->addComponent("ContentF", "content_footer", $result_f);
					break;
				}

			case "Regresar":
				$almacenes = OrdenCompraModel::obtenerAlmacenes();
				$almacenes['TODAS'] = "Todos los almacenes";
				$result = OrdenCompraTemplate::formSearch($dUltimoCierre, $almacenes);
				$result_f = "&nbsp;";
				break;

			case "Insertar": //INSERTA
				$proveedor = trim($_REQUEST['proveedor']);
				$credito = $_REQUEST['m_credito'];
				$percepcion2=trim($_REQUEST['xpercepcion']);
				if ($percepcion2==""){$percepcion=$_REQUEST['percepcion'];}else{$percepcion=$_REQUEST['xpercepcion'];}
				if ( $credito == 'S')	{$fpago = $_REQUEST['fpago1'];
				} else 		{$fpago = $_REQUEST['fpago2'];}
				$tcambio = $_REQUEST['tcambio'];
				if ($tcambio   == '')	{$tcambio='null';}
				if(OrdenCompraModel::obtenerOrdenExistente($_REQUEST['numero']) == 0){ 
					
					if ($proveedor == '' || $_REQUEST['nombre'] == ''){
						$result_f = "<script>alert('Debe ingresar un proveedor valido');</script>";
					} else {						
						
						$val_per=OrdenCompraModel::ctrl_percepcion($_REQUEST['numero'],$_REQUEST['subtotal'],$percepcion,0);
						$res = OrdenCompraModel::agregar($_REQUEST['numero'],$_REQUEST['serie'],$_REQUEST['fecha'],$_REQUEST['almacen'],$proveedor,$_REQUEST['moneda'],$tcambio,$credito,$fpago,$_REQUEST['factura'],$_REQUEST['comentario'],$_REQUEST['fentrega'],$_REQUEST['glosa'],$val_per[0],$val_per[1]);
						if ($res==TRUE) {//Agrego correctamente ORDEN CABECERA -> com_cabecera
							//Agregar FLETE
							if ( trim($_POST['chflete']) == 'S' ){ //AGREGAMOS FLETE
								$fe_flete = trim($_POST['fe_flete']);
								$fe_flete = strip_tags($fe_flete);
								$fe_flete = explode("/", $fe_flete);
								$fe_flete = $fe_flete[2] . "-" . $fe_flete[1] . "-" . $fe_flete[0];

								$Nu_Orden = "01".trim($_POST['serie']).trim($_POST['numero']);

								$fe_orden = trim($_POST['fecha']);
								$fe_orden = strip_tags($fe_orden);
								$fe_orden = explode("/", $fe_orden);
								$fe_orden = $fe_orden[2] . "-" . $fe_orden[1] . "-" . $fe_orden[0];
								$objOrdenModel->addFlete('00', $Nu_Orden, $fe_orden, $fe_flete, $_POST['cbo-MotivoTraslado'], $_POST['no_placa'], $_POST['no_licencia'], $_POST['no_certificado_inscripcion'], $_POST['id_transportista_proveedor']);
							}

							if (trim($_REQUEST['codigo']) != "" && trim($_REQUEST['descripcion']) != ""){
								$cantidad =$_REQUEST['cantidad'];
								if ($cantidad == ''){$cantidad='null';}
								$precio =$_REQUEST['precio'];
								if ($precio == ''){$precio='null';}
								$descuento =$_REQUEST['descuento'];
								if ($descuento == ''){$descuento='null';}
								$subtotal =$_REQUEST['subtotal'];
								if ($subtotal == ''){$subtotal='null';}
								$rr = OrdenCompraModel::agregardetalle($_REQUEST['numero'],$proveedor,$_REQUEST['fecha'],$_REQUEST['codigo'],$cantidad,$precio,$descuento, $subtotal,$_REQUEST['serie']);
							}
							?>
							<script>
								alert('Se ha registrado correctamente');
							</script>
							<?php
							$almacen_BD 	= OrdenCompraModel::obtenerAlmacenBD($_REQUEST['numero'],$proveedor);
							$moneda_BD 	= OrdenCompraModel::obtenerMonedaBD($_REQUEST['numero'],$proveedor);
							$credito_BD 	= OrdenCompraModel::obtenerCreditoBD($_REQUEST['numero'],$proveedor);
							if($credito_BD == 'S'){
								$fpago1_BD 	= OrdenCompraModel::obtenerFPagoBD($_REQUEST['numero'],$proveedor);
							} else {
								$fpago2_BD 	= OrdenCompraModel::obtenerFPagoBD($_REQUEST['numero'],$proveedor);
							}
							$articulos = OrdenCompraModel::obtenerArticulos($_REQUEST['numero'],$percepcion);
							$serie_doc = OrdenCompraModel::obtenerSerie();
							$almacenes = OrdenCompraModel::obtenerAlmacenes();
							$estados = OrdenCompraModel::obtenerEstados();
							$monedas = OrdenCompraModel::obtenerMonedas();
							$fpago1 = OrdenCompraModel::obtenerFPago('96'); //05 - Contado o 96 - Crédito
							$fpago2 = OrdenCompraModel::obtenerFPago('05'); //05 - Contado o 96 - Crédito
							if ($tcambio == 'null'){$tcambio='';}
							$boton = 'Modificar Cabecera';
							$habilitado1 = true;
							$habilitado2 = array("readonly");
							$habilitado3 = array("disabled");
							$habilitado4 = 'disabled';


							if (isset($_REQUEST['type-form'])) {
								if ($_REQUEST['type-form'] == 'edit-order') {
									$result = OrdenCompraTemplate::formActualizarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'],$_REQUEST['comentario'],$credito_BD,$_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$almacen_BD,$moneda_BD,$fpago1_BD,$fpago2_BD, $percepcion, trim($_POST['chflete']), $fe_flete, $_POST['cbo-MotivoTraslado'], $_POST['no_placa'], $_POST['no_licencia'], $_POST['no_certificado_inscripcion'], $_POST['id_transportista_proveedor'], $_POST['no_transportista_proveedor']);
								}
							} else {
								$result = OrdenCompraTemplate::formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'],$_REQUEST['comentario'],$credito_BD,$_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$almacen_BD,$moneda_BD,$fpago1_BD,$fpago2_BD, $percepcion, trim($_POST['chflete']), $fe_flete, $_POST['cbo-MotivoTraslado'], $_POST['no_placa'], $_POST['no_licencia'], $_POST['no_certificado_inscripcion'], $_POST['id_transportista_proveedor'], $_POST['no_transportista_proveedor']);
							}
							$result_f = "&nbsp;";
						} else {
							$result_f = "<script>alert('No se pudo registrar. Intente nuevamente');</script>";
						}
					}
				} else {
					if ($_REQUEST['codigo'] != '' && $_REQUEST['descripcion'] != ''){
						$cantidad =$_REQUEST['cantidad'];
						if ($cantidad == ''){$cantidad='null';}
						$precio =$_REQUEST['precio'];
						if ($precio == ''){$precio='null';}
						$descuento =$_REQUEST['descuento'];
						if ($descuento == ''){$descuento='null';}
						$subtotal =$_REQUEST['subtotal'];
						if ($subtotal == ''){$subtotal='null';}
						
						$val_per=OrdenCompraModel::ctrl_percepcion($_REQUEST['numero'],$_REQUEST['subtotal'],$percepcion,0);						
						$rexa = OrdenCompraModel::actualizar2($_REQUEST['numero'],$_REQUEST['proveedor'],$val_per[0],$val_per[1]);
						
						$res = OrdenCompraModel::agregardetalle($_REQUEST['numero'],$_REQUEST['proveedor'],$_REQUEST['fecha'],$_REQUEST['codigo'],$cantidad,$precio,$descuento, $subtotal,$_REQUEST['serie']);
						?>
							<script>
								alert('Se ha registrado correctamente');
							</script>
							<?php
							$almacen_BD 	= OrdenCompraModel::obtenerAlmacenBD($_REQUEST['numero'],$proveedor);
							$moneda_BD 	= OrdenCompraModel::obtenerMonedaBD($_REQUEST['numero'],$proveedor);
							$credito_BD 	= OrdenCompraModel::obtenerCreditoBD($_REQUEST['numero'],$proveedor);
							if($credito_BD == 'S') {
								$fpago1_BD 	= OrdenCompraModel::obtenerFPagoBD($_REQUEST['numero'],$proveedor);
							} else {
								$fpago2_BD 	= OrdenCompraModel::obtenerFPagoBD($_REQUEST['numero'],$proveedor);
							}
							$articulos = OrdenCompraModel::obtenerArticulos($_REQUEST['numero'],$percepcion);
							$serie_doc = OrdenCompraModel::obtenerSerie();
							$almacenes = OrdenCompraModel::obtenerAlmacenes();
							$estados = OrdenCompraModel::obtenerEstados();
							$monedas = OrdenCompraModel::obtenerMonedas();
							$fpago1 = OrdenCompraModel::obtenerFPago('96'); //05 - Contado o 96 - Crédito
							$fpago2 = OrdenCompraModel::obtenerFPago('05'); //05 - Contado o 96 - Crédito
							$boton = 'Modificar Cabecera';
							$habilitado1 = true;
							$habilitado2 = array("readonly");
							$habilitado3 = array("disabled");
							$habilitado4 = 'disabled';
							$result = OrdenCompraTemplate::formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'],$_REQUEST['comentario'],$credito_BD,$_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$almacen_BD,$moneda_BD,$fpago1_BD,$fpago2_BD, $percepcion);
							$result_f = "&nbsp;";
					} else {
						$result_f = "<script>alert('Debe ingresar un articulo valido');</script>";
					}
				}
				break;

			case "Eliminar":
				$percepcion2=trim($_REQUEST['xpercepcion']);
				if ($percepcion2==""){$percepcion=$_REQUEST['percepcion'];}else{$percepcion=$_REQUEST['xpercepcion'];}
				$res = OrdenCompraModel::eliminarArticulo($_REQUEST['codart'],$_REQUEST['numero'],$_REQUEST['proveedor']);
?>
					<script>
						alert('Se ha eliminado correctamente');
					</script>
					<?php
					$proveedor = $_REQUEST['proveedor'];
					$almacen_BD 	= OrdenCompraModel::obtenerAlmacenBD($_REQUEST['numero'],$proveedor);
					$moneda_BD 	= OrdenCompraModel::obtenerMonedaBD($_REQUEST['numero'],$proveedor);
					$credito_BD 	= OrdenCompraModel::obtenerCreditoBD($_REQUEST['numero'],$proveedor);
					if($credito_BD == 'S'){
						$fpago1_BD 	= OrdenCompraModel::obtenerFPagoBD($_REQUEST['numero'],$proveedor);
					} else {
						$fpago2_BD 	= OrdenCompraModel::obtenerFPagoBD($_REQUEST['numero'],$proveedor);
					}

					$articulos = OrdenCompraModel::obtenerArticulos($_REQUEST['numero'],$percepcion);
					$serie_doc = OrdenCompraModel::obtenerSerie();
					$almacenes = OrdenCompraModel::obtenerAlmacenes();
					$estados = OrdenCompraModel::obtenerEstados();
					$monedas = OrdenCompraModel::obtenerMonedas();
					$fpago1 = OrdenCompraModel::obtenerFPago('96'); //05 - Contado o 96 - Crédito
					$fpago2 = OrdenCompraModel::obtenerFPago('05'); //05 - Contado o 96 - Crédito
					$fpago = 0;
					$credito = '';
					if ($_REQUEST['m_credito'] == 'S'){
						$fpago = $_REQUEST['fpago1'];
						$credito = 'S';
					} else {
						$fpago = $_REQUEST['N'];
						$credito = 'N';
					}
					if ($tcambio == 'null'){
						$tcambio='';
					}
					
					$boton = 'Modificar Cabecera';
					$habilitado1 = true;
					$habilitado2 = array("readonly");
					$habilitado3 = array("disabled");
					$habilitado4 = 'disabled';


					if (isset($_REQUEST['type-form'])) {
						if ($_REQUEST['type-form'] == 'edit-order') {
							$result = OrdenCompraTemplate::formActualizarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'],$_REQUEST['comentario'],$credito_BD,$_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$almacen_BD,$moneda_BD,$fpago1_BD,$fpago2_BD, $percepcion);
						}
					} else {
						$result = OrdenCompraTemplate::formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'],$_REQUEST['comentario'],$credito_BD,$_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$almacen_BD,$moneda_BD,$fpago1_BD,$fpago2_BD, $percepcion);
					}

					$result_f = "&nbsp;";
				break;

			case "Modificar Cabecera":
					$percepcion2=trim($_REQUEST['xpercepcion']);
				if ($percepcion2==""){$percepcion=$_REQUEST['percepcion'];}else{$percepcion=$_REQUEST['xpercepcion'];}
					$proveedor 	= $_REQUEST['proveedor'];
					$almacen_BD 	= OrdenCompraModel::obtenerAlmacenBD($_REQUEST['numero'],$proveedor);
					$moneda_BD 	= OrdenCompraModel::obtenerMonedaBD($_REQUEST['numero'],$proveedor);
					$credito_BD 	= OrdenCompraModel::obtenerCreditoBD($_REQUEST['numero'],$proveedor);
					if($credito_BD == 'S'){
						$fpago1_BD 	= OrdenCompraModel::obtenerFPagoBD($_REQUEST['numero'],$proveedor);
					} else {
						$fpago2_BD 	= OrdenCompraModel::obtenerFPagoBD($_REQUEST['numero'],$proveedor);
					}

					$articulos 	= OrdenCompraModel::obtenerArticulos($_REQUEST['numero'],$percepcion);
					$serie_doc 	= OrdenCompraModel::obtenerSerie();
					$almacenes 	= OrdenCompraModel::obtenerAlmacenes();
					$estados 	= OrdenCompraModel::obtenerEstados();
					$monedas 	= OrdenCompraModel::obtenerMonedas();
					$fpago1 	= OrdenCompraModel::obtenerFPago('96'); //05 - Contado o 96 - Crédito
					$fpago2 	= OrdenCompraModel::obtenerFPago('05'); //05 - Contado o 96 - Crédito
					$fpago = 0;
					$credito = '';
					if ($_REQUEST['m_credito'] == 'S'){
						$fpago = $_REQUEST['fpago1'];
						$credito = 'S';
					} else {
						$fpago = $_REQUEST['fpago2'];
						$credito = 'N';
					}
					$tcambio =$_REQUEST['tcambio'];
					if ($tcambio == 'null'){
						$tcambio='';
					}
					$boton = 'Guardar Cabecera';
					$habilitado1 = false;
					$habilitado2 = '';
					$habilitado3 = '';
					$habilitado4 = '';
					$result = OrdenCompraTemplate::formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'],$_REQUEST['comentario'],$credito_BD,$_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$almacen_BD,$moneda_BD,$fpago1_BD,$fpago2_BD, $percepcion, trim($_POST['chflete']), $fe_flete, $_POST['cbo-MotivoTraslado'], $_POST['no_placa'], $_POST['no_licencia'], $_POST['no_certificado_inscripcion'], $_POST['id_transportista_proveedor'], $_POST['no_transportista_proveedor']);
					$result_f = "&nbsp;";
				break;

			case "Guardar Cabecera":
				$percepcion2=trim($_REQUEST['xpercepcion']);
				if ($percepcion2==""){$percepcion=$_REQUEST['percepcion'];}else{$percepcion=$_REQUEST['xpercepcion'];}
					$valormoneda = $_REQUEST['moneda'];
					$credito = $_REQUEST['m_credito'];
					if ($credito == 'S'){
						$fpago = $_REQUEST['fpago1'];
					} else {
						$fpago = $_REQUEST['fpago2'];
					}
					$tcambio =$_REQUEST['tcambio'];
					if ($tcambio == ''){
						$tcambio='null';
					}
					$proveedor = $_REQUEST['proveedor'];
					if ($proveedor == '' || $_REQUEST['nombre'] == ''){
						$result_f = "<script>alert('Debe ingresar un proveedor valido');</script>";
					} else {
						$val_per=OrdenCompraModel::ctrl_percepcion($_REQUEST['numero'],$_REQUEST['subtotal'],$percepcion,0);
						$res = OrdenCompraModel::actualizar($_REQUEST['numero'],$proveedor,$valormoneda,$tcambio,$credito,$fpago,$_REQUEST['factura'],$_REQUEST['comentario'],$_REQUEST['fentrega'],$_REQUEST['glosa'],$val_per[0],$val_per[1]);
						if ($res==TRUE) {
							//Modificar FLETE
							if ( trim($_POST['chflete']) == 'S' ){
								$fe_flete = trim($_POST['fe_flete']);
								$fe_flete = strip_tags($fe_flete);
								$fe_flete = explode("/", $fe_flete);
								$fe_flete = $fe_flete[2] . "-" . $fe_flete[1] . "-" . $fe_flete[0];

								$Nu_Orden = "01".trim($_POST['serie']).trim($_POST['numero']);

								$fe_orden = trim($_POST['fecha']);
								$fe_orden = strip_tags($fe_orden);
								$fe_orden = explode("/", $fe_orden);
								$fe_orden = $fe_orden[2] . "-" . $fe_orden[1] . "-" . $fe_orden[0];

								$objOrdenModel->updFlete('00', $Nu_Orden, $fe_orden, $fe_flete, $_POST['cbo-MotivoTraslado'], $_POST['no_placa'], $_POST['no_licencia'], $_POST['no_certificado_inscripcion'], $_POST['id_transportista_proveedor']);
							}

							?>
							<script>
								alert('Se ha registrado correctamente');
							</script>
							<?php
							$articulos 	= OrdenCompraModel::obtenerArticulos($_REQUEST['numero'],$percepcion);
							$serie_doc 	= OrdenCompraModel::obtenerSerie();
							$almacenes 	= OrdenCompraModel::obtenerAlmacenes();
							$estados 	= OrdenCompraModel::obtenerEstados();
							$monedas 	= OrdenCompraModel::obtenerMonedas();
							$fpago1 	= OrdenCompraModel::obtenerFPago('96'); //05 - Contado o 96 - Crédito
							$fpago2 	= OrdenCompraModel::obtenerFPago('05'); //05 - Contado o 96 - Crédito
							if ($tcambio == 'null'){
								$tcambio='';
							}
							$boton = 'Modificar Cabecera';
							$habilitado1 = true;
							$habilitado2 = array("readonly");
							$habilitado3 = array("disabled");
							$habilitado4 = 'disabled';
							$result = OrdenCompraTemplate::formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos,$_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'], $_REQUEST['comentario'],$credito , $_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$_REQUEST['almacen'],$_REQUEST['moneda'],$_REQUEST['fpago1'],$_REQUEST['fpago2'], $percepcion, trim($_POST['chflete']), $fe_flete, $_POST['cbo-MotivoTraslado'], $_POST['no_placa'], $_POST['no_licencia'], $_POST['no_certificado_inscripcion'], $_POST['id_transportista_proveedor'], $_POST['no_transportista_proveedor']);
							$result_f = "&nbsp;";
						} else {	
							$result_f = "<script>alert('No se pudo registrar. Intente nuevamente');</script>";	
						}
					}
				break;

			case "Reporte":

				$resulta = OrdenCompraModel::ModelReportePDF($_REQUEST['txt-dInicial'],$_REQUEST['txt-dFinal'],$_REQUEST['cbo-iAlmacen'],$_REQUEST['estado_pendiente'],$_REQUEST['estado_inventario'],$_REQUEST['estado_procesando'],$_REQUEST['estado_facturado'],$_REQUEST['estado_cerrado']);
				$resul = OrdenCompraTemplate::TemplateReportePDF($resulta);

				$mi_pdf = "/sistemaweb/compras/movimientos/pdf/reporte_ordenes.pdf";
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="'."reporte_ordenes.pdf".'"');
				readfile($mi_pdf);
				break;

			case "Modificar":
				//nuevo caso para modificar los items de la compra(añadir, quitar o editar los valores de cada item)
				if (strlen($_POST["radio_modificar"]) > 0) {
					$numorden = $_POST["radio_modificar"];

					$datos = explode('/',$numorden);

					$dato1 = $datos[0];
					$dato2 = $datos[1];

					$numero = $dato1;

					$Nu_Orden = "01" . $dato2 . $numero;//tipo | serie | numero
					$arrFlete = $objOrdenModel->getFlete($Nu_Orden);

					$statusFlete = '';
					if ($arrFlete['estado']) {
						$statusFlete = 'S';

						//$fe_flete = ;
						$fe_flete = explode("-", $arrFlete['result'][0]['fe_flete']);
						$fe_flete = $fe_flete[2] . "/" . $fe_flete[1] . "/" . $fe_flete[0];
						$id_motivo_traslado = $arrFlete['result'][0]['id_motivo_traslado'];
						$no_placa = $arrFlete['result'][0]['no_placa'];
						$no_licencia = $arrFlete['result'][0]['no_licencia'];
						$no_certificado_inscripcion = $arrFlete['result'][0]['no_certificado_inscripcion'];
						$id_transportista_proveedor = $arrFlete['result'][0]['id_transportista_proveedor'];
						$no_transportista_proveedor = $arrFlete['result'][0]['pro_razsocial'];
					}

					if (OrdenCompraModel::obtenerOrdenExistente($numero) == 1) {
						$orden = OrdenCompraModel::obtenerOrdenPorNumero($numero);
						$proveedor = $orden['pro_codigo'];
						$proveedorNombre = $orden['razsocial'];
						$percepcion = $orden['percepcion_i'];
						
						$almacen_BD = OrdenCompraModel::obtenerAlmacenBD($numero, $proveedor);
						$moneda_BD 	= OrdenCompraModel::obtenerMonedaBD($numero, $proveedor);
						$credito_BD = OrdenCompraModel::obtenerCreditoBD($numero, $proveedor);

						if ($credito_BD == 'S')
							$fpago1_BD 	= OrdenCompraModel::obtenerFPagoBD($numero, $proveedor);
						else
							$fpago2_BD 	= OrdenCompraModel::obtenerFPagoBD($numero, $proveedor);

						$articulos = OrdenCompraModel::obtenerArticulos($numero, $percepcion);
						$serie_doc = OrdenCompraModel::obtenerSerie();
						$almacenes = OrdenCompraModel::obtenerAlmacenes();
						$estados = OrdenCompraModel::obtenerEstados();
						$monedas = OrdenCompraModel::obtenerMonedas();
						$fpago1 = OrdenCompraModel::obtenerFPago('96'); //05 - Contado o 96 - Crédito
						$fpago2 = OrdenCompraModel::obtenerFPago('05'); //05 - Contado o 96 - Crédito
						$boton = 'Modificar Cabecera';
						$habilitado1 = true;
						$habilitado2 = array("readonly");
						$habilitado3 = array("disabled");
						$habilitado4 = 'disabled';
						//$result = OrdenCompraTemplate::formActualizarArticulo($boton, $habilitado1, $habilitado2, $habilitado3, $habilitado4, $articulos, $numero, $serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $proveedorNombre, $tcambio, $orden['factura'], $orden['obs'], $credito_BD, $orden['glosa'], $orden['fecha'], $orden['fecha_entrega'], $almacen_BD, $moneda_BD, $fpago1_BD, $fpago2_BD, $percepcion, trim($_POST['chflete']), $fe_flete, $_POST['cbo-MotivoTraslado'], $_POST['no_placa'], $_POST['no_licencia'], $_POST['no_certificado_inscripcion'], $_POST['id_transportista_proveedor'], $_POST['no_transportista_proveedor']);
						$result = OrdenCompraTemplate::formActualizarArticulo($boton, $habilitado1, $habilitado2, $habilitado3, $habilitado4, $articulos, $numero, $serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $proveedorNombre, $tcambio, $orden['factura'], $orden['obs'], $credito_BD, $orden['glosa'], $orden['fecha'], $orden['fecha_entrega'], $almacen_BD, $moneda_BD, $fpago1_BD, $fpago2_BD, $percepcion, $statusFlete, $fe_flete, $id_motivo_traslado, $no_placa, $no_licencia, $no_certificado_inscripcion, $id_transportista_proveedor, $no_transportista_proveedor);
						$result_f = "&nbsp;";
					}
				}
				break;

			case "Imprimir":
				if(strlen($_POST["radio_imprimir"]) > 0)
				{
					$numorden = $_POST["radio_imprimir"];

					$datos = explode('/',$numorden);

					$dato1 = $datos[0];
					$dato2 = $datos[1];

					$cabecera = OrdenCompraModel::obtenerCabecera($dato1,$dato2);
					$proveedor = OrdenCompraModel::obtenerProveedor($cabecera);
					$almacen = OrdenCompraModel::obtenerAlmacenUnico($cabecera);
					$almacen2 = OrdenCompraModel::obtenerAlmacenUnico($cabecera);
					$almacen3 = OrdenCompraModel::obtenerAlmacen2($cabecera);
					//$almacen3 = "";
					//$importante = OrdenCompraModel::obtenerImportante();
					$importante = null;
					if($cabecera['com_cab_credito']=='S'){$m_tab = "96";}
					else {$m_tab = "05";}

					$ordenes = OrdenCompraModel::obtenerDatosOrden($dato1,$dato2);
					$monedas = OrdenCompraModel::obtenerMoneda();
					$fpago = OrdenCompraModel::obtenerFormaPago($m_tab);

					/* Flete */
					$Nu_Orden = "01" . $dato2 . $dato1;//tipo | serie | numero
					$arrFlete = $objOrdenModel->getFlete($Nu_Orden);

					$statusFlete = '';
					if ($arrFlete['estado'])
						$statusFlete = 'S';

					$resul = OrdenCompraTemplate::TemplateReportePDFPersonal($cabecera,$proveedor,$almacen,$almacen2,$almacen3,$ordenes,$monedas,$fpago,$importante, $statusFlete, $arrFlete);
					$mi_pdf = "/sistemaweb/compras/movimientos/pdf/OrdenCompra_".trim($dato1).trim($dato2).".pdf";
					header('Content-type: application/pdf');
					header('Content-Disposition: attachment; filename="'."OrdenCompra_".trim($dato1).trim($dato2).".pdf".'"');
					readfile($mi_pdf);
				}
				else
				{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" Debe seleccionar una Orden de Compra !!! ") ');
					echo('</script>');
				}
				break;

			case "Enviar":
				if(strlen($_POST["radio_enviar"]) > 0)
				{
					$numorden = $_POST["radio_enviar"];
					$correo = OrdenCompraModel::obtenerCorreo();
					$clave = OrdenCompraModel::obtenerClave();
					$nombre = OrdenCompraModel::obtenerNombre();
					$destino = OrdenCompraModel::obtenerCorreoDestino();

					//Generamos el PDF
					$cabecera = OrdenCompraModel::obtenerCabecera($numorden);
					$proveedor = OrdenCompraModel::obtenerProveedor($cabecera);
					$almacen = OrdenCompraModel::obtenerAlmacenUnico($cabecera);
					$importante = OrdenCompraModel::obtenerImportante();
					if($cabecera['com_cab_credito']=='S'){$m_tab = "96";}
					else {$m_tab = "05";}
					$ordenes = OrdenCompraModel::obtenerDatosOrden($numorden);
					$monedas = OrdenCompraModel::obtenerMoneda();
					$fpago = OrdenCompraModel::obtenerFormaPago($m_tab);
					$resul = OrdenCompraTemplate::TemplateReportePDFPersonal($cabecera,$proveedor,$almacen,$ordenes,$monedas,$fpago,$importante);
					//var_dump($resul);
					/*$mail = new PHPMailer();
					$mail->SetLanguage("es");
					$mail->isSMTP();
					$mail->SMTPAuth = true;

					
					/*
					//YAHOO FUNCIONA
					$mail->Host = "smtp.mail.yahoo.com";
					$mail->Username = $correo['par_valor'];
					$mail->Password = $clave['par_valor'];
					$mail->SetFrom($correo['par_valor'],$nombre['par_valor']);
					*/
					//prueba
					$mail = new phpmailer();

  
  $mail->PluginDir = "/sistemaweb/compras/lib/";

  
  $mail->Mailer = "smtp";

  
  $mail->Host = "smtp.mail.yahoo.com";

 
  $mail->SMTPAuth = true;

  
  $mail->Username = "rocio@yahoo.com"; 
  $mail->Password = "Rocio2013";

  
  $mail->From = "rocio@yahoo.com";
  $mail->FromName = "Rocio2013";

  

  

  


					//fin de prueba
					//NO FUNCIONA QMAILHOSTING
					//$mail->SMTPSecure = "ssl";
					//$mail->Port = 465;
					/*$mail->Host = "smtp.gmail.com";
					$mail->Username = "falvarezponce@gmail.com";
					$mail->Password = "**********";
					$mail->SetFrom("falvarezponce@gmail.com","Fernando Alvarez");*/

					//mail.qmailhosting.net		465 - SSL			Auten
					//smtp.gmail.com		465 - SSL	587 - TLS	Auten		Saliente
					//pop.gmail.com			995 - SSL			Auten		Entrante
					//smtp.email.msn.com						Auten
					//pop3.email.msn.com		110				Auten
					//smtp.live.com		 	 25 - SSL	587 - TLS-SSL	Auten
					//pop3.live.com			995 - SSL			Auten
					//smtp.mail.yahoo.com	 	 25		465 - SSL	Auten		Saliente
					//pop.mail.yahoo.com    	110		995 - SSL			Entrante
					//pop3.mail.yahoo.com    	995 - 								
					//plus.pop.mail.yahoo.com	995 - SSL	
					//plus.smtp.mail.yahoo.com	465 - SSL			Auten

					$mail->Timeout=20;
					$mail->AddAddress("rocio@opensysperu.com");
					$mail->Subject = "Probando ENVIO DE EMAIL";
					$mail->Body = "Mensaje de prueba confirmar si llego";
					$mail->AltBody = "Mensaje de prueba confirmar si llego";
					$mail->AddAttachment("/sistemaweb/compras/movimientos/pdf/OrdenCompra_".trim($numorden).".pdf", "OrdenCompra_".trim($numorden).".pdf");
					$exito = $mail->Send();
					//var_dump($exito);
					$intentos=0;
					while ((!$exito) && ($intentos < 3)) {
						sleep(5);
						$exito = $mail->Send();
						$intentos=$intentos+1;
					}

					if(!$exito)	{ 
						?><script>alert('<?php echo $mail->ErrorInfo; ?>');</script><?php 
					} else		{ 
						?><script>alert('Mensaje Enviado');</script><?php 
					}
				}
				else
				{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" Debe seleccionar una Orden de Compra !!! ") ');
					echo('</script>');
				}
				break;

			case "GENERAR_INVENTARIO":
				$begin = OrdenCompraModel::BEGINTransacction();
				$arrInventario = OrdenCompraModel::saveInventario($_GET);
				
				if($arrInventario['estado']){
					OrdenCompraModel::COMMITTransacction();
					$resultados = OrdenCompraModel::buscar($_REQUEST['iAlmacenB'],$_REQUEST['dFechaInicioB'],$_REQUEST['dFechaFinalB'],'','','','','');
					$result_f = OrdenCompraTemplate::resultadosBusqueda($resultados, $_REQUEST['iAlmacenB'], $_REQUEST['dFechaInicioB'], $_REQUEST['dFechaFinalB']);
					?><script>alert("<?php echo 'Registro guardado satisfactoriamente' ; ?> ");</script><?php
				} else {
					?><script>alert("<?php echo 'Error al guardar' ; ?> ");</script><?php
					OrdenCompraModel::ROLLBACKTransacction();
				}

				break;

			default:
				$almacenes = OrdenCompraModel::obtenerAlmacenes();
				$arrTipoMovimientoInventarios = OrdenCompraModel::obtenerTipoMovimientoInventarios();
				$arrTipoDocumentos = OrdenCompraModel::obtenerTipoDocumentos();
				$arrTipoMonedas = OrdenCompraModel::obtenerTipoMonedas();
				$almacenes['TODAS'] = "Todos los almacenes";
				$result = OrdenCompraTemplate::formSearch($dUltimoCierre, $almacenes, $arrTipoMovimientoInventarios, $arrTipoDocumentos, $arrTipoMonedas);
				break;
		}
		$this->visor->addComponent("ContentT", "content_title", OrdenCompraTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}

}
