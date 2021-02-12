<?php

class PuntosFidelizaManualTemplate extends Template {
	//METODO QUE DEVUELVE EL TITULO
	function titulo(){
		$titulo = '<div align="center"><h2>Puntos de Fidelizacion Manualmente</h2></div><hr>';
		return $titulo;
	}
	//METODO QUE RETORNA UN MENSAJE DE ERROR
	function errorResultado($errormsg){
		return '<blink>'.$errormsg.'</blink>';
	}
	
	//LISTADO DE LOS PUNTOS FIDELIZACION MANUALES
	function listado($registros){
		$contador = 0;
		$titulo_grid = "";
		//formulario de busqueda
		$columnas = array('SUCURSAL', 'TARJETA','NOMBRE','PUNTOS','FECHA','HORA','USUARIO');
		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
				<table>
				<caption class="grid_title">'.$titulo_grid.'</caption>
				<thead align="center" valign="center" >
				<tr class="grid_header">';
		for($i=0;$i<count($columnas);$i++){
			$listado .= '<td class="grid_cabecera" height="20"> '.strtoupper($columnas[$i]).'&nbsp;</td>';
		}

		$listado .= '</tr><tbody class="grid_body" style="height:50px;">';
		//DETALLE
		foreach($registros as $reg){
			$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");	

			$listado .='<td class="'.$color.'" align="center">'.$reg[7].'</td>';
			$listado .='<td class="'.$color.'">'.$reg[1].'</td>';	
			$listado .='<td class="'.$color.'">'.$reg[2].'</td>';		
			$listado .='<td class="'.$color.'" align="right"> ' . $reg[3] . '</td>';	
			$listado .='<td class="'.$color.'">'.$reg[4].'</td>';	
			$listado .='<td class="'.$color.'">'.$reg[5].'</td>';	
			$listado .='<td class="'.$color.'" align="center">'.$reg[6].'</td>';
			
			//$listado .= '<td class="'.$color.'"> <a href="control.php?rqst=PROMOCIONES.PUNTOSFIDELIZAMANUAL&task=PUNTOSFIDELIZAMANUAL&action=Modificar&idpunto='.$reg[0].'&puntos='.$reg[1].'" target="control"><img alt="Editar Puntos de Fidelizacion" title="Editar Puntos de Fidelizacion" src="/sistemaweb/icons/kedit32x32.png" align="middle" border="0"/></a>&nbsp;</td>';
			
			//$listado .= '<td class="'.$color.'" ><a href="javascript:confirmarLink(\'�Desea eliminar el registro de codigo='.$reg[0].'?\',\'control.php?rqst=PROMOCIONES.PUNTOSFIDELIZAMANUAL&task=PUNTOSFIDELIZAMANUAL&action=Eliminar&idpunto='.$reg[0].'\',\'control\')"><img alt="Eliminar Puntos de Fidelizacion" title="Eliminar Puntos de Fidelizacion" src="/sistemaweb/icons/delete16x16.png" align="middle" 	border="0"/></a>&nbsp;</td>';	
		
			$listado .= '</tr>';
			$contador++;
		}
		$listado .= '</tbody></table></div>';
		return $listado;
	}

  	//Solo Formularios y otros
	function formBuscar($almacen, $dIni, $dFin, $paginacion){
	    $almacenes = PuntosFidelizaManualModel::obtenerAlmacenes();
	    $almacenes[''] = "Todos los Almacenes";

		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.PUNTOSFIDELIZAMANUAL'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PUNTOSFIDELIZAMANUAL'));
		
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));

	      	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacén: </td>'));
	        	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo('almacen', '', '', $almacenes, espacios(3), array("onfocus" => "getFechasIF();"), ''));
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
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button id="btn-add" name="action" type="submit" value="Nuevo"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar </button>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button id="btn-excel" name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Excel </button>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));


			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));


			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
					$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)"),array('readonly')));
					$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
					$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')"),array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(
	    '<script>
	      window.onload = function() {
	        parent.document.getElementById("almacen").focus();
	      }
	    </script>'
	    ));
		return $form->getForm();
	}

	function formPuntosfidelizamanual($puntosfidelizamanual){
		
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_Puntosfidelizamanual();"');
		
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.PUNTOSFIDELIZAMANUAL'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PUNTOSFIDELIZAMANUAL'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idtipocuenta', @$puntosfidelizamanual["idpunto"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td>'));
	
		// Inicio Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" class="form_cabecera">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($_REQUEST['titulo'].'</td></tr>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		if($_REQUEST['action'] == 'Modificar'){
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizarpuntosfidelizacionmanual'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">Nro. Tarjeta</td><td colspan="2" class="form_texto">: '));
			//$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta','Nro TARJETA  </td><td colspan="2" class="form_texto">: ', trim(@$puntosfidelizamanual["tarjeta"]),'', 50, 13,'',array("readonly")));
		    $form->addElement(FORM_GROUP_MAIN, new f2element_text('busquedatarjeta','', $_REQUEST['busquedatarjeta'],'', 30, 30,'',array('onkeypress="return soloNumeros(event)"', 'onfocus="getFechasIF();"')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_tarjeta.php', 'Buscar.busquedatarjeta','Buscar.itemdescripcion','tarjetas'".')">¿Necesita ayuda?'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('puntos','PUNTOS  </td><td colspan="2" class="form_texto">: ', trim(@$puntosfidelizamanual["puntos"]),'', 50, 13,'',array('')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		}else{
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecha','Fecha  </td><td colspan="2" class="form_texto">: ', date("d/m/Y", time()),'', 10, 13,'',array('readonly')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			//$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta','Nro TARJETA  </td><td colspan="2" class="form_texto">: ', '','', 10, 13,'',array('')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">Nro. Tarjeta</td><td colspan="2" class="form_texto">: '));
		    $form->addElement(FORM_GROUP_MAIN, new f2element_text('busquedatarjeta','', $_REQUEST['busquedatarjeta'],'', 30, 30,'',array('onkeypress="return soloNumeros(event)"', 'onfocus="getFechasIF();"')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_tarjeta.php', 'Buscar.busquedatarjeta','Buscar.itemdescripcion','tarjetas'".')">¿Necesita ayuda?'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('puntos','Puntos  </td><td colspan="2" class="form_texto">: ', '','', 10, 13,'',array('')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ticket','Nro. Ticket  </td><td colspan="2" class="form_texto">: ', '','', 10, 13,'',array('')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('caja','Caja  </td><td colspan="2" class="form_texto">: ', '','', 10, 13,'',array('')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('importe','Importe  </td><td colspan="2" class="form_texto">: ', '','', 10, 13,'',array('')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			
		}

		// UNA FILA COMO ESPACIO
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3"><HR></td></tr>'));
		
		// INICIO DE LA CELDA QUE CONTENDRA AL FORMULARIO TARJETAS	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2">'));
		// Fin Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
		//Fin Contenido TD 2
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-guardar" name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/gadd.png" align="right" />Guardar </button>'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button id="btn-regresar" name="action" type="button" onclick="regresar();"><img src="/sistemaweb/icons/greturn.png" align="right" />Regresar </button>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	
		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}
}

