<?php
/**
* Los valores para el campo "tableid"
* Descripción breve
* 1 = pos_transXXXXYY
* 2 = fac_ta_factura_cabecera
* 3 = cpag_ta_cabecera
* 4 = c_cash_transaction
*
* Los valores para el campo "regid", entendiendo que viene acompañado de "tableid":
* Descripción breve
* 1 = es * caja * trans * tabla * usr * Tipo de Documento SUNAT
* 2 = ch_almacen * ch_fac_tipodocumento * ch_fac_seriedocumento * ch_fac_numerodocumento * cli_codigo * Tipo de Documento SUNAT
* 3 = pro_cab_almacen * pro_cab_tipdocumento * pro_cab_seriedocumento * pro_cab_numdocumento * pro_codigo * Tipo de Documento SUNAT
* 4 = c_cash_transaction_id
*
* Los valores para el campo "account_order" de la tabla "act_config":
* Descripción breve
* 1 = Predeterminado
* 2 = Otros
* 3 = Otros
*
* Los valores para el campo "account_action" de la tabla "act_config":
* Descripción breve
* 1 = Debe
* 2 = Haber
*
* Los valores para la tabla "act_entrytype"
* Descripción breve
* 1 = Asientos Ventas Combustible
* 2 = Asientos Ventas Market
* 3 = Asientos Venta Oficina
* 4 = Asientos Cta Cobrar Combustible
* 5 = Asientos Cta Cobrar Market
* 6 = Asientos Cta Cobrar Oficina
* 7 = Asientos Compras
* 8 = Asientos Cta Pagar
*/
require("/sistemaweb/contabilidad/helper/helper.php");
date_default_timezone_set('America/Lima');

class AsientosContablesModel extends Model {
	function __construct(){
		$this->isDebug = false;
    }

	public function generarAsientos($arrParams) {
		$objHelper = new HelperClass();	

		//ACTIVAMOS MODO DEBUG
		$this->setIsDebug(false);
		$this->setIsDebug(true);

		//ASIENTOS VENTAS COMBUSTIBLE Y ASIENTOS VENTAS MARKET	
		$objHelper->str_debug("ASIENTOS_VENTA_PLAYA");
		$req['is_error']            = FALSE;		
		$data['dataVentasPlaya']    = $this->obtenerVentasPlaya($arrParams);
		$res['asientosVentasPlaya'] = $this->generarAsientosVentasPlaya($data['dataVentasPlaya']);
		$response                   = $this->checkResponse($res['asientosVentasPlaya'], $req);

		//SI HAY UN ERROR DETIENE LA GENERACION DE ASIENTOS
		if ($response['error']) {
			return $response;
		}

		//ASIENTOS VENTAS OFICINA
		$objHelper->str_debug("ASIENTOS_VENTAS_OFICINA");		
		$req['is_error']              = FALSE;
		$data['dataVentasOficina']    = $this->obtenerVentasOficina($arrParams);
		$res['asientosVentasOficina'] = $this->generarAsientosVentasOficina($data['dataVentasOficina']);
		$response                     = $this->checkResponse($res['asientosVentasOficina'], $req);
		
		//SI HAY UN ERROR DETIENE LA GENERACION DE ASIENTOS
		if ($response['error']) {
			return $response;
		}

		//ASIENTOS CTA COBRAR COMBUSTIBLE Y ASIENTOS CTA COBRAR MARKET (BOLETAS Y FACTURAS)
		$objHelper->str_debug("ASIENTOS_CTA_COBRAR_PLAYA_B_F");
		$req['is_error']               = FALSE;
		$data['dataVentasPlaya']       = $this->obtenerVentasPlaya($arrParams);
		$res['asientosCtaCobrarPlaya'] = $this->generarAsientosCtaCobrarPlaya_B_F($data['dataVentasPlaya']);
		$response                      = $this->checkResponse($res['asientosCtaCobrarPlaya'], $req);

		//SI HAY UN ERROR DETIENE LA GENERACION DE ASIENTOS
		if ($response['error']) {
			return $response;
		}

		//ASIENTOS CTA COBRAR COMBUSTIBLE Y ASIENTOS CTA COBRAR MARKET (NOTAS DE CREDITO)
		$objHelper->str_debug("ASIENTOS_CTA_COBRAR_PLAYA_NC");
		$req['is_error']               = FALSE;
		$data['dataVentasPlaya']       = $this->obtenerVentasPlaya($arrParams);
		$res['asientosCtaCobrarPlaya'] = $this->generarAsientosCtaCobrarPlaya_NC($data['dataVentasPlaya']);
		$response                      = $this->checkResponse($res['asientosCtaCobrarPlaya'], $req);

		//SI HAY UN ERROR DETIENE LA GENERACION DE ASIENTOS
		if ($response['error']) {
			return $response;
		}

		//ASIENTOS RECONOCIMIENTO DE SOBRANTES Y FALTANTES
		$objHelper->str_debug("RECONOCIMIENTO SOBRANTES Y FALTANTES");
		$req['is_error']                   = FALSE;
		$data['dataSobrantesFaltantes']    = $this->obtenerSobrantesFaltantes($arrParams);
		$res['asientosSobrantesFaltantes'] = $this->generarAsientosSobrantesFaltantes($data['dataSobrantesFaltantes']);
		$response                          = $this->checkResponse($res['asientosSobrantesFaltantes'], $req);

		//SI HAY UN ERROR DETIENE LA GENERACION DE ASIENTOS
		if ($response['error']) {
			return $response;
		}

		//ASIENTOS RECONOCIMIENTO DE REDONDEO EFECTIVO
		$objHelper->str_debug("RECONOCIMIENTO SOBRANTES Y FALTANTES");
		$req['is_error']                 = FALSE;
		$data['dataRedondeoEfectivo']    = $this->obtenerRedondeoEfectivo($arrParams);
		$res['asientosRedondeoEfectivo'] = $this->generarAsientosRedondeoEfectivo($data['dataRedondeoEfectivo']);
		$response                        = $this->checkResponse($res['asientosRedondeoEfectivo'], $req);

		//SI HAY UN ERROR DETIENE LA GENERACION DE ASIENTOS
		if ($response['error']) {
			return $response;
		}

		//ASIENTOS COMPRAS
		$objHelper->str_debug("ASIENTOS_COMPRAS");
		$req['is_error']        = FALSE;
		$data['dataCompras']    = $this->obtenerCompras($arrParams);
		$res['asientosCompras'] = $this->generarAsientosCompras($data['dataCompras']);
		$response               = $this->checkResponse($res['asientosCompras'], $req);

		//SI HAY UN ERROR DETIENE LA GENERACION DE ASIENTOS
		if ($response['error']) {
			return $response;
		}

		//GENERAMOS BALANCE CONTABLE
		$objHelper->str_debug("GENERAMOS BALANCE");
		$req['is_error'] = FALSE;
		$res['balance']  = $this->generarBalance($arrParams);
		$response        = $this->checkResponse($res['balance'], $req);

		//SI HAY UN ERROR DETIENE LA GENERACION DE ASIENTOS
		if ($response['error']) {
			return $response;
		}

		//RETORNAMOS RESPUESTA CORRECTA
		return array(
			'error' => FALSE
		);
    }

	public function obtenerVentasPlaya($arrParams) {
		$objHelper = new HelperClass();	

		global $sqlca;

		/* Recogemos parametros */
		$almacen = TRIM($arrParams['sCodeWarehouse']);
		$fecha   = TRIM($arrParams['dEntry']);

		/* Obtenemos partes del parametro fecha */
		$porciones = explode("-", $fecha);
		$anio      = $porciones[0];
		$mes       = $porciones[1];
		$desde     = $porciones[2];
		$hasta     = $porciones[2];

		/* Obtenemos fechas para usar en queries */
		$result 			= Array();
		$fecha_postrans 	= $anio . "" . $mes;
		$fecha_inicial 		= $anio . "-" . $mes . "-" . $desde;
		$fecha_final 		= $anio . "-" . $mes . "-" . $hasta;
		$correlativo 		= 0;

		/* Obtenemos fecha postrans del mes anterior y mes posterior */
		$anio_ant = $anio;
		$anio_des = $anio;
		$mes_ant  = $mes-1;
		$mes_des  = $mes+1;
		$mes_ant  = strlen($mes_ant) == 1 ? "0".$mes_ant : $mes_ant;
		$mes_des  = strlen($mes_des) == 1 ? "0".$mes_des : $mes_des;
		if($mes == "01"){
			$mes_ant  = "12";
			$anio_ant = $anio-1;
		}
		if($mes == "12"){
			$mes_des  = "01";
			$anio_des = $anio+1;
		}
		$fecha_postrans_ant = $anio_ant . "" . $mes_ant;
		$fecha_postrans_des = $anio_des . "" . $mes_des;

		/* Validamos que tablas pos_trans del mes anterior y posterior existan */
		$status_table_postrans_ant = $objHelper->validateTableBySchema("pos_trans".$fecha_postrans_ant);
		$status_table_postrans_des = $objHelper->validateTableBySchema("pos_trans".$fecha_postrans_des);

		/* Documentos para realizar las consultas */
		$tipo_documento_tickes 	= array("'B','F','N'");

		/** CONSULTA PARA LOS EXTORNOS **/
		if ( $status_table_postrans_ant == TRUE ) {

			$sql_aferciones .= "
			SELECT
				last(venta_tickes.tickes_refe),
				venta_tickes.registro,
				venta_tickes.trans_ext
			FROM
				(SELECT 
					(p.trans||'-'||p.caja) as tickes_refe,
					p.trans,
					extorno.trans as trans_ext,
					extorno.registro,
					extorno.trans1,p.fecha
				FROM
					pos_trans" . $fecha_postrans_ant . " AS p
					INNER JOIN (
					SELECT 
						(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
						fecha,
						trans||'-'||caja as trans,
						trans as trans1
					FROM
						pos_trans" . $fecha_postrans_ant . "
					WHERE
						tm = 'A'
						AND td IN ('B','F')
					) as extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago || abs(p.cantidad) || abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
					AND td IN ('B','F')
					AND tm = 'V'
					AND p.trans < extorno.trans1
				ORDER BY
					p.fecha asc
				) AS venta_tickes
			GROUP BY
				venta_tickes.registro,
				venta_tickes.trans_ext
			
			UNION ALL
			";
		}

        $sql_aferciones .= "
		SELECT
			last(venta_tickes.tickes_refe),
			venta_tickes.registro,
			venta_tickes.trans_ext
		FROM
			(SELECT 
				(p.trans||'-'||p.caja) as tickes_refe,
				p.trans,
				extorno.trans as trans_ext,
				extorno.registro,
				extorno.trans1,p.fecha
			FROM
				pos_trans" . $fecha_postrans . " AS p
				INNER JOIN (
				SELECT 
					(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
					fecha,
					trans||'-'||caja as trans,
					trans as trans1
				FROM
					pos_trans" . $fecha_postrans . "
				WHERE
					tm = 'A'
					AND td IN ('B','F')
				) as extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago || abs(p.cantidad) || abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
				AND td IN ('B','F')
				AND tm = 'V'
				AND p.trans < extorno.trans1
			ORDER BY
				p.fecha asc
			) AS venta_tickes
		GROUP BY
			venta_tickes.registro,
			venta_tickes.trans_ext";	
			
		if ( $status_table_postrans_des == true ) {
		
			$sql_aferciones .= "
			UNION ALL
			
			SELECT
				last(venta_tickes.tickes_refe),
				venta_tickes.registro,
				venta_tickes.trans_ext
			FROM
				(SELECT 
					(p.trans||'-'||p.caja) as tickes_refe,
					p.trans,
					extorno.trans as trans_ext,
					extorno.registro,
					extorno.trans1,p.fecha
				FROM
					pos_trans" . $fecha_postrans_des . " AS p
					INNER JOIN (
					SELECT 
						(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
						fecha,
						trans||'-'||caja as trans,
						trans as trans1
					FROM
						pos_trans" . $fecha_postrans_des . "
					WHERE
						tm = 'A'
						AND td IN ('B','F')
					) as extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago || abs(p.cantidad) || abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
					AND td IN ('B','F')
					AND tm = 'V'
					AND p.trans < extorno.trans1
				ORDER BY
					p.fecha asc
				) AS venta_tickes
			GROUP BY
				venta_tickes.registro,
				venta_tickes.trans_ext";
		}

		echo "<pre>sql_aferciones:";
		echo "$sql_aferciones";
		echo "</pre>";

		if ($sqlca->query($sql_aferciones) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_aferciones');
		}

		$num_afe = 0;
		$array_aferciones_cod = array();
		for (; $num_afe < $sqlca->numrows();){
		    $a_act_afericion		= $sqlca->fetchRow();
		    $array_aferciones_cod[] = $a_act_afericion[0];
		    $array_aferciones_cod[] = $a_act_afericion[2];
		    $num_afe++;
		}

		// echo "<script>console.log('array_aferciones_cod')</script>";
		// echo "<script>console.log('" . json_encode( array($array_aferciones_cod) ) . "')</script>";

		/** Obtener series de maquinas registradoras **/
    	$sql_series = "
       	SELECT
          	trim(dt_posz_fecha_sistema::TEXT) AS dt_posz_fecha_sistema,
          	trim(ch_posz_pos::TEXT) AS ch_posz_pos,
          	trim(nu_posz_z_serie::TEXT) AS nu_posz_z_serie,
           	trim(nu_posturno::TEXT) AS nu_posturno
        FROM
			pos_z_cierres 
        WHERE
			to_char(dt_posz_fecha_sistema,'YYYY-MM') = '" . $fecha_serie . "'
		GROUP BY
			dt_posz_fecha_sistema,
			ch_posz_pos,
			nu_posz_z_serie,
			nu_posturno
		ORDER BY
			dt_posz_fecha_sistema,
			ch_posz_pos,
			nu_posz_z_serie,
			nu_posturno;
		";

		// echo "<pre>sql_series:";
		// echo "$sql_series";
		// echo "</pre>";

		if ($sqlca->query($sql_series) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_series');
		}

    	for (; $correlativo_serie < $sqlca->numrows();) {
	    	$a_act = $sqlca->fetchRow();
	    	$array_series[$a_act['dt_posz_fecha_sistema']][$a_act['ch_posz_pos']][$a_act['nu_posturno']] = $a_act['nu_posz_z_serie'];
	    	$correlativo_serie++;
    	}

		//TICKES FACTURAS
		if ( $status_table_postrans_ant == true ) {
			$sql_tickes_factura .= "
			SELECT
				T.trans as trans,
				T.caja as caja,
				T.fecha as emision,
				T.fecha as vencimiento,
				FIRST(T.dia::DATE) as operativa,
				(CASE
					WHEN FIRST(T.td) = 'B' and T.usr = '' THEN '12' 
					WHEN FIRST(T.td) = 'N' and T.usr = '' THEN '12' 
					WHEN FIRST(T.td) = 'F' and T.usr = '' THEN '12' 
					WHEN FIRST(T.td) = 'B' and FIRST(T.tm) = 'V' and T.usr != '' THEN '03' 
					WHEN FIRST(T.td) = 'B' and FIRST(T.tm) = 'D' and T.usr != '' THEN '07' 
					WHEN FIRST(T.td) = 'B' and FIRST(T.tm) = 'A' and T.usr != '' THEN '07' 
					WHEN FIRST(T.td) = 'F' and FIRST(T.tm) = 'V' and T.usr != '' THEN '01' 
					WHEN FIRST(T.td) = 'F' and FIRST(T.tm) = 'D' and T.usr != '' THEN '07' 
					WHEN FIRST(T.td) = 'F' and FIRST(T.tm) = 'A' and T.usr != '' THEN '07' 	
				END) as tipo,
				(CASE WHEN FIRST(T.usr) = '' THEN to_char(T.trans,'FM999999999999') else SUBSTR(TRIM(T.usr), 6) END) as numero,
				(CASE
					WHEN FIRST(T.td) = 'B' AND FIRST(T.ruc) != '' THEN '1'
					WHEN FIRST(T.td) = 'B' AND FIRST(T.ruc) = '' THEN '0'
					WHEN FIRST(T.td) = 'B' AND FIRST(T.ruc) IS NULL THEN '0'
					WHEN FIRST(T.td) = 'N' THEN '0'
					WHEN FIRST(T.td) = 'F' THEN '6'
				END) as tipodi,
				(CASE
					WHEN FIRST(T.td) = 'N' THEN '00000000000'
					WHEN FIRST(T.ruc) = '' THEN '99999999'
					WHEN FIRST(T.ruc) IS NULL THEN '99999999'
				ELSE
					FIRST(T.ruc)
				END) as ruc,
				(CASE
					WHEN FIRST(T.td) = 'N' THEN 'COMPROBANTE ANULADO'
					WHEN FIRST(T.ruc) = '' THEN 'CLIENTE VARIOS'
					WHEN FIRST(T.ruc) IS NULL THEN 'CLIENTE VARIOS'
				ELSE
					substr(FIRST(R.razsocial),0,60)
				END) as cliente,
				ROUND(SUM(T.importe - T.igv), 4) AS imponible, --TODO: redondeo
				ROUND(SUM(T.igv), 4) AS igv, --TODO: redondeo
				ROUND(SUM(T.importe), 4) AS importe, --TODO: redondeo
				FIRST(TC.tca_venta_oficial) as tipocambio,
				(CASE
					WHEN FIRST(T.tm) IN ('D','A') THEN 'A' ELSE FIRST(T.td)  
				END) as tipo_pdf,
				'OK' as estadoventa,
				FIRST(T.turno) as turno,
				(SELECT par_valor FROM int_parametros WHERE par_nombre = 'taxoptional') taxoptional,
				FIRST(T.es) as es,
				SUBSTR(TRIM(T.usr), 0, 5) nserie,
				SUBSTR(TRIM(T.usr), 6) numdoc,
				FIRST(T.td) AS td,
				FIRST(T.rendi_gln) AS rendi_gln,
				FIRST(T.ruc) AS ruc_bd_interno,
				FIRST(T.td) AS td,
				FIRST(T.codigo) AS codigo,
				FIRST(art.art_linea) AS linea,
				CASE
					WHEN FIRST(T.td) = 'B' OR FIRST(T.td) = 'F' THEN COALESCE( ROUND(SUM(T.balance), 4), 0 ) --TODO: redondeo
					ELSE 0
				END AS balance, --ICBPER
				FIRST(T.tipo) AS tipo_venta,
				FIRST(T.fpago) AS fpago,
           		FIRST(T.at) AS at,
				'pos_trans" . $fecha_postrans_ant . "' AS tabla,
				TRIM(T.usr) AS usr
			FROM
				pos_trans" . $fecha_postrans_ant . " AS T
				LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = T.dia)
				LEFT JOIN ruc AS R ON (R.ruc = T.ruc)
				LEFT JOIN int_articulos art ON (art.art_codigo = T.codigo)
			WHERE
				T.td IN (" . implode(',', $tipo_documento_tickes) . ")
				AND T.fecha >= '" . pg_escape_string($fecha_inicial) . " 00:00:00' AND T.fecha <= '" . pg_escape_string($fecha_final) . " 23:59:59'
				AND T.es = '$almacen'
			GROUP BY
				T.fecha,
				T.trans,
				T.caja,
				T.usr

			UNION ALL
			";			
		}		

		$sql_tickes_factura .= "
		SELECT
			T.trans as trans,
			T.caja as caja,
			T.fecha as emision,
			T.fecha as vencimiento,
			FIRST(T.dia::DATE) as operativa,
			(CASE
				WHEN FIRST(T.td) = 'B' and T.usr = '' THEN '12' 
				WHEN FIRST(T.td) = 'N' and T.usr = '' THEN '12' 
				WHEN FIRST(T.td) = 'F' and T.usr = '' THEN '12' 
				WHEN FIRST(T.td) = 'B' and FIRST(T.tm) = 'V' and T.usr != '' THEN '03' 
				WHEN FIRST(T.td) = 'B' and FIRST(T.tm) = 'D' and T.usr != '' THEN '07' 
				WHEN FIRST(T.td) = 'B' and FIRST(T.tm) = 'A' and T.usr != '' THEN '07' 
				WHEN FIRST(T.td) = 'F' and FIRST(T.tm) = 'V' and T.usr != '' THEN '01' 
				WHEN FIRST(T.td) = 'F' and FIRST(T.tm) = 'D' and T.usr != '' THEN '07' 
				WHEN FIRST(T.td) = 'F' and FIRST(T.tm) = 'A' and T.usr != '' THEN '07' 	
			END) as tipo,
			(CASE WHEN FIRST(T.usr) = '' THEN to_char(T.trans,'FM999999999999') else SUBSTR(TRIM(T.usr), 6) END) as numero,
			(CASE
				WHEN FIRST(T.td) = 'B' AND FIRST(T.ruc) != '' THEN '1'
				WHEN FIRST(T.td) = 'B' AND FIRST(T.ruc) = '' THEN '0'
				WHEN FIRST(T.td) = 'B' AND FIRST(T.ruc) IS NULL THEN '0'
				WHEN FIRST(T.td) = 'N' THEN '0'
				WHEN FIRST(T.td) = 'F' THEN '6'
			END) as tipodi,
			(CASE
				WHEN FIRST(T.td) = 'N' THEN '00000000000'
				WHEN FIRST(T.ruc) = '' THEN '99999999'
				WHEN FIRST(T.ruc) IS NULL THEN '99999999'
			ELSE
				FIRST(T.ruc)
			END) as ruc,
			(CASE
				WHEN FIRST(T.td) = 'N' THEN 'COMPROBANTE ANULADO'
				WHEN FIRST(T.ruc) = '' THEN 'CLIENTE VARIOS'
				WHEN FIRST(T.ruc) IS NULL THEN 'CLIENTE VARIOS'
			ELSE
				substr(FIRST(R.razsocial),0,60)
			END) as cliente,
			ROUND(SUM(T.importe - T.igv), 4) AS imponible,
			ROUND(SUM(T.igv), 4) AS igv,
			ROUND(SUM(T.importe), 4) AS importe,
			FIRST(TC.tca_venta_oficial) as tipocambio,
			(CASE
				WHEN FIRST(T.tm) IN ('D','A') THEN 'A' ELSE FIRST(T.td)  
			END) as tipo_pdf,
			'OK' as estadoventa,
			FIRST(T.turno) as turno,
			(SELECT par_valor FROM int_parametros WHERE par_nombre = 'taxoptional') taxoptional,
			FIRST(T.es) as es,
			SUBSTR(TRIM(T.usr), 0, 5) nserie,
			SUBSTR(TRIM(T.usr), 6) numdoc,
			FIRST(T.td) AS td,
			FIRST(T.rendi_gln) AS rendi_gln,
			FIRST(T.ruc) AS ruc_bd_interno,
			FIRST(T.td) AS td,
			FIRST(T.codigo) AS codigo,
			FIRST(art.art_linea) AS linea,
			CASE
				WHEN FIRST(T.td) = 'B' OR FIRST(T.td) = 'F' THEN COALESCE( ROUND(SUM(T.balance), 4), 0 )
				ELSE 0
			END AS balance, --ICBPER
			FIRST(T.tipo) AS tipo_venta,
			FIRST(T.fpago) AS fpago,
			FIRST(T.at) AS at,
			'pos_trans" . $fecha_postrans . "' AS tabla,
			TRIM(T.usr) AS usr
		FROM
			pos_trans" . $fecha_postrans . " AS T
			LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = T.dia)
			LEFT JOIN ruc AS R ON (R.ruc = T.ruc)
			LEFT JOIN int_articulos art ON (art.art_codigo = T.codigo)
		WHERE
			T.td IN (" . implode(',', $tipo_documento_tickes) . ")
			AND T.fecha >= '" . pg_escape_string($fecha_inicial) . " 00:00:00' AND T.fecha <= '" . pg_escape_string($fecha_final) . " 23:59:59'
			AND T.es = '$almacen'
		GROUP BY
			T.fecha,
			T.trans,
        	T.caja,
        	T.usr
		";

		if ( $status_table_postrans_des == true ) {		
			$sql_tickes_factura .= "
			UNION ALL

			SELECT
				T.trans as trans,
				T.caja as caja,
				T.fecha as emision,
				T.fecha as vencimiento,
				FIRST(T.dia::DATE) as operativa,
				(CASE
					WHEN FIRST(T.td) = 'B' and T.usr = '' THEN '12' 
					WHEN FIRST(T.td) = 'N' and T.usr = '' THEN '12' 
					WHEN FIRST(T.td) = 'F' and T.usr = '' THEN '12' 
					WHEN FIRST(T.td) = 'B' and FIRST(T.tm) = 'V' and T.usr != '' THEN '03' 
					WHEN FIRST(T.td) = 'B' and FIRST(T.tm) = 'D' and T.usr != '' THEN '07' 
					WHEN FIRST(T.td) = 'B' and FIRST(T.tm) = 'A' and T.usr != '' THEN '07' 
					WHEN FIRST(T.td) = 'F' and FIRST(T.tm) = 'V' and T.usr != '' THEN '01' 
					WHEN FIRST(T.td) = 'F' and FIRST(T.tm) = 'D' and T.usr != '' THEN '07' 
					WHEN FIRST(T.td) = 'F' and FIRST(T.tm) = 'A' and T.usr != '' THEN '07' 	
				END) as tipo,
				(CASE WHEN FIRST(T.usr) = '' THEN to_char(T.trans,'FM999999999999') else SUBSTR(TRIM(T.usr), 6) END) as numero,
				(CASE
					WHEN FIRST(T.td) = 'B' AND FIRST(T.ruc) != '' THEN '1'
					WHEN FIRST(T.td) = 'B' AND FIRST(T.ruc) = '' THEN '0'
					WHEN FIRST(T.td) = 'B' AND FIRST(T.ruc) IS NULL THEN '0'
					WHEN FIRST(T.td) = 'N' THEN '0'
					WHEN FIRST(T.td) = 'F' THEN '6'
				END) as tipodi,
				(CASE
					WHEN FIRST(T.td) = 'N' THEN '00000000000'
					WHEN FIRST(T.ruc) = '' THEN '99999999'
					WHEN FIRST(T.ruc) IS NULL THEN '99999999'
				ELSE
					FIRST(T.ruc)
				END) as ruc,
				(CASE
					WHEN FIRST(T.td) = 'N' THEN 'COMPROBANTE ANULADO'
					WHEN FIRST(T.ruc) = '' THEN 'CLIENTE VARIOS'
					WHEN FIRST(T.ruc) IS NULL THEN 'CLIENTE VARIOS'
				ELSE
					substr(FIRST(R.razsocial),0,60)
				END) as cliente,
				ROUND(SUM(T.importe - T.igv), 4) AS imponible,
				ROUND(SUM(T.igv), 4) AS igv,
				ROUND(SUM(T.importe), 4) AS importe,
				FIRST(TC.tca_venta_oficial) as tipocambio,
				(CASE
					WHEN FIRST(T.tm) IN ('D','A') THEN 'A' ELSE FIRST(T.td)  
				END) as tipo_pdf,
				'OK' as estadoventa,
				FIRST(T.turno) as turno,
				(SELECT par_valor FROM int_parametros WHERE par_nombre = 'taxoptional') taxoptional,
				FIRST(T.es) as es,
				SUBSTR(TRIM(T.usr), 0, 5) nserie,
				SUBSTR(TRIM(T.usr), 6) numdoc,
				FIRST(T.td) AS td,
				FIRST(T.rendi_gln) AS rendi_gln,
				FIRST(T.ruc) AS ruc_bd_interno,
				FIRST(T.td) AS td,
				FIRST(T.codigo) AS codigo,
				FIRST(art.art_linea) AS linea,
				CASE
					WHEN FIRST(T.td) = 'B' OR FIRST(T.td) = 'F' THEN COALESCE( ROUND(SUM(T.balance), 4), 0 )
					ELSE 0
				END AS balance, --ICBPER
				FIRST(T.tipo) AS tipo_venta,
				FIRST(T.fpago) AS fpago,
           		FIRST(T.at) AS at,
				'pos_trans" . $fecha_postrans_des . "' AS tabla,
				TRIM(T.usr) AS usr
			FROM
				pos_trans" . $fecha_postrans_des . " AS T
				LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = T.dia)
				LEFT JOIN ruc AS R ON (R.ruc = T.ruc)
				LEFT JOIN int_articulos art ON (art.art_codigo = T.codigo)
			WHERE
				T.td IN (" . implode(',', $tipo_documento_tickes) . ")
				AND T.fecha >= '" . pg_escape_string($fecha_inicial) . " 00:00:00' AND T.fecha <= '" . pg_escape_string($fecha_final) . " 23:59:59'
				AND T.es = '$almacen'
			GROUP BY
				T.fecha,
				T.trans,
				T.caja,
				T.usr
			";
		}

		$sql_tickes_factura .= "
		ORDER BY
			3,
			1;
		";

		echo "<pre>sql_tickes_factura_:";
		echo "$sql_tickes_factura";
		echo "</pre>";

		if ($sqlca->query($sql_tickes_factura) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_tickes_factura');
		}

		/* Recorremos informacion de Comprobantes de Playa */
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();
			$tipo_venta = $a['tipo_venta'];

		    //Nuevos campos agregados
	    	$result[$tipo_venta][$correlativo]['td']	         = $a['td'];
	    	$result[$tipo_venta][$correlativo]['rendi_gln']      = $a['rendi_gln'];
	    	$result[$tipo_venta][$correlativo]['ruc_bd_interno'] = $a['ruc_bd_interno'];
	    	$result[$tipo_venta][$correlativo]['id_trans']	     = $a['trans'];

	    	$trans_caja 									  = trim($a['trans'] . "-" . $a['caja']);
	    	$result[$tipo_venta][$correlativo]['trans']		  = $a['numero'];
	    	$result[$tipo_venta][$correlativo]['caja'] 		  = $a['caja'];
	    	$result[$tipo_venta][$correlativo]['emision'] 	  = $a['emision'];
	    	$result[$tipo_venta][$correlativo]['vencimiento'] = $a['vencimiento'];
			$result[$tipo_venta][$correlativo]['operativa']   = $a['operativa'];
	    	$result[$tipo_venta][$correlativo]['tipo'] 		  = $a['tipo'];

	    	$result[$tipo_venta][$correlativo]['numero'] 	  = $a['numero'];
	    	$result[$tipo_venta][$correlativo]['tipodi'] 	  = $a['tipodi'];
	    	$result[$tipo_venta][$correlativo]['ruc'] 		  = $a['ruc'];
	    	$result[$tipo_venta][$correlativo]['cliente']	  = $a['cliente'];
	    	$result[$tipo_venta][$correlativo]['vfexp']		  = 0;

			if($a['igv'] == 0.00){
				$imponible	= 0.00;
    			$igv		= 0.00;
				$balance    = 0.00;
    			$exonerada	= $a['importe'];
    			$inafecto	= 0.00;
    			$importe	= $a['importe'];
			}else{
				$imponible	= $a['imponible'];
    			$igv		= $a['igv'];
				$balance    = $a['balance'];
    			$exonerada	= 0.00;
				$inafecto 	= 0.00;
    			$importe	= $a['importe'];
			}

			if (in_array($trans_caja, $array_aferciones_cod) && $a['numdoc']=='') {//PONEMOS LOS MONTOS EN CEROS PARA LOS TICKES DE EXTORNOS
				$imponible  = 0;
        		$igv		= 0;
				$balance    = 0;
        		$exonerada	= 0;
        		$inafecto	= 0;
        		$importe	= 0;
			}else{
				if($a['igv'] == 0.00){
					$imponible	= 0.00;
	    			$igv		= 0.00;
					$balance    = 0.00;
	    			$exonerada	= $a['importe'];
	    			$inafecto	= 0.00;
	    			$importe	= $a['importe'];
				}else{
					$imponible	= $a['imponible'];
	    			$igv		= $a['igv'];
					$balance    = $a['balance'];
	    			$exonerada	= 0.00;
					$inafecto 	= 0.00;
	    			$importe	= $a['importe'];
				}
			}
			
			$a['tipo_pdf'] = TRIM($a['tipo_pdf']);
		    $result[$tipo_venta][$correlativo]['imponible']     = $imponible;
	    	$result[$tipo_venta][$correlativo]['exonerada']     = $exonerada;
	    	$result[$tipo_venta][$correlativo]['inafecto'] 	    = $inafecto;
	    	$result[$tipo_venta][$correlativo]['isc']		    = 0;
	    	$result[$tipo_venta][$correlativo]['igv'] 		    = $igv;
			$result[$tipo_venta][$correlativo]['balance']       = $balance; //ICBPER
	    	$result[$tipo_venta][$correlativo]['otros'] 	    = 0;
	    	$result[$tipo_venta][$correlativo]['importe'] 	    = $importe;
	    	$result[$tipo_venta][$correlativo]['tipocambio']    = $a['tipocambio'];
	    	$result[$tipo_venta][$correlativo]['fecha2'] 	    = "";
	    	$result[$tipo_venta][$correlativo]['tipo2'] 	    = "";
	    	$result[$tipo_venta][$correlativo]['serie2'] 	    = "";
	    	$result[$tipo_venta][$correlativo]['numero2'] 	    = "";
	    	$result[$tipo_venta][$correlativo]['tipo_pdf'] 	    = $a['tipo_pdf'];
	    	$result[$tipo_venta][$correlativo]['estado'] 	    = $a['estadoventa'];
	    	$result[$tipo_venta][$correlativo]['taxoptional']   = $a['taxoptional'];
	    	$result[$tipo_venta][$correlativo]['es']		    = $a['es'];
			$result[$tipo_venta][$correlativo]['nserie']	    = $a['nserie'];
			$result[$tipo_venta][$correlativo]['codigo']        = $a['codigo'];
			$result[$tipo_venta][$correlativo]['linea']         = $a['linea'];
			$result[$tipo_venta][$correlativo]['tipo_venta']    = $a['tipo_venta'];
			$result[$tipo_venta][$correlativo]['fpago']         = $a['fpago'];
			$result[$tipo_venta][$correlativo]['at']            = $a['at'];
			$result[$tipo_venta][$correlativo]['tabla']         = $a['tabla'];
			$result[$tipo_venta][$correlativo]['usr']           = $a['usr'];

			$result[$tipo_venta][$correlativo]['reffec'] = "";
		    $result[$tipo_venta][$correlativo]['reftip'] = "";
		    $result[$tipo_venta][$correlativo]['refser'] = "";
		    $result[$tipo_venta][$correlativo]['refnum'] = "";

	    	if ($a['nserie'] == '') {
	    		$result[$tipo_venta][$correlativo]['serie'] = $array_series[$a['emision']][$a['caja']][$a['turno']];
			}else{
				$result[$tipo_venta][$correlativo]['serie'] = $a['nserie'];
			}

			$correlativo++;
		}

		if ($this->isDebug) {
			echo "<script>console.log('result')</script>";
			echo "<script>console.log('" . json_encode($result, JSON_FORCE_OBJECT) . "')</script>";
		}		

		return array(
			'error' => FALSE,
			'result' => $result,
			'arrParams' => $arrParams,
		);
	}

	public function generarAsientosVentasPlaya($data) {
		$objHelper = new HelperClass();	
		if ($data['error']) {			
			$res = array(
				'error' => TRUE,
				'message' => $data['message'],
			);
			return $res;
		}
		
		/* Recogemos parametros */
		$arrParams = $data['arrParams'];
		
		/* Recogemos data */
		$data = $data['result'];

		global $sqlca;

		/* Obtenemos cuentas contables para Asientos Ventas Playa (Combustibles y Market) */			
		$sql_cuentas_contables = "
		SELECT
			COALESCE(c1.act_config_id,'0') ||'*'|| COALESCE(c1.value,'0') AS venta_comb_subdiario
			,COALESCE(c2.act_config_id,'0') ||'*'|| COALESCE(c2.value,'0') AS venta_glp_subdiario
			,COALESCE(c3.act_config_id,'0') ||'*'|| COALESCE(c3.value,'0') AS venta_comb_ctacobrar_soles
			,COALESCE(c4.act_config_id,'0') ||'*'|| COALESCE(c4.value,'0') AS venta_comb_ctacobrar_dolares
			,COALESCE(c5.act_config_id,'0') ||'*'|| COALESCE(c5.value,'0') AS venta_comb_impuesto
			,COALESCE(c6.act_config_id,'0') ||'*'|| COALESCE(c6.value,'0') AS venta_glp_ctacobrar_soles
			,COALESCE(c7.act_config_id,'0') ||'*'|| COALESCE(c7.value,'0') AS venta_glp_ctacobrar_dolares
			,COALESCE(c8.act_config_id,'0') ||'*'|| COALESCE(c8.value,'0') AS venta_glp_impuesto
			
			,COALESCE(c9.act_config_id,'0') ||'*'|| COALESCE(c9.value,'0') AS venta_market_subdiario
			,COALESCE(c10.act_config_id,'0') ||'*'|| COALESCE(c10.value,'0') AS venta_market_ctacobrar_soles
			,COALESCE(c11.act_config_id,'0') ||'*'|| COALESCE(c11.value,'0') AS venta_market_ctacobrar_dolares
			,COALESCE(c12.act_config_id,'0') ||'*'|| COALESCE(c12.value,'0') AS venta_market_impuesto
			,COALESCE(c13.act_config_id,'0') ||'*'|| COALESCE(c13.value,'0') AS venta_market_icbper
		FROM 
			act_config c1
			LEFT JOIN act_config c2 ON   c2.module = 1   AND c2.category = 0   AND c2.subcategory = 1   --Subdiario de Venta GLP
			LEFT JOIN act_config c3 ON   c3.module = 1   AND c3.category = 1   AND c3.subcategory = 0   --Cuenta Combustible Cta. Cobrar - SOLES
			LEFT JOIN act_config c4 ON   c4.module = 1   AND c4.category = 1   AND c3.subcategory = 1   --Cuenta Combustible Cta. Cobrar - DOLARES
			LEFT JOIN act_config c5 ON   c5.module = 1   AND c5.category = 1   AND c5.subcategory = 2   --Cuenta Combustible Impuesto
			LEFT JOIN act_config c6 ON   c6.module = 1   AND c6.category = 2   AND c6.subcategory = 0   --Cuenta GLP Cta. Cobrar - SOLES
			LEFT JOIN act_config c7 ON   c7.module = 1   AND c7.category = 2   AND c7.subcategory = 1   --Cuenta GLP Cta. Cobrar - DOLARES
			LEFT JOIN act_config c8 ON   c8.module = 1   AND c8.category = 2   AND c8.subcategory = 2   --Cuenta GLP Impuesto
			
			LEFT JOIN act_config c9 ON   c9.module = 2   AND c9.category = 0   AND c9.subcategory = 0   --Subdiario de Venta Market
			LEFT JOIN act_config c10 ON   c10.module = 2   AND c10.category = 1   AND c10.subcategory = 0   --Cuenta Market Cta. Cobrar - SOLES
			LEFT JOIN act_config c11 ON   c11.module = 2   AND c11.category = 1   AND c11.subcategory = 1   --Cuenta Market Cta. Cobrar - DOLARES
			LEFT JOIN act_config c12 ON   c12.module = 2   AND c12.category = 1   AND c12.subcategory = 2   --Cuenta Market Impuesto
			LEFT JOIN act_config c13 ON   c13.module = 2   AND c13.category = 1   AND c13.subcategory = 3   --Cuenta Market ICBPER
		WHERE   
			c1.module = 1   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Venta Combustible
		";

		if ($sqlca->query($sql_cuentas_contables) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_cuentas_contables');
		}
	
		$a = $sqlca->fetchRow();
		$cuenta['venta_comb_subdiario']           = $objHelper->getCuentaContable($a[0]);
		$cuenta['venta_glp_subdiario']            = $objHelper->getCuentaContable($a[1]);

		$cuenta['venta_comb_ctacobrar_soles']     = $objHelper->getCuentaContable($a[2]);
		$cuenta['venta_comb_ctacobrar_dolares']   = $objHelper->getCuentaContable($a[3]);
		$cuenta['venta_comb_impuesto']            = $objHelper->getCuentaContable($a[4]);
		
		$cuenta['venta_glp_ctacobrar_soles']      = $objHelper->getCuentaContable($a[5]);
		$cuenta['venta_glp_ctacobrar_dolares']    = $objHelper->getCuentaContable($a[6]);
		$cuenta['venta_glp_impuesto']             = $objHelper->getCuentaContable($a[7]);
		
		$cuenta['venta_market_subdiario']         = $objHelper->getCuentaContable($a[8]);
		$cuenta['venta_market_ctacobrar_soles']   = $objHelper->getCuentaContable($a[9]);
		$cuenta['venta_market_ctacobrar_dolares'] = $objHelper->getCuentaContable($a[10]);
		$cuenta['venta_market_impuesto']          = $objHelper->getCuentaContable($a[11]);
		$cuenta['venta_market_icbper']            = $objHelper->getCuentaContable($a[12]);

		if ($this->isDebug) {
			echo "<script>console.log('cuentas_contables')</script>";
			echo "<script>console.log('" . json_encode($cuenta, JSON_FORCE_OBJECT) . "')</script>";
		}

		/* Array para almacenar asientos */
		$data_asientos = array();
		
		/* Array para recorrer Ventas Playa (Combustibles y Market) */
		$tipoVenta = array("C", "M");

		/* Generamos Asientos Venta Playa (Combustibles y Market) */
		foreach ($tipoVenta as $key) {
			$tipo = $key;

			foreach ($data[$tipo] as $key => $value) {
				/* Variables para realizar condicionales al generar asientos */
				$tipo_venta = TRIM($value['tipo_venta']);
				$codigo     = TRIM($value['codigo']);
				$linea      = TRIM($value['linea']);
				$tipo_pdf   = TRIM($value['tipo_pdf']);	
				$td         = TRIM($value['td']);						
	
				/* Obtenemos cuentas contables */
				if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_venta == "C") { 
						//Cuentas Combustibles Por Defecto
						$subdiario       = $cuenta['venta_comb_subdiario'];
						$cuenta_total    = $cuenta['venta_comb_ctacobrar_soles'];
						$cuenta_impuesto = $cuenta['venta_comb_impuesto'];
						$cuenta_bi       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "1", "category" => "1", "subcategory" => "3", "account_order" => "1", "art_codigo" => $codigo, "value" => $value));
						$cuenta_69       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "1", "category" => "1", "subcategory" => "3", "account_order" => "2", "art_codigo" => $codigo, "value" => $value));
						$cuenta_20       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "1", "category" => "1", "subcategory" => "3", "account_order" => "3", "art_codigo" => $codigo, "value" => $value));

						if ($codigo != "11620307") { //Cuentas Combustibles
							$subdiario       = $cuenta['venta_comb_subdiario'];
							$cuenta_total    = $cuenta['venta_comb_ctacobrar_soles'];
							$cuenta_impuesto = $cuenta['venta_comb_impuesto'];
							$cuenta_bi       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "1", "category" => "1", "subcategory" => "3", "account_order" => "1", "art_codigo" => $codigo, "value" => $value));
							$cuenta_69       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "1", "category" => "1", "subcategory" => "3", "account_order" => "2", "art_codigo" => $codigo, "value" => $value));
							$cuenta_20       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "1", "category" => "1", "subcategory" => "3", "account_order" => "3", "art_codigo" => $codigo, "value" => $value));
						} else if ($codigo == "11620307") { //Cuentas GLP
							$subdiario       = $cuenta['venta_glp_subdiario'];
							$cuenta_total    = $cuenta['venta_glp_ctacobrar_soles'];
							$cuenta_impuesto = $cuenta['venta_glp_impuesto'];
							$cuenta_bi       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "1", "category" => "2", "subcategory" => "3", "account_order" => "1", "art_codigo" => $codigo, "value" => $value));
							$cuenta_69       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "1", "category" => "2", "subcategory" => "3", "account_order" => "2", "art_codigo" => $codigo, "value" => $value));
							$cuenta_20       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "1", "category" => "2", "subcategory" => "3", "account_order" => "3", "art_codigo" => $codigo, "value" => $value));
						}			
					} else if ($tipo_venta == "M") { //Cuentas Market
						$subdiario       = $cuenta['venta_market_subdiario'];
						$cuenta_total    = $cuenta['venta_market_ctacobrar_soles'];
						$cuenta_impuesto = $cuenta['venta_market_impuesto'];
						$cuenta_icbper   = $cuenta['venta_market_icbper'];
						$cuenta_bi       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "M", "module" => "2", "category" => "1", "subcategory" => "4", "account_order" => "1", "art_linea" => $linea, "value" => $value));
						$cuenta_69       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "M", "module" => "2", "category" => "1", "subcategory" => "4", "account_order" => "2", "art_linea" => $linea, "value" => $value));
						$cuenta_20       = $objHelper->getCuentaContablePersonalizada(array("tipo" => "M", "module" => "2", "category" => "1", "subcategory" => "4", "account_order" => "3", "art_linea" => $linea, "value" => $value));
					}
				}
	
				/* Obtenemos Tipo de Asiento Contable (act_entrytype), descripcion del asiento y tableid del asiento para insertar en Detalle (act_entryline) y Cabecera (act_entry) */
				if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_venta == "C") { //Asientos Ventas Playa Combustibles
						$act_entrytype_id = 1;
					} else if ($tipo_venta == "M") { //Asientos Ventas Playa Market
						$act_entrytype_id = 2;
					}

					$tableid         = 1; //Tabla pos_transXXXXYY
					$regid           = $value["es"] ."*". $value["caja"] . "*" . $value["id_trans"] . "*" . $value["tabla"];
					$int_clientes_id = ( TRIM($value["ruc"]) == "" ) ? NULL : $value["ruc"];
					$tab_currency    = "01"; //Tabla "int_tabla_general", campo "tab_tabla" con valor "04"

					if ($tipo_pdf == 'F') { //Factura
						$description = "Por Factura de Venta " . $value["serie"] . "-" . $value["numero"];
						$regid .= "*" . $value["usr"];
					} else if ($tipo_pdf == 'B') { //Boleta
						$description = "Por Boleta de Venta " . $value["serie"] . "-" . $value["numero"];
						$regid .= "*" . $value["usr"];
					} else if ($tipo_pdf == 'N') { //Nota de Despacho
						$description = "Por Nota de Despacho " . $value["caja"] . "-" . $value["id_trans"];
						$regid .= "*" . $value["caja"] . "-" . $value["id_trans"];
					} else if ($tipo_pdf == 'A') { //Nota de Credito
						$description = "Por Nota de Credito " . $value["serie"] . "-" . $value["numero"];
						$regid .= "*" . $value["usr"];
					} else { //Otros
						$description = "Por Documento " . $value["serie"] . "-" . $value["numero"];
						$regid .= "*" . $value["usr"];
					}
					$regid .= "*" . $value["tipo"];
				}

				/* Información para Detalle (act_entryline) */
				$act_entryline = array();				
				if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_pdf == 'B' || $tipo_pdf == 'F' || $tipo_pdf == 'N' || $tipo_pdf == 'A') { //Boleta, Factura, Nota de Despacho, Nota de Credito
						if ($tipo_pdf == 'B' || $tipo_pdf == 'F' || $tipo_pdf == 'N') { //Boleta, Factura, Nota de Despacho
							if ($td != 'N') { //Nota de Despacho no genera 12/40/70
								//CUENTA TOTAL
								$act_entryline[] = array(
									"act_entry_id"   => NULL,
									"act_account_id" => $cuenta_total,
									"amtdt"          => $value["importe"], //DEBE
									"amtct"          => "0.00",            
									"amtsourcedt"    => $value["importe"], //DEBE
									"amtsourcect"    => "0.00",            
									"description"        => $description,
									"tableid"            => $tableid,
									"regid"              => $regid,
									"int_clientes_id"    => $int_clientes_id,
									"c_cash_mpayment_id" => NULL,
									"tab_currency"       => $tab_currency,
								);
					
								//CUENTA IMPUESTO
								$act_entryline[] = array(
									"act_entry_id"   => NULL,
									"act_account_id" => $cuenta_impuesto,
									"amtdt"          => "0.00",        
									"amtct"          => $value["igv"], //HABER
									"amtsourcedt"    => "0.00",        
									"amtsourcect"    => $value["igv"], //HABER
									"description"        => $description,
									"tableid"            => $tableid,
									"regid"              => $regid,
									"int_clientes_id"    => $int_clientes_id,
									"c_cash_mpayment_id" => NULL,
									"tab_currency"       => $tab_currency,
								);
					
								//CUENTA ICBPER
								if ($tipo_venta == "M" && $value["balance"] > 0) {
									$act_entryline[] = array(
										"act_entry_id"   => NULL,
										"act_account_id" => $cuenta_icbper,
										"amtdt"          => "0.00",            
										"amtct"          => $value["balance"], //HABER
										"amtsourcedt"    => "0.00",           
										"amtsourcect"    => $value["balance"], //HABER
										"description"        => $description,
										"tableid"            => $tableid,
										"regid"              => $regid,
										"int_clientes_id"    => $int_clientes_id,
										"c_cash_mpayment_id" => NULL,
										"tab_currency"       => $tab_currency,
									);
								}
			
								//CUENTA BI
								$act_entryline[] = array(
									"act_entry_id"   => NULL,
									"act_account_id" => $cuenta_bi,
									"amtdt"          => "0.00",              
									"amtct"          => $value["imponible"], //HABER
									"amtsourcedt"    => "0.00",              
									"amtsourcect"    => $value["imponible"], //HABER
									"description"        => $description,
									"tableid"            => $tableid,
									"regid"              => $regid,
									"int_clientes_id"    => $int_clientes_id,
									"c_cash_mpayment_id" => NULL,
									"tab_currency"       => $tab_currency,
								);		
							}
								
							//COSTO DE VENTA
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_69,
								"amtdt"          => $value["imponible"], //DEBE
								"amtct"          => "0.00",
								"amtsourcedt"    => $value["imponible"], //DEBE
								"amtsourcect"    => "0.00",
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,
							);

							//SALIDA DE MERCADERIA
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_20,
								"amtdt"          => "0.00",              
								"amtct"          => $value["imponible"], //HABER
								"amtsourcedt"    => "0.00",              
								"amtsourcect"    => $value["imponible"], //HABER
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,
							);												
						} else if ($tipo_pdf == 'A') { //Nota de Credito
							$value["importe"]   = abs($value["importe"]);
							$value["igv"]       = abs($value["igv"]);
							$value["balance"]   = abs($value["balance"]);
							$value["imponible"] = abs($value["imponible"]);

							if ($td != 'N') { //Nota de Despacho no genera 12/40/70
								//CUENTA TOTAL
								$act_entryline[] = array(
									"act_entry_id"   => NULL,
									"act_account_id" => $cuenta_total,
									"amtdt"          => "0.00",           
									"amtct"          => $value["importe"], //HABER
									"amtsourcedt"    => "0.00",            
									"amtsourcect"    => $value["importe"], //HABER
									"description"        => $description,
									"tableid"            => $tableid,
									"regid"              => $regid,
									"int_clientes_id"    => $int_clientes_id,
									"c_cash_mpayment_id" => NULL,
									"tab_currency"       => $tab_currency,
								);
					
								//CUENTA IMPUESTO
								$act_entryline[] = array(
									"act_entry_id"   => NULL,
									"act_account_id" => $cuenta_impuesto,
									"amtdt"          => $value["igv"], //DEBE
									"amtct"          => "0.00",        
									"amtsourcedt"    => $value["igv"], 
									"amtsourcect"    => "0.00",        //HABER
									"description"        => $description,
									"tableid"            => $tableid,
									"regid"              => $regid,
									"int_clientes_id"    => $int_clientes_id,
									"c_cash_mpayment_id" => NULL,
									"tab_currency"       => $tab_currency,
								);
					
								//CUENTA ICBPER
								if ($tipo_venta == "M" && $value["balance"] > 0) {
									$act_entryline[] = array(
										"act_entry_id"   => NULL,
										"act_account_id" => $cuenta_icbper,
										"amtdt"          => $value["balance"], //DEBE
										"amtct"          => "0.00",           
										"amtsourcedt"    => $value["balance"], //DEBE
										"amtsourcect"    => "0.00",            
										"description"        => $description,
										"tableid"            => $tableid,
										"regid"              => $regid,
										"int_clientes_id"    => $int_clientes_id,
										"c_cash_mpayment_id" => NULL,
										"tab_currency"       => $tab_currency,     
									);
								}						
		
								//CUENTA BI
								$act_entryline[] = array(
									"act_entry_id"   => NULL,
									"act_account_id" => $cuenta_bi,
									"amtdt"          => $value["imponible"], //DEBE
									"amtct"          => "0.00",              
									"amtsourcedt"    => $value["imponible"], //DEBE
									"amtsourcect"    => "0.00",             
									"description"        => $description,
									"tableid"            => $tableid,
									"regid"              => $regid,
									"int_clientes_id"    => $int_clientes_id,
									"c_cash_mpayment_id" => NULL,
									"tab_currency"       => $tab_currency,           
								);
							}
							
							//COSTO DE VENTA
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_20,
								"amtdt"          => "0.00",              
								"amtct"          => $value["imponible"], //HABER
								"amtsourcedt"    => "0.00",              
								"amtsourcect"    => $value["imponible"], //HABER
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,     
							);

							//SALIDA DE MERCADERIA
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_69,
								"amtdt"          => $value["imponible"], //DEBE
								"amtct"          => "0.00",
								"amtsourcedt"    => $value["imponible"], //DEBE
								"amtsourcect"    => "0.00",
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,     
							);
						}
					}
				}				
	
				/* Informacion para Cabecera (act_entry) */
				if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_pdf == 'B' || $tipo_pdf == 'F' || $tipo_pdf == 'N' || $tipo_pdf == 'A') { //Boleta, Factura, Nota de Despacho, Nota de Credito

						// OBTENEMOS NUMERO DE ASIENTO
						$responseCorrelativo = $objHelper->getCorrelativoSubdiario($subdiario, $arrParams);
						if ($responseCorrelativo['error'] == TRUE) {
							return $responseCorrelativo;
						}		
						$correlativo = $responseCorrelativo['correlativo'];
						// END OBTENEMOS NUMERO DE ASIENTO

						$data_asientos[] = array(
							"ch_sucursal"        => $value["es"],
							"dateacct"           => $value["operativa"],
							"description"        => $description,
							"act_entrytype_id"   => $act_entrytype_id,
							"subbookcode"        => $subdiario,
							"registerno"         => $correlativo,
							"documentdate"       => $value["emision"],
							"tableid"            => $tableid,
							"regid"              => $regid,
							"int_clientes_id"    => $int_clientes_id,
							"c_cash_mpayment_id" => NULL,
							"tab_currency"       => $tab_currency,
							"act_entryline"      => $act_entryline,
						);
						
					}
				}
			}
		}

		if ($this->isDebug) {
			echo "<script>console.log('data_asientos')</script>";
			echo "<script>console.log('" . json_encode($data_asientos, JSON_FORCE_OBJECT) . "')</script>";
		}
		
		return $this->executeInsert($data_asientos);
	}

	public function obtenerVentasOficina($arrParams) {
		global $sqlca;

		/* Recogemos parametros */
		$almacen = TRIM($arrParams['sCodeWarehouse']);
		$fecha   = TRIM($arrParams['dEntry']);

		/* Obtenemos partes del parametro fecha */
		$porciones = explode("-", $fecha);
		$anio      = $porciones[0];
		$mes       = $porciones[1];
		$desde     = $porciones[2];
		$hasta     = $porciones[2];

		/* Obtenemos fechas para usar en queries */
		$result 			= Array();
		$fecha_inicial 		= $anio . "-" . $mes . "-" . $desde;
		$fecha_final 		= $anio . "-" . $mes . "-" . $hasta;

		$sql_facturas_manuales="
		SELECT 
			Cab.ch_fac_numerodocumento as trans, 
			'M' as caja,
			Cab.dt_fac_fecha as emision, 
			first(Cab.dt_fac_fecha) as vencimiento,
			(CASE WHEN Cab.ch_fac_tipodocumento='35' then '03' else
			(CASE WHEN Cab.ch_fac_tipodocumento='10' then '01' else 
			(CASE WHEN Cab.ch_fac_tipodocumento='20' then '07' else '08' end) end)
			END) AS tipo, 
			Cab.ch_fac_seriedocumento  as serie,
			Cab.ch_fac_numerodocumento as numero,
			first(
			(CASE WHEN trim(Cli.cli_ruc)='9999' THEN '1' 
			ELSE  
			CASE
			WHEN char_length(trim(Cli.cli_ruc)) = 11 THEN '6'
			WHEN char_length(trim(Cli.cli_ruc)) = 8 AND Cli.cli_ruc != '99999999' THEN '1'
			WHEN char_length(trim(Cli.cli_ruc)) = 8 AND Cli.cli_ruc = '99999999' THEN '0'
			ELSE '0' END
			END)) as tipodi,
			first(trim(COALESCE(trim(cli.cli_ruc), trim(com.ch_fac_ruc)))) AS ruc,
			first(trim(COALESCE(substr(cli.cli_razsocial,0,40), substr(com.ch_fac_nombreclie,0,40)))) AS cliente,
			round(cab.nu_fac_valorbruto,2) AS imponible,
			round(cab.nu_fac_impuesto1,2) as igv,
			round(cab.nu_fac_valortotal,2) as importe,
			first(TC.tca_venta_oficial) as tipocambio,
			first(CASE WHEN Cab.ch_fac_anulado='S' THEN
			'AN'
			ELSE 
			'OK'
			END) AS estadoventa,
			Cab.ch_fac_tiporecargo2 as istranfer,
			(SELECT par_valor FROM int_parametros WHERE par_nombre = 'taxoptional') taxoptional,
			--CASE WHEN Cab.ch_fac_tipodocumento='20' OR Cab.ch_fac_tipodocumento='11' THEN CASE WHEN substring(com.ch_fac_observacion2, length(com.ch_fac_observacion2)-1, length(com.ch_fac_observacion2))='10' THEN substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'01'  ELSE substring(com.ch_fac_observacion2, 0, length(com.ch_fac_observacion2)-1)||'03' END END AS refdata,
			(string_to_array(com.ch_fac_observacion2, '*'))[1]||'*'||(string_to_array(com.ch_fac_observacion2, '*'))[2]||'*'||FIRST(TDOCUREFE.tab_car_03)||'*' AS refdata,
			com.ch_fac_observacion3 as reffecha,
			cab.ch_fac_moneda as moneda,
			FIRST(Cab.nu_fac_recargo3) as status,
			CASE
				WHEN FIRST(Cab.nu_fac_recargo3) IS NULL OR FIRST(Cab.nu_fac_recargo3) = 0 THEN 'Registrado'
				WHEN FIRST(Cab.nu_fac_recargo3) = 1 THEN 'Completado'
				WHEN FIRST(Cab.nu_fac_recargo3) = 2 THEN 'Anulado'
				WHEN FIRST(Cab.nu_fac_recargo3) = 3 THEN 'Completado - Enviado'
				WHEN FIRST(Cab.nu_fac_recargo3) = 4 THEN 'Completado - Error'
				WHEN FIRST(Cab.nu_fac_recargo3) = 5 THEN 'Anulado - Enviado'
				WHEN FIRST(Cab.nu_fac_recargo3) = 6 THEN 'Anulado - Error'
			END as statusname,
			FIRST(Cab.ch_almacen) as almacen,
			FIRST(Cab.ch_fac_tipodocumento) tipodocumento,
			FIRST(Cab.ch_descargar_stock) descargarstock
		FROM
			fac_ta_factura_cabecera AS Cab
			LEFT JOIN fac_ta_factura_detalle AS FD USING (ch_fac_tipodocumento, ch_fac_seriedocumento, ch_fac_numerodocumento, cli_codigo)
			LEFT JOIN fac_ta_factura_complemento AS com ON (cab.cli_codigo = com.cli_codigo AND cab.ch_fac_seriedocumento=com.ch_fac_seriedocumento AND cab.ch_fac_numerodocumento=com.ch_fac_numerodocumento AND cab.ch_fac_tipodocumento=com.ch_fac_tipodocumento)
			LEFT JOIN int_clientes AS Cli ON (Cli.cli_codigo = Cab.cli_codigo)
			LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = Cab.dt_fac_fecha)
			LEFT JOIN int_tabla_general AS TDOCUREFE
			 ON (SUBSTRING(TDOCUREFE.tab_elemento, 5) = (string_to_array(com.ch_fac_observacion2, '*'))[3] AND TDOCUREFE.tab_tabla ='08' AND TDOCUREFE.tab_elemento != '000000')
		WHERE
			Cab.ch_fac_tipodocumento IN ('10', '35', '11', '20')
			AND cab.ch_almacen = '" . $almacen . "'
			AND Cab.dt_fac_fecha BETWEEN '" . pg_escape_string($fecha_inicial) . "' AND '" . pg_escape_string($fecha_final) . "' --Busqueda por fecha --TODO: fecha			
			--AND Cab.dt_fac_fecha BETWEEN '2022-07-01' AND '2022-07-31' --Busqueda por fecha
			--AND Cab.dt_fac_fecha BETWEEN '2020-04-01' AND '2020-04-10' --Busqueda por fecha
		GROUP BY
			tipo,
			serie,
    		emision,
    		Cab.ch_fac_numerodocumento,
    		Cab.ch_fac_moneda,
    		Cab.ch_fac_tiporecargo2,
    		Cab.ch_fac_tipodocumento,
    		cab.nu_fac_impuesto1,
    		com.ch_fac_observacion2,
    		com.ch_fac_observacion3,
    		cab.nu_fac_valorbruto,
			cab.nu_fac_valortotal
    	ORDER BY 
			emision,
    		serie,
    		Cab.ch_fac_numerodocumento;
		";

		echo "<pre>sql_facturas_manuales:";
		echo "$sql_facturas_manuales";
		echo "</pre>";

		if ($sqlca->query($sql_facturas_manuales) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_facturas_manuales');
		}

		/* REEMPLAZAR SERIES */
		$serie 				= explode(",", $serie);
		$nserie 			= explode(",", $nserie);

		/* Recorremos informacion de Comprobantes de Oficina */
        for ($i = 0; $i < $sqlca->numrows(); $i++) {

		    $a = $sqlca->fetchRow();

			if ($a['statusname'] == 'Completado - Enviado' || $a['statusname'] == 'Anulado - Enviado') { //TODO: Importante
				$keyStatus = "manual_completado";
			} else {
				$keyStatus = "manual_registrado";
			}

			$a['tipo'] = TRIM($a['tipo']);			
			$result[$keyStatus][$i]['trans'] 		= $a['trans'];
			$result[$keyStatus][$i]['caja'] 		= $a['caja'];
			$result[$keyStatus][$i]['emision'] 		= $a['emision'];
			$result[$keyStatus][$i]['vencimiento'] 	= $a['vencimiento'];
			$result[$keyStatus][$i]['tipo'] 		= $a['tipo'];

	    	if (empty($serie[0]) || empty($nserie[0])) {
	        	$result[$keyStatus][$i]['serie'] = $a['serie'];
	    	} else {
	        	for ($s = 0; $s < count($serie); $s++) {
            		if (trim($serie[$s]) == trim($a['serie']))
                		$result[$keyStatus][$i]['serie'] = $nserie[$s];
	        	}
	    	}

			if ($a['tipo']=='07'){//7=N/Crédito
				if ($a['moneda']=='02'){//02 = DOLARES
					if (trim($a['istranfer']) == 'T'){ //Op. Gratuitas
					    $imponible	= 0.00;
		    			$igv		= -$a['igv']*$a['tipocambio'];
		    			$exonerada	= 0.00;
		    			$inafecto	= 0.00;
		    			$importe	= -$a['igv']*$a['tipocambio'];
					}else if ( trim($a['istranfer']) == 'S'){ //Op. Exoneradas
						$imponible	= -$a['importe']*$a['tipocambio'];
		    			$igv		= 0.00;
		    			$exonerada	= $a['importe']*$a['tipocambio'];
		    			$inafecto	= 0.00;
		    			$importe	= -$a['importe']*$a['tipocambio'];
		    		}else if ( trim($a['istranfer']) == 'V'){ //Op. Inafectas
						$imponible	= -$a['importe']*$a['tipocambio'];
		    			$igv		= 0.00;
		    			$exonerada	= 0.00;
		    			$inafecto	= -$a['importe']*$a['tipocambio'];
		    			$importe	= -$a['importe']*$a['tipocambio'];
		    		}else if ( trim($a['istranfer']) == 'U' || trim($a['istranfer']) == 'W'){ //Op. Gratuitas + Exoneradas || Op. Gratuitas + Inafectas
						$imponible	= 0.00;
		    			$igv		= 0.00;
		    			$exonerada	= 0.00;
		    			$inafecto	= 0.00;
		    			$importe	= 0.00;
					}else { //Op. Gravadas
						$imponible	= -$a['imponible']*$a['tipocambio'];
		    			$igv		= -$a['igv']*$a['tipocambio'];
		    			$exonerada	= 0.00;
		    			$inafecto	= 0.00;
		    			$importe	= -$a['importe']*$a['tipocambio'];
					}
				}else{//01 = SOLES
	    			if (trim($a['istranfer']) == 'T'){ //Op. Gratuitas
					    $imponible	= 0.00;
		    			$igv		= -$a['igv'];
		    			$exonerada	= 0.00;
		    			$inafecto	= 0.00;
	    				$importe	= -$a['igv'];
					}else if ( trim($a['istranfer']) == 'S'){ //Op. Exoneradas
						$imponible	= 0.00;
	    				$igv		= 0.00;
	    				$exonerada	= -$a['importe'];
	    				$inafecto	= 0.00;
	    				$importe	= -$a['importe'];
	   	 			}else if ( trim($a['istranfer']) == 'V'){ //Op. Inafectas
						$imponible	= -$a['importe'];
	 	   				$igv		= 0.00;
	    				$exonerada	= 0.00;
	    				$inafecto	= -$a['importe'];
	    				$importe	= -$a['importe'];
	    			}else if ( trim($a['istranfer']) == 'U' || trim($a['istranfer']) == 'W'){ //Op. Gratuitas + Exoneradas || Op. Gratuitas + Inafectas
						$imponible	= 0.00;
	    				$igv		= 0.00;
	    				$exonerada	= 0.00;
	    				$inafecto	= 0.00;
	    				$importe	= 0.00;
					}else { //Op. Gravadas
						$imponible	= -$a['imponible'];
	    				$igv		= -$a['igv'];
	    				$exonerada	= 0.00;
	    				$inafecto	= 0.00;
	    				$importe	= -$a['importe'];
	    			}	
				}
			}else{
				if ($a['moneda']=='02'){//02 = DOLARES
					if (trim($a['istranfer']) == 'T'){ //Op. Gratuitas
					    $imponible	= 0.00;
		    			$igv		= $a['igv']*$a['tipocambio'];
		    			$exonerada	= 0.00;
		    			$inafecto	= 0.00;
		    			$importe	= $a['igv']*$a['tipocambio'];
					}else if ( trim($a['istranfer']) == 'S'){ //Op. Exoneradas
						$imponible	= $a['importe']*$a['tipocambio'];
		    			$igv		= 0.00;
		    			$exonerada	= $a['importe']*$a['tipocambio'];
		    			$inafecto	= 0.00;
		    			$importe	= $a['importe']*$a['tipocambio'];
		    		}else if ( trim($a['istranfer']) == 'V'){ //Op. Inafectas
						$imponible	= $a['importe']*$a['tipocambio'];
		    			$igv		= 0.00;
		    			$exonerada	= 0.00;
		    			$inafecto	= $a['importe']*$a['tipocambio'];
		    			$importe	= $a['importe']*$a['tipocambio'];
		    		}else if ( trim($a['istranfer']) == 'U' || trim($a['istranfer']) == 'W'){ //Op. Gratuitas + Exoneradas || Op. Gratuitas + Inafectas
						$imponible	= 0.00;
		    			$igv		= 0.00;
		    			$exonerada	= 0.00;
		    			$inafecto	= 0.00;
		    			$importe	= 0.00;
					}else { //Op. Gravadas
						$imponible	= $a['imponible']*$a['tipocambio'];
		    			$igv		= $a['igv']*$a['tipocambio'];
		    			$exonerada	= 0.00;
		    			$inafecto	= 0.00;
		    			$importe	= $a['importe']*$a['tipocambio'];
					}
				}else{//01 = SOLES
	    			if (trim($a['istranfer']) == 'T'){ //Op. Gratuitas
					    $imponible	= 0.00;
		    			$igv		= $a['igv'];
		    			$exonerada	= 0.00;
		    			$inafecto	= 0.00;
	    				$importe	= $a['igv'];
					}else if ( trim($a['istranfer']) == 'S'){ //Op. Exoneradas
						$imponible	= 0.00;
	    				$igv		= 0.00;
	    				$exonerada	= $a['importe'];
	    				$inafecto	= 0.00;
	    				$importe	= $a['importe'];
	   	 			}else if ( trim($a['istranfer']) == 'V'){ //Op. Inafectas
						$imponible	= $a['importe'];
	 	   				$igv		= 0.00;
	    				$exonerada	= 0.00;
	    				$inafecto	= $a['importe'];
	    				$importe	= $a['importe'];
	    			}else if ( trim($a['istranfer']) == 'U' || trim($a['istranfer']) == 'W'){ //Op. Gratuitas + Exoneradas || Op. Gratuitas + Inafectas
						$imponible	= 0.00;
	    				$igv		= 0.00;
	    				$exonerada	= 0.00;
	    				$inafecto	= 0.00;
	    				$importe	= 0.00;
					}else { //Op. Gravadas
						$imponible	= $a['imponible'];
						$igv		= $a['igv'];
	    				$exonerada	= 0.00;
	    				$inafecto	= 0.00;
	    				$importe	= $a['importe'];
	    			}	
				}
			}

	    	$result[$keyStatus][$i]['numero'] 	= $a['numero'];
	    	$result[$keyStatus][$i]['tipodi'] 	= $a['tipodi'];
	    	$result[$keyStatus][$i]['ruc'] 		= $a['ruc'];
	    	$result[$keyStatus][$i]['cliente'] 	= str_pad(utf8_encode($a['cliente']),60," ",STR_PAD_RIGHT);

	    	$result[$keyStatus][$i]['vfexp'] 	  = 0;
	    	$result[$keyStatus][$i]['isc'] 		  = 0;
	   		$result[$keyStatus][$i]['otros'] 	  = 0;
	    	$result[$keyStatus][$i]['imponible']  = (empty($imponible) ? 0.00 : $imponible);
	    	$result[$keyStatus][$i]['igv'] 		  = (empty($igv) ? 0.00 : $igv);
	    	$result[$keyStatus][$i]['exonerada']  = $exonerada;
	    	$result[$keyStatus][$i]['inafecto']   = $inafecto;
	    	$result[$keyStatus][$i]['importe'] 	  = $importe;
	    	$result[$keyStatus][$i]['tipocambio'] = $a['tipocambio'];
	    	$result[$keyStatus][$i]['fecha2'] 	  = "";
	    	$result[$keyStatus][$i]['tipo2'] 	  = "";
	    	$result[$keyStatus][$i]['serie2'] 	  = "";
	    	$result[$keyStatus][$i]['numero2']	  = "";
	    	$result[$keyStatus][$i]['istranfer']  = trim($a['istranfer']);
			$result[$keyStatus][$i]['estado'] 	  = $a['estadoventa'];
			$result[$keyStatus][$i]['balance']    = "0.00"; //ICBPER
			$result[$keyStatus][$i]['status']     = $a['status'];
			$result[$keyStatus][$i]['statusname'] = $a['statusname'];
			$result[$keyStatus][$i]['almacen']    	  = $a['almacen'];
			$result[$keyStatus][$i]['tipodocumento']  = $a['tipodocumento'];
			$result[$keyStatus][$i]['descargarstock'] = $a['descargarstock'];
			$result[$keyStatus][$i]['moneda'] 		  = $a['moneda'];

			$nuevodata= explode('*',$a['refdata']);
			$dataref1=$nuevodata[0];//numero
			$dataref2=$nuevodata[1];//serie
			$dataref3=$nuevodata[2];//tipo

			$result[$keyStatus][$i]['reffec'] 		= $a['reffecha'];
			$result[$keyStatus][$i]['reftip'] 		= $dataref3;
			$result[$keyStatus][$i]['refser'] 		= $dataref2;
			$result[$keyStatus][$i]['refnum'] 		= $dataref1;
		}// /. FOR

		if ($this->isDebug) {
			echo "<script>console.log('result')</script>";
			echo "<script>console.log('" . json_encode($result, JSON_FORCE_OBJECT) . "')</script>";
		}		

		return array(
			'error' => FALSE,
			'result' => $result,
			'arrParams' => $arrParams,
		);
	}

	public function generarAsientosVentasOficina($data) {
		$objHelper = new HelperClass();
		if ($data['error']) {			
			$res = array(
				'error' => TRUE,
				'message' => $data['message'],
			);
			return $res;
		}
		
		/* Recogemos parametros */
		$arrParams = $data['arrParams'];
		
		/* Recogemos data */
		$data = $data['result'];

		global $sqlca;

		/* Obtenemos cuentas contables para Asientos Ventas Oficina */			
		$sql_cuentas_contables = "
		SELECT
			COALESCE(c1.act_config_id,'0') ||'*'|| COALESCE(c1.value,'0') AS venta_ofi_subdiario
			,COALESCE(c2.act_config_id,'0') ||'*'|| COALESCE(c2.value,'0') AS venta_ofi_ctacobrar_soles
			,COALESCE(c3.act_config_id,'0') ||'*'|| COALESCE(c3.value,'0') AS venta_ofi_ctacobrar_dolares
			,COALESCE(c4.act_config_id,'0') ||'*'|| COALESCE(c4.value,'0') AS venta_ofi_impuesto
			,COALESCE(c5.act_config_id,'0') ||'*'|| COALESCE(c5.value,'0') AS venta_ofi_mercaderia_70
			,COALESCE(c6.act_config_id,'0') ||'*'|| COALESCE(c6.value,'0') AS venta_ofi_mercaderia_69
			,COALESCE(c7.act_config_id,'0') ||'*'|| COALESCE(c7.value,'0') AS venta_ofi_mercaderia_20
		FROM 
			act_config c1
			LEFT JOIN act_config c2 ON   c2.module = 3   AND c2.category = 1   AND c2.subcategory = 0   --Cuenta Oficina Cta. Cobrar - SOLES
			LEFT JOIN act_config c3 ON   c3.module = 3   AND c3.category = 1   AND c3.subcategory = 1   --Cuenta Oficina Cta. Cobrar - DOLARES
			LEFT JOIN act_config c4 ON   c4.module = 3   AND c4.category = 1   AND c4.subcategory = 2   --Cuenta Oficina Impuesto
			LEFT JOIN act_config c5 ON   c5.module = 3   AND c5.category = 1   AND c5.subcategory = 3   AND c5.account_order = 1   --Cuenta Oficina Mercaderia - 70
			LEFT JOIN act_config c6 ON   c6.module = 3   AND c6.category = 1   AND c6.subcategory = 3   AND c6.account_order = 2   --Cuenta Oficina Mercaderia - 69
			LEFT JOIN act_config c7 ON   c7.module = 3   AND c7.category = 1   AND c7.subcategory = 3   AND c7.account_order = 3   --Cuenta Oficina Mercaderia - 20
		WHERE   
			c1.module = 3   AND c1.category = 0   AND c1.subcategory = 0;   --Subdiario de Ventas Oficina
		";

		if ($sqlca->query($sql_cuentas_contables) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_cuentas_contables');
		}
	
		$a = $sqlca->fetchRow();
		$cuenta['venta_ofi_subdiario']         = $objHelper->getCuentaContable($a[0]);
		$cuenta['venta_ofi_ctacobrar_soles']   = $objHelper->getCuentaContable($a[1]);
		$cuenta['venta_ofi_ctacobrar_dolares'] = $objHelper->getCuentaContable($a[2]);
		$cuenta['venta_ofi_impuesto']          = $objHelper->getCuentaContable($a[3]);
		$cuenta['venta_ofi_mercaderia_70']     = $objHelper->getCuentaContable($a[4]);
		$cuenta['venta_ofi_mercaderia_69']     = $objHelper->getCuentaContable($a[5]);
		$cuenta['venta_ofi_mercaderia_20']     = $objHelper->getCuentaContable($a[6]);		

		if ($this->isDebug) {
			echo "<script>console.log('cuentas_contables')</script>";
			echo "<script>console.log('" . json_encode($cuenta, JSON_FORCE_OBJECT) . "')</script>";
		}

		/* Array para almacenar asientos */
		$data_asientos = array();

		/* Generamos Asientos Venta Oficina */
		foreach ($data["manual_completado"] as $key => $value) {
			/* Variables para realizar condicionales al generar asientos */
			$tipo           = TRIM($value['tipo']);
			$moneda         = TRIM($value['moneda']);
			$istransfer     = TRIM($value['istransfer']);
			$descargarstock = TRIM($value['descargarstock']);

			/* Obtenemos cuentas contables */
			$subdiario = $cuenta['venta_ofi_subdiario'];
			if ($moneda=='02'){//02 = DOLARES
				$cuenta_total    = $cuenta['venta_ofi_ctacobrar_dolares'];
			}else{//01 = SOLES
				$cuenta_total    = $cuenta['venta_ofi_ctacobrar_soles'];
			}
			$cuenta_impuesto = $cuenta['venta_ofi_impuesto'];
			$cuenta_bi       = $cuenta['venta_ofi_mercaderia_70'];
			$cuenta_69       = $cuenta['venta_ofi_mercaderia_69'];
			$cuenta_20       = $cuenta['venta_ofi_mercaderia_20'];

			/* Obtenemos información para detalle de asientos */
			/* Si es NC convertimos importes en negativo a positivo. Si no es NC mantenemos los importes iguales */
			if ($tipo=='07'){//7=N/Crédito
				$importe    = abs($value['importe']);
				$igv        = abs($value['igv']);
				$imponible  = abs($value['imponible']);
				$exonerada  = abs($value['exonerada']);
				$inafecto   = abs($value['inafecto']);
				$tipocambio = $value['tipocambio'];
			}else{
				$importe    = $value['importe'];
				$igv        = $value['igv'];
				$imponible  = $value['imponible'];
				$exonerada  = $value['exonerada'];
				$inafecto   = $value['inafecto'];
				$tipocambio = $value['tipocambio'];
			}

			/* Si moneda es DOLARES calculamos importe origen. Si moneda es SOLES importe e importe origen son los mismos */
			if ($moneda=='02'){//02 = DOLARES
				$importe          = $importe;
				$igv	          = $igv;
				$imponible        = $imponible;
				$importe_origen   = $importe/$tipocambio;
				$igv_origen	      = $igv/$tipocambio;
				$imponible_origen = $imponible/$tipocambio;
														
				if ( trim($istransfer) == 'S'){ //Op. Exoneradas
					$importe          = $exonerada;
					$igv              = 0.00;
					$imponible        = $exonerada;
					$importe_origen   = $exonerada/$tipocambio;
					$igv_origen       = 0.00;
					$imponible_origen = $exonerada/$tipocambio;
				}else if ( trim($istransfer) == 'V'){ //Op. Inafectas
					$importe          = $inafecto;
					$igv              = 0.00;
					$imponible        = $inafecto;
					$importe_origen   = $inafecto/$tipocambio;
					$igv_origen       = 0.00;
					$imponible_origen = $inafecto/$tipocambio;
				}
			}else{//01 = SOLES
				$importe          = $importe;
				$igv	          = $igv;
				$imponible        = $imponible;
				$importe_origen   = $importe;
				$igv_origen	      = $igv;
				$imponible_origen = $imponible;
														
				if ( trim($istransfer) == 'S'){ //Op. Exoneradas
					$importe          = $exonerada;
					$igv              = 0.00;
					$imponible        = $exonerada;
					$importe_origen   = $exonerada;
					$igv_origen       = 0.00;
					$imponible_origen = $exonerada;
				}else if ( trim($istransfer) == 'V'){ //Op. Inafectas
					$importe          = $inafecto;
					$igv              = 0.00;
					$imponible        = $inafecto;
					$importe_origen   = $inafecto;
					$igv_origen       = 0.00;
					$imponible_origen = $inafecto;
				}
			}

			/* Obtenemos Tipo de Asiento Contable (act_entrytype), descripcion del asiento y tableid del asiento para insertar en Detalle (act_entryline) y Cabecera (act_entry) */
			$act_entrytype_id = 3; //Asientos Ventas Oficina
			
			$tableid         = 2;
			$regid           = $value["almacen"] ."*". $value["tipodocumento"] . "*" . $value["serie"] . "*" . $value["numero"] . "*" . $value["ruc"] . "*" . $value["tipo"];
			$int_clientes_id = ( TRIM($value["ruc"]) == "" ) ? NULL : $value["ruc"];
			$tab_currency    = ( $moneda == "02" ) ? "02" : "01"; //Tabla "int_tabla_general", campo "tab_tabla" con valor "04"

			if ($tipo == "01") { //Factura
				$description = "Por Factura de Venta " . $value["serie"] . "-" . $value["numero"];
			} else if ($tipo == "03") { //Boleta
				$description = "Por Boleta de Venta " . $value["serie"] . "-" . $value["numero"];
			} else if ($tipo == '07') { //Nota de Credito
				$description = "Por Nota de Credito " . $value["serie"] . "-" . $value["numero"];
			} else if ($tipo == '8') { //Nota de Debito
				$description = "Por Nota de Debito " . $value["serie"] . "-" . $value["numero"];
			} else { //Otros
				$description = "Por Documento " . $value["serie"] . "-" . $value["numero"];
			}			

			/* Información para Detalle (act_entryline) */
			$act_entryline = array();
			if ($tipo=='07'){//7=N/Crédito
				//CUENTA TOTAL
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_total,
					"amtdt"          => "0.00",
					"amtct"          => $importe, //HABER
					"amtsourcedt"    => "0.00",
					"amtsourcect"    => $importe_origen, //HABER
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,     
				);
	
				//CUENTA IMPUESTO
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_impuesto,
					"amtdt"          => $igv, //DEBE
					"amtct"          => "0.00",
					"amtsourcedt"    => $igv_origen, //DEBE
					"amtsourcect"    => "0.00",
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);

				//CUENTA BI
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_bi,
					"amtdt"          => $imponible, //DEBE
					"amtct"          => "0.00",
					"amtsourcedt"    => $imponible_origen, //DEBE
					"amtsourcect"    => "0.00",
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);

				if ($descargarstock == "S") {
					//COSTO DE VENTA
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_20,
						"amtdt"          => "0.00",
						"amtct"          => $imponible, //HABER
						"amtsourcedt"    => "0.00",
						"amtsourcect"    => $imponible_origen, //HABER
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
					);

					//SALIDA DE MERCADERIA
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_69,
						"amtdt"          => $imponible, //DEBE
						"amtct"          => "0.00",
						"amtsourcedt"    => $imponible_origen, //DEBE
						"amtsourcect"    => "0.00",
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
					);
				}
			}else{
				//CUENTA TOTAL
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_total,
					"amtdt"          => $importe, //DEBE
					"amtct"          => "0.00",
					"amtsourcedt"    => $importe_origen, //DEBE
					"amtsourcect"    => "0.00",
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);
	
				//CUENTA IMPUESTO
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_impuesto,
					"amtdt"          => "0.00",
					"amtct"          => $igv, //HABER
					"amtsourcedt"    => "0.00",
					"amtsourcect"    => $igv_origen, //HABER
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);					

				//CUENTA BI
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_bi,
					"amtdt"          => "0.00",
					"amtct"          => $imponible, //HABER
					"amtsourcedt"    => "0.00",
					"amtsourcect"    => $imponible_origen, //HABER
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);
				
				if ($descargarstock == "S") {
					//COSTO DE VENTA
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_69,
						"amtdt"          => $imponible, //DEBE
						"amtct"          => "0.00",
						"amtsourcedt"    => $imponible_origen, //DEBE
						"amtsourcect"    => "0.00",
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
					);

					//SALIDA DE MERCADERIA
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_20,
						"amtdt"          => "0.00",
						"amtct"          => $imponible_origen, //HABER
						"amtsourcedt"    => "0.00",
						"amtsourcect"    => $imponible_origen, //HABER
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
					);
				}
			}

			// OBTENEMOS NUMERO DE ASIENTO
			$responseCorrelativo = $objHelper->getCorrelativoSubdiario($subdiario, $arrParams);
			if ($responseCorrelativo['error'] == TRUE) {
				return $responseCorrelativo;
			}				
			$correlativo = $responseCorrelativo['correlativo'];
			// END OBTENEMOS NUMERO DE ASIENTO

			/* Informacion para Cabecera (act_entry) */
			$data_asientos[] = array(
				"ch_sucursal"        => $value["almacen"],
				"dateacct"           => $value["emision"],
				"description"        => $description,
				"act_entrytype_id"   => $act_entrytype_id,
				"subbookcode"        => $subdiario,
				"registerno"         => $correlativo,
				"documentdate"       => $value["emision"],
				"tableid"            => $tableid,
				"regid"              => $regid,
				"int_clientes_id"    => $int_clientes_id,
				"c_cash_mpayment_id" => NULL,
				"tab_currency"       => $tab_currency,
				"act_entryline"      => $act_entryline,
			);
		}

		if ($this->isDebug) {
			echo "<script>console.log('data_asientos')</script>";
			echo "<script>console.log('" . json_encode($data_asientos, JSON_FORCE_OBJECT) . "')</script>";
		}
		
		return $this->executeInsert($data_asientos);
	}

	public function generarAsientosCtaCobrarPlaya_B_F($data) {
		$objHelper = new HelperClass();
		if ($data['error']) {			
			$res = array(
				'error' => TRUE,
				'message' => $data['message'],
			);
			return $res;
		}

		/* Recogemos parametros */
		$arrParams = $data['arrParams'];

		/* Recogemos data */
		$data = $data['result'];
	
		global $sqlca;

		/* Obtenemos cuentas contables para Asientos Cta Cobrar Playa (Combustibles y Market) */
		$sql_cuentas_contables = "
		SELECT
			COALESCE(c01.act_config_id,'0') ||'*'|| COALESCE(c01.value,'0') AS ctacobrar_comb_subdiario
			,COALESCE(c02.act_config_id,'0') ||'*'|| COALESCE(c02.value,'0') AS ctacobrar_glp_subdiario
			,COALESCE(c03.act_config_id,'0') ||'*'|| COALESCE(c03.value,'0') AS ctacobrar_comb_cliente
			,COALESCE(c04.act_config_id,'0') ||'*'|| COALESCE(c04.value,'0') AS ctacobrar_comb_caja_efectivo
			,COALESCE(c05.act_config_id,'0') ||'*'|| COALESCE(c05.value,'0') AS ctacobrar_glp_cliente
			,COALESCE(c06.act_config_id,'0') ||'*'|| COALESCE(c06.value,'0') AS ctacobrar_glp_caja_efectivo

			,COALESCE(c07.act_config_id,'0') ||'*'|| COALESCE(c07.value,'0') AS ctacobrar_mkt_subdiario
			,COALESCE(c08.act_config_id,'0') ||'*'|| COALESCE(c08.value,'0') AS ctacobrar_mkt_cliente
			,COALESCE(c09.act_config_id,'0') ||'*'|| COALESCE(c09.value,'0') AS ctacobrar_mkt_caja_efectivo

			,COALESCE(c10.act_config_id,'0') ||'*'|| COALESCE(c10.value,'0') AS comb_gasto_cobrotc_1
			,COALESCE(c11.act_config_id,'0') ||'*'|| COALESCE(c11.value,'0') AS comb_gasto_cobrotc_2
			,COALESCE(c12.act_config_id,'0') ||'*'|| COALESCE(c12.value,'0') AS comb_gasto_cobrotc_3

			,COALESCE(c13.act_config_id,'0') ||'*'|| COALESCE(c13.value,'0') AS glp_gasto_cobrotc_1
			,COALESCE(c14.act_config_id,'0') ||'*'|| COALESCE(c14.value,'0') AS glp_gasto_cobrotc_2
			,COALESCE(c15.act_config_id,'0') ||'*'|| COALESCE(c15.value,'0') AS glp_cobro_cobrotc_3

			,COALESCE(c16.act_config_id,'0') ||'*'|| COALESCE(c16.value,'0') AS mkt_gasto_cobrotc_1
			,COALESCE(c17.act_config_id,'0') ||'*'|| COALESCE(c17.value,'0') AS mkt_gasto_cobrotc_2
			,COALESCE(c18.act_config_id,'0') ||'*'|| COALESCE(c18.value,'0') AS mkt_gasto_cobrotc_3
		FROM 
			act_config c01	
			LEFT JOIN act_config c02 ON   c02.module = 4   AND c02.category = 0   AND c02.subcategory = 1   --Subdiario de Cta Cobrar GLP
			LEFT JOIN act_config c03 ON   c03.module = 4   AND c03.category = 1   AND c03.subcategory = 0   --Cuenta Cobrar Combustible Cliente - SOLES
			LEFT JOIN act_config c04 ON   c04.module = 4   AND c04.category = 1   AND c04.subcategory = 1   --Cuenta Cobrar Combustible Caja Efectivo - SOLES   --No se utiliza, ya que se utiliza lo de act_config_cash
			LEFT JOIN act_config c05 ON   c05.module = 4   AND c05.category = 2   AND c05.subcategory = 0   --Cuenta Cobrar GLP Cliente - SOLES
			LEFT JOIN act_config c06 ON   c06.module = 4   AND c06.category = 2   AND c06.subcategory = 1   --Cuenta Cobrar GLP Caja Efectivo - SOLES           --No se utiliza, ya que se utiliza lo de act_config_cash

			LEFT JOIN act_config c07 ON   c07.module = 5   AND c07.category = 0   AND c07.subcategory = 0   --Subdiario de Cta Cobrar Market
			LEFT JOIN act_config c08 ON   c08.module = 5   AND c08.category = 1   AND c08.subcategory = 0   --Cuenta Cobrar Market Cliente - SOLES
			LEFT JOIN act_config c09 ON   c09.module = 5   AND c09.category = 1   AND c09.subcategory = 1   --Cuenta Cobrar Market Caja Efectivo - SOLES        --No se utiliza, ya que se utiliza lo de act_config_cash

			--COMBUSTIBLE
			LEFT JOIN act_config c10 ON   c10.module = 4   AND c10.category = 1   AND c10.subcategory = 2   AND c10.account_order = 1   --Gastos financieros Cobro (%) TC
			LEFT JOIN act_config c11 ON   c11.module = 4   AND c11.category = 1   AND c11.subcategory = 2   AND c11.account_order = 2   --Gastos financieros Cobro (%) TC
			LEFT JOIN act_config c12 ON   c12.module = 4   AND c12.category = 1   AND c12.subcategory = 2   AND c12.account_order = 3   --Cargas imputables a cuenta de costo y gasto
			
			--GLP
			LEFT JOIN act_config c13 ON   c13.module = 4   AND c13.category = 2   AND c13.subcategory = 2   AND c13.account_order = 1   --Gastos financieros Cobro (%) TC
			LEFT JOIN act_config c14 ON   c14.module = 4   AND c14.category = 2   AND c14.subcategory = 2   AND c14.account_order = 2   --Gastos financieros Cobro (%) TC
			LEFT JOIN act_config c15 ON   c15.module = 4   AND c15.category = 2   AND c15.subcategory = 2   AND c15.account_order = 3   --Cargas imputables a cuenta de costo y gasto
			
			--MARKET
			LEFT JOIN act_config c16 ON   c16.module = 5   AND c16.category = 1   AND c16.subcategory = 2   AND c16.account_order = 1   --Gastos financieros Cobro (%) TC
			LEFT JOIN act_config c17 ON   c17.module = 5   AND c17.category = 1   AND c17.subcategory = 2   AND c17.account_order = 2   --Gastos financieros Cobro (%) TC
			LEFT JOIN act_config c18 ON   c18.module = 5   AND c18.category = 1   AND c18.subcategory = 2   AND c18.account_order = 3   --Cargas imputables a cuenta de costo y gasto
		WHERE   
			c01.module = 4   AND c01.category = 0   AND c01.subcategory = 0;   --Subdiario de Cta Cobrar Combustible
		";

		if ($sqlca->query($sql_cuentas_contables) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_cuentas_contables');
		}

		$a = $sqlca->fetchRow();
		$cuenta['ctacobrar_comb_subdiario']           = $objHelper->getCuentaContable($a[0]);
		$cuenta['ctacobrar_glp_subdiario']            = $objHelper->getCuentaContable($a[1]);
		$cuenta['ctacobrar_comb_cliente_soles']       = $objHelper->getCuentaContable($a[2]);
		$cuenta['ctacobrar_comb_caja_efectivo_soles'] = $objHelper->getCuentaContable($a[3]); //No se utiliza, ya que se utiliza lo de act_config_cash
		$cuenta['ctacobrar_glp_cliente_soles']        = $objHelper->getCuentaContable($a[4]);
		$cuenta['ctacobrar_glp_caja_efectivo_soles']  = $objHelper->getCuentaContable($a[5]); //No se utiliza, ya que se utiliza lo de act_config_cash
		
		$cuenta['ctacobrar_mkt_subdiario']            = $objHelper->getCuentaContable($a[6]);
		$cuenta['ctacobrar_mkt_cliente_soles']        = $objHelper->getCuentaContable($a[7]);
		$cuenta['ctacobrar_mkt_caja_efectivo_soles']  = $objHelper->getCuentaContable($a[8]); //No se utiliza, ya que se utiliza lo de act_config_cash

		$cuenta['comb_gasto_cobrotc_1']               = $objHelper->getCuentaContable($a[9]);
		$cuenta['comb_gasto_cobrotc_2']               = $objHelper->getCuentaContable($a[10]);
		$cuenta['comb_gasto_cobrotc_3']               = $objHelper->getCuentaContable($a[11]);

		$cuenta['glp_gasto_cobrotc_1']                = $objHelper->getCuentaContable($a[12]);
		$cuenta['glp_gasto_cobrotc_2']                = $objHelper->getCuentaContable($a[13]);
		$cuenta['glp_gasto_cobrotc_3']                = $objHelper->getCuentaContable($a[14]);

		$cuenta['mkt_gasto_cobrotc_1']                = $objHelper->getCuentaContable($a[15]);
		$cuenta['mkt_gasto_cobrotc_2']                = $objHelper->getCuentaContable($a[16]);
		$cuenta['mkt_gasto_cobrotc_3']                = $objHelper->getCuentaContable($a[17]);
		
		if ($this->isDebug) {
			echo "<script>console.log('cuentas_contables')</script>";
			echo "<script>console.log('" . json_encode($cuenta, JSON_FORCE_OBJECT) . "')</script>";
		}

		/************************************** OBTENEMOS CUENTAS CONTABLES TARJETAS **************************************/
		//Nota: En la tabla act_config_cash, el campo act_account_id tiene las Cuentas Contables de los Pagos (Cuenta 10) de Cuentas por Cobrar
		$sql_cc = "SELECT
						act_config_cash_id, 
						ch_sucursal,   --DATO PARA CONSULTAR QUERY
						module,        --DATO PARA CONSULTAR QUERY
						documenttype,  --DATO PARA AGRUPACION EN ARRAY
						paymentmethod, --DATO PARA AGRUPACION EN ARRAY
						description, 
						percentagedsct, 
						act_account_id, 
						value
					FROM  
						act_config_cash
					WHERE
						module = '0'
					ORDER BY 
						documenttype, paymentmethod;";
				
		if ($sqlca->query($sql_cc) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_cc');			
		}

		$data_tarjetas = array();
		while ($reg = $sqlca->fetchRow()) {
			/*
			* TIPO DE DOCUMENTO 
			* B: Boleta 
			* F: Factura
			* 
			* FORMA DE PAGO
			* 0: Ticket efectivo
			* 1, 2, 3, 4, 5: Tipos de tarjetas
			* 
			*/ 
			$act_config_cash_id = $reg['act_config_cash_id'];   //VALOR NUMERIC EN TABLA
			$documenttype       = TRIM($reg['documenttype']);   //VALOR VARCHAR EN TABLA
			$paymentmethod      = TRIM($reg['paymentmethod']);  //VALOR VARCHAR EN TABLA
			$description        = TRIM($reg['description']);    //VALOR VARCHAR EN TABLA
			$percentagedsct     = TRIM($reg['percentagedsct']); //VALOR VARCHAR EN TABLA
			$act_account_id     = $reg['act_account_id'];       //VALOR NUMERIC EN TABLA
			$value              = TRIM($reg['value']);
			$data_tarjetas[$documenttype][$paymentmethod]['act_config_cash_id'] = $act_config_cash_id;
			$data_tarjetas[$documenttype][$paymentmethod]['description']        = $description;
			$data_tarjetas[$documenttype][$paymentmethod]['percentagedsct']     = $percentagedsct;
			$data_tarjetas[$documenttype][$paymentmethod]['act_account_id']     = $act_account_id;
			$data_tarjetas[$documenttype][$paymentmethod]['value']              = $value;
		}

		if ($this->isDebug) {
			echo "<script>console.log('cuentas_contables_tarjetas')</script>";
			echo "<script>console.log('" . json_encode($data_tarjetas, JSON_FORCE_OBJECT) . "')</script>";
		}
		/************************************** END OBTENEMOS CUENTAS CONTABLES TARJETAS **************************************/

		/************************************** FORMATEAMOS ARRAY PARA GENERAR LOS ASIENTOS PARA CTA COBRAR (COMBUSTIBLE Y MARKET) **************************************/
		/* Array para almacenar asientos */
		$result = array();

		/* Array para recorrer Cta Cobrar Playa (Combustibles y Market) */
		$tipoVenta = array("C", "M");

		/* Generamos Asientos Cta Cobrar Playa (Combustibles y Market) */
		foreach ($tipoVenta as $key) {
			$tipo = $key;

			foreach ($data[$tipo] as $key => $value) {
				/* Variables para realizar condicionales al generar asientos */
				$tipo_venta = TRIM($value['tipo_venta']);
				$codigo     = TRIM($value['codigo']);
				$tipo_pdf   = TRIM($value['tipo_pdf']);	
				$td         = TRIM($value['td']);
				$fpago      = TRIM($value['fpago']);
				$at         = TRIM($value['at']);	
				$serie      = TRIM($value['serie']);

				/*
				* FORMA DE PAGO (Campo fpago de pos_trans)
				* 1: Efectivo
				* 2: Tarjeta
				* 
				* TIPOS DE TARJETA (Campo at de pos_trans)
				* 1, 2, 3, 4, 5
				* 
				*/
				/* Validamos que si no hay Forma de Pago, siempre sera Forma de Pago Efectivo */
				if ($fpago == "1" || $fpago == "" || $fpago == NULL) {
					$fpago = "1";
				}				
				
				/*
				* En el documento original:
				* rendi_gln apunta al documento negativo(Nota de Crédito)
				* rendi_acu es NULL
				*
				* En el extorno(Nota de Crédito)
				* rendi_gln apunta al documento original
				* rendi_acu al documento resultante del extorno(nuevo documento)
				*
				* En el documento resultante del extorno:
				* rendi_gln es NULL
				* rendi_acu apunta al extorno(negativo)
				*
				*/
				/* Debe ser vacio para que se genere en los asientos, es decir solo se considera: documento resultante del extorno */
				if ( !empty($value['rendi_gln']) ) {
					continue;
				}
								
				/* Obtenemos información para tipos de asientos */
				if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_venta == "C") { 
						//Cuentas Combustibles Por Defecto
						$tipo_asiento = "COMBUSTIBLE";

						if ($codigo != "11620307") { //Cuentas Combustibles
							$tipo_asiento = "COMBUSTIBLE";

						} else if ($codigo == "11620307") { //Cuentas GLP
							$tipo_asiento = "GLP";

						}
					} else if ($tipo_venta == "M") { //Cuentas Market
						$tipo_asiento = "MARKET";

					}
				}

				/* Información para Detalle (act_entryline) */
				if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_pdf != 'A') { //Exclumos Nota de Credito
						if ($tipo_pdf != 'N') { //Excluimos Nota de Despacho
							/* Agrupamos por Tipo de Pago: Efectivo o Tipos de Tarjetas */
							if ($fpago == "1") { //Efectivo
								$result[$tipo_asiento]['DEBE']['fpago'][$fpago]['td'][$td]['total']    += $value["importe"];
								$result[$tipo_asiento]['DEBE']['fpago'][$fpago]['td'][$td]['detalle'][] = $value;
							} else if ($fpago == "2") { //Tarjeta
								$result[$tipo_asiento]['DEBE']['fpago'][$fpago]['td'][$td]['at'][$at]['total']    += $value["importe"];
								$result[$tipo_asiento]['DEBE']['fpago'][$fpago]['td'][$td]['at'][$at]['detalle'][] = $value;
							}
						
							//Agrupamos Boletas por Serie
							if ($td == 'B') {
								$result[$tipo_asiento]['HABER']['td'][$td]['serie'][$serie]['total']    += $value["importe"];						
								$result[$tipo_asiento]['HABER']['td'][$td]['serie'][$serie]['detalle'][] = $value;
							}

							//Detalle Facturas
							if ($td == 'F') {
								$result[$tipo_asiento]['HABER']['td'][$td]['total']    += $value["importe"];
								$result[$tipo_asiento]['HABER']['td'][$td]['detalle'][] = $value;
							}
						}				
					}
				}		
			}
		}

		if ($this->isDebug) {
			echo "<script>console.log('result')</script>";
			echo "<script>console.log('" . json_encode($result, JSON_FORCE_OBJECT) . "')</script>";
		}
		/************************************** END FORMATEAMOS ARRAY PARA GENERAR LOS ASIENTOS PARA CTA COBRAR **************************************/

		/* Array para almacenar asientos */
		$data_asientos = array();

		/* Generamos Asientos Cta Cobrar Playa (Combustibles y Market) */
		foreach ($result as $key => $value) {
			$tipo = $key;
			$total_cobro = 0;

			/* Obtenemos cuentas contables */
			if ($tipo == "COMBUSTIBLE") {
				$subdiario                 = $cuenta['ctacobrar_comb_subdiario'];
				$ctacobrar_12_cliente      = $cuenta['ctacobrar_comb_cliente_soles'];
				$ctacobrar_gasto_cobrotc_1 = $cuenta['comb_gasto_cobrotc_1'];
				$ctacobrar_gasto_cobrotc_2 = $cuenta['comb_gasto_cobrotc_2'];
				$ctacobrar_gasto_cobrotc_3 = $cuenta['comb_gasto_cobrotc_3'];
			} else if ($tipo == "GLP") {
				$subdiario                 = $cuenta['ctacobrar_glp_subdiario'];
				$ctacobrar_12_cliente      = $cuenta['ctacobrar_glp_cliente_soles'];
				$ctacobrar_gasto_cobrotc_1 = $cuenta['glp_gasto_cobrotc_1'];
				$ctacobrar_gasto_cobrotc_2 = $cuenta['glp_gasto_cobrotc_2'];
				$ctacobrar_gasto_cobrotc_3 = $cuenta['glp_gasto_cobrotc_3'];
			} else if ($tipo == "MARKET") {
				$subdiario                 = $cuenta['ctacobrar_mkt_subdiario'];
				$ctacobrar_12_cliente      = $cuenta['ctacobrar_mkt_cliente_soles'];
				$ctacobrar_gasto_cobrotc_1 = $cuenta['mkt_gasto_cobrotc_1'];
				$ctacobrar_gasto_cobrotc_2 = $cuenta['mkt_gasto_cobrotc_2'];
				$ctacobrar_gasto_cobrotc_3 = $cuenta['mkt_gasto_cobrotc_3'];
			}
			
			/* Obtenemos Tipo de Asiento Contable (act_entrytype), descripcion del asiento y tableid del asiento para insertar en Detalle (act_entryline) y Cabecera (act_entry) */
			if ($tipo == "COMBUSTIBLE") { //Asientos Cta. Cobrar Combustibles
				$act_entrytype_id = 4;
			} else if ($tipo == "GLP") { //Asientos Cta. Cobrar Combustibles
				$act_entrytype_id = 4;
			} else if ($tipo == "MARKET") { //Asientos Cta. Cobrar Market
				$act_entrytype_id = 5; 
			}

			$tableid         = 1; //Tabla pos_transXXXXYY
			$regid           = "-";
			$int_clientes_id = NULL;
			$tab_currency    = "01"; //Tabla "int_tabla_general", campo "tab_tabla" con valor "04"

			if ($tipo == "COMBUSTIBLE") { //Asientos Cta. Cobrar Combustibles
				$description = "Cta Cobrar Combustible";
			} else if ($tipo == "GLP") { //Asientos Cta. Cobrar Combustibles
				$description = "Cta Cobrar GLP";
			} else if ($tipo == "MARKET") { //Asientos Cta. Cobrar Market
				$description = "Cta Cobrar Market";
			}			

			/* Información para Detalle (act_entryline) */
			$act_entryline = array();

			//CUENTA EFECTIVO SOLES - TOTAL BOLETAS
			$importe = $value['DEBE']['fpago']['1']['td']['B']['total']; 
			$importe = isset($importe) ? $importe : "0.00";
			$act_entryline[] = array(
				"act_entry_id"   => NULL,
				"act_account_id" => $data_tarjetas['B']['0'],
				"amtdt"          => $importe, //DEBE
				"amtct"          => "0.00",
				"amtsourcedt"    => $importe,
				"amtsourcect"    => "0.00",
				"description"        => "CUENTA EFECTIVO SOLES - TOTAL BOLETAS",
				"tableid"            => $tableid,
				"regid"              => $regid,
				"int_clientes_id"    => $int_clientes_id,
				"c_cash_mpayment_id" => NULL,
				"tab_currency"       => $tab_currency,
			);

			//CUENTA EFECTIVO SOLES - TOTAL FACTURAS
			$importe = $value['DEBE']['fpago']['1']['td']['F']['total'];
			$importe = isset($importe) ? $importe : "0.00";
			$act_entryline[] = array(
				"act_entry_id"   => NULL,
				"act_account_id" => $data_tarjetas['F']['0'],
				"amtdt"          => $importe, //DEBE
				"amtct"          => "0.00",
				"amtsourcedt"    => $importe,
				"amtsourcect"    => "0.00",
				"description"        => "CUENTA EFECTIVO SOLES - TOTAL FACTURAS",
				"tableid"            => $tableid,
				"regid"              => $regid,
				"int_clientes_id"    => $int_clientes_id,
				"c_cash_mpayment_id" => NULL,
				"tab_currency"       => $tab_currency,
			);

			//CUENTAS TARJETAS SOLES - TOTAL BOLETAS
			$data_fpago2           = $value['DEBE']['fpago']['2']['td']['B']['at'];
			$data_tarjetas_boletas = $data_tarjetas['B'];
			foreach ($data_fpago2 as $keyat => $at) {
				$id_tarjeta = $keyat;
				$importe    = $at['total'];

				//CALCULAMOS EL COBRO POR EL USO DE TARJETA DE CREDITO
				$percentagedsct = $data_tarjetas_boletas[$id_tarjeta]['percentagedsct'];
				if ($percentagedsct > 0) {
					$importe_cobro = $importe * ($percentagedsct/100);
					$importe       = $importe - $importe_cobro;
					$total_cobro  += $importe_cobro;
				}

				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $data_tarjetas_boletas[$id_tarjeta],
					"amtdt"          => $importe, //DEBE
					"amtct"          => "0.00",
					"amtsourcedt"    => $importe,
					"amtsourcect"    => "0.00",
					"description"        => "CUENTA TARJETA SOLES - TOTAL BOLETAS - TARJETA $id_tarjeta: " . $data_tarjetas_boletas[$id_tarjeta]['description'],
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);
			}

			//CUENTAS TARJETAS SOLES - TOTAL FACTURAS
			$data_fpago2            = $value['DEBE']['fpago']['2']['td']['F']['at'];
			$data_tarjetas_facturas = $data_tarjetas['F'];
			foreach ($data_fpago2 as $keyat => $at) {
				$id_tarjeta = $keyat;
				$importe    = $at['total'];

				//CALCULAMOS EL COBRO POR EL USO DE TARJETA DE CREDITO
				$percentagedsct = $data_tarjetas_facturas[$id_tarjeta]['percentagedsct'];
				if ($percentagedsct > 0) {
					$importe_cobro = $importe * ($percentagedsct/100);
					$importe       = $importe - $importe_cobro;
					$total_cobro  += $importe_cobro;
				}

				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $data_tarjetas_facturas[$id_tarjeta],
					"amtdt"          => $importe, //DEBE
					"amtct"          => "0.00",
					"amtsourcedt"    => $importe,
					"amtsourcect"    => "0.00",
					"description"        => "CUENTA TARJETA SOLES - TOTAL FACTURAS - TARJETA $id_tarjeta: " . $data_tarjetas_boletas[$id_tarjeta]['description'],
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);
			}

			//CUENTAS COBRAR CLIENTE SOLES - BOLETAS AGRUPADAS POR SERIE
			$data_totales_series = $value['HABER']['td']['B']['serie'];
			foreach ($data_totales_series as $keyserie => $serie) {
				$importe = $serie['total'];
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $ctacobrar_12_cliente,
					"amtdt"          => "0.00",
					"amtct"          => $importe, //HABER
					"amtsourcedt"    => "0.00",
					"amtsourcect"    => $importe,
					"description"        => "CUENTAS COBRAR CLIENTE SOLES - BOLETAS AGRUPADAS POR SERIE - SERIE $keyserie",
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);
			}

			//CUENTAS COBRAR CLIENTE SOLES - FACTURAS DETALLADAS
			$data_detalle = $value['HABER']['td']['F']['detalle'];
			foreach ($data_detalle as $keydetalle => $detalle) {
				$importe = $detalle['importe'];
				$detalleserie        = $detalle["serie"];
				$detallenumero       = $detalle["numero"];
				$detalleregid        = $detalle["es"] ."*". $detalle["caja"] . "*" . $detalle["id_trans"] . "*" . $detalle["tabla"] . "*" . $detalle["usr"] . "*" . $detalle["tipo"];
				$detalleintclienteid = ( TRIM($detalle["ruc"]) == "" ) ? NULL : $detalle["ruc"];
				
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $ctacobrar_12_cliente,
					"amtdt"          => "0.00",
					"amtct"          => $importe, //HABER
					"amtsourcedt"    => "0.00",
					"amtsourcect"    => $importe,
					"description"        => "CUENTAS COBRAR CLIENTE SOLES - FACTURAS DETALLADAS - FACTURA $detalleserie-$detallenumero",
					"tableid"            => $tableid,
					"regid"              => $detalleregid,
					"int_clientes_id"    => $detalleintclienteid,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);
			}

			//GASTOS FINANCIEROS COBRO(%) TC
			$act_entryline[] = array(
				"act_entry_id"   => NULL,
					"act_account_id" => $ctacobrar_gasto_cobrotc_1,
					"amtdt"          => $total_cobro, //DEBE
					"amtct"          => "0.00",
					"amtsourcedt"    => $total_cobro, //DEBE
					"amtsourcect"    => "0.00",
					"description"        => "GASTOS FINANCIEROS COBRO(%) TC",
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
			);

			//GASTOS FINANCIEROS COBRO(%) TC
			$act_entryline[] = array(
				"act_entry_id"   => NULL,
					"act_account_id" => $ctacobrar_gasto_cobrotc_2,
					"amtdt"          => $total_cobro, //DEBE
					"amtct"          => "0.00",
					"amtsourcedt"    => $total_cobro, //DEBE
					"amtsourcect"    => "0.00",
					"description"        => "GASTOS FINANCIEROS COBRO(%) TC",
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
			);

			//CARGAS IMPUTABLES A CUENTA DE COSTO Y GASTO
			$act_entryline[] = array(
				"act_entry_id"   => NULL,
					"act_account_id" => $ctacobrar_gasto_cobrotc_3,
					"amtdt"          => "0.00",
					"amtct"          => $total_cobro, //HABER,
					"amtsourcedt"    => "0.00",
					"amtsourcect"    => $total_cobro, //HABER
					"description"        => "CARGAS IMPUTABLES A CUENTA DE COSTO Y GASTO",
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
			);

			// OBTENEMOS NUMERO DE ASIENTO
			$responseCorrelativo = $objHelper->getCorrelativoSubdiario($subdiario, $arrParams);
			if ($responseCorrelativo['error'] == TRUE) {
				return $responseCorrelativo;
			}				
			$correlativo = $responseCorrelativo['correlativo'];
			// END OBTENEMOS NUMERO DE ASIENTO

			/* Informacion para Cabecera (act_entry) */
			$data_asientos[] = array(
				"ch_sucursal"        => TRIM($arrParams['sCodeWarehouse']),
				"dateacct"           => TRIM($arrParams['dEntry']),
				"description"        => $description,
				"act_entrytype_id"   => $act_entrytype_id,
				"subbookcode"        => $subdiario,
				"registerno"         => $correlativo,
				"documentdate"       => TRIM($arrParams['dEntry']),
				"tableid"            => $tableid,
				"regid"              => $regid,
				"int_clientes_id"    => $int_clientes_id,
				"c_cash_mpayment_id" => NULL,
				"tab_currency"       => $tab_currency,
				"act_entryline"      => $act_entryline,		
			);
		}

		if ($this->isDebug) {
			echo "<script>console.log('data_asientos')</script>";
			echo "<script>console.log('" . json_encode($data_asientos, JSON_FORCE_OBJECT) . "')</script>";
		}

		return $this->executeInsert($data_asientos);
	}

	public function generarAsientosCtaCobrarPlaya_NC($data) {
		$objHelper = new HelperClass();
		if ($data['error']) {			
			$res = array(
				'error' => TRUE,
				'message' => $data['message'],
			);
			return $res;
		}
	
		/* Recogemos parametros */
		$arrParams = $data['arrParams'];
	
		/* Recogemos data */
		$data = $data['result'];
	
		global $sqlca;
	
		/* Obtenemos cuentas contables para Asientos Cta Cobrar Playa (Combustibles y Market) */
		$sql_cuentas_contables = "
		SELECT
			COALESCE(c01.act_config_id,'0') ||'*'|| COALESCE(c01.value,'0') AS ctacobrar_comb_subdiario
			,COALESCE(c02.act_config_id,'0') ||'*'|| COALESCE(c02.value,'0') AS ctacobrar_glp_subdiario
			,COALESCE(c03.act_config_id,'0') ||'*'|| COALESCE(c03.value,'0') AS ctacobrar_comb_cliente
			,COALESCE(c04.act_config_id,'0') ||'*'|| COALESCE(c04.value,'0') AS ctacobrar_comb_caja_efectivo
			,COALESCE(c05.act_config_id,'0') ||'*'|| COALESCE(c05.value,'0') AS ctacobrar_glp_cliente
			,COALESCE(c06.act_config_id,'0') ||'*'|| COALESCE(c06.value,'0') AS ctacobrar_glp_caja_efectivo

			,COALESCE(c07.act_config_id,'0') ||'*'|| COALESCE(c07.value,'0') AS ctacobrar_mkt_subdiario
			,COALESCE(c08.act_config_id,'0') ||'*'|| COALESCE(c08.value,'0') AS ctacobrar_mkt_cliente
			,COALESCE(c09.act_config_id,'0') ||'*'|| COALESCE(c09.value,'0') AS ctacobrar_mkt_caja_efectivo
		FROM 
			act_config c01	
			LEFT JOIN act_config c02 ON   c02.module = 4   AND c02.category = 0   AND c02.subcategory = 1   --Subdiario de Cta Cobrar GLP
			LEFT JOIN act_config c03 ON   c03.module = 4   AND c03.category = 1   AND c03.subcategory = 0   --Cuenta Cobrar Combustible Cliente - SOLES
			LEFT JOIN act_config c04 ON   c04.module = 4   AND c04.category = 1   AND c04.subcategory = 1   --Cuenta Cobrar Combustible Caja Efectivo - SOLES   --No se utiliza, ya que se utiliza lo de act_config_cash
			LEFT JOIN act_config c05 ON   c05.module = 4   AND c05.category = 2   AND c05.subcategory = 0   --Cuenta Cobrar GLP Cliente - SOLES
			LEFT JOIN act_config c06 ON   c06.module = 4   AND c06.category = 2   AND c06.subcategory = 1   --Cuenta Cobrar GLP Caja Efectivo - SOLES           --No se utiliza, ya que se utiliza lo de act_config_cash

			LEFT JOIN act_config c07 ON   c07.module = 5   AND c07.category = 0   AND c07.subcategory = 0   --Subdiario de Cta Cobrar Market
			LEFT JOIN act_config c08 ON   c08.module = 5   AND c08.category = 1   AND c08.subcategory = 0   --Cuenta Cobrar Market Cliente - SOLES
			LEFT JOIN act_config c09 ON   c09.module = 5   AND c09.category = 1   AND c09.subcategory = 1   --Cuenta Cobrar Market Caja Efectivo - SOLES        --No se utiliza, ya que se utiliza lo de act_config_cash
		WHERE   
			c01.module = 4   AND c01.category = 0   AND c01.subcategory = 0;   --Subdiario de Cta Cobrar Combustible
		";
	
		if ($sqlca->query($sql_cuentas_contables) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_cuentas_contables');
		}
	
		$a = $sqlca->fetchRow();
		$cuenta['ctacobrar_comb_subdiario']           = $objHelper->getCuentaContable($a[0]);
		$cuenta['ctacobrar_glp_subdiario']            = $objHelper->getCuentaContable($a[1]);
		$cuenta['ctacobrar_comb_cliente_soles']       = $objHelper->getCuentaContable($a[2]);
		$cuenta['ctacobrar_comb_caja_efectivo_soles'] = $objHelper->getCuentaContable($a[3]); //No se utiliza, ya que se utiliza lo de act_config_cash
		$cuenta['ctacobrar_glp_cliente_soles']        = $objHelper->getCuentaContable($a[4]);
		$cuenta['ctacobrar_glp_caja_efectivo_soles']  = $objHelper->getCuentaContable($a[5]); //No se utiliza, ya que se utiliza lo de act_config_cash
		
		$cuenta['ctacobrar_mkt_subdiario']            = $objHelper->getCuentaContable($a[6]);
		$cuenta['ctacobrar_mkt_cliente_soles']        = $objHelper->getCuentaContable($a[7]);
		$cuenta['ctacobrar_mkt_caja_efectivo_soles']  = $objHelper->getCuentaContable($a[8]); //No se utiliza, ya que se utiliza lo de act_config_cash
		
		if ($this->isDebug) {
			echo "<script>console.log('cuentas_contables')</script>";
			echo "<script>console.log('" . json_encode($cuenta, JSON_FORCE_OBJECT) . "')</script>";
		}

		/* Array para almacenar asientos */
		$data_asientos = array();
    
		/* Array para recorrer Ventas Playa (Combustibles y Market) */
		$tipoVenta = array("C", "M");
	
		/* Generamos Asientos Venta Playa (Combustibles y Market) */
		foreach ($tipoVenta as $key) {
			$tipo = $key;

        	foreach ($data[$tipo] as $key => $value) {
				/* Variables para realizar condicionales al generar asientos */
				$tipo_venta = TRIM($value['tipo_venta']);
				$codigo     = TRIM($value['codigo']);
				$tipo_pdf   = TRIM($value['tipo_pdf']);	
				$td         = TRIM($value['td']);
				$fpago      = TRIM($value['fpago']);
				$at         = TRIM($value['at']);	
				$serie      = TRIM($value['serie']);

				/*
				* En el documento original:
				* rendi_gln apunta al documento negativo(Nota de Crédito)
				* rendi_acu es NULL
				*
				* En el extorno(Nota de Crédito)
				* rendi_gln apunta al documento original
				* rendi_acu al documento resultante del extorno(nuevo documento)
				*
				* En el documento resultante del extorno:
				* rendi_gln es NULL
				* rendi_acu apunta al extorno(negativo)
				*
				*/
				/* Debe ser diferente de vacio para que se genere en los asientos, es decir solo se considera: documento original y extorno(Nota de Crédito) */
				if ( empty($value['rendi_gln']) ) {
					continue;
				}

				/* Obtenemos cuentas contables */
				if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_venta == "C") { 
						//Cuentas Combustibles Por Defecto
						$subdiario            = $cuenta['ctacobrar_comb_subdiario'];
						$ctacobrar_12_cliente = $cuenta['ctacobrar_comb_cliente_soles'];
	
						if ($codigo != "11620307") { //Cuentas Combustibles
							$subdiario            = $cuenta['ctacobrar_comb_subdiario'];
							$ctacobrar_12_cliente = $cuenta['ctacobrar_comb_cliente_soles'];
	
						} else if ($codigo == "11620307") { //Cuentas GLP
							$subdiario            = $cuenta['ctacobrar_glp_subdiario'];
							$ctacobrar_12_cliente = $cuenta['ctacobrar_glp_cliente_soles'];
	
						}
					} else if ($tipo_venta == "M") { //Cuentas Market
						$subdiario            = $cuenta['ctacobrar_mkt_subdiario'];
						$ctacobrar_12_cliente = $cuenta['ctacobrar_mkt_cliente_soles'];
	
					}
				}

				/* Obtenemos Tipo de Asiento Contable (act_entrytype), descripcion del asiento y tableid del asiento para insertar en Detalle (act_entryline) y Cabecera (act_entry) */
				if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_venta == "C") { //Asientos Ventas Playa Combustibles
						$act_entrytype_id = 4;
					} else if ($tipo_venta == "M") { //Asientos Ventas Playa Market
						$act_entrytype_id = 5;
					}										
	
					$tableid         = 1; //Tabla pos_transXXXXYY
					$regid           = $value["es"] ."*". $value["caja"] . "*" . $value["id_trans"] . "*" . $value["tabla"] . "*" . $value["usr"] . "*" . $value["tipo"];
					$int_clientes_id = ( TRIM($value["ruc"]) == "" ) ? NULL : $value["ruc"];
					$tab_currency    = "01"; //Tabla "int_tabla_general", campo "tab_tabla" con valor "04"

					if ($tipo_pdf == 'A') { //Nota de Credito
						$description = "Por Nota de Credito " . $value["serie"] . "-" . $value["numero"];
					} else { //Otros
						$description = "Por Documento " . $value["serie"] . "-" . $value["numero"];
					}					
				}

				/* Información para Detalle (act_entryline) */
				$act_entryline = array();				
            	if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_pdf == 'A') { //Nota de Credito
						$value["importe"]   = abs($value["importe"]);
						$value["igv"]       = abs($value["igv"]);
						$value["balance"]   = abs($value["balance"]);
						$value["imponible"] = abs($value["imponible"]);

						/* Obtenemos partes del parametro fecha */
						$porciones = explode("-", TRIM($arrParams['dEntry']));
						$anio      = $porciones[0];
						$mes       = $porciones[1];

						/* Obtenemos fecha postrans del mes anterior y mes posterior */
						$dataPosTrans = $objHelper->getPosTransAnteriorDespues($anio, $mes);
						/***/
						
						$arrParamsPOST = array(
							"sTablePostransYM" => 'pos_trans'.$anio.$mes,
							"sCodigoAlmacen" => TRIM($arrParams['sCodeWarehouse']),
							"sTablePostransYM_Ant" => 'pos_trans'.$dataPosTrans['anio_ant'].$dataPosTrans['mes_ant'],
							"sTablePostransYM_Des" => 'pos_trans'.$dataPosTrans['anio_des'].$dataPosTrans['mes_des'],
							"sStatusPostransYM_Ant" => $dataPosTrans['status_table_postrans_ant'],
							"sStatusPostransYM_Des" => $dataPosTrans['status_table_postrans_des'],
						);

						require("/sistemaweb/ventas_clientes/reportes/m_registro_ventas.php");
						$modelRegistroVentas = new RegistroVentasModel();

						if( $value['rendi_gln'] != "" ) {
							$arrData = array(
								//Datos para buscar registros
								"sNombreTabla" => $arrParamsPOST['sTablePostransYM'],
								"sCodigoAlmacen" => $arrParamsPOST['sCodigoAlmacen'],
								"sNombreTabla_Ant" => $arrParamsPOST['sTablePostransYM_Ant'],
								"sNombreTabla_Des" => $arrParamsPOST['sTablePostransYM_Des'],
								"sStatusTabla_Ant" => $arrParamsPOST['sStatusPostransYM_Ant'],
								"sStatusTabla_Des" => $arrParamsPOST['sStatusPostransYM_Des'],
								//Datos para buscar documento origen
								"sCaja" => $value['caja'],
								"sTipoDocumento" => $value['td'],
								"fIDTrans" => $value['rendi_gln'],
								"iNumeroDocumentoIdentidad" => $value['ruc'],
							);
							$arrResponseModel = $modelRegistroVentas->verify_reference_sales_invoice_document($arrData);
							$sSerieNumeroReferencia = "";
							if ($arrResponseModel["sStatus"] == "success") {
								$sSerieNumeroReferencia = $arrResponseModel["arrDataModel"]["usr"];
								
								//Datos adicionales
								$importeReferencia         = $arrResponseModel["arrDataModel"]["importe"];
								$descripcionReferencia     = "Por Documento de Referencia $sSerieNumeroReferencia";
								$regidReferencia           = $arrResponseModel["arrDataModel"]["es"] ."*". $arrResponseModel["arrDataModel"]["caja"] ."*". $arrResponseModel["arrDataModel"]["id_trans"] ."*". $arrResponseModel["arrDataModel"]["tabla"] ."*". $arrResponseModel["arrDataModel"]["usr"] ."*". $arrResponseModel["arrDataModel"]["tiporef"];
								$int_clientes_idReferencia = $arrResponseModel["arrDataModel"]["ruc"];
							}
						}
						
						//CUENTA DOCUMENTO ORIGINAL
						$act_entryline[] = array(
                            "act_entry_id"   => NULL,
                            "act_account_id" => $ctacobrar_12_cliente,
                            "amtdt"          => $importeReferencia, //DEBE
                            "amtct"          => "0.00",
                            "amtsourcedt"    => $importeReferencia, //DEBE
                            "amtsourcect"    => "0.00",
							"description"        => $descripcionReferencia,
							"tableid"            => $tableid,
							"regid"              => $regidReferencia,
							"int_clientes_id"    => $int_clientes_idReferencia,
							"c_cash_mpayment_id" => NULL,
							"tab_currency"       => $tab_currency,
                        );

						//CUENTA NOTA DE CREDITO
                        $act_entryline[] = array(
                            "act_entry_id"   => NULL,
                            "act_account_id" => $ctacobrar_12_cliente,
                            "amtdt"          => "0.00",
                            "amtct"          => $value["importe"], //HABER
                            "amtsourcedt"    => "0.00",
                            "amtsourcect"    => $value["importe"], //HABER
							"description"        => $description,
							"tableid"            => $tableid,
							"regid"              => $regid,
							"int_clientes_id"    => $int_clientes_id,
							"c_cash_mpayment_id" => NULL,
							"tab_currency"       => $tab_currency,
                        );
					}
				}

				/* Informacion para Cabecera (act_entry) */
				if ($tipo_venta == "C" || $tipo_venta == "M") { //COMBUSTIBLE O MARKET
					if ($tipo_pdf == "A") { //Nota de Credito

						// OBTENEMOS NUMERO DE ASIENTO
						$responseCorrelativo = $objHelper->getCorrelativoSubdiario($subdiario, $arrParams);
						if ($responseCorrelativo['error'] == TRUE) {
							return $responseCorrelativo;
						}		
						$correlativo = $responseCorrelativo['correlativo'];
						// END OBTENEMOS NUMERO DE ASIENTO

						$data_asientos[] = array(
							"ch_sucursal"        => $value["es"],
							"dateacct"           => $value["operativa"],
							"description"        => $description,
							"act_entrytype_id"   => $act_entrytype_id,
							"subbookcode"        => $subdiario,
							"registerno"         => $correlativo,
							"documentdate"       => $value["emision"],
							"tableid"            => $tableid,
							"regid"              => $regid,
							"int_clientes_id"    => $int_clientes_id,
							"c_cash_mpayment_id" => NULL,
							"tab_currency"       => $tab_currency,
							"act_entryline"      => $act_entryline,	
						);

					}
				}
			}
		}

		if ($this->isDebug) {
			echo "<script>console.log('data_asientos')</script>";
			echo "<script>console.log('" . json_encode($data_asientos, JSON_FORCE_OBJECT) . "')</script>";
		}
		
		return $this->executeInsert($data_asientos);
	}

	public function obtenerSobrantesFaltantes($arrParams) {
		$objHelper = new HelperClass();	

		global $sqlca;

		/* Recogemos parametros */
		$almacen = TRIM($arrParams['sCodeWarehouse']);
		$fecha   = TRIM($arrParams['dEntry']);

		//CONSULTA PARA OBTENER SOBRANTES Y FALTANTES
		$sql_sobrantes_faltantes = "
			SELECT
				id_diferencia_trabajador,
				es,
				ch_codigo_trabajador,
				dia,
				turno,
				flag,
				importe
			FROM
				comb_diferencia_trabajador
			WHERE
				es = '$almacen'
				AND dia = '$fecha'
		";

		if ($sqlca->query($sql_sobrantes_faltantes) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_sobrantes_faltantes');
		}

		/* Recorremos informacion de Sobrantes y Faltantes */
		$correlativo = 0;
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
	
			$result['sobfal'][$correlativo]['id_diferencia_trabajador']	= $a['id_diferencia_trabajador'];
			$result['sobfal'][$correlativo]['es']	                    = $a['es'];
			$result['sobfal'][$correlativo]['ch_codigo_trabajador']	    = $a['ch_codigo_trabajador'];
			$result['sobfal'][$correlativo]['dia']	                    = $a['dia'];
			$result['sobfal'][$correlativo]['turno']	                = $a['turno'];
			$result['sobfal'][$correlativo]['flag']	                    = $a['flag'];
			$result['sobfal'][$correlativo]['importe']	                = $a['importe'];
			$result['sobfal']['total']['importe']                      += $a['importe'];

			$correlativo++;
		}

		if ($this->isDebug) {
			echo "<script>console.log('result')</script>";
			echo "<script>console.log('" . json_encode($result, JSON_FORCE_OBJECT) . "')</script>";
		}		
	
		return array(
			'error' => FALSE,
			'result' => $result,
			'arrParams' => $arrParams,
		);
	}

	public function generarAsientosSobrantesFaltantes($data) {
		$objHelper = new HelperClass();
		if ($data['error']) {			
			$res = array(
				'error' => TRUE,
				'message' => $data['message'],
			);
			return $res;
		}

		/* Recogemos parametros */
		$arrParams = $data['arrParams'];

		/* Recogemos data */
		$data = $data['result'];

		global $sqlca;

		/* Obtenemos cuentas contables para Asientos Sobrantes y Faltantes */
		$sql_cuentas_contables = "
		SELECT
			 COALESCE(c01.act_config_id,'0') ||'*'|| COALESCE(c01.value,'0') AS subdiario
			,COALESCE(c02.act_config_id,'0') ||'*'|| COALESCE(c02.value,'0') AS efectivo
			,COALESCE(c03.act_config_id,'0') ||'*'|| COALESCE(c03.value,'0') AS otros_ingresos
			,COALESCE(c04.act_config_id,'0') ||'*'|| COALESCE(c04.value,'0') AS entregas_trabajador			
		FROM 
			act_config c01
			LEFT JOIN act_config c02 ON   c02.module = 9   AND c02.category = 1   AND c02.subcategory = 0   --Efectivo Sobrantes y Faltantes
			LEFT JOIN act_config c03 ON   c03.module = 9   AND c03.category = 1   AND c03.subcategory = 1   --Otros Ingresos
			LEFT JOIN act_config c04 ON   c04.module = 9   AND c04.category = 1   AND c04.subcategory = 2   --Entregas a Trabajador
		WHERE   
			c01.module = 9   AND c01.category = 0   AND c01.subcategory = 1;   --Subdiario de Sobrantes y Faltantes
		";
	
		if ($sqlca->query($sql_cuentas_contables) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_cuentas_contables');
		}

		$a = $sqlca->fetchRow();
		$cuenta['cnf']['subdiario']              = $objHelper->getCuentaContable($a[0]);
		$cuenta['sobfal']['efectivo']            = $objHelper->getCuentaContable($a[1]);
		$cuenta['sobfal']['otros_ingresos']      = $objHelper->getCuentaContable($a[2]);
		$cuenta['sobfal']['entregas_trabajador'] = $objHelper->getCuentaContable($a[3]);

		if ($this->isDebug) {
			echo "<script>console.log('cuentas_contables')</script>";
			echo "<script>console.log('" . json_encode($cuenta, JSON_FORCE_OBJECT) . "')</script>";
		}

		/* Array para almacenar asientos */
		$data_asientos = array();

		/* Obtenemos cuentas contables */
		$subdiario                  = $cuenta['cnf']['subdiario'];		
		//Sobrantes		
		$cuenta_efectivo            = $cuenta['sobfal']['efectivo'];
		$cuenta_otros_ingresos      = $cuenta['sobfal']['otros_ingresos'];		
		//Faltantes
		$cuenta_efectivo            = $cuenta['sobfal']['efectivo'];
		$cuenta_entregas_trabajador = $cuenta['sobfal']['entregas_trabajador'];

		/* Obtenemos Tipo de Asiento Contable (act_entrytype), descripcion del asiento y tableid del asiento para insertar en Detalle (act_entryline) y Cabecera (act_entry) */
        $act_entrytype_id = 9;
		$tableid          = 0;
		$regid            = "-";	
		$int_clientes_id  = NULL;
		$tab_currency     = "01"; //Tabla "int_tabla_general", campo "tab_tabla" con valor "04"
		$description      = "Por Reconocimiento de Sobrantes y Faltantes";

		/* Información para Detalle (act_entryline) */
		$total_importe = $data['sobfal']['total']['importe'];
		if ($total_importe >= 0) {
			//CUENTA EFECTIVO
			$act_entryline[] = array(
				"act_entry_id"   => NULL,
				"act_account_id" => $cuenta_efectivo,
				"amtdt"          => $total_importe, //DEBE
				"amtct"          => "0.00",
				"amtsourcedt"    => $total_importe, //DEBE
				"amtsourcect"    => "0.00",
				"description"        => $description,
				"tableid"            => $tableid,
				"regid"              => $regid,
				"int_clientes_id"    => $int_clientes_id,
				"c_cash_mpayment_id" => NULL,
				"tab_currency"       => $tab_currency,
			);

			//CUENTA OTROS INGRESOS
            $act_entryline[] = array(
                "act_entry_id"   => NULL,
                "act_account_id" => $cuenta_otros_ingresos,
                "amtdt"          => "0.00",
                "amtct"          => $total_importe, //HABER
                "amtsourcedt"    => "0.00",
                "amtsourcect"    => $total_importe, //HABER
                "description"        => $description,
                "tableid"            => $tableid,
                "regid"              => $regid,
                "int_clientes_id"    => $int_clientes_id,
                "c_cash_mpayment_id" => NULL,
                "tab_currency"       => $tab_currency,
            );
		} else {
			$total_importe = ABS($total_importe);
			//CUENTA EFECTIVO
			$act_entryline[] = array(
				"act_entry_id"   => NULL,
				"act_account_id" => $cuenta_efectivo,
				"amtdt"          => "0.00",
				"amtct"          => $total_importe, //HABER
				"amtsourcedt"    => "0.00",
				"amtsourcect"    => $total_importe, //HABER
				"description"        => $description,
				"tableid"            => $tableid,
				"regid"              => $regid,
				"int_clientes_id"    => $int_clientes_id,
				"c_cash_mpayment_id" => NULL,
				"tab_currency"       => $tab_currency,
			);

			//CUENTA ENTREGAS A TRABAJADOR
            $act_entryline[] = array(
                "act_entry_id"   => NULL,
                "act_account_id" => $cuenta_entregas_trabajador,
                "amtdt"          => $total_importe, //DEBE
                "amtct"          => "0.00",
                "amtsourcedt"    => $total_importe, //DEBE
                "amtsourcect"    => "0.00",
                "description"        => $description,
                "tableid"            => $tableid,
                "regid"              => $regid,
                "int_clientes_id"    => $int_clientes_id,
                "c_cash_mpayment_id" => NULL,
                "tab_currency"       => $tab_currency,
            );
		}

		// OBTENEMOS NUMERO DE ASIENTO
        $responseCorrelativo = $objHelper->getCorrelativoSubdiario($subdiario, $arrParams);
        if ($responseCorrelativo['error'] == TRUE) {
            return $responseCorrelativo;
        }				
        $correlativo = $responseCorrelativo['correlativo'];
        // END OBTENEMOS NUMERO DE ASIENTO

		/* Informacion para Cabecera (act_entry) */
        $data_asientos[] = array(
            "ch_sucursal"        => TRIM($arrParams['sCodeWarehouse']),
            "dateacct"           => TRIM($arrParams['dEntry']),
            "description"        => $description,
            "act_entrytype_id"   => $act_entrytype_id,
            "subbookcode"        => $subdiario,
            "registerno"         => $correlativo,
            "documentdate"       => TRIM($arrParams['dEntry']),
            "tableid"            => $tableid,
            "regid"              => $regid,
            "int_clientes_id"    => $int_clientes_id,
            "c_cash_mpayment_id" => NULL,
            "tab_currency"       => $tab_currency,
            "act_entryline"      => $act_entryline,
        );

		if ($this->isDebug) {
			echo "<script>console.log('data_asientos')</script>";
			echo "<script>console.log('" . json_encode($data_asientos, JSON_FORCE_OBJECT) . "')</script>";
		}
	
		return $this->executeInsert($data_asientos);
	}

	public function obtenerRedondeoEfectivo($arrParams) {
		$objHelper = new HelperClass();	

		global $sqlca;
	
		/* Recogemos parametros */
		$almacen = TRIM($arrParams['sCodeWarehouse']);
		$fecha   = TRIM($arrParams['dEntry']);

		/* Obtenemos partes del parametro fecha */
		$porciones = explode("-", $fecha);
		$anio      = $porciones[0];
		$mes       = $porciones[1];
		$desde     = $porciones[2];
		$hasta     = $porciones[2];

		/* Obtenemos fechas para usar en queries */
		$result 			= Array();
		$fecha_postrans 	= $anio . "" . $mes;
		$fecha_inicial 		= $anio . "-" . $mes . "-" . $desde;
		$fecha_final 		= $anio . "-" . $mes . "-" . $hasta;

		//CONSULTA PARA OBTENER TOTAL REDONDEO EFECTIVO
		//Nota: Query sacada de /sistemaweb/combustibles/liquidacion_ventas_diarias.php
		$sql_redondeo_efectivo = "
			SELECT 
				sum(x) as total
			FROM 
				(SELECT 
					round((((first(t.soles_km)*100)%10)/100),2) AS x 
				FROM 
					pos_trans" . $fecha_postrans . " AS t
				WHERE 
					t.es='" . pg_escape_string($almacen) . "'
					AND t.td IN ('B','F')
					AND t.fpago = '1' 
					AND t.dia BETWEEN '" . pg_escape_string($fecha_inicial) . "' AND '" . pg_escape_string($fecha_final) . "' 
				GROUP BY 
					t.caja,t.trans) x;
		";

		if ($sqlca->query($sql_redondeo_efectivo) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_redondeo_efectivo');
		}

		/* Recorremos informacion de Redondeo Efectivo */
		$a = $sqlca->fetchRow();
		$result['redondeoefe']['total']['importe'] = $a['total'];

		if ($this->isDebug) {
			echo "<script>console.log('result')</script>";
			echo "<script>console.log('" . json_encode($result, JSON_FORCE_OBJECT) . "')</script>";
		}		
	
		return array(
			'error' => FALSE,
			'result' => $result,
			'arrParams' => $arrParams,
		);
	}
	
	public function generarAsientosRedondeoEfectivo($data) {
		$objHelper = new HelperClass();
		if ($data['error']) {			
			$res = array(
				'error' => TRUE,
				'message' => $data['message'],
			);
			return $res;
		}

		/* Recogemos parametros */
		$arrParams = $data['arrParams'];

		/* Recogemos data */
		$data = $data['result'];

		global $sqlca;

		/* Obtenemos cuentas contables para Asientos Redondeo Efectivo */
		$sql_cuentas_contables = "
		SELECT
			 COALESCE(c01.act_config_id,'0') ||'*'|| COALESCE(c01.value,'0') AS subdiario
			,COALESCE(c02.act_config_id,'0') ||'*'|| COALESCE(c02.value,'0') AS efectivo_redondeo_faltante
			,COALESCE(c03.act_config_id,'0') ||'*'|| COALESCE(c03.value,'0') AS perdida_por_redondeo
			,COALESCE(c04.act_config_id,'0') ||'*'|| COALESCE(c04.value,'0') AS gasto_administrativo			
			,COALESCE(c05.act_config_id,'0') ||'*'|| COALESCE(c05.value,'0') AS cargas_imputables_costo_gasto	
		FROM 
			act_config c01
			LEFT JOIN act_config c02 ON   c02.module = 10   AND c02.category = 1   AND c02.subcategory = 0   --Efectivo Redondeo Faltante
			LEFT JOIN act_config c03 ON   c03.module = 10   AND c03.category = 1   AND c03.subcategory = 1   --Perdida por Redondeo
			LEFT JOIN act_config c04 ON   c04.module = 10   AND c04.category = 1   AND c04.subcategory = 2   --Gasto Administrativo
			LEFT JOIN act_config c05 ON   c05.module = 10   AND c05.category = 1   AND c05.subcategory = 3   --Cargas imputables a cuenta de costo y gasto
		WHERE   
			c01.module = 10   AND c01.category = 0   AND c01.subcategory = 1;   --Subdiario de Redondeo Efectivo
		";
	
		if ($sqlca->query($sql_cuentas_contables) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_cuentas_contables');
		}
	
		$a = $sqlca->fetchRow();
		$cuenta['cnf']['subdiario']                             = $objHelper->getCuentaContable($a[0]);
		$cuenta['redondeoefe']['efectivo_redondeo_faltante']    = $objHelper->getCuentaContable($a[1]);
		$cuenta['redondeoefe']['perdida_por_redondeo']          = $objHelper->getCuentaContable($a[2]);
		$cuenta['redondeoefe']['gasto_administrativo']          = $objHelper->getCuentaContable($a[3]);
		$cuenta['redondeoefe']['cargas_imputables_costo_gasto'] = $objHelper->getCuentaContable($a[4]);

		if ($this->isDebug) {
			echo "<script>console.log('cuentas_contables')</script>";
			echo "<script>console.log('" . json_encode($cuenta, JSON_FORCE_OBJECT) . "')</script>";
		}

		/* Array para almacenar asientos */
		$data_asientos = array();

		/* Obtenemos cuentas contables */
		$subdiario                         = $cuenta['cnf']['subdiario'];		
		//Faltante		
		$cta_efectivo_redondeo_faltante    = $cuenta['redondeoefe']['efectivo_redondeo_faltante'];
		$cta_perdida_por_redondeo          = $cuenta['redondeoefe']['perdida_por_redondeo'];				
		$cta_gasto_administrativo          = $cuenta['redondeoefe']['gasto_administrativo'];
		$cta_cargas_imputables_costo_gasto = $cuenta['redondeoefe']['cargas_imputables_costo_gasto'];
		
		/* Obtenemos Tipo de Asiento Contable (act_entrytype), descripcion del asiento y tableid del asiento para insertar en Detalle (act_entryline) y Cabecera (act_entry) */
		$act_entrytype_id = 10;
		$tableid          = 0;
		$regid            = "-";	
		$int_clientes_id  = NULL;
		$tab_currency     = "01"; //Tabla "int_tabla_general", campo "tab_tabla" con valor "04"
		$description      = "Por Reconocimiento de Redondeo Efectivo Faltante";

		/* Información para Detalle (act_entryline) */
		$total_importe = $data['redondeoefe']['total']['importe'];
		//CUENTA EFECTIVO REDONDEO FALTANTE
		$act_entryline[] = array(
            "act_entry_id"   => NULL,
            "act_account_id" => $cta_efectivo_redondeo_faltante,
            "amtdt"          => "0.00",
            "amtct"          => $total_importe, //HABER
            "amtsourcedt"    => "0.00",
            "amtsourcect"    => $total_importe, //HABER
            "description"        => $description,
            "tableid"            => $tableid,
            "regid"              => $regid,
            "int_clientes_id"    => $int_clientes_id,
            "c_cash_mpayment_id" => NULL,
            "tab_currency"       => $tab_currency,
        );

		//CUENTA PERDIDA POR REDONDEO
		$act_entryline[] = array(
            "act_entry_id"   => NULL,
            "act_account_id" => $cta_perdida_por_redondeo,
            "amtdt"          => $total_importe, //DEBE
            "amtct"          => "0.00",
            "amtsourcedt"    => $total_importe, //DEBE
            "amtsourcect"    => "0.00",
            "description"        => $description,
            "tableid"            => $tableid,
            "regid"              => $regid,
            "int_clientes_id"    => $int_clientes_id,
            "c_cash_mpayment_id" => NULL,
            "tab_currency"       => $tab_currency,
        );

		//CUENTA GASTO ADMINISTRATIVO
		$act_entryline[] = array(
            "act_entry_id"   => NULL,
            "act_account_id" => $cta_gasto_administrativo,
            "amtdt"          => $total_importe, //DEBE
            "amtct"          => "0.00",
            "amtsourcedt"    => $total_importe, //DEBE
            "amtsourcect"    => "0.00",
            "description"        => $description,
            "tableid"            => $tableid,
            "regid"              => $regid,
            "int_clientes_id"    => $int_clientes_id,
            "c_cash_mpayment_id" => NULL,
            "tab_currency"       => $tab_currency,
        );

		//CUENTA CARGAS IMPUTABLES A CUENTA DE COSTO Y GASTO		 
		$act_entryline[] = array(
            "act_entry_id"   => NULL,
            "act_account_id" => $cta_cargas_imputables_costo_gasto,
            "amtdt"          => "0.00",
            "amtct"          => $total_importe, //HABER
            "amtsourcedt"    => "0.00",
            "amtsourcect"    => $total_importe, //HABER
            "description"        => $description,
            "tableid"            => $tableid,
            "regid"              => $regid,
            "int_clientes_id"    => $int_clientes_id,
            "c_cash_mpayment_id" => NULL,
            "tab_currency"       => $tab_currency,
        );

		// OBTENEMOS NUMERO DE ASIENTO
		$responseCorrelativo = $objHelper->getCorrelativoSubdiario($subdiario, $arrParams);
		if ($responseCorrelativo['error'] == TRUE) {
			return $responseCorrelativo;
		}				
		$correlativo = $responseCorrelativo['correlativo'];
		// END OBTENEMOS NUMERO DE ASIENTO

		/* Informacion para Cabecera (act_entry) */
		$data_asientos[] = array(
			"ch_sucursal"        => TRIM($arrParams['sCodeWarehouse']),
			"dateacct"           => TRIM($arrParams['dEntry']),
			"description"        => $description,
			"act_entrytype_id"   => $act_entrytype_id,
			"subbookcode"        => $subdiario,
			"registerno"         => $correlativo,
			"documentdate"       => TRIM($arrParams['dEntry']),
			"tableid"            => $tableid,
			"regid"              => $regid,
			"int_clientes_id"    => $int_clientes_id,
			"c_cash_mpayment_id" => NULL,
			"tab_currency"       => $tab_currency,
			"act_entryline"      => $act_entryline,
		);
	
		if ($this->isDebug) {
			echo "<script>console.log('data_asientos')</script>";
			echo "<script>console.log('" . json_encode($data_asientos, JSON_FORCE_OBJECT) . "')</script>";
		}
	
		return $this->executeInsert($data_asientos);
	}

	public function obtenerCompras($arrParams) {
		global $sqlca;

		/* Recogemos parametros */
		$almacen = TRIM($arrParams['sCodeWarehouse']);
		$fecha   = TRIM($arrParams['dEntry']);

		/* Obtenemos partes del parametro fecha */
		$porciones = explode("-", $fecha);
		$anio      = $porciones[0];
		$mes       = $porciones[1];
		$desde     = $porciones[2];
		$hasta     = $porciones[2];

		/* Obtenemos fechas para usar en queries */
		$result 			= Array();
		$fecha_inicial 		= $anio . "-" . $mes . "-" . $desde;
		$fecha_final 		= $anio . "-" . $mes . "-" . $hasta;
		$correlativo 		= 0;

		//FUNCIONALIDAD PARA RECORRER UNO A UNO LOS REGISTROS DE COMPRAS Y GENERAR ASIENTOS, DE MODO QUE REEMPLAZAREMOS QUERY CON UNIONS
		$sql_compras = "
			SELECT
				TO_CHAR(DATE(c.pro_cab_fechaemision),'YYMMDD') as dia,
				''::text as DCUENTA,
				c.pro_codigo::text as pro,
				c.pro_cab_numdocumento::text as trans,
				'1'::text as tip,
				'H'::text as ddh,	

				-- round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_imptotal ELSE c.pro_cab_imptotal + pro_cab_impinafecto END), 2) as importe_total,	
				-- round(FIRST(c.pro_cab_impto1), 2) as importe_igv,	
				-- round(FIRST(CASE WHEN pro_cab_impinafecto IS NULL THEN c.pro_cab_impafecto ELSE c.pro_cab_impafecto + pro_cab_impinafecto END), 2) as importe_bi,	
				round(FIRST(COALESCE(c.pro_cab_imptotal,0)), 2) as importe_total,	
				round(FIRST(COALESCE(c.pro_cab_impto1,0)), 2) as importe_impuesto,	
				round(FIRST(COALESCE(c.pro_cab_impafecto,0)), 2) as importe_bi,	
				round(FIRST(COALESCE(c.pro_cab_impinafecto,0)), 2) as importe_inafecto,	
				round(FIRST(COALESCE(c.regc_sunat_percepcion,0)), 2) as importe_percepcion,	
				FIRST(TC.tca_venta_oficial) as tipo_cambio,
			
				'COMPRA '|| rubro.ch_descripcion_breve::text as venta,
				c.pro_cab_almacen as sucursal,
				c.pro_cab_seriedocumento|| '-' ||c.pro_cab_numdocumento::text as dnumdoc,
				'08'::TEXT AS subdiario,
				''::text as DCENCOS,
				'C'::text as tip2,
				c.pro_cab_tipdocumento::TEXT AS nutd,			
				rubro.ch_descripcion_breve::text as tipo_compra,
				FIRST(MOVI.exist) as tipo_compra_comb,
				FIRST(MOVI2.exist) as tipo_compra_glp,
				
				(CASE WHEN c.pro_cab_tipdocumento='35' then '03' else
				(CASE WHEN c.pro_cab_tipdocumento='10' then '01' else 
				(CASE WHEN c.pro_cab_tipdocumento='20' then '07' else '08' end) end)
				END) AS tipo_documento,

				FIRST(c.pro_cab_almacen) as pro_cab_almacen,
				FIRST(c.pro_cab_tipdocumento) as pro_cab_tipdocumento,
				FIRST(c.pro_cab_seriedocumento) as pro_cab_seriedocumento,
				FIRST(c.pro_cab_numdocumento) as pro_cab_numdocumento,
				FIRST(c.pro_codigo) as pro_codigo,
				FIRST(c.pro_cab_moneda) as pro_cab_moneda,
				DATE(FIRST(c.pro_cab_fechaemision)) as emision,
				DATE(FIRST(c.pro_cab_fechaemision)) as operativa
			FROM
				cpag_ta_cabecera c
				INNER JOIN cpag_ta_detalle d ON (c.pro_cab_tipdocumento = d.pro_cab_tipdocumento AND c.pro_cab_seriedocumento = d.pro_cab_seriedocumento AND c.pro_cab_numdocumento = d.pro_cab_numdocumento AND c.pro_codigo = d.pro_codigo)
				LEFT JOIN cpag_ta_rubros rubro ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)			
				--LEFT JOIN inv_movialma AS MOVI ON (c.pro_cab_almacen = MOVI.mov_almacen AND c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe AND c.pro_codigo = MOVI.mov_entidad)
				LEFT JOIN (
					SELECT 1 AS exist, *
					FROM   inv_movialma MOV1 
					WHERE  MOV1.art_codigo IN (SELECT TRIM(ch_codigocombustible) FROM comb_ta_combustibles WHERE ch_codigocombustible != '11620307')
				) AS MOVI ON (c.pro_cab_almacen = MOVI.mov_almacen AND c.pro_cab_tipdocumento = MOVI.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI.mov_docurefe AND c.pro_codigo = MOVI.mov_entidad)
				LEFT JOIN (
					SELECT 1 AS exist, *
					FROM   inv_movialma MOV2 
					WHERE  TRIM(MOV2.art_codigo) = '11620307'
				) AS MOVI2 ON (c.pro_cab_almacen = MOVI2.mov_almacen AND c.pro_cab_tipdocumento = MOVI2.mov_tipdocuref AND c.pro_cab_seriedocumento || '' || c.pro_cab_numdocumento = MOVI2.mov_docurefe AND c.pro_codigo = MOVI2.mov_entidad)
				LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = c.pro_cab_fechaemision)
			WHERE
				c.pro_cab_almacen = '$almacen'
				AND date(c.pro_cab_fechaemision) BETWEEN '$fecha_inicial' AND '$fecha_final' --TODO: fecha
				--AND DATE(c.pro_cab_fechaemision) BETWEEN '2020-04-01' AND '2020-05-31'
			GROUP BY
				dia,
				pro,
				subdiario,
				c.pro_cab_almacen,
				trans,
				c.pro_cab_seriedocumento,
				rubro.ch_descripcion_breve,
				c.pro_cab_tipdocumento
			ORDER BY
				dia, trans, pro, ddh DESC;
    	";

		echo "<pre>sql_compras:";
		echo "$sql_compras";
		echo "</pre>";

		if ($sqlca->query($sql_compras) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_compras');
		}

		/* Recorremos informacion de Compras */
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$result['compra'][$correlativo]['dia']	                  = $a['dia'];
			$result['compra'][$correlativo]['pro']	                  = $a['pro'];
			$result['compra'][$correlativo]['trans']	              = $a['trans'];
			$result['compra'][$correlativo]['importe_total']	      = $a['importe_total'];
			$result['compra'][$correlativo]['importe_impuesto']	      = $a['importe_impuesto'];
			$result['compra'][$correlativo]['importe_bi']	          = $a['importe_bi'];
			$result['compra'][$correlativo]['importe_inafecto']	      = $a['importe_inafecto'];
			$result['compra'][$correlativo]['importe_percepcion']     = $a['importe_percepcion'];
			$result['compra'][$correlativo]['tipo_cambio']			  = $a['tipo_cambio'];
			$result['compra'][$correlativo]['venta']	              = $a['venta'];
			$result['compra'][$correlativo]['sucursal']	              = $a['sucursal'];
			$result['compra'][$correlativo]['dnumdoc']	              = $a['dnumdoc'];
			$result['compra'][$correlativo]['nutd']	                  = $a['nutd'];
			$result['compra'][$correlativo]['tipo_compra']	          = TRIM($a['tipo_compra']);
			$result['compra'][$correlativo]['tipo_compra_comb']	      = TRIM($a['tipo_compra_comb']);
			$result['compra'][$correlativo]['tipo_compra_glp']	      = TRIM($a['tipo_compra_glp']);
			$result['compra'][$correlativo]['tipo_documento']	      = TRIM($a['tipo_documento']);
			$result['compra'][$correlativo]['pro_cab_almacen']	      = TRIM($a['pro_cab_almacen']);
			$result['compra'][$correlativo]['pro_cab_tipdocumento']	  = TRIM($a['pro_cab_tipdocumento']);
			$result['compra'][$correlativo]['pro_cab_seriedocumento'] = TRIM($a['pro_cab_seriedocumento']);
			$result['compra'][$correlativo]['pro_cab_numdocumento']	  = TRIM($a['pro_cab_numdocumento']);
			$result['compra'][$correlativo]['pro_codigo']	          = TRIM($a['pro_codigo']);
			$result['compra'][$correlativo]['pro_cab_moneda']	      = TRIM($a['pro_cab_moneda']);
			$result['compra'][$correlativo]['emision']	              = TRIM($a['emision']);
			$result['compra'][$correlativo]['operativa']	          = TRIM($a['operativa']);

			$correlativo++;
		}

		if ($this->isDebug) {
			echo "<script>console.log('result')</script>";
			echo "<script>console.log('" . json_encode($result, JSON_FORCE_OBJECT) . "')</script>";
		}		

		return array(
			'error' => FALSE,
			'result' => $result,
			'arrParams' => $arrParams,
		);
	}

	public function generarAsientosCompras($data) {
		$objHelper = new HelperClass();
		if ($data['error']) {			
			$res = array(
				'error' => TRUE,
				'message' => $data['message'],
			);
			return $res;
		}
		
		/* Recogemos parametros */
		$arrParams = $data['arrParams'];
		
		/* Recogemos data */
		$data = $data['result'];

		global $sqlca;

		/* Obtenemos cuentas contables para Asientos Compras */
		$sql_cuentas_contables = "
		SELECT
			 COALESCE(c01.act_config_id,'0') ||'*'|| COALESCE(c01.value,'0') AS compra_comb_subdiario
			,COALESCE(c02.act_config_id,'0') ||'*'|| COALESCE(c02.value,'0') AS compra_glp_subdiario
			,COALESCE(c03.act_config_id,'0') ||'*'|| COALESCE(c03.value,'0') AS compra_mkt_subdiario

			,COALESCE(c04.act_config_id,'0') ||'*'|| COALESCE(c04.value,'0') AS compra_comb_ctapagar_soles
			,COALESCE(c05.act_config_id,'0') ||'*'|| COALESCE(c05.value,'0') AS compra_comb_ctapagar_dolares
			,COALESCE(c06.act_config_id,'0') ||'*'|| COALESCE(c06.value,'0') AS compra_comb_impuesto
			,COALESCE(c07.act_config_id,'0') ||'*'|| COALESCE(c07.value,'0') AS compra_comb_inafecto
			,COALESCE(c08.act_config_id,'0') ||'*'|| COALESCE(c08.value,'0') AS compra_comb_percepcion

			,COALESCE(c09.act_config_id,'0') ||'*'|| COALESCE(c09.value,'0') AS compra_glp_ctapagar_soles
			,COALESCE(c10.act_config_id,'0') ||'*'|| COALESCE(c10.value,'0') AS compra_glp_ctapagar_dolares
			,COALESCE(c11.act_config_id,'0') ||'*'|| COALESCE(c11.value,'0') AS compra_glp_impuesto
			,COALESCE(c12.act_config_id,'0') ||'*'|| COALESCE(c12.value,'0') AS compra_glp_inafecto
			,COALESCE(c13.act_config_id,'0') ||'*'|| COALESCE(c13.value,'0') AS compra_glp_percepcion

			,COALESCE(c14.act_config_id,'0') ||'*'|| COALESCE(c14.value,'0') AS compra_mkt_ctapagar_soles
			,COALESCE(c15.act_config_id,'0') ||'*'|| COALESCE(c15.value,'0') AS compra_mkt_ctapagar_dolares
			,COALESCE(c16.act_config_id,'0') ||'*'|| COALESCE(c16.value,'0') AS compra_mkt_impuesto
			,COALESCE(c17.act_config_id,'0') ||'*'|| COALESCE(c17.value,'0') AS compra_mkt_impuesto
			,COALESCE(c18.act_config_id,'0') ||'*'|| COALESCE(c18.value,'0') AS compra_mkt_percepcion
		FROM 
			act_config c01
			LEFT JOIN act_config c02 ON   c02.module = 7   AND c02.category = 0   AND c02.subcategory = 1   --Subdiario de Compra GLP
			LEFT JOIN act_config c03 ON   c03.module = 7   AND c03.category = 0   AND c03.subcategory = 2   --Subdiario de Compra Market

			LEFT JOIN act_config c04 ON   c04.module = 7   AND c04.category = 1   AND c04.subcategory = 0   --Cuenta Combustible Cta. Pagar - SOLES
			LEFT JOIN act_config c05 ON   c05.module = 7   AND c05.category = 1   AND c05.subcategory = 1   --Cuenta Combustible Cta. Pagar - DOLARES
			LEFT JOIN act_config c06 ON   c06.module = 7   AND c06.category = 1   AND c06.subcategory = 2   --Cuenta Combustible Impuesto
			LEFT JOIN act_config c07 ON   c07.module = 7   AND c07.category = 1   AND c07.subcategory = 3   --Cuenta Combustible Inafecto
			LEFT JOIN act_config c08 ON   c08.module = 7   AND c08.category = 1   AND c08.subcategory = 4   --Cuenta Combustible Percepcion

			LEFT JOIN act_config c09 ON   c09.module = 7   AND c09.category = 2   AND c09.subcategory = 0   --Cuenta GLP Cta. Pagar - SOLES
			LEFT JOIN act_config c10 ON   c10.module = 7   AND c10.category = 2   AND c10.subcategory = 1   --Cuenta GLP Cta. Pagar - DOLARES
			LEFT JOIN act_config c11 ON   c11.module = 7   AND c11.category = 2   AND c11.subcategory = 2   --Cuenta GLP Impuesto
			LEFT JOIN act_config c12 ON   c12.module = 7   AND c12.category = 2   AND c12.subcategory = 3   --Cuenta GLP Inafecto
			LEFT JOIN act_config c13 ON   c13.module = 7   AND c13.category = 2   AND c13.subcategory = 4   --Cuenta GLP Percepcion

			LEFT JOIN act_config c14 ON   c14.module = 7   AND c14.category = 3   AND c14.subcategory = 0   --Cuenta Market Cta. Pagar - SOLES
			LEFT JOIN act_config c15 ON   c15.module = 7   AND c15.category = 3   AND c15.subcategory = 1   --Cuenta Market Cta. Pagar - DOLARES
			LEFT JOIN act_config c16 ON   c16.module = 7   AND c16.category = 3   AND c16.subcategory = 2   --Cuenta Market Impuesto
			LEFT JOIN act_config c17 ON   c17.module = 7   AND c17.category = 3   AND c17.subcategory = 3   --Cuenta Market Inafecto
			LEFT JOIN act_config c18 ON   c18.module = 7   AND c18.category = 3   AND c18.subcategory = 4   --Cuenta Market Percepcion			
		WHERE   
			c01.module = 7   AND c01.category = 0   AND c01.subcategory = 0;   --Subdiario de Compra Combustible
		";

		if ($sqlca->query($sql_cuentas_contables) < 0) {
			return array('error' => TRUE, 'message' => 'Error en sql_cuentas_contables');
		}

		$a = $sqlca->fetchRow();
		$cuenta['cnf']['compra_comb_subdiario']        = $objHelper->getCuentaContable($a[0]);
		$cuenta['cnf']['compra_glp_subdiario']         = $objHelper->getCuentaContable($a[1]);
		$cuenta['cnf']['compra_mkt_subdiario']         = $objHelper->getCuentaContable($a[2]);
		
		$cuenta['comb']['compra_comb_ctapagar_soles']   = $objHelper->getCuentaContable($a[3]);
		$cuenta['comb']['compra_comb_ctapagar_dolares'] = $objHelper->getCuentaContable($a[4]);
		$cuenta['comb']['compra_comb_impuesto']         = $objHelper->getCuentaContable($a[5]);
		$cuenta['comb']['compra_comb_inafecto']         = $objHelper->getCuentaContable($a[6]);
		$cuenta['comb']['compra_comb_percepcion']       = $objHelper->getCuentaContable($a[7]);
		
		$cuenta['glp']['compra_glp_ctapagar_soles']    = $objHelper->getCuentaContable($a[8]);
		$cuenta['glp']['compra_glp_ctapagar_dolares']  = $objHelper->getCuentaContable($a[9]);
		$cuenta['glp']['compra_glp_impuesto']          = $objHelper->getCuentaContable($a[10]);
		$cuenta['glp']['compra_glp_inafecto']          = $objHelper->getCuentaContable($a[11]);
		$cuenta['glp']['compra_glp_percepcion']        = $objHelper->getCuentaContable($a[12]);
		
		$cuenta['mkt']['compra_mkt_ctapagar_soles']    = $objHelper->getCuentaContable($a[13]);
		$cuenta['mkt']['compra_mkt_ctapagar_dolares']  = $objHelper->getCuentaContable($a[14]);
		$cuenta['mkt']['compra_mkt_impuesto']          = $objHelper->getCuentaContable($a[15]);
		$cuenta['mkt']['compra_mkt_inafecto']          = $objHelper->getCuentaContable($a[16]);
		$cuenta['mkt']['compra_mkt_percepcion']        = $objHelper->getCuentaContable($a[17]);

		if ($this->isDebug) {
			echo "<script>console.log('cuentas_contables')</script>";
			echo "<script>console.log('" . json_encode($cuenta, JSON_FORCE_OBJECT) . "')</script>";
		}

		/* Array para almacenar asientos */
		$data_asientos = array();

		/* Recorremos uno a uno las compras para generar los Asientos Compras */		
		foreach ($data['compra'] as $key => $value) {
			/* Variables para realizar condicionales al generar asientos */
			$es_tipo = TRIM($value['tipo_compra']);
			$tipo    = TRIM($value['tipo_documento']);
			$moneda  = TRIM($value['pro_cab_moneda']);

			/* Obtenemos cuentas contables */
			if ( TRIM($es_tipo) == "COMBUSTIBLES" ) {		
				//COMBUSTIBLES POR DEFECTO
				$subdiario         = $cuenta['cnf']['compra_comb_subdiario'];
				$cuenta_total      = ($moneda == '02') ? $cuenta['comb']['compra_comb_ctapagar_dolares'] : $cuenta['comb']['compra_comb_ctapagar_soles'];
				$cuenta_impuesto   = $cuenta['comb']['compra_comb_impuesto'];
				$cuenta_inafecto   = $cuenta['comb']['compra_comb_inafecto'];
				$cuenta_percepcion = $cuenta['comb']['compra_comb_percepcion'];
				$cuenta_bi         = "";
				$cuenta_69         = "";
				$cuenta_20         = "";
				$subdiario         = $cuenta['cnf']['compra_comb_subdiario'];

				if ( $value['tipo_compra_comb'] == 1 ) { //COMBUSTIBLE LIQUIDO
					$subdiario         = $cuenta['cnf']['compra_comb_subdiario'];
					$cuenta_total      = ($moneda == '02') ? $cuenta['comb']['compra_comb_ctapagar_dolares'] : $cuenta['comb']['compra_comb_ctapagar_soles'];
					$cuenta_impuesto   = $cuenta['comb']['compra_comb_impuesto'];
					$cuenta_inafecto   = $cuenta['comb']['compra_comb_inafecto'];
					$cuenta_percepcion = $cuenta['comb']['compra_comb_percepcion'];
					$cuenta_bi         = "";
					$cuenta_69         = "";
					$cuenta_20         = "";
					$subdiario         = $cuenta['cnf']['compra_comb_subdiario'];
				} else if ( $value['tipo_compra_glp'] == 1 ) { //GLP
					$subdiario         = $cuenta['cnf']['compra_glp_subdiario'];
					$cuenta_total      = ($moneda == '02') ? $cuenta['glp']['compra_glp_ctapagar_dolares'] : $cuenta['glp']['compra_glp_ctapagar_soles'];
					$cuenta_impuesto   = $cuenta['glp']['compra_glp_impuesto'];
					$cuenta_inafecto   = $cuenta['glp']['compra_glp_inafecto'];
					$cuenta_percepcion = $cuenta['glp']['compra_glp_percepcion'];
					$cuenta_bi         = "";
					$cuenta_69         = "";
					$cuenta_20         = "";
					$subdiario         = $cuenta['cnf']['compra_glp_subdiario'];
				}
			} else if ( TRIM($es_tipo) == "MARKET" ) { //MARKET
				$subdiario         = $cuenta['cnf']['compra_mkt_subdiario'];
				$cuenta_total      = ($moneda == '02') ? $cuenta['mkt']['compra_mkt_ctapagar_dolares'] : $cuenta['mkt']['compra_mkt_ctapagar_soles'];
				$cuenta_impuesto   = $cuenta['mkt']['compra_mkt_impuesto'];
				$cuenta_impuesto   = $cuenta['mkt']['compra_mkt_inafecto'];
				$cuenta_percepcion = $cuenta['mkt']['compra_mkt_percepcion'];
				$cuenta_bi         = $objHelper->getCuentaContablePersonalizada(array("tipo" => "M", "module" => "7", "category" => "3", "subcategory" => "5", "account_order" => "1", "art_linea" => "ALL"));
				$cuenta_69         = $objHelper->getCuentaContablePersonalizada(array("tipo" => "M", "module" => "7", "category" => "3", "subcategory" => "5", "account_order" => "2", "art_linea" => "ALL"));
				$cuenta_20         = $objHelper->getCuentaContablePersonalizada(array("tipo" => "M", "module" => "7", "category" => "3", "subcategory" => "5", "account_order" => "3", "art_linea" => "ALL"));
				$subdiario         = $cuenta['cnf']['compra_mkt_subdiario'];
			} else { //OTROS
				$subdiario         = $cuenta['cnf']['compra_mkt_subdiario'];
				$cuenta_total      = ($moneda == '02') ? $cuenta['mkt']['compra_mkt_ctapagar_dolares'] : $cuenta['mkt']['compra_mkt_ctapagar_soles'];
				$cuenta_impuesto   = $cuenta['mkt']['compra_mkt_impuesto'];
				$cuenta_impuesto   = $cuenta['mkt']['compra_mkt_inafecto'];
				$cuenta_percepcion = $cuenta['mkt']['compra_mkt_percepcion'];
				$cuenta_bi         = $objHelper->getCuentaContablePersonalizada(array("tipo" => "M", "module" => "7", "category" => "3", "subcategory" => "5", "account_order" => "1", "art_linea" => "ALL"));
				$cuenta_69         = $objHelper->getCuentaContablePersonalizada(array("tipo" => "M", "module" => "7", "category" => "3", "subcategory" => "5", "account_order" => "2", "art_linea" => "ALL"));
				$cuenta_20         = $objHelper->getCuentaContablePersonalizada(array("tipo" => "M", "module" => "7", "category" => "3", "subcategory" => "5", "account_order" => "3", "art_linea" => "ALL"));
				$subdiario         = $cuenta['cnf']['compra_mkt_subdiario'];
			}

			/* Obtenemos información para detalle de asientos */
			/* Si es NC convertimos importes en negativo a positivo. Si no es NC mantenemos los importes iguales */
			if ($tipo=='07'){//7=N/Crédito
				$importe    = round(abs($value['importe_impuesto']) + abs($value['importe_bi']) + abs($value['importe_inafecto']) + abs($value['importe_percepcion']),2);
				$igv        = abs($value['importe_impuesto']);
				$inafecto   = abs($value['importe_inafecto']);
				$percepcion = abs($value['importe_percepcion']);
				$imponible  = abs($value['importe_bi']);
				$tipocambio = abs($value['tipo_cambio']);
			}else{
				$importe    = round($value['importe_impuesto'] + $value['importe_bi'] + $value['importe_inafecto'] + $value['importe_percepcion'],2);
				$igv        = $value['importe_impuesto'];
				$inafecto   = $value['importe_inafecto'];
				$percepcion = $value['importe_percepcion'];
				$imponible  = $value['importe_bi'] + $value['importe_inafecto'];
				$tipocambio = $value['tipo_cambio'];
			}

			/* Si moneda es DOLARES calculamos importe real. Si moneda es SOLES importe e importe origen son los mismos */
			if ($moneda=='02'){//02 = DOLARES
				$importe           = $importe*$tipocambio;
				$igv	           = $igv*$tipocambio;
				$inafecto          = $inafecto*$tipocambio;
				$percepcion        = $percepcion*$tipocambio;
				$imponible         = $imponible*$tipocambio;

				$importe_origen    = $importe;
				$igv_origen	       = $igv;
				$inafecto_origen   = $inafecto*$tipocambio;
				$percepcion_origen = $percepcion;
				$imponible_origen  = $imponible;
			}else{//01 = SOLES
				$importe           = $importe;
				$igv	           = $igv;
				$inafecto		   = $inafecto;
				$percepcion        = $percepcion;
				$imponible         = $imponible;

				$importe_origen    = $importe;
				$igv_origen	       = $igv;
				$inafecto          = $inafecto;
				$percepcion_origen = $percepcion;
				$imponible_origen  = $imponible;
			}

			/* Obtenemos Tipo de Asiento Contable (act_entrytype), descripcion del asiento y tableid del asiento para insertar en Detalle (act_entryline) y Cabecera (act_entry) */
			$act_entrytype_id = 7;
			
			$tableid         = 3;
			$regid           = $value["pro_cab_almacen"] ."*". $value["pro_cab_tipdocumento"] . "*" . $value["pro_cab_seriedocumento"] . "*" . $value["pro_cab_numdocumento"] . "*" . $value["pro_codigo"] . "*" . $value["tipo_documento"];
			$int_clientes_id = ( TRIM($value["pro_codigo"]) == "" ) ? NULL : $value["pro_codigo"];
			$tab_currency    = ( $moneda == "02" ) ? "02" : "01"; //Tabla "int_tabla_general", campo "tab_tabla" con valor "04"
			

			if ($tipo == "01") { //Factura
				$description = "Por Factura de Compra " . $value["pro_cab_seriedocumento"] . "-" . $value["pro_cab_numdocumento"];
			} else if ($tipo == "03") { //Boleta
				$description = "Por Boleta de Compra " . $value["pro_cab_seriedocumento"] . "-" . $value["pro_cab_numdocumento"];
			} else if ($tipo == '07') { //Nota de Credito
				$description = "Por Nota de Credito " . $value["pro_cab_seriedocumento"] . "-" . $value["pro_cab_numdocumento"];
			} else if ($tipo == '08') { //Nota de Debito
				$description = "Por Nota de Debito " . $value["pro_cab_seriedocumento"] . "-" . $value["pro_cab_numdocumento"];
			} else { //Otros
				$description = "Por Documento " . $value["pro_cab_seriedocumento"] . "-" . $value["pro_cab_numdocumento"];
			}

			/* Información para Detalle (act_entryline) */
			$act_entryline = array();			
			if ($tipo=='07'){//7=N/Crédito
				//CUENTA TOTAL
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_total,
					"amtdt"          => $importe, //DEBE
					"amtct"          => "0.00",
					"amtsourcedt"    => $importe_origen, //DEBE
					"amtsourcect"    => "0.00",
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);
	
				//CUENTA IMPUESTO
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_impuesto,
					"amtdt"          => "0.00",
					"amtct"          => $igv, //HABER
					"amtsourcedt"    => "0.00",
					"amtsourcect"    => $igv_origen, //HABER
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);

				//CUENTA INAFECTO (SOLO SI EXISTE)
				if ( $percepcion > 0 ) {
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_inafecto,
						"amtdt"          => "0.00",
						"amtct"          => $inafecto, //HABER
						"amtsourcedt"    => "0.00",
						"amtsourcect"    => $inafecto_origen, //HABER
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
					);
				}

				//CUENTA PERCEPCION (SOLO SI EXISTE)
				if ( $percepcion > 0 ) {
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_percepcion,
						"amtdt"          => "0.00",
						"amtct"          => $percepcion, //HABER
						"amtsourcedt"    => "0.00",
						"amtsourcect"    => $percepcion_origen, //HABER
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
					);
				}
			}else{
				//CUENTA TOTAL
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_total,
					"amtdt"          => "0.00",
					"amtct"          => $importe, //HABER
					"amtsourcedt"    => "0.00",
					"amtsourcect"    => $importe_origen, //HABER
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);
	
				//CUENTA IMPUESTO
				$act_entryline[] = array(
					"act_entry_id"   => NULL,
					"act_account_id" => $cuenta_impuesto,
					"amtdt"          => $igv, //DEBE
					"amtct"          => "0.00",
					"amtsourcedt"    => $igv_origen, //DEBE
					"amtsourcect"    => "0.00",
					"description"        => $description,
					"tableid"            => $tableid,
					"regid"              => $regid,
					"int_clientes_id"    => $int_clientes_id,
					"c_cash_mpayment_id" => NULL,
					"tab_currency"       => $tab_currency,
				);

				//CUENTA INAFECTO (SOLO SI EXISTE)
				if ( $inafecto > 0 ) {
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_inafecto,
						"amtdt"          => $inafecto, //DEBE
						"amtct"          => "0.00",
						"amtsourcedt"    => $inafecto_origen, //DEBE
						"amtsourcect"    => "0.00",
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
					);
				}
				
				//CUENTA PERCEPCION (SOLO SI EXISTE)
				if ( $percepcion > 0 ) {
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_percepcion,
						"amtdt"          => $percepcion, //DEBE
						"amtct"          => "0.00",
						"amtsourcedt"    => $percepcion_origen, //DEBE
						"amtsourcect"    => "0.00",
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
					);
				}
			}

			//DATA PARA DESGLOSE
			$pro_cab_almacen        = $value['pro_cab_almacen'];        //ALMACEN
			$pro_cab_tipdocumento   = $value['pro_cab_tipdocumento'];   //TIPO DOCUMENTO
			$pro_cab_seriedocumento = $value['pro_cab_seriedocumento']; //SERIE
			$pro_cab_numdocumento   = $value['pro_cab_numdocumento'];   //NUMERO
			$pro_codigo             = $value['pro_codigo'];             //CODIGO DE CLIENTE
			$es_desglose            = ( TRIM($es_tipo) == "COMBUSTIBLES" ) ? TRUE : FALSE; //SI ES COMBUSTIBLE O GLP, LA BASE IMPONIBLE SE DESGLOSA
			
			//SI ES COMBUSTIBLE O GLP, LA BASE IMPONIBLE SE DESGLOSA
			$asientos_sin_desglose = FALSE;
			if ( $es_desglose == TRUE ) {
				$sql_desglose = "
					SELECT 
						TRIM(MOVI.art_codigo) as art_codigo,
						SUM(MOVI.mov_costototal) as mov_costototal
					FROM
						inv_movialma as MOVI 
					WHERE
						'".TRIM($pro_cab_almacen)."' = MOVI.mov_almacen AND '".TRIM($pro_cab_tipdocumento)."' = MOVI.mov_tipdocuref AND '".TRIM($pro_cab_seriedocumento)."' || '' || '".TRIM($pro_cab_numdocumento)."' = MOVI.mov_docurefe AND '".TRIM($pro_codigo)."' = MOVI.mov_entidad
					GROUP BY 
						MOVI.art_codigo;
				";
				echo "<pre>";		
				echo "COMPRAS COMBUSTIBLE - DESGLOSE BI: \n\n".$sql_desglose."\n\n";
				echo "</pre>";

				if ($sqlca->query($sql_desglose)>0) {
					while ($regdes = $sqlca->fetchRow()) {
						/* Obtenemos cuentas contables */
						if ( $regdes['art_codigo'] == '11620307' ) {
							$cuenta_bi = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "7", "category" => "2", "subcategory" => "5", "account_order" => "1", "art_codigo" => $regdes['art_codigo']));
							$cuenta_69 = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "7", "category" => "2", "subcategory" => "5", "account_order" => "2", "art_codigo" => $regdes['art_codigo']));
							$cuenta_20 = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "7", "category" => "2", "subcategory" => "5", "account_order" => "3", "art_codigo" => $regdes['art_codigo']));
						} else if ( $regdes['art_codigo'] == '11620301' || $regdes['art_codigo'] == '11620302' || $regdes['art_codigo'] == '11620303' ||
									$regdes['art_codigo'] == '11620304' || $regdes['art_codigo'] == '11620305' || $regdes['art_codigo'] == '11620306' ) {
							$cuenta_bi = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "7", "category" => "1", "subcategory" => "5", "account_order" => "1", "art_codigo" => $regdes['art_codigo']));
							$cuenta_69 = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "7", "category" => "1", "subcategory" => "5", "account_order" => "2", "art_codigo" => $regdes['art_codigo']));
							$cuenta_20 = $objHelper->getCuentaContablePersonalizada(array("tipo" => "C", "module" => "7", "category" => "1", "subcategory" => "5", "account_order" => "3", "art_codigo" => $regdes['art_codigo']));
						}		
						
						/* Obtenemos información para detalle de asientos */
						/* Si es NC convertimos importes en negativo a positivo. Si no es NC mantenemos los importes iguales */
						if ($tipo=='07'){//7=N/Crédito
							$imponible_ = abs($regdes['mov_costototal']);
						}else{
							$imponible_ = $regdes['mov_costototal'];
						}

						/* Si moneda es DOLARES calculamos importe real. Si moneda es SOLES importe e importe origen son los mismos */
						if ($moneda=='02'){//02 = DOLARES							
							$imponible_        = $imponible_*$tipocambio;
							$imponible_origen_ = $imponible_;							
						}else{//01 = SOLES							
							$imponible_        = $imponible_;
							$imponible_origen_ = $imponible_;							
						}

						if ($tipo=='07'){//7=N/Crédito
							//CUENTA BI
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_bi,
								"amtdt"          => "0.00", 
								"amtct"          => $imponible_, //HABER
								"amtsourcedt"    => "0.00",
								"amtsourcect"    => $imponible_origen_, //HABER
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,
							);

							//COSTO DE COMPRA
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_69,
								"amtdt"          => $imponible_, //DEBE
								"amtct"          => "0.00",
								"amtsourcedt"    => $imponible_origen_, //DEBE       
								"amtsourcect"    => "0.00",
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,
							);                    

							//INGRESO DE MERCADERIA
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_20,
								"amtdt"          => "0.00",
								"amtct"          => $imponible_, //HABER
								"amtsourcedt"    => "0.00", 
								"amtsourcect"    => $imponible_origen_, //HABER
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,
							);
						} else {
							//CUENTA BI
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_bi,
								"amtdt"          => $imponible_, //DEBE
								"amtct"          => "0.00",
								"amtsourcedt"    => $imponible_origen_, //DEBE
								"amtsourcect"    => "0.00",					
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,			
							);

							//COSTO DE COMPRA
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_69,
								"amtdt"          => "0.00",
								"amtct"          => $imponible_, //HABER
								"amtsourcedt"    => "0.00", 
								"amtsourcect"    => $imponible_origen_, //HABER
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,
							);

							//INGRESO DE MERCADERIA
							$act_entryline[] = array(
								"act_entry_id"   => NULL,
								"act_account_id" => $cuenta_20,
								"amtdt"          => $imponible_, //DEBE
								"amtct"          => "0.00",
								"amtsourcedt"    => $imponible_origen_, //DEBE       
								"amtsourcect"    => "0.00",
								"description"        => $description,
								"tableid"            => $tableid,
								"regid"              => $regid,
								"int_clientes_id"    => $int_clientes_id,
								"c_cash_mpayment_id" => NULL,
								"tab_currency"       => $tab_currency,
							);
						}
					}
				} else {
					$asientos_sin_desglose = TRUE;
				}
			} else {
				$asientos_sin_desglose = TRUE;
			}
						
			//SI ES MARKET U OTROS, NO HAY DESGLOSE
			if ( $asientos_sin_desglose == TRUE ) {
				if ($tipo=='07'){//7=N/Crédito
					//CUENTA BI
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_bi,
						"amtdt"          => "0.00", 
						"amtct"          => $imponible, //HABER
						"amtsourcedt"    => "0.00",
						"amtsourcect"    => $imponible_origen, //HABER
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
					);

					//COSTO DE COMPRA
					$act_entryline[] = array(
                        "act_entry_id"   => NULL,
                        "act_account_id" => $cuenta_69,
                        "amtdt"          => $imponible, //DEBE
                        "amtct"          => "0.00",
                        "amtsourcedt"    => $imponible_origen, //DEBE       
                        "amtsourcect"    => "0.00",
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
                    );                    

                    //INGRESO DE MERCADERIA
					$act_entryline[] = array(
                        "act_entry_id"   => NULL,
                        "act_account_id" => $cuenta_20,
                        "amtdt"          => "0.00",
                        "amtct"          => $imponible, //HABER
                        "amtsourcedt"    => "0.00", 
                        "amtsourcect"    => $imponible_origen, //HABER
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
                    );
				} else {
					//CUENTA BI
					$act_entryline[] = array(
						"act_entry_id"   => NULL,
						"act_account_id" => $cuenta_bi,
						"amtdt"          => $imponible, //DEBE
						"amtct"          => "0.00",
						"amtsourcedt"    => $imponible_origen, //DEBE
						"amtsourcect"    => "0.00",	
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,				
					);

					//COSTO DE COMPRA
                    $act_entryline[] = array(
                        "act_entry_id"   => NULL,
                        "act_account_id" => $cuenta_69,
                        "amtdt"          => "0.00",
                        "amtct"          => $imponible, //HABER
                        "amtsourcedt"    => "0.00", 
                        "amtsourcect"    => $imponible_origen, //HABER
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
                    );

                    //INGRESO DE MERCADERIA
                    $act_entryline[] = array(
                        "act_entry_id"   => NULL,
                        "act_account_id" => $cuenta_20,
                        "amtdt"          => $imponible, //DEBE
                        "amtct"          => "0.00",
                        "amtsourcedt"    => $imponible_origen, //DEBE       
                        "amtsourcect"    => "0.00",
						"description"        => $description,
						"tableid"            => $tableid,
						"regid"              => $regid,
						"int_clientes_id"    => $int_clientes_id,
						"c_cash_mpayment_id" => NULL,
						"tab_currency"       => $tab_currency,
                    );
				}
			}

			// OBTENEMOS NUMERO DE ASIENTO
			$responseCorrelativo = $objHelper->getCorrelativoSubdiario($subdiario, $arrParams);
			if ($responseCorrelativo['error'] == TRUE) {
				return $responseCorrelativo;
			}				
			$correlativo = $responseCorrelativo['correlativo'];
			// END OBTENEMOS NUMERO DE ASIENTO

			/* Informacion para Cabecera (act_entry) */
			$data_asientos[] = array(
				"ch_sucursal"        => $value["sucursal"],
				"dateacct"           => $value["operativa"],
				"description"        => $description,
				"act_entrytype_id"   => $act_entrytype_id,
				"subbookcode"        => $subdiario,
				"registerno"         => $correlativo,
				"documentdate"       => $value["emision"],
				"tableid"            => $tableid,
				"regid"              => $value["pro_cab_almacen"] ."*". $value["pro_cab_tipdocumento"] . "*" . $value["pro_cab_seriedocumento"] . "*" . $value["pro_cab_numdocumento"] . "*" . $value["pro_codigo"],
				"int_clientes_id"    => $int_clientes_id,
				"c_cash_mpayment_id" => NULL,
				"tab_currency"       => $tab_currency,
				"act_entryline"      => $act_entryline,
			);
		}

		if ($this->isDebug) {
			echo "<script>console.log('data_asientos')</script>";
			echo "<script>console.log('" . json_encode($data_asientos, JSON_FORCE_OBJECT) . "')</script>";
		}

		return $this->executeInsert($data_asientos);
	}	

	function generarBalance($arrParams) {
		global $sqlca;

		/* Recogemos parametros */
		$almacen = TRIM($arrParams['sCodeWarehouse']);
		$fecha   = TRIM($arrParams['dEntry']);

		/* Obtenemos partes del parametro fecha */
		$porciones = explode("-", $fecha);
		$anio      = $porciones[0];
		$mes       = $porciones[1];		

		/* Obtenemos fechas para usar en queries */
		$result        = Array();
		$fecha_mes     = $anio . "-" . $mes;
		$fecha_balance = $anio . "-" . $mes . "-" . "01";

		//ELIMINAMOS BALANCE DE TODO EL MES
		$sql_eliminar_balance = "DELETE FROM act_balance WHERE TRIM(ch_sucursal) = '$almacen' AND TO_CHAR(DATE(dateacct),'YYYY-MM') = '$fecha_mes'";

		//VERIFICAMOS QUE ELIMINACION SE REALIZO CORRECTAMENTE
		$iStatus = $sqlca->query($sql_eliminar_balance);
		
		if ((int)$iStatus >= 0) {
			//GENERAMOS BALANCE DE TODO EL MES
			$sql_generar_balance = "
				SELECT
					e.ch_sucursal, 
					a.act_account_id,
					el.tab_currency,
					SUM(el.amtdt) AS amtdt, 
					SUM(el.amtct) AS amtct, 
					SUM(el.amtsourcedt) AS amtsourcedt,
					SUM(el.amtsourcect) AS amtsourcect
				FROM
					act_entryline el
					LEFT JOIN act_entry   AS e ON (el.act_entry_id   = e.act_entry_id)
					LEFT JOIN act_account AS a ON (el.act_account_id = a.act_account_id)
				WHERE
					1 = 1
					AND TRIM(e.ch_sucursal) = '". $almacen . "'
					AND TO_CHAR(DATE(e.documentdate),'YYYY-MM') = '" . $fecha_mes . "'
				GROUP BY
					e.ch_sucursal, a.act_account_id, el.tab_currency
				ORDER BY 
					1,2,3;
			";

			echo "<pre>sql_facturas_manuales:";
			echo "$sql_generar_balance";
			echo "</pre>";

			if ($sqlca->query($sql_generar_balance) < 0) {
				return array('error' => TRUE, 'message' => 'Error en sql_generar_balance');
			}

			//RECORREMOS INFORMACION DE BALANCE
			for ($i = 0; $i < $sqlca->numrows(); $i++) {
				$a = $sqlca->fetchRow();
			
				$result[$i]['ch_sucursal']    = $a['ch_sucursal'];
				$result[$i]['act_account_id'] = $a['act_account_id'];
				$result[$i]['tab_currency']   = $a['tab_currency'];
				$result[$i]['amtdt']          = $a['amtdt'];
				$result[$i]['amtct']          = $a['amtct'];
				$result[$i]['amtsourcedt']    = $a['amtsourcedt'];
				$result[$i]['amtsourcect']    = $a['amtsourcect'];
			}

			if ($this->isDebug) {
				echo "<script>console.log('result')</script>";
				echo "<script>console.log('" . json_encode($result, JSON_FORCE_OBJECT) . "')</script>";
			}

			//RECORREMOS INFORMACION DE BALANCE
			foreach ($result as $key => $value) {	
				$ch_sucursal    = $value['ch_sucursal'];
				$act_account_id = $value['act_account_id'];
				$tab_currency   = $value['tab_currency'];

				if ($tab_currency == "01") {
					$amtdt = $value['amtdt'];
					$amtct = $value['amtct'];
				} else {
					$amtdt = $value['amtsourcedt'];
					$amtct = $value['amtsourcect'];
				}

				//INSERTAMOS BALANCE
				$sql_insertar_balance = "
					INSERT INTO act_balance (
						act_balance_id, 
						ch_sucursal, 
						dateacct, 
						act_account_id, 
						amtdt, 
						amtct, 
						tab_currency
					) VALUES (
						nextval('seq_act_balance_id'),
						'$ch_sucursal',
						'$fecha_balance',
						'$act_account_id',
						'$amtdt',
						'$amtct',
						'$tab_currency'
					);
				";
				
				$iStatus = $sqlca->query($sql_insertar_balance);

				if ((int)$iStatus < 0) {
					return array('error' => TRUE, 'message' => 'Error en insert sql_insertar_balance');
				}
			}
		} else {
			return array('error' => TRUE, 'message' => 'Error en sql_eliminar_balance');
		}

		return array(
			'error' => FALSE
		);
	}

	/**
	* Funcion para activar la depuracion
	* @param TRUE activa depuracion
	* @param FALSE desactiva depuracion
	*/
	public function setIsDebug($is) {
		$this->isDebug = $is;
	}

	/**
	* Funcion para permitir generar asientos contables
	* @return TRUE lo permite
	* @return FALSE no lo permite
	*/ 
	public function getAccoutingEnabled() {
		global $sqlca;
		
		$sqlAccoutingEnabled = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'AccoutingEnabled'";		
		
		if ($sqlca->query($sqlAccoutingEnabled) < 0)
			return array('bStatus' => FALSE);		

		$row = $sqlca->fetchRow();
		$parametro = $row['par_valor'];

		if ($parametro == 1)
			return array('bStatus' => TRUE);
		else
			return array('bStatus' => FALSE);		
	}

	/**
	* Funcion para insertar datos en "act_entry" y "act_entryline"
	* @return ARRAY
	*/
	public function executeInsert($dataAsientos) {
		global $sqlca;

		//EJECUTAMOS INSERT CABECERA (act_entry)
		foreach ($dataAsientos as $key => $value) {
			$ch_sucursal        = $value['ch_sucursal'];
			$dateacct           = $value['dateacct'];        
			$description        = $value['description'];     
			$act_entrytype_id   = $value['act_entrytype_id'];
			$subbookcode        = $value['subbookcode']['acctcode'];
			$registerno         = $value['registerno'];   
			$documentdate       = $value['documentdate'];
			$tableid            = $value['tableid'];         
			$regid              = $value['regid'];            
			$int_clientes_id    = $value['int_clientes_id'];  
			$c_cash_mpayment_id = $value['c_cash_mpayment_id'];
			$tab_currency       = $value['tab_currency'];     

			//Columns de insert
			$ch_sucursal_column        = isset($ch_sucursal)        ? ",ch_sucursal"        : NULL; 
			$dateacct_column           = isset($dateacct)           ? ",dateacct"           : NULL; 
			$description_column        = isset($description)        ? ",description"        : NULL; 
			$act_entrytype_id_column   = isset($act_entrytype_id)   ? ",act_entrytype_id"   : NULL; 
			$subbookcode_column    	   = isset($subbookcode)     	? ",subbookcode"        : NULL;
			$registerno_column    	   = isset($registerno)     	? ",registerno"         : NULL;
			$documentdate_column       = isset($documentdate)       ? ",documentdate"       : NULL; 
			$tableid_column            = isset($tableid)            ? ",tableid"            : NULL; 
			$regid_column              = isset($regid)              ? ",regid"              : NULL; 
			$int_clientes_id_column    = isset($int_clientes_id)    ? ",int_clientes_id"    : NULL; 
			$c_cash_mpayment_id_column = isset($c_cash_mpayment_id) ? ",c_cash_mpayment_id" : NULL; 
			$tab_currency_column       = isset($tab_currency)       ? ",tab_currency"       : NULL; 

			//Values de insert
			$ch_sucursal_value        = isset($ch_sucursal)        ? ",'$ch_sucursal'"        : NULL; 
			$dateacct_value           = isset($dateacct)           ? ",'$dateacct'"           : NULL; 
			$description_value        = isset($description)        ? ",'$description'"        : NULL; 
			$act_entrytype_id_value   = isset($act_entrytype_id)   ? ",'$act_entrytype_id'"   : NULL; 
			$subbookcode_value    	  = isset($subbookcode)        ? ",'$subbookcode'"        : NULL;
			$registerno_value         = isset($registerno)     	   ? ",'$registerno'"     	  : NULL; 
			$documentdate_value       = isset($documentdate)       ? ",'$documentdate'"       : NULL; 
			$tableid_value            = isset($tableid)            ? ",'$tableid'"            : NULL; 
			$regid_value              = isset($regid)              ? ",'$regid'"              : NULL; 
			$int_clientes_id_value    = isset($int_clientes_id)    ? ",'$int_clientes_id'"    : NULL; 
			$c_cash_mpayment_id_value = isset($c_cash_mpayment_id) ? ",'$c_cash_mpayment_id'" : NULL; 
			$tab_currency_value       = isset($tab_currency)       ? ",'$tab_currency'"       : NULL; 

			$iStatus = $sqlca->query("
				INSERT INTO public.act_entry (
					act_entry_id
					$ch_sucursal_column 
					$dateacct_column 
					$description_column 
					$act_entrytype_id_column 
					$subbookcode_column 
					$registerno_column 
					$documentdate_column 
					$tableid_column 
					$regid_column 
					$int_clientes_id_column 
					$c_cash_mpayment_id_column 
					$tab_currency_column
				) VALUES (
					nextval('seq_act_entry_id')
					$ch_sucursal_value 
					$dateacct_value 
					$description_value 
					$act_entrytype_id_value 
					$subbookcode_value
					$registerno_value 
					$documentdate_value
					$tableid_value 
					$regid_value 
					$int_clientes_id_value 
					$c_cash_mpayment_id_value 
					$tab_currency_value
				) RETURNING act_entry_id AS act_entry_id
			");

			if ((int)$iStatus < 0) {
				return array('error' => TRUE, 'message' => 'Error en insert act_entry');
			}

			$row = $sqlca->fetchRow();
			$act_entry_id = $row["act_entry_id"];

			//EJECUTAMOS INSERT DETALLE (act_entryline)
			$dataAsientosDetalle = $value['act_entryline'];
			foreach ($dataAsientosDetalle as $key => $value) {
				$act_entry_id       = $act_entry_id;
				$act_account_id     = $value['act_account_id']['act_account_id'];        
				$amtdt              = $value['amtdt'];     
				$amtct              = $value['amtct'];
				$amtsourcedt        = $value['amtsourcedt'];   
				$amtsourcect        = $value['amtsourcect'];         
				$description        = $value['description'];
				$tableid            = $value['tableid'];
				$regid              = $value['regid'];
				$int_clientes_id    = $value['int_clientes_id'];
				$c_cash_mpayment_id = $value['c_cash_mpayment_id'];
				$tab_currency       = $value['tab_currency'];

				//Columns de insert
				$act_entry_id_column       = isset($act_entry_id)       ? ",act_entry_id"       : NULL; 
				$act_account_id_column     = isset($act_account_id)     ? ",act_account_id"     : NULL; 
				$amtdt_column              = isset($amtdt)              ? ",amtdt"              : NULL; 
				$amtct_column              = isset($amtct)              ? ",amtct"              : NULL; 
				$amtsourcedt_column        = isset($amtsourcedt)        ? ",amtsourcedt"        : NULL; 
				$amtsourcect_column        = isset($amtsourcect)        ? ",amtsourcect"        : NULL;
				$description_column        = isset($description)        ? ",description"        : NULL;
				$tableid_column            = isset($tableid)            ? ",tableid"            : NULL;
				$regid_column              = isset($regid)              ? ",regid"              : NULL;
				$int_clientes_id_column    = isset($int_clientes_id)    ? ",int_clientes_id"    : NULL;
				$c_cash_mpayment_id_column = isset($c_cash_mpayment_id) ? ",c_cash_mpayment_id" : NULL;
				$tab_currency_column       = isset($tab_currency)       ? ",tab_currency"       : NULL;

				//Values de insert
				$act_entry_id_value    = isset($act_entry_id)       ? ",'$act_entry_id'"       : NULL; 
				$act_account_id_value  = isset($act_account_id)     ? ",'$act_account_id'"     : NULL; 
				$amtdt_value           = isset($amtdt)              ? ",'$amtdt'"              : NULL; 
				$amtct_value           = isset($amtct)              ? ",'$amtct'"              : NULL; 
				$amtsourcedt_value     = isset($amtsourcedt)        ? ",'$amtsourcedt'"        : NULL; 
				$amtsourcect_value     = isset($amtsourcect)        ? ",'$amtsourcect'"        : NULL;
				$description_value     = isset($description)        ? ",'$description'"        : NULL;
				$tableid_value         = isset($tableid)            ? ",'$tableid'"            : NULL;
				$regid_value           = isset($regid)              ? ",'$regid'"              : NULL;
				$int_clientes_id_value = isset($int_clientes_id)    ? ",'$int_clientes_id'"    : NULL;
				$c_cash_mpayment_value = isset($c_cash_mpayment_id) ? ",'$c_cash_mpayment_id'" : NULL;
				$tab_currency_value    = isset($tab_currency)       ? ",'$tab_currency'"       : NULL; 

				$iStatus = $sqlca->query("
					INSERT INTO public.act_entryline (
						act_entryline_id
						$act_entry_id_column 
						$act_account_id_column 
						$amtdt_column 
						$amtct_column 
						$amtsourcedt_column 
						$amtsourcect_column 	
						$description_column	
						$tableid_column
						$regid_column	
						$int_clientes_id_column	
						$c_cash_mpayment_id_column
						$tab_currency_column
					) VALUES (
						nextval('seq_act_entryline_id')
						$act_entry_id_value 
						$act_account_id_value 
						$amtdt_value 
						$amtct_value 
						$amtsourcedt_value 
						$amtsourcect_value 	
						$description_value
						$tableid_value	
						$regid_value
						$int_clientes_id_value
						$c_cash_mpayment_value		
						$tab_currency_value		
					);
				");

				if ((int)$iStatus < 0) {
					return array('error' => TRUE, 'message' => 'Error en insert act_entryline');
				}
			}
		}

		return array(
			'error' => FALSE
		);
	}

	/**
	* Funcion para verificar mensaje de respuesta
	* @return ARRAY
	*/
	public function checkResponse($res, $req) {
		/**
		 - Pruebas: 
		 - La funcionalidad se activa solo si recibe en Request la variable "is_error" en TRUE. La variable "is_error" en TRUE provoca un error, lo que genera ROLLBACK
		 - Si hay error, no hacemos nada ya que debe mostrar el error generado
		 - Si no hay error, indicamos que si genero error para que no haga cambios en BD y genere ROLLBACK
		 */
		if ($req['is_error'] == TRUE) {
			if ($res['error'] == FALSE) {
				$res['error'] = TRUE;
				$res['message'] = "Error provocado para realizar pruebas";
			}
		}	
		
		/**
		 - Error:
		 - Si no hay respuesta correcta, el proceso se detuvo en cualquiera de las funciones
		 */
		if ( !is_array($res) || empty($res) ) {
			$res['error'] = TRUE;
			$res['message'] = "Error: El proceso se detuvo";
		}

		return $res;
	} 	

	/**
	* Funcion para eliminar asientos contables
	* @return ARRAY
	*/
	public function eliminarAsientos($arrParams, $is_error = FALSE) {
		$objHelper = new HelperClass();	

		//ACTIVAMOS MODO DEBUG
		$this->setIsDebug(true);

		//ELIMINAR ASIENTOS
		$objHelper->str_debug("ELIMINAR_ASIENTOS");
		
		global $sqlca;
		$almacen = TRIM($arrParams['sCodeWarehouse']);
		$fecha   = TRIM($arrParams['dEntry']);

		//OBTENER ASIENTOS ORDENADOS POR SUBDIARIO Y CORRELATIVO DE SUBDIARIO
		$sql = "
			SELECT
				TRIM(subbookcode) AS subbookcode,  
				registerno AS registerno
			FROM
				act_entry
			WHERE
				TRIM(ch_sucursal) = '$almacen'
				AND DATE(documentdate) = '$fecha'
			ORDER BY
				subbookcode ASC, registerno ASC;
		";

		if ($this->isDebug) {
    		echo "\nOBTENER ASIENTOS ORDENADOS\n";
			echo "<pre>$sql</pre>";	
		}

		if ($sqlca->query($sql) < 0) {
			return array('error' => TRUE, 'message' => 'Error en obtener asientos ordenados en act_entry');
		}
		
		$result = array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$row         = $sqlca->fetchRow();
			$subbookcode = $row['subbookcode'];
			$registerno  = $row['registerno'];
			
			$result['correlativos_eliminar'][$subbookcode][] = array(				
				"subbookcode" => $subbookcode,
				"registerno"  => $registerno,
			);			
		}

		if ($this->isDebug) {
			echo "<script>console.log('result')</script>";
			echo "<script>console.log('" . json_encode($result, JSON_FORCE_OBJECT) . "')</script>";			
		}
		
		//RECORREREMOS ARRAY 
		foreach ($result['correlativos_eliminar'] as $key => $resultSubdiario) {
			foreach ($resultSubdiario as $key => $valueSubdiario) {
				// if ($this->isDebug) {
				// 	echo "<script>console.log('value correlativos')</script>";
				// 	echo "<script>console.log('" . json_encode($valueSubdiario, JSON_FORCE_OBJECT) . "')</script>";			
				// }

				//OBTENEMOS PARAMETROS
				$subdiario = TRIM($valueSubdiario['subbookcode']);

				//OBTENEMOS ACT_REGISTERNUMBER_ID POR MEDIO SUCURSAL, FECHA DEL MES Y SUBDIARIO
				$response_act_registernumber_id = $objHelper->get_act_registernumber_id($almacen, $fecha, $subdiario);
				if ($response_act_registernumber_id['error']) {
					return $response_act_registernumber_id;
				}
				$act_registernumber_id = $response_act_registernumber_id['act_registernumber_id'];				

				//INSERTAMOS SUBDIARIO Y CORRELATIVO EN ACT_PRESEQ
				$registerno = $valueSubdiario['registerno'];
				$sql = "INSERT INTO act_preseq (act_preseq_id, act_registernumber_id, numerator) VALUES (nextval('seq_act_preseq_id'), '$act_registernumber_id', '$registerno')";				

				if ($sqlca->query($sql) < 0) {
					return array('error' => TRUE, 'message' => 'Error en insert act_regiternumber');		
				}
			}
		}

		//ELIMINAMOS DETALLE DE ASIENTOS
		$sql_act_entryline = "
			DELETE FROM act_entryline WHERE act_entry_id IN (SELECT 
																act_entry_id 
															FROM 
																act_entry 
															WHERE 
																TRIM(ch_sucursal) = '$almacen'
																AND DATE(documentdate) = '$fecha');
		";
		/* $sql_act_entryline = "DELETE FROM act_entryline"; */ //TODO: delete
		$iStatus = $sqlca->query($sql_act_entryline);
		
		//ELIMINAMOS CABECERA DE ASIENTOS
		if ((int)$iStatus >= 0) {
			$sql_act_entry = "
				DELETE FROM 
					act_entry 
				WHERE
					TRIM(ch_sucursal) = '$almacen'
					AND DATE(documentdate) = '$fecha';
			";
			/* $sql_act_entry = "DELETE FROM act_entry"; */ //TODO: delete	
			$sqlca->query($sql_act_entry);	
		}

		//RETORNAMOS RESPUESTA CORRECTA
		if ($is_error) {
			return array('error' => TRUE, 'message' => 'Error provocado para realizar pruebas');
		} else {
			return array('error' => FALSE);
		}
	}
}