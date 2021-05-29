<?php
class ConfigurarConcarTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Configuracion Contable - Asientos Concar New SQL</b></h2>';
	}

	function formSearch($datos, $arrResponseTarjetasCredito){
		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.CONFIGURARCONCAR"));
		
		error_log(json_encode($datos['account_030']));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<!DOCTYPE html>
			<html lang="en">
			<head>
				<style>
					body {
						font-family: Verdana, sans-serif; 
						font-size:0.8em;
					}

					article {
						border-left: 5px solid #04AA6D; 
						border-top: 1px solid #04AA6D; 
						border-right: 1px solid #04AA6D; 
						border-bottom: 1px solid #04AA6D; 
						
						margin: 10px;
    					padding: 8px;						

						color:#04AA6D;
						width: auto;
					}		
					
					article h2 {
						font-size: 2em;
						margin-top: 9px;
					}

					article td {
						font-size: 0.8em; 
					}	
					
					input {												
						width: 100%!important;
						margin: 0px 0;
						display: inline-block;
						border: 1px solid #ccc;
						box-shadow: inset 0 1px 3px #ddd;
						border-radius: 4px;		
						-webkit-box-sizing: border-box;
						-moz-box-sizing: border-box;
						box-sizing: border-box;
						padding-left: 20px;
						padding-right: 20px;
						padding-top: 8px;
						padding-bottom: 8px;
						font-size: 1em;																												
					}

					/*
					input {
						width: 100%;
						margin: 8px 0;
						display: inline-block;
						border: 1px solid #ccc;
						box-shadow: inset 0 1px 3px #ddd;
						border-radius: 4px;
						-webkit-box-sizing: border-box;
						-moz-box-sizing: border-box;
						box-sizing: border-box;
						padding-left: 20px;
						padding-right: 20px;
						padding-top: 12px;
						padding-bottom: 12px;
					}
					*/
				</style>
			</head>

			<body>

			<article>
			<h2>Sucursal</h2>
			<table>
			<tr>
				<td>
					Codigo de Anexo: 
				<td>
				<input type="text" maxlength="11" size="11" name="cod_cliente" value="'.$datos['account_011']['account'].'" autofocus>
				</td>
			<tr>
				<td>
					Centro de Costo Combustible: 
				<td>
				<input type="text" maxlength="4" size="4" name="id_cencos_comb" value="'.$datos['account_020']['account'].'" autofocus>
				</td>
			<tr>
				<td>
					Centro de Costo GLP: 
				<td>
				<input type="text" maxlength="4" size="4" name="id_centro_costo_glp" value="'.$datos['account_021']['account'].'" autofocus>
				</td>
			<tr>
				<td>
					Centro de Costo Market: 
				<td>
				<input type="text" maxlength="4" size="4" name="id_centrocosto" value="'.$datos['account_022']['account'].'" autofocus>
				</td>
			<tr>
				<td>
					Centro de Costo Documentos Manuales: 
				<td>
				<input type="text" maxlength="4" size="4" name="id_centro_cos_dma" value="'.$datos['account_023']['account'].'" autofocus>
				</td>
			</table>
			</article>

			<article>
			<h2>I. Ventas Combustibles y GLP</h2>
			<table>
			<tr>
				<td>
					Cuenta Cliente Combustible:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_subdiario" value="'.$datos[0]['venta_subdiario'].'" >
				</td>
			<tr>
				<td>
					Cuenta Cliente Combustible:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente" value="'.$datos[0]['venta_cuenta_cliente'].'" >
				</td>
			<tr>
				<td>
					Cuenta Cliente GLP:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente_glp" value="'.$datos[0]['venta_cuenta_cliente_glp'].'" >
				</td>
				<td>
			<tr>
				<td>
					Cuenta Impuesto:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_impuesto" value="'.$datos[0]['venta_cuenta_impuesto'].'" >
				</td>
			<tr>
				<td>
					Cuenta Ventas Combustible:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas" value="'.$datos[0]['venta_cuenta_ventas'].'" >
				</td>
			<tr>
				<td>
					Cuenta Ventas GLP:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas_glp" value="'.$datos[0]['venta_cuenta_ventas_glp'].'" >
				</td>
			</tr>
			</table>
			</article>

			<article>
			<h2>II. Ventas de Tiendas y Productos</h2>
			<table>
			<tr>
				<td>
					Subdiario:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_subdiario_market" value="'.$datos[0]['venta_subdiario_market'].'" >
				</td>
			<tr>
			<tr>
				<td>
					Cuenta Cliente:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente_mkt" value="'.$datos[0]['venta_cuenta_cliente_mkt'].'" >
				</td>
			<tr>
			<tr>
				<td>
					Cuenta Impuesto:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_impuesto" value="'.$datos[0]['venta_cuenta_impuesto'].'" >
				</td>
			<tr>
			<tr>
				<td>
					Cuenta Ventas:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas_mkt" value="'.$datos[0]['venta_cuenta_ventas_mkt'].'" >
				</td>
			</tr>
			</table>
			</article>

			<article>
			<h2>III. Cuentas por Cobrar Combustibles y GLP</h2>
			<table>
			<tr>
				<td>
					Subdiario:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_subdiario" value="'.$datos[0]['ccobrar_subdiario'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Cliente Combustible:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_cliente" value="'.$datos[0]['ccobrar_cuenta_cliente'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Caja Combustible:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_caja" value="'.$datos[0]['ccobrar_cuenta_caja'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Cliente GLP:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_cliente_new" value="'.$datos[0]['ccobrar_cuenta_cliente_new'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Caja GLP:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_caja_new" value="'.$datos[0]['ccobrar_cuenta_caja_new'].'" >
				</td>
			</tr>
			</table>
			</article>

			<article>
			<h2>IV. Cuentas por Cobrar Market</h2>
			<table>
			<tr>
				<td>
					Subdiario:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_subdiario_mkt" value="'.$datos[0]['ccobrar_subdiario_mkt'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Cliente:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_cliente_mkt" value="'.$datos[0]['ccobrar_cuenta_cliente_mkt'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Caja:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_caja_mkt" value="'.$datos[0]['ccobrar_cuenta_caja_mkt'].'" >
				</td>
			</tr>
			</table>
			</article>

			<article>
				<h2>V. Documentos Manuales Ventas</h2>
				<table>
					<tr>
						<td>
							Subdiario:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_subdiario_docManual" value="'.$datos[0]['venta_subdiario_docManual'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Cliente Combustible:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente_dMa" value="'.$datos[0]['venta_cuenta_cliente_dMa'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Cliente GLP:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente_glp2" value="'.$datos[0]['venta_cuenta_cliente_glp2'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Impuesto:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_impuesto" value="'.$datos[0]['venta_cuenta_impuesto'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Ventas Combustible:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas_dMa" value="'.$datos[0]['venta_cuenta_ventas_dMa'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Ventas GLP:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas_glp2" value="'.$datos[0]['venta_cuenta_ventas_glp2'].'" >
						</td>
					</tr>
				</table>
			</article>

			<article>
				<h2>VI. Documentos Manuales Compras</h2>
				<table>
					<tr>
						<td>
							Subdiario:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_subdiario" value="'.$datos[0]['compra_subdiario'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra Proveedor:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_proveedor" value="'.$datos[0]['compra_cuenta_proveedor'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra Impuesto:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_impuesto" value="'.$datos[0]['compra_cuenta_impuesto'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra B.I:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_mercaderia" value="'.$datos[0]['compra_cuenta_mercaderia'].'" >
						</td>
					</tr>
				</table>
			</article>

			<article>
				<h2>VII. Anexos</h2>
				<table>
					<tr>
						<td>
							Combustible '.$datos[0]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_84" value="'.$datos[0]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datos[1]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_90" value="'.$datos[1]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datos[2]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_97" value="'.$datos[2]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datos[3]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_d2" value="'.$datos[3]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datos[4]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_95" value="'.$datos[4]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datos[5]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_glp" value="'.$datos[5]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							'.$datos[6]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_m" value="'.$datos[6]['codigo_concar'].'" >
						</td>
					</tr>
				</table>
			</article>

			<article>
				<h2>VIII. Cuentas tarjeta de crédito Combustible y GLP</h2>
				<table border="0" cellspacing="5">
					<tr>
						<td>Tipo</td>
						<td>Tarjeta</td>
						<td>Cuenta10</td>
						<td>Cuenta12</td>
					</tr>
'));

					foreach ( $arrResponseTarjetasCredito['arrData'] as $row ) {
						$sTipoDocumento='Boleta';
						if( $row['idtipodoc'] == 'F' ){
							$sTipoDocumento='Factura';
						}
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
						<tr>
							<td>'.$sTipoDocumento.'</td>
							<td>'.$row['descripcion'].'</td>
							<td>
								<input type="text" maxlength="15" size="15" name="arrCuentaCaja10[' . $row['id_config'] . ']" value="'.$row['cuenta10'].'" >
							</td>
							<td>
								<input type="text" maxlength="15" size="15" name="arrCuentaCaja12[' . $row['id_config'] . ']" value="'.$row['cuenta12'].'" >
							</td>
						</tr>'));
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
				</table>
			</article>

		'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/images/search.png" align="right" />Actualizar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		return $form->getForm();
	}

}
