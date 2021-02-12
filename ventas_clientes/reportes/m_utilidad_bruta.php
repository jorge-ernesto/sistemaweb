<?php

class UtilidadBrutaModel extends Model {

	function obtenerCostoUltimaCompra($almacen, $hasta, $art_codigo) {
		global $sqlca;

		$hasta = trim($hasta);
		$hasta = strip_tags($hasta);
		$hasta = explode("/", $hasta);
		$hasta = $hasta[2] . "-" . $hasta[1] . "-" . $hasta[0];

		$status = $sqlca->query("
		SELECT
			mov_costounitario AS ultmcosto
		FROM
			inv_movialma
		WHERE
			tran_codigo IN ('01', '21')
			AND mov_almacen = '" . $almacen . "'
			AND mov_fecha <= '" . $hasta . " 23:59:59'
			AND art_codigo = '" . trim($art_codigo) . "'
			AND mov_costounitario > 0
		ORDER BY
			mov_fecha DESC
		LIMIT 1;
		");

		$arrResult['estado'] = FALSE;
		$arrResult['result'] = '';

		if($status < 0)
			$arrResult['mensaje'] = 'Error SQL - function obtenerCostoUltimaCompra';
		else if($status == 0)
			$arrResult['mensaje'] = 'No se encontró ningún registro';
		else{
			$arrResult['estado'] = TRUE;
			$row = $sqlca->fetchRow();
			$arrResult['result'] = $row["ultmcosto"];
		}

		return $arrResult;
	}

	function obtieneVentas($almacen, $desde, $hasta, $anio, $mes, $iDetalladoPorDia) {
		global $sqlca;

		//Solo para Servigrifos Marbella es round(sum(comb.nu_ventagalon / 2),2) as cantidad,
		$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'lista precio';";

		if ($sqlca->query($sql) < 0)
			return false;

		$a 				= $sqlca->fetchRow();
		$listaprecio 	= trim($a[0]);

		$sanio 			= substr($desde,6,4);
		$mes 			= substr($desde,3,2);

		$sColumnFecha = '';
		$sCondFecha = '';
		$cond_obtener_ultimo_costo = "FIRST(sal.stk_costo" . $mes . ") AS ultmcosto,";
		$left_join_movialma = '';
		if ( $iDetalladoPorDia == 1 ) {//1 = Si
			$sColumnFecha = 'CTC.dt_fechaparte,';
			$sCondFecha = 'COMBPRECIO.dt_fechaparte = CTC.dt_fechaparte AND ';
			$cond_obtener_ultimo_costo = 'FIRST(MOVI.mov_costopromedio) AS ultmcosto,';
			$left_join_movialma = "LEFT JOIN inv_movialma AS MOVI ON(MOVI.tran_codigo='25' AND MOVI.mov_almacen = CTC.ch_sucursal AND MOVI.mov_fecha::DATE = CTC.dt_fechaparte AND MOVI.art_codigo = CTC.ch_codigocombustible)";
		}

		$sql = "
		SELECT
			" . $sColumnFecha . "
			art.art_codigo as codigo,
			art.art_descripcion as articulo,
			--max(pre.pre_precio_act1/(1+(util_fn_igv()/100))) as costovta,
			max(COMBPRECIO.precio/(1+(util_fn_igv()/100))) as costovta,
			" . $cond_obtener_ultimo_costo . "
			--ROUND(MAX(pre.pre_precio_act1/(1+(util_fn_igv()/100))) - 
			ROUND(MAX(COMBPRECIO.precio/(1+(util_fn_igv()/100))) - 
			(CASE 
				WHEN FIRST(sal.stk_costo".$mes.") = 0.0000 THEN 
					(SELECT 
					    mov_costounitario 
					FROM
					    inv_movialma WHERE mov_fecha::DATE <= TO_DATE('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY') AND art_codigo = art.art_codigo
					GROUP BY
						mov_costounitario,
						mov_fecha,
						mov_costopromedio
					ORDER BY
						mov_fecha DESC 
					LIMIT 1)
			ELSE
				FIRST(sal.stk_costo".$mes.")
			END)
			,2) as ganancia,
			123 as margen,
			round(sum(CTC.nu_ventagalon),2) as cantidad,
			111 as utilidad,
			CTC.ch_sucursal as almacen,  
			art.art_linea as linea,
			round(sum(nu_afericionveces_x_5),2) * 5 as afericiones
		FROM
	    	comb_ta_contometros AS CTC
			LEFT JOIN int_articulos AS art ON (art.art_codigo=CTC.ch_codigocombustible)
			LEFT JOIN fac_lista_precios AS pre ON (pre.art_codigo=CTC.ch_codigocombustible)
			LEFT JOIN inv_saldoalma AS sal ON (sal.art_codigo = art.art_codigo AND sal.stk_periodo = '" . $sanio . "' AND sal.stk_almacen = '" . $almacen . "')
			LEFT JOIN (
			SELECT
			 CTC.ch_sucursal,
			 " . $sColumnFecha . "
			 ITEM.art_linea,
			 DISPENSADOR.ch_codigocombustible,
			 (SUM(precio) / COUNT(*)) AS precio
			FROM
			 comb_ta_contometros AS CTC
			 LEFT JOIN comb_ta_surtidores AS DISPENSADOR ON (CTC.ch_surtidor = DISPENSADOR.ch_surtidor)
			 LEFT JOIN pos_contometros AS PC ON (PC.dia = CTC.dt_fechaparte AND PC.num_lado::VARCHAR = DISPENSADOR.ch_numerolado AND PC.manguera = DISPENSADOR.nu_manguera)
			 LEFT JOIN int_articulos AS ITEM ON (ITEM.art_codigo=CTC.ch_codigocombustible)
			WHERE
			 CTC.ch_sucursal='" . $almacen . "'
			 AND CTC.dt_fechaparte BETWEEN TO_DATE('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			 AND CTC.nu_ventavalor > 0.0000
	    	 AND CTC.nu_ventagalon > 0.0000
			 AND PC.cnt_vol > 0.0000
			 AND PC.cnt_val > 0.0000
			GROUP BY CTC.ch_sucursal,".$sColumnFecha."ITEM.art_linea,DISPENSADOR.ch_codigocombustible
			) AS COMBPRECIO ON(COMBPRECIO.ch_sucursal = CTC.ch_sucursal AND " . $sCondFecha . " COMBPRECIO.art_linea = art.art_linea AND COMBPRECIO.ch_codigocombustible = CTC.ch_codigocombustible)
			" . $left_join_movialma . "
		WHERE
			CTC.dt_fechaparte BETWEEN TO_DATE('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
	    	AND CTC.nu_ventavalor > 0
	    	AND CTC.nu_ventagalon > 0
	    	AND CTC.ch_sucursal = '" . $almacen . "'
			AND pre.pre_lista_precio = '" . $listaprecio . "'
		GROUP BY
		    almacen,
			" . $sColumnFecha . "
			codigo,
			articulo,
			linea
		ORDER BY
			" . $sColumnFecha . "
			linea,
			codigo;
		";


		echo "<pre>";
		echo "COMBUSTIBLE: " . $sql;
		echo "</pre>";


		$iStatusSQL = $sqlca->query($sql);

	    if ( (int)$iStatusSQL < 0 ) {
		    $arrResponse = array(
		        'sStatus' => 'danger',
		        'sMessage' => 'problemas al obtener datos de combustible',
		        'sMessageSQL' => $sqlca->get_error(),
		    );
		    return $arrResponse;
	    }

	    if ( $iStatusSQL == 0 ) {
	        $arrResponse = array(
	            'sStatus' => 'warning',
	            'sMessage' => 'No hay registros'
	        );
		    return $arrResponse;
	    }

        $arrDataSQL = $sqlca->fetchAll();
        $arrResponse = array(
            'sStatus' => 'success',
            'sMessage' => 'Datos encontrados',
            'arrData' => $arrDataSQL
        );
	    return $arrResponse;

		/*
		if ($sqlca->query($sql) < 0) 
			return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a 							= $sqlca->fetchRow();
			$resultado[$i]['codigo'] 	= $a[0];
			$resultado[$i]['articulo'] 	= $a[1];
			$resultado[$i]['costovta'] 	= $a[2];
			$resultado[$i]['ultmcosto'] = $a[3];
			$resultado[$i]['ganancia'] 	= $a[4];
			$resultado[$i]['margen'] 	= $a[5];
			$resultado[$i]['cantidad'] 	= $a[6];
			$resultado[$i]['utilidad'] 	= $a[7];
			$resultado[$i]['almacen'] 	= $a[8];
			$resultado[$i]['linea'] 	= $a[9];
			$resultado[$i]['afericiones'] = $a[10];
		}
		*/

		return $arrResponse;
    }

	function obtieneVentasMarket($almacen, $desde, $hasta, $anio, $mes, $tipo, $hanio, $hmes, $uprecio) {
		global $sqlca;

		$sql = "SELECT par_valor FROM int_parametros WHERE par_nombre = 'lista precio';";

		if ($sqlca->query($sql) < 0) 
			return false;

		if ($anio.$mes != $hanio.$hmes){
			return false;
		}

		$a = $sqlca->fetchRow();
		$listaprecio = trim($a[0]);

		$smes 	= substr($desde,3,2);
		$sanio 	= substr($desde,6,4);

		if($tipo == "K"){
			if($uprecio == "U"){

				$sql = "
				SELECT
					art.art_codigo as codigo,
					art.art_descripcion as articulo,
					max(pre.pre_precio_act1/(1+(util_fn_igv()/100))) as costovta,
					max(pro.rec_precio) as ultmcosto,
					max((pre.pre_precio_act1/(1+(util_fn_igv()/100))) - pro.rec_precio) as ganancia,
					123 as margen,
					sum(m.mov_cantidad) as cantidad,
					111 as utilidad,
					m.mov_almacen as almacen,
					art.art_linea as linea,
					ROUND((max(pre.pre_precio_act1/(1+(util_fn_igv()/100))) * sum(m.mov_cantidad)), 2) as vimporte,
					SUM(m.mov_costototal) as kimporte
				FROM
					inv_movialma m
					LEFT JOIN int_articulos art ON (art.art_codigo = m.art_codigo)
					LEFT JOIN fac_lista_precios pre ON (pre.art_codigo = m.art_codigo)		
					LEFT JOIN (SELECT art_codigo, max(rec_precio) AS rec_precio FROM com_rec_pre_proveedor GROUP BY art_codigo, rec_fecha_ultima_compra ORDER BY rec_fecha_ultima_compra DESC LIMIT 1) AS pro ON (pro.art_codigo = art.art_codigo)
				WHERE
					m.mov_fecha::DATE BETWEEN to_date('". pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					AND m.mov_almacen 			= '$almacen'
					AND art.art_plutipo 		= '1'
					AND m.tran_codigo 			= '45'
					AND pre.pre_lista_precio 	= '".$listaprecio."'
				GROUP BY
				    almacen,
					art.art_codigo,
					articulo,
					linea
				ORDER BY
					linea,
					codigo;
				";
			}else{
				$sql = "
				SELECT
					art.art_codigo as codigo,
					art.art_descripcion as articulo,
					max(pre.pre_precio_act1/(1+(util_fn_igv()/100))) as costovta,
					CASE 
					WHEN sal.stk_costo".$smes." = '0.0000' THEN 
						COALESCE((SELECT 
						mov_costounitario 
						FROM inv_movialma WHERE mov_fecha < '" . ($sanio . "-" . $smes . "-01") . " 00:00:00' AND art_codigo = art.art_codigo
						GROUP BY
							mov_costounitario,
							mov_fecha
						ORDER BY 
							mov_fecha DESC 
						LIMIT 1),0)
					ELSE sal.stk_costo".$smes."
					END as ultmcosto,
					max((pre.pre_precio_act1/(1+(util_fn_igv()/100))) - pro.rec_precio) as ganancia,
					123 as margen,
					sum(m.mov_cantidad) as cantidad,
					111 as utilidad,
					m.mov_almacen as almacen,  
					art.art_linea as linea,
					ROUND((max(pre.pre_precio_act1/(1+(util_fn_igv()/100))) * sum(m.mov_cantidad)), 2) as vimporte,
					SUM(m.mov_costototal) as kimporte
				FROM
					inv_movialma m
					LEFT JOIN int_articulos art ON (art.art_codigo = m.art_codigo)
					LEFT JOIN fac_lista_precios pre ON (pre.art_codigo = m.art_codigo)		
					LEFT JOIN (SELECT art_codigo, max(rec_precio) AS rec_precio FROM com_rec_pre_proveedor GROUP BY art_codigo, rec_fecha_ultima_compra ORDER BY rec_fecha_ultima_compra DESC LIMIT 1) AS pro ON (pro.art_codigo = art.art_codigo)
					LEFT JOIN inv_saldoalma sal ON (sal.art_codigo = art.art_codigo AND sal.stk_almacen=m.mov_almacen)
				WHERE
					m.mov_fecha::DATE BETWEEN to_date('". pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					AND m.mov_almacen 		= '$almacen'
					AND art.art_plutipo 		= '1'
					AND m.tran_codigo 		= '45'
					AND pre.pre_lista_precio 	= '".$listaprecio."'
					AND sal.stk_periodo 	= '" . pg_escape_string($sanio) . "'	
				GROUP BY
				    	almacen,
					art.art_codigo,
					articulo,
					linea,
					sal.stk_costo".$smes."
				ORDER BY
					linea,
					codigo;
			";
			
			}

		}else{

			$sql = "
				SELECT
			    	art.art_codigo as codigo,
					art.art_descripcion as articulo,
			    	max(pre.pre_precio_act1/(1+(util_fn_igv()/100))) as costovta,
					max(pro.rec_precio) as ultmcosto,
					max((pre.pre_precio_act1/(1+(util_fn_igv()/100))) - pro.rec_precio) as ganancia,
					123 as margen,
					sum(p.cantidad) as cantidad,
					111 as utilidad,
			    	p.es as almacen,  
			    	art.art_linea as linea,
					ROUND((max(pre.pre_precio_act1/(1+(util_fn_igv()/100))) * sum(p.cantidad)), 2) as vimporte,
					SUM(movi.mov_costototal) as kimporte
				FROM
					pos_trans".$anio.$mes." p
					LEFT JOIN int_articulos art ON (art.art_codigo = p.codigo)
					LEFT JOIN fac_lista_precios pre ON (pre.art_codigo = p.codigo)		
					LEFT JOIN (SELECT art_codigo, max(rec_precio) AS rec_precio FROM com_rec_pre_proveedor GROUP BY art_codigo, rec_fecha_ultima_compra ORDER BY rec_fecha_ultima_compra DESC LIMIT 1) AS pro ON (pro.art_codigo = art.art_codigo)
					LEFT JOIN inv_movialma movi ON (movi.mov_fecha = p.dia AND movi.art_codigo = p.codigo AND movi.mov_almacen = p.es AND movi.tran_codigo = '45')
				WHERE
					p.dia::DATE BETWEEN to_date('". pg_escape_string($desde) . "', 'DD/MM/YYYY') AND to_date('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
					AND p.es 						= '" . $almacen . "'
					AND art.art_plutipo			= '1'
					AND p.tipo 						= 'M'
					AND pre.pre_lista_precio 	= '".$listaprecio."'
				GROUP BY
				   almacen,
					art.art_codigo,
					articulo,
					linea
				ORDER BY
					linea,
					codigo;
				";
		}

		//echo "\n\n PRINCIPAL MARKET: \n\n".$sql;

		if ($sqlca->query($sql) <= 0) 
			return 1;
	
		$resultado = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();	    
			$resultado[$i]['codigo'] 	= $a[0];
			$resultado[$i]['articulo'] 	= $a[1];
			$resultado[$i]['costovta'] 	= $a[2];
			$resultado[$i]['ultmcosto'] = $a[3];
			$resultado[$i]['ganancia'] 	= $a[4];
			$resultado[$i]['margen'] 	= $a[5];
			$resultado[$i]['cantidad'] 	= $a[6];
			$resultado[$i]['utilidad'] 	= $a[7];
			$resultado[$i]['almacen'] 	= $a[8];
			$resultado[$i]['linea'] 	= $a[9];
			$resultado[$i]['vimporte'] 	= $a[10];
			$resultado[$i]['kimporte'] 	= $a[11];
		}

		return $resultado;	
    }

	function ultimo_costo_combustibles($desde,$hasta,$almacen,$uprecio){

		global $sqlca;

		$mes 	= substr($desde,3,2);
		$anio 	= substr($desde,6,4);

		if($uprecio == "U"){

		$sql = "
			SELECT
				art_codigo codigo,
				ROUND((COALESCE(SUM(mov_costototal),0) / COALESCE(SUM(mov_cantidad),1)) ,4) AS ultmcosto
			FROM
				inv_movialma
			WHERE
				tran_codigo = '21'
				AND mov_almacen = '" . pg_escape_string($almacen) . "'
				AND mov_fecha::date BETWEEN TO_DATE('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
				AND art_codigo IN('11620301','11620302','11620303','11620304','11620305','11620307')
			GROUP BY
				codigo
			ORDER BY
				codigo;
				";
			

			}else{

			$sql = "
			SELECT
				art_codigo codigo,
				stk_costo".$mes." 
			FROM
				inv_saldoalma
			WHERE
				stk_periodo 	= '" . pg_escape_string($anio) . "'
				AND stk_almacen = '" . pg_escape_string($almacen) . "'
				AND art_codigo IN (SELECT DISTINCT
								ch_codigocombustible
							FROM
								comb_ta_contometros
							WHERE
								dt_fechaparte BETWEEN TO_DATE('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
								AND ch_codigocombustible IN (SELECT
												art_codigo codigo
											FROM
												inv_movialma
											WHERE
												tran_codigo = '21'
												AND mov_almacen = '" . pg_escape_string($almacen) . "'
												AND mov_fecha::date BETWEEN TO_DATE('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
												AND art_codigo IN('11620301','11620302','11620303','11620304','11620305','11620307')
								))
			ORDER BY
				codigo;
			";}

		//echo "\n\n ULTIMO COSTO COMBUSTIBLE: \n\n".$sql;

		if ($sqlca->query($sql) < 0) 
			return false;

		$resultati = Array();

		for ($j = 0; $j < $sqlca->numrows(); $j++) {
		    	$a = $sqlca->fetchRow();	
			$resultati[$j]['codigo'] 	    = $a[0];
			$resultati[$j]['ultmcosto'] 	= $a[1];
		}
			
		return $resultati;

	}

	function costo_vta_combustibles($desde,$hasta){
		global $sqlca;

		$sql = "
			SELECT
				ch_codigocombustible AS codigo,
				ROUND((COALESCE(SUM(nu_ventavalor),0) / COALESCE(SUM(nu_ventagalon),1) / 1.18) ,4) AS costovta,
				art.art_linea AS linea
			FROM
				comb_ta_contometros c
				LEFT JOIN int_articulos art ON(art.art_codigo = c.ch_codigocombustible)
			WHERE
				dt_fechaparte BETWEEN TO_DATE('" . pg_escape_string($desde) . "', 'DD/MM/YYYY') AND TO_DATE('" . pg_escape_string($hasta) . "', 'DD/MM/YYYY')
			GROUP BY
				ch_codigocombustible,
				linea
			ORDER BY
				linea,
				codigo;
		";

		//echo "\n\n COSTO DE VENTA COMBUSTIBLE: \n\n".$sql;

		if($sqlca->query($sql)<0)
			return false;

		$resta = Array();

		for($i = 0; $i < $sqlca->numrows(); $i++){
	    		$a = $sqlca->fetchRow();	    
			$resta[$i]['codigo'] 	= $a[0];
			$resta[$i]['costovta'] 	= $a[1];
			$resta[$i]['linea'] 	= $a[2];
		}
			
		return $resta;
	
	}

	function obtieneListaEstaciones() {
		global $sqlca;
	
		$sql = "SELECT
			    	ch_almacen,
			    	trim(ch_nombre_almacen)
			FROM
			    	inv_ta_almacenes
			WHERE
			    	ch_clase_almacen='1'
			ORDER BY
			    	ch_almacen;";

		if ($sqlca->query($sql) < 0) 
			return false;	
		$result = Array();
	
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
		    	$a = $sqlca->fetchRow();
		    	$result[$a[0]] = $a[0] . " - " . $a[1];
		}
	
		return $result;
    	}

	function nombre_linea($codlinea) {
		global $sqlca;

		$sql = "SELECT 
				tab_elemento as codlinea,
				tab_descripcion as nomlinea
			FROM
				int_tabla_general 
			WHERE
				tab_tabla='20' 
				AND tab_elemento='$codlinea';";

		if ($sqlca->query($sql) < 0) 
			return false;	
	
		$a = $sqlca->fetchRow();
		$nomlinea = $a['nomlinea'];		
	
		return $nomlinea;
	}
}
