<?php

class ExtornoTemplate extends Template {
	function mostrarError($e) {
		switch ($e) {
			case 0:
				$msg = "Se ha realizado el extorno correctamente.";
				break;
			case 1:
				$msg = "No hay ticket anterior de el lado seleccionado. S&oacute;lo se puede hacer extorno del turno actual.";
				break;
			case 2:
				$msg = "El &uacute;ltimo ticket del lado seleccionado no puede ser extornado. S&oacute;lo se pueden extornar ticket con una sola linea.";
				break;
			case 3:
				$msg = "Se ha realizado un nuevo despacho para el lado seleccionado. Vuelva a iniciar el proceso.";
				break;
			case 4:
				$msg = "No se pudo identificar el despacho correspondiente al ticket.";
				break;
			case 5:
				$msg = "Tiempo de espera agotado. El punto de venta debe estar en ejecuci&oacute;n para realizar el extorno.";
				break;
			case 6:
				$msg = "El n&uacute;mero RUC no es v&aacute;lido. Por favor, verifiquelo.";
				break;
			case 7:
				$msg = "El n&uacute;mero de tarjeta magn&eacute;tica no es v&aacute;lido. Por favor, verifiquelo.";
				break;
			default:
				$msg = "Error desconocido ({$e})";
		}
		$result  = "<p style=\"text-align:center; font-size:14px; font-weight: bold; color:black;\">{$msg}</p>";
		return $result;
	}

	function initialForm($lados) {
		$result  = "<p style=\"text-align:center;\"><form action=\"control.php\" target=\"control\" method=\"post\">";
		$result .= "<input type=\"hidden\" name=\"rqst\" value=\"MOVIMIENTOS.EXTORNO\" />";
		$result .= "<table style=\"border-style:solid; border-width:1px; border-color:black; width:300px; margin-left:auto; margin-right:auto;\">";

		$result .= "<tr>";
		$result .= "<td style=\"font-weight:bold; font-size:14px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">Extorno de Venta de Combustible</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">Seleccione el lado a extornar</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><select name=\"lado\">";
		foreach ($lados as $lado)
			$result .= "<option value=\"{$lado}\">{$lado}</option>";
		$result .= "</select></td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"text-align:center; border-style:none;\"><input type=\"submit\" name=\"action\" value=\"Programar\"/></td>";
		$result .= "</tr>";

		$result .= "</table>";
		$result .= "</form></p>";

		return $result;
	}

	function mostrarTicket($ticket,$tds) {
		$result  = "<tr>";
		$result .= "<td colspan=\"2\" style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">&Uacute;ltimo ticket:</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"width:50%; font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Caja</td>";
		$result .= "<td style=\"width:50%; font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">{$ticket['caja']}</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">N&uacute;mero</td>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">{$ticket['trans']}</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Tipo Doc.</td>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">" . $tds[$ticket['td']] . "</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Fecha y Hora</td>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">{$ticket['fecha']}</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Lado</td>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">{$ticket['pump']}</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Producto</td>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">{$ticket['descripcion']}</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Importe</td>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">{$ticket['importe']}</td>";
		$result .= "</tr>";

		return $result;
	}

	function formUltimoTicket($ticket,$tds) {
		$result  = "<p style=\"text-align:center;\"><form action=\"control.php\" method=\"post\" target=\"control\">";
		$result .= "<input type=\"hidden\" name=\"rqst\" value=\"MOVIMIENTOS.EXTORNO\" />";
		$result .= "<input type=\"hidden\" name=\"o_trans\" value=\"{$ticket['trans']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_caja\" value=\"{$ticket['caja']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_td\" value=\"{$ticket['td']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_pump\" value=\"{$ticket['pump']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_importe\" value=\"{$ticket['importe']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_codigo\" value=\"{$ticket['codigo']}\" />";
		$result .= "<table style=\"border-style:solid; border-width:1px; border-color:black; width:300px; margin-left:auto; margin-right:auto;\">";

		$result .= "<tr>";
		$result .= "<td colspan=\"2\" style=\"font-weight:bold; font-size:14px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">Extorno de Venta de Combustible</td>";
		$result .= "</tr>";

		$result .= ExtornoTemplate::mostrarTicket($ticket,$tds);

		$result .= "<tr>";
		$result .= "<td colspan=\"2\" style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">Nuevo Ticket:</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td colspan=\"2\" style=\"text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><select name=\"td\">";
		foreach ($tds as $td => $tdd)
			$result .= "<option value=\"{$td}\">{$tdd}</option>";
		$result .= "</select></td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td colspan=\"2\" style=\"text-align:center; border-style:none;\"><input type=\"submit\" name=\"action\" value=\"Siguiente\"/></td>";
		$result .= "</tr>";

		$result .= "</table>";
		$result .= "</form></p>";

		return $result;
	}

	function formNuevoTicket($ntd,$ticket,$tds,$fps,$tts) {
		$result  = "<p style=\"text-align:center;\"><form action=\"control.php\" method=\"post\" target=\"control\">";
		$result .= "<input type=\"hidden\" name=\"rqst\" value=\"MOVIMIENTOS.EXTORNO\" />";
		$result .= "<input type=\"hidden\" name=\"o_trans\" value=\"{$ticket['trans']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_caja\" value=\"{$ticket['caja']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_td\" value=\"{$ticket['td']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_pump\" value=\"{$ticket['pump']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_importe\" value=\"{$ticket['importe']}\" />";
		$result .= "<input type=\"hidden\" name=\"o_codigo\" value=\"{$ticket['codigo']}\" />";
		$result .= "<input type=\"hidden\" name=\"n_td\" value=\"{$ntd}\" />";
		$result .= "<table style=\"border-style:solid; border-width:1px; border-color:black; width:300px; margin-left:auto; margin-right:auto;\">";

		$result .= "<tr>";
		$result .= "<td colspan=\"2\" style=\"font-weight:bold; font-size:14px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">Extorno de Venta de Combustible</td>";
		$result .= "</tr>";

		$result .= ExtornoTemplate::mostrarTicket($ticket,$tds);

		$result .= "<tr>";
		$result .= "<td colspan=\"2\" style=\"font-weight:bold; font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">Nuevo Ticket:</td>";
		$result .= "</tr>";

		$result .= "<tr>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Tipo Doc.</td>";
		$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\">{$tds[$ntd]}</td>";
		$result .= "</tr>";

		if ($ntd == "F") {
			$result .= "<tr>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">RUC</td>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><input type=\"text\" name=\"ruc\"/></td>";
			$result .= "</tr>";
		}

		if ($ntd == "B" || $ntd == "F") {
			$result .= "<tr>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Forma de Pago</td>";
			$result .= "<td style=\"text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><select name=\"fpago\">";
			foreach ($fps as $fp => $fpd)
				$result .= "<option value=\"{$fp}\">{$fpd}</option>";
			$result .= "</select></td>";
			$result .= "</tr>";

			$result .= "<tr>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Tipo de Tarjeta</td>";
			$result .= "<td style=\"text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><select name=\"ttarj\">";
			foreach ($tts as $tt => $ttd)
				$result .= "<option value=\"{$tt}\">{$ttd}</option>";
			$result .= "</select></td>";
			$result .= "</tr>";

			$result .= "<tr>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Numero de Voucher TC</td>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><input type=\"text\" name=\"voucher\"/></td>";
			$result .= "</tr>";
		}

		if ($ntd == "N") {
			$result .= "<tr>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Tarjeta Magn&eacute;tica</td>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><input type=\"text\" name=\"fptshe\"/></td>";
			$result .= "</tr>";

			$result .= "<tr>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Kilometraje</td>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><input type=\"text\" name=\"odometro\"/></td>";
			$result .= "</tr>";
		}

		if ($ntd == "A") {
			$result .= "<tr>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Velocidad</td>";
			$result .= "<td style=\"text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><select name=\"veloc\">";
			$result .= "<option value=\"L\">Lento</option>";
			$result .= "<option value=\"R\">R&aacute;pido</option>";
			$result .= "</select></td>";
			$result .= "</tr>";

			$result .= "<tr>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 1px 1px 0px;\">Lineas</td>";
			$result .= "<td style=\"font-size:11px; text-align:center; border-style:solid; border-width:0px 0px 1px 0px;\"><input type=\"text\" name=\"lineas\"/></td>";
			$result .= "</tr>";
		}

		$result .= "<tr>";
		$result .= "<td colspan=\"2\" style=\"text-align:center; border-style:none;\"><input type=\"submit\" name=\"action\" value=\"Extornar\"/></td>";
		$result .= "</tr>";

		$result .= "</table>";
		$result .= "</form></p>";

		return $result;
	}
}
