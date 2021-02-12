<?php
class OrdenCompraTemplate extends Template {
	function titulo() {
		return '<h2 align="center"><b>&Oacute;rden de Compra</b></h2>';
	}

	function TemplateReportePDF($reporte_array)
	{		
		$datos 		= array();
		$Cabecera 	= array( 
			    		"orden"  	=> "Orden",
			    		"almacen"  	=> "Almacen",
			    		"proveedor" => "Proveedor",
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
	    	foreach($reporte_array as $llave => $valores)
	    	{
			$reporte->nuevaFila($valores);
	    	}
	    	$reporte->Output("/sistemaweb/compras/movimientos/pdf/reporte_ordenes.pdf", "F");

		return '<center><iframe src="/sistemaweb/compras/movimientos/pdf/reporte_ordenes.pdf" style="width:0px; height:0px;" frameborder="0"></iframe></center>';			
	  }

	function TemplateReportePDFPersonal($recordord,$recordpro,$recordalmac,$recordalmac2,$recordalmac3,$ordenes,$monedas,$fpagos,$importante)
	{	
		global $usuario;	
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
		$reporte->definirCabeceraSize(6, "L", "courier,B,15", "".trim($recordalmac2['razsocial']));
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
		foreach($ordenes as $llave => $valor)
		{
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
		//$reporte->Lnew();
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
		while ($datos[$i]!= ''){
			if (strlen($datos[$i]) <= 100){
				$reporte->cell(10,7,$datos[$i],0,0,'L');
				$reporte->Ln();
			}else {$reporte->Ln();
				$reporte->Multicell(570,5,$datos[$i],0,'J');
			}
			$i = $i + 1;
		}
		//FIN : Imprimir Valores del Pie de página

		$reporte->Output("/sistemaweb/compras/movimientos/pdf/OrdenCompra_".trim($recordord['com_cab_numorden']).trim($recordord['num_seriedocumento']).".pdf", "F");
		//return '<center><iframe src="/sistemaweb/compras/movimientos/pdf/OrdenCompra_'.trim($recordord["com_cab_numorden"]).'.pdf" style="width:0px; height:0px;" frameborder="0"></iframe></center>';		
		return '<script> window.open("/sistemaweb/compras/movimientos/pdf/OrdenCompra_'.trim($recordord["com_cab_numorden"]).trim($recordord['num_seriedocumento']).'.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';

	  }

	function formSearch($almacenes) {
		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ORDENCOMPRA"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='1' style='width:1030px;'>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><th colspan='6'>CONSULTAR POR RANGO DE FECHAS </th></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th style='width:100px;'>DESDE :</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "fecha:", date("d/m/Y"), '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Agregar.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td><th>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("HASTA :</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "fecha2:", date("d/m/Y"), '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Agregar.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Estado:"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_checkbox2("Pendiente","estado_pendiente","0","","","",true));
		$form->addElement(FORM_GROUP_MAIN, new form_element_checkbox2("Inventario","estado_inventario","0","","","",true));
		$form->addElement(FORM_GROUP_MAIN, new form_element_checkbox2("Procesando","estado_procesando","0","","","",true));
		$form->addElement(FORM_GROUP_MAIN, new form_element_checkbox2("Facturado","estado_facturado","0","","","",true));
		$form->addElement(FORM_GROUP_MAIN, new form_element_checkbox2("Cerrado","estado_cerrado","0","</td><th rowspan='3'>","","",true));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Consultar", '</th></tr>', '', 20));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>ALMAC&Eacute;N :</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td colspan='4'>"));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "ALMACEN :", "TODAS", $almacenes, espacios(6)));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><th>&nbsp;</th><th>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("action", "Agregar", '</th>', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',''));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));

		/*$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<a href='/sistemaweb/compras/movimientos/OrdenesPDF.php?almacen=<?php echo urlencode($almacen);?>&fecha_del=<?php echo urlencode($fecha_del);?>&fecha_al=<?php echo urlencode($fecha_al);?>'>Reporte PDF</a>"));*/

		//$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<a href='#' onClick='javascript: document.location.href='/sistemaweb/compras/movimientos/OrdenesPDF.php?almacen='+document.getElementById(\"almacen\").value+'&fecha_del='+document.getElementById(\"fecha\").value.value+'&fecha_al='+document.getElementById(\"fecha2\").value;'>Reporte PDF</a>"));
		
		//$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<a href='#' onClick='javascript: alert(document.getElementById(\"almacen\").value);alert(document.getElementById(\"fecha\").value);alert(document.getElementById(\"fecha2\").value);'>Reporte2</a>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>&nbsp;</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table><br/>"));

		return $form->getForm();
	}

	/*function formImportar() {
		$form = new Form('', "Agregar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.SOBRANTESFALTANTESTRABAJADOR"));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "doImportar"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table style='width:400px;'><tr><td style='width:50%;text-align:right;'>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Fecha:</td><td style='text-align:left;'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha", date("d/m/Y"), '<a href="javascript:show_calendar(\'Agregar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div></td></tr><tr><td style="text-align:right;">', '', 10, 10, true));
		$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submitbutton", "Importar", '', '', 20));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));
		return $form->getForm();
	}*/

	function formAgregar($numero_orden,$serie_documento, $almacenes, $estados, $monedas, $fpago1, $fpago2) {

		$checksi=" ";
		$checkno=" ";
		if ($m_credito=='S') 	{$checksi="checked"; }  
		else 			{$checkno="checked"; }

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
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'proveedor','', '', '', 15, 15, false));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('."'../maestros/ayuda/lista_ayuda.php','editar.proveedor','editar.nombre','proveedores'".')">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<br/>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'nombre','', '', '', 50, 15, true));
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
	 	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda2('."'/sistemaweb/maestros/ayuda/lista_ayuda2.php','editar.codigo','editar.descripcion','editar.unidad','articulos'".')">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descripcion', '', '', '', 50, 15, true));
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

	function formAgregarArticulo($boton,$habilitado1,$habilitado2,$habilitado3,$habilitado4,$articulos, $numero_orden,$serie_documento, $almacenes, $estados, $monedas, $fpago1, $fpago2, $proveedor, $nombre, $tcambio, $factura, $comentario, $m_credito, $glosa,$fecha, $fentrega, $almacen, $valormoneda, $formapago1, $formapago2, $perc) {
		
		$checksi=" ";
		$checkno=" ";
		if ($m_credito=='S') 	{$checksi="checked"; }  
		else 			{$checkno="checked"; }

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
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('."'../maestros/ayuda/lista_ayuda.php','editar.proveedor','editar.nombre','proveedores'".')">'));
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
	 	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda2('."'/sistemaweb/maestros/ayuda/lista_ayuda2.php','editar.codigo','editar.descripcion','editar.unidad','articulos'".')">'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th colspan='2'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descripcion', '', '', '', 50, 15, true));
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

	function resultadosBusqueda($resultados,$almacenes) {

		$form = new Form('', "Resultado", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ORDENCOMPRA"));
		$result = '';
		$result .= '<table align="center" border="1"  style="width:1030px">';
		$result .= '<tr>';
		$result .= '<th>Numero</th>';
		$result .= '<th>Almacen</th>';
		$result .= '<th>Proveedor</th>';
		$result .= '<th>Nombre</th>';
		$result .= '<th>Fecha</th>';
		$result .= '<th>Moneda</th>';
		$result .= '<th>T.Cambio</th>';
		$result .= '<th>Importe</th>';
		$result .= '<th>Estado</th>';
		$result .= '<th>Factura</th>';
		$result .= '<th><input type="submit" name="action" value="Imprimir" style="width:80px"></th>';
		//$result .= '<th><input type="submit" name="action" value="Enviar" style="width:80px"></th>';
		$result .= '</tr>';
		for ($i = 0; $i < count($resultados); $i++) {
			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td align="center" >'. trim($a['orden'])      .'</td>';
			$result .= '<td align="center" >'. trim($a['almacen'])    .'</td>';
			$result .= '<td align="center" >'. trim($a['proveedor'])  .'</td>';
			$result .= '<td align="center" >'. trim($a['nombre'])     .'</td>';
			$result .= '<td align="center" >'. trim($a['fecha'])      .'</td>';
			$result .= '<td align="center" >'. trim($a['moneda'])     .'</td>';
			$result .= '<td align="center" >'. trim($a['tipocambio']) .'</td>';
			$result .= '<td align="right" >' . trim($a['importe'])    .'</td>';
			$result .= '<td align="center" >'. trim($a['estado'])     .'</td>';
			$result .= '<td align="center" >'. trim($a['factura'])    .'</td>';
			$result .= '<td align="center" ><input type="radio" name="radio_imprimir" value="'.trim($a['orden']).'/'.trim($a['serie']).'"></td>';
			//$result .= '<td align="center" ><input type="radio" name="radio_enviar" value="'.trim($a['orden']).'"></td>';
			$result .= '</tr>';
		}
		$result .= '</table>';

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext($result));
		//return $result;
		return $form->getForm();
	}
}
?>
