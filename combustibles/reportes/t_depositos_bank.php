<?php
class DepositosBankTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Dep&oacute;sitos Banco</b></h2>';
	}

	function formPag($paginacion, $vec, $fecha, $fecha2) {

		$fecha 	  = $vec[0];
		$fecha2	  = $vec[1];

		if($fecha == '' || $fecha2 == ''){

			$fecha = date(d."/".m."/".Y); 
			$fecha2 = date(d."/".m."/".Y);

		}

		$estaciones = DepositosBankModel::obtieneListaEstaciones();

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.DEPOSITOSBANK"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
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


	function formSearch($fecha,$fecha2,$paginacion){

		if($fecha == '' || $fecha2 == ''){

			$fecha = date(d."/".m."/".Y); 
			$fecha2 = date(d."/".m."/".Y);

		}

		$estaciones = DepositosBankModel::obtieneListaEstaciones();

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.DEPOSITOSBANK"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

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

	function formAgregar($fila = "", $paginacion = "", $vec = "") {

		$fecha 	  = $vec[0];
		$fecha2	  = $vec[1];

		$hoy = date("d/m/Y");

		$estaciones = DepositosBankModel::obtieneListaEstaciones();

		$moneda = array("1" => "Soles", "2" =>"Dolares");
		$banco = array("1" => "BCP", "2" =>"BBVA" , "3" =>"SCOTIABANK" , "4" =>"INTERBANK");

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.DEPOSITOSBANK"));

			if($_REQUEST['action'] == 'Modificar'){

				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));

			}
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table>"));

				if($_REQUEST['action'] == 'Agregar'){

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Estaci&oacute;n: <td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo("ch_almacen", "Estacion:", "", $estaciones, '</td></tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Fecha:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dt_fecha", $hoy, '<a href="javascript:show_calendar(\'Editar.dt_fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0 align="top"/></a><div style="position:relative"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div></td></tr><tr><td colspan="2" style="text-align:center;">', '', 10, 10,($_REQUEST['action']=='Modificar'?array('readonly'):array())));			
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Moneda:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo("moneda", "Moneda:", "", $moneda, '</td></tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Banco:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo("banco", "Banco:", "", $banco, '</td></tr><tr><td colspan="2" style="text-align:center;">'));
					//$form->addElement(FORM_GROUP_MAIN, new form_element_text('','moneda', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 12, 12, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("N&uacute;mero de Dep&oacute;sito:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','docu', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 12, 12, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Referencia:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','refe', '','</td></tr><tr><td colspan="2" style="text-align:center;">', '', 12, 12, false,'onkeypress="return validar(event,1)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Importe:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'total', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 12, 12, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '2' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

				}else{

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td style='text-align:right;'> Estaci&oacute;n: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_almacen', $fila['almacen'], "", "",4,4,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Fecha: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dt_fecha", $fila['fecha'], "", "",10,10,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Moneda: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'moneda', '1', "", "",2,2,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Banco: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "nombre", $fila['nombre'], "", "",50,50,($_REQUEST['action']=='Modificar'?array('readonly'):array())));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Numero de Dep&oacute;sito: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'docu', $fila['docu'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Referencia: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'refe', $fila['refe'], "", "",12,12,false,'onkeypress="return validar(event,1)"'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Importe: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'total', $fila['total'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));					
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'idred', $fila['idred'], "", "",12,12,false,'readonly'));					
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Actualizar"><img src="/sistemaweb/icons/update2.png" align="right" />Actualizar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td align='left'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));

				}

		return $form->getForm();
	}

	function resultadosBusqueda($resultados,$fecha = "",$fecha2 = "") {

		$result .= '<table align="center">';
		$result .= '<tr>';
		//$result .= '<th class="grid_cabecera"></th>';
		$result .= '<th class="grid_cabecera">ALMACEN</th>';
		$result .= '<th class="grid_cabecera">FECHA</th>';
		$result .= '<th class="grid_cabecera">BANCO</th>';
		$result .= '<th class="grid_cabecera">MONEDA</th>';
		$result .= '<th class="grid_cabecera">NUMERO DEPOSITO</th>';
		$result .= '<th class="grid_cabecera">REFERENCIA</th>';
		$result .= '<th class="grid_cabecera">IMPORTE</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';
			//$result .= '<td class="'.$color.'">' . htmlentities($a['id']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['almacen']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['fecha']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['nombre']) . '</td>';
			$result .= '<td class="'.$color.'">1</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['docu']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['refe']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['total']) . '</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.DEPOSITOSBANK&action=Modificar&id='.($a['id']).'&fecha='.$_REQUEST['fecha'].'&fecha2='.$_REQUEST['fecha2'].'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar el registro '. htmlentities($a['almacen']).' con fecha '. htmlentities($a['fecha']).'?\',\'control.php?rqst=REPORTES.DEPOSITOSBANK&action=Eliminar&dt_fecha='.($a['fecha']).'&id='.($a['id']).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
			$result .= '</tr>';

		}
		$result .= '</table>';
		return $result;
	}
}
