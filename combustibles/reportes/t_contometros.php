<?php

class ContometrosTemplate extends Template {

	function search_form($f_desde,$f_hasta,$paginacion) {
		if($f_desde == '' || $f_hasta == '') {
			$f_desde = date(d."/".m."/".Y); 
			$f_hasta = date(d."/".m."/".Y);
		}

		$estaciones=array('' => 'TODAS');

		$form = new form2('<h3 style="color:#336699;">Contometros Digitales por Turno</h3>', 'form_contometros', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.CONTOMETROS"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('estacion', '', $almacen, $estaciones, espacios(3), array("onfocus" => "getFechaEmision();")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $f_desde, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_contometros.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>&nbsp;<td/><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $f_hasta, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_contometros.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4"></td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Reporte", ""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4">&nbsp;</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4" align="center">'));

		if($paginacion['paginas'] == 'P') {
			$paginacion['paginas'] = '0';
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Página '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' Páginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera Página","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"Página Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"Página Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"Última Página","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por Página : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));
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

	function reporte($results1,$desde, $hasta) {
		$result .= '<table border="0" cellpadding="0" cellspacing="1" bgcolor="#959672" align="center">';
		$result .= '<tr>';
		$result .= '<th>&nbsp;&nbsp;FECHA SISTEMA&nbsp;&nbsp;</th>';
		$result .= '<th colspan="2">FECHA y HORA</th>';
		$result .= '<th>&nbsp;&nbsp;TURNO&nbsp;&nbsp;</th>';
		$result .= '<th>DETALLES</th>';
		$result .= '</tr>';
		$numfilas = 0;

		foreach($results1['propiedades'] as $a => $almacenes) {
			foreach($almacenes['almacenes'] as $ch_almacen=>$venta) {
				$result .= ContometrosTemplate::imprimirLinea($venta);
				$numfilas= $numfilas +1;
			}
		}

		$result .= '</table>';

		return $result;
	}

	function imprimirLinea($rs) {
		$result  = '<tr bgcolor="#FFFFCD">';
		$result .= '<td align="center">&nbsp;'.substr($rs[1], 0, 10).'&nbsp;</td>';
		$result .= '<td align="center" colspan="2" height="25">&nbsp;&nbsp;'.substr($rs[0], 0, 10).'&nbsp;&nbsp;'.substr($rs[0], 11, 11).'&nbsp;&nbsp;</td>';
		$result .= '<td align="center">&nbsp;'.$rs[2].'&nbsp;</td>';
		$result .= '<td align="center">&nbsp;&nbsp;<a href="#" onClick="window.open(\'reporte_por_manguera.php?id='.$rs[4].'\', \'Datos del Articulo\' , \'width=500,height=600,scrollbars=NO,resizable=NO\');">'.$rs[3].'</a>&nbsp;&nbsp;</td>';
		$result .= '</tr>';

		return $result;
	}
}
