<?php
date_default_timezone_set('America/Lima');

ini_set("memory_limit", '3G');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('t_interface_siscont.php');
include('m_interface_siscont.php');

$objModel = new Siscont_Model();

if (isset($_REQUEST['accion']))
	$accion = $_REQUEST['accion'];

$_SESSION['data_asientos'] 	= null;
$_SESSION['data_cuentas'] 	= null;
$_SESSION['almacen'] 		= null;
$_SESSION['modulos'] 		= null;

try {

	/* Parametros de Entrada */
	$modulos 		= trim($_POST['modulos']);
	$sucursal 		= trim($_POST['sucursal']);
	$year 			= trim($_POST['year']);
	$month 			= trim($_POST['month']);
	$decimales		= trim($_POST['decimales']);
	$nu_tipo_venta	= trim($_POST['nu_tipo_venta']); /* 1: Documentos Electrónicos, 2: Documentos Electrónicos y Tickets y 3: Tickets */
	$nu_nota_despacho = trim($_POST['nu_nota_despacho']); /* 1: Si y 2: No */

	$tipo			= "SU";
	$postrans		= "pos_trans" . $year . $month;
	$fecha_serie	= $year . "-" . $month;

	//comentar solo cuando sea localmente
	$cuentas		= $objModel->CuentasContables($modulos);
	$data			= NULL;

	if ($accion == "AsientosExcel") {
		$data		= $objModel->AsientoContablesSiscontExcel($sucursal, $postrans, $fecha_serie, $decimales, $nu_tipo_venta, $nu_nota_despacho);
		
		$arrResponse = array(
            'sStatus' => 'warning',
            'sMessage' => 'No se encontraron registros',
        );

		if(!empty($data)){
			$_SESSION['data_asientos'] 				= $data;
			$_SESSION['data_cuentas']				= $cuentas;
			$_SESSION['almacen'] 					= $sucursal;
			$_SESSION['modulos'] 					= $modulos;
			$_SESSION['decimales'] 					= $decimales;

			$arrResponse = array(
	            'sStatus' => 'success',
	        );
		}

        echo json_encode( $arrResponse );
		exit();
	}else if ($accion=="asientos") {
		if ( $modulos==1 ) { //1=Ventas
			$arrParams = array(
				'sCodeWarehouse' => $sucursal,
				'dYear' => $year,
				'dMonth' => $month,
				'iNumberDecimal' => $decimales
			);
			$arrManualInvoiceSale = $objModel->getManualInvoiceSale( $arrParams );
			
			if ( $arrManualInvoiceSale["sStatus"] == "danger" ) {
				echo json_encode( $arrManualInvoiceSale );
        		exit();
			}

			$arrPlayaInvoiceSale = $objModel->AsientosVentas($sucursal, $postrans, $fecha_serie, $tipo, $decimales, $nu_tipo_venta, $nu_nota_despacho);

			if ( isset($arrPlayaInvoiceSale["ticket"]) && count($arrPlayaInvoiceSale["ticket_tmp"]) < 0 ){
		        $arrResponse = array(
		            'sStatus' => 'danger',
		            'sMessage' => 'problemas al obtener ventas de playa (ventas)',
		        );

		        echo json_encode( $arrResponse );
        		exit();
			}

	        $sIDTipoDocumento = '';
	        $sIDSerieDocumento = '';
	        
	        $iDetener = 0;
	        
	        $fSubtotal = 0.00;
	        $fImpuesto = 0.00;
	        $fTotal = 0.00;

	        $arrDataManualInvoiceSale = array();
        	$rows_bv = array();

        	foreach($arrManualInvoiceSale["arrData"] as $row) {
        		$row["imponible"] = (float)$row["imponible"];
        		$row["igv"] = (float)$row["igv"];
        		$row["total"] = (float)$row["total"];

        		//Solo entra si no es boleta ó si es boleta pero es mayor a 700 soles
        		if ( $iDetener == 0 && ($row["tipo"] != "03" || ($row["tipo"] == "03" && $row["total"] >= 700.00)) ){
        			$rows_["emision"] = $row["emision"];
        			$rows_["vencimiento"] = $row["vencimiento"];
        			$rows_["tipo"] = $row["tipo"];
        			$rows_["serie"] = $row["serie"];
        			$rows_["numero"] = $row["numero"]."-".$row["numero"];
        			$rows_["tipodi"] = $row["tipodi"];
        			$rows_["ruc"] = $row["ruc"];
        			$rows_["cliente"] = $row["cliente"];
        			$rows_["no_apellido_paterno"] = $row["no_apellido_paterno"];
        			$rows_["no_apellido_materno"] = $row["no_apellido_materno"];
        			$rows_["no_nombre"] = $row["no_nombre"];
        			$rows_["tipocambio"] = $row["tipocambio"];
        			$rows_["serie_numero_referencia"] = $row["serie_numero_referencia"];
        			$rows_["fe_emision_referencia"] = $row["fe_emision_referencia"];
        			$rows_["nu_tipo_referencia"] = $row["nu_tipo_referencia"];
        			$rows_["imponible"] = $row["imponible"];
        			$rows_["igv"] = $row["igv"];
        			$rows_["importe"] = $row["total"];
	                if ( count($rows_) > 0 )
	                    $arrDataManualInvoiceSale[] = $rows_;
	                $sIDTipoDocumento = '';
        		}

	            if ( $sIDTipoDocumento != $row["tipo"] && $row["total"] < 700.00) {//BV INICIAL
	                if ( $row["tipo"] == "03" ){
	                	$rows_bv["emision"] = $row["emision"];
	                	$rows_bv["vencimiento"] = $row["vencimiento"];
	        			$rows_bv["tipo"] = $row["tipo"];
	        			$rows_bv["serie"] = $row["serie"];
	        			$rows_bv["numero_inicial"] = $row["numero"];
	        			$rows_bv["tipodi"] = $row["tipodi"];
	        			$rows_bv["ruc"] = $row["ruc"];
	        			$rows_bv["cliente"] = $row["cliente"];
	        			$rows_bv["no_apellido_paterno"] = $row["no_apellido_paterno"];
	        			$rows_bv["no_apellido_materno"] = $row["no_apellido_materno"];
	        			$rows_bv["no_nombre"] = $row["no_nombre"];
	        			$rows_bv["tipocambio"] = $row["tipocambio"];
	        			$rows_bv["serie_numero_referencia"] = $row["serie_numero_referencia"];
	        			$rows_bv["fe_emision_referencia"] = $row["fe_emision_referencia"];
	        			$rows_bv["nu_tipo_referencia"] = $row["nu_tipo_referencia"];
	                }
	                $sIDTipoDocumento = $row["tipo"];
	                $sIDSerieDocumento = $row["serie"];
	            }

	            if ( $row["tipo"] == "03" && $sIDSerieDocumento == $row["serie"] && $row["total"] < 700.00 ) {//BV FINAL
			        $fSubtotal += $row["imponible"];
			        $fImpuesto += $row["igv"];
			        $fTotal += $row["total"];

	                $rows_bv["numero"] = $rows_bv["numero_inicial"]."-".$row["numero"];
        			$rows_bv["imponible"] = $fSubtotal;
        			$rows_bv["igv"] = $fImpuesto;
        			$rows_bv["importe"] = $fTotal;
	            } else {
			        $fSubtotal = 0.00;
			        $fImpuesto = 0.00;
			        $fTotal = 0.00;

	                $sIDSerieDocumento = $row["serie"];
	                $iDetener = 1;
	            }
	            
	            if ( $iDetener == 1 ){
	                if ( count($rows_bv) > 0 )
	                    $arrDataManualInvoiceSale[] = $rows_bv;
	                $rows_bv = array();//Si en esto, la union de boletas inicial y final, no funcionará
	            }
	            
	            $iDetener = 0;
        	}

        	$arrInvoiceSale = $arrPlayaInvoiceSale["ticket"];
        	if ( count($arrDataManualInvoiceSale) > 0 ) {
				$arrInvoiceSale = array_merge( $arrDataManualInvoiceSale, $arrPlayaInvoiceSale["ticket"] );
        	}

			$_SESSION['data_asientos'] 	= $arrInvoiceSale;
			$_SESSION['data_cuentas'] 	= $cuentas;
			$_SESSION['almacen'] 		= $sucursal;
			$_SESSION['modulos'] 		= $modulos;
			$_SESSION['decimales'] 		= $decimales;
			// Nuevo parámetro - 24/07/2018
			$_SESSION['sMonth'] 		= $month;

			echo json_encode(
			array(
                'sStatus' => 'success',
                'arrAllData' => $arrInvoiceSale
            ));
		} else {//2=Cobranza
			$arrParams = array(
				'sCodeWarehouse' => $sucursal,
				'dYear' => $year,
				'dMonth' => $month,
				'iNumberDecimal' => $decimales
			);
			$arrManualInvoiceSaleReceivable = $objModel->getManualInvoiceSaleReceivable( $arrParams );

			if ( $arrManualInvoiceSaleReceivable["sStatus"] == "danger" ) {
				echo json_encode( $arrManualInvoiceSaleReceivable );
        		exit();
			}

			$arrPlayaInvoiceSale = $objModel->AsientosCobranzas($sucursal, $postrans, $fecha_serie, $tipo, $decimales, $nu_tipo_venta, $nu_nota_despacho, $year, $month);
			if ( isset($arrPlayaInvoiceSale["ticket"]) && count($arrPlayaInvoiceSale["ticket_tmp"]) < 0 ){
		        $arrResponse = array(
		            'sStatus' => 'danger',
		            'sMessage' => 'problemas al obtener ventas de playa (cobranza)',
		        );

		        echo json_encode( $arrResponse );
        		exit();
			}

			$arrPlayaInvoiceSaleExtornos = $objModel->AsientosCobranzasExtornos($sucursal, $postrans, $fecha_serie, $tipo, $decimales, $nu_tipo_venta, $nu_nota_despacho, $year, $month);
			if ( $arrPlayaInvoiceSaleExtornos["sStatus"] == "danger" ) {
				echo json_encode( $arrPlayaInvoiceSaleExtornos );
        		exit();
			}

	        $sIDTipoDocumento = '';
	        $sIDSerieDocumento = '';
	        
	        $iDetener = 0;

	        $fTotal = 0.00;

	        $arrDataManualInvoiceSale = array();
        	$rows_bv = array();

        	foreach($arrManualInvoiceSaleReceivable["arrData"] as $row) {
        		if ( $row["cuentacontable"] == "12121" ) {
	        		$row["total"] = (float)$row["total"];

	        		//Solo entra si no es boleta ó si es boleta y debe ser mayor a 700 soles
	        		if ( $iDetener == 0 && ($row["tipo"] != "03" || ($row["tipo"] == "03" && $row["total"] >= 700.00)) ){
	        			$rows_["nucorrelativo"] = $row["nucorrelativo"];
	        			$rows_["cuentacontable"] = $row["cuentacontable"];
	        			$rows_["no_flujoefectivo"] = $row["no_flujoefectivo"];
	        			$rows_["nu_mediopago"] = $row["nu_mediopago"];
	        			$rows_["emision"] = $row["emision"];
	        			$rows_["vencimiento"] = $row["vencimiento"];
	        			$rows_["tipo"] = $row["tipo"];
	        			$rows_["serie"] = $row["serie"];
	        			$rows_["numero"] = $row["numero"]."-".$row["numero"];
	        			$rows_["tipodi"] = $row["tipodi"];
	        			$rows_["ruc"] = $row["ruc"];
	        			$rows_["cliente"] = $row["cliente"];
	        			$rows_["no_apellido_paterno"] = $row["no_apellido_paterno"];
	        			$rows_["no_apellido_materno"] = $row["no_apellido_materno"];
	        			$rows_["no_nombre"] = $row["no_nombre"];
	        			$rows_["tipocambio"] = $row["tipocambio"];
						$rows_["importe"] = $row["total"];
						$rows_["txt_glosa"] = $row["txt_glosa"];
		                if ( count($rows_) > 0 )
		                    $arrDataManualInvoiceSale[] = $rows_;
		                $sIDTipoDocumento = '';
	        		}

		            if ( $sIDTipoDocumento != $row["tipo"] && $row["total"] < 700.00) {//BV INICIAL
		                if ( $row["tipo"] == "03" ){
		                	$rows_bv["nucorrelativo"] = $row["nucorrelativo"];
		                	$rows_bv["cuentacontable"] = $row["cuentacontable"];
		                	$rows_bv["no_flujoefectivo"] = $row["no_flujoefectivo"];
		                	$rows_bv["nu_mediopago"] = $row["nu_mediopago"];
		                	$rows_bv["emision"] = $row["emision"];
		                	$rows_bv["vencimiento"] = $row["vencimiento"];
		        			$rows_bv["tipo"] = $row["tipo"];
		        			$rows_bv["serie"] = $row["serie"];
		        			$rows_bv["numero_inicial"] = $row["numero"];
		        			$rows_bv["tipodi"] = $row["tipodi"];
		        			$rows_bv["ruc"] = $row["ruc"];
		        			$rows_bv["cliente"] = $row["cliente"];
		        			$rows_bv["no_apellido_paterno"] = $row["no_apellido_paterno"];
		        			$rows_bv["no_apellido_materno"] = $row["no_apellido_materno"];
		        			$rows_bv["no_nombre"] = $row["no_nombre"];
							$rows_bv["tipocambio"] = $row["tipocambio"];
							$rows_bv["txt_glosa"] = $row["txt_glosa"];
		                }
		                $sIDTipoDocumento = $row["tipo"];
		                $sIDSerieDocumento = $row["serie"];
		            }

		            if ( $row["tipo"] == "03" && $sIDSerieDocumento == $row["serie"] && $row["total"] < 700.00 ) {//BV FINAL
				        $fTotal += $row["total"];

		                $rows_bv["numero"] = $rows_bv["numero_inicial"]."-".$row["numero"];
	        			$rows_bv["importe"] = $fTotal;
		            } else {
				        $fSubtotal = 0.00;
				        $fImpuesto = 0.00;
				        $fTotal = 0.00;

		                $sIDSerieDocumento = $row["serie"];
		                $iDetener = 1;
		            }
		            
		            if ( $iDetener == 1 ){
		                if ( count($rows_bv) > 0 )
		                    $arrDataManualInvoiceSale[] = $rows_bv;
		                $rows_bv = array();//Si en esto, la union de boletas inicial y final, no funcionará
		            }
		            
		            $iDetener = 0;
		        }
        	}// Fin de documentos cuenta 12, para ventas oficina electrónicas y fisicas.

	        $arrDataManualInvoiceSaleCredit = array();
        	foreach($arrManualInvoiceSaleReceivable["arrData"] as $row) {
        		if ( $row["cuentacontable"] == "162911" ) {
	        		$row["total"] = (float)$row["total"];
	        		
					$rows_["nucorrelativo"] = $row["nucorrelativo"];
        			$rows_["cuentacontable"] = $row["cuentacontable"];
        			$rows_["no_flujoefectivo"] = $row["no_flujoefectivo"];
        			$rows_["nu_mediopago"] = $row["nu_mediopago"];
        			$rows_["emision"] = $row["emision"];
        			$rows_["vencimiento"] = $row["vencimiento"];
        			$rows_["tipo"] = $row["tipo"];
        			$rows_["serie"] = $row["serie"];
        			$rows_["numero"] = $row["numero"]."-".$row["numero"];
        			$rows_["tipodi"] = $row["tipodi"];
        			$rows_["ruc"] = $row["ruc"];
        			$rows_["cliente"] = $row["cliente"];
        			$rows_["no_apellido_paterno"] = $row["no_apellido_paterno"];
        			$rows_["no_apellido_materno"] = $row["no_apellido_materno"];
	       			$rows_["no_nombre"] = $row["no_nombre"];
        			$rows_["tipocambio"] = $row["tipocambio"];
        			$rows_["importe"] = $row["total"];
					$rows_["txt_glosa"] = $row["txt_glosa"];
	                $arrDataManualInvoiceSaleCredit[] = $rows_;
        		}
        	}// Fin de documentos cuenta 16, para ventas oficina electrónicas y fisicas.

	        $arrDataManualInvoiceSaleAccountBank = array();
        	foreach($arrManualInvoiceSaleReceivable["arrData"] as $row) {
        		if ( $row["cuentacontable"] != "162911" && $row["cuentacontable"] != "12121") {
	        		$row["total"] = (float)$row["total"];
	        		
					$rows_["nucorrelativo"] = $row["nucorrelativo"];
        			$rows_["cuentacontable"] = $row["cuentacontable"];
        			$rows_["no_flujoefectivo"] = $row["no_flujoefectivo"];
        			$rows_["nu_mediopago"] = $row["nu_mediopago"];
        			$rows_["emision"] = $row["emision"];
        			$rows_["vencimiento"] = $row["vencimiento"];
        			$rows_["tipo"] = $row["tipo"];
        			$rows_["serie"] = $row["serie"];
        			$rows_["numero"] = $row["numero"]."-".$row["numero"];
        			$rows_["tipodi"] = $row["tipodi"];
        			$rows_["ruc"] = trim($row["ruc"]);
        			$rows_["cliente"] = $row["cliente"];
        			$rows_["no_apellido_paterno"] = $row["no_apellido_paterno"];
        			$rows_["no_apellido_materno"] = $row["no_apellido_materno"];
	       			$rows_["no_nombre"] = $row["no_nombre"];
        			$rows_["tipocambio"] = $row["tipocambio"];
        			$rows_["importe"] = $row["total"];
					$rows_["txt_glosa"] = trim($row["txt_glosa"]);
	                $arrDataManualInvoiceSaleAccountBank[] = $rows_;
        		}
        	}// Fin de documentos cuenta 10 CUENTAS BANCARIAS, para COBROS(CAJA INGRESOS) oficina electrónicas y fisicas.

        	$arrInvoiceSale = $arrPlayaInvoiceSale["ticket"];
        	if ( count($arrDataManualInvoiceSale) > 0 ) {
				$arrInvoiceSale = array_merge( $arrPlayaInvoiceSale["ticket"], $arrPlayaInvoiceSaleExtornos["arrData"], $arrDataManualInvoiceSale, $arrDataManualInvoiceSaleCredit, $arrDataManualInvoiceSaleAccountBank);
        	}

	        $dOrderByEntry = array();
	        $sOrderByAccount = array();
	        foreach ($arrInvoiceSale as $key => $row) {
				$dOrderByEntry[$key] = $row["emision"];
				$sOrderByAccount[$key] = $row["cuentacontable"];
	        }
			array_multisort($dOrderByEntry, SORT_ASC, $sOrderByAccount, SORT_ASC, $arrInvoiceSale);
        	
			$_SESSION['data_asientos'] 	= $arrInvoiceSale;
			$_SESSION['data_cuentas'] 	= $cuentas;
			$_SESSION['almacen'] 		= $sucursal;
			$_SESSION['modulos'] 		= $modulos;
			$_SESSION['decimales'] 		= $decimales;
			$_SESSION['sMonth'] 		= $month;

			echo json_encode(
			array(
                'sStatus' => 'success',
                //'arrAllData' => $arrInvoiceSale
            ));
		}
	}
} catch (Exception $r) {
	echo $r->getMessage();
}

