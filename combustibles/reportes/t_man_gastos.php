<?php
class Gasto_Template extends Template {

	function titulo() {
		return '<h2 align="center"><b>Gastos</b></h2>';
	}

	function formSearch(){

		$gastos = Gasto_Model::ObtenerGastos();

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.GASTOS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Gastos: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("id", "Gastos:", "", $gastos, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		return $form->getForm();
	}

	function formAgregar($fila) {

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.GASTOS"));

			if($_REQUEST['action'] == 'Modificar'){

				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));

			}
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));

				if($_REQUEST['action'] == 'Agregar'){

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Nombre:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','name', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 30, 30, false,'onkeypress="return validar(event,4)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
                                        
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

				}else{
                                        
                                        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("ID Gasto:</td><td style='text-align:left;'>"));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_text('','c_cash_operation_id', $fila['c_cash_operation_id'],'</td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10, ($_REQUEST['action']=='Modificar'?array('readonly'):array()),'onkeypress="return validar(event,2)"'));
                                        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Nombre: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','name', $fila['name'],'</td></tr><tr><td colspan="2" style="text-align:center;">', '', 30, 30, false,'onkeypress="return validar(event,4)"'));	
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
                                        
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

				}

		return $form->getForm();
	}

	function resultadosBusqueda($resultados) {

		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">NOMBRE</th>';
	
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['name']) . '</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.GASTOS&action=Modificar&id='.($a['c_cash_operation_id']).'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar el Rubro '. htmlentities($a['name']).' ?\',\'control.php?rqst=REPORTES.GASTOS&action=Eliminar&id='.($a['c_cash_operation_id']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
			$result .= '</tr>';

		}

		$result .= '</table>';
		return $result;
	}
}
