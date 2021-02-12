<?php

class SIIGOProductosCRUDTemplate extends Template {

	function getTitulo() {
		return '<h2 align="center"><b>SIIGO - Productos</b></h2>';
    }
    
	function formPrincipal($sNombreProducto) {
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.SIIGOPRODUCTOS'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Producto: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		                <input type="hidden" id="txt-Nu_Id_Producto" name="Nu_Id_Producto" autocomplete="off" value="" />
			        	<input type="text" maxlength="55" size="60" id="txt-No_Producto" name="No_Producto" autocomplete="off" placeholder="Ingresar Código ó Nombre" value="' . $sNombreProducto . '" onfocus=getDatos(); />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button id="btn-html-agregar" name="action" type="submit" value="Add"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		return $form->getForm();
    }
    
	function formAdd() {
		$form = new form2('', 'Add', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.SIIGOPRODUCTOS'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Producto: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
		                <input type="hidden" id="txt-Nu_Id_Producto" name="Nu_Id_Producto_Add" />
			        	<input type="text" maxlength="55" size="60" id="txt-No_Producto" name="No_Producto_Add" autocomplete="off" placeholder="Ingresar Código ó Nombre" value="" onfocus=getDatos(); />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Codigo producto SIIGO: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
			        	<input type="text" maxlength="11" size="15" id="txt-codigo_producto_siigo" name="codigo_producto_siigo" autocomplete="off" placeholder="Ingresar Código" value="" />
		        	'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html-guardar" name="action" type="submit" value="Save"><img src="/sistemaweb/icons/gadd.png" align="right" />Guardar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		return $form->getForm();
    }
    
    function gridViewHTML($arrResult) {
		$result = '';

		$result .= '<table border="0" align="center" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th colspan="12" class="grid_cabecera">SIIGO</th>';
			$result .= '</tr>';

			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th rowspan="2" class="grid_cabecera">CENTRO COSTO</th>';
				$result .= '<th rowspan="2" class="grid_cabecera">ALMACEN</th>';
				$result .= '<th rowspan="2" class="grid_cabecera">COD. PRODUCTO OPENSOFT</th>';
				$result .= '<th rowspan="2" class="grid_cabecera">COD. PRODUCTO SIIGO</th>';
				$result .= '<th rowspan="2" class="grid_cabecera">NOM. PRODUCTO OPENSOFT</th>';
				$result .= '<th colspan="4" class="grid_cabecera">SERIES VENTAS</th>';
				$result .= '<th colspan="2" class="grid_cabecera">SERIES COMPRAS</th>';
			$result .= '</tr>';

			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">TICKETS BOLETA</th>';
				$result .= '<th class="grid_cabecera">TICKETS FACTURAS</th>';
				$result .= '<th class="grid_cabecera">MANUALES BOLETA</th>';
				$result .= '<th class="grid_cabecera">MANUALES FACTURAS</th>';
				$result .= '<th class="grid_cabecera">BOLETA</th>';
				$result .= '<th class="grid_cabecera">FACTURAS</th>';
			$result .= '</tr>';

			$result .= '<tbody>';
			if($arrResult['estado'] == FALSE) {
				$result .= '<tr class="bgcolor">';
					$result .= '<td colspan="11" class="grid_detalle_par" align="center"><b>No hay registros</b></td>';
				$result .= '</tr>';
			} else {
				$counter = 0;
				foreach ($arrResult['result'] as $row) {
					$color = ($counter%2) == 0 ? ' grid_detalle_par' : ' grid_detalle_impar';
			    	$result .= '<tr class="'. $color. '">';
				    	$result .= '<td align ="center">' . htmlentities($row["centrocosto"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["almacen"]) . '</td>';
				    	$result .= '<td align ="left">' . htmlentities($row["nu_codigo_producto"]) . '</td>';
				    	$result .= '<td align ="left">' . htmlentities($row["codigo_producto_siigo"]) . '</td>';
				    	$result .= '<td align ="left">' . htmlentities($row["no_nombre_producto"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["serietickesboleta"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["serietickesfactura"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["serietickesboleta"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["nuvmseriefactura"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["nucpseriefactura"]) . '</td>';
				    	$result .= '<td align ="center">' . htmlentities($row["nucpseriefacturaglp"]) . '</td>';
				    $result .= '</tr>';
				    $counter++;
				}
			}
			$result .= '</tbody>';
		$result .= '</table>';
		return $result;
    }
}
