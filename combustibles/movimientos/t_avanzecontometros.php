<?php

function showNumber($num) {
	return number_format(round($num,2),2,".","");
}

class AvanzeContometrosTemplate extends Template {
	function titulo() {
		return '<div align="center"><h2><b>Reporte de Avance de Contometros</b></h2></div>';
	}

	function mostrarReporte($cuadre) {
		$result = "";
print_r($cuadre);
		$result .= "<center>\n";
		$result .= "<table class=\"tablaContometros\">\n";

		if (count($cuadre) != 0) {
			// Detalle de Contometros para Combustibles
			$result .= "<tr><td class=\"celdaContenido\">\n";
			$result .= AvanzeContometrosTemplate::reporteContometros($cuadre);
			$result .= "</td></tr>\n";
		}

		$result .= "</table></center>\n";

		return $result;
	}

	function reporteContometros($cuadre) {
		$result  = "<table class=\"tablaContometros\">\n";
		$result .= "<tr><td class=\"celdaTrabajador\" colspan=\"14\" style=\"font-weight: bold;\">Detalle de Venta de Combustibles</td></tr>\n";
		$result .= "<tr>\n";
		$result .= "<td class=\"celdaCabecera width6\">Lado</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Manguera</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Producto</td>\n";
		$result .= "<td class=\"celdaCabecera width6\">Cantidad Tickets</td>\n";
		$result .= "<td class=\"celdaCabecera width6\">Importe Tickets</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Precio</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Contometro Cant. Inicial</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Contometro Cant. Final</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Contometro Imp. Inicial</td>\n";
		$result .= "<td class=\"celdaCabecera width8\">Contometro Imp. Final</td>\n";
		$result .= "<td class=\"celdaCabecera width7\">Cantidad Contometro</td>\n";
		$result .= "<td class=\"celdaCabecera width7\">Importe Contometro</td>\n";
		$result .= "<td class=\"celdaCabecera width6\">Diferencia X Cantidad</td>\n";
		$result .= "<td class=\"celdaCabecera width6\">Diferencia X Importe</td>\n";
		$result .= "</tr>\n";

		$ventaqticket = 0;
		$ventaqconto = 0;

		foreach ($cuadre as $lado) {
			if (!is_numeric($lado['lado']))
				continue;
			foreach ($lado['mangueras'] as $nm => $manguera) {
				$result .= "<tr>\n";
				$result .= "<td class=\"celdaEtiqueta\">{$lado['lado']}</td>\n";
				$result .= "<td class=\"celdaEtiqueta\">{$nm}</td>\n";
				$result .= "<td class=\"celdaEtiqueta\">{$manguera['producto']}</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['ticket_venta_vol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['ticket_venta_sol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['precio']) . "</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['conto_inicial_vol']) . "</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['conto_final_vol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['conto_inicial_sol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['conto_final_sol']) . "</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['conto_venta_vol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['conto_venta_sol']) . "</td>\n";
				$result .= "<td class=\"celdaContometro\">" . showNumber($manguera['diferencia_vol']) . "</td>\n";
				$result .= "<td class=\"celdaImporte\">" . showNumber($manguera['diferencia_sol']) . "</td>\n";
				$result .= "</tr>\n";
			}
			$result .= "<tr>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td class=\"celdaEtiquetaAcum\">TOTAL</td>\n";
			$result .= "<td class=\"celdaEtiquetaAcum\">LADO</td>\n";
			$result .= "<td class=\"celdaContometroAcum\">" . showNumber($lado['ticket_venta_vol']) . "</td>\n";
			$result .= "<td class=\"celdaImporteAcum\">" . showNumber($lado['ticket_venta_sol']) . "</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td>&nbsp;</td>\n";
			$result .= "<td class=\"celdaContometroAcum\">" . showNumber($lado['conto_venta_vol']) . "</td>\n";
			$result .= "<td class=\"celdaImporteAcum\">" . showNumber($lado['conto_venta_sol']) . "</td>\n";
			$result .= "<td class=\"celdaContometroAcum\">" . showNumber($lado['diferencia_vol']) . "</td>\n";
			$result .= "<td class=\"celdaImporteAcum\">" . showNumber($lado['diferencia_sol']) . "</td>\n";
			$result .= "</tr>\n";

			$ventaqticket += $lado['ticket_venta_vol'];
			$ventaqconto += $lado['conto_venta_vol'];
		}
		$result .= "<tr>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td class=\"celdaEtiquetaAcum\">TOTAL</td>\n";
		$result .= "<td class=\"celdaEtiquetaAcum\">COMB.</td>\n";
		$result .= "<td class=\"celdaContometroAcum\">" . showNumber($ventaqticket) . "</td>\n";
		$result .= "<td class=\"celdaImporteAcum\">" . showNumber($cuadre['venta_ticket']) . "</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td class=\"celdaContometroAcum\">" . showNumber($ventaqconto) . "</td>\n";
		$result .= "<td class=\"celdaImporteAcum\">" . showNumber($cuadre['venta_conto']) . "</td>\n";
		$result .= "<td>&nbsp;</td>\n";
		$result .= "<td class=\"celdaImporteAcum\">" . showNumber($cuadre['diferencia']) . "</td>\n";
		$result .= "</tr>\n";

		$result .= "</table>\n";

		return $result;
	}
}
