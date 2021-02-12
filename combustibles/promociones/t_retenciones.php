<?php

class RetencionesTemplate extends Template {

	//METODO QUE DEVUELVE EL TITULO
	function titulo(){
		$titulo = '<div align="center"><h2>RETENCIONES DE PUNTOS DE FIDELIZACION</h2></div><hr>';
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
			$listado .= '	<div id="resultados_grid" class="grid" align="center">
					<table width="80%">
					<caption ><hr></caption>
					<thead align="center" valign="center" >
					<tr class="grid_header">';

			$listado .='	<td class="grid_cabecera" rowspan="2">NUM. TARJETA</td>
					<td class="grid_cabecera" rowspan="2">NOMBRE</td>
					<td class="grid_cabecera" rowspan="2">PLACA</td>
					<td class="grid_cabecera" rowspan="2">FECHA Y HORA</td>
					<td class="grid_cabecera" rowspan="2">TIPO MOV.</td>
					<td class="grid_cabecera" colspan="4">REFERENCIA</td>
					<td class="grid_cabecera" rowspan="2">PUNTOS</td>
					<td class="grid_cabecera" rowspan="2">&nbsp;</td>
					</tr>';

			$listado .='	<tr class="grid_header">
					<td class="grid_cabecera">TD</td>
					<td class="grid_cabecera">CAJA</td>
					<td class="grid_cabecera">NUMERO</td>
					<td class="grid_cabecera">ITEM</td>					
					</tr>';
print_r($registros[0]);

			foreach($registros as $reg){
				$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");

				$listado .= '<tr id="listado_retenciones_tr_' . $reg['id_punto'] . '">';

				switch ($reg["nu_punto_tipomov"]) {
					case "1":
						$tipomov = "PUNTO";
						break;
					case "2":
						$tipomov = "CANJE";
						break;
					case "3":
						$tipomov = "VENCIMIENTO";
						break;
					case "4":
						$tipomov = "RETENCION";
						break;
					default:
						$tipomov = "?????";
				}

				$listado .='<td class="'.$color.'">'.$reg["nu_tarjeta_numero"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_tarjeta_descripcion"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_tarjeta_placa"].'&nbsp;</td>';

				$listado .='<td class="'.$color.'">'.$reg["dt_punto_fecha"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$tipomov.'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_trans_td"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_trans_caja"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_trans_numero"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["art_descbreve"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["nu_punto_puntaje"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'"><a target="control" alt="Liberar" href="control.php?rqst=PROMOCIONES.RETENCIONES&action=libera&id='.$reg["id_punto"].'"><img style="border: 0px" src="/sistemaweb/images/all.gif" alt="Liberar" /></a></td>';
				$listado .= '</tr>';

				$contador++;
			}
			$listado .= '</tbody></table></div>';
		}	
		return $listado;
	}
	
	// Solo Formularios y otros
	   
	function formBuscar(){
	
		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.RETENCIONES'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RETENCIONES'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table><tr><td width="40%">'));	
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fechainicio','Del  :', $_REQUEST['fechainicio'],'', 10, 5,'',array("readonly")));			
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td width="5%">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.fechainicio'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td width="40%">'));			
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fechafin','Al  : ', $_REQUEST['fechafin'],'', 10, 5,'',array("readonly")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td width="5%">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.fechafin'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv2" style="position:absolute; visibility:hidden;  z-index:1000;"></div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td width="10%" align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Consultar',espacios(0)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
				
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
		
		return $form->getForm();
	}

	function formPaginacion($paginacion,$filtro,$fechaini,$fechafin,$intListaPuntos){
		$form = new form2('', 'Paginacion', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.RETENCIONES'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RETENCIONES'));
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

	function formRetenciones($intListaPuntos){
		$form = new form2(' ', 'form_itemcanje', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
		// Inicio Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table width="100%" border="0" cellspacing="2" cellpadding="2">'));

		if($intListaPuntos>0){
		}
		else{
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="msg_informacion"><img src="/sistemaweb/icons/messagebox_info32x32.png" border="0">�No existe informaci�n para la consulta realizada!</td><tr>'));
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
			
		return $form->getForm();
	}
}

