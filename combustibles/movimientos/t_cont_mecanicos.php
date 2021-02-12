<?php

class ContMecanicosTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Contometros Mecanicos</b></h2>';
    	}
    
	function formSearch($fecha, $fecha2, $estacion, $turno) {

		if ($estacion == "") 
			$estacion = $_SESSION['almacen'];

		$estaciones = ContMecanicosModel::obtenerSucursales("");
		$id = NULL;

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.CONTMECANICOS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="center">Sucursal: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 10,false,'onkeypress="return validar(event,3)"'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:center"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1500;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 10, false,'onkeypress="return validar(event,3)"'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:center"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1500;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">Turno: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("turno", "Turno:", $_REQUEST['turno'], '', 2, 2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<A href="#" onClick="window.open(\'reporte_por_manguera_mecanicos.php?id='.$id.'&estacion='.$_REQUEST['estacion'].'\', \'Datos del Articulo\' , \'width=500,height=600,scrollbars=NO,resizable=NO\');"><button><img border="0" src="/sistemaweb/icons/agregar.gif" align="right" />Nuevo Cont.</button></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Parte"><img src="/sistemaweb/images/search.png" align="right" />Generar Parte</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
			
		return $form->getForm();

    	}
    
    	function listado($resultados) {

		if($resultados == false){
			/*?><script>alert("<?php echo 'No hay contometros de la fecha: '.$fecha.' turno: '.$turno?> ");</script><?php*/
		}else{
			$result .= '<table align="center" border="0.01px" style="background:#B2C7D3">';
			$result .= '<tr>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>FECHA DE SISTEMA</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>TURNO</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>FECHA DE REGISTRO</b></th>';
			$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>MANGUERAS</b></th>';
			$result .= '</tr>';

			for ($i = 0; $i < count($resultados); $i++) {

				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_par");
				$a = $resultados[$i];

				$result .= '<tr bgcolor="">';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['fecha']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['turno']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['dia']) . '</td>';
				$result .= '<td class="'.$color.'">&nbsp;&nbsp;<A href="#" onClick="window.open(\'reporte_por_manguera_mecanicos.php?id='.$a['sistema'].'\', \'Datos del Articulo\' , \'width=450,height=550,scrollbars=NO,resizable=NO\');">'.$a['mangueras'].'</a>&nbsp;&nbsp;</td>';		
				$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar el registro con fecha '. htmlentities($a['fecha']).' y turno '. htmlentities($a['turno']).'?\',\'control.php?rqst=MOVIMIENTOS.CONTMECANICOS&action=Eliminar&systemdate='.($a['fecha']).'&shift='.($a['turno']).'&fecha='.$_REQUEST['fecha'].'&fecha2='.$_REQUEST['fecha2'].'&estacion='.$_REQUEST['estacion'].'&turno='.$_REQUEST['turno'].'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
				$result .= '</tr>';

			}

			$result .= '</table>';
		}

		return $result;
    	}

    	function ParteVenta($resultados,$fecha,$fecha2,$estacion,$turno) {

		$result .= '<table align="center" border="0.01px" style="background:#B2C7D3">';
		$result .= '<tr>';

		if(empty($resultados) || $resultados < 1){
			$result .= '<th colspan = "9" bgcolor="#FFFFFF" style="color:#3B677E"><font size=2px><b>No hay contometros Del '. htmlentities($fecha). ' Al '. htmlentities($fecha2). ' Del Turno '. htmlentities($turno).'</b></th>';
		}elseif(empty($turno)){
			$result .= '<th colspan = "9" bgcolor="#FFFFFF" style="color:#3B677E"><font size=2px><b>Parte de Venta Del '. htmlentities($fecha). ' Al '. htmlentities($fecha2). '</b></th>';
		}else{
			$result .= '<th colspan = "9" bgcolor="#FFFFFF" style="color:#3B677E"><font size=2px><b>Parte de Venta Del '. htmlentities($fecha). ' Al '. htmlentities($fecha2). ' Del Turno '. htmlentities($turno).'</b></th>';
		}
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>LADO</b></th>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>MANGUERA</b></th>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>CONT. INICIAL</b></th>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>CONT. FINAL</b></th>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>VTA. CANTIDAD</b></th>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>VTA. IMPORTE</b></th>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>PRECIO</b></th>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>PRODUCTO</b></th>';
		$result .= '</tr>';
		
		for ($i = 0; $i < count($resultados); $i++) {
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_par");
			$a = $resultados[$i];

			if($a['producto'] == "GLP"){
				$cantglp = $cantglp + $a['cantidad'];
				$totglp = $totglp + $a['importe'];
			}elseif($a['producto'] == "84 OCT"){
				$cant84 = $cant84 + $a['cantidad'];
				$tot84 = $tot84 + $a['importe'];
			}elseif($a['producto'] == "90 OCT"){
				$cant90 = $cant90 + $a['cantidad'];
				$tot90 = $tot90 + $a['importe'];
			}elseif($a['producto'] == "95 OCT"){
				$cant95 = $cant95 + $a['cantidad'];
				$tot95 = $tot95 + $a['importe'];
			}elseif($a['producto'] == "97 OCT"){
				$cant97 = $cant97 + $a['cantidad'];
				$tot97 = $tot97 + $a['importe'];
			}elseif($a['producto'] == "DIESEL DB5"){
				$cantdiesel = $cantdiesel + $a['cantidad'];
				$totdiesel = $totdiesel + $a['importe'];
			}

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['lado']) . '</td>';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['manguera']) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities($a['cntinicial']) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities($a['cntfinal']) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities($a['cantidad']) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities($a['importe']) . '</td>';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['precio']) . '</td>';
			$result .= '<td class="'.$color.'" align = "center">' . htmlentities($a['producto']) . '</td>';
			$result .= '</tr>';

		}

		$result .= '</table>';

		if(empty($resultados) || $resultados < 1){
		}else{
		$result .= '<table>';
			$result .= '<tr bgcolor="#FFFFFF"><td> </td></tr>';
			$result .= '<tr bgcolor="#FFFFFF"><td> </td></tr>';
			$result .= '<tr bgcolor="#FFFFFF"><td> </td></tr>';
			$result .= '<tr bgcolor="#FFFFFF"><td> </td></tr>';
			$result .= '<tr bgcolor="#FFFFFF"><td> </td></tr>';
			$result .= '<tr bgcolor="#FFFFFF"><td> </td></tr>';
			$result .= '<tr bgcolor="#FFFFFF"><td> </td></tr>';
		$result .= '</table>';

		$result .= '<table align="center" border="0.01px" style="background:#B2C7D3">';
		$result .= '<tr>';
		$result .= '<th colspan = "3" bgcolor="4682B4" span style="color:#FFFFFF"><p style="font-size:1.2em;"><b>RESUMEN</b></th>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>PRODUCTO</b></th>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>GALONES</b></th>';
		$result .= '<th bgcolor="4682B4" span style="color:#FFFFFF"><b>SOLES</b></th>';
		$result .= '</tr>';

		$result .= '<tr bgcolor="">';
		$result .= '<td class="'.$color.'" align = "center">GLP</td>';
		$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($cantglp, 2, '.', ',')) . '</td>';
		$result .= '<td class="'.$color.'" align = "center">' . htmlentities($totglp) . '</td>';
		$result .= '</tr>';
		$result .= '<tr bgcolor="">';
		$result .= '<td class="'.$color.'" align = "center">84 OCT</td>';
		$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($cant84, 2, '.', ',')) . '</td>';
		$result .= '<td class="'.$color.'" align = "center">' . htmlentities($tot84) . '</td>';
		$result .= '</tr>';
		$result .= '<tr bgcolor="">';
		$result .= '<td class="'.$color.'" align = "center">90 OCT</td>';
		$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($cant90, 2, '.', ',')) . '</td>';
		$result .= '<td class="'.$color.'" align = "center">' . htmlentities($tot90) . '</td>';
		$result .= '</tr>';
		$result .= '<tr bgcolor="">';
		$result .= '<td class="'.$color.'" align = "center">95 OCT</td>';
		$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($cant95, 2, '.', ',')) . '</td>';
		$result .= '<td class="'.$color.'" align = "center">' . htmlentities($tot95) . '</td>';
		$result .= '</tr>';
		$result .= '<tr bgcolor="">';
		$result .= '<td class="'.$color.'" align = "center">97 OCT</td>';
		$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($cant97, 2, '.', ',')) . '</td>';
		$result .= '<td class="'.$color.'" align = "center">' . htmlentities($tot97) . '</td>';
		$result .= '</tr>';
		$result .= '<tr bgcolor="">';
		$result .= '<td class="'.$color.'" align = "center">DIESEL DB5</td>';
		$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($cantdiesel, 2, '.', ',')) . '</td>';
		$result .= '<td class="'.$color.'" align = "center">' . htmlentities($totdiesel) . '</td>';
		$result .= '</tr>';
	
		//TOTAL DE CANTIDAD E IMPORTE
		$totalcant = $cantglp + $cant84 + $cant90 + $cant95 + $cant97 + $cantdiesel;
		$totalimp = $totglp + $tot84 + $tot90 + $tot95 + $tot97 + $totdiesel;

		$result .= '<tr>';
		$result .= '<td bgcolor="#FFFFFF" style="color:black" align = "right"><p style="font-size:1.2em;"><b>TOTAL: </td>';
		$result .= '<td bgcolor="#FFFFFF" style="color:black" align = "right"><p style="font-size:1.2em;"><b>' . htmlentities(number_format($totalcant, 2, '.', ',')) . '</td>';
		$result .= '<td bgcolor="#FFFFFF" style="color:black" align = "right"><p style="font-size:1.2em;"><b>' . htmlentities(number_format($totalimp, 2, '.', ',')) . '</td>';
		$result .= '</tr>';

		$result .= '</table>';
		}
		return $result;
    	}
}
