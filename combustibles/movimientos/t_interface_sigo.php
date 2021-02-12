<?php

class InterfaceMovTemplate extends Template {
 
	function titulo(){
		$titulo = '<div align="center"><h2>Interface Opensoft  -->  SIIGO</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg){
		return '<blink>'.$errormsg.'</blink>';
	}

	function ResultadoEjecucion($msg){
		return '<blink>'.$msg.'</blink>';
	}

	function ListadoModulos()  {

		$CbModulos = array( 
				"VT"  => "VENTAS TICKES",
				"VTM" => "VENTAS MANUALES",
				"CP"  => "COMPRAS y CPAGAR"
		);

		return $CbModulos;

  	}
  	
 	function sucursalesCBArray() {
    	global $sqlca;

		$query    = "SELECT ch_sucursal, ch_sucursal||' '||ch_nombre_breve_sucursal FROM int_ta_sucursales ORDER BY ch_sucursal";
		$cbArray  = array();

		if ($sqlca->query($query)<=0)
  			return $cbArray;

		while($result = $sqlca->fetchRow()){
  			$cbArray[trim($result[0])] = $result[1];
		}

		return $cbArray;
  	}

	function formInterfaceMov()  {
		$hoy          = date("d/m/Y");
		$CbModulos    = InterfaceMovTemplate::ListadoModulos();
		$CbSucursales = InterfaceMovTemplate::sucursalesCBArray();

		$form = new form2('INTERFACE INTERFAZSIGO', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MOVIMIENTOS.INTERFAZSIGO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFAZSIGO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["ch_ruc"]));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr><td class="form_td_title">'));  
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[modulos]','M&oacute;dulos </td><td>: ', trim(@$datos["modulos"]), $CbModulos, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]','Sucursal </td><td>: ', trim(@$datos["sucursal"]), $CbSucursales, espacios(50)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('	  
		<tr>
			<td align="right">Fecha Inicial</td>
			<td align="left">: <input type="text" name="datos[fechaini]" id="datos[fechaini]" maxlength="10" size="10" class="fecha_formato" value="'.(empty($_REQUEST['datos']['fechaini']) ? $hoy : $_REQUEST['datos']['fechaini']).'" /></td>
		</tr>
		<tr>
			<td align="right">Fecha Final&nbsp;&nbsp;</td>
			<td align="left">: <input type="text" name="datos[fechafin]" id="datos[fechafin]" maxlength="10" size="10" class="fecha_formato" value="'.(empty($_REQUEST['datos']['fechafin']) ? $hoy : $_REQUEST['datos']['fechafin']).'" /></td>
		</tr>'
		));	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		
		$form->addGroup ('buttons', '');
		$form->addElement('buttons', new f2element_submit('action','Clientes', espacios(2)));
		$form->addElement('buttons', new f2element_submit('action','Asiento Contables', espacios(2)));
		$form->addElement('buttons', new f2element_submit('action','Asientos Contables Siigo basado en Ventas diarias', espacios(2)));
		
		return $form->getForm().'<div id="error_body" align="center"></div><hr>';
	}

	function reporteExcelSiigo($resultados, $alma, $dia1, $dia2) {
		ob_start();
		
		$chrFileName = "";
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
		$worksheet1->set_column(0, 0, 26);
		$worksheet1->set_column(1, 1, 26);
		$worksheet1->set_column(2, 2, 26);
		$worksheet1->set_column(3, 3, 26);
		$worksheet1->set_column(4, 4, 26);
		$worksheet1->set_column(5, 5, 26);
		$worksheet1->set_column(6, 6, 26);
		$worksheet1->set_column(7, 7, 26);
		$worksheet1->set_column(8, 8, 26);
		$worksheet1->set_column(9, 9, 26);
		$worksheet1->set_column(10, 10, 26);
		$worksheet1->set_column(11, 11, 26);
		$worksheet1->set_column(12, 12, 26);
		$worksheet1->set_column(13, 13, 26);
		$worksheet1->set_column(14, 14, 26);
		$worksheet1->set_column(15, 15, 26);
		$worksheet1->set_column(16, 16, 26);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "REPORTE DE ASIENTOS CONTABLES SIIGO",$formato0);
		$worksheet1->write_string(3, 0, "FECHA DEL ".$dia1."   AL   ".$dia2,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 6;
		
		$worksheet1->write_string($a, 0, "TIPO COMPROBANTE",$formato2);
		$worksheet1->write_string($a, 1, "CODIGO COMPROBANTE",$formato2);
		$worksheet1->write_string($a, 2, "NUMERO DOCUMENTO",$formato2);
		$worksheet1->write_string($a, 3, "CUENTA CONTABLE",$formato2);	
		$worksheet1->write_string($a, 4, "DEBITO CREDITO",$formato2);
		$worksheet1->write_string($a, 5, "VALOR SECUENCIA",$formato2);
		$worksheet1->write_string($a, 6, "A&ntilde;O DOCUMENTO",$formato2);
		$worksheet1->write_string($a, 7, "MES DOCUMENTO",$formato2);
		$worksheet1->write_string($a, 8, "DIA DOCUMENTO",$formato2);													
		$worksheet1->write_string($a, 9, "NOMBRE COMBUSTIBLE",$formato2);													

		$a = 7;	

		$dataVentas = $resultados[$alma];
		foreach ($dataVentas as $key=>$fecha) {
			foreach ($fecha as $key2=>$combustible) {
				$worksheet1->write_string($a, 0, $combustible['tipo_comprobante'], $formato5);
				$worksheet1->write_string($a, 1, $combustible['codigo_comprobante'], $formato5);
				$worksheet1->write_string($a, 2, $combustible['numero_documento'], $formato5);
				$worksheet1->write_string($a, 3, $combustible['cuenta_contable'], $formato5);
				$worksheet1->write_string($a, 4, $combustible['debito_credito'], $formato5);
				$worksheet1->write_string($a, 5, $combustible['valor_secuencia'], $formato5);
				$worksheet1->write_string($a, 6, $combustible['ano_documento'], $formato5);
				$worksheet1->write_string($a, 7, $combustible['mes_documento'], $formato5);
				$worksheet1->write_string($a, 8, $combustible['dia_documento'], $formato5);
				$worksheet1->write_string($a, 9, $combustible['ch_nombrecombustible'], $formato5);
				$a++;
			}
		}

		// foreach($resultados['propiedades'] as $estaciones => $almacenes) {
		// 	foreach($almacenes['almacenes'] as $ch_almacen => $venta) {
		// 		if ($ch_almacen == '')
		// 			$worksheet1->write_string($a, 0, "-", $formato5);	
		// 		else
		// 			$worksheet1->write_string($a, 0, $ch_almacen, $formato5);

		// 		$a++;

		// 		foreach($venta['partes'] as $dt_fecha=>$dia) {

		// 			$total_gal = $dia['11620301_galones'] + $dia['11620302_galones'] + $dia['11620304_galones'] + $dia['11620305_galones'] + $dia['11620303_galones'];
		// 			$total_imp = $dia['11620301_importe'] + $dia['11620302_importe'] + $dia['11620304_importe'] + $dia['11620305_importe'] + $dia['11620303_importe'];
		// 			$tt = $total_imp + $dia['11620307_importe'] + $dia['lubricantes'] + $dia['accesorios'] + $dia['servicios'] + $dia['market'];

		// 			$worksheet1->write_string($a, 0, $dt_fecha, $formato5);
		// 			$worksheet1->write_string($a, 1, $dia['11620301_galones'], $formato5);
		// 			$worksheet1->write_string($a, 2, $dia['11620301_importe'], $formato5);
		// 			$worksheet1->write_string($a, 3, $dia['11620302_galones'], $formato5);
		// 			$worksheet1->write_string($a, 4, $dia['11620302_importe'], $formato5);
		// 			$worksheet1->write_string($a, 5, $dia['11620305_galones'], $formato5);
		// 			$worksheet1->write_string($a, 6, $dia['11620305_importe'], $formato5);
		// 			$worksheet1->write_string($a, 7, $dia['11620303_galones'], $formato5);
		// 			$worksheet1->write_string($a, 8, $dia['11620303_importe'], $formato5);	
		// 			$worksheet1->write_string($a, 9, $dia['11620304_galones'], $formato5);
		// 			$worksheet1->write_string($a, 10, $dia['11620304_importe'], $formato5);
		// 			$worksheet1->write_string($a, 11, $dia['11620306_galones'], $formato5);
		// 			$worksheet1->write_string($a, 12, $dia['11620306_importe'], $formato5);
		// 			$worksheet1->write_string($a, 13, $total_gal, $formato5);
		// 			$worksheet1->write_string($a, 14, $total_imp, $formato5);
		// 			$worksheet1->write_string($a, 15, $dia['11620307_galones'], $formato5);
		// 			$worksheet1->write_string($a, 16, $dia['11620307_importe'], $formato5);	
		// 			$worksheet1->write_string($a, 17, $dia['lubricantes'], $formato5);
		// 			$worksheet1->write_string($a, 18, $dia['accesorios'], $formato5);
		// 			$worksheet1->write_string($a, 19, $dia['servicios'], $formato5);
		// 			$worksheet1->write_string($a, 20, $dia['market'], $formato5);
		// 			$worksheet1->write_string($a, 21, $dia['whiz'], $formato5);
		// 			$worksheet1->write_string($a, 22, $dia['ob'], $formato5);
		// 			$worksheet1->write_string($a, 23, $dia['otros'], $formato5);
		// 			$worksheet1->write_string($a, 24, $tt, $formato5);								
		// 			$a++;
		// 			$tttotal = $tt + $tttotal;
		// 		}
		// 	}
		// }

		// $a++;

		// foreach($resultados['propiedades'] as $estaciones => $almacenes) {
		// 	foreach($almacenes['almacenes'] as $ch_almacen => $venta) {
		// 		$worksheet1->write_string($a, 0, "TOTALES:", $formato5);
		// 		$worksheet1->write_string($a, 1, $venta['totales']['11620301_galones'], $formato5);
		// 		$worksheet1->write_string($a, 2, $venta['totales']['11620301_importe'], $formato5);
		// 		$worksheet1->write_string($a, 3, $venta['totales']['11620302_galones'], $formato5);
		// 		$worksheet1->write_string($a, 4, $venta['totales']['11620302_importe'], $formato5);
		// 		$worksheet1->write_string($a, 5, $venta['totales']['11620305_galones'], $formato5);
		// 		$worksheet1->write_string($a, 6, $venta['totales']['11620305_importe'], $formato5);
		// 		$worksheet1->write_string($a, 7, $venta['totales']['11620303_galones'], $formato5);
		// 		$worksheet1->write_string($a, 8, $venta['totales']['11620303_importe'], $formato5);
		// 		$worksheet1->write_string($a, 9, $venta['totales']['11620304_galones'], $formato5);
		// 		$worksheet1->write_string($a, 10, $venta['totales']['11620304_importe'], $formato5);
		// 		$worksheet1->write_string($a, 11, $venta['totales']['11620306_galones'], $formato5);
		// 		$worksheet1->write_string($a, 12, $venta['totales']['11620306_importe'], $formato5);
		// 		$worksheet1->write_string($a, 13, $venta['totales']['total_galones'], $formato5);
		// 		$worksheet1->write_string($a, 14, $venta['totales']['total_importe'], $formato5);
		// 		$worksheet1->write_string($a, 15, $venta['totales']['11620307_galones'], $formato5);
		// 		$worksheet1->write_string($a, 16, $venta['totales']['11620307_importe'], $formato5);
		// 		$worksheet1->write_string($a, 17, $venta['totales']['lubricantes'], $formato5);
		// 		$worksheet1->write_string($a, 18, $venta['totales']['accesorios'], $formato5);
		// 		$worksheet1->write_string($a, 19, $venta['totales']['servicios'], $formato5);
		// 		$worksheet1->write_string($a, 20, $venta['totales']['market'], $formato5);
		// 		$worksheet1->write_string($a, 21, $venta['totales']['whiz'], $formato5);
		// 		$worksheet1->write_string($a, 22, $venta['totales']['ob'], $formato5);
		// 		$worksheet1->write_string($a, 23, $venta['totales']['otros'], $formato5);
		// 		$worksheet1->write_string($a, 24, $tttotal, $formato5);
		// 	}
		// }

		$workbook->close();	

		$chrFileName = "Ventas Diarias";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");	
	}
}
