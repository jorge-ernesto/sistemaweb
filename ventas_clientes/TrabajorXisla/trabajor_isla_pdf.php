<?php

include_once('/sistemaweb/include/reportes2.inc.php');

class trabajor_isla_pdf {

	function TmpReportePDFFactura($data_are,$fecha_ini,$fecha_final) {
        
		$pdf = new CReportes2();
		$pdf->AddPage("L", "A4");
		$pdf->SetMargins(30, 20, 10);
		$pdf->SetFont("courier", "B", 12);
		$pdf->text(30,20,"ASIGNACION DE TRABAJADORES de ".$fecha_ini." HASTA ".$fecha_final);

		$pdf->SetFont("courier", "", 9.5);
		$header = array("FECHA", "TURNO", "LADO", "COD T", "NOMBRE TRABAJADOR","TIPO","FECHA DE CREACION");
		$w = array(80, 80, 60,60, 200, 100,80);

		$pdf->Ln();

		for ($i = 0; $i < count($header); $i++) {
		    $pdf->Cell($w[$i], 10, $header[$i], 'TB', 0, 'L', false);
		}

		$pdf->Ln();

        	foreach ($data_are as $value) {
			$tipo=$value['ch_tipo'];
		    	$tipo_prod=($tipo=="C")?'COMBUSTIBLE':'MARKET';
		    	$pdf->Cell($w[0], 10, $value['dt_dia'], 0, 0, 'L', false);
		    	$pdf->Cell($w[1], 10, $value['ch_posturno'], 0, 0, 'L', false);
		    	$pdf->Cell($w[2], 10, $value['ch_lado'], 0, 0, 'C', false);
		    	$pdf->Cell($w[3], 10, $value['ch_codigo_trabajador'], 0, 0, 'L', false);
		    	$pdf->Cell($w[4], 10, $value['nombre'], 0, 0, 'L', false);
				$pdf->Cell($w[5], 10, $tipo_prod, 0, 0, 'L', false);
				$pdf->Cell($w[6], 10, $value['fecha_replicacion'], 0, 0, 'L', false);
		    	$pdf->Ln();
        	}

		$url_archivo = "/sistemaweb/ventas_clientes/reportes/pdf/trabajador_x_isla.pdf";

        	$pdf->Output($url_archivo, "F");
        	$pdf->close();

	}

}

