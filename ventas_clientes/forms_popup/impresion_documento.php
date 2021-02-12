<?php

include_once('../../include/reportes2.inc.php');

class impresion_documentos {

	function TmpReportePDFFactura($data_are, $tipo_documento) {
    	$pdf = new CReportes2();

    	$pdf->AddPage("P", "A4");
		$pdf->SetFont('Arial','B',10);//<-- Tipo de letra arial, Bold, tamaño 20		
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(true);

        $order_fila_cabe		= array();
        $salto_sigui_intermedio	= 0;
        $permiso_sigui_vuelta	= array();
        $lines_salto			= 0;
        $int_conta_fila			= 0;
        $array_seq_int			= array();
        
		foreach ($data_are as $registros) {
		   	$x 		= $registros['X'];
		   	$y 		= $registros['Y'];
		   	$data 	= $registros['data'];

        	if ($registros['TC'] == "C")
				$pdf->text($x, $y, $data);

        	if ($registros['TC'] == "I") {
				if($data_are[18]['seq'] == 02){
					$sNombreProducto = $data;
					$data = substr($sNombreProducto,0,25);
				}

        		$pdf->text($x, $y + $lines_salto, $data);

        		//Producto
        		if ($data_are[18]['seq'] == 02 && strlen($data) >= 25) {
        			$lines_salto += 15;
        			$data_desc = substr($sNombreProducto, 25, 25);
        			$pdf->text($x, $y + $lines_salto, $data_desc);

        			$lines_salto += 15;
        			$data_desc2 = substr($sNombreProducto, 50, 25);
        			$pdf->text($x, $y + $lines_salto, $data_desc2);

        			$lines_salto -= 30;
        		}
        		

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

		echo "<a href='descarga.php'>Descargar documento PDF</a>";
	}
}

