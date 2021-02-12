<?php

include("/sistemaweb/valida_sess.php");
include("/sistemaweb/functions.php");
include_once('../../include/reportes2.inc.php');

//Parametros de GET
$iAlmacen   = $_GET['iAlmacen'];
$sCajaTrans = $_GET['sCajaTrans'];
$iYear   	= $_GET['iYear'];
$iMonth   	= $_GET['iMonth'];
$iDay   	= $_GET['iDay'];
$iTurno   	= $_GET['iTurno'];

// error_log("Etapa 1");
// error_log( json_encode( array($iAlmacen, $sCajaTrans, $iYear, $iMonth, $iDay, $iTurno) ) );

//Validación de los parametros de entrada que recibe la URL
if (isset($iAlmacen) && isset($sCajaTrans) && isset($iYear) && isset($iMonth) && isset($iTurno)) {
	/* SQL obtener empresa con almacén */
	$sqlca->query("
	SELECT
		EMPRE.ruc,
		EMPRE.razsocial,
		EMPRE.ch_direccion
	FROM
   	inv_ta_almacenes AS ALMA
   	JOIN int_ta_sucursales AS EMPRE
			ON (EMPRE.ch_sucursal = ALMA.ch_sucursal)
	WHERE
		EMPRE.ebikey IS NOT NULL AND EMPRE.ebikey != ''
		AND ALMA.ch_clase_almacen = '1'
		AND ALMA.ch_sucursal = '" . $iAlmacen . "'
	");
   $row = $sqlca->fetchRow();

   if(trim($row['ruc']) != '') {
		//Variables de la empresa
		$iEmpresaRuc           = trim($row['ruc']);
		$sEmpresaRazsocial     = trim($row['razsocial']);
		$arrEmpresaDireccion   = explode('[|]', trim($row['ch_direccion']));
		// $sEmpresaRazsocial     = "EMPRESA COMUNAL DE SERVICIOS MULTIPLES RANCAS";
		$sEmpresaDireccion     = $arrEmpresaDireccion[1] . ' ' . $arrEmpresaDireccion[2];
		$sEmpresaDistrito      = $arrEmpresaDireccion[3];
		$sEmpresaProvincia     = $arrEmpresaDireccion[4];
		$sEmpresaDepartamento  = $arrEmpresaDireccion[5];
   } else {
		/* SQL obtener empresa sin almacén */
		$sqlca->query("
		SELECT DISTINCT
			EMPRE.ruc,
			EMPRE.razsocial,
			EMPRE.ch_direccion
		FROM
			inv_ta_almacenes AS ALMA
			JOIN int_ta_sucursales AS EMPRE
				ON (EMPRE.ch_sucursal = ALMA.ch_sucursal)
		WHERE
			EMPRE.ebikey IS NOT NULL AND EMPRE.ebikey != ''
			AND ALMA.ch_clase_almacen = '1'
		");

		$row = $sqlca->fetchRow();
		// Variables de la empresa
		$iEmpresaRuc           = trim($row['ruc']);
		$sEmpresaRazsocial     = trim($row['razsocial']);
		$arrEmpresaDireccion   = explode('[|]', trim($row['ch_direccion']));
		$sEmpresaDireccion     = $arrEmpresaDireccion[1] . ' ' . $arrEmpresaDireccion[2];
		$sEmpresaDistrito      = $arrEmpresaDireccion[3];
		$sEmpresaProvincia     = $arrEmpresaDireccion[4];
		$sEmpresaDepartamento  = $arrEmpresaDireccion[5];
	}

	// error_log("Etapa 2");
	// error_log( json_encode( array($iEmpresaRuc, $sEmpresaRazsocial, $arrEmpresaDireccion, $sEmpresaDireccion, $sEmpresaDistrito, $sEmpresaProvincia, $sEmpresaDepartamento) ) );

	// SQL para ver los vales que se generan en la apertura de DIA / TURNO
	$sql = "SELECT ch_poscd AS nu_estado FROM pos_aprosys WHERE da_fecha = '" . pg_escape_string($iYear) . "-" . pg_escape_string($iMonth) . "-" . pg_escape_string($iDay) . "' AND ch_posturno = " . pg_escape_string($iTurno) . " LIMIT 1";
	$sqlca->query($sql);
	$row = $sqlca->fetchRow();

	// error_log("Etapa 3");
	// error_log( json_encode( array($row) ) );

	$table_pos_transYM = 'pos_trans' . $iYear . $iMonth;
	if ($row['nu_estado'] === 'A')
		$table_pos_transYM = 'pos_transtmp';

	// Verificar si el campo esta compuesto por CAJA-TRANS || TRANS
	$arrsCajaTrans = explode('[-]', $sCajaTrans);	
	if (isset($arrsCajaTrans[1]))
		$campo_caja_trans = $arrsCajaTrans[1];
	else
		$campo_caja_trans = $sCajaTrans;

	// error_log("Etapa 4");
	// error_log( json_encode( array($table_pos_transYM, $campo_caja_trans) ) );

	// SQL para obtener datos del establecimiento y datos del comprobante
	$sql = "
SELECT
 ALMA.ch_direccion_almacen AS txt_establecimiento_direccion,
 VC.dt_fecha,
 VC.fecha_replicacion,
 CLI.cli_razsocial,
 VC.ch_documento,
 TARJ.nomusu AS ch_usuario,
 VC.ch_tarjeta,
 VC.ch_placa,
 VCOM.ch_numeval,
 VC.nu_odometro,
 VC.ch_lado,
 PROD.art_descbreve,
 ROUND(VD.nu_cantidad, 3) AS nu_cantidad,
 UM.tab_car_03,
 ROUND(VD.nu_importe, 2) AS ss_total_producto,
 ROUND(VC.nu_importe, 2) AS ss_total,
 TO_CHAR(PT.fecha,'hh24:mi:ss') AS fe_hora
FROM
 val_ta_cabecera AS VC
 JOIN val_ta_detalle AS VD
  USING(ch_sucursal, dt_fecha, ch_documento)
 LEFT JOIN val_ta_complemento AS VCOM
  USING(ch_sucursal, dt_fecha, ch_documento)
 LEFT JOIN " . $table_pos_transYM . " AS PT
  ON(PT.es=VC.ch_sucursal AND PT.dia::DATE=VC.dt_fecha AND PT.caja=VC.ch_caja AND PT.trans::INTEGER=" . $campo_caja_trans . ")
 JOIN pos_fptshe1 AS TARJ
  ON(TARJ.numtar=VC.ch_tarjeta)
 JOIN inv_ta_almacenes AS ALMA
  ON(VC.ch_sucursal=ALMA.ch_almacen)
 JOIN int_clientes AS CLI
  ON(VC.ch_cliente=CLI.cli_codigo)
 JOIN int_articulos AS PROD
  ON(VD.ch_articulo=PROD.art_codigo)
 JOIN int_tabla_general AS UM
  ON(PROD.art_unidad=UM.tab_elemento AND UM.tab_tabla='34')
WHERE
 VC.ch_sucursal='" . $iAlmacen . "'
 AND VC.ch_documento='" . $sCajaTrans . "'
 AND VC.dt_fecha='".$iYear."-".$iMonth."-".$iDay."';
	";
	$status = $sqlca->query($sql);

	if($status < 0){
		$arrResult['estado'] = FALSE;
		$arrResult['mensaje'] = 'Error SQL - function getSocios';
	} else if($status === 0) {
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'No se encontró ningún registro';
	} else {
		$arrResult['estado'] = TRUE;
		$arrResult['mensaje'] = 'Encontro registro';
		$arrComprobante = $sqlca->fetchAll();
		
		// error_log("Etapa 5");
		// error_log( json_encode( array( $arrComprobante ) ) );		

		// Variables del establecimiento
		$arrEstablecimientoDireccion = explode('[|]', $arrComprobante[0]['txt_establecimiento_direccion']);

		$sEstablecimientoDireccion = utf8_decode($arrEstablecimientoDireccion[1] . ' ' . $arrEstablecimientoDireccion[2]);
		$sEstablecimientoDistrito = $arrEstablecimientoDireccion[3];
		$sEstablecimientoProvincia = $arrEstablecimientoDireccion[4];
		$sEstablecimientoDepartamento = $arrEstablecimientoDireccion[5];

		// error_log("Etapa 6");
		// error_log( json_encode( array( $sEstablecimientoDireccion, $sEstablecimientoDistrito, $sEstablecimientoProvincia, $sEstablecimientoDepartamento ) ) );

		// Variables del comprobante
		$arrFecha = explode('[-]', $arrComprobante[0]['dt_fecha']);
		$dFecha = $arrFecha[2] . '/' . $arrFecha[1] . '/' . $arrFecha[0];
		$arrHoraFecha = explode('[[:space:]]+', $arrComprobante[0]['fecha_replicacion']); 
		$dHora = $arrComprobante[0]['fe_hora'];
		if (isset($arrHoraFecha[1])){
			$arrHora = explode('[.]', $arrHoraFecha[1]);
			$dHora = $arrHora[0];
		}
		$arrTrans = explode('[-]', $arrComprobante[0]['ch_documento']);
		$iTrans = $arrTrans[0];
		if (isset($arrTrans[1])) {
			$iCaja = $arrTrans[0];
			$iTrans = $arrTrans[1];
		}

		// error_log("Etapa 7");
		// error_log( json_encode( array( $dFecha, $dHora, $iCaja, $iTrans ) ) );

		$sRazsocial = $arrComprobante[0]['cli_razsocial'];
		$sUsuario = $arrComprobante[0]['ch_usuario'];
		$iTarjeta = $arrComprobante[0]['ch_tarjeta'];
		$sPlaca = $arrComprobante[0]['ch_placa'];
		$iValeInterno = $arrComprobante[0]['ch_numeval'];
		$fOdometro = $arrComprobante[0]['nu_odometro'];			
		$iLado = $arrComprobante[0]['ch_lado'];
		$sNombreProducto = $arrComprobante[0]['art_descbreve'];
		$fCantidad = $arrComprobante[0]['nu_cantidad'];
		$sUnidadMedida = $arrComprobante[0]['tab_car_03'];
		$fTotalProducto = $arrComprobante[0]['ss_total_producto'];
		$fPrecioProducto = round($fTotalProducto / $fCantidad, 2);		
		$fTotal = $arrComprobante[0]['ss_total'];

		// error_log("Etapa 8");
		// error_log( json_encode( array( $sRazsocial, $sUsuario, $iTarjeta, $sPlaca, $iValeInterno, $fOdometro, $iLado, $sNombreProducto, $fCantidad, $sUnidadMedida, $fTotalProducto, $fPrecioProducto, $fTotal ) ) );

		//FORMATO DE TICKET EN PDF
		$pdf = new CReportes2("P","pt","A5");

		//set document properties
		$pdf->SetAuthor('OpenCombSystems');
		$pdf->SetTitle('Nota de Despacho');

		//set font for the entire document
		$pdf->SetFont('Courier','',10);

		//set up a page
		$pdf->AddPage();
		//$pdf->AddPage('P','mm','A5');

		//Set x and y position for the main text, reduce font size and write content
		$pdf->SetXY(10, 35);
      		$pdf->Write(5, '*******     NOTA DE DESPACHO      *******');
      		$pdf->SetXY(10, 50);
      		$pdf->Write(5, '******* NO ES COMPROBANTE DE PAGO *******');

		//Datos de la Empresa
		$pdf->SetXY(10, 65);
		$pdf->Write(5, substr($sEmpresaRazsocial, 0, 41));
		// error_log( json_encode( substr($sEmpresaRazsocial, 0, 41) ) );
		$iAltoEmpresaRazsocial -= 40;
		$_iAltoEmpresaRazsocial = 0;
      if (strlen($sEmpresaRazsocial) > 41){
			$pdf->SetXY(60, 80);
			$pdf->Write(5, substr($sEmpresaRazsocial, 41, strlen($sEmpresaRazsocial) - 41));
			$iAltoEmpresaRazsocial -= 10;
			$_iAltoEmpresaRazsocial = 15;
     	}
		$pdf->SetXY(80, 80 + $_iAltoEmpresaRazsocial);
		$pdf->Write(5, 'R.U.C. ' . $iEmpresaRuc);

		$pdf->ln();

		//Datos del establecimiento, si la dirección es diferente al de la empresa
		if(strlen($sEstablecimientoDireccion) > 41){
			$pdf->SetXY(10, 135 + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial);
			$pdf->Write(5, substr($sEstablecimientoDireccion, 0, 41));
			$pdf->SetXY(65, 150 + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial);
			$sEstablecimientoDireccion = substr($sEstablecimientoDireccion, 41, strlen($sEstablecimientoDireccion) - 41) . ' ' . $sEstablecimientoDistrito . ' ' . $sEstablecimientoProvincia . ' ' . $sEstablecimientoDepartamento;
			$pdf->Write(5, substr($sEstablecimientoDireccion, 0, 32));
			if (strlen($sEstablecimientoDireccion) > 32){
				$pdf->SetXY(65, 165 + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial);
				$pdf->Write(5, substr($sEstablecimientoDireccion, 32, strlen($sEstablecimientoDireccion) - 32));
				$_iAltoEmpresaRazsocial += 15;
			}
			$pdf->ln();
		} else
			$iAltoEmpresaRazsocial -= 30;

		$pdf->SetXY(70, 175 + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial);
		$pdf->Write(5, $dFecha . ' ' . $dHora);
		$pdf->SetXY(70, 190 + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial);
		if (isset($arrTrans[1]))
			$pdf->Write(5, 'Trans No. ' . trim($iTrans) . '/' . $iCaja);
		else
			$pdf->Write(5, 'Trans No. ' . trim($iTrans));

		$pdf->ln();

		$pdf->SetXY(10, 215 + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial);
		$pdf->Write(5, substr($sRazsocial, 0, 41));
		$iAltoCliente = 0;
		if (strlen($sRazsocial) > 41){
			$pdf->SetXY(10, 230 + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial);
			$pdf->Write(5, substr($sRazsocial, 41, strlen($sRazsocial) - 41));
			$iAltoCliente = 15;
		}

		$pdf->SetXY(65, 230 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial);
		$pdf->Write(5, 'Usuario : ' . substr($sUsuario, 0, 22));
		$iAltoUsuario = 0;
		if(strlen($sUsuario) > 22){
         $pdf->SetXY(10, 245 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial);
         $pdf->Write(5, substr($sUsuario, 22, strlen($sUsuario) - 22));
         $iAltoUsuario = 15;
		}

		$pdf->SetXY(10, 245 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, 'Tarjeta  : ' . $iTarjeta);
		$pdf->SetXY(10, 260 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, 'Placa    : ' . $sPlaca);
		$pdf->SetXY(150, 245 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
      $pdf->Write(5, 'Vale     : ' . $iValeInterno);
		$pdf->SetXY(150, 260 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
      $pdf->Write(5, 'Odometro : ' . $fOdometro);

		$iAltoEmpresaRazsocial -= 20;

		$pdf->SetXY(10, 290 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, '-----------------------------------------');
		$pdf->SetXY(10, 305 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, '*******     NOTA DE DESPACHO      *******');
		$pdf->SetXY(10, 320 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, '******* NO ES COMPROBANTE DE PAGO *******');
		$pdf->SetXY(10, 335 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, '-----------------------------------------');

		//Detalle de Comprobante
		$pdf->SetXY(10, 350 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, 'Lado ' . $iLado . ' ' . $sNombreProducto);
		$pdf->SetXY(10, 365 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, $fCantidad . ' ' . $sUnidadMedida . ' x ' . $fPrecioProducto);
		$pdf->SetXY(195, 365 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, 'S/ ' . $fTotalProducto);	
		$pdf->SetXY(10, 380 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, '-----------------------------------------');

		//Total de comprobante
		$pdf->SetXY(10, 395 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);//Left
		$pdf->Write(5, 'Total: ');
		$pdf->SetXY(195, 395 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);//Right
		$pdf->Write(5, 'S/ ' . $fTotal);

		$pdf->ln();
		$pdf->ln();
		$pdf->ln();

		//Datos del transportista
		$pdf->SetXY(60, 425 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, '--------------------');
		$pdf->SetXY(10, 440 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, 'Nombre __________________________________');
		$pdf->SetXY(10, 455 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, 'DNI ________________');
		$pdf->SetXY(10, 470 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, '*******     NOTA DE DESPACHO      *******');
		$pdf->SetXY(10, 485 + $iAltoCliente + $iAltoEmpresaRazsocial + $_iAltoEmpresaRazsocial + $iAltoUsuario);
		$pdf->Write(5, '******* NO ES COMPROBANTE DE PAGO *******');

		//Output the document
		$pdf->Output('nota_despacho.pdf','I');
	}
}
