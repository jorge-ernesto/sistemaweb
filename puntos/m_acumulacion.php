<?php

class AcumulacionModel {

	function obtenerModoFideliza() {
		global $sqlca;
		/*
			Modos de Fidelizacion:
			0	Fidelizacion Desactivada
			1	Normal
			2	Normal + Acumulaciones 0 Puntos
			3	Bonus
		*/

		$sql = "	SELECT
						par_valor
					FROM
						int_parametros
					WHERE
						par_nombre = 'modo_fideliza';
					";

		$r = $sqlca->query($sql);
		if ($sqlca->numrows() < 1)
			return 0;

		$row = $sqlca->fetchRow();
		$modo = $row['par_valor'];

		settype($modo,"int");
		if ($modo < 1 || $modo > 3)
			$modo = 0;

		return $modo;
	}

	function acumulaPuntos($tarjeta,$codigo,$fecha,$td,$caja,$numero,$cantidad,$importe,$sucursal) {
		global $sqlca;

		$ModoFide = AcumulacionModel::obtenerModoFideliza();
		if ($ModoFide == 0 || $ModoFide == 3)
			return "ok";

		$sql =	"	SELECT
					c.id_tipo_cuenta
				FROM
					prom_ta_tarjetas t
					JOIN prom_ta_cuentas c ON t.id_cuenta = c.id_cuenta
				WHERE
					t.nu_tarjeta_numero = $tarjeta;";

		$rs = $sqlca->query($sql);
		if ($sqlca->numrows() < 1)
			return "error0.1";

		$row = $sqlca->fetchRow();
		$tipocuenta = $row['id_tipo_cuenta'];

		if (strlen($codigo) != 8 || substr($codigo,0,7) != "1162030")
			$codigo = "MARKET";

		$sql = 	"	SELECT
					pp.puntos_sol,
					pp.puntos_unidad,
					c.nu_repeticiones
				FROM
					prom_ta_campanas_tipocuenta ct
					JOIN prom_ta_campanas c ON ct.id_campana = c.id_campana
					JOIN prom_ta_puntos_x_producto pp ON (c.id_campana = pp.id_campana AND pp.art_codigo = '$codigo')
				WHERE
					ct.id_tipo_cuenta = $tipocuenta
					AND now() BETWEEN c.dt_campana_fecha_inicio AND c.dt_campana_fecha_fin
				ORDER BY
					dt_campana_fecha_inicio DESC
				LIMIT
					1;";

		$rs = $sqlca->query($sql);
		if ($sqlca->numrows() < 1)
			return "ok";

		$row = $sqlca->fetchRow();
		$maxrepeticiones = $row['nu_repeticiones'];
		if ($row["puntos_sol"] > 0)
			$puntosCalculados = $importe * $row["puntos_sol"];
		else
			$puntosCalculados = $cantidad * $row["puntos_unidad"];

		$puntosCalculados = round($puntosCalculados,0,PHP_ROUND_HALF_DOWN);

		// Fecha en formato 2010-02-16 09:58:30.0000
		$fecha2 = substr($fecha, 0, 10);
		$hora = substr($fecha, 11, 8);
		list($ano, $mes, $dia) = explode("-", $fecha2);
		list($horas, $minutos, $segundos) = explode(":", $hora);

		$dias = array(
				"Monday"	=>	2,
				"Tuesday"	=>	3,
				"Wednesday"	=>	4,
				"Thursday"	=>	5,
				"Friday"	=>	6,
				"Saturday"	=>	7,
				"Sunday"	=>	1
			);

		$dateInfo = getdate(mktime($horas, $minutos, $segundos, $mes, $dia, $ano));
		$dia_semana = $dias[$dateInfo['weekday']];
		
		/*$sql_backip =	"	SELECT
					nu_horario_factor_multi
				FROM
					prom_ta_horarios_multi
				WHERE
					nu_horario_dia_multi=" . $dia_semana . "AND
					( " . $horas . "||':'||" . $minutos . ")::time 
					BETWEEN (to_char(nu_horario_hora_inicio,'99')||':'||to_char(nu_horario_minuto_inicio,'99'))::time AND
					(to_char(nu_horario_hora_fin,'99')||':'||to_char(nu_horario_minuto_fin,'99'))::time" ;
		
		*/

		$sql =	"	SELECT
					nu_horario_factor_multi
				FROM
					prom_ta_horarios_multi
				WHERE
					nu_horario_dia_multi=" . $dia_semana . "  AND
					( " . $horas . "||':'||" . $minutos . ")::time 
					BETWEEN (nu_horario_hora_inicio::TEXT||':'||nu_horario_minuto_inicio::TEXT)::time AND
					(nu_horario_hora_fin::TEXT||':'||nu_horario_minuto_fin::TEXT)::time" ;

		if ($sqlca->query($sql) < 0)
			return "error1";

		$row = $sqlca->fetchRow();

		if (isset($row[0]))
			$puntosCalculados *= $row['nu_horario_factor_multi'];

		if ($puntosCalculados >= 0)
			$puntosCalculados = floor($puntosCalculados);
		else	// < 0
			$puntosCalculados = ceil($puntosCalculados);

		if ($puntosCalculados == 0 && $ModoFide == 1)
			return "ok";

		$sql =	"	SELECT
					count(mp.*) AS repeticiones,
					mp.dt_punto_fecha::date AS dia
				FROM
					prom_ta_movimiento_puntos mp
					JOIN prom_ta_tarjetas t ON mp.id_tarjeta = t.id_tarjeta
				WHERE
					t.nu_tarjeta_numero = $tarjeta
					AND mp.dt_punto_fecha::date = '$fecha2'
				GROUP BY
			
					2";

		if ($sqlca->query($sql) < 0)
			return "error3";

		$row = $sqlca->fetchRow();

		$tipomov = '1';
		if ($row['repeticiones'] >= $maxrepeticiones)
			$tipomov = '4';

		$sql = "	SELECT
					c.isactive
				FROM
				 	prom_ta_tarjetas t
				 	JOIN prom_ta_cuentas c ON t.id_cuenta = c.id_cuenta
				WHERE
				 	t.nu_tarjeta_numero = $tarjeta;";

		if ($sqlca->query($sql) < 0)
			return "error4";

		$row = $sqlca->fetchRow();
		$acumulacion = $row['isactive'];

		if ($acumulacion == 0){
			$puntosCalculados = 0;
		}

		$sql =	"	INSERT INTO
					prom_ta_movimiento_puntos
				(
					id_tarjeta,
					nu_punto_tipomov,
					dt_punto_fecha,
					nu_punto_puntaje,
					ch_trans_td,
					ch_trans_caja,
					ch_trans_numero,
					ch_trans_codigo,
					nu_trans_cantidad,
					nu_trans_importe,
					ch_usuario,
					ch_sucursal
				) VALUES (
					(SELECT id_tarjeta FROM prom_ta_tarjetas WHERE nu_tarjeta_numero=" . $tarjeta . "),
					$tipomov,
					'" . $fecha . "',
					" . $puntosCalculados . ",
					'" . $td . "',
					'" . $caja . "',
					'" . $numero . "',
					'" . $codigo . "',
					'" . $cantidad . "',
					'" . $importe . "',
					'POS',
					'" . $sucursal . "'
				);";

		if ($sqlca->query($sql) < 0) {
			return "error2";
		} else {
			return "ok";
		}
	}

	function obtenerSaldo($tarjeta) {
		global $sqlca;

		$ModoFide = AcumulacionModel::obtenerModoFideliza();
		if ($ModoFide == 0 || $ModoFide == 3)
			return 0;

		$sql =	"	SELECT 
					c.nu_cuenta_puntos
				FROM
					prom_ta_tarjetas t
					JOIN prom_ta_cuentas c ON t.id_cuenta = c.id_cuenta
				WHERE
					t.nu_tarjeta_numero='" . pg_escape_string($tarjeta) . "';";

		if ($sqlca->query($sql) < 0)
			return "NaN";
		if ($sqlca->numrows() != 1)
			return "NaN";
		$row = $sqlca->fetchRow();

		return $row['nu_cuenta_puntos'];
	}

	function obtenerMensajeSaldo($tarjeta) {
		$ModoFide = AcumulacionModel::obtenerModoFideliza();
		if ($ModoFide == 0)
			return "";
		elseif ($ModoFide == 3)
			return "";

		$datos = AcumulacionModel::obtenerDatosTicket($tarjeta);
		if ($datos==NULL)
			return FALSE;

		$texto = alinea("Tarjeta: $tarjeta Saldo: {$datos[2]}",2) . "\r\n";

		if ($datos[6]!=NULL) {
			/*
				Cadenas aceptadas en el slogan

				%c	Nombre del Cliente
				%p	Saldo Anterior
				%t	Numero de Tarjeta
			*/

			$slogan = $datos[6];

			if (($pcp = strpos($slogan,"%c"))!==FALSE)
				$slogan = str_replace("%c","{$datos[4]}",$slogan);

			if (($pcp = strpos($slogan,"%p"))!==FALSE)
				$slogan = str_replace("%p","{$datos[2]}",$slogan);

			if (($pcp = strpos($slogan,"%t"))!==FALSE)
				$slogan = str_replace("%t","$tarjeta",$slogan);

			$texto .= "\r\n{$slogan}\r\n";
		}

		if ($datos[7]==1 && $datos[5]==1)
			$texto .= "\r\n{$datos[8]}\r\n";

		return $texto;
	}

	function obtenerDatosTicket($tarjeta) {
		global $sqlca;

		/*
			0	ID Campana
			1	Descripcion Campana
			2	Saldo Cuenta
			3	ID Tipo Cuenta
			4	Nombre Cliente
			5	Flag de Cumple del Cliente
			6	Slogan de Campana
			7	Flag que indica si debe saludar por cumple
			8	Mensaje de saludo de cumple
		*/

		$sql =	"	SELECT
					cp.id_campana,
					cp.ch_campana_descripcion,
					c.nu_cuenta_puntos,
					c.id_tipo_cuenta,
					c.ch_cuenta_nombres || ' ' || c.ch_cuenta_apellidos,
					(date_part('month',now()) = date_part('month',c.dt_fecha_nacimiento) AND date_part('day',now()) = date_part('day',c.dt_fecha_nacimiento))::integer,
					cp.slogan,
					cp.b_saluda_cumple::integer,
					COALESCE(p.par_valor,'FELIZ CUMPLEAÑOS!!!')
				FROM
					prom_ta_tarjetas t
					JOIN prom_ta_cuentas c ON t.id_cuenta = c.id_cuenta
					LEFT OUTER JOIN prom_ta_campanas_tipocuenta ct ON c.id_tipo_cuenta = ct.id_tipo_cuenta
					LEFT OUTER JOIN prom_ta_campanas cp ON ct.id_campana = cp.id_campana
					LEFT JOIN int_parametros p ON (p.par_nombre = 'fide_saludo_cumple')
				WHERE
					t.nu_tarjeta_numero = " . pg_escape_string($tarjeta) . "
					AND now() BETWEEN cp.dt_campana_fecha_inicio AND cp.dt_campana_fecha_fin;";

		if ($sqlca->query($sql) < 0)
			return NULL;

		if ($sqlca->numrows() != 1)
			return NULL;

		$row = $sqlca->fetchRow();

		return $row;
	}

}

	function espacios($q) {
		$ret = "";
		for ($q;$q>0;$q--)
			$ret .= " ";
		return $ret;
	}

	/*
		Alinea texto simple
		Tipo:
		0	Derecha
		1	Izquierda
		2	Centro
	*/

	function alinea($str,$tipo) {
		if ($tipo==0)
			return ($str . espacios((40-strlen($str))));
		else if ($tipo==1)
			return (espacios((40-strlen($str))) . $str);

		return (espacios((20-(strlen($str)/2))) . $str . espacios((20-(strlen($str)/2))));
	}
