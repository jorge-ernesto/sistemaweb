<?php
date_default_timezone_set('America/Lima');

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();

$data		= $_SESSION['data_asientos'];//Documentos manuales, tickets y electronicos (oficina y playa)
$cuentas	= $_SESSION['data_cuentas'];
$almacen	= $_SESSION['almacen'];
$modulos	= $_SESSION['modulos'];
$decimales	= $_SESSION['decimales'];
$nu_tarjeta_credito = $_SESSION['nu_tarjeta_credito'];

$campo13_sMonth	= "0" . $_SESSION['sMonth'];// Código de Cliente - C 15 (cambio de mes: 001 para enero, 002 para febrero, ...)

$asientos	= null;

if(!empty($data)){
	ob_clean();
	//$ntickets 		= count($data);
	//$arraytickets 	= $data["ticket"];//ARREGLO DE TICKETS
	$importe_vacio	= 0.00;
	$vacio			= " ";
	$id				= 1;
	$cuenta12		= NULL;
	$rucanulado		= "00000000000";
	$clienteanulado = "Comprobante Anulado";

	if ( $modulos==1 ){//VENTA
		$glosa			= "Venta de Combustible";
		$nombre_archivo = "vtassflor.txt";

		$sNumeroCuenta = '0';
		$fDebe = 0;
		$fHaber = 0;

		/*** Agregado 2020-01-17 ***/
		foreach($data as $key=>$value){					
			if (isset($data[$key]['caja'])) { //Se realizo el pago por POS, tablas pos_transXXXXXX
				$serie  = $data[$key]['serie'];
				$numero = $data[$key]['numero'];
				$usr    = $serie."-".$numero;				

				$sql = "SELECT fpago, at FROM pos_trans202001 WHERE usr ~* '$usr';";					
				$data[$key]['sql'] = $sql;				
			} else { //Se realizo el pago por la web, tabla fac_ta_factura_cabecera
				$serie  = $data[$key]['serie'];
				$numero = $data[$key]['numero'];				

				$sql = "SELECT ch_fac_seriedocumento, ch_fac_numerodocumento, dt_fac_fecha, ch_fac_credito FROM fac_ta_factura_cabecera WHERE ch_fac_seriedocumento ~* '$serie' AND ch_fac_numerodocumento ~* '$numero';";
				$data[$key]['sql'] = $sql;				
			}
			$usr_referencia = $data[$key]['serie_numero_referencia'];
			$sql_referencia = "SELECT fpago, at FROM pos_trans202001 WHERE usr ~* '$usr_referencia';";	
			$data[$key]['sql_referencia'] = $sql_referencia;
		}
		/***/
		
		/*** Problema para calcular cantidades exactas 2020-02-06 ***/
		$data_calculo = array();
		for ($i = 0; $i < count($data); $i++) {
			$a			= $data[$i];
			foreach($cuentas as $regcuenta){
				$sNumeroCuenta = substr($regcuenta['nu_cuentacontable'], 0, 2);
				$sNumeroCuentaCompleto = $regcuenta['nu_cuentacontable'];
				if ( $sNumeroCuenta == '70' && abs($a['importe']) == 0 ) {
				}else{

					/*** Validacion imponible ***/					
					$imponible_real = $a['importe'] - $a['igv'];
					$a['imponible'] = $imponible_real;
					/***/

					$fHaberTotal = $a['importe'];
					$fDebeTotal = 0;

					$fHaberImpuesto = 0;
					$fDebeImpuesto = $a['igv'];

					$fHaberImponible = 0;
					$fDebeImponible = $a['imponible'];

					if ( $a['tipo'] == '07' || $a['tipo'] == '08' ) {
						$fHaberTotal = 0;
						$fDebeTotal = $a['importe'];

						$fHaberImpuesto = $a['igv'];
						$fDebeImpuesto = 0;

						$fHaberImponible = $a['imponible'];
						$fDebeImponible = 0;
					}

					if ( $sNumeroCuenta == '12' ) {
						$fHaber = $fHaberTotal;
						$fDebe = $fDebeTotal;
					} else if ( $sNumeroCuenta == '40' ) {
						$fHaber = $fHaberImpuesto;
						$fDebe = $fDebeImpuesto;
					} else if ( $sNumeroCuenta == '70' ) {
						$fHaber = $fHaberImponible;
						$fDebe = $fDebeImponible;
					}					

					$nu_cuenta_contable = $sNumeroCuentaCompleto;
					if($nu_cuenta_contable == "12121"){
						if(trim($a['credito']) == "S" || trim($a['at']) == "7" || trim($a['at_referencia']) == "7"){
							$nu_cuenta_contable = "12123";
						}else{
							$nu_cuenta_contable = "12121";
						}
					}					

					// $importe_comparar = number_format($a['importe'],2,'.','');
					// $importe_igv_imponible_comparar = number_format(($a['igv'] + $a['imponible']),2,'.','');
					$data_calculo[] = array("nu_cuentacontable" => $nu_cuenta_contable,
											"serie" => $a['serie'],
											"numero" => $a['numero'],
											"serie_numero_referencia" => $a['serie_numero_referencia'],
											"tipo" => $a['tipo'],
											"importe" => $a['importe'],											
											// "importe_comparar" => $importe_comparar,
											// "importe_igv_imponible_comparar" => $importe_igv_imponible_comparar,
											// "verificar_igv" => ($importe_comparar != $importe_igv_imponible_comparar) ? "Esto es diferente" : "",
											"igv" => $a['igv'],
											"imponible" => $a['imponible'],											
											"at" => $a['at'],
											"at_referencia" => $a['at_referencia'],
											"credito" => $a['credito'],
											"haber" => str_pad(number_format(abs($fHaber), $decimales, '.', ''), 12, "0", STR_PAD_LEFT),
											"debe" => str_pad(number_format(abs($fDebe), $decimales, '.', ''), 12, "0", STR_PAD_LEFT));
					
					// $asientos .= str_pad(number_format(abs($fHaber), $decimales, '.', ''), 12, "0", STR_PAD_LEFT);//DEBE
					// $asientos .= str_pad(number_format(abs($fDebe), $decimales, '.', ''), 12, "0", STR_PAD_LEFT);//HABER

				}
			}
		}
	
		foreach($data_calculo as $key => $value){
			if($data_calculo[$key]['nu_cuentacontable'] == "12123"){
				$total_haber_12123 += $data_calculo[$key]['haber'];
				$total_debe_12123 += $data_calculo[$key]['debe'];				
				$data_calculo[$key]['total_haber_12123'] = $total_haber_12123;
				$data_calculo[$key]['total_debe_12123'] = $total_debe_12123;								
			}
			
			if($data_calculo[$key]['nu_cuentacontable'] == "12121"){
				$total_haber_12121 += $data_calculo[$key]['haber'];
				$total_debe_12121 += $data_calculo[$key]['debe'];
				$data_calculo[$key]['total_haber_12121'] = $total_haber_12121;
				$data_calculo[$key]['total_debe_12121'] = $total_debe_12121;			
			}
			
			if($data_calculo[$key]['nu_cuentacontable'] == "40111"){
				$total_haber_40111 += $data_calculo[$key]['haber'];
				$total_debe_40111 += $data_calculo[$key]['debe'];
				$data_calculo[$key]['total_haber_40111'] = $total_haber_40111;
				$data_calculo[$key]['total_debe_40111'] = $total_debe_40111;			
			}

			if($data_calculo[$key]['nu_cuentacontable'] == "70118"){
				$total_haber_70118 += $data_calculo[$key]['haber'];
				$total_debe_70118 += $data_calculo[$key]['debe'];
				$data_calculo[$key]['total_haber_70118'] = $total_haber_70118;
				$data_calculo[$key]['total_debe_70118'] = $total_debe_70118;			
			}

			$total_haber += $data_calculo[$key]['haber'];
			$total_debe += $data_calculo[$key]['debe'];			
			$data_calculo[$key]['total_haber'] = $total_haber;		
			$data_calculo[$key]['total_debe'] = $total_debe;

			$total_importe += $data_calculo[$key]['importe'];
			$total_igv += $data_calculo[$key]['igv'];
			$total_imponible += $data_calculo[$key]['imponible'];		
			$data_calculo[$key]['total_importe'] = $total_importe;		
			$data_calculo[$key]['total_igv'] = $total_igv;
			$data_calculo[$key]['total_imponible'] = $total_imponible;
		}

		foreach($data_calculo as $key => $value){
			$imponible_real = $value['importe'] - $value['igv'];
			$data_calculo[$key]['imponible_real'] = $imponible_real;
		}		
		
		// echo $almacen . "<br>";
		// echo $modulos . "<br>";
		// echo $decimales . "<br>";		
		// echo "<pre>";
		// print_r($cuentas);		
		// print_r($data); //print_r($data);
		// echo "</pre>";
		// die();		
		/***/

		for ($i = 0; $i < count($data); $i++) {
			$a			= $data[$i];
			$fe_emision = date("d/m", strtotime($a['emision']))."/".substr(date("Y", strtotime($a['emision'])),2);
			foreach($cuentas as $regcuenta){
				$sNumeroCuenta = substr($regcuenta['nu_cuentacontable'], 0, 2);
				if ( $sNumeroCuenta == '70' && abs($a['importe']) == 0 ) {
				}else{
					$asientos .= $regcuenta['nu_tipooperacion'];//TIPO DE OPERACION (01 = COMPRAS Y 02 = VENTAS)
					$asientos .= str_pad($id,5,"0",STR_PAD_LEFT);;//Número de voucher (correlativo de cada operación)
					$asientos .= $fe_emision;//FECHA EMISION
					//$asientos .= str_pad($regcuenta['nu_cuentacontable'],10," ",STR_PAD_RIGHT);//NUMERO DE LA CUENTA CONTABLE
						
					/*** Validacion Credito y Contado - 2020-10-16 ***/															
					if(trim($regcuenta['nu_cuentacontable']) == "12121"){	
						if (isset($a['caja'])) {

							if (trim($a['fpago']) == "2" || trim($a['fpago_referencia']) == "2") { //Se realizo el pago por POS, marcando la opcion TARJETA DE CREDITO
								if(trim($a['at']) == "7" || trim($a['at_referencia']) == "7"){ //Fue hecho marcando la opcion CREDITO
									$asientos .= str_pad("12123",10," ",STR_PAD_RIGHT);
									// echo "12123" . "<br>";				
								}else{
									$asientos .= str_pad($regcuenta['nu_cuentacontable'],10," ",STR_PAD_RIGHT);
									// echo $regcuenta['nu_cuentacontable'] . "<br>";				
								}
							}else{
								$asientos .= str_pad($regcuenta['nu_cuentacontable'],10," ",STR_PAD_RIGHT);
								// echo $regcuenta['nu_cuentacontable'] . "<br>";
							}

						}else{
							if(trim($a['credito']) == "S" || trim($a['at']) == "7" || trim($a['at_referencia']) == "7"){ //Fue hecho a credito por la web				
								$asientos .= str_pad("12123",10," ",STR_PAD_RIGHT);							
								// echo "12123" . "<br>";				
							}else{
								$asientos .= str_pad($regcuenta['nu_cuentacontable'],10," ",STR_PAD_RIGHT);
								// echo $regcuenta['nu_cuentacontable'] . "<br>";				
							}
						}											
					}else{
						$asientos .= str_pad($regcuenta['nu_cuentacontable'],10," ",STR_PAD_RIGHT);
						// echo $regcuenta['nu_cuentacontable'] . "<br>";
					}					
					/***/

					/*** Validacion imponible ***/					
					$imponible_real = $a['importe'] - $a['igv'];
					$a['imponible'] = $imponible_real;
					/***/

					$fHaberTotal = $a['importe'];
					$fDebeTotal = 0;

					$fHaberImpuesto = 0;
					$fDebeImpuesto = $a['igv'];

					$fHaberImponible = 0;
					$fDebeImponible = $a['imponible'];

					if ( $a['tipo'] == '07' || $a['tipo'] == '08' ) {
						$fHaberTotal = 0;
						$fDebeTotal = $a['importe'];

						$fHaberImpuesto = $a['igv'];
						$fDebeImpuesto = 0;

						$fHaberImponible = $a['imponible'];
						$fDebeImponible = 0;
					}

					if ( $sNumeroCuenta == '12' ) {
						$fHaber = $fHaberTotal;
						$fDebe = $fDebeTotal;
					} else if ( $sNumeroCuenta == '40' ) {
						$fHaber = $fHaberImpuesto;
						$fDebe = $fDebeImpuesto;
					} else if ( $sNumeroCuenta == '70' ) {
						$fHaber = $fHaberImponible;
						$fDebe = $fDebeImponible;
					}

					$asientos .= str_pad(number_format(abs($fHaber), $decimales, '.', ''), 12, "0", STR_PAD_LEFT);//DEBE
					$asientos .= str_pad(number_format(abs($fDebe), $decimales, '.', ''), 12, "0", STR_PAD_LEFT);//HABER

					$asientos .= "S";//MONEDA S = SOLES Y D = DOLARES
					$asientos .= "0".str_pad(number_format($a['tipocambio'],7, '.', ''),9,"0",STR_PAD_LEFT);//DEBE IMPORTE 12
					$asientos .= $a['tipo'];//TIPO DE DOCUMENTO

					$sNumeroComprobante = $a['numero'];
					if ( $a['tipo'] == "01" || $a['tipo'] == "07" ){//01 = Factura || 07 = Nota de Crédito
	                    $arrNumeroComprobante = explode("-", $a['numero']);
	                    $sNumeroComprobante = $arrNumeroComprobante[0];
					}
					$asientos .= str_pad($a['serie']."-".$sNumeroComprobante,40," ",STR_PAD_RIGHT);//SERIE Y NUMERO DEL DOCUMENTO

					$asientos .= $fe_emision;//FECHA EMISION
					$asientos .= $fe_emision;//FECHA VENCIMIENTO

					//if(abs($a['importe']) == 0 || $a['tipo_pdf'] == 'N' || $a['tipo_pdf'] == 'B')
					if ( abs($a['importe']) == 0 || ($a['tipo'] == "12" && $a['tipo_pdf'] == "B") || $a['tipo'] == "03" )
						$asientos .= str_pad($campo13_sMonth, 15, " ", STR_PAD_RIGHT);//CODIGO DEL CLIENTE - LONGITUD 15
					else
						$asientos .= str_pad($a['ruc'], 15, " ", STR_PAD_RIGHT);//CODIGO DEL CLIENTE - LONGITUD 15
 
					$asientos .= str_pad($vacio,10," ",STR_PAD_RIGHT);//CENTRO DE COSTO
					$asientos .= str_pad($vacio,4," ",STR_PAD_RIGHT);//FLUJO EFECTIVO
					$asientos .= str_pad($vacio,10," ",STR_PAD_RIGHT);//PRESUPUESTO
					$asientos .= str_pad($vacio,3," ",STR_PAD_RIGHT);//MEDIO PAGO
					$asientos .= str_pad($glosa,60," ",STR_PAD_RIGHT);//GLOSA

					if ( ( $sNumeroCuenta == '12' || $sNumeroCuenta == '70' ) && ($a['tipo'] != '07' || $a['tipo'] != '08') ) {
						$asientos .= str_pad($vacio,40," ",STR_PAD_RIGHT);//RNUMERO
						$asientos .= str_pad($vacio,2," ",STR_PAD_RIGHT);//RTDOC
						$asientos .= str_pad($vacio,8," ",STR_PAD_LEFT);//RFECHA
					} else {
						$asientos .= str_pad(trim($a['serie_numero_referencia']),40," ",STR_PAD_RIGHT);//RNUMERO
						$asientos .= str_pad(trim($a['nu_tipo_referencia']),2," ",STR_PAD_RIGHT);//RTDOC
						$asientos .= str_pad(trim($a['fe_emision_referencia']),8," ",STR_PAD_LEFT);//RFECHA
					}

					$asientos .= str_pad($vacio,40," ",STR_PAD_LEFT);//SNUMERO DETRACCION
					$asientos .= str_pad($vacio,8," ",STR_PAD_LEFT);//SFECHA DETRACCION

					if ( $sNumeroCuenta == '40' )
						$asientos .= $regcuenta['no_tipolibro'];//TIPO LIBRO
					else
						$asientos .= str_pad($vacio,1," ",STR_PAD_RIGHT);//FLUJO EFECTIVO

					if($regcuenta['nu_cuentacontable'] == '40111')
						$asientos .= str_pad(number_format(abs($a['imponible']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO
					else
						$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO

					if($regcuenta['nu_cuentacontable'] == '40111' and abs($a['igv']) > 0)
						$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO2
					else if($regcuenta['nu_cuentacontable'] == '40111' and abs($a['igv']) == 0)
						$asientos .= str_pad(number_format(abs($a['importe']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO2	
					else
						$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO2	

					$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO3
					$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO4

					if($regcuenta['nu_cuentacontable'] == '40111' and abs($a['igv']) > 0)
						$asientos .= str_pad(number_format(abs($a['igv']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//IGV
					else
						$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//IGV

					$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO5
					$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO6
					$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO7
					$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO8

					$balance = ($a['balance'] == NULL || trim($a['balance']) == "") ? $importe_vacio : $a['balance'];					
					$asientos .= str_pad(number_format($balance, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO9

					if(abs($a['importe']) == 0)
						$asientos .= str_pad($rucanulado,15," ",STR_PAD_RIGHT);//RUC DEL CLIENTE
					else
						$asientos .= str_pad($a['ruc'],15," ",STR_PAD_RIGHT);//RUC DEL CLIENTE

					$asientos .= $regcuenta['nu_tiposiscont'];//TABLA SISCONT

					if(abs($a['importe']) == 0)
						$asientos .= str_pad($clienteanulado,60," ",STR_PAD_RIGHT);//RAZON SOCIAL
					else
						$asientos .= str_pad(utf8_decode($a['cliente']),60," ",STR_PAD_RIGHT);//RAZON SOCIAL

					$asientos .= str_pad(utf8_decode($a['no_apellido_paterno']),20," ",STR_PAD_RIGHT);//APELLIDO PARTERNO
					$asientos .= str_pad(utf8_decode($a['no_apellido_materno']),20," ",STR_PAD_RIGHT);//APELLIDO MATERNO
					$asientos .= str_pad(utf8_decode($a['no_nombre']),20," ",STR_PAD_RIGHT);//NOMBRES

					if(abs($a['importe']) == 0)
						$asientos .= "0";//TIPO DE DOCUMENTO DEL CLIENTE
					else
						$asientos .= $a['tipodi'];//TIPO DE DOCUMENTO DEL CLIENTE

					$asientos .= str_pad($vacio,1," ",STR_PAD_LEFT);//RNUMDES
					$asientos .= str_pad($vacio,5," ",STR_PAD_LEFT);//RCODTASA
					$asientos .= str_pad($vacio,1," ",STR_PAD_LEFT);//RINDRET
					$asientos .= str_pad($vacio,12," ",STR_PAD_LEFT);//RMONTO
					$asientos .= str_pad($vacio,12," ",STR_PAD_LEFT);//RIGV
					$asientos .= str_pad($vacio,1," ",STR_PAD_LEFT);//TBIEN

					$asientos .= "\r\n";
				}
			}
			$id++;
		}
	} else {//COBRANZAS
		$glosa			= "Cobranza del dia";
		$nombre_archivo = "cbsflor.txt";
		$idcobranza		= 0;
		$dia 			= null;
		$cuentanew 		= null;

		/*** Validacion Distinguir Tarjetas - 2020-10-20 ***/		
		if(trim($nu_tarjeta_credito) == "CREDITO"){
						
			foreach($data as $key=>$value){
				if(trim($data[$key]['cuentacontable']) == "162911"){					
					if(trim($data[$key]['at']) == "1"){ //VISA
						$data[$key]['cuentacontable'] = "162911";
					}else if(trim($data[$key]['at']) == "3"){ //MASTERCARD
						$data[$key]['cuentacontable'] = "162912";
					}else if(trim($data[$key]['at']) == "2"){ //AMERICAN EXPRESS
						$data[$key]['cuentacontable'] = "162913";
					}else if(trim($data[$key]['at']) == "4"){ //DINNERS
						$data[$key]['cuentacontable'] = "162914";
					}else{
						$data[$key]['cuentacontable'] = "162911";
					}
				}
				if(trim($data[$key]['at'] == "7")){
					unset($data[$key]);
				}
			}						

		}

		for ($i = 0; $i < count($data); $i++) {
			$a = $data[$i];
			if(abs($a['importe']) == 0){//En cobranza no se debe de mostrar los documentos con importes en 0
			}else{	
				if ( $a["tipo"] != "07" ) {
					if($a['cuentacontable'] == '12121')
						$data[$i]['debe'] = str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//DEBE IMPORTE
					else
						$data[$i]['debe'] = str_pad(number_format(abs($a['importe']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//DEBE IMPORTE

					if($a['cuentacontable'] == '12121')
						$data[$i]['haber'] = str_pad(number_format(abs($a['importe']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//HABER IMPORTE
					else
						$data[$i]['haber'] = str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//HABER IMPORTE
				} else {
					if($a['cuentacontable'] == '12121')
						$data[$i]['debe'] = str_pad(number_format(abs($a['importe']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//DEBE IMPORTE
					else
						$data[$i]['debe'] = str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//DEBE IMPORTE

					if($a['cuentacontable'] == '12121')
						$data[$i]['haber'] = str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//HABER IMPORTE
					else
						$data[$i]['haber'] = str_pad(number_format(abs($a['importe']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//HABER IMPORTE
				}
			}
		}

		$dataFechas = array();
		foreach($data as $key=>$value){
			$emision = substr($data[$key]['emision'],0,10);
			$dataFechas[] = $emision;
		}
		$dataFechas = array_unique($dataFechas);
		
		$dataFechas2 = array();
		foreach($dataFechas as $key=>$value){				
			$dataFechas2[] = array("emision" => $dataFechas[$key],
								   "caja_real" => "");
		}
		$dataFechas = $dataFechas2;
		
		foreach($data as $key=>$value){					
			if(trim($data[$key]['serie']) == ""){
				$total_debe = 0;
				$total_haber = 0;
				$data[$key]['total_debe'] = $total_debe;
				$data[$key]['total_haber'] = $total_haber;			
				$data[($key-1)]['ultimo'] = "ultimo";
				continue;
			}					

			$total_debe += $data[$key]['debe'];
			$total_haber += $data[$key]['haber'];
			$data[$key]['total_debe'] = $total_debe;
			$data[$key]['total_haber'] = $total_haber;								
		}

		foreach($data as $key=>$value){					
			if($key == -1){
				unset($data[$key]);
			}
		}
		foreach($data as $key=>$value){					
			if($data[$key] === end($data)){
				$data[$key]['ultimo'] = "ultimo";
			}
		}
		foreach($data as $key=>$value){									
			if(isset($data[$key]['ultimo'])){
				$caja_real = $data[$key]['total_haber'] - $data[$key]['total_debe'];					
				$data[$key]['caja_real'] = $caja_real;
			}				
		}

		foreach($data as $key=>$value){
			foreach($dataFechas as $key2=>$value2){					
				if(isset($data[$key]['ultimo'])){
					if(trim(substr($data[$key]['emision'],0,10)) == trim($dataFechas[$key2]['emision'])){
						$dataFechas[$key2]['caja_real'] = $data[$key]['caja_real'];
					}
				}					
			}
		}
		foreach($data as $key=>$value){
			foreach($dataFechas as $key2=>$value2){
				if(trim($data[$key]['serie']) == ""){
					if(trim(substr($data[$key]['emision'],0,10)) == trim($dataFechas[$key2]['emision'])){
						$data[$key]['caja'] = $dataFechas[$key2]['caja_real'];
						$data[$key]['importe'] = $dataFechas[$key2]['caja_real'];
					}
				}
			}
		}
		
		// echo "<pre>Cobranza: <br>";
		// print_r($dataFechas);
		// print_r($data);
		// echo "</pre>";
		// die();
		/***/

		for ($i = 0; $i < count($data); $i++) {
			$a			= $data[$i];
			$fe_emision = date("d/m", strtotime($a['emision']))."/".substr(date("Y", strtotime($a['emision'])),2);

			if(abs($a['importe']) == 0){//En cobranza no se debe de mostrar los documentos con importes en 0
			}else{
				$asientos .= '04';//TIPO DE OPERACION (01 = COMPRAS Y 02 = VENTAS)
				$asientos .= str_pad($a['nucorrelativo'],5,"0",STR_PAD_LEFT);//Número de voucher (correlativo de cada operación)
				$asientos .= $fe_emision;//FECHA EMISION
				$asientos .= str_pad($a['cuentacontable'],10," ",STR_PAD_RIGHT);//NUMERO DE LA CUENTA CONTABLE				

				if ( $a["tipo"] != "07" ) {
					if($a['cuentacontable'] == '12121')
						$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//DEBE IMPORTE
					else
						$asientos .= str_pad(number_format(abs($a['importe']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//DEBE IMPORTE

					if($a['cuentacontable'] == '12121')
						$asientos .= str_pad(number_format(abs($a['importe']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//HABER IMPORTE
					else
						$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//HABER IMPORTE
				} else {
					if($a['cuentacontable'] == '12121')
						$asientos .= str_pad(number_format(abs($a['importe']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//DEBE IMPORTE
					else
						$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//DEBE IMPORTE

					if($a['cuentacontable'] == '12121')
						$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//HABER IMPORTE
					else
						$asientos .= str_pad(number_format(abs($a['importe']), $decimales, '.', ''),12,"0",STR_PAD_LEFT);//HABER IMPORTE
				}

				$asientos .= "S";//MONEDA S = SOLES Y D = DOLARES
				$asientos .= "0".str_pad(number_format($a['tipocambio'],7, '.', ''),9,"0",STR_PAD_LEFT);//TIPO DE CAMBIO
				$asientos .= $a['tipo'];//TIPO DE DOCUMENTO

				if($a['cuentacontable'] == '12121') {
					$sNumeroComprobante = $a['numero'];
					if ( $a['tipo'] == "01" || $a['tipo'] == "07" ) {
	                    $arrNumeroComprobante = explode("-", $a['numero']);
	                    $sNumeroComprobante = $arrNumeroComprobante[0];
					}
					$asientos .= str_pad($a['serie']."-".$sNumeroComprobante,40," ",STR_PAD_RIGHT);//SERIE Y NUMERO DEL DOCUMENTO
					//$asientos .= str_pad($a['serie']."-".$a['numero'],40," ",STR_PAD_RIGHT);//SERIE Y NUMERO DEL DOCUMENTO
				} else
					$asientos .= str_pad($a['serie'],40," ",STR_PAD_RIGHT);//SERIE Y NUMERO DEL DOCUMENTO

				$asientos .= $fe_emision;//FECHA EMISION
				$asientos .= $fe_emision;//FECHA VENCIMIENTO

				//if(abs($a['importe']) == 0 || $a['tipo_pdf'] == 'N' || $a['tipo_pdf'] == 'B' || $a['tipo_pdf'] == '12')
				if ( abs($a['importe']) == 0 || ($a['tipo'] == "12" && $a['tipo_pdf'] == "B") || $a['tipo'] == "03" )
					$asientos .= str_pad($campo13_sMonth, 15, " ", STR_PAD_RIGHT);//CODIGO DEL CLIENTE - LONGITUD 15
				else
					$asientos .= str_pad($a['ruc'], 15, " ", STR_PAD_RIGHT);//CODIGO DEL CLIENTE - LONGITUD 15
				//$asientos .= str_pad($campo13_sMonth, 15, " ", STR_PAD_RIGHT);//CODIGO DEL CLIENTE - LONGITUD 15
				$asientos .= str_pad($vacio,10," ",STR_PAD_RIGHT);//CENTRO DE COSTO

				if($a['cuentacontable'] == '12121')
					$asientos .= str_pad($vacio,4," ",STR_PAD_RIGHT);//FLUJO EFECTIVO
				else
					$asientos .= str_pad($a['no_flujoefectivo'],4," ",STR_PAD_RIGHT);//FLUJO EFECTIVO

				$asientos .= str_pad($vacio,10," ",STR_PAD_RIGHT);//PRESUPUESTO

				if($a['cuentacontable'] == '1011')
					$asientos .= str_pad($a['nu_mediopago'],3," ",STR_PAD_RIGHT);//MEDIO PAGO
				else
					$asientos .= str_pad($vacio,3," ",STR_PAD_RIGHT);//MEDIO PAGO

				$glosa = "Cobranza del dia";
				// if (isset($a['txt_glosa']))
				//  $glosa=$a['txt_glosa'];
				 
				$asientos .= str_pad($glosa,60," ",STR_PAD_RIGHT);//GLOSA
				$asientos .= str_pad($vacio,40," ",STR_PAD_LEFT);//RNUMERO
				$asientos .= str_pad($vacio,2," ",STR_PAD_LEFT);//RTDOC
				$asientos .= str_pad($vacio,8," ",STR_PAD_LEFT);//RFECHA
				$asientos .= str_pad($vacio,40," ",STR_PAD_LEFT);//SNUMERO
				$asientos .= str_pad($vacio,8," ",STR_PAD_LEFT);//SFECHA
				$asientos .= str_pad($vacio,1," ",STR_PAD_RIGHT);//tIPO LIBRO
				$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO

				$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO2

				$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO3
				$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO4
				$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//IGV
				$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO5

				$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO6
				$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO7
				$asientos .= str_pad(number_format($importe_vacio, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO8

				$balance = ($a['balance'] == NULL || trim($a['balance']) == "") ? $importe_vacio : $a['balance'];					
				$asientos .= str_pad(number_format($balance, $decimales, '.', ''),12,"0",STR_PAD_LEFT);//NETO9

				$asientos .= str_pad($a['ruc'],15," ",STR_PAD_RIGHT);//RUC DEL CLIENTE
				$asientos .= '2';//TABLA SISCONT

				if($a['cuentacontable'] == '1011'){
					$asientos .= str_pad($vacio,60," ",STR_PAD_LEFT);//APE1
					$asientos .= str_pad($vacio,20," ",STR_PAD_LEFT);//APE1
					$asientos .= str_pad($vacio,20," ",STR_PAD_LEFT);//APE2
					$asientos .= str_pad($vacio,20," ",STR_PAD_LEFT);//NOMBRE
				}else{
					$asientos .= str_pad(utf8_decode($a['cliente']),60," ",STR_PAD_RIGHT);//RAZON SOCIAL
					$asientos .= str_pad(utf8_decode($a['no_apellido_paterno']),20," ",STR_PAD_RIGHT);//APELLIDO PARTERNO
					$asientos .= str_pad(utf8_decode($a['no_apellido_materno']),20," ",STR_PAD_RIGHT);//APELLIDO MATERNO
					$asientos .= str_pad(utf8_decode($a['no_nombre']),20," ",STR_PAD_RIGHT);//NOMBRES
				}

				$asientos .= $a['tipodi'];//TIPO DE DOCUMENTO DEL CLIENTE
				$asientos .= str_pad($vacio,1," ",STR_PAD_LEFT);//RNUMDES
				$asientos .= str_pad($vacio,5," ",STR_PAD_LEFT);//RCODTASA
				$asientos .= str_pad($vacio,1," ",STR_PAD_LEFT);//RINDRET
				$asientos .= str_pad($vacio,12," ",STR_PAD_LEFT);//RMONTO
				$asientos .= str_pad($vacio,12," ",STR_PAD_LEFT);//RIGV
				$asientos .= str_pad($vacio,1," ",STR_PAD_LEFT);//TBIEN
				$asientos .= "\r\n";
			}
		}
	}
	// die();

	header("Content-type: text/plain");
	header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

	$data = trim($asientos);

	die($asientos);
}