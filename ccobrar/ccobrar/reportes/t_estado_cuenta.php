<?php

include('../include/reportes2.inc.php');

class EstadoCuentaTemplate extends Template {

  	function titulo(){
    	$titulo = '<div align="center"><h2>REPORTE DE ESTADO DE CUENTA</h2></div><hr>';
    	return $titulo;
  	}

  	function errorResultado($errormsg){
    	return '<blink>'.$errormsg.'</blink>';
  	}

	function ReportePDF($result, $fecha) {
		$fecha_trans = $_REQUEST['busqueda']['fecha'];

		$cabecera1 = array(
					'CAMPO1'=>'',
					'CAMPO2'=>'',
					'CAMPO3'=>'',
					'IMPORTE'=>'IMPORTE TOTAL',
					'PAGO'=>'PAGOS',
					'SALDO'=>'SALDO'
				  );

		$cabecera2 = array(
					'MONEDA'=>'MONEDA',
					'DOCUMENTO'=>'DOCUMENTO',
					'FECHAE'=>'F. EMISION', 
					'INDOLARES'=>'DOLARES',
					'INSOLES'=>'SOLES',
					'PAGO_DOLARES'=>'DOLARES',
					'PAGO_SOLES'=>'SOLES',
					'SADOLARES'=>'DOLARES',
					'SASOLES'=>'SOLES',
				  );

		$CabCli 	= array( 	"NOMCLI"          =>  " "  );
		$contdocumento	= 0;
		$fontsize 	= 7;
		$reporte 	= new CReportes2();

		$reporte->SetMargins(8, 5, 8);
		$reporte->SetFont("courier", "", $fontsize);
		
		$reporte->definirColumna('CAMPO1', $reporte->TIPO_TEXTO, 7, 'L','Cabecera1');
		$reporte->definirColumna('CAMPO2', $reporte->TIPO_TEXTO, 16, 'L','Cabecera1');
		$reporte->definirColumna('CAMPO3',$reporte->TIPO_TEXTO, 11, 'R','Cabecera1');
		$reporte->definirColumna('IMPORTE',$reporte->TIPO_TEXTO, 47, 'C','Cabecera1');
		$reporte->definirColumna('PAGO',$reporte->TIPO_TEXTO, 5, 'C','Cabecera1');
		$reporte->definirColumna('SALDO',$reporte->TIPO_TEXTO, 23, 'R','Cabecera1');
		$reporte->definirColumna('MONEDA', $reporte->TIPO_TEXTO, 7, 'L');
		$reporte->definirColumna('DOCUMENTO', $reporte->TIPO_TEXTO, 25, 'L');
		$reporte->definirColumna('FECHAE',$reporte->TIPO_TEXTO, 10, 'R');
		$reporte->definirColumna('INDOLARES',$reporte->TIPO_IMPORTE, 12, 'R');
		$reporte->definirColumna('INSOLES',$reporte->TIPO_IMPORTE, 12, 'R');
		$reporte->definirColumna('PAGO_DOLARES',$reporte->TIPO_IMPORTE, 12, 'R');
		$reporte->definirColumna('PAGO_SOLES',$reporte->TIPO_IMPORTE, 12, 'R');
		$reporte->definirColumna('SADOLARES',$reporte->TIPO_IMPORTE, 12, 'R');
		$reporte->definirColumna('SASOLES',$reporte->TIPO_IMPORTE, 12, 'R');

		$reporte->definirColumna("NOMCLI", $reporte->TIPO_TEXTO, 100, "L", "CLI");

		$reporte->definirCabecera(1, "L", "OPENSOFT");
		$reporte->definirCabecera(1, "C", "ESTADO DE CUENTA AL ".$fecha_trans);
		$reporte->definirCabecera(1, "R", "PAG.    %p");
		$reporte->definirCabecera(2, "R", " ");
		$reporte->definirCabecera(3, "R", "%f");
		$reporte->definirCabecera(4, "L", ' ');
		
		$reporte->definirCabeceraPredeterminada($cabecera1,'Cabecera1');
		$reporte->definirCabeceraPredeterminada($cabecera2);
		$reporte->AddPage();
		$reporte->Ln();

		$contdocumento 	= 0;
		$conta 			= 0;
						
		for($i=0; $i<count($result); $i++) {

			if ($result[$i-1]['CLIENTE'] != $result[$i]['CLIENTE']){

				$reporte->Ln();
				$codigo	= trim($result[$i]['CLIENTE']). ' ' .trim($result[$i]['RAZONSOCIAL']);
				$arr	= array("NOMCLI"=>$codigo);
				$reporte->nuevaFila($arr, "CLI");
				$reporte->Ln();

			}

			$datos["MONEDA"] 	 = $result[$i]["MONEDA"];
			$datos["DOCUMENTO"]  = $result[$i]["DOCUMENTO"];
			$datos["FECHAE"] 	 = $result[$i]["FECHAEMISION"];

			if($result[$i]["MONEDA"] == 'S/'){

				$datos["INSOLES"] 	 = $result[$i]["IMPORTEINICIAL_SOLES"];
				$total_soles 		+= $result[$i]["IMPORTEINICIAL_SOLES"];
				$datos["INDOLARES"]  = 0.00;

				$datos["PAGO_SOLES"] 	= $result[$i]["PAGO_SOLES"];
				$pago_soles 			+= $result[$i]["PAGO_SOLES"];
				$datos["PAGO_DOLARES"] 	= 0.00;

				$datos["SASOLES"] 	 	= $result[$i]["SALDO_SOLES"];
				$saldo_soles 			+= $result[$i]["SALDO_SOLES"];
				$datos["SADOLARES"] 	= 0.00;

			}else{

				$datos["INSOLES"] 	 = 0.00;
				$total_dolares 		+= $result[$i]["IMPORTEINICIAL_DOLARES"];
				$datos["INDOLARES"]  = $result[$i]["IMPORTEINICIAL_DOLARES"];

				$datos["PAGO_SOLES"] 	= 0.00;
				$pago_dolares 			+= $result[$i]["PAGO_DOLARES"];
				$datos["PAGO_DOLARES"] 	= $result[$i]["PAGO_DOLARES"];

				$datos["SASOLES"] 	= 0.00;
				$datos["SADOLARES"] = $result[$i]["SALDO_DOLARES"];
				$saldo_dolares 		+= $result[$i]["SALDO_DOLARES"];

			}

			$reporte->nuevaFila($datos);

			if($result[$i]['CLIENTE'] != $result[$i+1]['CLIENTE']) {

				$totales_cliente['DOCUMENTO']	= "TOTAL: ";

				$totales_cliente["INSOLES"]			= $total_soles;
				$totales_cliente["INDOLARES"]		= $total_dolares;
				$totales_cliente["PAGO_SOLES"] 		= $pago_soles;
				$totales_cliente["PAGO_DOLARES"] 	= $pago_dolares;
				$totales_cliente["SASOLES"]			= $total_soles - $pago_soles;
				$totales_cliente["SADOLARES"]		= $total_dolares - $pago_dolares;
				//$totales_cliente["SASOLES"]			= $saldo_soles;
				//$totales_cliente["SADOLARES"]		= $saldo_dolares;

				$reporte->Ln();
				$reporte->nuevaFila($totales_cliente);
				$reporte->Ln();	
				$reporte->lineaH();

				$total_soles	= 0;
				$saldo_soles	= 0;

				$pago_soles		= 0;
				$pago_dolares	= 0;

				$total_dolares	= 0;
				$saldo_dolares	= 0;
			}
		}
		$reporte->Output("/sistemaweb/ccobrar/estado_cuenta_fecha.pdf", "F");
		return '<script>window.open("/sistemaweb/ccobrar/estado_cuenta_fecha.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}

	function formBuscar($fecha) {

		$estaciones=array('' => 'TODAS');

		//$type_client = array("0"=>"Todos", "S"=>"Anticipo", "1"=>"Credito", "2"=>"Efectivo");
		$type_client = array("0"=>"Todos", "S"=>"Anticipo", "1"=>"Credito");

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.ESTADOCUENTA'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ESTADOCUENTA'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('estacion', '', $almacen, $estaciones, espacios(3), array("onfocus" => "getFechaEmision();")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Busqueda Hasta: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "", $fecha, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.fecha'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td align="right">Tipo de Busqueda: </td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('busqueda[combo]','',$_REQUEST['busqueda']['combo'],array('01'=>'Todos','02'=>'Por Cliente'),espacios(3), array("onChange"=>"display_cod_cliente(this.value);")));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td id="celda1" style="display:none;" align="left">Cod. Cliente: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','', $_REQUEST['busqueda']['codigo'], espacios(5), 20, 18,array("class"=>"form_input_numeric", "style" =>$estilo)));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="right">Tipo Cliente: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("busqueda[Nu_Tipo_Cliente]", "", $_REQUEST['busqueda']['Nu_Tipo_Cliente'], $type_client, ""));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="HTML"><img src="/sistemaweb/icons/gbuscar.png" align="right" /> Consultar </button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" align="right" /> PDF </button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="EXCEL"><img src="/sistemaweb/icons/gexcel.png" align="right" /> Excel </button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("estacion").focus();
			}
		</script>'
		));
		return $form->getForm();
	}

	function gridViewHTML($arrResult) {
		$form = new form2('', 'form_mostrar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.ESTADOCUENTA"));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ESTADOCUENTA'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th row="2" colspan="3" class="grid_cabecera">&nbsp;&nbsp;&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2" class="grid_cabecera">&nbsp;&nbsp;IMPORTE TOTAL&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2" class="grid_cabecera">&nbsp;&nbsp;PAGOS&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2" class="grid_cabecera">&nbsp;&nbsp;SALDO&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;MONEDA&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;DOCUMENTO&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;F. EMISION&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;DOLARES&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;SOLES&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;DOLARES&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;SOLES&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;DOLARES&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;SOLES&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$nu_documento_identidad = '';
			$color = '';

			$sumTotalInicialSoles = 0.00;
			$sumTotalPagoSoles = 0.00;
			$sumTotalSaldoSoles = 0.00;

			$sumTotalInicialDolares = 0.00;
			$sumTotalPagoDolares = 0.00;
			$sumTotalSaldoDolares = 0.00;

			for ($i = 0; $i < count($arrResult); $i++) {
				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
				if($nu_documento_identidad != $arrResult[$i]['CLIENTE']){
				 	if($i!=0) {	
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="right" class="grid_detalle_total" colspan="4"><b>TOTAL CLIENTE:</b> </td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($sumTotalInicialDolares, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($sumTotalInicialSoles, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($sumTotalPagoDolares, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($sumTotalPagoSoles, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($sumTotalInicialDolares - $sumTotalPagoDolares, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($sumTotalInicialSoles - $sumTotalPagoSoles, 2, '.' , ',')) . '</td></tr>'));
						//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($sumTotalSaldoDolares, 2, '.' , ',')) . '</td>'));
						//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($sumTotalSaldoSoles, 2, '.' , ',')) . '</td></tr>'));
						
						$sumTotalInicialSoles = 0.00;
						$sumTotalPagoSoles = 0.00;
						$sumTotalSaldoSoles = 0.00;

						$sumTotalInicialDolares = 0.00;
						$sumTotalPagoDolares = 0.00;
						$sumTotalSaldoDolares = 0.00;
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="left" class="grid_detalle_especial" colspan="9"><b>CLIENTE:</b> '.$arrResult[$i]['CLIENTE'].' - '.$arrResult[$i]['RAZONSOCIAL'].'</td></tr>'));
					$nu_documento_identidad = $arrResult[$i]['CLIENTE'];				
				}
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($arrResult[$i]['MONEDA']) . '</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($arrResult[$i]['DOCUMENTO']) . '</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($arrResult[$i]['FECHAEMISION']) . '</td>'));


					$Ss_Saldo_Dolares = $arrResult[$i]["MONEDA"] == 'S/' ? 0 : $arrResult[$i]["SALDO_DOLARES"];

					if($arrResult[$i]["MONEDA"] == 'S/'){
						$sumTotalInicialSoles 	+= $arrResult[$i]["IMPORTEINICIAL_SOLES"];
						$sumTotalPagoSoles 		+= $arrResult[$i]["PAGO_SOLES"];
						$sumTotalSaldoSoles 	+= $arrResult[$i]["SALDO_SOLES"];

					} else {
						$sumTotalInicialDolares += $arrResult[$i]["IMPORTEINICIAL_DOLARES"];
						$sumTotalPagoDolares 	+= $arrResult[$i]["PAGO_DOLARES"];
						$sumTotalSaldoDolares 	+= $Ss_Saldo_Dolares;
					}

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['IMPORTEINICIAL_DOLARES'], 2, '.' , ',')) . '</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['IMPORTEINICIAL_SOLES'], 2, '.' , ',')) . '</td>'));

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['PAGO_DOLARES'], 2, '.' , ',')) . '</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['PAGO_SOLES'], 2, '.' , ',')) . '</td>'));

					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($Ss_Saldo_Dolares, 2, '.' , ',')) . '</td>'));
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['SALDO_SOLES'], 2, '.' , ',')) . '</td>'));

				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
			}

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		return $form->getForm();
	}

	function gridViewEXCEL($arrResult) {
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

		$worksheet1 =& $workbook->add_worksheet('Estado de Cta. Cte. del Cliente');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 25);
		$worksheet1->set_column(2, 2, 25);
		$worksheet1->set_column(3, 3, 15);
		$worksheet1->set_column(4, 4, 25);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(0, 3, "REPORTE DE ESTADO DE CUENTA",$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$fila = 2;
		$worksheet1->write_string($fila, 4, "IMPORTE TOTAL",$formato2);
		$worksheet1->write_string($fila, 6, "PAGOS",$formato2);
		$worksheet1->write_string($fila, 8, "SALDO",$formato2);
		
		++$fila;
		$worksheet1->write_string($fila, 0, "MONEDA",$formato2);
		$worksheet1->write_string($fila, 1, "DOCUMENTO",$formato2);
		$worksheet1->write_string($fila, 2, "F. EMISION",$formato2);
		$worksheet1->write_string($fila, 3, "DOLARES",$formato2);
		$worksheet1->write_string($fila, 4, "SOLES",$formato2);
		$worksheet1->write_string($fila, 5, "DOLARES",$formato2);
		$worksheet1->write_string($fila, 6, "SOLES",$formato2);
		$worksheet1->write_string($fila, 7, "DOLARES",$formato2);
		$worksheet1->write_string($fila, 8, "SOLES",$formato2);

		$nu_documento_identidad = '';

		$sumTotalInicialSoles = 0.00;
		$sumTotalPagoSoles = 0.00;
		$sumTotalSaldoSoles = 0.00;

		$sumTotalInicialDolares = 0.00;
		$sumTotalPagoDolares = 0.00;
		$sumTotalSaldoDolares = 0.00;

		++$fila;
		for ($i=0; $i<count($arrResult); $i++) {
			if($nu_documento_identidad != $arrResult[$i]['CLIENTE']){
			 	if($i!=0) {
					$worksheet1->write_string($fila, 0, "TOTAL CLIENTE: ", $formato5);
					$worksheet1->write_number($fila, 3, number_format($sumTotalInicialDolares,2,'.',''), $formato5);
					$worksheet1->write_number($fila, 4, number_format($sumTotalInicialSoles,2,'.',''), $formato5);
					$worksheet1->write_number($fila, 5, number_format($sumTotalPagoDolares,2,'.',''), $formato5);
					$worksheet1->write_number($fila, 6, number_format($sumTotalPagoSoles,2,'.',''), $formato5);
					$worksheet1->write_number($fila, 7, number_format($sumTotalInicialDolares - $sumTotalPagoDolares,2,'.',''), $formato5);
					$worksheet1->write_number($fila, 8, number_format($sumTotalInicialSoles - $sumTotalPagoSoles,2,'.',''), $formato5);

					$sumTotalInicialDolares = 0.00;					
					$sumTotalInicialSoles = 0.00;

					$sumTotalPagoDolares = 0.00;
					$sumTotalPagoSoles = 0.00;

					$sumTotalSaldoSoles = 0.00;
					$sumTotalSaldoDolares = 0.00;
			 	}
				++$fila;
				$worksheet1->write_string($fila, 0, 'CLIENTE: '.$arrResult[$i]['CLIENTE'].' - '.$arrResult[$i]['RAZONSOCIAL'],$formato5);
				$nu_documento_identidad = $arrResult[$i]['CLIENTE'];
			}// /. If

			++$fila;
			$worksheet1->write_string($fila, 0, $arrResult[$i]['MONEDA'],$formato5);
			$worksheet1->write_string($fila, 1, $arrResult[$i]['DOCUMENTO'],$formato5);
			$worksheet1->write_string($fila, 2, $arrResult[$i]['FECHAEMISION'],$formato5);

			$Ss_Saldo_Dolares = $arrResult[$i]["MONEDA"] == 'S/' ? 0 : $arrResult[$i]["SALDO_DOLARES"];

			if ($arrResult[$i]["MONEDA"] == 'S/') {
				$sumTotalInicialSoles 	+= $arrResult[$i]["IMPORTEINICIAL_SOLES"];
				$sumTotalPagoSoles 		+= $arrResult[$i]["PAGO_SOLES"];
				$sumTotalSaldoSoles 	+= $arrResult[$i]["SALDO_SOLES"];

			} else {
				$sumTotalInicialDolares += $arrResult[$i]["IMPORTEINICIAL_DOLARES"];
				$sumTotalPagoDolares 	+= $arrResult[$i]["PAGO_DOLARES"];
				$sumTotalSaldoDolares 	+= $Ss_Saldo_Dolares;
			}

			$worksheet1->write_number($fila, 3, number_format($arrResult[$i]['IMPORTEINICIAL_DOLARES'],2,'.',''), $formato5);
			$worksheet1->write_number($fila, 4, number_format($arrResult[$i]['IMPORTEINICIAL_SOLES'],2,'.',''), $formato5);
			$worksheet1->write_number($fila, 5, number_format($arrResult[$i]['PAGO_DOLARES'],2,'.',''), $formato5);
			$worksheet1->write_number($fila, 6, number_format($arrResult[$i]['PAGO_SOLES'],2,'.',''), $formato5);
			$worksheet1->write_number($fila, 7, number_format($Ss_Saldo_Dolares,2,'.',''), $formato5);
			$worksheet1->write_number($fila, 8, number_format($arrResult[$i]['SALDO_SOLES'],2,'.',''), $formato5);

			++$fila;
		}// /. for

		$workbook->close();	

		$chrFileName = "EstadoCuentaCorrienteCliente" . $desde . "-" . $hasta;
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}


