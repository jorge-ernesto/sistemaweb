<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');

class TrabajadorTemplate extends Template {
//METODO QUE DEVUELVE EL TITULO
	function titulo(){
		$titulo = '<h2 align="center" style="color:#336699"><b>TRABAJADOR</b></h2>';
		return $titulo;
	}
//METODO QUE RETORNA UN MENSAJE DE ERROR
	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}
//METODO PARA GENERAR UN REPORTE
	function TemplateReportePDF($reporte_array) {
		//print_r($reporte_array);		
		//$datos = array();
		echo "entro al reporte";
		$Cabecera = array( 
		    "ch_codigo_trabajador"		=> "CODIGO",
		    "nom"						=> "NOMBRES",
			"ch_apellido_paterno"		=> "APELLIDO PAT.",
			"ch_apellido_materno"		=> "APELLIDO MAT.",
			"ch_sexo"					=> "SEXO",
			"ch_direccion"				=> "DIRECCION",
			"ch_telefono1"				=> "TELEFONO",
			"ch_documento_identidad"	=> "DNI",
			"dt_fecha_nacimiento"		=> "FECHA NAC.");
		echo "entro al reporte 2";
	
//$Totales_new = array_merge_recursive($Totales, $Totales2);
//print_r($Totales_new);
		$fontsize = 7;

		$reporte = new CReportes2();
		echo "entro al reporte 3";
		$reporte->SetMargins(5, 5, 5);
		$reporte->SetFont("courier", "", $fontsize);
		$reporte->definirCabecera(2, "L", "Reporte de Trabajadores");
		$reporte->definirCabecera(2, "R", "Pagina %p");
		$reporte->definirCabecera(4, "R", "");
		$reporte->definirColumna("ch_codigo_trabajador", $reporte->TIPO_TEXTO, 10, "L");//20
		$reporte->definirColumna("ch_apellido_paterno", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("ch_apellido_materno", $reporte->TIPO_TEXTO, 15, "L"); 
		$reporte->definirColumna("nom", $reporte->TIPO_TEXTO, 18, "L");//15
		
		$reporte->definirColumna("ch_sexo", $reporte->TIPO_TEXTO, 6, "L");
		$reporte->definirColumna("ch_documento_identidad", $reporte->TIPO_TEXTO, 8, "L");
		$reporte->definirColumna("ch_telefono1", $reporte->TIPO_TEXTO, 15, "L");
		 
		$reporte->definirColumna("dt_fecha_nacimiento", $reporte->TIPO_TEXTO, 11, "L");
		$reporte->definirColumna("ch_direccion", $reporte->TIPO_TEXTO, 40, "L");
		
		$reporte->definirCabeceraPredeterminada($Cabecera);

		echo "aun no agrega a pagina ";
		$reporte->AddPage();//Aqui cae el y no genera el reporte; observacion Daniel!!
		echo "nada ";

		foreach($reporte_array as $llave => $valores) {
			$reporte->nuevaFila($valores);
		}
		echo "nada 2 ";
		$reporte->Output("/sistemaweb/maestros/reportes2/pdf/reporte_trabajador.pdf", "F");
		//$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_ruc.pdf", "F");
		//segundo error, tampoco entra             /sistemaweb/maestros/reportes/pdf/reporte_trabajador.pdf
		echo "nada 3 ";
		return '<center><iframe src="/sistemaweb/maestros/reportes2/pdf/reporte_trabajador.pdf" width="1000"  height="500"></iframe>
		</center>';
	}

	function listado($registros){
    
		//$titulo_grid = "<h2 align='center style='color:#336699'><b>TRABAJADORES</b></h2>";

		$columnas = array('CÓDIGO','NOMBRE','APELLIDOS','SEXO','DIRECCIÓN','TELEFONO','DNI','FECHA NACIMIENTO', 'ESTADO');
    	$listado = '<div id="resultados_grid" class="grid" align="center"><br>
					<table>
					<caption class="grid_title">'.$titulo_grid.'</caption>
					<thead align="center" valign="center" >
					<tr class="grid_header">';

		for($i=0;$i<count($columnas);$i++) {
			$listado .= '<th class="grid_cabecera"> '.strtoupper($columnas[$i]).'</th>';
		}

    	$listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';
    	//DETALLE
		$a = 0;
    	foreach($registros as $reg) {
    		$listado .= '<tr>';
			$regCod = $reg["ch_codigo_trabajador"];
			
			$a++;
			$color = ($a%2==0?"grid_detalle_par":"grid_detalle_impar");

			for ($i=0; $i < count($columnas); $i++) {
				$listado .= '<td class="'.$color.'">'.$reg[$i].'</td>';
			}
			$listado .= '<td> <A href="control.php?rqst=MAESTROS.TRABAJADOR&task=TRABAJADOR&action=Modificar&registroid='.$regCod.'" target="control">
			<img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;';
			/*$listado .='<A href="control.php?rqst=MAESTROS.RUC&task=RUC&action=Eliminar&registroid='.$regCod.'" target="control">
			<img src="/sistemaweb/icons/delete.gif" align="middle" border="0"s/></A>&nbsp;';*/
			$listado .= '<A href="javascript:confirmarLink(\'Desea borrar al trabajador con codigo '.$regCod.'\',\'control.php?rqst=MAESTROS.TRABAJADOR&task=TRABAJADOR'.
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
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.TRABAJADOR'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TRABAJADOR'));
	//$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pp', $paginacion['pp']));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[parametro]','Nombre o Apellido :', '', espacios(2), 20, 18));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(3)));
	
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

	function formTrabajador($trab) {
		$form = new form2('<h4 align="center" style="color:#336699"><b> DATOS DE TRABAJADOR </b></h4>', 'form_trabajador', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_trab();"');

		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.TRABAJADOR'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TRABAJADOR'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('sexo', @$trab["ch_sexo"]));
		if($_REQUEST['action'] == 'Modificar'){
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));
		}
    
    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$trab["codigo"]));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table> <tr><td>'));

    // Inicio Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table cellspacing="2" cellpadding="2" border="0"> <tr><td colspan="2" align="center" class="form_td_title">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
	
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[codigo]','Codigo  </td><td>: ', trim(@$trab["ch_codigo_trabajador"]), '', 25, 10,'',($_REQUEST['action']=='Modificar'?array('readonly'):array())));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[nombre]','Primer Nombre  </td><td>: ', trim(@$trab["ch_nombre1"]),'', 25, 30));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
	     
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[nombre2]','Segundo Nombre  </td><td>: ',trim(@$trab["ch_nombre2"]),'', 25, 30));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[apepat]','Apellido Paterno  </td><td>: ', 

		trim(@$trab["ch_apellido_paterno"]),'', 25, 30));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('trab[apemat]','Apellido Materno  </td><td>: ', trim(@$trab["ch_apellido_materno"]),'', 25, 30));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_radio ('trab[sexo]','Sexo  </td><td>:  Masculino','M','','',(@$trab["ch_sexo"]=='M'?array('checked'):array()) ) );
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio ('trab[sexo]','Femenino', 'F','','',(@$trab["ch_sexo"]=='F'?array('checked'):array())));
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
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		$ch_tipo_contrato = trim(@$trab["ch_tipo_contrato"]);

		$form->addElement(FORM_GROUP_MAIN, new f2element_radio ('trab[s_estado_trabajador]','Estado  </td><td>:  Activo','0','','',( ( empty($ch_tipo_contrato) || $ch_tipo_contrato == '' || $ch_tipo_contrato == '0') ? array('checked') : array())));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio ('trab[s_estado_trabajador]','Inactivo', '1','','',($ch_tipo_contrato=='1'?array('checked'):array())));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

	// Fin Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td valign="top" width="25">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td valign="topl">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	//Fin Contenido TD 2
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(15)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}
}
