<?php
class InterfaceConcarTemplate extends Template {
	function titulo() {
		$titulo = '<div align="center"><h2>Interface Opensoft --> Concar</h2></div><hr>';
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
			"11" => "NOVIEMBRE",
			"12" => "DICIEMBRE"
		);
		return $CbMes;
	}

	function formInterfaceConcar($datos,$CbSucursales,$Parametros) {
		$CbMes = InterfaceConcarTemplate::ListadoMes();

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

		$form = new form2('INTERFACE CONCAR', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZCONCAR'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZCONCAR'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["ch_ruc"]));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr><td class="form_td_title">'));
    
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]','Sucursal </td><td>: ', trim(@$datos["sucursal"]), $CbSucursales, espacios(50)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[fechaini]','Fecha de Inicio</td><td>: ', @$datos["fechaini"], '', 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[fechafin]','Fecha de Fin</td><td>: ', @$datos["fechafin"], '', 12, 10));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>d&iacute;a/mes/a&ntilde;o</b></div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><select name="comboTipo" size="1">
											<option value="1" >Ventas</option>
											<option value="2">Cuentas por cobrar</option>
											<option value="3">Compras</option>
										</select><br/><br/>'));

		$form->addGroup ('buttons', '');
		$form->addElement('buttons', new f2element_submit('action','Procesar', espacios(2)));

		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}

	function imprimeResultado($res) {
		return "RESULTADO";
	}

	function formResultados($resultados){
		$form = new form2('INTERFACE CONCAR', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZCONCAR'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZCONCAR'));
		$result = '';
		$result .= '<table align="center" border="1"  style="width:1030px">';
		foreach ($resultados as $line_num => $line) {
			$datos = explode(",", $resultados);
			$result .= '<tr>';
			for ($i = 0; $i < 22; $i++) {
				$result .= '<td>&nbsp;'.$datos[$i].'</td>';
			}
			$result .= '</tr>';
		}
		$result .= '<tr>';
		$result .= '<td>'.$resultados.'</td>';
		$result .= '</tr>';
		$result .= '</table>';
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext($result));
		return $form->getForm();
	}
}

