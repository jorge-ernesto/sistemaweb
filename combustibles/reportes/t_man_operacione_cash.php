<?php
class MAN_CASHOPETemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>OPERACIONES</b></h2>';
	}

	function formPag($paginacion, $vec) {

		$fecha = $vec[0];
		$fecha2 = $vec[1];

		if ($fecha == '' || $fecha2 == '') {

			$fecha = date(d . "/" . m . "/" . Y);
			$fecha2 = date(d . "/" . m . "/" . Y);

		}

		$estaciones = MAN_CASHOPEModel::obtieneListaEstaciones();

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form -> addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.CUENTASBANCARIAS"));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Desde: '));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 12));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(' . "'Form.fecha'" . ');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">Hasta: '));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar(' . "'Form.fecha2'" . ');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		$form -> addGroup("GRUPO_PAGINA", "Paginacion");

		if ($paginacion['paginas'] == 'P') {
			$paginacion['paginas'] = '0';
		}

		$form -> addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina ' . $paginacion['paginas'] . ' de ' . $paginacion['numero_paginas'] . ' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form -> addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2), array("border" => "0", "alt" => "Primera P&aacute;gina", "onclick" => "javascript:PaginarRegistrosFecha('" . $paginacion['pp'] . "','" . $paginacion['primera_pagina'] . "','" . $fecha . "','" . $fecha2 . "')")));
		$form -> addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5), array("border" => "0", "alt" => "P&aacute;gina Anterior", "onclick" => "javascript:PaginarRegistrosFecha('" . $paginacion['pp'] . "','" . $paginacion['pagina_previa'] . "','" . $fecha . "','" . $fecha2 . "')")));
		$form -> addElement("GRUPO_PAGINA", new f2element_text('paginas', '', $paginacion['paginas'], espacios(5), 3, 2, array("onChange" => "javascript:PaginarRegistrosFecha('" . $paginacion['pp'] . "',this.value,'" . $fecha . "','" . $fecha2 . "')")));
		$form -> addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2), array("border" => "0", "alt" => "P&aacute;gina Siguente", "onclick" => "javascript:PaginarRegistrosFecha('" . $paginacion['pp'] . "','" . $paginacion['pagina_siguiente'] . "','" . $fecha . "','" . $fecha2 . "')")));
		$form -> addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2), array("border" => "0", "alt" => "&Uacute;ltima P&aacute;gina", "onclick" => "javascript:PaginarRegistrosFecha('" . $paginacion['pp'] . "','" . $paginacion['ultima_pagina'] . "','" . $fecha . "','" . $fecha2 . "')")));
		$form -> addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form -> addElement("GRUPO_PAGINA", new f2element_text('numero_registros', 'Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4, array("onChange" => "javascript:PaginarRegistros(this.value,'" . $paginacion['primera_pagina'] . "','" . $fecha . "','" . $fecha2 . "')")));

		return $form -> getForm();
	}

	function formSearch($paginacion) {

		$estaciones = MAN_CASHOPEModel::obtieneListaEstaciones();

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form -> addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.MAN_CASHOPE"));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		//PAGINADOR

		$form -> addGroup("GRUPO_PAGINA", "Paginacion");

		if ($paginacion['paginas'] == 'P') {
			$paginacion['paginas'] = '0';
		}

		$form -> addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina ' . $paginacion['paginas'] . ' de ' . $paginacion['numero_paginas'] . ' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form -> addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2), array("border" => "0", "alt" => "Primera P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['primera_pagina'] . "')")));
		$form -> addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5), array("border" => "0", "alt" => "P&aacute;gina Anterior", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_previa'] . "')")));
		$form -> addElement("GRUPO_PAGINA", new f2element_text('paginas', '', $paginacion['paginas'], espacios(5), 3, 2, array("onChange" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "',this.value)")));
		$form -> addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2), array("border" => "0", "alt" => "P&aacute;gina Siguente", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_siguiente'] . "')")));
		$form -> addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2), array("border" => "0", "alt" => "&Uacute;ltima P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['ultima_pagina'] . "')")));
		$form -> addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form -> addElement("GRUPO_PAGINA", new f2element_text('numero_registros', 'Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4, array("onChange" => "javascript:PaginarRegistros(this.value,'" . $paginacion['primera_pagina'] . "')")));

		return $form -> getForm();
	}

	function formAgregar($fila, $paginacion, $vec) {

		$fecha = $vec[0];
		$fecha2 = $vec[1];

		$hoy = date("d/m/Y");

		$estaciones = MAN_CASHOPEModel::obtieneListaEstaciones();

		$form = new Form('', "Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form -> addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.MAN_CASHOPE"));

		if ($_REQUEST['action'] == 'Modificar') {

			$form -> addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));

		}
		$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("<table>"));
		$array_tipo_ope = array("Ingreso Caja", "Egreso Caja");
		$array_tipo_relacion_i = array("Manuales", "Cuentas por Cobrar", "Cuentas por Pagar", );

		if ($_REQUEST['action'] == 'Agregar') {

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
			//$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("Cod Operacion:</td><td style='text-align:left;'>"));
			//$form -> addElement(FORM_GROUP_MAIN, new form_element_text('', '<tr><td colspan="2" style="text-align:center;">', '', 20, 20, ($_REQUEST['action'] == 'Modificar' ? array('readonly') : array()), 'onkeypress="return validar(event,2)"'));

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("Nombre Operacion: </td><td style='text-align:left;'>"));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_text('', 'name', $fila['name'], '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 30, 30, false, 'onkeypress="return validar(event,1)"'));

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("Tipo Operacion: </td><td style='text-align:left;'>"));
			$cadena = "";
			foreach ($array_tipo_ope as $key => $value) {
				$cadena .= "<option value='$key' >$value</option>";

			}

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("<select name='type'>$cadena</select></td></tr><tr><td colspan='2' style='text-align:center;'>"));
			$cadena_realcion = "";

			foreach ($array_tipo_relacion_i as $key => $value_i) {
				$cadena_realcion .= "<option value='$key' selected>$value_i</option>";

			}

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("Relacion(C.por pagar,cobrar): </td><td style='text-align:left;'>"));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("<select name='accounts'>$cadena_realcion</select></td></tr><tr><td colspan='2' style='text-align:center;'>"));

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
			$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
			$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

		} else {

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("Cod Operacion:</td><td style='text-align:left;'>"));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_text('', 'c_cash_operation_id', $fila['c_cash_operation_id'], '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 20, 20, ($_REQUEST['action'] == 'Modificar' ? array('readonly') : array()), 'onkeypress="return validar(event,2)"'));

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("Nombre Operacion: </td><td style='text-align:left;'>"));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_text('', 'name', $fila['name'], '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 30, 30, false, 'onkeypress="return validar(event,1)"'));

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("Tipo Operacion: </td><td style='text-align:left;'>"));
			$cadena = "";
			foreach ($array_tipo_ope as $key => $value) {

				if ($fila['type'] == $key) {
					$cadena .= "<option value='$key' selected>$value</option>";
				} else {
					$cadena .= "<option value='$key' >$value</option>";
				}

			}

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("<select name='type'>$cadena</select></td></tr><tr><td colspan='2' style='text-align:center;'>"));
			$cadena_realcion = "";

			foreach ($array_tipo_relacion_i as $key => $value_i) {
				if ($fila['accounts'] == $key) {
					$cadena_realcion .= "<option value='$key' selected>$value_i</option>";
				} else {
					$cadena_realcion .= "<option value='$key' >$value_i</option>";
				}
			}

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("Relacion(C.por pagar,cobrar): </td><td style='text-align:left;'>"));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("<select name='accounts'>$cadena_realcion</select></td></tr><tr><td colspan='2' style='text-align:center;'>"));

			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
			$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
			$form -> addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
			$form -> addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

		}

		return $form -> getForm();
	}

	function resultadosBusqueda($resultados) {

		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">ID</th>';
		$result .= '<th class="grid_cabecera">FECHA CREACION</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION BRV</th>';
		$result .= '<th class="grid_cabecera">TIPO OPERAION </th>';
		$result .= '<th class="grid_cabecera">TIPO REALACION</th>';

		$result .= '<th colspan="2" class="grid_cabecera"></th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i % 2 == 0 ? "grid_detalle_par" : "grid_detalle_impar");
			$a = $resultados[$i];
			$TIPO_INGRESO = ($a['type'] == 0) ? "I.CAJA" : "E.CAJA";

			$result .= '<tr bgcolor="">';
			$result .= '<td class="' . $color . '" align ="center">' . htmlentities($a['c_cash_operation_id']) . '</td>';
			$result .= '<td class="' . $color . '" align ="center">' . htmlentities($a['created']) . '</td>';
			$result .= '<td class="' . $color . '" align ="center">' . htmlentities($a['name']) . '</td>';
			$result .= '<td class="' . $color . '" align ="center">' . htmlentities($TIPO_INGRESO) . '</td>';
			if ($a['type'] == 0 && $a['accounts'] == 1) {
				$result .= '<td class="' . $color . '" align ="center">' . htmlentities("C. POR COBRAR") . '</td>';
			} else if ($a['type'] == 1 && $a['accounts'] == 2) {
				$result .= '<td class="' . $color . '" align ="center">' . htmlentities("C. POR PAGAR") . '</td>';
			} else {
				$result .= '<td class="' . $color . '" align ="center">' . htmlentities("INGRESO MANUAL") . '</td>';
			}

			$result .= '<td class="' . $color . '"><A href="control.php?rqst=REPORTES.MAN_CASHOPE&action=Modificar&ncuenta=' . ($a['c_cash_operation_id']) . '" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="' . $color . '"><A href="javascript:confirmarLink(\'Deseas eliminar el TIPO DE OPERACION ' . htmlentities($a['c_cash_operation_id']) . ' ?\',\'control.php?rqst=REPORTES.MAN_CASHOPE&action=Eliminar&ncuenta=' . ($a['c_cash_operation_id']) . '\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '</tr>';

		}

		$result .= '</table>';
		return $result;
	}

}
