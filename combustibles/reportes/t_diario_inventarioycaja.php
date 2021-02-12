<?php

class DiarioinventarioYCajaTemplate extends Template {

	function getTitulo() {
		return '<h2 align="center"><b>Cuadre diario de Inventarios y Caja</b></h2>';
    }
    
	function formPrincipal($arrAlmacenes, $nu_almacen, $fe_inicial, $fe_final) {
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.DIARIOINVENTARIOYCAJA'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacen: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-nu_almacen', '', $nu_almacen, $arrAlmacenes, espacios(3), array("onfocus" => "getFechasIF();"), ''));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("txt-fe_inicial", "", $fe_inicial, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<span class='msg-validacion_fe_inicial'></td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text("txt-fe_final", "", $fe_final, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<span class='msg-validacion_fe_final'></span></td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" align="center">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button id="btn-excel" name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button id="btn-pdf" name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</table>"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(
		'<script>
			window.onload = function() {
				parent.document.getElementById("cbo-nu_almacen").focus();
			}
		</script>'
		));

		return $form->getForm();
    }
    
    function gridViewHTML($arrCuadreDiarioInventarioYCaja, $nu_soles_gastos_varios, $nu_soles_creditos_clientes, $nu_soles_depositos_bancarios, $arrTarjetasCreditos, $nu_soles_depositos_pos) {
		$result = '';

		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">PRODUCTOS</th>';
				$result .= '<th class="grid_cabecera">INVENTARIO INICIAL LIBROS</th>';
				$result .= '<th class="grid_cabecera">INVENTARIO INICIAL FISICO</th>';
				$result .= '<th class="grid_cabecera">COMPRAS</th>';
				$result .= '<th class="grid_cabecera">VENTAS EN GALONES</th>';
				$result .= '<th class="grid_cabecera">VENTAS EN SOLES</th>';
				$result .= '<th class="grid_cabecera">INVENTARIO FINAL LIBROS</th>';
				$result .= '<th class="grid_cabecera">INVENTARIO FINAL FISICO</th>';
				$result .= '<th class="grid_cabecera">VARILLAJE</th>';
				$result .= '<th class="grid_cabecera">DIFERENCIAS LIBROS</th>';
				$result .= '<th class="grid_cabecera">DIFERENCIAS FISICAS</th>';
			$result .= '</tr>';

			$counter = 0;
			$tot_soles_venta = 0.00;
			if(count($arrCuadreDiarioInventarioYCaja) === 0) {
				$result .= '<tr class="bgcolor">';
					$result .= '<td class="grid_detalle_par" align ="center" colspan="12"><b>No hay registros</b></td>';
				$result .= '</tr>';
			} else {
				foreach ($arrCuadreDiarioInventarioYCaja as $rows) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
					$result .= '<tbody>';
			    	$result .= '<tr class="'. $color. '">';
				    	$result .= '<td align ="center">' . htmlentities($rows["no_producto"]) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($rows["nu_cantidad_inventario_inicial_kardex"], 4, '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($rows["nu_cantidad_inventario_inicial_varilla"], 4, '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($rows["nu_cantidad_compra"], 4, '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($rows["nu_cantidad_venta"], 3, '.', ',')) . '</td>';
				    	$result .= '<td align ="right">S/ ' . htmlentities(number_format($rows["nu_soles_venta"], 2, '.', ',')) . '</td>';
				    	$tot_soles_venta += $rows["nu_soles_venta"];
				    	$result .= '<td align ="right">' . htmlentities(number_format($rows["nu_cantidad_inventario_final_kardex"], 4, '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($rows["nu_cantidad_inventario_final_varilla"], 4, '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($rows["nu_cantidad_varilla"], 3, '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($rows["nu_cantidad_diferencia_kardex"], 4, '.', ',')) . '</td>';
				    	$result .= '<td align ="right">' . htmlentities(number_format($rows["nu_cantidad_diferencia_varilla"], 4, '.', ',')) . '</td>';
				    $result .= '</tr>';
				    $result .= '</tbody>';
				    $counter++;
				}
			}

			$result .= '<tr><td>&nbsp;</td></tr>';

			$result .= '<tr>';
				$result .= '<td class="bgcolor grid_detalle_par" colspan="3">&nbsp;</td>';
				$result .= '<td class="grid_cabecera">RESUMEN</td>';
				$result .= '<td class="grid_cabecera" align="right">TOTAL VENTAS</td>';
				$result .= '<td class="bgcolor grid_detalle_par" align="right">S/ ' . htmlentities(number_format($tot_soles_venta, 2, '.', ',')) . '</td>';
			$result .= '</tr>';

			$result .= '<tr>';
				$result .= '<td class="bgcolor grid_detalle_par" colspan="3">&nbsp;</td>';
				$result .= '<td class="grid_cabecera" align="right" colspan="2">GASTOS VARIOS</td>';
				$result .= '<td class="bgcolor grid_detalle_impar" align="right">S/ ' . htmlentities(number_format($nu_soles_gastos_varios, 4, '.', ',')) . '</td>';
			$result .= '</tr>';

			$result .= '<tr>';
				$result .= '<td class="bgcolor grid_detalle_par" colspan="3">&nbsp;</td>';
				$result .= '<td class="grid_cabecera" align="right" colspan="2">CREDITOS CLIENTES</td>';
				$result .= '<td class="bgcolor grid_detalle_par" align="right">S/ ' . htmlentities(number_format($nu_soles_creditos_clientes, 2, '.', ',')) . '</td>';
			$result .= '</tr>';

			$result .= '<tr>';
				$result .= '<td class="bgcolor grid_detalle_par" colspan="3">&nbsp;</td>';
				$result .= '<td class="grid_cabecera" align="right" colspan="2">DEPOSITOS BANCARIOS</td>';
				$result .= '<td class="bgcolor grid_detalle_impar" align="right">S/ ' . htmlentities(number_format($nu_soles_depositos_bancarios, 4, '.', ',')) . '</td>';
			$result .= '</tr>';

			$counter = 0;
			foreach ($arrTarjetasCreditos as $rows) {
				$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
		    	$result .= '<tr>';
		    		$result .= '<td class="bgcolor grid_detalle_par" colspan="3">&nbsp;</td>';
					$result .= '<td class="grid_cabecera" align="right" colspan="2">TARJETA CREDITO ' . htmlentities($rows["no_tipo_tarjeta_credito"]) . '</th>';
					$result .= '<td class="bgcolor ' . $color . '" align="right">S/ ' . htmlentities(number_format($rows["nu_soles_tarjeta_credito"], 4, '.', ',')) . '</td>';
			    $result .= '</tr>';
			    $counter++;
			}

			$result .= '<tr>';
				$result .= '<td class="bgcolor grid_detalle_par" colspan="3">&nbsp;</td>';
				$result .= '<td class="grid_cabecera" align="right" colspan="2">DINERO EN CAJA</td>';
				$result .= '<td class="bgcolor grid_detalle_impar" align="right">S/ ' . htmlentities(number_format($nu_soles_depositos_pos, 4, '.', ',')) . '</td>';
			$result .= '</tr>';
		$result .= '</table>';
		return $result;
    }
	
	function gridViewExcel($arrCuadreDiarioInventarioYCaja, $nu_soles_gastos_varios, $nu_soles_creditos_clientes, $nu_soles_depositos_bancarios, $arrTarjetasCreditos, $nu_soles_depositos_pos) {
		$chrFileName = "";

		$workbook = new Workbook($chrFileName);

		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato_string =& $workbook->add_format();
		$formato_numero =& $workbook->add_format();
		$resumen_formato =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_bottom(1);
		$formato2->set_bottom_color(8);
		$formato2->set_top(1);
		$formato2->set_top_color(8);
		$formato2->set_right(1);
		$formato2->set_right_color(8);
		$formato2->set_left(1);
		$formato2->set_left_color(8);
		$formato2->set_align('center');

		$formato5->set_size(11);
		$formato5->set_align('left');

		$formato_string->set_size(10);
		$formato_string->set_bottom(1);
		$formato_string->set_bottom_color(8);
		$formato_string->set_top(1);
		$formato_string->set_top_color(8);
		$formato_string->set_right(1);
		$formato_string->set_right_color(8);
		$formato_string->set_align('center');

		$formato_numero->set_size(10);
		$formato_numero->set_bottom(1);
		$formato_numero->set_bottom_color(8);
		$formato_numero->set_top(1);
		$formato_numero->set_top_color(8);
		$formato_numero->set_right(1);
		$formato_numero->set_right_color(8);
		$formato_numero->set_align('right');

		/* Total Resumen Formato de celda y font */
		$resumen_formato->set_size(10);
		$resumen_formato->set_bold(1);
		$resumen_formato->set_bottom(1);
		$resumen_formato->set_bottom_color(8);
		$resumen_formato->set_top(1);
		$resumen_formato->set_top_color(8);
		$resumen_formato->set_right(1);
		$resumen_formato->set_right_color(8);
		$resumen_formato->set_left(1);
		$resumen_formato->set_left_color(8);
		$resumen_formato->set_align('right');

		$worksheet1 =& $workbook->add_worksheet('Cuadre Diario');
		$worksheet1->set_column(0, 0, 15);
		$worksheet1->set_column(1, 1, 30);
		$worksheet1->set_column(2, 2, 35);
		$worksheet1->set_column(3, 3, 20);
		$worksheet1->set_column(4, 4, 25);
		$worksheet1->set_column(5, 5, 25);//VENTA SOLES
		$worksheet1->set_column(6, 6, 30);
		$worksheet1->set_column(7, 7, 30);
		$worksheet1->set_column(8, 8, 20);
		$worksheet1->set_column(9, 9, 30);
		$worksheet1->set_column(10, 10, 30);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 5, "CUADRE DIARIO DE INVENTARIOS Y CAJA", $formato0);

		$fila = 3;
		$worksheet1->write_string($fila, 0, "PRODUCTOS", $formato2);
		$worksheet1->write_string($fila, 1, "INVENTARIO INICIAL LIBROS", $formato2);
		$worksheet1->write_string($fila, 2, "INVENTARIO INICIAL FISICO", $formato2);
		$worksheet1->write_string($fila, 3, "COMPRAS", $formato2);
		$worksheet1->write_string($fila, 4, "VENTAS EN GALONES", $formato2);
		$worksheet1->write_string($fila, 5, "VENTAS EN SOLES", $formato2);
		$worksheet1->write_string($fila, 6, "INVENTARIO FINAL LIBROS", $formato2);
		$worksheet1->write_string($fila, 7, "INVENTARIO FINAL FISICO", $formato2);
		$worksheet1->write_string($fila, 8, "VARILLAJE", $formato2);
		$worksheet1->write_string($fila, 9, "DIFERENCIAS LIBROS", $formato2);
		$worksheet1->write_string($fila, 10, "DIFERENCIAS FISICAS", $formato2);

		$fila++;
		$tot_soles_venta = 0.00;
		if(count($arrCuadreDiarioInventarioYCaja) === 0) {
			$worksheet1->write_string($fila, 5, "No hay registros", $formato0);
		} else {
			foreach ($arrCuadreDiarioInventarioYCaja as $rows) {
				$worksheet1->write_string($fila, 0, $rows["no_producto"], $formato_string);
				$worksheet1->write_number($fila, 1, number_format($rows['nu_cantidad_inventario_inicial_kardex'], 4, '.', ''), $formato_numero);
				$worksheet1->write_number($fila, 2, number_format($rows['nu_cantidad_inventario_inicial_varilla'], 4, '.', ''), $formato_numero);
				$worksheet1->write_number($fila, 3, number_format($rows['nu_cantidad_compra'], 4, '.', ''), $formato_numero);
				$worksheet1->write_number($fila, 4, number_format($rows['nu_cantidad_venta'], 3, '.', ''), $formato_numero);
				$worksheet1->write_number($fila, 5, number_format($rows['nu_soles_venta'], 2, '.', ''), $formato_numero);
				$tot_soles_venta += $rows["nu_soles_venta"];
				$worksheet1->write_number($fila, 6, number_format($rows['nu_cantidad_inventario_final_kardex'], 4, '.', ''), $formato_numero);
				$worksheet1->write_number($fila, 7, number_format($rows['nu_cantidad_inventario_final_varilla'], 4, '.', ''), $formato_numero);
				$worksheet1->write_number($fila, 8, number_format($rows['nu_cantidad_varilla'], 3, '.', ''), $formato_numero);
				$worksheet1->write_number($fila, 9, number_format($rows['nu_cantidad_diferencia_kardex'], 4, '.', ''), $formato_numero);
				$worksheet1->write_number($fila, 10, number_format($rows['nu_cantidad_diferencia_varilla'], 4, '.', ''), $formato_numero);
				$fila++;
			}
		}

		$fila++;
		$fila++;

		$worksheet1->write_string($fila, 3, "RESUMEN", $formato2);
		$worksheet1->write_string($fila, 4, "TOTAL VENTAS", $resumen_formato);
		$worksheet1->write_number($fila, 5, number_format($tot_soles_venta, 3, '.', ''), $formato_numero);

		$fila++;
		$worksheet1->write_string($fila, 4, "GASTOS VARIOS", $resumen_formato);
		$worksheet1->write_number($fila, 5, number_format($nu_soles_gastos_varios, 4, '.', ''), $formato_numero);

		$fila++;
		$worksheet1->write_string($fila, 4, "CREDITOS CLIENTES", $resumen_formato);
		$worksheet1->write_number($fila, 5, number_format($nu_soles_creditos_clientes, 2, '.', ''), $formato_numero);

		$fila++;
		$worksheet1->write_string($fila, 4, "DEPOSITOS BANCARIOS", $resumen_formato);
		$worksheet1->write_number($fila, 5, number_format($nu_soles_depositos_bancarios, 4, '.', ''), $formato_numero);

		$fila++;
		foreach ($arrTarjetasCreditos as $rows) {
			$worksheet1->write_string($fila, 4, "TARJETA CREDITO " . $rows["no_tipo_tarjeta_credito"], $resumen_formato);
			$worksheet1->write_number($fila, 5, number_format($rows["nu_soles_tarjeta_credito"], 4, '.', ''), $formato_numero);
			$fila++;
		}

		$worksheet1->write_string($fila, 4, "DINERO EN CAJA", $resumen_formato);
		$worksheet1->write_number($fila, 5, number_format($nu_soles_depositos_pos, 4, '.', ''), $formato_numero);

		$workbook->close();	

		$chrFileName = "Cuadre_Diario_Inventarios_Y_Caja";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}

    function gridViewPDF($arrCuadreDiarioInventarioYCaja, $nu_soles_gastos_varios, $nu_soles_creditos_clientes, $nu_soles_depositos_bancarios, $arrTarjetasCreditos, $nu_soles_depositos_pos) {

		$cab = Array(
			"productos"						=>	"PRODUCTOS",
			"inventario_inicial_libros"		=>	"INVENTARIO INICIAL LIBROS",
			"inventario_inicial_fisico"		=>	"INVENTARIO INICIAL FISICO",
			"compras"						=>	"COMPRAS",
			"ventas_galones"				=>	"VENTAS EN GALONES",
			"ventas_soles"					=>	"VENTAS EN SOLES",
			"inventario_final_libros"		=>	"INVENTARIO FINAL LIBROS",
			"inventario_final_fisico"		=>	"INVENTARIO FINAL FISICO",
			"varillaje"						=>	"VARILLAJE",
			"diferencia_libros"				=>	"DIFERENCIAS LIBROS",
			"diferencia_fisicas"			=>	"DIFERENCIAS FISICAS",
		);

		$reporte = new CReportes2("L");
		//$reporte = new CReportes2("P","pt","A3");

		$reporte->Ln();	 
		$reporte->definirCabecera(2, "L", " ");
		$reporte->definirCabecera(2, "R", "Pagina %p");
		$reporte->definirCabeceraSize(3, "C", "courier,B,15", "Cuadre diario de Inventarios y Caja");
		$reporte->definirCabecera(6, "L", "");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 6.3);

		$reporte->definirColumna("productos",$reporte->TIPO_TEXTO,9,"L", "_pri");
		$reporte->definirColumna("inventario_inicial_libros",$reporte->TIPO_TEXTO,28,"R", "_pri");
		$reporte->definirColumna("inventario_inicial_fisico",$reporte->TIPO_TEXTO,28,"R", "_pri");
		$reporte->definirColumna("compras",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("ventas_galones",$reporte->TIPO_TEXTO,20,"R", "_pri");
		$reporte->definirColumna("ventas_soles",$reporte->TIPO_TEXTO,20,"R", "_pri");
		$reporte->definirColumna("inventario_final_libros",$reporte->TIPO_TEXTO,20,"R", "_pri");
		$reporte->definirColumna("inventario_final_fisico",$reporte->TIPO_TEXTO,20,"R", "_pri");
		$reporte->definirColumna("varillaje",$reporte->TIPO_TEXTO,10,"R", "_pri");
		$reporte->definirColumna("diferencia_libros",$reporte->TIPO_TEXTO,20,"R", "_pri");
		$reporte->definirColumna("diferencia_fisicas",$reporte->TIPO_TEXTO,20,"R", "_pri");

		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab, "_pri");
		$reporte->AddPage();
		$reporte->Ln();	

		$tot_soles_venta = 0.00;
		if(count($arrCuadreDiarioInventarioYCaja) === 0) {
			$arr = array(
				"compras" 		=> " ",
				"ventas_soles" 	=> "No hay registros",
			);
			$reporte->nuevaFila($arr, "_pri");
		} else {
			foreach ($arrCuadreDiarioInventarioYCaja as $rows) {
				$tot_soles_venta += $rows["nu_soles_venta"];
				$arr = array(
					"productos" 				=> $rows["no_producto"],
					"inventario_inicial_libros" => number_format($rows["nu_cantidad_inventario_inicial_kardex"], 4, '.', ','),
					"inventario_inicial_fisico" => number_format($rows["nu_cantidad_inventario_inicial_varilla"], 4, '.', ','),
					"compras" 					=> number_format($rows["nu_cantidad_compra"], 4, '.', ','),
					"ventas_galones" 			=> number_format($rows["nu_cantidad_venta"], 3, '.', ','),
					"ventas_soles" 				=> "S/ " . number_format($rows["nu_soles_venta"], 2, '.', ','),
					"inventario_final_libros" 	=> number_format($rows["nu_cantidad_inventario_final_kardex"], 4, '.', ','),
					"inventario_final_fisico" 	=> number_format($rows["nu_cantidad_inventario_final_varilla"], 4, '.', ','),
					"varillaje" 				=> number_format($rows["nu_cantidad_varilla"], 3, '.', ','),
					"diferencia_libros" 		=> number_format($rows["nu_cantidad_diferencia_kardex"], 4, '.', ','),
					"diferencia_fisicas" 		=> number_format($rows["nu_cantidad_diferencia_varilla"], 4, '.', ','),
				);
				$reporte->nuevaFila($arr, "_pri");
			}
		}

		$reporte->Ln();//Salto de linea
		$reporte->Ln();

		$arr2 = array(
			"compras" 					=> "RESUMEN",
			"ventas_galones" 			=> "TOTAL VENTAS",
			"ventas_soles" 				=> "S/ " . number_format($tot_soles_venta, 3, '.', ','),
		);
		$reporte->nuevaFila($arr2, "_pri");

		$arr3 = array(
			"compras" 					=> " ",
			"ventas_galones" 			=> "GASTOS VARIOS",
			"ventas_soles" 				=> number_format($nu_soles_gastos_varios, 4, '.', ','),
		);
		$reporte->nuevaFila($arr3, "_pri");

		$arr4 = array(
			"compras" 					=> " ",
			"ventas_galones" 			=> "CREDITOS CLIENTES",
			"ventas_soles" 				=> number_format($nu_soles_creditos_clientes, 2, '.', ','),
		);
		$reporte->nuevaFila($arr4, "_pri");

		$arr5 = array(
			"compras" 					=> " ",
			"ventas_galones" 			=> "DEPOSITOS BANCARIOS",
			"ventas_soles" 				=> number_format($nu_soles_depositos_bancarios, 4, '.', ','),
		);
		$reporte->nuevaFila($arr5, "_pri");

		foreach ($arrTarjetasCreditos as $rows) {
			$arr6 = array(
				"compras" 			=> " ",
				"ventas_galones" 	=> "TARJETA CREDITO " . $rows["no_tipo_tarjeta_credito"],
				"ventas_soles" 		=> number_format($rows["nu_soles_tarjeta_credito"], 4, '.', ','),
			);
			$reporte->nuevaFila($arr6, "_pri");
		}

		$arr7 = array(
			"compras" 			=> " ",
			"ventas_galones" 	=> "DINERO EN CAJA",
			"ventas_soles" 		=> number_format($nu_soles_depositos_pos, 4, '.', ','),
		);
		$reporte->nuevaFila($arr7, "_pri");

		$reporte->borrarCabecera();
		$reporte->borrarCabeceraPredeterminada();
		$reporte->Lnew();
		$reporte->Lnew();
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/Cuadre_Diario_Inventarios_Y_Caja.pdf", "F");

		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/Cuadre_Diario_Inventarios_Y_Caja.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}
}
