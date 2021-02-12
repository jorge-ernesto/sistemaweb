<?php
class ConsistenciaTemplate extends Template
{
    function Titulo()
    {
	return '<h2 align="center" style="color:#336699"><b> MOVIMIENTOS DE CONSISTENCIA DE ALMAC&Eacute;N </b></h2>';
    }
    
    function formSearch(){
		$hoy = date("d/m/Y");

		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.CONSISTENCIA"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Desde:", "desde", $hoy, '', '', 10, 10, ""));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif"></a>&nbsp;&nbsp;'));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Hasta:", "hasta", $hoy, '', '', 10, 10, ""));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif"></a>&nbsp;&nbsp;'));
		$estaciones = FormProcesModel::obtenerEstaciones();
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_combo("Almacén:", "estacion", "TODAS", '', '', 1, $estaciones, false, ''));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'
		<tr>
			<td colspan="3" align="center">
				<button type="submit" id="buscar" name="buscar" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>
			</td>
		</tr>'
		));
		
		return $form->getForm();
    }
    
    function listado($resultado, $desde, $hasta, $almacen, $tipo=0)
    {
	/*
	 * NOTA: esta funcion se usa en varios reportes del kardex, asi que hay que tener cuidado al hacer ciertas cosas,
	 * siempre usando la variable $tipo para diferenciar el tipo de reporte. Valores posibles de $tipo:
	 *
	 * 0 = Consistencia de Movimientos
	 * 1 = Consulta por Documento
	 */
	 
	 
	$formularios = FormProcesModel::ObtenerTiposFormularios();

	if ($tipo == 0) $result = '<div align="center"><a href="control.php?rqst=REPORTES.CONSISTENCIA&action=pdf&desde=' . htmlentities($desde) . '&hasta=' . htmlentities($hasta) . '&estacion=' . htmlentities($almacen) . '" target="_blank">Imprimir</a></div>';
	
	$result .= '<table align="center">';
	$result .= '<tr>';
	
	switch($tipo) {
	    case 1:
		$result .= '<th colspan="9"><b>CONSULTA POR DOCUMENTO DE REFERENCIA</b></th>';
		break;
	    default:
		$result .= '<th align="center" class="grid_cabecera" colspan="9"><b>RESUMEN DE REGISTROS POR TIPO DE MOVIMIENTO</b></th>';
		break;
	}

	$result .= '</tr>';

	$old_almacen = "";
	$old_tran = "";
	$old_numero = "";

	$cuentas = Array();

	foreach($resultado['almacenes'] as $mov_almacen => $movi_almas) {
	    /*
	    $result .= '<tr>';
	    $result .= '<th align="center" class="grid_cabecera" colspan="9"><b>ALMACÉN '.htmlentities($mov_almacen).': '.htmlentities(FormProcesModel::obtenerDescripcionAlmacen($mov_almacen)).'</b></th>';
	    $result .= '</tr>';
	    */
	    foreach($movi_almas['tipos'] as $tran_codigo => $movi_tipo) {
		    /*
			$result .= '<tr>';
			$result .= '<th align="center" class="grid_cabecera" colspan="9"><b>(*) TIPO DE MOVIMIENTO '.htmlentities($tran_codigo).': '.htmlentities($formularios[trim($tran_codigo)]).'</b></th>';
			$result .= '</tr>';
			*/
			$result .= '<tr>';
			$result .= '<th align="center" class="grid_cabecera">MOVIMIENTO</th>';
			$result .= '<th align="center" class="grid_cabecera">FECHA</th>';
			$result .= '<th align="center" class="grid_cabecera">ORDEN</th>';
			$result .= '<th align="center" class="grid_cabecera" colspan="3">PROVEEDOR</th>';
			$result .= '<th align="center" class="grid_cabecera" colspan="3"></th>';
			$result .= '</tr>';
			$result .= '<tr>';
			$result .= '<th align="center" class="grid_cabecera">CÓDIGO</th>';
			$result .= '<th align="center" class="grid_cabecera">DESCRIPCIÓN</th>';
			$result .= '<th align="center" class="grid_cabecera">CANTIDAD</th>';
			$result .= '<th align="center" class="grid_cabecera">COSTO</th>';
			$result .= '<th align="center" class="grid_cabecera">TOTAL</th>';
			$result .= '<th align="center" class="grid_cabecera">ORIGEN</th>';
			$result .= '<th align="center" class="grid_cabecera">DESTINO</th>';
			$result .= '<th align="center" class="grid_cabecera">DOC. REF.</th>';
			$result .= '<th align="center" class="grid_cabecera">NUM. DOC. REF.</th>';
			$result .= '</tr>';

			$result .= '<tr>';
			$result .= '<td align="center" class="grid_detalle_especial" colspan="9"><b>ALMACÉN '.htmlentities($mov_almacen).': '.htmlentities(FormProcesModel::obtenerDescripcionAlmacen($mov_almacen)).'</b></td>';
			$result .= '</tr>';

			$result .= '<tr>';
			$result .= '<td align="center" class="grid_detalle_especial" colspan="9"><b>(*) TIPO DE MOVIMIENTO: '.htmlentities($formularios[trim($tran_codigo)]).'</b></td>';
			$result .= '</tr>';
			foreach($movi_tipo['movimientos'] as $mov_numero => $movimiento) {
			    //$result .= '<tr style="background-color:#d6d6d6">';
			    $result .= '<tr class="grid_detalle_impar" align ="left" style="font-size:0.9em; color:black;"><b>';
			    $result .= '<td><b>' . htmlentities($mov_numero) . '</b></td>';
			    $result .= '<td><b>' . htmlentities($movimiento['mov_fecha']) . '</b></td>';
			    $result .= '<td><b>' . htmlentities($movimiento['com_num_compra']) . '</b></td>';
			    $result .= '<td><b>' . htmlentities($movimiento['mov_entidad']) . '</b></td>';
			    $result .= '<td colspan="5"></td>';
			    $result .= '</tr>';
			    foreach($movimiento['articulos'] as $i => $articulo) {
					$result .= '<tr class="bgcolor">';
					$result .= '<td>' . htmlentities($articulo['art_codigo']) . '</td>';
					$result .= '<td>' . htmlentities($articulo['art_descripcion']) . '</td>';
					$result .= '<td>' . htmlentities($articulo['mov_cantidad']) . '</td>';
					$result .= '<td>' . htmlentities($articulo['mov_costounitario']) . '</td>';
					$result .= '<td>' . htmlentities($articulo['mov_costototal']) . '</td>';
					$result .= '<td>' . htmlentities($articulo['mov_almaorigen'] . ' - ' . FormProcesModel::obtenerDescripcionAlmacen($articulo['mov_almaorigen'])) . '</td>';
					$result .= '<td>' . htmlentities($articulo['mov_almadestino'] . ' - ' . FormProcesModel::obtenerDescripcionAlmacen($articulo['mov_almadestino'])) . '</td>';
					$result .= '<td>' . htmlentities($articulo['mov_tipdocuref']) . '</td>';
					$result .= '<td>' . htmlentities($articulo['mov_docurefe']) . '</td>';
					$result .= '</tr>';			
					$cuentas[$tran_codigo]++;
			    }
			}
	    }
	}
	
	$result .= '</table>';

	if ($tipo == 0) {
	    $result .= '<br><h2 align="center" style="color:#336699"><b> RESUMEN DE TRANSACCIONES </b></h2>';
	
		$result .= '<table align="center">';
		
	    foreach($cuentas as $tran_codigo => $cuenta) {
			$result .= '<tr>';
			$result .= '<td style="font-size: 16px"> # de '.htmlentities($formularios[$tran_codigo]).'</td><td style="font-size: 16px">: '.htmlentities($cuenta).'</td>';
			$result .= '</tr>';
	    }
	    $result .= '<tr>';
	    $result .= '<td style="font-size: 16px"> # de '.htmlentities($formularios['25']).'</td><td style="font-size: 16px">: '.htmlentities(ConsistenciaModel::obtenerCuentaTransacciones($desde, $hasta, $almacen, '25')).'</td>';
	    $result .= '</tr><tr>';
	    $result .= '<td style="font-size: 16px"> # de '.htmlentities($formularios['45']).'</td><td style="font-size: 16px">: '.htmlentities(ConsistenciaModel::obtenerCuentaTransacciones($desde, $hasta, $almacen, '45')).'</td>';
	    $result .= '</tr>';
	    $result .= '</table>';
		//$result .= '</div>';
	}

	return $result;
    }

    function outputPDF($resultado, $desde, $hasta, $almacen)
    {
	global $usuario;

	$formularios = FormProcesModel::ObtenerTiposFormularios();
	$fontsize = 7;

	$cab1 = Array(
		"mov_numero"	=>	"Movimiento",
		"mov_fecha"		=>	"Fecha",
		"com_num_compra"=>	"Orden",
		"mov_entidad"	=>	"Proveedor"
	);
	
	$cab2 = Array(
		"art_codigo"		=>	"Codigo",
		"art_descripcion"	=>	"Descripcion",
		"mov_cantidad"		=>	"Cantidad",
		"mov_costounitario"	=>	"Costo",
		"mov_costototal"	=>	"Total",
		"mov_almaorigen"	=>	"Origen",
		"mov_almadestino"	=>	"Destino",
		"mov_tipdocuref"	=>	"Doc.R",
		"mov_docurefe"		=>	"Num. Doc.R"
	);

	$reporte = new CReportes2();

	$reporte->SetFont("courier", "", $fontsize);

	$reporte->definirColumna("art_codigo", $reporte->TIPO_TEXTO, 13, "L");
	$reporte->definirColumna("art_descripcion", $reporte->TIPO_TEXTO, 30, "L");
	$reporte->definirColumna("mov_cantidad", $reporte->TIPO_CANTIDAD, 10, "R");
	$reporte->definirColumna("mov_costounitario", $reporte->TIPO_COSTO, 10, "R");
	$reporte->definirColumna("mov_costototal", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("mov_almaorigen", $reporte->TIPO_TEXTO, 20, "L");
	$reporte->definirColumna("mov_almadestino", $reporte->TIPO_TEXTO, 20, "L");
	$reporte->definirColumna("mov_tipdocuref", $reporte->TIPO_TEXTO, 5, "L");
	$reporte->definirColumna("mov_docurefe", $reporte->TIPO_TEXTO, 10, "L");
	
	$reporte->definirColumna("mov_numero", $reporte->TIPO_TEXTO, 13, "L", "cab1");
	$reporte->definirColumna("mov_fecha", $reporte->TIPO_TEXTO, 20, "L", "cab1");
	$reporte->definirColumna("com_num_compra", $reporte->TIPO_TEXTO, 10, "L", "cab1");
	$reporte->definirColumna("mov_entidad", $reporte->TIPO_TEXTO, 42, "L", "cab1");

	$reporte->definirColumna("mov_numero", $reporte->TIPO_TEXTO, 13, "L", "_mov", "B");
	$reporte->definirColumna("mov_fecha", $reporte->TIPO_TEXTO, 20, "L", "_mov", "B");
	$reporte->definirColumna("com_num_compra", $reporte->TIPO_TEXTO, 10, "L", "_mov", "B");
	$reporte->definirColumna("mov_entidad", $reporte->TIPO_TEXTO, 42, "L", "_mov", "B");
	
	$reporte->definirColumna("mov_formulario", $reporte->TIPO_TEXTO, 50, "L", "_form", "B");

	$reporte->definirCabecera(1, "L", "sistemaweb-OFICINA CENTRAL");
	$reporte->definirCabecera(1, "C", "CONSISTENCIA DE MOVIMIENTOS");
	$reporte->definirCabecera(1, "R", "PAG.%p");
	$reporte->definirCabecera(2, "C", "DESDE " . $desde . " HASTA " . $hasta);
	$reporte->definirCabecera(2, "R", "%f");
	$reporte->definirCabecera(2, "L", "USUARIO: " . $usuario->obtenerUsuario());

	$reporte->definirCabeceraPredeterminada($cab1, "cab1");
	$reporte->definirCabeceraPredeterminada($cab2);

	$reporte->SetMargins(0, 0, 0);
	$reporte->AddPage();

	$cuentas = Array();

	//var_dump($formularios);
	
	foreach($resultado['almacenes'] as $mov_almacen => $movi_almas) {
	    $reporte->Ln();
	    $a = Array("mov_formulario" => 'Almacen ' . $mov_almacen . ': ' . FormProcesModel::obtenerDescripcionAlmacen($mov_almacen));
	    $reporte->nuevaFila($a, "_form");
	    foreach($movi_almas['tipos'] as $tran_codigo => $movi_tipo) {
	    //echo "tran_codigo: \"" . $tran_codigo . "\"\n";
		$a = Array("mov_formulario" => "(*)Tipo de Movimiento: " . $formularios[trim($tran_codigo)]);
		$reporte->nuevaFila($a, "_form");
//		$reporte->Cell(0, $fontsize, , 0, 1);
		$reporte->lineaH();
		$reporte->nuevaFila($cab1, "cab1", true);
		$reporte->nuevaFila($cab2, "_default", true);
		$reporte->lineaH();
	
		foreach($movi_tipo['movimientos'] as $mov_numero => $movimiento) {
		    $movimiento['mov_numero'] = $mov_numero;
		    $reporte->nuevaFila($movimiento, "_mov");
		    foreach($movimiento['articulos'] as $i => $articulo) {
			$articulo['mov_almaorigen'] .= '-' . FormProcesModel::obtenerDescripcionAlmacen($articulo['mov_almaorigen']);
			$articulo['mov_almadestino'] .= '-' . FormProcesModel::obtenerDescripcionAlmacen($articulo['mov_almadestino']);
			$reporte->nuevaFila($articulo);
			$cuentas[$tran_codigo]++;
		    }
		    $reporte->Ln();
		}
	    }
	}
	
	$reporte->Ln();
	$reporte->Ln();

	$reporte->SetFont("courier", "B", $fontsize);
	$reporte->Cell(0, $fontsize, "Resumen de transacciones", 0, 1);
	$reporte->SetFont("courier", "", $fontsize);
	
	
	foreach($cuentas as $tran_codigo => $cuenta) {
	    $reporte->Cell(0, $fontsize, "# de " . $formularios[$tran_codigo] . ": " . $cuenta, 0, 1);
	}
	$reporte->Cell(0, $fontsize, "# de " . $formularios['25'] . ": " . ConsistenciaModel::obtenerCuentaTransacciones($desde, $hasta, $almacen, '25'), 0, 1);
	$reporte->Cell(0, $fontsize, "# de " . $formularios['45'] . ": " . ConsistenciaModel::obtenerCuentaTransacciones($desde, $hasta, $almacen, '45'), 0, 1);

	$reporte->Output();
    }
    
}
