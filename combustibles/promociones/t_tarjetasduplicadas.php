<?php

class TarjetasDuplicadasTemplate extends Template {
	function titulo() {
		$titulo = '<div align="center" style="color:#336699;"><h2>TARJETAS DUPLICADAS</h2></div>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}

	function listado($resultados) {
		$result = '';
		$result .= '<div style="text-align:center;"><table style="border-style:none;width:500px;font-size:10px;margin-left:auto; margin-right:auto;">';
		$result .= '<tr style="background:#30767F;">';
		$result .= '<th style="color:#FFFFFF;">&nbsp;</th>';
		$result .= '<th style="color:#FFFFFF;">FECHA</th>';
		$result .= '<th style="color:#FFFFFF;">N&Uacute;MERO ANTERIOR</th>';
		$result .= '<th style="color:#FFFFFF;">N&Uacute;MERO DUPLICADO</th>';
		$result .= '<th style="color:#FFFFFF;">MOTIVO</th>';
		$result .= '<th style="color:#FFFFFF;">DETALLE</th>';
		$result .= '<th style="color:#FFFFFF;">USUARIO</th>';
		$result .= '<th style="color:#FFFFFF;">SUCURSAL</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
			$a = $resultados[$i];
			$result .= '<tr style="background:#' . ((($i%2)==0)?"FFFFFF":"E8F0EA") . '">';
			$result .= '<td>' . htmlentities($a['id']) . '</td>';
			$result .= '<td>' . htmlentities($a['fecha']) . '</td>';
			$result .= '<td>' . htmlentities($a['numero_anterior']) . '</td>';
			$result .= '<td>' . htmlentities($a['numero_duplicado']) . '</td>';
			$result .= '<td>' . htmlentities($a['motivo_duplicada']) . '</td>';
			$result .= '<td>' . htmlentities($a['motivo']) . '</td>';
			$result .= '<td>' . htmlentities($a['usuario']) . '</td>';
			$result .= '<td>' . htmlentities($a['sucursal']) . '</td>';
			$result .= '</tr>';
		}

		$result .= '</table></div>';

		return $result;
	}

	function formBuscar($dIni, $dFin) {
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "PROMOCIONES.TARJETASDUPLICADAS"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Nro. Tarjeta: </td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('busquedatarjeta','', $_REQUEST['busquedatarjeta'],'', 30, 30,'',array('onkeypress="return soloNumeros(event)"', 'onfocus="getFechasIF();"')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_tarjeta.php', 'Buscar.busquedatarjeta','Buscar.itemdescripcion','tarjetas'".')">¿Necesita ayuda?'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fechainicio", "", $dIni, '', 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fechafin", "", $dFin, '', 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Consultar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(
			'<script>
			window.onload = function() {
				parent.document.getElementById("busquedatarjeta").focus();
			}
			</script>'
		));

		return $form->getForm();
	}
}
