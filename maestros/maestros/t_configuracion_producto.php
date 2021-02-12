<?php
class ConfiguracionProductoTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Configuracio de Productos</b></h2>';
	}

	function formSearch($fecha,$fecha2,$paginacion){

		if($fecha == '' || $fecha2 == ''){

			$fecha = date(d."/".m."/".Y); 
			$fecha2 = date(d."/".m."/".Y);

		}

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.CONFIGPRODUCTO"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="center">'));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" alt="center" /></button>'));
		//PAGINADOR

		$form->addGroup("GRUPO_PAGINA", "Agregar");
 
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

		return $form->getForm();
	}


	function formAgregar($fila, $paginacion, $vec) {

		$fecha 	  = $vec[0];
		$fecha2	  = $vec[1];

		$hoy = date("d/m/Y");

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.CONFIGPRODUCTO"));

			if($_REQUEST['action'] == 'Modificar'){

				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));

			}
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));

				if($_REQUEST['action'] == 'Agregar'){

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Codigo Combustible: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_codigocombustible', "" , '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10,false,'onkeypress="return validar(event,2)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Nombre Combustible:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','ch_nombrecombustible', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 20, 20, false,array('onkeypress'=>'return validar(event,3);')));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Nombre Breve Combustible:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_nombrebreve', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10, false));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Precio Combustible:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'nu_preciocombustible', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 7, 7, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Codigo Pec:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_codigopec', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 4, 4, false,'onkeypress="return validar(event,2)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Codigo Combex:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_codigocombex', "" , '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 4, 4, false));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='right'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));
				}else{
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td style='text-align:right;'> Codigo Combustible: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_codigocombustible', $fila['ch_codigocombustible'], "", "",9,9,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Nombre Combustible: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_nombrecombustible', $fila['ch_nombrecombustible'], "", "",20,20));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Nombre Breve Combustible: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_nombrebreve', $fila['ch_nombrebreve'], "", "",10,10));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Precio Combustible: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'nu_preciocombustible', $fila['nu_preciocombustible'], "", "",7,7,false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Codigo Pec: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_codigopec', $fila['ch_codigopec'], "", "",2,2,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Codigo Combex: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_codigocombex', $fila['ch_codigocombex'], "", "",2,2,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td align='center'>"));
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
		$result .= '<th class="grid_cabecera">CODIGO</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION BREVE</th>';
		$result .= '<th class="grid_cabecera">PRECIO VENTA</th>';
		$result .= '<th class="grid_cabecera">COD PEC</th>';
		$result .= '<th class="grid_cabecera">COD COMBEX</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['ch_codigocombustible']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['ch_nombrecombustible']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['ch_nombrebreve']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['nu_preciocombustible']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['ch_codigopec']) . '</td>';
			$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['ch_codigocombex']) . '</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=MAESTROS.CONFIGPRODUCTO&action=Modificar&ch_codigocombustible='.htmlentities($a['ch_codigocombustible']).'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar la moneda '. htmlentities($a['ch_codigocombustible']).'?\',\'control.php?rqst=MAESTROS.CONFIGPRODUCTO&action=Eliminar&ch_codigocombustible='.($a['ch_codigocombustible']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
			$result .= '</tr>';

		}
		$result .= '</table>';
		return $result;
	}
}
