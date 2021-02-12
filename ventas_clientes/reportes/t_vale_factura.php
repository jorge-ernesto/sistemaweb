<?php

class ValesFacturaTemplate extends Template {

	function Titulo() {
		return '<div align="center"><h3><b style="color:#336699">CUADRO RESUMEN DE VERIFICACION DE VENTAS</b></h2></div>';
	}

	function formSearch() {

//$result = "<div id='cargando'><h3>Cargando página ...</h3> Sea paciente, los datos demoran en ser importados.</div>";
        	//return $result;
		$estaciones	= ValesFacturaModel::obtenerEstaciones();
		$ano		= date("Y");
		$mes		= date("m");
		$acciones	= array("Normal" => "Normal", "Agrupado" => "Agrupado");

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.KARDEXCLIENTE"));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("action", "Buscar"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "TODAS", $estaciones, ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>A&ntilde;o</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("ano", "", $ano, '', 4, 5));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mes: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes", "", $mes, '', 2, 4));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.gif" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}

	function listado($monto_vales_generados) {

		$result = "<div style='text-align:center;'>";

			$result .= '<h3>I. KARDEX CLIENTE<h3><br/>';
			$result .= '<table border="0" width="1120px">';

			$result .= '<tr>';

				$result .= '<td  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>CODIGO CLIENTE</strong></td>';
				$result .= '<td  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1 style="text-transform: uppercase;text-align: center;"><b>RAZON SOCIAL </strong></td>';
				$result .= '<td  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;text-align: center;"><font size=1 style="text-transform: uppercase;text-align: center;"> <b>MONTO INICIAL </strong></td>';
				$result .= '<td  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>CONSUMO</strong></td>';
				$result .= '<td  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>PAGOS</strong></td>';
				$result .= '<td  bgcolor="#4682B4" style="color:#FFFFFF;font-size:11px;"><font size=1  style="text-transform: uppercase;"><b>SALDO</strong></td>';

			$result .= '</tr>';

			//var_dump($monto_vales_generados);

			$diferencia_cliente_suma = 0.0;

			foreach ($monto_vales_generados as $key_alm => $value_fecha_row) {

				$cli_razsocial		= $value_fecha_row['cli_razsocial'];
		    		$saldo_inicial		= $value_fecha_row['saldo_inicial'];
		    		$importe_total_vales	= $value_fecha_row['importe_total_vales'];
		    		$total_facturado	= $value_fecha_row['total_facturado'];
		    		$diferencia_cliente	= ($saldo_inicial - $importe_total_vales) + $total_facturado;

		    		$diferencia_cliente_suma += $diferencia_cliente;

		    		$result .= '<tr>';

			    		$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;width: 100px;"><font size=1><b>' . $key_alm . '</strong></td>';
			    		$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: left;width: 300px;"><font size=1><b>' . $cli_razsocial . ' </strong></td>';
			    		$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($saldo_inicial, 4) . ' </strong></td>';
			    		$result .= '<td  bgcolor="#FFFFFF" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($importe_total_vales, 2) . ' </strong></td>';
			    		$result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($total_facturado, 2) . ' </strong></td>';
			    		$result .= '<td  bgcolor="#C9C9C6" style="color:#1C1D1C;font-size:11px;text-align: right;width: 97px;"><font size=1><b>' . number_format($diferencia_cliente, 2) . ' </strong></td>';

		    		$result .= '</tr>';

			}

			$result .= '<tr>';
	
				$result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;"><font size=1><b></strong></td>';
				$result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b></strong></td>';
				$result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b> </strong></td>';
				$result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b> </strong></td>';
				$result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b> </strong></td>';
				$result .= '<td  bgcolor="#DFBEBE" style="color:#1C1D1C;font-size:11px;text-align: right;"><font size=1><b>' . number_format($diferencia_cliente_suma, 4) . ' </strong></td>';

			$result .= '</tr>';

   		$result .= '</div>';

        	return $result;

	}

    	function getUltimoDiaMes($elAnio, $elMes) {

        	$fecha_actual		= date("Y-m");
        	$fecha_ingresada	= trim($elAnio . "-" . $elMes);

        	if ($fecha_actual == $fecha_ingresada) {
			return date("d");
		} else {
			return date("d", (mktime(0, 0, 0, $elMes + 1, 1, $elAnio) - 1));
		}

	}

}

