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

class SobrantesFaltantesTrabajadorModel extends Model {
	function obtenerAlmacenes() {
		global $sqlca;

		$sql =	"	SELECT
					ch_almacen,
					ch_almacen || ' - ' || ch_nombre_almacen
				FROM
					inv_ta_almacenes
				WHERE
					ch_clase_almacen='1';";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = array();

		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}

		return $result;
	}

	function validaDia($dia) {
		global $sqlca;

		$dia = substr($dia,6,4)."-".substr($dia,3,2)."-".substr($dia,0,2);

		$turno = 0;

		$almacen = $_SESSION['almacen'];

		$sql = " SELECT validar_consolidacion('$dia',$turno,'$almacen') ";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		echo "devolvio:\n";
		var_dump($estado);

		if($estado[0] == 1){
			return 1;//Consolidado
		}else{
			return 0;//No consolidado
		}
	}

	function obtenerTrabajadores($dia,$turno) {
		global $sqlca;

		if ($dia=="" || $turno=="")
			$sql =	"	SELECT
						ch_codigo_trabajador,
						TRIM(ch_apellido_paterno)||' '||TRIM(ch_nombre1)
					FROM
						pla_ta_trabajadores
					ORDER BY
						ch_codigo_trabajador ASC";
		else
			$sql = "SELECT DISTINCT
					p1.ch_codigo_trabajador,
					TRIM(p1.ch_apellido_paterno)||' '||TRIM(p1.ch_nombre1)
				FROM
					pla_ta_trabajadores p1
					INNER JOIN pos_historia_ladosxtrabajador p2 ON (p1.ch_codigo_trabajador=p2.ch_codigo_trabajador)
				WHERE
					p2.dt_dia=to_date('$dia','DD/MM/YYYY')
						AND p2.ch_posturno='$turno'
				ORDER BY
					p1.ch_codigo_trabajador ASC";

		if ($sqlca->query($sql) < 0)
			return false;

		$result = array();

		for ($i=0;$i<$sqlca->numrows();$i++) {
			$a = $sqlca->fetchRow();
			$result[$a[0]] = $a[1];
		}

		return $result;
	}

	function buscar($almacen, $dia, $dia2, $codtrabajador, $ordenpor) {
		global $sqlca;

		$sql =	"SELECT 
				to_char(dt.dia,'DD/MM/YYYY') as dia ,
				dt.turno as turno,
				TRIM(t.ch_codigo_trabajador)||' - '||TRIM(ch_apellido_paterno)||' '||TRIM(ch_apellido_materno)||' '||TRIM(ch_nombre1)||' '||trim(ch_nombre2) as trabajador,
				dt.importe as importe,
				dt.observacion as observacion,
				CASE
					WHEN dt.planilla=0 THEN 'No'
					ELSE 'Si'
				END as planilla,
				CASE
					WHEN dt.flag=0 THEN 'Automatico'
					ELSE 'Manual'
				END as flagescrito,
				dt.flag as flag,
				dt.es
			FROM
				comb_diferencia_trabajador dt
				LEFT JOIN pla_ta_trabajadores t ON (dt.ch_codigo_trabajador = t.ch_codigo_trabajador)
			WHERE
				1 = 1
				" . (($almacen=="")?"":"AND dt.es='$almacen'") . "
				" . (($dia=="" || $dia2=="")?"":"AND dt.dia BETWEEN to_date('$dia','DD/MM/YYYY') AND to_date('$dia2','DD/MM/YYYY')") . "
				" . ((trim($codtrabajador)=="")?"":"AND t.ch_codigo_trabajador='$codtrabajador'") . "
				" . (($ordenpor==1)?" ORDER BY dt.dia DESC ":" ORDER BY t.ch_codigo_trabajador ASC, dt.dia DESC ") . "
				;";

		if ($sqlca->query($sql) < 0)
			return false;

		$resultado = Array();
		
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();

			$resultado[$i]['dia'] = $a[0];
			$resultado[$i]['turno'] = $a[1];
			$resultado[$i]['trabajador'] = $a[2];
			$resultado[$i]['importe'] = $a[3];
			$resultado[$i]['observacion'] = $a[4];
			$resultado[$i]['planilla'] = $a[5];
			$resultado[$i]['flagescrito'] = $a[6];
			$resultado[$i]['flag'] = $a[7];
			$resultado[$i]['es'] = $a[8];
		}
		return $resultado;
	}

	function agregar($almacen,$dia, $turno, $trabajador, $importe, $observacion) {
		global $sqlca;

		$x =	"	SELECT
					ch_tipo
				FROM
					pos_historia_ladosxtrabajador
				WHERE
					dt_dia = to_date('$dia','DD/MM/YYYY')
					AND ch_posturno = $turno
					AND ch_codigo_trabajador = '".pg_escape_string($trabajador)."'
				LIMIT
					1
				";

		$sql =	"	INSERT INTO
					comb_diferencia_trabajador
				(
					es,
					dia,
					turno,
					ch_codigo_trabajador,
					importe,
					observacion,
					flag,
					tipo
				) VALUES (
					'$almacen',
					to_date('$dia','DD/MM/YYYY'),
					$turno,
					'".pg_escape_string($trabajador)."',
					$importe,
					'" . addslashes($observacion) . "',
					1,
					($x)
				);";

		if ($sqlca->query($sql)<0)
			return FALSE;

		return TRUE;
	}

	/*function importar($fecha1,$fecha2) {
		global $sqlca;

		$sql =	"	SELECT
					da_fecha
				FROM
					pos_aprosys
				WHERE
					de_fecha BETWEEN to_date('$fecha1','DD/MM/YYYY') AND to_date('$fecha2','DD/MM/YYYY')
					AND ch_poscd = 'S';"

		if ($sqlca->query($sql)<0)
			return "INTERNAL_ERROR";

		$dias = Array();
		while ($reg = $sqlca->fetchRow())
			$dias[] = $reg;

		foreach($dias as $dia)
			if (($res = SobrantesFaltantesTrabajadorModel::importarDia($r[2],$turno))!="OK")
				return $res;

	}*/

	function importarDia($dia) {
		global $sqlca;

		$sqlca->query("BEGIN;");

		$sql =	"	SELECT
					ch_posturno,
					ch_poscd,
					da_fecha
				FROM
					pos_aprosys
				WHERE
					da_fecha = to_date('$dia','DD/MM/YYYY');";
		if ($sqlca->query($sql)<0)
			return "INTERNAL_ERROR_ID1";
		if ($sqlca->numrows()==0)
			return "INVALID_DATE";
		$r = $sqlca->fetchRow();

		if ($r[1]!='S')
			return "INVALID_DATE";

		$i = $r[0];
		settype($i,"int");
		if ($i==0 || $i==1)
			return "INVALID_DATE";

		for ($j=1;$j<$i;$j++)
			if (($res = SobrantesFaltantesTrabajadorModel::importarTurno($r[2],$j))!="OK") {
				$sqlca->query("ROLLBACK;");
				return $res;
			}

		$sqlca->query("COMMIT;");

		return "OK";
	}

	function importarTurno($dia,$turno) {
		global $sqlca;

		$sql =	"	SELECT
					trim(hlx.ch_lado),
					trim(hlx.ch_codigo_trabajador),
					hlx.ch_tipo,
					hlx.ch_sucursal,
					p.par_valor
				FROM
					pos_historia_ladosxtrabajador hlx
					LEFT JOIN int_parametros p ON p.par_nombre = 'diferencia_ignorada'
				WHERE
					hlx.dt_dia = '$dia'
					AND hlx.ch_posturno = '$turno';";

		if ($sqlca->query($sql)<0)
			return "INTERNAL_ERROR_IT1";

		$lados = Array();
		while ($reg = $sqlca->fetchRow())
			$lados[] = $reg;

		// Borra registros pre existentes de sobrantes y faltantes para esta fecha
		// Y ingresa los depositos de cada trabajador como sobrantes
		$trabajadores = Array();
		foreach ($lados as $reg) {
			$diferencia_ignorada = $reg[4];
			$trabajador = $reg[1];
			$es = $reg[3];
			// Si aún no ha ingresado los depositos del trabajador, los ingresa
			if (!isset($trabajadores[$trabajador])) {
				$sql =	"	DELETE FROM
							comb_diferencia_trabajador
						WHERE
							es = '{$reg[3]}'
							AND ch_codigo_trabajador = '{$reg[1]}'
							AND dia = '{$dia}'
							AND turno = '{$turno}'
							AND flag = 0;";

				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT2";

				$sql =	"	SELECT
							COALESCE(sum(
								CASE
									WHEN ch_moneda='01' THEN nu_importe
									ELSE nu_importe * nu_tipo_cambio
								END
							),0)
						FROM
							pos_depositos_diarios
						WHERE
							ch_valida='S'
							AND dt_dia = '$dia'
							AND ch_posturno = $turno
							AND ch_codigo_trabajador = '$trabajador';";
				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT3";
				$r = $sqlca->fetchRow();
				$deptr = $r[0];
				if (SobrantesFaltantesTrabajadorModel::upsertFaltantes($es,$trabajador,$dia,$turno,$deptr)==FALSE)
					return "INTERNAL_ERROR_IT4";
				$trabajadores[$trabajador] = $trabajador;

			}
		}

		// Hace el cálculo de sobrante/faltante por cada lado
		foreach ($lados as $reg) {
			// Prepara Variables
			$lado = $reg[0];
			$trabajador = $reg[1];
			$es = $reg[3];
			$fechax = explode("-",$dia);
			$postrans = "pos_trans{$fechax[0]}{$fechax[1]}";

			/*
				Previamente, se ha insertado la suma de depositos de cada trabajador como un sobrante
			*/

			if ($reg[2]=="C") {
				/* En Combustibles:
					Al sobrante actual, le resta la venta x contometros, y le aumenta:
					Afericiones, N/D, Tarjetad, Descuentos y Devoluciones
					de cada lado asignado al trabajador.
				*/

				// El lado debe tener 2 digitos (01,02...). Si tiene uno, es un error de digitacion, asi que lo ignora.
				if (strlen($lado)!=2)
					continue;

				// Obtiene la venta del lado
				$ladoi = $lado;
				settype($ladoi,"int");
				$sql =	"	SELECT
							sum(cnt_val) AS importe,
							min(cnt),
							min(fecha)
						FROM
							pos_contometros
						WHERE
							num_lado = $ladoi
							AND dia = '$dia'
							AND turno = '$turno';";
				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT5";
				$r = $sqlca->fetchRow();
				$final = $r[0];
				$sql =	"	SELECT
							sum(cnt_val) AS importe
						FROM
							pos_contometros
						WHERE
							num_lado = $ladoi
							AND cnt < {$r[1]}
							AND fecha < '{$r[2]}'
						GROUP BY
							dia,
							turno
						ORDER BY
							dia DESC,
							turno DESC
						LIMIT
							1;";
				if ($sqlca->query($sql)<0) {
					return "INTERNAL_ERROR_IT6";}
				$r = $sqlca->fetchRow();
				$inicial = $r[0];
//				$ventalado = $inicial - $final;	// Para tenerlop en negativo
				$ventalado = diferencia_contometros($inicial,$final)*-1;	//Para tenerlo en negativo

				if (SobrantesFaltantesTrabajadorModel::upsertFaltantes($es,$trabajador,$dia,$turno,$ventalado)==FALSE)
					return "INTERNAL_ERROR_IT7";

				// Agrega las demas variables de la formula
				$sql =	"	SELECT
							(SELECT COALESCE(sum(importe),0) FROM $postrans WHERE td='A' AND dia='$dia' AND tipo='C' AND pump='$lado' AND turno='$turno') +
							(SELECT COALESCE(sum(importe),0) FROM $postrans WHERE td='N' AND dia='$dia' AND tipo='C' AND pump='$lado' AND turno='$turno') +
							(SELECT COALESCE(sum(importe),0) FROM $postrans WHERE td IN ('F','B') AND fpago='2' AND dia='$dia' AND tipo='C' AND pump='$lado' AND turno='$turno') +
							(SELECT COALESCE(sum(importe),0)*-1 FROM $postrans WHERE td IN ('F','B') AND fpago='1' AND importe<0 AND dia='$dia' AND tipo='C' AND pump='$lado' AND turno='$turno') AS GranFormulaXD;";
/*
En la Formula Compensatoria: En la linea que obtiene Descuentos y Devoluciones (4) Creo que debería haber la condicion "AND fpago='1'",
pues los descuentos y devoluciones pagados con T/C son contabilizados en la linea anterior.

Lo he eliminado para que cuadre con el reporte venta x manguera x trabajador.

Queda pendiente la discusion de si va o no va.
*/
				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT8";
				$r = $sqlca->fetchRow();
				$aumento = $r[0];
				if (SobrantesFaltantesTrabajadorModel::upsertFaltantes($es,$trabajador,$dia,$turno,$aumento)==FALSE)
					return "INTERNAL_ERROR_IT9";
			} else {
				// En Market, Saca la diferencia entre depositos y suma de tickets, y la inserta como faltante
				$sql =	"	SELECT
							COALESCE(sum(importe)*-1,0)
						FROM
							$postrans
						WHERE
							trim(caja) = '$lado'
							AND es = '$es'
							AND dia = '$dia'
							AND turno = '$turno'
							AND tipo='M';";

				if ($sqlca->query($sql)<0)
					return "INTERNAL_ERROR_IT10";
				$r = $sqlca->fetchRow();
				$aumento = $r[0];
				if (SobrantesFaltantesTrabajadorModel::upsertFaltantes($es,$trabajador,$dia,$turno,$aumento)==FALSE)
					return "INTERNAL_ERROR_IT11";
			}
		}

		if ($diferencia_ignorada>0) {
			$sql =	"	DELETE FROM
						comb_diferencia_trabajador
					WHERE
						dia = '$dia'
						AND turno = '$turno'
						AND flag = 0
						AND abs(importe)<{$diferencia_ignorada};";

			if ($sqlca->query($sql)<0)
				return "INTERNAL_ERROR_IT12";
		}

		return "OK";
	}

	function upsertFaltantes($es,$trabajador,$dia,$turno,$importe) {
		global $sqlca;

		$sql =	"	SELECT
					id_diferencia_trabajador
				FROM
					comb_diferencia_trabajador
				WHERE
					es = '$es'
					AND ch_codigo_trabajador = '$trabajador'
					AND dia = '$dia'
					AND turno = '$turno'
					AND flag = 0;";

		if ($sqlca->query($sql)<0)
			return FALSE;

		if ($sqlca->numrows()==0) {
			$sql =	"	INSERT INTO
						comb_diferencia_trabajador
					(
						es,
						ch_codigo_trabajador,
						dia,
						turno,
						flag,
						importe,
						observacion
					) VALUES (
						'$es',
						'$trabajador',
						'$dia',
						$turno,
						0,
						$importe,
						''
					);";
		} else {
			$r = $sqlca->fetchRow();
			$sql =	"	UPDATE
						comb_diferencia_trabajador
					SET
						importe = importe + ($importe)
					WHERE
						id_diferencia_trabajador = {$r[0]};";
		}

		return ($sqlca->query($sql)>=0);
	}
}
