<?php

class ReporteGeneralTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2><b>Estado de Cuenta General</b></h2></div>';
	}

	function formBuscar(){

		$precancelado = Array("N"=>"NO", "S"=>"SI");
		$dia_vencimiento = Array("N"=>"NO", "S"=>"SI");
		$categoria = Array("AC"=>"ACTIVOS", "JU"=>"EN JUICIO", "IN"=>"INACTIVOS", "T"=>"TODOS");
		$porgrupo = Array("GRUPOEMP"=>"Grupo Empresarial", "CLIENTE"=>"Cliente");
		$cliente = Array("S"=>"SI", "N"=>"NO");

		$hoy = date("d/m/Y");

		if($cod_moneda==""){$cod_moneda="02";}
		$c_tasa_cambio = tipoCambio($cod_moneda,$hoy);

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.GENERALES'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="5" bordercolor="white" CELLSPACING="2">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><tr BGCOLOR="#30767F"><td><font color="white" size="1" face="courier new">PRECANCELADO (S/N):</font></td><td>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("c_precancelado", "", "", $precancelado, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>&nbsp</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><tr BGCOLOR="#30767F"><td><font color="white" size="1" face="courier new">HASTA LA FECHA:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "c_fecha_hasta", date("d/m/Y"), '<a href="javascript:show_calendar(\'Buscar.c_fecha_hasta\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>', '', 10, 10, false));							
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td><input type="radio" name="condicion" value="seriedocumento"><font color="white" size="1" face="courier new">DOCUMENTOS'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><tr BGCOLOR="#30767F"><td><font color="white" size="1" face="courier new">DIAS DE VENCIMIENTO (S/N):</td><td>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("c_dias_vcmt", "", "", $dia_vencimiento, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td rowspan = "3"><div id="space" align="center" />&nbsp;</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr BGCOLOR="#30767F"><td><font color="white" size="1" face="courier new">TASA DE CAMBIO</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('c_tasa_cambio','', $c_tasa_cambio, '', 5, 5,array("class"=>"form_input_numeric"), ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr BGCOLOR="#30767F"><td><font color="white" size="1" face="courier new">CATEGORIA</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("c_categoria", "", "", $categoria, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr BGCOLOR="#30767F"><td><font color="white" size="1" face="courier new">POR GRUPO EMPRESARIAL O POR CLIENTE: </td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("c_grupoemp_cliente", "", "", $porgrupo, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>&nbsp</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr BGCOLOR="#30767F"><td><font color="white" size="1" face="courier new">TODOS LOS CLIENTES:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("c_todos_clientes", "", "", $cliente, "",array("onChange"=>"display_id_cliente(this);")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td><font color="white" size="1" face="courier new">CLIENTE: '));	

		$form->addElement(FORM_GROUP_MAIN, new form_element_text('','codcliente','', $_REQUEST['codcliente'], '', 20, 18,false,'onkeypress="return validar(event,3)"'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr BGCOLOR="#30767F"><td><font color="white" size="1" face="courier new">CODIGO SERIE DE DOCUMENTO: </td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('','c_serie', '',$serie, '', 15, 15, false,'onkeypress="return validar(event,3)"'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td><font color="white" size="1" face="courier new">Mostrar Vales</font>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('chk_vales','','1','',4,4));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr BGCOLOR="#30767F"><td>&nbsp</td><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Reporte">Reporte</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>&nbsp</td>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));

		return $form->getForm();
	}
	
			
	function mostrar($resta,$res,$vale,$cliente,$porgrupo) {

		$grupo_emp =  "";
		$sumtotGRP = 0;
		$totalventa = 0;

		$form = new form2('', 'Form_Mostrar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.GENERAL"));

		if($porgrupo == "GRUPOEMP"){
		
			$sumatodo = 0;

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table><tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;CLIENTE&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;DOCUMENTO&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;N. LIQUIDACION&nbsp;&nbsp;</th>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;FECHA&nbsp;&nbsp;</th>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;MON&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;IMPORTE&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;FECHA&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;DOLARES&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;SOLES&nbsp;&nbsp;</th>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;Nro. Vale&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;Nro. Liquidacion&nbsp;</th>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;EMISION&nbsp;&nbsp;</th>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;Moneda&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;Importe&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;VENCIMIENTO&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" colspan = "2">&nbsp;&nbsp;&nbsp;</th>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			for ($i = 0; $i < count($res); $i++) {
			
				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

					if($grupo_emp != $res[$i]['grupo']){

						if($i!= 0){

							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="8" align="right" class="grid_detalle_total" colspan="4">* TOTAL CLIENTE *</td>'));

							if($tipond == '20'){
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumtotGRP - $sumatotDNGRP, 2, '.', ',').'</td></tr>'));
								$sumatotDNGRP = 0;
								$sumtotGRP = 0;
							}else{
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumtotGRP, 2, '.', ',').'</td></tr>'));
								$sumtotGRP = 0;
							}

						}

						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="left" class="grid_detalle_especial">'.$res[$i]['grupo'].'</td></tr>'));
						$grupo_emp = $res[$i]['grupo'];				
					} 

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="left" class="grid_detalle_especial">&nbsp</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['documento']) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['num_documento']) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['fechaemision']) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['monetotal']) . '</td>'));

				if($res[$i]['tipo'] == '20')
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;-' . htmlentities(number_format($res[$i]['importe'], 2, '.', ',')) . '</td>'));
				else
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['importe'], 2, '.', ',')) . '</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['fechavencimiento']) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">0</td>'));

				if($res[$i]['tipo'] == '20')
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;-' . htmlentities(number_format($res[$i]['saldo'], 2, '.', ',')) . '</td>'));
				else
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['saldo'], 2, '.', ',')) . '</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

				if($res[$i]['tipo'] == '20'){
					$tipond = $res[$i]['tipo'];
					$sumatotDNGRP = $sumatotDNGRP + $res[$i]['importe'];
					$sumatodoND = $sumatodoND + $res[$i]['importe'];
				}else{
					$sumtotGRP = $sumtotGRP + $res[$i]['saldo'];
					$sumatodo = $sumatodo + $res[$i]['saldo'];//suma total por documentos
				}


			}		

			//SUMA DE TOTALES
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* TOTAL GENERAL *</td>'));

			if($tipond == '20')
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumatodo - $sumatodoND, 2, '.', ',').'</td>'));
			else
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumatodo, 2, '.', ',').'</td>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* DOLARES *</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">0</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* SOLES *</td>'));

			if($tipond == '20')
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumatodo - $sumatodoND, 2, '.', ',').'</td>'));
			else
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumatodo, 2, '.', ',').'</td>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* TOTAL *</td>'));

			if($tipond == '20')
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumatodo - $sumatodoND, 2, '.', ',').'</td>'));
			else
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumatodo, 2, '.', ',').'</td>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table></center>'));
		

		}else{
		
			$sumtot = 0;
			$sumtotval = 0;
			$grupo_cli =  "";


			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table><tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;CLIENTE&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;DOCUMENTO&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;N. LIQUIDACION&nbsp;&nbsp;</th>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;FECHA&nbsp;&nbsp;</th>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;MON&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;IMPORTE&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;FECHA&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;DOLARES&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;SOLES&nbsp;&nbsp;</th>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;Nro. Vale&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;Nro. Liquidacion&nbsp;</th>'));		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;EMISION&nbsp;&nbsp;</th>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;Moneda&nbsp;&nbsp;</th>'));				
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;Importe&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;VENCIMIENTO&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera" colspan = "2">&nbsp;&nbsp;&nbsp;</th>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			for ($i = 0; $i < count($res); $i++) {

				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

				if($grupo_cli != $res[$i]['grupo']){
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="left" class="grid_detalle_especial">'.$res[$i]['grupo'].'</td></tr>'));
					$grupo_cli = $res[$i]['grupo'];
				} 

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="left" class="grid_detalle_especial">&nbsp</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['documento']) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['num_documento']) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['fechaemision']) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['monetotal']) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['importe'], 2, '.', ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['fechavencimiento']) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">0</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['saldo'], 2, '.', ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

				//SUMA TOTAL GENERAL
				if($res[$i]['tipo'] == '20'){
					$tipond = $res[$i]['tipo'];
					$sumatotnd = $sumatotnd + $res[$i]['saldo'];
					$sumtotND = $sumtotND + $res[$i]['saldo'];//suma total por documentos
				}else{
					$sumatotdoc = $sumatotdoc + $res[$i]['saldo'];
					$sumtot = $sumtot + $res[$i]['saldo'];//suma total por documentos
				}

				if($res[$i]['grupo'] != $res[$i+1]['grupo']){
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="8" align="right" class="grid_detalle_total" colspan="4">* TOTAL DOCUMENTOS *</td>'));
					if($res[$i]['tipo'] == '20'){//suma total por documentos
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumtot - $sumtotND, 2, '.', ',').'</td></tr>'));
						$suma_doc = $sumtot - $sumtotND;
					}else{//suma total por documentos
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumtot, 2, '.', ',').'</td></tr>'));
						$suma_doc = $sumtot;
					}
					$sumtot = 0;
				}

				for($j = 0; $j < count($resta); $j++) {

					if($resta[$j]['grupo'] == $grupo_cli and $resta[$j]['grupo'] != $res[$i+1]['grupo']){

						$cli_val[$j] = $resta[$j]['grupo'];

						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="left" class="grid_detalle_especial">&nbsp</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($resta[$j]['documentoval']) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">-</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($resta[$j]['fecha']) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">-</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($resta[$j]['importeval'], 2, '.', ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">-</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">0</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($resta[$j]['importeval'], 2, '.', ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

						//suma total vales
						$sumtotval = $sumtotval + $resta[$j]['importeval'];
						$sumtodoval = $sumtodoval + $resta[$j]['importeval'];

						//}
					}

				}


				if($res[$i]['grupo'] != $res[$i+1]['grupo']){

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="8" align="right" class="grid_detalle_total" colspan="4">* TOTAL VALES *</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumtotval, 2, '.', ',').'</td></tr>'));
					$suma_val = $sumtotval;
					$suma_clientes = $suma_doc + $suma_val;
					$suma_todo_clientes = $suma_todo_clientes + $suma_clientes; 
					$sumtotval = 0;
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "8" align="right" class="grid_detalle_total" colspan="4">* TOTAL CLIENTES *</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($suma_clientes, 2, '.', ',').'</td></tr>'));
				}

				$sumtodo = $sumtotval + $sumtot;
	
			}
			
			$sumavales = 0;			

			for ($j = 0; $j < count($resta); $j++) {

				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

					if($grupo_clival != $resta[$j]['grupo']){

						if($resta[$j]['grupo'] != $cli_val[$j]){

							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="left" class="grid_detalle_especial">'.$resta[$j]['grupo'].'</td></tr>'));
							$grupo_clival = $resta[$j]['grupo'];

						}

					}

					if($resta[$j]['grupo'] != $cli_val[$j] and $grupo_clival != $cli_val[$j]) {

						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="left" class="grid_detalle_especial">&nbsp</td>'));

						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($resta[$j]['documentoval']) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">-</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($resta[$j]['fecha']) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">-</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($resta[$j]['importeval'], 2, '.', ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">-</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">0</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($resta[$j]['importeval'], 2, '.', ',')) . '</td>'));

						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

						//suma total por documentos
						$sumavales = $sumavales + $resta[$j]['importeval'];

					}

					if($j!= 0 and $resta[$j]['grupo'] != $cli_val[$j] and $resta[$j]['grupo'] != $resta[$j+1]['grupo']){

							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="8" align="right" class="grid_detalle_total" colspan="4">* TOTAL VALES *</td>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumavales, 2, '.', ',').'</td></tr>'));
							$suma_todoval = $suma_todoval + $sumavales;
							$sumavales = 0;

					}

			}

			//suma de todos los clientes empresariales
			//$total_todo = $sumtodocliente + $sumtodo;

			if($vale == '1'){
				$total_todo = ($suma_todo_clientes + $suma_todoval) - $sumatotnd;
			}else{
				if($tipond == '20')
					$total_todo = $sumatotdoc - $sumatotnd;					
				else
					$total_todo = $sumatotdoc;
			}

			//suma de todos los clientes
			if($tipond == '20')
				$sumtodocliente = ($sumatotdoc + $suma_todoval) - $sumatotnd;
			else
				$sumtodocliente = $sumatotdoc + $suma_todoval;

			if($cliente == 'S'){

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* TOTAL GENERAL *</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($total_todo, 2, '.', ',').'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* DOLARES *</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">0</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* SOLES *</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($total_todo, 2, '.', ',').'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* TOTAL *</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($total_todo, 2, '.', ',').'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table></center>'));
			}else{
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* TOTAL GENERAL *</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumtodocliente, 2, '.', ',').'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* DOLARES *</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">0</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* SOLES *</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumtodocliente, 2, '.', ',').'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="7" align="center" class="grid_detalle_total">&nbsp;</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="grid_detalle_total">* TOTAL *</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.number_format($sumtodocliente, 2, '.', ',').'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table></center>'));
			}
		

		}
		
		return $form->getForm();
			
    	}	    	

	function listaSeries() {
		$series = ReporteGeneralModel::obtenerSeries();
		$result = '<select name="seriesdocs[]" size="7" multiple>';	

		for ($t = 0; $t<count($series); $t++) {
			$ser = trim($series[$t]['cod_docu']);
			$doc = trim($series[$t]['desc_docu']);
			$result .= '<option value="'.$ser.'">'.$ser.' - '.$ser.' '.$doc.'</option>';		
		}			

		$result .= '</select>';

		return $result;
	}

}
