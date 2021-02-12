<?php
class OrdenCompraTemplate extends Template {
	function titulo() {
		return '<h2 align="center" style="color:#336699"><b>Orden de Compra</b></h2>';
	}

	function TemplateReportePDF($reporte_array) {		
		$datos 		= array();
		$Cabecera 	= array( 
			    		"orden"  	=> "Orden",
			    		"almacen"  	=> "Almacen",
			    		"proveedor"	=> "Proveedor",
			    		"nombre"  	=> "Nombre",
			    		"fecha"  	=> "Fecha",
			    		"moneda"  	=> "Moneda",
			    		"t_cambio" 	=> "T.Cambio",
			    		"importe"  	=> "Importe",
			    		"estado" 	=> "Estado",
			    		"factura"  	=> "Factura");

		$fontsize 	= 8;

		$reporte 	= new CReportes2();
		$reporte->SetMargins(5, 5, 5);
		$reporte->SetFont("courier", "", $fontsize);
		$reporte->definirCabecera(10, "L", "Ordenes de Compra");
		$reporte->definirCabecera(10, "R", "Pagina %p");
		$reporte->definirColumna("orden", $reporte->TIPO_TEXTO, 7, "L");
		$reporte->definirColumna("almacen", $reporte->TIPO_TEXTO, 16, "L");
		$reporte->definirColumna("proveedor", $reporte->TIPO_TEXTO, 7, "L");
		$reporte->definirColumna("nombre", $reporte->TIPO_TEXTO, 28, "L");
		$reporte->definirColumna("fecha", $reporte->TIPO_TEXTO, 8, "L");
		$reporte->definirColumna("moneda", $reporte->TIPO_TEXTO, 8, "L");
		$reporte->definirColumna("t_cambio", $reporte->TIPO_TEXTO, 7, "L");
		$reporte->definirColumna("importe", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirColumna("estado", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirColumna("factura", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirCabeceraPredeterminada($Cabecera);
		$reporte->AddPage();
		foreach($reporte_array as $llave => $valores) {
			$reporte->nuevaFila($valores);
		}
		$reporte->Output("/sistemaweb/compras/movimientos/pdf/reporte_ordenes.pdf", "F");

		return '<center><iframe src="/sistemaweb/compras/movimientos/pdf/reporte_ordenes.pdf" style="width:0px; height:0px;" frameborder="0"></iframe></center>';			
	}

	function TemplateReportePDFPersonal($recordord,$recordpro,$recordalmac,$recordalmac2,$recordalmac3,$ordenes,$monedas,$fpagos,$importante, $statusFlete, $arrFlete){	
		//global $usuario;

		$cabecera1 = Array
		(
		   "ITEM"         =>	"ITEM",		   
		   "CODIGO"       =>	"CODIGO",
		   "DESCRIPCION"  =>	"DESCRIPCION",
		   "CANT"         =>	"CANT",
		   "UNIDAD"  	  =>	"UNIDAD",
		   "PRECIO"       =>	"PRECIO",
		   "DESCUENTO"    =>	"DESCUENTO",
		   "VALOR VENTA"  =>	"VALOR VENTA"
		);

		$fontsize = 9;
		$reporte = new CReportes2();
		$reporte->SetMargins(5, 12, 5);
		$reporte->SetFont("courier", "", $fontsize);
		$reporte->definirCabeceraImagen(1,3,"/sistemaweb/logocliente.jpg", 100, 50);
		$reporte->definirCabeceraSize(4, "L", "courier,B,15", "                                 ORDEN DE COMPRA Nro. ".$recordord['com_cab_numorden']."");
		$reporte->definirCabeceraSize(5, "R", " ", " ");
		$reporte->definirCabeceraSize(6, "L", "courier,B,15", trim($recordalmac2['ruc']) . " " . trim($recordalmac2['razsocial']));
		$reporte->definirCabeceraSize(7, "L", "courier,B,9", "".trim($recordalmac2['ch_direccion']));
		$reporte->definirCabeceraSize(8, "L", "courier,B,9", "".trim($recordalmac2['ch_telefonos']));
		$reporte->definirCabeceraSize(9, "L", "courier,N,8", "       ");
		$reporte->definirCabecera(10, "L", "Fecha : ".$recordord['com_cab_fechaorden']." ");// Colocar Fecha
		$reporte->definirCabecera(11, "R", " ");
		$reporte->definirCabecera(12, "L", "Senores          : ".trim($recordpro['pro_razsocial'])."");
		$reporte->definirCabecera(13, "L", "RUC              : ".trim($recordpro['pro_ruc'])."");
		$reporte->definirCabecera(14, "L", "Direccion        : ".trim($recordpro['pro_direccion'])."  ".trim($recordpro['pro_comp_direcc'])."");// Colocar dir
		$reporte->definirCabecera(15, "L", "Telefonos        : ".trim($recordpro['pro_telefono1']).", ".trim($recordpro['pro_telefono2'])." ");// Colocar telef.
		$reporte->definirCabecera(16, "L", "Almacen	          : ".trim($recordalmac3['ch_nombre_almacen'])."");// Colocar Lugar
		$reporte->definirCabecera(17, "L", "Lugar de Entrega : ".trim($recordalmac3['ch_direccion_almacen'])."");
		$reporte->definirCabecera(18, "L", "Fecha de Entrega : ".$recordord['com_cab_fechaofrecida']."");// Colocar fecha de entrega.
		$reporte->definirCabecera(19, "R", " ");
		$reporte->definirCabecera(20, "L", "Sirvase a entregarnos a la direccion y en las condiciones que precisamos, lo siguiente :");
		$reporte->definirCabecera(21, "R", " ");
		$reporte->definirColumna("ITEM", $tipo->TIPO_TEXT, 4, "L");
		
		$reporte->definirColumna("CODIGO", $tipo->TIPO_TEXT, 15, "C");
		$reporte->definirColumna("DESCRIPCION", $tipo->TIPO_TEXT, 30, "C");
		$reporte->definirColumna("CANT", $tipo->TIPO_TEXT, 4, "R");
		$reporte->definirColumna("UNIDAD", $tipo->TIPO_TEXT, 10, "C");
		$reporte->definirColumna("PRECIO", $tipo->TIPO_IMPORTE, 9, "R");
		$reporte->definirColumna("DESCUENTO", $tipo->TIPO_IMPORTE, 9, "R");
		$reporte->definirColumna("VALOR VENTA", $tipo->TIPO_IMPORTE, 10, "R");
		$reporte->definirCabeceraPredeterminada($cabecera1);
		$reporte->addPage();

		$total_cantidad = 0;
		$total_venta = 0;
		foreach($ordenes as $llave => $valor) {
		   $datos['ITEM']       = $valor['ITEM'];		   
		   $datos['CODIGO']     = $valor['CODIGO'];
		   $datos['DESCRIPCION']= $valor['DESCRIPCION'];
		   $datos['PRECIO']     = $valor['PRECIO'];
		   $datos['DESCUENTO']  = $valor['DESCUENTO'];
		   $datos['CANT']       = $valor['CANT'];
		   $datos['UNIDAD']     = $valor['UNIDAD'];
		   $datos['VALOR VENTA']= $valor['VALOR VENTA'];
		   $total_cantidad     += $valor['CANT'];
		   $total_venta        += $valor['VALOR VENTA'];
		   $reporte->nuevaFila($datos);
		}

		$total_venta = money_format('%.2n',$total_venta);
		$valor_venta = money_format('%.2n',($total_venta / 1.18));
		$igv = money_format('%.2n',($total_venta-$valor_venta));
		$reporte->Ln();
		$reporte->Multicell(585,12,''.$recordord['com_cab_det_glosa'].'',0,'L');
		$reporte->Ln();
		$reporte->Ln();
		$reporte->cell(0,10,'VALOR VENTA : '.$valor_venta.'             I.G.V. : '.$igv.'              PRECIO TOTAL  : '.(round($recordord['com_cab_moneda'])==1?'S./':'US$').'  '.$total_venta.'',1,0,'L');

		$reporte->Lnew();
		$reporte->cell(0,10,'IMPORTE PERCEPCION : '.trim($recordord['percepcion_i']).'             TOTAL + PERCEPCION : '.trim($recordord['percepcion']).'',1,0,'L');		
		$reporte->Lnew();
		$reporte->Lnew();

		$reporte->SetFont('');
		$reporte->SetFont('Courier','',9); 
		$reporte->cell(0,0,'Forma de Pago       : '.$fpagos[trim($recordord['com_cab_formapago'])].'',0,0,'L');
		$reporte->Lnew();
		$reporte->cell(0,0,'Moneda              : '.strtoupper($monedas[round($recordord['com_cab_moneda'])]).'',0,0,'L');
		$reporte->Lnew();
		$reporte->Multicell(585,12,'Otras Instrucciones : '.$recordord['com_cab_observacion'].'',0,'L');

		/* FLETE */

		if ($statusFlete == 'S') {
		    $arrMotivoTraslado = array(
		    	0 => "Venta",
		    	1 => "Venta Sujeta a Confirmacion del Comprador",
		    	2 => "Compra",
				3 => "Consignacion",
				4 => "Devolucion",
				5 => "Traslado entre Establecimentos de la Misma Empresa",
				6 => "Traslado de Bienes para Transformacion",
				7 => "Recojo de Bienes Transformados",
				8 => "Traslado por Emisor Itinerante de Comprobantes de Pago",
				9 => "Traslado Zona Primaria",
				10 => "Importacion",
				11 => "Exportacion",
				12 => "Venta con entrega a terceros",
				13 => "Otros"
			);


		    $html_option_motivo_traslado = '';
			foreach($arrMotivoTraslado as $key => $value){
				$selected = '';
				if ($arrFlete['result'][0]['id_motivo_traslado'] == $key)
					$No_Motivo_Traslado = $value;
			}

			$reporte->Lnew();
			$reporte->cell(0,10,'DATOS DEL FLETE',1,0,'L');

			$reporte->Lnew();
			$reporte->cell(0,10,'TRANSPORTE                 : ' . $arrFlete['result'][0]['pro_ruc'] . ' ' . $arrFlete['result'][0]['pro_razsocial'],0,0,'L');
			//$reporte->cell(0,10,'CERTIFICADO DE INSCRIPCION : ' . $arrFlete['result'][0]['no_certificado_inscripcion'] . '                                                 NRO. BREVETE: ' . $arrFlete['result'][0]['no_licencia'],1,0,'L');
			$reporte->Lnew();
			$reporte->cell(0,10,'NRO. BREVETE 	              : ' . $arrFlete['result'][0]['no_licencia'],0,0,'L');
			$reporte->Lnew();
			$reporte->cell(0,10,'CERTIFICADO DE INSCRIPCION : ' . $arrFlete['result'][0]['no_certificado_inscripcion'],0,0,'L');
			$reporte->Lnew();
			$reporte->cell(0,10,'PLACA                      : ' . $arrFlete['result'][0]['no_placa'],0,0,'L');
			$reporte->Lnew();
			$reporte->cell(0,10,'MOTIVO TRASLADO            : ' . $No_Motivo_Traslado,0,0,'L');
			$reporte->Lnew();
			$reporte->Lnew();
		}

		$valY = (701 - $reporte->GetY());
		$setY = ($valY + $reporte->GetY());
		$reporte->SetY($setY); //Asignar el valor de Y para el Pie de página

		//INICIO : Imprimir Valores del Pie de página
		$reporte->SetFont('Courier','',8); 
		$reporte->Ln();
		//$reporte->cell(0,7,'Preparado por : '.$usuario->nombre.'                                              Aprobado por : __________',0,0,'C');
		$reporte->Ln();
		$reporte->cell(0,0,'                         -------------------                            ----------------------',0,0,'L');
		$reporte->Lnew();
		$reporte->cell(0,0,'                         Encargado de Market                            Encargado de Logistica',0,0,'L');
		$reporte->Lnew();
		$reporte->SetFont('Courier','B',8); 
		$reporte->Lnew();
		$reporte->cell(0,7,'**IMPORTANTE**',0,0,'L');
		$reporte->SetFont('Courier','',7); 
		$reporte->Ln();
		$datos = explode("  ", $importante['par_valor']);
		$i = 0;
		while ($datos[$i]!= '')  {
			if (strlen($datos[$i]) <= 100) {
				$reporte->cell(10,7,$datos[$i],0,0,'L');
				$reporte->Ln();
			} else {$reporte->Ln();
				$reporte->Multicell(570,5,$datos[$i],0,'J');
			}
			$i = $i + 1;
		}

	    $reporte->Output("/sistemaweb/compras/movimientos/pdf/OrdenCompra_".trim($recordord['com_cab_numorden']).trim($recordord['num_seriedocumento']).".pdf", "F");

		return '<script> window.open("/sistemaweb/compras/movimientos/pdf/OrdenCompra_'.trim($recordord["com_cab_numorden"]).trim($recordord['num_seriedocumento']).'.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}

	function reportePedidoCompra($resultados) {//cai
		$form = new form2('', 'ListarPedido', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.ORDENCOMPRA'));

		$result .= '<table border="0" style="border: 0; border-style: simple; border-color: #000000;" align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Nro. Pedido&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Fecha&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Tipo&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Observacion&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Fecha Actualizacion&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera" colspan="2" >&nbsp;</th>';
		$result .= '</tr>';

		for ($i=0; $i<count($resultados); $i++) {
			$result .= '<tr>';
			$result .= '<td align="center">'.trim($resultados[$i]['num_pedido']).'</td>';
			$result .= '<td align="center">'.trim($resultados[$i]['fecha']).'</td>';
			$result .= '<td align="center">'.trim($resultados[$i]['tipo']).'</td>';
			$result .= '<td align="center">'.trim($resultados[$i]['observacion']).'</td>';
			$result .= '<td align="center">'.trim($resultados[$i]['actualizacion']).'</td>';
			$result .= "<td align='center'><button type='submit' name='action' value='ListarPedido'>Listar</button></td>'";
			//$result .= "<td align='center'><a style='text-decoration:underline;cursor:pointer;' identificador='" . trim($resultados[$i]['num_pedido']) . "' onClick=verPedidoCompra('" . trim($resultados[$i]['num_pedido']) . "');><img src='/sistemaweb/icons/gadd.png' /></a></td>";
			$result .= '</tr>';	//type='submit' name='action' value='BuscarPedido'	    	
		}		
		
		$result .= '</table>';

		return $result;
    	}

	function formSearch($dUltimoCierre, $almacenes, $arrTipoMovimientoInventarios, $arrTipoDocumentos, $arrTipoMonedas) {

		if (!isset($fecha)) $fecha = date("d/m/Y");

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.ORDENCOMPRA'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="txt-dUltimoCierre" name="txt-dUltimoCierre" value="' . $dUltimoCierre . '">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacen: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
	       			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbo-iAlmacen', '', 'TODAS', $almacenes, espacios(3), array("onfocus" => "getFechasIF();getDatos();")));
	       		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dInicial', '', $fecha, '', 12, 10));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('txt-dFinal', '', $fecha, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Consultar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="Reporte"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		/* Jquery - Dialog */
		if ($arrTipoMovimientoInventarios['estado']) {
			$html_option_inventario = '';
			foreach ($arrTipoMovimientoInventarios['result'] as $row) {
				$html_option_inventario .= '<option value="' . $row['id_nu_codigo'] . '">' . $row['no_descripcion'] . '</option>';
			}
		} else
			$html_option_inventario = $arrTipoMovimientoInventarios['mensaje'];

		if ($arrTipoDocumentos['estado']) {
			$html_option_documento = '';
			foreach ($arrTipoDocumentos['result'] as $row) {
				$html_option_documento .= '<option value="' . $row['id_nu_codigo'] . '">' . $row['no_descripcion'] . '</option>';
			}
		} else
			$html_option_documento = $arrTipoDocumentos['mensaje'];

		$form->addElement(FORM_GROUP_MAIN,new f2element_freeTags('
		<div id="dialog" title="Generar Inventario">
			<input type="hidden" id="txt-arrOrdenCompra" name="txt-arrOrdenCompra"/>

			<input type="hidden" id="txt-almacen" name="txt-almacen"/>
			<input type="hidden" id="txt-fechaInicio" name="txt-fechaInicio"/>
			<input type="hidden" id="txt-fechaFinal" name="txt-fechaFinal"/>

			<table border="0" align="center" cellpadding="5" cellspacing="5">
				<tr>
					<td>Tipo Inventario: </td>
					<td>
						<select id="cbo-tipo-inventario" onfocus="getFechasIF()">
						    ' . $html_option_inventario . '
					    </select>
					</td>
			    </tr>
				<tr>
					<td>Tipo Documento: </td>
					<td>
						<select id="cbo-tipo-documento">
						    ' . $html_option_documento . '
					    </select>
					</td>
			    </tr>
				<tr>
					<td>Serie: </td>
					<td>
						<input type="text" id="serie" name="serie" maxlength="4" value="" required onkeypress="return validar(event,1)" onblur="autocompleteSerieCompraCeros()" />
					</td>
			    </tr>
				<tr>
					<td>Número: </td>
					<td>
						<input type="text" id="numero" name="numero" maxlength="8" value="" required onkeypress="return validar(event,2)" onblur="autocompleteNumeroCompraCeros()" />
					</td>
			    </tr>
				<tr>
					<td>Fecha Emision: </td>
					<td>
						<input type="text" id="txt-dFechaEmision" name="txt-dFechaEmision" value="' . $fecha . '"/>
					</td>
			    </tr>
				<tr>
					<td colspan="2" align="center">
						<button type="submit" name="action" id="btn-save-inventario" value="GENERAR_INVENTARIO" onClick="saveInventario();"><img src="/sistemaweb/icons/gadd.png" align="right" /> Guardar</button>
						<button type="button" name="action" id="btn-close" onClick="salirInventario();"><img src="/sistemaweb/icons/gdelete.png" align="right" /> Cancelar</button>
					</td>
				</tr>
		</div>
		'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("cbo-iAlmacen").focus();}</script>'));
		return $form->getForm();
	}

	function formAgregar($numero_orden,$serie_documento, $almacenes, $estados, $monedas, $fpago1, $fpago2) {

		$checksi=" ";
		$checkno=" ";
		if ($m_credito=='S') {$checksi="checked"; }  
		else {$checkno="checked"; }

		$form = new Form('','editar', FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ORDENCOMPRA"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_submit("submitButton\" style=\"visibility:hidden;\" id=\"submitButton", "submitButton", '', '', 20));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action\" id=\"action", "actualizaAgregar"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='1' width=100%>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>NUMERO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold; font-size: 14px'>&nbsp;".$numero_orden));
		$form->addElement(FORM_GROUP_MAIN, new form_element_hidden('numero', $numero_orden));
		$form->addElement(FORM_GROUP_MAIN, new form_element_hidden('serie', $serie_documento));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>FECHA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "fecha:", date("d/m/Y"), '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'editar.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>'));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>ALMACEN</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "ALMACEN :", "", $almacenes,espacios(6)));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>PROVEEDOR</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td class='form_texto'>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<input type="text" id="txt-No_ProveedorRUC" name="proveedor" value="" maxlength="15" size="15"></div>
		 '));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
        <input type="text" id="txt-No_Proveedor" onkeyup="autocompleteBridge(2)" class="mayuscula" name="nombre" placeholder="Ingresar Código o Nombre" autocomplete="off" value="" maxlength="15" size="50">
        '));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>MONEDA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("moneda", "MONEDA :", "", $monedas, espacios(6)));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>T.CAMBIO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tcambio', '', '', '', 15, 15, false));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>CREDITO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='radio' name='m_credito' value='S' ".$checksi."  onChange=cambiarCombo('S');>SI"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='radio' name='m_credito' value='N' ".$checkno."  onChange=cambiarCombo('N');>NO"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>FACTURA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'factura', '', '', '', 15, 7, false));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>F.DE PAGO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago1','F.PAGO :','',$fpago1,espacios(6), '',array('id=fpago1', 'style="display: none"','')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago2','F.PAGO :','',$fpago2,espacios(6), '',array('id=fpago2', 'style="display: block"','')));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>COMENTARIO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'comentario', '', '', '', 50, 20, false));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>F.DE ENTREGA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fentrega", "fentrega:", date("d/m/Y"), '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'editar.fentrega'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>GLOSA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='checkbox' name='chglosa' value='S' onclick='javascript:activaGlosa(this,document.getElementById(\"glosa\"),forms[0].glosa);'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr valign='top'><td colspan='8' align='center' valign='top'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<div id='glosa' style='display:none; font-weight:bold'>DESCRIPCI&Oacute;N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'glosa', '', '', '', 110, 200, false));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</div>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		/* FLETE */
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th></th><th></th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>FLETE</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='checkbox' name='chflete' value='S' onclick='javascript:activaFlete(this,document.getElementById(\"flete\"),forms[0].flete);'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr valign='top'><td colspan='8' align='center' valign='top'>"));

	    $arrMotivoTraslado = array(
	    	0 => "Venta",
	    	1 => "Venta Sujeta a Confirmacion del Comprador",
	    	2 => "Compra",
			3 => "Consignacion",
			4 => "Devolucion",
			5 => "Traslado entre Establecimentos de la Misma Empresa",
			6 => "Traslado de Bienes para Transformacion",
			7 => "Recojo de Bienes Transformados",
			8 => "Traslado por Emisor Itinerante de Comprobantes de Pago",
			9 => "Traslado Zona Primaria",
			10 => "Importacion",
			11 => "Exportacion",
			12 => "Venta con entrega a terceros",
			13 => "Otros"
		);

	    $html_option_motivo_traslado = '';
		foreach($arrMotivoTraslado as $key => $value){
			$selected = '';
			if ($ID_Motivo_Traslado == $key)
				$selected = "selected";
			$html_option_motivo_traslado .= '<option value="' . $key . '">' . $value . '</option>';
		}

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("
			<div id='flete' style='display:none; font-weight:bold'>
				<table border='1'>
					<tr>
						<td>Fecha Traslado</td>
						<td>
							<input name='fe_flete' id='fe_flete' value='" . date("d/m/Y") . "' class='form_input' size='10' maxlength='12' type='text'>
							<a href='javascript:show_calendar(\"editar.fe_flete\");'> <img src='/sistemaweb/images/showcalendar.gif' border='0' align='top'/></a>
						</td>

						<td>Motivo Traslado</td>
						<td>
							<select id='cbo-MotivoTraslado' name='cbo-MotivoTraslado'>
								" . $html_option_motivo_traslado . "
							</select>
						</td>
					</tr>

					<tr>
						<td>Placa Vehículo</td>
						<td>
							<input name='no_placa' id='no_placa' value='' autocomplete='off' class='form_input' size='20' maxlength='65' type='text'>
						</td>
						<td>Licencia</td>
						<td>
							<input name='no_licencia' id='no_licencia' value='' autocomplete='off' class='form_input' size='30' maxlength='65' type='text'>
						</td>
					</tr>

					<tr>
						<td>Certificado de Inscripción</td>
						<td>
							<input name='no_certificado_inscripcion' id='no_certificado_inscripcion' value='' autocomplete='off' class='form_input' size='30' maxlength='65' type='text'>
						</td>
						<td>Proveedor Transportista</td>
						<td>
							<input type='hidden' id='txt-ID_Transportista_Proveedor' name='id_transportista_proveedor' value='' maxlength='15' size='15'>
							<input type='text' id='txt-No_Transportista_Proveedor' name='no_transportista_proveedor' onkeyup='autocompleteBridge(4)' class='mayuscula' placeholder='Ingresar Código o Nombre' autocomplete='off' value='' maxlength='15' size='50'>
						</td>
					</tr>
				</table>
			</div>
		"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));


		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>&nbsp;</td></tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='1'>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th><th>PERCEPCION :</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan=8 style='font-weight:bold; font-size: 14px' valign=top>".""));
        $form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border=0><tr><td><input type='checkbox' name='percep' value='1.01' onclick='javascript:activaPer(this,document.getElementById(\"percep\"),forms[0].percep);'>
                				</td><td><div id='percep' style='display:none; font-weight:bold'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext(" %:"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'percepcion', '', '', '', '', 200, false)); 
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<input type='hidden' name='xpercepcion' value=''>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</div></td></tr></table>"));

/* PEDIDO DE COMPRAS cai */
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th><th>PEDIDO MERC.:</th>"));
		//$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		//$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='checkbox' name='chpedido' value='S' onclick='javascript:activaPedido(this,document.getElementById(\"pedido\"),forms[0].pedido);'>"));
		//$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan='8' valign='top'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border=0><tr><td><div id='pedido' style='display:none; font-weight:bold'>
						<td>F Inicio</td>
						<td><input name='fe_inicio' id='fe_inicio' value='" . date("d/m/Y") . "' class='form_input' size='10' maxlength='12' type='text'>
							<a href='javascript:show_calendar(\"editar.fe_inicio\");'> <img src='/sistemaweb/images/showcalendar.gif' border='0' align='top'/></a>
						</td>
						<td>F Final</td>
						<td><input name='fe_final' id='fe_final' value='" . date("d/m/Y") . "' class='form_input' size='10' maxlength='12' type='text'>
							<a href='javascript:show_calendar(\"editar.fe_final\");'> <img src='/sistemaweb/images/showcalendar.gif' border='0' align='top'/></a>
						</td>
						<td><button type='submit' name='action' value='BuscarPedido'>Buscar</button>
						</td>

			</div></td></tr></table>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>CODIGO</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>DESCRIPCION</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>UNIDAD</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>CANTIDAD</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>COSTO UNITARIO</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>DESCUENTO</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>SUBTOTAL</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		<input type="text" id="txt-Nu_Id_Producto" name="codigo" value="" maxlength="15" size="10"></div>
		 '));	
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
        <input type="text" id="txt-No_Producto" onkeyup="autocompleteBridge(0)" class="mayuscula" name="descripcion" placeholder="Ingresar Código o Nombre" autocomplete="off" value="" maxlength="15" size="50">         
        '));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'unidad', '', '', '', 8, 15, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'cantidad', '', '', '', 10, 15, false, "onChange='Subtotal()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'precio', '', '', '', 10, 15, false, "onChange='Subtotal()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descuento', '', '', '', 10, 15, false, "onChange='Subtotal()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'subtotal','0.00', '', '', 10, 15, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Insertar", '', "", 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td colspan='7'></td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Regresar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='5'>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));

		return $form->getForm();
	}

	function formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $numero_orden,$serie_documento, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $nombre, $tcambio, $factura, $comentario, $m_credito, $glosa,$fecha, $fentrega, $almacen, $valormoneda, $formapago1, $formapago2, $perc, $statusFlete, $Fe_Flete, $ID_Motivo_Traslado, $No_Placa, $No_Licencia, $No_Certificado_Inscripcion, $ID_Transportista_Proveedor, $No_Transportista_Proveedor) {
		
		$checksi=" ";
		$checkno=" ";
		if ($m_credito=='S')
			$checksi="checked";
		else
			$checkno="checked";

		$form = new Form('','editar', FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ORDENCOMPRA"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_submit("submitButton\" style=\"visibility:hidden;\" id=\"submitButton", "submitButton", '', '', 20));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action\" id=\"action", "actualizaAgregar"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='1' width=100%>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>NUMERO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold; font-size: 14px'>&nbsp;".$numero_orden));
		$form->addElement(FORM_GROUP_MAIN, new form_element_hidden('numero', $numero_orden));
		$form->addElement(FORM_GROUP_MAIN, new form_element_hidden('serie', $serie_documento));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>FECHA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "fecha:", $fecha, '', 10, 12,'', array("readonly")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'editar.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>'));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>ALMACEN</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "ALMACEN :", $almacen, $almacenes,espacios(6),'',array("disabled")));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>PROVEEDOR</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td class='form_texto'>"));
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'proveedor',$proveedor, '', '', 15, 15, true));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('."'../maestros/ayuda/lista_ayuda.php','editar.proveedor','editar.nombre','proveedores'".')">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<br/>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'nombre',$nombre, '', '', 50, 15, true));


		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>MONEDA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("moneda", "MONEDA :", $valormoneda, $monedas, espacios(6),'',$habilitado3));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>T.CAMBIO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tcambio', $tcambio, '', '', 15, 15, $habilitado1));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>CREDITO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='radio' name='m_credito' value='S' ".$habilitado4." ".$checksi."  onChange=cambiarCombo('S');>SI"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='radio' name='m_credito' value='N' ".$habilitado4." ".$checkno."  onChange=cambiarCombo('N');>NO"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>FACTURA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'factura', $factura, '', '', 15, 7, $habilitado1));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>F.DE PAGO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));

		if ($m_credito == 'S'){
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago1','F.PAGO :',$formapago1,$fpago1,espacios(6), '',array('id=fpago1', 'style="display: block"',$habilitado4)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago2','F.PAGO :',$formapago2,$fpago2,espacios(6), '',array('id=fpago2', 'style="display: none"',$habilitado4)));
		}else{
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago1','F.PAGO :',$formapago1,$fpago1,espacios(6), '',array('id=fpago1', 'style="display: none"',$habilitado4)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago2','F.PAGO :',$formapago2,$fpago2,espacios(6), '',array('id=fpago2', 'style="display: block"',$habilitado4)));
		}
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>COMENTARIO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'comentario', $comentario, '', '', 50, 20, $habilitado1));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>F.DE ENTREGA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fentrega", "fentrega:", $fentrega, '', 10, 12,'',$habilitado2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'editar.fentrega'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>GLOSA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));

		if ($glosa != ''){
			$habilitado4 = $habilitado4.' checked '; 
		}

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='checkbox' name='chglosa' value='S' ".$habilitado4."  onclick='javascript:activaGlosa(this,document.getElementById(\"glosa\"),forms[0].glosa);'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr valign='top'><td colspan='8' align='center' valign='top'>"));

		if ($glosa != ''){
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<div id='glosa' style='font-weight:bold'>DESCRIPCI&Oacute;N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;"));
		} else {
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<div id='glosa' style='display:none; font-weight:bold'>DESCRIPCI&Oacute;N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;"));
		}

		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'glosa', $glosa, '', '', 110, 200, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</div>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		/* FLETE */
		if ($statusFlete == 'S')
			$checkedFlete = $checkedFlete . ' checked '; 

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th></th><th></th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>FLETE</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='checkbox' name='chflete' value='S' " . $checkedFlete . " onclick='javascript:activaFlete(this,document.getElementById(\"flete\"),forms[0].flete);'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

	    $arrMotivoTraslado = array(
	    	0 => "Venta",
	    	1 => "Venta Sujeta a Confirmacion del Comprador",
	    	2 => "Compra",
			3 => "Consignacion",
			4 => "Devolucion",
			5 => "Traslado entre Establecimentos de la Misma Empresa",
			6 => "Traslado de Bienes para Transformacion",
			7 => "Recojo de Bienes Transformados",
			8 => "Traslado por Emisor Itinerante de Comprobantes de Pago",
			9 => "Traslado Zona Primaria",
			10 => "Importacion",
			11 => "Exportacion",
			12 => "Venta con entrega a terceros",
			13 => "Otros"
		);

	    $html_option_motivo_traslado = '';
		foreach($arrMotivoTraslado as $key => $value) {
			$selected = '';
			if ($ID_Motivo_Traslado == $key)
				$selected = "selected";
			$html_option_motivo_traslado .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
		}

		if ($statusFlete == 'S'){
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr valign='top'><td colspan='8' align='center' valign='top'>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("
				<div id='flete' style='font-weight:bold'>
					<table border='1'>
						<tr>
							<td>Fecha Traslado</td>
							<td>
								<input name='fe_flete' id='fe_flete' value='" . $fentrega . "' class='form_input' size='10' maxlength='12' type='text'>
								<a href='javascript:show_calendar(\"editar.fe_flete\");'> <img src='/sistemaweb/images/showcalendar.gif' border='0' align='top'/></a>
							</td>

							<td>Motivo Traslado</td>
							<td>
								<select id='cbo-MotivoTraslado' name='cbo-MotivoTraslado'>
									" . $html_option_motivo_traslado . "
								</select>
							</td>
						</tr>

						<tr>
							<td>Placa Vehículo</td>
							<td>
								<input name='no_placa' id='no_placa' value='" . $No_Placa . "' autocomplete='off' class='form_input' size='20' maxlength='65' type='text'>
							</td>
							<td>Licencia</td>
							<td>
								<input name='no_licencia' id='no_licencia' value='" . $No_Licencia . "' autocomplete='off' class='form_input' size='30' maxlength='65' type='text'>
							</td>
						</tr>

						<tr>
							<td>Certificado de Inscripción</td>
							<td>
								<input name='no_certificado_inscripcion' id='no_certificado_inscripcion' value='" . $No_Certificado_Inscripcion . "' autocomplete='off' class='form_input' size='30' maxlength='65' type='text'>
							</td>
							<td>Proveedor Transportista</td>
							<td>
								<input type='hidden' id='txt-ID_Transportista_Proveedor' name='id_transportista_proveedor' value='" . $ID_Transportista_Proveedor . "' maxlength='15' size='15'>
								<input type='text' id='txt-No_Transportista_Proveedor' name='no_transportista_proveedor' onkeyup='autocompleteBridge(4)' class='mayuscula' placeholder='Ingresar Código o Nombre' autocomplete='off' value='" . $No_Transportista_Proveedor . "' maxlength='15' size='50'>
							</td>
						</tr>
					</table>
				</div>
			"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));
		}

		$nfilas= 0;
		foreach($articulos['articulos'] as $c => $d) {			
			$nfilas++;
		}		
			$vper=number_format($articulos['articulos'][$nfilas-1]['perce'],2,'.','.');
			$vper_i=number_format($articulos['articulos'][$nfilas-1]['perce_i'],2,'.','.');
			$xtotal=number_format($articulos['articulos'][$nfilas-1]['total'],2,'.','.');

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>&nbsp;</td></tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='1'>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th><th>PERCEPCION :</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan=7 style='font-weight:bold; font-size: 14px' valign=top>".""));
		
		if($perc==''){$checked=""; $block="none";}else{$checked="checked"; $block="block";}
                $form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border=0><tr><td><input type='checkbox' $checked name='percep2' value='v' onclick='javascript:activaGlosa2(this,document.getElementById(\"glosa2\"),forms[0].glosa2);'>
                				</td><td><div id='glosa2' style='display:$block; font-weight:bold' align=left>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext(" %:"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'xpercepcion', $perc, '', '', '', '', false, "onChange='percepcionx()';onkeyup='soloNumeros(this)'"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</div></td></tr></table>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));



		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>CODIGO</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>DESCRIPCION</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>UNIDAD</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>CANTIDAD</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>COSTO UNITARIO</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>DESCUENTO</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>SUBTOTAL</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'codigo', '', '', '', 10, 15, false));
	 	//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda2('."'/sistemaweb/maestros/ayuda/lista_ayuda2.php','editar.codigo','editar.descripcion','editar.unidad','articulos'".')">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>"));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descripcion', '', '', '', 50, 15, true));
		$form->addElement(FORM_GROUP_MAIN,new f2element_freeTags('<input type="text" maxlength="15" size="50" class="form_input" value="" onkeyup="autocompleteBridge(3)" id="descripcion" name="descripcion">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'unidad', '', '', '', 8, 15, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'cantidad', '', '', '', 10, 15, false, "onChange='Subtotal()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'precio', '', '', '', 10, 15, false, "onChange='Subtotal()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descuento', '', '', '', 10, 15, false, "onChange='Subtotal()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'subtotal','0.00', '', '', 10, 15, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Insertar", '', "", 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));
		

		$numfilas= 0;
		foreach($articulos['articulos'] as $a => $b) {
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th><input type='radio' id='codart' name='codart' value='$b[0]' onclick='marcar(this, ".$numfilas.", \"editar\", \"posi\")'></th>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>".$b[0]."</th>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan='2'>".$b[1]."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>&nbsp;</td>"));			
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right'>".number_format($b[2],2,'.','.')."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right'>".number_format($b[3],4,'.','.')."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right'>".$b[4]."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right'>".$b[5]."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));
			$numfilas= $numfilas +1;
		}

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td colspan='7'></td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Eliminar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", $boton, '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Regresar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='center' style='font-weight: bold; color: blue'>TOTAL</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right' style='font-weight: bold; color: blue'>".number_format($articulos['articulos'][$numfilas-1]['descuento'],2,'.','.')."</td>"));
		//$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right' style='font-weight: bold; color: blue'>".number_format($articulos['articulos'][$numfilas-1]['total'],2,'.','.')."</td>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right' style='font-weight: bold; color: blue'>"));
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'totalx',$xtotal, '', '', 10, 15, true,"style=border:0px;color:blue;text-align:right"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan=5 valign=top>"."</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th><font color='blue'>PERCEPCION</font></th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td valign=top>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'valor_percep_i',$vper_i, '', '', 10, 15, true, "style=border:0px;color:blue;text-align:right", "onChange='percepcionx()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>")); 
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td valign=top>")); 
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'valor_percep',$vper, '', '', 10, 15, true, "style=border:0px;color:blue;text-align:right", "onChange='percepcionx()';onkeyup='soloNumeros(this)'"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));
		return $form->getForm();
	}

	function formActualizarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $numero_orden,$serie_documento, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $nombre, $tcambio, $factura, $comentario, $m_credito, $glosa,$fecha, $fentrega, $almacen, $valormoneda, $formapago1, $formapago2, $perc, $statusFlete, $Fe_Flete, $ID_Motivo_Traslado, $No_Placa, $No_Licencia, $No_Certificado_Inscripcion, $ID_Transportista_Proveedor, $No_Transportista_Proveedor) {
		
		$checksi=" ";
		$checkno=" ";
		if ($m_credito=='S')
			$checksi="checked";
		else
			$checkno="checked";

		$form = new Form('','editar', FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ORDENCOMPRA"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_submit("submitButton\" style=\"visibility:hidden;\" id=\"submitButton", "submitButton", '', '', 20));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action\" id=\"action", "actualizaAgregar"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='1' width=100%>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>NUMERO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold; font-size: 14px'>&nbsp;".$numero_orden));
		$form->addElement(FORM_GROUP_MAIN, new form_element_hidden('numero', $numero_orden));
		$form->addElement(FORM_GROUP_MAIN, new form_element_hidden('serie', $serie_documento));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>FECHA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "fecha:", $fecha, '', 10, 12,'', array("readonly")));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'editar.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));// para que no se pueda actualizar la fecha
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>'));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>ALMACEN</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "ALMACEN :", $almacen, $almacenes,espacios(6),'',array("disabled")));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>PROVEEDOR</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td class='form_texto'>"));
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'proveedor',$proveedor, '', '', 15, 15, true));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('."'../maestros/ayuda/lista_ayuda.php','editar.proveedor','editar.nombre','proveedores'".')">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<br/>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'nombre',$nombre, '', '', 50, 15, true));


		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>MONEDA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("moneda", "MONEDA :", $valormoneda, $monedas, espacios(6),'',$habilitado3));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>T.CAMBIO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tcambio', $tcambio, '', '', 15, 15, $habilitado1));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>CREDITO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='radio' name='m_credito' value='S' ".$habilitado4." ".$checksi."  onChange=cambiarCombo('S');>SI"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='radio' name='m_credito' value='N' ".$habilitado4." ".$checkno."  onChange=cambiarCombo('N');>NO"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>FACTURA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'factura', $factura, '', '', 15, 7, $habilitado1));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>F.DE PAGO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));

		if ($m_credito == 'S'){
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago1','F.PAGO :',$formapago1,$fpago1,espacios(6), '',array('id=fpago1', 'style="display: block" ',$habilitado4)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago2','F.PAGO :',$formapago2,$fpago2,espacios(6), '',array('id=fpago2', 'style="display: none"',$habilitado4)));
		}else{
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago1','F.PAGO :',$formapago1,$fpago1,espacios(6), '',array('id=fpago1', 'style="display: none"',$habilitado4)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('fpago2','F.PAGO :',$formapago2,$fpago2,espacios(6), '',array('id=fpago2', 'style="display: block"',$habilitado4)));
		}
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>COMENTARIO</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'comentario', $comentario, '', '', 50, 20, $habilitado1));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>F.DE ENTREGA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fentrega", "fentrega:", $fentrega, '', 10, 12,'',$habilitado2));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'editar.fentrega'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));//para que no se pueda modificar la fech

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>GLOSA</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));

		if ($glosa != '') {
			$habilitado4 = $habilitado4.' checked '; 
		}

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='checkbox' name='chglosa' value='S' ".$habilitado4."  onclick='javascript:activaGlosa(this,document.getElementById(\"glosa\"),forms[0].glosa);'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		if ($glosa != '') {
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<div id='glosa' style='font-weight:bold'>DESCRIPCI&Oacute;N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;"));
		} else {
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<div id='glosa' style='display:none; font-weight:bold'>DESCRIPCI&Oacute;N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;"));
		}

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr valign='top'><td colspan='8' align='center' valign='top'>"));
		$nfilas= 0;
		foreach($articulos['articulos'] as $c => $d) {			
			$nfilas++;
		}		
			$vper=number_format($articulos['articulos'][$nfilas-1]['perce'],2,'.','.');
			$vper_i=number_format($articulos['articulos'][$nfilas-1]['perce_i'],2,'.','.');
			$xtotal=number_format($articulos['articulos'][$nfilas-1]['total'],2,'.','.');
			
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'glosa', $glosa, '', '', 110, 200, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</div>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		/* FLETE */
		if ($statusFlete == 'S')
			$checkedFlete = $checkedFlete . ' checked '; 

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th></th><th></th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>FLETE</th><th>:</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<input type='checkbox' name='chflete' value='S' " . $habilitado4 . " " . $checkedFlete . " onclick='javascript:activaFlete(this,document.getElementById(\"flete\"),forms[0].flete);'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr valign='top'><td colspan='8' align='center' valign='top'>"));

	    $arrMotivoTraslado = array(
	    	0 => "Venta",
	    	1 => "Venta Sujeta a Confirmacion del Comprador",
	    	2 => "Compra",
			3 => "Consignacion",
			4 => "Devolucion",
			5 => "Traslado entre Establecimentos de la Misma Empresa",
			6 => "Traslado de Bienes para Transformacion",
			7 => "Recojo de Bienes Transformados",
			8 => "Traslado por Emisor Itinerante de Comprobantes de Pago",
			9 => "Traslado Zona Primaria",
			10 => "Importacion",
			11 => "Exportacion",
			12 => "Venta con entrega a terceros",
			13 => "Otros"
		);

	    $html_option_motivo_traslado = '';
		foreach($arrMotivoTraslado as $key => $value){

			$html_option_motivo_traslado .= '<option value="' . $key . '">' . $value . '</option>';
		}

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("
			<div id='flete' style='font-weight:bold'>
				<table border='1'>
					<tr>
						<td>Fecha Traslado</td>
						<td>
							<input name='fe_flete' id='fe_flete' value='" . $Fe_Flete . "' " . $habilitado4 . " class='form_input' size='10' maxlength='12' type='text'>
							<a href='javascript:show_calendar(\"editar.fe_flete\");'> <img src='/sistemaweb/images/showcalendar.gif' border='0' align='top'/></a>
						</td>

						<td>Motivo Traslado</td>
						<td>
							<select id='cbo-MotivoTraslado' name='cbo-MotivoTraslado'>
								" . $html_option_motivo_traslado . "
							</select>
						</td>
					</tr>

					<tr>
						<td>Placa Vehículo</td>
						<td>
							<input name='no_placa' id='no_placa' value='" . $No_Placa . "' " . $habilitado4 . " autocomplete='off' class='form_input' size='20' maxlength='65' type='text'>
						</td>
						<td>Licencia</td>
						<td>
							<input name='no_licencia' id='no_licencia' value='" . $No_Licencia . "' " . $habilitado4 . " autocomplete='off' class='form_input' size='30' maxlength='65' type='text'>
						</td>
					</tr>

					<tr>
						<td>Certificado de Inscripción</td>
						<td>
							<input name='no_certificado_inscripcion' id='no_certificado_inscripcion' value='" . $No_Certificado_Inscripcion . "' " . $habilitado4 . " autocomplete='off' class='form_input' size='30' maxlength='65' type='text'>
						</td>
						<td>Proveedor Transportista</td>
						<td>
							<input type='hidden' id='txt-ID_Transportista_Proveedor' name='id_transportista_proveedor' value='" . $ID_Transportista_Proveedor . "' maxlength='15' size='15'>
							<input type='text' id='txt-No_Transportista_Proveedor' onkeyup='autocompleteBridge(4)' class='mayuscula' placeholder='Ingresar Código o Nombre' autocomplete='off' value='" . $No_Transportista_Proveedor . "' " . $habilitado4 . " maxlength='15' size='50'>
						</td>
					</tr>
				</table>
			</div>
		"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>&nbsp;</td></tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>"));



		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='1'>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th><th>PERCEPCION :</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan=7 style='font-weight:bold; font-size: 14px' valign=top>".""));
		
		if ($perc=='') {$checked=""; $block="none";} else {$checked="checked"; $block="block";}
                $form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border=0><tr><td><input type='checkbox' $checked name='percep2' value='v' style='display: none' onclick='javascript:activaGlosa2(this,document.getElementById(\"glosa2\"),forms[0].glosa2);'>
                				</td><td><div id='glosa2' style='display:$block; font-weight:bold' align=left>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext(" %:"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'xpercepcion', $perc, '', '', '', '', false, "onChange='percepcionx()';onkeyup='soloNumeros(this)' readonly"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</div></td></tr></table>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));



		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>CODIGO</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>DESCRIPCION</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>UNIDAD</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>CANTIDAD</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>COSTO UNITARIO</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>DESCUENTO</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>SUBTOTAL</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'codigo', '', '', '', 10, 15, false));
	 	//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda2('."'/sistemaweb/maestros/ayuda/lista_ayuda2.php','editar.codigo','editar.descripcion','editar.unidad','articulos'".')">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>"));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descripcion', '', '', '', 50, 15, true));
		$form->addElement(FORM_GROUP_MAIN,new f2element_freeTags('<input type="text" maxlength="15" size="50" class="form_input" value="" onkeyup="autocompleteBridge(3)" id="descripcion" name="descripcion">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'unidad', '', '', '', 8, 15, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'cantidad', '', '', '', 10, 15, false, "onChange='Subtotal()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'precio', '', '', '', 10, 15, false, "onChange='Subtotal()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descuento', '', '', '', 10, 15, false, "onChange='Subtotal()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'subtotal','0.00', '', '', 10, 15, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Insertar", '', "", 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));
		

		$numfilas= 0;
		foreach($articulos['articulos'] as $a => $b) {
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th><input type='radio' id='codart' name='codart' value='$b[0]' onclick='marcar(this, ".$numfilas.", \"editar\", \"posi\")'></th>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>".$b[0]."</th>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan='2'>".$b[1]."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>&nbsp;</td>"));			
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right'>".number_format($b[2],2,'.','.')."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right'>".number_format($b[3],4,'.','.')."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right'>".$b[4]."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right'>".$b[5]."</td>"));
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));
			$numfilas= $numfilas +1;
		}

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td colspan='7'></td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Eliminar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>"));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", $boton, '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Regresar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='center' style='font-weight: bold; color: blue'>TOTAL</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right' style='font-weight: bold; color: blue'>".number_format($articulos['articulos'][$numfilas-1]['descuento'],2,'.','.')."</td>"));
		//$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right' style='font-weight: bold; color: blue'>".number_format($articulos['articulos'][$numfilas-1]['total'],2,'.','.')."</td>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='right' style='font-weight: bold; color: blue'>"));
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'totalx',$xtotal, '', '', 10, 15, true,"style=border:0px;color:blue;text-align:right"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan=5 valign=top>"."</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th><font color='blue'>PERCEPCION</font></th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>&nbsp;</th>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td valign=top>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'valor_percep_i',$vper_i, '', '', 10, 15, true, "style=border:0px;color:blue;text-align:right", "onChange='percepcionx()';onkeyup='soloNumeros(this)'"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>")); 
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td valign=top>")); 
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'valor_percep',$vper, '', '', 10, 15, true, "style=border:0px;color:blue;text-align:right", "onChange='percepcionx()';onkeyup='soloNumeros(this)'"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));
		$form->addElement(FORM_GROUP_MAIN,new f2element_freeTags('<input type="hidden" value="edit-order" name="type-form"><h5>Actualización</h5>'));
		return $form->getForm();
	}

	function resultadosBusqueda($resultados, $iAlmacen, $dInicio, $dFinal) {

		$form = new Form('', "Resultado", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ORDENCOMPRA"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="txt-iAlmacen" id="txt-iAlmacen" value = "' . $iAlmacen . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="txt-dInicio" id="txt-dInicio" value = "' . $dInicio . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="txt-dFinal" id="txt-dFinal" value = "' . $dFinal . '"/>'));

		$result = '';
		$result .= '<table align="center" border="0">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera"><input type="checkbox" id="check-todo" onClick="Marcar_Desmacar_Todo(this);" /></th>';
		$result .= '<th class="grid_cabecera">Almacen</th>';
		$result .= '<th class="grid_cabecera"># Orden Compra</th>';
		$result .= '<th class="grid_cabecera">Documento</th>';
		$result .= '<th class="grid_cabecera">Fecha</th>';
		$result .= '<th class="grid_cabecera">Proveedor</th>';
		$result .= '<th class="grid_cabecera">Nombre</th>';
		$result .= '<th class="grid_cabecera">Ref. Factura</th>';
		$result .= '<th class="grid_cabecera">Moneda</th>';
		$result .= '<th class="grid_cabecera">T.Cambio</th>';
		$result .= '<th class="grid_cabecera">Importe</th>';
		$result .= '<th class="grid_cabecera">Estado</th>';
		$result .= '<th class="grid_cabecera"><input type="submit" name="action" value="Imprimir" style="width:80px"></th>';
		$result .= '<th class="grid_cabecera"></th>';
		//$result .= '<th class="grid_cabecera"><input type="submit" name="action" value="Enviar" style="width:80px"></th>';
		$result .= '</tr>';

		echo '<pre>';
		var_dump($resultados);
		echo '</pre>';

		for ($i = 0; $i < count($resultados); $i++) {
			$a = $resultados[$i];

			

      		$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

      		$id_orden_compa = trim($a['proveedor']) . trim($a['serie']) . trim($a['orden']);

			$result .= '<tr class="bgcolor ' . $color . '">';
				if(trim($a['estado']) == 'Pendientgge')
					$result .= '<td class="bgcolor ' . $color . '" align ="center"><input type="checkbox" class="check-hijo" id="' . $id_orden_compa . '" name="arrOrdenCompra[]" value="' . $id_orden_compa . '"/></td>';
				else
					$result .= '<td class="bgcolor ' . $color . '" align ="center">&nbsp;</td>';
				$result .= '<td align="center" >'. trim($a['almacen'])    .'</td>';
				$result .= '<td align="center" >'. trim($a['orden'])      .'</td>';
				$result .= '<td align="center" >'. trim($a['documento'])      .'</td>';
				$result .= '<td align="center" >'. trim($a['fecha'])      .'</td>';
				$result .= '<td align="center" >'. trim($a['proveedor'])  .'</td>';
				$result .= '<td align="center" >'. trim($a['nombre'])     .'</td>';
				$result .= '<td align="center" >'. trim($a['factura'])    .'</td>';
				$result .= '<td align="center" >'. trim($a['moneda'])     .'</td>';
				$result .= '<td align="center" >'. trim($a['tipocambio']) .'</td>';
				$result .= '<td align="right" >' . trim($a['importe'])    .'</td>';
				$result .= '<td align="center" >'. trim($a['estado'])     .'</td>';
				$result .= '<td align="center" ><input type="radio" name="radio_imprimir" value="'.trim($a['orden']).'/'.trim($a['serie']).'"></td>';
				$result .= '<td align="center">';
				$result .= '<input type="submit" name="action" id="update-'.trim($a['orden']).'-'.trim($a['serie']).'" value="Modificar" style="display: none;">';
				$result .= '<input type="radio" name="radio_modificar" id="radio-'.trim($a['orden']).'-'.trim($a['serie']).'" value="'.trim($a['orden']).'/'.trim($a['serie']).'" style="display: none;">';
				//$result .= '<input type="button" name="modificar" onclick="sendUpdate(\''.trim($a['orden']).'-'.trim($a['serie']).'-'.trim($fe_orden).'\');" value="Modificar">';
				$result .= '<input type="button" name="modificar" onclick="sendUpdate(\''.trim($a['orden']).'-'.trim($a['serie']).'\');" value="Modificar">';
				$result .= '</td>';
				//$result .= '<td align="center" ><input type="radio" name="radio_enviar" value="'.trim($a['orden']).'"></td>';
			$result .= '</tr>';
		}

			$result .= '<tr>';
				$result .= '<td class="bgcolor ' . $color . '" align ="center">&nbsp;</td>';
			$result .= '</tr>';
			/*
			$result .= '<tr>';
				$result .= '<td class="bgcolor ' . $color . '" align ="center">&nbsp;</td>';
				$result .= '<td class="bgcolor ' . $color . '" align ="center"><button type="button" id="btn-generar-inventario" name="btn-generar-inventario" onClick="generarInventario(this.form, ' . "'arrOrdenCompra[]'" . ')";>Facturar</button></td>';
			$result .= '</tr>';
			*/
		$result .= '</table>';

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext($result));
		return $form->getForm();
	}
}
?>
