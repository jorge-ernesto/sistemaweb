<?php
class ConsolidacionModel extends Model {
	function GetAlmacenes() {
		global $sqlca;

		$sql = "
SELECT
 ch_almacen,
 ch_almacen||' - '||ch_nombre_almacen
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1' $cond 
ORDER BY
 ch_almacen;
		";
	
		if ($sqlca->query($sql) < 0) 
			return false;
	
		$result = Array();
		for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    	$a = $sqlca->fetchRow();
	    	$result[$a[0]] = $a[1];
		}
	
		return $result;
    }

	function buscar($almacen,$dia,$dia2) {	
		global $sqlca;

		$sql =	"
SELECT
 to_char(dia,'DD/MM/YYYY') AS fe_sistema,
 turno,
 to_char(fecha,'DD/MM/YYYY HH24:MI:SS') AS fecha,
 usuario,
 ip
FROM
 pos_consolidacion
WHERE
 dia BETWEEN to_date('$dia','DD/MM/YYYY') AND to_date('$dia2','DD/MM/YYYY')
 AND almacen = '".$almacen."'
 AND estado = '1'
ORDER BY
 dia DESC,
 turno DESC;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		$resultado = Array();

		for ($i = 0; $i < $sqlca->numrows(); $i++) {
			$a = $sqlca->fetchRow();
			$resultado[$i]['fe_sistema'] = $a[0];
			$resultado[$i]['turno'] = $a[1];
			$resultado[$i]['fecha'] = $a[2];
			$resultado[$i]['usuario'] = $a[3];
			$resultado[$i]['ip'] = $a[4];
		}
		return $resultado;
	}

	function obtenerDiferenciaIgnorada() {
		global $sqlca;

		$sql = "
		SELECT
			par_valor
		FROM
			int_parametros
		WHERE
			par_nombre = 'diferencia_ignorada';
		";

		if ($sqlca->query($sql) < 0)
			return 0;

		$r = $sqlca->fetchRow();

		settype($r[0],"float");
		return $r[0];
	}

	function consolidado($almacen,$dia,$turno) {
		global $sqlca;

		$sql =	"
		SELECT
			1
		FROM
			pos_consolidacion
		WHERE
			dia 		= '" . $dia . "'
			AND turno 	= " . $turno . "
			AND almacen = '" . $almacen . "';
		";

		if ($sqlca->query($sql)<0 || $sqlca->numrows()==0)
			return FALSE;

		return TRUE;
	}

	function obtenerHuecos($almacen, $siguiente){
		global $sqlca;							

		//Obtenemos dias a consolidar en pos_consolidacion (estado = 0), de dias anteriores
		if(!empty($almacen)){
			$sql="
			SELECT
				c.dia, --0
				c.turno, --1
				to_char(c.dia,'DD/MM/YYYY') --2
			FROM
				pos_consolidacion c
			WHERE
				almacen    = '" . $almacen . "'
				AND estado = '0'
				AND c.dia  < '" . $siguiente['dia'] . "'
			ORDER BY
				c.dia ASC,
				c.turno ASC
			LIMIT
				1;
			";
			// echo "<pre>";
			// echo "<b>obtenerHuecos dias anteriores</b><br>";
			// echo $sql;
			// echo "</pre>";
		}

		if ($sqlca->query($sql)<0) //Si falla la query
			return FALSE;

		if ($sqlca->query($sql)>0) { //Si hay registros
			$r = $sqlca->fetchRow();
			$ret = Array();
			$ret['dia'] 	= $r[0];
			$ret['diab'] 	= $r[2];
			$ret['turno'] 	= $r[1];
			$ret['flag']   = 0;

			return $ret;
		}

		//Obtenemos dias a consolidar en pos_consolidacion (estado = 0), del mismo dia pero turnos anteriores
		if(!empty($almacen)){
			$sql="
			SELECT
				c.dia, --0
				c.turno, --1
				to_char(c.dia,'DD/MM/YYYY') --2
			FROM
				pos_consolidacion c
			WHERE
				almacen 	   = '" . $almacen . "'
				AND estado  = '0'
				AND c.dia   = '" . $siguiente['dia'] . "'
				AND c.turno < '" . $siguiente['turno'] . "'
			ORDER BY
				c.dia ASC,
				c.turno ASC
			LIMIT
				1;
			";
			// echo "<pre>";
			// echo "<b>obtenerHuecos mismo dia</b><br>";
			// echo $sql;
			// echo "</pre>";
		}

		if ($sqlca->query($sql)<0 || $sqlca->numrows()==0) //Si falla la query o no retorna datos
			return FALSE;		

		$r = $sqlca->fetchRow();
		$ret = Array();
		$ret['dia'] 	= $r[0];
		$ret['diab'] 	= $r[2];
		$ret['turno'] 	= $r[1];
		$ret['flag']   = 0;
		
		return $ret;			
	}

	function obtenerSiguiente($almacen){
		global $sqlca;
	
		if(!empty($almacen)){

			$sql="
			SELECT
				c.dia, --0
				max(c.turno), --1
				max(a.ch_posturno), --2
				(c.dia + 1), --3
				max(c.turno) + 1, --4
				to_char(c.dia,'DD/MM/YYYY'), --5
				to_char((c.dia + 1),'DD/MM/YYYY') --6
			FROM
				pos_consolidacion c
				LEFT JOIN pos_aprosys a ON (c.dia = a.da_fecha)
			WHERE
				almacen 	= '" . $almacen . "'
				AND estado 	= '1'
			GROUP BY
				c.dia
			ORDER BY
				c.dia DESC
			LIMIT
				1
			;";
			// echo "<pre>";
			// echo "<b>obtenerSiguiente</b><br>";
			// echo $sql;
			// echo "</pre>";
		}

		if ($sqlca->query($sql)<0 || $sqlca->numrows()==0)
			return FALSE;

		$r = $sqlca->fetchRow();
		$ret = Array();
		// echo "<script>console.log('" . json_encode($r) . "')</script>";

		settype($r[1],"int");
		settype($r[2],"int");
		settype($r[4],"int");

		if ($r[4]==$r[2]) { //Dia siguiente turno 1
			$ret['dia'] 	= $r[3];
			$ret['diab'] 	= $r[6];
			$ret['turno'] 	= 1;
		} else {
			$ret['dia'] 	= $r[0];
			$ret['diab'] 	= $r[5];
			$ret['turno'] 	= $r[4];
		}
		
		//para mostrar mensaje de consolidacion
		$day = $ret['dia'];
		$sql = "SELECT ch_poscd FROM pos_aprosys WHERE da_fecha='$day';";

		if ($sqlca->query($sql)<0)
			return FALSE;

		$k = $sqlca->fetchRow();
		
		if (($r[2]-$r[1]==2) and ($k[0]=="S")) {
			$ret['flag'] = "1";
		} else {
			$ret['flag'] = "0";
		}

		return $ret;
	}

	function validarConsolidaciones($almacen, $dia, $turno){
		global $sqlca;

		$sql = "SELECT estado FROM pos_consolidacion WHERE dia = '$dia' AND turno = $turno AND almacen = '$almacen';";

		$sqlca->query($sql);

		$estado = $sqlca->fetchRow();

		return $estado[0];

	}

	function ActualizarConsolidaciones($reporte, $almacen, $dia, $turno, $usuario, $ip){
		global $sqlca;

		$cuadres = $reporte['cuadres'];

		$di = ConsolidacionModel::obtenerDiferenciaIgnorada();

		$sqlca->query("BEGIN;");


		foreach ($cuadres as $cuadre) {
			
			$pruebita = abs($cuadre['fs']);

			$floate = number_format($pruebita, 2);

			//if ($floate == $di) 
			//	continue;

			if (abs($cuadre['fs'])<$di)
				continue;

			if (count($cuadre['lados'])>0)
				$tipodif = "C";
			else
				$tipodif = "M";

			if ($cuadre['trabajador'] == '???') {
				$sqlca->query("ROLLBACK;");
				return "ERROR_CON_INCOMPLETE";
			}

			if($cuadre['sucursal']==$almacen){
			$sql =	"	INSERT INTO
						comb_diferencia_trabajador
					(
						es,
						ch_codigo_trabajador,
						dia,
						turno,
						flag,
						importe,
						observacion,
						tipo
					) VALUES (
						'{$cuadre['sucursal']}',
						'{$cuadre['trabajador']}',
						to_date('{$reporte['dia']}','DD/MM/YYYY'),
						{$reporte['turno']},
						0,
						{$cuadre['fs']},
						'',
						'{$tipodif}'
					);";

			echo $sql;

			if ($sqlca->query($sql)<0) {
				$sqlca->query("ROLLBACK;");
				return "INTERNAL_ERROR_CON2";
			}
		}
		}

		$sql = "
			UPDATE
				pos_consolidacion
			SET
				estado  	= '1',
				usuario 	= '".$usuario."',
				ip 		= '".$ip."'
			WHERE
				dia 		= '".$dia."'
				AND turno 	= ".$turno."
				AND almacen	= '".$almacen."';
			";

		echo $sql;

		if ($sqlca->query($sql)<0) {
			$sqlca->query("ROLLBACK;");
			return 0;
		} else {
			return 1;
		}
	}

	function consolidar($reporte, $almacen, $dia, $turno, $usuario, $ip) {
		global $sqlca;

		if (ConsolidacionModel::consolidado($almacen,$dia,$turno))
			return "ERROR_CON_PRE";

		$cuadres = $reporte['cuadres'];

		$di = ConsolidacionModel::obtenerDiferenciaIgnorada();

		$sqlca->query("BEGIN;");

		foreach ($cuadres as $cuadre) {
			$pruebita = abs($cuadre['fs']);
			$floate = number_format($pruebita, 2);

			if ($floate == $di) 
				continue;

			if (abs($cuadre['fs'])<$di) 
				continue;

			if (count($cuadre['lados'])>0)
				$tipodif = "C";
			else
				$tipodif = "M";

			if ($cuadre['trabajador'] == '???') {
				$sqlca->query("ROLLBACK;");
				return "ERROR_CON_INCOMPLETE";
			}

			if($cuadre['sucursal']==$almacen){
			$sql =	"	INSERT INTO
						comb_diferencia_trabajador
					(
						es,
						ch_codigo_trabajador,
						dia,
						turno,
						flag,
						importe,
						observacion,
						tipo
					) VALUES (
						'{$cuadre['sucursal']}',
						'{$cuadre['trabajador']}',
						to_date('{$reporte['dia']}','DD/MM/YYYY'),
						{$reporte['turno']},
						0,
						{$cuadre['fs']},
						'',
						'{$tipodif}'
					);";

			if ($sqlca->query($sql)<0) {
				$sqlca->query("ROLLBACK;");
				return "INTERNAL_ERROR_CON2";
			}
		}

		}

		$sql =	"
				INSERT INTO
					pos_consolidacion
				(
					dia,
					turno,
					almacen,
					usuario,
					ip,
					estado
				) VALUES (
					'".$dia."',
					$turno,
					'".$almacen."',
					'$usuario',
					'$ip',
					'1'
				);
			";

		if ($sqlca->query($sql)<0) {
			$sqlca->query("ROLLBACK;");
			return "INTERNAL_ERROR_CON3";
		}

		$sql =	"SELECT lastval();";

		if ($sqlca->query($sql)<0 || $sqlca->numrows()==0) {
			$sqlca->query("ROLLBACK;");
			return "INTERNAL_ERROR_CON4";
		}

		$r = $sqlca->fetchRow();
		$id = $r[0];
		settype($id,"int");

		return $id;
	}

	function finalizaConsolidacion() {
		global $sqlca;

		$sqlca->query("COMMIT;");
	}

	function revierteConsolidacion() {
		global $sqlca;

		$sqlca->query("ROLLBACK;");
	}

	function obtenerDatosEESS() {
		global $sqlca;

		$sql =	"	SELECT
					trim(p1.par_valor),
					trim(p2.par_valor),
					trim(p3.par_valor),
					trim(p4.par_valor)
				FROM
					int_parametros p1
					LEFT JOIN int_parametros p2 ON p2.par_nombre = 'desces'
					LEFT JOIN int_parametros p3 ON p3.par_nombre = 'dires'
					LEFT JOIN int_parametros p4 ON p4.par_nombre = 'razsocial_market'
				WHERE
					p1.par_nombre = 'razsocial';";
		if ($sqlca->query($sql)<0)
			return FALSE;
		return $sqlca->fetchRow();
	}

	function obtenerComandoImprimir($file) {
		global $sqlca;

		$sql = "
		SELECT
			TRIM(cfg.ip) AS printerip,
			TRIM(cfg.prn_samba) AS printername
		FROM
			pos_cfg cfg
			JOIN int_parametros p ON (p.par_nombre='pos_consolida' AND cfg.pos::VARCHAR = p.par_valor);
		";

		$rs = $sqlca->query($sql);
		if ($rs < 0) {
			echo "Error consultando POS\n";
			return false;
		}
		if ($sqlca->numrows()<1)
			return true;

		$row = $sqlca->fetchRow();
		$smbc = "lpr -H {$row['printerip']} -P {$row['printername']} {$file}";
		return $smbc;
	}

	/**
	* verificar el ultimo turno del dia a consolidar
	*/
	public function validateDateTurnLast($arrParams){
		global $sqlca;
		$sql = "SELECT ch_posturno-1 FROM pos_aprosys WHERE da_fecha ='".$arrParams['dEntry']."';"; //Aca muestra el ultimo turno de pos_aprosys del indicado -1
		// echo "<pre>";
		// echo "<b>validateDateTurnLast</b><br>";
		// echo $sql;
		// echo "</pre>";
		$iStatus = $sqlca->query($sql);
		if ((int)$iStatus==0 || (int)$iStatus<0)
			return array('bStatus' => false, 'sMessage' => 'No hay datos para verificar consolidación');
		$row = $sqlca->fetchRow();
		return array('bStatus' => true, 'sTurn' => $row[0]);
	}

	/*
	* Obtener documentos electronicos no enviados
	* Se buscará registros del día que se está consolidando el último turno y anterior a esa fecha
	*/
	public function validateDocumentPending($arrParams){
		global $sqlca;		
		$sql = "
SELECT
 *
FROM
 fac_ta_factura_cabecera
WHERE
 ch_almacen = '".$arrParams['sCodeWarehouse']."'
 AND dt_fac_fecha = '".$arrParams['dEntry']."'
 AND SUBSTRING(ch_fac_seriedocumento FROM '[A-Z]+') IN('B','F')
 AND (nu_fac_recargo3!='3' AND nu_fac_recargo3!='5')
LIMIT 1;
		";//3 = Completado-enviado OR 5 = Anulado-Enviado
		$iStatus=$sqlca->query($sql);
		if ($iStatus==0)
			return array('bStatus' => true, 'sMessage' => 'No hay documentos pendientes para enviar SUNAT');
		return array('bStatus' => false, 'sMessage' => 'ADVERTENCIA: Documentos electrónicos de oficina pendientes para enviar a SUNAT');
	}
}
