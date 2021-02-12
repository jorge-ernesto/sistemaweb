<?php

class RegistroVentasModel extends Model {

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
    
    	function SearchTickets($almacen, $fdesde, $fhasta, $type) {
		global $sqlca;

		$y = substr($fdesde, 6, 4);
		$m = substr($fdesde, 3, 2);

		$postrans = "pos_trans".$y.$m;

		$cond 	= "";
		$cond2 	= "";

		if($type == "C"){
			$cond 	= "AND t.tipo 	= 'C'";
			$cond2 	= "AND pt.tipo	= 'C'";
			$cond3 	= "AND tipo 	= 'C'";
		}elseif($type == "M"){
			$cond 	= "AND t.tipo 	= 'M'";
			$cond2 	= "AND pt.tipo 	= 'M'";
			$cond3 	= "AND tipo 	= 'M'";
		}
	
		$sql = "
			SELECT
				'12' as tipo,
				FIRST(cfp.nu_posz_z_serie) || ' - ' || t.caja as serie,
				MIN(t.trans) ||' - '|| MAX(t.trans) as rango,
				TO_CHAR(t.dia, 'dd/mm/YYYY') as femision,
				COUNT(t.trans) as cantidad,
				(SELECT
					COUNT(*)
				FROM
					$postrans pt
				WHERE
					pt.importe	= 0
					AND pt.cantidad	= 0
					AND pt.precio	= 0
					AND pt.igv	= 0
					AND pt.soles_km	= 0
					AND pt.caja	= t.caja
					AND pt.dia	= t.dia
					AND pt.trans BETWEEN MIN(t.trans) AND MAX(t.trans)
				) as anulado,
				SUM(allmonto.nubi) as vbruto,
				'0.00' as dscto,
				SUM(allmonto.nubi) as vventa,
				SUM(allmonto.nuigv) as igv,
				SUM(allmonto.nutotal) as total,
				'TICKETS DE VENTAS' as desc
			FROM
				$postrans t
				LEFT JOIN pos_z_cierres cfp ON(t.es = cfp.ch_sucursal AND t.dia = cfp.dt_posz_fecha_sistema::DATE AND t.caja = cfp.ch_posz_pos AND t.turno::INTEGER = cfp.nu_posturno)
				LEFT JOIN (
						SELECT
							dia,
							caja,
							trans,
							codigo,
							ROUND(SUM(importe - igv), 2) as nubi,
							ROUND(SUM(igv), 2) as nuigv,
							ROUND(SUM(importe), 2) as nutotal
						FROM
							$postrans
						WHERE
							DATE(dia) BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
							AND es = '$almacen'
							AND td IN ('B', 'F')
							$cond3
						GROUP BY	
							dia,
							caja,
							trans,
							codigo
				) AS allmonto ON (allmonto.dia = t.dia AND allmonto.caja = t.caja AND allmonto.trans = t.trans AND allmonto.codigo = t.codigo)
			WHERE
				DATE(t.dia) BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
				AND t.es = '$almacen'
				AND t.td IN ('B', 'F')
				$cond
			GROUP BY
				t.dia,
				t.caja
			ORDER BY
				t.dia,
				t.caja,
				MIN(t.trans);
		";

//		echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

		    	$a = $sqlca->fetchRow();	    

		    	$tipo 		= $a[0];
		    	$serie 		= $a[1];
		    	$rango 		= $a[2];
		    	$femision 	= $a[3];
		    	$cantidad	= $a[4];
		    	$anulado	= $a[5];
			$vbruto	 	= $a[6];
			$dscto	 	= $a[7];
			$vventa	 	= $a[8];
			$igv		= $a[9];
			$total	 	= $a[10];
			$desc	 	= $a[11];

			/* REGISTROS TICKETS */
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['tipo'] 	= $tipo;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['serie'] 	= $serie;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['rango'] 	= $rango;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['femision'] = $femision;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['cantidad'] = $cantidad;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['anulado'] 	= $anulado;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['vbruto'] 	= $vbruto;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['dscto'] 	= $dscto;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['vventa'] 	= $vventa;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['igv'] 	= $igv;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['total'] 	= $total;


		    	/* Totales por SERIE */
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['cantidad']	+= $cantidad;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['anulado'] 	+= $anulado;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['vbruto'] 	+= $vbruto;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['dscto'] 		+= $dscto;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['vventa'] 	+= $vventa;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['igv'] 		+= $igv;
		    	@$result['tipos'][$desc]['series'][$serie]['totales']['total'] 		+= $total;

		    	/* Totales por TIPO */
		    	@$result['tipos'][$desc]['totales']['cantidad'] += $cantidad;
		    	@$result['tipos'][$desc]['totales']['anulado'] 	+= $anulado;
		    	@$result['tipos'][$desc]['totales']['vbruto'] 	+= $vbruto;
		    	@$result['tipos'][$desc]['totales']['dscto'] 	+= $dscto;
		    	@$result['tipos'][$desc]['totales']['vventa'] 	+= $vventa;
		    	@$result['tipos'][$desc]['totales']['igv'] 	+= $igv;
		    	@$result['tipos'][$desc]['totales']['total'] 	+= $total;

		}
	
		return $result;

    	}

    	function SearchDocumentos($almacen, $fdesde, $fhasta, $type) {
		global $sqlca;
	
		$cond	= "";
		$cond2	= "";

		if($type == "C"){
			$cond 	="AND det.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			$cond2 	="AND tdet.art_codigo IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
		}elseif($type == "M"){
			$cond 	= "AND det.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
			$cond2 	="AND tdet.art_codigo NOT IN ('11620301','11620302','11620303','11620304','11620305','11620307')";
		}

		$sql = "
			SELECT
				gen.tab_car_03 tipo,
				fall.ch_fac_seriedocumento as serie,
				MIN(fall.ch_fac_numerodocumento) || '-' || MAX(fall.ch_fac_numerodocumento) as rango,
				TO_CHAR(fall.dt_fac_fecha, 'dd/mm/YYYY') as femision,
				COUNT(fall.ch_fac_numerodocumento) as cantidad,
				(SELECT
					COUNT(*)
				FROM
					fac_ta_factura_cabecera t
					LEFT JOIN fac_ta_factura_detalle tdet ON(t.cli_codigo = tdet.cli_codigo AND tdet.ch_fac_tipodocumento=t.ch_fac_tipodocumento AND tdet.ch_fac_seriedocumento=t.ch_fac_seriedocumento AND tdet.ch_fac_numerodocumento=t.ch_fac_numerodocumento)
					LEFT JOIN int_tabla_general as gent ON(t.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				WHERE
					t.ch_fac_anulado 				= 'S'
					AND gent.tab_car_03 			= gen.tab_car_03
					AND t.ch_fac_seriedocumento 	= fall.ch_fac_seriedocumento
					AND t.dt_fac_fecha 				= fall.dt_fac_fecha
					AND t.ch_fac_numerodocumento BETWEEN MIN(fall.ch_fac_numerodocumento) AND MAX(fall.ch_fac_numerodocumento)
					$cond2
				) as anulado,
				SUM(allmonto.vbruto),
				SUM(allmonto.dscto),
				SUM(allmonto.vventa),
				SUM(allmonto.igv),
				CASE WHEN fall.ch_fac_tiporecargo2 = 'T' then SUM(allmonto.igv) else SUM(allmonto.total) END,
				gen.tab_descripcion as desc
			FROM
				fac_ta_factura_cabecera fall
				LEFT JOIN fac_ta_factura_detalle det ON(fall.cli_codigo = det.cli_codigo AND det.ch_fac_tipodocumento=fall.ch_fac_tipodocumento AND det.ch_fac_seriedocumento=fall.ch_fac_seriedocumento AND det.ch_fac_numerodocumento=fall.ch_fac_numerodocumento)
				LEFT JOIN int_tabla_general as gen ON(fall.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
				LEFT JOIN (
				SELECT
					gen.tab_car_03 tipo,
					fmonto.ch_fac_seriedocumento as serie,
					fmonto.dt_fac_fecha as femision, 
					fmonto.ch_fac_numerodocumento AS nudocumento,
					det.cli_codigo as nucodcliente,
					det.art_codigo as nucodproducto,
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_importeneto) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_importeneto), 2) END) as vbruto,
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_descuento1) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_descuento1), 2) END) as dscto,
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_importeneto) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_importeneto), 2) END) as vventa,
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_impuesto1) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_impuesto1), 2) END) as igv, 
					(CASE WHEN fmonto.ch_fac_moneda = '02' THEN ROUND(SUM(det.nu_fac_valortotal) * FIRST(TC.tca_venta_oficial), 2) ELSE ROUND(SUM(det.nu_fac_valortotal), 2) END) as total
				FROM
					fac_ta_factura_cabecera fmonto
					LEFT JOIN fac_ta_factura_detalle det ON(fmonto.cli_codigo = det.cli_codigo AND det.ch_fac_tipodocumento=fmonto.ch_fac_tipodocumento AND det.ch_fac_seriedocumento=fmonto.ch_fac_seriedocumento AND det.ch_fac_numerodocumento=fmonto.ch_fac_numerodocumento)
					LEFT JOIN int_tabla_general as gen ON(fmonto.ch_fac_tipodocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
					LEFT JOIN int_tipo_cambio TC ON (TC.tca_fecha = fmonto.dt_fac_fecha)
				WHERE
					DATE(fmonto.dt_fac_fecha) BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') and TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
					AND fmonto.ch_almacen = '$almacen'
					AND fmonto.ch_fac_tipodocumento != '45'
					$cond
				GROUP BY
					tipo,
					serie,
					femision,
					nudocumento,
					nucodcliente,
					nucodproducto,
					fmonto.ch_fac_moneda
				ORDER BY
					tipo,
					serie,
					femision,
					nudocumento
				) AS allmonto ON (allmonto.nucodcliente = fall.cli_codigo AND allmonto.tipo = gen.tab_car_03 AND allmonto.serie = fall.ch_fac_seriedocumento AND allmonto.nudocumento = fall.ch_fac_numerodocumento AND allmonto.nucodproducto = det.art_codigo)
			WHERE
				DATE(fall.dt_fac_fecha) BETWEEN TO_DATE('" . pg_escape_string($fdesde) . "', 'DD/MM/YYYY') and TO_DATE('" . pg_escape_string($fhasta) . "', 'DD/MM/YYYY')
				AND fall.ch_almacen = '$almacen'
				AND fall.ch_fac_tipodocumento != '45'
				$cond
			GROUP BY
				gen.tab_car_03,
				fall.ch_fac_seriedocumento,
				fall.dt_fac_fecha,
				gen.tab_descripcion,
				fall.ch_fac_tiporecargo2
			ORDER BY
				tipo,
				serie,
				fall.dt_fac_fecha,
				MIN(fall.ch_fac_numerodocumento);
		";

		//echo $sql;

		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {

	    	$a = $sqlca->fetchRow();		    

	    	$tipo 		= $a[0];
	    	$serie 		= $a[1];
	    	$rango 		= $a[2];
	    	$femision 	= $a[3];
	    	$cantidad	= $a[4];
	    	$anulado	= $a[5];

			$vbruto	 	= $a[6];
			$dscto	 	= $a[7];
			$vventa	 	= ($a[6] - $a[7]);
			$igv		= $a[9];
			$total	 	= $a[10];
			$desc	 	= $a[11];

			$numeros = explode("-",$rango);

			$numero		= $numeros[0] - 1;//Le resto menos uno porque no contabiliza el primer documento
			$numeronew	= $numeros[1];

			$cantreal	= $numeronew - $numero;

			/* REGISTROS DOCUMENTOS MANUALES */
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['tipo'] 	= $tipo;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['serie'] 	= $serie;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['rango'] 	= $rango;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['femision'] = $femision;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['cantidad'] = $cantidad;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['cantreal'] = $cantreal;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['anulado'] 	= $anulado;

			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['vbruto'] 	= $vbruto;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['dscto'] 	= $dscto;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['vventa'] 	= $vventa;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['igv'] 		= $igv;
			$result['tipos'][$desc]['series'][$serie]['ventas'][$rango]['total'] 	= $total;

	    	/* Totales por SERIE */
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['cantidad'] 	+= $cantidad;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['anulado'] 	+= $anulado;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['vbruto'] 	+= $vbruto;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['dscto'] 		+= $dscto;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['vventa'] 	+= $vventa;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['igv'] 		+= $igv;
	    	@$result['tipos'][$desc]['series'][$serie]['totales']['total'] 		+= $total;

	    	/* Totales por TIPO */
	    	@$result['tipos'][$desc]['totales']['cantidad'] += $cantidad;
	    	@$result['tipos'][$desc]['totales']['anulado'] 	+= $anulado;
	    	@$result['tipos'][$desc]['totales']['vbruto'] 	+= $vbruto;
	    	@$result['tipos'][$desc]['totales']['dscto'] 	+= $dscto;
	    	@$result['tipos'][$desc]['totales']['vventa'] 	+= $vventa;
	    	@$result['tipos'][$desc]['totales']['igv'] 		+= $igv;
	    	@$result['tipos'][$desc]['totales']['total'] 	+= $total;


	    	/* TOTAL GENERAL */
	    	if($tipo == '07'){
		    	@$result['totales']['vbruto_nc'] 	+= $vbruto;
		    	@$result['totales']['dscto_nc'] 	+= $dscto;
		    	@$result['totales']['vventa_nc'] 	+= $vventa;
		    	@$result['totales']['igv_nc'] 		+= $igv;
		    	@$result['totales']['total_nc'] 	+= $total;
		    }else{
		    	@$result['totales']['cantidad'] += $cantidad;
		    	@$result['totales']['anulado'] 	+= $anulado;
		    	@$result['totales']['vbruto'] 	+= $vbruto;
		    	@$result['totales']['dscto'] 	+= $dscto;
		    	@$result['totales']['vventa'] 	+= $vventa;
		    	@$result['totales']['igv'] 		+= $igv;
		    	@$result['totales']['total'] 	+= $total;
		    }

		}
	
		return $result;

   	}
   
}
