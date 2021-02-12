<?php

class AsientosTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Asientos Contables</b></h2>';
    	}
    
	function formSearch($cod_almacen, $desde, $hasta) {

		$sucursales = AsientosModel::obtenerSucursales("");
		$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");	
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MOVIMIENTOS.ASIENTOS"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan = "2" align="center">Estaciones: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "Estacion:", "", $sucursales, espacios(3)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">Desde: '));		
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("mes", "Mes:", date(m), '&nbsp&nbsp', 02, 02));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("anio", "Anio:", date(Y), '', 04, 04)); 
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_freeTags('<button type="submit" name="action" value="Excel"><img src="/sistemaweb/images/excel_icon.png" alt="left"/>Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		
		return $form->getForm();
    	}
    
	function reporteExcel($res,$lubri,$debe,$abono) {

		$chrFileName = '';

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('left');
		$formato5->set_size(8);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('Asientos');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 16);
		$worksheet1->set_column(2, 2, 12);
		$worksheet1->set_column(3, 3, 12);
		$worksheet1->set_column(4, 4, 12);
		$worksheet1->set_column(5, 5, 16);
		$worksheet1->set_column(6, 6, 16);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "Asientos Combustible",$formato0);

		$a = 2;
		$worksheet1->write_string($a, 0, "fecha",$formato2);
		$worksheet1->write_string($a, 1, "cuenta",$formato2);
		$worksheet1->write_string($a, 2, "documento",$formato2);
		$worksheet1->write_string($a, 3, "debe",$formato2);
		$worksheet1->write_string($a, 4, "abono",$formato2);
		$worksheet1->write_string($a, 5, "moneda",$formato2);
		$worksheet1->write_string($a, 6, "glosa",$formato2);
		$worksheet1->write_string($a, 7, "idprov",$formato2);

		$a = 3;	

		for ($j=0; $j<count($res); $j++) {

			$worksheet1->write_string($a, 0, $res[$j]['fecha'],$formato5);
			$worksheet1->write_string($a, 1, $res[$j]['cuenta'],$formato5);
			$worksheet1->write_string($a, 2, $res[$j]['documento'],$formato5);

			if($res[$j]['cuenta'] == '6590301' || trim($res[$j]['cuenta']) == '6590301'){	
				if($debe < $abono){
					$worksheet1->write_string($a, 3, 0.01,$formato5);
				}
			}else{
				$worksheet1->write_string($a, 3, $res[$j]['debe'],$formato5);
			}

			if($res[$j]['cuenta'] == '6590301' || trim($res[$j]['cuenta']) == '6590301'){	
				if($debe > $abono){
					$worksheet1->write_string($a, 4, 0.01,$formato5);
				}
			}else{
				$worksheet1->write_string($a, 4, $res[$j]['abono'],$formato5);
			}

			$worksheet1->write_string($a, 5, $res[$j]['moneda'],$formato5);
			$worksheet1->write_string($a, 6, $res[$j]['glosa'],$formato5);
			$worksheet1->write_string($a, 7, $res[$j]['idprov'],$formato5);
			$a++;
		}
		
		$segu = count($res);

		$worksheet1->write_string($a+3, 0, "Asientos Lubricantes",$formato0);

		for ($x=0; $x<count($lubri); $x++) {
			$worksheet1->write_string($a+5, 0, $lubri[$x]['fecha'],$formato5);
			$worksheet1->write_string($a+5, 1, $lubri[$x]['cuenta'],$formato5);
			$worksheet1->write_string($a+5, 2, $lubri[$x]['documento'],$formato5);
			$worksheet1->write_string($a+5, 3, $lubri[$x]['debe'],$formato5);
			$worksheet1->write_string($a+5, 4, $lubri[$x]['abono'],$formato5);
			$worksheet1->write_string($a+5, 5, $lubri[$x]['moneda'],$formato5);
			$worksheet1->write_string($a+5, 6, $lubri[$x]['glosa'],$formato5);
			$worksheet1->write_string($a+5, 7, $lubri[$x]['idprov'],$formato5);
			$a++;
		}
			
		$workbook->close();	

		$chrFileName = "asientos";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$chrFileName.xls" );
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}
}
