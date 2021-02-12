<?php

class AjusteInventarioTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Ajuste de Inventario Fisico</b></h2>';
	}

	function formSearch($almacenes, $ubicaciones) {


		$fregistro	= AjusteInventarioModel::FechaSistema();

        	$almacenes['SELE'] = "Seleccionar..";

		$form = new form2('',"Agregar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.AJUSTEINVENTARIO"));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'REGISTROS'));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_submit("submitButton\" style=\"visibility:hidden;\" id=\"submitButton", "submitButton", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<table border='0'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td align="right"><p style="font-family:Verdana,Arial,Helvetica,sans-serif; color:black; font-size:10px;"><b>Fecha Sistema: '));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td align="left"><p style="font-family:Verdana,Arial,Helvetica,sans-serif; color:black; font-size:10px;"><b>'.$fregistro['fecha'].''));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='width:50%;text-align:right;'>Almacen:</td><td style='width:50%;text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", ($_REQUEST['almacen'] == null ? "SELE" : $_REQUEST['almacen']), $almacenes, "","",array('onChange="Ubicaciones();"'),""));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td style="text-align:right;">'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Ubicaciones:</td><td style='width:50%;text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("", "ubica", $_REQUEST['ubica'], '</td><td style="text-align:right;">', '', '', $ubicaciones, false, ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="right">Orden:'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td align="left"><input type="radio" name="myorden" value="C" >C&oacute;digo'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="radio" name="myorden" value="D" checked>Descripci&oacute;n</td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center"><button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

		return $form->getForm();
	}

	function resultadosBusqueda($resultados, $almacen, $ubica) {

		$result = '';
		$result .= '<form><table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera"></th>';
		$result .= '<th class="grid_cabecera">CODIGO</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION</th>';
		$result .= '<th class="grid_cabecera">STOCK ACTUAL</th>';
		$result .= '<th class="grid_cabecera">STOCK FISICO</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_par");

			$a	= $resultados[$i];

			$fisico	= "fisico";
			$codigo = $a['codigo'];
			$stock	= "stockfisico";

			$result .= '<tr>';
			$result .= '<td class="'.$color.'" align="center"><input type="checkbox" id="'.$fisico.$i.'" name="t[]" value="'.$codigo.'" </td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['codigo']) . '</td>';
			$result .= '<td class="'.$color.'" align="left">' . htmlentities($a['producto']) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities((empty($a['stkact']) ? '0.00' : $a['stkact'])) . '</td>';
			$result .= '<td class="'.$color.'" align="right"><input id="'.$stock.$codigo.'" type="text" size="15" maxlength="15" onkeyup="return check(this.form, '."'t[]'".', \'' . $fisico.$i . '\', \'' . $stock.$codigo . '\');" onkeypress="return validar(event,2);"></td>';
			$result .= '</tr>';

		}

		$result .= '<tr>';
		$result .= '<td colspan="5" class="'.$color.'" align="right"><input id="proce" type="button" value="Procesar ..." onclick="Procesar(\' Esta seguro de Procesar ?\',this.form, '."'t[]'".', \'' . $almacen . '\', \'' . $ubica . '\', \''.$codigo.'\');" style="font-family:verdana; font-weight: bold; color:black; font-size:11px;" disabled /></td>';
		$result .= '</tr></table></form>';

		return $result;

	}

	function ReporteAjustes($resultados, $almacen, $codpro, $ubica) {

		$result = '';
		$result .= '<form><table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">CODIGO</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION</th>';
		$result .= '<th class="grid_cabecera">PRECIO VENTA</th>';
		$result .= '<th class="grid_cabecera">CONTABLE</th>';
		$result .= '<th class="grid_cabecera">FISICO</th>';
		$result .= '<th class="grid_cabecera">VARIACION</th>';
		$result .= '<th class="grid_cabecera">COSTO UNITARIO</th>';
		$result .= '<th class="grid_cabecera">COSTO TOTAL VARIACION</th>';
		$result .= '<th class="grid_cabecera">PRECIO TOTAL DIFERENCIA</th>';
		$result .= '</tr>';

		$totstkact	= 0;
		$totvaria	= 0;
		$totstock	= 0;
		$totcosto	= 0;
		$totventa	= 0;

		for ($i = 0; $i < count($resultados); $i++) {			

			$color = ($i%2==0?"grid_detalle_impar":"grid_detalle_par");

			$a = $resultados[$i];

			$result .= '<tr>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['codigo']) . '</td>';
			$result .= '<td class="'.$color.'" align="left">' . htmlentities($a['nombre']) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['precio'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities($a['varia']) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities($a['stkact']) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities($a['stock']) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['costo'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format(($a['costo'] * $a['stock']), 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['dife'], 2, '.', ',')) . '</td>';
			$result .= '</tr>';

			$totstkact	= $totstkact + $a['stkact'];
			$totvaria	= $totvaria + $a['varia'];
			$totstock	= $totstock + $a['stock'];
			$totcosto	= $totcosto + ($a['costo'] * $a['stock']);
			$totventa	= $totventa + $a['dife'];

		}


		$result .= '<tr>';
		$result .= '<td colspan="7" style="color:black; font-weight: bold; font-size:11px;" align="right">TOTAL: </td>';
//		$result .= '<td style="color:black; font-weight: bold; font-size:11px;" align="right" align="right">' . htmlentities($totstkact) . '</td>';
//		$result .= '<td style="color:black; font-weight: bold; font-size:11px;" align="right" align="right">' . htmlentities($totvaria) . '</td>';
//		$result .= '<td style="color:black; font-weight: bold; font-size:11px;" align="right" align="right">' . htmlentities($totstock) . '</td>';
//		$result .= '<td align="right"></td>';
		$result .= '<td style="color:black; font-weight: bold; font-size:11px;" align="right" align="right">' . htmlentities(number_format($totcosto, 2, '.', ',')) . '</td>';
		$result .= '<td style="color:black; font-weight: bold; font-size:11px;" align="right" align="right">' . htmlentities(number_format($totventa, 2, '.', ',')) . '</td>';
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<td colspan="10" class="'.$color.'" align="right"><b><input id="excel" type="button" value="Excel" onclick="Excel(\''.$almacen.'\',\''.$codpro.'\',\''.$ubica.'\');" style="font-family:verdana; font-weight: bold; color:green; font-size:12px;"/></td>';
		$result .= '</tr></table></form>';

		return $result;

	}

	function reporteExcel($res, $almacen, $ubica) {

		$nomalmacen	= AjusteInventarioModel::obtenerAlmacenes($almacen);
		$nomubicacion	= AjusteInventarioModel::ObtenerUbicaciones($almacen, $ubica);

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 50);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 16);
		$worksheet1->set_column(6, 6, 16);
		$worksheet1->set_column(7, 7, 30);
		$worksheet1->set_column(8, 8, 30);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "AJUSTE DE INVENTARIO FISICO",$formato0);
		$worksheet1->write_string(3, 0, "ALMACEN: ".$nomalmacen[$almacen],$formato0);
		$worksheet1->write_string(5, 0, "UBICACION: ".$nomubicacion[$ubica],$formato0);

		$a = 7;

		$worksheet1->write_string($a, 0, "CODIGO",$formato2);
		$worksheet1->write_string($a, 1, "DESCRIPCION",$formato2);
		$worksheet1->write_string($a, 2, "PRECIO VENTA",$formato2);
		$worksheet1->write_string($a, 3, "CONTABLE",$formato2);
		$worksheet1->write_string($a, 4, "FISICO",$formato2);
		$worksheet1->write_string($a, 5, "VARIACION",$formato2);
		$worksheet1->write_string($a, 6, "COSTO UNITARIO",$formato2);
		$worksheet1->write_string($a, 7, "COSTO TOTAL VARIACION",$formato2);
		$worksheet1->write_string($a, 8, "PRECIO TOTAL DIFERENCIA",$formato2);
		
		$a = 8;	

		for ($j=0; $j<count($res); $j++) {
			
			$worksheet1->write_string($a, 0, $res[$j]['codigo'],$formato5);
			$worksheet1->write_string($a, 1, $res[$j]['nombre'],$formato5);
			$worksheet1->write_number($a, 2, number_format($res[$j]['precio'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 3, $res[$j]['varia'],$formato5);
			$worksheet1->write_number($a, 4, $res[$j]['stkact'],$formato5);
			$worksheet1->write_number($a, 5, $res[$j]['stock'],$formato5);
			$worksheet1->write_number($a, 6, number_format($res[$j]['costo'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 7, number_format(($res[$j]['costo'] * $res[$j]['stock']),2,'.',''),$formato5);
			$worksheet1->write_number($a, 8, number_format($res[$j]['dife'],2,'.',''),$formato5);
			$a++;
		}
			
		$workbook->close();	

		$chrFileName = "AjusteFisico";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}

}
