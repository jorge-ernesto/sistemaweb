<?php

class RankingPuntosAcumuladosTemplate extends Template {

	function titulo() {
		$titulo = '<div align="center"><h2>RANKING DE PUNTOS ACUMULADOS</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg) {
		return '<blink>'.$errormsg.'</blink>';
	}

	function listado($registros, $iAlmacen, $dIni, $dFin) {
		$contador 	= 0;
		$listado 	= '';
	
		if(count($registros) > 0) {
			$listado .= '
			<div id="resultados_grid" class="grid" align="center">
				<table width="70%">
					<caption ><hr></caption>
					<thead align="center" valign="center">
						<tr>
							<th class="grid_cabecera">&nbsp;</td>
							<th class="grid_cabecera">NUMERO CUENTA</td>
							<th class="grid_cabecera">CREACION CUENTA</td>
							<th class="grid_cabecera">DNI</td>
							<th class="grid_cabecera">CLIENTE</td>
							<th class="grid_cabecera">TELEFONO</td>
							<th class="grid_cabecera">PUNTOS ACUMULADOS</td>
							<th class="grid_cabecera">PUNTOS ACTUAL</td>
							<th class="grid_cabecera">ULTIMO DESPACHO</td>	
							<th class="grid_cabecera">SUCURSAL</td>	
						</tr>
					</thead>
					<tbody>
			';
			foreach($registros as $reg) {
				$color = ($contador%2==0?"grid_detalle_par":"grid_detalle_impar");
				$listado .='<tr>';
					$listado .='<td style="cursor:pointer" align="center" class="'.$color.'"><img src="../inventarios/images/plus.gif" id="img' . htmlentities($reg['cuenta']) . htmlentities($reg['tarjeta']) . '" onclick="javascript:getDetalleMovimientosFidelizacion(\''.htmlentities($reg['cuenta']).'\',\''.htmlentities($reg['tarjeta']).'\',\''.htmlentities($iAlmacen).'\',\''.htmlentities($dIni).'\',\''.htmlentities($dFin).'\')" /></td>';
					$listado .='<td align="center" class="'.$color.'">'.$reg["cuenta"].'</td>';
					$listado .='<td align="center" class="'.$color.'">'.$reg["fecha_creacion_cuenta"].'</td>';
					$listado .='<td align="center" class="'.$color.'">'.$reg["dni"].'</td>';
					$listado .='<td class="'.$color.'">'.$reg["cliente"].'</td>';
					$listado .='<td align="right" class="'.$color.'">'.$reg["telefono"].'</td>';
					$listado .='<td align="right" class="'.$color.'">'.$reg["puntosacumulados"].'</td>';
					$listado .='<td align="right" class="'.$color.'">'.$reg["nu_puntaje_actual"].'</td>';
					$listado .='<td align="center" class="'.$color.'">'.$reg["ultdespacho"].'</td>';
					$listado .='<td align="center" class="'.$color.'">'.$reg["ch_sucursal"].'</td>';
				$listado .= '</tr>';
				$listado .= '<tr style="display:none;" id="tr' . htmlentities($reg['cuenta']) . htmlentities($reg['tarjeta']) . '">';
					$listado .= '<td>&nbsp;</td>';
					$listado .= '<td colspan="9"><div id="div' . htmlentities($reg['cuenta']) . htmlentities($reg['tarjeta']) . '" name="div'. htmlentities($reg['cuenta']) . htmlentities($reg['tarjeta']) . '">Cargando...</div></td>';
				$listado .= '</tr>';
				$contador++;
			}
			$listado .= '
					</tbody>
				</table>
			</div>';
		}
		return $listado;
	}

	function gridViewHTMLDetail($arrDataDetalle) {

		$result  = '';
		$result .= '<table border="0" align="left" class="report_CRUD">';
			$result .= '<tr bgcolor="#FFFFCD">';
				$result .= '<th class="grid_cabecera">NRO. TARJETA</td>';
				$result .= '<th class="grid_cabecera">FECHA Y HORA</td>';
				$result .= '<th class="grid_cabecera">TIPO MOV.</td>';
				$result .= '<th class="grid_cabecera">T.D.</td>';
				$result .= '<th class="grid_cabecera">CAJA</td>';
				$result .= '<th class="grid_cabecera"># TICKET</td>';
				$result .= '<th class="grid_cabecera">PRODUCTO</td>';
				$result .= '<th class="grid_cabecera">PUNTOS</td>';
				$result .= '<th class="grid_cabecera">USUARIO</td>';
				$result .= '<th class="grid_cabecera">ALMACEN</td>';
			$result .= '</tr>';
			$result .= '<tbody>';
			$counter = 0;
				foreach ($arrDataDetalle as $row) {
					$color = ($counter%2) == 0 ? 'grid_detalle_impar' : 'grid_detalle_par';
					$result .= '<tr class="'. $color. '">';
						$result .= '<td align="center">' . htmlentities($row['tarjeta']) . '</td>';
						$result .= '<td align="center">' . htmlentities($row['fe_emision_punto']) . '</td>';
						$result .= '<td align="center">' . htmlentities($row['no_punto']) . '</td>';
						$result .= '<td align="center">' . htmlentities($row['no_tipo_documento']) . '</td>';
						$result .= '<td align="center">' . htmlentities($row['nu_caja']) . '</td>';
						$result .= '<td align="center">' . htmlentities($row['nu_ticket']) . '</td>';
						$result .= '<td align="center">' . htmlentities($row['no_producto']) . '</td>';
						$result .= '<td align="center">' . htmlentities($row['nu_puntaje']) . '</td>';
						$result .= '<td align="center">' . htmlentities($row['no_usuario']) . '</td>';
						$result .= '<td align="center">' . htmlentities($row['ch_sucursal']) . '</td>';
					$result .= '</tr>';
					$counter++;
				}
			$result .= '</tbody>';
		$result .= '</table>';
		return $result;
	}
		   
	function formBuscar($almacenes, $dIni, $dFin) {
		$almacenes[''] = "Todos los Almacenes";
	
		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.RANKINGPUNTOSACUMULADOS'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RANKINGPUNTOSACUMULADOS'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));

	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacén: </td>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
		          $form->addElement(FORM_GROUP_MAIN, new f2element_combo('almacen', '', '', $almacenes, espacios(3), array("onfocus" => "getFechasIF();"), ''));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
		          $form->addElement(FORM_GROUP_MAIN, new f2element_text("fechainicio", "", $dIni, '', 12, 10));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));

		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
		          $form->addElement(FORM_GROUP_MAIN, new f2element_text("fechafin", "", $dFin, '', 12, 10));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			if($_REQUEST['estado'] == 'N') {
				$op1 = ' '; 
				$op2 = ' selected';
			} else {
				$op1 = ' selected'; 
				$op2 = ' ';
			}

	    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Solo Cuentas Activas: </td>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
			        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<select name="estado" id="cuenta" class="form_combo">'));
				        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="S"'.$op1.'>Si </option>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<option value="N"'.$op2.'>No </option>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</select>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		      $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Consultar"><img src="/sistemaweb/icons/gbuscar.png" align="right" alt="right" />Buscar </button>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button id="btn-excel" name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" alt="right" />Excel </button>'));
		        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
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

	function generaCSV() {
		return '<script> window.open("/sistemaweb/combustibles/promociones/ranking_puntos.csv","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}

	function formPaginacion($paginacion, $fechaini, $fechafin, $intListaPuntos, $sucursal) {

		$form = new form2('', 'Paginacion', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.RANKINGPUNTOSACUMULADOS'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'RANKINGPUNTOSACUMULADOS'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));

		if($intListaPuntos > 0) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].'de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$fechaini."','".$fechafin."','".$sucursal."')")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$fechaini."','".$fechafin."','".$sucursal."')")));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value,'".$fechaini."','".$fechafin."','".$sucursal."')")));	
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$fechaini."','".$fechafin."','".$sucursal."')")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$fechaini."','".$fechafin."','".$sucursal."')")));
			$form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."','".$fechaini."','".$fechafin."','".$sucursal."')")));
		}
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
	
		return $form->getForm();
	}
	
	function formRankingPuntosAcumulados($intListaPuntos) {
		
		$form = new form2(' ', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control','');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table width="100%" border="0" cellspacing="2" cellpadding="2">'));
		if($intListaPuntos > 0) {
		
		} else {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="msg_informacion"><img src="/sistemaweb/icons/messagebox_info32x32.png" border="0">No existe informacion para la consulta realizada.</td><tr>'));
		}
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
			
		return $form->getForm();
	}	
}
