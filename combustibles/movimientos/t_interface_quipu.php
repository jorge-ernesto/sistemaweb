<?php
class InterfaceQuipuTemplate extends Template {
	function titulo() {
		$titulo = '<div align="center"><h2>Interface Opensoft --> Quipu</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}

	function ResultadoEjecucion($msg) {
		return '<blink>'.$msg.'</blink>';
	}

	function formInterface3DO($CbSucursales, $hoy) {

		$form = new form2('INTERFACE QUIPU', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');

		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZQUIPU'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZQUIPU'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="1" cellpadding="1">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Seleccionar Almacen: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]','', '', $CbSucursales, espacios(50), "", ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<tr>
			<td align="right">Fecha Inicial: </td>
			<td align="left"><input type="text" name="datos[fechaini]" id="datos[fechaini]" maxlength="10" size="10" class="fecha_formato" value="'.(empty($_REQUEST['datos']['fechaini']) ? $hoy : $_REQUEST['datos']['fechaini']).'" /></td>
		</tr>
		'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<tr>
			<td align="right">Fecha Final: </td>
			<td align="left"><input type="text" name="datos[fechafin]" id="datos[fechafin]" maxlength="10" size="10" class="fecha_formato" value="'.(empty($_REQUEST['datos']['fechafin']) ? $hoy : $_REQUEST['datos']['fechafin']).'" /></td>
		</tr>
		'));

	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Ticket Boleta Agrupadas: </td>'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('agrupado', '', 'S', ''));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></td>'));

	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Procesar"><img src="/sistemaweb/icons/database.png" align="right" alt="Importar"/>Importar &nbsp;</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar &nbsp;</button>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();

	}

	function imprimeResultado($res, $fini, $ffin) {

		if ($res === TRUE){
			$result = "<p style=\"text-align: center;\">Se ha copiado la informaci&oacute;n a Quipu.<br>
				<br>Fecha Desde: $fini Hasta: $ffin<br>
			</p>";
		}else 
			$result = "<p style=\"text-align: center;\">No se pudo copiar la informaci&oacute;n a Quipu Fecha: $fini - $ffin <script language=\"javascript\">alert('{$res}');</script></p>";

		return $result;

	}

	function ResultadosBusqueda($data){

		$result  = '';
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">TIPO</th>';
		$result .= '<th class="grid_cabecera">SERIE</th>';
		$result .= '<th class="grid_cabecera">NUMERO</th>';
		$result .= '<th class="grid_cabecera">RUC</th>';
		$result .= '<th class="grid_cabecera">RAZON SOCIAL</th>';
		$result .= '<th class="grid_cabecera">FECHA EMISION</th>';
		$result .= '<th class="grid_cabecera">BASE IMPONIBLE</th>';
		$result .= '<th class="grid_cabecera">IGV</th>';
		$result .= '<th class="grid_cabecera">EXONERADA</th>';
		$result .= '<th class="grid_cabecera">PERCEPCION</th>';
		$result .= '<th class="grid_cabecera">TOTAL</th>';
		$result .= '</tr>';

		$sum_bi 				= 0.00;
		$sum_igv 				= 0.00;
		$sum_exonerada			= 0.00;
		$sum_percepcion			= 0.00;
		$sum_total 				= 0.00;
		$sum_bi_general 		= 0.00;
		$sum_igv_general		= 0.00;
		$sum_exonerada_general	= 0.00;
		$sum_percepcion_general	= 0.00;
		$sum_total_general		= 0.00;
		$nutv 					= null;

		for ($i = 0; $i < count($data); $i++) {

			$color		= ($i%2 == 0 ? "grid_detalle_par" : "grid_detalle_impar");
			$a			= $data[$i];

			if($a['nutv'] != $nutv){
				if($i != 0){
					$result .= '<tr>';
					$result .= '<td colspan="6" class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">TOTAL: </td>';
					$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_bi, 2, '.', ',')) . '</td>';
					$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_igv, 2, '.', ',')) . '</td>';
					$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_exonerada, 2, '.', ',')) . '</td>';
					$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_percepcion, 2, '.', ',')) . '</td>';
					$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_total, 2, '.', ',')) . '</td>';
					$result .= '</tr>';

					$sum_bi 		= 0;
					$sum_igv 		= 0;
					$sum_exonerada 	= 0;
					$sum_percepcion = 0;
					$sum_total 		= 0;
				}
				$result .= '<tr>';
				$result .= '<td colspan="11" class="grid_detalle_especial" align="center" style="font-size:11px; font-weight:bold">' . htmlentities($a['notipoventa']) . '</td>';
				$result .= '</tr>';
				$nutv = $a['nutv'];
			}
	

		
			$result .= '<tr>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['nutd']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['nuserie']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['nudocumento']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['nuruc']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['norazsocial']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['femision']) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['nubi'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['nuigv'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['nuexonerada'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['nupercepcion'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['nutotal'], 2, '.', ',')) . '</td>';
			$result .= '</tr>';

			$sum_bi 		+= $a['nubi'];
			$sum_igv 		+= $a['nuigv'];
			$sum_exonerada 	+= $a['nuexonerada'];
			$sum_percepcion += $a['nupercepcion'];
			$sum_total 		+= $a['nutotal'];

			$sum_bi_general 		+= $a['nubi'];
			$sum_igv_general 		+= $a['nuigv'];
			$sum_exonerada_general 	+= $a['nuexonerada'];
			$sum_percepcion_general += $a['nupercepcion'];
			$sum_total_general 		+= $a['nutotal'];
		}

		$result .= '<tr>';
		$result .= '<td colspan="6" class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">TOTAL TICKET: </td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_bi, 2, '.', ',')) . '</td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_igv, 2, '.', ',')) . '</td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_exonerada, 2, '.', ',')) . '</td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_percepcion, 2, '.', ',')) . '</td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_total, 2, '.', ',')) . '</td>';
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<td colspan="6" class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">TOTAL GENERAL: </td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_bi_general, 2, '.', ',')) . '</td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_igv_general, 2, '.', ',')) . '</td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_exonerada_general, 2, '.', ',')) . '</td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_percepcion_general, 2, '.', ',')) . '</td>';
		$result .= '<td class="grid_detalle_total" align="right" style="font-size:11px; font-weight:bold">' . htmlentities(number_format($sum_total_general, 2, '.', ',')) . '</td>';
		$result .= '</tr>';

		$result .= '</table>';

		return $result;

	}

}

