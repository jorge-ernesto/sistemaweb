<?php

class InterfaceConcarActTemplate extends Template {

	function titulo() {
		$titulo = '<div align="center"><h2>Interface Opensoft - Concar</h2></div><hr>';
		return $titulo;
	}

	function formInterfaceConcarAct($datos, $CbSucursales, $Parametros, $empresa, $almacen) {

		$fecha = date("d/m/Y");
//		$CbSucursales[""] = "Seleccionar...";

		$form = new form2('', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZCONCARACT'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZCONCARACT'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["ch_ruc"]));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellpadding="5"> <tr><td class="form_td_title">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]','Almacen </td><td>: ', $almacen, $CbSucursales, espacios(1), "", array('onChange="Buscar(this.value);"', 'onfocus="getFechaEmision();"'),""));
//		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]','Almacen </td><td>: ', trim(@$datos["sucursal"]), $CbSucursales, espacios(1), "", array('onChange="Buscar(this.value);"'),""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));


		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Fecha inicial<td>: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fechaini", "", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_agen_ret.fechaini'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">Fecha final<td>: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fechafin", "", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_agen_ret.fechafin'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[empresa]','</td></tr><tr><td>Empresa </td><td>: ', trim(@$datos["empresa"]), $empresa, espacios(1)));							
										
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Tipo</td><td>:&nbsp&nbsp'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<select name="comboTipo" size="1">
										<option value="1" >Ventas Combustible</option>
										<option value="2" >Ventas Market</option>
										<option value="6" >Ventas Manuales</option>
										<option value="3" >Cta. Cobrar Combustible</option>
										<option value="4" >Cta. Cobrar Market</option>
										<option value="7" >Cta. Cobrar Manuales</option>
										<option value="5" >Compras</option>
										<option value="8" >Liquidacion de Caja</option>
										</select><br/>'));

		/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>Version:</td><td>:&nbsp&nbsp'));

/*
		<option value="1" >Concar 13.38</option>
		<option value="2" >Concar 13.25</option>*/

		/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<select name="versiones" size="1">
										<option value="3" >Concar 13.20</option>
										</select><br/>'));*/

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('num_actual','Numero Actual</td><td>: ', @$datos["num_actual"], '', 12, 10));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Procesar', espacios(2)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("datos[sucursal]").focus();
			}
		</script>'
		));

		return $form->getForm();
	}

	/*function formResultados($resultados){
		$form = new form2('INTERFACE CONCAR', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZCONCARACT'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZCONCARACT'));
		$result = '';
		$result .= '<table align="center" border="1"  style="width:1030px">';
		foreach ($resultados as $line_num => $line) {
			$datos = explode(",", $resultados);
			$result .= '<tr>';
			for ($i = 0; $i < 22; $i++) {
				$result .= '<td>&nbsp;'.$datos[$i].'</td>';
		var_dump($datos[$i]);
			}
			$result .= '</tr>';
		}

		$result .= '<tr>';
		$result .= '<td>'.$resultados.'</td>';
		$result .= '</tr>';
		$result .= '</table>';
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext($result));
		
		return $form->getForm();
	}*/
}
