<?php

class TransTrabajadorTemplate extends Template {

	function titulo() {
		return '<h2 align="center" style="color:#336699;"><b>Informe de Número de Transacciones de Venta por Trabajador</b></h2>';
	}
	
	function formBuscar(){
		$tipo 			= Array("T"=>"Todos", "C"=>"Combustible", "M"=>"Market");
		$tiporeporte	= Array("R"=>"Resumido", "D"=>"Detallado");	

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.TRANSTRABAJADOR"));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TRANSTRABAJADOR'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Año: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text ('periodo', '', $periodo, '', 4, 4));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Mes: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
					$form->addElement(FORM_GROUP_MAIN,  new f2element_text ('mes', '', $mes,'', 2, 2));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="2" align="center">Día Del: '));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text ('diadesde', '', $dia, '', 2, 2));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(' Al: '));
					$form->addElement(FORM_GROUP_MAIN, new f2element_text ('diahasta', '', $dia, '', 2, 2));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Tipo de Venta: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tipo", "", "", $tipo, ""));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Tipo de Reporte: </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_combo("tiporeporte", "", "", $tiporeporte, ""));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
	        	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Considerar Trabajador: </td><td>'));
	        		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('t', '', 'S', ''));
	        	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
	        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
	        	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Considerar Caja: </td><td>'));
	        		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('sConsiderarCaja', '', 1, ''));
	        	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
	        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
	        	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Considerar Producto: </td><td>'));
	        		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('iActiveProduct', '', 1, ''));
	        	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
	        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
	        	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Considerar Cantidad: </td><td>'));
	        		$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('iActiveQuantity', '', 1, ''));
	        	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
	        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" colspan="2">'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Reporte">Reporte</button>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		return $form->getForm();
	}

	function mostrar($res, $tiporeporte, $sConsiderarCaja, $arrParams) {
		$trab =  "";
		$sumtot = 0;
		$sumtrans = 0;
		$totalventa = 0;
		$totaltrans = 0;

		$form = new form2('', 'form_mostrar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.TRANSTRABAJADOR"));
		
		if ( $tiporeporte == "D" ){//Reporte Detallatado
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">FECHA</th>'));
					if ( $sConsiderarCaja=='1' ) {
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">CAJA</th>'));
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">TURNO</th>'));
					if ($arrParams['iActiveProduct']=='1'){
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">PRODUCTO</th>'));
					}
					if ($arrParams['iActiveQuantity']=='1'){
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">CANTIDAD</th>'));
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">TOTAL DE VENTA</th>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">NRO. TRANSACCIONES</th>'));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		
			if ( $res["sStatus"] != "success" ) {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center" class="grid_detalle_total">'.$res["sMessage"].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));				
			} else {
				$iColspanTotalTrabajador = ($sConsiderarCaja=='1' ? 3 : 2);
				$iColspanNombreTrabajador = ($sConsiderarCaja=='1' ? 5 : 4);

				if ($arrParams['iActiveProduct']=='1'){
					++$iColspanTotalTrabajador;
					++$iColspanNombreTrabajador;
				}

				if ($arrParams['iActiveQuantity']=='1'){
					++$iColspanTotalTrabajador;
					++$iColspanNombreTrabajador;
				}

				$res = $res["arrData"];
				for ( $i = 0; $i < count($res); $i++ ) {
					$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
					if( $trab != $res[$i]['cod_trab'] ){
					 	if( $i!=0 ){//total por trabajador
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="'.$iColspanTotalTrabajador.'" align="right" class="grid_detalle_total" colspan="4">Total Trabajador</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$sumtot.'</td>'));	
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$sumtrans.'</td>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
							$sumtot=0;					
							$sumtrans=0;
						}
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="'.$iColspanNombreTrabajador.'" align="left" class="grid_detalle_especial">'.$res[$i]['cod_trab'].$res[$i]['nom_trab'].'</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
						$trab = $res[$i]['cod_trab'];
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));		
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">'.htmlentities($res[$i]['dia']).'</td>'));
						if ( $sConsiderarCaja == '1' ) {
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">'.htmlentities($res[$i]['caja']).'</td>'));
						}
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">'.htmlentities($res[$i]['turno']).'</td>'));
						if ( $arrParams['iActiveProduct']=='1' ) {
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" class="'.$color.'">'.htmlentities($res[$i]['codigo'] . ' - ' . $res[$i]['no_producto']).'</td>'));
						}
						if ( $arrParams['iActiveQuantity']=='1' ) {
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($res[$i]['cantidad']).'</td>'));
						}
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($res[$i]['ventatotal']).'</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($res[$i]['num_trans']).'</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
					$sumtot = $sumtot + $res[$i]['ventatotal'];
					$sumtrans = $sumtrans + $res[$i]['num_trans'];	
					$totalventa = $totalventa + $res[$i]['ventatotal'];
					$totaltrans = $totaltrans + $res[$i]['num_trans'];
				}

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="'.$iColspanTotalTrabajador.'" align="right" class="grid_detalle_total" colspan="4">Total Trabajador</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$sumtot.'</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$sumtrans.'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="'.$iColspanTotalTrabajador.'" align="right" class="grid_detalle_total">TOTAL</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$totalventa.'</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$totaltrans.'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			}
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		} else {//Reporte Resumido
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">TRABAJADOR</th>'));		
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">TOTAL DE VENTA</th>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">NRO. DE TRANSACCIONES</th>'));	
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			if ( $res["sStatus"] != "success" ) {
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center" class="grid_detalle_total">'.$res["sMessage"].'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));				
			} else {
				$res = $res["arrData"];
				$trab2 = $res[0]['cod_trab'];
				for ( $i = 0; $i < count($res); $i++ ) {
					$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
					if( $trab2 == $res[$i]['cod_trab'] ){
						$sumtot = $sumtot + $res[$i]['ventatotal'];
						$sumtrans = $sumtrans + $res[$i]['num_trans'];
						$trab2 = $res[$i]['cod_trab'];
					} else {
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));		
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" class="'.$color.'">'.htmlentities($res[$i-1]['cod_trab']) . ' ' . htmlentities($res[$i-1]['nom_trab']).'</td>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($sumtot).'</td>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($sumtrans).'</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
						$sumtot=0;
						$sumtrans=0;
						$trab2 = $res[$i]['cod_trab'];			
						$sumtot = $sumtot + $res[$i]['ventatotal'];					
						$sumtrans = $sumtrans + $res[$i]['num_trans'];
					}
					$totalventa = $totalventa+$res[$i]['ventatotal'];
					$totaltrans = $totaltrans+$res[$i]['num_trans'];					
				}
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));		
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" class="'.$color.'">'.htmlentities($res[$i-1]['cod_trab']) . ' ' . htmlentities($res[$i-1]['nom_trab']).'</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($sumtot).'</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($sumtrans).'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));		
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">T O T A L</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.htmlentities($totalventa).'</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.htmlentities($totaltrans).'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			}
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		}
		return $form->getForm();
    }	

    function mostrarMD($res, $tiporeporte, $sConsiderarCaja, $arrParams) {		
		$trab =  "";
		$d = "";
		$t = "";
		$sumtot = 0;
		$sumtrans = 0;
		$totalventa = 0;
		$totaltrans = 0;
		
		$form = new form2('', 'form_mostrarmd', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.TRANSTRABAJADOR"));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">FECHA</th>'));
				if ( $sConsiderarCaja == "1" ) {
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">CAJA</th>'));
				}
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">TURNO</th>'));
				if ($arrParams['iActiveProduct']=='1'){
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">PRODUCTO</th>'));
				}
				if ($arrParams['iActiveQuantity']=='1'){
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">CANTIDAD</th>'));
				}
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">TOTAL DE VENTA</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">NRO. TRANSACCIONES</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

		if ( $res["sStatus"] != "success" ) {
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="5" align="center" class="grid_detalle_total">'.$res["sMessage"].'</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));				
		} else {
			$iColspanTotalTrabajador = ($sConsiderarCaja=='1' ? 3 : 2);
			$iColspanNombreTrabajador = ($sConsiderarCaja=='1' ? 5 : 4);

			if ($arrParams['iActiveProduct']=='1'){
				++$iColspanTotalTrabajador;
				++$iColspanNombreTrabajador;
			}

			if ($arrParams['iActiveQuantity']=='1'){
				++$iColspanTotalTrabajador;
				++$iColspanNombreTrabajador;
			}

			$res = $res["arrData"];
			for ( $i = 0; $i < count($res); $i++ ) {	
				$color=($i%2==0?"grid_detalle_par":"grid_detalle_impar");
				if( $trab != $res[$i]['cod_trab'] ){
				 	if( $i != 0 ) {//total por trabajador
				 		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="'.$iColspanTotalTrabajador.'" align="right" class="grid_detalle_total" colspan="4">Total Trabajador</td>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$sumtot.'</td>'));	
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$sumtrans.'</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
						$sumtot = 0;					
						$sumtrans = 0;
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" class="grid_detalle_especial" colspan="'.$iColspanNombreTrabajador.'">'.$res[$i]['cod_trab'].' - '.$res[$i]['nom_trab'].'</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
					$trab = $res[$i]['cod_trab'];				
				}
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));		
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">'.htmlentities($res[$i]['dia']).'</td>'));
					if ( $sConsiderarCaja == "1" ) {
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">'.htmlentities($res[$i]['caja']).'</td>'));
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">'.htmlentities($res[$i]['turno']).'</td>'));
					if ( $arrParams['iActiveProduct']=='1' ) {
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" class="'.$color.'">'.htmlentities($res[$i]['articulo'] . ' - ' . $res[$i]['descripcion']).'</td>'));
					}
					if ( $arrParams['iActiveQuantity']=='1' ) {
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($res[$i]['cant_art']).'</td>'));
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($res[$i]['ventatotal']).'</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">'.htmlentities($res[$i]['num_trans']).'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				$sumtot = $sumtot + $res[$i]['ventatotal'];
				$sumtrans = $sumtrans + $res[$i]['num_trans'];	
				$totalventa = $totalventa + $res[$i]['ventatotal'];
				$totaltrans = $totaltrans + $res[$i]['num_trans'];
			}
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="'.$iColspanTotalTrabajador.'" align="right" class="grid_detalle_total">Total Trabajador</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$sumtot.'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$sumtrans.'</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="'.$iColspanTotalTrabajador.'" align="right" class="grid_detalle_total">TOTAL</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$totalventa.'</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="grid_detalle_total" align="right">'.$totaltrans.'</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		}
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		return $form->getForm();
    }
}