<?php
class ExistenciasTemplate extends Template {
	function titulo() {
		return '<h2 style="color:#336699" align="center"><b>Existencia de Combustibles</b></h2>';
	}

	function formSearch() {
		$hoy = date("d/m/Y");
		$tipos = Array("VENTAS"	=>"Con venta promedio","CAPACIDAD"=>"Con capacidad de tanques");
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.EXISTENCIAS"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "search"));
		$form->addGroup("FORM_GROUP_FECHA", "A la fecha:");
		$form->addElement("FORM_GROUP_FECHA", new form_element_text("", "fecha", $hoy, '', '', 10, 12));
		$form->addElement("FORM_GROUP_FECHA", new form_element_anytext('<a href="javascript:show_calendar('."'Buscar.fecha'".');"><img src="/sistemaweb/images/showcalendar.gif"  border="0" align="top"/></a>'));
		$form->addElement("FORM_GROUP_FECHA", new form_element_anytext('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addGroup("FORM_GROUP_BOTONES", "");
		$form->addElement("FORM_GROUP_BOTONES", new form_element_submit("submit", "Buscar","","",""));

		return $form->getForm();
	}

	function listado($resultado, $fecha,$a) {
		$result  = '<div align="center"><button name="fm" value="" onClick="javascript:parent.location.href=\'control.php?rqst=REPORTES.EXISTENCIAS&fecha=' . htmlentities($fecha).'&action=PDF\';return false"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button></div><br>';
		$result .= '<table border="0" align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera" colspan="17">EXISTENCIA DE COMBUSTIBLES AL: ' . htmlentities($fecha) . '</td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td class="grid_cabecera">ESTACION</td>';
		$result .= '<td class="grid_cabecera">'.$a[0].'</td>';
		$result .= '<td class="grid_cabecera">(%)</td>';
		$result .= '<td class="grid_cabecera">'.$a[1].'</td>';
		$result .= '<td class="grid_cabecera">(%)</td>';
		$result .= '<td class="grid_cabecera">'.$a[3].'</td>';
		$result .= '<td class="grid_cabecera">(%)</td>';
		$result .= '<td class="grid_cabecera">'.$a[4].'</td>';
		$result .= '<td class="grid_cabecera">(%)</td>';
		$result .= '<td class="grid_cabecera">'.$a[2].'</td>';
		$result .= '<td class="grid_cabecera">(%)</td>';
		//$result .= '<td>D1</td>';
		//$result .= '<td>(%)</td>';
		$result .= '<td class="grid_cabecera">TOT.COMB.</td>';
		$result .= '<td class="grid_cabecera">(%)</td>';
		$result .= '<td class="grid_cabecera">GLP</td>';
		$result .= '<td class="grid_cabecera">(%)</td>';
		$result .= '</tr>';

		foreach($resultado['sucursales'] as $ch_sucursal => $z) {
			$result .= '<tr>';
			$result .= '<td class="grid_cabecera">' . htmlentities($ch_sucursal) . '</td>';
			$productos = $z['productos'];
			$result .= '<td>' . htmlentities($productos['11620301_medicion']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620301_porcentaje']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620302_medicion']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620302_porcentaje']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620303_medicion']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620303_porcentaje']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620304_medicion']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620304_porcentaje']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620305_medicion']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620305_porcentaje']) . '</td>';
			//$result .= '<td>' . htmlentities($productos['11620306_medicion']) . '</td>';
			//$result .= '<td>' . htmlentities($productos['11620306_porcentaje']) . '</td>';
			$result .= '<td class="grid_cabecera">' . htmlentities($z['totales']['medicion']) . '</td>';
			$result .= '<td class="grid_cabecera">' . htmlentities($z['totales']['porcentaje']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620307_medicion']) . '</td>';
			$result .= '<td>' . htmlentities($productos['11620307_porcentaje']) . '</td>';
			$result .= '</tr>';
			$result .= '<tr>';
			$result .= '<td class="grid_cabecera">Capacidad</td>';
			$result .= '<td>' . htmlentities($productos['11620301_capacidad']) . '</td>';
			$result .= '<td>&nbsp;</td>';
			$result .= '<td>' . htmlentities($productos['11620302_capacidad']) . '</td>';
			$result .= '<td>&nbsp;</td>';
			$result .= '<td>' . htmlentities($productos['11620303_capacidad']) . '</td>';
			$result .= '<td>&nbsp;</td>';
			$result .= '<td>' . htmlentities($productos['11620304_capacidad']) . '</td>';
			$result .= '<td>&nbsp;</td>';
			$result .= '<td>' . htmlentities($productos['11620305_capacidad']) . '</td>';
			$result .= '<td>&nbsp;</td>';
			//$result .= '<td>' . htmlentities($productos['11620306_capacidad']) . '</td>';
			//$result .= '<td>&nbsp;</td>';
			$result .= '<td class="grid_cabecera">' . htmlentities($z['totales']['capacidad']) . '</td>';
			$result .= '<td>&nbsp;</td>';
			$result .= '<td>' . htmlentities($productos['11620307_capacidad']) . '</td>';
			$result .= '<td>&nbsp;</td>';
			$result .= '</tr>';
			$result .= '<tr><td colspan="17">&nbsp;</td></tr>';
		}

		//	$result .= '
		$result .= '</table>';
		return $result;
	}

	function reportePDF($resultado, $fecha, $tipo) {
		$cabecera = Array(
			"ESTACION"			=> "ESTACION",
			"11620301_medicion"	=>	"84",
			"11620301_porcentaje"	=>	"(%)",
			"11620302_medicion"	=>	"90",
			"11620302_porcentaje"	=>	"(%)",
			"11620305_medicion"	=>	"95",
			"11620305_porcentaje"	=>	"(%)",
			"11620303_medicion"	=>	"97",
			"11620303_porcentaje"	=>	"(%)",
			"11620304_medicion"	=>	"D2",
			"11620304_porcentaje"	=>	"(%)",
			//"11620306_medicion"		=>	"D1",
			//"11620306_porcentaje"	=>	"(%)",
			"TOTAL_medicion"		=>	"TOT.COMB.",
			"TOTAL_porcentaje"		=>	"(%)",
			"11620307_medicion"		=>	"11620307",
			"11620307_porcentaje"		=>	"(%)"
		);

		$reporte = new CReportes2();	
		$reporte->definirColumna("ESTACION", $reporte->TIPO_TEXTO, 12, "L");
		$reporte->definirColumna("11620301_medicion", $reporte->TIPO_TEXTO, 7, "R");
		$reporte->definirColumna("11620301_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
		$reporte->definirColumna("11620302_medicion", $reporte->TIPO_TEXTO, 7, "R");
		$reporte->definirColumna("11620302_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
		$reporte->definirColumna("11620305_medicion", $reporte->TIPO_TEXTO, 7, "R");
		$reporte->definirColumna("11620305_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
		$reporte->definirColumna("11620303_medicion", $reporte->TIPO_TEXTO, 7, "R");
		$reporte->definirColumna("11620303_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
		$reporte->definirColumna("11620304_medicion", $reporte->TIPO_TEXTO, 7, "R");
		$reporte->definirColumna("11620304_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
		//$reporte->definirColumna("11620306_medicion", $reporte->TIPO_TEXTO, 7, "R");
		//$reporte->definirColumna("11620306_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
		$reporte->definirColumna("TOTAL_medicion", $reporte->TIPO_TEXTO, 7, "R");
		$reporte->definirColumna("TOTAL_porcentaje", $reporte->TIPO_TEXTO, 3, "C");
		$reporte->definirColumna("11620307_medicion", $reporte->TIPO_TEXTO, 7, "R");
		$reporte->definirColumna("11620307_porcentaje", $reporte->TIPO_TEXTO, 3, "C");

		$reporte->definirColumna("CAPACIDAD", $reporte->TIPO_TEXTO, 12, "L", "_capacidad");
		$reporte->definirColumna("11620301_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
		$reporte->definirColumna("dummy84", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
		$reporte->definirColumna("11620302_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
		$reporte->definirColumna("dummy90", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
		$reporte->definirColumna("11620305_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
		$reporte->definirColumna("dummy95", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
		$reporte->definirColumna("11620303_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
		$reporte->definirColumna("dummy97", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
		$reporte->definirColumna("11620304_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
		$reporte->definirColumna("dummyd2", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
		//$reporte->definirColumna("11620306_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
		//$reporte->definirColumna("dummyd1", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
		$reporte->definirColumna("TOTAL_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");
		$reporte->definirColumna("dummytot", $reporte->TIPO_TEXTO, 3, "C", "_capacidad");
		$reporte->definirColumna("11620307_capacidad", $reporte->TIPO_TEXTO, 7, "R", "_capacidad");

		$reporte->definirCabecera(1, "L", "SISTEMA WEB");
		$reporte->definirCabecera(1, "R", "%f");
		$reporte->definirCabecera(2, "C", "EXISTENCIA DE COMBUSTIBLES AL: " . $fecha);

		$reporte->definirCabeceraPredeterminada($cabecera);

		$reporte->SetFont("courier", "", 7);
		$reporte->AddPage();

		foreach($resultado['sucursales'] as $cod_sucursal => $sucursal) {
			$fila = $sucursal['productos'];

			$fila['ESTACION'] = $cod_sucursal;
			$fila['TOTAL_medicion'] = $sucursal['totales']['medicion'];
			$fila['TOTAL_porcentaje'] = $sucursal['totales']['porcentaje'];
			$reporte->nuevaFila($fila);

			$fila['CAPACIDAD'] = "Capacidad";
			$fila['TOTAL_capacidad'] = $sucursal['totales']['capacidad'];
			$reporte->nuevaFila($fila, "_capacidad");
			$reporte->Ln();
		}

		$reporte->lineaH();

		$resultado['totales']['ESTACION'] = "TOTALES:";
		$reporte->nuevaFila($resultado['totales']);

		$resultado['totales']['CAPACIDAD'] = "Capacidad";
		$reporte->nuevaFila($resultado['totales'], "_capacidad");

		$reporte->lineaH();

		$reporte->Output();
	}
}
