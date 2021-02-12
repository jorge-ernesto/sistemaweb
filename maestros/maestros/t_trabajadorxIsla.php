<?php

class TrabajadorxIslaTemplate extends Template {

	function titulo() {
		$titulo = '<div align="center"><h2>TRABAJADOR POR ISLA</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}

	function listado($registros) {

		$titulo_grid = "TRABAJADOR POR ISLA";
		$columnas = array('CODIGO SUCURSAL','FECHA','TURNO','LADO','CODIGO TRABAJADOR','NOMBRE TRABAJADOR','TIPO');
		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      		<table>
                      		<caption class="grid_title">'.$titulo_grid.'</caption>
                      		<thead align="center" valign="center" >
                      		<tr class="grid_header">';    

		for($i = 0; $i < count($columnas); $i++) {
			$listado .= '<td class="grid_cabecera"> '.strtoupper($columnas[$i]).'</td>';
		}
		$listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';

		foreach($registros as $reg) {
			$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");
			$listado .= '<tr >';
			$regCod = $reg["ch_sucursal"];
			$regFecha = $reg["dt_dia"];
			$regTurno = $reg["ch_posturno"];

			if ($reg["ch_tipo"] == 'C') {
				$regCodIsla = $reg["ch_lado"];
			} else {
				$regCodIsla = $reg["ch_caja"];
			}

			$regCodTrab = $reg["ch_codigo_trabajador"];	
			$regTipo = $reg["ch_tipo"];
		
	  		for ($i=0; $i < count($columnas); $i++) {
       		 		if($i == 3) {
					if ($reg[$i] != '') {
						$listado .= '<td class="'.$color.'">'.substr($reg[$i].' ',0,2).'</td>';
					} else {
						$listado .= '<td class="'.$color.'">'.$reg[7].'</td>';
					}
				} else {
            				$listado .= '<td class="'.$color.'">'.$reg[$i].'</td>';
				}
      			}

			$listado .= '<td class="'.$color.'"><A href="control.php?rqst=MAESTROS.TRABAJADORXISLA&task=TRABAJADORXISLA&action=Modificar&registroid='.$regCod.'&fecha='.$regFecha.'&turno='.$regTurno.'&codIsla='.$regCodIsla.'&codTrab='.$regCodTrab.'&tipo='.$regTipo.'" target="control"><img alt="Editar" title="Editar" src="/sistemaweb/icons/anular.gif" align="middle" border="0"/></A>&nbsp;';

			$listado .= '<td><A href="javascript:confirmarLink(\'Desea eliminar la matricula?\',\'control.php?rqst=MAESTROS.TRABAJADORXISLA&task=TRABAJADORXISLA&action=Eliminar&registroid='.$regCod.'&fecha='.$regFecha.'&turno='.$regTurno.'&codIsla='.$regCodIsla.'&codTrab='.$regCodTrab.'&tipo='.$regTipo.'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td>';

			$listado .= '</tr>';
			$contador++;
    		}
    		$listado .= '</tbody></table></div>';

    		return $listado;
  	}

	function formBuscar($paginacion) {
		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.TRABAJADORXISLA'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TRABAJADORXISLA'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fdesde','Desde :', '', espacios(2), 20, 18));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.fdesde'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fhasta','Hasta :', '', espacios(2), 20, 18));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.fhasta'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('trabajador','Cod. Trabajador :', '', espacios(2), 20, 18));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Exportar',espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));

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
   		$Tipo = array('C' => 'Combustible',
   		               'M' => 'Market');

    		$lado1 = TrabajadorxIslaModel::obtieneLados();
    		$lado1['TODOS'] = "Seleccionar";

    		$lado2 = TrabajadorxIslaModel::obtieneLadosMarket();
    		$lado2['TODOS'] = "Seleccionar";

    		$fechaxdefecto = TrabajadorxIslaModel::obtieneFechaxDefecto();
    		$turno = TrabajadorxIslaModel::obtieneTurnos();	
    		$form = new form2('DATOS DE ISLA', 'form_trabajadorxIsla', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_trab();"');

    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.TRABAJADORXISLA'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TRABAJADORXISLA'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
	
		if($_REQUEST['action'] == 'Modificar') {
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));
		}

    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$trab["ch_sucursal"]));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td bgcolor="#FFFFCD">'));
    
    		// Inicio Contenido TD 1
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_td_title">'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('DATOS DE ISLA  </td></tr><tr><td>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
 		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Codigo Sucursal </td><td>:&nbsp;&nbsp;'. $_SESSION['almacen']));
 		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('trab[codigosuc]', $_SESSION['almacen']));

    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		if (trim(@$trab["dt_dia"]) == '') {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecha','Fecha  </td><td>: ', $fechaxdefecto,'', 25, 30,'',array('readonly')));
		} else {
    			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecha','Fecha  </td><td>: ', trim(@$trab["dt_dia"]),'', 25, 30,'',array('readonly')));
		}

		if($_REQUEST['action'] != 'Modificar') {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_trabajadorxIsla.fecha'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));    
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

         	$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('trab[turno]','Turno  </td><td>: ',trim(@$trab["ch_posturno"]),$turno, '', 3,'',($_REQUEST['action']=='Modificar'?array('readonly'):array('onKeyUp=validarTurno();'))));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    
		if($_REQUEST['action'] != 'Modificar'){
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo('tipo','Tipo </td><td>: ', trim(@$trab["ch_tipo"]), $Tipo,'',2,array('onChange=cambiarCombo(tipo,isla1,isla2)'),''));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Isla/Lado  </td><td>: '));

	    		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('isla1','', 'TODOS',$lado1, '', 2,array('id=isla1'),($_REQUEST['action']=='Modificar'?array('readonly'):array())));
	    		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('isla2','', 'TODOS',$lado2, '', 2,array('id=isla2', 'style="display: none"'),($_REQUEST['action']=='Modificar'?array('readonly'):array())));
		} else {
		   	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Tipo </td><td>:&nbsp;&nbsp;'. (strtoupper(trim(@$trab["ch_tipo"])) =='C'?'Combustible':'Market') ));
		   	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('tipo', trim(@$trab["ch_tipo"])));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

			if(trim(@$trab["ch_tipo"]) == 'C') {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Isla/Lado  </td><td>: '));

		    		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('isla1','', trim(@$trab["ch_lado"]),$lado1, '', 2,array('id=isla1'),($_REQUEST['action']=='Modificar'?array('readonly'):array())));
		    		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('isla2','', trim(@$trab["ch_lado"]),$lado2, '', 2,array('id=isla2', 'style="display: none"'),($_REQUEST['action']=='Modificar'?array('readonly'):array())));
			} else {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Isla/Lado  </td><td>: '));

		    		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('isla1','', trim(@$trab["ch_lado"]),$lado1, '', 2,array('id=isla1', 'style="display: none"'),($_REQUEST['action']=='Modificar'?array('readonly'):array())));
		    		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('isla2','', trim(@$trab["ch_caja"]),$lado2, '', 2,array('id=isla2'),($_REQUEST['action']=='Modificar'?array('readonly'):array())));
			}
		}
	
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('codigotrab','Codigo Trabajador  </td><td>: ', trim(@$trab["ch_codigo_trabajador"]),'', 25, 10,'',array('onkeypress="return soloNumeros(event)"')));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="../images/help.gif" width="16" height="15" onClick="javascript:mostrarAyuda('."'lista_ayuda.php','form_trabajadorxIsla.codigotrab','form_trabajadorxIsla.nombretrab','trabajadores'".')">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('¿Necesita Ayuda?'));

	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
	 	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('nombretrab','Nombre Trabajador  </td><td>: ', trim(@$trab["nombretrab"]),'', 50, 60,'',array('readonly')));

	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
	   
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
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
	    
	    	return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  	}
}
