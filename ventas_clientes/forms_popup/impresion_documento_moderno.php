<?php

class impresion_documentos_moderno {

	function TmpReportePDFFactura($data_are, $tipo_documento) {

		$tipo_documeto = $tipo_documento;

		$pdf = new CReportes2();

		if($tipo_documeto == '35'){
			$pdf->AddPage("P", "A3");
			$pdf->SetFont('arial', '', 6.5);
		}else{
			$pdf->AddPage("P", "A4");
			$pdf->SetFont('arial', 'B', 10);
		}

		$pdf->SetMargins(0, 0, 0);
		$pdf->SetAutoPageBreak(true);

		$order_fila_cabe 		= array();
		$salto_sigui_intermedio	= 0;
		$permiso_sigui_vuelta	= array();
		$lines_salto 			= 0;
		$int_conta_fila 		= 0;
		$array_seq_int 			= array();

		array_merge($data_are,$uu);

		foreach ($data_are as $registros) {
		   	$x 		= $registros['X'];
		   	$y 		= $registros['Y'];
		   	$data 	= $registros['data'];

        	if ($registros['TC'] == "C")
				$pdf->text($x, $y, $data);

        	if ($registros['TC'] == "I") {

        		$pdf->text($x, $y + $lines_salto, $data);

        		$sque = trim($registros['seq']);

           		if (in_array($sque, $array_seq_int)) {
				} else {
					$int_conta_fila++;
				}
				
				if ($int_conta_fila == 5) {
		            $lines_salto+=15;
		            $int_conta_fila = 0;
		            unset($array_seq_int);
		        } else {
		            $array_seq_int[] = trim($registros['seq']);
		        }
            }

			if ($registros['TC'] == "P")
				$pdf->text($x, $y, $data);
		}

		$url_archivo = "/sistemaweb/ventas_clientes/reportes/pdf/reporte_documento.pdf";
        $pdf->Output($url_archivo, "F");
        $pdf->close();

	}

}

