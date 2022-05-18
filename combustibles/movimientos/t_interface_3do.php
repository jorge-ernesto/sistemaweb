<?php
class Interface3DOTemplate extends Template {
	function titulo() {
		$titulo = '<div align="center"><h2>Interface Opensoft --> 3DO</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}

	function ResultadoEjecucion($msg) {
		return '<blink>'.$msg.'</blink>';
	}

	function ListadoMes() {
		$CbMes = array(
			"01" => "ENERO",
			"02" => "FEBRERO",
			"03" => "MARZO",
			"04" => "ABRIL",
			"05" => "MAYO",
			"06" => "JUNIO",
			"07" => "JULIO",
			"08" => "AGOSTO",
			"09" => "SETIEMBRE",
			"10" => "OCTUBRE",
			"11" => "NOVIEMBREA",
			"12" => "DICIEMBRE"
		);
		return $CbMes;
	}

	function formInterface3DO($datos,$CbSucursales,$Parametros) {
		$CbMes = Interface3DOTemplate::ListadoMes();

		if(empty($datos["fechaini"])) {
			$dia  = date("d");
			$mes  = date("m");
			$anio = date("Y");
			$datos["fechaini"] = $dia."/".$mes."/".$anio;
		}
		if(empty($datos["fechafin"])) {
			$dia  = date("d");
			$mes  = date("m");
			$anio = date("Y");
			$datos["fechafin"] = $dia."/".$mes."/".$anio;
		}

		$form = new form2('INTERFACE 3DO', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZ3DO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZ3DO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["ch_ruc"]));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr><td class="form_td_title">'));
    		
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]','Sucursal </td><td>: ', trim(@$datos["sucursal"]), $CbSucursales, espacios(50)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('par_3doserver','Servidor</td><td>: ', @$Parametros[0], '', 12, 42));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('par_3douser','Usuario</td><td>: ', @$Parametros[1], '', 12, 24));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('par_3dopass','Contrase&ntilde;a</td><td>: ', @$Parametros[2], '', 12, 24));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('par_3dodb','Base de Datos</td><td>: ', @$Parametros[3], '', 12, 16));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[fechaini]','Fecha de Inicio</td><td>: ', @$datos["fechaini"], '', 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[fechafin]','Fecha de Fin : ', @$datos["fechafin"], '', 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>d&iacute;a/mes/a&ntilde;o</b></div>'));

	        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4" align="center">Ticket Boleta Agrupadas'));
        	$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('agrupado', '', 'S', ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		$form->addGroup ('buttons', '');
		$form->addElement('buttons', new f2element_submit('action','Consultar', espacios(2)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;'));
		$form->addElement('buttons', new f2element_submit('action','Procesar', espacios(2)));

		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}

	function imprimeResultado($res) {
		if ($res === TRUE) {
			$result = "<p style=\"text-align: center;\">Se ha copiado la informaci&oacute;n al 3DO</p>";
		} else {
			$result = "<p style=\"text-align: center;\">No se pudo copiar la informaci&oacute;n al 3DO<script language=\"javascript\">alert('{$res}');</script></p>";
		}
		return $result;
	}

	function imprimeConsulta($resultados,$inicio,$fin,$sucursal){
		$form = new form2('INTERFACE 3DO', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZ3DO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZ3DO'));
		$result = '';
		$result .= '<table align="center" border="1"  style="width:1030px">';
		$result .= '<tr>';
		$result .= '<th>Almacen</th>';
		$result .= '<th>Inicio</th>';
		$result .= '<th>Fin</th>';
		$result .= '<th>Usuario</th>';
		$result .= '<th>Fecha</th>';
		$result .= '<th><input type="submit" name="action" value="Eliminar" style="width:80px">
				<input type="hidden" name="f_inicio" id="f_inicio" value="'.$inicio.'">
				<input type="hidden" name="f_fin" id="f_fin" value="'.$fin.'">
				<input type="hidden" name="f_sucursal" id="f_sucursal" value="'.$sucursal.'"></th>';
		$result .= '</tr>';
		for ($i = 0; $i < count($resultados); $i++) {
			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td align="center" >'. trim($a['almacen'])     .'</td>';
			$result .= '<td align="center" >'. trim($a['inicio'])      .'</td>';
			$result .= '<td align="center" >'. trim($a['fin'])    .'</td>';
			$result .= '<td align="center" >'. trim($a['usuario'])     .'</td>';
			$result .= '<td align="center" >'. trim($a['actual'])  .'</td>';
			$result .= '<td align="center" ><input type="radio" name="radio_eliminar" value="'.trim($a['id']).'"></td>';
			//$result .= '<td align="center" ><a href="javascript:confirmarLink(\'�Desea eliminar el proceso de fecha '.trim($a["inicio"]).'?\',\'control.php?rqst=MOVIMIENTOS.INTERFAZ3DO&task=INTERFAZ3DO&action=Eliminar&id='.trim($a["id"]).'\')"><img alt="Eliminar Proceso" title="Eliminar Proceso" src="/sistemaweb/icons/delete16x16.png" align="middle" border="0"/></a>&nbsp;</td>';
			$result .= '</tr>';
		}
		$result .= '</table>';
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext($result));
		return $form->getForm();
	}
}

