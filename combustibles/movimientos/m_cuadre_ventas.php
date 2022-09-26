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

class CuadreVentasModel extends Model {

	function obtenerReporte($dia1,$dia2,$turno1,$turno2,$trabajador) {
		global $sqlca;

		$dt = explode("/",$dia1);
		$dia1 = "{$dt[2]}-{$dt[1]}-{$dt[0]}";
		$dt = explode("/",$dia2);
		$dia2 = "{$dt[2]}-{$dt[1]}-{$dt[0]}";

		if ($trabajador=="")
			$trabajador = NULL;
		else
			$trabajador = addslashes($trabajador);

		//Obtiene dia y turnos de pos_aprosys
		$sql =	"	SELECT
					da_fecha,
					ch_posturno
				FROM
					pos_aprosys
				WHERE
					da_fecha BETWEEN '$dia1' AND '$dia2';";
		echo "<pre>Query obtiene dia y turnos de pos_aprosys:";
		echo $sql;
		echo "</pre>";

		if ($sqlca->query($sql)<0)
			return FALSE;

		$reporte = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++){
			$b[] = $sqlca->fetchRow();
		}
		echo "<script>console.log('Dia y turnos de pos_aprosys')</script>";
		echo "<script>console.log('" . json_encode($b) . "')</script>";

		foreach ($b as $a) {
			$dia = $a[0]; //Obtiene dia de pos_aprosys
			$turno = $a[1]; //Obtiene turno de pos_aprosys

			if ($dia==$dia1) { //Valida el dia en pos_aprosys
				if ($turno<$turno1) //Si el turno maximo de pos_aprosys < turno ingresado -> salta el foreach es decir no hace nada
					continue;
				$j = $turno1;
			}
			echo "<script>console.log('j')</script>";
			echo "<script>console.log('" . json_encode($j) . "')</script>";
			
			if ($dia==$dia2) { //Valida el dia en pos_aprosys
				if ($turno2>$turno) //Si el turno ingresado > turno maximo de pos_aprosys -> No hace nada
					;
				else
					$turno = $turno2+1;
			}
			echo "<script>console.log('turno')</script>";
			echo "<script>console.log('" . json_encode($turno) . "')</script>";
			for ($j=$j;$j<$turno;$j++) { //Solo obtiene informacion del turno seleccionado
				echo "<script>console.log('obtenerReporteTurno')</script>";
				echo "<script>console.log('" . json_encode( array($dia,$j,$trabajador) ) . "')</script>";
				$tabla = CuadreVentasModel::obtenerReporteTurno($dia,$j,$trabajador);
				if ($tabla===FALSE)
					return FALSE;
				$reporte[] = $tabla;
				$tabla = NULL;
			}
		}

		return $reporte;
	}

	function obtenerReporteTurno($dia,$turno,$trabajador) {
		global $sqlca;

		$dt = explode("-",$dia);
		$postrans = "pos_trans{$dt[0]}{$dt[1]}";
		$diabonito = "{$dt[2]}/{$dt[1]}/{$dt[0]}";

		$reporte = Array();
		$reporte['dia'] = $diabonito;
		$reporte['turno'] = $turno;
		$reporte['cuadres'] = Array();
		echo "<script>console.log('reporte')</script>";
		echo "<script>console.log('" . json_encode( $reporte ) . "')</script>";

		$apertura = "";
		$cierre = "";
		$turnoactual = FALSE;
		$tabcontometros = "pos_contometros";

		$sql =	"	SELECT
					da_fecha,
					ch_posturno
				FROM
					pos_aprosys
				WHERE
					ch_poscd='A';"; //Ultimo dia abierto
		echo "<pre>Query ultimo dia abierto:";
		echo $sql;
		echo "</pre>";

		if ($sqlca->query($sql)<0)
			return FALSE;

		$taa = $sqlca->fetchRow();

		if ($taa[0] == $dia && $taa[1] == $turno) { //Si el dia y turno ingresados estan abiertos
			$tabcontometros = "pos_contometros_avance";
			$turnoactual = TRUE;
			$postrans = "pos_transtmp";
		} else {
			$sql =	"	SELECT
						ch_posturno
					FROM
						pos_aprosys
					WHERE
						da_fecha = '$dia';";
			echo "<pre>Turno maximo de pos_aprosys:";
			echo $sql;
			echo "</pre>";

			if ($sqlca->query($sql) < 0)
				return FALSE;

			$txx = $sqlca->fetchRow();
			$ldt = $txx[0];
			settype($ldt,"integer"); //Turno maximo del dia
			settype($turno,"integer"); //Turno ingresado
			if ($ldt <= $turno)
				return FALSE;
		}

		$sql =	"	SELECT
					trim(hl.ch_lado),
					trim(hl.ch_codigo_trabajador),
					hl.ch_tipo,
					t.ch_nombre1 || ' ' || t.ch_nombre2 || ' ' || t.ch_apellido_paterno || ' ' || t.ch_apellido_materno || ' ',
					ch_sucursal
				FROM
					pos_historia_ladosxtrabajador hl
					LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
				WHERE
					hl.dt_dia = '$dia'
					AND hl.ch_sucursal = '".$_SESSION['almacen']."'
					AND hl.ch_posturno = $turno".(($trabajador===NULL)?"":"
					AND hl.ch_codigo_trabajador='$trabajador'")."
				ORDER BY
					hl.ch_codigo_trabajador,hl.ch_tipo,hl.ch_lado;";

		if ($sqlca->query($sql)<0)
			return FALSE;

		echo "<pre>Informacion de trabajadores:";
		echo $sql;
		echo "</pre>";

		$ladosxtrabajador = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			if (!isset($ladosxtrabajador[$a[1].$a[4]])) { //Solo ingresa la informacion sino fue ingresada antes
				$ladosxtrabajador[$a[1].$a[4]] = Array();
				$ladosxtrabajador[$a[1].$a[4]]['trabajador'] = $a[1];
				$ladosxtrabajador[$a[1].$a[4]]['tipo']       = $a[2];
				$ladosxtrabajador[$a[1].$a[4]]['nombre']     = $a[3];
				$ladosxtrabajador[$a[1].$a[4]]['sucursal']   = $a[4];
				$ladosxtrabajador[$a[1].$a[4]]['lados']      = Array();
			}
			if (strlen($a[0]) == 2 && $a[2] != 'M') //Combustible
				$ladosxtrabajador[$a[1].$a[4]]['lados'][] = $a[0];
			else //Market
				$ladosxtrabajador[$a[1].$a[4]]['pos'][] = $a[0];
		}
		echo "<script>console.log('ladosxtrabajador')</script>";
		echo "<script>console.log('" . json_encode( $ladosxtrabajador ) . "')</script>";

		if ($i == 0)
			return FALSE;

		foreach ($ladosxtrabajador as $lxa) { //Recorremos trabajadores
			$cuadre = Array();
			$cuadre['trabajador'] = $lxa['trabajador'];
			$cuadre['nombre'] = $lxa['nombre'];
			$cuadre['sucursal'] = $lxa['sucursal'];

			$venta_conto = 0;
			$venta_ticket = 0;
			$diferencia = 0;
			$listalados = "";
			$lados = Array();

			foreach ($lxa['lados'] as $lado) { //Solo recorre lados //Recorremos lados
				$lda = Array();
				$lda['lado'] = $lado;
				$listalados .= "'$lado',";

				$lado_conto_vol = 0;
				$lado_conto_sol = 0;
				$lado_ticket_vol = 0;
				$lado_ticket_sol = 0;
				$lado_diferencia_vol = 0;
				$lado_diferencia_sol = 0;

				$sql =	"
					SELECT
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
				$row = $sqlca->fetchRow(); //Solo obtiene una fila
				echo "<script>console.log('row - ".$lado."')</script>";
				echo "<script>console.log('" . json_encode( $row ) . "')</script>";
				for ($i=1;$i<5;$i++) {
					// if ($row[(($i-1)*3)]===NULL)
					// 	break;
					$manguera = Array();
					if ($row[(($i-1)*3)] !== NULL) {
						$manguera['codigocombex'] = $row[((($i-1)*3)+2)]; //Esto es para capturar ch_codigocombex
						$manguera['producto'] = $row[((($i-1)*3)+1)]; //ch_nombrebreve
						$manguera['codigo'] = $row[(($i-1)*3)]; //ch_codigocombutible
					} else {
						$manguera['codigocombex'] = "??";
						$manguera['producto'] = "??";
						$manguera['codigo'] = "11620306";
					}
					$sql =	"	SELECT
								cnt_vol,
								cnt_val,
								cnt,
								fecha,
								precio".(($cierre=="")?",
								to_char(fecha,'DD/MM/YYYY HH24:MI')":"")."
							FROM
								$tabcontometros
							WHERE
								num_lado = $lado
								AND manguera = $i".(($turnoactual==TRUE)?" ":"
								AND dia = '$dia'
								AND turno = '$turno'").";";
					//$turnoactual es variable que se identifica para cuando se hace select a pos_contometros_avance
					echo "<pre>Query pos_contometros o pos_contometros_avance inicial:";
					echo $sql;
					echo "</pre>";					
					if ($sqlca->query($sql)<0)
						return FALSE;

					$r = $sqlca->fetchRow();
					echo "<script>console.log('row - Query pos_contometros o pos_contometros_avance inicial')</script>";
					echo "<script>console.log('" . json_encode( $r ) . "')</script>";
					if (!$r) {
						if ($manguera['producto'] == "??")
							continue;
						$manguera['precio'] = 0;
						$manguera['conto_final_vol'] = 0;
						$manguera['conto_final_sol'] = 0;
						$manguera['conto_inicial_vol'] = 0;
						$manguera['conto_inicial_sol'] = 0;
						$cierre = $apertura = "CONTOMETROs NO DISPONIBLE";
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
						echo "<pre>Query pos_contometros o pos_contometros_avance final:";
						echo $sql;
						echo "</pre>";

									// AND cnt < {$r[2]}
						if ($sqlca->query($sql)<0)
							return FALSE;

						$r = $sqlca->fetchRow();
						echo "<script>console.log('row - Query pos_contometros o pos_contometros_avance final')</script>";
						echo "<script>console.log('" . json_encode( $r ) . "')</script>";
						$manguera['conto_inicial_vol'] = $r[0];
						$manguera['conto_inicial_sol'] = $r[1];

						if ($apertura=="")
							$apertura = $r[2];
					}

					$manguera['conto_venta_vol'] = diferencia_contometros($manguera['conto_inicial_vol'],$manguera['conto_final_vol']);
					$manguera['conto_venta_sol'] = diferencia_contometros($manguera['conto_inicial_sol'],$manguera['conto_final_sol']);
					// $manguera['conto_venta_vol'] = $manguera['conto_final_vol'] - $manguera['conto_inicial_vol'];
					// $manguera['conto_venta_sol'] = $manguera['conto_final_sol'] - $manguera['conto_inicial_sol'];

					$lado_conto_vol += $manguera['conto_venta_vol'];
					$lado_conto_sol += $manguera['conto_venta_sol'];

					$filtrotrans = "codigo = '{$manguera['codigo']}'"; //Esto no sirve al final se reemplaza

					$sql =	"	
							SELECT
								--round(sum(cantidad * (importe / abs(importe))),2),
								ROUND(SUM(cantidad),2),
								sum(importe)
							FROM
								$postrans
							WHERE
								dia = '$dia'
								AND turno = '$turno'
								AND pump = '$lado'
								AND codigo = '{$manguera['codigo']}'

						";
								// --AND (importe > 0 OR tm = 'A');";

					if ($sqlca->query($sql)<0)
						return FALSE;

					$r = $sqlca->fetchRow();
					$manguera['ticket_venta_vol'] = $r[0];
					$manguera['ticket_venta_sol'] = $r[1];

					$lado_ticket_vol += $manguera['ticket_venta_vol'];
					$lado_ticket_sol += $manguera['ticket_venta_sol'];

					if ($turnoactual == TRUE) {
						$manguera['conto_venta_vol'] = $manguera['ticket_venta_vol'];
						$manguera['conto_venta_sol'] = $manguera['ticket_venta_sol'];
					}

					$manguera['diferencia_vol'] = $manguera['ticket_venta_vol'] - $manguera['conto_venta_vol'];
					$manguera['diferencia_sol'] = $manguera['ticket_venta_sol'] - $manguera['conto_venta_sol'];

					$lado_diferencia_vol += $manguera['diferencia_vol'];
					$lado_diferencia_sol += $manguera['diferencia_sol'];

					$mangueras[$i] = $manguera;
					$manguera = NULL;
				}
				echo "filtrotrans: " . $filtrotrans; //Esto no sirve al final se reemplaza

				$lda['mangueras'] = $mangueras;
				$mangueras = NULL;

				$lda['conto_venta_vol'] = $lado_conto_vol;
				$lda['conto_venta_sol'] = $lado_conto_sol;
				$lda['ticket_venta_vol'] = $lado_ticket_vol;
				$lda['ticket_venta_sol'] = $lado_ticket_sol;
				$lda['diferencia_vol'] = $lado_diferencia_vol;
				$lda['diferencia_sol'] = $lado_diferencia_sol;

				if ($turnoactual == TRUE) {
					$lda['conto_venta_vol'] = $lado_ticket_vol;
					$lda['conto_venta_sol'] = $lado_ticket_sol;
					$lda['diferencia_vol'] = 0;
					$lda['diferencia_sol'] = 0;
					$venta_conto += $lado_ticket_sol;
					$venta_ticket += $lado_ticket_sol;
					$diferencia = 0;
				} else {
					$venta_conto += $lado_conto_sol;
					$venta_ticket += $lado_ticket_sol;
					$diferencia += $lado_diferencia_sol;
				}

				$lados[] = $lda;
			}

			// Validando cuadre venta
			$valida = CuadreVentasModel::validaCuadreTicket($dia, $turno);
			if($valida==1) {				
				$venta_exigible = $venta_ticket;
			} else {
				$venta_exigible = $venta_conto;
			}

			//$venta_exigible = $venta_conto;
			

			$cuadre['lados'] = $lados;
			$lados = NULL;

			$venta_market = 0;
			$listapos = "";
			$pox = Array();

			foreach ($lxa['pos'] as $pos) { //Recorremos pos
				$pos = trim($pos);
				$caja = Array();
				$caja['pos'] = $pos;

				$listapos .= "'$pos',";

				$sql =	"
						SELECT
							sum(cantidad),
							sum(importe)-sum(COALESCE(km,0))
						FROM
							$postrans
						WHERE
							dia = '$dia'
							AND turno = '$turno'
							AND caja = '$pos'
							AND tipo = 'M'
							AND (tm = 'V' OR tm = 'D' AND fpago='2')
							AND es = '".$_SESSION['almacen']."';
					";
				
				if ($sqlca->query($sql)<0)
					return FALSE;

				$r = $sqlca->fetchRow();
				$caja['ticket_venta_vol'] = $r[0];
				$caja['ticket_venta_sol'] = $r[1];

				$venta_market += $r[1];
				$venta_exigible += $r[1];

				$pox[] = $caja;
			}


			$cuadre['pos'] = $pox;
			$pox = NULL;

			$cuadre['venta_conto'] = $venta_conto;
			$cuadre['venta_exigible'] = $venta_exigible;
			$cuadre['venta_ticket'] = $venta_ticket;
			$cuadre['venta_market'] = $venta_market;
			$cuadre['diferencia'] = $diferencia;

			/*Configuracion de where*/
			$filtrolados = "";
			$filtroladosvt = "";
			$filtropos = "";
			$filtroposvt = "";

			if ($listalados != "") {
				$listalados = substr($listalados,0,-1);
				$filtrolados = " t.pump IN ($listalados) ";
				$filtroladosvt = " v.ch_lado IN ($listalados) ";
			}
			if ($listapos != "") {
				$listapos = substr($listapos,0,-1);
				$filtropos = " t.caja IN ($listapos) AND t.tipo='M' ";
				$filtroposvt = " v.ch_caja IN ($listapos) ";
			}

			if ($filtrolados != "" && $filtropos != "") {
				$filtrotrans = " (({$filtrolados}) OR ({$filtropos})) "; //$filtrotrans
				$filtrotransvt = " (({$filtroladosvt}) OR ({$filtroposvt} AND (v.ch_lado IS NULL OR v.ch_lado = ''))) ";
				$filtrotransc = $filtrolados;
			} else if ($filtrolados != "") {
				$filtrotrans = $filtrolados; //$filtrotrans
				$filtrotransvt = $filtroladosvt;
				$filtrotransc = $filtrolados;
			} else if ($filtropos != "") {
				$filtrotrans = $filtropos; //$filtrotrans
				$filtrotransvt = "{$filtroposvt} AND (v.ch_lado IS NULL OR v.ch_lado = '')";
			}
			/*Cerrar Configuracion de where*/

			$sql =	"SELECT
					ch_numero_correl,
					ch_moneda,
					CASE
						WHEN ch_moneda = '01' THEN 1
						ELSE nu_tipo_cambio
					END,
					nu_importe,
					CASE
						WHEN ch_moneda = '01' THEN nu_importe
						ELSE nu_importe * nu_tipo_cambio
					END,
					to_char(dt_fecha,'HH24:MI:SS')
				FROM
					pos_depositos_diarios
				WHERE
					dt_dia = '$dia'
					AND ch_posturno = $turno
					AND ch_codigo_trabajador = '{$lxa['trabajador']}'
					AND ch_valida = 'S'
				ORDER BY
					ch_numero_correl;";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_depositos = 0;
			$depositos = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$deposito = Array();
				$deposito['correlativo'] = $a[0];
				$deposito['moneda'] = $a[1];
				$deposito['tc'] = $a[2];
				$deposito['importe'] = $a[3];
				$deposito['importe_soles'] = $a[4];
				$deposito['hora'] = $a[5];
				$total_depositos += $a[4];
				$depositos[] = $deposito;
			}
			$depositos['total'] = $total_depositos;
			$cuadre['depositos'] = $depositos;
			$depositos = NULL;

			$fpago = Array(
				"1"	=>	"EFECTIVO",
				"2"	=>	"TARJETA",
				"3"	=>	"OTROS"
			);

			$sql =	"
			SELECT
				v.ch_documento,
				c.cli_codigo,
				c.cli_razsocial,
				v.nu_importe
			FROM
				val_ta_cabecera v
				LEFT JOIN int_clientes c ON v.ch_cliente = c.cli_codigo
			WHERE
				c.cli_ndespacho_efectivo = 0
				AND v.dt_fecha = '$dia'
				AND v.ch_turno = '$turno'
				AND v.ch_sucursal = '".$_SESSION['almacen']."'
				AND $filtrotransvt;
			";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_notas = 0;
			$notas = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$nota = Array();
				$nota['trans']   = $a[0];
				$nota['cliente'] = $a[1];
				$nota['nombre']  = $a[2];
				$nota['importe'] = $a[3];
				$total_notas += $a[3];
				$notas[] = $nota;
			}
			$notas['total'] = $total_notas;
			$cuadre['nd'] = $notas;
			$notas = NULL;

			$sql =	"	SELECT
						v.ch_documento,
						c.cli_codigo,
						c.cli_razsocial,
						v.nu_importe
					FROM
						val_ta_cabecera v
						LEFT JOIN int_clientes c ON v.ch_cliente = c.cli_codigo
					WHERE
						c.cli_ndespacho_efectivo = 1
						AND v.dt_fecha = '$dia'
						AND v.ch_turno = '$turno'
						AND v.ch_sucursal = '".$_SESSION['almacen']."'
						AND $filtrotransvt;";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_notase = 0;
			$notase = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$notae = Array();
				$notae['trans'] = $a[0];
				$notae['cliente'] = $a[1];
				$notae['nombre'] = $a[2];
				$notae['importe'] = $a[3];
				$total_notase += $a[3];
				$notase[] = $notae;
			}
			$notase['total'] = $total_notase;
			$cuadre['nde'] = $notase;
			$notase = NULL;

			//Query Tarjetas de Credito
			$sql =	"
					SELECT
						t.trans,
						max(t.text1),
						max(g.tab_desc_breve),
						sum(t.importe)-first(COALESCE(t.km,0)),
						to_char(first(t.fecha),'HH24:MI:SS')
					FROM
						$postrans t
						LEFT JOIN int_tabla_general g ON (g.tab_tabla = '95' AND g.tab_elemento = '00000'||t.at)
					WHERE
						t.td IN ('B','F')
						AND t.fpago = '2'
						AND t.dia = '$dia'
						AND t.turno = '$turno'
						AND $filtrotrans
					GROUP BY
						t.fecha,
						t.trans
					ORDER BY
						t.fecha;
			";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_tarjetas = 0;
			$tarjetas = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$tarjeta = Array();
				$tarjeta['trans'] = $a[0];
				$tarjeta['tarjeta'] = $a[1];
				$tarjeta['tipo'] = $a[2];
				$tarjeta['importe'] = $a[3];
				$tarjeta['hora'] = $a[4];
				$total_tarjetas += $a[3];
				$tarjetas[] = $tarjeta;
			}
			$tarjetas['total'] = $total_tarjetas;
			$cuadre['tc'] = $tarjetas;
			$tarjetas = NULL;

			//OPENSOFT-98: Redondeo de documentos en efectivo en reportes de liquidación //
			$sql =	"
					SELECT 
						sum(x) 
					FROM 
						(SELECT 
							round((((first(t.soles_km)*100)%10)/100),2) AS x 
						FROM 
							$postrans t
						WHERE 
							t.td IN ('B','F')
							AND t.fpago = '1' 
							AND t.dia = '$dia'
							AND t.turno = '$turno'
							AND $filtrotrans --Esto me interesa
						GROUP BY 
							t.caja,t.trans) x;
			";
			echo "<pre>Redonde de documentos en efectivo en reportes de liquidacion:";
			echo $sql;
			echo "</pre>";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_redondeo_efe = 0;
			$redondeo_efe = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a                    = $sqlca->fetchRow();
				$redondeo             = Array();
				$redondeo['importe']  = $a[0];
				$total_redondeo_efe  += $a[0];
				$redondeo_efe[]       = $redondeo;
			}
			$redondeo_efe['total']       = $total_redondeo_efe;
			$cuadre['redondeo_efectivo'] = $redondeo_efe;
			$redondeo_efe                = NULL;
			//Cerrar OPENSOFT-98: Redondeo de documentos en efectivo en reportes de liquidación

			$sql =	"	SELECT
						t.trans,
						CASE
							WHEN t.td = 'N' AND c.cli_ndespacho_efectivo = 1 THEN '1'
							WHEN t.td = 'N' AND c.cli_ndespacho_efectivo = 0 THEN '2'
							ELSE t.fpago
						END AS fpago,
						t.importe
					FROM
						$postrans t
						LEFT JOIN pos_fptshe1 f ON (t.tarjeta = f.numtar)
						LEFT JOIN int_clientes c ON trim(f.codcli) = trim(c.cli_codigo)
					WHERE
						tm = 'D'
						AND t.td IN ('B','F','N')
						AND t.dia = '$dia'
						AND t.turno = '$turno'
						AND $filtrotrans";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_devoluciones = 0;
			$total_devoluciones_efectivo = 0;
			$devoluciones = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$devolucion = Array();
				$devolucion['trans'] = $a[0];
				$devolucion['fpago'] = $fpago[$a[1]];
				$devolucion['importe'] = $a[2];
				$total_devoluciones += $a[2];
				if ($a[1]=='1')
					$total_devoluciones_efectivo += $a[2];
				$devoluciones[] = $devolucion;
			}
			$devoluciones['total'] = $total_devoluciones;
			$devoluciones['total_efectivo'] = $total_devoluciones_efectivo;
			$cuadre['devol'] = $devoluciones;
			$devoluciones = NULL;

			$sql =	"	SELECT
						t.trans,
						t.importe
					FROM
						$postrans t
					WHERE
						tm = 'A'
						AND t.dia = '$dia'
						AND t.turno = '$turno'
						AND $filtrotrans;";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_anulaciones = 0;
			$anulaciones = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$anulacion = Array();
				$anulacion['trans'] = $a[0];
				$anulacion['importe'] = $a[1];
				$total_anulaciones += $a[1];
				$anulaciones[] = $anulacion;
			}
			$anulaciones['total'] = $total_anulaciones;
			$cuadre['anul'] = $anulaciones;
			$anulaciones = NULL;

			$sql =  "       SELECT
						t.trans,
						sum(t.importe),
						to_char(first(t.fecha),'HH24:MI:SS')
					FROM
						$postrans t
					WHERE
						t.td IN ('B','F')
						AND t.fpago = '3'
						AND t.dia = '$dia'
						AND t.turno = '$turno'
						AND $filtrotrans
					GROUP BY
						t.trans;";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_transgrat = 0;
			$transgrat = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$trgrat = Array();
				$trgrat['trans'] = $a[0];
				$trgrat['importe'] = $a[1];
				$trgrat['hora'] = $a[2];
				$total_transgrat += $a[1];
				$transgrat[] = $trgrat;
			}
			$transgrat['total'] = $total_transgrat;
			$cuadre['tg'] = $transgrat;
			$transgrat = NULL;

			//Query Descuentos
			if ($listalados != "") {
				$sql =	"	
					SELECT
						t.trans,
						a.art_descbreve,
						t.precio,
						t.importe,
						t.fpago,
						CASE
							WHEN r.ruc IS NULL THEN ' '
							ELSE r.razsocial
						END
					FROM
						$postrans t
						LEFT JOIN int_articulos a ON t.codigo = a.art_codigo
						LEFT JOIN ruc r ON t.ruc = trim(r.ruc)
					WHERE
						--tm = 'V'
						--AND (importe < 0 or (importe>0 and grupo='D'))
						grupo 		= 'D'
						AND t.dia 	= '$dia'
						AND t.turno = '$turno'
						AND $filtrotransc;
				";

				if ($sqlca->query($sql)<0)
					return FALSE;

				$total_descuentos = 0;
				$total_descuentos_efectivo = 0;
				$descuentos = Array();
				for ($i=0;$i<$sqlca->numrows();$i++) {
					$a = $sqlca->fetchRow();
					$descuento = Array();
					$descuento['trans'] = $a[0];
					$descuento['descripcion'] = trim($a[5] . " " . $a[1] . " " . $a[2]);
					$descuento['fpago'] = $fpago[$a[4]];
					$descuento['importe'] = $a[3];
					$total_descuentos += $a[3];
					$descuentos[] = $descuento;
				}
				$descuentos['total'] = $total_descuentos;
				$descuentos['total_efectivo'] = $total_descuentos;
				$cuadre['desc'] = $descuentos;
				$descuentos = NULL;

				$sql =	"	SELECT
							t.trans,
							a.art_descbreve,
							t.veloc,
							t.lineas,
							t.importe
						FROM
							pos_ta_afericiones t
							LEFT JOIN int_articulos a ON t.codigo = a.art_codigo
						WHERE
							t.dia = '$dia'
							AND t.turno = '$turno'
							AND $filtrotransc;";

				if ($sqlca->query($sql)<0)
					return FALSE;

				$total_afericiones = 0;
				$afericiones = Array();
				for ($i=0;$i<$sqlca->numrows();$i++) {
					$a = $sqlca->fetchRow();
					$afericion = Array();
					$afericion['trans'] = $a[0];
					$afericion['producto'] = $a[1];
					$afericion['detalle'] = $a[2] . " " . $a[3];
					$afericion['importe'] = $a[4];
					$total_afericiones += $a[4];
					$afericiones[] = $afericion;
				}
				$afericiones['total'] = $total_afericiones;
				$cuadre['afer'] = $afericiones;
				$afericiones = NULL;
			} else {
				$total_descuentos = $total_descuentos_efectivo = $total_afericiones = 0;
			}

			/*if ($cuadre['tipo']=="C")
				$cuadre['fs'] = ($total_depositos - $venta_conto) + ($total_notas + $total_tarjetas + ($total_descuentos_efectivo * -1) + ($total_devoluciones_efectivo * -1) + $total_afericiones);
			else
				$cuadre['fs'] = ($total_depositos - $venta_ticket) + ($total_notas + $total_tarjetas + ($total_descuentos_efectivo * -1) + ($total_devoluciones_efectivo * -1));
			*/

			$cuadre['fs'] = ($total_depositos - $venta_exigible) + ($total_notas + $total_tarjetas + $total_redondeo_efe + ($total_descuentos * -1) + ($total_devoluciones_efectivo * -1) + $total_afericiones + $total_transgrat);
			// $sob_faltante_trabajador = CuadreVentasModel::obtenerReporteTurnoConsolidacion($dia,$turno,$cuadre['trabajador'],$cuadre['sucursal']);
			// $cuadre['sob_fal'][$cuadre['trabajador']] = $sob_faltante_trabajador[$cuadre['trabajador']]['cuadres']['fs'];
			// $cuadre['sob_fal'][$cuadre['trabajador']] = $sob_faltante_trabajador;			

			$reporte['cuadres'][] = $cuadre;
			$cuadre = NULL;
		}

		$ladosxtrabajador = NULL;

		$reporte['apertura'] = $apertura;
		$reporte['cierre'] = $cierre;

		//OBTENER VENTA MARKET POR DIA, TURNO Y LINEA		
		$caja_linea = array();
		$sql = "
			SELECT
				art.art_linea AS linea,
				max(tab.tab_descripcion) AS descripcion_linea,
				sum(pt.cantidad),
				sum(pt.importe)-sum(COALESCE(pt.km,0))
			FROM
				$postrans pt
				RIGHT JOIN int_articulos art ON (art.art_codigo=pt.codigo)
				LEFT JOIN int_tabla_general tab ON (tab.tab_tabla='20' AND tab.tab_elemento=art.art_linea)
			WHERE
				pt.dia = '$dia'
				AND pt.turno = '$turno'					
				AND pt.tipo = 'M'
				AND (pt.tm = 'V' OR pt.tm = 'D' AND pt.fpago='2')
				AND pt.es = '".$_SESSION['almacen']."'
			GROUP BY
				art.art_linea
			ORDER BY
				art.art_linea;
		";
		if ($sqlca->query($sql)<0)
			return FALSE;

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$caja_linea[] = array(
				'linea'             => $a[0],
				'descripcion_linea' => $a[1],
				'cantidad'          => $a[2],
				'importe'           => $a[3]
			);
		}			
		$reporte['resumen_market_linea'] = $caja_linea;
		//CERRAR OBTENER VENTA MARKET POR DIA, TURNO Y LINEA							

		return $reporte;
	}



	function obtenerReporteTurnoConsolidacion($dia,$turno,$trabajador,$almacen) { //Sobrantes y faltantes
		global $sqlca;

		$dt = explode("-",$dia);
		$postrans = "pos_trans{$dt[0]}{$dt[1]}";
		$diabonito = "{$dt[2]}/{$dt[1]}/{$dt[0]}";

		$reporte = Array();
		$reporte['dia'] = $diabonito;
		$reporte['turno'] = $turno;
		$reporte['cuadres'] = Array();

		$apertura = "";
		$cierre = "";
		$turnoactual = FALSE;
		$tabcontometros = "pos_contometros";

		$sql =	"	SELECT
					da_fecha,
					ch_posturno
				FROM
					pos_aprosys
				WHERE
					ch_poscd='A';";

		if ($sqlca->query($sql)<0)
			return FALSE;

		$taa = $sqlca->fetchRow();

		if ($taa[0] == $dia && $taa[1] == $turno) {
			$tabcontometros = "pos_contometros_avance";
			$turnoactual = TRUE;
			$postrans = "pos_transtmp";
		} else {
			$sql =	"	SELECT
						ch_posturno
					FROM
						pos_aprosys
					WHERE
						da_fecha = '$dia';";

			if ($sqlca->query($sql) < 0)
				return FALSE;

			$txx = $sqlca->fetchRow();
			$ldt = $txx[0];
			settype($ldt,"integer");
			settype($turno,"integer");
			if ($ldt <= $turno)
				return FALSE;
		}

		$sql =	"	SELECT
					trim(hl.ch_lado),
					trim(hl.ch_codigo_trabajador),
					hl.ch_tipo,
					t.ch_nombre1 || ' ' || t.ch_nombre2 || ' ' || t.ch_apellido_paterno || ' ' || t.ch_apellido_materno || ' ',
					ch_sucursal
				FROM
					pos_historia_ladosxtrabajador hl
					LEFT JOIN pla_ta_trabajadores t ON hl.ch_codigo_trabajador = t.ch_codigo_trabajador
				WHERE
					hl.dt_dia = '$dia'
					AND hl.ch_sucursal = '$almacen'
					AND hl.ch_posturno = $turno".(($trabajador===NULL)?"":"
					AND hl.ch_codigo_trabajador='$trabajador'")."
				ORDER BY
					hl.ch_codigo_trabajador,hl.ch_tipo,hl.ch_lado;";

		if ($sqlca->query($sql)<0)
			return FALSE;

		$ladosxtrabajador = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			if (!isset($ladosxtrabajador[$a[1].$a[4]])) {
				$ladosxtrabajador[$a[1].$a[4]] = Array();
				$ladosxtrabajador[$a[1].$a[4]]['trabajador'] = $a[1];
				$ladosxtrabajador[$a[1].$a[4]]['tipo']       = $a[2];
				$ladosxtrabajador[$a[1].$a[4]]['nombre']     = $a[3];
				$ladosxtrabajador[$a[1].$a[4]]['sucursal']   = $a[4];
				$ladosxtrabajador[$a[1].$a[4]]['lados']      = Array();
			}
			if (strlen($a[0]) == 2 && $a[2] != 'M')
				$ladosxtrabajador[$a[1].$a[4]]['lados'][] = $a[0];
			else
				$ladosxtrabajador[$a[1].$a[4]]['pos'][] = $a[0];
		}

		if ($i == 0)
			return FALSE;

		foreach ($ladosxtrabajador as $lxa) {
			$cuadre = Array();
			$cuadre['trabajador'] = $lxa['trabajador'];
			$cuadre['nombre'] = $lxa['nombre'];
			$cuadre['sucursal'] = $lxa['sucursal'];

			$venta_conto = 0;
			$venta_ticket = 0;
			$diferencia = 0;
			$listalados = "";
			$lados = Array();

			foreach ($lxa['lados'] as $lado) {
				$lda = Array();
				$lda['lado'] = $lado;
				$listalados .= "'$lado',";

				$lado_conto_vol = 0;
				$lado_conto_sol = 0;
				$lado_ticket_vol = 0;
				$lado_ticket_sol = 0;
				$lado_diferencia_vol = 0;
				$lado_diferencia_sol = 0;

				$sql =	"
					SELECT
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
								precio".(($cierre=="")?",
								to_char(fecha,'DD/MM/YYYY HH24:MI')":"")."
							FROM
								$tabcontometros
							WHERE
								num_lado = $lado
								AND manguera = $i".(($turnoactual==TRUE)?" ":"
								AND dia = '$dia'
								AND turno = '$turno'").";";
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

//									AND cnt < {$r[2]}
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
//					$manguera['conto_venta_vol'] = $manguera['conto_final_vol'] - $manguera['conto_inicial_vol'];
//					$manguera['conto_venta_sol'] = $manguera['conto_final_sol'] - $manguera['conto_inicial_sol'];

					$lado_conto_vol += $manguera['conto_venta_vol'];
					$lado_conto_sol += $manguera['conto_venta_sol'];

					$filtrotrans = "codigo = '{$manguera['codigo']}'";

					$sql =	"	
							SELECT
								--round(sum(cantidad * (importe / abs(importe))),2),
								ROUND(SUM(cantidad),2),
								ROUND(SUM(importe),2)
							FROM
								$postrans
							WHERE
								dia = '$dia'
								AND turno = '$turno'
								AND pump = '$lado'
								AND codigo = '{$manguera['codigo']}'
								AND importe > 0
								AND tm = 'V'
						";
//								--AND (importe > 0 OR tm = 'A');";

					if ($sqlca->query($sql)<0)
						return FALSE;

					$r = $sqlca->fetchRow();
					$manguera['ticket_venta_vol'] = $r[0];
					$manguera['ticket_venta_sol'] = $r[1];

					$lado_ticket_vol += $manguera['ticket_venta_vol'];
					$lado_ticket_sol += $manguera['ticket_venta_sol'];

					if ($turnoactual == TRUE) {
						$manguera['conto_venta_vol'] = $manguera['ticket_venta_vol'];
						$manguera['conto_venta_sol'] = $manguera['ticket_venta_sol'];
					}

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

				if ($turnoactual == TRUE) {
					$lda['conto_venta_vol'] = $lado_ticket_vol;
					$lda['conto_venta_sol'] = $lado_ticket_sol;
					$lda['diferencia_vol'] = 0;
					$lda['diferencia_sol'] = 0;
					$venta_conto += $lado_ticket_sol;
					$venta_ticket += $lado_ticket_sol;
					$diferencia = 0;
				} else {
					$venta_conto += $lado_conto_sol;
					$venta_ticket += $lado_ticket_sol;
					$diferencia += $lado_diferencia_sol;
				}

				$lados[] = $lda;
			}

			// Validando cuadre venta
			$valida = CuadreVentasModel::validaCuadreTicket($dia, $turno);
			if($valida==1) {				
				$venta_exigible = $venta_ticket;
			} else {
				$venta_exigible = $venta_conto;
			}

			//$venta_exigible = $venta_conto;
			

			$cuadre['lados'] = $lados;
			$lados = NULL;

			$venta_market = 0;
			$listapos = "";
			$pox = Array();

			foreach ($lxa['pos'] as $pos) {
				$pos = trim($pos);
				$caja = Array();
				$caja['pos'] = $pos;

				$listapos .= "'$pos',";

				$sql =	"
						SELECT
							sum(cantidad),
							sum(importe)-sum(COALESCE(km,0))
						FROM
							$postrans
						WHERE
							dia = '$dia'
							AND turno = '$turno'
							AND caja = '$pos'
							AND tipo = 'M'
							AND (tm = 'V' OR tm = 'D' AND fpago='2')
							AND es = '$almacen';
					";
				
				if ($sqlca->query($sql)<0)
					return FALSE;

				$r = $sqlca->fetchRow();
				$caja['ticket_venta_vol'] = $r[0];
				$caja['ticket_venta_sol'] = $r[1];

				$venta_market += $r[1];
				$venta_exigible += $r[1];

				$pox[] = $caja;
			}


			$cuadre['pos'] = $pox;
			$pox = NULL;

			$cuadre['venta_conto'] = $venta_conto;
			$cuadre['venta_exigible'] = $venta_exigible;
			$cuadre['venta_ticket'] = $venta_ticket;
			$cuadre['venta_market'] = $venta_market;
			$cuadre['diferencia'] = $diferencia;

			$filtrolados = "";
			$filtroladosvt = "";
			$filtropos = "";
			$filtroposvt = "";

			if ($listalados != "") {
				$listalados = substr($listalados,0,-1);
				$filtrolados = " t.pump IN ($listalados) ";
				$filtroladosvt = " v.ch_lado IN ($listalados) ";
			}
			if ($listapos != "") {
				$listapos = substr($listapos,0,-1);
				$filtropos = " t.caja IN ($listapos) AND t.tipo='M' ";
				$filtroposvt = " v.ch_caja IN ($listapos) ";
			}

			if ($filtrolados != "" && $filtropos != "") {
				$filtrotrans = " (({$filtrolados}) OR ({$filtropos})) ";
				$filtrotransvt = " (({$filtroladosvt}) OR ({$filtroposvt} AND (v.ch_lado IS NULL OR v.ch_lado = ''))) ";
				$filtrotransc = $filtrolados;
			} else if ($filtrolados != "") {
				$filtrotrans = $filtrolados;
				$filtrotransvt = $filtroladosvt;
				$filtrotransc = $filtrolados;
			} else if ($filtropos != "") {
				$filtrotrans = $filtropos;
				$filtrotransvt = "{$filtroposvt} AND (v.ch_lado IS NULL OR v.ch_lado = '')";
			}

			$sql =	"SELECT
					ch_numero_correl,
					ch_moneda,
					CASE
						WHEN ch_moneda = '01' THEN 1
						ELSE nu_tipo_cambio
					END,
					nu_importe,
					CASE
						WHEN ch_moneda = '01' THEN nu_importe
						ELSE nu_importe * nu_tipo_cambio
					END,
					to_char(dt_fecha,'HH24:MI:SS')
				FROM
					pos_depositos_diarios
				WHERE
					dt_dia = '$dia'
					AND ch_posturno = $turno
					AND ch_codigo_trabajador = '{$lxa['trabajador']}'
					AND ch_valida = 'S'
				ORDER BY
					ch_numero_correl;";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_depositos = 0;
			$depositos = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$deposito = Array();
				$deposito['correlativo'] = $a[0];
				$deposito['moneda'] = $a[1];
				$deposito['tc'] = $a[2];
				$deposito['importe'] = $a[3];
				$deposito['importe_soles'] = $a[4];
				$deposito['hora'] = $a[5];
				$total_depositos += $a[4];
				$depositos[] = $deposito;
			}
			$depositos['total'] = $total_depositos;
			$cuadre['depositos'] = $depositos;
			$depositos = NULL;

			$fpago = Array(
				"1"	=>	"EFECTIVO",
				"2"	=>	"TARJETA",
				"3"	=>	"OTROS"
			);

			$sql =	"
			SELECT
				v.ch_documento,
				c.cli_codigo,
				c.cli_razsocial,
				v.nu_importe
			FROM
				val_ta_cabecera v
				LEFT JOIN int_clientes c ON v.ch_cliente = c.cli_codigo
			WHERE
				c.cli_ndespacho_efectivo = 0
				AND v.dt_fecha = '$dia'
				AND v.ch_turno = '$turno'
				AND v.ch_sucursal = '$almacen'
				AND $filtrotransvt;
			";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_notas = 0;
			$notas = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$nota = Array();
				$nota['trans']   = $a[0];
				$nota['cliente'] = $a[1];
				$nota['nombre']  = $a[2];
				$nota['importe'] = $a[3];
				$total_notas += $a[3];
				$notas[] = $nota;
			}
			$notas['total'] = $total_notas;
			$cuadre['nd'] = $notas;
			$notas = NULL;

			$sql =	"	SELECT
						v.ch_documento,
						c.cli_codigo,
						c.cli_razsocial,
						v.nu_importe
					FROM
						val_ta_cabecera v
						LEFT JOIN int_clientes c ON v.ch_cliente = c.cli_codigo
					WHERE
						c.cli_ndespacho_efectivo = 1
						AND v.dt_fecha = '$dia'
						AND v.ch_turno = '$turno'
						AND v.ch_sucursal = '$almacen'
						AND $filtrotransvt;";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_notase = 0;
			$notase = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$notae = Array();
				$notae['trans'] = $a[0];
				$notae['cliente'] = $a[1];
				$notae['nombre'] = $a[2];
				$notae['importe'] = $a[3];
				$total_notase += $a[3];
				$notase[] = $notae;
			}
			$notase['total'] = $total_notase;
			$cuadre['nde'] = $notase;
			$notase = NULL;

			//Query Tarjetas de Credito
			$sql =	"
					SELECT
						t.trans,
						max(t.text1),
						max(g.tab_desc_breve),
						sum(t.importe)-first(COALESCE(t.km,0)),
						to_char(first(t.fecha),'HH24:MI:SS')
					FROM
						$postrans t
						LEFT JOIN int_tabla_general g ON (g.tab_tabla = '95' AND g.tab_elemento = '00000'||t.at)
					WHERE
						t.td IN ('B','F')
						AND t.fpago = '2'
						AND t.dia = '$dia'
						AND t.turno = '$turno'
						AND $filtrotrans
					GROUP BY
						t.fecha,
						t.trans
					ORDER BY
						t.fecha;
			";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_tarjetas = 0;
			$tarjetas = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$tarjeta = Array();
				$tarjeta['trans'] = $a[0];
				$tarjeta['tarjeta'] = $a[1];
				$tarjeta['tipo'] = $a[2];
				$tarjeta['importe'] = $a[3];
				$tarjeta['hora'] = $a[4];
				$total_tarjetas += $a[3];
				$tarjetas[] = $tarjeta;
			}
			$tarjetas['total'] = $total_tarjetas;
			$cuadre['tc'] = $tarjetas;
			$tarjetas = NULL;

			//OPENSOFT-98: Redondeo de documentos en efectivo en reportes de liquidación
			$sql =	"
					SELECT 
						sum(x) 
					FROM 
						(SELECT 
							round((((first(t.soles_km)*100)%10)/100),2) AS x 
						FROM 
							$postrans t
						WHERE 
							t.td IN ('B','F')
							AND t.fpago = '1' 
							AND t.dia = '$dia'
							AND t.turno = '$turno'
							AND $filtrotrans
						GROUP BY 
							t.caja,t.trans) x;
			";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_redondeo_efe = 0;
			$redondeo_efe = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a                    = $sqlca->fetchRow();
				$redondeo             = Array();
				$redondeo['importe']  = $a[0];
				$total_redondeo_efe  += $a[0];
				$redondeo_efe[]       = $redondeo;
			}
			$redondeo_efe['total']       = $total_redondeo_efe;
			$cuadre['redondeo_efectivo'] = $redondeo_efe;
			$redondeo_efe                = NULL;
			//Cerrar OPENSOFT-98: Redondeo de documentos en efectivo en reportes de liquidación

			$sql =	"	SELECT
						t.trans,
						CASE
							WHEN t.td = 'N' AND c.cli_ndespacho_efectivo = 1 THEN '1'
							WHEN t.td = 'N' AND c.cli_ndespacho_efectivo = 0 THEN '2'
							ELSE t.fpago
						END AS fpago,
						t.importe
					FROM
						$postrans t
						LEFT JOIN pos_fptshe1 f ON (t.tarjeta = f.numtar)
						LEFT JOIN int_clientes c ON trim(f.codcli) = trim(c.cli_codigo)
					WHERE
						tm = 'D'
						AND t.td IN ('B','F','N')
						AND t.dia = '$dia'
						AND t.turno = '$turno'
						AND $filtrotrans";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_devoluciones = 0;
			$total_devoluciones_efectivo = 0;
			$devoluciones = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$devolucion = Array();
				$devolucion['trans'] = $a[0];
				$devolucion['fpago'] = $fpago[$a[1]];
				$devolucion['importe'] = $a[2];
				$total_devoluciones += $a[2];
				if ($a[1]=='1')
					$total_devoluciones_efectivo += $a[2];
				$devoluciones[] = $devolucion;
			}
			$devoluciones['total'] = $total_devoluciones;
			$devoluciones['total_efectivo'] = $total_devoluciones_efectivo;
			$cuadre['devol'] = $devoluciones;
			$devoluciones = NULL;

			$sql =	"	SELECT
						t.trans,
						t.importe
					FROM
						$postrans t
					WHERE
						tm = 'A'
						AND t.dia = '$dia'
						AND t.turno = '$turno'
						AND $filtrotrans;";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_anulaciones = 0;
			$anulaciones = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$anulacion = Array();
				$anulacion['trans'] = $a[0];
				$anulacion['importe'] = $a[1];
				$total_anulaciones += $a[1];
				$anulaciones[] = $anulacion;
			}
			$anulaciones['total'] = $total_anulaciones;
			$cuadre['anul'] = $anulaciones;
			$anulaciones = NULL;

			$sql =  "       SELECT
						t.trans,
						sum(t.importe),
						to_char(first(t.fecha),'HH24:MI:SS')
					FROM
						$postrans t
					WHERE
						t.td IN ('B','F')
						AND t.fpago = '3'
						AND t.dia = '$dia'
						AND t.turno = '$turno'
						AND $filtrotrans
					GROUP BY
						t.trans;";

			if ($sqlca->query($sql)<0)
				return FALSE;

			$total_transgrat = 0;
			$transgrat = Array();
			for ($i=0;$i<$sqlca->numrows();$i++) {
				$a = $sqlca->fetchRow();
				$trgrat = Array();
				$trgrat['trans'] = $a[0];
				$trgrat['importe'] = $a[1];
				$trgrat['hora'] = $a[2];
				$total_transgrat += $a[1];
				$transgrat[] = $trgrat;
			}
			$transgrat['total'] = $total_transgrat;
			$cuadre['tg'] = $transgrat;
			$transgrat = NULL;

			if ($listalados != "") {
				$sql =	"	
					SELECT
						t.trans,
						a.art_descbreve,
						t.precio,
						t.importe,
						t.fpago,
						CASE
							WHEN r.ruc IS NULL THEN ' '
							ELSE r.razsocial
						END
					FROM
						$postrans t
						LEFT JOIN int_articulos a ON t.codigo = a.art_codigo
						LEFT JOIN ruc r ON t.ruc = trim(r.ruc)
					WHERE
						--tm = 'V'
						--AND (importe < 0 or (importe>0 and grupo='D'))
						grupo 		= 'D'
						AND t.dia 	= '$dia'
						AND t.turno = '$turno'
						AND $filtrotransc;
				";

				if ($sqlca->query($sql)<0)
					return FALSE;

				$total_descuentos = 0;
				$total_descuentos_efectivo = 0;
				$descuentos = Array();
				for ($i=0;$i<$sqlca->numrows();$i++) {
					$a = $sqlca->fetchRow();
					$descuento = Array();
					$descuento['trans'] = $a[0];
					$descuento['descripcion'] = trim($a[5] . " " . $a[1] . " " . $a[2]);
					$descuento['fpago'] = $fpago[$a[4]];
					$descuento['importe'] = $a[3];
					$total_descuentos += $a[3];
					$descuentos[] = $descuento;
				}
				$descuentos['total'] = $total_descuentos;
				$descuentos['total_efectivo'] = $total_descuentos;
				$cuadre['desc'] = $descuentos;
				$descuentos = NULL;

				$sql =	"	SELECT
							t.trans,
							a.art_descbreve,
							t.veloc,
							t.lineas,
							t.importe
						FROM
							pos_ta_afericiones t
							LEFT JOIN int_articulos a ON t.codigo = a.art_codigo
						WHERE
							t.dia = '$dia'
							AND t.turno = '$turno'
							AND $filtrotransc;";

				if ($sqlca->query($sql)<0)
					return FALSE;

				$total_afericiones = 0;
				$afericiones = Array();
				for ($i=0;$i<$sqlca->numrows();$i++) {
					$a = $sqlca->fetchRow();
					$afericion = Array();
					$afericion['trans'] = $a[0];
					$afericion['producto'] = $a[1];
					$afericion['detalle'] = $a[2] . " " . $a[3];
					$afericion['importe'] = $a[4];
					$total_afericiones += $a[4];
					$afericiones[] = $afericion;
				}
				$afericiones['total'] = $total_afericiones;
				$cuadre['afer'] = $afericiones;
				$afericiones = NULL;
			} else {
				$total_descuentos = $total_descuentos_efectivo = $total_afericiones = 0;
			}

/*			if ($cuadre['tipo']=="C")
				$cuadre['fs'] = ($total_depositos - $venta_conto) + ($total_notas + $total_tarjetas + ($total_descuentos_efectivo * -1) + ($total_devoluciones_efectivo * -1) + $total_afericiones);
			else
				$cuadre['fs'] = ($total_depositos - $venta_ticket) + ($total_notas + $total_tarjetas + ($total_descuentos_efectivo * -1) + ($total_devoluciones_efectivo * -1));
*/

			$cuadre['fs'] = ($total_depositos - $venta_exigible) + ($total_notas + $total_tarjetas + $total_redondeo_efe + ($total_descuentos * -1) + ($total_devoluciones_efectivo * -1) + $total_afericiones + $total_transgrat);

			$reporte['cuadres'][] = $cuadre;
			$cuadre = NULL;
		}

		$ladosxtrabajador = NULL;

		$reporte['apertura'] = $apertura;
		$reporte['cierre'] = $cierre;

		return $reporte;
	}


	function validaCuadreTicket($dia, $turno) {
		global $sqlca;
		
		$sql = "SELECT count(*) FROM caja_cuadre_turno_ticket WHERE fecha='$dia' AND turno=$turno;";
		if ($sqlca->query($sql) < 0) 
			return 0;						
		$a = $sqlca->fetchRow();	
		
		if($a[0]==0) {
			return 0; // No existe
		} else {
			return 1; // Existe, tomar total de tickets
		}
	}
}
/*
Estructura del arreglo del reporte

Array (
[x]	Nivel 1: Dia y Turno
   [dia]	Dia
   [turno]	Turno
   [apertura]	Hora y fecha de cuando se abrio el turno
   [cierre]	Hora y fecha de cuando se cerro el turno
   [cuadres]	Tabla de Cuadres
      [y]
         [trabajador]	Codigo del trabajador
         [nombre]	Nombre del trabajador
         [sucursal]	Nombre del trabajador
         [lados]	Lados asignados al trabajador en el turno (Combustibles)
            [z]		--
               [lado]	Numero de lado (Combustibles)
               [mangueras]	Contometros x manguera del lado
                  [a]	Numero de Manguera (?)
                     [producto]		Nombre del Producto "GLP" "DieselB2" ""(Manguera no utilizada)
                     [codigo]		Codigo del Producto
                     [codigocombex]	Codigo del Producto "90" "D2" "GL"
                     [precio]		Precio del Producto segun contometros
                     [conto_inicial_vol]	Contometro Inicial de la manguera (Volumen)
                     [conto_inicial_sol]	Contometro Inicial de la manguera (Soles)
                     [conto_final_vol]	Contometro Final de la manguera (Volumen)
                     [conto_final_sol]	Contometro Final de la maguera (Soles)
                     [conto_venta_vol]	Venta Total de la Manguera x Contometros (Volumen)
                     [conto_venta_sol]	Venta Total de la Manguera x Contometros (Soles)
                     [ticket_venta_vol]	Venta Total de la Manguera x Tickets (Volumen)
                     [ticket_venta_sol]	Venta Total de la Manguera x Tickets (Soles)
                     [diferencia_vol]	Diferencia entre Contometros y Tickets (Volumen)
                     [diferencia_sol]	Diferencia entre Contometros y Tickets (Soles)
               [conto_venta_vol]	Venta Total del lado x Contometros (Volumen)
               [conto_venta_sol]	Venta Total del lado x Contometros (Soles)
               [ticket_venta_vol]	Venta Total del lado x Tickets (Volumen)
               [ticket_venta_sol]	Venta Total del lado x Tickets (Soles)
               [diferencia_vol]		Diferencia Total del Lado (Volumen)
               [diferencia_sol]		Diferencia Total del Lado (Soles)
         [pos]	Puntos de Venta Asignados
            [ticket_venta_vol]	Venta Total del Punto de Venta x Tickets (Cantidad)
            [ticket_venta_sol]	Venta Total del Punto de Venta x Tickets (Soles)
         [venta_conto]	Venta Total de Combustibles segun contometros (Soles)
         [venta_ticket]	Venta Total de Combustibles segun tickets (Soles)
         [venta_market]	Venta Total de Tiendasegun tickets (Soles)
         [venta_exigible]	Venta Total Exigible
         [diferencia]	Diferencia Total de la Tabla (Soles)  
         [depositos]	Depositos realizados por el trabajador
            [total]	Importe total de los depositos
            [b]
               [correlativo]	Correlativo del deposito
               [moneda]		Moneda del Deposito
               [tc]		Tipo de Cambio
               [importe]	Monto del deposito
               [importe_soles]	Importe Soles
         [nd]	Notas de Despacho
            [total]	Importe total de las ND
            [c]
               [trans]	Numero de Transaccion
               [cliente]	Codigo de Cliente
               [nombre]		Nombre del Cliente
               [importe]	Importe del ticket
         [nde]	Notas de Despacho en Efectivo
            [total]	Importe total de las ND
            [c]
               [trans]	Numero de Transaccion
               [cliente]	Codigo de Cliente
               [nombre]		Nombre del Cliente
               [importe]	Importe del ticket
         [tc]	Pagos con Tarjeta de Credito
            [d]
               [trans]	Numero de Transaccion
               [tarjeta]	Numero de Tarjeta ingresado
               [tipo]		Tipo de Tarjeta (VISA-MC-AMEX)
               [importe]	Importe del ticket
         [desc]	Descuentos Otorgados
            [total]	Importe total de los descuentos (Efectivo y TC)
            [total_efectivo]	Importe Total de los Descuentos en Efectivo
            [c]
               [trans]	Numero de Transaccion
               [descripcion]	Descripcion del Descuento(PRODUCTO - MONTO)
               [fpago]	Monto Unitario del Descuento
               [importe]	Importe del Descuento
         [devol]	Devoluciones
            [total]	Importe total de las devoluciones
            [total_efectivo]	Importe Total de las Devoluciones en Efectivo
            [e]
               [trans]	Numero de Transaccion
               [fpago]	Forma de pago de la devolucion
               [importe]	Importe de la Devolucion
         [afer]	Afericiones
            [total]	Importe Total de las Afericiones
            [f]
               [trans]	Numero de Transaccion
               [producto]	Producto
               [detalle]	Modo y Lineas
               [importe]	Importe de la Afericion
         [fs]	Sobrante/Faltante de la Tabla
)
*/
