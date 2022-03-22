<?php

class AfericionesTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2><b>Registro de Afericiones</b></h2></div>';
	}
    
	function search_form($f_desde,$f_hasta,$paginacion) {

		if($f_desde == '' || $f_hasta == ''){

			$f_desde = date(d."/".m."/".Y); 
			$f_hasta = date(d."/".m."/".Y);

		}

		$estaciones=array('' => 'TODAS');

		$form = new form2('Registro de Afericiones', 'form_afericiones', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.AFERICIONES"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('estacion', '', $almacen, $estaciones, espacios(3), array("onfocus" => "getFechaEmision();")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $f_desde, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_afericiones.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $f_hasta, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_afericiones.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte", espacios(5)));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Agregar", espacios(5)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4" align="center">'));
	
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}
 		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$f_desde."','".$f_hasta."')")));
	   	$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$f_desde."','".$f_hasta."')")));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value,'".$f_desde."','".$f_hasta."')")));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$f_desde."','".$f_hasta."')")));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$f_desde."','".$f_hasta."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."','".$f_desde."','".$f_hasta."')")));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("estacion").focus();
			}
		</script>'
		));

		return $form->getForm();
    }
    
    	function reporte($resultados,$desde, $hasta) {

		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;DIA&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;TURNO&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;CAJA&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;TRANS&nbsp;&nbsp;</th>';		
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;FECHA&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;LADO&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;CODIGO&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;CANTIDAD&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;PRECIO&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;IMPORTE&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;VELOC&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;LINEAS&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;&nbsp;RESPONSABLE&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera"">&nbsp;</th>';
		$result .= '</tr>';
		$i = 0;
		foreach ($resultados as $x => $a) {
			if ($x == -1) {
				break;
			}
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$result .= '<tr>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['dia']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['turno']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['caja']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['trans']).'&nbsp;</td>';			
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['fecha']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['pump']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['codigo']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['cantidad']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['precio']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['importe']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['veloc']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['lineas']).'&nbsp;</td>';
			$result .= '<td align="center" class="'.$color.'" >&nbsp;'.htmlentities($a['responsabl']).'&nbsp;</td>';	
			$result .= '<td  class="'.$color.'" ><A href="javascript:confirmarLink(\'Desea eliminar la afericion Nro. '.htmlentities(trim($a['trans'])).'/'.htmlentities(trim($a['caja'])).'?\',\'control.php?rqst=REPORTES.AFERICIONES&action=Eliminar&trans='.trim($a['trans']).'&dia='.trim($a['dia']).'&caja='.trim($a['caja']).'&codigo='.trim($a['codigo']).'&pump='.trim($a['pump']).'&es='.trim($a['es']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td>';      	 	      	 
			$result .= '</tr>';
			$i++;		    	
		}		
		$result .= '</table>';

		return $result;
    	}

	function formAfericion() {

       		$caja = AfericionesModel::obtieneCajas();

		$form = new form2('Datos de la boleta que se pasara a Aferici&oacute;n', 'form_afericion', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.AFERICIONES'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="5" bgcolor="#FFFFCD">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('C&oacute;digo Sucursal </td><td>:&nbsp;&nbsp;'. $_SESSION['almacen']));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('sucursal', $_SESSION['almacen']));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecha','Fecha  </td><td>: ', date(d."/".m."/".Y) ,'', 10, 12,'','')); //array('readonly')
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_afericion.fecha'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ticket','Nro. Ticket</td><td>: ', '','', 12, 14,''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(' / '));
	        $form->addElement(FORM_GROUP_MAIN, new form_element_combo("Caja:", "caja", "", '<br>', '', '', $caja, false, ''));
//		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('caja','', '','', 3, 4,''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));  
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(10)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
	}
}
