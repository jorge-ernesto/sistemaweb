<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


class ModelContingenciaFE extends Model {

	function GetAlmacen() {
		global $sqlca;

		try {

			$sql = "
				SELECT
					ch_almacen as nualmacen,
					TRIM(ch_almacen) || ' - ' || TRIM(ch_nombre_almacen) as noalmacen
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen = '1'
				ORDER BY
					ch_almacen;
			";

			if($sqlca->query($sql) <= 0){
				throw new Exception("Error Almacen");
			}

			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}

			return $registro;

		}catch(Exception $e){
			throw $e;
		}

	}

	function GetEmpresa($nualmacen){
		global $sqlca;

		$cond = NULL;

		if($nualmacen != 'T'){
			$cond = "
					WHERE
						ch_sucursal = '".$nualmacen."'";
		}

		try {

			$query = "
				SELECT
					ruc as nuruc
				FROM
					int_ta_sucursales
				$cond
				LIMIT 1;
			";

			if($sqlca->query($query) <= 0){
				throw new Exception("Error Empresa");
			}

			$data = $sqlca->fetchRow();

			return $data['nuruc'];

		}catch(Exception $e){
			throw $e;
		}
	}

	function ContingenciaFE($data){
		global $sqlca;
		$cboalmacen 	= $data['cboalmacen'];
		$cbomtc 		= $data['cbomtc'];//MTC = Motivo Tipo Contingencia
		$cbotv 			= $data['cbotv'];
		$txtnofechaini 	= $data['txtnofechaini'];
		$txtnofechafin 	= $data['txtnofechafin'];

		$postrans 		= "pos_trans" . substr($txtnofechaini,0,4) . substr($txtnofechaini,5,2);

		$condalmacentk 	= NULL;
		$condalmacencm 	= NULL;

		if($cboalmacen != 'T'){
			$condalmacentk	= "AND es = '" . $cboalmacen . "'";//TK = Tickets 
			$condalmacencm	= "AND fc.ch_almacen = '" . $cboalmacen . "'";//CM = Comprabantes Manuales
		}

		try {
			$registros = array();
			$sql = "";

			if($cbotv == 'T' || $cbotv == 'TK'){
			$sql .= "
				SELECT * FROM (
					SELECT
						'".$cbomtc."'::TEXT AS nomotivo_contingencia,--MTC = Motivo Tipo Contingencia
						'01'::TEXT AS tipoop,
						TO_CHAR(TKB.femision, 'DD/MM/YYYY') AS femision,
						'12'::TEXT AS nutd,
						TKB.noserie,
						MIN(TKB.nuticketb)::TEXT AS nudocumento_inicial,
						MAX(TKB.nuticketb)::TEXT AS nudocumento_final,
						'0'::TEXT AS nutd_identidad,
						'0'::TEXT AS nudocumento_identidad,
						'CONSOLIDADO DEL DIA - VARIOS'::TEXT AS nodocumento_identidad,
						'PEN'::TEXT as moneda,
						SUM(TKB.nuvalor_venta_og) AS nuvalor_venta_og,--OG = Operaciones Gravadas
						SUM(TKB.nuvalor_venta_oe) AS nuvalor_venta_oe,--OE = Operaciones Exoneradas
						0 AS nuvalor_venta_oi,--OI = Operaciones Inafectas
						0 AS nuvalor_venta_ex,--OX = Operaciones Exportacion
						0 AS nuisc,--ISC = Impuesto Selectivo al Consumo
						SUM(TKB.nuigv) AS nuigv,
						0 as nuotros_cargos,--Que no forman parte del(os) valor(es) de venta - # 15
						SUM(TKB.nuvalor_venta_og + TKB.nuvalor_venta_oe + TKB.nuigv) AS nutotal,
						'|'::TEXT AS nutd_referencia,--APLICA SOLO (nota de débito o la nota de crédito)
						'|'::TEXT AS nuserie_referencia,--APLICA SOLO (nota de débito o la nota de crédito)
						'|'::TEXT AS nunumero_referencia--APLICA SOLO (nota de débito o la nota de crédito)
					FROM
					(
					SELECT DISTINCT
						ON (TKB.nuticketb) TKB.nuticketb AS _nuticketb,
						TKB.nuticket AS nuticketb,
						TKB.femision AS femision,
						TKB.nucaja AS nucaja,
						TKB.nuturno AS nuturno,
						TKB.nuvalor_venta_og AS nuvalor_venta_og,--OG = Operaciones Gravadas
						TKB.nuvalor_venta_oe AS nuvalor_venta_oe,--OG = Operaciones Exoneradas
						TKB.nuigv AS nuigv,
						TKF.nuticket AS nuticketf,
						cfp.nu_posz_z_serie AS noserie
					FROM
					(
						SELECT
							es AS nualmacen,
							dia AS femision,
							caja AS nucaja,
							turno AS nuturno,
							trans AS nuticket,
							es||dia||caja||turno||trans AS nuticketb,
							(CASE WHEN SUM(igv) = 0.0000 THEN 0 ELSE ROUND(SUM(importe - igv), 2) END) AS nuvalor_venta_og,--BI
							(CASE WHEN SUM(igv) = 0.0000 THEN ROUND(SUM(importe), 2) ELSE 0 END) AS nuvalor_venta_oe,--OE = Operaciones Exoneradas,
							ROUND(SUM(igv), 2) AS nuigv
						FROM
							".$postrans."
						WHERE
							tm 		IN ('V', 'D')
							AND td 	= 'B'
							AND usr = ''
							AND dia BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
							".$condalmacentk."
						GROUP BY
							nualmacen,
							dia,
							caja,
							turno,
							trans
					) AS TKB

					LEFT JOIN pos_z_cierres cfp ON(TKB.nucaja = cfp.ch_posz_pos AND TKB.femision = cfp.dt_posz_fecha_sistema::DATE AND TKB.nuturno::INTEGER = cfp.nu_posturno AND TKB.nualmacen = cfp.ch_sucursal)

					LEFT JOIN
					(SELECT
						es AS nualmacen,
						dia AS femision,
						caja AS nucaja,
						turno AS nuturno,
						trans AS nuticket
					FROM
						".$postrans."
					WHERE
						tm 		= 'V'
						AND td 	= 'F'
						AND usr = ''
						AND dia BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
						".$condalmacentk."
					) AS TKF ON
					(
						TKF.nualmacen 		= TKB.nualmacen	
						AND TKF.femision 	= TKB.femision
						AND TKF.nucaja 	= TKB.nucaja
						AND TKF.nuturno 	= TKB.nuturno
						AND TKB.nuticket < TKF.nuticket
					)
					ORDER BY
						_nuticketb,
						nuticketf
					) AS TKB--BOLETAS
					GROUP BY
						TKB.femision,
						TKB.noserie,
						TKB.nuturno
				) AS TKB--FIN TICKETS BOLETAS
				UNION ALL--TICKETS FACTURAS DE VENTAS QUE SERAN ANULADAS POR UN EXTORNO
				(
				SELECT DISTINCT
					'1'::TEXT AS nomotivo_contingencia,--MTC = Motivo Tipo Contingencia
					'01'::TEXT AS tipoop,
					TO_CHAR(TKF.femision, 'DD/MM/YYYY') AS femision,
					'12'::TEXT AS nutd,
					cfp.nu_posz_z_serie AS noserie,
					TKF.nuticket::TEXT AS nudocumento_inicial,
					TKF.nuticket::TEXT AS nudocumento_final,
					(CASE
						WHEN TRIM(TKF.nuruc) = '99999999999' THEN '0'
						WHEN CHAR_LENGTH(TRIM(TKF.nuruc)) = 11 THEN '6'
						WHEN CHAR_LENGTH(TRIM(TKF.nuruc)) = 8 THEN '1'
						ELSE '0'
					END)::TEXT AS nutd_identidad,
					TKF.nuruc::TEXT AS nudocumento_identidad,
					CASE WHEN ruc.razsocial IS NULL OR ruc.razsocial = '' THEN '' ELSE SUBSTR(ruc.razsocial, 0, 60)::TEXT END AS nodocumento_identidad,
					'PEN'::TEXT as moneda,
					0.00 AS nuvalor_venta_og,--OG = Operaciones Gravadas
					0 AS nuvalor_venta_oe,--OE = Operaciones Exoneradas
					0 AS nuvalor_venta_oi,--OI = Operaciones Inafectas
					0 AS nuvalor_venta_ex,--OX = Operaciones Exportacion
					0 AS nuisc,--ISC = Impuesto Selectivo al Consumo
					0.00 AS nuigv,
					0 as nuotros_cargos,--Que no forman parte del(os) valor(es) de venta - # 15
					0.00 AS nutotal,
					'|'::TEXT AS nutd_referencia,--APLICA SOLO (nota de débito o la nota de crédito)
					'|'::TEXT AS nuserie_referencia,--APLICA SOLO (nota de débito o la nota de crédito)
					'|'::TEXT AS nunumero_referencia--APLICA SOLO (nota de débito o la nota de crédito)
				FROM
					(
						(SELECT
							TKF.es AS nues,
							TKF.dia AS femision,
							TKF.caja AS nucaja,
							TKF.turno AS nuturno,
							TKF.trans AS nuticket,
							TKF.ruc AS nuruc
						FROM
							".$postrans." TKF
							INNER JOIN (
								SELECT 
									(caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
									fecha,
									es||caja||trans AS idticket
								FROM
									".$postrans."
								WHERE
									tm 		= 'A'
									AND td 	= 'F'
									AND usr = ''
									AND dia BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
									".$condalmacentk."
							) AS extorno ON (TKF.caja||TKF.td||TKF.dia||TKF.turno||TKF.codigo||abs(TKF.cantidad)||abs(TKF.precio)||abs(TKF.igv)||abs(TKF.importe)||TKF.ruc||TKF.tipo||TKF.pump||TKF.fpago||TKF.at||TKF.text1||TKF.placa||TKF.es) = extorno.registro
							AND TKF.tm 	IN ('V', 'D')
							AND TKF.td 	= 'F'
							AND TKF.usr = ''
							AND TKF.dia BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
							AND TKF.fecha < extorno.fecha
						WHERE
							td 	= 'F'
							AND usr = ''
							AND dia BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
							".$condalmacentk."
						GROUP BY
							TKF.es,
							TKF.dia,
							TKF.caja,
							TKF.turno,
							TKF.trans,
							TKF.ruc
						) AS TKF
						LEFT JOIN pos_z_cierres cfp ON(TKF.nucaja = cfp.ch_posz_pos AND TKF.femision = cfp.dt_posz_fecha_sistema::date AND TKF.nuturno::integer = cfp.nu_posturno AND TKF.nues = cfp.ch_sucursal)
						LEFT JOIN ruc ON(ruc.ruc = TKF.nuruc)
					)
				)-- TICKETS FACTURAS DE VENTAS QUE SERAN ANULADAS POR UN EXTORNO
				UNION ALL--TICKETS FACTURAS DE EXTORNOS
				(
				SELECT DISTINCT
					'1'::TEXT AS nomotivo_contingencia,--MTC = Motivo Tipo Contingencia
					'01'::TEXT AS tipoop,
					TO_CHAR(TKF.femision, 'DD/MM/YYYY') AS femision,
					'12'::TEXT AS nutd,
					cfp.nu_posz_z_serie AS noserie,
					TKF.nuticket::TEXT AS nudocumento_inicial,
					TKF.nuticket::TEXT AS nudocumento_final,
					(CASE
						WHEN TRIM(TKF.nuruc) = '99999999999' THEN '0'
						WHEN CHAR_LENGTH(TRIM(TKF.nuruc)) = 11 THEN '6'
						WHEN CHAR_LENGTH(TRIM(TKF.nuruc)) = 8 THEN '1'
						ELSE '0'
					END)::TEXT AS nutd_identidad,
					TKF.nuruc::TEXT AS nudocumento_identidad,
					CASE WHEN ruc.razsocial IS NULL OR ruc.razsocial = '' THEN '' ELSE SUBSTR(ruc.razsocial, 0, 60)::TEXT END AS nodocumento_identidad,
					'PEN'::TEXT as moneda,
					0.00 AS nuvalor_venta_og,--OG = Operaciones Gravadas
					0 AS nuvalor_venta_oe,--OE = Operaciones Exoneradas
					0 AS nuvalor_venta_oi,--OI = Operaciones Inafectas
					0 AS nuvalor_venta_ex,--OX = Operaciones Exportacion
					0 AS nuisc,--ISC = Impuesto Selectivo al Consumo
					0.00 AS nuigv,
					0 AS nuotros_cargos,--Que no forman parte del(os) valor(es) de venta - # 15
					0.00 AS nutotal,
					'|'::TEXT AS nutd_referencia,--APLICA SOLO (nota de débito o la nota de crédito)
					'|'::TEXT AS nuserie_referencia,--APLICA SOLO (nota de débito o la nota de crédito)
					'|'::TEXT AS nunumero_referencia--APLICA SOLO (nota de débito o la nota de crédito)
				FROM
					(
						(SELECT
							TKF.es AS nues,
							TKF.dia AS femision,
							TKF.caja AS nucaja,
							TKF.turno AS nuturno,
							TKF.trans AS nuticket,
							TKF.ruc AS nuruc
						FROM
							".$postrans." TKF
						WHERE
							tm 		= 'A'
							AND td 	= 'F'
							AND usr = ''
							AND dia BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
							".$condalmacentk."
						GROUP BY
							TKF.es,
							TKF.dia,
							TKF.caja,
							TKF.turno,
							TKF.trans,
							TKF.ruc
						) AS TKF
						LEFT JOIN pos_z_cierres cfp ON(TKF.nucaja = cfp.ch_posz_pos AND TKF.femision = cfp.dt_posz_fecha_sistema::date AND TKF.nuturno::integer = cfp.nu_posturno AND TKF.nues = cfp.ch_sucursal)
						LEFT JOIN ruc ON(ruc.ruc = TKF.nuruc)
					)
				)--TICKETS FACTURAS DE EXTORNOS
				UNION ALL--TICKETS FACTURAS DE VENTAS SIN CONSIDERAR (TICKETS FACTURAS DE VENTAS QUE SERAN ANULADAS POR UN EXTORNO) Y (TICKETS FACTURAS DE EXTORNOS)
				(
				SELECT DISTINCT
					'1'::TEXT AS nomotivo_contingencia,--MTC = Motivo Tipo Contingencia
					'01'::TEXT AS tipoop,
					TO_CHAR(TKF.femision, 'DD/MM/YYYY') AS femision,
					'12'::TEXT AS nutd,
					cfp.nu_posz_z_serie AS noserie,
					TKF.nuticket::TEXT AS nudocumento_inicial,
					TKF.nuticket::TEXT AS nudocumento_final,
					(CASE
						WHEN TRIM(TKF.nuruc) = '99999999999' THEN '0'
						WHEN CHAR_LENGTH(TRIM(TKF.nuruc)) = 11 THEN '6'
						WHEN CHAR_LENGTH(TRIM(TKF.nuruc)) = 8 THEN '1'
						ELSE '0'
					END)::TEXT AS nutd_identidad,
					TKF.nuruc::TEXT AS nudocumento_identidad,
					CASE WHEN ruc.razsocial IS NULL OR ruc.razsocial = '' THEN '' ELSE SUBSTR(ruc.razsocial, 0, 60)::TEXT END AS nodocumento_identidad,
					'PEN'::TEXT as moneda,
					TKF.nuvalor_venta_og,--OG = Operaciones Gravadas
					TKF.nuvalor_venta_oe,--OE = Operaciones Exoneradas
					0 AS nuvalor_venta_oi,--OI = Operaciones Inafectas
					0 AS nuvalor_venta_ex,--OX = Operaciones Exportacion
					0 AS nuisc,--ISC = Impuesto Selectivo al Consumo
					TKF.nuigv,
					0 as nuotros_cargos,--Que no forman parte del(os) valor(es) de venta - # 15
					(TKF.nuvalor_venta_og + TKF.nuvalor_venta_oe + TKF.nuigv) AS nutotal,
					'|'::TEXT AS nutd_referencia,--APLICA SOLO (nota de débito o la nota de crédito)
					'|'::TEXT AS nuserie_referencia,--APLICA SOLO (nota de débito o la nota de crédito)
					'|'::TEXT AS nunumero_referencia--APLICA SOLO (nota de débito o la nota de crédito)
				FROM 
					(
						(SELECT
							TKF.es AS nues,
							TKF.dia AS femision,
							TKF.caja AS nucaja,
							TKF.turno AS nuturno,
							TKF.trans AS nuticket,
							(CASE WHEN SUM(TKF.igv) = 0.0000 THEN 0 ELSE ROUND(SUM(TKF.importe - TKF.igv), 2) END) AS nuvalor_venta_og,
							(CASE WHEN SUM(TKF.igv) = 0.0000 THEN ROUND(SUM(TKF.importe), 2) ELSE 0 END) AS nuvalor_venta_oe,--OE = Operaciones Exoneradas,
							ROUND(SUM(TKF.igv), 2) AS nuigv,
							TKF.ruc AS nuruc
						FROM
							".$postrans." TKF
						WHERE
							tm 			IN ('V', 'D')
							AND td 		= 'F'
							AND usr  	= ''
							AND dia 	BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
							".$condalmacentk."
							AND es||caja||trans NOT IN (
							SELECT
								LAST(TKF.es||TKF.caja||TKF.trans) co_vtrans
							FROM
								".$postrans." TKF
								INNER JOIN (
									SELECT 
										(caja||td||dia||turno||codigo||abs(cantidad)||abs(precio)||abs(igv)||abs(importe)||ruc||tipo||pump||fpago||at||text1||placa||es) AS registro,
										fecha,
										es||caja||trans AS idticket
									FROM
										".$postrans."
									WHERE
										tm 		= 'A'
										AND td  = 'F'
										AND usr = ''
										AND dia BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
										".$condalmacentk."
									) AS extorno ON (TKF.caja||TKF.td||TKF.dia||TKF.turno||TKF.codigo||abs(TKF.cantidad)||abs(TKF.precio)||abs(TKF.igv)||abs(TKF.importe)||TKF.ruc||TKF.tipo||TKF.pump||TKF.fpago||TKF.at||TKF.text1||TKF.placa||TKF.es) = extorno.registro
									AND TKF.tm 	IN ('V', 'D')
									AND TKF.td 	= 'F'
									AND TKF.usr = ''
									AND TKF.dia BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
									AND TKF.fecha < extorno.fecha
							GROUP BY
								extorno.idticket
							)
						GROUP BY
							TKF.es,
							TKF.dia,
							TKF.caja,
							TKF.turno,
							TKF.trans,
							TKF.ruc
						) AS TKF
						LEFT JOIN pos_z_cierres cfp ON(TKF.nucaja = cfp.ch_posz_pos AND TKF.femision = cfp.dt_posz_fecha_sistema::date AND TKF.nuturno::integer = cfp.nu_posturno AND TKF.nues = cfp.ch_sucursal)
						LEFT JOIN ruc ON(ruc.ruc = TKF.nuruc)
					)
				)--TICKETS FACTURAS SOLO DE VENTAS SIN CONSIDERAR (TICKETS FACTURAS DE VENTAS QUE SERAN ANULADAS POR UN EXTORNO) Y (TICKETS FACTURAS SOLO DE EXTORNOS)
			";
			}
			if($cbotv == 'T'){
			$sql .= "
				UNION ALL--COMPROBANTES MANUALES VENTAS
				(
			";
			}
			if($cbotv == 'T' || $cbotv == 'CM'){
			$sql .= "
				SELECT
					'".$cbomtc."'::TEXT AS nomotivo_contingencia,--MTC = Motivo Tipo Contingencia
					'01'::TEXT AS tipoop,
					TO_CHAR(fc.dt_fac_fecha,'DD/MM/YYYY') AS femision,
					TD.tab_car_03 AS nutd,
					'0'||fc.ch_fac_seriedocumento AS noserie,
					fc.ch_fac_numerodocumento::TEXT AS nudocumento_inicial,
					NULL::TEXT AS nudocumento_final,
					(CASE
						WHEN TRIM(cli.cli_ruc) = '99999999999' THEN '0'
						WHEN CHAR_LENGTH(TRIM(cli.cli_ruc)) = 11 THEN '6'
						WHEN CHAR_LENGTH(TRIM(cli.cli_ruc)) = 8 THEN '1'
						ELSE '0'
					END) AS nutd_identidad,
					(CASE WHEN cli.cli_ruc IS NULL THEN NULL ELSE cli.cli_ruc END) AS nudocumento_identidad,
					(CASE WHEN cli.cli_razsocial IS NULL THEN NULL ELSE SUBSTR(cli.cli_razsocial, 0, 60) END) AS nodocumento_identidad,
					CASE
						WHEN ch_fac_moneda = '02' THEN 'USD'::TEXT
						ELSE 'PEN'::TEXT
					END AS moneda,
					(CASE WHEN fc.ch_fac_tiporecargo2 IS NULL AND fc.nu_fac_impuesto1 > 0.00 THEN ROUND(fc.nu_fac_valorbruto, 2) ELSE 0.00 END) AS nuvalor_venta_og,--OG = Operaciones Gravadas
					(CASE WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 = 0.00 THEN ROUND(nu_fac_valortotal, 2) ELSE 0 END) AS nuvalor_venta_oe,--OE = Operaciones Exoneradas
					(CASE WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0.00 THEN ROUND(nu_fac_valortotal, 2) ELSE 0 END) AS nuvalor_venta_oi,--OI = Operaciones Inafectas
					0 AS nuvalor_venta_ex,--OX = Operaciones Exportacion
					0 AS nuisc,
					ROUND(fc.nu_fac_impuesto1, 2) AS nuigv,
					0 AS nuotros_cargos,
					(
					(CASE WHEN fc.ch_fac_tiporecargo2 IS NULL AND fc.nu_fac_impuesto1 > 0.00 THEN ROUND(fc.nu_fac_valorbruto, 2) ELSE 0.00 END)
					+
					(CASE WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 = 0.00 THEN ROUND(nu_fac_valortotal, 2) ELSE 0.00 END)
					+
					(CASE WHEN fc.ch_fac_tiporecargo2 = 'S' AND fc.nu_fac_impuesto1 > 0.00 THEN ROUND(nu_fac_valortotal, 2) ELSE 0.00 END)
					+
					ROUND(fc.nu_fac_impuesto1, 2)
					) AS nutotal,
					(CASE WHEN TD2.tab_car_03 IS NULL THEN '|' ELSE TD2.tab_car_03||'|' END) AS nutd_referencia,
					(CASE WHEN (string_to_array(com.ch_fac_observacion2, '*'))[2] IS NULL THEN '|' ELSE (string_to_array(com.ch_fac_observacion2, '*'))[2]||'|' END) AS nuserie_referencia,
					(CASE WHEN (string_to_array(com.ch_fac_observacion2, '*'))[1] IS NULL THEN '|' ELSE (string_to_array(com.ch_fac_observacion2, '*'))[1]||'|' END) AS nunumero_referencia
				FROM
					fac_ta_factura_cabecera fc
					LEFT JOIN int_clientes AS cli ON (cli.cli_codigo = fc.cli_codigo)
					LEFT JOIN int_tabla_general as TD ON(fc.ch_fac_tipodocumento = substring(TRIM(TD.tab_elemento) FOR 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD.tab_tabla ='08' AND TD.tab_elemento != '000000')
					LEFT JOIN fac_ta_factura_complemento com ON (fc.ch_fac_tipodocumento = com.ch_fac_tipodocumento AND fc.ch_fac_seriedocumento = com.ch_fac_seriedocumento AND fc.ch_fac_numerodocumento = com.ch_fac_numerodocumento AND fc.cli_codigo = com.cli_codigo)
					LEFT JOIN int_tabla_general as TD2 ON((string_to_array(com.ch_fac_observacion2, '*'))[3] = substring(TRIM(TD2.tab_elemento) FOR 2 FROM length(TRIM(TD.tab_elemento))-1) AND TD2.tab_tabla ='08' AND TD2.tab_elemento != '000000')
				WHERE
					fc.ch_fac_tipodocumento 	IN ('10','11','20','35')
					AND fc.dt_fac_fecha 		BETWEEN '".$txtnofechaini."' AND '".$txtnofechafin."'
					AND SUBSTRING(fc.ch_fac_seriedocumento FROM '[A-Z]+') IS NULL
					".$condalmacencm."
				ORDER BY
						nutd,
						femision,
						noserie,
						nudocumento_inicial
				";
			}

			if($cbotv == 'T'){
			$sql .= "
				)--FIN COMPROBANTES MANUALES VENTAS
				";
			}

			if($cbotv == 'T' || $cbotv == 'TK'){
			$sql .= "
				ORDER BY
					nutd,
					femision,
					noserie,
					nudocumento_inicial
			";
			}

			if ($sqlca->query($sql) <= 0) {
               	throw new Exception("No se encontro ningun registro");
			}

			$registros = $sqlca->fetchAll();
			return $registros;
		}catch(Exception $e){
			throw $e;
		}
	}
}
