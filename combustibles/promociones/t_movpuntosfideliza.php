<?php

class MovPuntosFidelizaTemplate extends Template {

	//METODO QUE DEVUELVE EL TITULO
	function titulo(){
		$titulo = '<div align="center"><h2>MOVIMIENTO DE PUNTOS DE FIDELIZACION</h2></div><hr>';
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
					<td class="grid_cabecera" colspan="7">REFERENCIA</td>
					<td class="grid_cabecera" rowspan="2">PUNTOS</td>
					<td class="grid_cabecera" rowspan="2">SUCURSAL</td>		
					</tr>';

			$listado .='	<tr class="grid_header">
					<td class="grid_cabecera">TD</td>
					<td class="grid_cabecera">CAJA</td>
					<td class="grid_cabecera">NUMERO</td>
					<td class="grid_cabecera">ITEM</td>	
					<td class="grid_cabecera">CANTIDAD</td>
					<td class="grid_cabecera">P.UNIT</td>
					<td class="grid_cabecera">IMPORTE</td>				
					</tr>';
					
	//detalle
			foreach($registros as $reg){

				$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");
			
				$listado .= '<tr>';		

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

				$listado .='<td class="'.$color.'">'.$reg["nu_trans_cantidad"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["nu_trans_preciounit"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["nu_trans_importe"].'&nbsp;</td>';

				$listado .='<td class="'.$color.'">'.$reg["nu_punto_puntaje"].'&nbsp;</td>';
				$listado .='<td class="'.$color.'">'.$reg["ch_sucursal"].'&nbsp;</td>';
				$listado .= '</tr>';
				
				$contador++;
			
			}
			$listado .= '</tbody></table></div>';
		}	
		return $listado;
	}
	
	function formBuscar($dIni, $dFin){
		$almacenes = MovPuntosFidelizaModel::obtenerAlmacenes();
		$almacenes[''] = "Todos los Almacenes";

		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.MOVPUNTOSFIDELIZA'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'MOVPUNTOSFIDELIZA'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacén: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo('almacen', '', '', $almacenes, espacios(3), array("onfocus" => "getFechasIF();"), ''));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("fechainicio", "", $dIni, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text("fechafin", "", $dFin, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">RUC: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc','', $_REQUEST['ruc'],'', 13, 11,'',array('onkeypress="return soloNumeros(event)"')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Nro. Veces: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numveces','', $_REQUEST['numveces'], '',8, 3,'',array('onkeypress="return soloNumeros(event)"')));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Consultar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(
		'<script>
			window.onload = function() {
				parent.document.getElementById("almacen").focus();
			}
		</script>'
		));
		
		return $form->getForm();
	}

	function formPaginacion($paginacion,$filtro,$fechaini,$fechafin,$intListaPuntos){
		$form = new form2('', 'Paginacion', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.MOVPUNTOSFIDELIZA'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'MOVPUNTOSFIDELIZA'));
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
	
	function formMovimientopuntos($intListaPuntos){
		
		$form = new form2(' ', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
		// Inicio Contenido TD 1
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table width="100%" border="0" cellspacing="2" cellpadding="2">'));

		if($intListaPuntos>0){
		}
		else{
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="msg_informacion"><img src="/sistemaweb/icons/messagebox_info32x32.png" border="0">No existe información para la consulta realizada.</td><tr>'));
		}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
			
		return $form->getForm();
	}	

	function reportePDF($daotxruc, $cabecera) {

		$cab_items = Array(
			"ruc"		=>	"RUC",
			"razsocial"	=>	"Razon Social",
			"caja"		=>	"Caja",
			"trans"		=>	"No. Doc.",
			"dia"		=>	"Dia",
			"vventa"	=>	"Valor Venta",
			"igv"		=>	"IGV",
			"total"		=>	"Total"

		);

		$reporte = new CReportes2("P","pt","A4");

		$reporte->definirCabecera(1, "C", $cabecera);
		$reporte->definirCabecera(2, "R", "Pagina %p");
		$reporte->definirCabecera(2, "L", "Fecha: ".date("d/m/Y"));
		$reporte->definirCabecera(3, "C", "DAOT - Detalle por Cliente");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 9.5);

		$reporte->definirColumna("caja", $reporte->TIPO_ENTERO, 5, "L");
		$reporte->definirColumna("trans", $reporte->TIPO_TEXTO, 10, "R");
		$reporte->definirColumna("dia", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirColumna("vventa", $reporte->TIPO_IMPORTE, 15, "R");
		$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 15, "R"); 
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 15, "R");

		$reporte->definirColumna("totales", $reporte->TIPO_TEXTO, 27, "R", "_totales", "B");
		$reporte->definirColumna("vventa", $reporte->TIPO_IMPORTE, 15, "R", "_totales");
		$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 15, "R", "_totales"); 
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 15, "R", "_totales");

		$reporte->definirCabeceraPredeterminada($cab_items);


		foreach ($daotxruc['ruc'] as $ruc) {
			$reporte->definirCabecera(4, "C", "RUC: " . $ruc['ruc'] . " - Razon Social: " . $ruc['razsocial']);
			$reporte->addPage();

			foreach($ruc['documentos'] as $item)
				$reporte->nuevaFila($item);
			$reporte->lineaH();
			$ruc['totales']['totales'] = "Total";
			$reporte->nuevaFila($ruc['totales'], "_totales");
		}

		$reporte->Output("/sistemaweb/ventas_clientes/daot/reporteDaotxRuc.pdf","F");

	}
}

