<?php

include('../include/reportes2.inc.php');

class ReporteGeneralTemplatePDF {

    	function ReportePDF($res,$resta,$fecha_hasta,$tasa_cambio,$porgrupo,$vale,$cliente)   {
    	
    		$Cabecera = array( 
				    "CLIENTE"           => "CLIENTE",
				    "DOCUMENTO"         => "DOCUMENTO",
				    "F. EMISION"        => "F. EMISION",
				    "VENCIMIENTO" 	=> "VENCIMIENTO",
				    "MONEDA"            => "MONEDA",
				    "IMPORTE"           => "IMPORTE",				    
				    "SUCURSAL" 		=> "SUCURSAL",
				    "DOLARES"           => "SALDO $",
				    "SOLES"             => "SALDO S/.",
				    "CREDITO"           => "CRDEDITO"
				    );

		$CabCli = array( 	"NOMCLI"          =>  " "  );

    		$fontsize = 7;
    		$reporte = new CReportes2();
    		$reporte->SetMargins(5, 5, 5);
    		$reporte->SetFont("courier", "", $fontsize);    
	
    		$reporte->definirColumna("CLIENTE", $reporte->TIPO_TEXTO, 15, "L");
    		$reporte->definirColumna("DOCUMENTO", $reporte->TIPO_TEXTO, 20, "L");
    		$reporte->definirColumna("F. EMISION", $reporte->TIPO_TEXTO, 12, "R");
    		$reporte->definirColumna("VENCIMIENTO", $reporte->TIPO_TEXTO, 12, "R");
    		$reporte->definirColumna("MONEDA", $reporte->TIPO_TEXTO, 6, "L");
    		$reporte->definirColumna("IMPORTE", $reporte->TIPO_TEXTO, 10, "R");    		
    		$reporte->definirColumna("SUCURSAL", $reporte->TIPO_TEXTO, 15, "C");
    		$reporte->definirColumna("DOLARES", $reporte->TIPO_IMPORTE, 10, "R");
    		$reporte->definirColumna("SOLES", $reporte->TIPO_IMPORTE, 15, "R");
		$reporte->definirColumna("CREDITO", $reporte->TIPO_IMPORTE, 15, "R");
		$reporte->definirColumna("NOMCLI", $reporte->TIPO_TEXTO, 100, "L", "CLI");
    
    		$reporte->definirColumna("DESCRIPCION", $reporte->TIPO_TEXTO, 75, "L","_DETALLE");
    
    		$reporte->definirCabecera(1, "L", "SISTEMAWEB");
    		$reporte->definirCabecera(1, "C", "REPORTE PENDIENTE DE COBRANZA");
    		$reporte->definirCabecera(1, "R", "PAG.%p");
    		$reporte->definirCabecera(2, "C", " Al: ".$fecha_hasta."");
    		$reporte->definirCabecera(2, "R", "%f");
    		$reporte->definirCabecera(3, "C", "TASA DE CAMBIO: ".$tasa_cambio."");
    		$reporte->definirCabecera(4, "R", " ");
    
    		$reporte->definirCabeceraPredeterminada($Cabecera);    
    		$reporte->AddPage();

		if($porgrupo == 'GRUPOEMP'){

			for($j=0;$j<count($res);$j++){

				if ($res[$j-1]['grupo'] != $res[$j]['grupo']){
					$reporte->Ln();	
					$codigo = $res[$j]['grupo'];				
						
					$arr = array("NOMCLI"=>$codigo);
					$reporte->nuevaFila($arr,"CLI"); 
					$reporte->Ln();
			
				}

					$datos["DOCUMENTO"] 	 = $res[$j]["documento"];
					$datos["F. EMISION"] 	 = $res[$j]["fechaemision"];
					$datos["VENCIMIENTO"] 	 = $res[$j]["fechavencimiento"];
					$datos["MONEDA"]	 = $res[$j]["monetotal"];

					if($res[$j]["tipo"] == '20')
						$datos["IMPORTE"]	 = "-".$res[$j]["importe"];
					else
						$datos["IMPORTE"]	 = $res[$j]["importe"];

					$datos["SUCURSAL"]	 = $res[$j]["almacen"];

					if($res[$j]["tipo"] == '20')
						$datos["SOLES"]		 = "-".$res[$j]["saldo"];
					else
						$datos["SOLES"]		 = $res[$j]["saldo"];

					$datos["CREDITO"]	 = $res[$j]["credito"];

					if($res[$j]["tipo"] == '20')
						$total_docu_solesNDGRP	+= $res[$j]["saldo"];	
					else
						$total_docu_soles	+= $res[$j]["saldo"];

					$credito 	 	 = $datos["CREDITO"];

					$reporte->nuevaFila($datos);

						if($res[$j]['grupo'] != $res[$j+1]['grupo']) {

							$totales_cliente["SUCURSAL"] = "* TOTAL SALDO *";
							if($res[$j]["tipo"] == '20')
								$totales_cliente["SOLES"] = $total_docu_soles - $total_docu_solesNDGRP - $credito;
							else
								$totales_cliente["SOLES"] = $total_docu_soles - $credito;
							$general_doc_sol += $totales_cliente["SOLES"];
							$reporte->Ln();
							$reporte->nuevaFila($totales_cliente);
							$reporte->Ln();	
							$reporte->lineaH();
							$total_docu_soles   = 0;

						
						}
			}

		}else{

			for($j=0;$j<count($res);$j++){

				if ($res[$j-1]['grupo'] != $res[$j]['grupo']){

					$reporte->Ln();	
					$codigo = $res[$j]['grupo'];				
						
					$arr = array("NOMCLI"=>$codigo);
					$reporte->nuevaFila($arr,"CLI"); 
					$reporte->Ln();
			
				}

					$datos["DOCUMENTO"] 	 = $res[$j]["documento"];
					$datos["F. EMISION"] 	 = $res[$j]["fechaemision"];
					$datos["VENCIMIENTO"] 	 = $res[$j]["fechavencimiento"];
					$datos["MONEDA"]	 = $res[$j]["monetotal"];
					$datos["IMPORTE"]	 = $res[$j]["importe"];
					$datos["SUCURSAL"]	 = $res[$j]["almacen"];
					$datos["SOLES"]		 = $res[$j]["saldo"];
					$datos["CREDITO"]	 = $res[$j]["credito"];
					$datos["TIPO"]	 	= $res[$j]["tipo"];
					
					if($res[$j]["tipo"] == '20')
						$total_docu_solesND	+= $res[$j]["saldo"];									
					else
						$total_docu_soles	+= $res[$j]["saldo"];

					$credito 	 	 = $datos["CREDITO"];

					$reporte->nuevaFila($datos);

					$total_docu_soles_todo	+= $res[$j]["saldo"];

					if($res[$j]['grupo'] != $res[$j+1]['grupo']) {

						$totales_cliente["SUCURSAL"] = "TOTAL DOCUMENTOS";
						if($res[$j]["tipo"] == '20')
							$totales_cliente["SOLES"] = $total_docu_soles - $total_docu_solesND;
						else
							$totales_cliente["SOLES"] = $total_docu_soles;
						$general_doc_sol += $totales_cliente["SOLES"];
						$reporte->Ln();
						$reporte->nuevaFila($totales_cliente);
						$reporte->Ln();	
						$reporte->lineaH();
						$total_docu_soles   = 0;

					}

					for($i = 0; $i<count($resta); $i++){
	
							if($resta[$i]['grupo'] == $codigo and $resta[$i]['grupo'] != $res[$j+1]['grupo']){

								$codigo_val[$i] = $resta[$i]['grupo'];

								$datos["DOCUMENTO"] 	 = $resta[$i]["documentoval"];
								$datos["F. EMISION"] 	 = $resta[$i]["fecha"];
								$datos["VENCIMIENTO"] 	 = "-";
								$datos["MONEDA"]	 = "S/.";
								$datos["IMPORTE"]	 = $resta[$i]["importeval"];
								$datos["SUCURSAL"]	 = $res[$j]["almacen"];
								$datos["SOLES"]		 = $resta[$i]["importeval"];
								$datos["CREDITO"]	 = $resta[$i]["credito"];

								$total_vales_soles	+= $resta[$i]["importeval"];

								$reporte->nuevaFila($datos);

								if($resta[$i]['grupo'] != $resta[$i+1]['grupo']) {

									$totales_cliente["SUCURSAL"] = "TOTAL VALES";
									$totales_cliente["SOLES"] = $total_vales_soles;
									$general_val_sol += $totales_cliente["SOLES"];
									$reporte->Ln();
									$reporte->nuevaFila($totales_cliente);
									$reporte->Ln();	
									$reporte->lineaH();
									$totales_cliente["SUCURSAL"] = "TOTAL CLIENTE";
									$totales_cliente["SOLES"] = $general_val_sol + $general_doc_sol;
									$reporte->Ln();
									$reporte->nuevaFila($totales_cliente);
									$reporte->Ln();	
									$reporte->lineaH();
									$totales_cliente["SUCURSAL"] = "TOTAL SALDO";
									$totales_cliente["SOLES"] = $general_val_sol + $general_doc_sol - $credito;
									$reporte->Ln();
									$reporte->nuevaFila($totales_cliente);
									$reporte->Ln();	
									$reporte->lineaH();
									$total_vales_soles   = 0;

								}
							}
					}
			}


		}

		if($vale == '1' and $porgrupo == 'CLIENTE'){

			for($i=0; $i<count($resta); $i++){

				if ($resta[$i-1]['grupo'] != $resta[$i]['grupo'] and $resta[$i]['grupo'] != $codigo_val[$i]){

					$reporte->Ln();	
					$codigo_vales = $resta[$i]['grupo'];				
					
					$arr = array("NOMCLI"=>$codigo_vales);
					$reporte->nuevaFila($arr,"CLI"); 
					$reporte->Ln();
		
				}

					if($resta[$i]['grupo'] != $codigo_val[$i] and $codigo_vales != $codigo_val[$i]){

						$datos["DOCUMENTO"] 	 = $resta[$i]["documentoval"];
						$datos["F. EMISION"] 	 = $resta[$i]["fecha"];
						$datos["VENCIMIENTO"] 	 = "-";
						$datos["MONEDA"]	 = "S/.";
						$datos["IMPORTE"]	 = $resta[$i]["importeval"];
						$datos["SUCURSAL"]	 = $resta[$i]["almacen"];
						$datos["SOLES"]		 = $resta[$i]["importeval"];
						$datos["CREDITO"]	 = $resta[$i]["credito"];

						$total_vales_soles	+= $resta[$i]["importeval"];
						$credito 	 	 = $datos["CREDITO"];

						$reporte->nuevaFila($datos);

					}

					if($resta[$i]['grupo'] != $resta[$i+1]['grupo'] and $resta[$i]['grupo'] != $codigo_val[$i]) {

						$totales_cliente["SUCURSAL"] = " TOTAL VALES ";
						$totales_cliente["SOLES"] = $total_vales_soles;
						$general_vales_sol += $totales_cliente["SOLES"];
						$reporte->Ln();
						$reporte->nuevaFila($totales_cliente);
						$reporte->Ln();	
						$reporte->lineaH();
						$total_vales_soles   = 0;

					}
			}
		}
		

		if($cliente == 'N'){

			$totales_cliente["SUCURSAL"] = "TOTAL CLIENTE";
			$totales_cliente["SOLES"] = $general_val_sol + $general_doc_sol;
			$reporte->Ln();
			$reporte->nuevaFila($totales_cliente);
			$reporte->Ln();	
		}else{

			$totales_generales['SUCURSAL'] = "TOTALES GENERALES";
			$totales_generales['SOLES'] = $general_vales_sol + $general_val_sol + $general_doc_sol;
			$reporte->Ln();	
			$reporte->nuevaFila($totales_generales);
		}
		
    		$reporte->Output("/sistemaweb/ccobrar/estado_cuenta_general.pdf", "F");
    		
    		return '<script> window.open("/sistemaweb/ccobrar/estado_cuenta_general.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
    	}
}
