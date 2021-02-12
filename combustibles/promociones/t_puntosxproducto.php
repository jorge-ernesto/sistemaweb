<?php

class PuntosxProductoTemplate extends Template {
	//METODO QUE DEVUELVE EL TITULO
	function titulo(){
		$titulo = '<div align="center"><h2>Puntos por Producto</h2></div><hr>';
		return $titulo;
	}
	//METODO QUE RETORNA UN MENSAJE DE ERROR
	function errorResultado($errormsg){
		return '<blink>'.$errormsg.'</blink>';
	}
	
	//LISTADO DE LOS PUNTOS POR PRODUCTO
	function listado($registros){
		$contador =0;
		$titulo_grid = "LISTADO DE PUNTOS POR PRODUCTO";
		//formulario de busqueda
		$columnas = array('CAMPANIA','CODIGO ARTICULO', 'ARTICULO','PUNTOS SOL','PUNTOS UNIDAD');
		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
				<table>
				<caption class="grid_title">'.$titulo_grid.'</caption>
				<thead align="center" valign="center" >
				<tr class="grid_header">';

		for($i=0;$i<count($columnas);$i++){
			$listado .= '<td class="grid_cabecera" height="20"> '.strtoupper($columnas[$i]).'&nbsp;</td>';
		}

		$listado .= '</tr><tbody class="grid_body" style="height:250px;">';

		//DETALLE
		foreach($registros as $reg){
			$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");
			
			$listado .= '<tr >';
			//$listado .='<td class="'.$color.'">'.$reg["codigocampania"].'&nbsp;</td>';		
			$listado .='<td class="'.$color.'">'.$reg["descripcioncampania"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["codigoarticulo"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["descripcionarticulo"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["puntossol"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["puntosunidad"].'&nbsp;</td>';			
			$listado .= '<td class="'.$color.'"> <a href="control.php?rqst=PROMOCIONES.PUNTOSXPRODUCTO&task=PUNTOSXPRODUCTO&action=Modificar&idcampania='.$reg["codigocampania"].'&descampania='.$reg["descripcioncampania"].'&idarticulo='.$reg["codigoarticulo"].'&desarticulo='.$reg["descripcionarticulo"].'&puntossol='.$reg["puntossol"].'&puntosunidad='.$reg["puntosunidad"].'" target="control"><img alt="Editar Puntos x Producto" title="Editar Puntos x Producto" src="/sistemaweb/icons/kedit32x32.png" align="middle" border="0"/></a>&nbsp;</td>';
			
			$listado .= '<td class="'.$color.'" ><a href="javascript:confirmarLink(\'�Desea eliminar el registro '.$reg["codigocampania"].'&idarticulo='.$reg["codigoarticulo"].'?\',\'control.php?rqst=PROMOCIONES.PUNTOSXPRODUCTO&task=PUNTOSXPRODUCTO&action=Eliminar&idcampania='.$reg["codigocampania"].'&idarticulo='.$reg["codigoarticulo"].'\',\'control\')"><img alt="Eliminar Puntos x Producto" title="Eliminar Puntos x Producto" src="/sistemaweb/icons/delete16x16.png" align="middle" 	border="0"/></a>&nbsp;</td>';	
		
			$listado .= '</tr>';
			$contador++;
		}
	$listado .= '</tbody></table></div>';
	return $listado;
	}


  // Solo Formularios y otros

	function formBuscar($paginacion){
	
		echo "ENTRO BUSCAR\n";
		$Tipo = array(	'C'=>'Campania',
				'A' => 'Articulo');

		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.PUNTOSXPRODUCTO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PUNTOSXPRODUCTO'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('tipobusqueda','Buscar por : ',' ', $Tipo));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda','Busqueda  : ', ' ','', 40, 30,'',array()));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Nuevo',espacios(3)));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));
		
		return $form->getForm();
	}


	function formPuntosxProducto($puntosxproducto){
		
		$form = new form2('', 'form_PuntosxProducto', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_PuntosxProducto();"');
		
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.PUNTOSXPRODUCTO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PUNTOSXPRODUCTO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idcampania', @$puntosxproducto["idcampania"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idarticulo', @$puntosxproducto["idarticulo"]));	
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));
	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td>'));
	
		// Inicio Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" class="form_cabecera">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($_REQUEST['titulo'].'</td></tr>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		if($_REQUEST['action'] == 'Modificar'){
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizarPuntosxProducto'));
			//CREACION DE CAMPO CAMPAÑA:
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('descampania','CAMPA&Ntilde;A *  </td><td colspan="2" class="form_texto">: ', 
			trim(@$puntosxproducto["descampania"]),'', 50, 13,'',array("readonly")));
			
			//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_campaniafide.php','form_PuntosxProducto.idcampania','form_PuntosxProducto.descampania','campanias'".')">�Necesita ayuda?'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		
			//CREACION DE CAMPO ARTICULO:
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('desarticulo','ARTICULO *  </td><td colspan="2" class="form_texto">: ', 
			trim(@$puntosxproducto["desarticulo"]),'', 50, 13,'',array("readonly")));
			
			//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda.php','form_PuntosxProducto.idarticulo','form_PuntosxProducto.desarticulo','articulos'".')">�Necesita ayuda?'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		}else{
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
			//CREACION DE CAMPO CAMPAÑA:
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('descampania','CAMPA�A *  </td><td colspan="2" class="form_texto">: ', 
			trim(@$puntosxproducto["descampania"]),'', 50, 13,'',array("readonly")));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_campaniafide.php','form_PuntosxProducto.idcampania','form_PuntosxProducto.descampania','campanias'".')">�Necesita ayuda?'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		
			//CREACION DE CAMPO ARTICULO:
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('desarticulo','ARTICULO *  </td><td colspan="2" class="form_texto">: ', 
			trim(@$puntosxproducto["desarticulo"]),'', 50, 13,'',array("readonly")));
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda.php','form_PuntosxProducto.idarticulo','form_PuntosxProducto.desarticulo','articulos'".')">�Necesita ayuda?'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		}
/*
		//CREACION DE CAMPO CAMPAÑA:
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('descampania','CAMPA�A *  </td><td colspan="2" class="form_texto">: ', 
		trim(@$puntosxproducto["descampania"]),'', 50, 13,'',array("readonly")));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_campaniafide.php','form_PuntosxProducto.idcampania','form_PuntosxProducto.descampania','campanias'".')">�Necesita ayuda?'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
		//CREACION DE CAMPO ARTICULO:
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('desarticulo','ARTICULO *  </td><td colspan="2" class="form_texto">: ', 
		trim(@$puntosxproducto["desarticulo"]),'', 50, 13,'',array("readonly")));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda.php','form_PuntosxProducto.idarticulo','form_PuntosxProducto.desarticulo','articulos'".')">�Necesita ayuda?'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
*/
		//CREACION CAMPO PUNTOS SOLES
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('puntossol','PUNTOS SOL *</td><td colspan="2" class="form_texto">: ', trim(@$puntosxproducto["puntossol"]),'', 15, 7,'',array('onkeypress="return soloNumerosDec(event)"')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		//CREACION CAMPO PUNTOS UNIDADES
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('puntosunidad','PUNTOS UNIDAD *</td><td colspan="2" class="form_texto">: ', trim(@$puntosxproducto["puntosunidad"]),'', 15, 7,'',array('onkeypress="return soloNumerosDec(event)"')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		// UNA FILA COMO ESPACIO
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3"><HR></td></tr>'));
		
		// INICIO DE LA CELDA QUE CONTENDRA AL FORMULARIO TARJETAS	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2">'));
		// Fin Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
		//Fin Contenido TD 2
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(2)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	
		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}
	
}

