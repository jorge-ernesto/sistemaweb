<?php
  /*
    Templates para reportes 
    @TBCA modificado por @MATT
  */
include "/sistemaweb/include/fpdf.php";
//include "/sistemaweb/include/functions.inc.php";
class CartasTemplate extends Template {

  function titulo(){
    $titulo = '<div align="center"><h2>Impresion de Cartas</h2></div><hr>';
    return $titulo;
  }

  function formImprimir()
  {//Inicio de Función 
    $form = new form2('', 'form_carta', FORM_METHOD_POST, '../control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.CARTAS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CARTAS'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags ('<table border="0" cellspacing="5" cellpadding="5"><tbody class="grid_body"><tr><td align="center">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('reporte[desde]','Num. Desde </td><td>: ', '', '', 7, 7));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('reporte[hasta]','&nbsp;Num. Hasta </td><td>: ', '', '', 7, 7));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte', '</td></tr>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags ('</tbody></table>'));
    return $form->getForm().'<div id="reporte" align="center"></div>';
  }//Fin de Función
   
  function pdfImprimir($datos_array)
  {
  	 //global $meses;
    $fontsize = 8;
    $pdf = new FPDF("P", "pt", "A4");
    //print_r($datos_array);
	foreach ($datos_array as $k => $v){
		if ($v['fecha1']!=''){
			$pdf->SetMargins(5, 5, 5);
			$pdf->AddPage();
		    $pdf->SetXY(60,190);
		    $pdf->SetFont("times", "", 12);
		   	$auxi = explode('-',$v['fecha1']);
		   	switch ($auxi[1]){
		   		case '01': $mes = 'Enero'; break;
		   		case '02': $mes = 'Febrero'; break;
		   		case '03': $mes = 'Marzo'; break;
				case '04': $mes = 'Abril'; break;
				case '05': $mes = 'Mayo'; break;
				case '06': $mes = 'Junio'; break;
				case '07': $mes = 'Julio'; break;
				case '08': $mes = 'Agosto'; break;
				case '09': $mes = 'Septiembre'; break;
				case '10': $mes = 'Octubre'; break;
				case '11': $mes = 'Noviembre'; break;
				case '12': $mes = 'Diciembre'; break;
		   	}
		   	        
		    $pdf->Cell(0, $fontsize, "Lima, ".$auxi[2]." de ".$mes." de ".$auxi[0]);
		    $pdf->Ln(); 
		    $pdf->Ln();
		    $pdf->Ln();
		    $pdf->Ln();
		    $pdf->Ln();
		    $pdf->SetX(60);
		    $pdf->Cell(0, $fontsize, "Se�ores",0,2); 
		    $pdf->Ln();
		    $pdf->SetX(60);
		    $pdf->SetFont("times", "B", 11);
		    $pdf->Cell(0, $fontsize, $v['razon1'],0,2); 
		    $pdf->Ln();
		    $pdf->SetX(60);
		    $pdf->SetFont("times", "", 11);
		    $pdf->Cell(0, $fontsize, $v['direccion1'],0,2); 
		    $pdf->Ln();
		    $pdf->SetX(60);
		    if ($v['distrito1']=='') $v['distrito1']='PRESENTE.-';
		    else $v['distrito1'].='.-';
		    $pdf->Cell(0, $fontsize, $v['distrito1'],0,2);
		    $pdf->Line(64,288,64+(strlen($v['distrito1'])-2)*6.4,288); 
		    $pdf->SetFont("times", "", 12);
		    $pdf->Ln();
			$pdf->Ln();
			$pdf->Ln();
		   	$pdf->SetX(60);
		    $pdf->Cell(0, $fontsize, "Atencion      :      ".$v['contacto1'],0,2); 
		    $pdf->Ln();
		    $pdf->Ln();
		    $pdf->Ln();
		    $pdf->Ln();
			$pdf->SetX(60);
			$pdf->Cell(60,12,"Referencia    :      Consumo de la Semana",0); 
			$pdf->Line(150,361,260,361);
			$pdf->Ln();
		    $pdf->Ln();
		    $pdf->Ln();
			$pdf->SetX(60);
			$pdf->Cell(0, $fontsize, "De nuestra consideracion:",0,2);
			$pdf->Ln();
		    $pdf->Ln();
		    $pdf->SetX(60);
			$pdf->Cell(0, $fontsize, "De acuerdo a lo convenido, adjunto sirvanse encontrar lo siguiente:",0,2);
			$pdf->Ln();
		    $pdf->Ln();
		    $pdf->SetX(60);
		    $auxi = explode('-',$v['fecha1']);
		    $v['fecha1'] = $auxi[2].'.'.$auxi[1].'.'.$auxi[0];
		    $pdf->Cell(0, $fontsize, "1. Estado de Cuenta Corriente al ".$v['fecha1']." reflejando un saldo ".($v['sumafinal1']<0?"deudor":"acreedor")." de S/. ".($v['sumafinal1']<0?-1*$v['sumafinal1']:$v['sumafinal1']).".",0,2);
		    $pdf->Ln();
		    $pdf->Ln();
		    $pdf->SetX(60);
		    $pdf->Cell(0, $fontsize, '2. Un total de '.$v['ordenes1'].' Ordenes de Consumo con las que han realizado '.$v['transac1'].' transacciones, conforme ',0,2);
		    $pdf->Ln();
		    $pdf->SetX(60);
		    $auxi = explode('-',$v['fecha_adjunto1']);
		    $v['fecha_adjunto1'] = $auxi[2].'.'.$auxi[1].'.'.$auxi[0];
		    $pdf->Cell(0, $fontsize, 'aparece en el reporte "Resumen de Facturas" del '.$v['fecha_adjunto1'].' adjunto y firmado.',0,2);
		    $pdf->Ln();
		    $pdf->Ln();
		    $pdf->SetX(60);
		    $pdf->Cell(0, $fontsize, "Sin otro particular, quedamos de ustedes.",0,2);
		    $pdf->Ln();
		    $pdf->Ln();
		    $pdf->Ln();
		    $pdf->SetX(60);
		    $pdf->Cell(0, $fontsize, "Atentamente,",0,2);
		}
	    
	}
    //$pdf->SetXY(1,1);
    $pdf->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_cartas.pdf", "F");
    return '<iframe src="/sistemaweb/ventas_clientes/reportes/pdf/reporte_cartas.pdf" width="900" height="300"></iframe>';
  }
}

