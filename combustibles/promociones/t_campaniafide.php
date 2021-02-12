<?php

class CampaniaFideTemplate extends Template {

  	function titulo() {
    		$titulo = '<div align="center"><h2>CAMPA&Ntilde;AS DE FIDELIZACI&Oacute;N</h2></div><hr>';
    		return $titulo;
  	}

  	function errorResultado($errormsg) {
    		return '<blink>'.$errormsg.'</blink>';
  	}

 	function listado($registros) {
		$contador =0;
    		$titulo_grid = "LISTADO DE CAMPA&Ntilde;AS";
   		$columnas = array('DESCRIPCIÓN','FEC. INICIO','FEC. FIN','DIAS VENCIMIENTO','OBJETIVO');
    		$listado = '<div id="resultados_grid" class="grid" align="center"><br><table><caption class="grid_title">'.$titulo_grid.'</caption><thead align="center" valign="center" ><tr class="grid_header">';
    
		for($i=0;$i<count($columnas);$i++) {
      			$listado .= '<td class="grid_cabecera" height="20"> '.strtoupper($columnas[$i]).'&nbsp;</td>';
    		}
		$listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';

    		foreach($registros as $reg){
			$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");	
			$listado .= '<tr >';		
			$listado .='<td class="'.$color.'">&nbsp;<a alt="Mostrar Campaña" title="Mostrar Campaña"  href="control.php?rqst=PROMOCIONES.CAMPANIAFIDE&task=CAMPANIAFIDE&action=MostrarCampania&idcampania='.$reg["id_campana"]. '" target="control">'.$reg["ch_campana_descripcion"].'&nbsp;</a></td>';
			$listado .='<td class="'.$color.'">'.$reg["dt_campana_fecha_inicio"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["dt_campana_fecha_fin"].'&nbsp;</td>';
			$listado .='<td class="'.$color.'">'.$reg["nu_dias_vencimiento"].'&nbsp;</td>';
			$listado .='<td width="250px" class="'.$color.'">'.$reg["ch_campana_objetivo"].'&nbsp;</td>';
      			$listado .= '</tr>';
     			$contador++;
    		}
    		$listado .= '</tbody></table></div>';
    		return $listado;
  	}

 	function formBuscar($paginacion) {
    		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
	    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.CAMPANIAFIDE'));
	    	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CAMPANIAFIDE'));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda','Busqueda  : ', ' ','', 40, 30,[],[]));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Nueva',espacios(3)));
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

	function formCampaniafide($campania,$listaTipoCuentas,$rqaction="", $dIni, $dFin) {
		$form = new form2('', 'form_campaniafide', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_campaniafide();"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.CAMPANIAFIDE'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CAMPANIAFIDE'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('idcampania', @$campania["idcampania"]));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));

		if($rqaction == 'Modificar') {
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizarcampania'));
		} else {
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
		}

		if($rqaction != 'MostrarCampania'){
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td>'));
    			// Inicio Contenido TD 1
    			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4" align="center" class="form_cabecera">'));
    			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($_REQUEST['titulo'].'</td></tr>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3">'));
    			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('campaniadescripcion','DESCRIPCI&Oacute;N *  </td><td colspan="3" class="form_texto">: ', trim(@$campania["campaniadescripcion"]),'', 50,50,Array(),array('onfocus="getFechasIF();"')));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			/*==CREACION DE CAMPO: ==*/
			
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">FECHA INICIO *</td>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
		          $form->addElement(FORM_GROUP_MAIN, new f2element_text("campaniafechaini", ": ", $dIni, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">FECHA FIN * </td>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
		          $form->addElement(FORM_GROUP_MAIN, new f2element_text("campaniafechafin", ": ", $dFin, '', 12, 10));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			
			/*
	    	if ($rqaction != 'Modificar') {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_text ('campaniafechaini','FECHA INICIO * </td><td colspan="2" class="form_texto">: ', 
				trim(@$campania["campaniafechaini"]),'', 20, 30,'',array('readonly')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td width="179"><a href="javascript:show_calendar('
				."'form_campaniafide.campaniafechaini'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; 
				z-index:1000;"></div>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	    	}
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('campaniafechafin','FECHA FIN * </td><td colspan="2" class="form_texto">: ', 
			trim(@$campania["campaniafechafin"]),'', 20, 30,'',array('readonly')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td width="179"><a href="javascript:show_calendar('
			."'form_campaniafide.campaniafechafin'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; 
			z-index:1000;"></div>'));  
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

	    	*/

			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('campaniadiasven','D&Iacute;AS VENCIMIENTO PTOS. *</td><td colspan="3" class="form_texto">: ', 
			trim(@$campania["campaniadiasven"]),'', 15, 10,[],array('onkeypress="return soloNumeros(event)"')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_textarea  ('campaniaobjetivo','OBJETIVO DE CAMPA&Ntilde;A </td>
			<td colspan="3" class="form_texto">:</td></tr><tr><td></td><td colspan="3">&nbsp;&nbsp;&nbsp;', 
			trim(@$campania["campaniaobjetivo"]),'', 50, 7,[],[]));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		    	if ($rqaction != 'Modificar') {
				/*==CREACION DE CAMPO: ==*/
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">TIPOS DE CLIENTES *</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" class="form_texto"> :</td></tr>'));
		
				foreach($listaTipoCuentas as $reg) {
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td>'));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td width="30px">&nbsp;&nbsp;&nbsp;'));	
					$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('campaniatiposcli[]','',$reg['id_tipo_cuenta'],'',array(),array()));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_texto">'.$reg['ch_tipo_descripcion'].'</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));		
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				}
    			}

			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('campaniarepeticiones','REPETICIONES PARA RETENCION *</td><td colspan="3" class="form_texto">: ', 
			trim(@$campania["campaniarepeticiones"]),'', 15, 10,[],array('onkeypress="return soloNumeros(event)"')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_textarea  ('slogan','SLOGAN DE LA CAMPA&Ntilde;A </td>
			<td colspan="3" class="form_texto">:</td></tr><tr><td></td><td colspan="3">&nbsp;&nbsp;&nbsp;', 
			trim(@$campania["slogan"]),'', 50, 7,[],[]));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">'));
			$checked = ((@$campania["saludacumple"]=="1")?Array("checked=\"checked\""):Array());
			$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('saludacumple','PREMIO POR CUMPLEA&Ntilde;OS </td>
			<td colspan="3" class="form_texto">:</td></tr><tr><td></td><td colspan="3">&nbsp;&nbsp;&nbsp;',"1",'',$checked,array()));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			// UNA FILA COMO ESPACIO
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4"><HR></td></tr>'));
		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2">'));
			// Fin Contenido TD 1
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
	 		//Fin Contenido TD 2
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(15)));
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td>'));    
		    	// Inicio Contenido TD 1
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4" align="center" class="form_cabecera">'));
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags($_REQUEST['titulo'].'</td></tr>'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3">'));
		    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">DESCRIPCIÓN </td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" class="form_texto">: &nbsp;<span class="form_valor_texto">'
			.trim(@$campania["campaniadescripcion"])));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">FECHA CREACIÓN</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" class="form_texto">: &nbsp;<span class="form_valor_texto">'
			.trim(@$campania["campaniafechacrea"])));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</span></td></tr>'));
	
			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">FECHA INICIO </td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" class="form_texto">: &nbsp;<span class="form_valor_texto">'
			.trim(@$campania["campaniafechaini"])));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">FECHA FIN </td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" class="form_texto">: &nbsp;<span class="form_valor_texto">'
			.trim(@$campania["campaniafechafin"])));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">DÍAS VENCIMIENTO PTOS. </td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" class="form_texto">: &nbsp;<span class="form_valor_texto">'
			.trim(@$campania["campaniadiasven"])));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	
			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td  class="form_texto">OBJETIVO DE CAMPAÑA </td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td  class="form_texto"> :</td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td>'));
		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td width="250px" colspan="3" class="form_texto"><span class="form_valor_texto">'.trim(@$campania["campaniaobjetivo"])));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
		
			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">TIPOS DE CLIENTES</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" class="form_texto"> :</td></tr>'));
		
			foreach($listaTipoCuentas as $reg){
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td>'));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td width="20px">&nbsp;&nbsp;&nbsp;'));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('- </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="form_valor_texto">'.$reg['ch_tipo_descripcion'].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>&nbsp;</td>'));		
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			}

			/*==CREACION DE CAMPO: ==*/
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_texto">REPETICIONES PARA RETENCION</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="3" class="form_texto">: &nbsp;<span class="form_valor_texto">'
			.trim(@$campania["campaniarepeticiones"])));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
			// UNA FILA COMO ESPACIO
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td></td></tr>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4"><HR></td></tr>'));
		
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2">'));
			// Fin Contenido TD 1
    			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
    			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr></table>'));
	 		//Fin Contenido TD 2
    			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30">'));
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(
	    '<script>
	      window.onload = function() {
	        parent.document.getElementById("campaniadescripcion").focus();
	      }
	    </script>'
	    ));
		    
		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  	}
}
