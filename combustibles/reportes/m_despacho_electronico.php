<?php
class depacho_electronico_Model extends Model {
	function IniciarTransaccion() {
		global $sqlca;
		try {
			$sql = "BEGIN";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("No se pudo INICIAR la TRANSACION");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function COMMITransaccion() {
		global $sqlca;
		try {
			$sql = "COMMIT";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("No se pudo procesar la TRANSACION");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function ROLLBACKTransaccion() {
		global $sqlca;
		try {
			$sql = "ROLLBACK";
			if ($sqlca->query($sql) < 0) {
				throw new Exception("No se pudo Retroceder el proceso.");
			}
		} catch (Exception $e) {
			throw $e;
		}
	}

	function ObtenerFechaDTurno($fecha) {
		global $sqlca;
		try {
			//REALIZAMOS LA BUSQUEDA DEL CIERRE DEL DIA ANTERIOR
			$sql_ini = "SELECT turno,fecha FROM pos_contometros  WHERE dia=(select ('$fecha'::DATE - interval '1 days')) GROUP BY turno,fecha  ORDER BY  fecha DESC LIMIT 1; ";
			if ($sqlca->query($sql_ini) < 0) {
				//throw new Exception("Error no se encontro fechas en la fecha Indicada .");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registroanterio[] = $reg;
			}

			$sql = "SELECT turno,fecha FROM pos_contometros  WHERE dia='$fecha' GROUP BY turno,fecha  ORDER BY  fecha ASC ";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error no se encontro fechas en la fecha Indicada .");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return array($registroanterio,$registro);
		} catch (Exception $e) {
			throw $e;
		}
	}

	function ObtenerLados() {
		global $sqlca;
		try {
			$sql = "SELECT  f_pump_id,name FROM f_pump";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al obtener lados.");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;
		} catch (Exception $e) {
			throw $e;
		}
	}

	function ObtenerMangueras($id_pump) {
		global $sqlca;
		try {
			$sql = "SELECT f_grade_id,name FROM f_grade where f_pump_id='$id_pump' order by f_grade_id asc;";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al obtener lados.");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}
			return $registro;
		} catch (Exception $e) {
			throw $e;
		}
	}

	function ObtenerReporte($fecha_inicio,$fecha_final,$tralad,$tragra) {
		global $sqlca;
		try {
			$registro=array();
			$sql = "
			SELECT
				expoac,
				--to_char(hora,'YYYY-MM-DD HH24:MI:SS') AS hora,
				hora,
				tralad,
				tragra,
				trapre,
				tragal,
				tratot,
				traiv,
				--codpos,
				(CASE WHEN codpos = 'N' THEN 'Normal' WHEN codpos = 'R' THEN 'Recuperada' END) AS codpos,
				nregtr,
				ncaja,
				fecha_creacion,
				ntrans,
				tot_volume,
				tot_value
			FROM
				pos_nbastra_historico
			WHERE
				hora BETWEEN '$fecha_inicio' AND '$fecha_final'
				AND tralad = '$tralad'
				AND tragra = '$tragra'
			ORDER BY
				hora;
			";

			if ($sqlca->query($sql) < 0) {
				throw new Exception("Error al realizar la busqueda verificar los parametros Ingresado.");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}

			return $registro;
		} catch (Exception $e) {
			throw $e;
		}
	}
}
