<?php

class CodigosRapidoTemplate extends Template {
    /*
    */
    function getTitulo() {
		//return '<h2 align="center"><b>Registro de Códigos Rápido</b></h2>';
		return '';
    }

	function search_form() {

		/*$buscar = '<div class="container-buscar">
		<h2 align="center" style="color:#336699"> <b> Buscar de Códigos Rápido </b> </h2>
		<form target="control" action="control.php" name="buscar_codigos_rapido" method="get"><table>
		<tr>
		 <td>
		 <select id="select-buscar" onchange="selectBuscar()">
		  <option value="0">Idenfificador</option>
		  <option value="1">Articulo</option>
		 </select>
		 </td>
		 <td>
		  <input type="text" name="text-identidicador" id="text-identidicador" placeholder="Ingrese el identificador">
		  <input type="text" name="text-nombre-producto" id="text-nombre-producto none" placeholder="Ingrese el articulo" onkeyup="autocompleteBridge(1)">
		  <input type="hidden" name="text-codigo-producto" id="text-codigo-producto none">
		 </td>
		</tr>
		<tr>
		 <td>
		 </td>
		 <td>
		  <input type="submit" name="buscar" value="Buscar">
		 </td>
		</tr>
		</table></form></div>';*/


		$form = new form2(
			//$buscar.
			'<div><h2 align="center" style="color:#336699"> <b> Registro de Códigos Rápido </b> </h2>'
			, 'form_codigos_rapido', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.CODIGOSRAPIDO"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" cellpadding="0">'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_text("identificador", "Identificador:", '', '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>
		<td>
		<span class="form_label">Identificador:</span>
		</td>
		<td>
		<input id="identificador" class="form_input" type="text" maxlength="12" value="" name="identificador">
		</td>
		</tr>'));

		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('
        <tr>
            <td>
                Art&iacute;culo
            </td>
            <td>
                <input type="text" id="txt-No_Producto" onkeyup="autocompleteBridge(0)" class="mayuscula" name="nom_articulo" placeholder="Ingresar Código o Nombre" autocomplete="off" value="" maxlength="35" size="35"> 
            </td>
        </tr>
        <tr>
        	<td style="display: none;">
                C&oacute;digo
            </td>
        	<td>
                <input type="hidden" id="txt-Nu_Id_Producto" name="articulo" placeholder="Ingresar codigo producto" value="" maxlength="25" size="25"></div>
        '));

		//$form->addElement(FORM_GROUP_MAIN, new f2element_text("articulo", "Cod. Articulo:", '', '', 13, 13));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_text('nom_articulo', '', '', '', 30, 50,'',array('readonly')));
		/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor=\'hand\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'form_codigos_rapido.articulo\',\'form_codigos_rapido.nom_articulo\',\'articulos\',\'\',\'<?php echo $valor;?>\');"> ')); */
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
//		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Buscar", espacios(5)));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Agregar", espacios(5)));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	    
	    //exit;
	    //$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<script>window.onload = function() {parent.document.getElementById("identificador").focus();}</script>'));

		return $form->getForm();
    	}
    
    	function reporte($resultados) {
		$result  = '';
		$result .= '<table cellpadding="0" style="border: 1; border-style: simple; border-color: #00000;" align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera" width="100">IDENTIFICADOR</th>';
		$result .= '<th class="grid_cabecera" width="150">CODIGO DE ARTICULO</th>';
		$result .= '<th class="grid_cabecera" width="200">NOMBRE DE ARTICULO</th>';
		$result .= '<th class="grid_cabecera" width="20">&nbsp;</th>';
		$result .= '</tr>';

		foreach ($resultados as $x => $a) {		
			$result .= '<tr>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['identificador']).'&nbsp;</td>';
			$result .= '<td align="center">&nbsp;'.htmlentities($a['articulo']).'&nbsp;</td>';	
			$result .= '<td align="center">&nbsp;'.htmlentities($a['nom_articulo']).'&nbsp;</td>';
			$result .= '<td><A href="javascript:confirmarLink(\'Desea eliminar el articulo rapido con Identificador: '.htmlentities(trim($a['identificador'])).'?\',\'control.php?rqst=MOVIMIENTOS.CODIGOSRAPIDO&action=Eliminar&identificador='.trim($a['identificador']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td>';      	 	      	 
			$result .= '</tr>';		    	
		}		
		$result .= '</table>';

		return $result;
    	}

}
