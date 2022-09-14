<?php
ini_set("memory_limit", '-1');
ini_set("upload_max_filesize", "15M");
ini_set("post_max_size", "15M");
ini_set('max_execution_time', '700');
ini_set('max_input_time', '700');

class RegistroVentasModel extends Model {

	function obtieneRegistros($almacen, $anio, $mes, $desde, $hasta, $tipo, $orden, $seriesdocumentos, $tipo_vista_monto, $serie, $nserie, $BI_incre, $IGV_incre, $TOTAL_incre, $monto_igual, $nd) { //Obtiene Registros
		global $sqlca;

		/* Obtenemos fechas para usar en queries */
		$result 			= Array();
		$fecha_postrans 	= $anio . "" . $mes;
		$fecha_serie 		= $anio . "-" . $mes;
		$fecha_inicial 		= $anio . "-" . $mes . "-" . $desde;
		$fecha_final 		= $anio . "-" . $mes . "-" . $hasta;
		$correlativo 		= 0;

		/* Obtenemos fecha de parametro "reportsByRealDate" de "int_parametros" en formato "YYYY-MM" y fecha del reporte en formato "YYYY-MM" */
 		$dataParametro = $this->getFechaParametro($anio, $mes);

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
		$status_table_postrans_ant = $this->validateTableBySchema("pos_trans".$fecha_postrans_ant);
		$status_table_postrans_des = $this->validateTableBySchema("pos_trans".$fecha_postrans_des);

		if ($nd == 'S')
			$tipo_documento_tickes 	= array("'F','N'");
		else
			$tipo_documento_tickes 	= array("'F'");

		$len_registro 		= 0;
		$key_array 		= "";
		$array_series 		= array();
		$correlativo_serie 	= 0;

		$aplicar_incremento = FALSE;

		/** CONSULTA PARA LOS EXTORNOS **/
		if ( $dataParametro['fecha_parametro'] != 0 ) {
			if ( $status_table_postrans_ant == TRUE ) {

				//El valor del parametro corresponde a un mes anterior al indicado en el registro de ventas
				if (strcmp($dataParametro['fecha_registro_ventas'], $dataParametro['fecha_parametro']) > 0) {
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
			}
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
			
		if ( $dataParametro['fecha_parametro'] != 0 ) {
			if ( $status_table_postrans_des == TRUE ) {
			
				//El valor del parametro corresponde a un mes anterior al indicado en el registro de ventas / El valor del parametro corresponde al mismo mes y año indicado en el registro de ventas. Es decir las fechas son iguales
				if (strcmp($dataParametro['fecha_registro_ventas'], $dataParametro['fecha_parametro']) > 0 || strcmp($dataParametro['fecha_registro_ventas'], $dataParametro['fecha_parametro']) == 0) {
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
			}
		}

		echo "<pre>sql_aferciones:";
		echo "$sql_aferciones";
		echo "</pre>";

		if ($sqlca->query($sql_aferciones) < 0)
        	return false;

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

    	if ($sqlca->query($sql_series) < 0)
			return false;

    	for (; $correlativo_serie < $sqlca->numrows();) {
	    	$a_act = $sqlca->fetchRow();
	    	$array_series[$a_act['dt_posz_fecha_sistema']][$a_act['ch_posz_pos']][$a_act['nu_posturno']] = $a_act['nu_posz_z_serie'];
	    	$correlativo_serie++;
    	}

    	if (strcmp($tipo, "N") == 0) { //Tipo Vista: Detallado
			array_push($tipo_documento_tickes, "'B'");
			$key_array = "ticket";
		} else { //Tipo Vista: SUNAT
			array_push($tipo_documento_tickes, "'B'");
			$key_array = "ticket_tmp";
		}

		//TICKES FACTURAS
		$where_tickets = "AND T.dia BETWEEN '" . pg_escape_string($fecha_inicial) . "' AND '" . pg_escape_string($fecha_final) . "'";
		$fecha_emision_tickets     = "T.dia::DATE as emision,";
		$fecha_vencimiento_tickets = "T.dia::DATE as vencimiento,";
		$group_by_fecha_tickets    = "T.dia,";

		if ( $dataParametro['fecha_parametro'] != 0 ) {
			//El valor del parametro corresponde al mismo mes y año indicado en el registro de ventas. Es decir las fechas son iguales
			if (strcmp($dataParametro['fecha_registro_ventas'], $dataParametro['fecha_parametro']) == 0) {
				$where_tickets = "AND T.dia >= '" . pg_escape_string($fecha_inicial) . "' AND T.fecha <= '" . pg_escape_string($fecha_final) . " 23:59:59'";
				$fecha_emision_tickets     = "T.fecha::DATE as emision,";
				$fecha_vencimiento_tickets = "T.fecha::DATE as vencimiento,";
				$group_by_fecha_tickets    = "T.fecha,";
			}

			//El valor del parametro corresponde a un mes anterior al indicado en el registro de ventas
			if (strcmp($dataParametro['fecha_registro_ventas'], $dataParametro['fecha_parametro']) > 0) {
				$where_tickets = "AND T.fecha >= '" . pg_escape_string($fecha_inicial) . " 00:00:00' AND T.fecha <= '" . pg_escape_string($fecha_final) . " 23:59:59'";
				$fecha_emision_tickets     = "T.fecha::DATE as emision,";
				$fecha_vencimiento_tickets = "T.fecha::DATE as vencimiento,";
				$group_by_fecha_tickets    = "T.fecha,";
			}
		}

		if ( $dataParametro['fecha_parametro'] != 0 ) {
			if ( $status_table_postrans_ant == TRUE ) { //

				//El valor del parametro corresponde a un mes anterior al indicado en el registro de ventas
				if (strcmp($dataParametro['fecha_registro_ventas'], $dataParametro['fecha_parametro']) > 0) {
					$sql_tickes_factura .= "
					SELECT
						T.trans as trans,
						T.caja as caja,
						$fecha_emision_tickets
						$fecha_vencimiento_tickets
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
						(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.importe - T.igv), 2) END) AS imponible,
						(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.igv), 2) END) AS igv,
						(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.importe), 2) END) AS importe,
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
						FIRST(t.td) AS td,
						FIRST(t.codigo) AS codigo,
						CASE
							WHEN FIRST(t.td) = 'B' OR FIRST(t.td) = 'F' THEN COALESCE( ROUND(SUM(T.balance), 2), 0 )
							ELSE 0
						END AS balance --ICBPER
					FROM
						pos_trans" . $fecha_postrans_ant . " AS T
						LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = T.dia)
						LEFT JOIN ruc AS R ON (R.ruc = T.ruc)
					WHERE
						T.td IN (" . implode(',', $tipo_documento_tickes) . ")
						$where_tickets
						AND T.es = '$almacen'
					GROUP BY
						$group_by_fecha_tickets
						T.trans,
						T.caja,
						T.usr

					UNION ALL
					";
				}
			}
		}

		$sql_tickes_factura .= "
		SELECT
			T.trans as trans,
			T.caja as caja,
			$fecha_emision_tickets
			$fecha_vencimiento_tickets
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
			(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.importe - T.igv), 2) END) AS imponible,
			(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.igv), 2) END) AS igv,
			(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.importe), 2) END) AS importe,
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
			FIRST(t.td) AS td,
			FIRST(t.codigo) AS codigo,
			CASE
				WHEN FIRST(t.td) = 'B' OR FIRST(t.td) = 'F' THEN COALESCE( ROUND(SUM(T.balance), 2), 0 )
				ELSE 0
			END AS balance --ICBPER
		FROM
			pos_trans" . $fecha_postrans . " AS T
			LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = T.dia)
			LEFT JOIN ruc AS R ON (R.ruc = T.ruc)
		WHERE
			T.td IN (" . implode(',', $tipo_documento_tickes) . ")
			$where_tickets
			AND T.es = '$almacen'
		GROUP BY
			$group_by_fecha_tickets
			T.trans,
        	T.caja,
        	T.usr
		";

		if ( $dataParametro['fecha_parametro'] != 0 ) {
			if ( $status_table_postrans_des == TRUE ) { //
			
				//El valor del parametro corresponde a un mes anterior al indicado en el registro de ventas / El valor del parametro corresponde al mismo mes y año indicado en el registro de ventas. Es decir las fechas son iguales
				if (strcmp($dataParametro['fecha_registro_ventas'], $dataParametro['fecha_parametro']) > 0 || strcmp($dataParametro['fecha_registro_ventas'], $dataParametro['fecha_parametro']) == 0) {
					$sql_tickes_factura .= "
					UNION ALL

					SELECT
						T.trans as trans,
						T.caja as caja,
						$fecha_emision_tickets
						$fecha_vencimiento_tickets
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
						(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.importe - T.igv), 2) END) AS imponible,
						(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.igv), 2) END) AS igv,
						(CASE WHEN FIRST(T.td) = 'N' THEN '0' ELSE ROUND(SUM(T.importe), 2) END) AS importe,
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
						FIRST(t.td) AS td,
						FIRST(t.codigo) AS codigo,
						CASE
							WHEN FIRST(t.td) = 'B' OR FIRST(t.td) = 'F' THEN COALESCE( ROUND(SUM(T.balance), 2), 0 )
							ELSE 0
						END AS balance --ICBPER
					FROM
						pos_trans" . $fecha_postrans_des . " AS T
						LEFT JOIN int_tipo_cambio AS TC ON (TC.tca_fecha = T.dia)
						LEFT JOIN ruc AS R ON (R.ruc = T.ruc)
					WHERE
						T.td IN (" . implode(',', $tipo_documento_tickes) . ")
						$where_tickets
						AND T.es = '$almacen'
					GROUP BY
						$group_by_fecha_tickets
						T.trans,
						T.caja,
						T.usr
					";
				}
			}
		}

		$sql_tickes_factura .= "
		ORDER BY
			--tipo desc,
			--nserie,
			2,
			1;
		";

		echo "<pre>sql_tickes_factura:";
		echo "$sql_tickes_factura";
		echo "</pre>";

		if ($sqlca->query($sql_tickes_factura) < 0)
			return false;

		/* Recorremos informacion de Comprobantes de Playa */
		$sumatotal_formato_sunat_bi 		= 0;
		$sumatotal_formato_sunat_igv 		= 0;
		$sumatotal_formato_sunat_exonerada 	= 0;
		$sumatotal_formato_sunat_inafecto 	= 0;
		$sumatotal_formato_sunat_vv 		= 0;

		$imponible	= 0;
		$igv		= 0;
		$balance	= 0; 
		$exonerada	= 0;
		$inafecto	= 0;
		$importe	= 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    $a = $sqlca->fetchRow();

		    //Nuevos campos agregados
	    	$result[$key_array][$correlativo]['td']	            = $a['td'];
	    	$result[$key_array][$correlativo]['rendi_gln']      = $a['rendi_gln'];
	    	$result[$key_array][$correlativo]['ruc_bd_interno']	= $a['ruc_bd_interno'];
	    	$result[$key_array][$correlativo]['id_trans']	    = $a['trans'];

	    	$trans_caja 									 = trim($a['trans'] . "-" . $a['caja']);
	    	$result[$key_array][$correlativo]['trans']		 = $a['numero'];
	    	$result[$key_array][$correlativo]['caja'] 		 = $a['caja'];
	    	$result[$key_array][$correlativo]['emision'] 	 = $a['emision'];
	    	$result[$key_array][$correlativo]['vencimiento'] = $a['vencimiento'];
	    	$result[$key_array][$correlativo]['tipo'] 		 = $a['tipo'];

	    	$result[$key_array][$correlativo]['numero'] 	 = $a['numero'];
	    	$result[$key_array][$correlativo]['tipodi'] 	 = $a['tipodi'];
	    	$result[$key_array][$correlativo]['ruc'] 		 = $a['ruc'];
	    	$result[$key_array][$correlativo]['cliente']	 = $a['cliente'];
	    	$result[$key_array][$correlativo]['vfexp']		 = 0;

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
				$imponible	= 0;
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
		    $result[$key_array][$correlativo]['imponible'] 		= $imponible;
	    	$result[$key_array][$correlativo]['exonerada'] 		= $exonerada;
	    	$result[$key_array][$correlativo]['inafecto'] 		= $inafecto;
	    	$result[$key_array][$correlativo]['isc']			= 0;
	    	$result[$key_array][$correlativo]['igv'] 			= $igv;
			$result[$key_array][$correlativo]['balance']    	= $balance; //ICBPER
	    	$result[$key_array][$correlativo]['otros'] 			= 0;
	    	$result[$key_array][$correlativo]['importe'] 		= $importe;
	    	$result[$key_array][$correlativo]['tipocambio'] 	= $a['tipocambio'];
	    	$result[$key_array][$correlativo]['fecha2'] 		= "";
	    	$result[$key_array][$correlativo]['tipo2'] 			= "";
	    	$result[$key_array][$correlativo]['serie2'] 		= "";
	    	$result[$key_array][$correlativo]['numero2'] 		= "";
	    	$result[$key_array][$correlativo]['tipo_pdf'] 		= $a['tipo_pdf'];
	    	$result[$key_array][$correlativo]['estado'] 		= $a['estadoventa'];
	    	$result[$key_array][$correlativo]['taxoptional']	= $a['taxoptional'];
	    	$result[$key_array][$correlativo]['es']				= $a['es'];
			$result[$key_array][$correlativo]['nserie']			= $a['nserie'];

	    	$result[$key_array][$correlativo]['reffec'] = "";
		    $result[$key_array][$correlativo]['reftip'] = "";
		    $result[$key_array][$correlativo]['refser'] = "";
		    $result[$key_array][$correlativo]['refnum'] = "";

	    	if ($a['nserie'] == '') {
	    		$result[$key_array][$correlativo]['serie'] = $array_series[$a['emision']][$a['caja']][$a['turno']];
			}else{
				$result[$key_array][$correlativo]['serie'] = $a['nserie'];
			}

			//VARIABLES PARA LA SUMA TOTAL TICKETS POR BOLETAS, FACTURAS Y NOTAS DE CREDITO
			$tipo_pdf = $a['tipo_pdf'];
			$result['ticket']['tipo'][$tipo_pdf]['total_imponible']	+= $imponible;
			$result['ticket']['tipo'][$tipo_pdf]['total_igv']		+= $igv;			
			$result['ticket']['tipo'][$tipo_pdf]['total_balance']	+= $balance; //ICBPER
			$result['ticket']['tipo'][$tipo_pdf]['total_exonerada'] += $exonerada;
			$result['ticket']['tipo'][$tipo_pdf]['total_inafecto'] 	+= $inafecto;
			$result['ticket']['tipo'][$tipo_pdf]['total_importe'] 	+= $importe;

			//VARIABLES PARA LA SUMA TOTAL TICKETS GUARDO 2
	    	$result['ticket']['total_imponible']	+= $imponible;
			$result['ticket']['total_igv']			+= $igv;			
			$result['ticket']['total_balance']		+= $balance; //ICBPER
	    	$result['ticket']['total_exonerada'] 	+= $exonerada;
	    	$result['ticket']['total_inafecto'] 	+= $inafecto;
	    	$result['ticket']['total_importe'] 		+= $importe;

	    	//TOTALES
	    	$result['totales_imponible'] 		+= $imponible;
			$result['totales_igv'] 				+= $igv;
			$result['totales_balance']		    += $balance; //ICBPER
	    	$result['totales_exonerada'] 		+= $exonerada;
	    	$result['totales_inafecto'] 		+= $inafecto;
	    	$result['totales_importe'] 			+= $importe;

			$correlativo++;
		}

		// echo "<script>console.log('tipo')</script>";
		// echo "<script>console.log('" . json_encode(strcmp($tipo, "SU")) . "')</script>";
        if (strcmp($tipo, "SU") == 0) { //Tipo Vista: SUNAT
	    	$correlativo			= 0;
	    	$inicio_boleta 			= "0";
	    	$fin_boleta 			= "0";
	    	$fecha_agrupar 			= "";
	    	$serie_agrupar 			= "";
	    	$tipo_documento_venta	= "";
	    	$array_tmp_impresion 	= array();
	    	$imprimir 				= 0;
	    	$cantidad_factura 		= 0;
	    	$array_boletas 			= array();

			// echo "<script>console.log('result')</script>";
			// echo "<script>console.log('" . json_encode( array($result) ) . "')</script>";
        	for ($i = 0; $i < count($result['ticket_tmp']); $i++) {
				$a = $result['ticket_tmp'][$i];

	        	if ($i == 0) {
			    	$tipo_documento_venta 	= trim($a['tipo_pdf']);
			    	$fecha_agrupar		= $a['emision'];
			    	$serie_agrupar 		= $a['serie'];
			    	$inicio_boleta 		= $a['numero'];
			    }

            	if ((strcmp($tipo_documento_venta, $a['tipo_pdf']) == 0 && strcmp($a['emision'], $fecha_agrupar) == 0 && strcmp($a['serie'], $serie_agrupar) == 0) && $cantidad_factura == 0) {
					$fin_boleta = $a['numero'];
					if ($a['tipo_pdf'] == "F" || $a['tipo_pdf'] == "N" || $a['tipo_pdf'] == "A"){
						$cantidad_factura = 1;
					}					
				}else {

			    	$result['ticket'][$correlativo] = $array_tmp_impresion;

			    	$result['ticket'][$correlativo]['trans']	= $inicio_boleta; //para que sea unico numero transacion
			    	$result['ticket'][$correlativo]['numero']	= $inicio_boleta . "-" . $fin_boleta;
			    	$result['ticket'][$correlativo]['serie']	= $serie_agrupar;
			    	$result['ticket'][$correlativo]['emision']	= $fecha_agrupar;

			    	$result['ticket'][$correlativo]['imponible']	= $array_tmp_impresion['imponible'];
			    	$result['ticket'][$correlativo]['igv']		= $array_tmp_impresion['igv'];
					$result['ticket'][$correlativo]['balance'] = $array_tmp_impresion['balance']; 
			    	$result['ticket'][$correlativo]['exonerada']	= $array_tmp_impresion['exonerada'];
			    	$result['ticket'][$correlativo]['inafecto']	= $array_tmp_impresion['inafecto'];
					$result['ticket'][$correlativo]['importe']	= $array_tmp_impresion['importe'];

                	if ($result['ticket'][$correlativo]['tipodi'] == 1) {
						$array_boletas[] = $correlativo;
					}	//SUMA DE MONTOS

			    	$sumatotal_formato_sunat_bi 		+= $array_tmp_impresion['imponible'];
					$sumatotal_formato_sunat_igv		+= $array_tmp_impresion['igv'];
					$sumatotal_formato_sunat_balance	+= $array_tmp_impresion['balance']; 
			    	$sumatotal_formato_sunat_exonerada	+= $array_tmp_impresion['exonerada'];
			    	$sumatotal_formato_sunat_inafecto	+= $array_tmp_impresion['inafecto'];
			    	$sumatotal_formato_sunat_vv 		+= $array_tmp_impresion['importe'];

			    	$inicio_boleta = $a['numero'];
			    	$i--;
			    	$correlativo++;
			    	$cantidad_factura = 0;
			    	$array_tmp_impresion = array(); //volvemos el array_tmp a vacio
			    	$imprimir = 1;
				}

        		$tipo_documento_venta 	= trim($a['tipo_pdf']);
        		$fecha_agrupar 		= $a['emision'];
        		$serie_agrupar 		= $a['serie'];                		

				if ($imprimir == 0){
					$array_tmp_impresion = RegistroVentasModel::llenar_arreglo_objecto_imprimir($array_tmp_impresion, $a, $tipo_vista_monto);
				}

				$imprimir = 0;
            }

	    	//PARA LA ULTIMA IMPRESION 
	    	$result['ticket'][$correlativo] 		= $array_tmp_impresion;
	    	$result['ticket'][$correlativo]['trans'] 	= $inicio_boleta; //para que sea unico numero transacion
	    	$result['ticket'][$correlativo]['numero'] 	= $inicio_boleta . "-" . $fin_boleta;
	    	$result['ticket'][$correlativo]['serie'] 	= $serie_agrupar;

	    	$result['ticket'][$correlativo]['imponible'] 	= $array_tmp_impresion['imponible'];
	    	$result['ticket'][$correlativo]['igv'] 		= $array_tmp_impresion['igv'];
			$result['ticket'][$correlativo]['balance'] = $array_tmp_impresion['balance'];
	    	$result['ticket'][$correlativo]['exonerada']	= $array_tmp_impresion['exonerada'];
	    	$result['ticket'][$correlativo]['inafecto']	= $array_tmp_impresion['inafecto'];
	    	$result['ticket'][$correlativo]['importe'] 	= $array_tmp_impresion['importe'];

	    	$result['ticket'][$correlativo]['reffec'] 	= $array_tmp_impresion['reffec'];
	    	$result['ticket'][$correlativo]['reftip'] 	= $array_tmp_impresion['reftip'];
	    	$result['ticket'][$correlativo]['refser'] 	= $array_tmp_impresion['refser'];
			$result['ticket'][$correlativo]['refnum'] 	= $array_tmp_impresion['refnum'];

	    	//SUMA DE MONTOS
	    	$sumatotal_formato_sunat_bi 		+= $array_tmp_impresion['imponible'];
			$sumatotal_formato_sunat_igv		+= $array_tmp_impresion['igv'];
			$sumatotal_formato_sunat_balance	+= $array_tmp_impresion['balance']; 
	    	$sumatotal_formato_sunat_exonerada	+= $array_tmp_impresion['exonerada'];
	    	$sumatotal_formato_sunat_inafecto	+= $array_tmp_impresion['inafecto'];
	    	$sumatotal_formato_sunat_vv 		+= $array_tmp_impresion['importe'];
		}

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
			END as statusname
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
			AND Cab.dt_fac_fecha BETWEEN '" . pg_escape_string($fecha_inicial) . "' AND '" . pg_escape_string($fecha_final) . "' --Busqueda por fecha
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
    		serie,
    		emision,
    		Cab.ch_fac_numerodocumento;
		";

		echo "<pre>sql_facturas_manuales:";
		echo "$sql_facturas_manuales";
		echo "</pre>";

		if ($sqlca->query($sql_facturas_manuales) < 0)
			return false;

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

			$nuevodata= explode('*',$a['refdata']);
			$dataref1=$nuevodata[0];//numero
			$dataref2=$nuevodata[1];//serie
			$dataref3=$nuevodata[2];//tipo

			$result[$keyStatus][$i]['reffec'] 		= $a['reffecha'];
			$result[$keyStatus][$i]['reftip'] 		= $dataref3;
			$result[$keyStatus][$i]['refser'] 		= $dataref2;
			$result[$keyStatus][$i]['refnum'] 		= $dataref1;

			//VARIABLES PARA LA SUMA TOTAL DE DOCUMENTOS DE VENTAS MANUALES POR BOLETA, FACTURA, NOTA DE CREDITO, NOTA DE DEBITO
			$tipo = $a['tipo'];
			$result[$keyStatus]['tipo'][$tipo]['total_imponible'] += $imponible;
			$result[$keyStatus]['tipo'][$tipo]['total_igv'] 	  += $igv;
			$result[$keyStatus]['tipo'][$tipo]['total_balance']	  += "0.00"; //ICBPER
			$result[$keyStatus]['tipo'][$tipo]['total_exonerada'] += $exonerada;
			$result[$keyStatus]['tipo'][$tipo]['total_inafecto']  += $inafecto;
			$result[$keyStatus]['tipo'][$tipo]['total_importe']   += $importe;

			if ($a['statusname'] == 'Anulado - Enviado') { //TODO: Importante
				$result['manual_anulado']['tipo'][$tipo]['cantidad'] += 1;
				$result['manual_anulado']['tipo']['cantidad'] += 1;
			}

			//VARIABLES PARA LA SUMA TOTAL DE DOCUMENTOS VENTAS MANUALES
		    if ($a['tipo'] != '07') {
				$result[$keyStatus]['total_imponible'] 	+= $imponible;
				$result[$keyStatus]['total_igv'] 		+= $igv;
				$result[$keyStatus]['total_balance']	+= "0.00"; //ICBPER
				$result[$keyStatus]['total_exonerada'] 	+= $exonerada;
				$result[$keyStatus]['total_inafecto'] 	+= $inafecto;
				$result[$keyStatus]['total_importe'] 	+= $importe;
		    }

			//VARIABLES PARA LA SUMA TOTAL DE DOCUMENTOS DE VENTAS MANUALES (NOTAS DE CREDITO) //TODO: Importante
			if ($a['tipo'] == '07') {
				$result[$keyStatus]['nota_credito']['totales_imponible_credito'] += $imponible;
				$result[$keyStatus]['nota_credito']['totales_igv_credito'] += $igv;
				$result[$keyStatus]['nota_credito']['totales_balance_credito'] += "0.00"; //ICBPER
				$result[$keyStatus]['nota_credito']['totales_importe_credito'] += $importe;
				$result[$keyStatus]['nota_credito']['totales_exonerada_nc']	+= abs($exonerada);
				$result[$keyStatus]['nota_credito']['totales_inafecto_nc'] += abs($inafecto);
			}
		}// /. FOR

		// echo "<pre>";
		// print_r($result);				
		// echo "</pre>";

		return $result;
    }

    function obtieneRegistrosGNV($anio, $mes, $desde, $hasta) {
        global $sqlca;

        	$sql = "
				SELECT
					c.c_invoiceheader_id trans,
					to_char(c.created,'YYYY-mm-dd') as emision,
					to_char(c.updated ,'YYYY-mm-dd') as vencimiento,
					'12' as tipo, 
					c.documentserial serie,
					--c.c_invoiceheader_id numero,
			                trim(substring(c.documentno from position(' ' in c.documentno)::INTEGER +1 for 25))::TEXT as numero,
					(CASE WHEN c.c_doctype_id = '10' THEN '6' ELSE '1' END) tipodi,
					c.c_bpartner_id ruc,
					(CASE
						WHEN FIRST(c.c_bpartner_id) IS NULL THEN 'CLIENTE VARIOS'
						WHEN FIRST(c.c_bpartner_id) = '99999999' THEN 'CLIENTE VARIOS'
					ELSE
						substr(FIRST(r.razsocial),0,60)
					END) as cliente,
					--'' cliente,
					ROUND(SUM(d.linetotal/1.18),2) imponible,
					ROUND(SUM(d.linetotal)-SUM(d.linetotal/1.18),2) igv,
					ROUND(SUM(d.linetotal),2) importe,
					FIRST(TC.tca_venta_oficial) as tipocambio
				FROM 
					c_invoiceheader c
					JOIN c_invoicedetail d ON(c.c_invoiceheader_id = d.c_invoiceheader_id)
					LEFT JOIN int_tipo_cambio TC ON (to_char(TC.tca_fecha,'YYYY-MM-DD') = to_char(c.created,'YYYY-MM-DD'))
					LEFT JOIN ruc AS r ON (r.ruc = c.c_bpartner_id::VARCHAR)
				WHERE
					c.created::date BETWEEN '$anio-$mes-$desde' AND '$anio-$mes-$hasta'
				GROUP BY
					trans,
					emision,
					vencimiento,
					serie,
					numero,
					tipodi,
					ruc
				ORDER BY
					serie,
                    emision;
				";

		echo "<pre>sql_registros_gnv";
		echo $sql;
		echo "</pre>";

		if ($sqlca->query($sql) < 0)
		    return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

		    $a = $sqlca->fetchRow();

		    $result['gnv'][$i]['trans'] = $a['trans'];
		    $result['gnv'][$i]['emision'] = $a['emision'];
		    $result['gnv'][$i]['vencimiento'] = $a['vencimiento'];
		    $result['gnv'][$i]['tipo'] = $a['tipo'];
		    $result['gnv'][$i]['serie'] = $a['serie'];
		    $result['gnv'][$i]['numero'] = $a['numero'];
		    $result['gnv'][$i]['tipodi'] = $a['tipodi'];
		    $result['gnv'][$i]['ruc'] = $a['ruc'];
		    $result['gnv'][$i]['cliente'] = $a['cliente'];
		    $result['gnv'][$i]['vfexp'] = 0;
		    $result['gnv'][$i]['imponible'] = $a['imponible'];
		    $result['gnv'][$i]['exonerada'] = 0;
		    $result['gnv'][$i]['inafecto'] = 0;
		    $result['gnv'][$i]['isc'] = 0;
		    $result['gnv'][$i]['igv'] = $a['igv'];
		    $result['gnv'][$i]['otros'] = 0;
		    $result['gnv'][$i]['importe'] = $a['importe'];
		    $result['gnv'][$i]['tipocambio'] = $a['tipocambio'];
		    $result['gnv'][$i]['fecha2'] = "";
		    $result['gnv'][$i]['tipo2'] = "";
		    $result['gnv'][$i]['serie2'] = "";
		    $result['gnv'][$i]['numero2'] = "";

		    $result['gnv']['total_imponible'] += $a['imponible'];
		    $result['gnv']['total_igv'] += $a['igv'];
			$result['gnv']['total_importe'] += $a['importe'];
			
			$result['gnv']['balance'] = "0.00"; 

			// TOTALES
			$result['totales_imponible'] += $a['imponible'];
			$result['totales_igv'] += $a['igv'];
			$result['totales_balance'] += "0.00"; 
			$result['totales_importe'] += $a['importe'];

		}

		return $result;
	}

	function getFechaParametro($anio, $mes) {
		global $sqlca;

		/** OBTENEMOS FECHA **/
		$fecha_registro_ventas = $anio . "-" . $mes;

		/** OBTENEMOS PARAMETRO PARA REGISTRO DE VENTAS REAL **/
		$sql_reports_real_date = "
		SELECT 
			TRIM(par_valor) AS par_valor
		FROM
			int_parametros
		WHERE 
			par_nombre = 'reportsByRealDate';
		";

		if ($sqlca->query($sql_reports_real_date) < 0)
			return false;

		$row = $sqlca->fetchRow();
		$fecha_parametro = isset($row['par_valor']) ? $row['par_valor'] : 0;

		$dataParametro = array("fecha_registro_ventas" => $fecha_registro_ventas, "fecha_parametro" => $fecha_parametro);
		error_log("dataParametro");
		error_log(json_encode($dataParametro));

		return $dataParametro;

		/** 
		 * Si fecha parametro es mayor a fecha registro ventas, entonces el resultado sera negativo //ESTO NO DEBE HACER NADA
		 * Si fecha parametro y fecha actual son iguales      , entonces el resultado sera 0        //ESTO BUSCAMOS
		 * Si fecha parametro es menor a fecha registro ventas, entonces el resultado sera positivo //ESTO BUSCAMOS
		 */
		// $comparacion_fecha = strcmp($fecha_registro_ventas, $fecha_parametro);	

		// return array(
		// 	"fecha_registro_ventas" => $fecha_registro_ventas, 
		// 	"fecha_parametro"       => $fecha_parametro, 
		// 	"comparacion_fecha"     => $comparacion_fecha
		// );
	}

	function validateTableBySchema($table) {
		global $sqlca;
		$iStatusTable = $sqlca->query("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$table."'");
		error_log("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$table."'");
		return $iStatusTable;
	}

	function llenar_arreglo_objecto_imprimir($array_tmp_impresion, $a, $tipo_vista_monto) { //llenar_arreglo_objecto_imprimir
		$array_tmp_impresion['caja'] 		= $a['caja'];
		$array_tmp_impresion['emision'] 	= $a['emision'];
		$array_tmp_impresion['vencimiento'] 	= $a['vencimiento'];
		$array_tmp_impresion['tipo'] 		= $a['tipo'];
		$array_tmp_impresion['serie'] 		= $a['serie'];
		$array_tmp_impresion['numero'] 		= $a['numero'];
		$array_tmp_impresion['tipodi'] 		= $a['tipodi'];
		$array_tmp_impresion['ruc'] 		= $a['ruc'];
		$array_tmp_impresion['cliente'] 	= $a['cliente'];
		$array_tmp_impresion['vfexp'] 		= 0;
		$array_tmp_impresion['isc'] 		= 0;
		$array_tmp_impresion['otros'] 		= 0;
		$array_tmp_impresion['tipocambio'] 	= $a['tipocambio'];
		$array_tmp_impresion['fecha2'] 		= "";
		$array_tmp_impresion['tipo2'] 		= "";
		$array_tmp_impresion['serie2'] 		= "";
		$array_tmp_impresion['numero2'] 	= "";
		$array_tmp_impresion['tipo_pdf'] 	= $a['tipo_pdf'];
		//$array_tmp_impresion['estado'] 		= $a['estado'];
		$array_tmp_impresion['reffec'] 		= $a['reffec'];
		$array_tmp_impresion['reftip'] 		= $a['reftip'];
		$array_tmp_impresion['refser'] 		= $a['refser'];
		$array_tmp_impresion['refnum'] 		= $a['refnum'];


		//if((($a['es'] == '101' || $a['es'] == '201') && $a['taxoptional'] == '1') || ($a['igv'] == 0 || $a['igv'] == 0.00)){
		if($a['taxoptional'] == '1'){

			$imponible	= 0.00;
			$igv		= 0.00;
			$balance    = 0.00;
			$exonerada	= $a['exonerada'];
			$inafecto	= 0.00;
			$importe	= $a['importe'];

			//VARIABLES PARA LA SUMA TOTAL TICKETS 1
			$imponibleTOT	= 0.00;
			$igvTOT		    = 0.00;
			$balanceTOT     = 0.00;
			$exoneradaTOT	= $a['exonerada'];
			$inafectoTOT	= 0.00;
			$importeTOT	    = $a['importe'];

		}else{

			$imponible	= $a['imponible'];
			$igv		= $a['igv'];
			$balance    = $a['balance'];
			$inafecto 	= 0.00;
			$exonerada 	= 0.00;
			$importe	= $a['importe'];

			//VARIABLES PARA LA SUMA TOTAL TICKETS 1
			$imponibleTOT	= $a['imponible'];
			$igvTOT		    = $a['igv'];
			$balanceTOT     = $a['balance'];
			$inafectoTOT 	= 0.00;
			$exoneradaTOT	= 0.00;
			$importeTOT	    = $a['importe'];
		}

    	$array_tmp_impresion['imponible']	+= $imponible;
		$array_tmp_impresion['igv']			+= $igv;
		$array_tmp_impresion['balance']		+= $balance;
    	$array_tmp_impresion['exonerada']	+= $exonerada;
    	$array_tmp_impresion['inafecto']	+= $inafecto;
		$array_tmp_impresion['importe']		+= $importe;

		//Nuevos campos agregados
		$array_tmp_impresion['td'] = $a['td'];
		$array_tmp_impresion['rendi_gln'] = $a['rendi_gln'];
		$array_tmp_impresion['ruc_bd_interno'] = $a['ruc_bd_interno'];
		$array_tmp_impresion['id_trans'] = $a['id_trans'];

		return $array_tmp_impresion;
    }

    function obtenerSeries() {
    	global $sqlca;

   		$sql = "
			SELECT	
				num_seriedocumento,
				num_descdocumento,
				num_tipdocumento
			FROM
				int_num_documentos
			WHERE
				num_tipdocumento IN ('10','11', '20', '35') 
			ORDER BY 
				num_tipdocumento,
				num_seriedocumento,
				num_descdocumento;
			";

        if ($sqlca->query($sql) < 0)
            return false;

        $resultado = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {

            $a = $sqlca->fetchRow();

            $resultado[$i]['serie'] = $a['num_seriedocumento'];
            $resultado[$i]['documento'] = $a['num_descdocumento'];
            $resultado[$i]['tipodoc'] = $a['num_tipdocumento'];
        }

        return $resultado;
    }

    function obtieneListaEstaciones() {
        global $sqlca;

        $sql = "
		SELECT 
			ch_almacen, 
			trim(ch_nombre_almacen)
		FROM 
			inv_ta_almacenes
		WHERE 
			ch_clase_almacen='1'
		ORDER BY 
			ch_almacen;
		";

        if ($sqlca->query($sql) < 0)
            return false;

        $result = Array();

        for ($i = 0; $i < $sqlca->numrows(); $i++) {

            $a = $sqlca->fetchRow();

            $result[$a[0]] = $a[0] . " - " . $a[1];
        }

        return $result;
    }

    function obtenerAlma($almacen) {
        global $sqlca;

        $sql = "
SELECT
 EMPRE.ruc,
 EMPRE.razsocial
FROM
 inv_ta_almacenes AS ALMA
 JOIN int_ta_sucursales AS EMPRE
  USING ( ch_sucursal )
WHERE
 ALMA.ch_clase_almacen = '1'
 AND ALMA.ch_almacen = '" . $almacen . "'
LIMIT 1;
		";
	
        $iStatusSQL = $sqlca->query($sql);
        $arrResponseSQL = array(
            'status_sql' => $iStatusSQL,
            'message_sql' => $sqlca->get_error(),
            'sStatus' => 'danger',
            'sMessage' => 'Problemas al obtener datos de empresa',
        );
        if ( $iStatusSQL == 0 ) {
            $arrResponseSQL = array(
                'sStatus' => 'warning',
                'sMessage' => 'No existe empresa'
            );
        } else if ( $iStatusSQL > 0 ) {
        	$a = $sqlca->fetchRow();
            $arrResponseSQL = array(
                'sStatus' => 'success',
                'arrData' => $a
            );
        }
        return $arrResponseSQL;
    }

    //Esta funcion solo es para PLE - TXT
  	function ObtenerDocumentoReferencia($anio, $mes, $numDocumeto, $numSerieDocumento, $tipoDocumento, $arrCond) {
        global $sqlca;

        $arrDataPLERef = array(
            "fecha_emision_original" => "01/01/0001",
            "tipo_docu_original" => "00",
            "num_serie_original" => "-",
            "num_docu_original" => "-"
        );
		$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Problemas al buscar documento referencia', 'arrDataRef' => $arrDataPLERef);

        $sql = "
SELECT
 CASE
  WHEN substring(co.ch_fac_observacion2, length(co.ch_fac_observacion2)-1, length(co.ch_fac_observacion2))='10' THEN substring(co.ch_fac_observacion2, 0, length(co.ch_fac_observacion2)-1)||'01'
  ELSE substring(co.ch_fac_observacion2, 0, length(co.ch_fac_observacion2)-1)||'03'
 END AS ch_fac_observacion2,
 co.ch_fac_observacion3 
FROM
 fac_ta_factura_cabecera AS ca  
 INNER JOIN fac_ta_factura_complemento AS co ON(ca.ch_fac_tipodocumento = co.ch_fac_tipodocumento AND ca.ch_fac_seriedocumento = co.ch_fac_seriedocumento AND ca.ch_fac_numerodocumento = co.ch_fac_numerodocumento)
WHERE
 ca.ch_fac_tipodocumento = '" . $tipoDocumento . "'
 AND ca.ch_fac_seriedocumento = '" . $numSerieDocumento . "'
 AND ca.ch_fac_numerodocumento = '" . $numDocumeto . "'
		";

		$iStatus = $sqlca->query($sql);
		if ((int)$iStatus >= 1) {//Existe registro en la tabla fact_fa_factura_cabecera
	        $info_documento_referencia = $sqlca->fetchRow();

	        if (isset($info_documento_referencia[0]) && isset($info_documento_referencia[1])) {
	            $datos_integrado = explode("*", $info_documento_referencia[0]);
	            if (count($datos_integrado) == 3) {
	                $numDocumeto_Original = trim($datos_integrado[0]);
	                $numSerieDocumento_Original = trim($datos_integrado[1]);
	                $tipoDocumento_original = trim($datos_integrado[2]);
	                if (strlen(trim($numSerieDocumento_Original)) < 4) {
	                    $numSerieDocumento_Original = trim($numSerieDocumento_Original);
	                    $numSerieDocumento_Original = str_pad($numSerieDocumento_Original, 4, "0", STR_PAD_LEFT);
	                }

	                $fecha_original = trim($info_documento_referencia[1]);

	                if ($tipoDocumento_original == 12) {//quiere decir que la serie sera la de la maquina
	                    if (strlen($numSerieDocumento_Original) == 10) {
	                        $numSerieDocumento_Original = substr($numSerieDocumento_Original, 4, 6);
	                    }
	                }

	                $arrDataPLERef = array(
	                    "fecha_emision_original" => $fecha_original,
	                    "tipo_docu_original" => $tipoDocumento_original,
	                    "num_serie_original" => $numSerieDocumento_Original,
	                    "num_docu_original" => $numDocumeto_Original
	                );
	            } else {
	                $arrDataPLERef = array(
	                    "fecha_emision_original" => "01/01/0001",
	                    "tipo_docu_original" => "00",
	                    "num_serie_original" => "-",
	                    "num_docu_original" => "-"
	                );
	            }
	        } else {
	            $arrDataPLERef = array(
	                "fecha_emision_original" => "01/01/0001",
	                "tipo_docu_original" => "00",
	                "num_serie_original" => "-",
	                "num_docu_original" => "-"
	            );
	        }

			$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Documento de oficina encontrado', 'arrDataRef' => $arrDataPLERef);
    	} else if ((int)$iStatus == 0) {//Si no existe registro en la tabla fact_fa_factura_cabecera, entonces buscamos en la tabla pos_transYYYYMM
            $arrDataPLERef = array(
                "fecha_emision_original" => "01/01/0001",
                "tipo_docu_original" => "00",
                "num_serie_original" => "-",
                "num_docu_original" => "-"
            );
      		$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'No existe documento de referencia', 'arrDataRef' => $arrDataPLERef);
      		
      		//Formamos tabla pos_transYYYYMM
	    	$arrFecha = explode('-', $arrCond['dFechaEmision']);
	    	$table_pos_transym = 'pos_trans' . $anio . $mes;
			$table_pos_transym_ant = $arrCond['sNombreTabla_Ant'];
			$table_pos_transym_des = $arrCond['sNombreTabla_Des'];
			$status_table_ant = $arrCond['sStatusTabla_Ant'];
			$status_table_des = $arrCond['sStatusTabla_Des'];

			//Verificar si existe tabla pos_transYYYYMM
			$iStatusTable = $sqlca->query("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='".$table_pos_transym."'");

			if ( $iStatusTable == 1 ){ //Existe tabla
				$cond_caja = $arrCond['sCaja'];
				$cond_tipo_documento = $arrCond['sTipoDocumento'];
				$cond_trans = $arrCond['fIDTrans'];

				$sql = "";
				if ( $status_table_ant == true ) {
					$sql .= "
						(SELECT
							usr,
							CASE
							WHEN tm='V' AND td='B' THEN '03'
							WHEN tm='V' AND td='F' THEN '01'
							ELSE '07'
							END AS tiporef,
							TO_CHAR(fecha, 'DD/MM/YYYY') AS fecharef,
							SUBSTR(TRIM(usr), 0, 5) AS serieref,
							SUBSTR(TRIM(usr), 6) AS numref
						FROM
							" . $table_pos_transym_ant . "
						WHERE
							caja = '" . $cond_caja . "'
							AND td = '" . $cond_tipo_documento . "'
							AND trans = " . $cond_trans . "
							AND tm = 'V'
							AND grupo != 'D')
						
						UNION ALL
					";
				}

				$sql .= "
					(SELECT
						usr,
						CASE
						WHEN tm='V' AND td='B' THEN '03'
						WHEN tm='V' AND td='F' THEN '01'
						ELSE '07'
						END AS tiporef,
						TO_CHAR(fecha, 'DD/MM/YYYY') AS fecharef,
						SUBSTR(TRIM(usr), 0, 5) AS serieref,
						SUBSTR(TRIM(usr), 6) AS numref
					FROM
						" . $table_pos_transym . "
					WHERE
						caja = '" . $cond_caja . "'
						AND td = '" . $cond_tipo_documento . "'
						AND trans = " . $cond_trans . "
						AND tm = 'V'
						AND grupo != 'D')
				";

				if ( $status_table_des == true ) {
					$sql .= "
						UNION ALL
		
						(SELECT
							usr,
							CASE
							WHEN tm='V' AND td='B' THEN '03'
							WHEN tm='V' AND td='F' THEN '01'
							ELSE '07'
							END AS tiporef,
							TO_CHAR(fecha, 'DD/MM/YYYY') AS fecharef,
							SUBSTR(TRIM(usr), 0, 5) AS serieref,
							SUBSTR(TRIM(usr), 6) AS numref
						FROM
							" . $table_pos_transym_des . "
						WHERE
							caja = '" . $cond_caja . "'
							AND td = '" . $cond_tipo_documento . "'
							AND trans = " . $cond_trans . "
							AND tm = 'V'
							AND grupo != 'D')
					";
				}

				$sql .= "LIMIT 1;";

				error_log("trans: " . $cond_trans);
				error_log("sql: " . $sql);

    			$iStatus = $sqlca->query($sql);
				if ((int)$iStatus >= 1) {//Existe registro en la tabla pos_transYM
					$row = $sqlca->fetchRow();
					$arrDataPLERef = array(
	                    "fecha_emision_original" => $row['fecharef'],
	                    "tipo_docu_original" => $row['tiporef'],
	                    "num_serie_original" => $row['serieref'],
	                    "num_docu_original" => $row['numref'],
	                );
					$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Documento de playa encontrado', 'arrDataRef' => $arrDataPLERef);
				}
      		}
		}
		return $arrResponse;
	}

/*
    function ObtenerDocumentoReferencia($anio, $mes, $numDocumeto, $numSerieDocumento, $tipoDocumento) {
        global $sqlca;

        $fecha_postrans = $anio . "" . $mes;

        $sql1 = "
SELECT
 CASE
  WHEN substring(co.ch_fac_observacion2, length(co.ch_fac_observacion2)-1, length(co.ch_fac_observacion2))='10' THEN substring(co.ch_fac_observacion2, 0, length(co.ch_fac_observacion2)-1)||'01'
  ELSE substring(co.ch_fac_observacion2, 0, length(co.ch_fac_observacion2)-1)||'03'
 END AS ch_fac_observacion2,
 co.ch_fac_observacion3 
FROM
 fac_ta_factura_cabecera ca  
 INNER JOIN fac_ta_factura_complemento co ON(ca.ch_fac_tipodocumento = co.ch_fac_tipodocumento AND ca.ch_fac_seriedocumento = co.ch_fac_seriedocumento AND ca.ch_fac_numerodocumento = co.ch_fac_numerodocumento)
WHERE
 ca.ch_fac_tipodocumento = '" . $tipoDocumento . "'
 AND ca.ch_fac_seriedocumento = '" . $numSerieDocumento . "'
 AND ca.ch_fac_numerodocumento = '" . $numDocumeto . "'
UNION
(SELECT
 SUBSTR(TRIM(venta_tickes.fe), 6)||'*'||SUBSTR(TRIM(venta_tickes.fe), 0, 5)||'*'||(CASE WHEN SUBSTR(TRIM(venta_tickes.fe), 0, 2)='F' then '01' else '03' END) as ch_fac_observacion2,
 TO_CHAR(venta_tickes.diatickes::DATE,'DD/MM/YYYY') as ch_fac_observacion3
FROM
 (SELECT 
 (p.trans||'-'||p.caja) as tickes_refe,
p.trans,
p.usr as fe,
extorno.trans as trans_ext,
extorno.registro,
extorno.trans1,
p.fecha,
p.dia as diatickes
FROM
pos_trans$fecha_postrans p
INNER JOIN (
SELECT 
(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
fecha,
trans||'-'||caja as trans,
trans as trans1,
usr
FROM
pos_trans$fecha_postrans
WHERE
tm = 'A'
AND td IN ('B','F')
) as extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago ||  abs(p.cantidad) ||abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
AND td IN ('B','F')
AND tm = 'V'
AND p.trans < extorno.trans1
AND SUBSTR(TRIM(extorno.usr), 6) = '$numDocumeto'
AND SUBSTR(TRIM(extorno.usr), 0, 5) = '$numSerieDocumento'
ORDER BY
p.fecha asc
) AS venta_tickes
GROUP BY
venta_tickes.registro,
venta_tickes.trans_ext,
venta_tickes.fe,
venta_tickes.diatickes
);
				
				";

	/*
	echo "<pre>";
	echo $sql1;
	echo "</pre>";
	*/
/*
        if ($sqlca->query($sql1) < 0) {
            return array(
                "fecha_emision_original" => "01/01/0001",
                "tipo_docu_original" => "00",
                "num_serie_original" => "-",
                "num_docu_original" => "-"
            );
        }

        $info_documento_referencia = $sqlca->fetchRow();

        if (isset($info_documento_referencia[0]) && isset($info_documento_referencia[1])) {
            $datos_integrado = explode("*", $info_documento_referencia[0]);
            if (count($datos_integrado) == 3) {
                $numDocumeto_Original = trim($datos_integrado[0]);
                $numSerieDocumento_Original = trim($datos_integrado[1]);
                $tipoDocumento_original = trim($datos_integrado[2]);
                if (strlen(trim($numSerieDocumento_Original)) < 4) {
                    $numSerieDocumento_Original = trim($numSerieDocumento_Original);
                    $numSerieDocumento_Original = str_pad($numSerieDocumento_Original, 4, "0", STR_PAD_LEFT);
                }

                $fecha_original = trim($info_documento_referencia[1]);

                if ($tipoDocumento_original == 12) {//quiere decir que la serie sera la de la maquina
                    if (strlen($numSerieDocumento_Original) == 10) {
                        $numSerieDocumento_Original = substr($numSerieDocumento_Original, 4, 6);
                    }
                }

                return array(
                    "fecha_emision_original" => $fecha_original,
                    "tipo_docu_original" => $tipoDocumento_original,
                    "num_serie_original" => $numSerieDocumento_Original,
                    "num_docu_original" => $numDocumeto_Original
                );
            } else {

                return array(
                    "fecha_emision_original" => "01/01/0001",
                    "tipo_docu_original" => "00",
                    "num_serie_original" => "-",
                    "num_docu_original" => "-"
                );
            }
        } else {

            return array(
                "fecha_emision_original" => "01/01/0001",
                "tipo_docu_original" => "00",
                "num_serie_original" => "-",
                "num_docu_original" => "-"
            );
        }
    }
*/

	function verify_reference_sales_invoice_document($arrCond){
		echo "<pre>";
		print_r($arrCond);
		echo "</pre>";

		global $sqlca;

		$nombre_tabla = $arrCond['sNombreTabla'];
		$nombre_tabla_ant = $arrCond['sNombreTabla_Ant'];
		$nombre_tabla_des = $arrCond['sNombreTabla_Des'];
		$status_tabla_ant = $arrCond['sStatusTabla_Ant'];
		$status_tabla_des = $arrCond['sStatusTabla_Des'];
		$cond_codigo_almacen = $arrCond['sCodigoAlmacen'];
		$cond_caja = $arrCond['sCaja'];
		$cond_tipo_documento = $arrCond['sTipoDocumento'];
		$cond_trans = $arrCond['fIDTrans'];

  		$arrResponse = array('sStatus' => 'warning', 'sMessage' => 'No existe documento de referencia');

		$sql = "";
		if ( $status_tabla_ant == true ) {
			$sql .= "
				(SELECT
					usr,
					CASE
					WHEN tm='V' AND td='B' THEN '03'
					WHEN tm='V' AND td='F' THEN '01'
					ELSE '07'
					END AS tiporef,
					TO_CHAR(fecha, 'DD/MM/YYYY') AS fecharef,
					SUBSTR(TRIM(usr), 0, 5) AS serieref,
					SUBSTR(TRIM(usr), 6) AS numref,
					ROUND(importe, 4) AS importe,
					es AS es,
					caja AS caja,
					trans AS id_trans,
					(CASE
						WHEN td = 'N' THEN '00000000000'
						WHEN ruc = '' THEN '99999999'
						WHEN ruc IS NULL THEN '99999999'
					ELSE
						ruc
					END) AS ruc,
					'" . $nombre_tabla_ant . "' AS tabla
				FROM
					" . $nombre_tabla_ant . "
				WHERE
					es = '" . $cond_codigo_almacen . "'
					AND caja = '" . $cond_caja . "'
					AND td = '" . $cond_tipo_documento . "'
					AND trans = " . $cond_trans . "
					AND tm = 'V'
					AND grupo != 'D')
				
				UNION ALL
			";
		}

		$sql .= "
			(SELECT
				usr,
				CASE
				WHEN tm='V' AND td='B' THEN '03'
				WHEN tm='V' AND td='F' THEN '01'
				ELSE '07'
				END AS tiporef,
				TO_CHAR(fecha, 'DD/MM/YYYY') AS fecharef,
				SUBSTR(TRIM(usr), 0, 5) AS serieref,
				SUBSTR(TRIM(usr), 6) AS numref,
				ROUND(importe, 4) AS importe,
				es AS es,
				caja AS caja,
				trans AS id_trans,
				(CASE
					WHEN td = 'N' THEN '00000000000'
					WHEN ruc = '' THEN '99999999'
					WHEN ruc IS NULL THEN '99999999'
				ELSE
					ruc
				END) AS ruc,
				'" . $nombre_tabla . "' AS tabla
			FROM
				" . $nombre_tabla . "
			WHERE
				es = '" . $cond_codigo_almacen . "'
				AND caja = '" . $cond_caja . "'
				AND td = '" . $cond_tipo_documento . "'
				AND trans = " . $cond_trans . "
				AND tm = 'V'
				AND grupo != 'D')
		";

		if ( $status_tabla_des == true ) {
			$sql .= "
				UNION ALL

				(SELECT
					usr,
					CASE
					WHEN tm='V' AND td='B' THEN '03'
					WHEN tm='V' AND td='F' THEN '01'
					ELSE '07'
					END AS tiporef,
					TO_CHAR(fecha, 'DD/MM/YYYY') AS fecharef,
					SUBSTR(TRIM(usr), 0, 5) AS serieref,
					SUBSTR(TRIM(usr), 6) AS numref,
					ROUND(importe, 4) AS importe,
					es AS es,
					caja AS caja,
					trans AS id_trans,
					(CASE
						WHEN td = 'N' THEN '00000000000'
						WHEN ruc = '' THEN '99999999'
						WHEN ruc IS NULL THEN '99999999'
					ELSE
						ruc
					END) AS ruc,
					'" . $nombre_tabla_des . "' AS tabla
				FROM
					" . $nombre_tabla_des . "
				WHERE
					es = '" . $cond_codigo_almacen . "'
					AND caja = '" . $cond_caja . "'
					AND td = '" . $cond_tipo_documento . "'
					AND trans = " . $cond_trans . "
					AND tm = 'V'
					AND grupo != 'D')
			";
		}

		$sql .= "LIMIT 1;";

		echo "<pre>";
		echo $sql;
		echo "</pre>";

		$iStatus = $sqlca->query($sql);
		if ((int)$iStatus >= 1) {//Existe registro en la tabla pos_transYM
			$row = $sqlca->fetchRow();
			$arrResponse = array('sStatus' => 'success', 'sMessage' => 'Documento de playa encontrado', 'arrDataModel' => $row);
		}
		echo "<pre>";
		print_r($arrResponse);
		echo "</pre>";
    	return $arrResponse;
	}

}

