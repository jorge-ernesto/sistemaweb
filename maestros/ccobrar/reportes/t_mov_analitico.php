<?php

include('../include/reportes2.inc.php');

class TarjetasMagneticasTemplate extends Template {
 
	function titulo(){
		$titulo = '<div align="center"><h2>REPORTE ANALITICO DE MOVIMIENTOS POR CLIENTE</h2></div><hr>';
		return $titulo;
	}
  
	function ReportePDF($datos_array) {

		$fecha_inicio = $datos_array[0]['FECHA_INI'];
		$fecha_fin    = $datos_array[0]['FECHA_FIN'];

		if ($datos_array[0]['RADIO'] == 'DETALLADO') {	// empieza reporte detallado
		
			if ($datos_array[0]['COMBO'] == '01') { // reporte para TODOS los clientes
			
				$Cabecera1 = array( 
							"CAB1"          =>  " ",
							"CAB2"          =>  " ",
							"CAB3"          =>  " ",
							"CAB4"          =>  " ",
							"CAB5"          =>  " ",
							"CAB6"          =>  " ",
							"IMPORTE S"     => "IMPORTE S/.",
							"FECHITA"     => "FECHA DE"
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
							"FECHA VEN"     =>  "VENCIMIENTO",
							"REFERENCIA"    =>  "    REFERENCIA",
							"VOUCHER"       =>  "VOUCHER",
							"GLOSA"		=>  "GLOSA" ,
							"MONETOTAL"	=>  "MON." ,
							"CABTOTAL"	=>  "IMP.TOTAL" 
						  );
						  
				$CabCli = array( 	"NOMCLI"          =>  " "  );
						  
				$fontsize = 6;
				$reporte = new CReportes2();
				$reporte->SetMargins(5, 1, 5);
				$reporte->SetFont("courier", "", $fontsize);
				$reporte->definirColumna("CAB1", $reporte->TIPO_TEXTO, 5, "L", "Cabecera1");
				$reporte->definirColumna("CAB2", $reporte->TIPO_TEXTO, 5, "L", "Cabecera1");
				$reporte->definirColumna("CAB3", $reporte->TIPO_TEXTO, 5, "L", "Cabecera1");
				$reporte->definirColumna("CAB4", $reporte->TIPO_TEXTO, 5, "L", "Cabecera1");
				$reporte->definirColumna("CAB5", $reporte->TIPO_TEXTO, 5, "L", "Cabecera1");
				$reporte->definirColumna("CAB6", $reporte->TIPO_TEXTO, 8, "L", "Cabecera1");

				$reporte->definirColumna("IMPORTE S", $reporte->TIPO_TEXTO, 18, "R", "Cabecera1");
				$reporte->definirColumna("FECHITA", $reporte->TIPO_TEXTO, 18, "R", "Cabecera1");
				$reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "C", "Cabecera1");
				$reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "L");
				$reporte->definirColumna("ACCION", $reporte->TIPO_TEXTO, 11, "L");
				$reporte->definirColumna("TIPO DOCUMENTO", $reporte->TIPO_TEXTO, 10, "L");
				$reporte->definirColumna("DOCUMENTO", $reporte->TIPO_TEXTO, 10, "L");
				$reporte->definirColumna("MO", $reporte->TIPO_TEXTO, 5, "C");
				$reporte->definirColumna("CARGO SOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("ABONO SOL", $reporte->TIPO_IMPORTE, 10, "L");
				$reporte->definirColumna("FECHA VEN", $reporte->TIPO_TEXTOO, 12, "R");
				$reporte->definirColumna("REFERENCIA", $reporte->TIPO_TEXTOO, 25, "L");
				$reporte->definirColumna("VOUCHER", $reporte->TIPO_TEXTO, 12, "L");
				$reporte->definirColumna("GLOSA", $reporte->TIPO_TEXTO, 20, "C");
				$reporte->definirColumna("MONETOTAL", $reporte->TIPO_TEXTO, 3, "L");
				$reporte->definirColumna("CABTOTAL", $reporte->TIPO_TEXTO, 13, "L");
				$reporte->definirColumna("NOMCLI", $reporte->TIPO_TEXTO, 100, "L", "CLI");
				
				/* 
				$reporte->definirColumna("TOT CARGO SOL", $reporte->TIPO_IMPORTE, 10, "R");
				$reporte->definirColumna("TOT ABONO SOL", $reporte->TIPO_IMPORTE, 10, "R");
				$reporte->definirColumna("TOT CARGO DOL", $reporte->TIPO_IMPORTE, 10, "R");
				$reporte->definirColumna("TOT ABONO DOL", $reporte->TIPO_IMPORTE, 10, "R");*/				
				
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
				//$reporte->lineaH();
		
				for($j=0;$j<count($datos_array);$j++) {  //////
					if ($datos_array[$j-1]['CLIENTE'] != $datos_array[$j]['CLIENTE']){
						$reporte->Ln();	
						$codigo = $datos_array[$j]['CLIENTE'];
						$raz_social = $datos_array[$j]['RAZSOCIAL'];					
						
						$arr = array("NOMCLI"=>"CLIENTE: ".$codigo);
						$reporte->nuevaFila($arr,"CLI"); 
						$reporte->Ln();						
					}
					$datos['CLIENTE'] 	 = $datos_array[$j]['CLIENTE'];
					$datos["FECHA"] 	 = $datos_array[$j]["FECHA"];
					$datos["ACCION"] 	 = $datos_array[$j]["ACCION"];
					$datos["TIPO DOCUMENTO"] = $datos_array[$j]["TIPO DOCUMENTO"];
					$datos["DOCUMENTO"] 	 = $datos_array[$j]["DOCUMENTO"];
					$datos["MO"] 		 = $datos_array[$j]["MONEDA"];
					$datos["CARGO SOL"] 	 = $datos_array[$j]["CARGO SOLES"];
					$datos["ABONO SOL"] 	 = $datos_array[$j]["ABONO SOLES"];
					$datos["FECHA VEN"] 	 = $datos_array[$j]["FECHA VENCIMIENTO"];
					$datos["REFERENCIA"]	 = $datos_array[$j]["DOC REFERENCIA"];
					$datos["VOUCHER"]	 = $datos_array[$j]["VOUCHER"];
					$datos["GLOSA"]	 	 = $datos_array[$j]["GLOSA"];
					$datos["MONETOTAL"]	 = $datos_array[$j]["MONETOTAL"];
					$datos["CABTOTAL"]	 = $datos_array[$j]["CABTOTAL"];
				
					$total_cargo_soles	+= $datos_array[$j]['CARGO SOLES'];
					$total_abono_soles  	+= $datos_array[$j]['ABONO SOLES'];
					$reporte->nuevaFila($datos);
				
					if($datos_array[$j]['CLIENTE'] != $datos_array[$j+1]['CLIENTE']) {
	  					$totales_cliente['DOCUMENTO'] = "TOTALES DEL CLIENTE";
						$totales_cliente['CARGO SOL'] = $total_cargo_soles;
						$totales_cliente['ABONO SOL'] = $total_abono_soles;
						$general_cargo_sol += $totales_cliente['CARGO SOL'];
						$general_abono_sol += $totales_cliente['ABONO SOL'];
						$reporte->Ln();
						$reporte->nuevaFila($totales_cliente);
						$reporte->Ln();	
						$reporte->lineaH();
						$total_cargo_soles   = 0;
						$total_abono_soles   = 0;
					}
				
					if ($datos_array[$j+1][0] != $datos_array[$j][0]) {
						$reporte->lineaH();
						$reporte->Ln();
						$reporte->Ln();
					}
				} /////
				$totales_generales['DOCUMENTO'] = "TOTALES";
				$totales_generales['CARGO SOL'] = $general_cargo_sol;
				$totales_generales['ABONO SOL'] = $general_abono_sol;
				$reporte->Ln();	
				$reporte->nuevaFila($totales_generales);
			}

			// reporte para un SOLO CLIENTE

			if($datos_array[0]['COMBO'] == '02') {
				$Cabecera1 = array( 
							"CAB1"            =>  " ",
							"CAB2"            =>  " ",
							"CAB3"            =>  " ",
							"CAB4"            =>  " ",
							"CAB5"            =>  " ",
							"CAB6"            =>  " ",
							"IMPORTE S"       => "IMPORTE S/."
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
				$reporte->SetMargins(5, 1, 5);
				$reporte->SetFont("courier", "", $fontsize);
			
				$reporte->definirColumna("CAB1", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB2", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB3", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB4", $reporte->TIPO_TEXTO, 10, "L", "Cabecera1");
				$reporte->definirColumna("CAB5", $reporte->TIPO_TEXTO, 5, "L", "Cabecera1");
				$reporte->definirColumna("CAB6", $reporte->TIPO_TEXTO, 15, "L", "Cabecera1");
				$reporte->definirColumna("IMPORTE S", $reporte->TIPO_TEXTO, 23, "L", "Cabecera1");
		
				$reporte->definirColumna("FECHITA", $reporte->TIPO_TEXTO, 18, "R", "Cabecera1");
				$reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "C", "Cabecera1");
				$reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "L");
				$reporte->definirColumna("ACCION", $reporte->TIPO_TEXTO, 11, "L");
				$reporte->definirColumna("TIPO DOCUMENTO", $reporte->TIPO_TEXTO, 8, "L");
				$reporte->definirColumna("DOCUMENTO", $reporte->TIPO_TEXTO, 10, "L");
				$reporte->definirColumna("MO", $reporte->TIPO_TEXTO, 5, "C");
				$reporte->definirColumna("CARGO SOL", $reporte->TIPO_IMPORTE, 12, "L");
				$reporte->definirColumna("ABONO SOL", $reporte->TIPO_IMPORTE, 10, "L");
				$reporte->definirColumna("FECHA VEN", $reporte->TIPO_TEXTOO, 12, "R");
				$reporte->definirColumna("REFERENCIA", $reporte->TIPO_TEXTOO, 25, "L");
				$reporte->definirColumna("VOUCHER", $reporte->TIPO_TEXTO, 17, "L");
				$reporte->definirColumna("GLOSA", $reporte->TIPO_TEXTO, 18, "L");
				$reporte->definirColumna("MONETOTAL", $reporte->TIPO_TEXTO, 3, "L");
				$reporte->definirColumna("CABTOTAL", $reporte->TIPO_TEXTO, 13, "L");
				$reporte->definirColumna("NOMCLI", $reporte->TIPO_TEXTO, 100, "L", "CLI");
				
			
				/*$reporte->definirColumna("TOT CARGO SOL", $reporte->TIPO_IMPORTE, 10, "R");
				$reporte->definirColumna("TOT ABONO SOL", $reporte->TIPO_IMPORTE, 10, "R");
				$reporte->definirColumna("TOT CARGO DOL", $reporte->TIPO_IMPORTE, 10, "R");
				$reporte->definirColumna("TOT ABONO DOL", $reporte->TIPO_IMPORTE, 10, "R");*/
			
				for($j=0;$j<count($datos_array);$j++) {
					$rows= $rows + 1;
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
					$datos['CLIENTE'] = $datos_array[$j]['CLIENTE'];
					$datos["FECHA"] = $datos_array[$j]["FECHA"];
					$datos["ACCION"] = $datos_array[$j]["ACCION"];
					$datos["TIPO DOCUMENTO"] = $datos_array[$j]["TIPO DOCUMENTO"];
					$datos["DOCUMENTO"] = $datos_array[$j]["DOCUMENTO"];
					$datos["MO"] = $datos_array[$j]["MONEDA"];
					$datos["CARGO SOL"] = $datos_array[$j]["CARGO SOLES"];
					$datos["ABONO SOL"] = $datos_array[$j]["ABONO SOLES"];
					$datos["CARGO DOL"] = $datos_array[$j]["CARGO DOLARES"];
					$datos["ABONO DOL"] = $datos_array[$j]["ABONO DOLARES"];
					$datos["REFERENCIA"] = $datos_array[$j]["DOC REFERENCIA"];
					$datos["VOUCHER"] = $datos_array[$j]["VOUCHER"];
					$datos["GLOSA"] = $datos_array[$j]["GLOSA"];					
					$datos["MONETOTAL"]	 = $datos_array[$j]["MONETOTAL"];
					$datos["CABTOTAL"]	 = $datos_array[$j]["CABTOTAL"];
					
					$reporte->nuevaFila($datos);
					$total_cargo_soles += $datos_array[$j]['CARGO SOLES'];
					$total_abono_soles += $datos_array[$j]['ABONO SOLES'];
					$total_cargo_dolares += $datos_array[$j]['CARGO DOLARES'];
					$total_abono_dolares += $datos_array[$j]['ABONO DOLARES'];
					$totales_cliente['DOCUMENTO'] = "TOTALES";
					$totales_cliente['CARGO SOL'] = $total_cargo_soles;
					$totales_cliente['ABONO SOL'] = $total_abono_soles;
					$totales_cliente['CARGO DOL'] = $total_cargo_dolares;
					$totales_cliente['ABONO DOL'] = $total_abono_dolares; 
				}
				$reporte->lineaH();
				$reporte->Ln();
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
						"IMPORTE S"       => "IMPORTE S/.",
						"IMPORTE D"       => "IMPORTE US$/."
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

	function formBuscar() {
		$fecha_inicio = @$_REQUEST['busqueda']['fecha_ini'];
		$fecha_final  = @$_REQUEST['busqueda']['fecha_fin'];
		$codi_cli  = @$_REQUEST['busqueda']['codigo'];
		$fecha_ini = date("d/m/Y");
		$fecha_fin = date("d/m/Y");
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.ANALITICO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ANALITICO'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[fecha_ini]', @$fecha_inicio));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[fecha_fin]', @$fecha_final));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="3"> <tr><td class="form_label">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[fecha_ini]','Fecha Inicio  :', $_REQUEST['busqueda']['fecha_ini']?@$_REQUEST["busqueda"]["fecha_ini"]:@$fecha_ini, espacios(3), 20, 18));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[fecha_fin]','Fecha Fin :', $_REQUEST['busqueda']['fecha_fin']?@$_REQUEST["busqueda"]["fecha_fin"]:@$fecha_fin, espacios(0), 20, 18));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('busqueda[combo]','Tipo de Busqueda',$_REQUEST['busqueda']['combo'],array('01'=>'Todos','02'=>'Por Cliente'),espacios(3), array("onChange"=>"display_cod_cliente(this);")));

		if ($_REQUEST['busqueda']['combo']=='01' || $_REQUEST['busqueda']['combo']=='')	
			$estilo = "display:none";
		else 
			$estilo = "display:inline";

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','', $_REQUEST['busqueda']['codigo'], espacios(5), 20, 18,array("class"=>"form_input_numeric", "style" =>$estilo)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('busqueda[tipmovi]','Tipo de Movimiento',$_REQUEST['busqueda']['tipmovi'],array('4'=>'Todos', '1'=>'Inclusion', '2'=>'Cancelacion', '3'=>'Aplicacion')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio("busqueda[modo]", "Detallado", "DETALLADO", '', '', Array("checked")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_radio("busqueda[modo]", "Resumido", "RESUMIDO", ''));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(0)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));

		return $form->getForm();
  	}
}
