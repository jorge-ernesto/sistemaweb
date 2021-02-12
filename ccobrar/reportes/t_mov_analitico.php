<?php

include('../include/reportes2.inc.php');

class TarjetasMagneticasTemplate extends Template {
 
	function titulo(){
	        return '<div align="center"><h2><b>Reporte Analitico de Movimientos por Cliente</b></h2></div>';
		return $titulo;
	}
  
	function ReportePDF($datos_array, $fecha_inicio, $fecha_fin) {

		if ($datos_array[0]['RADIO'] == 'DETALLADO') {	// empieza reporte detallado
			if ($datos_array[0]['COMBO'] == '01') { // reporte para TODOS los clientes
				$Cabecera1 = array( 
							"CAB1"            => " ",
							"CAB2"            => " ",
							"CAB3"            => " ",
							"CAB4"            => " ",
							"CAB5"            => " ",
							"CAB6"            => " ",
							"CAB7"            => " ",
							"IMPORTE S"       => "IMPORTE S/",
							"CAB8"            => " ",
							"IMPORTE D"       => "IMPORTE $"
						  );
			
				$Cabecera2 = array(
							"CLIENTE"       =>  "CLIENTE",
							"FECHA"         =>  "FECHA EMI.",
							"FECHA VEN"	=>  "FECHA VEN.",
							"ACCION"        =>  "ACCION",
							"DOCUMENTO"     =>  "DOCUMENTO",
							"MONEDA"	=>  "MONEDA",
							"CARGO SOL"     =>  "CARGO",
							"ABONO SOL"     =>  "ABONO",
							"CARGO DOL"     =>  "CARGO",
							"ABONO DOL"     =>  "ABONO",
							"SALDO SOLES"   =>  "SALDO S/",
							"SALDO DOLARES" =>  "SALDO $", 
						  );
						  
				$CabCli = array( 	"NOMCLI"          =>  " "  );
						  
				$fontsize = 6.5;
				$reporte = new CReportes2();
				$reporte->SetMargins(5, 1, 5);
				$reporte->SetFont("courier", "", $fontsize);
				$reporte->definirColumna("CAB1", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB2", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB3", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB4", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB5", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB6", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB7", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("IMPORTE S", $reporte->TIPO_TEXTO, 16, "L", "Cabecera1");
				$reporte->definirColumna("CAB8", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("IMPORTE D", $reporte->TIPO_TEXTO, 16, "L", "Cabecera1");

				$reporte->definirColumna("FECHITA", $reporte->TIPO_TEXTO, 18, "R", "Cabecera1");
				$reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "C", "Cabecera1");
				$reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "L");
				$reporte->definirColumna("FECHA VEN", $reporte->TIPO_TEXTOO, 10, "L");
				$reporte->definirColumna("ACCION", $reporte->TIPO_TEXTO, 14, "L");
				$reporte->definirColumna("DOCUMENTO", $reporte->TIPO_TEXTO, 26, "C");
				$reporte->definirColumna("MONEDA", $reporte->TIPO_TEXTO, 8, "L");
				$reporte->definirColumna("CARGO SOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("ABONO SOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("CARGO DOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("ABONO DOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("SALDO SOLES", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("SALDO DOLARES", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("NOMCLI", $reporte->TIPO_TEXTO, 100, "L", "CLI");			
				
				$reporte->definirCabecera(1, "L", "SISTEMA OPENSOFT");
				$reporte->definirCabecera(1, "C", "REPORTE DE COBRANZAS");
				$reporte->definirCabecera(1, "L", "");
				$reporte->definirCabecera(1, "R", "PAG.%p");
				$reporte->definirCabecera(2, "R", " ");
				$reporte->definirCabecera(3, "L", "Usuario : %u");
				$reporte->definirCabecera(3, "C", "Del: ".$fecha_inicio."   al: ".$fecha_fin."");
				$reporte->definirCabecera(3, "R", "%f");
				$reporte->definirCabecera(4, "R", " ");
				
				$reporte->definirCabeceraPredeterminada($Cabecera1, "Cabecera1");
				$reporte->definirCabeceraPredeterminada($Cabecera2);
				$reporte->AddPage();
		
				for($j = 0; $j < count($datos_array); $j++){

					if ($datos_array[$j-1]['CLIENTE'] != $datos_array[$j]['CLIENTE']){

						$reporte->Ln();
						$codigo		= $datos_array[$j]['CLIENTE'];
						$raz_social	= $datos_array[$j]['RAZSOCIAL'];											
						$arr		= array("NOMCLI"=>"CLIENTE: ".$codigo);
						$reporte->nuevaFila($arr,"CLI"); 
						$reporte->Ln();

					}

					$datos["CLIENTE"] 	= $datos_array[$j]["CLIENTE"];
					$datos["FECHA"] 	= $datos_array[$j]["FECHA"];
					$datos["FECHA VEN"] = $datos_array[$j]["FECHA VENCIMIENTO"];
					$datos["ACCION"] 	= $datos_array[$j]["ACCION"];
					$datos["MONEDA"]	= $datos_array[$j]["MONETOTAL"];
					$datos["DOCUMENTO"]	= $datos_array[$j]["DOCUMENTO"];

					if(($datos_array[$j]["CONTABLE"] == "A" && $datos_array[$j]["TIPODOC"] == "21" && $datos_array[$j]["MOVIMIENTO"] == "1") || ($datos_array[$j]["MOVIMIENTO"] == "3" && $datos_array[$j]["TIPODOC"] == "22") || ($datos_array[$j]["MOVIMIENTO"] == "2")){
						if(TRIM($datos_array[$j]["MONETOTAL"]) == 'S/'){
							$datos["CARGO SOL"] = 0.00;
							$total_cargo_soles 	+= 0.00;
							$datos["ABONO SOL"] = $datos_array[$j]["CABTOTAL"];
							$total_abono_soles 	+= $datos_array[$j]['CABTOTAL'];
							$datos["CARGO DOL"]	= 0.00;
							$datos["ABONO DOL"]	= 0.00;
							$general_abono_sol 	+= $datos_array[$j]['CABTOTAL'];
						}else{
							$datos["CARGO SOL"] = 0.00;
							$total_cargo_soles 	+= 0.00;
							$datos["ABONO SOL"] = 0.00;
							$total_abono_soles 	+= 0.00;
							$datos["CARGO DOL"]	= 0.00;
							$datos["ABONO DOL"]	= $datos_array[$j]["CABTOTAL"];
							$total_abono_dolares += $datos_array[$j]["CABTOTAL"];
							$general_abono_dol += $datos_array[$j]['CABTOTAL'];
						}
					}else{
						if(TRIM($datos_array[$j]["MONETOTAL"]) == 'S/'){
							$datos["CARGO SOL"] = $datos_array[$j]["CABTOTAL"];
							$total_cargo_soles 	+= $datos_array[$j]['CABTOTAL'];
							$general_cargo_sol 	+= $datos_array[$j]['CABTOTAL'];
							$datos["ABONO SOL"] = 0.00;
							$total_abono_soles 	+= 0.00;
							$datos["CARGO DOL"]	= 0.00;
							$datos["ABONO DOL"]	= 0.00;
						}else{
							$datos["CARGO SOL"] = 0.00;
							$total_cargo_soles 	+= 0.00;
							$datos["ABONO SOL"] = 0.00;
							$total_abono_soles 	+= 0.00;
							$datos["CARGO DOL"] = $datos_array[$j]["CABTOTAL"];
							$total_cargo_dolares += $datos_array[$j]["CABTOTAL"];
							$datos["ABONO DOL"]	= 0.00;
						}
					}

					if(TRIM($datos_array[$j]["MONETOTAL"]) == 'S/'){
						$datos["SALDO SOLES"]	= $datos_array[$j]["SALDOSOLES"];
						$datos["SALDO DOLARES"]	= 0.00;
						$total_saldo_dolares 	+= 0.00;

						if($datos_array[$j]["CANTCOBRANZA"] <= 2){
							$total_saldo_soles 	+= $datos_array[$j]["SALDOSOLES"];
							$general_saldo_soles 	+= $datos_array[$j]["SALDOSOLES"];
						}else{
							$total_saldo_soles 	+= $datos_array[$j]["SALDOFINALSOLES"];
							$general_saldo_soles 	+= $datos_array[$j]["SALDOFINALSOLES"];
						}

					}else{
						$datos["SALDO SOLES"]	= 0.00;
						$total_saldo_soles 	+= 0.00;
						$datos["SALDO DOLARES"]	= $datos_array[$j]["SALDOSOLES"];
						$total_saldo_dolares 	+= $datos_array[$j]["SALDOSOLES"];
						$general_saldo_dolares 	+= $datos_array[$j]["SALDOSOLES"];
					}

					$reporte->nuevaFila($datos);

					if($datos_array[$j]['CLIENTE'] != $datos_array[$j+1]['CLIENTE']) {

	  					$totales_cliente["DOCUMENTO"] = "TOTAL: ";

						$totales_cliente["CARGO SOL"] 		= $total_cargo_soles;
						$totales_cliente["ABONO SOL"] 		= $total_abono_soles;
						$totales_cliente["CARGO DOL"] 		= $total_cargo_dolares;
						$totales_cliente["ABONO DOL"] 		= $total_abono_dolares;
						$totales_cliente["SALDO SOLES"] 	= $total_saldo_soles;
						$totales_cliente["SALDO DOLARES"] 	= $total_saldo_dolares;

						$reporte->Ln();
						$reporte->nuevaFila($totales_cliente);
						$reporte->Ln();	
						$reporte->lineaH();
						$total_cargo_soles   	= 0;
						$total_abono_soles  	= 0;
						$total_cargo_dolares   	= 0;
						$total_abono_dolares   	= 0;
						$total_saldo_soles   	= 0;
						$total_saldo_dolares   	= 0;
					}
				
					if ($datos_array[$j+1][0] != $datos_array[$j][0]) {
						$reporte->lineaH();
						$reporte->Ln();
						$reporte->Ln();
					}
				}

				$totales_generales['DOCUMENTO'] 	= "TOTAL GENERAL: ";

				$totales_generales['CARGO SOL'] 	= $general_cargo_sol;
				$totales_generales['ABONO SOL'] 	= $general_abono_sol;
				$totales_generales['CARGO DOL'] 	= $general_cargo_dol;
				$totales_generales['ABONO DOL'] 	= $general_abono_dol;
				$totales_generales['SALDO SOLES'] 	= $general_saldo_soles;
				$totales_generales['SALDO DOLARES'] = $general_saldo_dolares;

				$reporte->Ln();	
				$reporte->nuevaFila($totales_generales);

			}

			// reporte para un SOLO CLIENTE

			if($datos_array[0]['COMBO'] == '02') {

				$Cabecera1 = array( 
							"CAB1"            => " ",
							"CAB2"            => " ",
							"CAB3"            => " ",
							"CAB4"            => " ",
							"CAB5"            => " ",
							"CAB6"            => " ",
							"CAB7"            => " ",
							"IMPORTE S"       => "IMPORTE S/",
							"CAB8"            => " ",
							"IMPORTE D"       => "IMPORTE $"
						  );
			
				$Cabecera2 = array(
							"CLIENTE"       =>  "CLIENTE",
							"FECHA"         =>  "FECHA EMI.",
							"FECHA VEN"	=>  "FECHA VEN.",
							"ACCION"        =>  "ACCION",
							"DOCUMENTO"     =>  "DOCUMENTO",
							"MONEDA"	=>  "MONEDA",
							"CARGO SOL"     =>  "CARGO",
							"ABONO SOL"     =>  "ABONO",
							"CARGO DOL"     =>  "CARGO",
							"ABONO DOL"     =>  "ABONO",
							"SALDO SOLES"   =>  "SALDO S/",
							"SALDO DOLARES" =>  "SALDO $",
						  );

				$fontsize = 6.5;
				$reporte = new CReportes2();
				$reporte->SetMargins(5, 1, 5);
				$reporte->SetFont("courier", "", $fontsize);
				$reporte->definirColumna("CAB1", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB2", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB3", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB4", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB5", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB6", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB7", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("IMPORTE S", $reporte->TIPO_TEXTO, 16, "L", "Cabecera1");
				$reporte->definirColumna("CAB8", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("IMPORTE D", $reporte->TIPO_TEXTO, 16, "L", "Cabecera1");
		
				$reporte->definirColumna("FECHITA", $reporte->TIPO_TEXTO, 18, "R", "Cabecera1");
				$reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "C", "Cabecera1");
				$reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "L");
				$reporte->definirColumna("FECHA VEN", $reporte->TIPO_TEXTOO, 10, "L");
				$reporte->definirColumna("ACCION", $reporte->TIPO_TEXTO, 14, "L");
				$reporte->definirColumna("DOCUMENTO", $reporte->TIPO_TEXTO, 26, "C");
				$reporte->definirColumna("MONEDA", $reporte->TIPO_TEXTO, 8, "L");
				$reporte->definirColumna("CARGO SOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("ABONO SOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("CARGO DOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("ABONO DOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("SALDO SOLES", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("SALDO DOLARES", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("NOMCLI", $reporte->TIPO_TEXTO, 100, "L", "CLI");

				for($j = 0; $j < count($datos_array); $j++) {

					$rows = $rows + 1;

					if ($rows == 1 || is_int($rows/93)) {

						$codigo = $datos_array[$j]['CLIENTE'];
						$raz_social = $datos_array[$j]['RAZSOCIAL'];
						$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
						$reporte->definirCabecera(1, "C", "REPORTE DE COBRANZAS");
						$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
						$reporte->definirCabecera(1, "C", "REPORTE DE COBRANZAS");
						$reporte->definirCabecera(1, "R", "PAG.%p");
						$reporte->definirCabecera(2, "R", " ");
						$reporte->definirCabecera(3, "L", "Usuario : %u");
						$reporte->definirCabecera(3, "C", "Del: ".$fecha_inicio." Al: ".$fecha_fin."");
						$reporte->definirCabecera(3, "R", "%f");
						$reporte->definirCabecera(4, "R", " ");
						$reporte->definirCabecera(5, "L", "Cliente : ".$codigo);
						$reporte->definirCabeceraPredeterminada($Cabecera1, "Cabecera1");
						$reporte->definirCabeceraPredeterminada($Cabecera2);
						$reporte->AddPage();
						$reporte->lineaH();

					}

					$datos['CLIENTE'] 		= $datos_array[$j]['CLIENTE'];
					$datos["FECHA"] 		= $datos_array[$j]["FECHA"];
					$datos["FECHA VEN"] 		= $datos_array[$j]["FECHA VENCIMIENTO"];
					$datos["ACCION"] 		= $datos_array[$j]["ACCION"];
					$datos["MONEDA"]	 	= $datos_array[$j]["MONETOTAL"];
					$datos["DOCUMENTO"]		= $datos_array[$j]["DOCUMENTO"];

					if(($datos_array[$j]["CONTABLE"] == "A" && $datos_array[$j]["TIPODOC"] == "21" && $datos_array[$j]["MOVIMIENTO"] == "1") || ($datos_array[$j]["MOVIMIENTO"] == "3" && $datos_array[$j]["TIPODOC"] == "22") || ($datos_array[$j]["MOVIMIENTO"] == "2")){
						if(TRIM($datos_array[$j]["MONETOTAL"]) == 'S/'){
							$datos["CARGO SOL"] 	= 0.00;
							$total_cargo_soles 	+= 0.00;
							$datos["ABONO SOL"] 	= $datos_array[$j]["CABTOTAL"];
							$total_abono_soles 	+= $datos_array[$j]['CABTOTAL'];
							$datos["CARGO DOL"]	= 0.00;
							$datos["ABONO DOL"]	= 0.00;
						}else{
							$datos["CARGO SOL"] 	= 0.00;
							$total_cargo_soles 	+= 0.00;
							$datos["ABONO SOL"] 	= 0.00;
							$total_abono_soles 	+= 0.00;
							$datos["CARGO DOL"]	= 0.00;
							$datos["ABONO DOL"]	= $datos_array[$j]["CABTOTAL"];
							$total_abono_dolares 	+= $datos_array[$j]["CABTOTAL"];
						}
					}else{
						if(TRIM($datos_array[$j]["MONETOTAL"]) == 'S/'){
							$datos["CARGO SOL"] 	= $datos_array[$j]["CABTOTAL"];
							$total_cargo_soles 	+= $datos_array[$j]['CABTOTAL'];
							$datos["ABONO SOL"] 	= 0.00;
							$total_abono_soles 	+= 0.00;
							$datos["CARGO DOL"]	= 0.00;
							$datos["ABONO DOL"]	= 0.00;
						}else{
							$datos["CARGO SOL"] 	= 0.00;
							$total_cargo_soles 	+= 0.00;
							$datos["ABONO SOL"] 	= 0.00;
							$total_abono_soles 	+= 0.00;
							$datos["CARGO DOL"] 	= $datos_array[$j]["CABTOTAL"];
							$total_cargo_dolares 	+= $datos_array[$j]["CABTOTAL"];
							$datos["ABONO DOL"]	= 0.00;
						}
					}

					if(TRIM($datos_array[$j]["MONETOTAL"]) == 'S/'){
						$datos["SALDO SOLES"]	= $datos_array[$j]["SALDOSOLES"];
						if($datos_array[$j]["CANTCOBRANZA"] <= 2)
							$total_saldo_soles 	+= $datos_array[$j]["SALDOSOLES"];
						else
							$total_saldo_soles 	+= $datos_array[$j]["SALDOFINALSOLES"];
						$datos["SALDO DOLARES"]	= 0.00;
					}else{
						$datos["SALDO SOLES"]	= 0.00;
						$datos["SALDO DOLARES"]	= $datos_array[$j]["SALDOSOLES"];
						if($datos_array[$j]["CANTCOBRANZA"] <= 2)
							$total_saldo_dolares 	+= $datos_array[$j]["SALDOSOLES"];
						else
							$total_saldo_dolares 	+= $datos_array[$j]["SALDOFINALSOLES"];
					}
					
					$reporte->nuevaFila($datos);

					$totales_cliente["DOCUMENTO"] 	= "TOTAL: ";

					$totales_cliente["CARGO SOL"] 	= $total_cargo_soles;
					$totales_cliente["ABONO SOL"] 	= $total_abono_soles;
					$totales_cliente["CARGO DOL"] 	= $total_cargo_dolares;
					$totales_cliente["ABONO DOL"] 	= $total_abono_dolares;
					$totales_cliente["SALDO SOLES"] = $total_saldo_soles;
					$totales_cliente["SALDO DOLARES"] = $total_saldo_dolares;
					

				}

				$reporte->lineaH();
				$reporte->Ln();
				$reporte->nuevaFila($totales_cliente);

			}

		// termina reporte detallado

		} else {

		// empieza reporte resumido

			$Cabecera1 = array( 
						"CAB1"            =>  " ",
						"CAB2"            =>  " ",
						"CAB3"            =>  " ",
						"CAB4"            =>  " ",
						"CAB5"            =>  " ",
						"CAB6"            =>  " ",
						"IMPORTE S"       => "IMPORTE S/",
						"IMPORTE D"       => "IMPORTE $"
					);
		
			$Cabecera2 = array(
						"CLIENTE"       =>  "CLIENTE",
						"FECHA"         =>  "FECHA",
						"ACCION"        =>  "ACCION",
						"TIPO DOCUMENTO"=>  "T. DOC.",
						"DOCUMENTO"     =>  "DOCUMENTO",
						"MO"            =>  "MO",
						"CARGO SOL"     =>  "CARGO",
						"ABONO SOL"     =>  "ABONO",
						"CARGO DOL"     =>  "CARGO",
						"ABONO DOL"     =>  "ABONO",
						"REFERENCIA"    =>  "    REFERENCIA",
						"VOUCHER"       =>  "VOUCHER",
						"GLOSA"		=>  "GLOSA" ,
						"MONETOTAL"	=>  "MON." ,
						"CABTOTAL"	=>  "IMP. TOTAL" 
					  );
			$fontsize = 6;
	
			$reporte = new CReportes2();
			$reporte->SetMargins(15, 5, 15);
			$reporte->SetFont("courier", "", $fontsize);
		
			$reporte->definirColumna("CAB1", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
			$reporte->definirColumna("CAB2", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
			$reporte->definirColumna("CAB3", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
			$reporte->definirColumna("CAB4", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
			$reporte->definirColumna("CAB5", $reporte->TIPO_TEXTO, 5, "L", "Cabecera1");
			$reporte->definirColumna("CAB6", $reporte->TIPO_TEXTO, 38, "L", "Cabecera1");
			$reporte->definirColumna("IMPORTE S", $reporte->TIPO_TEXTO, 23, "C", "Cabecera1");
			$reporte->definirColumna("IMPORTE D", $reporte->TIPO_TEXTO, 20, "C", "Cabecera1");		
			$reporte->definirColumna("CLIENTE", $reporte->TIPO_TEXTO, 60, "L");
			$reporte->definirColumna("-", $reporte->TIPO_TEXTO, 25, "L");
			$reporte->definirColumna("CARGO SOL", $reporte->TIPO_IMPORTE, 10, "R");
			$reporte->definirColumna("ABONO SOL", $reporte->TIPO_IMPORTE, 10, "R");
			$reporte->definirColumna("CARGO DOL", $reporte->TIPO_IMPORTE, 10, "R");
			$reporte->definirColumna("ABONO DOL", $reporte->TIPO_IMPORTE, 10, "R");		
			
			/*$reporte->definirColumna("TOT CARGO SOL", $reporte->TIPO_IMPORTE, 10, "R");
			$reporte->definirColumna("TOT ABONO SOL", $reporte->TIPO_IMPORTE, 10, "R");
			$reporte->definirColumna("TOT CARGO DOL", $reporte->TIPO_IMPORTE, 10, "R");
			$reporte->definirColumna("TOT ABONO DOL", $reporte->TIPO_IMPORTE, 10, "R");*/

		        $rows = 1;
	
			for($j=0;$j<count($datos_array);$j++) {
				if ($rows == 1 || is_int($rows/50)) {
					$codigo     = $datos_array[$j]['CLIENTE'];
					$raz_social = $datos_array[$j]['RAZSOCIAL'];
					$reporte->definirCabecera(1, "L", "SISTEMAWEB");
					$reporte->definirCabecera(1, "C", "ANALISIS MOVIMIENTO DE CLIENTES");
					$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
					$reporte->definirCabecera(1, "C", "ANALISIS MOVIMIENTO DE CLIENTES");
					$reporte->definirCabecera(1, "R", "PAG.%p");
					$reporte->definirCabecera(2, "R", " ");
					$reporte->definirCabecera(3, "L", "Usuario : %u");
					$reporte->definirCabecera(3, "C", "Del: ".$fecha_inicio." Al: ".$fecha_fin."");
					$reporte->definirCabecera(3, "R", "%f");
					$reporte->definirCabeceraPredeterminada($Cabecera1, "Cabecera1");
					$reporte->definirCabeceraPredeterminada($Cabecera2);
					$reporte->AddPage();
					$reporte->lineaH();
					$rows = 2;
				}
			
				if ($datos_array[$j]['CLIENTE'] != $datos_array[$j+1]['CLIENTE']){
					$total_cargo_soles   += $datos_array[$j]["CARGO SOLES"];
					$total_abono_soles   += $datos_array[$j]["ABONO SOLES"];
					$total_cargo_dolares += $datos_array[$j]["CARGO DOLARES"];
					$total_abono_dolares += $datos_array[$j]["ABONO DOLARES"];
					$datos['CLIENTE']     = $datos_array[$j]['CLIENTE'];
					$datos["-"]           = "TOTAL CLIENTE";
					$datos["CARGO SOL"]   = $total_cargo_soles;
					$datos["ABONO SOL"]   = $total_abono_soles;
					$datos["CARGO DOL"]   = $total_cargo_dolares;
					$datos["ABONO DOL"]   = $total_abono_dolares;

					if($datos["CARGO SOL"] > $datos["ABONO SOL"]) {
						$totales["CARGO SOL"] = $datos["CARGO SOL"] - $datos["ABONO SOL"];
					}
					if($datos["CARGO SOL"] < $datos["ABONO SOL"]) {
						$totales["ABONO SOL"] = $datos["CARGO SOL"] - $datos["ABONO SOL"];
					}
					if($datos["CARGO SOL"] == $datos["ABONO SOL"]) {
						$totales["CARGO SOL"] = $datos["CARGO SOL"] - $datos["ABONO SOL"];
						$totales["ABONO SOL"] = $datos["CARGO SOL"] - $datos["ABONO SOL"];
					}
					if($datos["CARGO DOL"] > $datos["ABONO DOL"]) {
						$totales["CARGO DOL"] = $datos["CARGO DOL"] - $datos["ABONO DOL"];
					}
					if($datos["CARGO DOL"] < $datos["ABONO DOL"]) {
						$totales["ABONO DOL"] = $datos["CARGO DOL"] - $datos["ABONO DOL"];
					}	
					if($datos["CARGO DOL"] == $datos["ABONO DOL"]) {
						$totales["CARGO DOL"] = $datos["CARGO DOL"] - $datos["ABONO DOL"];
						$totales["ABONO DOL"] = $datos["CARGO DOL"] - $datos["ABONO DOL"];
					}

					$total_final["CARGO SOL"] = $total_final["CARGO SOL"] + $total_cargo_soles;
			                $total_final["ABONO SOL"] = $total_final["ABONO SOL"] + $total_abono_soles;
					$total_final["CARGO DOL"] = $total_final["CARGO DOL"] + $total_cargo_dolares;
					$total_final["ABONO DOL"] = $total_final["ABONO DOL"] + $total_abono_dolares;
	 				$rows= $rows + 1;
					$reporte->nuevaFila($datos);
					$reporte->nuevaFila($totales);
					$reporte->Ln();
				}
				$total_cargo_soles += $datos_array[$j]["CARGO SOLES"];
				$total_abono_soles += $datos_array[$j]["ABONO SOLES"];
				$total_cargo_dolares += $datos_array[$j]["CARGO DOLARES"];
				$total_abono_dolares += $datos_array[$j]["ABONO DOLARES"];
			
				if ($datos_array[$j]['CLIENTE'] != $datos_array[$j+1]['CLIENTE']) {
					$totales["CARGO SOL"] = 0;
					$totales["ABONO SOL"] = 0;
					$totales["CARGO DOL"] = 0;
					$totales["ABONO DOL"] = 0;
					$total_cargo_soles = 0;
					$total_abono_soles = 0;
					$total_cargo_dolares = 0;
					$total_abono_dolares = 0;
				}
			}
			if($total_final["CARGO SOL"] > $total_final["ABONO SOL"]) {
				$diferencia["CARGO SOL"] = $total_final["CARGO SOL"] - $total_final["ABONO SOL"];
			}
			if($total_final["CARGO SOL"] < $total_final["ABONO SOL"]) {
				$diferencia["ABONO SOL"] = $total_final["CARGO SOL"] - $total_final["ABONO SOL"];
			}
			if($total_final["CARGO SOL"] == $total_final["ABONO SOL"]) {
				$diferencia["CARGO SOL"] = $total_final["CARGO SOL"] - $total_final["ABONO SOL"];
				$diferencia["ABONO SOL"] = $total_final["CARGO SOL"] - $total_final["ABONO SOL"];
			}
			if($total_final["CARGO DOL"] > $total_final["ABONO DOL"]) {
				$diferencia["CARGO DOL"] = $total_final["CARGO DOL"] - $total_final["ABONO DOL"];
			}
			if($total_final["CARGO DOL"] < $total_final["ABONO DOL"]) {
				$diferencia["ABONO DOL"] = $total_final["CARGO DOL"] - $total_final["ABONO DOL"];
			}
			if($total_final["CARGO DOL"] == $total_final["ABONO DOL"]) {
				$diferencia["CARGO DOL"] = $total_final["CARGO DOL"] - $total_final["ABONO DOL"];
				$diferencia["ABONO DOL"] = $total_final["CARGO DOL"] - $total_final["ABONO DOL"];
			}
			$total_final["-"] = "TOTAL FINAL";
			$reporte->lineaH();
			$reporte->Ln();
			$reporte->nuevaFila($total_final);
			$reporte->nuevaFila($diferencia);
	  	}

    		$reporte->Output("/sistemaweb/ccobrar/analisis_movimiento_clientes.pdf", "F");

   		return '<script> window.open("/sistemaweb/ccobrar/analisis_movimiento_clientes.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';

  	}
  
  	function errorResultado($errormsg) {
    		return '<blink>'.$errormsg.'</blink>';
  	}

	function formBuscar($desde, $hasta, $combo, $codigo, $tipmovi, $modo) {

		$estaciones=array('' => 'TODAS');

		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.ANALITICO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ANALITICO'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('estacion', '', $almacen, $estaciones, espacios(3), array("onfocus" => "getFechaEmision();")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr><tr><td align="right">Fecha Inicio: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("desde", "", $desde, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.desde'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="right">Fecha Final: <td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("hasta", "", $hasta, '', 10, 12));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.hasta'".');"> <img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td align="right">Tipo de Busqueda: </td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('busqueda[combo]','',$_REQUEST['busqueda']['combo'],array('01'=>'Todos','02'=>'Por Cliente'),espacios(3), array("onChange"=>"display_cod_cliente(this.value);")));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td><td id="celda1" style="display:none;" align="left">Cod. Cliente: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text('busqueda[codigo]','', $_REQUEST['busqueda']['codigo'], espacios(5), 20, 18,array("class"=>"form_input_numeric", "style" =>$estilo)));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td align="right">Tipo de Movimiento: </td><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_combo('busqueda[tipmovi]','',$_REQUEST['busqueda']['tipmovi'],array('4'=>'Todos', '1'=>'Inclusion', '2'=>'Cancelacion', '3'=>'Aplicacion')));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td align="right">Tipo de Reporte: </td><td>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_radio("busqueda[modo]", "Detallado", "DETALLADO", '', '', Array("checked")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio("busqueda[modo]", "Resumido", "RESUMIDO", ''));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext('</td></tr><tr><td colspan="2" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="HTML"><img src="/sistemaweb/icons/gbuscar.png" align="right" /> Consultar </button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="PDF"><img src="/sistemaweb/images/icono_pdf.gif" align="right" /> PDF </button>'));
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

	function gridViewHTML($arrResult, $sTipoVista) {
		$form = new form2('', 'form_mostrar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.ANALITICO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ANALITICO'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th row="2" colspan="5" class="grid_cabecera">&nbsp;&nbsp;&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2" class="grid_cabecera">&nbsp;&nbsp;IMPORTE S/&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2" class="grid_cabecera">&nbsp;&nbsp;IMPORTE $&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th colspan="2" class="grid_cabecera">&nbsp;&nbsp;&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;F. EMISION&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;F. VENCIMIENTO&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;ACCION&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;DOCUMENTO&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;MONEDA&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;CARGO&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;ABONO&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;CARGO&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;ABONO&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;SALDO S/&nbsp;&nbsp;</th>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<th class="grid_cabecera">&nbsp;&nbsp;SALDO $&nbsp;&nbsp;</th>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$nu_documento_identidad = '';
			$color = '';
			for ($i = 0; $i < count($arrResult); $i++) {
				$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
				if($nu_documento_identidad != $arrResult[$i]['CLIENTE']){
				 	if($i!=0) {
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="5" align="right" class="grid_detalle_total" colspan="4"><b>TOTAL CLIENTE:</b> </td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_cargo_soles, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_abono_soles, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_cargo_dolares, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_abono_dolares, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_saldo_soles, 2, '.' , ',')) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_saldo_dolares, 2, '.' , ',')) . '</td></tr>'));
						
						$total_cargo_soles = 0.00;
						$total_abono_soles = 0.00;
						$total_cargo_dolares = 0.00;
						$total_abono_dolares = 0.00;
						$total_saldo_soles = 0.00;
						$total_saldo_dolares = 0.00;
					}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td align="left" class="grid_detalle_especial" colspan="11"><b>CLIENTE:</b> '.$arrResult[$i]['CLIENTE'].'</td></tr>'));
					$nu_documento_identidad = $arrResult[$i]['CLIENTE'];				
				}

				if(($arrResult[$i]["CONTABLE"] == "A" && $arrResult[$i]["TIPODOC"] == "21" && $arrResult[$i]["MOVIMIENTO"] == "1") || ($arrResult[$i]["MOVIMIENTO"] == "3" && $arrResult[$i]["TIPODOC"] == "22") || ($arrResult[$i]["MOVIMIENTO"] == "2")){
					if(TRIM($arrResult[$i]["MONETOTAL"]) == 'S/'){
						$total_cargo_soles 	+= 0.00;
						$total_abono_soles 	+= $arrResult[$i]['CABTOTAL'];
						$total_cargo_dolares += 0.00;
						$total_abono_dolares += 0.00;
						$general_abono_sol 	+= $arrResult[$i]['CABTOTAL'];
					}else{
						$total_cargo_soles 	+= 0.00;
						$total_abono_soles 	+= 0.00;
						$total_cargo_dolares += 0.00;
						$total_abono_dolares += $arrResult[$i]["CABTOTAL"];
						$general_abono_dol += $arrResult[$i]['CABTOTAL'];
					}
				}else{
					if(TRIM($arrResult[$i]["MONETOTAL"]) == 'S/'){
						$total_cargo_soles 	+= $arrResult[$i]['CABTOTAL'];
						$total_abono_soles 	+= 0.00;
						$total_cargo_dolares += 0.00;
						$total_abono_dolares += 0.00;
						$general_cargo_sol 	+= $arrResult[$i]['CABTOTAL'];
					}else{
						$total_cargo_soles 	+= 0.00;
						$total_abono_soles 	+= 0.00;
						$total_cargo_dolares += $arrResult[$i]["CABTOTAL"];
						$total_abono_dolares += 0.00;
						$general_cargo_dol 	+= $arrResult[$i]['CABTOTAL'];
					}
				}

				if(TRIM($arrResult[$i]["MONETOTAL"]) == 'S/'){
					$total_saldo_dolares += 0.00;
					if($arrResult[$i]["CANTCOBRANZA"] <= 2){
						$total_saldo_soles += $arrResult[$i]["SALDOSOLES"];
						$general_saldo_soles += $arrResult[$i]["SALDOSOLES"];
					}else{
						$total_saldo_soles += $arrResult[$i]["SALDOFINALSOLES"];
						$general_saldo_soles += $arrResult[$i]["SALDOFINALSOLES"];
					}

				}else{
					$total_saldo_soles += 0.00;
					$total_saldo_dolares += $arrResult[$i]["SALDOSOLES"];
					$general_saldo_dolares += $arrResult[$i]["SALDOSOLES"];
				}

				if($sTipoVista == 'DETALLADO'){
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($arrResult[$i]['FECHA']) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($arrResult[$i]['FECHA VENCIMIENTO']) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($arrResult[$i]['ACCION']) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($arrResult[$i]['DOCUMENTO']) . '</td>'));
						$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="center" class="'.$color.'">&nbsp;' . htmlentities($arrResult[$i]['MONETOTAL']) . '</td>'));
					
						if(($arrResult[$i]["CONTABLE"] == "A" && $arrResult[$i]["TIPODOC"] == "21" && $arrResult[$i]["MOVIMIENTO"] == "1") || ($arrResult[$i]["MOVIMIENTO"] == "3" && $arrResult[$i]["TIPODOC"] == "22") || ($arrResult[$i]["MOVIMIENTO"] == "2")){
							if(TRIM($arrResult[$i]["MONETOTAL"]) == 'S/'){
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['CABTOTAL'], 2, '.' , ',')) . '</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
							} else {
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['CABTOTAL'], 2, '.' , ',')) . '</td>'));
							}
						} else {
							if(TRIM($arrResult[$i]["MONETOTAL"]) == 'S/'){
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['CABTOTAL'], 2, '.' , ',')) . '</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
							} else {
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['CABTOTAL'], 2, '.' , ',')) . '</td>'));
								$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
							}
						}

						if(TRIM($arrResult[$i]["MONETOTAL"]) == 'S/'){
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['SALDOSOLES'], 2, '.' , ',')) . '</td>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
						}else{
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;0.00</td>'));
							$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="'.$color.'">&nbsp;' . htmlentities(number_format($arrResult[$i]['SALDOSOLES'], 2, '.' , ',')) . '</td>'));
						}
					$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
				}
			}

			if(($arrResult[$i]["CONTABLE"] == "A" && $arrResult[$i]["TIPODOC"] == "21" && $arrResult[$i]["MOVIMIENTO"] == "1") || ($arrResult[$i]["MOVIMIENTO"] == "3" && $arrResult[$i]["TIPODOC"] == "22") || ($arrResult[$i]["MOVIMIENTO"] == "2")){
				if(TRIM($arrResult[$i]["MONETOTAL"]) == 'S/'){
					$total_cargo_soles 	+= 0.00;
					$total_abono_soles 	+= $arrResult[$i]['CABTOTAL'];
					$total_cargo_dolares += 0.00;
					$total_abono_dolares += 0.00;
					$general_abono_sol 	+= $arrResult[$i]['CABTOTAL'];
				}else{
					$total_cargo_soles 	+= 0.00;
					$total_abono_soles 	+= 0.00;
					$total_cargo_dolares += 0.00;
					$total_abono_dolares += $arrResult[$i]["CABTOTAL"];
					$general_abono_dol += $arrResult[$i]['CABTOTAL'];
				}
			}else{
				if(TRIM($arrResult[$i]["MONETOTAL"]) == 'S/'){
					$total_cargo_soles 	+= $arrResult[$i]['CABTOTAL'];
					$total_abono_soles 	+= 0.00;
					$total_cargo_dolares += 0.00;
					$total_abono_dolares += 0.00;
					$general_cargo_sol 	+= $arrResult[$i]['CABTOTAL'];
				}else{
					$total_cargo_soles 	+= 0.00;
					$total_abono_soles 	+= 0.00;
					$total_cargo_dolares += $arrResult[$i]["CABTOTAL"];
					$total_abono_dolares += 0.00;
					$general_cargo_dol 	+= $arrResult[$i]['CABTOTAL'];
				}
			}

			if(TRIM($arrResult[$i]["MONETOTAL"]) == 'S/'){
				$total_saldo_dolares += 0.00;
				if($arrResult[$i]["CANTCOBRANZA"] <= 2){
					$total_saldo_soles += $arrResult[$i]["SALDOSOLES"];
					$general_saldo_soles += $arrResult[$i]["SALDOSOLES"];
				}else{
					$total_saldo_soles += $arrResult[$i]["SALDOFINALSOLES"];
					$general_saldo_soles += $arrResult[$i]["SALDOFINALSOLES"];
				}
			}else{
				$total_saldo_soles += 0.00;
				$total_saldo_dolares += $arrResult[$i]["SALDOSOLES"];
				$general_saldo_dolares += $arrResult[$i]["SALDOSOLES"];
			}
						
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="5" align="right" class="grid_detalle_total" colspan="4"><b>TOTAL CLIENTE:</b> </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_cargo_soles, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_abono_soles, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_cargo_dolares, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_abono_dolares, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_saldo_soles, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($total_saldo_dolares, 2, '.' , ',')) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="5" align="right" class="grid_detalle_total" colspan="4"><b>TOTAL GENERAL:</b> </td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($general_cargo_sol, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($general_abono_sol, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($general_cargo_dol, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($general_abono_dol, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($general_saldo_soles, 2, '.' , ',')) . '</td>'));
				$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right" class="grid_detalle_total">' . htmlentities(number_format($general_saldo_dolares, 2, '.' , ',')) . '</td>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

		return $form->getForm();
	}
}

