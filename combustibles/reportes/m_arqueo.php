<?php

class ArqueoModel extends Model {

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

    	function SearchTurnosEfectivo($almacen, $fecha, $type) {
		global $sqlca;

		$y = substr($fecha, 6, 4);
		$m = substr($fecha, 3, 2);

		$postrans = "pos_trans".$y.$m;

		$cond = "";
		$cond2 = "";

		if($type == "C"){
			$cond	= "AND tipo = 'C'";
			$cond2	=" art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307') AND ";
		}elseif($type == "M"){
			$cond	= "AND tipo = 'M'";
			$cond2	= "art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307') AND ";
		}

		$sql =	"
			SELECT
				to_char(PRIN.dia,'DD/MM/YYYY') as fecha,
				'EFECTIVO SOLES' descripcion,
				(SELECT SUM(importe) as importe FROM $postrans WHERE dia = PRIN.dia AND fpago != '2' AND turno = '1' $cond GROUP BY dia, turno) as turno1,
				(SELECT SUM(importe) as importe FROM $postrans WHERE dia = PRIN.dia AND fpago != '2' AND turno = '2' $cond GROUP BY dia, turno) as turno2,
				(SELECT SUM(importe) as importe FROM $postrans WHERE dia = PRIN.dia AND fpago != '2' AND turno = '3' $cond GROUP BY dia, turno) as turno3,
				(SELECT SUM(nu_fac_valortotal) as importe FROM fac_ta_factura_detalle WHERE $cond2 ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN (SELECT ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento FROM fac_ta_factura_cabecera WHERE  dt_fac_fecha = PRIN.dia AND ch_fac_tipodocumento IN ('10','35') AND ch_fac_credito = 'N' AND ch_fac_tiporecargo3='1' AND ch_fac_tiporecargo2 IS NULL)) as turnom1,
				(SELECT SUM(nu_fac_valortotal) as importe FROM fac_ta_factura_detalle WHERE $cond2 ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN (SELECT ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento FROM fac_ta_factura_cabecera WHERE  dt_fac_fecha = PRIN.dia AND ch_fac_tipodocumento IN ('10','35') AND ch_fac_credito = 'N' AND ch_fac_tiporecargo3='2' AND ch_fac_tiporecargo2 IS NULL)) as turnom2,
				(SELECT SUM(nu_fac_valortotal) as importe FROM fac_ta_factura_detalle WHERE $cond2 ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN (SELECT ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento FROM fac_ta_factura_cabecera WHERE  dt_fac_fecha = PRIN.dia AND ch_fac_tipodocumento IN ('10','35') AND ch_fac_credito = 'N' AND ch_fac_tiporecargo3='3' AND ch_fac_tiporecargo2 IS NULL)) as turnom3
			FROM
				$postrans PRIN
			WHERE
				date(dia) = to_date('" . pg_escape_string($fecha) . "', 'DD/MM/YYYY')
				AND es = '$almacen'
				AND fpago != '2'
				$cond
			GROUP BY
				dia,
				fpago
			ORDER BY
				dia;
			";

		echo "TICKETS SOLES: \n".$sql;

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

		    	$a = $sqlca->fetchRow();	    

		    	$dia 		= $a[0];
		    	$descripcion	= $a[1];
		    	$turno1 	= $a[2];
		    	$turno2		= $a[3];
		    	$turno3		= $a[4];
		    	$turnom1	= $a[5];
		    	$turnom2	= $a[6];
		    	$turnom3	= $a[7];

			$result['data'][$descripcion]['dia'] 		= $dia;
			$result['data'][$descripcion]['descripcion'] 	= $descripcion;
			$result['data'][$descripcion]['turno1'] 	= $turno1 + $turnom1;
			$result['data'][$descripcion]['turno2'] 	= $turno2 + $turnom2;
			$result['data'][$descripcion]['turno3'] 	= $turno3 + $turnom3;

		}
	
		return $result;

    	}

    	function SearchTurnosTarjetaCreditos($almacen, $fecha, $type) {
		global $sqlca;

		$y = substr($fecha, 6, 4);
		$m = substr($fecha, 3, 2);

		$postrans = "pos_trans".$y.$m;

		$cond = "";
		$cond2 = "";

		if($type == "C"){
			$cond	= "AND tipo = 'C'";
			$cond2	=" art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307') AND ";
		}elseif($type == "M"){
			$cond	= "AND tipo = 'M'";
			$cond2	= "art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307') AND ";
		}

		$sql =	"
			SELECT
				TO_CHAR(PRIN.dia,'DD/MM/YYYY') as fecha,
				SUBSTRING(GEN.tab_elemento,6,6) id,
				GEN.tab_descripcion descripcion,
				(SELECT SUM(importe) as importe FROM $postrans WHERE dia = PRIN.dia AND at = PRIN.at AND turno = '1' $cond GROUP BY dia, turno) as turno1,
				(SELECT SUM(importe) as importe FROM $postrans WHERE dia = PRIN.dia AND at = PRIN.at AND turno = '2' $cond GROUP BY dia, turno) as turno2,
				(SELECT SUM(importe) as importe FROM $postrans WHERE dia = PRIN.dia AND at = PRIN.at AND turno = '3' $cond GROUP BY dia, turno) as turno3,
				(SELECT SUM(nu_fac_valortotal) as importe FROM fac_ta_factura_detalle WHERE $cond2 ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN (SELECT ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento FROM fac_ta_factura_cabecera WHERE  dt_fac_fecha = PRIN.dia AND ch_fac_tipodocumento IN ('10','35') AND ch_fac_credito != 'N' AND ch_fac_tiporecargo3='1' AND ch_fac_tiporecargo2 != 'S')) as turnom1,
				(SELECT SUM(nu_fac_valortotal) as importe FROM fac_ta_factura_detalle WHERE $cond2 ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN (SELECT ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento FROM fac_ta_factura_cabecera WHERE  dt_fac_fecha = PRIN.dia AND ch_fac_tipodocumento IN ('10','35') AND ch_fac_credito != 'N' AND ch_fac_tiporecargo3='2' AND ch_fac_tiporecargo2 != 'S')) as turnom2,
				(SELECT SUM(nu_fac_valortotal) as importe FROM fac_ta_factura_detalle WHERE $cond2 ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN (SELECT ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento FROM fac_ta_factura_cabecera WHERE  dt_fac_fecha = PRIN.dia AND ch_fac_tipodocumento IN ('10','35') AND ch_fac_credito != 'N' AND ch_fac_tiporecargo3='3' AND ch_fac_tiporecargo2 != 'S')) as turnom3
			FROM
				$postrans PRIN
				LEFT JOIN int_tabla_general GEN ON(PRIN.at = substring(GEN.tab_elemento,6,6) AND GEN.tab_tabla = '95' AND GEN.tab_elemento != '000000')
			WHERE
				date(dia)	= to_date('" . pg_escape_string($fecha) . "', 'DD/MM/YYYY')
				AND es		= '$almacen'
				AND fpago	= '2'
				$cond
			GROUP BY
				dia,
				GEN.tab_descripcion,
				GEN.tab_elemento,
				PRIN.at
			ORDER BY
				GEN.tab_elemento;
			";
	
		echo "\nTICKETS CREDITO: \n".$sql;

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

		    	$a = $sqlca->fetchRow();	    

		    	$dia 		= $a[0];
		    	$id 		= $a[1];
		    	$descripcion	= $a[2];
		    	$turno1 	= $a[3];
		    	$turno2		= $a[4];
		    	$turno3		= $a[5];
		    	$turnom1	= $a[6];
		    	$turnom2	= $a[7];
		    	$turnom3	= $a[8];

			$result['data'][$descripcion]['dia'] 		= $dia;
			$result['data'][$descripcion]['id'] 		= $id;
			$result['data'][$descripcion]['descripcion'] 	= $descripcion;
			$result['data'][$descripcion]['turno1'] 	= $turno1 + $turnom1;
			$result['data'][$descripcion]['turno2'] 	= $turno2 + $turnom2;
			$result['data'][$descripcion]['turno3'] 	= $turno3 + $turnom3;

		}
	
		return $result;

    	}

    function SearchTickets($almacen, $fecha, $type) {
		global $sqlca;

		$y = substr($fecha, 6, 4);
		$m = substr($fecha, 3, 2);

		$postrans = "pos_trans".$y.$m;
	
		$cond = "";
		$cond2 = "";

		if($type == "C"){
			$cond	= "AND tipo = 'C'";
			$cond2	= "AND pt.tipo = 'C'";
			$condval = "AND vd.ch_articulo IN (SELECT ch_codigocombustible FROM comb_ta_combustibles)";
		}elseif($type == "M"){
			$cond	= "AND tipo = 'M'";
			$cond2	= "AND pt.tipo = 'M'";
			$condval = "AND vd.ch_articulo NOT IN (SELECT ch_codigocombustible FROM comb_ta_combustibles)";
		}

		$sql = "
			SELECT
				turno,
				'12' as tipo,
				FIRST(pos.printerserial) || ' - ' || caja as serie,
				MIN(trans) ||' - '|| MAX(trans) as rango,
				COUNT(trans) as cantidad,
				(SELECT
					COUNT(*)
				FROM
					$postrans pt
				WHERE
					pt.importe		= 0
					AND pt.cantidad	= 0
					AND pt.precio	= 0
					AND pt.igv		= 0
					AND pt.soles_km	= 0
					AND pt.caja		= t.caja
					AND pt.trans BETWEEN MIN(t.trans) AND MAX(t.trans)
					$cond2
				) as anulado,
				(SELECT
					SUM(importe)
				FROM
					$postrans pt
				WHERE
					pt.dia		= t.dia
					AND pt.td IN ('B','F')
					AND pt.caja	= t.caja
					AND pt.turno = t.turno
					AND pt.trans BETWEEN MIN(t.trans) AND MAX(t.trans)
					$cond2
				) as total,
				(SELECT
					SUM(VAL.nu_importe_vale) + ABS(COALESCE(SUM(pt.importe), 0)) AS nu_importe_vale
				FROM
					(SELECT
						VAL.ch_sucursal,
						VAL.dt_fecha,
						VAL.ch_turno,
						VAL.ch_lado,
						VAL.ch_documento,
						VAL.ch_cliente,
						VAL.ch_tarjeta,
						VAL.ch_placa,
						VAL.nu_importe AS nu_importe_vale
					FROM
						val_ta_cabecera AS VAL
						LEFT JOIN val_ta_detalle AS vd ON(VAL.ch_sucursal = vd.ch_sucursal AND VAL.dt_fecha = vd.dt_fecha AND VAL.ch_documento = vd.ch_documento)
					WHERE
						VAL.dt_fecha 		= t.dia
						AND VAL.ch_caja 	= t.caja
						AND VAL.ch_turno 	= t.turno
						$condval
					) AS VAL
					LEFT JOIN $postrans AS pt ON (pt.grupo = 'D' AND pt.es = VAL.ch_sucursal AND pt.dia = VAL.dt_fecha AND pt.caja ||'-'|| pt.trans = VAL.ch_documento AND pt.turno = VAL.ch_turno AND pt.pump = VAL.ch_lado AND pt.placa = VAL.ch_placa AND pt.cuenta = VAL.ch_cliente AND pt.tarjeta = VAL.ch_tarjeta $cond2)
				) as totalvales
			FROM
				$postrans t
				LEFT JOIN s_pos pos ON(t.caja = pos.s_pos_id::CHAR)
			WHERE
				date(dia)	= to_date('" . pg_escape_string($fecha) . "', 'DD/MM/YYYY')
				AND es		= '$almacen'
				$cond
			GROUP BY
				dia,
				turno,
				caja
			ORDER BY
				turno,
				caja,
				MIN(trans);
		";

		echo "\nTICKETS DETALLADO POR SERIE: \n".$sql;

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

	    	$a = $sqlca->fetchRow();	    

	    	$turno 		= $a[0];
	    	$tipo 		= $a[1];
	    	$serie 		= $a[2];
	    	$rango 		= $a[3];
	    	$cantidad	= $a[4];
	    	$anulado	= $a[5];
			$total	 	= $a[6];
			$totalvales = $a[7];

			/* REGISTROS TICKETS */
			$result['tipos'][$tipo]['series'][$turno]['ventas'][$rango]['turno'] 		= $turno;
			$result['tipos'][$tipo]['series'][$turno]['ventas'][$rango]['tipo'] 		= $tipo;
			$result['tipos'][$tipo]['series'][$turno]['ventas'][$rango]['serie'] 		= $serie;
			$result['tipos'][$tipo]['series'][$turno]['ventas'][$rango]['rango'] 		= $rango;
			$result['tipos'][$tipo]['series'][$turno]['ventas'][$rango]['cantidad'] 	= $cantidad;
			$result['tipos'][$tipo]['series'][$turno]['ventas'][$rango]['anulado'] 		= $anulado;
			$result['tipos'][$tipo]['series'][$turno]['ventas'][$rango]['total'] 		= $total;
			$result['tipos'][$tipo]['series'][$turno]['ventas'][$rango]['totalvales'] 	= $totalvales;

	    	/* Totales por SERIE */
	    	@$result['tipos'][$tipo]['series'][$turno]['totales']['total'] += $total;
	    	@$result['tipos'][$tipo]['series'][$turno]['totales']['totalvales'] += $totalvales;

	    	/* Totales por TIPO */
	    	@$result['tipos'][$tipo]['totales']['total'] += $total;
	    	@$result['tipos'][$tipo]['totales']['totalvales'] += $totalvales;

	    	/* TOTAL GENERAL */
	    	@$result['totales']['total'] += $total;
	    	@$result['totales']['totalvales'] += $totalvales;

		}
	
		return $result;

    }

    function SearchDocumentos($almacen, $fecha, $type) {
		global $sqlca;	

		$cond = "";
		$cond2 = "";

		if($type == "C"){
			$cond = "AND det.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			$cond2 = "AND ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN (SELECT
												ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento
										FROM
											fac_ta_factura_detalle
										WHERE
											ch_fac_tipodocumento = cab.ch_fac_tipodocumento
											AND ch_fac_seriedocumento = cab.ch_fac_seriedocumento
											AND ch_fac_numerodocumento=cab.ch_fac_numerodocumento
											AND art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')
										)";
		}elseif($type == "M"){
			$cond = "AND det.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			$cond2 = "AND ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento IN (SELECT
												ch_fac_tipodocumento||ch_fac_seriedocumento||ch_fac_numerodocumento
										FROM
											fac_ta_factura_detalle
										WHERE
											ch_fac_tipodocumento = cab.ch_fac_tipodocumento
											AND ch_fac_seriedocumento = cab.ch_fac_seriedocumento
											AND ch_fac_numerodocumento=cab.ch_fac_numerodocumento
											AND art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')
										)";
		}

		$sql = "
			SELECT
				cab.ch_fac_tiporecargo3 turno,
				gen.tab_car_03 tipo,
				cab.ch_fac_seriedocumento as serie,
				MIN(cab.ch_fac_numerodocumento) || ' - ' || MAX(cab.ch_fac_numerodocumento) as rango,
				COUNT(cab.ch_fac_numerodocumento) as cantidad,
				(SELECT
					count(*)
				FROM
					fac_ta_factura_cabecera t
					INNER JOIN fac_ta_factura_detalle det ON(det.ch_fac_tipodocumento=t.ch_fac_tipodocumento AND det.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND det.ch_fac_numerodocumento=t.ch_fac_numerodocumento)
					LEFT JOIN int_tabla_general as gent ON(t.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				WHERE
					t.ch_fac_anulado 		= 'S'
					AND gent.tab_car_03 		= gen.tab_car_03
					AND t.ch_fac_seriedocumento 	= cab.ch_fac_seriedocumento
					AND t.dt_fac_fecha 		= cab.dt_fac_fecha
					AND t.ch_almacen		= cab.ch_almacen
					AND t.ch_fac_numerodocumento BETWEEN MIN(cab.ch_fac_numerodocumento) AND MAX(cab.ch_fac_numerodocumento)
					$cond
				) as anulado,
				(SELECT
					SUM(det.nu_fac_valortotal)
				FROM
					fac_ta_factura_cabecera t
					INNER JOIN fac_ta_factura_detalle det ON(det.ch_fac_tipodocumento=t.ch_fac_tipodocumento AND det.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND det.ch_fac_numerodocumento=t.ch_fac_numerodocumento)
					LEFT JOIN int_tabla_general as gent ON(t.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				WHERE
					t.ch_fac_tiporecargo2 IS NULL
					AND t.ch_fac_tiporecargo3 > '0'
					AND gent.tab_car_03 		= gen.tab_car_03
					AND t.ch_fac_seriedocumento 	= cab.ch_fac_seriedocumento
					AND t.dt_fac_fecha 		= cab.dt_fac_fecha
					AND t.ch_almacen		= cab.ch_almacen
					AND t.ch_fac_numerodocumento BETWEEN MIN(cab.ch_fac_numerodocumento) AND MAX(cab.ch_fac_numerodocumento)
					$cond
				) as total,
				(SELECT
					SUM(det.nu_fac_valortotal)
				FROM
					fac_ta_factura_cabecera t
					INNER JOIN fac_ta_factura_detalle det ON(det.ch_fac_tipodocumento=t.ch_fac_tipodocumento AND det.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND det.ch_fac_numerodocumento=t.ch_fac_numerodocumento)
					LEFT JOIN int_tabla_general as gent ON(t.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				WHERE
					t.ch_fac_tiporecargo2 ='S'
					AND t.ch_fac_tiporecargo3 > '0'
					AND gent.tab_car_03 		= gen.tab_car_03
					AND t.ch_fac_seriedocumento 	= cab.ch_fac_seriedocumento
					AND t.dt_fac_fecha 		= cab.dt_fac_fecha
					AND t.ch_almacen		= cab.ch_almacen
					AND t.ch_fac_numerodocumento BETWEEN MIN(cab.ch_fac_numerodocumento) AND MAX(cab.ch_fac_numerodocumento)
					$cond
				) as totalgratuita
			FROM
				fac_ta_factura_cabecera cab
				--INNER JOIN fac_ta_factura_detalle d ON(d.ch_fac_tipodocumento=cab.ch_fac_tipodocumento AND d.ch_fac_seriedocumento=cab.ch_fac_seriedocumento AND d.ch_fac_numerodocumento=cab.ch_fac_numerodocumento)
				LEFT JOIN int_tabla_general as gen ON(cab.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
			WHERE
				date(cab.dt_fac_fecha) 		= to_date('" . pg_escape_string($fecha) . "', 'DD/MM/YYYY')
				AND cab.ch_almacen 		= '$almacen'
				AND cab.ch_fac_tipodocumento 	!= '45'
				AND cab.ch_fac_tiporecargo3 	> '0'
				$cond2
			GROUP BY
				turno,
				tipo,
				serie,
				cab.dt_fac_fecha,
				gen.tab_descripcion,
				cab.ch_almacen
			ORDER BY
				tipo,
				serie,
				cab.dt_fac_fecha,
				MIN(cab.ch_fac_numerodocumento);
		";

		echo "\n DOCUMENTOS DETALLADO POR SERIE: \n".$sql;

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

		    	$a = $sqlca->fetchRow();		    

		    	$turno 		= $a[0];
		    	$tipo 		= $a[1];
		    	$serie 		= $a[2];
		    	$rango 		= $a[3];
		    	$cantidad	= $a[4];
		    	$anulado	= $a[5];
			$total	 	= $a[6];
			$totalgratuita 	= $a[7];

			/* REGISTROS DOCUMENTOS MANUALES */
			$result['tipos'][$tipo]['series'][$serie]['ventas'][$rango]['turno'] 	= $turno;
			$result['tipos'][$tipo]['series'][$serie]['ventas'][$rango]['tipo'] 	= $tipo;
			$result['tipos'][$tipo]['series'][$serie]['ventas'][$rango]['serie'] 	= $serie;
			$result['tipos'][$tipo]['series'][$serie]['ventas'][$rango]['rango'] 	= $rango;
			$result['tipos'][$tipo]['series'][$serie]['ventas'][$rango]['cantidad'] = $cantidad;
			$result['tipos'][$tipo]['series'][$serie]['ventas'][$rango]['anulado'] 	= $anulado;
			$result['tipos'][$tipo]['series'][$serie]['ventas'][$rango]['total'] 	= $total;
			$result['tipos'][$tipo]['series'][$serie]['ventas'][$rango]['totalgratuita'] 	= $totalgratuita;


		    	/* Totales por SERIE */
		    	@$result['tipos'][$tipo]['series'][$serie]['totales']['total'] += $total;
		    	@$result['tipos'][$tipo]['series'][$serie]['totales']['totalgratuita'] += $totalgratuita;

		    	/* Totales por TIPO */
		    	@$result['tipos'][$tipo]['totales']['total'] += $total;
		    	@$result['tipos'][$tipo]['totales']['totalgratuita'] += $totalgratuita;

		    	/* TOTAL GENERAL */
		    	@$result['totales']['total'] += $total;
		    	@$result['totales']['totalgratuita'] += $totalgratuita;

		}
	
		return $result;

    	}
   
}
