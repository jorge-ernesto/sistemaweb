<?php

/*
 * Funcion: utilPadNum
 *
 * Proposito:
 * Hacer un pad a un numero, de tal forma que podamos decidir cuantos digitos decimales tenemos. El comportamiento
 * en caso que la cantidad de digitos ya presentes exceda a la cantidad pedida es simplemente no hacer nada.
 *
 * Regresa: un string con la cantidad de digitos especificada.
 */
function utilPadNum($numero, $decimales) {
/*    list($e, $d) = sscanf($numero, "%s.%s");
    
    if ($enteros >= 0) $e = str_pad($e, $enteros, "0", STR_PAD_LEFT);
    
    if ($decimales >= 0) $d = str_pad($d, $decimales, "0");

    return $e . "." . $d;*/
    return sprintf("%0." . $decimales . "f", $numero);
}

/*
 * Funcion: obtieneRazonSocialPorCodigo
 *
 * Proposito:
 * Obtiene la razon social del cliente mediante su codigo en la tabla maestra de clientes.
 *
 * Regresa: un string con la razon social asociada con el codigo especificado.
 */
function obtieneRazonSocialPorCodigo($codigo) {
    $query = "SELECT Cli_RazSocial FROM Int_Clientes WHERE Cli_Codigo='" . pg_escape_string($codigo) . "';";
    $rs = pg_query($query);
    
    if (pg_numrows($rs) > 0) {
	$a = pg_fetch_array($rs, 0);
	return $a[0];
    }
    else return "";
}

/*
 * Funcion: obtieneMontoPagado
 *
 * Proposito:
 * Obtiene el monto pagado asociado con el documento especificado. Se especifica la moneda, para que si se registra
 * un pago en otra moneda se pueda hacer la conversion correspondiente. Ademas, el calculo se realiza a la fecha
 * especificada.
 *
 * Regresa: la cantidad pagada para el documento especificado. Dicha cantidad se encuentra expresada en la moneda que
 * se ha especificado en el parametro $moneda.
 *
 * Nota: esta funcion podria simplificarse usando una sola sentencia SQL. Sin embargo, dicha sentencia no haria
 *       la funcion de conversion de tipo de cambio que la funcion posee de esta forma.
 */
function obtieneMontoPagado ($documento, $moneda, $fecha) {

    $query =	"SELECT NU_ImporteMovimiento,
			CH_Moneda,
			NU_TipoCambio
		 FROM CCOB_TA_Detalle
		 WHERE CH_NumDocumento = '" . pg_escape_string($documento) . "'
		    AND
			(CH_TipMovimiento = '1'
			    OR CH_TipMovimiento = '2')
		    AND DT_FechaMovimiento <= to_date('" . pg_escape_string($fecha) . "', 'YYYY-MM-DD')";
    $rs = pg_query($query);

    $pagado = 0;
    
    for ($i = 0; $i < pg_numrows($rs); $i++) {
	$A = pg_fetch_array($rs, $i);
	
	$monto = $A[0];
	$mon = $A[1];
	$cambio = $A[2];

	/* Efectua el cambio correspondiente a la moneda indicada */
	if ($mon != $moneda) {
	    if ($moneda == 1)
		$monto /= $cambio;
	    else if ($moneda == 2)
		$monto *= $cambio;
	}
	
	$pagado += $monto;
    }
    return $pagado;
}

/*
 * Funcion: obtieneSaldoDocumento
 *
 * Proposito: regresa la cantidad que falta pagar del documento especificado. Ademas, se puede especificar la fecha
 * a la que se desea calcular el saldo del documento.
 *
 * Regresa: el monto que falta pagar en el documento especificado. Dicha cantidad puede ser negativa, en el caso que
 * halla un exceso de pago por parte del cliente. La cantidad regresada se expresa en la moneda del documento en
 * cuestion.
 */
function obtieneSaldoDocumento ($documento, $fecha) {
    $query =	"SELECT NU_ImporteTotal, CH_Moneda
		 FROM CCOB_TA_CABECERA
		 WHERE CH_NumDocumento = '" . pg_escape_string($documento) . "'
		    AND DT_FechaEmision <= to_date('" . pg_escape_string($fecha) . "', 'YYYY-MM-DD');";
    $rs = pg_query($query);
    
    $A = pg_fetch_array($rs, 0);
    
    $importe = $A[0];
    $moneda = $A[1];
    
    $amortizacion = obtieneMontoPagado($documento, $moneda, $fecha);
    
    return $importe-$amortizacion;
}

/*
 * Funcion: obtieneDocumentosVencidosPorCliente
 *
 * Proposito: busca todos los documentos del cliente especificado que a la fecha especificada no se encuentran
 *  totalmente cancelados.
 *
 * Regresa: un array conteniendo:
 *	- numero: el numero del documento.
 *	- vencimiento: la fecha de vencimiento del documento.
 *	- emision: la fecha de emision del documento;
 *	- moneda: "S/." o "US$".
 *	- importe: Monto total del documento.
 *	- saldo: Cantidad que falta pagar.
 *	- nombre: razon social del cliente.
 */
function obtieneDocumentosVencidosPorCliente($codcliente, $fecha) {
    $query =	"SELECT
		    cab.CH_NumDocumento,
		    cab.DT_FechaVencimiento,
		    cab.DT_FechaEmision,
		    cab.CH_Moneda,
		    cab.NU_ImporteTotal,
		    Int_Clientes.Cli_RazSocial
		 FROM
		    CCOB_TA_CABECERA cab
		 RIGHT JOIN 
		    Int_Clientes on cab.Cli_Codigo = Int_Clientes.Cli_Codigo
		 WHERE
			cab.Cli_Codigo =  '" . pg_escape_string($codcliente) . "'
		    AND cab.DT_FechaVencimiento <= to_date('" . pg_escape_string($fecha) . "', 'YYYY-MM-DD')
		 ;";
    $rs = pg_query($query);
    
    $c = 0;
    
    for ($i = 0; $i < pg_numrows($rs); $i++) {
	$A =  pg_fetch_array($rs, $i);
	$saldo = obtieneSaldoDocumento($A[0], $fecha);
	if ($saldo > 0) {
	    $resultado[$c]['numero'] = $A[0];
	    $resultado[$c]['vencimiento'] = $A[1];
	    $resultado[$c]['emision'] = $A[2];
	    $resultado[$c]['moneda'] = $A[3] == 1 ? "S/." : "US$";
	    $resultado[$c]['importe'] = $A[4];
	    $resultado[$c]['saldo'] = utilPadNum($saldo, 2);
	    $resultado[$c]['nombre'] = $A[5];
	    $c++;
	}
    }

    return $resultado;
    
}

/*
 *
 */
function obtieneDocumentosVencidos($fecha) {
    $query =	"SELECT
		    cab.CH_NumDocumento,
		    cab.DT_FechaVencimiento,
		    cab.DT_FechaEmision,
		    cab.CH_Moneda,
		    cab.NU_ImporteTotal,
		    Int_Clientes.Cli_RazSocial,
		    Int_Clientes.Cli_Codigo
		 FROM
		    CCOB_TA_CABECERA cab
		 RIGHT JOIN 
		    Int_Clientes on cab.Cli_Codigo = Int_Clientes.Cli_Codigo
		 WHERE
		    cab.DT_FechaVencimiento <= to_date('" . pg_escape_string($fecha) . "', 'YYYY-MM-DD')
		 ORDER BY
		    Int_Clientes.Cli_Codigo
		 ;";
    $rs = pg_query($query);

    $c = 0;
    for ($i = 0; $i < pg_numrows($rs); $i++) {
	$A = pg_fetch_array($rs, $i);
	$saldo = obtieneSaldoDocumento($A[0], $fecha);
	if ($saldo > 0) {
	    $resultado[$c]['numero'] = $A[0];
	    $resultado[$c]['vencimiento'] = $A[1];
	    $resultado[$c]['emision'] = $A[2];
	    $resultado[$c]['moneda'] = $A[3] == 1 ? "S/." : "US$";
	    $resultado[$c]['importe'] = utilPadNum($A[4], 2);
	    $resultado[$c]['saldo'] = utilPadNum($saldo, 2);
	    $resultado[$c]['nombre'] = $A[5];
	    $resultado[$c]['codigo'] = $A[6];
	    $c++;
	}
    }
    
    return $resultado;
}

function obtieneDeudaPorCliente($codigo, $fecha) {
    $documentos = obtieneDocumentosVencidosPorCliente($codigo, $fecha);
    
    $deuda = 0;
    $pagado = 0;
    
    for ($i = 0; $i < count($documentos); $i++) {
	$deuda += $documentos[$i]['importe'];
	$pagado += $documentos[$i]['saldo'];
    }
    
    return Array('deuda' => $deuda, 'pagado' => $pagado, 'nombre' => $documentos[0]['nombre']);
}

function obtieneDeuda($fecha) {
    $documentos = obtieneDocumentosVencidos($fecha);
    
    $deuda = 0;
    $pagado = 0;

    $last_cod = "";
    $a = -1;
        
    for ($i = 0; $i < count($documentos); $i++) {
	$codigo = $documentos[$i]['codigo'];
	if ($codigo != $last_cod) { $a++; $last_cod = $codigo; }
	$resultado[$a]['deuda'] += $documentos[$i]['importe'];
	$resultado[$a]['saldo'] += $documentos[$i]['saldo'];
	$resultado[$a]['codigo'] = $codigo;
	$resultado[$a]['nombre'] = $documentos[$i]['nombre'];
    }

    for ($i = 0; $i < count($resultado); $i++) {
	$resultado[$i]['saldo'] = utilPadNum($resultado[$i]['saldo'], 2);
	$resultado[$i]['deuda'] = utilPadNum($resultado[$i]['deuda'], 2);
    }
    
    return $resultado;
}

function obtieneDocumentos($codigos, $fecha) {
    for ($i = 0; $i < count($codigos); $i++) {
        $query =    "SELECT
		        DT_FechaEmision,
			DT_FechaVencimiento,
			CH_Moneda,
		        NU_ImporteTotal
		     FROM
		        CCOB_TA_CABECERA
		     WHERE
		        CH_NumDocumento = '" . pg_escape_string($codigos[$i]) . "'
		    ;";
	$rs = pg_query($query);
        if (pg_numrows($rs) == 0) return 0;

	$A = pg_fetch_array($rs, 0);
    
	$resultado[$i]['emision'] = $A[0];
	$resultado[$i]['vencimiento'] = $A[1];
        $resultado[$i]['moneda'] = $A[2] == '1' ?  'S/.' : 'US$';
        $resultado[$i]['importe'] = utilPadNum($A[3], 2);
        $resultado[$i]['saldo'] = utilPadNum($A[3]-obtieneMontoPagado($codigo, $A[2], $fecha), 2);
    }    
    return $resultado;
}

function obtieneListaVencidosPorCliente($cod_cliente, $fecha) {
    $documentos = obtieneDocumentosVencidosPorCliente($cod_cliente, $fecha);
    for ($i = 0; $i < count($documentos); $i++) {
	$resultado[$i] = $documentos[$i]['numero'];
    }
    
    return $resultado;
}

function esDocumentoSunat ($tipo) {
    $sql = "SELECT
		*
	    FROM
		int_tabla_general
	    WHERE
		    tab_car_03 is not null
		AND substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1)='" . pg_escape_string($tipo) . "'
	    ;";
    $rs = pg_query($sql);

    if (pg_numrows($rs) == 0)
	return false;
    else
	return true;
}

function anulaDocumento ($tipo, $serie, $numero, $cliente, $bAnulaVale, $bEsEliminacion) {
    if (!esDocumentoSunat($tipo)) return false;

    $sql = "SELECT
		CH_LIQUIDACION,
		CH_FAC_CREDITO
	    FROM
		Fac_ta_Factura_Cabecera
	    WHERE
		    CLI_CODIGO='" . pg_escape_string($cliente) . "'
		AND CH_FAC_TIPODOCUMENTO='" . pg_escape_string($tipo) . "'
		AND CH_FAC_SERIEDOCUMENTO='" . pg_escape_string($serie) . "'
		AND CH_FAC_NUMERODOCUMENTO='" . pg_escape_string($numero) . "'
	    ;";

    $rs = pg_query($sql);
    $A = pg_fetch_array($rs, 0);
    
    $liquidacion = $A[0];
    $credito = $A[1];

    $sql = "SELECT
		(NU_ImporteTotal-NU_ImporteSaldo) AS NU_ImportePagado
	    FROM
	        CCOB_TA_CABECERA
	    WHERE
		    Cli_Codigo='" . pg_escape_string($cliente) . "'
		AND CH_TipDocumento='" . pg_escape_string($tipo) . "'
		AND CH_SerieDocumento='" . pg_escape_string($serie) . "'
		AND CH_NumDocumento='" . pg_escape_string($numero) . "'
	    ;";

    $rs = pg_query($sql);
    if (pg_numrows($rs) != 1) {
	/* INCONSISTENCIA: Cuenta al credito, pero no hay movimientos de cuentas por cobrar */
	echo "ADVERTENCIA: Inconsistencia en la base de datos: documento al credito, pero no hay movimientos en CCOB!!";
    } else {
        $A = pg_fetch_array($rs, 0);
        if ($A['NU_ImportePagado'] != 0) return false;
    }

    $sql = "SELECT
		det.ART_CODIGO, cab.CH_ALMACEN
	    FROM
		Fac_ta_Factura_Detalle det,
		Fac_ta_Factura_Cabecera cab
	    WHERE
		    det.CH_FAC_TIPODOCUMENTO='" . pg_escape_string($tipo) . "'
		AND det.CH_FAC_SERIEDOCUMENTO='" . pg_escape_string($serie) . "'
		AND det.CH_FAC_NUMERODOCUMENTO='" . pg_escape_string($numero) . "'
		AND det.CLI_CODIGO='" . pg_escape_string($cliente) . "'
    		AND det.ch_fac_tipodocumento = cab.ch_fac_tipodocumento
		AND det.ch_fac_seriedocumento = cab.ch_fac_seriedocumento
		AND det.ch_fac_numerodocumento = cab.ch_fac_numerodocumento
		AND det.cli_codigo= cab.cli_codigo
	    ;";
    $rs = pg_query($sql);
    
    for ($i = 0; $i < pg_numrows($rs); $i++) {
        $A = pg_fetch_array($rs, $i);
	
        $sql = "DELETE FROM
		    INV_MOVIALMA
		WHERE
			tran_codigo='" . pg_escape_string($tipo) . "'
		    AND mov_numero=TRIM('" . pg_escape_string($serie) . "')||TRIM('" . pg_escape_string($numero) . "')
		    AND mov_entidad='" . pg_escape_string($cliente) . "'
		    AND art_codigo='" . pg_escape_string($A[0]) . "'
		    AND mov_almacen='" . pg_escape_string($A[1]) . "'
		;";
	$rs2 = pg_query($sql);							    
    }
    
    if ($credito == "S") {
	$sql = "DELETE FROM
		    CCOB_TA_DETALLE
	        WHERE
			Cli_Codigo='" . pg_escape_string($cliente) . "'
		    AND CH_TipDocumento='" . pg_escape_string($tipo) . "'
		    AND CH_SerieDocumento='" . pg_escape_string($serie) . "'
		    AND CH_NumDocumento='" . pg_escape_string($numero) . "'
		    AND CH_Identidad='001'
		    AND CH_TipMovimiento='1'
		;";
	$rs = pg_query($sql);							
	
	$sql = "DELETE FROM
		    CCOB_TA_CABECERA
		WHERE
			Cli_Codigo='" . pg_escape_string($cliente) . "'
		    AND CH_TipDocumento='" . pg_escape_string($tipo) . "'
		    AND CH_SerieDocumento='" . pg_escape_string($serie) . "'
		    AND CH_NumDocumento='" . pg_escape_string($numero) . "'
		;";
	$rs = pg_query($sql);
    }

    $sql = "DELETE FROM
		fac_ta_factura_detalle
	    WHERE
		    ch_fac_tipodocumento='" . pg_escape_string($tipo) . "'
		AND ch_fac_seriedocumento='" . pg_escape_string($serie) . "'
		AND ch_fac_numerodocumento='" . pg_escape_string($numero) . "'
		AND cli_codigo='" . pg_escape_string($cliente) . "'
	    ;";
    $rs = pg_query($sql);
    
    
    if ($bEsEliminacion) {
	$sql = "DELETE FROM
		    fac_ta_factura_cabecera
		WHERE
			ch_fac_tipodocumento='" . pg_escape_string($tipo) . "'
		    AND ch_fac_seriedocumento='" . pg_escape_string($serie) . "'
		    AND ch_fac_numerodocumento='" . pg_escape_string($numero) . "'
		    AND cli_codigo='" . pg_escape_string($cliente) . "'
		;";
	$rs = pg_query($sql);
    }
    else {
	$sql = "UPDATE
		    Fac_ta_Factura_Cabecera
		SET
		    CH_FAC_ANULADO='S',
		    NU_FAC_VALORBRUTO=0.0,
		    NU_FAC_VALORTOTAL=0.0,
		    NU_FAC_IMPUESTO1=0.0
		WHERE
			ch_fac_tipodocumento='" . pg_escape_string($tipo) . "'
		    AND ch_fac_seriedocumento='" . pg_escape_string($serie) . "'
		    AND ch_fac_numerodocumento='" . pg_escape_string($numero) . "'
		    AND cli_codigo='" . pg_escape_string($cliente) . "'
		;";
	$rs = pg_query($sql);								
    }
    
    if ($bAnulaVale) {
	$sql = "UPDATE
		    VAL_TA_CABECERA
		SET
		    CH_LIQUIDACION=null
		WHERE
	            CH_LIQUIDACION='" . pg_escape_string($liquidacion) . "'
		;";
	$rs = pg_query($sql);    
    }
    
    return true;
}

function obtieneNumeroDocumento($tipo) {
    $sql = "SELECT
		num_numactual
	    FROM
		int_num_documentos
	    WHERE
		    num_tipdocumento='LV';";

    $rs = pg_query($sql);
    
    return pg_result($rs, 0, 0)+1;
}

function obtieneDocumentosLiquidados($liquidacion)
{
}

function obtieneVentasPorEstacion($estacion, $desde, $hasta, $bResumido)
{
    $sql = "SELECT
		NU_VENTAVALOR,
		NU_VENTAGALON,
		(NU_AFERICIONVECES_X_5*5) AS NU_AFERICION,
		(round((NU_VENTAVALOR/NU_VENTAGALON), 2)) AS NU_PRECIOGALON,
		CH_CODIGOCOMBUSTIBLE,
		DT_FECHAPARTE
	    FROM
		COMB_TA_CONTOMETROS
	    WHERE
		    CH_SUCURSAL='" . pg_escape_string($estacion) . "'
		AND DT_FECHAPARTE >= to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')
		AND DT_FECHAPARTE <= to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		AND NU_VENTAVALOR > 0
		AND NU_VENTAGALON > 0
	    ORDER BY
		DT_FECHAPARTE
	    ;";
    
    $rs = pg_query($sql);

    $otros = obtieneVentasPorTipo($desde, $hasta, $estacion);
    
    if (pg_numrows($rs) == 0 && count($otros) < 2) return Array();

    /* Array de codigos de combustibles para asociar con el array de resultados */
    $codigos = Array(
	    '11620301'	=>	'84',
	    '11620302'	=>	'90',
	    '11620303'	=>	'97',
	    '11620304'	=>	'd2',
	    '11620305'	=>	'95',
	    '11620306'	=>	'kd',
	    '11620307'	=>	'glp'
	    );

    $old_fecha = pg_result($rs, 0, 5);
    $f = 0;

    for ($i = 0; $i < pg_numrows($rs); $i++) {
	$A = pg_fetch_array($rs, $i);
	
	$imp_venta = $A[0];				/* valor venta */
	$gal_venta = $A[1];				/* galones */
	$gal_afericion = $A[2];				/* galones afericion */
	$imp_afericion = $A[3]*$gal_afericion;		/* costo afericion */
	
	$imp_venta -= $imp_afericion;
	$gal_venta -= $gal_afericion;

	if ($old_fecha != $A[5]) {
	    $old_fecha = $A[5];
	    $f++;
	}

	if (!$bResumido) {	
	    /* Acumulacion de total por fecha */
	    $totales['data'][$f]['imp'.$codigos[$A[4]]] += $imp_venta;
	    $totales['data'][$f]['gal'.$codigos[$A[4]]] += $gal_venta;
	    $totales['data'][$f]['fecha'] = $A[5];
//	    $totales['data'][$f]['otros'] = $otros[$A[5]];
	}

	/* Acumulacion de totales (para informe resumido) */
	$totales['totales']['imp'.$codigos[$A[4]]] += $imp_venta;
	$totales['totales']['gal'.$codigos[$A[4]]] += $gal_venta;

    }

    for ($i = 0; $i < count($otros['data']); $i++) {
	if (!$bResumido) {
	    $bChanged = false;
	    $fecha = $totales['data'][$i]['fecha'];
	    if (!isset($fecha)) $fecha = $otros['data']['fechas'][$i];
//	    var_dump($otros);
//	    echo "fecha: $fecha I: $i";
	    if (isset($otros['data'][$fecha]['02    '])) { $totales['data'][$i]['000002'] = $otros['data'][$fecha]['02    ']; $bChanged = true; }
	    if (isset($otros['data'][$fecha]['03    '])) { $totales['data'][$i]['000003'] = $otros['data'][$fecha]['03    ']; $bChanged = true; }
	    if (isset($otros['data'][$fecha]['06    '])) { $totales['data'][$i]['000006'] = $otros['data'][$fecha]['06    ']; $bChanged = true; }
	    if (isset($otros['data'][$fecha]['05    '])) { $totales['data'][$i]['000005'] = $otros['data'][$fecha]['05    ']; $bChanged = true; }
	    if (isset($otros['data'][$fecha]['O'])) { $totales['data'][$i]['O'] = $otros['data'][$fecha]['O']; $bChanged = true; }
	    if ($bChanged == true && !isset($totales['data'][$i]['fecha'])) { $totales['data'][$i]['fecha'] = $fecha; }
	}
	
    }
    if ($otros['totales']['02    '] > 0) $totales['totales']['000002'] = $otros['totales']['02    '];
    if ($otros['totales']['03    '] > 0) $totales['totales']['000003'] = $otros['totales']['03    '];
    if ($otros['totales']['05    '] > 0) $totales['totales']['000005'] = $otros['totales']['05    '];
    if ($otros['totales']['06    '] > 0) $totales['totales']['000006'] = $otros['totales']['06    '];	
    if ($otros['totales']['O'] > 0) $totales['totales']['O'] = $otros['totales']['O'];

//echo "inicio de descarga para estacion $estacion\n";
//    var_dump($totales);
//echo "fin de descarga\n";
    return $totales;
}

function obtieneVentas($desde, $hasta, $bResumido) {
    $sql = "SELECT
		ch_almacen,
		ch_nombre_breve_almacen,
		'S' AS ch_almacen_propio
	    FROM
		inv_ta_almacenes
	    WHERE
		ch_clase_almacen='1'
	    ORDER BY
		3 DESC,
		ch_almacen
	    ;";
    $rs = pg_query($sql);

    $resultado = Array(
	'totales'	=>  Array(
			    'imp84'	=>	0,
			    'gal84'	=>	0,
	    		    'imp90'	=>	0,
	    		    'gal90'	=>	0,
	    		    'imp95'	=>	0,
	    		    'gal95'	=>	0,
	    		    'imp97'	=>	0,
	    		    'gal97'	=>	0,
	    		    'impd2'	=>	0,
	    		    'gald2'	=>	0,
	    		    'impkd'	=>	0,
	    		    'galkd'	=>	0,
			    'impglp'	=>	0,
	    		    'galglp'	=>	0
			)
	    );

    $r = 0;
    for ($i = 0; $i < pg_numrows($rs); $i++) {
	$A = pg_fetch_array($rs, $i);
	$cada = obtieneVentasPorEstacion($A[0], $desde, $hasta, $bResumido);
	if (count($cada) == 0) {
	    continue;
	}

	$resultado['estaciones'][$r] = $cada;
	$resultado['estaciones'][$r]['nombre'] = $A[0] . " - " . $A[1];
	$resultado['estaciones'][$r]['propio'] = $A[2];
	$r++;

	/* Acumula los totales */
	$resultado['totales']['imp84'] += $cada['totales']['imp84'];
	$resultado['totales']['gal84'] += $cada['totales']['gal84'];	
	$resultado['totales']['imp90'] += $cada['totales']['imp90'];
	$resultado['totales']['gal90'] += $cada['totales']['gal90'];	
	$resultado['totales']['imp95'] += $cada['totales']['imp95'];
	$resultado['totales']['gal95'] += $cada['totales']['gal95'];	
	$resultado['totales']['imp97'] += $cada['totales']['imp97'];
	$resultado['totales']['gal97'] += $cada['totales']['gal97'];	
	$resultado['totales']['impd2'] += $cada['totales']['impd2'];
	$resultado['totales']['gald2'] += $cada['totales']['gald2'];	
	$resultado['totales']['impkd'] += $cada['totales']['impkd'];
	$resultado['totales']['galkd'] += $cada['totales']['galkd'];	
	$resultado['totales']['impglp'] += $cada['totales']['impglp'];
	$resultado['totales']['galglp'] += $cada['totales']['galglp'];
	$resultado['totales']['000002'] += $cada['totales']['000002'];
	$resultado['totales']['000003'] += $cada['totales']['000003'];
	$resultado['totales']['000005'] += $cada['totales']['000005'];
	$resultado['totales']['000006'] += $cada['totales']['000006'];
	$resultado['totales']['O'] += $cada['totales']['O'];

    }

    return $resultado;
}

function obtieneVentasPorTipo($desde, $hasta, $estacion) {
    $sql = "SELECT
		sum(d.nu_fac_valortotal) as total_tipo,
		art.art_tipo,
		c.dt_fac_fecha
	    FROM
		fac_ta_factura_cabecera c,
		fac_ta_factura_detalle d,
		int_articulos art
	    WHERE
		    c.dt_fac_fecha>=to_date('". pg_escape_string($desde) . "', 'DD/MM/YYYY')
		AND c.ch_almacen='" . pg_escape_string($estacion) . "'
		AND c.dt_fac_fecha<=to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		AND c.ch_fac_tipodocumento=d.ch_fac_tipodocumento
		AND c.ch_fac_seriedocumento=d.ch_fac_seriedocumento
		AND c.ch_fac_numerodocumento=d.ch_fac_numerodocumento
		AND c.cli_codigo=d.cli_codigo
		AND art.art_codigo = d.art_codigo
	    GROUP BY
		c.dt_fac_fecha,
		art.art_tipo
	    ORDER BY
		c.dt_fac_fecha
	    ;";
	    
//	    echo $sql;
    $rs = pg_query($sql);
    
    $sql = "SELECT
		sum(nu_fac_valortotal),
		DT_FAC_FECHA
	    FROM
		fac_ta_factura_cabecera
	    WHERE
	    	    DT_FAC_FECHA >= to_date('" . pg_escape_string($desde) . "', 'DD/MM/YYYY')
		AND DT_FAC_FECHA <= to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
		AND CH_ALMACEN='" . pg_escape_string($estacion) . "'
	    GROUP BY
		DT_FAC_FECHA
	    ;";
    $rs2 = pg_query($sql);

    $i_fecha = 0;
    $fecha = pg_result($rs, 0, 2);

    for ($i = 0; $i < pg_numrows($rs); $i++) {
	$A = pg_fetch_array($rs, $i);
	
//	echo "inicio de descarga para estacion $estacion. valor de i: $i. descarga de array de resultado:\n";
//	var_dump($A);

	$resultado['data'][$A[2]][$A[1]] = $A[0];
	$subtotal += $A[0];

//	echo "A[2] = " . $A[2] . " fecha = " . $fecha . " i_fecha: $i_fecha\n";
	if ($A[2] != $fecha) {
	
	    // evita pasar por aqui la primera vez
	    if ($i_fecha >= 0) {
		$resultado['data'][$fecha]['subtotal'] = $subtotal;
//		$resultado['data'][$fecha]['0'] = $resultado['data'][$fecha]['total'] - $subtotal;
		$resultado['data']['fechas'][$i_fecha] = $fecha;
	    }
	    $i_fecha++;
	    $fecha = $A[2];
	    $subtotal = 0;
	}
//	echo "descarga de array parcial:\n";
//	var_dump($resultado);
//	echo "fin de descarga\n";
    }

//echo "fecha: $fecha. A[2] = " . $A[2] . "\n";
//    $i_fecha++;
//    $subtotal = 0;

    $fecha = $A[2];
    $resultado['data'][$fecha]['subtotal'] = $subtotal;
//	    $resultado['data'][$fecha]['0'] = $resultado['data'][$fecha]['total'] - $subtotal;
    $resultado['data']['fechas'][$i_fecha] = $fecha;

    for ($i = 0; $i < pg_numrows($rs2); $i++) {
	$A = pg_fetch_array($rs2, $i);
	$subtotal = $resultado['data'][$A[1]]['02    ']+$resultado['data'][$A[1]]['03    ']+$resultado['data'][$A[1]]['06    ']+$resultado['data'][$A[1]]['05    '];
	$resultado['data'][$A[1]]['O'] = $A[0] - $subtotal;

	$resultado['totales']['02    '] += $resultado['data'][$A[1]]['02    '];	
	$resultado['totales']['03    '] += $resultado['data'][$A[1]]['03    '];	
	$resultado['totales']['05    '] += $resultado['data'][$A[1]]['05    '];	
	$resultado['totales']['06    '] += $resultado['data'][$A[1]]['06    '];	
	$resultado['totales']['O'] += $resultado['data'][$A[1]]['O'];
	$resultado['totales']['total'] = $A[0];
    }

//    echo "******************inicio descargs para $estacion... ****************************\n";
//    var_dump($resultado);
//    echo "************************* fin de descarga **************************\n";
    return $resultado;
}

