<?php
class CuadreTurnoTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Turnos Cuadre por Ticket</b></h2>';
	}
	
	function formSearch($almacen,$fecha,$fecha2){
		
		$hoy_timestamp  = time();
		$ayer_timestamp = $hoy_timestamp - (24*60*60);
		$ayer = date("d/m/Y", $ayer_timestamp);

		$mes  = date("m");
		$dia  = "01";
		$year = date("Y");
	
		$inicio_mes = mktime(0, 0, 0, $mes, $dia, $year);
		$fin_mes    = mktime(0, 0, 0, $mes+1, 0, $year);
		
		if ($fecha == "" or $fecha2 == "") {
			$fecha      = date("d/m/Y", $inicio_mes);
			$fecha2     = date("d/m/Y", $fin_mes);
		} 

		$almacenes = CuadreTurnoModel::obtenerAlmacenes("");

		if($almacen == "")
			$almacen = $_SESSION['almacen'];
	
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.CUADRETURNO"));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $almacen, $almacenes, "", array("onfocus" => "getFechaEmision();")));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td align="center">'));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text("Fecha Inicio:", "fecha", $fecha, '<a href="javascript:show_calendar(\'Buscar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("Fecha Inicio:", "fecha", $fecha, '', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td align="center">'));		
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("Fecha Final:"));
		$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", $fecha2, '', '', 10, 10, false));
		//$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "fecha2", $fecha2, '<a href="javascript:show_calendar(\'Buscar.fecha2\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>', '', 10, 10, false));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('</td></tr><tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("almacen").focus();
			}
		</script>'
		));

		return $form->getForm();
	}

	function formAgregar($resultado,$fecha,$fecha2){
		
		$hoy_timestamp  = time();
		$ayer_timestamp = $hoy_timestamp;
		$hoy = date("d/m/Y", $ayer_timestamp);

		if (trim($fecha)=="" or trim($fecha2)=="") {
			$fecha = $hoy;
			$fecha2 = $hoy;
		}

		$almacenes = CuadreTurnoModel::obtenerAlmacenes("");

		if($almacen == "")
			$almacen = $_SESSION['almacen'];

		$turno = array("1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5","6"=>"6","7"=>"7","8"=>"8","9"=>"9");
	
		$form = new Form('','editar', FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.CUADRETURNO"));

		if($_REQUEST['action'] == 'Modificar'){

			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));}
			$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));

			if ($_REQUEST['action'] == 'Agregar'){

				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:center;'>Almacen:"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td>"));			
				$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", $resultado['es'], $almacen, $almacenes, ""));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:center;'>Fecha:"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'fecha', $hoy, '<a href="javascript:show_calendar(\'editar.fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a><div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden;"></div></div></td></tr>','', 10, 10,'readonly()'));			
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:center;'>Turno:"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_combo("turno", $resultado['turno'], "", $turno, ""));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<tr><td style='text-align:center;'>Observacion:"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text ("descripcion",$resultado['descripcion'],"","",39,45));				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td><tr>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:center;'>Fecha Registro:"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'fecha_actualizacion', date("d/m/Y"), '', '', 10, 10, ($_SESSION['usuario'] == "OCS" || $_SESSION['usuario'] == "SISTEMAS") ? false : true));
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</tr></td>"));
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td align='center'>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='left'>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '<br>', '', 1, 'onclick="CuadreTurnoRegresar(\'' . $fecha . '\', \'' . $fecha2 . '\')"'));
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));

			}else{

				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:center;'>Centro Costo:"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td><td>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text ("almacen", "", $resultado['es'], "", "",5,5,($_REQUEST['action']=='Modificar'?array('readonly'):array())));				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td></tr>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:center;'>Fecha:"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td><td>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text ("fecha","",$resultado['fecha'],"","",10,10,($_REQUEST['action']=='Modificar'?array('readonly'):array())));				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr><tr>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:center;'>Turno:"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td><td>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text ("turno","",$resultado['turno'],"","",5,5,($_REQUEST['action']=='Modificar'?array('readonly'):array())));				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td></tr>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:center;'>Observacion:"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td><td>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text ("descripcion","",$resultado['descripcion'],"",39,45));				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td></tr>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td style='text-align:center;'>Fecha Registro:"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td><td>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text ("fecha_actualizacion","",$resultado['fecha_actualizacion'],"","",25,25,($_REQUEST['action']=='Modificar'?array('readonly'):array())));				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td></tr>"));
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<tr><td align='center'>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/update2.png" align="right" />Actualizar</button>'));
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td>"));
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<td align='left'>"));
				$form->addElement(FORM_GROUP_MAIN, new form_element_button("btRegresar", "Regresar", '<br>', '', 1, 'onclick="CuadreTurnoRegresar(\'' . $fecha . '\', \'' . $fecha2 . '\')"'));
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("</td></tr></table>"));
			}
		
		return $form->getForm();
	}

	function listado($resultados,$dia,$dia2) {

		$result .= '<div align="center"><form name="editar" method="post" target="control" action="control.php">';
		$result .= '<input type="HIDDEN" name="rqst" value="MOVIMIENTOS.CUADRETURNO">';
		$result .= '<input type="HIDDEN" name="dia" value="' . htmlentities($dia) . '">';
		$result .= '<input type="HIDDEN" name="dia2" value="' . htmlentities($dia2) . '">';
		
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">CC&nbsp</th>';
		$result .= '<th class="grid_cabecera">Fecha</th>';
		$result .= '<th class="grid_cabecera">Turno</th>';
		$result .= '<th class="grid_cabecera">Observacion</th>';
		$result .= '<th class="grid_cabecera">Fecha Registro</th>';
		$result .= '<th class="grid_cabecera">Usuario</th>';
		$result .= '<th class="grid_cabecera">IP</th>';
		$result .= '</tr>';

			for ($i = 0; $i < count($resultados); $i++) {
				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
				$a = $resultados[$i];
				$result .= '<tr bgcolor="">';			
				$result .= '<td class="'.$color.'">' . htmlentities($a['es']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['fecha']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['turno']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['descripcion']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['fecha_actualizacion']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['usuario']) . '</td>';
				$result .= '<td class="'.$color.'">' . htmlentities($a['auditorpc']) . '</td>';
				$result .= '<td class="'.$color.'"><A href="control.php?rqst=MOVIMIENTOS.CUADRETURNO&action=Modificar&es='.$a['es'].'&fecha='.$a['fecha'].'&turno='.$a['turno'].'&descripcion='.$a['descripcion'].'&fecha_actualizacion='.$a['fecha_actualizacion'].'&dia='.$dia.'&dia2='.$dia2.'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
				$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Estas seguro de borrar este registro '. $a['es'].'?\',\'control.php?rqst=MOVIMIENTOS.CUADRETURNO&action=Eliminar&id_cuadre_turno_ticket='.($a['id_cuadre_turno_ticket']).'&dia='.$dia.'&dia2='.$dia2.'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
				$result .= '</tr>';
			}

		$result .= '</table>';

		return $result;
	}
}
