<?php

class VarillasTemplate extends Template {

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

		$estaciones = VarillasModel::obtieneListaEstaciones();
        	$estaciones['TODOS'] = "Todas las estaciones...";

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.VARILLAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" cellspacing="5" cellpadding="5">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "TODOS", $estaciones, espacios(3), array("onfocus" => "getFechaEmision();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
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

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("estacion").focus();
			}
		</script>'
		));

		return $form->getForm();
    }

	function formSearch($fecha, $fecha2, $paginacion){

		$estaciones 			= VarillasModel::obtieneListaEstaciones();
        $estaciones['TODOS'] 	= "Todas las estaciones...";

		$form = new Form('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.VARILLAS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" cellspacing="5" cellpadding="5">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "TODOS", $estaciones, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Desde: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Desde:", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="left">Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Form.fecha2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/agregar.gif" align="right" />Agregar</button>'));
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

		$estaciones = VarillasModel::obtieneListaEstaciones();

		$form = new Form('',"Editar", FORM_METHOD_POST, "control.php", '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.VARILLAS"));

		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext("<table border = '0'>"));

				if($_REQUEST['action'] == 'Agregar'){

//TITULO
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<tr><td colspan="3" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<p style="font-size:2em; color:black;"><b>Ventas'));

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td colspan="2" style="text-align:center;">'));			
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<p style="font-size:2em; color:black;"><b>Liquidaci&oacuten</td><td style="text-align:left;">'));

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
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_cli_credito", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"9"), ''));
					
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Totales Contometros Surtidor Soles:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("surtidor_soles", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"2"), ''));	

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Ventas a Clientes Anticipo:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_cli_anticipo", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"10"), ''));					

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Contometro General Inicial:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("<input onblur='if(this.value == ''){this.value='0'}'   onKeyUp='contometros()' tabindex='3' type='text' name='cnt_inicial' id='cnt_inicial' value='".$fila."'>"));

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Ventas con Tarjeta de Cr&eacute;dito:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_tar_credito", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"11"), ''));										

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr><td colspan="2" style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Contometro General Final:</td><td style='text-align:left;'>"));
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
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr>'));

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td colspan="2" style="text-align:right;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Costo Unitario M3:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("nu_costo_unitario", "", "", "", 8, 8, array('onkeypress="return validar(event,3)"',"tabindex"=>"16"), ''));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:center;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(" Efectivo D&oacute;lares:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("tot_dolares", "", "", "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"17"), ''));										
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

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Descuentos Otorgados: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_descuentos', $fila['tot_descuentos'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'> Contometro General Final: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'cnt_final', $fila['contometro_final'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Faltantes Trabajadores: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_text('', 'tot_trab_faltantes', $fila['tot_trab_faltantes'], "", "",12,12,false,'onkeypress="return validar(event,3)"'));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr><tr><td style='text-align:right;'>(B) Total Contometro General Cantidad M3: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
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
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td><td style="text-align:center;">'));

				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</tr><tr>'));
				   	$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('<td style="text-align:right;">'));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("Costo Unitario M3:</td><td style='text-align:left;'>"));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('nu_costo_unitario', 'nu_costo_unitario', $fila['nu_costo_unitario'], "", 8, 8, array('onkeypress="return validar(event,3)"',"tabindex"=>"16"), ''));

					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td><td style='text-align:right;'> Efectivo Dolares: "));
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td style="text-align:left;">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text('tot_dolares', 'tot_dolares', $fila['tot_dolares'], "", 12, 12, array('onkeypress="return validar(event,3)"',"tabindex"=>"17"), ''));										
					$form->addElement(FORM_GROUP_MAIN, new form_element_anytext("</td></tr>"));

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
		$result .= '<th class="grid_cabecera">VENTA MEDIDOR M3</th>';
		$result .= '<th class="grid_cabecera">VENTA SURTIDOR M3</th>';
		$result .= '<th class="grid_cabecera">MERMAS M3</th>';
		$result .= '<th class="grid_cabecera">VENTA MEDIDOR SOLES</th>';
		$result .= '<th class="grid_cabecera">VENTA SURTIDOR SOLES</th>';
		$result .= '<th class="grid_cabecera">MERMA S/.</th>';
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
		$result .= '<th class="grid_cabecera">COSTO UNI.</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {

			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

			$a = $resultados[$i];

			$sum_cantidad			= $sum_cantidad + $a['tot_cantidad'];
			$sum_surtidor_cantidad	= $sum_surtidor_cantidad + $a['tot_surtidor_cantidad'];
			$sum_mermas_cantidad	= $sum_mermas_cantidad + $a['mermas'];
			$sum_venta				= $sum_venta + $a['tot_venta'];
			$sum_surtidor_soles		= $sum_surtidor_soles + $a['tot_surtidor_soles'];
			$sum_mermas_soles		= $sum_mermas_soles + ($a['tot_surtidor_soles'] - $a['tot_venta']);
			$sum_abono				= $sum_abono + $a['tot_abono'];
			$sum_cli_credito		= $sum_cli_credito + $a['tot_cli_credito'];
			$sum_soles				= $sum_soles + $a['tot_soles'];

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['ch_almacen']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['dt_fecha']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['cnt_inicial']) . '</td>';
			$result .= '<td class="'.$color.'" align ="center">' . htmlentities($a['cnt_final']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_cantidad']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_surtidor_cantidad']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['mermas']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_venta']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_surtidor_soles']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities(($a['tot_surtidor_soles'] - $a['tot_venta'])) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_abono']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_afericion']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_cli_credito']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_cli_anticipo']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_tar_credito']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_descuentos']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_trab_faltantes']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_trab_sobrantes']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_soles']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['tot_dolares']) . '</td>';
			$result .= '<td class="'.$color.'" align ="right">' . htmlentities($a['nu_costo_unitario']) . '</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.VARILLAS&action=Modificar&ch_almacen='.htmlentities($a['ch_almacen']).'&dt_fecha='.htmlentities($a['dt_fecha']).'&fecha='.$_REQUEST['fecha'].'&fecha2='.$_REQUEST['fecha2'].'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="javascript:confirmarLink(\'Deseas eliminar el registro '. htmlentities($a['ch_almacen']).' con fecha '. htmlentities($a['dt_fecha']).'?\',\'control.php?rqst=REPORTES.VARILLAS&action=Eliminar&ch_almacen='.($a['ch_almacen']).'&dt_fecha='.($a['dt_fecha']).'&fecha='.$_REQUEST['fecha'].'&fecha2='.$_REQUEST['fecha2'].'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>&nbsp;</td>';			
			$result .= '</tr>';

		}

			$result .= '<tr bgcolor="DFBEBE">';
			$result .= '<td colspan="4" class="grid_cabecera" align = "right"><p style="font-size:1.1em; color:white;"><b>TOTALES: </td>';
			$result .= '<td class="grid_cabecera" align ="right"><p style="font-size:1.2em; color:white;"><b>' . htmlentities(number_format($sum_cantidad, 2, '.', ',')) . '</td>';
			$result .= '<td class="grid_cabecera" align ="right"><p style="font-size:1.2em; color:white;"><b>' . htmlentities(number_format($sum_surtidor_cantidad, 2, '.', ',')) . '</td>';
			$result .= '<td class="grid_cabecera" align ="right"><p style="font-size:1.2em; color:white;"><b>' . htmlentities(number_format($sum_mermas_cantidad, 2, '.', ',')) . '</td>';
			$result .= '<td class="grid_cabecera" align ="right"><p style="font-size:1.2em; color:white;"><b>' . htmlentities(number_format($sum_venta, 2, '.', ',')) . '</td>';
			$result .= '<td class="grid_cabecera" align ="right"><p style="font-size:1.2em; color:white;"><b>' . htmlentities(number_format($sum_surtidor_soles, 2, '.', ',')) . '</td>';
			$result .= '<td class="grid_cabecera" align ="right"><p style="font-size:1.2em; color:white;"><b>' . htmlentities(number_format($sum_mermas_soles, 2, '.', ',')) . '</td>';
			$result .= '<td class="grid_cabecera" align ="right"><p style="font-size:1.2em; color:white;"><b>' . htmlentities(number_format($sum_abono, 2, '.', ',')) . '</td>';
			$result .= '<td class="grid_cabecera" align ="right"></td>';
			$result .= '<td class="grid_cabecera" align ="right"><p style="font-size:1.2em; color:white;"><b>' . htmlentities(number_format($sum_cli_credito, 2, '.', ',')) . '</td>';
			$result .= '<td colspan="5" class="grid_cabecera" align ="right"></td>';
			$result .= '<td class="grid_cabecera" align ="right"><p style="font-size:1.2em; color:white;"><b>' . htmlentities(number_format($sum_soles, 2, '.', ',')) . '</td>';
			$result .= '<td colspan="2" class="grid_cabecera" align ="right"></td>';

		$result .= '</table>';
		return $result;
	}
	
	function reporteExcel($res, $desde, $hasta) {

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(12);
		$formato0->set_bold(1);
		$formato0->set_align('center');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados GNV');
		$worksheet1->set_column(0, 0, 10);
		$worksheet1->set_column(1, 1, 15);
		$worksheet1->set_column(2, 2, 20);
		$worksheet1->set_column(3, 3, 20);
		$worksheet1->set_column(4, 4, 20);
		$worksheet1->set_column(5, 5, 20);
		$worksheet1->set_column(6, 6, 20);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(4, 6, "Reporte de Liquidacion de Venta GNV",$formato0);
//		$worksheet1->write_string(4, 0, "Fecha Desde: ".$desde." Hasta: ".$hasta, $formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 7;
		$worksheet1->write_string($a, 0, "ALMACEN",$formato2);
		$worksheet1->write_string($a, 1, "FECHA",$formato2);
		$worksheet1->write_string($a, 2, "CONTOMETRO INICIAL",$formato2);
		$worksheet1->write_string($a, 3, "CONTOMETRO FINAL",$formato2);	
		$worksheet1->write_string($a, 4, "VENTA MEDIDOR M3",$formato2);
		$worksheet1->write_string($a, 5, "VENTA SURTIDOR M3",$formato2);
		$worksheet1->write_string($a, 6, "MERMAS M3",$formato2);
		$worksheet1->write_string($a, 7, "VENTA MEDIDOR SOLES",$formato2);
		$worksheet1->write_string($a, 8, "VENTA SURTIDOR SOLES",$formato2);
		$worksheet1->write_string($a, 9, "MERMA S/",$formato2);
		$worksheet1->write_string($a, 10, "ABONO",$formato2);
		$worksheet1->write_string($a, 11, "AFERICION",$formato2);
		$worksheet1->write_string($a, 12, "VENTAS CLIENTES CREDITOS",$formato2);
		$worksheet1->write_string($a, 13, "VENTAS CLIENTES ANTICIPOS",$formato2);
		$worksheet1->write_string($a, 14, "VENTAS TARJETAS CREDITOS",$formato2);
		$worksheet1->write_string($a, 15, "DESCUENTOS",$formato2);
		$worksheet1->write_string($a, 16, "FALTANTES TRABAJADORES",$formato2);
		$worksheet1->write_string($a, 17, "SOBRANTES TRABAJADORES",$formato2);
		$worksheet1->write_string($a, 18, "EFECTIVO SOLES",$formato2);
		$worksheet1->write_string($a, 19, "EFECTIVO DOLARES",$formato2);

		$a = 8;	

		for ($j=0; $j<count($res); $j++) {	
			
			$sum_cantidad		= $sum_cantidad + $res[$j]['tot_cantidad'];
			$sum_surtidor_cantidad	= $sum_surtidor_cantidad + $res[$j]['tot_surtidor_cantidad'];
			$sum_mermas_cantidad	= $sum_mermas_cantidad + $res[$j]['mermas'];
			$sum_venta		= $sum_venta + $res[$j]['tot_venta'];
			$sum_surtidor_soles	= $sum_surtidor_soles + $res[$j]['tot_surtidor_soles'];
			$sum_mermas_soles	= $sum_mermas_soles + ($res[$j]['tot_surtidor_soles'] - $res[$j]['tot_venta']);
			$sum_abono		= $sum_abono + $res[$j]['tot_abono'];
			$sum_cli_credito	= $sum_cli_credito + $res[$j]['tot_cli_credito'];
			$sum_soles		= $sum_soles + $res[$j]['tot_soles'];

			$worksheet1->write_string($a, 0, $res[$j]['ch_almacen'],$formato5);
			$worksheet1->write_string($a, 1, $res[$j]['dt_fecha'],$formato5);	
			$worksheet1->write_number($a, 2, number_format($res[$j]['cnt_inicial'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 3, number_format($res[$j]['cnt_final'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 4, number_format($res[$j]['tot_cantidad'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 5, number_format($res[$j]['tot_surtidor_cantidad'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 6, number_format($res[$j]['mermas'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 7, number_format($res[$j]['tot_venta'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 8, number_format($res[$j]['tot_surtidor_soles'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 9, number_format(($res[$j]['tot_surtidor_soles'] - $res[$j]['tot_venta']),2,'.',''),$formato5);
			$worksheet1->write_number($a, 10, number_format($res[$j]['tot_abono'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 11, number_format($res[$j]['tot_afericion'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 12, number_format($res[$j]['tot_cli_credito'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 13, number_format($res[$j]['tot_cli_anticipo'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 14, number_format($res[$j]['tot_tar_credito'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 15, number_format($res[$j]['tot_descuentos'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 16, number_format($res[$j]['tot_trab_faltantes'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 17, number_format($res[$j]['tot_trab_sobrantes'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 18, number_format($res[$j]['tot_soles'],2,'.',''),$formato5);
			$worksheet1->write_number($a, 19, number_format($res[$j]['tot_dolares'],2,'.',''),$formato5);

			$a++;

		}

			$worksheet1->write_string($a+1, 3, "TOTALES : ",$formato2);
			$worksheet1->write_number($a+1, 4, number_format($sum_cantidad, 2, '.', ''),$formato5);
			$worksheet1->write_number($a+1, 5, number_format($sum_surtidor_cantidad, 2, '.', ''),$formato5);
			$worksheet1->write_number($a+1, 6, number_format($sum_mermas_cantidad, 2, '.', ''),$formato5);
			$worksheet1->write_number($a+1, 7, number_format($sum_venta, 2, '.', ''),$formato5);
			$worksheet1->write_number($a+1, 8, number_format($sum_surtidor_soles, 2, '.', ''),$formato5);
			$worksheet1->write_number($a+1, 9, number_format($sum_mermas_soles, 2, '.', ''),$formato5);
			$worksheet1->write_number($a+1, 10, number_format($sum_abono, 2, '.', ''),$formato5);
			$worksheet1->write_number($a+1, 12, number_format($sum_cli_credito, 2, '.', ''),$formato5);
			$worksheet1->write_number($a+1, 18, number_format($sum_soles, 2, '.', ''),$formato5);
			
		$workbook->close();	

		$chrFileName = "LiquidacionVentaGNV";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");	
	}
}
