<?php

class ComprasTemplate extends Template{

	function titulo() {
		return '<div align="center"><h2><b>Compras de combustibles</b></h2></div>';
    	}

	function form_search($f_desde, $f_hasta, $estaciones) {


		if ($estaciones == "") 
			$estaciones = $_SESSION['almacen'];

		$estaciones		= ComprasModel::obtenerSucursales("");
		$estaciones['TODAS']	= "Todas las estaciones";

		$form = new form2('', "form_compras", FORM_METHOD_POST, "control.php", '', "control");	
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.COMPRAS"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Almac&eacute;n: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estaciones", "", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>Detallado?'));
//		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox("detallado", "Detallado?", "SI", "", "", true));
	        $form->addElement(FORM_GROUP_MAIN, new form_element_checkbox("detallado", "detallado", "SI", '', '', '', true));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table><br><table align="center" border="0"><tr><td align="center">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "", $f_desde, '', 12, 10));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_compras.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:center"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1500;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "", $f_hasta, '', 12, 10));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_compras.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:center"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1500;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table><br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<p align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="pdf"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</p>'));
		
		return $form->getForm();
    	}
    
    	function listado($results, $desde, $hasta, $detallado) {
		$result =  '<table style="width:1500px" align="center" border="0">';
		$result .= '<tr>';
		//$result .= '<td colspan="27" align="center"><p style="font-size:12px; color:black;"><b>Reporte de compras de combustibles</td>';
		$result .= '<tr>';
		$result .= '<td class="grid_cabecera">Fecha</td>';
		$result .= '<td class="grid_cabecera">Proveedor</td>';
		$result .= '<td class="grid_cabecera">Nro.Factura</td>';
		$result .= '<td class="grid_cabecera">Num.Ord.</td>';
		$result .= '<td class="grid_cabecera" colspan="3" align="center">84 OCTANOS</td>';
		$result .= '<td class="grid_cabecera" colspan="3" align="center">90 OCTANOS</td>';
		$result .= '<td class="grid_cabecera" colspan="3" align="center">95 OCTANOS</td>';
		$result .= '<td class="grid_cabecera" colspan="3" align="center">97 OCTANOS</td>';
		$result .= '<td class="grid_cabecera" colspan="3" align="center">D2 DIESEL</td>';
//		$result .= '<td class="theader" colspan="3" align="center">D1 KEROSENE</td>';
		$result .= '<td class="grid_cabecera" colspan="3" align="center">GLP</td>';
//		$result .= '<td class="theader">Canon</td>';
		$result .= '<td class="grid_cabecera">Utilidad</td>';
		$result .= '<td class="grid_cabecera">SCOP</td>';
		$result .= '</tr>';
		$result .= '<tr>';
		$result .= '<td class="grid_cabecera" colspan="4">&nbsp;</td>';
		$result .= '<td class="grid_cabecera">Costo U.</td>';
		$result .= '<td class="grid_cabecera">Galones</td>';
		$result .= '<td class="grid_cabecera">Utilidad</td>';
		$result .= '<td class="grid_cabecera">Costo U.</td>';
		$result .= '<td class="grid_cabecera">Galones</td>';
		$result .= '<td class="grid_cabecera">Utilidad</td>';
		$result .= '<td class="grid_cabecera">Costo U.</td>';
		$result .= '<td class="grid_cabecera">Galones</td>';
		$result .= '<td class="grid_cabecera">Utilidad</td>';
		$result .= '<td class="grid_cabecera">Costo U.</td>';
		$result .= '<td class="grid_cabecera">Galones</td>';
		$result .= '<td class="grid_cabecera">Utilidad</td>';
		$result .= '<td class="grid_cabecera">Costo U.</td>';
		$result .= '<td class="grid_cabecera">Galones</td>';
		$result .= '<td class="grid_cabecera">Utilidad</td>';
		/*$result .= '<td class="theader">Costo U.</td>';
		$result .= '<td class="theader">Galones</td>';
		$result .= '<td class="theader">Utilidad</td>';*/
		$result .= '<td class="grid_cabecera">Costo U.</td>';
		$result .= '<td class="grid_cabecera">Litros</td>';
		$result .= '<td class="grid_cabecera">Utilidad</td>';
		$result .= '<td class="grid_cabecera">Prov.</td>';
		$result .= '<td class="grid_cabecera">Total</td>';
//		$result .= '<td class="theader">&nbsp;</td>';
		$result .= '</tr>';

		$i = 0;

		foreach($results['estaciones'] as $cod_estacion=>$movimientos) {
		    	$result .= '<tr>';
		    	$result .= '<td class="bgcolor_cabecera" colspan="24">Almac&eacute;n: ' . htmlentities($cod_estacion) . '</td>';
		    	$result .= '</tr>';
		    
		    	foreach($movimientos['movimientos'] as $mov_numero=>$movimiento) {

					$i++;
					$estilo = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

					$result .= '<tr>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$movimiento['fecha']) . '</td>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$movimiento['noproveedor']) . '</td>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$movimiento['factura']) . '</td>';
					$result .= '<td class="'.$estilo.'">' . htmlentities(@$movimiento['orden']) . '</td>';
			
					/* 84 */
					if(!empty($movimiento['11620301_cantidad'])){
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620301_costo'], 3, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620301_cantidad'], 2, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620301_utilidad'], 2, '.', ',')) . '&nbsp;</td>';
					}else{
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
					}

					/* 90 */
					if(!empty($movimiento['11620302_cantidad'])){
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620302_costo'], 3, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620302_cantidad'], 2, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620302_utilidad'], 2, '.', ',')) . '&nbsp;</td>';
					}else{
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
					}

					/* 95 */
					if(!empty($movimiento['11620305_cantidad'])){
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620305_costo'], 3, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620305_cantidad'], 2, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620305_utilidad'], 2, '.', ',')) . '&nbsp;</td>';
					}else{
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
					}

					/* 97 */
					if(!empty($movimiento['11620303_cantidad'])){
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620303_costo'], 3, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620303_cantidad'], 2, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620303_utilidad'], 2, '.', ',')) . '&nbsp;</td>';
					}else{
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
					}

					/* D2 */
					if(!empty($movimiento['11620304_cantidad'])){
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620304_costo'], 3, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620304_cantidad'], 2, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620304_utilidad'], 2, '.', ',')) . '&nbsp;</td>';
					}else{
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
					}

					/* D1 */
					/*$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620306_costo'], 3, '.', ',')) . '&nbsp;</td>';
					$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620306_cantidad'], 2, '.', ',')) . '&nbsp;</td>';
					$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620306_utilidad'], 2, '.', ',')) . '&nbsp;</td>';*/

					/* GLP */
					if(!empty($movimiento['11620307_cantidad'])){
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620307_costo'], 3, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620307_cantidad'], 2, '.', ',')) . '&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['11620307_utilidad'], 2, '.', ',')) . '&nbsp;</td>';
					}else{
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
						$result .= '<td class="'.$estilo.'" align="right">&nbsp;</td>';
					}

	//				$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['participacion'], 2, '.', ',')) . '&nbsp;</td>';
					$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(number_format(@$movimiento['utilidad'], 2, '.', ',')) . '&nbsp;</td>';

					$result .= '<td class="'.$estilo.'" align="right">' . htmlentities(@$movimiento['scop']) . '&nbsp;</td>';

					$result .= '</tr>';
		    	}
		    
		    	$result .= '<tr>';
		    	$result .= '<td class="bgcolor_cabecera" colspan="4" align="right">Total CC ' . htmlentities($cod_estacion) . ': </td>';

				$costo84 = ($movimientos['totales']['11620301_total'] / $movimientos['totales']['11620301_cantidad']);
				$costo90 = ($movimientos['totales']['11620302_total'] / $movimientos['totales']['11620302_cantidad']);
				$costo95 = ($movimientos['totales']['11620305_total'] / $movimientos['totales']['11620305_cantidad']);
				$costo97 = ($movimientos['totales']['11620303_total'] / $movimientos['totales']['11620303_cantidad']);
				$costod2 = ($movimientos['totales']['11620304_total'] / $movimientos['totales']['11620304_cantidad']);
				$costoglp = ($movimientos['totales']['11620307_total'] / $movimientos['totales']['11620307_cantidad']);

				/* 84 */
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costo84, 3, '.', ',')) . '</td>';
		   		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620301_cantidad'], 2, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620301_utilidad'], 2, '.', ',')) . '</td>';

		    	/* 90 */
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costo90, 3, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620302_cantidad'], 2, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620302_utilidad'], 2, '.', ',')) . '</td>';

		    	/* 95 */
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costo95, 3, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620305_cantidad'], 2, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620305_utilidad'], 2, '.', ',')) . '</td>';

		    	/* 97 */
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costo97, 3, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620303_cantidad'], 2, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620303_utilidad'], 2, '.', ',')) . '</td>';

		    	/* D2 */
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costod2, 3, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620304_cantidad'], 2, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620304_utilidad'], 2, '.', ',')) . '</td>';

		    	/* D1 */
		    	/*$result .= '<td class="theader" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620306_costo'], 3, '.', ',')) . '</td>';
		    	$result .= '<td class="theader" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620306_cantidad'], 2, '.', ',')) . '</td>';
		    	$result .= '<td class="theader" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620306_utilidad'], 2, '.', ',')) . '</td>';*/

		    	/* GLP */
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costoglp, 3, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620307_cantidad'], 2, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['11620307_utilidad'], 2, '.', ',')) . '</td>';
		    
//		    	$result .= '<td class="theader" align="right">' . htmlentities(number_format(@$movimientos['totales']['participacion'], 2, '.', ',')) . '</td>';
		    	$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$movimientos['totales']['utilidad'], 2, '.', ',')) . '</td>';

			$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(@$movimiento['scop']) . '&nbsp;</td>';

		    	$result .= '</tr>';
		}

		$result .= '<tr>';
	
		$result .= '<td class="bgcolor_cabecera" colspan="4" align="right">Total General: </td>';

		$costo84 = ($results['totales']['11620301_total'] / $results['totales']['11620301_cantidad']);
		$costo90 = ($results['totales']['11620302_total'] / $results['totales']['11620302_cantidad']);
		$costo95 = ($results['totales']['11620305_total'] / $results['totales']['11620305_cantidad']);
		$costo97 = ($results['totales']['11620303_total'] / $results['totales']['11620303_cantidad']);
		$costod2 = ($results['totales']['11620304_total'] / $results['totales']['11620304_cantidad']);
		$costoglp = ($results['totales']['11620307_total'] / $results['totales']['11620307_cantidad']);

		/* 84 */
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costo84, 3, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620301_cantidad'], 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620301_utilidad'], 2, '.', ',')) . '</td>';

		/* 90 */
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costo90, 3, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620302_cantidad'], 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620302_utilidad'], 2, '.', ',')) . '</td>';

		/* 95 */
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costo95, 3, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620305_cantidad'], 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620305_utilidad'], 2, '.', ',')) . '</td>';

		/* 97 */
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costo97, 3, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620303_cantidad'], 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620303_utilidad'], 2, '.', ',')) . '</td>';

		/* D2 */
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costod2, 3, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620304_cantidad'], 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620304_utilidad'], 2, '.', ',')) . '</td>';

		/* D1 */
		/*$result .= '<td class="theader" align="right">' . htmlentities(number_format(@$results['totales']['11620306_costo'], 3, '.', ',')) . '</td>';
		$result .= '<td class="theader" align="right">' . htmlentities(number_format(@$results['totales']['11620306_cantidad'], 2, '.', ',')) . '</td>';
		$result .= '<td class="theader" align="right">' . htmlentities(number_format(@$results['totales']['11620306_utilidad'], 2, '.', ',')) . '</td>';*/

		/* GLP */
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$costoglp, 3, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620307_cantidad'], 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['11620307_utilidad'], 2, '.', ',')) . '</td>';

//		$result .= '<td class="theader" align="right">' . htmlentities(number_format(@$results['totales']['participacion'], 2, '.', ',')) . '</td>';
		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(number_format(@$results['totales']['utilidad'], 2, '.', ',')) . '</td>';

		$result .= '<td class="bgcolor_cabecera" align="right">' . htmlentities(@$movimiento['scop']) . '&nbsp;</td>';

		$result .= '</tr>';
		$result .= '</table>';
	
		return $result;
    	}

    	function reportePDF($results) {
		$reporte = new CReportes2('L');
	
		$cab1 = Array(
			"fecha" 		=> "Fecha",
			"factura"		=> "Nro.Factur",
			"orden"			=> "Num. Orden",
			"11620301"		=> "84 Octanos",
			"11620302"		=> "90 Octanos",
			"11620305"		=> "95 Octanos",
			"11620303"		=> "97 Octanos",
			"11620304"		=> "D2 Diesel",
			"11620306"		=> "D1 Kerosene",
			"11620307"		=> "GLP",
			"participacion"		=> "Participa.",
			"utilidad"		=> "Utilidad",
			"scop"			=> "scop"
		    );
		
		$cab2 = Array(
			"fecha"			=> " ",
			"factura"		=> " ",
			"orden"			=> " ",
			"11620301_costo"	=> "Cost.Unit",
			"11620301_cantidad"	=> "Cantidad",
			"11620301_utilidad"	=> "Utilidad",
			"11620302_costo"	=> "Cost.Unit",
			"11620302_cantidad"	=> "Cantidad",
			"11620302_utilidad"	=> "Utilidad",
			"11620305_costo"	=> "Cost.Unit",
			"11620305_cantidad"	=> "Cantidad",
			"11620305_utilidad"	=> "Utilidad",
			"11620303_costo"	=> "Cost.Unit",
			"11620303_cantidad"	=> "Cantidad",
			"11620303_utilidad"	=> "Utilidad",
			"11620304_costo"	=> "Cost.Unit",
			"11620304_cantidad"	=> "Cantidad",
			"11620304_utilidad"	=> "Utilidad",
			"11620306_costo"	=> "Cost.Unit",
			"11620306_cantidad"	=> "Cantidad",
			"11620306_utilidad"	=> "Utilidad",
			"11620307_costo"	=> "Cost.Unit",
			"11620307_cantidad"	=> "Cantidad",
			"11620307_utilidad"	=> "Utilidad",
			"participacion"		=> " ",
			"utilidad"		=> " ",
			"scop"			=> " "
		    );
		    

		$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirColumna("factura", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirColumna("orden", $reporte->TIPO_TEXTO, 8, "L");
		$reporte->definirColumna("11620301_costo", $reporte->TIPO_COSTO, 10, "R");
		$reporte->definirColumna("11620301_cantidad", $reporte->TIPO_CANTIDAD, 10, "R");
		$reporte->definirColumna("11620301_utilidad", $reporte->TIPO_IMPORTE, 10, "R");
		$reporte->definirColumna("11620302_costo", $reporte->TIPO_COSTO, 10, "R");
		$reporte->definirColumna("11620302_cantidad", $reporte->TIPO_CANTIDAD, 10, "R");
		$reporte->definirColumna("11620302_utilidad", $reporte->TIPO_IMPORTE, 10, "R");
		$reporte->definirColumna("11620305_costo", $reporte->TIPO_COSTO, 10, "R");
		$reporte->definirColumna("11620305_cantidad", $reporte->TIPO_CANTIDAD, 10, "R");
		$reporte->definirColumna("11620305_utilidad", $reporte->TIPO_IMPORTE, 10, "R");
		$reporte->definirColumna("11620303_costo", $reporte->TIPO_COSTO, 10, "R");
		$reporte->definirColumna("11620303_cantidad", $reporte->TIPO_CANTIDAD, 10, "R");
		$reporte->definirColumna("11620303_utilidad", $reporte->TIPO_IMPORTE, 10, "R");
		$reporte->definirColumna("11620304_costo", $reporte->TIPO_COSTO, 10, "R");
		$reporte->definirColumna("11620304_cantidad", $reporte->TIPO_CANTIDAD, 10, "R");
		$reporte->definirColumna("11620304_utilidad", $reporte->TIPO_IMPORTE, 10, "R");
		$reporte->definirColumna("11620306_costo", $reporte->TIPO_COSTO, 9, "R");
		$reporte->definirColumna("11620306_cantidad", $reporte->TIPO_CANTIDAD, 9, "R");
		$reporte->definirColumna("11620306_utilidad", $reporte->TIPO_IMPORTE, 9, "R");
		$reporte->definirColumna("11620307_costo", $reporte->TIPO_COSTO, 10, "R");
		$reporte->definirColumna("11620307_cantidad", $reporte->TIPO_CANTIDAD, 10, "R");
		$reporte->definirColumna("11620307_utilidad", $reporte->TIPO_IMPORTE, 10, "R");
		$reporte->definirColumna("participacion", $reporte->TIPO_IMPORTE, 5, "R");
		$reporte->definirColumna("utilidad", $reporte->TIPO_IMPORTE, 9, "R");
		$reporte->definirColumna("scop", $reporte->TIPO_IMPORTE, 7, "L");
	
		$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 10, "L", "head1");
		$reporte->definirColumna("factura", $reporte->TIPO_TEXTO, 10, "L", "head1");
		$reporte->definirColumna("orden", $reporte->TIPO_TEXTO, 8, "L", "head1");
		$reporte->definirColumna("11620301", $reporte->TIPO_TEXTO, 32, "C", "head1");
		$reporte->definirColumna("11620302", $reporte->TIPO_TEXTO, 32, "C", "head1");
		$reporte->definirColumna("11620305", $reporte->TIPO_TEXTO, 32, "C", "head1");
		$reporte->definirColumna("11620303", $reporte->TIPO_TEXTO, 32, "C", "head1");
		$reporte->definirColumna("11620304", $reporte->TIPO_TEXTO, 32, "C", "head1");
		$reporte->definirColumna("11620306", $reporte->TIPO_TEXTO, 29, "C", "head1");
		$reporte->definirColumna("11620307", $reporte->TIPO_TEXTO, 32, "C", "head1");
		$reporte->definirColumna("participacion", $reporte->TIPO_IMPORTE, 5, "R", "head1");
		$reporte->definirColumna("utilidad", $reporte->TIPO_IMPORTE, 9, "R", "head1");
		$reporte->definirColumna("scop", $reporte->TIPO_IMPORTE, 7, "L", "head1");

		$reporte->definirColumna("almacen", $reporte->TIPO_TEXTO, 50, "L", "almacen", "B");
	
		$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 30, "L", "total", "B");
		$reporte->definirColumna("11620301_costo", $reporte->TIPO_COSTO, 10, "R", "total", "B");
		$reporte->definirColumna("11620301_cantidad", $reporte->TIPO_CANTIDAD, 10, "R", "total", "B");
		$reporte->definirColumna("11620301_utilidad", $reporte->TIPO_IMPORTE, 10, "R", "total", "B");
		$reporte->definirColumna("11620302_costo", $reporte->TIPO_COSTO, 10, "R", "total", "B");
		$reporte->definirColumna("11620302_cantidad", $reporte->TIPO_CANTIDAD, 10, "R", "total", "B");
		$reporte->definirColumna("11620302_utilidad", $reporte->TIPO_IMPORTE, 10, "R", "total", "B");
		$reporte->definirColumna("11620305_costo", $reporte->TIPO_COSTO, 10, "R", "total", "B");
		$reporte->definirColumna("11620305_cantidad", $reporte->TIPO_CANTIDAD, 10, "R", "total", "B");
		$reporte->definirColumna("11620305_utilidad", $reporte->TIPO_IMPORTE, 10, "R", "total", "B");
		$reporte->definirColumna("11620303_costo", $reporte->TIPO_COSTO, 10, "R", "total", "B");
		$reporte->definirColumna("11620303_cantidad", $reporte->TIPO_CANTIDAD, 10, "R", "total", "B");
		$reporte->definirColumna("11620303_utilidad", $reporte->TIPO_IMPORTE, 10, "R", "total", "B");
		$reporte->definirColumna("11620304_costo", $reporte->TIPO_COSTO, 10, "R", "total", "B");
		$reporte->definirColumna("11620304_cantidad", $reporte->TIPO_CANTIDAD, 10, "R", "total", "B");
		$reporte->definirColumna("11620304_utilidad", $reporte->TIPO_IMPORTE, 10, "R", "total", "B");
		$reporte->definirColumna("11620306_costo", $reporte->TIPO_COSTO, 9, "R", "total", "B");
		$reporte->definirColumna("11620306_cantidad", $reporte->TIPO_CANTIDAD, 9, "R", "total", "B");
		$reporte->definirColumna("11620306_utilidad", $reporte->TIPO_IMPORTE, 9, "R", "total", "B");
		$reporte->definirColumna("11620307_costo", $reporte->TIPO_COSTO, 10, "R", "total", "B");
		$reporte->definirColumna("11620307_cantidad", $reporte->TIPO_CANTIDAD, 10, "R", "total", "B");
		$reporte->definirColumna("11620307_utilidad", $reporte->TIPO_IMPORTE, 10, "R", "total", "B");
		$reporte->definirColumna("participacion", $reporte->TIPO_IMPORTE, 5, "R", "total", "B");
		$reporte->definirColumna("utilidad", $reporte->TIPO_IMPORTE, 9, "R", "total", "B");
		$reporte->definirColumna("scop", $reporte->TIPO_IMPORTE, 7, "L", "B");

		$reporte->definirCabecera(1, "L", "SISTEMAWEB");
		$reporte->definirCabecera(1, "R", "Pag. %p");
		$reporte->definirCabecera(2, "L", "Usuario: %u");
		$reporte->definirCabecera(2, "R", "%f");
		$reporte->definirCabecera(3, "C", "Reporte de Compras de Combustibles");

		$reporte->definirCabeceraPredeterminada($cab1, "head1");
		$reporte->definirCabeceraPredeterminada($cab2);

		$reporte->SetFont("courier", "", 5);
		$reporte->SetMargins(0,0,0);
		$reporte->SetAutoPageBreak(true, 0);
	
		$reporte->AddPage();	      
		foreach($results['estaciones'] as $cod_estacion=>$movimientos) {
		    	$array = Array("almacen" => "Estacion de Servicio: " . $cod_estacion);
		    	$reporte->nuevaFila($array, "almacen");

		    	foreach($movimientos['movimientos'] as $mov_numero=>$movimiento) {
				$reporte->nuevaFila($movimiento);
		    	}
		    
		    	$movimientos['totales']['rotulo'] = "Total CC " . $cod_estacion;
		    	$reporte->nuevaFila($movimientos['totales'], "total");
		    	$reporte->Ln();
		}
	
		$results['totales']['rotulo'] = "Total General";
	
		$reporte->Ln();
		$reporte->lineaH();
		$reporte->nuevaFila($results['totales'], "total");

		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/Compras_Combustible.pdf", "F");

		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/Compras_Combustible.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	
    	}
	
	function reporteExcel($results, $desde, $hasta, $almacen, $detallado) {

		$nomalmacen = ComprasModel::obtenerSucursales($almacen);

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();
		$formato4 =& $workbook->add_format();
		$formato6 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');

		$formato4->set_size(10);
		$formato4->set_align('right');
		$formato6->set_size(10);
		$formato6->set_align('right');
		$formato6->set_bold(1);

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados Compras');
		$worksheet1->set_column(0, 0, 20);
		$worksheet1->set_column(1, 1, 50);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 16);
		$worksheet1->set_column(6, 6, 16);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "COMPRAS DE COMBUSTIBLE",$formato0);
		$worksheet1->write_string(3, 0, "ALMACEN: ".$nomalmacen[$almacen],$formato0);
		$worksheet1->write_string(4, 0, "FECHA DEL ".$desde." AL ".$hasta,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 7;

		$worksheet1->write_string($a, 0, "Fecha",$formato2);
		$worksheet1->write_string($a, 1, "Nro. Factura",$formato2);
		$worksheet1->write_string($a, 2, "Nro. Orden",$formato2);
		$worksheet1->write_string($a, 3, "",$formato2);
		$worksheet1->write_string($a, 4, "84 OCTANOS",$formato2);
		$worksheet1->write_string($a, 5, "",$formato2);
		$worksheet1->write_string($a, 6, "",$formato2);
		$worksheet1->write_string($a, 7, "90 OCTANOS",$formato2);
		$worksheet1->write_string($a, 8, "",$formato2);
		$worksheet1->write_string($a, 9, "",$formato2);
		$worksheet1->write_string($a, 10, "95 OCTANOS",$formato2);
		$worksheet1->write_string($a, 11, "",$formato2);
		$worksheet1->write_string($a, 12, "",$formato2);
		$worksheet1->write_string($a, 13, "97 OCTANOS",$formato2);
		$worksheet1->write_string($a, 14, "",$formato2);
		$worksheet1->write_string($a, 15, "",$formato2);
		$worksheet1->write_string($a, 16, "D2 DIESEL",$formato2);
		$worksheet1->write_string($a, 17, "",$formato2);
		$worksheet1->write_string($a, 18, "",$formato2);
		$worksheet1->write_string($a, 19, "GLP",$formato2);
		$worksheet1->write_string($a, 20, "",$formato2);
		$worksheet1->write_string($a, 21, "Canon",$formato2);
		$worksheet1->write_string($a, 22, "Utilidad",$formato2);
		$worksheet1->write_string($a, 23, "SCOP",$formato2);
		
		$a = 8;

		$worksheet1->write_string($a, 3, "Costo U.",$formato2);
		$worksheet1->write_string($a, 4, "Galones",$formato2);
		$worksheet1->write_string($a, 5, "Utilidad",$formato2);
		$worksheet1->write_string($a, 6, "Costo U.",$formato2);
		$worksheet1->write_string($a, 7, "Galones",$formato2);
		$worksheet1->write_string($a, 8, "Utilidad",$formato2);
		$worksheet1->write_string($a, 9, "Costo U.",$formato2);
		$worksheet1->write_string($a, 10, "Galones",$formato2);
		$worksheet1->write_string($a, 11, "Utilidad",$formato2);
		$worksheet1->write_string($a, 12, "Costo U.",$formato2);
		$worksheet1->write_string($a, 13, "Galones",$formato2);
		$worksheet1->write_string($a, 14, "Utilidad",$formato2);
		$worksheet1->write_string($a, 15, "Costo U.",$formato2);
		$worksheet1->write_string($a, 16, "Galones",$formato2);
		$worksheet1->write_string($a, 17, "Utilidad",$formato2);
		$worksheet1->write_string($a, 18, "Costo U.",$formato2);
		$worksheet1->write_string($a, 19, "Galones",$formato2);
		$worksheet1->write_string($a, 20, "Utilidad",$formato2);
		$worksheet1->write_string($a, 21, "Prov.",$formato2);
		$worksheet1->write_string($a, 22, "Total",$formato2);

		$a = 9;

		foreach($results['estaciones'] as $cod_estacion => $movimientos) {

			$worksheet1->write_string($a, 0, "Almacen: ",$formato6);
			$worksheet1->write_string($a, 1, $cod_estacion,$formato6);

			$a++;

		    	foreach($movimientos['movimientos'] as $mov_numero=>$movimiento) {

				$worksheet1->write_string($a, 0, @$movimiento['fecha'],$formato5);
				$worksheet1->write_string($a, 1, @$movimiento['factura'],$formato5);
				$worksheet1->write_string($a, 2, @$movimiento['orden'],$formato5);

				$worksheet1->write_string($a, 3, number_format(@$movimiento['11620301_costo'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 4, number_format(@$movimiento['11620301_cantidad'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 5, number_format(@$movimiento['11620301_utilidad'],2,'.',''),$formato4);

				$worksheet1->write_string($a, 6, number_format(@$movimiento['11620302_costo'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 7, number_format(@$movimiento['11620302_cantidad'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 8, number_format(@$movimiento['11620302_utilidad'],2,'.',''),$formato4);

				$worksheet1->write_string($a, 9, number_format(@$movimiento['11620305_costo'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 10, number_format(@$movimiento['11620305_cantidad'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 11, number_format(@$movimiento['11620305_utilidad'],2,'.',''),$formato4);

				$worksheet1->write_string($a, 12, number_format(@$movimiento['11620303_costo'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 13, number_format(@$movimiento['11620303_cantidad'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 14, number_format(@$movimiento['11620303_utilidad'],2,'.',''),$formato4);

				$worksheet1->write_string($a, 15, number_format(@$movimiento['11620304_costo'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 16, number_format(@$movimiento['11620304_cantidad'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 17, number_format(@$movimiento['11620304_utilidad'],2,'.',''),$formato4);

				$worksheet1->write_string($a, 18, number_format(@$movimiento['11620307_costo'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 19, number_format(@$movimiento['11620307_cantidad'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 20, number_format(@$movimiento['11620307_utilidad'],2,'.',''),$formato4);

				$worksheet1->write_string($a, 21, number_format(@$movimiento['participacion'],2,'.',''),$formato4);
				$worksheet1->write_string($a, 22, number_format(@$movimiento['utilidad'],2,'.',''),$formato4);

				$a++;

		    	}

			$worksheet1->write_string($a, 0, "Total Almacen: ",$formato6);
			$worksheet1->write_string($a, 1, $cod_estacion,$formato6);

			$worksheet1->write_string($a, 3, number_format(@$movimientos['totales']['11620301_costo'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 4, number_format(@$movimientos['totales']['11620301_cantidad'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 5, number_format(@$movimientos['totales']['11620301_utilidad'],2,'.',''),$formato6);

			$worksheet1->write_string($a, 6, number_format(@$movimientos['totales']['11620302_costo'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 7, number_format(@$movimientos['totales']['11620302_cantidad'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 8, number_format(@$movimientos['totales']['11620302_utilidad'],2,'.',''),$formato6);

			$worksheet1->write_string($a, 9, number_format(@$movimientos['totales']['11620305_costo'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 10, number_format(@$movimientos['totales']['11620305_cantidad'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 11, number_format(@$movimientos['totales']['11620305_utilidad'],2,'.',''),$formato6);

			$worksheet1->write_string($a, 12, number_format(@$movimientos['totales']['11620303_costo'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 13, number_format(@$movimientos['totales']['11620303_cantidad'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 14, number_format(@$movimientos['totales']['11620303_utilidad'],2,'.',''),$formato6);

			$worksheet1->write_string($a, 15, number_format(@$movimientos['totales']['11620304_costo'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 16, number_format(@$movimientos['totales']['11620304_cantidad'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 17, number_format(@$movimientos['totales']['11620304_utilidad'],2,'.',''),$formato6);

			$worksheet1->write_string($a, 18, number_format(@$movimientos['totales']['11620307_costo'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 19, number_format(@$movimientos['totales']['11620307_cantidad'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 20, number_format(@$movimientos['totales']['11620307_utilidad'],2,'.',''),$formato6);

			$worksheet1->write_string($a, 21, number_format(@$movimientos['totales']['participacion'],2,'.',''),$formato6);
			$worksheet1->write_string($a, 22, number_format(@$movimientos['totales']['utilidad'],2,'.',''),$formato6);

			$a++;

		}

		$worksheet1->write_string($a, 1, "Total General: ",$formato6);

		$worksheet1->write_string($a, 3, number_format(@$results['totales']['11620301_costo'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 4, number_format(@$results['totales']['11620301_cantidad'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 5, number_format(@$results['totales']['11620301_utilidad'],2,'.',''),$formato6);

		$worksheet1->write_string($a, 6, number_format(@$results['totales']['11620302_costo'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 7, number_format(@$results['totales']['11620302_cantidad'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 8, number_format(@$results['totales']['11620302_utilidad'],2,'.',''),$formato6);

		$worksheet1->write_string($a, 9, number_format(@$results['totales']['11620305_costo'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 10, number_format(@$results['totales']['11620305_cantidad'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 11, number_format(@$results['totales']['11620305_utilidad'],2,'.',''),$formato6);

		$worksheet1->write_string($a, 12, number_format(@$results['totales']['11620303_costo'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 13, number_format(@$results['totales']['11620303_cantidad'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 14, number_format(@$results['totales']['11620303_utilidad'],2,'.',''),$formato6);

		$worksheet1->write_string($a, 15, number_format(@$results['totales']['11620304_costo'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 16, number_format(@$results['totales']['11620304_cantidad'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 17, number_format(@$results['totales']['11620304_utilidad'],2,'.',''),$formato6);

		$worksheet1->write_string($a, 18, number_format(@$results['totales']['11620307_costo'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 19, number_format(@$results['totales']['11620307_cantidad'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 20, number_format(@$results['totales']['11620307_utilidad'],2,'.',''),$formato6);

		$worksheet1->write_string($a, 21, number_format(@$results['totales']['participacion'],2,'.',''),$formato6);
		$worksheet1->write_string($a, 22, number_format(@$results['totales']['utilidad'],2,'.',''),$formato6);

		$workbook->close();	

		$chrFileName = "Compras_Combustible";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}
