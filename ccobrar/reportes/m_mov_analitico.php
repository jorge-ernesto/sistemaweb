<?php

class TarjetasMagneticasModel extends Model {

	function ModelReportePDF($filtro=array(), $desde, $hasta) {
		global $sqlca;

		$combo 	   	= $filtro['combo'];
		$tipmovi   	= $filtro['tipmovi'];
		$radio     	= $filtro['modo'];
		$codigo    	= trim($filtro['codigo']);
		$registro  	= array();	
		$cond1 		= "";
		$cond2 		= "";
		
		if($combo != "01")
			$cond1	= "AND det.cli_codigo = '$codigo'";

		if($tipmovi!="4") 
			$cond2 ="AND det.ch_tipmovimiento = '$tipmovi' ";

		$sql = "
		SELECT
			trim(det.cli_codigo)||' - '||trim(cli.cli_razsocial) as cliente,
   			det.ch_comprobante,
			det.ch_tipmovimiento tipo_mov, 
			cab.ch_tipcontable tipo_contable,
			det.ch_tipdocumento tipo_doc,
			gen.tab_desc_breve||' - '||trim(cab.ch_seriedocumento)||' - '||trim(cab.ch_numdocumento) as documento,
			to_char(det.dt_fechamovimiento,'dd/mm/yyyy') as fecha,
			trim(gen.tab_desc_breve)||' - '||trim(det.ch_numdocreferencia) as documento_referencia,
			tmcc.tab_desc_breve as accion,
			det.ch_glosa as ch_glosa,  
			' ' ||mone.tab_desc_breve monetotal,  
			det.nu_importemovimiento as cabtotal, 
			to_char(cab.dt_fechavencimiento,'dd/mm/yyyy') as fecha_vencimiento,
			(CASE WHEN
				det.ch_tipmovimiento = '2' AND (SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy')) > 1
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
					AND det2.dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy')
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
						AND det3.dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy')
					)
				ELSE
					0.00
				END)
			ELSE
				CASE WHEN
					(SELECT COUNT(*) FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy')) = 1
				THEN
					det.nu_importemovimiento
				END
			END) as saldosoles,
			(CASE WHEN
				(det.ch_identidad)::INTEGER = (SELECT MAX(ch_identidad)::INTEGER FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy'))
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
				AND det2.dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy')
				) -
				(SELECT
					SUM(det4.nu_importemovimiento)
				FROM
					ccob_ta_detalle as det4
				WHERE
					det4.ch_tipmovimiento = '2'
					AND det4.ch_identidad::INTEGER BETWEEN (SELECT MIN(ch_identidad)::INTEGER FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy')) AND (SELECT MAX(ch_identidad)::INTEGER FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy'))
					AND det4.ch_tipdocumento	= det.ch_tipdocumento
					AND det4.ch_seriedocumento	= det.ch_seriedocumento
					AND det4.ch_numdocumento	= det.ch_numdocumento
					AND det4.cli_codigo 		= det.cli_codigo
					AND det4.dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy')
				)
			END) saldofinal,
			(SELECT MAX(ch_identidad)::INTEGER FROM ccob_ta_detalle WHERE ch_tipdocumento||ch_seriedocumento||ch_numdocumento = det.ch_tipdocumento||det.ch_seriedocumento||det.ch_numdocumento AND cli_codigo = det.cli_codigo AND dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy')) cantcobranza
 		FROM
 			ccob_ta_cabecera AS cab
			INNER JOIN ccob_ta_detalle AS det ON(cab.ch_tipdocumento = det.ch_tipdocumento and cab.ch_seriedocumento = det.ch_seriedocumento and cab.ch_numdocumento = det.ch_numdocumento AND cab.cli_codigo = det.cli_codigo)
			INNER JOIN int_clientes AS cli ON(cab.cli_codigo = cli.cli_codigo)
			LEFT JOIN int_tabla_general AS gen ON(cab.ch_tipdocumento = substring(trim(tab_elemento) for 2 from length(trim(tab_elemento))-1) and tab_tabla ='08')	
			LEFT JOIN int_tabla_general AS mone ON(det.ch_moneda = (substring(trim(mone.tab_elemento) for 2 from length(trim(mone.tab_elemento))-1)) AND mone.tab_tabla='04' AND mone.tab_elemento != '000000')
			LEFT JOIN int_tabla_general AS tmcc ON(det.ch_tipmovimiento = (substring(trim(tmcc.tab_elemento) for 1 from length(trim(tmcc.tab_elemento)))) AND tmcc.tab_tabla ='TMCC')
		WHERE 
			det.dt_fechamovimiento BETWEEN TO_DATE('$desde','dd/mm/yyyy') AND TO_DATE('$hasta','dd/mm/yyyy')
			$cond1
			$cond2
		ORDER BY
			cliente,
			cab.ch_tipdocumento,
			cab.ch_seriedocumento,
			cab.ch_numdocumento,
			tipo_mov,
			det.dt_fechamovimiento,
			det.dt_fecha_actualizacion;
		";

echo "<pre>";
print_r($sql);
echo "</pre>";

		if ($sqlca->query($sql) < 0) 
			return false;

		$DatosArrayFinal 	= array();
	  	$DatosArray      	= array();
	  	$x 			= 0;

		for($i = 0; $i < $sqlca->numrows(); $i++) {

			$A = $sqlca->fetchRow();

			$DatosArray['CLIENTE'] 	        	= $A["cliente"];
			$DatosArray['RAZSOCIAL']        	= $A["raz_social"];
			$DatosArray['FECHA']           	 	= $A["fecha"];
			$DatosArray['ACCION']           	= $A["accion"];
			$DatosArray['DOCUMENTO']        	= $A["documento"];
			$DatosArray['FECHA VENCIMIENTO'] 	= $A["fecha_vencimiento"];
			$DatosArray['DOC REFERENCIA']  		= $A["documento_referencia"];
			$DatosArray['VOUCHER']         		= $A["ch_comprobante"];
			$DatosArray['GLOSA']         		= $A["ch_glosa"];
			$DatosArray['MONETOTAL']       		= $A["monetotal"];
			$DatosArray['CABTOTAL']       		= $A["cabtotal"];
			$DatosArray['COMBO']           		= $combo;
			$DatosArray['TIPMOVI']          	= $tipmovi;
			$DatosArray['RADIO']           		= $radio;
			$DatosArray['CONTABLE']        		= $A["tipo_contable"];
			$DatosArray['MOVIMIENTO']			= $A["tipo_mov"];
			$DatosArray['TIPODOC']				= $A["tipo_doc"];
			$DatosArray['SALDOSOLES']			= $A["saldosoles"];
			$DatosArray['SALDOFINALSOLES']		= $A["saldofinal"];
			$DatosArray['CANTCOBRANZA']			= $A["cantcobranza"];

			$DatosArrayFinal[$i] 	= $DatosArray;
			$codigo 				= $A["cliente"];
			$elegir 				= $A["accion"];
		}
		return $DatosArrayFinal;
  	}
}
