<?php

class VentasEspecialesTemplate extends Template	{

	function listaLineas($cod) {
		$lineas = VentasEspecialesModel::obtenerLineas($cod);

		$result = 'Codigo: <input type="text" id="cod_busca" name="cod_busca"/><button type="button" name="buscar" onclick="cargarListaBusqueda(\'LINEA\');">Buscar</button><br>
			   Ordenar:<input type="radio" name="ordenar" value="codigo" onclick="cargarListaOrdenarCodigo(\'LINEA\');" '.('Codigo' == $cod  ? ' checked': '').'>codigo
				<input type="radio" name="ordenar" value="descripcion" onclick="cargarListaOrdenarDescripcion(\'LINEA\');" '.('Descripcion' == $cod  ? ' checked': '').'>descripcion
				<br/><select name="linea[]" size="7" multiple>';
			
		foreach($lineas as $cod_linea => $descripcion) {
			$result .= '<option value="' . htmlentities($cod_linea) . '">' . htmlentities($descripcion) . '</option>';
		}
			
		$result .= '</select>';
		return $result;
	}
		
	function listaTipos($cod) {
		$tipos = VentasEspecialesModel::obtenerTipos($cod);
		$result = 'Codigo:<input type="text" id="cod_busca" name="cod_busca"/><button type="button" name="buscar" onclick="cargarListaBusqueda(\'TIPO\');">Buscar</button><br>
			   Ordenar:<input type="radio" id="ordenar" name="ordenar" value="codigo" onclick="cargarListaOrdenarCodigo(\'TIPO\');" '.('Codigo' == $cod  ? ' checked': '').'>codigo
				<input type="radio" id="ordenar" name="ordenar" value="descripcion" onclick="cargarListaOrdenarDescripcion(\'TIPO\');" '.('Descripcion' == $cod  ? ' checked': '').'>descripcion
				<br/><select name="tipo[]" size="7" multiple>';
			
		foreach($tipos as $cod_tipo => $descripcion) {
			$result .= '<option value="' . htmlentities($cod_tipo) . '">' . htmlentities($descripcion) . '</option>';
		}
		
		$result .= '</select>';
		return $result;
	}
		
	function listaArticulos($cod) {
		$articulos = VentasEspecialesModel::obtenerArticulos($cod);
		$result = 'Codigo:<input type="text" id="cod_busca" name="cod_busca"/><button type="button" name="buscar" onclick="cargarListaBusqueda(\'ARTICULO\');">Buscar</button><br>
			   Ordenar:<input type="radio" name="ordenar" value="codigo" onclick="cargarListaOrdenarCodigo(\'ARTICULO\');" '.('Codigo' == $cod  ? ' checked': '').'>codigo
				<input type="radio" name="ordenar" value="descripcion" onclick="cargarListaOrdenarDescripcion(\'ARTICULO\');" '.('Descripcion' == $cod  ? ' checked': '').'>descripcion
				<br/><select name="codigo[]" size="7" multiple>';
			
		foreach($articulos as $cod_articulo => $descripcion) {
			$result .= '<option value="' . htmlentities($cod_articulo) . '">' . htmlentities($descripcion) . '</option>';
		}
			
		$result .= '</select>';
		return $result;
	}

	function titulo() {
		return '<h2 align="center"><b>Ventas Especiales</b></h2>';
	}
		
	function formSearch($almacenes, $desde, $hasta) {

		if($desde=="" or $hasta==""){
			$desde	 = date("d/m/Y");
			$hasta	 = date("d/m/Y");
		}

		$result  = '';
		$result .= '<center><form name="reporte" method="post" action="control.php" target="control">';
		$result .= '<input type="hidden" name="rqst" value="REPORTES.VENTASESPECIALES">';
		$result .= '<table border="0" bordercolor="#cccc99">';
		$result .= '<tr>';
		//$result .= '<td align="right" width="220">Desde:<input type="text" name="desde" value="' . htmlentities($desde) . '" size="12"><a href="javascript:show_calendar('."'reporte.desde'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></td>';
		$result .= '<td align="right" width="220">Desde:<input type="text" id="txt-date-ini" name="desde" value="' . htmlentities($desde) . '" size="12"></td>';
		//$result .= '<td colspan="3" align="left" width="220">Hasta:<input type="text" name="hasta" value="' . htmlentities($hasta) . '" size="12"><a href="javascript:show_calendar('."'reporte.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></td>';
		$result .= '<td colspan="3" align="left" width="220">Hasta:<input type="text" id="txt-date-fin" name="hasta" value="' . htmlentities($hasta) . '" size="12"></td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td><div align="right">Almacen</div></td>';
		$result .= '<td colspan="3"><select id="estacion" name="exalma" onfocus="getFechaEmision();">';
		foreach ($almacenes as $exalma => $nexalma)
			if ($exalma=="TODOS")
				$result .= "<option value=\"TODOS\">TODOS</option>";
			else
				$result .= "<option value=\"{$exalma}\">{$nexalma}</option>";
		$result .= '</select></td>';
		$result .= '</tr>'; 
		$result .= '<tr>';
		$result .= '<td><div align="right">Detallado por d&iacute;a</div></td>';
		$result .= '<td colspan="3"><input type="checkbox" name="detallado" value="S"></td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td><div align="right">Totales con IGV:</div></td>';
		$result .= '<td colspan="3"><input type="checkbox" name="conigv" value="S"></td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td><div align="right">Forma del reporte:</div></td>';
		$result .= '<td colspan="3"><input type="radio" name="forma" value="resumido" checked>Resumido <input type="radio" name="forma" value="detallado">Detallado</td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td><div align="right"><b>Ordenado por:</b></div></td>';
		$result .= '<td><input type="radio" name="orden" value="Total">Total</td>';
		$result .= '<td><input type="radio" name="orden" value="almacen">Almacen</td>';
		$result .= '<td><input type="radio" name="orden" value="articulo">Articulo</td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td><div align="right"><b>Condicion:</b></div></td>';
		$result .= '<td><input type="radio" name="condicion" value="tipo" onclick="cargarListaEspeciales(\'TIPO\');">Tipo</td>';
		$result .= '<td><input type="radio" name="condicion" value="linea" onclick="cargarListaEspeciales(\'LINEA\');">Linea</td>';
		$result .= '<td><input type="radio" name="condicion" value="articulo" onclick="cargarListaEspeciales(\'ARTICULO\');">Articulo</td>';
		$result .= '</tr>';
		$result .= '<td colspan="7"><div id="space" align="center">&nbsp;</td>';
		$result .= '<tr>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td colspan="6"><div align="center"><button type="submit" name="action" value="Buscar"><img src="/sistemaweb/images/search.png" alt="left" />  Buscar</button>';
		$result .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" name="action" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button>';
		$result .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" name="action" value="ExcelTotal"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel Totales</button></td>';
		$result .= '</tr>';
		$result .= '</table><script>window.onload = function() {parent.document.getElementById("estacion").focus();}</script>';
		$result .= '</form></center>';
		return $result;
	}
		
	function reporte($resultado, $conigv){

		$nColumnas	= 2 + 2 * (count($resultado['almacenes']));
		$result		= '';
			
		if ($conigv == "S")
			$factor = 1.18;
		else
			$factor = 1;
		
		$result .= '<center><table border="0">';
		$result .= '<tr>';
		$result .= '<td colspan="2" bgcolor="#E3CEF6" align="center" style="font-weight:bold">&nbsp;</td>';
			
		foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
			$result .= '<td colspan="2" bgcolor="#E3CEF6" align="center" style="font-size:0.7em; color:black;"><STRONG>' . htmlentities($ch_nombre_almacen) . '</td>';
		}

		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td bgcolor="#E3CEF6" align="center" style="font-size:0.7em; color:black;" width="140"><STRONG>CODIGO</td>';
		$result .= '<td bgcolor="#E3CEF6" align="center" style="font-size:0.7em; color:black;" width="320"><STRONG>DESCRIPCION</td>';
		
		for ($i = 0; $i < count($resultado['almacenes']); $i++) {
			$result .= '<td bgcolor="#E3CEF6" align="center" style="font-size:0.7em; color:black;"><STRONG>CANTIDAD</td>';
			$result .= '<td bgcolor="#E3CEF6" align="center" style="font-size:0.7em; color:black;"><STRONG>IMPORTE</td>';
		}
			
		$result .= '</tr>';

		foreach($resultado['fechas'] as $dt_fac_fecha => $lineas) {
			// Dias
			$result .= '<tr>';

			$result .= '<td colspan="' . $nColumnas . '" bgcolor="#f4fa58" align="center" style="font-size:0.7em; color:red">' . htmlentities($dt_fac_fecha) . '</td>';

			$result .= '</tr>';
			
			// lineass
			foreach ($lineas['lineas'] as $art_linea_desc => $linea) {
				$result .= '<tr>';
				$result .= '<td colspan="' . $nColumnas . '" bgcolor="#a9f5f2" style="font-size:0.7em; color:black;"><STRONG>***LINEA: '.htmlentities($art_linea_desc). ' ***</td>';
				$result .= '</tr>';
					
				foreach($linea['codigos'] as $art_codigo => $codigo) {
					// Codigos
					$result .= '<tr>';
					$result .= '<td bgcolor="white" style="font-weight:bold" align="center">' . htmlentities($art_codigo) .'</td>';
					$result .= '<td bgcolor="white" align="left" style="font-weight:bold">' . htmlentities($codigo['descripcion']) . '</td>';
					foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
						$result .= '<td bgcolor="white" align="right" style="font-weight:bold">' . htmlentities(number_format($codigo[$ch_almacen."_cantidad"], 2, '.', ' ')) . '</td>';
						if($conigv == "S")
							$result .= '<td bgcolor="white" align="right" style="font-weight:bold">' . htmlentities(number_format($codigo[$ch_almacen."_total"], 2, '.', ' ')) . '</td>';
						else
							$result .= '<td bgcolor="white" align="right" style="font-weight:bold">' . htmlentities(number_format($codigo[$ch_almacen."_neto"], 2, '.', ' ')) . '</td>';
					}
					$result .= '</tr>';
				}
				$result .= '<tr>';
				$result .= '<td colspan="2" style="font-size:0.7em; color:black;" align="right"><STRONG>TOTAL LINEA</STRONG></td>';
				foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
					$result .= '<td bgcolor="white" align="right" style="Font-size:0.7em; font-weight:bold">' . htmlentities(number_format($linea[$ch_almacen."_cantidad"], 2, '.', ' ')) . '</td>';
					if($conigv == "S")
						$result .= '<td bgcolor="white" align="right" style="font-size:0.7em; font-weight:bold">' . htmlentities(number_format($linea[$ch_almacen."_total"], 2, '.', ' ')) . '</td>';
					else
						$result .= '<td bgcolor="white" align="right" style="font-size:0.7em; font-weight:bold">' . htmlentities(number_format($linea[$ch_almacen."_neto"], 2, '.', ' ')) . '</td>';
				}
		        }
				
			$result .= '<tr>';
			$result .= '<td colspan="2" style="font-size:0.7em; color:blue; font-weight:bold" align="right">TOTAL DIA</td>';
			
			foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
				$result .= '<td style="font-size:0.7em; color:blue; font-weight:bold" align="right" width="130">&nbsp;' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha][$ch_almacen."_cantidad"], 2, '.', ' ')) . '</td>';
				if($conigv == "S"){
					$result .= '<td style="font-size:0.7em; color:blue; font-weight:bold" align="right" width="130">&nbsp;' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha][$ch_almacen."_total"], 2, '.', ' ')) . '</td>';
				}else{
					$result .= '<td style="font-size:0.7em; color:blue; font-weight:bold" align="right" width="130">&nbsp;' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha][$ch_almacen."_neto"], 2, '.', ' ')) . '</td>';
				}
				
			}

		}			
		$result .= '<tr>';
		$result .= '<td colspan="2" bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right">TOTAL GENERAL</td>';
			
		foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
			$result .= '<td bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right" width="130">&nbsp;' . htmlentities(number_format($resultado[$ch_almacen.'_cantidad'], 2, '.', ' ')) . '</td>';
				if($conigv == "S"){
					$result .= '<td bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right" width="130">&nbsp;' . htmlentities(number_format($resultado[$ch_almacen.'_total'], 2, '.', ' ')) . '</td>';
				}else{
					$result .= '<td bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right" width="130">&nbsp;' . htmlentities(number_format($resultado[$ch_almacen.'_neto'], 2, '.', ' ')) . '</td>';
				}
		}

		$result .= '</tr>';
		$result .= '</table></center>';
		
		return $result;
	}

	function reporteExcel($res, $almacen, $desde, $hasta, $igv) {

		$chrFileName = "";

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

		$worksheet1 =& $workbook->add_worksheet('Ventas Especiales');
		$worksheet1->set_column(0, 0, 6);
		$worksheet1->set_column(1, 1, 6);
		$worksheet1->set_column(2, 2, 6);
		$worksheet1->set_column(3, 3, 10);
		$worksheet1->set_column(4, 4, 20);
		$worksheet1->set_column(5, 5, 11);
		$worksheet1->set_column(6, 6, 30);
		$worksheet1->set_column(7, 7, 10);
		$worksheet1->set_column(8, 8, 10);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);
		
		$worksheet1->write_string(1, 0, "VENTAS POR PRODUCTO",$formato0);
		$worksheet1->write_string(3, 0, "FECHA DEL ".$desde." AL ".$hasta,$formato0);
		$worksheet1->write_string(4, 0, " ",$formato0);

		$a = 5;//11	
		$alma = "";

		$d = 1;//2
		$e = 3;//3

		for ($j=0; $j<count($res); $j++){

			if($alma != $res[$j]['almacen']){

				$c = ($a + $d);
				$b = ($a + $e);

				$nomalmacen = VentasEspecialesModel::obtenerSucursales($res[$j]['almacen']);

				$worksheet1->write_string($c, 0, "ALMACEN: ".$res[$j]['almacen']." - ".$nomalmacen[$res[$j]['almacen']],$formato0);
				$alma = $res[$j]['almacen'];

				$worksheet1->write_string($b, 0, "ANO",$formato2);
				$worksheet1->write_string($b, 1, "MES",$formato2);
				$worksheet1->write_string($b, 2, "DIA",$formato2);
				$worksheet1->write_string($b, 3, "COD. LINEA",$formato2);	
				$worksheet1->write_string($b, 4, "LINEA",$formato2);
				$worksheet1->write_string($b, 5, "COD. PRODUCTO",$formato2);
				$worksheet1->write_string($b, 6, "PRODUCTO",$formato2);
				$worksheet1->write_string($b, 7, "UNIDADES VENTA S/.",$formato2);
				$worksheet1->write_string($b, 8, "VENTAS S/.",$formato2);
				$worksheet1->write_string($b, 9, "COSTO VENTA",$formato2);
				$worksheet1->write_string($b, 10, "STOCK ACTUAL",$formato2);

				$a = $a + 4;

			}

			$worksheet1->write_string($a, 0, $res[$j]['anio'],$formato5);
			$worksheet1->write_string($a, 1, $res[$j]['mes'],$formato5);
			$worksheet1->write_string($a, 2, $res[$j]['dia'],$formato5);
			$worksheet1->write_string($a, 3, $res[$j]['codlinea'],$formato5);
			$worksheet1->write_string($a, 4, $res[$j]['linea'],$formato5);
			$worksheet1->write_string($a, 5, $res[$j]['codproducto'],$formato5);
			$worksheet1->write_string($a, 6, $res[$j]['producto'],$formato5);
			$worksheet1->write_string($a, 7, $res[$j]['cantidad'],$formato5);
			if($igv == "S")
				$worksheet1->write_number($a, 8, number_format(($res[$j]['importe'] * 0.18),2,'.',''),$formato5);
			else
				$worksheet1->write_number($a, 8, number_format($res[$j]['importe'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 9, number_format(($res[$j]['costovta'] * $res[$j]['cantidad']),4,'.',''),$formato5);
			$worksheet1->write_number($a, 10, number_format(($res[$j]['nu_cantidad_actual']),4,'.',''),$formato5);

			$a++;
		}
		$workbook->close();	
		$chrFileName = "VentaProductos";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}

	function reporteExcelTotales($res, $almacen, $desde, $hasta, $igv) {

		$chrFileName = "";

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

		$worksheet1 =& $workbook->add_worksheet('Ventas Especiales');
		$worksheet1->set_column(0, 0, 10);
		$worksheet1->set_column(1, 1, 20);
		$worksheet1->set_column(2, 2, 20);
		$worksheet1->set_column(3, 3, 20);
		$worksheet1->set_column(4, 4, 20);
		$worksheet1->set_column(5, 5, 20);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);
		
		$worksheet1->write_string(1, 0, "VENTAS POR PRODUCTO",$formato0);
		$worksheet1->write_string(3, 0, "FECHA DEL ".$desde." AL ".$hasta,$formato0);
		$worksheet1->write_string(4, 0, " ",$formato0);

		$a = 5;//11	
		$alma = "";

		$d = 1;//2
		$e = 3;//3


	
				$c = ($a + $d);
				$b = ($a + $e);

				$nomalmacen = VentasEspecialesModel::obtenerSucursales($almacen);

				$worksheet1->write_string($c, 0, "ALMACEN: ".$almacen." - ".$nomalmacen[$almacen],$formato0);
				$alma = $almacen;

				$worksheet1->write_string($b, 0, "COD. LINEA",$formato2);	
				$worksheet1->write_string($b, 1, "LINEA",$formato2);
				$worksheet1->write_string($b, 2, "COD. PRODUCTO",$formato2);
				$worksheet1->write_string($b, 3, "PRODUCTO",$formato2);
				$worksheet1->write_string($b, 4, "UNIDADES VENTA",$formato2);
				$worksheet1->write_string($b, 5, "TOTAL VENTA",$formato2);

				$a = $a + 4;

			



		for ($j=0; $j<count($res); $j++){

			$worksheet1->write_string($a, 0, $res[$j]['codlinea'],$formato5);
			$worksheet1->write_string($a, 1, $res[$j]['linea'],$formato5);
			$worksheet1->write_string($a, 2, $res[$j]['codproducto'],$formato5);
			$worksheet1->write_string($a, 3, $res[$j]['producto'],$formato5);
			$worksheet1->write_string($a, 4, $res[$j]['cantidad'],$formato5);
			if($igv == "S")
				$worksheet1->write_number($a, 5, number_format(($res[$j]['importe'] * 0.18),2,'.',''),$formato5);
			else
				$worksheet1->write_number($a, 5, number_format($res[$j]['importe'],2,'.',''),$formato5);
			
			$a++;
		}
		$workbook->close();	
		$chrFileName = "VentaProductosTotales";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}


}
