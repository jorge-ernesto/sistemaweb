<?php
class ProductoxProveedorTemplate extends Template 
{
	function titulo()
	{
		return '<h2 align="center">Productos por Proveedor</h2>';
	}
	
	function formBuscar()
	{
		$almacenes = ProductoxProveedorModel::obtenerAlmacenes();
		print_r($almacenes);
	
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.PRODXPROV"));
	
		$form->addGroup("GROUP_ALMACEN", "Almacen");
		$form->addElement("GROUP_ALMACEN", new form_element_combo("Almacen:", "ch_almacen", $_SESSION['almacen'], '<br>', '', '', $almacenes, false, ''));
	
		$form->addGroup("GRUPO_CONDICIONES", "Condiciones");
		$form->addElement("GRUPO_CONDICIONES", new form_element_text("Mes :", "ch_mes", date("m"), '<br></br>&nbsp;', '', 5, 2, false));	
		$form->addElement("GRUPO_CONDICIONES", new form_element_text("A&#241;o :", "ch_anio", date("Y"), '<br></br>&nbsp;', '', 5, 4, false));
		$form->addElement("GRUPO_CONDICIONES", new form_element_text("Proveedor :", "ch_proveedor", '', '<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/maestros/ayuda/lista_ayuda.php\',\'Buscar.ch_proveedor\',\'despro\',\'proveedores\');"/><div name="despro" id="despro"></div><br>', '', 20,'' , false));	
		$form->addElement("GRUPO_CONDICIONES", new form_element_text("Producto :", "ch_producto", '', '<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/maestros/ayuda/lista_ayuda.php\',\'Buscar.ch_producto\',\'desart\',\'articulos\');"/><div name="desart" id="desart"></div><br>', '', 20,'' , false));
	
		$form->addGroup("GRUPO_BOTONES", "");
		$form->addElement("GRUPO_BOTONES", new form_element_submit("action", "Agregar", '', '', 20));
		$form->addElement("GRUPO_BOTONES", new form_element_submit("action", "Buscar", '', '', 20));
		return $form->getForm();
	}
	
	function formProdxProv($ch_proveedor, $ch_producto, $ch_moneda, $ch_costounitario, $ch_fechacreacion, $registro_nuevo)
	{
		$array_monedas = array('00'=>' ','01'=>'S/. - Nuevos Soles','02'=>'US$ - Dolares Americanos');

		$form = new Form('', "ProdProv", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.PRODXPROV"));
		if ($registro_nuevo) {
			$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("nuevo", "1"));
		}
		else {
			$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("nuevo", "0"));
		}

		$form->addGroup("GRUPO_CONDICIONES", "Gestionar Producto por Proveedor");
		$form->addElement("GRUPO_CONDICIONES", new form_element_text("Proveedor :", "ch_proveedor", $ch_proveedor, '<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/maestros/ayuda/lista_ayuda.php\',\'ProdProv.ch_proveedor\',\'despro\',\'proveedores\');"/><div name="despro" id="despro"></div><br>', '', 20,'' , false));	
		$form->addElement("GRUPO_CONDICIONES", new form_element_text("Producto :", "ch_producto", $ch_producto, '<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/maestros/ayuda/lista_ayuda.php\',\'ProdProv.ch_producto\',\'desart\',\'articulos\');"/><div name="desart" id="desart">foo</div><br>', '', 20,'' , false));
		//$form->addElement("GRUPO_CONDICIONES", new form_element_text("Moneda:", "ch_moneda", '', '<br>&nbsp;', '', 5, 4, false));
		$form->addElement("GRUPO_CONDICIONES", new form_element_combo("Moneda :", "ch_moneda",$ch_moneda, '<br>&nbsp;', '', '', $array_monedas, false, ''));
		$form->addElement("GRUPO_CONDICIONES", new form_element_text("Costo Unitario :", "ch_costounitario", $ch_costounitario, '<br>&nbsp;', '', 6, 6, false));
		$form->addElement("GRUPO_CONDICIONES", new form_element_text("Fecha de Creacion :", "ch_fechacreacion", trim($ch_fechacreacion?$ch_fechacreacion:date("Y-m-d")), '<br>&nbsp;', '', 10, 10, true));

		$form->addGroup("GRUPO_BOTONES", "");
		$form->addElement("GRUPO_BOTONES", new form_element_submit("action", "Guardar", '', '', 20));
		$form->addElement("GRUPO_BOTONES", new form_element_submit("action", "Regresar", '', '', 20));
		return $form->getForm();

	}	

	function listado($resultados)
	{
		$result = '';
		$result .= '<table border="1" align="center" >';
		$result .= '<tr>';
		$result .= '<th>Proveedor</th>';
		$result .= '<th>Producto</th>';
		$result .= '<th>Moneda</th>';
		$result .= '<th>Costo Unitario</th>';
		$result .= '<th>Fecha Creacion</th>';
		$result .= '<th>Stock Final Mes</th>';
		$result .= '<th>Venta Mes</th>';
		$result .= '<th>Venta Mes Anterior</th>';
		$result .= '</tr>';
	
		foreach($resultados as $a) {

			$result .= '<tr bgcolor="" onMouseOver=this.style.backgroundColor="#FFFFCC"; this.style.cursor="hand"; onMouseOut=this.style.backgroundColor="";>';		
			$result .= '<td>' . htmlentities($a['proveedor']) . '</td>';
			$result .= '<td>' . htmlentities($a['producto']) . '</td>';
			$result .= '<td>' . htmlentities($a['moneda']) . '</td>';
			$result .= '<td>' . htmlentities($a['costo_unitario']) . '</td>';
			$result .= '<td>' . htmlentities($a['fecha_creacion']) . '</td>';
			$result .= '<td>' . htmlentities($a['stock_actual']) . '</td>';
			$result .= '<td>' . htmlentities($a['venta_mes']) . '</td>';
			$result .= '<td>' . htmlentities($a['venta_mes_anterior']) . '</td>';
			$result .= "<td>					
					<a href=\"control.php?rqst=MAESTROS.PRODXPROV&action=Modificar&ch_proveedor={$a['codproveedor']}&ch_producto={$a['codproducto']}\" target=\"control\">Modificar</a>&nbsp;
					<a href=\"control.php?rqst=MAESTROS.PRODXPROV&action=Eliminar&ch_proveedor={$a['codproveedor']}&ch_producto={$a['codproducto']}\" target=\"control\">Eliminar</a>&nbsp;
				    </td>";
			$result .= '</tr>';
		}
		return $result;
	}

	function mostrarResultadoEliminacion($res) {
		$result = (($res===true)?"El registro ha sido eliminado":"No se pudo eliminar el registro") . "<br /><a href=\"control.php?rqst=MAESTROS.PRODXPROV\" target=\"control\">Regresar</a>";
		return $result;
	}

	function resultadoGrabar($res) {
		$result = (($res===true)?"El registro ha sido guardado":"No se pudo guardar el registro") . "<br /><a href=\"control.php?rqst=MAESTROS.PRODXPROV\" target=\"control\">Regresar</a>";
		return $result;
	}

}
