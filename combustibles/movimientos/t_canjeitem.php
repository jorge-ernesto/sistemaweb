<?php


class CanjeitemTemplate extends Template {
 //METODO QUE DEVUELVE EL TITULO
  function titulo(){
echo " titulo de TargpromocionTemplate \n"; 
    $titulo = '<div align="center"><h2>PRODUCTOS DE CANJE</h2></div><hr>';
    return $titulo;
  }
//METODO QUE RETORNA UN MENSAJE DE ERROR
  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

	//LISTADO DE LOS PRODUCTOS EN CANJE
 	function listado($registros){
	$contador =0;
    $titulo_grid = "LISTADO DE PRODUCTOS";
    //formulario de busqueda
    $columnas = array('COD. ARTICULO','DESCRIPCIÓN','FEC. CREACION','FEC. VENCIMIENTO','OBSERVACION','PUNTOS DE CANJE');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table>
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    
for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<td class="grid_cabecera" height="20"> '.strtoupper($columnas[$i]).'&nbsp;</td>';
    }
    $listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';
    //detalle
    foreach($registros as $reg){
	$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");
	
	$listado .= '<tr >';		
	$listado .='<td class="'.$color.'">'.$reg["art_codigo"].'&nbsp;</a></td>';
	$listado .='<td class="'.$color.'">'.$reg["ch_item_descripcion"].'&nbsp;</td>';
	$listado .='<td class="'.$color.'">'.$reg["dt_item_fecha_creacion"].'&nbsp;</td>';
	$listado .='<td class="'.$color.'">'.$reg["dt_item_fecha_vencimiento"].'&nbsp;</td>';
	$listado .='<td class="'.$color.'">'.$reg["ch_item_observacion"].'&nbsp;</td>';
	$listado .='<td class="'.$color.'">'.$reg["nu_item_puntos"].'&nbsp;</td>';

      
      $listado .= '<td class="'.$color.'"> <A href="control.php?rqst=MOVIMIENTOS.CANJEITEM&task=CANJEITEM&action=Modificar&itemid='.$reg["id_item"].'&articulocod='.$reg["art_codigo"].'&itemdescripcion='.$reg["ch_item_descripcion"].'&itemfechacre='.$reg["dt_item_fecha_creacion"].'&itemfechaven='.$reg["dt_item_fecha_vencimiento"].'&itemobservacion='.$reg["ch_item_observacion"].'&itempuntos='.$reg['nu_item_puntos'].'" target="control"><img alt="Editar Producto" src="/sistemaweb/icons/kedit32x32.png" align="middle" border="0"/></A>&nbsp;</td>';
	


		$listado .= '<td class="'.$color.'" ><a href="javascript:confirmarLink(\'¿Desea eliminar el producto Nº '.
		$reg['art_codigo'].' - '.$reg['ch_item_descripcion'] .'?\',\'control.php?rqst=MOVIMIENTOS.CANJEITEM&task=CANJEITEM&action=Eliminar&itemid='.
		$reg["id_item"].'\',\'control\')"><img alt="Eliminar Producto" src="/sistemaweb/icons/delete22x22.png" align="middle" 		
					border="0"/></a></td>';	

      $listado .= '</tr>';
     $contador++;
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }


  // Solo Formularios y otros

 function formBuscar($paginacion){
   echo "ENTRO BUSCAR\n";
    $Tipo = array('T' => 'Tarjeta',
                  'C' => 'Cuenta',
		  'P' => 'Persona o Empresa');



    $form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.CANJEITEM'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CANJEITEM'));
    //$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pp', $paginacion['pp']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda','Busqueda  : ', ' ','', 40, 30,'',array()));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Nuevo Producto',espacios(3)));
 
	
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


   function formProductocanje($item){
   echo ' entro a formProducto';
	$SiNo = array('1' => 'SI',
			      		  '2' => 'NO');
	
    $form = new form2('', 'form_itemcanje', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_productocanje();"');

    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.CANJEITEM'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CANJEITEM'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('iditem', @$item["id_item"]));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('codarticulo', @$item["art_codigo"]));	
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));

	if($_REQUEST['action'] == 'Modificar'){
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizaritem'));
	}else{
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
	}

	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td>'));
    
    // Inicio Contenido TD 1
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" class="form_cabecera">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($_REQUEST['titulo'].'</td></tr>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('itemarticulo','ARTÍCULO *  </td><td colspan="2" class="form_texto">: ', 
	trim(@$item["art_codigo"]),'', 25, 13,'',array()));
	
	 $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda.php','form_itemcanje.itemarticulo','form_itemcanje.itemdescripcion','articulos'".')">¿Necesita ayuda?'));
	//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="/sistemaweb/icons/search20x20.gif">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('itemdescripcion','DESCRIPCIÓN * </td><td colspan="2" class="form_texto">: ', 
	trim(@$item["ch_item_descripcion"]),'', 50, 40,'',array()));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));
	
	if($_REQUEST['action'] == 'Modificar'){
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('FECHA CREACIÓN </td><td class="form_texto">: &nbsp;<span class="form_valor_texto">'
		.trim(@$item["dt_item_fecha_creacion"])));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('itemfechacrea', trim(@$item["dt_item_fecha_creacion"])));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</span></td></tr><tr><td class="form_texto">'));
	}
		
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('itemfechaven','FECHA VENCIMIENTO  </td><td class="form_texto">: ', 
	trim(@$item["dt_item_fecha_vencimiento"]),'', 20, 30,'',array('readonly')));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td width="179"><a href="javascript:show_calendar('
	."'form_itemcanje.itemfechaven'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; 
	z-index:1000;"></div>'));    
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));
	
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('itempuntos','PUNTAJE *</td><td colspan="2" class="form_texto">: ', 
	trim(@$item["nu_item_puntos"]),'', 15, 15,'',array('onkeypress="return soloNumeros(event)"')));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td class="form_texto">'));
	 $form->addElement(FORM_GROUP_MAIN, new f2element_textarea  ('itemobservacion','OBSERVACIÓN </td><td colspan="2" class="form_texto">:</td></tr>	
	 <tr><td></td><td colspan="2">&nbsp;&nbsp;&nbsp;', 
	trim(@$item["ch_item_observacion"]),'', 50, 7,'',array()));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3">'));
	


	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	// UNA FILA COMO ESPACIO
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td></tr>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3"><HR></td></tr>'));
	
	// INICIO DE LA CELDA QUE CONTENDRA AL FORMULARIO TARJETAS	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2">'));
	// Fin Contenido TD 1
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
    /*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="top" width="25">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="topl"></td>'));*/
   
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
    //Fin Contenido TD 2
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(15)));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }



 
 
}

