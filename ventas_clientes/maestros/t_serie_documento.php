<?php
class SerieDocumentoTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Series de Documentos</b></h2>';
	}

	function formSearch($almacen){
	    $almacenes		= SerieDocumentoModel::obtieneListaEstaciones();
		$almacenes['']	= "Todos los Almacenes";

        $form = new form2("", "form_serie_documento", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MAESTROS.SERIEDOCUMENTO"));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0"><tr><td align="right">Busqueda por Almac&eacute;n: '));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $almacen, $almacenes, "", "", array('onChange="Buscar(this.value);"'),""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar</button>'));

		return $form->getForm();
	}

	function formAgregar($fila) {
	    $almacenes	= SerieDocumentoModel::obtieneListaEstaciones();
	    $documentos	= SerieDocumentoModel::Documentos();

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.SERIEDOCUMENTO"));

			if($_REQUEST['action'] == 'Modificar'){
				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));
			}
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border='0'>"));

				if($_REQUEST['action'] == 'Agregar'){
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:right;">'));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Almac&eacute;n: <td style='text-align:left;'>"));
				    $form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", "", $almacenes, espacios(3)));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan='2' style='text-align:right;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Tipo: </td><td style='text-align:left;'>"));
				    $form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipo", "", "", $documentos, espacios(3)));
					$form->addElement(FORM_GROUP_MAIN, new form_element_freeTags('</td></tr><tr><td colspan="2" style="text-align:right;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Serie: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'serie', "", '</td></tr><tr><td colspan="2" style="text-align:right;">', '', 4, 4, false,'onkeypress="return validar(event,1)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Numero: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'numero', "" , '</td></tr><tr><td colspan="2" style="text-align:right;">', '', 7, 7, false,'onkeypress="return validar(event,2)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));
				} else {
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td style='text-align:right;'>Almacen: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'almacen', $fila['almacen'], "", "",4,4,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'>Tipo: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tipo', $fila['tipo'], "", "",2,2,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'>Serie: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "serie", $fila['serie'], "", "",4,4,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'>Descripcion: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "nombre", $fila['nombre'], "", "",30,30,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'>Numero Actual: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'numero', trim($fila['numero']), "", "",7,7,false,'onkeypress="return validar(event,2)"'));			
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/update2.png" align="right" />Actualizar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));
				}

		return $form->getForm();
	}

	function resultadosBusqueda($resultados, $almacen) {

		$result  = '';
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">ALMACEN</th>';
		$result .= '<th class="grid_cabecera">TIPO</th>';
		$result .= '<th class="grid_cabecera">SERIE</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION</th>';
		$result .= '<th class="grid_cabecera">LONGITUD</th>';
		$result .= '<th class="grid_cabecera">NUMERO ACTUAL</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color	= ($i%2 == 0 ? "grid_detalle_par" : "grid_detalle_impar");
			$a	= $resultados[$i];

			$result .= '<tr>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['almacen']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tipo']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['serie']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['nombre']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['longitud']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['numero']) . '</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=MAESTROS.SERIEDOCUMENTO&action=Modificar&tipo='.htmlentities($a['tipo']).'&serie='.htmlentities($a['serie']).'" target="control"><img src="/sistemaweb/icons/gedit.png" align="middle" border="0"/></A>&nbsp;</td>';
			//$result .= '<td style="color:'.$colorletter.';"  class="'.$color.'" ><A href="javascript:confirmarLink(\'Deseas eliminar: '. htmlentities($a['nombre']).'?\',\'control.php?rqst=MAESTROS.SERIEDOCUMENTO&action=Eliminar&tipo='.($a['tipo']).'&serie='.($a['serie']).'&almacen='.($almacen).'&nombre='.($a['nombre']).'\', \'control\')"><img src="/sistemaweb/icons/gdelete.png" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '</tr>';

		}

		$result .= '</table>';

		return $result;

	}
}
