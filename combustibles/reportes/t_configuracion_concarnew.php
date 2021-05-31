<?php
class ConfigurarConcarTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Configuracion Contable - Asientos Concar New SQL</b></h2>';
	}

	function formSearch($datos, $datosEquivalenciaProducto, $arrResponseTarjetasCredito){
		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.CONFIGURARCONCARNEW"));
		
		//error_log(json_encode($datos['account_030']));

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
						margin-top: 9px;
					}

					article td {
						font-size: 0.6em; 
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
						padding-top: 5px;
						padding-bottom: 5px;
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
					Subdiario:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_subdiario" value="'.$datos['account_100']['account'].'" >
				</td>
			<tr>
				<td>
					Cuenta Cliente Combustible:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente" value="'.$datos['account_110']['account'].'" >
				</td>
			<tr>
				<td>
					Cuenta Cliente GLP:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente_glp" value="'.$datos['account_120']['account'].'" >
				</td>
				<td>
			<tr>
				<td>
					Cuenta Impuesto:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_impuesto_account_111" value="'.$datos['account_111']['account'].'" >
				</td>
			<tr>
				<td>
					Cuenta Ventas Combustible:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas" value="'.$datos['account_112']['account'].'" >
				</td>
			<tr>
				<td>
					Cuenta Ventas GLP:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas_glp" value="'.$datos['account_121']['account'].'" >
				</td>
			</tr>
			</table>
			</article>

			<article>
			<h2>II. Ventas Market</h2>
			<table>
			<tr>
				<td>
					Subdiario:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_subdiario_market" value="'.$datos['account_200']['account'].'" >
				</td>
			<tr>
			<tr>
				<td>
					Cuenta Cliente:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente_mkt" value="'.$datos['account_210']['account'].'" >
				</td>
			<tr>
			<tr>
				<td>
					Cuenta Impuesto:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_impuesto_account_211" value="'.$datos['account_211']['account'].'" >
				</td>
			<tr>
			<tr>
				<td>
					Cuenta Ventas:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas_mkt" value="'.$datos['account_212']['account'].'" >
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
				<input type="text" maxlength="6" size="6" name="ccobrar_subdiario" value="'.$datos['account_300']['account'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Cliente Combustible:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_cliente" value="'.$datos['account_310']['account'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Caja Combustible:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_caja" value="'.$datos['account_311']['account'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Cliente GLP:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_cliente_new" value="'.$datos['account_320']['account'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Caja GLP:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_caja_new" value="'.$datos['account_321']['account'].'" >
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
				<input type="text" maxlength="6" size="6" name="ccobrar_subdiario_mkt" value="'.$datos['account_400']['account'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Cliente:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_cliente_mkt" value="'.$datos['account_410']['account'].'" >
				</td>
			</tr>
			<tr>
				<td>
					Cuenta Caja:
				</td>
				<td>
				<input type="text" maxlength="6" size="6" name="ccobrar_cuenta_caja_mkt" value="'.$datos['account_411']['account'].'" >
				</td>
			</tr>
			</table>
			</article>

			<article>
				<h2>V. Ventas Documentos Manuales</h2>
				<table>
					<tr>
						<td>
							Subdiario:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_subdiario_docManual" value="'.$datos['account_600']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Cliente Combustible:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente_dMa" value="'.$datos['account_610']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Cliente GLP:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_cliente_glp2" value="'.$datos['account_620']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Impuesto:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_impuesto_account_611" value="'.$datos['account_611']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Ventas Combustible:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas_dMa" value="'.$datos['account_612']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Ventas GLP:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="venta_cuenta_ventas_glp2" value="'.$datos['account_621']['account'].'" >
						</td>
					</tr>
				</table>
			</article>

			<article>
				<h2>VI. Compras Documentos Manuales</h2>
				<table>
					<tr>
						<td>
							Subdiario Combustible:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_subdiario_comb" value="'.$datos['account_500']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Subdiario GLP:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_subdiario_glp" value="'.$datos['account_501']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Subdiario Market:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_subdiario_mkt" value="'.$datos['account_502']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra Proveedor Combustible:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_proveedor_comb" value="'.$datos['account_510']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra Proveedor GLP:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_proveedor_glp" value="'.$datos['account_520']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra Proveedor Market:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_proveedor_mkt" value="'.$datos['account_530']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra Impuesto:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_impuesto" value="'.$datos['account_531']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra B.I Combustible:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_mercaderia_comb" value="'.$datos['account_511']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra B.I GLP:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_mercaderia_glp" value="'.$datos['account_521']['account'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Cuenta Compra B.I Market:
						</td>
						<td>
						<input type="text" maxlength="6" size="6" name="compra_cuenta_mercaderia_mkt" value="'.$datos['account_532']['account'].'" >
						</td>
					</tr>
				</table>
			</article>

			<article>
				<h2>VII. Anexos</h2>
				<table>
					<tr>
						<td>
							Combustible '.$datosEquivalenciaProducto[0]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_84" value="'.$datosEquivalenciaProducto[0]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datosEquivalenciaProducto[1]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_90" value="'.$datosEquivalenciaProducto[1]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datosEquivalenciaProducto[2]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_97" value="'.$datosEquivalenciaProducto[2]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datosEquivalenciaProducto[3]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_d2" value="'.$datosEquivalenciaProducto[3]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datosEquivalenciaProducto[4]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_95" value="'.$datosEquivalenciaProducto[4]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							Combustible '.$datosEquivalenciaProducto[5]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_glp" value="'.$datosEquivalenciaProducto[5]['codigo_concar'].'" >
						</td>
					</tr>
					<tr>
						<td>
							'.$datosEquivalenciaProducto[6]['codigo_iridium'].':
						</td>
						<td>
						<input type="text" maxlength="13" size="13" name="codigo_concar_m" value="'.$datosEquivalenciaProducto[6]['codigo_concar'].'" >
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
