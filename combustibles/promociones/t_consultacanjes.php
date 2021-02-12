<?php

class ConsultaCanjesTemplate extends Template {

	function titulo() {
		$titulo = '<div align="center"><h2>CONSULTA DE CANJES</h2></div><hr>';
		return $titulo;
  	}

	function errorResultado($errormsg) {
    		return '<blink>'.$errormsg.'</blink>';
  	}

	function listado($registros) {
		$contador = 0;
		$listado = '';
		if(count($registros) > 0) {

			$listado .= '<div id="resultados_grid" class="grid" align="center">
		        		<table width="70%">
		              		<caption ><hr></caption>
		              		<thead align="center" valign="center" >
		              		<tr class="grid_header">';
	    		$listado .= '<td class="grid_cabecera" height="20" width="15%">FECHA Y HORA</td>
					<td class="grid_cabecera" height="20" width="15%">NRO. TARJETA</td>
					<td class="grid_cabecera" height="20" width="30%">USUARIO TARJETA</td>
					<td class="grid_cabecera" height="20" width="10%">PTOS CANJEADOS</td>
					<td class="grid_cabecera" height="20" width="20%">ITEM CANJEADO</td>
					<td class="grid_cabecera" height="20" width="10%">SUCURSAL</td>		
					<td class="grid_cabecera" height="20" width="10%">USUARIO</td>	
					</tr>';

			foreach($registros as $reg) {
				$color = ($contador%2 == 0?"grid_detalle_par":"grid_detalle_impar");
		
				$listado .= '<tr>';		
				$listado .='<td class="'.$color.'">'.$reg["dt_canje_fecha"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["nu_tarjeta_numero"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_tarjeta_descripcion"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["nu_canje_puntaje_canjeado"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_item_descripcion"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_sucursal"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_usuario"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.'<a href="javascript:mostrarDatosCuenta('."'".$reg["id_cuenta"]."'".')" >'.
				'<img alt="Mostrar Cuenta" title="Mostrar Cuenta" border="0" src="/sistemaweb/icons/view16x16.png"></a>'.'&nbsp;</td>';
				$listado .= '</tr>';
			
				$contador++;
			}
			$listado .= '</table></div>';
		} else {
			$listado .= '<div align="center"><table>';
			$listado .= '<tr>';
			$listado .= '<td align="center" class="msg_informacion">';
			$listado .= '<img src="/sistemaweb/icons/messagebox_info32x32.png" border="0">No existe informacion para la consulta realizada.';
			$listado .= '</td>';
			$listado .= '<tr>';
			$listado .= '</table></div>';
		}
    	return $listado;
	}

	function formBuscar($dIni, $dFin) {
		$almacenes = ConsultaCanjesModel::obtenerAlmacenes();
		$almacenes['TODOS'] = "Todos los Almacenes";

		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control','onSubmit="return validar_busqueda_consultacanjes()"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.CONSULTACANJES'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CONSULTACANJES'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('iditemcanje', ''));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacén: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo('almacen', '', 'TODOS', $almacenes, espacios(3)));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("fechainicio", "", $dIni, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("fechafin", "", $dFin, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Nro. Tarjeta: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
				    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busquedatarjeta','', $_REQUEST['busquedatarjeta'],'', 30, 30,'',array('onkeypress="return soloNumeros(event)"', 'onfocus="getFechasIF();"')));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_tarjeta.php', 'Buscar.busquedatarjeta','Buscar.itemdescripcion','tarjetas'".')">¿Necesita ayuda?'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Item: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busquedaitem','', $_REQUEST['busquedaitem'],'', 30, 30,'',array()));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_itemcanje.php','Buscar.iditemcanje','Buscar.busquedaitem','itemscanje'".')">¿Necesita ayuda? <span class="texto_opcional">(Opcional)'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Consultar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

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

	function formPaginacion($paginacion,$filtro,$filtroitem,$fechaini,$fechafin,$intListaPuntos) {

		$form = new form2('', 'Paginacion', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.MOVPUNTOS'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'MOVPUNTOS'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		if($intListaPuntos > 0) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
	    
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$filtro."','".$filtroitem."','".$fechaini."','".$fechafin."')")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$filtro."','".$filtroitem."','".$fechaini."','".$fechafin."')")));
    
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value,'".$filtro."','".$fechaini."','".$fechafin."')")));
		    
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$filtro."','".$filtroitem."','".$fechaini."','".$fechafin."')")));
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$filtro."','".$filtroitem."','".$fechaini."','".$fechafin."')")));
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."','".$filtro."','".$filtroitem."','".$fechaini."','".$fechafin."')")));
		}
 		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));

    		return $form->getForm();
	} 
}
