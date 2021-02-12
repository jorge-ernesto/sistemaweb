<?php


class HorarioMultiTemplate extends Template {
 //METODO QUE DEVUELVE EL TITULO
  function titulo(){
    $titulo = '<div align="center"><h2>HORARIOS DE MULTIPLICACI�N</h2></div><hr>';
    return $titulo;
  }
//METODO QUE RETORNA UN MENSAJE DE ERROR
  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

	//LISTADO DE LOS HORARIOS
 	function listado($registros){
	$contador =0;
    $titulo_grid = "LISTADO DE HORARIOS";
    //formulario de busqueda
    $columnas = array('DESCRIPCI�N','CAMPA�A','FEC. CREACION','D�A MULTIPLICACI�N','HORARIO INICIO','HORARIO FIN','FACTOR MULTIPLICACI�N');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table>
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    
for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<td class="grid_cabecera" height="20"> '.strtoupper($columnas[$i]).'&nbsp;</td>';
    }
    $listado .= '</tr><tbody class="grid_body" style="height:250px;">';
    //detalle
    foreach($registros as $reg){
	$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");
	
	$listado .= '<tr >';		
	$listado .='<td class="'.$color.'">'.$reg["ch_horario_descripcion"].'&nbsp;</a></td>';
	$listado .='<td class="'.$color.'">'.$reg["ch_campana_descripcion"].'&nbsp;</td>';
	$listado .='<td class="'.$color.'">'.$reg["dt_horario_fecha_creacion"].'&nbsp;</td>';
	
	$listado .='<td class="'.$color.'">'.HorarioMultiTemplate::mostrarDia($reg["nu_horario_dia_multi"]).'&nbsp;</td>';
	
	$listado .='<td class="'.$color.'">'.($reg["nu_horario_hora_inicio"]<=9?"0".$reg["nu_horario_hora_inicio"]:
	$reg["nu_horario_hora_inicio"]).
	":".($reg["nu_horario_minuto_inicio"]<=9?"0".$reg["nu_horario_minuto_inicio"]:$reg["nu_horario_minuto_inicio"]).'&nbsp;</td>';
	$listado .='<td class="'.$color.'">'.($reg["nu_horario_hora_fin"]<=9?"0".$reg["nu_horario_hora_fin"]:
	$reg["nu_horario_hora_fin"]).
	":".($reg["nu_horario_minuto_fin"]<=9?"0".$reg["nu_horario_minuto_fin"]:$reg["nu_horario_minuto_fin"]).'&nbsp;</td>';
	$listado .='<td class="'.$color.'">'.$reg["nu_horario_factor_multi"].'&nbsp;</td>';

      $listado .= '<td class="'.$color.'"> <A href="control.php?rqst=PROMOCIONES.HORARIOMULTI&task=HORARIOMULTI&action=Modificar&idhorariomulti='.$reg["id_horario_multi"].'&idcampania='.$reg["id_campana"].'&horamultidesccampania='.$reg["ch_campana_descripcion"].'&horamultidescripcion='.$reg["ch_horario_descripcion"].'&horamultifechacrea='.$reg["dt_horario_fecha_creacion"].'&horamultidias='.$reg["nu_horario_dia_multi"].'&horamultihoraini='.$reg["nu_horario_hora_inicio"].'&horamultiminutoini='.$reg['nu_horario_minuto_inicio'].'&horamultihorafin='.$reg["nu_horario_hora_fin"].'&horamultiminutofin='.$reg['nu_horario_minuto_fin'].'&horamultifactor='.$reg['nu_horario_factor_multi'].'" target="control"><img alt="Editar Horario" title="Editar Horario" src="/sistemaweb/icons/kedit32x32.png" align="middle" border="0"/></A>&nbsp;</td>';
	
		$listado .= '<td class="'.$color.'" ><a href="javascript:confirmarLink(\'�Desea eliminar el horario '.
		$reg['ch_horario_descripcion'] .'?\',\'control.php?rqst=PROMOCIONES.HORARIOMULTI&task=HORARIOMULTI&action=Eliminar&idhorariomulti='.
		$reg["id_horario_multi"].'\',\'control\')"><img alt="Eliminar Horario" title="Eliminar Horario" src="/sistemaweb/icons/delete16x16.png" align="middle" 		
					border="0"/></a>&nbsp;</td>';	

      $listado .= '</tr>';
     $contador++;
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }


  // Solo Formularios y otros

 function formBuscar($paginacion){

    $form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.HORARIOMULTI'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'HORARIOMULTI'));
    //$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pp', $paginacion['pp']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda','Busqueda  : ', ' ','', 40, 30,'',array()));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Nuevo Horario',espacios(3)));
 
	
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


   function formHorarioMulti($horariomulti){

	$hora = array();
	$minuto	= array();
	$dias = array();
	
	//Almaccenando d�as
	$dias[1] = 'DOMINGO';
	$dias[2] = 'LUNES';
	$dias[3] = 'MARTES';
	$dias[4] = 'MIERCOLES';
	$dias[5] = 'JUEVES';
	$dias[6] = 'VIERNES';
	$dias[7] = 'S�BADO';
	
	
	//Almacenando horas
	for($i=0;$i<24;$i++){
		if($i<10){
			$hora['0'.$i]='0'.$i;
		}
		else{
			$hora[$i]=$i;
		}
	}	
	// Almacenando minutos
	for($i=0;$i<60;$i++){
		if($i<10){
			$minuto['0'.$i]='0'.$i;
		}
		else{
			$minuto[$i]=$i;
		}
	}
	
	
	
				  
			  			  
	
    $form = new form2('', 'form_horariomulti', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_horariomulti();"');

    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.HORARIOMULTI'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'HORARIOMULTI'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idhorariomulti', @$horariomulti["idhorariomulti"]));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idcampania', @$horariomulti["idcampania"]));	
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));

	if($_REQUEST['action'] == 'Modificar'){
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizarhorariomulti'));
	}else{
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
	}

	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td>'));
    
    // Inicio Contenido TD 1
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center" class="form_cabecera">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($_REQUEST['titulo'].'</td></tr>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
	/*==CREACION DE CAMPO: ==*/
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('horamultidesccampania','CAMPA�A *  </td><td colspan="2" class="form_texto">: ', 
	trim(@$horariomulti["desccampania"]),'', 50, 13,'',array("readonly")));
	
	 $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_campaniafide.php','form_horariomulti.idcampania','form_horariomulti.horamultidesccampania','campanias'".')">�Necesita ayuda?'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
	/*==CREACION DE CAMPO: ==*/
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('horamultidescripcion','DESCRIPCI�N * </td><td colspan="2" class="form_texto">: ', 
	trim(@$horariomulti["descripcion"]),'', 50, 50,'',array()));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
	/*==CREACION DE CAMPO: ==*/
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
	if($_REQUEST['action'] == 'Modificar'){
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('FECHA CREACI�N </td><td class="form_texto" colspan="2">: &nbsp;<span class="form_valor_texto">'
		.trim(@$horariomulti["fechacrea"])));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('horamultifechacrea', trim(@$horariomulti["fechacrea"])));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</span></td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
	}
		
		/*==CREACION DE CAMPO: ==*/
    /*$form->addElement(FORM_GROUP_MAIN, new f2element_text ('horamultidias','DIA MULTIPLICACI�N * </td><td colspan="2" class="form_texto">: ', 
	trim(@$horariomulti["dias"]),'', 15, 10,'',array('onkeypress="return soloNumeros(event)"')));*/
	
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('horamultidias','DIA MULTIPLICACI�N * </td><td width="90" class="form_texto" colspan="2">: ', trim(@$horariomulti["dias"]), $dias));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>')); 
			
	/*==CREACION DE CAMPO: ==*/
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('horamultihoraini','HORARIO INICIO * </td><td width="90" class="form_texto">:&nbsp;&nbsp;HORA:', trim(@$horariomulti["horaini"]), $hora));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
	
	/*==CREACION DE CAMPO: ==*/
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('horamultiminutoini','<td class="form_texto">MINUTO:', 
	trim(@$horariomulti["minutoini"]), $minuto));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
	
		/*==CREACION DE CAMPO: ==*/
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('horamultihorafin','HORARIO FIN * </td><td width="90" class="form_texto">:&nbsp;&nbsp;HORA:', trim(@$horariomulti["horafin"]), $hora));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
	
	/*==CREACION DE CAMPO: ==*/
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('horamultiminutofin','<td class="form_texto">MINUTO:', 
	trim(@$horariomulti["minutofin"]), $minuto));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
	
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('horamultifactor','FACTOR MULTIPLICACI�N *</td><td colspan="2" class="form_texto">: ', 
	trim(@$horariomulti["factor"]),'', 15, 7,'',array('onkeypress="return soloNumerosDec(event)"')));
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
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(15)));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }
	
	//M�TODOS AUXILIARES
	function mostrarDia($dia){
	$dianombre='';
	if($dia=='1') $dianombre='DOMINGO';
	else if($dia==2) $dianombre='LUNES';
	else if($dia==3) $dianombre='MARTES';
	else if($dia==4) $dianombre='MIERCOLES';
	else if($dia==5) $dianombre='JUEVES';
	else if($dia==6) $dianombre='VIERNES';
	else{
		if($dia==7) $dianombre='S�BADO';	
	}
	return $dianombre;
	}


 
 
}

