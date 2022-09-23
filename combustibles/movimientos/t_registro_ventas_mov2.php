<?php

class RegistroVentasMOVTemplate extends Template {

	function titulo() {
		return '<div align="center"><h3 align="center" style="color:#336699;">Movimientos de Venta en Playa y Oficina</h3></div>';
	}

	function search_form($paginacion, $almacen, $dia1, $dia2, $tipodoc, $art_codigo, $art_cliente, $serie, $numero) {

		$tipos = Array(""=>"Todos", "N"=>"Nota de Credito", "F"=>"Factura", "B"=>"Boleta");

		$almacenes = RegistroVentasMOVModel::obtenerAlmacenes("");
		// $almacenes['TODOS'] = "Todos los Almacenes";
		
		if($almacen == "")
			$almacen = $_SESSION['almacen'];
			
		if($dia1 == "" or $dia2 == "") {
			$dia1 = date(d."/".m."/".Y);		
			$dia2 = date(d."/".m."/".Y);
		}			

		$form = new form2('', 'form_buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.MOVIMIENTOVENTAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));

		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4">Almacen : '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $almacen, $almacenes, "", array("onfocus" => "getFechaEmision();")));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Fecha Inicio: </td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("dia1", "", $dia1, '', 10, 12));
        //$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(' . "'Buscar.desde'" . ');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hasta: '));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("dia2", "", $dia2, '', 10, 12));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
        <tr>
            <td>
                <div id="label" style="display:block;">Art&iacute;culo:</td></div>            
            <td>
            <div id="label2" style="display:block;">
                <input type="text" id="txt-No_Producto" onkeyup="autocompleteBridge(0)" class="mayuscula" name="art_codigo2" placeholder="Ingresar código o nombre articulo" autocomplete="off" value="" maxlength="35" size="35">  
				<input type="text" readonly id="txt-Nu_Id_Producto" name="art_codigo" placeholder="Ingresar codigo producto" value="'.$art_codigo.'" maxlength="25" size="25">
        '));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
        <tr>
            <td>
                <div id="label" style="display:block;">Cliente:</td></div>            
            <td>
            <div id="label2" style="display:block;">
				<input type="text" id="txt-No_Proveedor" onkeyup="autocompleteBridge(1)" class="mayuscula" name="art_cliente2" placeholder="Ingresar código o nombre cliente" autocomplete="off" maxlength="35" size="35"/>  
				<input type="text" readonly id="txt-No_ProveedorRUC" name="art_cliente" placeholder="Ingresar codigo cliente" value="'.$art_cliente.'" maxlength="25" size="25">
        '));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			<tr>
			<td>
				<div id="label" style="display:block;">Serie:</td></div>            
			<td>
			<div id="label2" style="display:block;">
				<input type="text" id="txt-serie" name="serie" placeholder="Ingresar serie" value="'.$serie.'" maxlength="4" size="15">  
		'));
        
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			<tr>
			<td>
				<div id="label" style="display:block;">Numero:</td></div>            
			<td>
			<div id="label2" style="display:block;">
				<input type="text" id="txt-numero" name="numero" placeholder="Ingresar numero" value="'.$numero.'" maxlength="8" size="15">  
		'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Tipo Documento: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipo_doc", "", $tipodoc, $tipos, ""));
											
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center" colspan="4">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" alt="left"/> Excel</button>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}
 		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$almacen."','".$dia1."','".$dia2."','".$tipodoc."','".$art_codigo."','".$art_cliente."','".$serie."','".$numero."')")));
	   	$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$almacen."','".$dia1."','".$dia2."','".$tipodoc."','".$art_codigo."','".$art_cliente."','".$serie."','".$numero."')")));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value,'".$almacen."','".$dia1."','".$dia2."','".$tipodoc."','".$art_codigo."','".$art_cliente."','".$serie."','".$numero."')")));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$almacen."','".$dia1."','".$dia2."','".$tipodoc."','".$art_codigo."','".$art_cliente."','".$serie."','".$numero."')")));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$almacen."','".$dia1."','".$dia2."','".$tipodoc."','".$art_codigo."','".$art_cliente."','".$serie."','".$numero."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."','".$almacen."','".$dia1."','".$dia2."','".$tipodoc."','".$art_codigo."','".$art_cliente."','".$serie."','".$numero."')")));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("almacen").focus();
			}
		</script>'
		));

		return $form->getForm();
    }
	
	function reporte($resultados) {
		$res = array();
		$res = $resultados['datos'];
	
		$form = new form2('', 'form_listado', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.MOVIMIENTOVENTAS"));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table><tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">TIPO DOCUMENTO</th>'));				
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">Nº DOCUMENTO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">FECHA VENCIMIENTO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">FECHA EMISIÓN</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">MONEDA</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">PRODUCTO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">CANTIDAD</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">PRECIO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">BASE IMPONIBLE</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">IMPUESTO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">IMPORTE</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">PLACA</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">RUC</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">RAZÓN SOCIAL</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">STOCK</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">FECHA ANULACIÓN</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">NOTA CREDITO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">USUARIO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		
		for ($i = 0; $i < count($res); $i++) {	
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">' . htmlentities($res[$i]['td']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['usr']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['fecha_vencimiento']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['fecha_emision']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['moneda']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['art_codigo']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['cantidad']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['precio']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['base'], 4, '.', '')) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['igv'], 4, '.', '')) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['importe'], 4, '.', '')) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['placa']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['ruc']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['razsocial']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['stock']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['fanulacion']) . '</td>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['ntcred']) . '</td>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['usuario']) . '</td>'));						
		}
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="8" align="right" class="grid_detalle_total">TOTALES: </td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.$resultados['datos_totales']['totales']['totBase'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.$resultados['datos_totales']['totales']['totIgv'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.$resultados['datos_totales']['totales']['totImpo'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total"></td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total"></td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total"></td><td colspan="6" class="grid_detalle_total">&nbsp</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table></center>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><br><br>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center><table style="width:20%;">
			<thead>
				<tr>
					<th class="grid_cabecera" align="center" colspan="4">RESUMEN DE VENTAS</th>
				</tr>
				<tr>
					<th class="grid_cabecera" align="center" >&nbsp;&nbsp;PRODUCTO&nbsp;&nbsp;</th>
					<th class="grid_cabecera" align="center">&nbsp;&nbsp;CANTIDAD&nbsp;&nbsp;</th>
					<th class="grid_cabecera" align="center">&nbsp;&nbsp;IMPORTE&nbsp;&nbsp;</th>		    	
				</tr>
        	</thead>
			<tbody id="product">'));
            	
			$i = 0;
			$total_articulos = $resultados['datos_totales']['totales']['articulos'];
			foreach ($total_articulos as $key => $articulos) {
				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<tr class='$color'>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<td align='left'>" . $key . "</td>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">' . $articulos['cantidad']. '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . $articulos['importe'] . '</td>'));
				$i++;								
			}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			</tbody>
		</table></center>'));
		
		return $form->getForm();
    }

	function reporteExcel($resultados, $alma, $dia1, $dia2) {
		ob_start();

    	$res = array();
		$res = $resultados['resultado_1'];

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados');
		$worksheet1->set_column(0, 0, 20);
		$worksheet1->set_column(1, 1, 20);
		$worksheet1->set_column(2, 2, 20);
		$worksheet1->set_column(3, 3, 20);
		$worksheet1->set_column(4, 4, 20);
		$worksheet1->set_column(5, 5, 20);
		$worksheet1->set_column(6, 6, 20);
		$worksheet1->set_column(7, 7, 20);
		$worksheet1->set_column(8, 8, 20);
		$worksheet1->set_column(9, 9, 20);
		$worksheet1->set_column(10, 10, 20);
		$worksheet1->set_column(11, 11, 20);
		$worksheet1->set_column(12, 12, 20);
		$worksheet1->set_column(13, 13, 20);
		$worksheet1->set_column(14, 14, 20);
		$worksheet1->set_column(15, 15, 20);
		$worksheet1->set_column(16, 16, 20);
		$worksheet1->set_column(17, 17, 20);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "MOVIMIENTOS DE VENTAS",$formato0);
		$worksheet1->write_string(3, 0, "ALMACEN: ".$alma,$formato0);
		$worksheet1->write_string(4, 0, "FECHA DEL ".$dia1."  AL   ".$dia2 ,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 7;
		
		$worksheet1->write_string($a, 0, "TIPO DOCUMENTO",$formato2);
		$worksheet1->write_string($a, 1, "N DOCUMENTO",$formato2);
		$worksheet1->write_string($a, 2, "FECHA VEN",$formato2);
		$worksheet1->write_string($a, 3, "FECHA EMI",$formato2);	
		$worksheet1->write_string($a, 4, "MONEDA",$formato2);	
		$worksheet1->write_string($a, 5, "PRODUCTO",$formato2);
		$worksheet1->write_string($a, 6, "CANTIDAD",$formato2);
		$worksheet1->write_string($a, 7, "PRECIO",$formato2);
		$worksheet1->write_string($a, 8, "BASE IMP",$formato2);
		$worksheet1->write_string($a, 9, "IMPUESTO",$formato2);
		$worksheet1->write_string($a, 10, "IMPORTE",$formato2);	
		$worksheet1->write_string($a, 11, "PLACA",$formato2);	
		$worksheet1->write_string($a, 12, "RUC",$formato2);	
		$worksheet1->write_string($a, 13, "RAZON SOCIAL",$formato2);	
		$worksheet1->write_string($a, 14, "STOCK",$formato2);	
		$worksheet1->write_string($a, 15, "FECHA ANU",$formato2);	
		$worksheet1->write_string($a, 16, "NOTA CRED",$formato2);	
		$worksheet1->write_string($a, 17, "USUARIO",$formato2);			
		
		$a = 8;	

		for ($j=0; $j<count($res); $j++) {
				
			$worksheet1->write_string($a, 0, $res[$j]['td'],$formato5);
			$worksheet1->write_string($a, 1, $res[$j]['usr'],$formato5);
			$worksheet1->write_string($a, 2, $res[$j]['fecha_vencimiento'],$formato5);	
			$worksheet1->write_string($a, 3, $res[$j]['fecha_emision'],$formato5);
			$worksheet1->write_string($a, 4, $res[$j]['moneda'],$formato5);
			$worksheet1->write_string($a, 5, $res[$j]['art_codigo'],$formato5);
			$worksheet1->write_string($a, 6, $res[$j]['cantidad'],$formato5);	
			$worksheet1->write_string($a, 7, $res[$j]['precio'],$formato5);	
			$worksheet1->write_string($a, 8, $res[$j]['base'],$formato5);	
			$worksheet1->write_string($a, 9, $res[$j]['igv'],$formato5);	
			$worksheet1->write_string($a, 10, $res[$j]['importe'],$formato5);
			$worksheet1->write_string($a, 11, $res[$j]['placa'],$formato5);
			$worksheet1->write_string($a, 12, $res[$j]['ruc'],$formato5);
			$worksheet1->write_string($a, 13, $res[$j]['razsocial'],$formato5);
			$worksheet1->write_string($a, 14, $res[$j]['stock'],$formato5);
			$worksheet1->write_string($a, 15, $res[$j]['fanulacion'],$formato5);
			$worksheet1->write_string($a, 16, $res[$j]['ntcred'],$formato5);
			$worksheet1->write_string($a, 17, $res[$j]['usuario'],$formato5);							
			$a++;

		}
			
		$workbook->close();	

		$chrFileName = "MovimientoVentas";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");	
	}
}
