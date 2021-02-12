<?php

class ConsultaCuentaxCobrarTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Consulta de Saldos de Cuentas por Cobrar</b></h2>';
    	}
    
	function FormConsulta($cod_almacen) {

		$tipo = array("T" => "Todos", "AC" => "ACTIVO", "IN" => "INACTIVO");

		$sucursales = ConsultaCuentaxCobrarModel::obtenerSucursales("");
        $sucursales[''] = "Todos los Almacenes";

		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");	
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "REPORTES.CONSULTA"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $sucursales, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Tipo Cliente: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipo", "Tipo Cliente:", "", $tipo, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Consulta"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		
		return $form->getForm();
    	}

	function FormBuscar($resultados,$cobrarcab,$cobrardet,$anticiposcab,$anticiposdet,$valescab,$valesdet,$estacion,$cliente,$razsocial,$documento,$doc,$limite) {

		$result = "";
		$result .= '<table id="tabprincipal" align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera"></th>';
		$result .= '<th class="grid_cabecera"><b>CODIGO CLIENTE</b></th>';
		$result .= '<th class="grid_cabecera"><b>RAZON SOCIAL</b></th>';
		$result .= '<th class="grid_cabecera"><b>MONEDA</b></th>';
		$result .= '<th class="grid_cabecera"><b>DOC. POR COBRAR</b></th>';
		$result .= '<th class="grid_cabecera"><b>ANTICIPOS</b></th>';
		$result .= '<th class="grid_cabecera"><b>VALES PENDIENTES</b></th>';
		$result .= '<th class="grid_cabecera"><b>LIMITE DE CREDITO SOLES</b></th>';
		$result .= '<th class="grid_cabecera"><b>LINEA DISPONIBLE</b></th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
					
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];

			$total = $total + $a['total'];
			$total2 = $total2 + $a['total2'];
			$total3 = $total3 + $a['total3'];
	
			if($newcliente != $a['cliente']){
				$newcliente  = $a['cliente'];
				$clilimit    = ClienteModel::recuperarRegistroArray($newcliente);
			}

			$num = $num + 1;
			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'" align = "left">' . htmlentities($num) . '. </td>';
			$result .= '<td class="'.$color.'" align = "left">' . htmlentities($a['cliente']) . '</td>';
			$result .= '<td class="'.$color.'" align = "left">' . htmlentities($a['razsocial']) . '</td>';
			$result .= '<td class="'.$color.'" align = "left">' . htmlentities($a['moneda']) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total2'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['total3'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($a['credito'], 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities($clilimit[22]) . '</td>';
//			$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.CONSULTA&action=MostrarCab&&estacion='.$_REQUEST['estacion'].'&cliente='.htmlentities($a['cliente']).'&razsocial='.htmlentities($a['razsocial']).'&ctotal='.htmlentities($a['total']).'&ctotal2='.htmlentities($a['total2']).'&ctotal3='.htmlentities($a['total3']).'" onClick="foco();" target="control"><img src="/sistemaweb/images/all.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.CONSULTA&action=MostrarCab&&estacion='.$_REQUEST['estacion'].'&cliente='.htmlentities($a['cliente']).'&razsocial='.htmlentities($a['razsocial']).'" onClick="foco();" target="control" id="origen"><img src="/sistemaweb/images/all.gif" align="middle" border="0"/></A>&nbsp;</td>';
			$result .= '</tr>';
			
		}

			$result .= '<tr bgcolor="">';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '</tr>';

			$result .= '<tr bgcolor="">';
			$result .= '<td colspan = "3" class="'.$color.'" align = "right">TOTAL: </td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($total, 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($total2, 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($total3, 2, '.', ',')) . '</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'">&nbsp;</td>';
			$result .= '<td class="'.$color.'"></td>';
			$result .= '</tr>';

			$result .= '<tr bgcolor=""><td> </td></tr>';
			$result .= '<tr bgcolor=""><td> </td></tr>';
			$result .= '<tr bgcolor=""><td> </td></tr>';
			$result .= '<tr bgcolor=""><td> </td></tr>';
			$result .= '<tr bgcolor=""><td> </td></tr>';
			$result .= '<tr bgcolor=""><td> </td></tr>';
			$result .= '<tr bgcolor=""><td> </td></tr>';//

		$result .= '</table>';

		/* DOCUMENTOS CABECERA */
		if($cobrarcab != "" and $anticiposcab != "" and $valescab != ""){

			$result .= '<table align="center">';
			$result .= '<tr>';
			$result .= '<th colspan = "7" class="grid_cabecera"><b>DOCUMENTOS DE: ' . htmlentities($razsocial). ' RUC: ' . htmlentities($cliente). '</b></th>';
			$result .= '</tr>';
			$result .= '<tr>';
			$result .= '<th class="grid_cabecera"><b>DOC.</b></th>';
			$result .= '<th class="grid_cabecera"><b>NUMERO</b></th>';
			$result .= '<th class="grid_cabecera"><b>FECHA EMISION</b></th>';
			$result .= '<th class="grid_cabecera"><b>FECHA VENCIMIENTO</b></th>';
			$result .= '<th class="grid_cabecera"><b>MON</b></th>';
			$result .= '<th class="grid_cabecera"><b>IMPORTE</b></th>';
			$result .= '<th class="grid_cabecera"><b>SALDO</b></th>';
			$result .= '</tr>';

				for ($h = 0; $h < count($cobrarcab); $h++) {
		
				$color = ($h%2==0?"grid_detalle_par":"grid_detalle_impar");
				$d = $cobrarcab[$h];

				$vtotal = $vtotal + $d['total'];
				$vtotal2 = $vtotal2 + $d['saldo'];

				$result .= '<tr bgcolor="">';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($d['doc']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($d['documento']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($d['femision']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($d['fvencimiento']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($d['moneda']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($d['total']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($d['saldo']) . '</td>';
				$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.CONSULTA&action=MostrarDet&estacion='.htmlentities($estacion).'&cliente='.htmlentities($cliente).'&razsocial='.htmlentities($razsocial).'&documento='.htmlentities($d['documento']).'&doc='.htmlentities($d['doc']).'" onClick="focodet();" target="control" id ="origen2"><img src="/sistemaweb/images/all.gif" align="middle" border="0"/></A>&nbsp;</td>';
				$result .= '</tr>';
			
				}

				for ($j = 0; $j < count($anticiposcab); $j++) {
		
				$color = ($j%2==0?"grid_detalle_par":"grid_detalle_impar");
				$b = $anticiposcab[$j];

				$vtotal = $vtotal + $b['total'];
				$vtotal2 = $vtotal2 + $b['saldo'];

				$result .= '<tr bgcolor="">';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($b['doc']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($b['documento']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($b['femision']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($b['fvencimiento']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">S/.</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($b['total']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($b['saldo']) . '</td>';
				$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.CONSULTA&action=MostrarDet&estacion='.htmlentities($estacion).'&cliente='.htmlentities($cliente).'&razsocial='.htmlentities($razsocial).'&documento='.htmlentities($b['documento']).'&doc='.htmlentities($b['doc']).'" onClick="focodet();" target="control" id ="origen2"><img src="/sistemaweb/images/all.gif" align="middle" border="0"/></A>&nbsp;</td>';
				$result .= '</tr>';
			
				}

				for ($l = 0; $l < count($valescab); $l++) {
		
				$color = ($l%2==0?"grid_detalle_par":"grid_detalle_impar");
				$c = $valescab[$l];

				$vtotal = $vtotal + $c['total'];
				$vtotal2 = $vtotal2 + $c['saldo'];

				$result .= '<tr bgcolor="">';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($c['doc']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($c['documento']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($c['femision']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($c['fvencimiento']) . '</td>';
				$result .= '<td class="'.$color.'" align = "center">S/.</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($c['total']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($c['saldo']) . '</td>';
				$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.CONSULTA&action=MostrarDet&estacion='.htmlentities($estacion).'&cliente='.htmlentities($cliente).'&razsocial='.htmlentities($razsocial).'&documento='.htmlentities($c['documento']).'&doc='.htmlentities($c['doc']).'" onClick="focodet();" target="control" id ="origen2"><img src="/sistemaweb/images/all.gif" align="middle" border="0"/></A>&nbsp;</td>';
				$result .= '</tr>';
			
				}

				$result .= '<tr bgcolor="">';
				$result .= '<td colspan = "5" class="'.$color.'" align = "right">TOTAL: </td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($vtotal, 2, '.', ',')) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($vtotal2, 2, '.', ',')) . '</td>';
				$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.CONSULTA&action=Consulta&estacion='.htmlentities($estacion).'" onclick="subir();" target="control"><img src="/sistemaweb/images/arriba.png" align="middle" border="0"/></A>&nbsp;</td>';
				$result .= '<td class="'.$color.'"></td>';
				$result .= '</tr>';

				$result .= '<tr bgcolor=""><td> </td></tr>';
				$result .= '<tr bgcolor=""><td> </td></tr>';
				$result .= '<tr bgcolor=""><td> </td></tr>';
				$result .= '<tr bgcolor=""><td> </td></tr>';
				$result .= '<tr bgcolor=""><td> </td></tr>';
				$result .= '<tr bgcolor=""><td> </td></tr>';
				$result .= '<tr bgcolor=""><td> </td></tr>';

			$result .= '</table>';

		}

		/* DOCUMENTOS DETALLE */
		$result .= '<div id="destino" style="width:20px;height:20px;"></div>';

		if($cobrardet != "" and $anticiposdet != "" and $valesdet != ""){

			$result .= '<table id="tabdetalle" align="center">';
			$result .= '<tr>';
			$result .= '<th colspan = "5" class="grid_cabecera"><b>DETALLE '. htmlentities($doc). ' : '. htmlentities($documento). ' DOCUMENTOS  DE: ' . htmlentities($razsocial). ' RUC: ' . htmlentities($cliente). '</b></th>';
			$result .= '</tr>';
			$result .= '<tr>';
			$result .= '<th class="grid_cabecera"><b>ITEM</b></th>';
			$result .= '<th class="grid_cabecera"><b>DESCRIPCION</b></th>';
			$result .= '<th class="grid_cabecera"><b>PRECIO U.</b></th>';
			$result .= '<th class="grid_cabecera"><b>CANTIDAD</b></th>';
			$result .= '<th class="grid_cabecera"><b>TOTAL</b></th>';
			$result .= '</tr>';

			for ($m = 0; $m < count($cobrardet); $m++) {
		
				$color = ($m%2==0?"grid_detalle_par":"grid_detalle_impar");
				$e = $cobrardet[$m];

				$dettotal = $dettotal + $c['total'];

				$result .= '<tr bgcolor="">';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($e['item']) . '</td>';
				$result .= '<td class="'.$color.'" align = "left">' . htmlentities($e['producto']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($e['precio']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($e['cantidad']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($e['total'], 2, '.', ',')) . '</td>';
				$result .= '</tr>';
			
			}

			for ($n = 0; $n < count($anticiposdet); $n++) {
		
				$color = ($n%2==0?"grid_detalle_par":"grid_detalle_impar");
				$f = $anticiposdet[$n];

				$dettotal = $dettotal + $c['total'];

				$result .= '<tr bgcolor="">';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($f['item']) . '</td>';
				$result .= '<td class="'.$color.'" align = "left">' . htmlentities($f['producto']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($f['precio']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($f['cantidad']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($f['total'], 2, '.', ',')) . '</td>';
				$result .= '</tr>';
			
			}

			for ($o = 0; $o < count($valesdet); $o++) {
		
				$color = ($o%2==0?"grid_detalle_par":"grid_detalle_impar");
				$g = $valesdet[$o];

				$dettotal = $dettotal + $g['total'];

				$result .= '<tr bgcolor="">';
				$result .= '<td class="'.$color.'" align = "center">' . htmlentities($g['item']) . '</td>';
				$result .= '<td class="'.$color.'" align = "left">' . htmlentities($g['producto']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($g['precio']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities($g['cantidad']) . '</td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($g['total'], 2, '.', ',')) . '</td>';
				$result .= '</tr>';
			
			}
				$result .= '<tr bgcolor="">';
				$result .= '<td colspan = "4" class="'.$color.'" align = "right">TOTAL: </td>';
				$result .= '<td class="'.$color.'" align = "right">' . htmlentities(number_format($dettotal, 2, '.', ',')) . '</td>';
				$result .= '<td class="'.$color.'"><A href="control.php?rqst=REPORTES.CONSULTA&action=MostrarCab&estacion='.htmlentities($estacion).'&cliente='.htmlentities($cliente).'&razsocial='.htmlentities($razsocial).'" onclick="subirdet();" target="control"><img src="/sistemaweb/images/arriba.png" align="middle" border="0"/></A>&nbsp;</td>';
				$result .= '</tr>';

			$result .= '</table>';
		}

		$result .= '<div id="destino2" style="width:20px;height:20px;"></div>';
		return $result;

	}
}
