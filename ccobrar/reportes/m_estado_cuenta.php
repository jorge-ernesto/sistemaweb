<?php
class EstadoCuentaModel extends Model {

	function ModelReportePDF($fecha, $Nu_Tipo_Cliente) {
	global $sqlca;

		$cond_tipo_cliente = "";

		if($Nu_Tipo_Cliente == "1")//CREDITO
			$cond_tipo_cliente = "AND cli.cli_ndespacho_efectivo = '0' AND cli.cli_anticipo = 'N'";
		elseif($Nu_Tipo_Cliente == "2")//EFECTIVO
			$cond_tipo_cliente = "AND cli.cli_ndespacho_efectivo = '1' AND cli.cli_anticipo = 'N'";
		elseif($Nu_Tipo_Cliente == "S")//ANTICIPO
			$cond_tipo_cliente = "AND cli.cli_anticipo = 'S' AND cli.cli_ndespacho_efectivo='0'";

		$sql = "
		SELECT
			cab.cli_codigo AS CLIENTE,
			cli.cli_razsocial AS RAZONSOCIAL,
			cab.ch_tipdocumento AS TIPODOCUMENTO,
			cab.ch_seriedocumento AS SERIEDOCUMENTO,
			cab.ch_numdocumento AS NUMDOCUMENTO,
			mone.tab_desc_breve AS MONEDA,
			TO_CHAR(cab.dt_fechaemision, 'DD/MM/YYYY') AS FECHAEMISION,
			cab.dt_fechavencimiento AS FECHAVENCIMIENTO,
			gen.tab_desc_breve||' - '||trim(cab.ch_seriedocumento)||' - '||trim(cab.ch_numdocumento) as DOCUMENTO,
			(CASE WHEN det.ch_tipmovimiento = '1' AND det.ch_moneda = '01' THEN (CASE WHEN cab.ch_tipdocumento = '21' THEN (cab.nu_importetotal * -1) ELSE cab.nu_importetotal END) ELSE 0.00 END) AS IMPORTEINICIAL_SOLES,
			(CASE WHEN det.ch_tipmovimiento = '1' AND det.ch_moneda = '02' THEN (CASE WHEN cab.ch_tipdocumento = '21' THEN (cab.nu_importetotal * -1) ELSE cab.nu_importetotal END) ELSE 0.00 END) AS IMPORTEINICIAL_DOLARES,
			(CASE WHEN det.ch_tipmovimiento = '2' AND det.ch_moneda = '01' THEN nu_importemovimiento ELSE 0.00 END) AS Nu_Importe_Pagos_Soles,
			(CASE WHEN det.ch_tipmovimiento = '2' AND det.ch_moneda = '02' THEN nu_importemovimiento ELSE 0.00 END) AS Nu_Importe_Pagos_Dolares,
			(CASE WHEN
				det.ch_tipmovimiento = '2' AND (SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) > 1
			THEN
				(SELECT
					SUM(det2.nu_importemovimiento)
				FROM
					ccob_ta_detalle as det2
				WHERE
					det2.ch_tipmovimiento 		= '1'
					AND det2.ch_tipdocumento	= det.ch_tipdocumento
					AND det2.ch_seriedocumento	= det.ch_seriedocumento
					AND det2.ch_numdocumento	= det.ch_numdocumento
					AND det2.cli_codigo 		= det.cli_codigo
					AND det2.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				) - 
				(CASE WHEN
					det.ch_tipmovimiento = '2'
				THEN
					(SELECT
						SUM(det3.nu_importemovimiento)
					FROM
						ccob_ta_detalle as det3
					WHERE
						det3.ch_tipmovimiento = '2'
						AND (CASE WHEN
								det3.ch_identidad = '2'
							THEN
								det3.ch_identidad = '2'
							ELSE
								det3.ch_identidad::INTEGER BETWEEN 2 AND det.ch_identidad::integer
							END)
						AND det3.ch_tipdocumento	= det.ch_tipdocumento
						AND det3.ch_seriedocumento	= det.ch_seriedocumento
						AND det3.ch_numdocumento	= det.ch_numdocumento
						AND det3.cli_codigo 		= det.cli_codigo
						AND det3.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
					)
				ELSE
					0.00
				END)
			ELSE
				CASE WHEN
					(SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) = 1
				THEN
					det.nu_importemovimiento
				END
			END) as saldo_soles,
			(CASE WHEN
				det.ch_tipmovimiento = '2' AND (SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) > 1
			THEN
				(SELECT
					SUM(det2.nu_importemovimiento)
				FROM
					ccob_ta_detalle as det2
				WHERE
					det2.ch_tipmovimiento 		= '1'
					AND det2.ch_tipdocumento	= det.ch_tipdocumento
					AND det2.ch_seriedocumento	= det.ch_seriedocumento
					AND det2.ch_numdocumento	= det.ch_numdocumento
					AND det2.cli_codigo 		= det.cli_codigo
					AND det2.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				) - 
				(CASE WHEN
					det.ch_tipmovimiento = '2'
				THEN
					(SELECT
						SUM(det3.nu_importemovimiento)
					FROM
						ccob_ta_detalle as det3
					WHERE
						det3.ch_tipmovimiento = '2'
						AND (CASE WHEN
								det3.ch_identidad = '2'
							THEN
								det3.ch_identidad = '2'
							ELSE
								det3.ch_identidad::INTEGER BETWEEN 2 AND det.ch_identidad::integer
							END)
						AND det3.ch_tipdocumento	= det.ch_tipdocumento
						AND det3.ch_seriedocumento	= det.ch_seriedocumento
						AND det3.ch_numdocumento	= det.ch_numdocumento
						AND det3.cli_codigo 		= det.cli_codigo
						AND det3.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
					)
				ELSE
					0.00
				END)
			ELSE
				CASE WHEN
					(SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) = 1
				THEN
					det.nu_importemovimiento
				END
			END) as saldo_dolares
		FROM
			ccob_ta_cabecera cab
			JOIN ccob_ta_detalle AS det ON(cab.cli_codigo = det.cli_codigo AND cab.ch_tipdocumento = det.ch_tipdocumento AND cab.ch_seriedocumento = det.ch_seriedocumento AND cab.ch_numdocumento = det.ch_numdocumento)
			JOIN int_clientes AS cli ON(cab.cli_codigo = cli.cli_codigo)
			LEFT JOIN int_tabla_general AS mone ON(cab.ch_moneda = (substring(trim(mone.tab_elemento) for 2 from length(trim(mone.tab_elemento))-1)) AND mone.tab_tabla='04' AND mone.tab_elemento != '000000')
			LEFT JOIN int_tabla_general AS gen ON(cab.ch_tipdocumento = substring(trim(gen.tab_elemento) for 2 from length(trim(gen.tab_elemento))-1) AND gen.tab_tabla ='08' AND gen.tab_elemento != '000000')
			LEFT JOIN (SELECT
				ch_tipdocumento,
				ch_seriedocumento,
				ch_numdocumento,
				cli_codigo,
				nu_importemovimiento AS Nu_Importe_Movimiento_Detalle_Inclusion
			FROM
				ccob_ta_detalle	
			WHERE
				ch_tipdocumento IN ('10','11','20','21','22')
				AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				AND ch_tipmovimiento = '1'
			) AS CDINCLUSION ON (cab.ch_tipdocumento = CDINCLUSION.ch_tipdocumento and cab.ch_seriedocumento = CDINCLUSION.ch_seriedocumento and cab.ch_numdocumento = CDINCLUSION.ch_numdocumento AND cab.cli_codigo = CDINCLUSION.cli_codigo)
			LEFT JOIN (SELECT
				ch_tipdocumento,
				ch_seriedocumento,
				ch_numdocumento,
				cli_codigo,
				SUM(nu_importemovimiento) AS Nu_Importe_Movimiento_Detalle_Cancelacion
			FROM
				ccob_ta_detalle	
			WHERE
				ch_tipdocumento IN ('10','11','20','21','22')
				AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				AND ch_tipmovimiento = '2'
			GROUP BY
				1,2,3,4
			) AS CDCANCELACION ON (cab.ch_tipdocumento = CDCANCELACION.ch_tipdocumento and cab.ch_seriedocumento = CDCANCELACION.ch_seriedocumento and cab.ch_numdocumento = CDCANCELACION.ch_numdocumento AND cab.cli_codigo = CDCANCELACION.cli_codigo)
		WHERE
			cab.ch_tipdocumento IN ('10','11','20','21','22')
			AND det.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
			AND COALESCE(CDINCLUSION.Nu_Importe_Movimiento_Detalle_Inclusion, 0) > COALESCE(CDCANCELACION.Nu_Importe_Movimiento_Detalle_Cancelacion, 0)
			" . $cond_tipo_cliente . "
	  	ORDER BY
			1,
			det.dt_fechamovimiento,
			3,
			4,
			5;
		";

		//echo "<pre>".$sql."</pre>";

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['CLIENTE']					= $a[0];
			$resultado[$i]['RAZONSOCIAL']				= $a[1];
			$resultado[$i]['TIPODOCUMENTO'] 			= $a[2];
			$resultado[$i]['SERIEDOCUMENTO']			= $a[3];
			$resultado[$i]['NUMDOCUMENTO'] 				= $a[4];
			$resultado[$i]['MONEDA']					= $a[5];
			$resultado[$i]['FECHAEMISION']				= $a[6];
			$resultado[$i]['FECHAVENCIMIENTO'] 			= $a[7];
			$resultado[$i]['DOCUMENTO']					= $a[8];
			$resultado[$i]['IMPORTEINICIAL_SOLES'] 		= $a[9];
			$resultado[$i]['IMPORTEINICIAL_DOLARES']	= $a[10];
			$resultado[$i]['PAGO_SOLES']				= $a[11];
			$resultado[$i]['PAGO_DOLARES'] 				= $a[12];
			$resultado[$i]['SALDO_SOLES']				= $a[13];
			$resultado[$i]['SALDO_DOLARES'] 			= $a[14];
			
		}
		return $resultado;
	}

	function ModelReportePDFCLIENTE($fecha, $codcliente, $Nu_Tipo_Cliente) {
	global $sqlca;

		$cond_tipo_cliente = "";

		if($Nu_Tipo_Cliente == "1")//CREDITO
			$cond_tipo_cliente = "AND cli.cli_ndespacho_efectivo = '0' AND cli.cli_anticipo = 'N'";
		elseif($Nu_Tipo_Cliente == "2")//EFECTIVO
			$cond_tipo_cliente = "AND cli.cli_ndespacho_efectivo = '1' AND cli.cli_anticipo = 'N'";
		elseif($Nu_Tipo_Cliente == "S")//ANTICIPO
			$cond_tipo_cliente = "AND cli.cli_anticipo = 'S' AND cli.cli_ndespacho_efectivo='0'";

		$sql = "
		SELECT
			cab.cli_codigo as CLIENTE,                               
			cli.cli_razsocial AS RAZONSOCIAL,                        
			det.ch_tipdocumento AS TIPODOCUMENTO,                    
			det.ch_seriedocumento AS SERIEDOCUMENTO,                 
			det.ch_numdocumento AS NUMDOCUMENTO,               
			mone.tab_desc_breve AS MONEDA,                                 
			cab.dt_fechaemision AS FECHAEMISION,                     
			cab.dt_fechavencimiento AS FECHAVENCIMIENTO,
			gen.tab_desc_breve||' - '||trim(cab.ch_seriedocumento)||' - '||trim(cab.ch_numdocumento) as DOCUMENTO,			
			(CASE WHEN det.ch_tipmovimiento = '1' AND det.ch_moneda = '01' THEN (CASE WHEN cab.ch_tipdocumento = '21' THEN (cab.nu_importetotal * -1) ELSE cab.nu_importetotal END) ELSE 0.00 END) AS IMPORTEINICIAL_SOLES,
			(CASE WHEN det.ch_tipmovimiento = '1' AND det.ch_moneda = '02' THEN (CASE WHEN cab.ch_tipdocumento = '21' THEN (cab.nu_importetotal * -1) ELSE cab.nu_importetotal END) ELSE 0.00 END) AS IMPORTEINICIAL_DOLARES,
			(CASE WHEN det.ch_tipmovimiento = '2' AND det.ch_moneda = '01' THEN nu_importemovimiento ELSE 0.00 END) AS Nu_Importe_Pagos_Soles,
			(CASE WHEN det.ch_tipmovimiento = '2' AND det.ch_moneda = '02' THEN nu_importemovimiento ELSE 0.00 END) AS Nu_Importe_Pagos_Dolares,
			(CASE WHEN
				det.ch_tipmovimiento = '2' AND (SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) > 1
			THEN
				(SELECT
					SUM(det2.nu_importemovimiento)
				FROM
					ccob_ta_detalle as det2
				WHERE
					det2.ch_tipmovimiento 		= '1'
					AND det2.ch_tipdocumento	= det.ch_tipdocumento
					AND det2.ch_seriedocumento	= det.ch_seriedocumento
					AND det2.ch_numdocumento	= det.ch_numdocumento
					AND det2.cli_codigo 		= det.cli_codigo
					AND det2.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				) - 
				(CASE WHEN
					det.ch_tipmovimiento = '2'
				THEN
					(SELECT
						SUM(det3.nu_importemovimiento)
					FROM
						ccob_ta_detalle as det3
					WHERE
						det3.ch_tipmovimiento = '2'
						AND (CASE WHEN
								det3.ch_identidad = '2'
							THEN
								det3.ch_identidad = '2'
							ELSE
								det3.ch_identidad::INTEGER BETWEEN 2 AND det.ch_identidad::integer
							END)
						AND det3.ch_tipdocumento	= det.ch_tipdocumento
						AND det3.ch_seriedocumento	= det.ch_seriedocumento
						AND det3.ch_numdocumento	= det.ch_numdocumento
						AND det3.cli_codigo 		= det.cli_codigo
						AND det3.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
					)
				ELSE
					0.00
				END)
			ELSE
				CASE WHEN
					(SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) = 1
				THEN
					det.nu_importemovimiento
				END
			END) as saldo_soles,
			(CASE WHEN
				det.ch_tipmovimiento = '2' AND (SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) > 1
			THEN
				(SELECT
					SUM(det2.nu_importemovimiento)
				FROM
					ccob_ta_detalle as det2
				WHERE
					det2.ch_tipmovimiento 		= '1'
					AND det2.ch_tipdocumento	= det.ch_tipdocumento
					AND det2.ch_seriedocumento	= det.ch_seriedocumento
					AND det2.ch_numdocumento	= det.ch_numdocumento
					AND det2.cli_codigo 		= det.cli_codigo
					AND det2.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				) - 
				(CASE WHEN
					det.ch_tipmovimiento = '2'
				THEN
					(SELECT
						SUM(det3.nu_importemovimiento)
					FROM
						ccob_ta_detalle as det3
					WHERE
						det3.ch_tipmovimiento = '2'
						AND (CASE WHEN
								det3.ch_identidad = '2'
							THEN
								det3.ch_identidad = '2'
							ELSE
								det3.ch_identidad::INTEGER BETWEEN 2 AND det.ch_identidad::integer
							END)
						AND det3.ch_tipdocumento	= det.ch_tipdocumento
						AND det3.ch_seriedocumento	= det.ch_seriedocumento
						AND det3.ch_numdocumento	= det.ch_numdocumento
						AND det3.cli_codigo 		= det.cli_codigo
						AND det3.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
					)
				ELSE
					0.00
				END)
			ELSE
				CASE WHEN
					(SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')) = 1
				THEN
					det.nu_importemovimiento
				END
			END) as saldo_dolares
		FROM
			ccob_ta_cabecera cab
			INNER JOIN ccob_ta_detalle AS det ON(cab.ch_tipdocumento = det.ch_tipdocumento and cab.ch_seriedocumento = det.ch_seriedocumento and cab.ch_numdocumento = det.ch_numdocumento)
			INNER JOIN int_clientes AS cli ON(cab.cli_codigo = cli.cli_codigo)
			LEFT JOIN int_tabla_general AS mone ON(det.ch_moneda = (substring(trim(mone.tab_elemento) for 2 from length(trim(mone.tab_elemento))-1)) AND mone.tab_tabla='04' AND mone.tab_elemento != '000000')
			LEFT JOIN int_tabla_general AS gen ON(cab.ch_tipdocumento = substring(trim(gen.tab_elemento) for 2 from length(trim(gen.tab_elemento))-1) AND gen.tab_tabla ='08' AND mone.tab_elemento != '000000')
			LEFT JOIN (SELECT
				ch_tipdocumento,
				ch_seriedocumento,
				ch_numdocumento,
				cli_codigo,
				nu_importemovimiento AS Nu_Importe_Movimiento_Detalle_Inclusion
			FROM
				ccob_ta_detalle	
			WHERE
				ch_tipdocumento IN ('10','11','20','21','22')
				AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				AND ch_tipmovimiento = '1'
			) AS CDINCLUSION ON (cab.ch_tipdocumento = CDINCLUSION.ch_tipdocumento and cab.ch_seriedocumento = CDINCLUSION.ch_seriedocumento and cab.ch_numdocumento = CDINCLUSION.ch_numdocumento AND cab.cli_codigo = CDINCLUSION.cli_codigo)
			LEFT JOIN (SELECT
				ch_tipdocumento,
				ch_seriedocumento,
				ch_numdocumento,
				cli_codigo,
				SUM(nu_importemovimiento) AS Nu_Importe_Movimiento_Detalle_Cancelacion
			FROM
				ccob_ta_detalle	
			WHERE
				ch_tipdocumento IN ('10','11','20','21','22')
				AND dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
				AND ch_tipmovimiento = '2'
			GROUP BY
				1,2,3,4
			) AS CDCANCELACION ON (cab.ch_tipdocumento = CDCANCELACION.ch_tipdocumento and cab.ch_seriedocumento = CDCANCELACION.ch_seriedocumento and cab.ch_numdocumento = CDCANCELACION.ch_numdocumento AND cab.cli_codigo = CDCANCELACION.cli_codigo)
		WHERE
			cab.ch_tipdocumento IN ('10','11','20','21','22')
			AND det.dt_fechamovimiento <= to_date('" . $fecha . "','DD/MM/YYYY')
			AND COALESCE(CDINCLUSION.Nu_Importe_Movimiento_Detalle_Inclusion, 0) > COALESCE(CDCANCELACION.Nu_Importe_Movimiento_Detalle_Cancelacion, 0)
			" . $cond_tipo_cliente . "
		";

		if($todo != '01')
			$sql .= "AND cab.cli_codigo = '" . $codcliente . "'";

		$sql .= "
			ORDER BY
				1,
				det.dt_fechamovimiento,
				3,
				4,
				5;
		";

		// echo "<pre>".$sql."</pre>";

		if ($sqlca->query($sql)<=0)
			return $sqlca->get_error();
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['CLIENTE']					= $a[0];
			$resultado[$i]['RAZONSOCIAL']				= $a[1];
			$resultado[$i]['TIPODOCUMENTO'] 			= $a[2];
			$resultado[$i]['SERIEDOCUMENTO']			= $a[3];
			$resultado[$i]['NUMDOCUMENTO'] 				= $a[4];
			$resultado[$i]['MONEDA']					= $a[5];
			$resultado[$i]['FECHAEMISION']				= $a[6];
			$resultado[$i]['FECHAVENCIMIENTO'] 			= $a[7];
			$resultado[$i]['DOCUMENTO']					= $a[8];
			$resultado[$i]['IMPORTEINICIAL_SOLES'] 		= $a[9];
			$resultado[$i]['IMPORTEINICIAL_DOLARES']	= $a[10];
			$resultado[$i]['PAGO_SOLES']				= $a[11];
			$resultado[$i]['PAGO_DOLARES'] 				= $a[12];
			$resultado[$i]['SALDO_SOLES']				= $a[13];
			$resultado[$i]['SALDO_DOLARES'] 			= $a[14];
		}
		return $resultado;
	}
}
