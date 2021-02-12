<?php
class LiquidacionGNVTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Liquidacion de Venta GNV</b></h2>';
	}

	function formPag($paginacion, $vec, $fecha, $fecha2) {

		$fecha 	  = $vec[0];
		$fecha2	  = $vec[1];

		if($fecha == '' || $fecha2 == ''){

			$fecha = date(d."/".m."/".Y); 
			$fecha2 = date(d."/".m."/".Y);

		}

		$estaciones = LiquidacionGNVModel::obtieneListaEstaciones();

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.LIQUIDACIONGNV"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="1" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Excel</button>'));
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

		$estaciones = LiquidacionGNVModel::obtieneListaEstaciones();

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.LIQUIDACIONGNV"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="1" >'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Excel</button>'));
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

	function formAgregar($fila) {

		$hoy = date("d/m/Y");

		$estaciones = LiquidacionGNVModel::obtieneListaEstaciones();

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.LIQUIDACIONGNV"));

			/*if($_REQUEST['action'] == 'Modificar'){

				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));

			}*/
				$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border = '0'>"));

				if($_REQUEST['action'] == 'Agregar'){


//TITULO
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<p style="font-size:2em; color:black;"><b>Ventas'));

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td colspan="2" style="text-align:center;">'));			
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<p style="font-size:2em; color:black;"><b>Liquidaci&oacuten</td><td style="text-align:left;">'));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));

//CONTENIDO

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="2" style="text-align:center;">'));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Estaci&oacute;n: <td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo("ch_almacen", "Estacion:", "", $estaciones, '</td>'));


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Abono Cofide:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_abono", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"7"), ''));


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Fecha:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dt_fecha", $hoy, '<a href="javascript:show_calendar(\'Editar.dt_fecha\');"><img src="/sistemaweb/images/showcalendar.gif" border=0 align="top"/></a><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>', '', 10, 10,($_REQUEST['action']=='Modificar'?array('readonly'):array())));


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Despachos de Prueba:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_afericion", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"8"), ''));
					

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("(A) Totales Contometros Surtidor M3:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<input onblur='if(this.value == ''){this.value='0'}' tabindex='1' type='text' name='surtidor_m3' id='surtidor_m3' value='0'>"));


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Ventas a Clientes de Cr&eacute;dito:</td><td style='text-align:left;'>"));
					//$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_cli_credito', "", '</td></tr><tr><td colspan="2" style="text-align:center;">', '', 12, 12, false,'onkeypress="return validar(event,3)"'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_cli_credito", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"9"), ''));
					

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Totales Contometros Surtidor Soles:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("surtidor_soles", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"2"), ''));	


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Ventas a Clientes Anticipo:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_cli_anticipo", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"10"), ''));					


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Contometro General Inicial:</td><td style='text-align:left;'>"));
//					$form->addElement(FORM_GROUP_MAIN, new f2element_text("cnt_inicial", "", $fila, "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"3"), ($_REQUEST['action']=='Agregar'?array('readonly'):array())));	
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<input onblur='if(this.value == ''){this.value='0'}'   onKeyUp='contometros()' tabindex='3' type='text' name='cnt_inicial' id='cnt_inicial' value='".$fila."'>"));

//					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_almacen', $fila['ch_almacen'], "", "",4,4,($_REQUEST['action']=='Modificar'?array('readonly'):array())));

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Ventas con Tarjeta de Cr&eacute;dito:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_tar_credito", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"11"), ''));										


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Contometro General Final:</td><td style='text-align:left;'>"));
//					$form->addElement(FORM_GROUP_MAIN, new f2element_text("cnt_final", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"4"), ''));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<input onblur='if(this.value == ''){this.value='0'}'   onKeyUp='contometros()' tabindex='4' type='text' name='cnt_final' id='cnt_final' value='0'>"));


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Descuentos Otorgados:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_descuentos", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"12"), ''));										

					
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("(B) Total Contometro General Cantidad M3:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<input onblur='if(this.value == ''){this.value='0'}' tabindex='5' type='text' name='tot_cantidad' id='tot_cantidad' value='0'>"));


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Faltantes Trabajadores:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_trab_faltantes", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"13"), ''));															


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Total Contometro General Soles:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_venta", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"6"), ''));


				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Sobrantes Trabajadores:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_trab_sobrantes", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"14"), ''));										
					

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("(A - B) Mermas m3:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('','mermas', '','</td>', '', 12, 12, false,'onkeypress="return validar(event,3)"'));

				   	
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Efectivo Soles:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_soles", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"15"), ''));										

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td><td style="text-align:center;">'));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="4" style="text-align:right;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Efectivo D&oacute;lares:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_dolares", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"16"), ''));										
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan = '5' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="button" onclick="Guardar()"><img src="/sistemaweb/icons/agregar.gif" align="right" />Guardar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));



				}else{


//TITULO
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<p style="font-size:2em; color:black;"><b>Ventas'));

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td colspan="2" style="text-align:center;">'));			
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<p style="font-size:2em; color:black;"><b>Liquidaci&oacuten</td><td style="text-align:left;">'));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));


//CONTENIDO
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<td style='text-align:right;'> Estaci&oacute;n: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'ch_almacen', $fila['ch_almacen'], "", "",4,4,($_REQUEST['action']=='Modificar'?array('readonly'):array())));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Abono Cofide: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_abono', $fila['tot_abono'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Fecha: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text("", "dt_fecha", $fila['dt_fecha'], "", "",10,10,($_REQUEST['action']=='Modificar'?array('readonly'):array())));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Despachos de Prueba: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_afericion', $fila['tot_afericion'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> (A) Totales Contometros Surtidor M3: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
//					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'surtidor_m3', $fila['tot_surtidor_m3'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));
//					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<input onblur='if(this.value == ''){this.value='0'}'  onKeyUp='suma();' type='text' name='surtidor_m3' id='surtidor_m3' value='".$fila['tot_surtidor_m3']."'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input style="text-align:left" onKeyUp="suma2();" type="text" name="surtidor_m3" id="surtidor_m3" maxlength="12" size="12" value="'.number_format($fila['tot_surtidor_m3'], 2, '.', '').'" onkeypress="return validar(event,3)" />'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Ventas a Clientes de Cr&eacute;dito: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_cli_credito', $fila['tot_cli_credito'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Totales Contometros Surtidor Soles: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'surtidor_soles', $fila['tot_surtidor_soles'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Ventas a Clientes Anticipo: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_cli_anticipo', $fila['tot_cli_anticipo'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Total Contometro General Soles: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_venta', $fila['tot_venta'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Ventas con Tarjeta de Cr&eacute;dito: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_tar_credito', $fila['tot_tar_credito'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Contometro General Inicial: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'cnt_inicial', $fila['contometro_inicial'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Sobrantes Trabajadores: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_trab_faltantes', $fila['tot_trab_sobrantes'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Contometro General Final: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'cnt_final', $fila['contometro_final'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Descuentos Otorgados: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_descuentos', $fila['tot_descuentos'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'>(B) Total Contometro General Cantidad M3: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
//					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_cantidad', $fila['tot_cantidad'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));
//					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<input onblur='if(this.value == ''){this.value='0'}'  onKeyUp='suma();' type='text' name='tot_cantidad' id='tot_cantidad' value='".number_format($fila['tot_cantidad'], 2, '.', '')."'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<input style="text-align:left" onKeyUp="suma2();" type="text" name="tot_cantidad" id="tot_cantidad" maxlength="12" size="12" value="'.number_format($fila['tot_cantidad'], 2, '.', '').'" onkeypress="return validar(event,3)" />'));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Sobrantes Trabajadores: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_trab_sobrantes', $fila['tot_trab_sobrantes'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> (A - B) Mermas m3: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'mermas', $fila['mermas_m3'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));


					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Efectivo Soles: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_soles', $fila['tot_soles'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td><td><td style='text-align:right;'> Efectivo D&oacute;lares: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_dolares', $fila['tot_dolares'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));						

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<tr><td colspan='5' align='center'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="button" onclick="Actualizar()"><img src="/sistemaweb/icons/update2.png" align="right" />Actualizar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr></table>"));


				}

		return $form->getForm();
	}

	function resultadosBusqueda($resultados,$fecha,$fecha2) {

		$result .= '<table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">ALMACEN</th>';
		$result .= '<th class="grid_cabecera">FECHA</th>';
		$result .= '<th class="grid_cabecera">CONTOMETRO INICIAL</th>';
		$result .= '<th class="grid_cabecera">CONTOMETRO FINAL</th>';
		$result .= '<th class="grid_cabecera">TOTAL SURTIDOR M3</th>';
		$result .= '<th class="grid_cabecera">TOTAL SURTIDOR SOLES</th>';
		$result .= '<th class="grid_cabecera">MERMAS M3</th>';
		$result .= '<th class="grid_cabecera">TOTAL CANTIDAD M3</th>';
		$result .= '<th class="grid_cabecera">TOTAL SOLES</th>';
		$result .= '<th class="grid_cabecera">ABONO</th>';
		$result .= '<th class="grid_cabecera">AFERICION</th>';
		$result .= '<th class="grid_cabecera">VENTAS CLIENTES CREDITOS</th>';
		$result .= '<th class="grid_cabecera">VENTAS CLIENTES ANTICIPOS</th>';
		$result .= '<th class="grid_cabecera">VENTAS TARJETAS CREDITOS</th>';
		$result .= '<th class="grid_cabecera">DESCUENTOS</th>';
		$result .= '<th class="grid_cabecera">FALTANTES TRABAJADORES</th>';
		$result .= '<th class="grid_cabecera">SOBRANTES TRABAJADORES</th>';
		$result .= '<th class="grid_cabecera">EFECTIVO SOLES</th>';
		$result .= '<th class="grid_cabecera">EFECTIVO DOLARES</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];
			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'">' . htmlentities($a['ch_almacen']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['dt_fecha']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['cnt_inicial']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['cnt_final']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_surtidor_cantidad']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_surtidor_soles']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['mermas']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_cantidad']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_venta']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_abono']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_afericion']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_cli_credito']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_cli_anticipo']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_tar_credito']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_descuentos']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_trab_faltantes']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_trab_sobrantes']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_soles']) . '</td>';
			$result .= '<td class="'.$color.'">' . htmlentities($a['tot_dolares']) . '</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.LIQUIDACIONGNV&action=Modificar&ch_almacen='.htmlentities($a['ch_almacen']).'&dt_fecha='.htmlentities($a['dt_fecha']).'&fecha='.$_REQUEST['fecha'].'&fecha2='.$_REQUEST['fecha2'].'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar el registro '. htmlentities($a['ch_almacen']).' con fecha '. htmlentities($a['dt_fecha']).'?\',\'control.php?rqst=REPORTES.LIQUIDACIONGNV&action=Eliminar&ch_almacen='.($a['ch_almacen']).'&dt_fecha='.($a['dt_fecha']).'&fecha='.$_REQUEST['fecha'].'&fecha2='.$_REQUEST['fecha2'].'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
			$result .= '</tr>';

		}
		$result .= '</table>';
		return $result;
	}

}
