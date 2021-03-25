<?php

/**
 * Modificado por Néstor Hernández Loli el 24/02/2012
 * a motivo de requerimiento de Servigrifos
 */
class LiquidacionTemplate extends Template {

	function search_form() {

		$fecha = date(d . "/" . m . "/" . Y);
		$estaciones = LiquidacionModel::obtieneListaEstaciones();

		$form = new form2("<h4>RESUMEN DE LIQUIDACIONES<h4>", "form_liquidacion", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.LIQUIDACION"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(' . "'form_liquidacion.desde'" . ');"> <img src="/sistemaweb/images/showcalendar.gif" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>&nbsp;<td/><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(' . "'form_liquidacion.hasta'" . ');"> <img src="/sistemaweb/images/showcalendar.gif" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">Tipo: <select name="tipo" id="tipo" class="form_combo">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="T">Todos</option>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="C">Combustible</option>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="M">Market</option>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="GLP">GLP</option>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</select>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>&nbsp;</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte", ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();

	}

	function reporte($results1, $results2, $results3, $results4, $results5, $desde, $hasta, $estacion) {

		$result = '<table align="center" border="0">';
		$result .= '<tr><td colspan="15"></td></tr>';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera" colspan="2">FECHA</th>';
		$result .= '<th class="grid_cabecera">EFEC SOLES</th>';
		$result .= '<th class="grid_cabecera">EFEC DOLARES (EN SOLES)</th>';
		$result .= '<th class="grid_cabecera">CREDITOS</th>';
		$result .= '<th class="grid_cabecera">ANTICIPOS</th>';
		$result .= '<th class="grid_cabecera">TARJETAS</th>';
		$result .= '<th class="grid_cabecera">SERAFIN</th>';
		$result .= '<th class="grid_cabecera">FALTANTES</th>';
		$result .= '<th class="grid_cabecera">SOBRANTES</th>';
		$result .= '<th class="grid_cabecera">TOTAL INGRESO</th>';
		$result .= '<th class="grid_cabecera">DIFERENCIA DE PRECIO</th>';
		$result .= '<th class="grid_cabecera">DIFERENCIA DIARIA</th></tr>';
		$result .= '<tr><td colspan="15"></td></tr>';
		$numfilas = 0;

        $totalDiferenciaPrecio = 0;
		$totalDiferenciaDiaria = 0;
		foreach ($results1['propiedades'] as $a => $almacenes) {
		    foreach ($almacenes['almacenes'] as $ch_almacen => $venta) {
		        $result .= LiquidacionTemplate::imprimirLinea($venta);
		        $numfilas = $numfilas + 1;
                $totalDiferenciaPrecio += number_format(($venta[12] + $venta[13]), 2, '.', ',');
                $totalDiferenciaDiaria += number_format(($venta[1]+$venta[2]+$venta[3]+$venta[4]+$venta[5]+$venta[12]+$venta[13]-$venta[7]-$venta[8]-$venta[9]+$venta[6]), 2, '.', ',');
                //$totalDiferenciaDiaria += number_format($venta[1]+$venta[2]+$venta[3]+$venta[4]+$venta[5]+$venta[12]-$venta[7]-$venta[8]-$venta[9]+$venta[6]-$venta[6], 2, '.', ',');
		    }
		}

		$result .= '<tr><td colspan="15"></td></tr>';
		$result .= '<tr style="background-color: #CEE7AC">';
		$result .= '<td align="center" colspan="2" style="font-weight:bold; color:blue">TOTALES</td>';
		
		for ($i = 1; $i < 10; $i++) {
		    $result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format($almacenes['almacen'][$numfilas - 1]['total' . $i], 2, '.', ',')) . '</td>';
		}

        $result .= "<td align= 'right' style='font-weight:bold; color:blue'>". round($totalDiferenciaPrecio, 2)."</td>";
		$result .= "<td align= 'right' style='font-weight:bold; color:blue'>". round($totalDiferenciaDiaria, 2)."</td>";
		$result .= '</tr>';
		$result .= '<tr><td colspan="15"></td></tr>';
		$result .= '</table>';

		return $result;

	}

    function reporteGLP($results1, $desde, $hasta, $estacion) {

	$result = '<table align="center" border="0">';
        $result .= '<tr><td colspan="15"></td></tr>';
        $result .= '<tr>';
        $result .= '<th class="grid_cabecera" colspan="2">FECHA</th>';
        $result .= '<th class="grid_cabecera">EFECTIVO SOLES</th>';
        $result .= '<th class="grid_cabecera">EFECTIVO DOLARES</th>';
        $result .= '<th class="grid_cabecera">CREDITOS</th>';
        $result .= '<th class="grid_cabecera">ANTICIPOS</th>';
        $result .= '<th class="grid_cabecera">TARJETAS</th>';
        $result .= '<th class="grid_cabecera">SERAFIN</th>';
        $result .= '<th class="grid_cabecera">FALTANTES</th>';
        $result .= '<th class="grid_cabecera">SOBRANTES</th>';
        $result .= '<th class="grid_cabecera">TOTAL INGRESO GLP</th>';
        $result .= '<th class="grid_cabecera">DIFERENCIA DE PRECIO</th>';
        $result .= '<th class="grid_cabecera">DIFERENCIA DIARIA</th></tr>';
        $result .= '<tr><td colspan="15"></td></tr>';
        $numfilas = 0;

        //Para calcular la diferencia diaria tengo
        //que sumar la diferencia de precio + la diferencia de precio
        $totalDiferenciaDiaria = 0;
        foreach ($results1['propiedades'] as $a => $almacenes) {
            foreach ($almacenes['almacenes'] as $ch_almacen => $venta) {
                $result .= LiquidacionTemplate::imprimirLineaGLP($venta);
                $numfilas = $numfilas + 1;
                $totalDiferenciaDiaria += number_format($venta[1]+$venta[2]+$venta[3]+$venta[4]+$venta[5]+$venta[12]-$venta[7]-$venta[8]-$venta[9]+$venta[6], 2, '.', ',');
                //$totalDiferenciaDiaria += number_format(($array[1]+$array[2]+$array[3]+$array[4]+$array[5]+$array[12]-$array[7]-$array[8]-$array[9]+$array[6]-$array[6]), 2, '.', ',');
            }
        }
        
        $result .= '<tr><td colspan="15"></td></tr>';
        $result .= '<tr style="background-color: #CEE7AC">';
        $result .= '<td align="center" colspan="2" style="font-weight:bold; color:blue">TOTALES</td>';
        
        for ($i = 1; $i < 11; $i++) {
            $result .= '<td align="right" style="font-weight:bold; color:blue">' . htmlentities(number_format($almacenes['almacen'][$numfilas - 1]['total' . $i], 2, '.', ',')) . '</td>';
        }
        $result .= '<td align="right" style="font-weight:bold; color:blue">'. number_format($totalDiferenciaDiaria, 2, '.', ',') . '</td>';
        $result .= '</tr>';
        $result .= '<tr><td colspan="15"></td></tr>';
        $result .= '</table>';

        return $result;
    }

    function imprimirLinea($array) {
        $result = '<tr>';
        $result .= '<td align="center" colspan="2" style="font-weight:bold">' . htmlentities($array[0]) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[1], 2, '.', ',')) . '</td>';//EFECTIVO SOLES
        $result .= '<td align="right">' . htmlentities(number_format($array[2], 2, '.', ',')) . '</td>';//EFECTIVO DOLARES
        $result .= '<td align="right">' . htmlentities(number_format($array[3], 2, '.', ',')) . '</td>';//CREDITOS
        $result .= '<td align="right">' . htmlentities(number_format($array[4], 2, '.', ',')) . '</td>';//ANTICIPOS
        $result .= '<td align="right">' . htmlentities(number_format($array[5], 2, '.', ',')) . '</td>';//TARJETAS
        $result .= '<td align="right">' . htmlentities(number_format($array[6], 2, '.', ',')) . '</td>';//SERAFIN
        $result .= '<td align="right">' . htmlentities(number_format($array[7], 2, '.', ',')) . '</td>';//FALTANTES
        $result .= '<td align="right">' . htmlentities(number_format($array[8], 2, '.', ',')) . '</td>';//SOBRANTES
        $result .= '<td align="right">' . htmlentities(number_format($array[9], 2, '.', ',')) . '</td>';//TOTAL INGRESO
        $result .= '<td align="right">' . htmlentities(number_format($array[12] + $array[13], 2, '.', ',')) . '</td>';//DIFERENCIA DE PRECIO $array[13] = Descuento de Notas de despacho
        $result .= '<td align="right">' . htmlentities(number_format((-$array[7]-$array[8]), 2, '.', ',')) . '</td>';
        //$result .= '<td align="right">' . htmlentities(number_format(($array[1]+$array[2]+$array[3]+$array[4]+$array[5]+$array[12]+$array[13]-$array[7]-$array[8]-$array[9]+$array[6]), 2, '.', ',')) . '</td>';cai
        //$result .= '<td align="right">' . htmlentities(number_format(($array[1]+$array[2]+$array[3]+$array[4]+$array[5]+$array[12]-$array[7]-$array[8]-$array[9]+$array[6]), 2, '.', ',')) . '</td>';
        //$result .= '<td align="right">' . htmlentities(number_format(($array[1]+$array[2]+$array[3]+$array[4]+$array[5]+$array[12]-$array[7]-$array[8]-$array[9]+$array[6]-$array[6]), 2, '.', ',')) . '</td>';
        $result .= '</tr>';
        return $result;
    }

    function imprimirLineaGLP($array) {
        $result = '<tr>';
        $result .= '<td align="center" colspan="2" style="font-weight:bold">' . htmlentities($array[0]) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[1], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[2], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[3], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[4], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[5], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[6], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[7], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[8], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[9], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format($array[12], 2, '.', ',')) . '</td>';
        $result .= '<td align="right">' . htmlentities(number_format((-$array[7]-$array[8]), 2, '.', ',')) . '</td>';
        //$result .= '<td align="right">' . htmlentities(number_format(($array[1]+$array[2]+$array[3]+$array[4]+$array[5]+$array[12]-$array[7]-$array[8]-$array[9]+$array[6]), 2, '.', ',')) . '</td>'; cai
        //$result .= '<td align="right">' . htmlentities(number_format(($array[1]+$array[2]+$array[3]+$array[4]+$array[5]+$array[12]-$array[7]-$array[8]-$array[9]+$array[6]-$array[6]), 2, '.', ',')) . '</td>';
        $result .= '</tr>';
        return $result;
    }

}

