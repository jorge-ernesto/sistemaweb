<?php
class TipodeCambioTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Tipo de Cambio</b></h2>';
	}

	function formPag($paginacion, $vec, $fecha, $fecha2) {

		$fecha 	  = $vec[0];
		$fecha2	  = $vec[1];

		if($fecha == '' || $fecha2 == ''){

			$fecha = date(d."/".m."/".Y); 
			$fecha2 = date(d."/".m."/".Y);

		}

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.TIPODECAMBIO"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
	
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

	 	$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$fecha."','".$fecha2."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$fecha."','".$fecha2."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."',this.value,'".$fecha."','".$fecha2."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$fecha."','".$fecha2."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$fecha."','".$fecha2."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistrosFecha(this.value,'".$paginacion['primera_pagina']."','".$fecha."','".$fecha2."')")));

		return $form->getForm();
    	}


	function formSearch($fecha,$fecha2,$paginacion){

		if($fecha == '' || $fecha2 == ''){

			$fecha = date(d."/".m."/".Y); 
			$fecha2 = date(d."/".m."/".Y);

		}

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.TIPODECAMBIO"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		//PAGINADOR

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
 
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

 		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));


		return $form->getForm();
	}

	function formAgregar($fila, $paginacion, $vec) {

		$fecha 	  = $vec[0];
		$fecha2	  = $vec[1];

		$hoy = date("d/m/Y");

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.TIPODECAMBIO"));

			if($_REQUEST['action'] == 'Modificar'){

				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));

			}
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));

				if($_REQUEST['action'] == 'Agregar'){

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Moneda: <td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tca_moneda', "" , '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 4, 4,false,'onkeypress="return validar(event,2)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Fecha:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "tca_fecha", $hoy, '<a href="javascript:show_calendar(\'Editar.tca_fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0 align="top"/></a><div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10,($_REQUEST['action']=='Modificar'?array('readonly'):array())));			
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Compra libre:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','compra_libre', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Venta libre:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'venta_libre', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Compra banco:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'compra_banco', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Venta banco:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'venta_banco', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Compra oficial:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'compra_oficial', "" , '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Venta oficial:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'venta_oficial', "" , '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));
				}else{
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td style='text-align:right;'> Moneda: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tca_moneda', $fila['tca_moneda'], "", "",4,4,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Fecha: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "tca_fecha", $fila['tca_fecha'], "", "",10,10,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Compra libre: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'compra_libre', $fila['tca_compra_libre'], "", "",6,6,false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Venta libre: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'venta_libre', $fila['tca_venta_libre'], "", "",6,6,false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Compra banco: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'compra_banco', $fila['tca_compra_banco'], "", "",6,6,false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Venta banco: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'venta_banco', $fila['tca_venta_banco'], "", "",6,6,false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Compra oficial: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'compra_oficial', $fila['tca_compra_oficial'], "", "",6,6,false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Venta oficial: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'venta_oficial', $fila['tca_venta_oficial'], "", "",6,6,false,'onkeypress="return validar(event,3)"'));					
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/update2.png" align="right" />Actualizar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));
				}

		return $form->getForm();
	}

	function resultadosBusqueda($resultados,$fecha,$fecha2) {

		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">MONEDA</th>';
		$result .= '<th class="grid_cabecera">FECHA</th>';
		$result .= '<th class="grid_cabecera">COMPRA LIBRE</th>';
		$result .= '<th class="grid_cabecera">VENTA LIBRE</th>';
		$result .= '<th class="grid_cabecera">COMPRA BANCO</th>';
		$result .= '<th class="grid_cabecera">VENTA BANCO</th>';
		$result .= '<th class="grid_cabecera">COMPRA OFICIAL</th>';
		$result .= '<th class="grid_cabecera">VENTA OFICIAL</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tca_moneda']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tca_fecha']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tca_compra_libre']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tca_venta_libre']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tca_compra_banco']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tca_venta_banco']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tca_compra_oficial']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tca_venta_oficial']) . '</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=MAESTROS.TIPODECAMBIO&action=Modificar&tca_moneda='.htmlentities($a['tca_moneda']).'&tca_fecha='.htmlentities($a['tca_fecha']).'&fecha='.$_REQUEST['fecha'].'&fecha2='.$_REQUEST['fecha2'].'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar la moneda '. htmlentities($a['tca_moneda']).'?\',\'control.php?rqst=MAESTROS.TIPODECAMBIO&action=Eliminar&tca_moneda='.($a['tca_moneda']).'&tca_fecha='.($a['tca_fecha']).'&fecha='.$_REQUEST['fecha'].'&fecha2='.$_REQUEST['fecha2'].'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
			$result .= '</tr>';

		}
		$result .= '</table>';
		return $result;
	}
}
