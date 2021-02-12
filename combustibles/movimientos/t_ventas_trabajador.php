<?php
class VentasTrabajadorTemplate extends Template {

	function titulo() {
		return '<center><h2><b>Cuadre de Ventas de Trabajador</b></h2></center>';
	}
	
	function mostrarBusqueda($dia,$turno,$trabajador,$tipo,$bande) {
		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN,new form_element_hidden("rqst", "MOVIMIENTOS.VENTASTRABAJADOR"));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("<table style='width:400px;' cellspacing=0 border='1'>"));
		
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("<td style='width:50%;text-align:right;'>Fecha:</td>"));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("<td style='text-align:left;'>"));

		if($bande == 0) 
			$form->addElement(FORM_GROUP_MAIN,  new form_element_text('', "dia", $dia, '<a href="javascript:show_calendar(\'Agregar.dia\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>', '', 10, 10, true));
		else
			$form->addElement(FORM_GROUP_MAIN,  new form_element_text('', "dia", $dia, '', '', 10, 10, true));

		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('</td>'));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('</tr>'));		
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('<tr>'));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("<td style='width:50%;text-align:right;'>Turno:</td>"));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("<td style='text-align:left;'>"));

		if($bande == 0)
			$form->addElement(FORM_GROUP_MAIN,  new form_element_text('', "turno", $turno, '', '', 10, 10, false));
		else
			$form->addElement(FORM_GROUP_MAIN,  new form_element_text('', "turno", $turno, '', '', 10, 10, true));

		if($tipo == 3)
			$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('&nbsp;&nbsp;Turno Consolidado'));
		else
			if($tipo == 2) {
				$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("&nbsp;&nbsp;"));
				$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('<input type="submit" size="20" class="form_button" value="Consolidar" name="action" onclick="return confirm(\'¿Esta seguro de consolidar el turno?\')">'));
				$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('<input type="submit" size="20" class="form_button" value="Consolidar sin impresion" name="action" onclick="return confirm(\'¿Esta seguro de consolidar el turno?\')">'));
			}
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('</td>'));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('</tr>'));		
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('<tr>'));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('<td style="text-align:right;">'));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("Trabajador:"));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("<td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_text('', "trabajador", $trabajador, '', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('</td>'));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('</tr>'));		
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('<tr>'));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext('<td style="text-align:center;" colspan="2">'));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_submit("action","Reporte",'&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp','', 20));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_submit("action", "Nuevo Dia", '&nbsp&nbsp&nbsp&nbsp&nbsp', '', 20));
		if($tipo == 2) 	
			$form->addElement(FORM_GROUP_MAIN,  new form_element_submit("action", "Imprimir Wincha", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("</tr>"));
		$form->addElement(FORM_GROUP_MAIN,  new form_element_anytext("</table>"));
		return $form->getForm();
	}
	
	function mostrarReporte ($vector_datos = Array(),$eess,$impresion) {
		$apertura_cierre		= Array();
		$vector_productos		= Array();
		$contometros_cantidad		= Array();
		$contometros_importe		= Array();
		$numero_mangueras_x_lado	= 0;
		$cabecera_combustible		= 0;
		$cabecera_market		= 0;
		$cant_ticket_x_lado		= 0;
		$imp_ticket_x_lado		= 0;
		$cant_ticket_x_tipo		= 0;
		$imp_ticket_x_tipo		= 0;
		$ticket_total			= 0;
		$cantidad_x_lado		= 0;
		$importe_x_lado			= 0;
		$dif_cantidad_x_lado		= 0;
		$dif_importe_x_lado		= 0;
		$cantidad_x_tipo		= 0;
		$importe_x_tipo			= 0;
		$dif_cantidad_x_tipo		= 0;
		$dif_importe_x_tipo		= 0;
		$total_combustible		= 0;
		$total_market			= 0;
		$total_contometros		= 0;
		$total_descuentos		= 0;
		$total_devoluciones		= 0;
		$texto_impresion		= "";
		$CRLF					= "\r\n";
		$tipo_consolidacion		= "";
		
		if($impresion == 1) {
			if ($vector_datos[0]['tipo']=="C")
				$texto_impresion .= alinea($eess[0],2,40) . $CRLF;
			else
				$texto_impresion .= alinea($eess[3],2,40) . $CRLF;
			$texto_impresion .= alinea($eess[1],2,40) . $CRLF;
			$texto_impresion .= alinea($eess[2],2,40) . $CRLF;
			$texto_impresion .= $CRLF;
			$texto_impresion .= alinea("REPORTE DE LIQUIDACION DE TRABAJADOR",2,40) . $CRLF;
			$texto_impresion .= $CRLF;
			$texto_impresion .= " NOMBRE: " . $vector_datos[0]['nombre'] . " - ".$vector_datos[0]['codigo'].$CRLF;
			$texto_impresion .= " DIA: " . $vector_datos[0]['dia'] . " TURNO: " . $vector_datos[0]['turno'] . $CRLF;
			$texto_impresion .= $CRLF;
		}
		
		$resultado  = "<center>";
		$resultado .= "<table border='1' cellspacing=0 width=1000>";
		
		$apertura_cierre = VentasTrabajadorModel::obtenerAperturaCierre($vector_datos[0]['dia'],$vector_datos[0]['turno'],$vector_datos[0]['codigo']);
		
		$resultado .= "<tr>";
		$resultado .= "<th colspan='14' bgcolor='#81BEF7' style='font-size:14px' height=25> Abierto: ".$apertura_cierre[0]['apertura']." - CERRADO: ".$apertura_cierre[0]['cierre']."</th>";
		$resultado .= "</tr>";
		$resultado .= "<tr>";
		$resultado .= "<th colspan='14' bgcolor='#2E9AFE' style='font-size:14px' height=25>".$vector_datos[0]['codigo']." - ".$vector_datos[0]['nombre']."</th>";
		$resultado .= "</tr>";
		
		//DETALLE DE COMBUSTIBLE Y MARKET
		for ($i = 0; $i < count($vector_datos); $i++) {
			$a = $vector_datos[$i];
			if($a['tipo'] == "C")
			{
				$tipo_consolidacion = "C";
				//COLOCAMOS LOS PRODUCTOS QUE CORRESPONDEN AL LADO Y MANGUERA EN CURSO
				$numero_mangueras_x_lado += 1;
				if ($i == 0)
					$vector_productos = VentasTrabajadorModel::obtenerProductos($a['lado']);
				else
					if($vector_datos[$i]['lado']!=$vector_datos[$i-1]['lado'])
						$vector_productos = VentasTrabajadorModel::obtenerProductos($a['lado']);
				
				$contometros_cantidad = VentasTrabajadorModel::obtenercontometros_cantidad($a['lado'],$a['manguera'],$a['fecha_contometro'],$a['contometro']);
				$tickets_importe = VentasTrabajadorModel::obtenerTickets_cantidad($a['postrans'],$a['dia'],$a['turno'],$a['lado'],$vector_productos[0][($numero_mangueras_x_lado-1)*3],$a['tipo']);
				
				if($cabecera_combustible == 0)
				{
					$cabecera_combustible = 1;
					$resultado .= "<tr>";
					$resultado .= "<td colspan='14'>&nbsp;</td>";
					$resultado .= "</tr>";
					$resultado .= "<tr>";
					$resultado .= "<th colspan='14' bgcolor='#D8D8D8' style='font-size:11px' height=20>VENTAS DE COMBUSTIBLE</th>";
					$resultado .= "</tr>";
					$resultado .= "<tr>";
					$resultado .= "<th colspan='6' bgcolor='#A9F5F2' style='font-size:11px' height=20>&nbsp;</th>";
					$resultado .= "<th colspan='8' bgcolor='#A9F5F2' style='font-size:11px' height=20>CONTOMETROS</th>";
					$resultado .= "</tr>";
					$resultado .= "<tr>";
					$resultado .= "<th bgcolor='#A9F5F2'>LADO</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>MANGUERA</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>PRODUCTO</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>CANT.TICKET</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMP.TICKET</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>PRECIO</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>CANT.INICIAL</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>CANT.FINAL</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMP.INICIAL</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMP.FINAL</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>CANTIDAD</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMPORTE</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>DIF.CANT.</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>DIF.IMP.</th>";
					$resultado .= "</tr>";
					if($impresion == 1)
						$texto_impresion .= "LD MG PD    INICIAL      FINAL    VENTA". $CRLF;
				}
				$resultado .= "<tr>";
				$resultado .= "<td align='center'>&nbsp;".$a['lado']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$a['manguera']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$vector_productos[0][($numero_mangueras_x_lado-1)*3+1]."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($tickets_importe[0][0],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($tickets_importe[0][1],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['precio'],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($contometros_cantidad[0][0],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['contometro_volumen'],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($contometros_cantidad[0][1],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['contometro_valor'],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['contometro_volumen'] - $contometros_cantidad[0][0],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['contometro_valor'] - $contometros_cantidad[0][1],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['contometro_volumen'] - $contometros_cantidad[0][0] - $tickets_importe[0][0],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['contometro_valor'] - $contometros_cantidad[0][1] - $tickets_importe[0][1],2), 2, '.', ','))."</td>";
				$resultado .= "</tr>";
				
				if($impresion == 1 && showNumber($contometros_cantidad[0][1]) != "0.00" && showNumber($a['contometro_valor']) != "0.00" )
					$texto_impresion .= alinea($a['lado'],0,3) . alinea($a['manguera'],0,3) . alinea($vector_productos[0][($numero_mangueras_x_lado-1)*3+2],0,3) . alinea(showNumber($contometros_cantidad[0][1]),1,10) . alinea(showNumber($a['contometro_valor']),1,11) . alinea(showNumber($a['contometro_valor'] - $contometros_cantidad[0][1]),1,9) . $CRLF;
				
				$cant_ticket_x_lado	+= $tickets_importe[0][0];
				$imp_ticket_x_lado	+= $tickets_importe[0][1];
				$cantidad_x_lado	+= $a['contometro_volumen'] - $contometros_cantidad[0][0];
				$importe_x_lado		+= $a['contometro_valor'] - $contometros_cantidad[0][1];
				$dif_cantidad_x_lado+= $a['contometro_volumen'] - $contometros_cantidad[0][0] - $tickets_importe[0][0];
				$dif_importe_x_lado	+= $a['contometro_valor'] - $contometros_cantidad[0][1] - $tickets_importe[0][1];
				
				//PINTAMOS LOS TOTALES POR LADO
				if($vector_datos[$i]['lado']!=$vector_datos[$i+1]['lado'])
				{
					$resultado .= "<tr>";
					$resultado .= "<th align='right' colspan='3' bgcolor='#CEF6F5' style='font-size:10px'>TOTAL LADO</th>";
					$resultado .= "<td align='right' bgcolor='#CEF6F5' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($cant_ticket_x_lado,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#CEF6F5' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($imp_ticket_x_lado,2), 2, '.', ','))."</td>";
					$resultado .= "<th align='right' colspan='5' bgcolor='#CEF6F5' style='font-size:10px'>&nbsp;</th>";
					$resultado .= "<td align='right' bgcolor='#CEF6F5' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($cantidad_x_lado,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#CEF6F5' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($importe_x_lado,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#CEF6F5' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($dif_cantidad_x_lado,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#CEF6F5' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($dif_importe_x_lado,2), 2, '.', ','))."</td>";
					$resultado .= "</tr>";
					$cant_ticket_x_tipo	+= $cant_ticket_x_lado;
					$imp_ticket_x_tipo	+= $imp_ticket_x_lado;
					$cantidad_x_tipo	+= $cantidad_x_lado;
					$importe_x_tipo		+= $importe_x_lado;
					$dif_cantidad_x_tipo+= $dif_cantidad_x_lado;
					$dif_importe_x_tipo	+= $dif_importe_x_lado;
					$ticket_total	+= $imp_ticket_x_tipo;
					$cant_ticket_x_lado	= 0;
					$imp_ticket_x_lado	= 0;
					$numero_mangueras_x_lado= 0;
					$cantidad_x_lado		= 0;
					$importe_x_lado			= 0;
					$dif_cantidad_x_lado	= 0;
					$dif_importe_x_lado		= 0;
				}
				
				//PINTAMOS LOS TOTALES POR TIPO
				if($vector_datos[$i]['tipo']!=$vector_datos[$i+1]['tipo'])
				{
					$total_combustible = $imp_ticket_x_tipo;
					$total_contometros = $importe_x_tipo;
					$resultado .= "<tr>";
					$resultado .= "<th align='right' colspan='3' bgcolor='#81BEF7' style='font-size:10px'>TOTAL COMBUSTIBLE</th>";
					$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($cant_ticket_x_tipo,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($imp_ticket_x_tipo,2), 2, '.', ','))."</td>";
					$resultado .= "<th align='right' colspan='5' bgcolor='#81BEF7' style='font-size:10px'>&nbsp;</th>";
					$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($cantidad_x_tipo,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($importe_x_tipo,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($dif_cantidad_x_tipo,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($dif_importe_x_tipo,2), 2, '.', ','))."</td>";
					$resultado .= "</tr>";
					
					if($impresion == 1)
					{
						$texto_impresion .= $CRLF;
						$texto_impresion .= "Venta Contometros         " . alinea(showNumber($importe_x_tipo),1,13) . $CRLF;
						$texto_impresion .= "Venta Tickets             " . alinea(showNumber($imp_ticket_x_tipo),1,13) . $CRLF;
						$texto_impresion .= "Diferencia                " . alinea(showNumber($dif_importe_x_tipo),1,13) . $CRLF;
						$texto_impresion .= $CRLF;
					}
					
					$cant_ticket_x_tipo	= 0;
					$imp_ticket_x_tipo	= 0;
					$cantidad_x_tipo	= 0;
					$importe_x_tipo		= 0;

					$dif_cantidad_x_tipo= 0;
					$dif_importe_x_tipo	= 0;
				}
			}
			else
			{
				if ($tipo_consolidacion == "")
					$tipo_consolidacion = "M";
				else
					$tipo_consolidacion = "H";
				$tickets_importe = VentasTrabajadorModel::obtenerTickets_cantidad($a['postrans'],$a['dia'],$a['turno'],$a['lado'],'',$a['tipo']);
				
				if($cabecera_market == 0)

				{
					$cabecera_market = 1;
					$resultado .= "<tr>";
					$resultado .= "<td colspan='14'>&nbsp;</td>";
					$resultado .= "</tr>";
					$resultado .= "<tr>";
					$resultado .= "<th colspan='14' bgcolor='#D8D8D8' style='font-size:11px' height=20>VENTAS DE MARKET</th>";
					$resultado .= "</tr>";
					$resultado .= "<tr>";
					$resultado .= "<th bgcolor='#A9F5F2' colspan='3'>CAJA</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>ARTICULOS</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMP.TICKET</th>";
					$resultado .= "<th bgcolor='#A9F5F2' colspan='9'>&nbsp;</th>";
					$resultado .= "</tr>";
				}
				$resultado .= "<tr>";
				$resultado .= "<td align='center' colspan='3'>&nbsp;".$a['lado']."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($tickets_importe[0][0],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($tickets_importe[0][1],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='center' colspan='9'>&nbsp;</td>";
				$resultado .= "</tr>";
				$cant_ticket_x_lado += $tickets_importe[0][0];
				$imp_ticket_x_lado += $tickets_importe[0][1];
				
				//PINTAMOS LOS TOTALES POR CAJA
				if($vector_datos[$i]['lado']!=$vector_datos[$i+1]['lado'])
				{
					$resultado .= "<tr>";
					$resultado .= "<th align='right' colspan='3' bgcolor='#CEF6F5' style='font-size:10px'>TOTAL CAJA</th>";
					$resultado .= "<td align='right' bgcolor='#CEF6F5' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($cant_ticket_x_lado,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#CEF6F5' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($imp_ticket_x_lado,2), 2, '.', ','))."</td>";
					$resultado .= "<th align='right' colspan='9' bgcolor='#CEF6F5' style='font-size:10px'>&nbsp;</th>";
					$resultado .= "</tr>";
					$cant_ticket_x_tipo	+= $cant_ticket_x_lado;
					$imp_ticket_x_tipo	+= $imp_ticket_x_lado;
					$cantidad_x_tipo	+= $cantidad_x_lado;
					$importe_x_tipo		+= $importe_x_lado;
					$dif_cantidad_x_tipo+= $dif_cantidad_x_lado;
					$dif_importe_x_tipo	+= $dif_importe_x_lado;
					$ticket_total	+= $imp_ticket_x_tipo;
					$cant_ticket_x_lado	= 0;
					$imp_ticket_x_lado	= 0;
					$numero_mangueras_x_lado= 0;
				}
				
				//PINTAMOS LOS TOTALES POR TIPO
				if($i + 1 == count($vector_datos))
				{
					$total_market = $imp_ticket_x_tipo;
					$resultado .= "<tr>";
					$resultado .= "<th align='right' colspan='3' bgcolor='#81BEF7' style='font-size:10px'>TOTAL MARKET</th>";
					$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($cant_ticket_x_tipo,2), 2, '.', ','))."</td>";
					$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($imp_ticket_x_tipo,2), 2, '.', ','))."</td>";
					$resultado .= "<th align='right' colspan='9' bgcolor='#81BEF7' style='font-size:10px'>&nbsp;</th>";
					$resultado .= "</tr>";
					
					if($impresion == 1)
						$texto_impresion .= $CRLF;
					
					$cant_ticket_x_tipo	= 0;
					$imp_ticket_x_tipo	= 0;
					$cantidad_x_tipo	= 0;
					$importe_x_tipo		= 0;
					$dif_cantidad_x_tipo= 0;
					$dif_importe_x_tipo	= 0;
				}
			}
		}
		
		$resultado .= "<tr>";
		$resultado .= "<td colspan='14'>&nbsp;</td>";
		$resultado .= "</tr>";
		
		$resultado .= "<tr>";
		$resultado .= "<td colspan='7' width=500>";
		
		//DETALLE DE NOTAS DE DESPACHO
		$total_notas_despacho= 0;
		$notas_despacho		 = Array();
		$notas_despacho	 = VentasTrabajadorModel::obtenerNotasDespacho($vector_datos[0]['postrans'],$vector_datos[0]['dia'],$vector_datos[0]['turno'],$vector_datos[0]['codigo'],1);
		
		if(count($notas_despacho)>0)
		{
			$resultado .= "<table border='1' cellspacing=0 width=500>";
			$resultado .= "<tr>";
			$resultado .= "<th colspan='5' bgcolor='#D8D8D8' style='font-size:11px' height=20>DETALLE DE NOTAS DE DESPACHO</th>";
			$resultado .= "</tr>";
			
			for ($i = 0; $i < count($notas_despacho); $i++) 
			{
				if($i == 0)
				{
					$resultado .= "<tr>";
					$resultado .= "<th bgcolor='#A9F5F2'>TRANSACCION</th>";
					$resultado .= "<th bgcolor='#A9F5F2' colspan='3'>CLIENTE</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMPORTE</th>";
					$resultado .= "</tr>";
					if($impresion == 1)
					{
						$texto_impresion .= "NOTAS DE DESPACHO" . $CRLF;
						$texto_impresion .= "TRANS      CLIENTE              IMPORTE" . $CRLF;
					}
				}
				$a = $notas_despacho[$i];
				$resultado .= "<tr>";
				$resultado .= "<td align='center'>&nbsp;".$a['trans']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$a['codigo']."</td>";
				$resultado .= "<td align='center' colspan='2'>&nbsp;".$a['cliente']."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['importe'],2), 2, '.', ','))."</td>";
				$resultado .= "</tr>";
				$total_notas_despacho += $a['importe'];
				
				if($impresion == 1)
					$texto_impresion .= alinea($a['trans'],0,11) . alinea(substr($a['cliente'],0,18),0,19) . alinea(showNumber($a['importe']),1,9) . $CRLF;
				
			}
			
			$resultado .= "<tr>";
			$resultado .= "<th align='right' colspan='4' bgcolor='#81BEF7' style='font-size:10px'>TOTAL NOTAS DE DESPACHO</th>";
			$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($total_notas_despacho,2), 2, '.', ','))."</td>";
			$resultado .= "</tr>";
			if($impresion == 1)
			{
				$texto_impresion .= "========================================" . $CRLF;
				$texto_impresion .= alinea(showNumber($total_notas_despacho),1,39) . $CRLF;
			}
			$resultado .= "</table>";
		}
		
		//DETALLE DE NOTAS DE DESPACHO EN EFECTIVO
		$total_notas_despacho_efectivo	= 0;
		$notas_despacho_efectivo		= Array();
		$notas_despacho_efectivo		= VentasTrabajadorModel::obtenerNotasDespacho($vector_datos[0]['postrans'],$vector_datos[0]['dia'],$vector_datos[0]['turno'],$vector_datos[0]['codigo'],0);
		
		if(count($notas_despacho_efectivo)>0)
		{
			$resultado .= "&nbsp;<table border='1' cellspacing=0 width=500>";
			$resultado .= "<tr>";
			$resultado .= "<th colspan='5' bgcolor='#D8D8D8' style='font-size:11px' height=20>DETALLE DE NOTAS DE DESPACHO EN EFECTIVO</th>";
			$resultado .= "</tr>";
			
			for ($i = 0; $i < count($notas_despacho_efectivo); $i++) 
			{
				if($i == 0)
				{
					$resultado .= "<tr>";
					$resultado .= "<th bgcolor='#A9F5F2'>TRANSACCION</th>";
					$resultado .= "<th bgcolor='#A9F5F2' colspan='3'>CLIENTE</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMPORTE</th>";
					$resultado .= "</tr>";
					if($impresion == 1)
					{
						$texto_impresion .= "NOTAS DE DESPACHO EN EFECTIVO" . $CRLF;
						$texto_impresion .= "TRANS      CLIENTE              IMPORTE" . $CRLF;
					}
				}
				$a = $notas_despacho_efectivo[$i];
				$resultado .= "<tr>";
				$resultado .= "<td align='center'>&nbsp;".$a['trans']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$a['codigo']."</td>";
				$resultado .= "<td align='center' colspan='2'>&nbsp;".$a['cliente']."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['importe'],2), 2, '.', ','))."</td>";
				$resultado .= "</tr>";
				$total_notas_despacho_efectivo += $a['importe'];
				
				if($impresion == 1)
					$texto_impresion .= alinea($a['trans'],0,11) . alinea(substr($a['cliente'],0,18),0,19) . alinea(showNumber($a['importe']),1,9) . $CRLF;
				
			}
			
			$resultado .= "<tr>";
			$resultado .= "<th align='right' colspan='4' bgcolor='#81BEF7' style='font-size:10px'>TOTAL NOTAS DE DESPACHO EN EFECTIVO</th>";
			$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($total_notas_despacho_efectivo,2), 2, '.', ','))."</td>";
			$resultado .= "</tr>";
			if($impresion == 1)
			{
				$texto_impresion .= "========================================" . $CRLF;
				$texto_impresion .= alinea(showNumber($total_notas_despacho_efectivo),1,39) . $CRLF;
			}
			$resultado .= "</table>";
		}
		
		//DETALLE DE TARJETAS DE CREDITO
		$total_tarjetas		= 0;
		$tarjetas_credito	= Array();
		$tarjetas_credito	= VentasTrabajadorModel::obtenerTarjetas_Credito($vector_datos[0]['postrans'],$vector_datos[0]['dia'],$vector_datos[0]['turno'],$vector_datos[0]['codigo']);
		
		if(count($tarjetas_credito)>0)
		{
			$resultado .= "&nbsp;<table border='1' cellspacing=0 width=500>";
			$resultado .= "<tr>";
			$resultado .= "<th colspan='5' bgcolor='#D8D8D8' style='font-size:11px' height=20>DETALLE DE TARJETAS DE CREDITO</th>";
			$resultado .= "</tr>";
			
			for ($i = 0; $i < count($tarjetas_credito); $i++) 
			{
				if($i == 0)
				{
					$resultado .= "<tr>";
					$resultado .= "<th bgcolor='#A9F5F2' colspan='2'>TRANSACCION</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>NUMERO</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>TARJETA</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMPORTE</th>";
					$resultado .= "</tr>";
					if($impresion == 1)
					{
						$texto_impresion .= "TARJETAS DE CREDITO" . $CRLF;
						$texto_impresion .= "TRANS      TIPO                 IMPORTE" . $CRLF;
					}
				}
				
				$a = $tarjetas_credito[$i];
				$resultado .= "<tr>";
				$resultado .= "<td align='center' colspan='2'>&nbsp;".$a['trans']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$a['numero']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$a['tarjeta']."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['importe'],2), 2, '.', ','))."</td>";
				$resultado .= "</tr>";
				$total_tarjetas += $a['importe'];
				
				if($impresion == 1)
					$texto_impresion .= alinea($a['trans'],0,11) . alinea(substr($a['tarjeta'],0,18),0,19) . alinea(showNumber($a['importe']),1,9) . $CRLF;
				
			}
			
			$resultado .= "<tr>";
			$resultado .= "<th align='right' colspan='4' bgcolor='#81BEF7' style='font-size:10px'>TOTAL TARJETAS DE CREDITO</th>";
			$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($total_tarjetas,2), 2, '.', ','))."</td>";
			$resultado .= "</tr>";
			if($impresion == 1)
			{
				$texto_impresion .= "========================================" . $CRLF;
				$texto_impresion .= alinea(showNumber($total_tarjetas),1,39) . $CRLF;
			}
			$resultado .= "</table>";
		}
		
		//DETALLE DE DESCUENTOS
		$total_descuentos	= 0;
		$descuentos			= Array();
		$descuentos	= VentasTrabajadorModel::obtenerDescuentos($vector_datos[0]['postrans'],$vector_datos[0]['dia'],$vector_datos[0]['turno'],$vector_datos[0]['codigo']);
		
		if(count($descuentos)>0)
		{
			$resultado .= "&nbsp;<table border='1' cellspacing=0 width=500>";
			$resultado .= "<tr>";
			$resultado .= "<th colspan='5' bgcolor='#D8D8D8' style='font-size:11px' height=20>DETALLE DE DESCUENTOS</th>";
			$resultado .= "</tr>";
			
			for ($i = 0; $i < count($descuentos); $i++) 
			{
				if($i == 0)
				{
					$resultado .= "<tr>";
					$resultado .= "<th bgcolor='#A9F5F2'>TRANSACCION</th>";
					$resultado .= "<th bgcolor='#A9F5F2' colspan='2'>DESCRIPCION</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>F.PAGO</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMPORTE</th>";
					$resultado .= "</tr>";
					if($impresion == 1)
					{
						$texto_impresion .= "DESCUENTOS" . $CRLF;
						$texto_impresion .= "TRANS      PRODUCTO           FP IMPORTE" . $CRLF;
					}
				}
				
				$a = $descuentos[$i];
				$resultado .= "<tr>";
				$resultado .= "<td align='center'>&nbsp;".$a['trans']."</td>";
				$resultado .= "<td align='center' colspan='2'>&nbsp;".$a['descripcion']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$a['forma_pago']."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['importe'],2), 2, '.', ','))."</td>";
				$resultado .= "</tr>";
				$total_descuentos += $a['importe'];
				
				if($impresion == 1)
					$texto_impresion .= alinea($a['trans'],0,11) . alinea(substr($a['descripcion'],0,18),0,19) . alinea(substr($a['forma_pago'],0,18),0,3). alinea(showNumber($a['importe']),1,7) . $CRLF;
				
			}
			
			$resultado .= "<tr>";
			$resultado .= "<th align='right' colspan='4' bgcolor='#81BEF7' style='font-size:10px'>TOTAL DESCUENTOS</th>";
			$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($total_descuentos,2), 2, '.', ','))."</td>";
			$resultado .= "</tr>";
			if($impresion == 1)
			{
				$texto_impresion .= "========================================" . $CRLF;
				$texto_impresion .= alinea(showNumber($total_descuentos),1,39) . $CRLF;
			}
			$resultado .= "</table>";
		}
		
		//DETALLE DE DEVOLUCIONES
		$total_devoluciones		= 0;
		$devoluciones	= Array();
		$devoluciones	= VentasTrabajadorModel::obtenerDevoluciones($vector_datos[0]['postrans'],$vector_datos[0]['dia'],$vector_datos[0]['turno'],$vector_datos[0]['codigo']);
		
		if(count($devoluciones)>0)
		{
			$resultado .= "&nbsp;<table border='1' cellspacing=0 width=500>";
			$resultado .= "<tr>";
			$resultado .= "<th colspan='5' bgcolor='#D8D8D8' style='font-size:11px' height=20>DETALLE DE DEVOLUCIONES</th>";
			$resultado .= "</tr>";
			
			for ($i = 0; $i < count($devoluciones); $i++) 
			{
				if($i == 0)
				{
					$resultado .= "<tr>";
					$resultado .= "<th bgcolor='#A9F5F2' colspan='2'>TRANSACCION</th>";
					$resultado .= "<th bgcolor='#A9F5F2' colspan='2'>FORMA DE PAGO</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMPORTE</th>";
					$resultado .= "</tr>";
					if($impresion == 1)
					{
						$texto_impresion .= "DEVOLUCIONES" . $CRLF;
						$texto_impresion .= "TRANS      F. PAGO               IMPORTE" . $CRLF;
					}
				}
				
				$a = $devoluciones[$i];
				$resultado .= "<tr>";
				$resultado .= "<td align='center' colspan='2'>&nbsp;".$a['trans']."</td>";
				$resultado .= "<td align='center' colspan='2'>&nbsp;".$a['fpago']."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['importe'],2), 2, '.', ','))."</td>";
				$resultado .= "</tr>";
				$total_devoluciones += $a['importe'];
				
				if($impresion == 1)
					$texto_impresion .= alinea($a['trans'],0,11) . alinea(substr($a['fpago'],0,18),0,19) . alinea(showNumber($a['importe']),1,9) . $CRLF;
				
			}
			
			$resultado .= "<tr>";
			$resultado .= "<th align='right' colspan='4' bgcolor='#81BEF7' style='font-size:10px'>TOTAL DEVOLUCIONES</th>";
			$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($total_devoluciones,2), 2, '.', ','))."</td>";
			$resultado .= "</tr>";
			if($impresion == 1)
			{
				$texto_impresion .= "========================================" . $CRLF;
				$texto_impresion .= alinea(showNumber($total_devoluciones),1,39) . $CRLF;
			}
			$resultado .= "</table>";
		}
		
		//DETALLE DE AFERICIONES
		$total_afericiones	= 0;
		$afericiones		= Array();
		$afericiones		= VentasTrabajadorModel::obtenerAfericiones($vector_datos[0]['postrans'],$vector_datos[0]['dia'],$vector_datos[0]['turno'],$vector_datos[0]['codigo']);
		
		if(count($afericiones)>0)
		{
			$resultado .= "&nbsp;<table border='1' cellspacing=0 width=500>";
			$resultado .= "<tr>";
			$resultado .= "<th colspan='5' bgcolor='#D8D8D8' style='font-size:11px' height=20>DETALLE DE AFERICIONES</th>";
			$resultado .= "</tr>";
			
			for ($i = 0; $i < count($afericiones); $i++) 
			{
				if($i == 0)
				{
					$resultado .= "<tr>";
					$resultado .= "<th bgcolor='#A9F5F2' colspan='2'>TRANSACCION</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>PRODUCTO</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>DETALLE</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMPORTE</th>";
					$resultado .= "</tr>";
					if($impresion == 1)
					{
						$texto_impresion .= "AFERICIONES" . $CRLF;
						$texto_impresion .= "TRANS      PRODUCTO              IMPORTE" . $CRLF;
					}
				}
				
				$a = $afericiones[$i];
				$resultado .= "<tr>";
				$resultado .= "<td align='center' colspan='2'>&nbsp;".$a['trans']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$a['producto']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$a['detalle']."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['importe'],2), 2, '.', ','))."</td>";
				$resultado .= "</tr>";
				$total_afericiones += $a['importe'];
				
				if($impresion == 1)
					$texto_impresion .= alinea($a['trans'],0,11) . alinea(substr($a['producto'],0,18),0,19) . alinea(showNumber($a['importe']),1,9) . $CRLF;
				
			}
			
			$resultado .= "<tr>";
			$resultado .= "<th align='right' colspan='4' bgcolor='#81BEF7' style='font-size:10px'>TOTAL AFERICIONES</th>";
			$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($total_afericiones,2), 2, '.', ','))."</td>";
			$resultado .= "</tr>";
			if($impresion == 1)
			{
				$texto_impresion .= "========================================" . $CRLF;
				$texto_impresion .= alinea(showNumber($total_afericiones),1,39) . $CRLF;
			}
			$resultado .= "</table>";
		}
		
		$resultado .= "&nbsp;</td>";
		$resultado .= "<td colspan = '7' width=500>";
		
		//DETALLE DE DEPOSITOS
		$total_depositos= 0;
		$depositos		= Array();
		$depositos		= VentasTrabajadorModel::obtenerDepositos($vector_datos[0]['codigo'],$vector_datos[0]['dia'],$vector_datos[0]['turno']);
		
		if(count($depositos)>0)
		{
			$resultado .= "<table border='1' cellspacing=0 width=500>";
			$resultado .= "<tr>";
			$resultado .= "<th colspan='5' bgcolor='#D8D8D8' style='font-size:11px' height=20>DETALLE DE DEPOSITOS</th>";
			$resultado .= "</tr>";
			
			for ($i = 0; $i < count($depositos); $i++) 
			{
				if($i == 0)
				{
					$resultado .= "<tr>";
					$resultado .= "<th bgcolor='#A9F5F2'>CORRELATIVO</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>MONEDA</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>T.CAMBIO</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMPORTE</th>";
					$resultado .= "<th bgcolor='#A9F5F2'>IMP.SOLES</th>";
					$resultado .= "</tr>";
					if($impresion == 1)
					{
						$texto_impresion .= "DEPOSITOS" . $CRLF;
						$texto_impresion .= "NRO DOC    M T/C      IMPORTE     SOLES" . $CRLF;
					}
				}
				$moneda = "";
				if ($a['moneda'] == "Nuevos Soles")
					$moneda = "S";
				else
					$moneda = "D";
				$a = $depositos[$i];
				$resultado .= "<tr>";
				$resultado .= "<td align='center'>&nbsp;".$a['correlativo']."</td>";
				$resultado .= "<td align='center'>&nbsp;".$a['moneda']."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['tipo_cambio'],4), 4, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['importe'],2), 2, '.', ','))."</td>";
				$resultado .= "<td align='right'>&nbsp;".htmlentities(number_format(round($a['importe_soles'],2), 2, '.', ','))."</td>";
				$resultado .= "</tr>";
				$total_depositos += $a['importe_soles'];
				
				if($impresion == 1)
					$texto_impresion .= alinea($a['correlativo'],0,11) . alinea($moneda,0,2) . alinea($a['tipo_cambio'],0,7) . alinea(showNumber($a['importe']),1,9) . " " . alinea(showNumber($a['importe_soles']),1,9) . $CRLF;
				
			}
			
			$resultado .= "<tr>";
			$resultado .= "<th align='right' colspan='4' bgcolor='#81BEF7' style='font-size:10px'>TOTAL DEPOSITOS</th>";
			$resultado .= "<td align='right' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($total_depositos,2), 2, '.', ','))."</td>";
			$resultado .= "</tr>";
			if($impresion == 1)
			{
				$texto_impresion .= "========================================" . $CRLF;
				$texto_impresion .= alinea(showNumber($total_depositos),1,39) . $CRLF;
			}
			$resultado .= "</table>";
		}
		
		//RESUMEN DE VENTAS
		$total = 0;
		
		$resultado .= "&nbsp;<table border='1' cellspacing=0 width=500>";
		$resultado .= "<tr>";
		$resultado .= "<th colspan='6' bgcolor='#D8D8D8' style='font-size:11px' height=20>RESUMEN DE VENTAS</th>";
		$resultado .= "</tr>";
		
		$resultado .= "<tr>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='3'>TOTAL CONTOMETROS</td>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='2'>&nbsp;".htmlentities(number_format(round($total_contometros,2), 2, '.', ','))."</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6'>+</td>";
		
		/*$resultado .= "<tr>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='3'>TOTAL TICKET COMBUSTIBLE</td>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='2'>&nbsp;".htmlentities(number_format(round($total_combustible,2), 2, '.', ','))."</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6'>-</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6' colspan='8'>&nbsp;</td>";*/
		
		$resultado .= "<tr>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='3'>TOTAL TICKET MARKET</td>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='2'>&nbsp;".htmlentities(number_format(round($total_market,2), 2, '.', ','))."</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6'>-</td>";
		
		$resultado .= "<tr>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='3'>TOTAL NOTAS DE DESPACHO</td>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='2'>&nbsp;".htmlentities(number_format(round($total_notas_despacho,2), 2, '.', ','))."</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6'>-</td>";
		
		$resultado .= "<tr>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='3'>TOTAL TARJETAS DE CREDITO</td>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='2'>&nbsp;".htmlentities(number_format(round($total_tarjetas,2), 2, '.', ','))."</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6'>+</td>";
		
		$resultado .= "<tr>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='3'>TOTAL DESCUENTOS</td>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='2'>&nbsp;".htmlentities(number_format(round($total_descuentos,2), 2, '.', ','))."</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6'>+</td>";
		$resultado .= "</tr>";
		
		$resultado .= "<tr>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='3'>TOTAL DEVOLUCIONES</td>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='2'>&nbsp;".htmlentities(number_format(round($total_devoluciones,2), 2, '.', ','))."</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6'>-</td>";
		$resultado .= "</tr>";
		
		$resultado .= "<tr>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='3'>TOTAL AFERICIONES</td>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='2'>&nbsp;".htmlentities(number_format(round($total_afericiones,2), 2, '.', ','))."</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6'>-</td>";
		$resultado .= "</tr>";
		
		$resultado .= "<tr>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='3'>TOTAL DEPOSITOS</td>";
		$resultado .= "<td align='right' bgcolor='#CEE3F6' colspan='2'>&nbsp;".htmlentities(number_format(round($total_depositos,2), 2, '.', ','))."</td>";
		$resultado .= "<td align='center' bgcolor='#CEE3F6'>=</td>";
		$resultado .= "</tr>";
		
		$total = $total_depositos - ($total_contometros + $total_market - $total_notas_despacho - $total_tarjetas + $total_descuentos + $total_devoluciones - $total_afericiones);

		if(showNumber($total)>0){
			$var = " SOBRANTE";
		} else {
			$var = " FALTANTE";
		}
		$resultado .= "<tr>";
		$resultado .= "<th align='right' colspan='3' bgcolor='#81BEF7' style='font-size:10px'>DIFERENCIA TOTAL $var</th>";
		$resultado .= "<td align='right' colspan='2' bgcolor='#81BEF7' style='font-size:10px; font-weight:bold'>".htmlentities(number_format(round($total,2), 2, '.', ','))."</td>";
		$resultado .= "<th align='right' bgcolor='#81BEF7' style='font-size:10px'>&nbsp;</th>";
		$resultado .= "</tr>";
		$resultado .= "</td>";
		$resultado .= "</tr>";
		
		if($impresion == 1)
		{
			$texto_impresion .= $CRLF;
			$texto_impresion .= "Venta Contometros             " . alinea(showNumber($total_contometros),1,9) . "+" . $CRLF;
			$texto_impresion .= "Venta Market                  " . alinea(showNumber($total_market),1,9) . "-" . $CRLF;
			$texto_impresion .= "Total Notas de Despacho       " . alinea(showNumber($total_notas_despacho),1,9) . "-" . $CRLF;
			$texto_impresion .= "Total Tarjetas de Credito     " . alinea(showNumber($total_tarjetas),1,9) . "+" . $CRLF;
			$texto_impresion .= "Total Descuentos              " . alinea(showNumber($total_descuentos),1,9) . "+" . $CRLF;
			$texto_impresion .= "Total Devoluciones            " . alinea(showNumber($total_devoluciones),1,9) . "-" . $CRLF;
			$texto_impresion .= "Total Afericiones             " . alinea(showNumber($total_afericiones),1,9) . "-" . $CRLF;
			$texto_impresion .= "Total Depositos               " . alinea(showNumber($total_depositos),1,9) . "=" . $CRLF;
			$texto_impresion .= "========================================" . $CRLF;
			
			if (showNumber($total)>0)
				$word = "Sobrante";
			else
				$word = "Faltante";
			
			$texto_impresion .= $word."                      " . alinea(showNumber($total),1,9) . $CRLF;
			$texto_impresion .= $CRLF;
			$texto_impresion .= "Consolidado: " . date("d/m/Y H:i:s") . $CRLF;
			$texto_impresion .= $CRLF;
			$texto_impresion .= $CRLF;
			$texto_impresion .= $CRLF;
			$texto_impresion .= $CRLF;
			$texto_impresion .= $CRLF;
			$texto_impresion .= "------------------    ------------------" . $CRLF;
			$texto_impresion .= " Firma Trabajador     Firma Responsable" . $CRLF;
			$texto_impresion .= $CRLF;
			$texto_impresion .= $CRLF;
		}
		$resultado .= "</table>";
		
		$resultado .= "</table></center>";
		
		//Escribimos en el Ticket de Consolidacion
		if ($impresion == 1)
		{
			$dia_vector	= explode("-",$vector_datos[0]['dia']);
			$id = $dia_vector[2].$dia_vector[1].$dia_vector[0].$vector_datos[0]['turno'];
			settype($id,"int");
			$file = "/tmp/imprimir/Consolidacion_".$id;
			$fh = fopen($file, "a");
			fwrite($fh,$texto_impresion.PHP_EOL.PHP_EOL.PHP_EOL);
			fclose($fh);
			
			$file = "/sistemaweb/combustibles/movimientos/query_consolidacion.txt";
			$fh = fopen($file, "a");
			fwrite($fh,$vector_datos[0]['sucursal'].";".$vector_datos[0]['codigo'].";".$tipo_consolidacion.";".showNumber($total).";".PHP_EOL);
			fclose($fh);
		}

			$dia_vector	= explode("-",$vector_datos[0]['dia']);
			$id = $dia_vector[2].$dia_vector[1].$dia_vector[0].$vector_datos[0]['turno'];
			settype($id,"int");
			$file = "/tmp/imprimir/PreConsolidacion_".$id;
			$fh = fopen($file, "a");
			fwrite($fh,$texto_impresion.PHP_EOL.PHP_EOL.PHP_EOL);
			fclose($fh);		
			
		return $resultado;
	}
}
function showNumber($num) 
{
	return number_format(round($num,2),2,".","");
}

function espaciosA($q) 
{
	$ret = "";
	for ($q;$q>0;$q--)
		$ret .= " ";
	return $ret;
}
	
function alinea($str,$tipo,$ll) 
{
	if ($tipo==0)
		return ($str . espaciosA(($ll-strlen($str))));
	else if ($tipo==1)
		return (espaciosA(($ll-strlen($str))) . $str);
	return (espaciosA((($ll/2)-(strlen($str)/2))) . $str . espaciosA((($ll/2)-(strlen($str)/2))));
}
