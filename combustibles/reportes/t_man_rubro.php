<?php
class man_rubro_Template extends Template {

	function titulo() {
		return '<h2 align="center"><b>RUBROS</b></h2>';
	}

	function formPag($paginacion, $vec) {

		$fecha 	  = $vec[0];
		$fecha2	  = $vec[1];

		if($fecha == '' || $fecha2 == ''){

			$fecha = date(d."/".m."/".Y); 
			$fecha2 = date(d."/".m."/".Y);

		}

		$rubros = man_rubro_Model::Rubros();
		$rubros['TODOS'] = "Todos los Rubros";

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.MAN_RUBRO"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Seleccionar Rubros: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("rubro", "Seleccionar Rubros: ", "", $rubros, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center"><button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
	
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

	 	$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$fecha."','".$fecha2."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$fecha."','".$fecha2."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."',this.value,'".$fecha."','".$fecha2."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$fecha."','".$fecha2."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$fecha."','".$fecha2."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistrosFecha(this.value,'".$paginacion['primera_pagina']."','".$fecha."','".$fecha2."')")));

		return $form->getForm();
    	}


	function formSearch($paginacion){

		$rubros = man_rubro_Model::Rubros();
		$rubros['TODOS'] = "Todos los Rubros";

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.MAN_RUBRO"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Seleccionar Rubros: </td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("rubro", "Seleccionar Rubros: ", "", $rubros, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td colspan="2" align="center"><button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));

		//PAGINADOR

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
 
		if ($paginacion['paginas'] == 'P'){
			$paginacion['paginas'] = '0';
		}

 		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
	    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));


		return $form->getForm();
	}

	function formAgregar($fila, $paginacion, $vec) {

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.MAN_RUBRO"));

			if($_REQUEST['action'] == 'Modificar'){

				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));

			}
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));

				if($_REQUEST['action'] == 'Agregar'){

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Cod rubro:</td><td style='text-align:left;'>"));
				   	
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','cod_rubro', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false,'onkeypress="return validar(event,2)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Descripcion: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','descripcion_id', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 30, 30, false,'onkeypress="return validar(event,1)"'));	
                                        
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Descripcion Breve: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','desc_breve', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 20, 20, false,'onkeypress="return validar(event,1)"'));					
					
                                        
                                        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Tipo item: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','tipo_item', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false,'onkeypress="return validar(event,1)"'));					
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
                                        
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

				}else{

				
                                        
                                        
                                        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Cod rubro:</td><td style='text-align:left;'>"));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_text('','cod_rubro', $fila['ch_codigo_rubro'],'</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, ($_REQUEST['action']=='Modificar'?array('readonly'):array()),'onkeypress="return validar(event,2)"'));
					
                                        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Descripcion: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','descripcion_id', $fila['ch_descripcion'],'</td></tr><tr><td colspan="2" style="text-align:center;">', '', 30, 30, false,'onkeypress="return validar(event,1)"'));	
                                        
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Descripcion Breve: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','desc_breve', $fila['ch_descripcion_breve'],'</td></tr><tr><td colspan="2" style="text-align:center;">', '', 20, 20, false,'onkeypress="return validar(event,1)"'));					
					
                                        
                                        $form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Tipo item: </td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','tipo_item', $fila['ch_tipo_item'],'</td></tr><tr><td colspan="2" style="text-align:center;">', '', 6, 6, false,'onkeypress="return validar(event,1)"'));					
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
                                        
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

				}

		return $form->getForm();
	}

	function resultadosBusqueda($resultados) {

		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">COD RUBRO</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION</th>';
		$result .= '<th class="grid_cabecera">DESCRIPCION BRV</th>';
		$result .= '<th class="grid_cabecera">COD ITEM</th>';
		
		$result .= '<th colspan="2" class="grid_cabecera"></th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['ch_codigo_rubro']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['ch_descripcion']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['ch_descripcion_breve']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['ch_tipo_item']) . '</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.MAN_RUBRO&action=Modificar&ncuenta='.($a['ch_codigo_rubro']).'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar el Rubro '. htmlentities($a['ch_codigo_rubro']).' ?\',\'control.php?rqst=REPORTES.MAN_RUBRO&action=Eliminar&ncuenta='.($a['ch_codigo_rubro']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
			$result .= '</tr>';

		}

		$result .= '</table>';
		return $result;
	}
}
