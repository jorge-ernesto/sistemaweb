<?php

class AsientosModel extends Model {

	function obtenerSucursales($alm) {
		global $sqlca;
		
		if(trim($alm) == "")
			$cond = "";
		else
			$cond = " AND ch_almacen = '$alm'"; 
	
		$sql = "SELECT
			    ch_almacen,
			    ch_almacen||' - '||ch_nombre_almacen
			FROM
			    inv_ta_almacenes
			WHERE
			    ch_clase_almacen='1' $cond 
			ORDER BY
			    ch_almacen;";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();		    
		    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    	}

	function AsientosLubricantes($almacen, $anio, $mes) {
		global $sqlca;

		if ($anio % 4 != 0 && $mes == '02')
			$fin = '28';
			$mes1 = '28/02/'.$anio;
			$nmes = 'FEBRERO';
		if ($anio % 4 == 0 && $mes == '02')
			$fin = '28';
			$mes1 = '28/02/'.$anio;
			$nmes = 'FEBRERO';
		if ($mes == '01' or $mes == '03' or $mes == '05' or $mes == '07' or $mes == '08' or $mes == '10' or $mes == '12')
			$fin = '31';
		if ($mes == '04' or $mes == '06' or $mes == '09' or $mes == '11')
			$fin = '30';

		$desde = $anio . '-' . $mes . '-01';
		$hasta = $anio . '-' . $mes . '-' . $fin;

		$mes2 = $mes;

		if($mes == '01'){
			$mes1 = '31/01/'.$anio;
			$nmes = 'ENERO';
		}elseif($mes == '03'){
			$mes1 = '31/03/'.$anio;
			$nmes = 'MARZO';
		}elseif($mes == '04'){
			$mes1 = '30/04/'.$anio;
			$nmes = 'ABRIL';
		}elseif($mes == '05'){
			$mes1 = '31/05/'.$anio;
			$nmes = 'MAYO';
		}elseif($mes == '06'){
			$mes1 = '30/06/'.$anio;
			$nmes = 'JUNIO';
		}elseif($mes == '07'){
			$me1 = '31/07/'.$anio;
			$nmes = 'JULIO';
		}elseif($mes == '08'){
			$mes1 = '31/08/'.$anio;
			$nmes = 'AGOSTO';
		}elseif($mes == '09'){
			$mes1 = '31/09/'.$anio;
			$nmes = 'SETIEMBRE';
		}elseif($mes == '10'){
			$mes1 = '31/10/'.$anio;
			$nmes = 'OCTUBRE';
		}elseif($mes == '11'){
			$mes1 = '31/11/'.$anio;
			$nmes = 'NOVIEMBRE';
		}elseif($mes == '12'){
			$mes1 = '31/12/'.$anio;
			$nmes = 'DICIEMBRE';
		}

		$sql ="SELECT * FROM(
					SELECT
						'$mes1'::text AS fecha,
						'1211015'::text AS CUENTA,
						'$mes'::text || '-' || '$anio' || '-' || 'CH'::text AS DOCUMENTO,
						TRUNC(SUM(d.nu_fac_valortotal),2) AS DEBE,
						' '::text AS ABONO,
						'001'::text MONEDA,
						'VENTA EESS CHICLAYO MES DE ' || '$nmes' || ' $anio'::text AS GLOSA,
						'1338'::text IDPROV
					FROM
						fac_ta_factura_cabecera c
							RIGHT JOIN fac_ta_factura_detalle d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
							RIGHT JOIN int_articulos art ON (art.art_codigo=d.art_codigo)
							LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
							LEFT JOIN int_clientes k on c.cli_codigo=k.cli_codigo AND k.cli_ndespacho_efectivo != 1
					WHERE
						c.ch_fac_seriedocumento='$almacen' AND 
						c.ch_fac_tipodocumento='45' AND
						c.dt_fac_fecha BETWEEN '$desde' AND '$hasta' AND
						art.art_linea = '000045'
					GROUP BY
						ch_almacen
					) AS L

					UNION

					(SELECT
						'$mes1'::text AS fecha,
						'4011101'::text AS CUENTA,
						'$mes'::text || '-' || '$anio' || '-' || 'CH'::text AS DOCUMENTO,
						0::integer AS DEBE,
						CAST(SUM(d.nu_fac_valortotal) - ROUND(SUM(d.nu_fac_valortotal)/1.18,2)AS text) AS ABONO,
						'001'::text MONEDA,
						'VENTA EESS CHICLAYO MES DE ' || '$nmes' || ' $anio'::text AS GLOSA,
						'1338'::text IDPROV
					FROM
						fac_ta_factura_cabecera c
							RIGHT JOIN fac_ta_factura_detalle d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
							RIGHT JOIN int_articulos art ON (art.art_codigo=d.art_codigo)
							LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
							LEFT JOIN int_clientes k on c.cli_codigo=k.cli_codigo AND k.cli_ndespacho_efectivo != 1
					WHERE
						c.ch_fac_seriedocumento='$almacen' AND 
						c.ch_fac_tipodocumento='45' AND
						c.dt_fac_fecha BETWEEN '$desde' AND '$hasta' AND
						art.art_linea = '000045'
					GROUP BY
						ch_almacen
					)
					
					UNION

					(SELECT
						'$mes1'::text AS fecha,
						'7010106'::text AS CUENTA,
						'$mes'::text || '-' || '$anio' || '-' || 'CH'::text AS DOCUMENTO,
						0::integer AS DEBE,
						CAST(ROUND(SUM(d.nu_fac_valortotal)/1.18,2)AS text) AS ABONO,
						'001'::text MONEDA,
						'VENTA EESS CHICLAYO MES DE ' || '$nmes' || ' $anio'::text AS GLOSA,
						'1338'::text IDPROV
					FROM
						fac_ta_factura_cabecera c
							RIGHT JOIN fac_ta_factura_detalle d ON (d.ch_fac_tipodocumento=c.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=c.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=c.ch_fac_numerodocumento AND d.cli_codigo=c.cli_codigo)
							RIGHT JOIN int_articulos art ON (art.art_codigo=d.art_codigo)
							LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
							LEFT JOIN int_clientes k on c.cli_codigo=k.cli_codigo AND k.cli_ndespacho_efectivo != 1
					WHERE
						c.ch_fac_seriedocumento='$almacen' AND 
						c.ch_fac_tipodocumento='45' AND
						c.dt_fac_fecha BETWEEN '$desde' AND '$hasta' AND
						art.art_linea = '000045'
					GROUP BY
						ch_almacen
					)

					ORDER BY
						CUENTA;";
	
		//echo $sql;

		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['fecha']		= $a[0];
			$resultado[$i]['cuenta']	= $a[1];
			$resultado[$i]['documento']	= $a[2];
			$resultado[$i]['debe']		= $a[3];
			$resultado[$i]['abono']		= $a[4];
			$resultado[$i]['moneda']	= $a[5];
			$resultado[$i]['glosa']		= $a[6];
			$resultado[$i]['idprov']	= $a[7];
		}
		
		return $resultado;
    	}
    

    	function AsientosCombustibles($almacen, $anio, $mes) {
		global $sqlca;

		if ($anio % 4 != 0 && $mes == '02')
			$fin = '28';
			$mes1 = '28/02/'.$anio;
			$nmes = 'FEBRERO';
		if ($anio % 4 == 0 && $mes == '02')
			$fin = '28';
			$mes1 = '28/02/'.$anio;
			$nmes = 'FEBRERO';
		if ($mes == '01' or $mes == '03' or $mes == '05' or $mes == '07' or $mes == '08' or $mes == '10' or $mes == '12')
			$fin = '31';
		if ($mes == '04' or $mes == '06' or $mes == '09' or $mes == '11')
			$fin = '30';

		$desde = $anio . '-' . $mes . '-01';
		$hasta = $anio . '-' . $mes . '-' . $fin;

		$mes2 = $mes;

		if($mes == '01'){
			$mes1 = '31/01/'.$anio;
			$nmes = 'ENERO';
		}elseif($mes == '03'){
			$mes1 = '31/03/'.$anio;
			$nmes = 'MARZO';
		}elseif($mes == '04'){
			$mes1 = '30/04/'.$anio;
			$nmes = 'ABRIL';
		}elseif($mes == '05'){
			$mes1 = '31/05/'.$anio;
			$nmes = 'MAYO';
		}elseif($mes == '06'){
			$mes1 = '30/06/'.$anio;
			$nmes = 'JUNIO';
		}elseif($mes == '07'){
			$me1 = '31/07/'.$anio;
			$nmes = 'JULIO';
		}elseif($mes == '08'){
			$mes1 = '31/08/'.$anio;
			$nmes = 'AGOSTO';
		}elseif($mes == '09'){
			$mes1 = '31/09/'.$anio;
			$nmes = 'SETIEMBRE';
		}elseif($mes == '10'){
			$mes1 = '31/10/'.$anio;
			$nmes = 'OCTUBRE';
		}elseif($mes == '11'){
			$mes1 = '31/11/'.$anio;
			$nmes = 'NOVIEMBRE';
		}elseif($mes == '12'){
			$mes1 = '31/12/'.$anio;
			$nmes = 'DICIEMBRE';
		}
		
		$sql = "SELECT * FROM (
					SELECT 
						'$mes1'::text AS fecha,
						'1312013'::text AS CUENTA,
						t.ch_fac_seriedocumento || '-' ||  t.ch_fac_numerodocumento::text AS DOCUMENTO, 
						ROUND(t.nu_fac_valortotal,2) AS DEBE,
						' '::text AS ABONO,
						CASE
							WHEN t.ch_fac_moneda = '01' THEN '001'
							WHEN t.ch_fac_moneda = '02' THEN '002'
						END AS MONEDA,
						c.cli_rsocialbreve::text AS GLOSA,
						CASE
							WHEN c.cli_ruc = '20100366747' THEN '1'
							WHEN c.cli_ruc = '20509227668' THEN '2'
						END AS IDPROV
					FROM 
						fac_ta_factura_cabecera t
						INNER JOIN
							fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento=t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento=t.ch_fac_tipodocumento)
						INNER JOIN
							int_clientes c ON (c.cli_codigo = t.cli_codigo)
					WHERE 
						t.ch_fac_tipodocumento = '10'
						AND t.dt_fac_fecha BETWEEN '$desde' AND '$hasta'
						AND t.nu_fac_valortotal>0
						AND c.cli_codigo IN('0001','0002','0003','0005')
						AND t.ch_almacen = '$almacen' 
					GROUP BY 
						t.ch_fac_tipodocumento,
						t.ch_fac_numerodocumento,
						t.ch_fac_seriedocumento,
						t.nu_fac_valortotal,
						t.ch_fac_moneda,
						c.cli_rsocialbreve,
						c.cli_ruc
					ORDER BY 
						DOCUMENTO
					) AS K

					UNION

					(
					SELECT 
						'$mes1'::text AS fecha,
						'1211011'::text AS CUENTA,
						t.ch_fac_seriedocumento || '-' ||  t.ch_fac_numerodocumento::text AS DOCUMENTO, 
						ROUND(t.nu_fac_valortotal,2) AS DEBE,
						' '::text AS ABONO,
						CASE
							WHEN t.ch_fac_moneda = '01' THEN '001'
							WHEN t.ch_fac_moneda = '02' THEN '002'
						END AS MONEDA,
						c.cli_rsocialbreve::text AS GLOSA,
						CASE
							WHEN c.cli_ruc = '20143229816' THEN '2014322981'
						END AS IDPROV
					FROM 
						fac_ta_factura_cabecera t
						INNER JOIN
							fac_ta_factura_detalle d ON (d.ch_fac_numerodocumento=t.ch_fac_numerodocumento AND d.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND d.ch_fac_tipodocumento=t.ch_fac_tipodocumento)
						INNER JOIN
							int_clientes c ON (c.cli_codigo = t.cli_codigo)
					WHERE 
						t.ch_fac_tipodocumento = '10'
						AND t.dt_fac_fecha BETWEEN '$desde' AND '$hasta' 
						AND t.nu_fac_valortotal>0
						AND c.cli_codigo IN('00016')
						--AND c.cli_ruc NOT IN('20100366747','20509227668','20516681463','20101556906','20351516560')
						AND t.ch_almacen = '$almacen' 
						AND t.ch_liquidacion != ''
					GROUP BY 
						t.ch_fac_tipodocumento,
						t.ch_fac_numerodocumento,
						t.ch_fac_seriedocumento,
						t.nu_fac_valortotal,
						t.ch_fac_moneda,
						c.cli_rsocialbreve,
						c.cli_ruc
					ORDER BY 
						DOCUMENTO
					)

					UNION 

					(SELECT
						'$mes1'::text AS fecha,
						'4011101'::text AS CUENTA,
						'$mes'::text || '-' || '$anio' || '-' || 'CH'::text AS DOCUMENTO,
						0::integer AS DEBE,
						CAST(ROUND(TOTAL - AFE,2) - ROUND((TOTAL - AFE) / 1.18,2) as text) AS ABONO,
						'001'::text MONEDA,
						'VENTA EESS CHICLAYO MES DE ' || '$nmes' || ' $anio'::text AS GLOSA,
						'1338'::text IDPROV
					FROM
						(SELECT 
							comb.ch_sucursal as estacion,
							TRUNC((SUM(comb.nu_ventavalor) + SUM(comb.nu_descuentos)),2) as TOTAL
						FROM
							comb_ta_contometros comb
						WHERE 	
							comb.ch_sucursal='$almacen' 
							AND comb.dt_fechaparte between '$desde' AND '$hasta'
							AND comb.nu_ventavalor > 0
						       	AND comb.nu_ventagalon > 0
						GROUP BY 
							comb.ch_sucursal
						) C
	
						LEFT JOIN

						(SELECT
							es as estacion,
							TRUNC(SUM(afe.importe),2) AFE
						FROM
							pos_ta_afericiones afe
						WHERE
							es='$almacen' AND
							dia between '$desde' AND '$hasta'
						GROUP BY
							es
						) A on A.estacion = C.estacion
					)

					UNION 

					(
					SELECT
						'$mes1'::text AS fecha,
						CASE
							WHEN C.CODIGO  = '11620301' THEN '7010103'
							WHEN C.CODIGO  = '11620302' THEN '7010104'
							WHEN C.CODIGO  = '11620305' THEN '7010105'
							WHEN C.CODIGO  = '11620304' THEN '7010102'
							WHEN C.CODIGO  = '11620307' THEN '7010101'
						END AS CUENTA,
						'$mes'::text || '-' || '$anio' || '-' || 'CH'::text AS DOCUMENTO, 
						0::integer AS DEBE,
						CASE WHEN AFE > 0 THEN CAST(ROUND((TOTAL - AFE),2) as text) ELSE CAST(ROUND(TOTAL,2) as text) END ABONO,
						'001'::text MONEDA,
						'VENTA EESS CHICLAYO MES DE ' || '$nmes' || ' $anio'::text AS GLOSA,
						'1338'::text IDPROV
					FROM
						(SELECT
							COMB.codigo codigo,
							(CASE WHEN VAL.TOTAL > 0 THEN COMB.TOTAL - VAL.TOTAL ELSE COMB.TOTAL END) TOTAL
						FROM
							(SELECT 
								comb.ch_codigocombustible as codigo,
								ROUND((SUM(comb.nu_ventavalor) + SUM(comb.nu_descuentos))/1.18,2) as TOTAL
							FROM
								comb_ta_contometros comb
							WHERE 	
								comb.ch_sucursal = '$almacen'
								AND comb.dt_fechaparte BETWEEN '$desde' AND '$hasta'
								AND comb.nu_ventavalor > 0
							       	AND comb.nu_ventagalon > 0
							GROUP BY 
								codigo
							ORDER BY
								codigo) COMB

							LEFT JOIN

							(SELECT 
								VALES.codigo as codigo,
								(CASE WHEN CLI.TOTAL > 0 THEN VALES.TOTAL - CLI.TOTAL ELSE VALES.TOTAL END) TOTAL
	
							FROM
								(SELECT 
									d.ch_articulo as codigo,	
									ROUND(ROUND(sum(d.nu_importe),2)/1.18,2) TOTAL
								FROM
									val_ta_detalle d
								WHERE
									d.ch_sucursal = '$almacen' AND
									d.dt_fecha BETWEEN '$desde' AND '$hasta'
								GROUP BY
									codigo
								ORDER BY
									codigo) VALES

								LEFT JOIN

								(SELECT 
									d.ch_articulo as codigo,	
									ROUND(ROUND(sum(d.nu_importe),2)/1.18,2) TOTAL
								FROM
									val_ta_cabecera c
									JOIN val_ta_detalle d ON(c.ch_documento = d.ch_documento AND c.dt_fecha = d.dt_fecha AND c.ch_sucursal = d.ch_sucursal)
								WHERE
									d.ch_sucursal = '$almacen'
									AND d.dt_fecha BETWEEN '$desde' AND '$hasta'
									AND c.ch_cliente = '00016'
								GROUP BY
									codigo) CLI ON CLI.codigo = VALES.codigo) VAL ON VAL.codigo = COMB.codigo
						) C
	
						LEFT JOIN

						(SELECT
							codigo as codigo,
							ROUND(SUM(afe.importe)/1.18,2) AFE
						FROM
							pos_ta_afericiones afe
						WHERE
							es = '$almacen' AND
							dia BETWEEN '$desde' AND '$hasta'
						GROUP BY
							codigo
						) A on A.codigo = C.codigo
					)

					UNION
					
					(SELECT
						'$mes1'::text AS fecha,
						CASE
							WHEN d.ch_articulo  = '11620301' THEN '7010203'
							WHEN d.ch_articulo  = '11620302' THEN '7010204'
							WHEN d.ch_articulo  = '11620305' THEN '7010205'
							WHEN d.ch_articulo  = '11620304' THEN '7010202'
							WHEN d.ch_articulo  = '11620307' THEN '7010201'
						END AS CUENTA,
						'$mes'::text || '-' || '$anio' || '-' || 'CH'::text AS DOCUMENTO, 
						0::integer AS DEBE,
						CAST(ROUND(sum(d.nu_importe)/1.18,2) as text) ABONO,
						'001'::text MONEDA,
						'VENTA EESS CHICLAYO MES DE ' || '$nmes' || ' $anio'::text AS GLOSA,
						'1338'::text IDPROV
					FROM
						val_ta_cabecera c
						JOIN val_ta_detalle d ON(c.ch_documento = d.ch_documento AND c.dt_fecha = d.dt_fecha AND c.ch_sucursal = d.ch_sucursal)
					WHERE
						d.ch_sucursal = '$almacen' 
						AND d.dt_fecha BETWEEN '$desde' AND '$hasta'
						AND c.ch_cliente IN ('0001','0002','0005','00013')
					GROUP BY
						d.ch_articulo
					)

					UNION

					(SELECT
						'$mes1'::text AS fecha,
						'1211015'::text AS CUENTA,
						'$mes'::text || '-' || '$anio' || '-' || 'CH'::text AS DOCUMENTO,";

		if($mes == '06'){				
			$sql .="		
						TRUNC(TRUNC((TRUNC((TOTAL - AFE)/1.18,2) - TRUNC(VALES/1.18,4)),2)*1.18,2) - FALTA DEBE,";
		}else{
			$sql .="
						TRUNC(TRUNC((TRUNC((TOTAL - AFE)/1.18,2) - TRUNC(VALES/1.18,4)),2)*1.18,2) DEBE,";
		}

		$sql.="
						' '::text AS ABONO,
						'001'::text MONEDA,
						'VENTA EESS CHICLAYO MES DE ' || '$nmes' || ' $anio'::text AS GLOSA,
						'1338'::text IDPROV
					FROM
						(SELECT 
							comb.ch_sucursal as estacion,
							TRUNC((SUM(comb.nu_ventavalor) + SUM(comb.nu_descuentos)),2) as TOTAL
						FROM
							comb_ta_contometros comb
						WHERE 	
							comb.ch_sucursal = '$almacen' 
							AND comb.dt_fechaparte BETWEEN '$desde' AND '$hasta'
							AND comb.nu_ventavalor > 0
						       	AND comb.nu_ventagalon > 0
						GROUP BY 
							comb.ch_sucursal
						) C
	
						LEFT JOIN

						(SELECT
							es as estacion,
							TRUNC(SUM(afe.importe),2) AFE
						FROM
							pos_ta_afericiones afe
						WHERE
							es='$almacen' AND
							dia between '$desde' AND '$hasta'
						GROUP BY
							es
						) A on A.estacion = C.estacion

						LEFT JOIN

						(SELECT
							ch_sucursal as estacion,
							ROUND(SUM(nu_importe),2) VALES
						FROM
							val_ta_detalle
						WHERE
							ch_sucursal = '$almacen' AND
							dt_fecha between '$desde' and '$hasta'
						GROUP BY
							ch_sucursal
						) V on V.estacion = C.estacion

						LEFT JOIN

						(SELECT
							ch_sucursal as estacion,
							SUM(nu_importe) FALTA
						FROM
							val_ta_cabecera
						WHERE
							ch_sucursal = '$almacen'
							AND dt_fecha = '2013-05-31'
							AND ch_cliente = '00016'
						GROUP BY
							ch_sucursal
						) F on F.estacion = C.estacion
					)

					UNION

					(SELECT
						'$mes1'::text AS fecha,
						'6590301'::text AS CUENTA,
						'$mes'::text || '-' || '$anio' || '-' || 'CH'::text AS DOCUMENTO,  
						0::integer DEBE,
						' '::text AS ABONO,
						'001'::text MONEDA,
						'VENTA EESS CHICLAYO MES DE ' || '$nmes' || ' $anio'::text AS GLOSA,
						'0'::text IDPROV
					FROM
						comb_ta_contometros comb
					WHERE 	
						comb.ch_sucursal='$almacen' 
						AND comb.dt_fechaparte between '$desde' AND '$hasta'
						AND comb.nu_ventavalor > 0
					       	AND comb.nu_ventagalon > 0
					GROUP BY 
						comb.ch_sucursal
					)

					ORDER BY
						DOCUMENTO,
						CUENTA;";

		//echo $sql;
	
		if ($sqlca->query($sql) < 0)
			return false;
	    
		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['fecha']		= $a[0];
			$resultado[$i]['cuenta']	= $a[1];
			$resultado[$i]['documento']	= $a[2];
			$resultado[$i]['debe']		= $a[3];
			$resultado[$i]['abono']		= $a[4];
			$resultado[$i]['moneda']	= $a[5];
			$resultado[$i]['glosa']		= $a[6];
			$resultado[$i]['idprov']	= $a[7];
		}
		
		return $resultado;

    	}
}
