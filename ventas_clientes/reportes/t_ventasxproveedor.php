<?php

class VentasxProveedorTemplate extends Template	{

	function listaLineas($cod) {
		$lineas = VentasxProveedorModel::obtenerLineas($cod);
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
		$tipos = VentasxProveedorModel::obtenerTipos($cod);
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
		$articulos = VentasxProveedorModel::obtenerArticulos($cod);
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
		
	function listaProveedores($cod) {
		$proveedores_lista = VentasxProveedorModel::obtenerProveedores($cod);
		$result = '<input type="text" id="cod_busca" name="cod_busca"/><button type="button" name="buscar" onclick="cargarListaBusqueda(\'PROVEEDOR\');">Buscar</button><br>
			   Ordenar:<input type="radio" name="ordenar" value="codigo" onclick="cargarListaOrdenarCodigo(\'PROVEEDOR\');" '.('Codigo' == $cod  ? ' checked': '').'>codigo
				<input type="radio" name="ordenar" value="descripcion" onclick="cargarListaOrdenarDescripcion(\'PROVEEDOR\');" '.('Descripcion' == $cod  ? ' checked': '').'>descripcion
				<br/><select name="proveedor[]" size="7" multiple>';
			
		foreach($proveedores_lista as $cod_proveedor => $descripcion) {
			$result .= '<option value="' . htmlentities($cod_proveedor) . '">' . htmlentities($descripcion) . '</option>';
		}
			
		$result .= '</select>';
		return $result;
	}
		
	function titulo() {
		return '<h2 align="center"><b>Ventas por Proveedor</b></h2>';
	}
		
	function formSearch($almacenes, $desde, $hasta) {

		if($desde=="" or $hasta==""){
			$desde	 = date("d/m/Y");
			$hasta	 = date("d/m/Y");
		}

		$result  = '';
		$result .= '<center><form name="reporte" method="post" action="control.php" target="control">';
		$result .= '<input type="hidden" name="rqst" value="REPORTES.VENTASXPROVEEDOR">';
		$result .= '<table border="0" bordercolor="#cccc99">';
		$result .= '<tr>';
		$result .= '<td align="right" width="220">Desde:<input type="text" id="txt-date-ini" name="desde" value="' . htmlentities($desde) . '" size="12"><a href="javascript:show_calendar('."'reporte.desde'".');"></td>';
		$result .= '<td align="left" width="220">Hasta:<input type="text" id="txt-date-fin" name="hasta" value="' . htmlentities($hasta) . '" size="12"><a href="javascript:show_calendar('."'reporte.hasta'".');"></td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td><div align="right">Almacen: </div></td>';
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
		$result .= '<td><div align="right"><b>Condicion:</b></div></td>';
		$result .= '<td><input type="radio" name="condicion" value="proveedor" onclick="cargarLista(\'PROVEEDOR\');">Proveedor';
		$result .= '<input type="radio" name="condicion" value="linea" onclick="cargarLista(\'LINEA\');">Linea';
		$result .= '<input type="radio" name="condicion" value="articulo" onclick="cargarLista(\'ARTICULO\');">Articulo</td>';
		$result .= '</tr>';
		$result .= '<td colspan="7"><div id="space" align="center">&nbsp;</td>';
		$result .= '<tr>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td><div align="center"><button type="submit" name="action" value="Buscar"><img src="/sistemaweb/images/search.png" alt="left" />  Buscar</button></div></td>';
		$result .= '<td><div align="center"><button type="submit" name="action" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button></div></td>';
		$result .= '</tr>';
		$result .= '</table><script>window.onload = function() {parent.document.getElementById("estacion").focus();}</script>';
		$result .= '</form></center>';
			
		return $result;
	}
		
	function reporte($resultado, $conigv) {
		$nColumnas	= 2 + 3*(count($resultado['almacenes'])+1);
		$result		= '';
			
		if ($conigv == "S")
			$factor = 1.18;
		else
			$factor = 1;
		
		$result .= '<center><table border="1">';
		$result .= '<tr>';
		$result .= '<td colspan="2" bgcolor="#E3CEF6" align="center" style="font-weight:bold">&nbsp;</td>';
			
		foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
			$result .= '<td colspan="3" bgcolor="#E3CEF6" align="center" style="font-weight:bold">' . htmlentities($ch_nombre_almacen) . '</td>';
		}
			
		$result .= '<td colspan="3" bgcolor="#E3CEF6" align="center" style="font-weight:bold">Total</td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td bgcolor="#E3CEF6" align="center" style="font-weight:bold" width="140">Codigo</td>';
		$result .= '<td bgcolor="#E3CEF6" align="center" style="font-weight:bold" width="320">Descripcion</td>';
		
		for ($i = 0; $i < count($resultado['almacenes'])+1; $i++) {
			$result .= '<td bgcolor="#E3CEF6" align="center" style="font-weight:bold">Cantidad</td>';
			$result .= '<td bgcolor="#E3CEF6" align="center" style="font-weight:bold">Importe</td>';
			$result .= '<td bgcolor="#E3CEF6" align="center" style="font-weight:bold">Stock Actual</td>';
		}
			
		$result .= '</tr>';
			
		foreach($resultado['fechas'] as $dt_fac_fecha => $proveedores) {
			// Dias
			$result .= '<tr>';
			$result .= '<td colspan="' . $nColumnas . '" bgcolor="#f4fa58" align="center" style="font-weight:bold; color:red">' . htmlentities($dt_fac_fecha) . '</td>';
			$result .= '</tr>';
			
			// Proveedores
			foreach ($proveedores['proveedor'] as $art_proveedor => $proveedor) {
				$result .= '<tr>';
				$result .= '<td colspan="' . $nColumnas . '" bgcolor="#a9f5f2" style="font-weight:bold">  ' . htmlentities($art_proveedor) . '</td>';
				$result .= '</tr>';
					
				foreach($proveedor['codigos'] as $art_codigo => $codigo) {
					// Codigos
					$result .= '<tr>';
					$result .= '<td align="center">' . htmlentities($art_codigo) . '</td>';
					$result .= '<td>' . htmlentities($codigo['descripcion']) . '</td>';
					
					//Almacenes (horizontal)
					foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
						$result .= '<td align="right" width="70">&nbsp;' . htmlentities(number_format($codigo[$ch_almacen.'_cantidad'], 2, '.', ' ')) . '</td>';
						$result .= '<td align="right" width="80">&nbsp;' . htmlentities(number_format($codigo[$ch_almacen.'_neto'], 2, '.', ' ')) . '</td>';
						$result .= '<td align="right" width="80">&nbsp;' . htmlentities($codigo['stock']) . '</td>';
					}
					$result .= '<td align="right" width="70">' . htmlentities(number_format($codigo['total_cantidad'], 2, '.', ' ')) . '</td>';
					$result .= '<td align="right" width="80">' . htmlentities(number_format($codigo['total_neto']*$factor, 2, '.', ' ')) . '</td>';
					$result .= '<td align="right" width="80">' . htmlentities($codigo['stock']) . '</td>';
					$result .= '</tr>';
				}
					
				$result .= '<tr>';
				$result .= '<td colspan="2" style="font-weight:bold" align="right">TOTAL PROVEEDOR</td>';
					
				foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
					$result .= '<td style="font-weight:bold" align="right" width="70">&nbsp;' . htmlentities(number_format($proveedor[$ch_almacen.'_cantidad'], 2, '.', ' ')) . '</td>';
					$result .= '<td style="font-weight:bold" align="right" width="80">&nbsp;' . htmlentities(number_format($proveedor[$ch_almacen.'_neto']*$factor, 2, '.', ' ')) . '</td>';
					$result .= '<td style="font-weight:bold" align="right" width="80">&nbsp;</td>';
				}
				$result .= '<td style="font-weight:bold" align="right" width="70">' . htmlentities(number_format($proveedor['total_cantidad'], 2, '.', ' ')) . '</td>';
				$result .= '<td style="font-weight:bold" align="right" width="80">' . htmlentities(number_format($proveedor['total_neto']*$factor, 2, '.', ' ')) . '</td>';
				$result .= '<td style="font-weight:bold" align="right" width="80">&nbsp;</td>';
				$result .= '</tr>';
			}
				
			$result .= '<tr>';
			$result .= '<td colspan="2" style="color:blue; font-weight:bold" align="right">TOTAL DIA</td>';
			
			foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
				$result .= '<td style="color:blue; font-weight:bold" align="right" width="70">&nbsp;' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha][$ch_almacen."_cantidad"], 2, '.', ' ')) . '</td>';
				$result .= '<td style="color:blue; font-weight:bold" align="right" width="80">&nbsp;' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha][$ch_almacen."_neto"]*$factor, 2, '.', ' ')) . '</td>';
				$result .= '<td style="color:blue; font-weight:bold" align="right" width="80">&nbsp;</td>';
			}
			$result .= '<td style="color:blue; font-weight:bold" align="right" width="70">' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha]['total_cantidad'], 2, '.', ' ')) . '</td>';
			$result .= '<td style="color:blue; font-weight:bold" align="right" width="80">' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha]['total_neto']*$factor, 2, '.', ' ')) . '</td>';
			$result .= '<td style="color:blue; font-weight:bold" align="right" width="80">&nbsp;</td>';
			$result .= '</tr>';
		}			
		$result .= '<tr>';
		$result .= '<td colspan="2" bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right">TOTAL GENERAL</td>';
			
		foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
			$result .= '<td bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right" width="70">&nbsp;' . htmlentities(number_format($resultado[$ch_almacen.'_cantidad'], 2, '.', ' ')) . '</td>';
			$result .= '<td bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right" width="80">&nbsp;' . htmlentities(number_format($resultado[$ch_almacen.'_neto']*$factor, 2, '.', ' ')) . '</td>';
			$result .= '<td bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right" width="80">&nbsp;</td>';
		}
		$result .= '<td bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right" width="70">' . htmlentities(number_format($resultado['total_cantidad'], 2, '.', ' ')) . '</td>';
		$result .= '<td bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right" width="80">' . htmlentities(number_format($resultado['total_neto']*$factor, 2, '.', ' ')) . '</td>';
		$result .= '<td bgcolor="#BEF781" style="color:blue; font-weight:bold; font-size:13px" align="right" width="80">&nbsp;</td>';
		$result .= '</tr>';
		$result .= '</table></center>';
		
		return $result;

	}

	function reporteExcel($res, $almacen, $desde, $hasta, $igv) {



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

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados Varillaje');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 50);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 16);
		$worksheet1->set_column(6, 6, 16);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "VENTAS POR PROVEEDOR",$formato0);
		$worksheet1->write_string(3, 0, "FECHA DEL ".$desde." AL ".$hasta,$formato0);
		$worksheet1->write_string(4, 0, " ",$formato0);

		$a = 5;//11	
		$alma = "";

		$d = 1;//2
		$e = 3;//3

		for ($j=0; $j<count($res); $j++){

			if($alma != $res[$j]['almacen']){

				$nomalmacen = VentasxProveedorModel::obtenerSucursales($res[$j]['almacen']);

				$c = ($a + $d);
				$b = ($a + $e);

				$worksheet1->write_string($c, 0, "ALMACEN: ".$res[$j]['almacen']." - ".$nomalmacen[$res[$j]['almacen']],$formato0);
				$alma = $res[$j]['almacen'];

				$worksheet1->write_string($b, 0, "RUC",$formato2);
				$worksheet1->write_string($b, 1, "PROVEEDOR",$formato2);
				$worksheet1->write_string($b, 2, "COD. LINEA",$formato2);	
				$worksheet1->write_string($b, 3, "LINEA",$formato2);
				$worksheet1->write_string($b, 4, "COD. PRODUCTO",$formato2);
				$worksheet1->write_string($b, 5, "PRODUCTO",$formato2);
				$worksheet1->write_string($b, 6, "UNIDADES VENTA S/.",$formato2);
				$worksheet1->write_string($b, 7, "VENTAS S/.",$formato2);
				$worksheet1->write_string($b, 8, "STOCK ACTUAL",$formato2);

				$a = $a + 4;

			}

			$worksheet1->write_string($a, 0, $res[$j]['ruc'],$formato5);
			$worksheet1->write_string($a, 1, $res[$j]['proveedor'],$formato5);
			$worksheet1->write_string($a, 2, $res[$j]['codlinea'],$formato5);
			$worksheet1->write_string($a, 3, $res[$j]['linea'],$formato5);
			$worksheet1->write_string($a, 4, $res[$j]['codproducto'],$formato5);
			$worksheet1->write_string($a, 5, $res[$j]['producto'],$formato5);
			$worksheet1->write_string($a, 6, $res[$j]['cantidad'],$formato5);

			if($igv == "S")
				$worksheet1->write_number($a, 7, number_format(($res[$j]['importe'] * 0.18),2,'.',''),$formato5);
			else
				$worksheet1->write_number($a, 7, number_format($res[$j]['importe'],2,'.',''),$formato5);

			$worksheet1->write_number($a, 8, number_format(($res[$j]['stock'] * 0.18),2,'.',''),$formato5);

			$a++;

		}
			
		$workbook->close();	

		$chrFileName = "VentasxProveedor";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");	
	}





}
