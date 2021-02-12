<?php

class FacturasElectronicasTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2><b>Importacion de Facturas Electronicas de Proveedor</b></h2></div>';
	}
    
	function search_form() {
		$form = new form2('', 'form_search_form', FORM_METHOD_POST, 'control.php', '', 'control','enctype="multipart/form-data"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.FACTURASELECTRONICAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Archivo: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="file" name="ubicacion" id="ubicacion" size="60">&nbsp;&nbsp;&nbsp;'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Procesar", espacios(3)));

		return $form->getForm();
    	}
    
    	function reporte($res) {
    		$ins = $res['ingresa'];
    		$inv = $res['invalido'];
    		$almacenes = FacturasElectronicasModel::obtieneListaEstaciones($res['almacen']);
    		$tipodocus = FacturasElectronicasModel::obtieneTipoDocus();
    		$rubros    = FacturasElectronicasModel::obtieneRubros();
    		$monedas   = FacturasElectronicasModel::obtieneMonedas();
    		$tipocambio= FacturasElectronicasModel::obtieneTipoCambio($res['fecha']);
    		$factorigv = FacturasElectronicasModel::sacarIgv();
    		$factorigv = $factorigv+1;
		
	    	$form = new form2('', 'form_lista', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.FACTURASELECTRONICAS"));
		$total = 0;		
		// Datos para ingresar
		if(count($ins)>0) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table align="center" style="border-top:1px solid;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;">'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<thead align="center" valign="center" ><tr><td colspan="7" class="grid_cabecera">INVENTARIOS: INGRESO POR COMPRA</td></tr></thead>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha</td><td>:</td><td>'.$res['fechav'].'</td>'));
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("fechar", $res['fecha']));	
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Tipo de Doc.</td><td>:</td><td>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipodocu", "", "10", $tipodocus, '</td>'));	
					
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Nro. de Formulario</td><td>:</td><td>'.$res['numfactura'].'</td>'));
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("numfactura", $res['numfactura']));	
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Nro. Documento</td><td>:</td><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("docurefe", "", $res['docurefe'], '', 20, 20, '',''));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Proveedor</td><td>:</td><td>'.$res['codigopro'].' - '.$res['nombrepro'].'</td>'));
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("codigopro", $res['codigopro']));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Nro. O/C</td><td>:</td><td>'.$res['nroorden'].'</td>'));
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("nroorden", $res['nroorden']));
			
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $res['almacen'], $almacenes, '</td>'));	
							
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></table>'));				
							
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><table align="center" cellpadding="2" >'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<thead align="center" valign="center" ><tr class="grid_header">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera" >COD. ARTICULO</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera" >DESCRIPCION</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera" >UNIDAD</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera" >MONEDA</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera" >CANTIDAD</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera" >COSTO UNITARIO</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_cabecera" >VALOR TOTAL</td>'));			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></thead>'));
				
			for($i=0; $i<count($ins); $i++) {
				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
				$ins[$i][10] = round($ins[$i][10],2);
				$total = $total + $ins[$i][10];
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">'));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("cod_art[".$i."]", "", $ins[$i][4], '</td><td class="'.$color.'">', 20, 20, '',array('readonly')));			
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("nom_art[".$i."]", "", $ins[$i][8], '</td><td class="'.$color.'">', 70, 70, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("unidad[".$i."]", "", $ins[$i][5], '</td><td class="'.$color.'">', 15, 15, '',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("moneda[".$i."]", "", $ins[$i][1], '</td><td class="'.$color.'">', 15, 15, '',array('readonly')));												
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("cantidad[".$i."]", "", $ins[$i][6], '</td><td class="'.$color.'">', 12, 12, array("style"=>"text-align:right"),array('readonly')));										
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("precio[".$i."]", "", $ins[$i][7], '</td><td class="'.$color.'">', 18, 18, array("style"=>"text-align:right"),array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("valtot[".$i."]", "", $ins[$i][10], '</td>', 15, 15, array("style"=>"text-align:right"),array('readonly')));																				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));		    	
			}			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr style="font-size:16px;background:#ABD3D2;"><td colspan="6" align="right">TOTAL&nbsp;&nbsp;&nbsp;</td><td align="right">'.$total.'</th></tr>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table><div align="center">'));
					
			// +++++++++ registro de compras +++++++++
			$impinafecto = 0;
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><br><table align="center" style="border-top:1px solid;border-bottom:1px solid;border-left:1px solid;border-right:1px solid;">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<thead align="center" valign="center" ><tr><td colspan="7" class="grid_cabecera">REGISTRO DE COMPRAS<input type="checkbox" name="cpagar" value="S" onClick="if(this.checked) {rgfechadocumento.disabled=false;rgtipodocu.disabled=false;rgseriedocu.disabled=false;rgnumerodocu.disabled=false;rgrubro.disabled=false;rgmoneda.disabled=false;rgvencimiento.disabled=false;rgvventa.disabled=false;rgimpuesto.disabled=false;rgvtotal.disabled=false;rgtcambio.disabled=false;rgimpinafecto.disabled=false;} else {rgfechadocumento.disabled=true;rgtipodocu.disabled=true;rgseriedocu.disabled=true;rgnumerodocu.disabled=true;rgvencimiento.disabled=true;rgrubro.disabled=true;rgmoneda.disabled=true;rgvventa.disabled=true;rgimpuesto.disabled=true;rgvtotal.disabled=true;rgtcambio.disabled=true;rgimpinafecto.disabled=true;}" ></td></tr></thead>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha de Registro</td><td>:</td><td>'.$res['fechav'].'</td>'));
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rgfechasistema", $res['fecha']));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Moneda</td><td>:</td><td>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo("rgmoneda", "", "", $monedas, '</td>','',Array('disabled=true')));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha de Documento</td><td>:</td><td>'));			
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("rgfechadocumento", "", $res['fechatxtv'], '', 10, 12,'',Array('disabled=true')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_lista.rgfechadocumento'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Tipo de Cambio</td><td>:</td><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("rgtcambio", "", $tipocambio, '</td>', 8, 10,'',Array('disabled=true')));	
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><tr><td>Tipo de Doc.</td><td>:</td><td>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo("rgtipodocu", "", "10", $tipodocus, '</td>','',Array('disabled=true')));	

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Valor de Venta</td><td>:</td><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("rgvventa", "", $total, '</td>', 10, 12,'',Array('disabled=true','onKeyUp=\'actVVenta(this.value, '.$factorigv.')\'')));	
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Serie y Nro. Documento</td><td>:</td><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("rgseriedocu", "", substr($res['docurefe'],0,3), ' - ', 3, 3, '',Array('disabled=true')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("rgnumerodocu", "", substr($res['docurefe'],3,9), '</td>', 9, 9, '',Array('disabled=true')));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Impuesto</td><td>:</td><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("rgimpuesto", "", round(($total*$factorigv)-$total,2), '</td>', 10, 12,'',Array('disabled=true','onKeyUp=\'actImpuesto(this.value)\'')));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Rubro</td><td>:</td><td>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo("rgrubro", "", "", $rubros, '</td>','',Array('disabled=true')));	

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));						
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Varios</td><td>:</td><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("rgimpinafecto", "", $impinafecto, '</td>', 10, 12,'',Array('disabled=true')));	

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha de Vencimiento</td><td>:</td><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("rgvencimiento", "", date("d/m/Y"), '', 10, 12,'',Array('disabled=true')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_lista.rgvencimiento'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></td>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>'));						
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Total Compra</td><td>:</td><td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("rgvtotal", "", round($total*$factorigv,2), '</td>', 10, 12,'',Array('disabled=true','onKeyUp=\'actVTotal(this.value)\'')));	
							
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));			
			// +++++++++ end registro de compras +++++++++
						
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</div><br><div align="center"><button type="submit" name="action" value="Ingresar">Registrar&nbsp;&nbsp;<img src="/sistemaweb/icons/agregar.gif" alt="agregar"/></button></div>'));
		}
		
		// Datos invalidos
		if(count($inv)>0) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><div align="center"><h3>Datos invalidos</h3></div>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table align="center"><thead align="center" valign="center" ><tr class="grid_header">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2" class="grid_cabecera" >PROVEEDOR</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2" class="grid_cabecera" >ARTICULO</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" >UNIDAD</th>'));													
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" >MONEDA</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" >CANTIDAD</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" >COSTO UNIT.</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></thead>'));		
		
			for($i=0; $i<count($inv); $i++) {
				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				
				if($inv[$i][9]=="---") 
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100" class="'.$color.'">'.$inv[$i][0].'&nbsp;&nbsp;<img src="/sistemaweb/icons/bad.gif" alt="bad"/></td>'));
				else
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100" class="'.$color.'">'.$inv[$i][0].'&nbsp;&nbsp;<img src="/sistemaweb/icons/ok.gif" alt="ok"/></td>'));
					
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:220" class="'.$color.'">'.$inv[$i][9].'</td>'));

				if(substr($inv[$i][4],0,3)=="---")
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100" class="'.$color.'">'.substr($inv[$i][4],3).'&nbsp;&nbsp;<img src="/sistemaweb/icons/bad.gif" alt="bad"/></td>'));
				else 
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:100" class="'.$color.'">'.$inv[$i][4].'&nbsp;&nbsp;<img src="/sistemaweb/icons/ok.gif" alt="ok"/></td>'));
					
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:260" class="'.$color.'">'.$inv[$i][8].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:60" class="'.$color.'">'.$inv[$i][5].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:60" class="'.$color.'">'.$inv[$i][1].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:50" class="'.$color.'" align="right">'.$inv[$i][6].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td style="width:70" class="'.$color.'" align="right">'.$inv[$i][7].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));		    	
			}		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		}

		return $form->getForm();
    	}
}
