<?php

class MargenLineaTemplate extends Template
{

	function titulo(){
		return '<div align="center"><h2>MARGEN ACTUAL vs. MARGEN ESPERADO</h2></div>';
	}

	function encabeza(){
		$almacenes = MargenLineaModel::obtenerAlmacenes();
		print_r($almacenes);

		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.MARGENARTICULO"));
		$form->addGroup("ENCABEZA", "Datos");
		$form->addElement("ENCABEZA", new form_element_combo("Almacen:", "codalmacen", $almacenes, '<br><br>', '', '', $almacenes, false, ''));
		$form->addElement("ENCABEZA", new form_element_text("Linea:&nbsp&nbsp&nbsp&nbsp&nbsp", "codlinea", '', '<br>', '', 13, 6, false));
		$form->addGroup("GRUPO_BOTONES", "");
		$form->addElement("GRUPO_BOTONES", new form_element_submit("action", "Buscar", '', '', 20));
		return $form->getForm();
	}
    
	function listar($resultados, $detalle)
	{	
		$result  = '<p align="center">';
		$result .= '<table border="1">';		
		for ($i = 0; $i < count($resultados); $i++) {
			$result .= '<tr>';
			$result .= '<th bgcolor="#CEF6F5">Codigo</th>';
			$result .= '<th colspan="3" bgcolor="#CEF6F5">Descripcion Linea</th>';
			$result .= '<th bgcolor="#CEF6F5">Margen Actual</th>';
			$result .= '<th bgcolor="#CEF6F5">Margen Esperado</th>';
			$result .= '<th> </th>';
			$result .= '</tr>';

			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';			
			$result .= '<td bgcolor="#CEF6F5" style="font-size:11px">' . htmlentities($a['linea']) . '</td>';
			$result .= '<td colspan="3" bgcolor="#CEF6F5" style="font-size:11px">' . htmlentities($a['descripcion_linea']) . '</td>';
			$result .= '<td align="right" bgcolor="#CEF6F5" style="font-size:11px">' . htmlentities(number_format($a['margen_actual'], 3)) . '</td>';
			$result .= '<td align="right" bgcolor="#CEF6F5" style="font-size:11px">' . htmlentities(number_format($a['margen_linea'], 3)) . '</td>';
			$result .= '</tr>';			
									
				$result .= '<tr>';
				$result .= '<td style="font-size:10px; font-weight:bold">Codigo</td>';
				$result .= '<td style="font-size:10px; font-weight:bold">Descripcion</td>';
				$result .= '<td style="font-size:10px; font-weight:bold">Stock</td>';
				$result .= '<td style="font-size:10px; font-weight:bold">Unidad</td>';
				$result .= '<td style="font-size:10px; font-weight:bold">Costo</td>';
				$result .= '<td style="font-size:10px; font-weight:bold">Precio</td>';
				$result .= '<td style="font-size:10px; font-weight:bold">Margen actual</td>';				
				$result .= '<td style="font-size:10px; font-weight:bold">Fecha Ult. Compra</td>';
				$result .= '<td style="font-size:10px; font-weight:bold">Cantidad Ult. Compra</td>';
				$result .= '<td style="font-size:10px; font-weight:bold">Fecha Ult. Venta</td>';
				$result .= '</tr>';
				for ($m = 0; $m < count($detalle); $m++) {
		
					if($resultados[$i]['linea'] == $detalle[$m]['lineap']){
						$ucom = $detalle[$m]['fecha_ult_compra'];
						$uven = $detalle[$m]['fecha_ult_venta'];
						$fuc = substr($ucom,8,2).'/'.substr($ucom,5,2).'/'.substr($ucom,0,4);
						$fuv = substr($uven,8,2).'/'.substr($uven,5,2).'/'.substr($uven,0,4);
						$result .= '<tr>';
						$result .= '<td>' . htmlentities($detalle[$m]['codigo']) . '</td>';
						$result .= '<td>' . htmlentities($detalle[$m]['descripcion']) . '</td>';
						$result .= '<td align="right">' . $detalle[$m]['stock']. '</td>';
						$result .= '<td align="right">' . $detalle[$m]['unidad']. '</td>';
						$result .= '<td align="right">' . htmlentities(number_format($detalle[$m]['costo'], 3)) . '</td>';
						$result .= '<td align="right">' . htmlentities(number_format($detalle[$m]['precio'], 3)) . '</td>';
						$result .= '<td align="right">' . htmlentities(number_format($detalle[$m]['margen_actual'], 3)) . '</td>';
						$result .= '<td align="right">' . $fuc. '</td>';
						$result .= '<td align="right">' . $detalle[$m]['cantidadc']. '</td>';
						$result .= '<td align="right">' . $fuv. '</td>';
						$result .= '</tr>';
					}
				}				
		}
		$result .= '<tr>';
		$result .= '</p>';
		$result .= '</table>';
		return $result;
	}

}

