<?php
//require("PHPMailer/class.phpmailer.php");
include("lib/class.phpmailer.php");
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

		$result = "";
		$result_f = "";
		$form_search = false;
		$listado = false;
		$editar = false;
		$actualizar = false;

		switch($this->action) {
			case "Consultar":
				$percepcion2=trim($_REQUEST['xpercepcion']);
				if ($percepcion2==""){$percepcion=$_REQUEST['percepcion'];}else{$percepcion=$_REQUEST['xpercepcion'];} 	
				$almacenes = OrdenCompraModel::obtenerAlmacenes();
				$almacenes['TODAS'] = "Todos los almacenes";
				$resultados = OrdenCompraModel::buscar($_REQUEST['almacen'],$_REQUEST['fecha'],$_REQUEST['fecha2'],$_REQUEST['estado_pendiente'],$_REQUEST['estado_inventario'],$_REQUEST['estado_procesando'],$_REQUEST['estado_facturado'],$_REQUEST['estado_cerrado'],$percepcion);
				$result_f = OrdenCompraTemplate::resultadosBusqueda($resultados,$almacenes);
				break;

			case "Agregar":
				$almacenes = OrdenCompraModel::obtenerAlmacenes();
				$monedas = OrdenCompraModel::obtenerMonedas();
				$estados = OrdenCompraModel::obtenerEstados();
				$fpago1 = OrdenCompraModel::obtenerFPago('96'); //05 - Contado o 96 - Crédito
				$fpago2 = OrdenCompraModel::obtenerFPago('05'); //05 - Contado o 96 - Crédito
				$numero_orden = OrdenCompraModel::obtenerOrdenCorrelativa();
				$serie_doc = OrdenCompraModel::obtenerSerie();

				$result = OrdenCompraTemplate::formAgregar($numero_orden,$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2);
				$result_f = "&nbsp;";
				break;

			case "Regresar":
				$almacenes = OrdenCompraModel::obtenerAlmacenes();
				$almacenes['TODAS'] = "Todos los almacenes";
				$result = OrdenCompraTemplate::formSearch($almacenes);
				$result_f = "&nbsp;";
				break;

			case "Insertar":
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
						if ($res==TRUE) {
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
							$result = OrdenCompraTemplate::formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'],$_REQUEST['comentario'],$credito_BD,$_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$almacen_BD,$moneda_BD,$fpago1_BD,$fpago2_BD, $percepcion);
							
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
					$result = OrdenCompraTemplate::formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'],$_REQUEST['comentario'],$credito_BD,$_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$almacen_BD,$moneda_BD,$fpago1_BD,$fpago2_BD, $percepcion);
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
					$result = OrdenCompraTemplate::formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'],$_REQUEST['comentario'],$credito_BD,$_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$almacen_BD,$moneda_BD,$fpago1_BD,$fpago2_BD, $percepcion);
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
							$result = OrdenCompraTemplate::formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos,$_REQUEST['numero'],$serie_doc, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $_REQUEST['nombre'], $tcambio, $_REQUEST['factura'], $_REQUEST['comentario'],$credito , $_REQUEST['glosa'], $_REQUEST['fecha'],$_REQUEST['fentrega'],$_REQUEST['almacen'],$_REQUEST['moneda'],$_REQUEST['fpago1'],$_REQUEST['fpago2'], $percepcion);
							$result_f = "&nbsp;";
						} else {	
							$result_f = "<script>alert('No se pudo registrar. Intente nuevamente');</script>";	
						}
					}
				break;

			case "Reporte":

				$resulta = OrdenCompraModel::ModelReportePDF($_REQUEST["fecha"],$_REQUEST["fecha2"],$_REQUEST["almacen"],$_REQUEST['estado_pendiente'],$_REQUEST['estado_inventario'],$_REQUEST['estado_procesando'],$_REQUEST['estado_facturado'],$_REQUEST['estado_cerrado']);
				$resul = OrdenCompraTemplate::TemplateReportePDF($resulta);

				$mi_pdf = "/sistemaweb/compras/movimientos/pdf/reporte_ordenes.pdf";
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename="'."reporte_ordenes.pdf".'"');
				readfile($mi_pdf);
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
					$importante = OrdenCompraModel::obtenerImportante();

					if($cabecera['com_cab_credito']=='S'){$m_tab = "96";}
					else {$m_tab = "05";}

					$ordenes = OrdenCompraModel::obtenerDatosOrden($dato1,$dato2);
					$monedas = OrdenCompraModel::obtenerMoneda();
					$fpago = OrdenCompraModel::obtenerFormaPago($m_tab);
					$resul = OrdenCompraTemplate::TemplateReportePDFPersonal($cabecera,$proveedor,$almacen,$almacen2,$almacen3,$ordenes,$monedas,$fpago,$importante);
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

  
  $mail->Username = "collazos_tec@yahoo.com"; 
  $mail->Password = "Gean2013";

  
  $mail->From = "collazos_tec@yahoo.com";
  $mail->FromName = "Gean2013";

  

  

  


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
					$mail->AddAddress("gcollazos@opensysperu.com");
					$mail->Subject = "Probando ENVIO DE EMAIL";
					$mail->Body = "Mensaje de prueba confirmar si llego";
					$mail->AltBody = "Mensaje de prueba confirmar si llego";
					$mail->AddAttachment("/sistemaweb/compras/movimientos/pdf/OrdenCompra_".trim($numorden).".pdf", "OrdenCompra_".trim($numorden).".pdf");
					$exito = $mail->Send();
					var_dump($exito);
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

			default:
				$almacenes = OrdenCompraModel::obtenerAlmacenes();
				$almacenes['TODAS'] = "Todos los almacenes";
				$result = OrdenCompraTemplate::formSearch($almacenes);
				break;
		}
		$this->visor->addComponent("ContentT", "content_title", OrdenCompraTemplate::titulo());
		if ($result != "") $this->visor->addComponent("ContentB", "content_body", $result);
		if ($result_f != "") $this->visor->addComponent("ContentF", "content_footer", $result_f);
	}

}
?>
