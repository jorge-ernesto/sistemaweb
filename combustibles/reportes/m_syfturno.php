	<?php

class SYFTurnoModel extends Model {

	function obtenerAlmacenes() {
		global $sqlca;

		$sql =	"	SELECT
					ch_almacen,
					ch_almacen || ' - ' || ch_nombre_almacen
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen='1'
					AND ch_sucursal=ch_almacen
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

	function obtenerTanques() {
		global $sqlca;

		$sql =	"	SELECT
					t.ch_tanque,
					t.ch_tanque || ' - ' || c.ch_nombrecombustible
				FROM
					comb_ta_tanques t
					LEFT OUTER JOIN comb_ta_combustibles c ON (t.ch_codigocombustible = c.ch_codigocombustible)
					LEFT JOIN inv_ta_almacenes a ON (t.ch_sucursal = a.ch_almacen)
				WHERE
					a.ch_clase_almacen='1'
				ORDER BY
					1;";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}

		return $result;
	}

	function obtenerReporte($fechaini,$fechafin,$tanque) {   // CAMBIO: RIGHT JOIN inv_movialma ma  POR "LEFT"
		global $sqlca;

		$sql =	"
		SELECT
			to_char(sct.fecha_stock_sistema,'DD/MM/YYYY')            AS dia,
			sct.turno_stock                                          AS turno,
			COALESCE(ma.mov_docurefe,'0')                            AS mov_docurefe,
			COALESCE(mac.numero_scop,'0')                            AS numero_scop,
			COALESCE(ma.mov_cantidad,0)                              AS mov_cantidad,
			round((sct.stock_fisico / t.nu_capacidad),2)             AS porcentaje, 
			sct.stock_fisico                                         AS varilla,
			t.ch_codigocombustible                                   AS codigo_combustible,
			'pos_trans' || to_char(sct.fecha_stock_sistema,'YYYYMM') AS tabla,
			sct.fecha_stock_sistema                                  AS fecha
		FROM
			comb_stock_combustible_turno sct			
			LEFT JOIN comb_ta_tanques t            ON (t.ch_tanque = sct.id_tanque)
			LEFT JOIN inv_movialma_complemento mac ON (DATE(sct.fecha_stock_sistema) = DATE(mac.mov_fecha) AND sct.turno_stock = mac.turno_recepcion)
			LEFT JOIN inv_movialma ma              ON (mac.tran_codigo = ma.tran_codigo AND mac.mov_numero = ma.mov_numero AND mac.mov_fecha::date = ma.mov_fecha::date AND t.ch_codigocombustible = ma.art_codigo)
		WHERE
			sct.fecha_stock_sistema BETWEEN to_date('$fechaini','DD/MM/YYYY') AND to_date('$fechafin','DD/MM/YYYY')
			AND sct.id_tanque = '{$tanque}'
		ORDER BY
			10 ASC,
			2 ASC;
		";
		echo $sql;
		if ($sqlca->query($sql) < 0)
			return false;

		$ss = SYFTurnoModel::obtenerSaldoDiaAnterior($tanque,$fechaini);
		echo "\nSaldo INI:".$ss;

		$result = Array();
		$rr = Array();
		// $rr[12] = 0;
		// $rr[13] = 0;
		$ac = Array();
		// $ac[5] = 0;
		// $ac[6] = 0;
		// $ac[7] = 0;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$r = $sqlca->fetchRow();

			$rr['dia']            = $r['dia'];							                                                     
			$rr['turno']          = $r['turno'];							                                                 
			$rr['saldo_anterior'] = $ss;
			
			$rr['compra']         = SYFTurnoModel::obtenerCompra($r['codigo_combustible'],$r['fecha'],$r['turno'],'21');
			$ac['compra']        += $rr['compra'];
			
			$rr['afericion']      = SYFTurnoModel::obtenerAfericion($r['codigo_combustible'],$r['fecha'],$r['turno'],$r['tabla']); 		
			$ac['afericion']     += $rr['afericion'];
			
			$rr['venta']          = SYFTurnoModel::obtenerVenta($r['codigo_combustible'],$r['fecha'],$r['turno']);		               
			$ac['venta']         += $rr['venta'];

			$rr['ingreso']        = SYFTurnoModel::obtenerIngreso($r['codigo_combustible'],$r['fecha'],$r['turno'],'27');
			$ac['ingreso']       += $rr['ingreso'];

			$rr['salida']         = SYFTurnoModel::obtenerSalida($r['codigo_combustible'],$r['fecha'],$r['turno'],'28');
			$ac['salida']        += $rr['salida'];

			$rr['parte']          = $rr['saldo_anterior'] + $rr['compra'] + $rr['afericion'] - $rr['venta'] + $rr['ingreso'] - $rr['salida'];
			$ac['parte']         += $rr['parte'];

			$rr['varilla']        = $r['varilla'];
			$ac['varilla']       += $rr['varilla'];

			$rr['diferencia_diaria']     = ($r['varilla'] - $rr['parte']);
			$rr['diferencia_acumulada'] += ($r['varilla'] - $rr['parte']);
			$ac['diferencia_diaria']    += $rr['diferencia_diaria'];

			$result[$i] = $rr;

			$ss = $r['varilla'];
		}
		$result[$i] = $ac;

		return $result;
	}

	function obtenerSaldoDiaAnterior($cod,$dia){	
		$dia = explode("/",$dia);
		$dia = $dia[2]."-".$dia[1]."-".$dia[0];
		$dia_anterior = date("Y-m-d",strtotime($dia."- 1 days")); 
		echo "\nFecha actual: ". $dia;
		echo "\nFecha dia anterior: ". $dia_anterior;

		global $sqlca;

		$sql =	"
			SELECT
				sct.stock_fisico
			FROM
				comb_stock_combustible_turno sct
			WHERE    
				sct.fecha_stock_sistema = '$dia_anterior'
				AND sct.id_tanque = '$cod'
			ORDER BY
				sct.fecha_stock_sistema DESC,
				sct.turno_stock DESC
			LIMIT
				1;";
		echo "\nSaldo dia anterior: ". $sql;

		if ($sqlca->query($sql,"SALDO") < 0)
			return NULL;

		for ($i = 0; $i < $sqlca->numrows("SALDO"); $i++) {
			$rr = $sqlca->fetchRow("SALDO");
			$r += $rr[0];
		}

		return $r;
	}

	function obtenerAfericion($cod,$dia,$turno,$postrans) {
		global $sqlca;

		$sql =	"
			SELECT
				COALESCE(sum(cantidad),0)
			FROM
				$postrans
			WHERE
				dia = '$dia'
				AND turno = '$turno'
				AND td = 'A'
				AND codigo = '$cod';";
		echo "\n\nAfericion: ". $sql;

		if ($sqlca->query($sql,"AFERICIONES") < 0)
			return NULL;	

		$r = $sqlca->fetchRow("AFERICIONES");
		return $r[0];
	}

	function obtenerVenta($cod,$dia,$turno) {
		global $sqlca;

		$sql =	"
			SELECT
				c1.cnt_vol - (
					SELECT
						c2.cnt_vol
					FROM
						pos_contometros c2
					WHERE
						c1.num_lado = c2.num_lado
						AND c1.manguera = c2.manguera
						AND c1.cnt > c2.cnt
					ORDER BY
						cnt DESC
					LIMIT
						1)
			FROM
				pos_contometros c1
				LEFT JOIN comb_ta_surtidores s ON (s.ch_numerolado::integer = c1.num_lado AND c1.manguera = s.nu_manguera)
			WHERE
				c1.dia = '$dia'
				AND c1.turno = '$turno'
				AND s.ch_codigocombustible = '$cod';";
		echo "\nContometros: ". $sql;

		if ($sqlca->query($sql,"VENTA") < 0)
			return NULL;

		$r = 0;		

		for ($i = 0; $i < $sqlca->numrows("VENTA"); $i++) {
			$rr = $sqlca->fetchRow("VENTA");
			$r += $rr[0];
		}

		return $r;
	}	

	function obtenerCompra($cod,$dia,$turno,$tran_codigo){
		global $sqlca;

		$sql = "
				SELECT     
					SUM(ma.mov_cantidad) as cantidad
				FROM       
					inv_movialma ma
					LEFT JOIN inv_movialma_complemento mac ON (ma.mov_numero          = mac.mov_numero 
															   AND DATE(ma.mov_fecha) = DATE(mac.mov_fecha) 
															   AND ma.tran_codigo     = mac.tran_codigo)
				WHERE      
					DATE(ma.mov_fecha)      = '$dia' 
					AND ma.art_codigo       = '$cod' 
					AND ma.tran_codigo      = '$tran_codigo'
					AND mac.turno_recepcion = '$turno';";
		echo "\nCompras: ". $sql;

		if ($sqlca->query($sql,"COMPRA") < 0)
			return NULL;

		$r = $sqlca->fetchRow("COMPRA");
		return $r[0];
	}

	function obtenerIngreso($cod,$dia,$turno,$tran_codigo){
		global $sqlca;

		$sql = "
				SELECT     
					SUM(ma.mov_cantidad) as cantidad
				FROM       
					inv_movialma ma
					LEFT JOIN inv_movialma_complemento mac ON (ma.mov_numero          = mac.mov_numero 
															   AND DATE(ma.mov_fecha) = DATE(mac.mov_fecha) 
															   AND ma.tran_codigo     = mac.tran_codigo)
				WHERE      
					DATE(ma.mov_fecha)      = '$dia' 
					AND ma.art_codigo       = '$cod' 
					AND ma.tran_codigo      = '$tran_codigo'
					AND mac.turno_recepcion = '$turno';";
		echo "\nCompras: ". $sql;

		if ($sqlca->query($sql,"INGRESO") < 0)
			return NULL;

		$r = $sqlca->fetchRow("INGRESO");
		return $r[0];
	}

	function obtenerSalida($cod,$dia,$turno,$tran_codigo){
		global $sqlca;

		$sql = "
				SELECT     
					SUM(ma.mov_cantidad) as cantidad
				FROM       
					inv_movialma ma
					LEFT JOIN inv_movialma_complemento mac ON (ma.mov_numero          = mac.mov_numero 
															   AND DATE(ma.mov_fecha) = DATE(mac.mov_fecha) 
															   AND ma.tran_codigo     = mac.tran_codigo)
				WHERE      
					DATE(ma.mov_fecha)      = '$dia' 
					AND ma.art_codigo       = '$cod' 
					AND ma.tran_codigo      = '$tran_codigo'
					AND mac.turno_recepcion = '$turno';";
		echo "\nCompras: ". $sql;

		if ($sqlca->query($sql,"SALIDA") < 0)
			return NULL;

		$r = $sqlca->fetchRow("SALIDA");
		return $r[0];
	}
}
