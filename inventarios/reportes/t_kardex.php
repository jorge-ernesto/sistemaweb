<?php

class KardexTemplate extends Template
{
    function Titulo()
    {
	return '<h2><b>Kardex</b></h2>';
    }
    
    function formSearch()
    {
	$estaciones = FormProcesModel::ObtenerEstaciones();
	$hoy = date("d/m/Y");
	$tipos = Array(
		"CONTABLE"	=> "Contable (completo)",
		"FISICO"	=> "Fisico"
		);

	$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");

	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.KARDEX"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Buscar"));

	$form->addGroup("GRUPO_FECHA", "Fecha");
	$form->addElement("GRUPO_FECHA", new form_element_text("Desde:", "desde", $hoy, '', '', 12, 10,""));
	$form->addElement("GRUPO_FECHA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.desde'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement("GRUPO_FECHA", new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div><br/>'));
	$form->addElement("GRUPO_FECHA", new form_element_text("Hasta:", "hasta", $hoy, '', '', 12, 10,""));
	$form->addElement("GRUPO_FECHA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.hasta'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addGroup("GRUPO_ARTICULO", "Articulo");
	$form->addElement("GRUPO_ARTICULO", new form_element_text("Desde:", "art_desde", '', '', '', 17, 13,""));
	$form->addElement("GRUPO_ARTICULO", new form_element_anytext('<img src="/sistemaweb/images/help.gif" onclick="openHelperWindow(\'art_desde\')">'));
	$form->addElement("GRUPO_ARTICULO", new form_element_text("Hasta:", "art_hasta", '', '', '', 17, 13,""));
	$form->addElement("GRUPO_ARTICULO", new form_element_anytext('<img src="/sistemaweb/images/help.gif" onclick="openHelperWindow(\'art_hasta\')"><br>'));
	
	$form->addGroup("GRUPO_ESTACIONES", "Estaciones");
	$form->addElement("GRUPO_ESTACIONES", new form_element_combo("Estacion:", "estacion", "TODAS", "<br>", '', 1, $estaciones, false, ''));

	$form->addGroup("GRUPO_TIPOREPORTE", "Tipo de reporte");
	$form->addElement("GRUPO_TIPOREPORTE", new form_element_radio('', "tipo_reporte", "CONTABLE", '<br>', '', 1, $tipos, ""));

	$form->addGroup("GRUPO_BOTONES", "&nbsp;");
	$form->addElement("GRUPO_BOTONES", new form_element_submit("submit", "Buscar", "", "", ""));
	return $form->getForm();
    }
    
	function listado($resultado, $desde, $hasta, $art_desde, $art_hasta, $estacion, $tipo) {

		$result = '<button name="fm" value="" onClick="javascript:parent.location.href=\'/sistemaweb/inventarios/control.php?rqst=REPORTES.KARDEX&desde=' . htmlentities($desde) . '&hasta=' . htmlentities($hasta) . '&art_desde=' . htmlentities($art_desde) . '&art_hasta=' . htmlentities($art_hasta) . '&estacion=' . htmlentities($estacion) . '&tipo_reporte=' . htmlentities($tipo) . '&action=pdf\';return false"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>';

		foreach($resultado['almacenes'] as $mov_almacen => $almacen) {
				$result .= '<table border="0" width="100%" id="tabprincipal" align="center">';
		    	$result .= '<tr>';
		    
		    	if ($tipo == "CONTABLE")
				$result .= '<td class="grid_cabecera" style="color:white;" colspan="16"><center>' . htmlentities($mov_almacen . " " . FormProcesModel::obtenerDescripcionAlmacen($mov_almacen)) . '</center></td>';
		    	else
				$result .= '<td class="grid_cabecera" style="color:white;" colspan="10"><center>' . htmlentities($mov_almacen . " " . FormProcesModel::obtenerDescripcionAlmacen($mov_almacen)) . '</center></td>';

		    	$result .= '</tr>';
		    	$result .= '<tr>';
		   	$result .= '<td class="grid_cabecera" style="color:white;">Codigo</td>';
		    	$result .= '<td class="grid_cabecera" style="color:white;" colspan="5">DESCRIPCION</td>';
		    
		    	if ($tipo == "CONTABLE")
				$result .= '<td class="grid_cabecera" style="color:white;" colspan="10">&nbsp;</td>';
		    	else
				$result .= '<td class="grid_cabecera" style="color:white;" colspan="4">&nbsp;</td>';
		    	$result .= '</tr>';
		    	$result .= '<tr>';
		    	$result .= '<td class="grid_cabecera" style="color:white;">Fecha</td>';
		    	$result .= '<td class="grid_cabecera" style="color:white;">Formulario</td>';
		    	$result .= '<td class="grid_cabecera" style="color:white;">Numero</td>';
		    	$result .= '<td class="grid_cabecera" style="color:white;">Origen/Destino</td>';
		    	$result .= '<td class="grid_cabecera" style="color:white;">Cliente/Proveedor</td>';
		    	$result .= '<td class="grid_cabecera" style="color:white;">No.REF.</td>';
		    	$result .= '<td class="grid_cabecera" style="color:white;">CANT-ANT</td>';
		    
		    	if ($tipo == "CONTABLE")
				$result .= '<td class="grid_cabecera" style="color:white;">VAL-UNIT-ANT</td>';
		    	$result .= '<td class="grid_cabecera" style="color:white;">CANT-ENTRADA</td>';
		    
		    	if ($tipo == "CONTABLE")
				$result .= '<td class="grid_cabecera" style="color:white;">COST-ENTRADA</td>';
		    	$result .= '<td class="grid_cabecera" style="color:white;">CANT-SALIDA</td>';
		    
		    	if ($tipo == "CONTABLE") {
				$result .= '<td class="grid_cabecera" style="color:white;">COST-SALIDA</td>';
				$result .= '<td class="grid_cabecera" style="color:white;">COST-MVMTO</td>';
		    	}
		    
		    	$result .= '<td class="grid_cabecera" style="color:white;">CANT-ACTUAL</td>';

		    	if ($tipo == "CONTABLE") {
				$result .= '<td class="grid_cabecera" style="color:white;">VAL-UNI-ACT</td>';
				$result .= '<td class="grid_cabecera" style="color:white;">VAL-TOT-ACT</td>';
		    	}

		    	$result .= '</tr>';

		    	foreach($almacen['articulos'] as $art_codigo => $articulo) {
				$result .= '<tr>';
				$result .= '<td class="grid_detalle_impar" bgcolor="#F4FA58" style="font-weight:bold">' . htmlentities($art_codigo) . '</td>';
				$result .= '<td class="grid_detalle_impar" bgcolor="#F4FA58" style="font-weight:bold" colspan="5">' . htmlentities(FormProcesModel::obtenerDescripcion($art_codigo)) . '</td>';
				$result .= '<td class="grid_detalle_impar" bgcolor="#F4FA58" style="font-weight:bold">' . htmlentities($articulo['saldoinicial']['cant_anterior']) . '</td>';
		
				if ($tipo == "CONTABLE") {
				    	$result .= '<td class="grid_detalle_impar" bgcolor="#F4FA58" style="font-weight:bold">' . htmlentities($articulo['saldoinicial']['unit_anterior']) . '</td>';
				    	$result .= '<td class="grid_detalle_impar" bgcolor="#F4FA58" style="font-weight:bold" colspan="7">&nbsp;</td>';
				} else
				    	$result .= '<td class="grid_detalle_impar" bgcolor="#F4FA58" style="font-weight:bold" colspan="3">&nbsp;</td>';

				if ($tipo == "CONTABLE")
				    	$result .= '<td class="grid_detalle_impar" bgcolor="#F4FA58" style="font-weight:bold">' . htmlentities($articulo['saldoinicial']['costo_total']) . '</td>';

				$result .= '</tr>';
				
				$x_color = -1;
				foreach($articulo['movimientos'] as $i => $movimiento) {
						$x_color++;
						$color = ($x_color%2==0?"grid_detalle_par":"grid_detalle_impar");
			    		$result .= '<tr>';
			    		$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_fecha']) . '</td>';
			    		$result .= '<td class="'.$color.'">' . htmlentities($movimiento['tran_codigo']) . '</td>';
			    		$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_numero']) . '</td>';
			    		$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_almacen']) . '</td>';
			    		$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_entidad']) . '</td>';
			    		$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_docurefe']) . '</td>';
			    		$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_cant_anterior']) . '</td>';
			    
			    	if ($tipo == "CONTABLE")
					$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_val_ant']) . '</td>';

			    	$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_cant_entrada']) . '</td>';
			    
			    	if ($tipo == "CONTABLE")
					$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_cost_entrada']) . '</td>';

			    	$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_cant_salida']) . '</td>';
			    
			    	if ($tipo == "CONTABLE") {
					$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_cost_salida']) . '</td>';
					$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_costounitario']) . '</td>';
			    	}	    

			    	$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_cant_actual']) . '</td>';
			    
			    	if ($tipo == "CONTABLE") {
					$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_val_unit_act']) . '</td>';
					//$result .= '<h1>' . $movimiento['mov_total_act'] . '</h1>';
					$result .= '<td class="'.$color.'">' . htmlentities($movimiento['mov_total_act']) . '</td>';
			    	}
			}
			$result .= '<tr>';
		
			if ($tipo == "CONTABLE")
			    	$result .= '<td colspan="8">&nbsp;</td>';
			else
			    	$result .= '<td colspan="7">&nbsp;</td>';

			$result .= '<td>' . htmlentities($articulo['totales']['cant_entrada']) . '</td>';

			if ($tipo == "CONTABLE")
					$result .= '<td>' . htmlentities($articulo['totales']['cost_entrada']) . '</td>';

			$result .= '<td>' . htmlentities($articulo['totales']['cant_salida']) . '</td>';
		
			if ($tipo == "CONTABLE") {
					$result .= '<td>' . htmlentities($articulo['totales']['cost_salida']) . '</td>';
					$result .= '<td colspan="3">&nbsp;</td>';
			
					$result .= '<td>' . htmlentities($articulo['totales']['valor_total']) . '</td>';
			}
			$result .= '</tr>';
			}
			$result .= '</table>';
		}
		return $result;
    	}
    
    function reportePDF($resultado, $desde, $hasta, $tipo)
    {
	$cabecera2 = Array(
		"mov_fecha"		=>	"FECHA",
		"tran_codigo"		=>	"FORMULARIO",
		"mov_numero"		=>	"NUMERO",
		"mov_almacen"		=>	"ORIGEN/DESTINO",
		"mov_entidad"		=>	"CLIENTE/PROVEEDOR",
		"mov_docurefe"		=>	"No.REF",
		"mov_cant_anterior"	=>	"CANT-ANT",
		"mov_val_ant"		=>	"VAL-UNIT-ANT",
		"mov_cant_entrada"	=>	"CANT-ENTRADA",
		"mov_cost_entrada"	=>	"COST-ENTRADA",
		"mov_cant_salida"	=>	"CANT-SALIDA",
		"mov_cost_salida"	=>	"COST-SALIDA",
		"mov_costounitario"	=>	"COSTO-MVMTO",
		"mov_cant_actual"	=>	"CANT-ACTUAL",
		"mov_val_unit_act"	=>	"VAL-UNI-ACT",
		"mov_total_act"		=>	"VAL-TOT-ACT"
	    );
	
	$cabecera1 = Array(
		"art_codigo"		=>	"CODIGO",
		"art_descripcion"	=>	"DESCRIPCION-ARTICULO",
		"cant_anterior"		=>	" ",
		"unit_anterior"		=>	" ",
		"dummy"			=>	" ",
		"costo_total"		=>	" "
	);
	
	$fontsize = 6;

	$reporte = new CReportes2("L");

	if ($tipo == "CONTABLE") {
	    $reporte->definirColumna("mov_fecha", $reporte->TIPO_TEXTO, 10, "L");
	    $reporte->definirColumna("tran_codigo", $reporte->TIPO_TEXTO, 18, "L");
	    $reporte->definirColumna("mov_numero", $reporte->TIPO_TEXTO, 10, "L");
	    $reporte->definirColumna("mov_almacen", $reporte->TIPO_TEXTO, 18, "L");
	    $reporte->definirColumna("mov_entidad", $reporte->TIPO_TEXTO, 25, "L");
	    $reporte->definirColumna("mov_docurefe", $reporte->TIPO_TEXTO, 10, "L");
	    $reporte->definirColumna("mov_cant_anterior", $reporte->TIPO_CANTIDAD, 12, "R");
	    $reporte->definirColumna("mov_val_ant", $reporte->TIPO_COSTO, 12, "R");
	    $reporte->definirColumna("mov_cant_entrada", $reporte->TIPO_CANTIDAD, 12, "R");
	    $reporte->definirColumna("mov_cost_entrada", $reporte->TIPO_COSTO, 12, "R");
	    $reporte->definirColumna("mov_cant_salida", $reporte->TIPO_CANTIDAD, 12, "R");
	    $reporte->definirColumna("mov_cost_salida", $reporte->TIPO_COSTO, 12, "R");
	    $reporte->definirColumna("mov_costounitario", $reporte->TIPO_COSTO, 12, "R");
	    $reporte->definirColumna("mov_cant_actual", $reporte->TIPO_CANTIDAD, 12, "R");
	    $reporte->definirColumna("mov_val_unit_act", $reporte->TIPO_COSTO, 12, "R");
	    $reporte->definirColumna("mov_total_act", $reporte->TIPO_COSTO, 12, "R");

	    $reporte->definirColumna("art_codigo", $reporte->TIPO_TEXTO, 15, "L", "_saldoinicial");
	    $reporte->definirColumna("art_descripcion", $reporte->TIPO_TEXTO, 80, "L", "_saldoinicial");
	    $reporte->definirColumna("cant_anterior", $reporte->TIPO_CANTIDAD, 12, "R", "_saldoinicial");
	    $reporte->definirColumna("unit_anterior", $reporte->TIPO_COSTO, 12, "R", "_saldoinicial");
	    $reporte->definirColumna("dummy", $reporte->TIPO_TEXTO, 90, "L", "_saldoinicial");
	    $reporte->definirColumna("costo_total", $reporte->TIPO_COSTO, 12, "R", "_saldoinicial");

	    $reporte->definirColumna("dummy1", $reporte->TIPO_TEXTO, 122, "R", "_totales");
	    $reporte->definirColumna("cant_entrada", $reporte->TIPO_CANTIDAD, 12, "R", "_totales");
	    $reporte->definirColumna("cost_entrada", $reporte->TIPO_COSTO, 12, "R", "_totales");
	    $reporte->definirColumna("cant_salida", $reporte->TIPO_CANTIDAD, 12, "R", "_totales");
	    $reporte->definirColumna("cost_salida", $reporte->TIPO_COSTO, 12, "R", "_totales");
	    $reporte->definirColumna("dummy2", $reporte->TIPO_TEXTO, 38, "R", "_totales");
	    $reporte->definirColumna("valor_total", $reporte->TIPO_COSTO, 12, "R", "_totales");
	}
	else {
	    $reporte->definirColumna("mov_fecha", $reporte->TIPO_TEXTO, 10, "L");
	    $reporte->definirColumna("tran_codigo", $reporte->TIPO_TEXTO, 18, "L");
	    $reporte->definirColumna("mov_numero", $reporte->TIPO_TEXTO, 10, "L");
	    $reporte->definirColumna("mov_almacen", $reporte->TIPO_TEXTO, 18, "L");
	    $reporte->definirColumna("mov_entidad", $reporte->TIPO_TEXTO, 25, "L");
	    $reporte->definirColumna("mov_docurefe", $reporte->TIPO_TEXTO, 10, "L");
	    $reporte->definirColumna("mov_cant_anterior", $reporte->TIPO_CANTIDAD, 12, "R");
	    $reporte->definirColumna("mov_cant_entrada", $reporte->TIPO_CANTIDAD, 12, "R");
	    $reporte->definirColumna("mov_cant_salida", $reporte->TIPO_CANTIDAD, 12, "R");
	    $reporte->definirColumna("mov_cant_actual", $reporte->TIPO_CANTIDAD, 12, "R");

	    $reporte->definirColumna("art_codigo", $reporte->TIPO_TEXTO, 15, "L", "_saldoinicial");
	    $reporte->definirColumna("art_descripcion", $reporte->TIPO_TEXTO, 80, "L", "_saldoinicial");
	    $reporte->definirColumna("cant_anterior", $reporte->TIPO_CANTIDAD, 12, "R", "_saldoinicial");
	    $reporte->definirColumna("dummy1", $reporte->TIPO_TEXTO, 122, "R", "_totales");
	    $reporte->definirColumna("cant_entrada", $reporte->TIPO_CANTIDAD, 12, "R", "_totales");
	    $reporte->definirColumna("cant_salida", $reporte->TIPO_CANTIDAD, 12, "R", "_totales");
	}

	$begline = 1;
	$reporte->definirCabecera(($begline), "L", "sistemaweb-OF.CENT.");
	$reporte->definirCabecera(($begline), "C", "ANALISIS DE ACTUALIZACION DE STOCK DEL " . $desde . " AL " . $hasta);
	$reporte->definirCabecera(($begline), "R", "PAG.%p");
	//$reporte->definirCabecera(($begline+1), "R", "%f");
	$reporte->definirCabecera(($begline+1), "R", $hasta);

	$reporte->definirCabeceraPredeterminada($cabecera1, "_saldoinicial");
	$reporte->definirCabeceraPredeterminada($cabecera2);
	
	$reporte->SetFont("courier", "", $fontsize);
	$reporte->SetMargins(0,10,0);
	$reporte->SetAutoPageBreak(true,10);

	$formularios = FormProcesModel::ObtenerTiposFormularios();

	foreach($resultado['almacenes'] as $mov_almacen => $almacen) {
	    $reporte->definirCabecera(($begline+1), "C", $mov_almacen . " " . FormProcesModel::obtenerDescripcionAlmacen($mov_almacen));
	    $reporte->AddPage();

	    foreach($almacen['articulos'] as $art_codigo => $articulo) {
		$articulo['saldoinicial']['art_codigo'] = $art_codigo;
		$articulo['saldoinicial']['art_descripcion'] = FormProcesModel::obtenerDescripcion($art_codigo);
		
		$reporte->nuevaFila($articulo['saldoinicial'], "_saldoinicial");
		foreach($articulo['movimientos'] as $i => $movimiento) {
		    $reporte->nuevaFila($movimiento);
		}
		
		$reporte->nuevaFila($articulo['totales'], "_totales");
	    }
	}
	
	//$reporte->Output();
	$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/Kardex.pdf", "F");
        return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/Kardex.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
   
    }
}

