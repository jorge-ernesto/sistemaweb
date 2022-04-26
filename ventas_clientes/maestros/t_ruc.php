<?php
class RucTemplate extends Template {
	function titulo(){
		$titulo = '<div align="center"><h2 style="color:#336699">RUC</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}

	function TemplateReportePDF($reporte_array) {
	
		$datos = array();
		$Cabecera = array( "ruc" => "RUC", "razsocial" => "RAZON SOCIAL");

		//$Totales_new = array_merge_recursive($Totales, $Totales2);
    		//print_r($Totales_new);
    		$fontsize = 10;

		$reporte = new CReportes2();
		$reporte->SetMargins(5, 5, 5);
		$reporte->SetFont("courier", "", $fontsize);
		$reporte->definirCabecera(2, "L", "Clientes por RUC");
		$reporte->definirCabecera(2, "R", "Pagina %p");
		    	
		$reporte->definirColumna("ruc", $reporte->TIPO_TEXTO, 20, "L");//20
		$reporte->definirColumna("razsocial", $reporte->TIPO_TEXTO, 40, "L");//15
		$reporte->definirCabeceraPredeterminada($Cabecera);
        	
		$reporte->AddPage(); 
    		foreach($reporte_array as $llave => $valores) {
    			$reporte->nuevaFila($valores);
    		}
    		//print_r($Totales);
	
    		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_ruc.pdf", "F");
		return '<center><iframe src="/sistemaweb/ventas_clientes/reportes/pdf/reporte_ruc.pdf" width="1000" height="500"></iframe>
			 <br/><button class="form_button" onclick="regresar();" name="button" type="button">Regresar</button></center>';
	
		//return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/reporte_tarj_magneticas_cli.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
  	}

	
	function listado($registros, $sunat) {
	
		$columnas = array('RUC','RAZON SOCIAL');
		$isOpensoftServer2 = RucModel::getOpensoftServer2();
		$isOpensoftServer2['address'] ? array_push($columnas, "DIRECCIÓN")        : "";
		$isOpensoftServer2['locid']   ? array_push($columnas, "CÓDIGO DE UBIGEO") : "";

		if ($sunat == '0') {
	    		$titulo_grid = "RUC";
	    		//formulario de busqueda
	    		// $columnas = array('RUC','RAZON SOCIAL'/*, 'FECHA'*/);
				$columnas = $columnas;
	    		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
				      <table>
				      <caption class="grid_title">'.$titulo_grid.'</caption>
				      <thead align="center" valign="center" >
				      <tr class="grid_header">';
				      
	    		for($i=0;$i<count($columnas);$i++) {
	      			$listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
	    		}
	    		$listado .= '<th>&nbsp;</th></tr><tbody class="grid_body" style="height:250px;">';
	    		//detalle
	    		$j1 = 0;$j2 = 0;

	    		foreach($registros as $reg) {
	    			$listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>';
	    			$regCod = $reg["ruc"];

	    			for ($i=0; $i < count($columnas); $i++) {
	      				$listado .= '<td class="grid_item">'.$reg[$i].'</td>';
	      			}

	      			$j1++;
	      			$listado .= '<td> <A href="control.php?rqst=MAESTROS.RUC&task=RUC&action=Modificar&registroid='.$regCod.'" target="control">
					<img src="/sistemaweb/icons/open.gif" align="middle" border="0"/></A>&nbsp;';
	      			$listado .= '<A href="javascript:confirmarLink(\'Desea borrar al usuario con Ruc '.$regCod.'\',\'control.php?rqst=MAESTROS.RUC&task=RUC'.
		          		'&action=Eliminar&registroid='.$regCod.'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A></td>';
		
	      			$listado .= '</tr>';
	    		}

	    		$listado .= '</tbody></table></div>';
		} else {
			//$listado  = "<center><table border='1'><tr><th><blink style='color:red'>";
			//$listado .= "RECOMENDACION: Antes de buscar refrescar el codigo<br>";
			//$listado .= "</blink></th></tr>";
			$listado  = "<center><table border='1'><tr><th style='color:red'>";
			$listado .= "RECOMENDACION: Antes de buscar refrescar el codigo<br>";
			$listado .= "</th></tr>";
			$listado .= "<tr><td style='color:green'>";
			$listado .= "1&deg; Seleccione un rango de fechas de b&uacute;squeda y con el bot&oacute;n 'Generar' obtendr&aacute; un archivo por cada 100 RUCs modificados en esas fechas";
			$listado .= "</td></tr>";
			$listado .= "<tr><td style='color:green'>";
			$listado .= "2&deg; Descargue todos los archivos generados en la b&uacute;squeda, de preferencia en el Escritorio";
			$listado .= "</td></tr>";
			$listado .= "<tr><td style='color:green'>";
			$listado .= "3&deg; Con el boton 'Examinar...' de la parte inferior, desplacese a la ubicaci&oacute;n donde descargo los archivos, seleccione el primero de ellos y de click en 'Abrir'";
			$listado .= "</td></tr>";
			$listado .= "<tr><td style='color:green'>";
			$listado .= "4&deg; Ingrese el c&oacute;digo de seguridad que se le solicite mas abajo y de click en 'Enviar'";
			$listado .= "</td></tr>";
			$listado .= "<tr><td style='color:green'>";
			$listado .= "5&deg; Espere unos segundos...";
			$listado .= "</td></tr>";
			$listado .= "<tr><td style='color:green'>";
			$listado .= "6&deg; Luego guarde el archivo que le indican descargar en la parte inferior, al go muy importante: con 'EL MISMO NOMBRE DE DESCARGA' y de preferencia en el Escritorio";
			$listado .= "</td></tr>";
			$listado .= "<tr><td style='color:green'>";
			$listado .= "7&deg; Seleccione el archivo descargado con el bot&oacute;n 'Examinar...' de la parte superior y luego de click en 'Cargar'";
			$listado .= "</td></tr>";
			$listado .= "<tr><td style='color:green'>";
			$listado .= "8&deg; Siga el mismo procedimiento para todos los archivos descargados inicialmente";
			$listado .= "</td></tr>";
			$listado .= "<tr><td align='center'>";
			$listado .= "<iframe name='Consulta de RUC - Sunat' width='890' height='900' src='http://www.sunat.gob.pe/cl-ti-itmrconsmulruc/jrmS00Alias' marginwidth='1' marginheight='0' title='Consulta de RUC - Sunat' border='0' frameborder='0'>Consulta de RUC - Sunat</iframe>";
			$listado .= "</td></tr>";
		    	$listado .= "<tr><td>";
			//$listado .= "<input type= 'text' class='form_input' name='nombre' id='nombre' size='30' maxlength='25'>&nbsp;&nbsp;&nbsp;&nbsp;";
			//$listado .= "<input type= 'submit' class='form_button' name='action' value='Cargar'>";
			$listado .= '</td></tr></table></center>';
		}

    		return $listado;
  	}
  	
  	function formBuscar($paginacion, $importar, $numarchivos, $cant_rucs, $tipo,$archivos_descarga){
  	
		$isOpensoftServer2 = RucModel::getOpensoftServer2();

		$fecha = date("d/m/Y", time());

    		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control','ACCEPT="zip" enctype="multipart/form-data"');
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.RUC'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RUC'));
    		//$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pp', $paginacion['pp']));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));

		if($importar == '0') {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Numero de Ruc :', '', '', 13, 13));
			if ($isOpensoftServer2['address']) {
				$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[address]','Dirección :', '', '', 13, 1000));
			}
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('tipobusqueda', '0'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Importar',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "<br/>Desde:", $fecha, '', 10, 12));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $fecha,'' , 10, 12));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><br>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));
		} else {
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('tipobusqueda', '1'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "Desde:", $fecha, '', 10, 12));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "Hasta:", $fecha,'' , 10, 12));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Generar',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','CIF OCS',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','RENIEC',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button class="form_button" onclick="regresar();" name="button" type="button">Regresar</button>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><br/>'));
			//$form->addElement(FORM_GROUP_MAIN, new f2element_file('file','Archivo Obtenido: ','file',espacios(3),83));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<span class="form_label">Archivo Obtenido (.zip): </span>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="file" name = "ubicacion" id="ubicacion" size="70">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Cargar',espacios(3)));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));

			if($tipo == '1') {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<center><p style='color:red'>Se generaron -".pg_escape_string($numarchivos)."- archivos de un total de -".pg_escape_string($cant_rucs)."- rucs</p></center>"));
				/*$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<br/><br/>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<a target='_blank' href='vta_reporte_ventas_xhoras.php'>ARCHIVOS 1</a>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<br/><br/>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<a target='_blank' href=\"javascript:window.open('vta_reporte_ventas_xhoras.php','w','width=500,height=400')\">ARCHIVOS 2</a>"));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<br/><br/>"));*/

				if($numarchivos>0){
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<table border='1' width='900'><tr><th>"));
					for($i = 0 ; $i< $numarchivos; $i++) {
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<blink><a href=".$archivos_descarga[$i]." style='color:blue'>Archivo de descarga ".($i+1)."</a></blink><br/>"));
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</th></tr></table>"));
				}
			}
		}
		
    		return $form->getForm();
  	} 

   	function formRuc($ruc) {
		$isOpensoftServer2 = RucModel::getOpensoftServer2();

    		$form = new form2('DATOS DE RUC', 'form_ruc', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_ruc();"');
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.RUC'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RUC'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
    		
		if($_REQUEST['action'] == 'Modificar') {
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', 'actualizar'));
		}

		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$ruc["ruc"]));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td bgcolor="#FFFFCD">'));
    
		// Inicio Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_td_title">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('DATOS DE RUC  </td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc[ruc]','Ruc  </td><td>: ', @$ruc["ruc"], '', 13, 13,'',($_REQUEST['action']=='Modificar'?array('readonly'):array())));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc[razsocial]','Razon Social  </td><td>: ', @$ruc["razsocial"],'', 40, 40));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "Fecha  </td><td>: ", @$ruc["fecha"], '', 10, 12));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_ruc.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

		if ($isOpensoftServer2['address']) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc[address]','Dirección  </td><td>: ', @$ruc["address"],'', 40, 1000));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		}

		if ($isOpensoftServer2['locid']) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc[locid]','Código de Ubigeo  </td><td>: ', @$ruc["locid"],'', 13, 6, array(), array('onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"')));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		}

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
