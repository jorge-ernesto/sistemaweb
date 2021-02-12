<?php
function diferencia_contometros($ini,$fin) {
//El caso standard, el inicial es menor que el final. Retorna la diferencia
	if ($ini<=$fin)
		return ($fin-$ini);

//Ahora, si el final es menor al inicial, hubo un reinicio de contometro.

	$limite = 1000000.00;	//NOTA: Para futura expansion, debería calcular el limite.

	$tramo1 = $limite-$ini;	//Desde el Inicla hasta el Limite
	$tramo2 = $fin;		//Desde el Cero hasta el final

	return $tramo1 + $tramo2;
}

class AvanzeContometrosModel extends Model {
	function obtenerReporte() {
		global $sqlca;

		$reporte = Array();

		$lxa = Array();

		$sql =	"	SELECT
					lado
				FROM
					pos_cmblados
				ORDER BY
					lado ASC;";

		if ($sqlca->query($sql)<0)
			return FALSE;

		$reporte = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$tlx = $sqlca->fetchRow();
			$lxa[] = $tlx[0];
		}

		$venta_conto = 0;
		$venta_ticket = 0;
		$diferencia = 0;

		foreach ($lxa as $lado) {
			$lda = Array();
			$lda['lado'] = $lado;
//			$listalados .= "'$lado',";

			$lado_conto_vol = 0;
			$lado_conto_sol = 0;
			$lado_ticket_vol = 0;
			$lado_ticket_sol = 0;
			$lado_diferencia_vol = 0;
			$lado_diferencia_sol = 0;

			$sql =	"SELECT
					ctc1.ch_codigocombustible,
					ctc1.ch_nombrebreve,
					ctc1.ch_codigocombex,
					ctc2.ch_codigocombustible,
					ctc2.ch_nombrebreve,
					ctc2.ch_codigocombex,
					ctc3.ch_codigocombustible,
					ctc3.ch_nombrebreve,
					ctc3.ch_codigocombex,
					ctc4.ch_codigocombustible,
					ctc4.ch_nombrebreve,
					ctc4.ch_codigocombex
				FROM
					pos_cmblados pcl
					LEFT JOIN comb_ta_combustibles ctc1 ON pcl.prod1 = ctc1.ch_codigocombex
					LEFT JOIN comb_ta_combustibles ctc2 ON pcl.prod2 = ctc2.ch_codigocombex
					LEFT JOIN comb_ta_combustibles ctc3 ON pcl.prod3 = ctc3.ch_codigocombex
					LEFT JOIN comb_ta_combustibles ctc4 ON pcl.prod4 = ctc4.ch_codigocombex
				WHERE
					pcl.lado = '$lado';";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$mangueras = Array();
			$row = $sqlca->fetchRow();
			for ($i=1;$i<5;$i++) {
				if ($row[(($i-1)*3)]===NULL)
					break;
				$manguera = Array();
				$manguera['codigocombex'] = $row[((($i-1)*3)+2)];
				$manguera['producto'] = $row[((($i-1)*3)+1)];
				$manguera['codigo'] = $row[(($i-1)*3)];

				$sql =	"	SELECT
							cnt_vol,
							cnt_val,
							cnt,
							fecha,
							precio,
							to_char(fecha,'DD/MM/YYYY HH24:MI')
						FROM
							pos_contometros_avance
						WHERE
							num_lado = $lado
							AND manguera = $i;";
				if ($sqlca->query($sql)<0)
					return FALSE;

				$r = $sqlca->fetchRow();
				if (!$r) {
					$manguera['precio'] = 0;
					$manguera['conto_final_vol'] = 0;
					$manguera['conto_final_sol'] = 0;
					$manguera['conto_inicial_vol'] = 0;
					$manguera['conto_inicial_sol'] = 0;
					$cierre = $apertura = "CONTOMETRO NO DISPONIBLE";
				} else {
					$manguera['precio'] = $r[4];
					$manguera['conto_final_vol'] = $r[0];
					$manguera['conto_final_sol'] = $r[1];

					if ($cierre=="")
							$cierre = $r[5];

					$sql =	"	SELECT
								min(cnt_vol),
								min(cnt_val)".(($apertura=="")?",
								to_char(min(fecha),'DD/MM/YYYY HH24:MI')":"")."
							FROM
								pos_contometros
							WHERE
								num_lado = $lado
								AND manguera = $i
								AND fecha < '{$r[3]}'
							GROUP BY
								dia,
								turno
							ORDER BY
								dia DESC,
								turno DESC
							LIMIT
								1;";

					if ($sqlca->query($sql)<0)
						return FALSE;

					$r = $sqlca->fetchRow();
					$manguera['conto_inicial_vol'] = $r[0];
					$manguera['conto_inicial_sol'] = $r[1];

					if ($apertura=="")
						$apertura = $r[2];
				}

				$manguera['conto_venta_vol'] = diferencia_contometros($manguera['conto_inicial_vol'],$manguera['conto_final_vol']);
				$manguera['conto_venta_sol'] = diferencia_contometros($manguera['conto_inicial_sol'],$manguera['conto_final_sol']);

				$lado_conto_vol += $manguera['conto_venta_vol'];
				$lado_conto_sol += $manguera['conto_venta_sol'];

				$filtrotrans = "codigo = '{$manguera['codigo']}'";

				$sql =	"	SELECT
							sum(cantidad),
							sum(importe)
						FROM
							pos_transtmp
						WHERE
							pump = '$lado'
							AND codigo = '{$manguera['codigo']}'
							AND (importe > 0 OR tm = 'A');";

				if ($sqlca->query($sql)<0)
					return FALSE;

				$r = $sqlca->fetchRow();
				$manguera['ticket_venta_vol'] = $r[0];
				$manguera['ticket_venta_sol'] = $r[1];

				$lado_ticket_vol += $manguera['ticket_venta_vol'];
				$lado_ticket_sol += $manguera['ticket_venta_sol'];

				$manguera['diferencia_vol'] = $manguera['ticket_venta_vol'] - $manguera['conto_venta_vol'];
				$manguera['diferencia_sol'] = $manguera['ticket_venta_sol'] - $manguera['conto_venta_sol'];

				$lado_diferencia_vol += $manguera['diferencia_vol'];
				$lado_diferencia_sol += $manguera['diferencia_sol'];

				$mangueras[$i] = $manguera;
				$manguera = NULL;
			}

			$lda['mangueras'] = $mangueras;
			$mangueras = NULL;

			$lda['conto_venta_vol'] = $lado_conto_vol;
			$lda['conto_venta_sol'] = $lado_conto_sol;
			$lda['ticket_venta_vol'] = $lado_ticket_vol;
			$lda['ticket_venta_sol'] = $lado_ticket_sol;
			$lda['diferencia_vol'] = $lado_diferencia_vol;
			$lda['diferencia_sol'] = $lado_diferencia_sol;

			$venta_conto += $lado_conto_sol;
			$venta_ticket += $lado_ticket_sol;
			$diferencia += $lado_diferencia_sol;

			$reporte[] = $lda;
		}

		$reporte['venta_conto'] = $venta_conto;
		$reporte['venta_ticket'] = $venta_ticket;
		$reporte['diferencia'] = $diferencia;

		return $reporte;
	}
}
