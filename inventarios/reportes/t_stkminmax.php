<?php

class StkMinMaxTemplate extends Template {  
    
	function searchForm() {
		$periodo = date(Y);
		$mes = date(m);  
		$almacenes = StkMinMaxModel::obtieneListaEstaciones();
		$ordenar = Array(	"D"=>"Descripcion", 
					"L"=>"Linea");

		$form = new form2('<b> Stock M&iacute;nimo y M&aacute;ximo </b>', 'form_stkminmax', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.STKMINMAX"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "Almac&eacute;n:", $_SESSION['almacen'], $almacenes, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("periodo", "Periodo", $periodo, '', 4, 6));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbspMes : &nbsp;&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes", "", $mes, '', 2, 4));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('opcion', 'Solo items por debajo del limite', 'S', ''));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('orden','Ordenar por: ', '', $ordenar, '&nbsp', '',''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.gif" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left"/> Excel</button>&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="fm" value="" onClick="javascript:parent.location.href=\'/sistemaweb/ventas_clientes/reportes/pdf/StockMinimoMaximo.pdf\';return false"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));		
	
		return $form->getForm();
    	}
    
    	function reporte($res, $opcion, $almacen, $periodo, $mes, $orden) {

    	echo "<pre>";
    	var_dump($res);
    	echo "</pre>";

		$result  = '';
		$result .= '<table style="border: 1; border-style: simple; border-color: #000000;" align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;C&oacute;digo&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Descripci&oacute;n&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Stock&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Stock Minimo&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Stock Maximo&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Requerimiento Minimo&nbsp;&nbsp;</th>';
		$result .= '<th class="grid_cabecera">&nbsp;&nbsp;Requerimiento Maximo&nbsp;&nbsp;</th>';
		$result .= '</tr>';

		$lin = '';
		for ($i=0; $i<count($res); $i++) {
			if($opcion == 'S') {
				if($res[$i]['actual'] < $res[$i]['minimo']) {
					if($orden == "L") {
						if($res[$i]['linea'] != $lin) {
							$result .= '<tr bgcolor="#FFFFAA"><td colspan="7">'.$res[$i]['linea'].' - '.$res[$i]['deslinea'].'</td></tr>';
							$lin = $res[$i]['linea'];
						}
					}
					$result .= '<tr>';
					$result .= '<td align="center">&nbsp;'.trim($res[$i]['codigo']).'&nbsp;</td>';
					$result .= '<td align="left">&nbsp;'.trim($res[$i]['descripcion']).'&nbsp;</td>';
					$result .= '<td align="center">&nbsp;'.trim($res[$i]['actual']).'&nbsp;</td>';
					$result .= '<td align="center">&nbsp;'.trim($res[$i]['minimo']).'&nbsp;</td>';
					$result .= '<td align="center">&nbsp;'.trim($res[$i]['maximo']).'&nbsp;</td>';
					$result .= '<td align="center">&nbsp;'.trim($res[$i]['reqminimo']).'&nbsp;</td>';
					$result .= '<td align="center">&nbsp;'.trim($res[$i]['reqmaximo']).'&nbsp;</td>';
					$result .= '</tr>';
				}
			} else {
				if($orden == "L") {
					if($res[$i]['linea'] != $lin) {
						$result .= '<tr bgcolor="#FFFFAA"><td colspan="7">'.$res[$i]['linea'].' - '.$res[$i]['deslinea'].'</td></tr>';
						$lin = $res[$i]['linea'];
					}
				}
				if($res[$i]['actual']<$res[$i]['minimo']) {
					$color = 'style="color:red"';
				} else {
					if($res[$i]['actual']>$res[$i]['maximo']) 
						$color = 'style="color:orange"';
					else
						$color = '';
				}
				
				$result .= '<tr>';
				$result .= '<td align="center" '.$color.' >&nbsp;'.trim($res[$i]['codigo']).'&nbsp;</td>';
				$result .= '<td align="left" '.$color.' >&nbsp;'.trim($res[$i]['descripcion']).'&nbsp;</td>';
				$result .= '<td align="center" '.$color.' >&nbsp;'.trim($res[$i]['actual']).'&nbsp;</td>';
				$result .= '<td align="center" '.$color.' >&nbsp;'.trim($res[$i]['minimo']).'&nbsp;</td>';
				$result .= '<td align="center" '.$color.' >&nbsp;'.trim($res[$i]['maximo']).'&nbsp;</td>';
				$result .= '<td align="center" '.$color.' >&nbsp;'.trim($res[$i]['reqminimo']).'&nbsp;</td>';
				$result .= '<td align="center" '.$color.' >&nbsp;'.trim($res[$i]['reqmaximo']).'&nbsp;</td>';
				$result .= '</tr>';
			}		    	
		}		
		$result .= '</table>';

		// *************************** Reporte PDF ********************************* //
		$nomalmacen = StkMinMaxModel::obtieneNombreEstacion($almacen);

		$cab = Array(
				"codigo"	=>	"CODIGO",
				"descripcion"	=>	"DESCRIPCION",
				"actual"	=>	"STK. ACTUAL",
				"minimo"	=>	"STK. MINIMO",
				"maximo"	=>	"STK. MAXIMO",
				"reqminimo"	=>	"REQ. MINIMO",
				"reqmaximo"	=>	"REQ. MAXIMO"
			);

		$reporte = new CReportes2("P","pt","A4");

		$reporte->Ln();	 
		$reporte->definirCabecera(2, "L", " ");
		$reporte->definirCabecera(2, "R", "Pagina %p");
		$reporte->definirCabeceraSize(3, "C", "courier,B,13", "STOCK MINIMO Y MAXIMO");
		$reporte->definirCabecera(4, "L", "ALMACEN  : ".$nomalmacen);
		$reporte->definirCabecera(5, "L", "PERIODO  : ".$periodo. "   MES: ".$mes);
		$reporte->definirCabecera(6, "C", " ");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 9);

		$reporte->definirColumna("codigo",$reporte->TIPO_TEXTO,13,"L", "_pri");
		$reporte->definirColumna("descripcion",$reporte->TIPO_TEXTO,30,"L", "_pri");
		$reporte->definirColumna("actual",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("minimo",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("maximo",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("reqminimo",$reporte->TIPO_IMPORTE,10,"R", "_pri");
		$reporte->definirColumna("reqmaximo",$reporte->TIPO_IMPORTE,10,"R", "_pri");

		$reporte->definirColumna("dato1",$reporte->TIPO_TEXTO,13,"C", "_seg");
		$reporte->definirColumna("dato2",$reporte->TIPO_TEXTO,40,"L", "_seg");

		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab, "_pri");
		$reporte->AddPage();

		$lin2 = '';
		for ($j=0; $j<count($res); $j++) {
			if($opcion == 'S') {
				if($res[$j]['actual'] < $res[$j]['minimo']) {				

					if($orden == "L") {
						if($res[$j]['linea'] != $lin2) {
							$reporte->Ln();	
							$arr = array("dato1"=>$res[$j]['linea'], "dato2"=>"*** ".trim($res[$j]['deslinea'])." ***");
							$reporte->nuevaFila($arr, "_seg"); 							
							$lin2 = $res[$j]['linea'];
						}
					}

					$arr = array("codigo"=>$res[$j]['codigo'], "descripcion"=>$res[$j]['descripcion'], "actual"=>$res[$j]['actual'], "minimo"=>$res[$j]['minimo'], "maximo"=>$res[$j]['maximo'], "reqminimo"=>$res[$j]['reqminimo'],"reqmaximo"=>$res[$j]['reqmaximo']);
					$reporte->nuevaFila($arr, "_pri"); 	
				}
			} else {
				if($orden == "L") {
					if($res[$j]['linea'] != $lin2) {
						$reporte->Ln();	
						$arr = array("dato1"=>$res[$j]['linea'], "dato2"=>"*** ".trim($res[$j]['deslinea'])." ***");
						$reporte->nuevaFila($arr, "_seg"); 						
						$lin2 = $res[$j]['linea'];
					}
				}
				$arr = array("codigo"=>$res[$j]['codigo'], "descripcion"=>$res[$j]['descripcion'], "actual"=>$res[$j]['actual'], "minimo"=>$res[$j]['minimo'], "maximo"=>$res[$j]['maximo'], "reqminimo"=>$res[$j]['reqminimo'],"reqmaximo"=>$res[$j]['reqmaximo']);
				$reporte->nuevaFila($arr, "_pri"); 	
			}		    	
		}
		$reporte->borrarCabecera();
		$reporte->borrarCabeceraPredeterminada();
		$reporte->Lnew();$reporte->Lnew();
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/StockMinimoMaximo.pdf", "F");
		
		return $result;
    	}

	function reporteExcel($res, $opcion, $almacen, $periodo, $mes, $orden){

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato1 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato3 =& $workbook->add_format();
		$formato4 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato1->set_top(1);
		$formato1->set_left(1);
		$formato1->set_border(0);
		$formato1->set_bold(1);
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato3->set_num_format(2);
		$formato4->set_num_format(2);
		$formato4->set_bold(1);
		$formato5->set_size(11);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Hoja de Resultados');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 50);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 16);
		$worksheet1->set_column(6, 6, 16);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$nomalmacen = StkMinMaxModel::obtieneNombreEstacion($almacen);
		$worksheet1->write_string(1, 0, "STOCK MINIMO Y MAXIMO",$formato0);
		$worksheet1->write_string(3, 0, "ALMACEN: ".$nomalmacen,$formato0);
		$worksheet1->write_string(4, 0, "PERIODO: ".$periodo."   MES: ".$mes,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 6;
		$worksheet1->write_string($a, 0, "CODIGO",$formato2);
		$worksheet1->write_string($a, 1, "DESCRIPCION",$formato2);
		$worksheet1->write_string($a, 2, "ACTUAL",$formato2);
		$worksheet1->write_string($a, 3, "MINIMO",$formato2);	
		$worksheet1->write_string($a, 4, "MAXIMO",$formato2);	
		$worksheet1->write_string($a, 5, "REQUER. MIN.",$formato2);	
		$worksheet1->write_string($a, 6, "REQUER. MAX.",$formato2);		

		$lin = '';
		for ($j=0; $j<count($res); $j++) {
			if($opcion == 'S') {
				if($res[$j]['actual']<$res[$j]['minimo']) {
					$a++;

					if($orden == "L") {
						if($res[$j]['linea'] != $lin) {
							$worksheet1->write_string($a, 0, $res[$j]['linea'],$formato4);
							$worksheet1->write_string($a, 1, $res[$j]['deslinea'],$formato4);
							$a++;
							$lin = $res[$j]['linea'];
						}
					}

					$worksheet1->write_string($a, 0, $res[$j]['codigo'],$formato5);
					$worksheet1->write_string($a, 1, $res[$j]['descripcion'],$formato5);
					$worksheet1->write_number($a, 2, number_format($res[$j]['actual'],2,'.',''),$formato3);	
					$worksheet1->write_number($a, 3, number_format($res[$j]['minimo'],2,'.',''),$formato3);
					$worksheet1->write_number($a, 4, number_format($res[$j]['maximo'],2,'.',''),$formato3);
					$worksheet1->write_number($a, 5, number_format($res[$j]['reqminimo'],2,'.',''),$formato3);	
					$worksheet1->write_number($a, 6, number_format($res[$j]['reqmaximo'],2,'.',''),$formato3);	
				}
			} else {
				$a++;

				if($orden == "L") {
					if($res[$j]['linea'] != $lin) {
						$worksheet1->write_string($a, 0, $res[$j]['linea'],$formato4);
						$worksheet1->write_string($a, 1, $res[$j]['deslinea'],$formato4);
						$a++;
						$lin = $res[$j]['linea'];
					}
				}

				$worksheet1->write_string($a, 0, $res[$j]['codigo'],$formato5);
				$worksheet1->write_string($a, 1, $res[$j]['descripcion'],$formato5);
				$worksheet1->write_number($a, 2, number_format($res[$j]['actual'],2,'.',''),$formato3);	
				$worksheet1->write_number($a, 3, number_format($res[$j]['minimo'],2,'.',''),$formato3);
				$worksheet1->write_number($a, 4, number_format($res[$j]['maximo'],2,'.',''),$formato3);
				$worksheet1->write_number($a, 5, number_format($res[$j]['reqminimo'],2,'.',''),$formato3);	
				$worksheet1->write_number($a, 6, number_format($res[$j]['reqmaximo'],2,'.',''),$formato3);		
			}		    	
		}
			
		$workbook->close();	

		$chrFileName = "StkMinimoMaximo";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");		
	}
}
