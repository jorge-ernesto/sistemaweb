<?php

class StockTurnoTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Stock de Combustible por Turno</b></h2>';
	}

	function formPag($paginacion, $vec, $desde, $hasta) {

		$desde 	  = $vec[0];
		$hasta	  = $vec[1];

		if($desde == '' || $hasta == ''){

			$desde = date("01"."/".m."/".Y); 
			$hasta = date(d."/".m."/".Y);

		}

		$estaciones = StockTurnoModel::obtieneListaEstaciones();

		$form = new Form('', "form_stock_turno", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.STOCKTURNO"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" >'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3), array("onfocus" => "getFechaEmision();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $desde, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_stock_turno.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $hasta, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_stock_turno.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Nuevo Stock"><img src="/sistemaweb/icons/agregar.gif" align="right" />Nuevo Stock</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
	
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

	 	$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$desde."','".$hasta."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$desde."','".$hasta."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."',this.value,'".$desde."','".$hasta."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$desde."','".$hasta."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$desde."','".$hasta."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistrosFecha(this.value,'".$paginacion['primera_pagina']."','".$desde."','".$hasta."')")));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("estacion").focus();
			}
		</script>'
		));

		return $form->getForm();
    }


	function search_form($desde,$hasta,$paginacion){

		$estaciones = StockTurnoModel::obtieneListaEstaciones();
	
		$form = new Form("", "form_stock_turno", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.STOCKTURNO"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" >'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3), array("onfocus" => "getFechaEmision();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $desde, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_stock_turno.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $hasta, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_stock_turno.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Nuevo Stock"><img src="/sistemaweb/icons/agregar.gif" align="right" />Nuevo Stock</button>'));
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

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("estacion").focus();
			}
		</script>'
		));

		return $form->getForm();
    }
    
    	function reporte($resultados,$desde,$hasta) {

		$result = '';
		$result .= '<table align="center">';
		$result .= '<tr>';	
		$result .= '<th class="grid_cabecera">Fecha Sistema</th>';
		$result .= '<th class="grid_cabecera">Turno</th>';
		$result .= '<th class="grid_cabecera">Hora Inventario</th>';
		$result .= '<th class="grid_cabecera">Tanque</th>';
		$result .= '<th class="grid_cabecera">Producto</th>';
		$result .= '<th class="grid_cabecera">Stock</th>';
		$result .= '<th class="grid_cabecera">Unidad</th>';
		$result .= '<th class="grid_cabecera">Responsable</th>';
		$result .= '<th class="grid_cabecera">Fecha de Cierre</th>';
		$result .= '<th class="grid_cabecera">&nbsp</th>';
		$result .= '</tr>';

		if(empty($resultados)){
			$result .= '<tr>';	
			$result .= '<th colspan="10" class="grid_cabecera">No hay datos en este rango de fecha '.$desde.' - '.$hasta.'</th>';
			$result .= '</tr>';	
		}else{
			for ($i = 0; $i < count($resultados); $i++) {
				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");	
				$a = $resultados[$i];
				$result .= '<tr bgcolor="">';
				$result .= '<td align="center" class="'.$color.'">&nbsp;'.trim($a['f_sistema']).'&nbsp;</td>';
				$result .= '<td align="center" class="'.$color.'">&nbsp;'.trim($a['turno']).'&nbsp;</td>';
				$result .= '<td align="center" class="'.$color.'">&nbsp;'.trim($a['f_fisico']).'&nbsp;</td>';
				$result .= '<td align="center" class="'.$color.'">&nbsp;'.trim($a['tanque']).'&nbsp;</td>';
				$result .= '<td align="center" class="'.$color.'" >&nbsp;'.trim($a['articulo']).'&nbsp;</td>';
				$result .= '<td align="center" class="'.$color.'">&nbsp;'.trim($a['stock']).'&nbsp;</td>';
				$result .= '<td align="center" class="'.$color.'">&nbsp;'.trim($a['unidad']).'&nbsp;</td>';
				$result .= '<td align="center" class="'.$color.'">&nbsp;' .trim($a['responsable']).'&nbsp;</td>';
				$result .= '<td align="center" class="'.$color.'">&nbsp;'.trim($a['f_cierre']).'&nbsp;</td>';
				$result .= '<td align="center" class="'.$color.'"> <A href="control.php?rqst=MOVIMIENTOS.STOCKTURNO&action=Modificar&registroid='.trim($a['registroid']).
							'&fecha='.trim($a['f_sistema']).'&turno='.trim($a['turno']).'&fechaf='.trim($a['f_fisico']).'&tanque='.trim($a['tanque']).
							'&articulo='.trim($a['articulo']).'&stock='.trim($a['stock']).'&unidad='.trim($a['unidad']).'&responsable='.trim($a['responsable']).
							'&fechacierre='.trim($a['f_cierre']).'&desde='.trim($desde).'&hasta='.trim($hasta).'&almacen='.trim($almacen).'" target="control">
							<img alt="Editar" title="Editar" src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;';
				$result .= '</tr>';
			}
		}
		$result .= '</table>';
		return $result;
    	}

	function formAgregar($almacenes, $fechas_sistema, $tanques) {

		$form = new Form('','agregar', FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.STOCKTURNO"));

		$fechas_sistema['']	= "Seleccionar...";

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='0' width=100%>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;ESTACION</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "ESTACION :", "", $almacenes,espacios(6)));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;FECHA DE SISTEMA</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("fecha_sistema", "FECHA :", "", $fechas_sistema,espacios(6),2,array('onChange=showUser(this.value)'),''));
		$form->addElement(FORM_GROUP_MAIN, new form_element_hidden('fechita', '1'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;TURNO CERRADO</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td><div id='txtHint'><b>Turnos</b></div>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;FECHA Y HORA DE INVENTARIO&nbsp;</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha_inventario", "FECHA INVENTARIO:", date("d/m/Y"), '', 10, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hora_inventario", "HORA INVENTARIO:", date("h:i"), '', 10, 5));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'agregar.fecha_inventario'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;TANQUE</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tanque", "TANQUE :", "", $tanques, espacios(6)));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;STOCK FISICO</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'stock', '', '', '', 20, 20, false,'onkeypress="return validar(event,3)"'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;RESPONSABLE</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'responsable', '', '', '', 20, 20, false,'onkeypress="return validar(event,4)"'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));
		return $form->getForm();
	}
	
	function formModificar($record, $estacion,$desde,$hasta,$almacen) { 

		$form = new form2('','modificar', FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.STOCKTURNO"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='0' width=100%>"));
		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;ESTACION</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'almacen', $estacion, '', '', 20, 20, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;FECHA DE SISTEMA</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'fecha_sistema', trim($record[1]), '', '', 10, 20, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;TURNO CERRADO</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));  
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'turno', trim($record[2]), '', '', 3, 20, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;FECHA Y HORA DE INVENTARIO&nbsp;</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha_inventario", "", substr($record[3],0,10), '', 10, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hora_inventario", "", substr($record[3],10,5), '', 10, 5));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'modificar.fecha_inventario'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;TANQUE</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tanque', trim($record[4]).' - '.trim($record[8]).' - '.trim($record[9]), '', '', 20, 20, true));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;STOCK FISICO</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'stock', $record[5], '', '', 20, 20, false, 'onkeypress="return validar(event,3)"'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td style='font-weight:bold'>&nbsp;RESPONSABLE</td>"));  
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'responsable', $record[6], '', '', 20, 20, false, 'onkeypress="return validar(event,4)"'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<th>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/update2.png" align="right" />Actualizar</button>'));
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("regid", trim($record[7])));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</th>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr>"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr>"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</table>"));
		return $form->getForm();
	}
}
