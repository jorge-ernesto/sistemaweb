<?php

class DepositosPosTemplate extends Template {

	function titulo() {
		return '<div align="center"><h2><b>Dep&oacute;sitos POS</b></h2></div>';
	}

	function formBuscar($almacen, $dia1, $turno1, $dia2, $turno2, $busqueda, $find) {

		$almacenes = DepositosPosModel::obtenerAlmacenes("");
        	$almacenes['TODOS'] = "Todos los Almacenes";
		
		if($almacen == "")
			$almacen = $_SESSION['almacen'];
			
		if($dia1 == "" or $dia2 == "") {
			$dia1 = date(d."/".m."/".Y);		
			$dia2 = date(d."/".m."/".Y);
		}
		$t1="";$t2="";$t3="";$t4="";
		if ($find=="T") {$t1 = "checked";$t2 = "";$t3 = "";$t4 = "";}
		if ($find=="C") {$t1 = "";$t2 = "checked";$t3 = "";$t4 = "";}
		if ($find=="D") {$t1 = "";$t2 = "";$t3 = "checked";$t4 = "";}
		if ($find=="S") {$t1 = "";$t2 = "";$t3 = "";$t4 = "checked";}		
		
		$tipos = Array("T"=>"Trabajador", "C"=>"Correlativo", "N"=>"Num. Docum.", "S"=>"Serie");
		$tipomoneda = array("00"=>"Todos","01"=>"Monedas","02"=>"Billetes","03"=>"B y M");

		$form = new form2('', 'form_buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.DEPOSITOSPOS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="4">Almacen : '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("almacen", "", $almacen, $almacenes, "", array("onfocus" => "getFechaEmision();")));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2">DESDE&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Dia : '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("dia1", "", $dia1, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_buscar.dia1'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div style="position:relative"><div id="overDiv" style="position:absolute;float:right; visibility:hidden; z-index:0;"></div></div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>Turno :</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("turno1", "", $turno1, '', 2, 4));				

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2">HASTA&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Dia : '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("dia2", "", $dia2, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_buscar.dia2'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td>Turno :</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("turno2", "", $turno2, '', 2, 4));
				
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4">'));	
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Busqueda :&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("busqueda", "", $busqueda, '', 33, 40));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4">'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="find" value="T" '.$t1.' >Trabajador'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="find" value="C" '.$t2.' >Correlativo'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="find" value="D" '.$t3.' >Num.Docum.'));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="radio" name="find" value="S" '.$t4.' >Serie'));				
			
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center" colspan="4"> Denominacion: '));			
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("denomina", "", $tipomoneda, $tipomoneda, ""));				
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center" colspan="4"> '));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center" colspan="4">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" alt="left"/> Buscar</button>&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>&nbsp;&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Excel"><img src="/sistemaweb/icons/gexcel.png" alt="left"/> Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));		
		
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("almacen").focus();
			}
		</script>'
		));

		return $form->getForm();
    }
	
	function listado($resultados, $almacen, $dia1, $turno1, $dia2, $turno2, $busqueda, $find) {
		$res = Array();
		$res = $resultados['detalles'];
	
		$form = new form2('', 'form_listado', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MOVIMIENTOS.DEPOSITOSPOS"));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("almacen", $almacen));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("dia1", $dia1));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("turno1", $turno1));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("dia2", $dia2));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("turno2", $turno2));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("busqueda", $busqueda));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("find", $find));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Validar", espacios(5)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table><tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">VALIDA</th>'));				
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">DIA</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">TURNO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">TRABAJADOR</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">FECHA CREACION</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">FECHA ACTUALIZACION</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">SEQ</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">NUM</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">MONEDA</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">CAMBIO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">IMPORTE S/.</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">IMPORTE US$</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">DENOMINACION</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">OBSERVACION 1</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">USUARIO</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">IP</th>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		
		for ($i = 0; $i < count($res); $i++) {	
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$key = trim($res[$i]['almacen'])."+".trim($res[$i]['dia'])."+".trim($res[$i]['turno'])."+".trim($res[$i]['codtrab'])."+".trim($res[$i]['num']);
		
			if(trim($res[$i]['valida'])=="S" or trim($res[$i]['valida'])=="s") {
				$check = "checked disabled";
				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("conf[".$i."]", "V")); // ya ha sido validado
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="'.$color.'">'));
			} else {
				$check = "";
				$color = "grid_detalle_especial";
				$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("conf[".$i."]", "F")); // no validado o aún no ha sido validado
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="'.$color.'">'));
			}
						
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("<input type='checkbox' name='vec_check[".$i."]' value='S' ".$check." >"));
			$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("val[".$i."]", $key)); 
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['valida']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['dia']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['turno']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td class="'.$color.'">&nbsp;' . htmlentities($res[$i]['trabajador']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['fecha']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['fechaact']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['seq']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['num']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['moneda']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['cambio'], 4, '.', '')) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['soles'], 4, '.', '')) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($res[$i]['dolares'], 4, '.', '')) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['denominacion']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['observacion1']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['usuario']) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($res[$i]['ip']) . '</td>'));			
		}
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="11" align="right" class="grid_detalle_total">Cantidad de depositos validados: '.$resultados['totales']['sem'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.$resultados['totales']['semsol'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.$resultados['totales']['semdol'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">B: '.$resultados['totales']['sbilletes'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">M: '.$resultados['totales']['smonedas'].'</td><td colspan="2" class="grid_detalle_total">&nbsp</td></tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="11" align="right" class="grid_detalle_total">Cantidad de depositos totales: '.$resultados['totales']['can'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.$resultados['totales']['totsol'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">'.$resultados['totales']['totdol'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">B: '.$resultados['totales']['billetes'].'</td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">M: '.$resultados['totales']['monedas'].'</td><td colspan="2" class="grid_detalle_total">&nbsp</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table></center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit("action", "Validar", espacios(5)));	
		
		return $form->getForm();
    	}
    	
    	function reportePDF($resultados, $alma, $dia1, $turno1, $dia2, $turno2) {
    	
    		$res = Array();
		$res = $resultados['detalles'];
    	
		$cab = Array(
				"tipo"		=>	"TIPO",
				"dia"		=>	"DIA",
				"turno"		=>	"TURNO",
				"trabajador"	=>	"TRABAJADOR",
				"fecha"		=>	"FECHA CREACION",
				"fechaact"	=>	"FECHA ACTUALIZACION",
				"secuencia"	=>	"SEQ.",
				"numero"	=>	"NUM.",
				"moneda"	=>	"MONEDA",
				"cambio"	=>	"CAMBIO",																		
				"soles"		=>	"IMPORTE S/.",
				"dolares"	=>	"IMPORTE $",
				"denominacion"	=>	"DENOMIN.",
				"observacion1"	=>	"OBS."
			);

		$reporte = new CReportes2("L","pt","A4");

		$reporte->Ln();	 
		$reporte->definirCabecera(2, "L", " ");
		$reporte->definirCabecera(2, "R", "Pagina %p");
		$reporte->definirCabeceraSize(3, "C", "courier,B,15", "DEPOSITOS DIARIOS");
		$reporte->definirCabecera(4, "L", "ALMACEN  : ".$alma);
		$reporte->definirCabecera(5, "L", "FECHA DEL ".$dia1."  TURNO  ".$turno1."   AL   ".$dia2."  TURNO  ".$turno2);
		$reporte->definirCabecera(6, "L", "");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 6.8);

		$reporte->definirColumna("dia",$reporte->TIPO_TEXTO,10,"C", "_pri");
		$reporte->definirColumna("turno",$reporte->TIPO_TEXTO,5,"C", "_pri");
		$reporte->definirColumna("trabajador",$reporte->TIPO_TEXTO,35,"L", "_pri");
		$reporte->definirColumna("fecha",$reporte->TIPO_TEXTO,16,"L", "_pri");
		$reporte->definirColumna("fechaact",$reporte->TIPO_TEXTO,16,"L", "_pri");
		$reporte->definirColumna("secuencia",$reporte->TIPO_TEXTO,6,"C", "_pri");
		$reporte->definirColumna("numero",$reporte->TIPO_TEXTO,6,"C", "_pri");
		$reporte->definirColumna("moneda",$reporte->TIPO_TEXTO,6,"C", "_pri");
		$reporte->definirColumna("cambio",$reporte->TIPO_NUMERO,8,"C", "_pri");
		$reporte->definirColumna("soles",$reporte->TIPO_NUMERO,12,"R", "_pri");
		$reporte->definirColumna("dolares",$reporte->TIPO_NUMERO,12,"R", "_pri");
		$reporte->definirColumna("denominacion",$reporte->TIPO_TEXTO,10,"C", "_pri");
		$reporte->definirColumna("observacion1",$reporte->TIPO_TEXTO,15,"C", "_pri");
				

		$reporte->borrarCabeceraPredeterminada();
		$reporte->definirCabeceraPredeterminada($cab, "_pri");
		$reporte->AddPage();
		$reporte->Ln();	

		for ($i = 0; $i<count($res); $i++) {
	
			if($res[$i]['valida']=="S" or $res[$i]['valida']=="s"){				
			
				$arr = array(					
						"dia"		=>	$res[$i]['dia'],
						"turno"		=>	$res[$i]['turno'],
						"trabajador"	=>	$res[$i]['trabajador'],
						"fecha"		=>	$res[$i]['fecha'],
						"fechaact"		=>	$res[$i]['fechaact'],
						"secuencia"	=>	$res[$i]['seq'],
						"numero"	=>	$res[$i]['num'],
						"moneda"	=>	$res[$i]['moneda'],
						"cambio"	=>	$res[$i]['cambio'],																		
						"soles"		=>	$res[$i]['soles'],
						"dolares"	=>	$res[$i]['dolares'],
						"denominacion"	=>	$res[$i]['denominacion'],
						"observacion1"	=>	$res[$i]['observacion1']
					);
				$reporte->nuevaFila($arr, "_pri"); 	
			}
		}
		$reporte->Ln();	
		$reporte->lineaH();
		$arr = array("dia"=>"", "turno"=>"","trabajador"=>"","fecha"=>"Cantidad: ".$resultados['totales']['sem'],"fechaact"=>"","secuencia"=>"","numero"=>"","moneda"=>"","cambio"=>"","soles"=>$resultados['totales']['semsol'],"dolares"=>$resultados['totales']['semdol']);
		$reporte->nuevaFila($arr, "_pri"); 	

		$reporte->borrarCabecera();
		$reporte->borrarCabeceraPredeterminada();
		$reporte->Lnew();$reporte->Lnew();				
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/Depositos.pdf", "F");
	
		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/Depositos.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';	
	}

	function reporteExcel($resultados, $alma, $dia1, $turno1, $dia2, $turno2) {
	
    	$res = Array();
		$res = $resultados['detalles'];

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
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

		$worksheet1->write_string(1, 0, "DEPOSITOS DIARIOS",$formato0);
		$worksheet1->write_string(3, 0, "ALMACEN: ".$alma,$formato0);
		$worksheet1->write_string(4, 0, "FECHA DEL ".$dia1."  TURNO  ".$turno1."   AL   ".$dia2."  TURNO  ".$turno2,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 7;
		
		$worksheet1->write_string($a, 0, "DIA",$formato2);
		$worksheet1->write_string($a, 1, "TURNO",$formato2);
		$worksheet1->write_string($a, 2, "TRABAJADOR",$formato2);
		$worksheet1->write_string($a, 3, "FECHA CREACION",$formato2);	
		$worksheet1->write_string($a, 4, "FECHA ACTUALIZACION",$formato2);	
		$worksheet1->write_string($a, 5, "SECUENCIA",$formato2);
		$worksheet1->write_string($a, 6, "NUMERO",$formato2);
		$worksheet1->write_string($a, 7, "MONEDA",$formato2);
		$worksheet1->write_string($a, 8, "CAMBIO",$formato2);
		$worksheet1->write_string($a, 9, "SOLES",$formato2);
		$worksheet1->write_string($a, 10, "DOLARES",$formato2);												
		
		$a = 8;	

		for ($j=0; $j<count($res); $j++) {
			if($res[$j]['valida']=="S" or $res[$j]['valida']=="s"){		
							
				$worksheet1->write_string($a, 0, $res[$j]['dia'],$formato5);
				$worksheet1->write_string($a, 1, $res[$j]['turno'],$formato5);
				$worksheet1->write_string($a, 2, $res[$j]['trabajador'],$formato5);	
				$worksheet1->write_string($a, 3, $res[$j]['fecha'],$formato5);
				$worksheet1->write_string($a, 4, $res[$j]['fechaact'],$formato5);
				$worksheet1->write_string($a, 5, $res[$j]['seq'],$formato5);
				$worksheet1->write_string($a, 6, $res[$j]['num'],$formato5);	
				$worksheet1->write_string($a, 7, $res[$j]['moneda'],$formato5);	
				$worksheet1->write_string($a, 8, $res[$j]['cambio'],$formato5);	
				$worksheet1->write_string($a, 9, $res[$j]['soles'],$formato5);	
				$worksheet1->write_string($a, 10, $res[$j]['dolares'],$formato5);							
				$a++;
			}
		}
			
		$workbook->close();	

		$chrFileName = "Depositos";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");	
	}
}
