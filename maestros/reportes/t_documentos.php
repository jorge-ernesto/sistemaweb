<?php
class DocumentosTemplate extends Template {


	function titulo() {
		return '<h2 align="center"><b>Numero de Documentos</b></h2>';
	}

	function formSearch(){



		$almacenes 		= DocumentosModel::ObtenerAlmacenes();
		$almacenes['TODOS'] 	= "Todos Almacenes";

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.DOCUMENTOS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">documentos: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("banco", "Almacen:", "", $almacenes, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><table>'));

		return $form->getForm();
	}

	function resultadosBusqueda($resultados) {
		$result  = '';
		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">TIPO DE DOC</th>';
		$result .= '<th class="grid_cabecera">CODIGO</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION</th>';
		$result .= '<th class="grid_cabecera">LONGITUD</th>';
		$result .= '<th class="grid_cabecera">NUM ACTUAL</th>';
		$result .= '<th class="grid_cabecera">ALMACEN</th>';
		$result .= '<th colspan="2" class="grid_cabecera"></th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['banco']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['num_tipdocumento']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['num_seriedocumento']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['num_descdocumento']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['ini']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['ch_almacen']) . '</td>';
			/*$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.DOCUMENTOS&action=Modificar&num_tipdocumento='
			.($a['num_tipdocumento']).'&idbanco='.($a['idbanco']).'" target="control"><img src="/sistemaweb/icons/open.gif" 
			align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar el Nro. cuenta '
			. htmlentities($a['num_tipdocumento']).' ?\',\'control.php?rqst=REPORTES.DOCUMENTOS&action=Eliminar&num_tipdocumento='
			.($a['num_tipdocumento']).'&idbanco='.($a['idbanco']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" 
			alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
			$result .= '</tr>';*/

		}

		$result .= '</table>';
		return $result;
	}
}
