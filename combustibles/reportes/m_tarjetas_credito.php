<?php

class Tarjetas_Credito_Model extends Model {

    function ObtenerEstaciones() {
		global $sqlca;
	
		try {
			$sql = "
SELECT
 ch_almacen as almacen,
 trim(ch_nombre_almacen) as nombre
FROM
 inv_ta_almacenes
WHERE
 ch_clase_almacen='1'
ORDER BY
 ch_almacen;
			";
			if($sqlca->query($sql) <= 0){
				throw new Exception("Error no se encontro turnos en la fecha indicada");
			}
			while($reg = $sqlca->fetchRow()){
				$registro[] = $reg;
			}
			return $registro;
		}catch(Exception $e){
			throw $e;
		}
   	}

	function ObtenerFechaDTurno($fecha) {
		global $sqlca;

		$fecha = trim($fecha);
		$fecha = strip_tags($fecha);
		$fecha = explode("/", $fecha);
		$pos_transYM = "pos_trans" . $fecha[2] . $fecha[1];
		$fecha = $fecha[2] . "-" . $fecha[1] . "-" . $fecha[0];

		try {
			$sql = "SELECT turno FROM " . $pos_transYM . " WHERE dia = '" . $fecha . "' GROUP BY turno ORDER BY turno";
			if($sqlca->query($sql) <= 0){
				throw new Exception("No hay turnos");
			}
			while($reg = $sqlca->fetchRow()){
			    $registro[] = $reg;
			}
			return $registro;
		}catch(Exception $e){
			throw $e;
		}
	}

	function ObtenerTarjetas() {
		global $sqlca;

		try {
			$sql = "
SELECT
 substring(tab_elemento from 6 for 1) id,
 tab_descripcion as name
FROM
 int_tabla_general
WHERE
 tab_tabla='95'
 AND tab_elemento!='000000'
ORDER BY
 tab_descripcion;
			";

            if ($sqlca->query($sql) <= 0) {
				throw new Exception("Error al obtener Tarjetas");
			}

			while ($reg = $sqlca->fetchRow()) {
				$registro[] = $reg;
			}
			return $registro;
		}catch(Exception $e){
			throw $e;
		}
	}

	function ObtenerReporte($almacen, $fdesde, $fhasta, $tdesde, $thasta, $tipo, $tarjeta) {
		global $sqlca;

		$fdesde = trim($fdesde);
		$fdesde = strip_tags($fdesde);
		$fdesde = explode("/", $fdesde);

		$pos_trans 	= "pos_trans".$fdesde[2].$fdesde[1];
		$fdesde 	= $fdesde[2] . "-" . $fdesde[1] . "-" . $fdesde[0];

		$fhasta = trim($fhasta);
		$fhasta = strip_tags($fhasta);
		$fhasta = explode("/", $fhasta);
		$fhasta = $fhasta[2] . "-" . $fhasta[1] . "-" . $fhasta[0];

		if($tarjeta == "T")
			$cond = "";
		else
			$cond = "at = '" . $tarjeta . "' AND ";

		if($almacen == "T")
			$es = "";
		else
			$es = "es = '" . $almacen . "' AND ";

		try {
			$registro = array();

			$sql = "
			SELECT
				p.tipo tipo,
				p.text1 numtar,
				p.at||' - '||tar.tab_descripcion nomtar,
				p.caja caja,
				ROUND(p.importe-COALESCE(p.km,0),2) importe,
				p.trans ticket,
				to_char(p.fecha,'DD/MM/YYYY') fecha,
				to_char(p.fecha,'HH24:MI') hora,
				ROUND(p.cantidad,2) cantidad,
				r.razsocial cliente,
				(SELECT
					COUNT(*)
				FROM
					" . $pos_trans . " AS pos
				WHERE
					" . $es . "
					" . $cond  . "
					";
					$sql.= "pos.dia::date||pos.turno BETWEEN '" . $fdesde . "'||'" . $tdesde . "' AND '" . $fhasta . "'||'" . $thasta . "'
					AND pos.fpago ='2'
					AND pos.td != 'N'
					AND pos.text1 = p.text1
					AND pos.at = p.at
					AND pos.grupo != 'D'
				) AS contador
			FROM
				" . $pos_trans . " AS p
				LEFT JOIN ruc AS r
					ON (p.ruc = r.ruc)
				LEFT JOIN int_tabla_general AS tar
					ON (trim(p.at) = substring(tar.tab_elemento,6,6) AND tar.tab_tabla ='95' AND tar.tab_elemento != '000000')
			WHERE
				" . $es. "
				" . $cond;

			$sql.= "
			p.dia::DATE||p.turno BETWEEN '" . $fdesde . "'||'" . $tdesde . "' AND '" . $fhasta . "'||'" . $thasta . "'
			AND p.td IN('B', 'F')
			AND p.fpago = '2'
			";

			if ($tipo == 'M') {
				$sql.= "
				AND p.tipo = 'M'";
			}elseif($tipo == 'C'){
				$sql.= "
				AND p.tipo = 'C'
				AND p.codigo != '11620307'";
			}elseif($tipo == 'GLP'){
				$sql.= "
				AND p.tipo = 'C'
				AND p.codigo = '11620307'";
			}

			$sql.= "
ORDER BY
 p.es,
 p.fecha;
			";

/*
echo "<pre>";
print_r($sql);
echo "<pre>";
*/

			if ($sqlca->query($sql) <= 0)
               	throw new Exception("No hay ningun registro en este rango de fecha: ".$fdesde.' - '.$fhasta);
			while ($reg = $sqlca->fetchRow())
				$registro[] = $reg;
			return $registro;
		}catch(Exception $e){
			throw $e;
		}
	}
}

