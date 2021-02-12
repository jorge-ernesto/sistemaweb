<?php
class MovpuntosTemplate extends Template {
 //METODO QUE DEVUELVE EL TITULO
  function titulo(){
    $titulo = '<div align="center"><h2>PUNTAJE Y DETALLE DE MOVIMIENTO</h2></div><hr>';
    return $titulo;
  }
//METODO QUE RETORNA UN MENSAJE DE ERROR
  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

	//LISTADO DE LOS PRODUCTOS EN CANJE
 	function listado($registros){
	$contador =0;
	$listado='';
	if(count($registros)>0) {

	//CREAREMOS LA PAGINACION - DPC 09/05/09
	
	//==========================================


	//formulario de busqueda
    $listado .= '<div id="resultados_grid" class="grid" align="center">
                      <table width="50%">
                      <caption ><hr></caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    $listado .='<td class="grid_cabecera" rowspan="2">FECHA</td>
				<td class="grid_cabecera" rowspan="2">TIPO MOV.</td>
				<td class="grid_cabecera" colspan="4">REFERENCIA</td>
				<td class="grid_cabecera" rowspan="2">PUNTOS</td>		
				</tr>';
	$listado .='<tr class="grid_header">
						<td class="grid_cabecera">TD</td>
						<td class="grid_cabecera">CAJA</td>
						<td class="grid_cabecera">NUMERO</td>
						<td class="grid_cabecera">ITEM</td>					
						</tr>';
				
    //detalle
		foreach($registros as $reg){
			$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");
		
			$listado .= '<tr>';		
			$listado .='<td class="'.$color.'">'.$reg["dt_punto_fecha"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.($reg["nu_punto_tipomov"]=='1'?'PUNTO':'CANJE').'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["ch_trans_td"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["ch_trans_caja"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["ch_trans_numero"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["ch_trans_codigo"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["nu_punto_puntaje"].'&nbsp;</td>';
			$listado .= '</tr>';
			
			$contador++;
		
		}
    $listado .= '</tbody></table></div>';
	}
	
    return $listado;
  }


  // Solo Formularios y otros

 function formBuscar(){

    $form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control',
	'onSubmit="return validar_busqueda_movimientopuntos()"');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.MOVPUNTOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'MOVPUNTOS'));
   	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table><tr><td colspan="5">'));	
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busquedatarjeta','Tarjeta  : ', $_REQUEST['busquedatarjeta'],'', 30, 30,'',array('onkeypress="return soloNumeros(event)"')));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" 
	style="cursor:hand" width="16" height="15" 
	onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_tarjeta.php',
	'Buscar.busquedatarjeta','Buscar.itemdescripcion','tarjetas'".')">¿Necesita ayuda?'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td></td></tr>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr valign="top"><td width="45%">'));

	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fechainicio','Del  : ', $_REQUEST['fechainicio'],'', 20, 30,'',array("readonly")));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td width="10%">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('
	."'Buscar.fechainicio'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; 
	z-index:1000;"></div>'));
	  $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td width="42%">'));
	   
	  $form->addElement(FORM_GROUP_MAIN, new f2element_text ('fechafin','Al  : ', $_REQUEST['fechafin'],'', 20, 30,'',array("readonly")));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td width="10%">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('
	."'Buscar.fechafin'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv2" style="position:absolute; visibility:hidden; 
	z-index:1000;"></div>'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td width="15%" align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Consultar',espacios(0)));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
	
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
	
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    
    //$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    return $form->getForm();
  }

	function formPaginacion($paginacion,$filtro,$fechaini,$fechafin,$intListaPuntos){
	 $form = new form2('', 'Paginacion', FORM_METHOD_GET, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.MOVPUNTOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'MOVPUNTOS'));
    //$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pp', $paginacion['pp']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
	if($intListaPuntos>0){
 	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$filtro."','".$fechaini."','".$fechafin."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$filtro."','".$fechaini."','".$fechafin."')")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value,'".$filtro."','".$fechaini."','".$fechafin."')")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$filtro."','".$fechaini."','".$fechafin."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$filtro."','".$fechaini."','".$fechafin."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."','".$filtro."','".$fechaini."','".$fechafin."')")));
}
 $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));

    return $form->getForm();
	}

   function formMovimientopuntos($objCuenta,$objTarjeta,$intListaPuntos){
	
    $form = new form2(' ', 'form_itemcanje', FORM_METHOD_POST, 'control.php', '', 'control','');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
	

	
	//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td >'));
    
    // Inicio Contenido TD 1
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table width="100%" border="0" cellspacing="2" cellpadding="2">'));
	if($intListaPuntos>0){
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" class="form_cabecera">DATOS DE CUENTA</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" class="form_cabecera">DATOS DE TARJETA</td></tr>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">Nº CUENTA</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$objCuenta["nu_cuenta_numero"]).'</span></td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">Nº TARJETA</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$objTarjeta["nu_tarjeta_numero"]).'</span></td></tr>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">TITULAR</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$objCuenta["ch_cuenta_nombres"]).' '
	.trim(@$objCuenta["ch_cuenta_apellidos"]).'</span></td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">NOMBRE</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$objTarjeta["ch_tarjeta_descripcion"]).'</span></td></tr>'));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" class="form_texto">DNI :&nbsp;<span class="form_valor_texto">'
	.trim(@$objCuenta["ch_cuenta_dni"]).'</span>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp; RUC :&nbsp;<span class="form_valor_texto">'.trim(@$objCuenta["ch_cuenta_ruc"]).'</span></td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">PLACA</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$objTarjeta["ch_tarjeta_placa"]).'</span></td></tr>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">DIRECCIÓN</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$objCuenta["ch_cuenta_direccion"]).'</span></td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td></tr>'));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">TELÉFONO</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">:&nbsp;<span class="form_valor_texto">'.trim(@$objCuenta["ch_cuenta_telefono1"]).'</span></td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td></tr>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="5"><hr></td><tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" class="form_texto"><span class="form_pie">PUNTOS CUENTA :&nbsp;'
	.trim(@$objCuenta["nu_cuenta_puntos"])));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</span></td><td>&nbsp;</td></tr>'));
	
	}
	else{
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="msg_informacion"><img src="/sistemaweb/icons/messagebox_info32x32.png" border="0">¡No existe información para la consulta realizada!</td><tr>'));
	}
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
	//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	
    
    return $form->getForm();
  }



 
 
}

