<?php

session_start();
include_once('/sistemaweb/include/reportes2.inc.php');

class consumo_xplaca_pdf {

	function ConsumosPlacaPDF($datos,$fdesde,$fhasta, $arrGet,$empresa = array()) {
		error_log(json_encode($empresa));
		$ruc = trim($empresa['ruc']);
		$razsocial = $empresa['razsocial'];
		$establecimiento = $empresa['ch_sucursal'] . " - " . $empresa['ch_nombre_almacen'];

		$fini = substr($fdesde,8,2)."/".substr($fdesde,5,2)."/".substr($fdesde,0,4);
		$ffin = substr($fhasta,8,2)."/".substr($fhasta,5,2)."/".substr($fhasta,0,4);
        
		$pdf = new CReportes2();
		$pdf->AddPage("P", "A4");
		$pdf->SetMargins(5, 5, 5);
		$pdf->SetFont("courier", "B", 8);
		$pdf->Ln();
		$pdf->Cell(390, 5, "REPORTE DE CONSUMO POR PLACA DE VEHICULOS", 0, 0, "C",false);
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Cell(390, 5, "RUC: $ruc", 0, 0, "L",false);
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Cell(380, 5, "RAZON SOCIAL: $razsocial", 0, 0, "L",false);
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Cell(350, 5, "ESTABLECIMIENTO: $establecimiento", 0, 0, "L",false);
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Cell(250, 5, "Consumos por placa de Vehiculo Del: ".$fdesde." Al: ".$fhasta, 0, 0, "L",false);
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();

		$pdf->SetFont("courier", "", 8);
		$header = array("FECHA", "ESTACION", "TICKET", "ODOMETRO", "CODIGO","DESCRIPCION", "PRECIO", "CANTIDAD", "IMPORTE");
		$w = array(60, 47, 65, 50, 80, 150, 50, 50, 50);

		$pdf->Ln();

		for ($i = 0; $i < count($header); $i++) {
		    $pdf->Cell($w[$i], 10, $header[$i], 'TB', 0, 'L', false);
		}

		$pdf->Ln();

		$i = 0;
		$tickets = 0;
		$cliente = "";
		$placa = "";
		$numvales = 0;
		$sumcantidad = 0;
		$sumimporte = 0;
		$cantidadplaca = 0;
		$importeplaca = 0;
		$cantidadcli = 0;
		$importecli = 0;

		$tickets = count($datos);
		for($i=0; $i < $tickets; $i++){
			if($placa != $datos[$i]['placa']){
				if($i!=0){
					$espacios_cantidadplaca = "";
					$string_cantidadplaca = number_format($cantidadplaca, 2, '.', ',')."";
					if(strlen($string_cantidadplaca) <= 6){
						$espacios_cantidadplaca = "   ";
					}

					$espacios_importeplaca = "";
					$string_importeplaca = number_format($importeplaca, 2, '.', ',')."";				
					if(strlen($string_importeplaca) <= 6){
						$espacios_importeplaca = "   ";
					}					

					$espacios_cantidadcli = "";
					$string_cantidadcli = number_format($cantidadcli, 2, '.', ',')."";				
					if(strlen($string_cantidadcli) <= 6){
						$espacios_cantidadcli = "   ";
					}

					$espacios_importecli = "";
					$string_importecli = number_format($importecli, 2, '.', ',')."";				
					if(strlen($string_importecli) <= 6){
						$espacios_importecli = "   ";
					}
					
	    			$pdf->Cell(837, 10, " Total Placa:  $espacios_cantidadplaca".number_format($cantidadplaca, 2, '.', ',')."   $espacios_importeplaca".number_format($importeplaca, 2, '.', ','), 'TB', 0, 'C', false);
		    		$pdf->Ln();
					$cantidadplaca = 0;
					$importeplaca = 0;
					if($cliente != $datos[$i]['codcliente'] && ($arrGet['sIdCliente'] == '' || empty($arrGet['sIdCliente']))){
		    			$pdf->Cell(780, 10, " Total por Cliente:  $espacios_cantidadcli".number_format($cantidadcli, 2, '.', ',')."   $espacios_importecli".number_format($importecli, 2, '.', ','), 'TB', 0, 'C', false);
			    		$pdf->Ln();
						$cantidadcli = 0;
						$importecli = 0;
						$cliente = $datos[$i]['codcliente'];
					}
				}
    			$pdf->Cell(602, 10, "Placa: ".$datos[$i]['placa']." Cliente: ".$datos[$i]['codcliente']." - ".$datos[$i]['descliente'], 0, 0, 'L', false);
		    	$pdf->Ln();
				$placa = $datos[$i]['placa'];
			}

			if ( $arrGet['sMostrarReporte'] == 'D' ) {
		    	$pdf->Cell($w[0], 10, $datos[$i]['fecha'], 0, 0, 'L', false);
		    	$pdf->Cell($w[1], 10, $datos[$i]['desalmacen'], 0, 0, 'L', false);
				$pdf->Cell($w[2], 10, $datos[$i]['ticket'], 0, 0, 'L', false);
		    	$pdf->Cell($w[3], 10, $datos[$i]['odometro'], 0, 0, 'L', false);
		    	$pdf->Cell($w[4], 10, $datos[$i]['codproducto'], 0, 0, 'L', false);
		    	$pdf->Cell($w[5], 10, $datos[$i]['nomproducto'], 0, 0, 'L', false);
		    	$pdf->Cell($w[6], 10, $datos[$i]['precio'], 0, 0, 'L', false);
		    	$pdf->Cell($w[7], 10, $datos[$i]['cantidad'], 0, 0, 'L', false);
		    	$pdf->Cell($w[8], 10, $datos[$i]['importe'], 0, 0, 'L', false);
		    	$pdf->Ln();
		    }

			$cantidadcli += (double)$datos[$i]['cantidad'];
			$importecli += (double)$datos[$i]['importe'];

			$sumcantidad += $datos[$i]['cantidad'];
			$sumimporte	+= $datos[$i]['importe'];
			$cantidadplaca += $datos[$i]['cantidad'];
			$importeplaca += $datos[$i]['importe'];
        }

		$pdf->Cell(837, 10, " Total Placa:  $espacios_cantidadplaca".number_format($cantidadplaca, 2, '.', ',')."   $espacios_importeplaca".number_format($importeplaca, 2, '.', ','), 'TB', 0, 'C', false);
    	$pdf->Ln();
		$pdf->Cell(780, 10, " Total por Cliente:  $espacios_cantidadcli".number_format($cantidadcli, 2, '.', ',')."   $espacios_importecli".number_format($importecli, 2, '.', ','), 'TB', 0, 'C', false);
    	$pdf->Ln();
		$pdf->Ln();
		$pdf->Cell(850, 10, "Total General Tickets:  ".$tickets, 'TB', 0, 'C', false);
		$pdf->Ln();
		$pdf->Cell(850, 10, "Total General Cantidad: ".number_format($sumcantidad, 2, '.', ','), 'TB', 0, 'C', false);
		$pdf->Ln();
		$pdf->Cell(850, 10, "Total General Importe:  ".number_format($sumimporte, 2, '.', ','), 'TB', 0, 'C', false);
		$pdf->Ln();

		$url_archivo = "/sistemaweb/ventas_clientes/reportes/pdf/consumos_x_placa.pdf";

    	$pdf->Output($url_archivo, "F");
    	$pdf->close();
	}
}

