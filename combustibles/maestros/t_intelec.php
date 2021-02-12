<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');

class IntelecTemplate extends Template {
 //METODO QUE DEVUELVE EL TITULO
  function titulo(){
    $titulo = '<div align="center"><h2>INTERFACES ELECTRONICAS</h2></div><hr>';
    return $titulo;
  }
//METODO QUE RETORNA UN MENSAJE DE ERROR
  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }
//METODO PARA GENERAR UN REPORTE
  function TemplateReportePDF($reporte_array)
  {
		
	 		//print_r($reporte_array);		
	   	//$datos = array();
		echo "entro al reporte";
		$Cabecera = array( 
		    "id"  	=> "ID",
		    "dispositivo"  	=> "DISPOSITIVO",
			"tipo" => "TIPO",
			"sleep" => "SLEEP",
			"maxsleep"		=> "MAXSLEEP");
			echo "entro al reporte 2";
			
    //$Totales_new = array_merge_recursive($Totales, $Totales2);
    //print_r($Totales_new);
    	$fontsize = 7;

   	 $reporte = new CReportes2();
	echo "entro al reporte 3";
    	 $reporte->SetMargins(5, 5, 5);
    	 $reporte->SetFont("courier", "", $fontsize);
	 $reporte->definirCabecera(2, "L", "Reporte de Interfaces Electricas");
	 $reporte->definirCabecera(2, "R", "Pagina %p");
  	 $reporte->definirCabecera(4, "R", "");
	 $reporte->definirColumna("id", $reporte->TIPO_TEXTO, 8, "L");//20
     	 $reporte->definirColumna("dispositivo", $reporte->TIPO_TEXTO, 40, "L");
	 $reporte->definirColumna("tipo", $reporte->TIPO_TEXTO, 30, "L"); 
	 $reporte->definirColumna("sleep", $reporte->TIPO_TEXTO, 8, "L");//15
	
	 $reporte->definirColumna("maxsleep", $reporte->TIPO_TEXTO, 8, "L");
/*	 $reporte->definirColumna("ch_documento_identidad", $reporte->TIPO_TEXTO, 8, "L");
	 $reporte->definirColumna("ch_telefono1", $reporte->TIPO_TEXTO, 15, "L");
	 
     	 $reporte->definirColumna("dt_fecha_nacimiento", $reporte->TIPO_TEXTO, 11, "L");
$reporte->definirColumna("ch_direccion", $reporte->TIPO_TEXTO, 40, "L");*/
	
   	 $reporte->definirCabeceraPredeterminada($Cabecera);
  
    	 echo "aun no agrega a pagina ";
	$reporte->AddPage();//Aqui cae el y no genera el reporte; observacion Daniel!!
	echo "nada ";
print_r($reporte_array);
    foreach($reporte_array as $llave => $valores){
		$reporte->nuevaFila($valores);
    }
	echo "nada 2 ";
 	$reporte->Output("/sistemaweb/maestros/reportes2/pdf/reporte_interfaceselec.pdf", "F");
      //$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_ruc.pdf", "F");
	//segundo error, tampoco entra             /sistemaweb/maestros/reportes/pdf/reporte_trabajador.pdf
	echo "nada 3 ";
	return '<center><iframe src="/sistemaweb/maestros/reportes2/pdf/reporte_interfaceselec.pdf" width="1000"  height="500"></iframe>	
	</center>';
	
  }
	
	  
 
 	function listado($registros){
    //isset($_REQUEST["paglistado"])?$pagina=$_REQUEST["paglistado"]:$pagina=1;
    $titulo_grid = "INTERFACES ELECTRONICAS";
    //formulario de busqueda
    $columnas = array('CODIGO','DISPOSITIVO','TIPO','SLEEP','MAXSLEEP');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table>
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    
for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
    }
    $listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:30;">';
    //detalle
    foreach($registros as $reg){
      $listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $regCod = $reg["id"];
      //$listado .= '<td class="grid_columtitle">'.$cont.'</td>';
      for ($i=0; $i < count($columnas); $i++){
        //echo "";
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
      $listado .= '<td> <A href="control.php?rqst=MAESTROS.INTELEC&task=INTELEC&action=Modificar&registroid='.$regCod.'" target="control">
			<img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;';

	/* $listado .='<A href="control.php?rqst=MAESTROS.RUC&task=RUC&action=Eliminar&registroid='.$regCod.'" target="control">
		  <img src="/sistemaweb/icons/delete.gif" align="middle" border="0"s/></A>&nbsp;';*/

      $listado .= '<A href="javascript:confirmarLink_Intelec(\'Desea borrar la interfaz electronica con codigo '.$regCod.'\',\'control.php?rqst=MAESTROS.INTELEC&task=INTELEC'.
                  '&action=Eliminar&registroid='.$regCod.'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td><td>&nbsp;</td>';
      $listado .= '</tr>';
     //$cont += 1;
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
 function formBuscar($paginacion){
   //echo "ENTRO BUSCAR\n";
    $form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.INTELEC'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTELEC'));
    //$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pp', $paginacion['pp']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    //$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[parametro]','Nombre o Apellido :', '', espacios(2), 20, 18));
    //$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(3)));
	
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros_Intelec('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros_Intelec('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros_Intelec('".$paginacion['pp']."',this.value)")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros_Intelec('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros_Intelec('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros_Intelec(this.value,'".$paginacion['primera_pagina']."')")));
   
    return $form->getForm();
  }

   function formTrabajador($trab){
    $form = new form2('DATOS DE LA INTERFAZ', 'form_trabajador', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_Intelec();"');

    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.INTELEC'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTELEC'));
//    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
//    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
//	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('sexo', @$trab["ch_sexo"]));
	if($_REQUEST['action'] == 'Modificar'){
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));
	}
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$trab["id"]));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td bgcolor="#FFFFCD">'));
    
    // Inicio Contenido TD 1
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_td_title">'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('INTERFACES ELECTRONICAS  </td></tr><tr><td>'));
	
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
	
//    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[codigo]','Codigo  </td><td>: ', trim(@$trab["id"]), '', 25, 10,'',($_REQUEST['action']=='Modificar'?array('readonly'):array())));

//    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[dispositivo]','Dispositivo  </td><td>: ', trim(@$trab["dispositivo"]),'', 25, 30));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[tipo]','Tipo  </td><td>: ',trim(@$trab["tipo"]),'', 25, 30));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[sleep]','Sleep  </td><td>: ',trim(@$trab["sleep"]),'', 25, 30));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[maxsleep]','Maxsleep  </td><td>: ',trim(@$trab["maxsleep"]),'', 25, 30));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
/*
    
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[apepat]','Apellido Paterno  </td><td>: ', 

    trim(@$trab["ch_apellido_paterno"]),'', 25, 30));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[apemat]','Apellido Materno  </td><td>: ', trim(@$trab["ch_apellido_materno"]),'', 25, 30));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_radio ('trab[sexo]','Sexo  </td><td>:  Masculino','M','','',(@$trab["ch_sexo"]=='M'?array('checked'):array()) ) );
    $form->addElement(FORM_GROUP_MAIN, new f2element_radio ('trab[sexo]',
    'Femenino', 'F','','',(@$trab["ch_sexo"]=='F'?array('checked'):array())));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[direccion]','Direccion  </td><td>: ', trim(@$trab["ch_direccion"]),'', 50, 60));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[telefono]','Telefono  </td><td>: ', trim(@$trab["ch_telefono1"]),'', 25, 15));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[dni]','DNI  </td><td>: ', trim(@$trab["ch_documento_identidad"]),'', 25, 15));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fechaNac','Fecha de Nacimiento  </td><td>: ', 
	@$trab["dt_fecha_nacimiento"],'', 25, 15,'',array('readonly')));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_trabajador.fechaNac'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
	//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('');
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));*/
	   
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    // Fin Contenido TD 1
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="top" width="25">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="topl">'));
   
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    //Fin Contenido TD 2
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(15)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar_Intelec();')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }
  
 

}

