<?php

class EspecialesTemplate extends Template {

	function listaLineas($cod) {

		$lineas = EspecialesModel::obtenerLineas($cod);
		$result = 'Codigo: <input type="text" id="cod_busca" name="cod_busca"/><button type="button" name="buscar" onclick="cargarListaBusqueda(\'LINEA\');">Buscar</button><br>
		           Ordenar:<input type="radio" name="ordenar" value="codigo" onclick="cargarListaOrdenarCodigo(\'LINEA\');" '.('Codigo' == $cod  ? ' checked': '').'>codigo
				   <input type="radio" name="ordenar" value="descripcion" onclick="cargarListaOrdenarDescripcion(\'LINEA\');" '.('Descripcion' == $cod  ? ' checked': '').'>descripcion
				   <br/><select name="linea[]" size="7" multiple>';
			
		foreach($lineas as $cod_linea => $descripcion)
		{
			$result .= '<option value="' . htmlentities($cod_linea) . '">' . htmlentities($descripcion) . '</option>';
		}
		
		$result .= '</select>';
		
		return $result;
	}
		
	function listaTipos($cod) {
		$tipos = EspecialesModel::obtenerTipos($cod);
		$result = 'Codigo:<input type="text" id="cod_busca" name="cod_busca"/><button type="button" name="buscar" onclick="cargarListaBusqueda(\'DOCUMENTO\');">Buscar</button><br>
			   Ordenar:<input type="radio" id="ordenar" name="ordenar" value="codigo" onclick="cargarListaOrdenarCodigo(\'DOCUMENTO\');" '.('Codigo' == $cod  ? ' checked': '').'>codigo
				<input type="radio" id="ordenar" name="ordenar" value="descripcion" onclick="cargarListaOrdenarDescripcion(\'DOCUMENTO\');" '.('Descripcion' == $cod  ? ' checked': '').'>descripcion
				<br/><select name="documento[]" size="7" multiple>';
			
		foreach($tipos as $cod_tipo => $descripcion) {
			$result .= '<option value="' . htmlentities($cod_tipo) . '">' . htmlentities($descripcion) . '</option>';
		}
		
		$result .= '</select>';
		return $result;
	}
		
	function listaArticulos($cod)
	{
		$articulos = EspecialesModel::obtenerArticulos($cod);
		$result = 'Codigo:<input type="text" id="cod_busca" name="cod_busca"/>	
				  <button type="button" name="buscar" onclick="cargarListaBusqueda(\'ARTICULO\');">Buscar</button><br>
		           Ordenar:<input type="radio" name="ordenar" value="codigo" onclick="cargarListaOrdenarCodigo(\'ARTICULO\');" '.('Codigo' == $cod  ? ' checked': '').'>codigo
				   <input type="radio" name="ordenar" value="descripcion" onclick="cargarListaOrdenarDescripcion(\'ARTICULO\');" '.('Descripcion' == $cod  ? ' checked': '').'>descripcion
			           <br/>';
		$result .= '<select name="codigo[]" size="7" multiple>';
		
		foreach($articulos as $cod_articulo => $descripcion)
		{
			$result .= '<option value="' . htmlentities($cod_articulo) . '">' . htmlentities($descripcion) . '</option>';
		}
		
		$result .= '</select>';
		return $result;
	}

	function titulo(){
		return '<div align="center"><h2><b>Ventas Especiales</b></h2></div>';
	}
		
	function formSearch($almacenes, $desde, $hasta) {

		if($desde=="" or $hasta==""){
			$desde	 = date("d/m/Y");
			$hasta	 = date("d/m/Y");
		}

		$result  = '';
		$result .= '<center><form name="reporte" method="post" action="control.php" target="control">';
		$result .= '<input type="hidden" name="rqst" value="REPORTES.ESPECIALES">';
		$result .= '<table border="0" bordercolor="#cccc99">';
		$result .= '<tr>';
		$result .= '<td align="right" width="220">Desde:<input type="text" name="desde" value="'.htmlentities($desde) . '" size="12"><a href="javascript:show_calendar('."'reporte.desde'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></td>';
		$result .= '<td colspan="3" align="left" width="220">Hasta:<input type="text" name="hasta" value="'.htmlentities($hasta) . '" size="12"><a href="javascript:show_calendar('."'reporte.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a></td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td><div align="right">Almacen</div></td>';
		$result .= '<td colspan="3"><select name="exalma">';
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
		$result .= '<td><div align="right">neto es con IGV:</div></td>';
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
		$result .= '<td><input type="radio" name="condicion" value="tipo" onclick="cargarLista(\'TIPO\');">Tipo</td>';
		$result .= '<td><input type="radio" name="condicion" value="linea" onclick="cargarLista(\'LINEA\');">Linea</td>';
		$result .= '<td><input type="radio" name="condicion" value="articulo" onclick="cargarLista(\'ARTICULO\');">Articulo</td>';
		$result .= '</tr>';
		$result .= '<td colspan="7"><div id="space" align="center">&nbsp;</td>';
		$result .= '<tr>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td colspan="6"><div align="center"><button type="submit" name="action" value="Reporte"><img src="/sistemaweb/images/search.png" alt="left" />  Buscar</button>';
		$result .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" name="action" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left" />  Excel</button></div></td>';
		$result .= '</tr>';
		$result .= '</table>';
		$result .= '</form></center>';
			
		return $result;
	}
		
	function reporte($resultado, $conigv,$exalma){

		$nColumnas	= 2 + 2 * (count($resultado['almacenes']));
		$result		= '';
			
		if ($conigv == "S")
			$factor = 1.18;
		else
			$factor = 1;
		
		$result .= '<center><table border="1">';
		$result .= '<tr>';
		$result .= '<td colspan="2" bgcolor="#E3CEF6" align="center" style="font-weight:bold">&nbsp;</td>';
			
		foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
			$result .= '<td colspan="2" bgcolor="#E3CEF6" align="center" style="font-weight:bold">' . htmlentities($ch_nombre_almacen) . '</td>';
		}

		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td bgcolor="#E3CEF6" align="center" style="font-weight:bold" width="140">Codigo</td>';
		$result .= '<td bgcolor="#E3CEF6" align="center" style="font-weight:bold" width="320">Descripcion</td>';
		
		for ($i = 0; $i < count($resultado['almacenes']); $i++) {
			$result .= '<td bgcolor="#E3CEF6" align="center" style="font-weight:bold">Cantidad</td>';
			$result .= '<td bgcolor="#E3CEF6" align="center" style="font-weight:bold">Importe</td>';
		}
			
		$result .= '</tr>';
			
		foreach($resultado['fechas'] as $dt_fac_fecha => $lineas) {
			// Dias
			$result .= '<tr>';
			$result .= '<td colspan="' . $nColumnas . '" bgcolor="#f4fa58" align="center" style="font-weight:bold; color:red">' . htmlentities($dt_fac_fecha) . '</td>';
			$result .= '</tr>';
			
			// lineass
			foreach ($lineas['lineas'] as $art_linea_desc => $linea) {
				$result .= '<tr>';
				$result .= '<td colspan="' . $nColumnas . '" bgcolor="#a9f5f2" style="font-weight:bold">***LINEA: '.htmlentities($art_linea_desc). ' ***</td>';
				$result .= '</tr>';
					
				foreach($linea['codigos'] as $art_codigo => $codigo) {
					// Codigos
					$result .= '<tr>';
					$result .= '<td bgcolor="#a9f5f2" style="font-weight:bold" >Codigo: ' . htmlentities($art_codigo) .'</td>';
					$result .= '<td colspan="3" align="left" bgcolor="#a9f5f2" style="font-weight:bold">' . htmlentities($codigo['descripcion']) . '</td>';
					$result .= '</tr>';
					$result .= '<tr>';
					$result .= '<td colspan="2" style="font-weight:bold" align="right">TOTAL LINEA</td>';
					//Almacenes (horizontal)
					foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
						$result .= '<td align="right" width="130">&nbsp;' . htmlentities(number_format($codigo[$ch_almacen.'_cantidad'], 2, '.', ' ')) . '</td>';
						if($conigv=="S"){						
							$result .= '<td align="right" width="130">&nbsp;' . htmlentities(number_format($codigo[$ch_almacen.'_neto']*1.18, 2, '.', ' ')) . '</td>';
						}else{
							$result .= '<td align="right" width="130">&nbsp;' . htmlentities(number_format($codigo[$ch_almacen.'_neto'], 2, '.', ' ')) . '</td>';
						}
					}
				}
					
				$result .= '</tr>';
		        }
				
			$result .= '<tr>';
			$result .= '<td colspan="2" style="color:blue; font-weight:bold" align="right">TOTAL DIA</td>';
			
			foreach($resultado['almacenes'] as $ch_almacen => $ch_nombre_almacen) {
				$result .= '<td style="color:blue; font-weight:bold" align="right" width="130">&nbsp;' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha][$ch_almacen."_cantidad"], 2, '.', ' ')) . '</td>';
				if($conigv == "S"){
					$result .= '<td style="color:blue; font-weight:bold" align="right" width="130">&nbsp;' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha][$ch_almacen."_total"], 2, '.', ' ')) . '</td>';
				}else{
					$result .= '<td style="color:blue; font-weight:bold" align="right" width="130">&nbsp;' . htmlentities(number_format($resultado['fechas'][$dt_fac_fecha][$ch_almacen."_neto"], 2, '.', ' ')) . '</td>';
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

	function reporteExcel($res, $almacen, $desde, $hasta) {

		$nomalmacen = EspecialesModel::obtenerSucursales($almacen);

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

		$worksheet1->write_string(1, 0, "MEDIDA DIARIA DE VARILLA",$formato0);
		$worksheet1->write_string(3, 0, "ALMACEN: ".$nomalmacen[$almacen],$formato0);
		$worksheet1->write_string(4, 0, "FECHA DEL ".$desde." AL ".$hasta,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 7;
		$worksheet1->write_string($a, 0, "FECHA",$formato2);
		$worksheet1->write_string($a, 1, "TANQUE",$formato2);
		$worksheet1->write_string($a, 2, "NOMBRE COMBUSTIBLE",$formato2);
		$worksheet1->write_string($a, 3, "MEDICION",$formato2);	
		$worksheet1->write_string($a, 4, "RESPONSABLE",$formato2);
		
		$a = 8;	

		/*for ($j=0; $j<count($res); $j++) {	
			$nomtanque = VarillasModel::obtenerTanques($almacen, $res[$j]['ch_tanque']);	
			
			$worksheet1->write_string($a, 0, $res[$j]['dt_fecha'],$formato5);
			$worksheet1->write_string($a, 1, $nomtanque[$res[$j]['ch_tanque']],$formato5);
			$worksheet1->write_string($a, 2, $res[$j]['ch_nombre'],$formato5);	
			$worksheet1->write_number($a, 3, number_format($res[$j]['nu_medicion'],3,'.',''),$formato5);
			$worksheet1->write_string($a, 4, $res[$j]['ch_responsable'],$formato5);	
			$a++;
		}*/
			
		$workbook->close();	

		$chrFileName = "VentasEspeciales";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");

	}

}


?>



