<?php

class RegistroComprasTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Registro de Compras</b></h2>';
	}

	function formSearch($fecha, $fecha2, $paginacion, $almacen, $proveedor, $documento, $tdocu, $moneda, $type_ple = ""){

		$estaciones 			= RegistroComprasModel::obtieneListaEstaciones();
		$documentos 			= RegistroComprasModel::Documentos();
        $documentos["TODOS"] 	= "Todos";

		$monedas 			= array("01" => "Soles", "02" => "Dolares");
	    $monedas["TODOS"] 	= "Todos";

	    if($type_ple == 'RC'){
	    	$rc = 'checked';
	    	$rcd = '';
	    	$rcs = '';
	    }else if($type_ple == 'RCD'){
	    	$rc = '';
	    	$rcd = 'checked';
	    	$rcs = '';
	    }else if($type_ple == 'RCS'){
			$rc = '';
			$rcd = '';
	    	$rcs = 'checked';
	    }else {
	    	$rc = 'checked';
	    	$rcd = '';
	    	$rcs = '';
	    }

		$form = new form2('', 'Form', FORM_METHOD_POST, 'control.php', '', 'control');
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.REGISTROCOMPRAS'));
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'REGISTROS'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Estaciones: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('estacion', '', $almacen, $estaciones, espacios(3), array("onfocus" => "getFechaEmision();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Fecha inicial: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "", $fecha, '', 12, 10));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">Fecha final: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "", $fecha2, '', 12, 10));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Tipo Documento: <td>'));
       	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('tdocu', '', $tdocu, $documentos, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Proveedor: <td>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_text('proveedorb','', (empty($_REQUEST['proveedorb']) ? '' : $_REQUEST['proveedorb']), espacios(2), 13, 13, array("onkeyup"=>"this.value=this.value.toUpperCase();getRegistroProB(this.value);","tabindex"=>"1")));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_proveedor"  style="display:inline;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Documento: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("documento", "", $documento, '', 11, 11,array("onkeypress"=>"return validar(event,2);"), ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Tipo Moneda: <td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_combo('tmoneda', '', $moneda, $monedas, espacios(3)));

	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">PLE: '));
	    //$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left"><input type="radio" name="pletype" value="RC" checked>Registro de Compras'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left"><input type="radio" id="pletype[]" name="pletype" value="RC" '. $rc .'>Registro de Compras'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" id="pletype[]" name="pletype" value="RCD" '. $rcd .'>Registro de Compras No Domiciliado'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" id="pletype[]" name="pletype" value="RCS" '. $rcs .'>Registro de Compras Simplificado</td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><tr><td colspan="4" align="center"><br><button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Reporte"><img src="/sistemaweb/images/icono_pdf.gif" align="right" />PDF</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<A href="#" onClick="Excel();"><button><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel</button></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Libros"><img src="/sistemaweb/icons/gbook.png" align="right" />Libros Electronicos</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar" onClick="listar();"><img src="/sistemaweb/icons/gadd.png" align="right" />Nuevo Registro</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center"><br>'));

		//PAGINADOR

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
 
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

 		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$fecha."','".$fecha2."','".$almacen."','".$proveedor."','".$documento."','".$tdocu."','".$tmoneda."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$fecha."','".$fecha2."','".$almacen."','".$proveedor."','".$documento."','".$tdocu."','".$tmoneda."')")));
	    $form->addElement("GRUPO_PAGINA", new f2element_text('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
	    $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$fecha."','".$fecha2."','".$almacen."','".$proveedor."','".$documento."','".$tdocu."','".$tmoneda."')")));
	    $form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$fecha."','".$fecha2."','".$almacen."','".$proveedor."','".$documento."','".$tdocu."','".$tmoneda."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."','".$fecha."','".$fecha2."','".$almacen."','".$proveedor."','".$documento."','".$tdocu."','".$tmoneda."')")));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("estacion").focus();
			}
		</script>'
		));

		return $form->getForm();

	}

	function resultadosBusqueda($resultados, $fecha, $fecha2, $rxp, $pagina, $estacion, $pro, $doc, $tdocu, $tmoneda) {

		$result = "";

		$result .= '<div class="form" align="center"><form name="Fec"><table align="center" border="0">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>CORRELATIVO</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>FECHA EMISION</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>DOCUMENTO</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>PROVEEDOR</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>RUBRO</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>MONEDA</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>T.C</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>B.I</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>IMPUESTO</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>INAFECTO</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>PERCEPCION</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>TOTAL S/</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>SALDO S/</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>TOTAL $</th>';
		$result .= '<th class="grid_cabecera"><p style="font-size:1.1em; color:white;"><b>SALDO $</th>';
		$result .= '</tr>';


		//SUMA DE TOTALES
		$nimponible 	= 0;
		$nimpuesto 	= 0;
		$ninafecto 	= 0;
		$nperce 	= 0;
		$ntotal 	= 0;
		$nsaldo 	= 0;
		$ntotald	= 0;
		$nsaldod	= 0;

		//VARIABLES MOSTRAR
		$imponible 	= 0;
		$impuesto 	= 0;
		$inafecto 	= 0;
		$perce 		= 0;
		$total 		= 0;
		$saldo 		= 0;
		$totald 	= 0;
		$saldod 	= 0;


		for ($i = 0; $i < count($resultados); $i++) {

			$color	= ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

			$a	= $resultados[$i];

			$nimponible 	= $a['imponible'];
			$nimpuesto 		= $a['impuesto'];
			$ninafecto 		= $a['inafecto'];
			$nperce 		= $a['perce'];
			$ntotal 		= ($a['imponible'] + $a['impuesto'] + $a['inafecto']);
			$nsaldo 		= $a['saldo'];

			//DOLARES
			if($a['totald'] > 0)
				$ntotald	= ($a['imponible'] + $a['impuesto'] + $a['inafecto']);
			else
				$ntotald	= $a['totald'];
			
			$nsaldod	= $a['saldod'];

			$result .= '<tr bgcolor="">';

			if($a['doctype'] == '20' && empty($a['validanc']))
				$colorletter = 'red';
			else
				$colorletter = 'black';

			$viewnumerator	= str_pad($a['corre'], 10, "0", STR_PAD_LEFT);

			$result .= '<td style="color:'.$colorletter.';" class="'.$color.'" align ="center">' . htmlentities($viewnumerator) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="center">' . htmlentities($a['femision']) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="left">' . htmlentities($a['documento']) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="left">' . htmlentities($a['proveedor']) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="center">' . htmlentities($a['rubro']) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' . htmlentities($a['moneda']) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' . htmlentities(number_format($a['tc'], 3, '.', ',')) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' .  htmlentities(number_format($nimponible, 2, '.', ',')) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' .  htmlentities(number_format($nimpuesto, 2, '.', ',')) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' .  htmlentities(number_format($ninafecto, 2, '.', ',')) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' . htmlentities(number_format($a['perce'], 2, '.', ',')) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' . htmlentities(number_format($ntotal, 2, '.', ',')) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' . htmlentities(number_format($nsaldo, 2, '.', ',')) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' . htmlentities(number_format($ntotald, 2, '.', ',')) . '</td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" align ="right">' . htmlentities(number_format($nsaldod, 2, '.', ',')) . '</td>';


			/* SUMA DE CADA IMPORTE PARA TOTALIZAR */

			$imponible 	= $imponible + $nimponible;
			$impuesto 	= $impuesto + $nimpuesto;
			$inafecto 	= $inafecto + $ninafecto;
			$perce 		= $perce + $nperce;
			$total 		= $total + $ntotal;
			$saldo 		= $saldo + $nsaldo;

			$totald		= $totald + $ntotald;
			$saldod		= $saldod + $nsaldod;

			$eliminar = $a['eliminar'];

			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=Imprimir&documento='.($a['eliminar']).'" target="control"><img src="/sistemaweb/icons/imprimir.gif" align="right"/></A></td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=Update&doctype='.($a['doctype']).'&estacion='.$estacion.'&pro='.$pro.'&doc='.$doc.'&tdocu='.$tdocu.'&documento='.($a['eliminar']).'&fecha='.$fecha.'&fecha2='.$fecha2.'&rxp='.$rxp.'&pagina='.$pagina.'&tmoneda='.$tmoneda.'&nu_almacen='.($a['nu_almacen']).'&femision='.($a['femision']).'" target="control"><img src="/sistemaweb/icons/gedit.png" align="right"/></A></td>';
			$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="javascript:confirmarLink(\'Deseas Eliminar Documento: '. htmlentities($a['documento']).'?\',\'control.php?rqst=MOVIMIENTOS.REGISTROCOMPRAS&task=REGISTROS&action=Eliminar&fperiodo='.($a['fregistro']).'&correlativo='.($a['corre']).'&nu_almacen='.($a['nu_almacen']).'&femision='.($a['femision']).'&estacion='.$estacion.'&pro='.$pro.'&doc='.$doc.'&tdocu='.$tdocu.'&documento='.($a['eliminar']).'&fecha='.$fecha.'&fecha2='.$fecha2.'&tmoneda='.$tmoneda.'\', \'control\')"><img src="/sistemaweb/icons/gdelete.png" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '</tr>';
		}

		$result .= '<tr>';
		$result .= '<th class="grid_cabecera" colspan="7" align ="right"><p style="font-size:1.1em; color:white;"><b>TOTALES: </td>';
		$result .= '<th class="grid_cabecera" align ="right"><p style="font-size:1.1em; color:white;"><b>' . htmlentities(number_format($imponible, 2, '.', ',')) . '</td>';
		$result .= '<th class="grid_cabecera" align ="right"><p style="font-size:1.1em; color:white;"><b>' . htmlentities(number_format($impuesto, 2, '.', ',')) . '</td>';
		$result .= '<th class="grid_cabecera" align ="right"><p style="font-size:1.1em; color:white;"><b>' . htmlentities(number_format($inafecto, 2, '.', ',')) . '</td>';
		$result .= '<th class="grid_cabecera" align ="right"><p style="font-size:1.1em; color:white;"><b>' . htmlentities(number_format($perce, 2, '.', ',')) . '</td>';
		$result .= '<th class="grid_cabecera" align ="right"><p style="font-size:1.1em; color:white;"><b>' . htmlentities(number_format($total, 2, '.', ',')) . '</td>';
		$result .= '<th class="grid_cabecera" align ="right"><p style="font-size:1.1em; color:white;"><b>' . htmlentities(number_format($saldo, 2, '.', ',')) . '</td>';
		$result .= '<th class="grid_cabecera" align ="right"><p style="font-size:1.1em; color:white;"><b>' . htmlentities(number_format($totald, 2, '.', ',')) . '</td>';
		$result .= '<th class="grid_cabecera" align ="right"><p style="font-size:1.1em; color:white;"><b>' . htmlentities(number_format($saldod, 2, '.', ',')) . '</td>';
		$result .= '</tr>';

		$result .= '</table></form></div>';

		return $result;
	}

	function FormUpdate($documento, $fecha, $fecha2, $rxp, $pagina, $fila, $almacen, $pro, $doc, $tdocu, $doctype, $tmoneda) {

		$currency	= RegistroComprasModel::TipoMoneda();
		$tiposref	= RegistroComprasModel::DocumentosRef();

		$form = new form2('',"Actualizar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.REGISTROCOMPRAS"));
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'REGISTROS'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border = "0" align="center">'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="rxp" id="rxp" value = "' . $rxp . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="pagina" id="pagina" value = "' . $pagina . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="fecha" id="fecha" value = "' . $fecha . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="fecha2" id="fecha2" value = "' . $fecha2 . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="almacen" id="almacen" value = "' . $almacen . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="pro" id="pro" value = "' . $pro . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="doc" id="doc" value = "' . $doc . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="tdocu" id="tdocu" value = "' . $tdocu . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="documento" id="documento" value = "' . $documento . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="tmoneda" id="tmoneda" value = "' . $tmoneda . '"/>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Fecha Emision:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'fregistro', $fila['fregistro'], "", "",10,10, array('readonly')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Actualizar.fregistro'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Fecha Registro:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'fperiodo', $fila['fperiodo'], "", "",10,10, array('readonly')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Actualizar.fperiodo'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Documento:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'descripcion', $fila['documento'], "", "",30,30, array('readonly')));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Proveedor:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'proveedor', $fila['proveedor'], "", "",60,60, array('readonly')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		if($doctype == '20') {

			$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="right">Tipo Referencia: <td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tiporef", "", $fila['tiporef'], $tiposref, ""));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Serie Referencia:<td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("serieref", "", $fila['serieref'], "", 4, 4, array("onkeypress"=>"return validar(event,1);","tabindex"=>"3","onblur"=>"return cceros2(document.Actualizar.serieref,4,'serieref');"), ''));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Numero Referencia:<td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("docuref", "", $fila['docuref'], '', 8, 8,array("onkeypress"=>"return validar(event,2);","tabindex"=>"2","onblur"=>"return cceros2(document.Actualizar.docuref,8,'docuref');"), ''));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Fecha Referencia:<td>'));
			$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'freferencia', $fila['freferencia'], "", "",10,12, array('')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Actualizar.freferencia'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));

		}
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Moneda:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("moneda", "", "", $currency, ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Base Imponible:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'imponible', $fila['imponible'], "", "",15,15, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Impuesto:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'impuesto', $fila['impuesto'], "", "",15,15, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Total:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'total', $fila['total'], "", "",15,15, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Percepcion:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'perce', $fila['perce'], "", "",15,15, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right" >Inafecto IGV:<td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'inafecto', $fila['inafecto'], "", "",15,15, ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/gedit.png" align="right" />Actualizar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button name="action" type="submit" value="Regresar"><img src="/sistemaweb/icons/greturn.png" align="right" />Regresar</button></td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</table>'));

		return $form->getForm();

	}


	function formAgregar($rxp, $pagina, $fecha, $fecha2, $almacen, $proveedor, $documento, $tdocu, $tmoneda) {

		$estaciones		= RegistroComprasModel::obtieneListaEstaciones();
		$currency		= RegistroComprasModel::TipoMoneda();
		$rubros			= RegistroComprasModel::Rubros();
		$rubros[''] 	= "Seleccionar...";
		$tipos			= RegistroComprasModel::Documentos();
		$tipos[''] 		= "Seleccionar...";
		$tiposref		= RegistroComprasModel::DocumentosRef();
		$tiposref[''] 	= "Seleccionar...";
		$fregistro		= RegistroComprasModel::FechaSistema();

		$correlativo	= RegistroComprasModel::BuscarCorrelativo($fregistro['fecha']);
		$viewnumerator	= str_pad($correlativo, 10, "0", STR_PAD_LEFT);
		$tc 			= RegistroComprasModel::TipoCambio($fregistro['fecha']);
		$freferencia 	= date("d/m/Y");
		$contabilizar 	= array( "N" => "NO", "S" => "SI");

		$form = new form2('',"Agregar", FORM_METHOD_POST, "control.php", '', 'control');

		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.REGISTROCOMPRAS"));
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'REGISTROS'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="rxp" id="rxp" value = "' . $rxp . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="pagina" id="pagina" value = "' . $pagina . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="fecha" id="fecha" value = "' . $fecha . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="fecha2" id="fecha2" value = "' . $fecha2 . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="almacen" id="almacen" value = "' . $almacen . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="pro" id="pro" value = "' . $pro . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="doc" id="doc" value = "' . $doc . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="tdocu" id="tdocu" value = "' . $tdocu . '"/>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input type="hidden" name="tmoneda" id="tmoneda" value = "' . $tmoneda . '"/>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<table border='0'>"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align='right'><p style='font-size:1.2em; color:black;'><b>Correlativo: </td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'><p style='font-size:1.2em; color:black;'><b> ".$viewnumerator."</td>"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align='right'>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Sucursal:</td><td align='left'>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "", $estaciones, '</td>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='right'>Tipo: <td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipo", "", "", $tipos, "", "", array('onChange="Mostrar(this.value);"'), ""));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td id='tiporef' style='display:none;' align='right'>Tipo Referencia: <td id='tiporef2' style='display:none;'>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tiporef", "", "", $tiposref, ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Proveedor:<td>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_text('proveedor','', $_REQUEST['proveedor'], '', 11, 11, array("onfocus" => "myFunction()", "onkeyup"=>"this.value=this.value.toUpperCase();getRegistroPro(this.value);","tabindex"=>"1")));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="desc_proveedor"  style="display:inline;"></div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="right">Serie: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("serie", "", "$serie", "", 4, 4,array("onkeyup"=>"return serie();","onkeypress"=>"return validar(event,1);","tabindex"=>"3","onblur"=>"return cceros(document.Agregar.serie,4,'serie');"), ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td id="serieref" style="display:none" align="right">Serie Referencia: <td id="serieref2" style="display:none">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("serieref", "", "", "", 4, 4, array("onkeyup"=>"return serie();","onkeypress"=>"return validar(event,1);","tabindex"=>"3","onblur"=>"return cceros(document.Agregar.serieref,4,'serieref');"), ''));//cai
		//$form->addElement(FORM_GROUP_MAIN, new f2element_text("serieref", "", "", "", 4, 4, array("onkeypress"=>"return validar(event,2);","tabindex"=>"3","onblur"=>"return cceros(document.Agregar.serieref,4,'serieref');"), ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		</tr><tr><td align="right">Fecha Periodo:</td><td align="left">
		<input type="text" onfocus="myFunction()" name="fperiodo" id="fperiodo" maxlength="10" size="12" class="fecha_formato" value="'.$fregistro['fecha'].'" />'
		));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Numero: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("documento", "", "", "", 10, 10,array("onkeyup"=>"return documento();","onkeypress"=>"return validar(event,2);","tabindex"=>"4","onblur"=>"return cceros(document.Agregar.documento,8,'documento');"), ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td id="documentoref" style="display:none;" align="right">Documento Referencia: <td id="documentoref2" style="display:none;">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("documentoref", "", "", '', 10, 10,array("onkeypress"=>"return validar(event,2);","tabindex"=>"2","onblur"=>"return cceros(document.Agregar.documentoref,8,'documentoref');"), ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		</tr><tr><td align="right">Fecha Emision:</td><td align="left">
		<input type="text" onfocus="myFunction()" name="femision" id="femision" maxlength="10" size="12" class="fecha_formato" value="'.$fecha.'" />
		<span id="error" style="color:red;"></span>'
		));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Dias Vencimiento: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("dvec", "", "", '', 2, 2,array("onkeyup"=>"return CalcularFecha(this.value);","onkeypress"=>"return validar(event,2);","tabindex"=>"5"), ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align='right'>Rubro: <td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("rubro", "", "", $rubros, "","",array('onChange="Rubro(this.value, \''.$fecha.'\', \''.$fecha2.'\', \''.$rxp.'\', \''.$pagina.'\', \''.$almacen.'\', \''.$pro.'\', \''.$doc.'\', \''.$tdocu.'\');"'),""));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Vencimiento: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fvencimiento", "", $hoy, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Agregar.fvencimiento'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='right'>Moneda: <td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("moneda", "", "", $currency, "", array("class" => "tiposmoneda")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Tipo Cambio: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tc','', $tc, '', 5, 5,array("onkeypress"=>"return validar(event,3);", "class" => "valormoneda"), ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Glosa: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('txt_glosa','', $txt_glosa, '', 40, 40, array("onkeypress"=>"return validar(event, 1);"), ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan='6' align='center'><br>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="Almacen" name="action" type="submit" value="Almacen"><img src="/sistemaweb/icons/gadd.png" align="right" />Ingresos Almacen</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<button id="Regresar" name="action" type="submit" value="Regresar"><img src="/sistemaweb/icons/greturn.png" align="right" />Regresar</button></td>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</form></table>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
										<script>
											window.onload = function() {
												parent.document.getElementById("proveedor").focus();
											}
										</script>
									'));

		return $form->getForm();
	}

	function resultadosComprasDevolucion($resultados, $estacion, $femision, $proveedor, $rubro, $tipo, $serie, $documento, $dvec, $fvencimiento, $tc, $moneda, $fecha, $fecha2, $rxp, $pagina, $almacen, $pro, $doc, $tdocu, $tmoneda){

		$result ='';

		$result .= '<form><table align="center" border="0">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera"></th>';
		$result .= '<th class="grid_cabecera">TIPO</th>';
		$result .= '<th class="grid_cabecera">FECHA</th>';
		$result .= '<th class="grid_cabecera">MOVIMIENTO</th>';
		$result .= '<th class="grid_cabecera">O/C</th>';
		$result .= '<th class="grid_cabecera">PRODUCTO</th>';
		$result .= '<th class="grid_cabecera">CANTIDAD</th>';
		$result .= '<th class="grid_cabecera">COSTO U.</th>';
		$result .= '<th class="grid_cabecera">COSTO TOTAL</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];
			$c = "check";
			$cantidad = $a['cantidad'];
			$total = $a['total'];
			$tipo = $a['tipo'];
			$producto = $a['producto'];
			$mov = $a['id'];

			$result .= '<td class="'.$color.'" align ="center"><input type="checkbox" id="'.$c.$i.'" name="t[]" value="'.$mov.'" onClick="hallarSubTotal(\'' . $c.$i . '\', \'' . $cantidad . '\', \'' . $total . '\', \'' . $tipo . '\', \'' . $fecha . '\', \'' . $fecha2 . '\', this.form, '."'t[]'".', \'' . $rxp . '\', \'' . $pagina . '\', \'' . $almacen . '\', \'' . $pro . '\', \'' . $doc . '\', \'' . $tdocu . '\', \'' . $tmoneda . '\')"</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['tipo']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['fecha']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['movimiento']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['compra']) . '</td>';
			$result .= '<td class="'.$color.'" align ="left">' . htmlentities($a['producto']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['cantidad']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' .  htmlentities(number_format($a['costo'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities(number_format($a['total'], 2, '.', ',')) . '</td>';
			$result .= '</tr>';
		}

		$cantidad= 0;
		$subtotal= 0;
		$base= 0;

		$result .='<tr>';
		$result .='<td colspan="6" align="right">Total Seleccionado: </td>';
		$result .='<input type="hidden" name="actualcan" id="actualcan" />';
		$result .='<input type="hidden" name="actualtot" id="actualtot" />';
		$result .='<td align="right"><input style="text-align:right" type="text" name="cantidad" id="cantidad" readonly="true" maxlength="15" size="15" value="'.$cantidad.'" /></td>';
		$result .='<td></td>';
		$result .='<td align="right"><input style="text-align:right" name="subtotal" id="subtotal" readonly="true" maxlength="15" size="15" value="'.$subtotal.'" /></td>';
    	$result .='</table></form>';
		
		/* MOSTRAR LOS TOTALES */
		$result .='<table><tr><td><div id="Totales" align="center" >'.RegistroComprasTemplate::verTotales($base, $fecha, $fecha2, $rubro, $id, $rxp, $pagina, $almacen, $pro, $doc, $tdocu, $tmoneda).'</div>';

		return $result;
	}

	function verTotales($base, $fecha, $fecha2, $rubro, $id, $rxp, $pagina, $almacen, $pro, $doc, $tdocu, $tmoneda) {

		$limit	= RegistroComprasModel::Limite();
		$data	= RegistroComprasModel::Igv();
		$rowigv = $data['igv'];

		$impuesto 	= ($base * $rowigv)-$base;
		$total 		= ($base + $impuesto);
		
		$rubros = RegistroComprasModel::BuscarRubros($rubro);

    		$totales ='<table border="0" align="center">';

		$totales .='<input type="hidden" name="fecha" id="fecha" value = "' . $fecha . '"/>';
		$totales .='<input type="hidden" name="fecha2" id="fecha2" value = "' . $fecha2 . '"/>';
		$totales .='<input type="hidden" name="codalmacen" id="codalmacen" value = "' . $almacen . '"/>';
		$totales .='<input type="hidden" name="pro" id="pro" value = "' . $pro . '"/>';
		$totales .='<input type="hidden" name="doc" id="doc" value = "' . $doc . '"/>';
		$totales .='<input type="hidden" name="tdocu" id="tdocu" value = "' . $tdocu . '"/>';
		$totales .='<input type="hidden" name="rxp" id="rxp" value = "' . $rxp . '"/>';
		$totales .='<input type="hidden" name="pagina" id="pagina" value = "' . $pagina . '"/>';
		$totales .='<input type="hidden" name="tmoneda" id="tmoneda" value = "' . $tmoneda . '"/>';

		$totales .='<input type="hidden" name="rubros" id="rubros" value = "' . $rubros['rubro'] . '"/>';
//		$totales .='<input type="hidden" name="correlativo" id="correlativo" value = "' . $correlativo . '"/>';

		$totales .='<tr><td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='Inafecto IGV:
						<select OnChange="Inafecto(this.value);">
							<option value="N">NO</option>
							<option value="S">SI</option>
						</select>';

		$totales .='<tr><td colspan="6" align="right"><br>';

		$totales .='<tr>';
		$totales .='<td colspan="6" align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';

		$totales .='Base Imponible:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<input style="text-align:right" type="text" name="base" id="base" maxlength="15" size="15" value="'.number_format($base, 2, '.', '').'" onblur="Totales(this.value);CalcularTotales(this.value,\'' . $rowigv . '\')" onkeypress="return validar(event,3)" />';
		$totales .='<input type="hidden" name="vali" id="vali" value="'.number_format($base, 2, '.', '').'"/>';
		$totales .='<input type="hidden" name="limit" id="limit" value="'.number_format($limit['limite'], 2, '.', '').'"/>';
		$totales .='&nbsp;&nbsp;&nbsp;&nbspImpuesto:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='<input style="text-align:right" type="text" name="impuesto" id="impuesto" maxlength="15" size="15" value="'.number_format($impuesto, 2, '.', '').'" onkeypress="return validar(event,3)" />';
		$totales .='&nbsp;&nbsp;&nbsp;&nbspImporte Total:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';

		$totales .='<input style="text-align:right" type="text" name="total" id="total" maxlength="15" size="15" value="'.number_format($total, 2, '.', '').'" onkeypress="return validar(event,3)" />';
		$totales .='&nbsp;&nbsp;&nbsp;&nbspPercepcion:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';

		$totales .='<input style="text-align:right" type="text" name="perce" id="perce" maxlength="15" size="15" value="'.number_format($perce, 2, '.', '').'" onkeypress="return validar(event,3)" />';

		$totales .='<tr><td id="celda2" style="display:none" colspan="13" align="right">';
		$totales .='&nbsp;&nbsp;&nbsp;&nbspInafecto IGV:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='<input style="text-align:right" type="text" name="inafecto" id="inafecto" maxlength="15" size="15" value="'.number_format($inafecto, 2, '.', ',').'" onkeypress="return validar(event,1)" />';

		$totales .='<tr><td colspan="13" align="right"><br>';
		$totales .='<tr><td colspan="13" align="right"><button name="action" type="submit" value="Totales" onClick=ir(\'' . $id . '\')><img src="/sistemaweb/icons/gadd.png" align="right" />Registrar Compra</button>';
		$totales .='&nbsp;&nbsp;<button name="action" type="submit" value="Regresar" onClick=regresar(\'' . $fecha . '\',\'' . $fecha2 . '\',\'' . $rxp . '\',\'' . $pagina . '\',\'' . $almacen . '\',\'' . $pro . '\',\'' . $doc . '\',\'' . $tdocu . '\');><img src="/sistemaweb/icons/greturn.png" align="right" />Regresar</button></td>';
		$totales .='</tr></tbody></table>';

    		return $totales;

  	}

	function verTotalesOtros($base, $fecha, $fecha2, $rubro, $rxp, $pagina, $almacen, $pro, $doc, $tdocu, $tmoneda) {

		$data	= RegistroComprasModel::Igv();

		$rowigv = $data['igv'];

		$rubros = RegistroComprasModel::BuscarRubros($rubro);

    		$totales ='<table border = "0">';

		$totales .='<input type="hidden" name="fecha" id="fecha" value = "' . $fecha . '"/>';
		$totales .='<input type="hidden" name="fecha2" id="fecha2" value = "' . $fecha2 . '"/>';
		$totales .='<input type="hidden" name="codalmacen" id="codalmacen" value = "' . $almacen . '"/>';
		$totales .='<input type="hidden" name="pro" id="pro" value = "' . $pro . '"/>';
		$totales .='<input type="hidden" name="doc" id="doc" value = "' . $doc . '"/>';
		$totales .='<input type="hidden" name="tdocu" id="tdocu" value = "' . $tdocu . '"/>';
		$totales .='<input type="hidden" name="rxp" id="rxp" value = "' . $rxp . '"/>';
		$totales .='<input type="hidden" name="pagina" id="pagina" value = "' . $pagina . '"/>';
		$totales .='<input type="hidden" name="tmoneda" id="tmoneda" value = "' . $tmoneda . '"/>';

		$totales .='<input type="hidden" name="rubros" id="rubros" value = "' . $rubros['rubro'] . '"/>';
		$totales .='<tr>';
		$totales .='<td colspan="6" align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='Inafecto IGV:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<select OnChange="Inafecto(this.value);">
											<option value="N">NO</option>
											<option value="S">SI</option>';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='<tr>';
		$totales .='<td colspan="6" align="right">';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$totales .='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='Base Imponible:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<input style="text-align:right" type="text" name="base" id="base" maxlength="15" size="15" value="'.number_format($base, 2, '.', ',').'" onKeyUp="CalcularTotales(this.value,\'' . $rowigv . '\')" onkeypress="return validar(event,3)" />';
		$totales .='&nbsp;&nbsp;&nbsp;&nbspImpuesto:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='<input style="text-align:right" type="text" name="impuesto" id="impuesto" maxlength="15" size="15" value="'.number_format($impuesto, 2, '.', ',').'" onkeypress="return validar(event,3)" />';
		$totales .='&nbsp;&nbsp;&nbsp;&nbspImporte Total:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='<input style="text-align:right" type="text" name="total" id="total" maxlength="15" size="15" value="'.number_format($total, 2, '.', ',').'" onkeypress="return validar(event,3)" />';
		$totales .='&nbsp;&nbsp;&nbsp;&nbspPercepcion:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='<input style="text-align:right" type="text" name="perce" id="perce" maxlength="15" size="15" value="'.number_format($perce, 2, '.', ',').'" onkeypress="return validar(event,3)" />';
		$totales .='<tr><td colspan="13" align="right"><tr><td id="celda2" style="display:none" colspan="13" align="right">';
		$totales .='&nbsp;&nbsp;&nbsp;&nbspInafecto IGV:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$totales .='<input style="text-align:right" type="text" name="inafecto" id="inafecto" maxlength="15" size="15" value="'.number_format($inafecto, 2, '.', ',').'" onkeypress="return validar(event,1)" />';
		$totales .='<tr><td colspan="13" align="right"><tr><td colspan="13" align="right">';
		$totales .='<tr><td colspan="13" align="right"><button name="action" type="submit" id="btn-GuardarCompra" value="Totales" onClick=irOtros()><img src="/sistemaweb/icons/gadd.png" align="right" />Registrar Compra</button>';
		$totales .='&nbsp;&nbsp;<button name="action" type="submit" value="Regresar" onClick=regresar(\'' . $fecha . '\',\'' . $fecha2 . '\',\'' . $rxp . '\',\'' . $pagina . '\',\'' . $almacen . '\',\'' . $pro . '\',\'' . $doc . '\',\'' . $tdocu . '\');><img src="/sistemaweb/icons/greturn.png" align="right" />Regresar</button></td>';
		$totales .='</tr></tbody></table>';

    		return $totales;

  	}

	function setRegistrosProveedor($proveedor){

    		$RegistrosCB = RegistroComprasModel::ProveedorCBArray("trim(pro_codigo)||''||trim(pro_razsocial) ~ '".pg_escape_string($proveedor)."'");
    		$RegistrosAD = RegistroComprasModel::ProveedorAdi("trim(pro_codigo)||''||trim(pro_razsocial) ~ '".pg_escape_string($proveedor)."'");
    		$RegistrosDD = RegistroComprasModel::ProveedorDias($RegistrosAD[0]['num']);
    		$RegistrosRU = RegistroComprasModel::ProveedorRubro($RegistrosAD[0]['rubro']);
			
		$dias = $RegistrosDD[0]['dias'];
		$rubro = trim($RegistrosRU[0]['rubro']);

    		if (count($RegistrosCB) == 1) {
      			foreach($RegistrosCB as $cod => $descri){
      				$result = $descri." <script language=\"javascript\">top.setRegistroPro('".trim($cod)."','".trim($dias)."','".trim($rubro)."');</script>";
      			}
    		}
    		
    		if (count($RegistrosCB) > 1){
      			$att_opt = array();
      			foreach($RegistrosCB as $cod => $descri){
        			$att_opt[trim($cod)] = array("onclick"=>"getRegistroPro('".trim($cod)."');");
      			}
      			$cb = new f2element_combo('cbDatosCliente', '','', $RegistrosCB,'',array("size"=>"3"), array(), $att_opt);
     			$result = '<td><div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';

   		}
   		   		
    		return $result;

  	}

	function setRegistrosProveedorB($proveedor){

    		$RegistrosCB = RegistroComprasModel::ProveedorCBArray("trim(pro_codigo)||''||trim(pro_razsocial) ~ '".pg_escape_string($proveedor)."'");
			
    		if (count($RegistrosCB) == 1) {
      			foreach($RegistrosCB as $cod => $descri){
      				$result = $descri." <script language=\"javascript\">top.setRegistroProB('".trim($cod)."');</script>";
      			}
    		}
    		
    		if (count($RegistrosCB) > 1){
      			$att_opt = array();
      			foreach($RegistrosCB as $cod => $descri){
        			$att_opt[trim($cod)] = array("onclick"=>"getRegistroProB('".trim($cod)."');");
      			}
      			$cb = new f2element_combo('cbDatosCliente', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
     			$result = '<td><div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';

   		}
   		   		
    		return $result;

  	}

	function reportePDF($resultado,$desde,$hasta) {

		$cabecera1 = Array(
					"corre" 	=> " NUMERO ",
					"femision" 	=> " FECHA ",
					"fvencimiento" 	=> " FECHA ",
					"documento" 	=> "COMPROBANTE DE DOCUMENTO",
					"numero" 	=> "  NUMERO",
					"infoprovee"	=> "    INFORMACION           DEL          PROVEEDOR ",
					"adq"		=> "ADQUISIC. GRAV. DEST. A OPER.",
					"adqexp"	=> " ADQUISICIONES GRAVADAS Y/O EXPORTACION ",
					"adqno"		=> " ADQUISICIONES NO GRAVADAS ",
					"valor"		=> " VALOR ",
					"blanco"	=> "",
					"otros"		=> "OTROS",
					"blanco2"	=> "",
					"pago"		=> "NRO. DE",
					"blanco3"	=> "",
					"depd"		=> "DEPOSITO DETRACCION",
					"blanco4"	=> "",
					"docuref"	=> "REFERENCIA DEL COMPROBANTE DE PAGO",
		);

		$cabecera2 = Array(
					"corre" 	=> "CORRELATIVO",
					"femision" 	=> " DE ",
					"fvencimiento" 	=> " DE ",
					"tipo" 		=> " TIPO ",
					"serie" 	=> " SERIE ",
					"dsi" 		=> " DSI ",
					"numero"	=> "      DOCUMENTO",
					"tipiden"	=> " DOCUMENTO ",
					"numiden"	=> " IDENTIDAD ",
					"razon"		=> " RAZON ",
					"grav"		=> "GRAV. Y/O EXPORTACION",
					"blanco2"	=> " ",
					"basexp"	=> " BASE ",
					"blanco3"	=> " ",
					"basen"		=> " BASE ",
					"blanco4"	=> " ",
					"adq"		=> "ADQUISIC.",
					"isc"		=> "ISC",
					"otros"		=> "TRIBUTOS",
					"total"		=> " IMPORTE ",
					"pago"		=> "COMP.",
					"numd"		=> "NUMERO",
					"femid"		=> "FECHA",
					"tc"		=> "T.C.",
					"fecref"	=> "FECHA",
					"tiporef"	=> "TIPO",
					"serieref"	=> "SERIE",
					"numref"	=> "NRO.",
		);


		$cabecera3 = Array(
					"corre" 	=> "DEL REGISTRO",
					"femision" 	=> " EMISION ",
					"fvencimiento" 	=> "VENCIMIENTO",
					"blanco" 	=> " ",
					"tipoprovee" 	=> "TIPO",
					"numeroprovee"	=> " NUMERO ",
					"blanco2"	=> " ",
					"social"	=> " SOCIAL ",
					"imponible"	=> "BASE IMPONIBLE",
					"igv"		=> " IGV ",
					"blanco3"	=> " ",
					"imponiblexp"	=> " IMPONIBLE ",
					"igvxp"		=> " IGV ",
					"blanco4"	=> " ",
					"imponiblen"	=> " IMPONIBLE ",
					"igvn"		=> " IGV ",
					"blanco5"	=> " ",
					"grava"		=> "NO GRAVADAS",
					"blanco6"	=> " ",
					"otros"		=> "Y CARGOS",
					"total"		=> "TOTAL",
					"blanco7"	=> " ",
					"pago"		=> "DE PAGO",
					"blanco8"	=> " ",
					"femid"		=> "EMISION",
					"blanco9"	=> " ",
					"numref"	=> "DOCUMENTO",
		);

		$fontsize = 5.6;
		$reporte = new CReportes2("L");

		/*cabecera1*/

		$reporte->definirColumna("corre", $reporte->TIPO_TEXTO, 12, "C","cab1");
		$reporte->definirColumna("femision", $reporte->TIPO_TEXTO, 10, "C","cab1");
		$reporte->definirColumna("fvencimiento", $reporte->TIPO_TEXTO, 10, "C","cab1");
		$reporte->definirColumna("documento", $reporte->TIPO_TEXTO, 24, "C","cab1");
		$reporte->definirColumna("numero", $reporte->TIPO_TEXTO, 10, "L","cab1");
		$reporte->definirColumna("infoprovee", $reporte->TIPO_TEXTO, 56, "C","cab1");
		$reporte->definirColumna("adq", $reporte->TIPO_TEXTO, 40, "R","cab1");
		$reporte->definirColumna("valor", $reporte->TIPO_TEXTO, 12, "C","cab1");
		$reporte->definirColumna("otros", $reporte->TIPO_TEXTO, 5, "C","cab1");
		$reporte->definirColumna("blanco4", $reporte->TIPO_TEXTO, 23, "C","cab1");
		$reporte->definirColumna("docuref", $reporte->TIPO_TEXTO, 35, "C","cab1");

		/*cabecera2*/

		$reporte->definirColumna("corre", $reporte->TIPO_TEXTO, 12, "C","cab2");
		$reporte->definirColumna("femision", $reporte->TIPO_TEXTO, 10, "C","cab2");
		$reporte->definirColumna("fvencimiento", $reporte->TIPO_TEXTO, 10, "C","cab2");
		$reporte->definirColumna("tipo", $reporte->TIPO_TEXTO, 6, "L","cab2");
		$reporte->definirColumna("serie", $reporte->TIPO_TEXTO, 6, "C","cab2");
		$reporte->definirColumna("dsi", $reporte->TIPO_TEXTO, 6, "R","cab2");
		$reporte->definirColumna("numero", $reporte->TIPO_TEXTO, 15, "L","cab2");
		$reporte->definirColumna("tipiden", $reporte->TIPO_TEXTO, 10, "L","cab2");
		$reporte->definirColumna("numiden", $reporte->TIPO_TEXTO, 12, "L","cab2");
		$reporte->definirColumna("razon", $reporte->TIPO_TEXTO, 40, "C","cab2");
		$reporte->definirColumna("grav", $reporte->TIPO_COSTO, 21, "R","cab2");
		$reporte->definirColumna("blanco2", $reporte->TIPO_COSTO, 12, "L","cab2");
		$reporte->definirColumna("adq", $reporte->TIPO_COSTO, 11, "R","cab2");
		$reporte->definirColumna("otros", $reporte->TIPO_TEXTO, 8, "R","cab2");
		$reporte->definirColumna("total", $reporte->TIPO_COSTO, 15, "R","cab2");
		$reporte->definirColumna("tc", $reporte->TIPO_TEXTO, 5, "R","cab2");
		$reporte->definirColumna("fecref", $reporte->TIPO_TEXTO, 10, "C","cab2");
		$reporte->definirColumna("tiporef", $reporte->TIPO_TEXTO, 4, "R","cab2");
		$reporte->definirColumna("serieref", $reporte->TIPO_TEXTO, 10, "C","cab2");
		$reporte->definirColumna("numref", $reporte->TIPO_TEXTO, 10, "C","cab2");

		/*cabecera3*/

		$reporte->definirColumna("corre", $reporte->TIPO_TEXTO, 12, "C","cab3");
		$reporte->definirColumna("femision", $reporte->TIPO_TEXTO, 10, "C","cab3");
		$reporte->definirColumna("fvencimiento", $reporte->TIPO_TEXTO, 10, "C","cab3");
		$reporte->definirColumna("blanco", $reporte->TIPO_TEXTO, 38, "C","cab3");
		$reporte->definirColumna("tipoprovee", $reporte->TIPO_TEXTO, 7, "C","cab3");
		$reporte->definirColumna("numeroprovee", $reporte->TIPO_TEXTO, 10, "C","cab3");
		$reporte->definirColumna("social", $reporte->TIPO_TEXTO, 40, "C","cab3");
		$reporte->definirColumna("imponible", $reporte->TIPO_COSTO, 18, "R","cab3");
		$reporte->definirColumna("igv", $reporte->TIPO_COSTO, 14, "R","cab3");
		$reporte->definirColumna("grava", $reporte->TIPO_COSTO, 12, "R","cab3");
		$reporte->definirColumna("otros", $reporte->TIPO_TEXTO,11, "R","cab3");
		$reporte->definirColumna("total", $reporte->TIPO_COSTO, 10, "R","cab3");
		$reporte->definirColumna("blanco9", $reporte->TIPO_TEXTO, 33, "C","cab3");
		$reporte->definirColumna("numref", $reporte->TIPO_TEXTO, 10, "C","cab3");

		$formato = "FORMATO 8.1";
		$titulo = "REGISTRO DE COMPRAS";
        
		$reporte->SetFont("courier", "", $fontsize);
		$reporte->SetMargins(0, 0, 0);

		$reporte->definirCabecera(1, "L", $formato);
		$reporte->definirCabecera(1, "R", "PAG.%p");
		$reporte->definirCabecera(2, "C", $titulo);
		
		//DATOS PRINCIPALES
                $datos 		= RegistroComprasModel::obtenerAlma($_REQUEST['estacion']);

		$reporte->definirCabecera(3, "L", "PERIODO: " . $desde . " AL " . $hasta);
		$reporte->definirCabecera(5, "L", "RAZON SOCIAL: " . $datos[0]);
		$reporte->definirCabecera(4, "L", "RUC: " . trim($datos[1]));

		$reporte->definirCabeceraPredeterminada($cabecera1,"cab1");
		$reporte->definirCabeceraPredeterminada($cabecera2,"cab2");
		$reporte->definirCabeceraPredeterminada($cabecera3,"cab3");

		$reporte->AddPage();
		
		$imponible=0;
		$igv=0;
		$total=0;

		for ($i = 0; $i<count($resultado); $i++) {

			$a = $resultado[$i];

			$femision = $a['femision'];

			if($a['tipo'] != '07' && $a['tipo'] != '08'){
				$a['fecharef'] = '';
				$a['tiporef'] = '';
				$a['serieref'] = '';
				$a['docuref'] = '';
			}

			$imponible	= $imponible + $a['imponible'];
			$inafecto 	= $inafecto + $a['inafecto'];
			$igv 		= $igv + $a['impuesto'];
			$total 		= $total + ($a['total'] + $a['inafecto']);

			$arr = array(
					"corre"		=> $a['corre'],
					"femision"	=> $a['femision'],
					"fvencimiento"	=> $a['fvencimiento'],
					"tipo"		=> "  ".$a['tipo'],
					"serie"		=> $a['serie'],
					"dsi"		=> $a['dsi'],
					"numero"	=> "      ".$a['numero'],
					"tipiden"	=> "    6",
					"numiden"	=> $a['ruc'],
					"razon"		=> $a['razonsocial'],
					"grav"		=> $a['imponible'],
					"blanco2"	=> $a['impuesto'],//IGV
					"adq"		=> $a['inafecto'],
					"otros"		=> "0.00",
					"total"		=> $a['total'] + $a['inafecto'],
					"tc"		=> $a['tc'],
					"fecref"	=> $a['fecharef'],
					"tiporef"	=> $a['tiporef'],
					"serieref"	=> $a['serieref'],
					"numref"	=> $a['docuref'],
				);

			$reporte->nuevaFila($arr, "cab2");
		}

			$arr2 = array(
					"social"	=> "TOTAL: ",
					"imponible"	=> $imponible,
					"igv"		=> $igv,
					"grava"		=> $inafecto,
					"total"		=> $total,
				);

			$reporte->nuevaFila($arr2, "cab3");

		$reporte->Output("/sistemaweb/compras/movimientos/pdf/RegistroCompras.pdf", "F");

		return '<script> window.open("/sistemaweb/compras/movimientos/pdf/RegistroCompras.pdf","miwin","width=700,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';

	}

}

